<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yii/tinyyii.php');
require_once(__DIR__ . '/../models/BillInvoicesAdminModel.php');
require_once(__DIR__ . '/../models/BillInvoicesPayForm.php');


class BillInvoicesAdminController extends CController 
{
    protected $billInvoicesAdminModel;




    /**
     * Инициализация контроллера
     */
    public function init($action) 
    {
        parent::init();

        $this->layout = '//layouts/content-default';
        $this->billInvoicesAdminModel = new BillInvoicesAdminModel();
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



    public function actionIndex() 
    {
        $limit = 20;
        $do = __paramInit('string', 'do', 'do', '');
        $page = __paramInit('int', 'page', 'page', 1);
        
        $filter = array();
        $filter_default = array(
            'date' => date('d.m.Y', strtotime('-1 month')) . ' - ' . date('d.m.Y')
        );
        
        switch($do) {
            
            case 'factura_delete':
                
                $nums = __paramInit('array', NULL, 'num', null);
                $this->billInvoicesAdminModel->deleteFactura($nums);
                
                break;
            
            case 'factura_update':
                
                $invoice_id = __paramInit('int', NULL, 'invoice_id', null);
                $file = $_FILES['new_file'];
                
                $this->billInvoicesAdminModel->updateFactura($invoice_id, $file);
                
                break;
            
            case 'factura':
                
                $nums = __paramInit('array', NULL, 'num', null);
                $dates = __paramInit('array', NULL, 'date', null);
                $this->billInvoicesAdminModel->addFactura($nums, $dates);
                
                break;
            
            case 'filter':
                
                $filter['do'] = $do;
                $filter['login'] = __paramInit('string', 'login', 'login', '');
                $filter['date'] = __paramInit('string', 'date', 'date', null);
                
                break;
            
            
            //Операция зачисления средств по счету
            case 'pay':
                require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
                require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/account.php' );
                require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/billing.php');
                
                $sums = __paramInit('array', NULL, 'sum', null);
                
                //@todo: слишком толстый контроллер 
                //все ниже нужно было определить в модель
                
                $account = new account();
                $user  = new users();
                
                if($sums) {
                    $sAdmin  = 'Запись добавил: ' . @$_SESSION['login'];
                    $sDate   = date('c');
                    
                    foreach($sums as $user_id => $invoices) {
                        
                        $user->GetUserByUID($user_id);
                        
                        if(!$user->uid || 
                           !$account->GetInfo($user->uid, true) || 
                           empty($invoices)) {
                            continue;
                        }

                        $bill = new billing($user->uid);
                        
                        foreach($invoices as $invoice_id => $sum)
                        {
                            $account_sum = $account->sum;
                            
                            if(!is_numeric($sum) || 
                               $sum <= 0 || 
                               ($account->sum + $sum) < 0) {
                                continue;
                            }

                            $comments  = sprintf("Безналичный перевод по счету Б-%06d", $invoice_id);
                            
                            if(!$account->depositEx2(
                                    $acc_op_id,
                                    $account->id, 
                                    $sum, 
                                    $sAdmin, 
                                    $comments, 
                                    12, 
                                    $sum, 
                                    4,//безнал 
                                    $sDate)){

                                $this->billInvoicesAdminModel->update($invoice_id, array(
                                    'acc_op_id' => $acc_op_id
                                ));
                                
                                //Автоматическая покупка услуги погашения задолженности
                                if ($account_sum < 0) {
                                    $payed_sum = abs($account_sum);
                                    $option = array('acc_sum' => $payed_sum);
                                    $billReserveId = $bill->addServiceAndCheckout(135, $option);
                                    if ($billReserveId) {
                                        $bill->buyOrder($billReserveId);
                                    }
                                }
                            }
                        }
                    }

                    
                    $this->redirect('.');
                }

                break;
        }
        
        $this->billInvoicesAdminModel->setPage($limit, $page);
        $list = $this->billInvoicesAdminModel->setFilter($filter)->getInvoices();
        $count = $this->billInvoicesAdminModel->getInvoicesCnt();
        
        $filter_query = '';
        if(empty($filter)) {
            $filter = $filter_default;
        } else {
            $filter_query = http_build_query($filter) . '&';
        }
        
        $this->render('index', array(
            'list' => $list,
            'limit' => $limit,
            'page' => $page,
            'page_count' => $count,
            'filter_query' => $filter_query,
            'filter' => $filter
        ));
    }
     
}