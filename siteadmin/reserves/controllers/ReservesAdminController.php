<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yii/tinyyii.php');
require_once(__DIR__ . '/../models/ReservesAdminOrderModel.php');
require_once(__DIR__ . '/../widgets/ReservesAdminNavigation.php');
require_once(__DIR__ . '/../models/ReservesFilterForm.php');
require_once(__DIR__ . '/../models/ReservesChangeStatusForm.php');
require_once(__DIR__ . '/../models/ReservesReestrForm.php');
require_once(__DIR__ . '/../models/ReservesAdminReestrModel.php');

class ReservesAdminController extends CController 
{
    const DETAILS_URL = "/siteadmin/reserves/?action=details&num=%d";
    
    /**
     * Инициализация контроллера
     */
    public function init($action) 
    {
        parent::init();

        $this->layout = '//layouts/content-full-width';
        
        $this->getClips()->add('navigation', $this->widget(
                'ReservesAdminNavigation', 
                array('current_action' => $action), 
                true));
    }


    /**
     * Обработка события до какого-либо экшена
     * 
     * @param string $action
     * @return bool
     */
    /*
    public function beforeAction($action) 
    {

    }
    */

   
    
   public function actionArchive()
   {
       require_once(__DIR__ . '/../models/ReservesArchiveModel.php');
       
       $archiveModel = ReservesArchiveModel::model();
       
       if (isset($_POST) && sizeof($_POST) > 0) {
            require_once(__DIR__ . '/../models/ReservesAddArchiveForm.php');
            $addArchiveForm = new ReservesAddArchiveForm();
            if ($addArchiveForm->isValid($_POST)) {
                $archiveModel->addArchiveRequest($addArchiveForm->getValues());
                $this->redirect('?action=archive');
            }
       }
       
       
       $page = __paramInit('int', 'page', 'page', 1);
       $limit = 20; 

       $list = $archiveModel->setPage($limit, $page)->getList();
       $count = $archiveModel->getCount();
       
       $this->render('archive', array(
           'list' => $list,
           'page' => $page,
           'limit' => $limit,
           'page_count' => $count
       ));
   }








   public function actionFactura()
   {
       require_once(__DIR__ . '/../models/ReservesAdminReestrFacturaModel.php');
       
       $page = __paramInit('int', 'page', 'page', 1);
       $limit = 20;   
       
       $model = ReservesAdminReestrFacturaModel::model();
       $files = $model->setPage($limit, $page)->getReestrs();
       $count = $model->getReestrsCount();
       
       $this->render('factura', array(
           'files' => $files,
           'limit' => $limit,
           'page' => $page,
           'page_count' => $count           
       ));        
   }

      /**
    * Список подозрительный сделок
    */ 
   public function actionFrod()
   {
        $page = __paramInit('int', 'page', 'page', 1);
		$limit = 20;   
        
        $reserveInstance = ReservesAdminOrderModel::model();
        $reserveInstance->setFilterType('frod');
        $reserveInstance->setFilterData(array('is_frod' => 't'));
        $reserves = $reserveInstance->setPage($limit, $page)->getReservesList();
        $count = $reserveInstance->getReservesListCount();
        
        $this->render('frod', array(
            'reserves' => $reserves,
            'limit' => $limit,
            'page' => $page,
            'page_count' => $count
        ));        
   }

   

   /**
    * Подробная инфо о БС
    */ 
   public function actionDetails()
   {
       $num = __paramInit('int', 'num');
       
       if (!$num) {
           $this->missingAction(null);
       }
       
       $reserveInstance = ReservesAdminOrderModel::model();
       $reserve = $reserveInstance->getReserveAdmin($num);
       
       if (!$reserve) {
           $this->missingAction(null);
       }
       
       $formChangeStatus = new ReservesChangeStatusForm();
       if (isset($_POST) && sizeof($_POST) > 0) {
           
           $do = __paramInit('string', NULL, 'do', null);
           
           switch ($do) {
               
               case 'accept_back':
                   $reserveInstance->changeBackStatus(ReservesAdminOrderModel::SUBSTATUS_PAYED);
                   break;
               case 'decline_back':
                   $message = __paramInit('string', NULL, 'message', null);
                   $reserveInstance->setUpdatedData('reason_payback', $message);
                   $reserveInstance->changeBackStatus(ReservesAdminOrderModel::SUBSTATUS_ERR);
                   break; 
               
               
               case 'accept_pay':
                   $reserveInstance->changePayStatus(ReservesAdminOrderModel::SUBSTATUS_PAYED);
                   break;
               case 'decline_pay':
                   $message = __paramInit('string', NULL, 'message', null);
                   $reserveInstance->setUpdatedData('reason_payout', $message);
                   $reserveInstance->changePayStatus(ReservesAdminOrderModel::SUBSTATUS_ERR);
                   break;               
               
               
               case 'accept_reserve':
                   $reserveInstance->changeStatus(ReservesAdminOrderModel::STATUS_RESERVE);
                   break;
               case 'decline_reserve':
                   $message = __paramInit('string', NULL, 'message', null);
                   $reserveInstance->setUpdatedData('reason_reserve', $message);
                   $reserveInstance->changeStatus(ReservesAdminOrderModel::STATUS_ERR);
                   break;
               
               
               
               default:
                   $docs = __paramInit('array_int', NULL, 'docs', array());
                   $is_create = __paramInit('bool', NULL, 'create', false);

                   if (!empty($docs) || $is_create) {

                       $data = array();
                       $datereqv_complete = __paramInit('string', NULL, 'file_date', null);

                       //@todo: если будет ряд данных на изменения то подумать как сделать лучше
                       if ($datereqv_complete) {
                           $datereqv_complete = date_text($datereqv_complete,'j');
                           $data['datereqv_complete'] = $datereqv_complete;
                       }

                       $reserveInstance->updateDocs($docs, $is_create, $data);

                   } elseif ($formChangeStatus->isValid($_POST)) {

                       $to_status = $formChangeStatus->getValue('status');
                       $reserveInstance->switchStatus($to_status);

                   } 
           }

           $this->redirect(sprintf(self::DETAILS_URL, $num));
           
       } else {
           $formChangeStatus->setDefaultStatus(
                   $reserveInstance->getReserveOrderStatus(), 
                   $reserveInstance->getReserveDataByKey('arbitrage_is_emp'));
       }
       

       $this->render('details', array(
           'reserveInstance' => $reserveInstance,
           'form' => $formChangeStatus->render()
       ));
   }


   
   
   /**
    * Список БС
    */
   public function actionIndex() 
   {
        $page = __paramInit('int', 'page', 'page', 1);
		$limit = 20;
        $params = '';
        
        $reserveInstance = ReservesAdminOrderModel::model();
        $reserveInstance->setFilterType('all');
        $form = new ReservesFilterForm($reserveInstance->getFilter());
        
        if (isset($_GET) && sizeof($_GET) > 0 && $form->isValid($_GET)) {
            $data = $form->getValues();
            $reserveInstance->setFilterData($data);
            $params = '&' . http_build_query($data);
        }
        
        $reserves = $reserveInstance->setPage($limit, $page)->getReservesList();
        $count = $reserveInstance->getReservesListCount();
        
        $this->render('index', array(
            'reserves' => $reserves,
            'limit' => $limit,
            'page' => $page,
            'page_count' => $count,
            'form' => $form,
            'params' => $params,
            'summary' => $reserveInstance->getSummary()
        ));
    }
    
    
    
    /**
     * Реестры
     */
    public function actionReestr()
    {
        $tableData = $submenu = $summary = array();
        
        $mode = __paramInit('string', 'mode', 'mode', ReservesAdminReestrModel::MODE_REESTRES);

        $reestrModel = new ReservesAdminReestrModel($mode);
        $form = new ReservesReestrForm();
        
        if (isset($_GET) && sizeof($_GET) > 0 && $form->isValid($_GET)) {
            $data = $form->getValues();
            
            //Пока календарь в некоторых случаях выдает дату в формате "15 апреля 2014"
            //Заменяем на стабильное значение
            $data['date_start'] = __paramInit('string', 'date_start_eng_format');
            $data['date_end'] = __paramInit('string', 'date_end_eng_format');
            
            if (!$data['date_start'] || !$data['date_end']) {
                $form->reset();
            }
            
            if ($reestrModel->getMode() == ReservesAdminReestrModel::MODE_REESTRES) {
                $reestrModel->generateReestrs($data);
                $summary = $reestrModel->getSummary();
                $tableData = $reestrModel->getReestr3();
            } elseif ($reestrModel->getMode() == ReservesAdminReestrModel::MODE_DOCUMENTS) {
                $tableData = $reestrModel->getDocuments($data);
            } elseif($reestrModel->getMode() == ReservesAdminReestrModel::MODE_NDFL) {
                $tableData = $reestrModel->getNdfl($data);
            }
            $submenu = $reestrModel->getSubmenu();
        }
        
        $this->render('reestr', array(
            'reestrModel' => $reestrModel,
            'form' => $form,
            'mode' => $reestrModel->getMode(),
            'menu' => $reestrModel->getMenu(),
            'submenu' => $submenu,
            'path' => WDCPREFIX . $reestrModel->path,
            'summary' => $summary,
            'fields' => $reestrModel->getFields(),
            'data' => $tableData,
            'isDocMode' => $reestrModel->isDocMode()
        ));        
   }
     
}