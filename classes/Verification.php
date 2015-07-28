<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/payment_keys.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/account.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/pskb.php';
require_once 'HTTP/Request2.php';

/**
 * Класс для верификации пользователей
 * 
 */
class Verification {
    /**
     * минимальный уровень аттестата webmoney необходимый для верификации
     * 
     */
    const WM_ATTESTAT_LEVEL = 120;
    /**
     * принимающий url для OAuth от FF.RU
     * 
     */
    const FF_REDIRECT_URI = 'https://www.free-lance.ru/income/ff.php';
    /**
     * код операции оплаты услуги по верификации через FF.RU
     * 
     */
    const FF_OP_CODE = 117;
    
    /**
     * Верификация через Яндекс.Деньги. URI авторизации
     */
    const YD_URI_AUTH = 'https://sp-money.yandex.ru';
    
    /**
     * Верификация через Яндекс.Деньги. URI API
     */
    const YD_URI_API = 'https://money.yandex.ru/api';
    
    /**
     * Верификация через Яндекс.Деньги. Идентификатор приложения
     */
    const YD_CLIENT_ID = '9297F3ADF2F2079458C8E61313433DC30DFAFB0C159BCE9326C8316E2562726D';
    
    /**
     * Верификация через Яндекс.Деньги. URI для передачи результата авторизации приложения
     */
    const YD_REDIRECT_URI = 'https://www.free-lance.ru/income/wm_verify.php';
    
    /**
     * Верификация через Яндекс.Деньги. Секретное слово для проверки подлинности приложения
     */
    const YD_CLIENT_SECRET = '7C2E413B2DD451DE61C5D9667A5BD0225A74A719488F39984BB884F88DD8A378075D65A55C029BBD6849AE603688D833172ADC36C44B133808BDDD791D9A6A72'; 

    /**
     * Верификация через OKPAY. URI API
     */
    const OKPAY_URI_API = 'https://api.okpay.com/OkPayAPI?wsdl';

    /**
     * Верификация через OKPAY. ID кошелька
     */
    const OKPAY_WALLETID = 'OK460571733';

    /**
     * Верификация через OKPAY. Секретное слово для проверки подлинности приложения
     */
    const OKPAY_CLIENT_SECRET = 'o8M5TtFk93Yme7RCa64Ayb2SK';
    
    /**
     * Сообщение об ошибке если пользователь не залогинен
     */
    const ERROR_NO_AUTH = 'Чтобы пройти верификацию вам нужно <a href="/login/">авторизоваться</a> или <a href="/registration/">зарегистрироваться</a>.';
    
    /**
     * Опкод услуги через ЯКассу по банковской карте
     */
    const YKASSA_AC_OP_CODE = 191;
    

    
    /**
     * Ссылка для WebMoney авторизации
     */
    const WMLOGIN_URL = 'https://login.wmtransfer.com/GateKeeper.aspx?RID=%s';
    


    
    const ERROR_DEFAULT = 'Произошла ошибка при верификации. Попробуйте ещё раз.';
    
    
    
    /**
     * Содержит тект ошибки в случае неудачи
     * 
     * @var string
     */
    public $error = '';
    /**
     * Этот массив необходимо заполнить данными перед вызвовом $this->verify(int)
     * 
     * @var array
     */
    public $data = array (
        'fio'         => '',  // Фамилия Имя Отчество
        'birthday'    => '',  // Дата рождения (формат YYYY-MM-DD)
        'idcard_name' => '',  // Название документа
        'idcard'      => '',  // Серия и номер документа
        'idcard_from' => '',  // Дата выдачи документа (формат YYYY-MM-DD)
        'idcard_to'   => '',  // Дата окончания действия документа (формат YYYY-MM-DD)
        'idcard_by'   => '',  // Орган, выдавший документ 
        'mob_phone'   => ''   // Номер мобильного телефона
    );
    
    
    /**
     * Верификация через FF.RU.
     * Шаг 1. Начало верификация и оплата
     * 
     * @param  integer  $uid   uid верифицируемого пользователя
     * @return boolean         успех
     */
    public function ffBegin($uid) {
        global $DB;
        $user    = new users;
        $account = new account;
        $billId  = NULL;
        $user->GetUserByUID($uid);
        if ( empty($user->uid) ) {
            $this->error = 'Вы не авторизованы';
            return false;
        }
        if ( $user->is_verify == 't' ) {
            $this->error = 'Вы уже верифицированы';
            return false;
        }
        $prev = $DB->val("SELECT result FROM verify_ff WHERE user_id = ? ORDER BY req_time DESC LIMIT 1", $uid);
        if ( empty($prev) || $prev == 't' ) {
//            if ( $user->is_pro != 't' ) {
//                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/op_codes.php");
//                $op_codes = new op_codes();
//                $sum = round($op_codes->GetField(self::FF_OP_CODE, $err, "sum"), 2);
//                $ac_sum = round( (float)$_SESSION["ac_sum"], 2);
//                if ( $sum > $ac_sum ) {
//                    $this->error = "Недостаточно средств на счету.";
//                    return false;
//                }
//            }
            $DB->insert('verify_ff', array('user_id'=>$uid, 'is_pro'=>$user->is_pro, 'bill_id'=>$billId));
        }
        return true;
    }
    
    
    /**
     * Верификация через FF.RU.
     * Шаг 2. Получения кода авторизация и общение с ff.ru
     * 
     * @global type $DB
     * @param type $uid
     * @param type $code
     * @return boolean
     */
    public function ffCommit($uid, $code) {
        global $DB;
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
        $user = new users;
        $user->GetUserByUID($uid);
        if ( empty($user->uid) ) {
            $this->error = 'Вы не авторизованы';
            return false;
        }
        $prev = $DB->row("SELECT * FROM verify_ff WHERE user_id = ? ORDER BY req_time DESC LIMIT 1", $uid);
        if ( $prev['result'] != 'f' ) {
            $this->error = 'Вам необходимо использовать такую же учетную запись с который вы начинали верификацию.';
            return false;
        }
        // Для тестирования на бете/альфе
        if(is_release()) { 
            // меняем код авторизации на токен
            $request = new HTTP_Request2('https://ff.ru/oauth/token', HTTP_Request2::METHOD_POST);
            $request->setConfig($requestConfig);
            $request->setHeader('Content-Type', 'application/x-www-form-urlencoded');
            $request->addPostParameter('client_id', FF_CLIENT_ID);
            $request->addPostParameter('client_secret', md5(FF_CLIENT_ID . FF_CLIENT_SECRET));
            $request->addPostParameter('grant_type', 'authorization_code');
            $request->addPostParameter('code', $code);
            $request->addPostParameter('redirect_uri', self::FF_REDIRECT_URI);
            $resp = $request->send();
            //var_dump($resp); // del
            $body = json_decode(iconv('UTF-8', 'CP1251', $resp->getBody()));
            if ( $resp->getStatus() == 200 ) {
                // меняем токен на паспортные данные
                $request = new HTTP_Request2('https://ff.ru/oauth/userinfo', HTTP_Request2::METHOD_GET);
                $request->setConfig($requestConfig);
                $request->setHeader('Authorization', 'Bearer ' . $body->access_token);
                $url = $request->getUrl();
                $url->setQueryVariable('scope', 'passport question account video');
                $resp = $request->send();
                $body = json_decode($resp->getBody());
                $DB->query("UPDATE verify_ff SET body = ? WHERE id = ?", $resp->getBody(), $prev['id']);
                if ( $resp->getStatus() == 200 ) {
                    if ( empty($body->passport_sn) ) {
                        $this->error = 'Необходимо подтвердить личность в личном кабинете сайта FF.RU.';
                        return false;
                    }
                    $fio = $body->last_name . ' ' . $body->first_name . ' ' . $body->patronimic;
                    $this->data = array(
                        'fio'         => iconv('UTF-8', 'CP1251', htmlentities($fio, ENT_QUOTES, "UTF-8")),
                        'birthday'    => dateFormat('Y-m-d', (string) $body->birth_date),
                        'idcard_name' => 'Паспорт',
                        'idcard'      => $body->passport_sn,
                        'idcard_from' => dateFormat('Y-m-d', (string) $body->passport_date),
                        'idcard_to'   => NULL,
                        'idcard_by'   => iconv('UTF-8', 'CP1251', htmlentities($body->passport_issuer, ENT_QUOTES, "UTF-8")),
                        'mob_phone'   => '+7' . $body->cellular
                    );
                    //var_dump($this->data);
                } else {
                    if ( empty($body->error) ) {
                        $this->error = 'Ошибка при получении данных с FF.RU.';
                    } else {
                        $this->error = 'Ошибка при получении данных с FF.RU (' . $body->error . ' / ' . $body->error_description . '). ';
                    }
                    $this->error .= $resp->getStatus() . '.';
                    return false;
                }
            } else {
                if ( empty($body->error) ) {
                    $this->error = 'Ошибка при подключении к сервису FF.RU.';
                } else {
                    $this->error = 'Ошибка при подключении к сервису FF.RU (' . $body->error . ' / ' . $body->error_description . '). ';
                }
                $this->error .= $resp->getStatus() . '.';
                return false;
            }
        } else {
            $this->data = array(
                'fio'         => 'Фамилия Имя Отчество',
                'birthday'    => dateFormat('Y-m-d', (string) '1950-01-01'),
                'idcard_name' => 'Паспорт',
                'idcard'      => '1900 100001',
                'idcard_from' => dateFormat('Y-m-d', (string) '2000-01-01'),
                'idcard_to'   => NULL,
                'idcard_by'   => 'УВД г. Города',
                'mob_phone'   => '+79' . rand(100000000, 900000000)
            );
        }
        $this->is_pro = true;
        if ( $user->is_pro != 't' && empty($prev['bill_id']) ) {
                //переносим сюда списание средств
                $account = new account;
//                $billId  = NULL;
//                $transactionId = $account->start_transaction($uid);
//                $description   = 'Верификация через сервис FF.RU';
//                $buyResult     = $account->Buy($billId, $transactionId, self::FF_OP_CODE, $uid, $description, $description, 1, 0);
//                if ( $buyResult ) {
//                    $this->error .= $buyResult;
//                    return false;
//                }
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
                $bill      = new billing($uid);
                $bill->setOptions(array('prev' => $prev, 'data' => $this->data));
                $create_id = $bill->create(self::FF_OP_CODE);
                $this->is_pro = false;
                if(!$create_id) {
                    $this->error .= 'Ошибка создания услуги';
                    return false;
                } else {
                    return true;
                    //header("Location: /bill/orders/");
                    exit;
                }
        }
        $DB->query("UPDATE verify_ff SET is_pro = ?, bill_id = ?  WHERE id = ?", $user->is_pro, $billId, $prev['id']);
        if ( $this->verify($uid) ) {
            $DB->query("UPDATE verify_ff SET result = TRUE WHERE id = ?", $prev['id']);
            //$account->commit_transaction($transactionId, $uid);
            return true;
        }
            
    }
    
    
    /**
     * Возвращает статус верификации пользователя через FF.ru
     * 
     * @param  integer  $uid  uid пользователя
     * @return boolean|int    FALSE - не пробовал верифицироваться, 0 - пробовал, но еще не закончил, 1 - верифицирован
     */
    public function ffStatus($uid) {
        global $DB;
        $row = $DB->row("SELECT * FROM verify_ff WHERE user_id = ? ORDER BY req_time DESC LIMIT 1", $uid);
        if ( empty($row) ) {
            return FALSE;
        } else if ( $row['result'] == 't' ) {
            return 1;
        }
        return 0;
    }
    
    
    
    
    /**
     * Проверка авторизация и верификация при помощи WebMoney
     * 
     * @global type $DB
     * @param type $uid
     * @return boolean
     */
    public function webmoney($uid)
    {
        global $DB;

        require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/WMXI/WMXILogin.php');
        
        $siteHolder = defined('WM_VERIFY_AUTHCHECK_WMID')? 
                WM_VERIFY_AUTHCHECK_WMID : 
                WM_VERIFY_WMID;
                
        $wmxi = new WMXILogin(
                WM_VERIFY_URL_UD, 
                $siteHolder, 
                realpath(ABS_PATH . '/classes/WMXI/WMXI.crt'));
  
        if ($wmid = $wmxi->AuthorizeWMID()) {
            if ($res = $this->webmoneyCheckWMID($wmid, $uid)) {
                if ($this->verify($uid)) {
                    
                    $ret = $DB->insert('verify_webmoney', array(
                        'user_id' => $uid, 
                        'wmid' => $wmid, 
                        'log' => $res->asXML(),
                        'result' => true
                    ));
                    
                    return $ret;
                }
            }
        }
        
        if (empty($this->error)) {
            $this->error = 'Произошла ошибка во время верификации. Попробуйте ещё раз.';
        }
        
        return false;
    }

    
    
    /**
     * Проверка WMID:
     * - Проверка ввода WMID
     * - Верифицирован ли уже переданный WMID
     * - Проверка аттестата у WMID
     * 
     * @global type $DB
     * @param type $wmid
     * @param type $uid
     * @return boolean
     */
    public function webmoneyCheckWMID($wmid, $uid)
    {
        global $DB;
        
        //Проверка ввода WMID
        if (!preg_match('/^[0-9]{12}$/', $wmid)) {
            $this->error = 'Неправильно указан WMID.';
            
            return false;
        }

        
        //Верифицирован ли уже переданный WMID
        $ret = $DB->val("
            SELECT 1 
            FROM verify_webmoney 
            WHERE wmid = ? AND user_id <> ?i AND result", 
            $wmid, $uid);
        
        if ($ret) {
            $this->error = 'Данный WMID уже используется для верификации другим пользователем сайта.';
            
            return false;
        }
        
        require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/WMXI/WMXI.php');
        
        //Проверка аттестата у WMID
        $wmxi = new WMXI;
        $key  = array( 'file' => WM_VERIFY_KEYFILE, 'pass' => WM_VERIFY_KEYPASS );
        $wmxi->Classic(WM_VERIFY_WMID, $key);
        $res = $wmxi->X11($wmid, 0, 1, 0);
        $res = $res->toObject();
        $retval = (int)$res['retval'];
        
        if ($retval > 0) {
            $this->error = 'Произошла ошибка при проверке аттестата. Попробуйте ещё раз.';
            
            return false;
        }
        
        $tid = (int) $res->certinfo->attestat->row['tid'];
        
        if ($tid < self::WM_ATTESTAT_LEVEL) {
            $this->error = 'Требуется аттестат не ниже начального. Получите <a class="b-layout__link underline" href="https://wiki.webmoney.ru/projects/webmoney/wiki/Аттестаты" target="_blank">начальный аттестат</a> или выберите другой способ верификации.';
            
            return false;
        }
        
        return $res;        
    }


    
    /**
     * Вернуть ссылку на авторизацию через WebMoney
     * 
     * @return type
     */
    public function getWMLoginUrl()
    {
        return sprintf(self::WMLOGIN_URL, WM_VERIFY_URL_UD);
    }


    
    /**
     * Вернуть ошибки
     * 
     * @return type
     */
    public function getError()
    {
        return $this->error;
    }




    
    
    /**
     * Инициализация верификации через Яндекс.Деньги
     * 
     * @param  int $uid UID пользователя
     * @return bool true - успех, false - провал
     */
    public function ydBegin( $uid ) {
        $user    = new users;
        $user->GetUserByUID($uid);
        
        if ( empty($user->uid) ) {
            $this->error = self::ERROR_NO_AUTH;
            return false;
        }
        
        if ( $user->is_verify == 't' ) {
            $this->error = 'Вы уже верифицированы';
            return false;
        }
        
        $prev = $GLOBALS['DB']->val( 'SELECT result FROM verify_yd WHERE user_id = ? ORDER BY req_time DESC LIMIT 1', $uid );
        
        if ( empty($prev) || $prev == 't' ) {
            $sIsEmp = is_emp() ? 't' : 'f';
            
            $GLOBALS['DB']->insert( 'verify_yd', array('user_id' => $uid, 'is_emp' => $sIsEmp) );
        }
        
        return true;
    }


    /**
     * Проверяет персональные данные пользователя, необходимые для верификации через Яндекс.Деньги
     * Используется также в self::pskb()
     * 
     * @param  int $uid UID пользователя
     * @return bool true - успех, false - провал
     */
    public function ydCheckUserReqvs( $uid = 0 ) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php';
        
        $this->error = '';
        $nError      = 0; // код ошибки см $aError в этой функции ниже. 0 - нет ошибок
        $aFields     = array(
            array( 'fio', 'birthday' ),                                  // ФИО и дата рождения
            array( 'idcard_name', 'idcard', 'idcard_from', 'idcard_by' ) // паспортные данные
        );
        
        return empty( $nError );
    }
    
    /**
     * Возвращает URI авторизации для верификации через Яндекс.Деньги
     * 
     * @param  string $scope
     * @return string
     */
    public function ydAuthorizeUri( $scope = null ) {
        if ( empty($scope) ) {
            $scope = 'account-info operation-history';
        }
        
        $scope = trim( strtolower($scope) );
        
        $res = self::YD_URI_AUTH . '/oauth/authorize?client_id='. self::YD_CLIENT_ID .'&response_type=code&scope=' 
            . urlencode($scope) . "&redirect_uri=" . urlencode(self::YD_REDIRECT_URI);
        
        return $res;
    }

    /**
     * Верификация через кредитные карты
     * 
     * @param  int $uid UID пользователя
     * @param  string $card номер карты
     * @return bool true - успех, false - провал
     */
    public function card( $uid, $card ) {
        global $DB;
        
        $DB->query("INSERT INTO verify_card(user_id, card, result, req_time) VALUES(?i, ?, TRUE, NOW())", get_uid(false), $card);
        $DB->query("UPDATE users SET is_verify='t' WHERE uid=?i", get_uid(false));
        $antiuid = $DB->val("SELECT anti_uid FROM users WHERE uid=?i", get_uid(false));
        if($antiuid) { $DB->query("UPDATE users SET is_verify='t' WHERE uid=?i", $antiuid); }
        $_SESSION['verifyStatus'] = array( 'status' => 1 );
        $_SESSION['is_verify']    = 't';
    }
    
    /**
     * Верификация через кредитные карты (через ЯКассу)
     * 
     * @param  int $uid UID пользователя
     * @return bool true - успех, false - провал
     */
    public function cardYK($uid) {
        global $DB;

        $DB->query("INSERT INTO verify_card(user_id, card, result, req_time) VALUES(?i, ?, TRUE, NOW())", $uid, "yandex.kassa");
        $DB->query("UPDATE users SET is_verify='t' WHERE uid=?i", $uid);
        $antiuid = $DB->val("SELECT anti_uid FROM users WHERE uid=?i", $uid);
        if($antiuid) { 
            $DB->query("UPDATE users SET is_verify='t' WHERE uid=?i", $antiuid); 
        }
        return true;
    }

    /**
     * Верификация через Яндекс.Деньги
     * 
     * @param  int $uid UID пользователя
     * @param  string $is_emp является ли пользователь работодателем: 't' или 'f'
     * @param  string $code временный токен, полученный в ответ на Запрос авторизации в Яндекс.Деньги
     * @return bool true - успех, false - провал
     */
    public function ydVerification( $uid = null, $is_emp = 'f', $code = '', $fname='', $lname='' ) {
        $prev = $GLOBALS['DB']->row("SELECT * FROM verify_yd WHERE user_id = ? ORDER BY req_time DESC LIMIT 1", $uid);
        
        
        $nError        = 0;  // код ошибки см $aError в этой функции ниже. 0 - нет ошибок
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
        
        // меняем код авторизации на токен
        $request = new HTTP_Request2( self::YD_URI_AUTH . '/oauth/token', HTTP_Request2::METHOD_POST );
        $request->setConfig( $requestConfig );
        $request->setHeader( 'Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8' );
        $request->setHeader( 'Expect', '' );
        $request->addPostParameter( 'code', $code );
        $request->addPostParameter( 'client_id', self::YD_CLIENT_ID );
        $request->addPostParameter( 'grant_type', 'authorization_code' );
        $request->addPostParameter( 'redirect_uri', self::YD_REDIRECT_URI );
        $request->addPostParameter( 'client_secret', self::YD_CLIENT_SECRET );
        
        $resp = $request->send();
        $body = json_decode( iconv('UTF-8', 'CP1251', $resp->getBody()) );
        
        $GLOBALS['DB']->query( 'UPDATE verify_yd SET log = ? WHERE id = ?', $resp->getBody(), $prev['id'] );
        
        if ( $resp->getStatus() == 200 ) {
            // получаем информацию о состоянии счета пользователя
            $request = new HTTP_Request2( self::YD_URI_API . '/account-info', HTTP_Request2::METHOD_POST );
            $request->setConfig( $requestConfig );
            $request->setHeader( 'Authorization', 'Bearer ' . $body->access_token );
            $request->setHeader( 'Expect', '' );
            
            $resp = $request->send();
            $body = json_decode($resp->getBody());
            
            $GLOBALS['DB']->query( 'UPDATE verify_yd SET log = ? WHERE id = ?', $resp->getBody(), $prev['id'] );
            
            if ( $resp->getStatus() == 200 ) {
                $bTestServer = ( (defined('SERVER') && SERVER != 'release') || (defined('IS_LOCAL') && IS_LOCAL === TRUE) );
                
                if ( $bTestServer || $body->identified ) {
                    $aVerifyYd = $GLOBALS['DB']->rows( 'SELECT is_emp FROM verify_yd WHERE account = ? AND result = true', $body->account );
                    
                    if ( count($aVerifyYd) > 1 ) {
                        $nError = 3;
                    }
                    elseif ( count($aVerifyYd) && $aVerifyYd[0]['is_emp'] == $is_emp ) {
                        $nError = 4;
                    }
                    
                    if ( !$nError ) {
                        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php';

                        $aFields    = array( 'fio', 'birthday', 'idcard_name', 'idcard', 'idcard_from', 'idcard_by', 'mob_phone' );
                        $this->data = array();

                        if ( empty($this->aUserReqvs) ) {
                            $this->aUserReqvs = sbr_meta::getUserReqvs($uid);
                        }

                        if ( is_array($this->aUserReqvs) && $this->aUserReqvs ) {
                            foreach ( $aFields as $sField ) {
                                $this->data[$sField] = $this->aUserReqvs[1][$sField];
                            }

                            $this->data['el_yd'] = $body->account;

                            if ( $this->verify($uid) ) {
                                $GLOBALS['DB']->update(
                                    'verify_yd', 
                                    array( 'account' => $body->account, 'result' => true ),
                                    'id = ?', $prev['id']
                                );
                            }
                            else {
                                return false; // сообщение об ошибках из $this->verify($uid)
                            }
                        }
                        else {
                            $nError = 1;
                        }
                    }
                }
                else {
                    $nError = 2;
                }
            }
            else {
                $nError = 1;
            }
        }
        else {
            $nError = 1;
        }
        
        if ( $nError ) {
            $aError = array(
                1 => 'Произошла ошибка во время верификации.',
                2 => 'Для верификации у вас должен быть идентифицирован кошелек.',
                3 => 'Данный кошелек уже был использован при верификации кем-то из пользователей.', // два аккаунта: и фрилансер и работодатель
                4 => 'Данный кошелек уже был использован при верификации кем-то из пользователей.' // один аккаунт с той же ролью
            );
            
            $this->error = $aError[$nError];
        }
        
        return empty( $nError );
    }

    /**
     * Верификация через OKPAY. 
     * 
     * @param  integer $uid  uid пользователя
     * @return boolean       результат операции
     */
    public function okpay($uid) {
        global $DB;
        if ( empty($uid) ) {
            $this->error = 'Вы не авторизованы.';
            return false;
        }

        $logId = $DB->insert('verify_okpay', array('user_id'=>$uid), 'id');

        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php';
        $this->aUserReqvs = sbr_meta::getUserReqvs($uid);

        if ( empty($this->aUserReqvs[1]['mob_phone']) || $this->aUserReqvs['is_activate_mob'] == 'f' ) {
            $this->error = 'Для верификации у вас должен быть активирован номер телефона в <a href="/users/'. $_SESSION['login'] .'/setup/main/">основных настройках</a> аккаунта.';
            return false;
        }

        $is_verify = false;
        $phone = str_replace("+", "", $this->aUserReqvs[1]['mob_phone']);

        $sql = "SELECT COUNT(user_id) FROM sbr_reqv WHERE _1_mob_phone=?";
        $foundPhones = $DB->val($sql, "+".$phone);
        if($foundPhones>1) {
            $this->error = 'Данный номер телефона уже был использован при верификации кем-то из пользователей.';
            return false;
        }


        $datePart = gmdate("Ymd");
        $timePart = gmdate("H");
        $authString = self::OKPAY_CLIENT_SECRET.":".$datePart.":".$timePart;
        $secToken = hash('SHA256', $authString);
        $secToken = strtoupper($secToken);

        try {
            $client = new SoapClient(self::OKPAY_URI_API);
        } catch (Exception $e) {
            header('Location: /promo/verification/?service=okpay&error=1');
            exit;
        }
        $obj = new stdClass();
        $obj->WalletID = self::OKPAY_WALLETID;
        $obj->SecurityToken = $secToken;
        $obj->Account = $phone;

        $webService = $client->Account_Check($obj);
        $res = $webService->Account_CheckResult;
        $DB->update('verify_okpay', array('phone'=>$phone, 'log'=>$res), "id = ?", $logId);
        if($res) {
            $is_verify = true;
        } else {
            $this->error = 'Для верификации у вас должен быть верифицированный кошелек.';
            return false;

        }

        if ( $is_verify ) {
            $DB->update('verify_okpay', array('result'=>true), "id = ?", $logId);
            $DB->query("UPDATE users SET is_verify = TRUE WHERE uid = ?", $uid);
            return true;
        }


        return false;
    }    
    
    /**
     * Верификация через веб-кошелек ПСКБ. 
     * Требуется заполненность ФИО, пасспорта и активированного номера телефона
     * 
     * @param  integer $uid  uid пользователя
     * @return boolean       результат операции
     */
    public function pskb($uid) {
        global $DB;
        if ( empty($uid) ) {
            $this->error = 'Вы не авторизованы.';
            return false;
        }
        $logId = $DB->insert('verify_pskb', array('user_id'=>$uid), 'id');
        // используем проверку на заполненость полей от яндекса, она подходит
        if ( empty($this->aUserReqvs) ) {
            if ( !$this->ydCheckUserReqvs($uid) ) {
                return false;
            }
        }
        $phone = $this->aUserReqvs[1]['mob_phone'];
        $pskb  = new pskb;
        $res   = $pskb->checkOrCreateWallet($phone);
        $DB->update('verify_pskb', array('phone'=>$phone, 'log'=>$res), "id = ?", $logId);
        if ( empty($res) ) {
            $this->error = 'Ошибка соединения с Веб-кошельком.';
            return false;
        }
        $res = json_decode(iconv('cp1251', 'utf8', $res), 1);
        if ( empty($res['state']) || !in_array($res['state'], array('EXIST', 'COMPLETE')) ) {
            $this->error = 'Веб-кошелек не создан.';
            return false;
        }
        if ( !$res['verified'] ) {
            $this->error = 'Для верификации у вас должен быть идентифицированный кошелек.';
            return false;
        }
        $aFields    = array( 'fio', 'birthday', 'idcard_name', 'idcard', 'idcard_from', 'idcard_by', 'mob_phone' );
        $this->data = array();
        foreach ( $aFields as $sField ) {
            $this->data[$sField] = $this->aUserReqvs[1][$sField];
        }
        if ( $this->verify($uid) ) {
            $DB->update('verify_pskb', array('result'=>true), "id = ?", $logId);
            return true;
        }
        return false;
    }
    
    
    /**
     * Общий метод верификации. После обработки данных и заполнения $this->data через методы webmoney или ff 
     * они должны вызвать этот метод, чтобы сохранить данные и верифицировать пользователя на нашем сайте
     * 
     * @param  integer  $uid - uid верифицируемого пользователя
     * @return boolean       - успех
     */
    public function verify($uid) 
    {
        global $DB;
        $user = new users;
        $user->GetUserByUID($uid);
        if ( empty($user->uid) ) {
            $this->error = 'Вы не авторизованы';
            return false;
        }
        if ( $user->is_verify == 't' ) {
            $this->error = 'Вы уже верифицированы';
            return false;
        }
        $DB->hold()->query("UPDATE users SET is_verify = TRUE WHERE uid = ?", $user->uid);
        $antiuid = $DB->val("SELECT anti_uid FROM users WHERE uid=?i", $user->uid);
        if($antiuid) { $DB->hold()->query("UPDATE users SET is_verify='t' WHERE uid=?i", $antiuid); }

        if ( !$DB->query() ) {
            //@todo: такие ошибки в UI не нужны
            //$this->error = 'Системная ошибка.';
            return false;
        }
        
        if (isset($_SESSION['uid']) && 
            $_SESSION['uid'] == $uid) {
            
            $_SESSION['is_verify'] = 't';
        }
        
        return true;
    }
    
    
    /**
     * Количество пользователей, прошедших верификацию
     * 
     * @return integer  кол-во верифицированных пользователей
     */
    public function verifyCount() {
        return (int) $GLOBALS['DB']->cache(3600)->val("
            WITH verifys as (
                        SELECT COUNT(v.user_id) as cnt FROM verify_ff v 
                        WHERE v.result = true
                        UNION 
                        SELECT COUNT(v.user_id) as cnt FROM verify_webmoney v
                        WHERE v.result = true 
                        UNION 
                        SELECT COUNT(v.user_id) as cnt FROM verify_yd v
                        WHERE v.result = true 
                        UNION 
                        SELECT COUNT(v.user_id) as cnt FROM verify_pskb v
                        WHERE v.result = true 
                        UNION 
                        SELECT COUNT(v.user_id) as cnt FROM verify_okpay v
                        WHERE v.result = true 
                        UNION 
                        SELECT COUNT(v.user_id) as cnt FROM verify_card v
                        WHERE v.result = true                  
                    )
            SELECT SUM(cnt)
            FROM verifys 
        ");
    }
    
    
    /**
     * Возвращает время когда пользователь верифицировался
     * 
     * @param  integer  $user_id  uid пользователя
     * @return string             время верификации в формате постгриса
     */
    static public function verifyLast($user_id) {
        global $DB;
        return $DB->value("
            SELECT
                *
            FROM (
                SELECT req_time FROM verify_webmoney WHERE user_id = ? AND result
                UNION ALL
                SELECT req_time FROM verify_ff WHERE user_id = ? AND result
                UNION ALL
                SELECT req_time FROM verify_yd WHERE user_id = ? AND result
                UNION ALL
                SELECT req_time FROM verify_pskb WHERE user_id = ? AND result
                UNION ALL
                SELECT req_time FROM verify_okpay WHERE user_id = ? AND result
            ) v
            ORDER BY
                req_time DESC
            LIMIT
                1
        ", $user_id, $user_id, $user_id, $user_id);
    }
    
    /**
     * Возвращает статистику по верификации пользователей
     * 
     * @global type $DB 
     * @param string  $fromDate    Начальный промежуток даты выборки
     * @param string  $toDate      Конечный промежуток даты выборки
     * @param string  $type        Тип выборки
     * @param boolean $is_verify  Прошли верификацию или нет
     * @param boolean $role       true - Фрилансер, false - Исполнитель
     * @return array
     */
    static public function getStatVerify($fromDate, $toDate, $type = 'wm', $is_verify = false, $role = null) {
        global $DB;
        
        $inner = "";
        if($role !== null) {
            $tbl   = $role ? "freelancer":"employer";
            $inner = "INNER JOIN {$tbl} u ON u.uid = v.user_id";
        }
        
        
        switch($type) {
            case 'wm':
                $sql = "SELECT COUNT(v.*) as cnt FROM verify_webmoney v 
                        {$inner}
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = ?";
                break;
            case 'ffpro':
                $sql = "SELECT COUNT(v.*) as cnt FROM verify_ff v
                        {$inner}
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.is_pro = true AND v.result = ?";
                break;
            case 'ffnopro':
                $sql = "SELECT COUNT(v.*) as cnt FROM verify_ff v
                        {$inner}
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.is_pro = false AND v.result = ?";
                break;
            case 'yd':
                $sql = "SELECT COUNT(v.*) as cnt FROM verify_yd v
                        {$inner}
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = ?";
                break;
            case 'pskb':
                $sql = "SELECT COUNT(v.*) as cnt FROM verify_pskb v
                        {$inner}
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = ?";
                break;
            case 'okpay':
                $sql = "SELECT COUNT(v.*) as cnt FROM verify_okpay v
                        {$inner}
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = ?";
                break;
            case 'country':
                $sql = "
                    WITH verifys as (
                        SELECT v.user_id FROM verify_ff v 
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = true
                        UNION 
                        SELECT v.user_id FROM verify_webmoney v
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = true 
                        UNION 
                        SELECT v.user_id FROM verify_yd v
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = true 
                        UNION 
                        SELECT v.user_id FROM verify_pskb v
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = true 
                    )
                    SELECT COUNT(*) as cnt, c.country_name FROM  verifys v
                    INNER JOIN users u ON u.uid = v.user_id
                    INNER JOIN country c ON c.id = u.country
                    GROUP BY c.country_name
                    ORDER by cnt DESC
                    LIMIT 10";
                return $DB->rows($sql, $fromDate, $toDate, $fromDate, $toDate, $fromDate, $toDate, $fromDate, $toDate);
                break;
            case 'ff_country':
                $sql = "
                    WITH verifys as (
                        SELECT v.user_id, v.is_pro FROM verify_ff v
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = ?
                    ), country_pro AS (
                        SELECT COUNT(*) as cnt, c.country_name, true::boolean as is_pro FROM verifys v
                        INNER JOIN users u ON u.uid = v.user_id
                        INNER JOIN country c ON c.id = u.country
                        WHERE v.is_pro = true
                        GROUP BY c.country_name
                        LIMIT 10
                    ), country_notpro AS (
                        SELECT COUNT(*) as cnt, c.country_name, false::boolean as is_pro FROM verifys v
                        INNER JOIN users u ON u.uid = v.user_id
                        INNER JOIN country c ON c.id = u.country
                        WHERE v.is_pro = false
                        GROUP BY c.country_name
                        LIMIT 10
                    )
                    SELECT * FROM country_pro
                    UNION
                    SELECT * FROM country_notpro
                    ORDER by cnt DESC";
                return $DB->rows($sql, $fromDate, $toDate, $is_verify);
                break;
            case 'yd_country':
                $sql = "
                    WITH verifys as (
                        SELECT v.user_id FROM verify_yd v
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = ?
                    )
                    SELECT COUNT(*) as cnt, c.country_name FROM  verifys v
                    INNER JOIN users u ON u.uid = v.user_id
                    INNER JOIN country c ON c.id = u.country
                    GROUP BY c.country_name
                    ORDER by cnt DESC
                    LIMIT 10";
                return $DB->rows($sql, $fromDate, $toDate, $is_verify);
                break;
            case 'wm_country':
                $sql = "
                    WITH verifys as (
                        SELECT v.user_id FROM verify_webmoney v
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = ? 
                    )
                    SELECT COUNT(*) as cnt, c.country_name FROM  verifys v
                    INNER JOIN users u ON u.uid = v.user_id
                    INNER JOIN country c ON c.id = u.country
                    GROUP BY c.country_name
                    ORDER by cnt DESC
                    LIMIT 10";
                return $DB->rows($sql, $fromDate, $toDate, $is_verify);
                break;
            case 'pskb_country':
                $sql = "
                    WITH verifys as (
                        SELECT v.user_id FROM verify_pskb v
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = ? 
                    )
                    SELECT COUNT(*) as cnt, c.country_name FROM  verifys v
                    INNER JOIN users u ON u.uid = v.user_id
                    INNER JOIN country c ON c.id = u.country
                    GROUP BY c.country_name
                    ORDER by cnt DESC
                    LIMIT 10";
                return $DB->rows($sql, $fromDate, $toDate, $is_verify);
                break;
            case 'okpay_country':
                $sql = "
                    WITH verifys as (
                        SELECT v.user_id FROM verify_okpay v
                        WHERE v.req_time::date >= ? AND v.req_time::date <= ? AND v.result = ? 
                    )
                    SELECT COUNT(*) as cnt, c.country_name FROM  verifys v
                    INNER JOIN users u ON u.uid = v.user_id
                    INNER JOIN country c ON c.id = u.country
                    GROUP BY c.country_name
                    ORDER by cnt DESC
                    LIMIT 10";
                return $DB->rows($sql, $fromDate, $toDate, $is_verify);
                break;
        }
        
        return $DB->row($sql, $fromDate, $toDate, $is_verify);
    }
    /**
    * @desc Уменьшить количество счетчика авторизаций
    * @param int $uid
    **/
    static public function decrementStat($uid) {
        $uid = intval($uid);
        global $DB;
        $query = "SELECT ff_0.id AS n, pskb.id AS pskb_id, wm.id AS wm_id, yd.id AS yd_id, ff.id AS ff_id, okpay.id as okpay_id  
                FROM verify_ff AS ff_0 
                LEFT JOIN verify_pskb AS pskb ON pskb.user_id = {$uid}
                LEFT JOIN verify_webmoney AS wm ON wm.user_id = {$uid}
                LEFT JOIN verify_yd       AS yd ON yd.user_id = {$uid}
                LEFT JOIN verify_ff       AS ff ON ff.user_id = {$uid}
                LEFT JOIN verify_okpay       AS okpay ON okpay.user_id = {$uid}
                WHERE ff_0.user_id = {$uid}
                OR     pskb.user_id = {$uid}
                OR    wm.user_id = {$uid}
                OR    yd.user_id = {$uid}
                OR    ff.user_id = {$uid}
                OR    okpay.user_id = {$uid}";
        //так как на бете много тестовых аккаунтов, в которых есть статистика о верификации одного и того же пользователя через разные системы
        // и такая ситуация возможна в принципе и на бое
        // удаляю все идентификаторы этого пользователя из таблиц verify_*  
        $data = $DB->rows($query);
        $pskb_ids = array();
        $wm_ids   = array();
        $yd_ids   = array();
        $ff_ids   = array();
        $okpay_ids   = array();
        foreach ($data as $row) {
            if ( intval($row["pskb_id"]) ) {
                $pskb_ids[intval($row["pskb_id"])] = 1;
            }
            if ( intval($row["wm_id"]) ) {
                $wm_ids[intval($row["wm_id"])] = 1;
            }
            if ( intval($row["yd_id"]) ) {
                $yd_ids[intval($row["yd_id"])] = 1;
            }
            if ( intval($row["ff_id"]) ) {
                $ff_ids[intval($row["ff_id"])] = 1;
            }
            if ( intval($row["okpay_id"]) ) {
                $okpay_ids[intval($row["okpay_id"])] = 1;
            }
        }
        if ( count( array_keys($pskb_ids) ) ) {
            $ids = join(",", array_keys($pskb_ids));
            $query = "DELETE FROM verify_pskb WHERE id IN ({$ids}) AND result = 't'";
            $DB->query($query);
        }
        if ( count( array_keys($wm_ids) ) ) {
            $ids = join(",", array_keys($wm_ids));
            $query = "DELETE FROM verify_webmoney WHERE id IN ({$ids}) AND result = 't'";
            $DB->query($query);
        }
        if ( count( array_keys($yd_ids) ) ) {
            $ids = join(",", array_keys($yd_ids));
            $query = "DELETE FROM verify_yd WHERE id IN ({$ids}) AND result = 't'";
            $DB->query($query);
        }
        if ( count( array_keys($ff_ids) ) ) {
            $ids = join(",", array_keys($ff_ids));
            $query = "DELETE FROM verify_ff WHERE id IN ({$ids}) AND result = 't'";
            $DB->query($query);
        }
        if ( count( array_keys($okpay_ids) ) ) {
            $ids = join(",", array_keys($okpay_ids));
            $query = "DELETE FROM verify_okpay WHERE id IN ({$ids}) AND result = 't'";
            $DB->query($query);
        }
    }
    
    
    
    public static function getYDUriAuth($project_id = null)
    {
        return sprintf("%s/oauth/authorize?client_id=%s&response_type=code&scope=%s&redirect_uri=%s", 
                self::YD_URI_AUTH,
                self::YD_CLIENT_ID, 
                urlencode('account-info'),
                urlencode(self::YD_REDIRECT_URI . ($project_id ? "?type=project&id={$project_id}" : "?type=promo")));
    }
    
}