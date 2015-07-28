<?php
if(!defined("SMS_GATE_AUTH")) {
    define("SMS_GATE_AUTH", 'ht612091716:igiqvF9f');
}
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sms_gate.php");

/**
 * Класс для работы с СМС Шлюзом A1
 */
class sms_gate_a1 extends sms_gate
{
    /**
     * Адрес сервера
     * 
     * @var string
     */
    protected $_request_url = 'http://api.a1smsmarket.ru/send';
    
    protected $_password = '';
    
    protected $_user     = '';
    
    static public $enable_link_css  = 'b-layout__link_bordbot_dot_0f71c8';
    static public $disable_link_css = 'b-layout__link_bordbot_dot_80';
    
    private   $_limit_is_exceed = false;
    /**
     * Порт
     * 
     * @var integer 
     */
    protected $_request_port = 80;
    
    public function __construct($msisdn = false) {
        parent::__construct($msisdn);
        
        $msisdn = preg_replace('/[^0-9]/', '', $msisdn);

        list($this->_login, $this->_password) = explode(":", SMS_GATE_AUTH);
        
        // if($msisdn[0] == '7' && $msisdn[1] != '7') {// Код страны Россия, вторая 7 это Казахстан значит не Россия
        //    list($this->_login, $this->_password) = explode(":", SMS_GATE_AUTH);
        // } else {
        //    $this->_password = 'i7Sfr6G8';
        //    $this->_login    = 'ht612091728';
        // }
    }
    
    /**
     * Отправка сообщения абоненту
     * 
     * @param string  $message  Текст сообщения абоненту
     * @param integer $sms_id   Идентификатор сообщения в системе Пластик, на который отправляется ответ (если null то сообщение не ответное а новое)
     * @return boolean
     */
    public function sendSMS($message = "", $type= "text", $sms_id = null) {
        $message = $this->translit($message);
        $params = array(
            'operation' => 'send',
            'login'     => $this->_login,
            'password'  => $this->_password,
            'msisdn'    => $this->getCell(),
            'shortcode' => $this->getISNN(),
            'text'      => $message
        );
        //0024839 - не даем отправить смс на один и тот же номер более N раз в сутки
        $this->_limit_is_exceed = $this->limitSmsOnNumberIsExceed($params["msisdn"], $recId, $count, $message);
        if ( $this->_limit_is_exceed ) {
            return false;
        } else {
            $result = $this->_send($params);
	        $count++;
	        if ($result) {
	            $this->incrementSmsCounter($params["msisdn"], (bool)$recId, $recId);
	        }
	        $this->getSmsLimitMessage($count);
	        return $result;
        }
    }
    
    /**
     * Проверяем статус отправкис собщения
     * 
     * @param integer $id
     * @return string
     */
    public function checkStatus($id) {
        if(!$id) return;
        
        $params = array(
            'operation' => 'status',
            'login'     => $this->_login,
            'password'  => $this->_password,
            'id'        => $id
        );
        
        return $this->_send($params);
    }
    
     /**
     * Отправка пакета сообщения через СМС-Шлюз
     * 
     * @param type $params
     * @return integer
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
            $this->_log->writeln($out);
            $this->_log->writeln('Ошибка параметров для запроса');
            $this->_setError('Ошибка параметров для запроса');
            return false;
        }
        
        if (!SMS_GATE_DEBUG) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->_login . ":" . $this->_password);
            curl_setopt($ch, CURLOPT_URL, $this->_request_url . "?" . $build_query );
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
        if(!is_numeric($res) && $params['operation'] == 'send') { // Произошла ошибка
            $this->_http_code = 500;
            $this->_setError('Ошибка запроса');
            $this->_log->writeln("Error: {$res}");
        } else {
            $this->_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
        
        ob_start();
        var_dump($this->_request_url . "?" . $build_query);
        var_dump($params);
        var_dump(SMS_GATE_AUTH);
        var_dump($res);
        var_dump($this->_http_code);
        $out = ob_get_clean();
        $this->_log->writeln($this->_enc($out, true));
        
        return $res;
    }

    /**
    * @desc Проверка превышения лимита в SMS_ON_NUMBER_PER_24_HOURS SMS на номер $msisdn
    * Возвращает true если предел превышен
    * @param $msisdn - номер
    * @param int &$recordId - записывает номер записи или 0, в зависимости от существования записи в таблице sms_log
    * @param int &$count - сколько раз уже отправлено
    * @param string &$message - сообщение "Осталось N попыток"
    * @return bool
    **/
    public function limitSmsOnNumberIsExceed($msisdn, &$recordId, &$count, &$message) {
    	$msisdn = str_replace("+", "", $msisdn);
        $db = new DB("stat");
        $limit = sms_gate::SMS_ON_NUMBER_PER_24_HOURS;
        $row = $db->row("SELECT id, count, last_send, NOW() AS _now FROM sms_log WHERE msisdn = ? LIMIT 1", $msisdn);
        $recordId = intval($row["id"]);
        $count = intval($row["count"]);
        $message = $this->getSmsLimitMessage($count);
        if ($row["id"]) {
            $diff = strtotime($row["_now"]) - strtotime($row["last_send"]);
            if ($diff >= 24*3600) {
            	$count = 0;
                $message = $this->getSmsLimitMessage($count);
            }
            if ( $row["count"] >= $limit && $diff < 24*3600) {
                return true;
            }
        }
        return false;
    }

   /**
    * @desc Инкремент количества отправленных смс на номер $msisdn
    * @param $msisdn - номер
    * @param bool    $update использовать обновление или вставку
    * @param integer $recId = 0  номер записи для обновления
    * @return bool
    **/
    private function incrementSmsCounter($msisdn, $update, $recId = 0) {
        $msisdn = str_replace("+", "", $msisdn);
        $db = new DB("stat");
        $limit = sms_gate::SMS_ON_NUMBER_PER_24_HOURS;
        if ($update) {
            $query = "UPDATE sms_log SET count =
	                      CASE WHEN count < ? THEN count + 1 ELSE 1 END,
	                      last_send = NOW()
	                  WHERE id = ?";
            $db->query($query, $limit, $recId);
        } else {
            $db->insert("sms_log", array("msisdn"=>$msisdn));
        }
    }
    
    
    /**
    * @desc Возвращает сообщение об оставшемся количестве попыток отправки смс на номер
    * @param int &$count - количество уже отправленных на номер сообщений
    * @return string s
    **/
    public function getLimitMessage(&$count) {
        $count = $this->_count_sent_message;
        return $this->_limit_message;
    }
    
    /**
    * @desc Возвращает true если количество смс на номер превысило допустимый предел 
    * @return string s
    **/
    public function limitIsExceed() {
        return $this->_limit_is_exceed;
    }
    
    /**
    * @desc Возвращает сообщение "Осталось N попыток" 
    * @return string s
    **/
    public function getSmsLimitMessage($count) {
        $c = sms_gate::SMS_ON_NUMBER_PER_24_HOURS - $count;
        $s = '';
        switch ($c) {
            case 0:
                $s = sms_gate::LIMIT_EXCEED_LINK_TEXT;
                break;
            default:
                $s = "осталось {$c} " . ending($c, 'попытка', 'попытки', 'попыток');
        }
        $this->_limit_message = $s;
        $this->_count_sent_message = $count;
        return $s;
    }
}