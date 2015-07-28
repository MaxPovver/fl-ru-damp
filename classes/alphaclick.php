<?php

require_once 'HTTP/Request2.php';


class AlphaClickFunctions {
    
    public function test() {
        //return 'Hello, ' . $in . '!';
        return 'Hello, World!';
    }
    
}


class AlphaClick {
    
    const WSCustomerAlfaClick_Location = 'http://testssl.alfabank.ru/CS/EQ/WSCustomerAlfaClick/WSCustomerAlfaClick10';
    const WSCustomerAlfaClick_URI      = 'http://WSCustomerAlfaClick10.EQ.CS.ws.alfabank.ru';
    const WSCustomerAlfaClick_Func     = 'WSCustomerAlfaClickCheck';
    
    //const WSInvoiceCreate_Location = 'http://testssl.alfabank.ru/IV_InvoiceCreateProcessWeb/sca/WSInvoiceCreate';
    const WSInvoiceCreate_Location = 'http://x13-net.ru/tunnel2.php';
    const WSInvoiceCreate_URI      = 'http://WSInvoiceCreate10.IV.AS.ws.alfabank.ru';
    const WSInvoiceCreate_Func     = 'WSInvoiceCreateStart';
    
    const INVOICE_TYPE = 157;
    const INVOICE_TTL = 259200; // 3 days
    const INVOICE_SHORT_DESC = 'Пополнение счета Free-Lance.ru';
    const INVOICE_FULL_DESC  = 'Пополнение счета пользователя %LOGIN% на сайте Free-Lance.ru';
    
    protected $_wsdlRoot = '';
    
    public $error = '';
    
    public function __construct($server=false) {
        if ( $server ) {
            $server = new SoapServer(null, array(
                'uri'          => 'http://x13-net.ru/tunnel.php',
                'soap_version' => SOAP_1_1
            ));
            $server->setClass('AlphaClickFunctions');
            $server->handle();
        }
    }
    
    protected function _soapClient($location, $uri) {
        return new SoapClient(null, array(
            'location'           => $location,
            'uri'                => $uri,
            'exceptions'         => true,
            'soap_version'       => SOAP_1_1,
            'connection_timeout' => 360,
            'style'              => SOAP_RPC,  
            'use'                => SOAP_LITERAL,
            'trace'              => true
        ));
    }
    
    protected function _error($error='') {
        if ( is_object($error) && ($error instanceof SoapFault) ) {
            $errnum = empty($error->detail->WSAppException->errorCode)? '': $error->detail->WSAppException->errorCode;
            echo "-{$errnum}-";
            switch ( $errnum ) {
                case 'DCA0100':
                    $this->error = 'Вы не зарегистрированы в системе «Альфа-Клик».1';
                    break;
                case 'F2-01':
                case 'F26-01': 
                    $this->error = 'Вы не зарегистрированы в системе «Альфа-Клик».2';
                    break;
                case 'F26-02':
                case 'F2-03':
                case 'F2-14':
                    $this->error = '«Альфа-Клик» не подключен или заблокирован для вашей учетой записи.';
                    break;
                case 'F2-05':
                case 'F2-02':
                case 'F2-04':
                case 'F2-07':
                case 'F2-08':
                case 'F2-09':
                case 'F2-10':
                case 'F2-11':
                case 'F2-12':
                case 'F2-13':
                    $this->error = 'Произошла ошибка при работе сервиса. Пожалуйста, попробуйте позднее.';
                    break;
                case 'F2-05':
                    $this->error = 'Неправильно указана сумма пополнения.';
                    break;
                case 'F2-06':
                    $this->error = 'Сумма пополнения превышает ваш доступный лимит в сервисе «Альфа-Клик».';
                    break;
                case 'F2-15':
                    $this->error = 'Вы превысили доступный лимит операций в сервисе «Альфа-Клик».';
                    break;
                default:
                    if ( empty($error->detail->WSAppException->errorString) ) {
                        $this->error = 'Произошла ошибка при подключении к сервису «Альфа-Клик». Пожалуйста, попробуйте позднее.';
                    } else {
                        $this->error = iconv('UTF-8', 'CP1251', $error->detail->WSAppException->errorString);
                    }
                    break;
            }
        } else if ( is_string($error) && $error != '' ) {
            $this->error = $error;
        } else {
            $this->error = 'Произошла ошибка при работе сервиса. Пожалуйста, попробуйте позднее.';
        }
        if ( $errnum ) {
            $this->error = $errnum . '. ' . $this->error;
        }
    }
    
    public function CustomerAlfaClickCheck($clientID) {
        $client = $this->_soapClient(self::WSCustomerAlfaClick_Location, self::WSCustomerAlfaClick_URI);
        $var1 = new stdclass;
        $var1->externalSystemCode = new SoapVar("GRCHK14", XSD_STRING);
        $var1->externalUserCode   = new SoapVar("GRCHK14", XSD_STRING);
        $param1 = new SoapParam($var1, "inCommonParms");
        $var2 = new stdclass;
        $var2->ID_client = new SoapVar($clientID, XSD_STRING);
        $param2 = new SoapParam($var2, 'inParms');
        try {
            $result = $client->__soapCall(self::WSCustomerAlfaClick_Func, array($param1, $param2));
            var_dump($result);
            if ( $result->Valid_client == 'Y' ) {
                return true;
            } else {
                $this->_error();
                return false;
            }
        } catch (SoapFault $fault) {
            $this->_error($fault);
            var_dump($fault);
        }
        return false;
    }
    
    
    public function InvoiceCreate($userID, $clientID, $amount) {
        global $DB;
        $user = new users;
        $user->GetUserByUID($userID);
        if ( empty($user->uid) || empty($clientID) || !is_string($clientID) || intval($amount) < 0 ) {
            $this->_error();
            return false;
        }
        $amount  = intval($amount);
        $endDate = date('Y-m-d', time() + self::INVOICE_TTL);
        $id = $DB->insert('alphaclick', array(
            'user_id'   => $userID,
            'amount'    => intval($amount),
            'client_id' => $clientID,
            'end_date'  => $endDate
        ), 'id');
        $shortDesc = str_replace(
            array('%LOGIN%', '%UNAME%', '%USURNAME%'), 
            array($user->login, $user->uname, $user->usurname), 
            self::INVOICE_SHORT_DESC
        );
        $fullDesc  = str_replace(
            array('%LOGIN%', '%UNAME%', '%USURNAME%'), 
            array($user->login, $user->uname, $user->usurname), 
            self::INVOICE_FULL_DESC
        );
        $client = $this->_soapClient(self::WSInvoiceCreate_Location, self::WSInvoiceCreate_URI);
        $var1 = new stdclass;
        $var1->externalSystemCode = new SoapVar("GRCHK14", XSD_STRING);
        $var1->externalUserCode   = new SoapVar("GRCHK14", XSD_STRING);
        $param1 = new SoapParam($var1, "inCommonParms");
        $var2 = new stdclass;
        $var2->ID_invtype   = new SoapVar(self::INVOICE_TYPE, XSD_INTEGER);
        $var2->ID_client    = new SoapVar($clientID, XSD_STRING);
        $var2->ID_statement = new SoapVar($id, XSD_STRING);
        $var2->Amount       = new SoapVar($amount, XSD_INTEGER);
        $var2->End_date     = new SoapVar($endDate . 'Z', XSD_STRING);
        $var2->Short_desc   = new SoapVar(iconv('CP1251', 'UTF-8', $shortDesc), XSD_STRING);
        $var2->Full_desc    = new SoapVar(iconv('CP1251', 'UTF-8', $fullDesc), XSD_STRING);
        $param2 = new SoapParam($var2, "inParms");
        try {
            $result = $client->__soapCall(self::WSInvoiceCreate_Func, array($param1, $param2));
            if ( empty($result->ID_invoice) ) {
                $this->_error();
            } else {
                $DB->update('alphaclick', array('invoice_id' => $result->ID_invoice), 'id = ?', $id);
                return $result->ID_invoice;
            }
        } catch (SoapFault $fault) {
            echo 'Request : <br/><xmp>', 
            $client->__getLastRequest(), 
            '</xmp><br/><br/> Error Message : <br/>', 
            $fault->getMessage(); 
            if ( !empty($fault->detail->WSAppException->errorCode) ) {
                $DB->update('alphaclick', array('error_num' => $fault->detail->WSAppException->errorCode), 'id = ?', $id);
            }
            $this->_error($fault);
        }
        echo $client->__getLastResponseHeaders();
        return 0;
    }
    
    
    public function income() {
        $server = new SoapServer(null, array('soap_version' => SOAP_1_1));
        $server->setClass('AlphaClick_Functions');
    }
    
    
    
    
    
    public function checkCustomer($name) {
        
        $requestConfig = array (
            'adapter'           => 'HTTP_Request2_Adapter_Curl',
            'connect_timeout'   => 20,
            'protocol_version'  => '1.1',
            'ssl_verify_peer'   => false,
            'ssl_verify_host'   => false,
            'ssl_cafile'        => null,
            'ssl_capath'        => null,
            'ssl_passphrase'    => null
        );
        $request = new HTTP_Request2('http://testssl.alfabank.ru/CS/EQ/WSCustomerAlfaClick/WSCustomerAlfaClick10', HTTP_Request2::METHOD_POST);
        $request->setConfig($requestConfig);
        $request->setHeader('Content-Type', 'text/xml; charset=utf-8');
        $request->setHeader('SoapAction', 'WSCustomerAlfaClick#Check');  // /CS/EQ/WSCustomerAlfaClick10#Get
        /*$request->setBody('
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:wsc="http://WSCustomerAlfaClick10.EQ.CS.ws.alfabank.ru">
   <soapenv:Header/>
   <soapenv:Body>
      <wsc:WSCustomerAlfaClickCheck>
         <inCommonParms>
            <externalSystemCode>GRCHK14</externalSystemCode>
            <externalUserCode>GRCHK14</externalUserCode>
            <inCommonParmsExt>
               <name/>
               <value/>
            </inCommonParmsExt>
         </inCommonParms>
         <inParms>
         <ID_client>2694835</ID_client></inParms>
      </wsc:WSCustomerAlfaClickCheck>
   </soapenv:Body>
</soapenv:Envelope>            
        ');*/
        $request->setBody('
            <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://WSCustomerAlfaClick10.EQ.CS.ws.alfabank.ru">
            <SOAP-ENV:Body><ns1:WSCustomerAlfaClickCheck>
                     <inCommonParms>
            <externalSystemCode>GRCHK14</externalSystemCode>
            <externalUserCode>GRCHK14</externalUserCode>
         </inCommonParms>
            <inParms><ID_client>2694834</ID_client></inParms></ns1:WSCustomerAlfaClickCheck>
            </SOAP-ENV:Body></SOAP-ENV:Envelope>');
        $result = $request->send();

        
        echo "<pre>"; print_r($result); echo "</pre>";
        
        return;
        
    }
    
    public function checkCustomer2($name) {
        
        $client = new SoapClient(null, 
        array( 
            'location' => 'http://testssl.alfabank.ru/CS/EQ/WSCustomerAlfaClick/WSCustomerAlfaClick10',
            //'uri' => 'http://WSCustomerAlfaClick10.EQ.CS.ws.alfabank.ru',
            "uri"      => "http://WSCustomerAlfaClick10.EQ.CS.ws.alfabank.ru",  
            "style"    => SOAP_RPC,  
            "use"      => SOAP_LITERAL  ,
            

            'trace' => 1, 
            'exceptions' => true, 
            'cache_wsdl' => WSDL_CACHE_NONE, 
            //'features' => SOAP_SINGLE_ELEMENT_ARRAYS, 
            'soap_version' => SOAP_1_1,
            //'typemap' => array("type_name" => "soap")
            /*
            // Auth credentials for the SOAP request. 
            'login' => 'username', 
            'password' => 'password', 

            // Proxy url. 
            'proxy_host' => 'example.com', // Do not add the schema here (http or https). It won't work. 
            'proxy_port' => 44300, 

            // Auth credentials for the proxy. 
            'proxy_login' => NULL, 
            'proxy_password' => NULL, 

             */
        ) );
        
        //$soap->WSCustomerAlfaClick10($name);
        $ID_client = new SoapVar("2694835", XSD_STRING); //array("ID_client", "2694835");
        //$inParms = array(new SoapParam($ID_client, "ID_client"));
        $wrapper->ID_client = $ID_client;
        $inParms = new SoapParam($wrapper, "inParms");
        
        $arr = array('inParms'=>$ID_client);
        
        
        $externalSystemCode = new SoapVar("GRCHK14", XSD_STRING);
        $externalUserCode = new SoapVar("GRCHK14", XSD_STRING);
        $wrapper2->externalSystemCode = $externalSystemCode;
        $wrapper2->externalUserCode = $externalUserCode;
        $inCommonParms = new SoapParam($wrapper2, "inCommonParms");
        
        
        
        //$inParms = array($inParms, "inParms");
        //var_dump($soap->WSCustomerAlfaClickCheck(new SoapParam($ID_client, 'ID_client')));
        try {
            var_dump($client->__soapCall('WSCustomerAlfaClickCheck', array($inCommonParms, $inParms)));
            //$soap->WSCustomerAlfaClickCheck($inParms);
        } catch(SoapFault $ex) { 
            echo 'Request : <br/><xmp>', 
            $ex->__getLastRequest(), 
            '</xmp><br/><br/> Error Message : <br/>', 
            $ex->getMessage(); 
            //var_dump($ex->faultcode, $ex->faultstring, $ex->faultactor, $ex->detail, $ex->_name, $ex->headerfault);
            //echo $ex->detail->WSAppException->errorCode . "<br />";
            //cho iconv('UTF-8', 'CP1251', $ex->detail->WSAppException->errorString . "<br />");
            
        }
        
        //echo "REQUEST:\n" . $client->__getLastRequest() . "\n";
            
    }
    
}