<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer.php');
require_once(__DIR__ . '/../models/NewDataForm.php');
require_once(__DIR__ . '/../models/GuestNewOrderForm.php');
require_once(__DIR__ . '/../models/GuestNewProjectForm.php');
require_once(__DIR__ . '/../models/GuestNewVacancyForm.php');
require_once(__DIR__ . '/../models/GuestActivationModel.php');
require_once(__DIR__ . '/../models/GuestMemoryModel.php');
require_once(__DIR__ . '/../models/GuestSmail.php');
require_once(__DIR__ . '/../models/GuestInviteUnsubscribeModel.php');


class GuestController extends CController 
{
    protected $uid;
    protected $is_adm = false;

    /**
     * Инициализация контроллера
     */
    public function init($action) 
    {
        parent::init();

        $this->uid = get_uid(false);
        $this->is_adm = hasPermissions('projects', $this->uid);
        
        $this->layout = '//layouts/content-with-right-narrow-sidebar';
    }


    /**
     * Обработка события до какого-либо экшена
     * 
     * @param string $action
     * @return bool
     */
    public function beforeAction($action) 
    {
        //Только для не зарегиных пользователей
        // и админов в вакансиях/проектах
        if ( ($this->uid > 0) && 
             !in_array($action, array('activate', 'unsubscribe')) && 
             !(in_array($action, array('vacancy','project')) && $this->is_adm) ) {
            
            $this->missingAction($action);//404
        }
        
        
        $is_project = false;
        
        switch ($action) {
            case 'project':
                $is_project = true;
            case 'vacancy':
                
                $this->layout = '//layouts/content-with-right-table-sidebar';
                require_once(__DIR__ . '/../widgets/GuestProjectSidebar.php');
                $this->getClips()->add('sidebar', $this->widget(
                        'GuestProjectSidebar', 
                        array('is_project' => $is_project), 
                        true));
                
                break;
        }
        
    }


    /**
     * Активация созданного ранее чего-либа и регистрация/авторизация
     */
    public function actionActivate()
    {
        $code = __paramInit('string', 'code', 'code', NULL);
        $redirect = GuestActivationModel::model()->doActivation($code);
        
        if (!$redirect) {
            $this->missingAction(NULL);
        }
        
        $this->redirect($redirect);
    }


    
    /**
     * Создание проекта
     */
    public function actionProject()
    {
        $subform = new GuestNewProjectForm(array('is_adm' => $this->is_adm));
        $form = new NewDataForm($subform, array('is_adm' => $this->is_adm));
        
        $form->addElement(new Form_Element_Hidden('social'));
        
        //Проверка перехода с лендинга публикации проекта
        if (($name = isLandingProject())) {
            $subform->setDefault('name', $name);
        }

        if (isset($_POST) && sizeof($_POST) > 0) {
            
            if (isset($_POST['social']) && $_POST['social']) {
                $form->getElement('uname')->setRequired(false);
                $form->getElement('usurname')->setRequired(false);
                $form->getElement('email')->setRequired(false);
            }
            
            $valid = $form->isValid($_POST);
            
            $data = $form->getValues();
            
            //@todo: подготовка данных, лучше все это делать в контролах но нет времени!
            //@todo: частично дублирует GuestHelper::overrideData
            
            //Если публикация проекта из лендинга, то фиксируем ID 
            //чтобы в случае публикации привязать проект
            if(($landingProjectId = getLastLandingProjectId())) {
                $data['dataForm']['landingProjectId'] = $landingProjectId;
            }
            
            $data['dataForm']['kind'] = 1;//проект!
            unset($data['dataForm']['profession']);
            $data['dataForm']['categories'][] = array(
                'category_id' => $subform->getElement('profession')->getGroupDbIdValue(),
                'subcategory_id' => $subform->getElement('profession')->getSpecDbIdValue()
            );
            $data['dataForm']['IDResource'] = @$data['dataForm']['IDResource'][0];
            
            $cost_element = $subform->getElement('cost');
            $is_agreement = $cost_element->getValue('agreement') == 1;
            $data['dataForm']['cost'] = ($is_agreement)?0:$data['dataForm']['cost'];
            $data['dataForm']['currency'] = ($is_agreement)?0:$cost_element->getValue('currency_db_id');
            $data['dataForm']['priceby'] = ($is_agreement)?1:$cost_element->getValue('priceby_db_id');           

            $filter = @$data['dataForm']['filter'];
            if(!$filter) $filter = array();
            $data['dataForm']['pro_only'] = true;//in_array('pro_only', $filter);
            $data['dataForm']['verify_only'] = in_array('verify_only', $filter);
            unset($data['dataForm']['filter']);
                
            if ($this->is_adm) {
                if (!$data['uname'] && !$data['usurname']) {
                    $data['uname'] = "Менеджер";
                    $data['usurname'] = "Компании";
                }
            }

            if (isset($data['dataForm']['auth']) && $data['dataForm']['auth']) {
                $data['dataForm']['agreement'] = $is_agreement;
                
                $guestMemoryModel = new GuestMemoryModel();
                $hash = $guestMemoryModel->saveData($data['dataForm']);
                $redirect = GuestConst::getMessage(GuestConst::URI_CANCEL, GuestConst::TYPE_PROJECT) . '&hash=' . $hash;
                $this->redirect($redirect);
            }
            
            if ($valid) {
                $oauth_link = $form->getElement('social')->getValue();
                unset($data['social']);
                
                if (!$oauth_link) {
                    $userValidator = $form->getElement('email')->getValidator('NoUserExists');
                    $user = $userValidator->getUser();

                    $data['user_id'] = $user->uid;
                }
                
              
                
                $data['type'] = GuestConst::TYPE_PROJECT;    
                
                $code = GuestActivationModel::model()->newActivation($data);

                if ($code) {
                    if ($oauth_link) {
                        $redirect = urlencode("/guest/activate/" . $code);
                        $this->redirect($oauth_link . '&emp_redirect=' . $redirect);                        
                    } else {
                        $guestSmail = new GuestSmail();

                        $link = ($this->is_adm)?$data['dataForm']['link']:'';
                        $guestSmail->sendActivation(
                                $data['email'], 
                                $code, 
                                $user, 
                                $data['type'], 
                                $link);

                        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Helpers/PopupAfterPageLoaded.php");
                        PopupAfterPageLoaded::getInstance()->showAfterLoad(GuestConst::getMessage(
                                ($user->uid > 0)?GuestConst::MSG_AL_EXIST:GuestConst::MSG_AL, $data['type']
                        ));

                        $ref_uri = '.';
                        if (!$this->is_adm) {
                            $ref_uri = (empty($_SESSION['ref_uri']))?
                                    sprintf('/'):
                                    urldecode($_SESSION['ref_uri']);
                        }

                        $this->redirect($ref_uri);
                    }                   
                }
            }
        }
        
        $this->render('project', array(
           'title' => 'Новый проект (задание на разовую работу)',
           'form' => $form->render()
        ));        
    }
    
    
    
    /**
     * Создание вакансии
     */
    public function actionVacancy()
    {
        $subform = new GuestNewVacancyForm(array('is_adm' => $this->is_adm));
        $form = new NewDataForm($subform, array('is_adm' => $this->is_adm));
        
        $form->addElement(new Form_Element_Hidden('social'));
        
        //@todo: не корректно использование - есть общее решение в GuestConst::$_error_messages
        $userValidator = $form->getElement('email')->getValidator('NoUserExists');
        $userValidator->setMessages(array('userFound' => GuestConst::VACANCY_EMAIL_BUSY));

        if (isset($_POST) && sizeof($_POST) > 0) {
            
            if (isset($_POST['social']) && $_POST['social']) {
                $form->getElement('uname')->setRequired(false);
                $form->getElement('usurname')->setRequired(false);
                $form->getElement('email')->setRequired(false);
            }
            
            $valid = $form->isValid($_POST);
            $data = $form->getValues();

            //@todo: подготовка данных, лучше все это делать в контролах но нет времени!
            //@todo: частично дублирует GuestHelper::overrideData
            $data['dataForm']['kind'] = 4;//вакансия!
            unset($data['dataForm']['profession']);
            $data['dataForm']['categories'][] = array(
                'category_id' => $subform->getElement('profession')->getGroupDbIdValue(),
                'subcategory_id' => $subform->getElement('profession')->getSpecDbIdValue()
            );

            unset($data['dataForm']['location']);
            $data['dataForm']['country'] = $subform->getElement('location')->getColumnId(0);
            $data['dataForm']['city'] = $subform->getElement('location')->getColumnId(1);

            $data['dataForm']['IDResource'] = @$data['dataForm']['IDResource'][0];

            $cost_element = $subform->getElement('cost');
            $is_agreement = $cost_element->getValue('agreement') == 1;
            $data['dataForm']['cost'] = ($is_agreement)?0:$data['dataForm']['cost'];
            $data['dataForm']['currency'] = ($is_agreement)?0:$cost_element->getValue('currency_db_id');
            $data['dataForm']['priceby'] = ($is_agreement)?1:$cost_element->getValue('priceby_db_id');           

            $filter = @$data['dataForm']['filter'];
            if(!$filter) $filter = array();
            $data['dataForm']['pro_only'] = in_array('pro_only', $filter);
            $data['dataForm']['verify_only'] = in_array('verify_only', $filter);
            unset($data['dataForm']['filter']);
            
            if ($this->is_adm) {
                if (!$data['uname'] && !$data['usurname']) {
                    $data['uname'] = "Менеджер";
                    $data['usurname'] = "Компании";
                }
            }
                
            if (isset($data['dataForm']['auth']) && $data['dataForm']['auth']) {
                $data['dataForm']['agreement'] = $is_agreement;
                
                $guestMemoryModel = new GuestMemoryModel();
                $hash = $guestMemoryModel->saveData($data['dataForm']);
                $redirect = GuestConst::getMessage(GuestConst::URI_CANCEL, GuestConst::TYPE_VACANCY) . '&hash=' . $hash;
                $this->redirect($redirect);
            }
            
            if ($valid) {
                
                $oauth_link = $form->getElement('social')->getValue();
                unset($data['social']);
                
                if (!$oauth_link) {
                    $userValidator = $form->getElement('email')->getValidator('NoUserExists');
                    $user = $userValidator->getUser();

                    $data['user_id'] = $user->uid;
                }
                
                $data['type'] = GuestConst::TYPE_VACANCY;
                
                $code = GuestActivationModel::model()->newActivation($data);

                if ($code) {
                    if ($oauth_link) {
                        $redirect = urlencode("/guest/activate/" . $code);
                        $this->redirect($oauth_link . '&emp_redirect=' . $redirect);                        
                    } else {
                        $guestSmail = new GuestSmail();

                        $link = ($this->is_adm)?$data['dataForm']['link']:'';
                        $guestSmail->sendActivation(
                                $data['email'], 
                                $code, 
                                $user, 
                                $data['type'], 
                                $link);

                        $messageKey = ($user->uid > 0) ? GuestConst::MSG_AL_EXIST : GuestConst::MSG_AL;
                        $messageText = GuestConst::getMessage($messageKey, $data['type']);

                        if ($user->uid > 0) {
                            $action = $user->is_pro == 't' ? GuestConst::VACANCY_ACTION_PRO : GuestConst::VACANCY_ACTION_NOPRO;
                            $messageText['message'] = sprintf($messageText['message'], $action);
                        }
                        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Helpers/PopupAfterPageLoaded.php");
                        PopupAfterPageLoaded::getInstance()->showAfterLoad($messageText);

                        $ref_uri = '.';
                        if (!$this->is_adm) {
                            $ref_uri = (empty($_SESSION['ref_uri']))?
                                    sprintf('/'):
                                    urldecode($_SESSION['ref_uri']);
                        }

                        $this->redirect($ref_uri);
                   }
                }
            }
        }
        
        $this->render('index', array(
           'title' => 'Новая вакансия',
           'form' => $form->render()
        ));        
    }
    
    
    
    /**
     * Создание персонального заказа
     */
    public function actionPersonalOrder()
    {
        $login = __paramInit('string', 'user', 'user', NULL);

        $freelancer = new freelancer();
        $freelancer->GetUser($login);
        if ($freelancer->uid <= 0) {
           $this->missingAction(NULL);
        }
        
        $subform = new GuestNewOrderForm();
        $subform->freelancer = $freelancer;
        $form = new NewDataForm($subform);

        if (isset($_POST) && sizeof($_POST) > 0 && $form->isValid($_POST)) {
           $userValidator = $form->getElement('email')->getValidator('NoUserExists');
           $user = $userValidator->getUser();
           $data = $form->getValues();
           $data['user_id'] = $user->uid;
           $data['type'] = GuestConst::TYPE_PERSONAL_ORDER;
           $data['dataForm']['frl_id'] = intval($freelancer->uid); 
                   
           $code = GuestActivationModel::model()->newActivation($data);

           if ($code) {
               $guestSmail = new GuestSmail();
               $guestSmail->sendActivation($data['email'], $code, $user, $data['type']);
               
               require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Helpers/PopupAfterPageLoaded.php");
               PopupAfterPageLoaded::getInstance()->showAfterLoad(GuestConst::getMessage(
                       ($user->uid > 0)?GuestConst::MSG_AL_EXIST:GuestConst::MSG_AL, $data['type']
               ));
               
               $ref_uri = (empty($_SESSION['ref_uri']))?
                       sprintf('/users/%s/', $freelancer->login):
                       urldecode($_SESSION['ref_uri']);
               $this->redirect($ref_uri);
           }
        }

        //Выводим в сайдбар виджет индикатор статуса заказа
        require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/OrderStatusIndicator.php');
        $this->getClips()->add('sidebar', $this->widget('OrderStatusIndicator', array(), TRUE));

        $this->render('index', array(
           'title' => 'Новый заказ',
           'form' => $form->render()
        ));
    }
    
    /**
     * Отписка от приглашений
     */
    public function actionUnsubscribe()
    {
        $hash = __paramInit('string', 'hash', 'hash', NULL);
        $email = __paramInit('string', 'email', 'email', NULL);
        
        $guestInviteUnsubscribeModel = new GuestInviteUnsubscribeModel();
        
        $trueHash = $guestInviteUnsubscribeModel->getHash($email);
        
        if ($hash == $trueHash) {
            $guestInviteUnsubscribeModel->addEmail($email);
            //@todo: решение не учитывает что отписываться могу не только от вакансий
            // пока пришлось подправить сообщение
            $notification = GuestConst::$_unsubscribe_ok_message;
            $notification['message'] = sprintf($notification['message'], $email);
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Helpers/PopupAfterPageLoaded.php");
            PopupAfterPageLoaded::getInstance()->showAfterLoad($notification);
            $this->redirect('/');
        } else {
            $this->missingAction(NULL);
        }
    }
    
}