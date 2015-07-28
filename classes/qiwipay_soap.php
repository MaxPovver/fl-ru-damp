<?php

require_once("qiwipay.php");

class getBillList {
    public $login; // string
    public $password; // string
    public $dateFrom; // string
    public $dateTo; // string
    public $status; // int
}

class getBillListResponse {
    public $txns; // string
    public $count; // int
}

class createBill {
    public $login; // string
    public $password; // string
    public $user; // string
    public $amount; // string
    public $comment; // string
    public $txn; // string
    public $lifetime; // string
    public $alarm; // int
    public $create; // boolean
}

class createBillResponse {
    public $createBillResult; // int
}

/**
 * ServerWSService class
 *  ласс дл€ работы с протоколом SOAP
 */
class ServerWSService extends SoapClient {
    
    public $uri = 'www.free-lance.ru/soap/qiwi.php'; // 
    
    private static $classmap = array(
                                   'getBillList' => 'getBillList',
                                   'getBillListResponse' => 'getBillListResponse',
                                   'createBill' => 'createBill',
                                   'createBillResponse' => 'createBillResponse',
                                   );
    

                                   
    public function __construct($wsdl = "IShopServerWS.wsdl", $options = array()) {
        $this->uri = HTTP_PREFIX.$this->uri;
        foreach(self::$classmap as $key => $value) {
            if(!isset($options['classmap'][$key])) {
                $options['classmap'][$key] = $value;
            }
        }
        parent::__construct($wsdl, $options);
    }

    /**
     *  
     *
     * @param getBillList $parameters
     * @return getBillListResponse
     */
    public function getBillList(getBillList $parameters) {
        return $this->__soapCall('getBillList', array($parameters),       array(
                'uri' => $this->uri,
                'soapaction' => ''
               )
        );
    }

    /**
     *  
     *
     * @param createBill $parameters
     * @return createBillResponse
     */
    public function createBill(createBill $parameters) {
        return $this->__soapCall('createBill', array($parameters),       array(
            'uri' => $this->uri,
            'soapaction' => ''
           )
        );
    } 
}

class qiwipay_soap extends qiwipay
{
    public $url  = 'https://ishop.qiwi.ru/services/ishop';
    public $wsdl = 'https://ishop.qiwi.ru/services/ishop?wsdl';
    public $client_wsdl = 'IShopClientWS.wsdl';
    
    function __construct($uid = NULL) {
        $this->service = new ServerWSService($this->wsdl, array('location' => $this->url, 'trace' => TRACE));
        parent::__construct($uid);
    }
    
    /**
     * ѕолучение списка счетов с указанием текущих статусов (максимальный период запроса счетов - 31 день). 
     *
     * @return unknown
     */
    function getBillList() {
        $params = new getBillList();
        $params->login    = $this->login; // логин
        $params->password = $this->passwd; // пароль
        $params->dateFrom = date('d.m.Y', (time()-86400*30));
        $params->dateTo   = date('d.m.Y');
        $params->status   = self::STATUS_COMPLETED;
        
        $result = $this->service->getBillList($params);   
        
        return $result;
    }
    
    /**
     * —оздание счета @see class qiwipay
     *
     * @param array $request параметры ($_POST).
     * @return unknown
     */
    function createBill($request) {
        if ( !$this->uid ) return 'ѕользователь не определен';
        
        $account = new account();
        $account->GetInfo( $this->uid, true );
        
        if ( $error = $this->validate($request, $account->id) ) return $error;
        
		$this->DB->start();
		
		$aData = array(
			'account_id' => $account->id,
			'phone'      => $this->form['phone'],
			'sum'        => $this->form['sum']
		);
		
		$id = $this->DB->insert("qiwi_account", $aData, "id");
		
		if ($id) {
		    $params = new createBill();
        	$params->login    = $this->login; // логин
        	$params->password = $this->passwd; // пароль
        	$params->user     = $this->form['phone']; // пользователь, которому выставл€етс€ счет
        	$params->amount   = $this->form['sum']; // сумма
        	$params->comment  = $this->form['comment']; // комментарий
        	$params->txn      = $id; // номер заказа
        	$params->lifetime = $this->ltime; // врем€ жизни (если пусто, используетс€ по умолчанию 30 дней)
		    $params->alarm    = $this->alarm_sms; 
        	
        	if($this->passwd=='debug') {
                $result = 1;
            } else {
                $result = $this->service->createBill($params)->createBillResult;
            }
            if($err = $this->_checkResultError($result)) {
                $error['qiwi'] = $err;
                $this->DB->rollback();
                die;
                return $error;
            }
            
            unset( $aData['sum'] );
            
            $sCode = substr( $aData['phone'], 0, 3 );
    		$sNum  = substr( $aData['phone'], 3 );
    		$sOper = $this->DB->val( 'SELECT COALESCE(operator_id, 0) FROM mobile_operator_codes 
                WHERE code = ? AND ? >= start_num AND ? <= end_num', 
                $sCode, $sNum, $sNum 
    		);
            
    		$aData['operator_id'] = $sOper;
    		
            $this->DB->insert( 'qiwi_phone', $aData );
            
        	$memBuff = new memBuff();
        	$nStamp  = time();
        	$sKey    = 'qiwiPhone' . $account->id . '_' . $aData['phone'];
        	
        	if ( !$aData = $memBuff->get($sKey) ) {
        		$aData = array( 'time' => $nStamp, 'cnt' => 0 );
        	}
        	
        	$aData['time'] = ( $aData['time'] + 3600 > $nStamp ) ? $aData['time']    : $nStamp;
        	$aData['cnt']  = ( $aData['time'] + 3600 > $nStamp ) ? $aData['cnt'] + 1 : 1;
        	
        	$memBuff->set( $sKey, $aData, 3600 );
        	//-----------------------------------
        }
        $this->DB->commit();
        $this->saveBillForm();
        return 0;
    }
    
    /**
     * ≈сли платежна€ система сообщает об ошибке, то возвращет текст ошибки
     *
     * @param string $result   ответ системы (объект)
     * @return string   пусто или текст ошибки.
     */
    function _checkResultError($rc) {
        return $this->_errors[(string)$rc];
    }
}   
?>