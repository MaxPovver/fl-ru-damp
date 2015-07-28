<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");

/**
 * Режим отладки для беты
 */
if(!defined("SMS_GATE_DEBUG")) {
    define("SMS_GATE_DEBUG", false);
}

/**
 * Данные авторизации
 */
if(!defined("SMS_GATE_AUTH")) {
    define("SMS_GATE_AUTH", 'freelance:w83457hhn');
}

/**
 * Класс для работы с СМС Шлюзом
 */
class sms_gate
{
    /**
     * Не может быть отправлено
     */
    const STATUS_REJECTED = 'REJECTED';
    
    /**
     * Передано оператору
     */
    const STATUS_SUBMIT_ACKNOWLEGED = 'SUBMIT ACKNOWLEGED';
    
    /**
     * Доставлено абоненту
     */
    const STATUS_DELIVERED = 'DELIVERED';
    
    /**
     * Не может быть доставлено абоненту
     */
    const STATUS_UNDELIVERED = 'UNDELIVERED';
    
    /**
     * Короткий номер с которого отправляется сообщение
     */
    const ISNN = 'Free-lance';
    
    /**
     * Номер в цифрах, c которого отправляются сообщения
     */
    const ISNN_NUMERIC = 79010101000;
    
    /**
     * Таймаут перед следующей отправкой сообщения
     */
    const TIMEOUT_SEND = '1 min';
    
    /**
     * Длинна кода подтверждения
     */
    const LENGTH_CODE = 4;
    
    /**
     * Сколько смс можно отправить в сутки на один номер (0024839)
     */
    const SMS_ON_NUMBER_PER_24_HOURS = 50;
    
    /**
     * Сообщение о том, что отправлять смс на номер больше нельзя (0024839)
     */
    const LIMIT_EXCEED_LINK_TEXT = "К сожалению, суточный лимит SMS с кодом исчерпан";
    /**
     * Адрес сервера для ответов абонентам
     * 
     * @var string
     */
    protected $_request_url = 'http://81.177.1.226';
    
    /**
     * Порт
     * @var integer 
     */
    protected $_request_port = 2780;
    
    /**
     * Подключени к базе данных
     * @var DB
     */
    protected $_db;
    
    /**
     * Текст последней ошибки 
     * 
     * @var string 
     */
    protected $_error;
    
    /**
     * Класс для работы с логами
     * 
     * @var Log
     */
    protected $_log;
    
    /**
     * Код ответа сервера (200,400,401,404,500)
     * 
     * @var integer 
     */
    protected $_http_code;
    
    /**
     * Мобильный телефон абонента
     * 
     * @var integer 
     */
    protected $_msisdn;
    
    /**
     * Сообщение об оставшемся количестве сообщений
     * @var string 
     */
    protected  $_limit_message;
    
    /**
     * Количество уже отправленных на номер сообщений
     * @var int 
     */
    protected  $_count_sent_message;

    /**
     * Тип сообщения (активация телефона)
     */
    const TYPE_ACTIVATE = 1;
    
    /**
     * Тип сообщения (вход в финансы)
     */
    const TYPE_AUTH     = 2;
    
    /**
     * Тип сообщения (востановление пароля)
     */
    const TYPE_PASS     = 3;

    /**
     * Тип сообщения (закрытие сделки)
     */
    const TYPE_CLOSE_SBR = 4;
    
    
    /**
     * Тексты сообщений исходя из типа сообщения
     * %s -- заменяется на код(пароль)
     * 
     * @var array
     */
    public $text_messages = array(
        self::TYPE_ACTIVATE  => 'Подтвердите, что это ваш телефон: введите код на FL.ru - %s',
        self::TYPE_AUTH      => 'Для входа на страницу «Финансы» введите код на Free-lance.ru - %s',
        self::TYPE_PASS      => 'Восстановление доступа к аккаунту на FL.ru. Логин -LOGIN-, новый пароль %s',
        self::TYPE_CLOSE_SBR => 'Завершение этапа сделки на сайте FL.ru. Код подтверждения: %s'
    );
    
    /**
     * Инициализируем БД и класс лога
     */
    public function __construct($msisdn = false) {
        if (SMS_GATE_DEBUG) {
            $_host = !defined('IS_LOCAL') ? str_replace('http://', 'https://', $GLOBALS['host']) : $GLOBALS['host'];
            $this->_request_url = $_host . '/sms/sms_gate_server.php';
        }
        
        if($msisdn) $this->setCell($msisdn);
        
        $this->_db  = new DB('master');
        $this->_log = new Log("sms_gate/".SERVER.'-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
    }
    
    /**
     * Задает номер абонента
     * 
     * @param integer $msisdn
     */
    public function setCell($msisdn) {
        $this->_msisdn = str_replace('+', '', $msisdn);
    }
    
    /**
     * Номер абонента
     * 
     * @return integer
     */
    public function getCell() {
        return $this->_msisdn;
    }
    
    /**
     * Перекодировка символов
     * 
     * @param string  $str         Перекодировка символов
     * @param boolean $revert      Обратная кодировка
     * @return string
     */
    protected function _enc($str, $revert = false) {
        return ( $revert ? iconv('utf8', 'cp1251', $str) : iconv('cp1251', 'utf8', $str) );
    }
    
    /**
     * Генерация уникального кода для проверки номера телефона
     * 
     * @param integer $length  Длинна кода
     * @return integer
     */
    public function generateCode($length = self::LENGTH_CODE) {
        $min = intval('1' . str_repeat('0', $length-1));
        $max = intval(str_repeat('9', $length));
        $this->setAuthCode(rand($min, $max));
        return $this->getAuthCode();
    }
    
    /**
     * Задаем код подвтерждения телефона
     * 
     * @param string|integer $code
     */
    public function setAuthCode($code) {
        $this->_code = $code;
    }
    
    /**
     * Возвращает код подтверждения телефона
     * @return integer
     */
    public function getAuthCode() {
        return $this->_code;
    }
    
    /**
     * Через какой промежуток времени можно будет отослать следующее сообщение
     * 
     * @param type $date        Дата
     * @param type $msisdn      Телефон абонента
     * @return type
     */
    public function nextTimeSend($date = false) {
        if(!$date) {
            $sql = "SELECT date_send FROM sms_gate WHERE msisdn = ? ORDER by date_send DESC LIMIT 1";
            $date = $this->_db->val($sql, $this->getCell());
            if(!$date) return false;
        }
        $this->next_time_send =  strtotime($date . ' + ' . self::TIMEOUT_SEND);
        return $this->next_time_send;
    }
    
    /**
     * Информация по отправке сообщения на соответствующий номер
     * 
     * @return array
     */
    public function getInfoSend() {
        return $this->_db->row("SELECT id, data, dlr_status, date_send, is_auth FROM sms_gate WHERE msisdn = ? AND user_id = ? ORDER by date_send DESC", $this->getCell(), $_SESSION['uid']);
    }
    
    /**
     * Проверяем можно ли отправить сообщение
     * 
     * @param string $date
     * @return boolean
     */
    public function isNextSend($date = false) {
        return (time() < $this->nextTimeSend($date));
    }
    
    /**
     * Возвращаем код ответа
     * @return type
     */
    public function getHTTPCode() {
        return $this->_http_code;
    }
    
    public function getTextMessage($type, $code) {
        return sprintf($this->text_messages[$type], $code);
        //return iconv("cp1251", "utf-8", sprintf($this->text_messages[$type], $code));
    }
    /**
     * Отправка кода для активации номера телефона
     * 
     * @return boolean
     */
    public function sendAuthCellCode($type = sms_gate::TYPE_ACTIVATE) {
        $info  = $this->getInfoSend();
        
        if($this->isNextSend($info['date_send'])) {
            return false;
        }
        $code    = $this->generateCode();    
        $text    = $this->getTextMessage($type, $code);
        $sms_id  = intval($this->sendSMS($text));
            
        if($this->_http_code == 200) {
            $data = array(
                'sms_id'     => $sms_id,
                'msisdn'     => $this->getCell(),
                'isnn'       => $this->getISNN(),
                'type'       => 'text',
                'data'       => $code,
                'user_id'    => $_SESSION['uid'],
                'dlr_status' => SMS_GATE_DEBUG ? self::STATUS_DELIVERED : null
            );
            
            if($info['id']> 0) {
                $data['date_send'] = 'NOW()';
                $this->_db->update('sms_gate', $data, "id = {$info['id']}");
            } else {
                $this->_db->insert('sms_gate', $data);
            }
            
            return $sms_id;
        }
        
        return false;
    }
    
    /**
     * Обновляем флаг, активации телефона
     * 
     * @param integer $id      Ид записи
     * @param boolean $auth    флаг активации 
     * @return boolean
     */
    public function setIsAuth($id, $auth = false) {
        if(!$id) return false;
        return $this->_db->update('sms_gate', array('is_auth' => $auth), "id = {$id}");
    }
    
    /**
     * Отправка сообщения абоненту
     * 
     * @param string  $message  Текст сообщения абоненту
     * @param string  $type     Тип ответного сообщения: text – текстовое сообщение, push – wap-push сообщение в формате <название>;<ссылка>
     * @param integer $sms_id   Идентификатор сообщения в системе Пластик, на который отправляется ответ (если null то сообщение не ответное а новое)
     * @return boolean
     */
    public function sendSMS($message = "", $type= "text", $sms_id = null) {
        $message = $this->translit($message);
        $params = array(
            'sms_id'  => $sms_id,
            'msisdn'  => $this->getCell(),
            'isnn'    => $this->getISNN(),
            'type'    => $type,
            'data'    => $message
        );
        
        return $this->_send($params);
    }
    
    /**
     * Отправка пакета сообщения через СМС-Шлюз
     * 
     * @param type $params
     * @return boolean
     */
    protected function _send($params) {
        $ch = curl_init();
        
        if (is_array($params)) {
            foreach ($params as $k => $v) {
                if($v === null) continue;
                $params[$k] = $this->_enc($v);
            }
            $build_query = http_build_query($params);
        } else {
            ob_start();
            var_dump($params);
            $out = ob_get_clean();
            $this->_log->writeln($this->_enc($out, true));
            $this->_log->writeln('Ошибка параметров для запроса');
            $this->_setError('Ошибка параметров для запроса');
            return false;
        }
        
        if (!SMS_GATE_DEBUG) {
            curl_setopt($ch, CURLOPT_USERPWD, SMS_GATE_AUTH);
            curl_setopt($ch, CURLOPT_URL, ( is_release() ? $this->_request_url : "localhost" ) . "?" . $build_query );
            if($this->_request_port) curl_setopt($ch, CURLOPT_PORT, $this->_request_port);
        } else {
            if(defined('BASIC_AUTH')) {
                curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
            }
            curl_setopt($ch, CURLOPT_URL, $this->_request_url . "?" . $build_query);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $res = curl_exec($ch);
        $this->_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        ob_start();
        var_dump($this->_request_url . "?" . $build_query);
        var_dump($params);
        var_dump($res);
        var_dump($this->_http_code);
        $out = ob_get_clean();
        $this->_log->writeln($this->_enc($out, true));
        
        return $res;
    }
    
    /**
     * Фиксируем ошибку
     * 
     * @param type $msg Сообщение ошибки
     */
    protected function _setError($msg) {
        $this->_error = $msg;
    }
    
    /**
     * Возвращает ошибку
     * 
     * @return string
     */
    public function getError() {
        return $this->_error;
    }
    /**
     * Сохраняем данные об подтвержденном при регистрации пользователя телефонном номере
     * @param string $phone номер телефона абонента
     * @param string $isnn  короткий номер, на который поступил запрос
     * @param string $data  текст сообщения 
     * @param string $date_send  время отправки сообщения 
     * @param string $uid   идентификатор пользователя из users 
     */
    static public function saveSmsInfo($phone, $isnn, $data, $date_send, $uid) {
        global $DB;
        if ( strtolower( mb_detect_encoding($data, array("Windows-1251") ) )== "windows-1251") {
            $data = mb_convert_encoding($data, "UTF-8", "Windows-1251");
        }
        $DB->insert("sms_gate", array("msisdn"=>$phone, "isnn"=>$isn, "type"=>"text", "data"=>$data, "dlr_status" => "DELIVERED", "date_send" => $date_send, "user_id" => $uid, "is_auth" =>true ));
        $DB->insert("sbr_reqv", array("_1_mob_phone"=>$phone, "_2_mob_phone"=>$phone, "user_id"=>$uid, "is_activate_mob"=>true ));
    }
     /**
     * Есть ли такой подтвержденный номерв базе данных?
     * @param  string $phone номер телефона абонента
     * @return bool   true если есть  и false если нет 
     */
    public function phoneIsExistsAndVerify($phone) {
        global $DB;
        $phone = preg_replace("#[\D]#", '', $phone);
        if ( strlen($phone) ) {
            $val = $DB->val("SELECT id FROM sms_gate WHERE is_auth = TRUE AND msisdn = '{$phone}' LIMIT 1");
            if ($val) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * проверяет номер телефона
     * если это неРусский номер, то делается транслитерация
     * транслитерирует только символы
     * @return string транслитерированная строка
     */
    function translit ($str) {
        $tr = array(
            "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D",
            "Е"=>"E","Ё"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
            "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
            "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
            "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
            "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
            "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
            "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ё"=>"e",
            "ж"=>"j","з"=>"z","и"=>"i","й"=>"y","к"=>"k",
            "л"=>"l","м"=>"m","н"=>"n","о"=>"o","п"=>"p",
            "р"=>"r","с"=>"s","т"=>"t","у"=>"u","ф"=>"f",
            "х"=>"h","ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch",
            "ъ"=>"y","ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu",
            "я"=>"ya",
        );

        $cell = $this->getCell();
        // для неРоссии делаем транслитерацию сообщения
        if ($cell{0} != 7) {
            $str = strtr($str, $tr);
        }

        return $str;
    }
    
    /**
     * Определяем какой ISNN будем посылать
     * 
     * @return string|integer
     */
    public function getISNN() {
        switch(true) {
            // Код Азербайджана (Оператор Azercell)
            case ( strpos((string)$this->getCell(), '99451') === 0 ):
            case ( strpos((string)$this->getCell(), '99450') === 0 ):
            case ( strpos((string)$this->getCell(), '90') === 0 ): //#0024762
            case ( strpos((string)$this->getCell(), '373') === 0 ): // Молдавия
                $isnn = self::ISNN_NUMERIC;
                break;
            default:
                $isnn = self::ISNN;
                break;
        }
        return $isnn;
    }
}

/**
 * Класс для обработки входящих сообщений от партнера СМС-Шлюза
 */
class sms_gate_listener
{
    /**
     * Подключени к базе данных
     * @var DB
     */
    private $_db;
    
    /**
     * Класс для работы с логами
     * 
     * @var Log
     */
    private $_log;
    
    /**
     * Инициализируем БД и Лог
     */
    public function __construct() {
        $this->_db  = new DB('master');
        $this->_log = new Log("sms_gate/listener-".SERVER.'-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
    }
    
    /**
     * Обрабатываем взодящие запросы
     * 
     * @param array  $request     Данные запроса
     * @param string $path        Адрес где идет обработка
     */
    public function listener($request, $path) {
        $this->_request = $request;
        
        ob_start();
        var_dump($this->_request);
        $out = ob_get_clean();
        $this->_log->writeln($out);
        
        switch($path) {
            case 'sms': // Адрес для сообщений от абонентов
                $this->_SMSListener();
                break;
            case 'dlr': // Адрес для отчетов о доставке
                $this->_DLRListener();
                break;
            default:
                $this->_log->writeln('HTTP/1.0 400 Bad Request');
                header('HTTP/1.0 400 Bad Request');
                break;
        }
        exit;
    }
    
    /**
     * Обрабатываем сообщение от абонента 
     * @todo на данный момент в данной функции мы не нужнадаемся
     * 
     * @return type
     */
    protected function _SMSListener() {
        return;
    }
    
    /**
     * Обработка отчетов о доставке
     * 
     * @return boolean
     */
    protected function _DLRListener() {
        $this->_log->writeln('DLRListener');
        
        $sms_id = __paramValue('integer', $this->_request['sms_id']);
        $status = __paramValue('string', $this->_request['dlr_status']);
        if($sms_id <= 0) {
            $this->_log->writeln('HTTP/1.0 400 Bad Request');
            header('HTTP/1.0 400 Bad Request');
            return;
        }
        
        $update = array(
            'dlr_status' => $status
        );
        
        $ok = $this->_db->update('sms_gate', $update, "sms_id = {$sms_id}");
        
        if($ok) {
            $this->_log->writeln('HTTP/1.0 200 OK');
            header('HTTP/1.0 200 OK');
            return true;
        }
        $this->_log->writeln('HTTP/1.0 400 Bad Request');
        header('HTTP/1.0 400 Bad Request');
        return false;
    }
}

/**
 * Класс для эмуляции сервера партнера СМС-Шлюза
 */
class sms_gate_server
{
    /**
     * Адрес сервера для отчетов нам
     */
    protected $_request_url;
    
    /**
     * Подключени к базе данных
     * @var DB
     */
    private $_db;
    
    /**
     * Инициализируем БД
     */
    public function __construct() {
        if (SMS_GATE_DEBUG) {
            $_host = !defined('IS_LOCAL') ? str_replace('http://', 'https://', $GLOBALS['host']) : $GLOBALS['host'];
            $this->_request_url = $_host;
        }
        $this->_db  = new DB('master');
    }
    
    /**
     * Прослушиваем входящие запросы
     * В настоящий момент входящий запрос один 
     * 
     * @param type $request
     */
    public function listener($request) {
        if($request['msisdn'] == '') {
            header('HTTP/1.0 400 Bad Request');
            exit;
        }
        
        $insert = array(
            'msisdn'     => __paramValue('string', $request['msisdn']),
            'data'       => __paramValue('string', $request['data']),
            'dlr_status' => sms_gate::STATUS_DELIVERED
        );
        $sms_id = $this->_db->insert('sms_gate_server', $insert, 'id');
        
        echo $sms_id;
        
//        $params = array(
//            'sms_id'     => $sms_id,
//            'dlr_status' => $insert['dlr_status']
//        );
//        $this->report($params, 'dlr');
    }
    
    /**
     * Рассылка отчетов
     * 
     * @param array  $request Данные запроса
     * @param string $type    Тип запроса (@see sms_gate_listener::listener())
     * @return integer Код ответа сервера
     */
    public function report($request, $type = 'sms') {
        $ch = curl_init();
        
        foreach ($request as $k => $v) {
            $request[$k] = iconv('cp1251', 'utf8', $v);
        }
        $build_query = http_build_query($request);
        
        if(defined('BASIC_AUTH')) {
            curl_setopt($ch, CURLOPT_USERPWD, BASIC_AUTH);
        }
        curl_setopt($ch, CURLOPT_URL, $this->_request_url . "/" . $type . "/?" . $build_query);
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $res = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        return $http_code;
    }
}

?>