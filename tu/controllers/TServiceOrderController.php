<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/yii/tinyyii.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/registration.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceOrderUserProfile.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceOrderStatus.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceOrderFeedback.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceOrderHistory.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceOrderFiles.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceOrderChangeCostPopup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceOrderMessagesForm.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceOrderMessages.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceOrderBreadcrumbs.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/OrderStatusIndicator.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_helper.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_smail.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/NewOrderForm.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php');


/**
 * Class TServiceOrderController
 * Контроллер заказов ТУ
 */
class TServiceOrderController extends CController 
{
        private $uid = NULL; 
        private $is_emp;
        private $is_adm;
        private $order_model;

        
        /**
         * Инициализация контроллера
         */
        public function init() 
        {
            parent::init();
        
            $this->is_adm = hasPermissions('tservices');
            $this->is_emp = (bool)is_emp();
            $this->layout = '//layouts/content-with-right-sidebar-fixed';
            $this->order_model = TServiceOrderModel::model();
        }
    
        
        /**
         * Обработка события до какого-либо экшена
         * 
         * @param string $action
         * @return bool
         */
        public function beforeAction($action) 
        {
            $this->uid = get_uid(false);
            
            //Если будет новый заказ от анонимуса 
            //то эти проверки не нужны
            if ($action != 'neworder') {
                //Юзер должен быть зарегистрирован
                if (!$this->uid) { 
                    switch ($action) {
                        //редирект на создание заказа для анонимуса
                        case 'newpersonalorder':
                            $this->redirect(tservices_helper::getGuestPersonalOrderUrl());
                            break;
                        
                        //редирект на регистрацию/авторизацию
                        default:
                            $this->redirect('/registration/?user_action=add_order');
                    }
                }
            
                //Юзер в белом списке?
                if (!tservices_helper::isUserOrderWhiteList()) {
                    $this->missingAction($action);
                }
            }
            
            //искуственно повторяем чтобы сохранить текущий урл в сессию
            $this->uid = get_uid();
            
            //заказ инициировать может только работодатель
            $filter_action = in_array($action, array('order', 'newprojectorder', 'newpersonalorder'));
            if ($filter_action && !$this->is_emp) {
                $this->redirect('/registration/?user_action=add_order');
            }
            
            //свой список заказов видит только фрилансер
            if($action == 'frllist' && $this->is_emp){
                if ($_SESSION['login']) {
                    $url = "/users/{$_SESSION['login']}/tu-orders/";
                    $this->redirect($url);
                } else {
                    $this->missingAction($action);
                }
            }
            
            return TRUE;
        }
        
        
        
        
        /**
         * Cоздание заказа на базе проекта
         */
        public function actionNewProjectOrder()
        {
            //@todo: на период разработки резерва заказов
            if(!tservices_helper::isAllowOrderReserve()) $this->missingAction(NULL);
            
            $offer_id = __paramInit('int', 'offer_id', 'offer_id', NULL);
            
            $projects = new projects();
            $projectData = $projects->getProjectByOfferId($offer_id, $this->uid);
            
            if(!$projectData) $this->missingAction(NULL);
            
            $freelancer = new freelancer();
            $freelancer->GetUserByUID($projectData['frl_id']);
            if($freelancer->uid <= 0) $this->missingAction(NULL);
            
            $form = new NewOrderForm(/*options*/);
            
            $form->setDefaults(array(
                //@todo: в проектах при сохранении символы преобразуются в сущности
                'title' => addslashes(htmlspecialchars_decode($projectData['name'], ENT_QUOTES)),
                'description' => addslashes(htmlspecialchars_decode($projectData['descr'], ENT_QUOTES)),
                'order_price' => ($projectData['currency'] == 2)?$projectData['cost']:''
            ));
            
            if(isset($_POST) && sizeof($_POST) > 0 && $form->isValid($_POST)) 
            {
                $data = $form->getValues();
                $data['frl_id'] = $freelancer->uid;
                $data['emp_id'] = $this->uid;
                $data['tu_id'] = $projectData['id'];
                
                if($order = $this->order_model->createFromProject($data))
                {
                    $projects->SetExecutor($projectData['id'], $projectData['frl_id'], $projectData['user_id']);
                    
                    $tservices_smail = new tservices_smail();
                    $tservices_smail->newOrder($order);
                
                    $this->redirect(sprintf(tservices_helper::url('order_card_url'),$order['id']));                  
                }
            }
            
            //Выводим в сайдбар виджет индикатор статуса заказа
            $this->getClips()->add('sidebar', $this->widget('OrderStatusIndicator', array(), TRUE));
            
            $this->render('new-order-form', array(
                'title' => 'Новый заказ по проекту',
                'submit_title' => 'Выбрать исполнителем и предложить заказ',
                'cansel_url' => getFriendlyURL("project", $projectData),
                'form' => $form,
                'freelancer' => $freelancer
            ));
        }




        /**
         * Создание персонального заказа
         */
        public function actionNewPersonalOrder()
        {
            //@todo: на период разработки резерва заказов
            if(!tservices_helper::isAllowOrderReserve()) $this->missingAction(NULL); 
            
            $login = __paramInit('string', 'user', 'user', NULL);
            $freelancer = new freelancer();
            $freelancer->GetUser($login);
            if($freelancer->uid <= 0) $this->missingAction(NULL);
            
            $form = new NewOrderForm(/*options*/);
            
            if(isset($_POST) && sizeof($_POST) > 0 && $form->isValid($_POST)) 
            {
                $data = $form->getValues();
                $data['frl_id'] = $freelancer->uid;
                $data['emp_id'] = $this->uid;
                
                if($order = $this->order_model->createPersonal($data))
                {
                    $tservices_smail = new tservices_smail();
                    $tservices_smail->newOrder($order);
                
                    $this->redirect(sprintf(tservices_helper::url('order_card_url'),$order['id']));                  
                }
            }
            
            //Выводим в сайдбар виджет индикатор статуса заказа
            $this->getClips()->add('sidebar', $this->widget('OrderStatusIndicator', array(), TRUE));
            
            $this->render('new-order-form', array(
                'title' => 'Новый заказ',
                'submit_title' => 'Предложить заказ',
                'cansel_url' => '/',
                'form' => $form,
                'freelancer' => $freelancer
            ));
        }



        /**
         * Список заказов ТУ фрилансера
         */
        public function actionFrllist()
        {   
            $status = __paramInit('string', 's', 's', NULL);
            
            $page = __paramInit('int', 'page', 'page', 1);
            if($page <= 0) $page = 1;
            $on_page = 10;
            
            //Если параметры не проходят валидацию то редирект на основную по умолчанию
            if(!$this->order_model->attributes(array('status' => $status))) 
            {
                $this->redirect('/tu-orders/', TRUE, 301);
            }
            
            $orders_list = $this->order_model->setPage($on_page, $page)->getListForFrl($this->uid);
            $cnts = $this->order_model->getCounts($this->uid, FALSE);
            if(!$cnts['total']) $this->missingAction(NULL);
            
            $this->tserviceOrderStatusWidget = $this->createWidget($this,'TServiceOrderStatus', array('is_list' => true)); 
            $this->tserviceOrderStatusWidget->setIsEmp(FALSE);
            $this->modelMessage = TServiceMsgModel::model();
            
            $this->layout = '//layouts/content-full-width-border';
            $this->render('frllist', array(
                'orders_list' => $orders_list,
                'cnts' => $cnts,
                'on_page' => $on_page,
                'page' => $page,
                'status' => $status
            ));
        }

        
        
        /**
         * Смена статуса заказа
         */
        public function actionStatus()
        {
            $params = (empty($_POST))?$_GET:$_POST;
            unset($params['action'], $params['hash']);
            $curhash = tservices_helper::getOrderUrlHash($params);
            $hash = __paramInit('string','hash','hash',NULL);
            if($curhash !== $hash) $this->redirect('/404.php');
            
            $order_id = __paramInit('int','order_id','order_id',0);
            $new_status = __paramInit('string','status','status',NULL);
            
            $order = $this->order_model->getCard($order_id, $this->uid);
            if(!$order) $this->redirect('/404.php');          
            
            $this->order_model->changeStatus($order_id, $new_status, $this->is_emp);
            $url = tservices_helper::getOrderCardUrl($order_id);
            $this->redirect($url);
        }



        /**
         * Карточка заказа
         */
        public function actionIndex() 
        {
            $order_id = __paramInit('int','order_id','order_id',0);
            
            $this->order_model->attributes(array('is_adm' => $this->is_adm));
            $order = $this->order_model->getCard($order_id, $this->uid);
            if(!$order) $this->missingAction(NULL);
            
            $this->getClips()->add('sidebar', $this->widget('OrderStatusIndicator', array('order' => $order), TRUE));
            
            $prefix = $this->is_emp?'emp':'frl';
            $is_owner = ($order["{$prefix}_id"] == $this->uid);
            $allowChangePriceTime = $is_owner && $this->is_emp && $order['status'] == TServiceOrderModel::STATUS_NEW && !isset($order['reserve_data']);
            
            //Виджет окошка редактирования бюджета и сроков
            if($allowChangePriceTime)
            {
                $this->getClips()->add('order-change-cost-popup', $this->widget('TServiceOrderChangeCostPopup', array(
                    'order' => $order
                ), TRUE));
            }
            
            if($is_owner)
            {
                $this->getClips()->add('user-profile', $this->widget('TServiceOrderUserProfile', array(
                    'order' => $order,
                    'is_emp' => $this->is_emp
                ), TRUE)); 
            }
            else
            {
                $this->getClips()->add('employer-profile', $this->widget('TServiceOrderUserProfile', array(
                    'order' => $order,
                    'is_emp' => FALSE
                ), TRUE));
                
                $this->getClips()->add('freelancer-profile', $this->widget('TServiceOrderUserProfile', array(
                    'order' => $order,
                    'is_emp' => TRUE
                ), TRUE));
            }
            
            $allowMessagesForm = $is_owner || ($this->is_adm && $this->order_model->isArbitrage());
            if ($allowMessagesForm) {
                $this->getClips()->add('order-messages-form', $this->widget('TServiceOrderMessagesForm', array(
                    'order_id' => $order_id,
                    'uid' => $this->uid
                ), TRUE));
            }
            
            $this->getClips()->add('order-messages', $this->widget('TServiceOrderMessages', array(
                'order_id' => $order_id,
                'is_owner' => $is_owner,
                'uid' => $this->uid,
                'frl_id' => $order['frl_id']
            ), TRUE));
                
            $this->getClips()->add('order-status', $this->widget('TServiceOrderStatus', array(
                'order' => $order,
                'is_emp' => $this->is_emp,
                'is_owner' => $is_owner
            ), TRUE));
            
            $this->getClips()->add('order-history', $this->widget('TServiceOrderHistory', array(
                'order_id' => $order_id
            ), TRUE));
            
            $this->getClips()->add('order-files', $this->widget('TServiceOrderFiles', array(
                'order_files' => $order['files']
            ), TRUE));
            
            $this->getClips()->add('order-breadcrumbs', $this->widget('TServiceOrderBreadcrumbs', array(
                'order' => $order,
                'is_emp' => $this->is_emp || !$is_owner,
            ), TRUE));
            
            if($is_owner)
            {
                /*
                $this->getClips()->add('order-feedback-popup', $this->widget('TServiceOrderFeedback', array(
                ), TRUE));
                */
                
                //Помечаем заказ как прочтенный
                if($order["{$prefix}_read"] == 'f')
                {
                    $this->order_model->markAsReadOrderEvents(
                            $this->uid, 
                            $order_id, 
                            $this->is_emp);
                }
            }
            
            //Показать уведомление в серой плашке под меню
            if($this->is_emp && $allowChangePriceTime && $this->order_model->isPayTypeDefault()) {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Helpers/SubBarNotificationHelper.php");
                SubBarNotificationHelper::getInstance()->setNowMessage(SubBarNotificationHelper::TYPE_RESERVE_PROMO, array(
                    'url' => tservices_helper::getOrderCardUrl($order['id']) . '?tu_edit_budjet=1&paytype=1'
                ));
            }
            
            
            $this->render('index', array(
                'order' => $order,
                'is_emp' => $this->is_emp,
                'is_owner' => $is_owner,
                'allow_change' => $allowChangePriceTime
            ));
            
            global $page_title;
            $page_title = "Заказ №{$order_id} – FL.ru";
        }
        
    
        /**
         * Формирование заказа
         */
        public function actionOrder()
        {
            $service_id = __paramInit('int','tuid','tuid',0);

            $debt_info = $this->order_model->isDebt($this->uid);
            
            //блокируем возможность заказать если у исполнителя долг 
            //и вышли все сроки погашения
            if($debt_info && $debt_info['is_blocked'] == 't')
            {
                $this->missingAction(NULL);
            }
            
            $this->order_model->attributes(array(
                'order_is_express' => __paramInit('bool',NULL,'is_express',FALSE),
                'order_extra' => __paramInit('array_int',NULL,'extra',array()),
                'order_paytype' => __paramInit('int',NULL,'order_paytype',0),
                'emp_id' => $this->uid
            ));


            if($order = $this->order_model->create($service_id))
            {
                $tservices_smail = new tservices_smail();
                $tservices_smail->newOrder($order);
                
                $this->redirect(sprintf(tservices_helper::url('order_card_url'),$order['id']));
            }
            
            $this->missingAction(NULL);
        }
        
        
        
        
        
        
        public function actionNewOrder()
        {
            $code = __paramInit('string','code','code','');
            $activation_data = $this->order_model->getOrderActivation($code);
            if(!$activation_data) $this->missingAction(NULL); 
            $this->order_model->deleteOrderActivation($code);
            $is_new = !($activation_data['user_id'] > 0);
            
            $registration = new registration();
            $user_data = $registration->autoRegistationAndLogin(array(
                    //Если есть можно просто авторизовать юзера
                    'uid' => $activation_data['user_id'],
                    'role' => 1,
                    //обязательное поле это email
                    'email' => $activation_data['email'],
                    'uname' => $activation_data['uname'],
                    'usurname' => $activation_data['usurname']
                ));
            
            //Если почему то не можем зарегать
            //то редиректим на регистрацию
            if (!$user_data || !$user_data['ret']) {
                $this->redirect('/registration/');
            }  
            
            $status = $user_data['ret'];
            $user = $user_data['user'];            
            
            $this->uid = $user->uid;
            $service_id = intval($activation_data['tu_id']);
            
            $debt_info = $this->order_model->isDebt($this->uid);
            
            //блокируем возможность заказать если у исполнителя долг 
            //и вышли все сроки погашения
            if ($debt_info && $debt_info['is_blocked'] == 't') {
                $this->missingAction(NULL);
            }
            
            $activation_data['options']['emp_id'] = $this->uid;
            $this->order_model->attributes($activation_data['options']);
            $order = $this->order_model->create($service_id);
            //Не удалось создать заказ показываем 404
            if (!$order) {
                $this->missingAction(NULL);
            }
                
            //Уведомляем все стороны
            $tservices_smail = new tservices_smail();
            $tservices_smail->newOrder($order);
            
            //Если юзер уже бывалый пользователь то редиректим на карточку заказа
            $order_url = sprintf(tservices_helper::url('order_card_url'),$order['id']);
            if (!$is_new) { 
                if ($status == users::AUTH_STATUS_2FA) {
                    $_SESSION['ref_uri'] = $order_url;
                    $order_url = '/auth/second/';                    
                }

                $this->redirect($order_url);            
            }
            
            //Берем доп.инфу о фрилансере
            $freelancer = new freelancer();
            $freelancer->GetUserByUID($order['frl_id']);
            
            //Если юзер новичек то показываем ему логин/пароль и статус заказа
            $this->layout = '//layouts/content-full-width';
            $this->render('new-order', array(
                'order_url' => $order_url,
                'login' => $user->login,
                'passwd' => $user->passwd,
                'freelancer' => (array)$freelancer
            ));
        }
        
        
        
}