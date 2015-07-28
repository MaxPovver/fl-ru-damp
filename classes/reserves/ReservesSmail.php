<?php

/**
 *  Класс для работы с уведомлениями при резервировании средств.
 *
 */

require_once($_SERVER['DOCUMENT_ROOT'].'/classes/tservices/tservices_helper.php');
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/employer.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/freelancer.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/template.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/smail.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/reserves/ReservesSms.php';

/**
 * Директория шаблонов писем
 */
define('RESERVES_TPL_MAIL_PATH', $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/reserves/');
define('RESERVES_TPL_BASE_LAYOUT', 'layout.tpl.php');

class ReservesSmail extends smail
{

    protected $order = array();
    protected $is_emp;
    
    protected $template_format = '%s_%s.tpl.php';
    protected $is_local = FALSE;
    
    public function __construct() 
    {
        parent::__construct();

        $server = defined('SERVER')?strtolower(SERVER):'local';
        $this->is_local = ($server == 'local');
    }
    
    /**
     * Скрываем вызов некоторых методов чтобы при их вызове проверить 
     * в каком окружении запускается рассылка и если на локале то игнорим ее
     * 
     * @todo: Если мешает достаточно закоментить проверку на лакальность ;)
     * 
     * @param string $method
     * @param type $arguments
     * @return boolean
     */
    public function __call($method, $arguments) 
    {
        if($this->is_local) return FALSE;
        
        $method = '_' . $method;
        if(method_exists($this, $method)) 
        {
            call_user_func_array(array($this, $method), $arguments);
        }
        
        return TRUE;
    }
    
    
    /**
     * Инициализация или получение аттрибутов класса
     * 
     * @param array $attributes
     * @return type
     */
    public function attributes($attributes = null) 
    {
        if (is_null($attributes)) {
            return get_object_vars($this);
        }

        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }    
    
    
    
    
    protected function sendFromTemplate($template)
    {
        $prefix = ($this->is_emp)?'emp':'frl';
        $sufix = ($this->is_emp)?'frl':'emp';
        $template = sprintf($this->template_format,$template, $sufix);
        
        $this->recipient = $this->_formatFullname($this->order[($this->is_emp)?'freelancer':'employer'],true);
        $content = Template::render(
                RESERVES_TPL_MAIL_PATH . $template, 
                array(
                    'smail' => &$this,
                    'order' => $this->order,
                    'params' => $this->_addUrlParams($this->is_emp?'f':'e'),
                    $prefix . '_fullname' => $this->_formatFullname($this->order[($this->is_emp)?'employer':'freelancer'])
                )
        );       

        $message = Template::render(
                RESERVES_TPL_MAIL_PATH . RESERVES_TPL_BASE_LAYOUT, 
                array(
                    'content' => $content
                )
        ); 
        
        $this->message = nl2br($message);
        
        return array('ret' => $this->send('text/html'), 'message' => $message); 
    }
    
    
    
    
    
    /*
    public function _onPayed()
    {
        if(empty($this->order)) return FALSE;
     
        
        
        $result = $this->sendFromTemplate('payed');
        $ret = $result['ret'];
        
        
        
        
        
        $user = new employer();
        $user->GetUserByUID(intval($emp_id));
        if(!$user->uid) return FALSE;
        
        $this->recipient = $this->_formatFullname($order['employer'],true);
        $this->message = Template::render(
            RESERVES_TPL_MAIL_PATH . 'new_arbitrage.tpl.php', 
            array(
                'smail' => &$this, 
                'order' => $order,
                'params' => $this->_addUrlParams('e')
            )
        );

        $ret = $this->send('text/html');
        return $ret;
    }
    */
    
    
    
    
    /**
     * Уведомление о подозрительной сделке
     * 
     * @param type $orderObj
     * @return boolean
     */
    public function _frodPosible($orderObj)
    {
        global $NOTIFY_FROD_EMAILS;
        
        if (!$orderObj || !isset($NOTIFY_FROD_EMAILS)) {
           return false; 
        }
        
        $this->recipient = $NOTIFY_FROD_EMAILS;
        $order = $orderObj->getOrderData();
        
        $this->message = Template::render(
            RESERVES_TPL_MAIL_PATH . 'frod_possible.tpl.php', 
            array(
                'smail' => $this, 
                'order_id' => $order['id'],
                'emp' => htmlentities($this->_formatFullname($order['employer'], true), ENT_QUOTES, 'cp1251'),
                'frl' => htmlentities($this->_formatFullname($order['freelancer'], true), ENT_QUOTES, 'cp1251'),
                'num' => $orderObj->getReserve()->getNUM(),
                'date_reserve' => $orderObj->getReserve()->getReserveDataByKey('date_reserve'),
                'price' => $orderObj->getReserve()->getReservePrice(),
                'invoiceId' => $orderObj->getReserve()->getInvoiceId()
            )
        );

        return $this->send('text/html');
    }

   




   /**
     * Уведомить участников сделки об успешном резервировании средств.
     * 
     * @return boolean
     */
    public function _onReserveOrder()
    {
        if(empty($this->order)) return FALSE;

        $this->is_emp = true;
        $result_frl = $this->sendFromTemplate('reserve_order');
        $this->is_emp = false;
        $result_emp = $this->sendFromTemplate('reserve_order');
           
        $price_txt = tservices_helper::cost_format($this->order['reserve_data']['price'], false, false, false);
        ReservesSms::model($this->order['emp_id'])->sendByStatus(ReservesSms::STATUS_RESERVE_DONE_EMP, $price_txt, $this->order['id']);
        ReservesSms::model($this->order['frl_id'])->sendByStatus(ReservesSms::STATUS_RESERVE_DONE_FRL, $price_txt, $this->order['id']);
        
        return $result_frl['ret'] && $result_emp['ret'];
    }
    
   



    /**
     * Отправляем письма уведомления о создании арбитража заказчику или исполнителю.
     * 
     * @param array $order
     * @param bool $is_emp От кого поступила заявка
     * @return boolean
     */
    public function _onNewArbitrage($order, $is_emp = false)
    {
        if(empty($order)) return FALSE;
        
        $uid = $is_emp ? @$order['frl_id'] : @$order['emp_id'];
        //@todo: если тут входные данные по заказу то там должны быть данные юзеров
        $user = new users();
        $user->GetUserByUID((int) $uid);
        if(!$user->uid) return FALSE;
        
        $this->recipient = $this->_formatFullname(&$user,true);
        $this->message = Template::render(
            RESERVES_TPL_MAIL_PATH . 'new_arbitrage.tpl.php', 
            array(
                'smail' => &$this, 
                'order' => $order,
                'is_emp' => $is_emp,
                'params' => $this->_addUrlParams('e')
            )
        );

        $ret = $this->send('text/html');
        
        //Сообщение СМС
        $status = $is_emp ? ReservesSms::STATUS_NEW_ARBITRAGE_FRL : ReservesSms::STATUS_NEW_ARBITRAGE_EMP;
        ReservesSms::model($uid)->sendByStatus($status, $order['id']);
        
        return $ret;
    }
    
    /**
     * Отправляем письма уведомления об отмене арбитража заказчику и исполнителю.
     * 
     * @param array $order Заказ
     * @return boolean
     */
    protected function _onRemoveArbitrage($order) 
    {
        if(empty($order)) return FALSE;
        
        //Уведомления заказчику
        //@todo: если тут входные данные по заказу то там должны быть данные юзеров
        $user = new employer();
        $user->GetUserByUID((int)@$order['emp_id']);
        if(!$user->uid) return FALSE;
        
        $this->recipient = $this->_formatFullname(&$user,true);
        $this->message = Template::render(
            RESERVES_TPL_MAIL_PATH . 'cancel_arbitrage.tpl.php', 
            array(
                'smail' => &$this, 
                'order' => $order,
                'is_emp' => true,
                'params' => $this->_addUrlParams('e')
            )
        );
        $ret1 = $this->send('text/html');        
        ReservesSms::model($user->uid)->sendByStatus(ReservesSms::STATUS_CANCEL_ARBITRAGE_EMP, $order['id']);
        
        //Уведомления исполнителю
        $user = new freelancer();
        $user->GetUserByUID((int)@$order['frl_id']);
        if(!$user->uid) return FALSE;
        
        $this->recipient = $this->_formatFullname(&$user,true);
        $this->message = Template::render(
            RESERVES_TPL_MAIL_PATH . 'cancel_arbitrage.tpl.php', 
            array(
                'smail' => &$this, 
                'order' => $order,
                'is_emp' => false,
                'params' => $this->_addUrlParams('e')
            )
        );
        $ret2 = $this->send('text/html');        
        ReservesSms::model($user->uid)->sendByStatus(ReservesSms::STATUS_CANCEL_ARBITRAGE_FRL, $order['id']);
        
        return $ret1 && $ret2;
    }
    
    /**
     * Отправляем письма уведомления об отмене арбитража заказчику и исполнителю.
     * 
     * @param array $order Заказ
     * @return boolean
     */
    protected function _onApplyArbitrage($order, $price) {
        if(empty($order)) return FALSE;
        
        $priceBack = $order['reserve_data']['price'] - $price;
        
        //Уведомления заказчику
        $user = new employer();
        $user->GetUserByUID((int)@$order['emp_id']);
        if(!$user->uid) return FALSE;
        
        $this->recipient = $this->_formatFullname(&$user,true);
        $this->message = Template::render(
            RESERVES_TPL_MAIL_PATH . 'apply_arbitrage.tpl.php', 
            array(
                'smail' => &$this, 
                'order' => $order,
                'is_emp' => true,
                'pricePay' => $price,
                'priceBack' => $priceBack,
                'params' => $this->_addUrlParams('e')
            )
        );
        $ret1 = $this->send('text/html');        
        ReservesSms::model($user->uid)->sendByStatusAndPrice(ReservesSms::STATUS_APPLY_ARBITRAGE_EMP, $price, $priceBack, $order['id']);
        
        //Уведомления исполнителю
        $user = new freelancer();
        $user->GetUserByUID((int)@$order['frl_id']);
        if(!$user->uid) return FALSE;
        
        $this->recipient = $this->_formatFullname(&$user,true);
        $this->message = Template::render(
            RESERVES_TPL_MAIL_PATH . 'apply_arbitrage.tpl.php', 
            array(
                'smail' => &$this, 
                'order' => $order,
                'is_emp' => false,
                'pricePay' => $price,
                'priceBack' => $priceBack,
                'params' => $this->_addUrlParams('e')
            )
        );
        $ret2 = $this->send('text/html');        
        ReservesSms::model($user->uid)->sendByStatusAndPrice(ReservesSms::STATUS_APPLY_ARBITRAGE_FRL, $price, $priceBack, $order['id']);
        
        return $ret1 && $ret2;
    }

    /**
     * Форматтер имени юзера
     * @todo Не лучшее место для этого?
     * 
     * @param type $user
     * @param type $with_email
     * @return type
     */
    protected function _formatFullname(&$user, $with_email = false)
    {
        $u = (is_object($user))?array(
            'uname' => $user->uname,
            'usurname' => $user->usurname,
            'login' => $user->login,
            'email' => $user->email
            ):$user;
        
        $fullname = "{$u['uname']}";
        $fullname .= ((empty($fullname))?"":" ") . "{$u['usurname']}";
        $fullname .= (empty($fullname))?"{$u['login']}":" [{$u['login']}]";
        if($with_email) $fullname .= " <{$u['email']}>";
        return $fullname;
    }
    
}
