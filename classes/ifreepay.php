<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_answers.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/sms_services.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php";
/**
 *
 * Оплата с помощью SMS через сервис IFree.
 *
 */
class ifreepay extends account
{
    
	/**
	 * Личный секретный ключ для проверки того, что данные пришли от ifree
	 *
	 */
	const SECRETKEY      = IFREE_KEY;
	/**
	 * Личный секретный ключ для дебага
	 *
	 */
    const DEBUGSECRETKEY = IFREE_DEBUG_KEY;
	/**
	 * ID системы оплаты через SMS
	 *
	 */
	const PAYMENT_SYS    = 7;

	
    /**
	 * Полученные от ifree данные
	 *
	 * @var string
	 */
	private $_request;
    /**
     * Код оплачиваемого сервиса
     *
     * @var integer
     */
    private $_type;
    /**
     * Пользователь который произвел платеж
     *
     * @var object
     */
    private $_user;
    /**
     * Код платежа текущей операции из op_codes
     *
     * @var integer
     */
    private $_opcode;
    /**
	 * Префикс, который указывает пользователь в SMS для оплаты наших сервисов
	 *
	 * @var string
	 */
	private $_smsPrefix = 'free';
	/**
	 * Индекс массива - код оплачиваемого сервиса, который указывает пользователь в SMS
	 * Значение - код оплаты в таблице op_codes
	 *
	 * @var array
	 */
	private $_opcodes = array(1=>12, 2=>71); // Отрубили покупку ответов на проекты: 3=>62
	/**
	 * SMS текст присланный пользователем.
	 *
	 * @var string
	 */
	private $_smsDecoded = '';
	/**
	 * Данные о тарифе за оплачиваемый сервис.
	 * @see sms_services::tariffs
	 *
	 * @var array
	 */
	private $_tariff = array();
	/**
	 * Прошли ли проверку данные от ifree
	 *
	 * @var boolean
	 */
	private $_isValidated = false;

 /**
  * ИД незавершенной операции. Используется для блокировки
  * 
  * @var string 
  */
 private $_oplock;

    /**
	 * Конструктор. Выполняет все необходимые операцации по проверке пришедших данных и оплате сервисов.
	 * 
	 * @param   string   $request          пришедший от ifree запрос
	 * @param   boolean  $validate         проверить пришедшие данные?
	 * @param   boolean  $processRequest   произвести оплату, если данные прошли проверку?
	 */
	function __construct($request, $validate = false, $processRequest = false)
    {
        $this->_request = $request;
        if($validate)
            $this->validate();
        if($processRequest)
            $this->processRequest();
    }


    /**
	 * Проверяет на корректность запрос от ifree и заполняет свойства пришедшими от него данными.
	 * В случае ошибки работа скрипта завершается.
	 */
	public function validate()
    {
        // отключено #0019358
        $this->_errorif(true, false, 'Сервис недоступен.');
        
        if(isset($this->_request['test']))
            $this->_response( !trim($this->_request['test']) ? 'OK' : $this->_request['test']);
            
        $this->_errorif(!$this->_request['evtId'], 'Неверный запрос.');

        $add_value = "";
        if($this->_request['retry'])
            $add_value = $this->_request['retry'];
        if($this->_request['debug'])
            $add_value .= $this->_request['debug'].self::DEBUGSECRETKEY;
        
        $valid = md5($this->_request['serviceNumber'].$this->_request['smsText'].$this->_request['country'].$this->_request['abonentId'].self::SECRETKEY.$this->_request['now'].$add_value);
        $this->_errorif(strcasecmp($valid, $this->_request['md5key']) != 0, "Неверный запрос.", "Несовпадение md5key.");

        $this->_smsDecoded = base64_decode($this->_request['smsText']);

        list($pfx, $this->_type, $login) = preg_split('/[\s+]+/', $this->_smsDecoded);

        $this->_errorif(strtolower($pfx) != $this->_smsPrefix, "Неверный формат запроса.");
        $this->_errorif(!$this->_type, "Не указан тип услуги.");
        $this->_errorif(!($this->_opcode = $this->_opcodes[$this->_type]), "Тип услуги не найден.");
        $this->_tariff = sms_services::checkTariff($this->_type, $this->_request['serviceNumber'], $this->_request['country'], $err);
        $this->_errorif($err == 1, "Тип услуги не найден.");
        $this->_errorif($err == 2, "Неверный сервисный номер.");
        $this->_errorif($err == 3, "Ошибочный запрос.", "Страна абонента не распознана.");
        $this->_errorif(!$login, "Не указан логин пользователя.");
        $this->_user = new users();
        $this->_user->GetUser($login);
        $this->_errorif(!$this->_user->uid, "Пользователь с логином {$login} не найден.");
    
        $this->_isValidated = true;
    }

	/**
	 * Оплата выбранного сервиса и ответ пользователю об успехе или ошибке.
	 */
    public function processRequest()
    {
        if(!$this->_isValidated)
            $this->validate();
        
        // Блокируем входящие запросы с данным ид., пока текущая операция не выполнится (см. self::_response()) 
        $mcache = new memBuff();
        $mkey = 'ifreepay.evtId'.$this->_request['evtId'];
        if ($mcache->get($mkey)) {
            $this->_errorif(TRUE, 'Предыдущий запрос в процессе обработки.');
        }
        $mcache->set($mkey, 1, 60);
        $this->_oplock = $mkey;
        
        $op_id = 0;
        $dup = 0;
        $profit = floatval($this->_request['profit']);
        $currency_str = trim(strtoupper($this->_request['profitCurrency']));
        // Внимание! Прежде чем менять текст описания операции, загляните в account::getSmsInfo() и sms_service::checkEvtId().
        $descr = "SMS #{$this->_request['evtId']} с номера {$this->_request['phone']} ({$this->_request['country']})"
               . " на номер {$this->_request['serviceNumber']}, ID абонента {$this->_request['abonentId']},"
               . " оператор {$this->_request['operator']}, текст: {$this->_smsDecoded}, обработан {$this->_request['now']},"
               . " профит {$profit} {$currency_str},"
               . " номер попытки: ".intval($this->_request['retry']);
               
        // Для обработки повторных запросов (в случае сбоев на одной из сторон).
        if(intval($this->_request['retry']) > 0) {
            $dup = sms_services::checkEvtId($this->_request['evtId'], $op_id);
        }
               
        switch($this->_type) {
            case 1:
                if (!$dup && $operator != 'i-Free') {
                    $this->GetInfo($this->_user->uid);
                    $this->_errorif(!$this->id, 'Счет пользователя не открыт.');
                    $error = $this->deposit($op_id, $this->id, $this->_tariff['fm_sum'], $descr, self::PAYMENT_SYS, $this->_tariff['usd_sum'], $this->_opcode);
                    $this->_errorif(!!$error, $error);
                }
                $res_text = "Ваш счет пополнен на {$this->_tariff['fm_sum']} FM";
            case 2:
                $new_password = users::ResetPasswordSMS($this->_user->uid,$this->_request['phone']);
                $this->_errorif(!$new_password, "Неверный логин или телефон не привязан к аккаунту.");
                if (!$dup) {
                    $this->_errorif(!($tr_id = $this->start_transaction($this->_user->uid)), "Ошибка при проведении операции по счету.");
                    $this->_errorif($this->BuyFromSMS($op_id, $tr_id, $this->_opcode, $this->_user->uid, $descr, '', $this->_tariff['usd_sum'], 1, self::PAYMENT_SYS), "Ошибка при проведении денежной операции.");
                }
                $res_text = "Ваш новый пароль: {$new_password}";
            case 3:
                if (!$dup) {
                    $answers = new projects_offers_answers;
                    $this->_errorif(!$answers->AddPayAnswers($this->_user->uid, 1), "Ошибка добавления ответа.");
                    $this->_errorif(!($tr_id = $this->start_transaction($this->_user->uid)), "Ошибка при проведении операции по счету.");
                    $this->_errorif($this->BuyFromSMS($op_id, $tr_id, $this->_opcode, $this->_user->uid, $descr, '', $this->_tariff['usd_sum'], 1, self::PAYMENT_SYS), "Ошибка при проведении денежной операции.");
                }
                $res_text = 'Спасибо за покупку. Теперь вы можете ответить на проект.';
            default:
                $this->_errorif(true, "Тип услуги не найден.");
        }
        
        if(!$dup || $dup == sms_services::DUP_OP_NOTSAVED) {
            $sms_opid = sms_services::saveEvtId($op_id, $profit, $currency_str, $this->_request['evtId']);
        }
        
        $this->_response($res_text);
    }


    /**
	 * Обработка ошибок
	 * @param   boolean   $assert    флаг ошибки. Если TRUE, то сообщение уходит сервису ifree и работа завершается. Если FALSE - ничего не делать.
	 * @param   string    $userErr   сообщение об ошибке, которое отправляется в виде SMS пользователю. Если сообщение не указано, то ничего не отправляется.
	 * @param   string    $ifreeErr  сообщение об ошибке, которое отправляется сервису ifree. Если сообщение не указано, то используется $userErr
	 */
	private function _errorif($assert, $userErr, $ifreeErr = NULL)
    {
        if(!$assert)
            return;

        if($this->_oplock) {
            $mcache = new memBuff();
            $mcache->delete($this->_oplock);
        }
        
        if(!$ifreeErr)
            $ifreeErr = $userErr;

        $response = '<Response><ErrorText><![CDATA['.iconv('windows-1251', 'UTF-8', 'Free-lance.ru. '.$ifreeErr).']]></ErrorText>';
        if($userErr)
          $response .= '<SmsText><![CDATA['.iconv('windows-1251', 'UTF-8', 'Free-lance.ru. '.$userErr).']]></SmsText>';
        $response .= '</Response>';

        die($response);
    }


    /**
	 * Посылает ответ сервису ifree, который в свою очередь пересылает ее в виде SMS абоненту, и завершает работу.
	 * @param   string   $sms   текст сообщения
	 */
	private function _response($sms)
    {

        if($this->_oplock) {
            $mcache = new memBuff();
            $mcache->delete($this->_oplock);
        }
        
        die('<Response><SmsText><![CDATA['.iconv('windows-1251', 'UTF-8', 'Free-lance.ru. '.$sms).']]></SmsText></Response>');
    }
}
?>
