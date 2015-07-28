<?
/**
 * Подключаем файл с основными функциями
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Класс для работы с оплатой через СМС
 *
 */
class sms_services
{

    const DUP_OP_FULL     = 1; // полный дубль (прошел все стадии)
    const DUP_OP_NOTSAVED = 2; // полудубль (операция по сервису прошла, но не было регистрации в sms_operations).
    
    
    /**
     * Таблица тарифов (индекс -- номер телефона). Указывается стоимость SMS в USD.
     *  .descr -- аннотация к тарифу.
     * @var array
     */
    public static $tariffs =
      array ( '4161' => array('country'=>array('RU'=>4.75, 'UA'=>5, 'BY'=>5), 'descr'=>'Номер 4161, стоимость SMS-сообщения составляет приблизительно 160 рублей без НДС. Для абонентов всех национальных GSM операторов Украины, кроме Голден Телеком, стоимость SMS - 30 UAH. Тариф в гривнах с учетом НДС. Дополнительно удерживается сбор в пенсионный фонд в размере 7,5% от стоимости услуги без учета НДС. <br/>Для абонентов МТС Беларусь стоимость SMS — 25000 BYR.'),
              '4446' => array('country'=>array('RU'=>1,    'UA'=>1, 'BY'=>1), 'descr'=>'Стоимость SMS-сообщения для РФ составляет приблизительно 35 рублей с НДС. Стоимость для жителей Украины &mdash; 8 гривен с учетом НДС (дополнительно удерживается сбор в пенсионный фонд в размере 7,5%).'),
              '4449' => array('country'=>array('RU'=>3,    'UA'=>3, 'BY'=>3), 'descr'=>'Номер 4449, стоимость SMS-сообщения составляет приблизительно 90 рублей без НДС. Для Украины 16 UAH с учётом НДС. Дополнительно удерживается сбор в пенсионный фонд в размере 7,5%. <br />Для абонентов МТС Беларусь стоимость SMS — 19900 BYR.')
            );

    /**
     * Таблица действующих сервисов (индекс -- код сервиса, включаемый
     * в SMS-сообщение: 1=Пополнение счета), разделелнная по тарифам (номерам телефонов).
     * В значениях сумма FM, получаемая при пополнении счета (код 1) или 0 -- оплата других услуг.
     * @var array
     */
    public static $services =array( 
        '1' => array( 
            '4449'=>array( 'fm_sum'=>'1.3', 'rur_sum'=>'90', 'byr_sum'=>'19&nbsp;900', 'uah_sum'=>'16' ),
            '4161'=>array( 'fm_sum'=>'2.5', 'rur_sum'=>'160', 'byr_sum'=>'24&nbsp;900', 'uah_sum'=>'30' )
        ), 
        '2' => array('4446'=>0) 
    );

    /**
     * Проверяет и возвращает тариф по коду сервиса, номеру SMS и стране абонента.
     *
     * @param string  $serviceCode   код услуги (1 -- пополнение счета; 2 -- платное место; 3 -- платный ответ на проект и т.д.).
     * @param string  $phone         номер, на который отправляется SMS для оплаты данной услуги.
     * @param string  $countryCode   страна абонента (двухсимвольный код по ISO 3166-1).
     * @param integer $error         Возвращает код ошибки (1 -- неверная услуга; 2 -- неверный номер; 3 -- страна не распознана).
     * @return array   .usd_sum -- стоимость SMS (сколько снимется с его телефона) в USD,
     *                 .fm_sum  -- эквивалент стоимости в FM.
     */
    function checkTariff($serviceCode, $phone, $countryCode = 'RU', &$error = NULL)
    {
        $countryCode = strtoupper($countryCode);

        if(!($service = self::$services[$serviceCode]))
            $error = 1;
        else if(!self::$tariffs[$phone] || !isset($service[$phone]))
            $error = 2;
        else if(!($tariff = self::$tariffs[$phone]['country'][$countryCode]))
            $error = 3;

        if($error)
            return NULL;

        return array('usd_sum'=>$tariff, 'fm_sum'=>$service[$phone]['fm_sum']);
    }


    /**
     * Проверяет СМС запрос по его уникальному номеру (evtId).
     *
     * @global DB $DB
     * @param integer $evtId ID запроса
     * @param integer $op_id   возвращает ид. account_operations.
     * @return integer   тип дубля, или 0, если не дубль.
     */
    function checkEvtId($evtId, &$op_id) {
        global $DB;
        $r = self::DUP_OP_FULL;
        $op_id = $DB->val('SELECT operation_id FROM sms_operations WHERE evt_id = ?', $evtId);
        if(!$op_id) {
            $op_id = $DB->val('SELECT id FROM account_operations WHERE payment_sys = 7 AND descr LIKE ?', "SMS #{$evtId} %");
            $r = self::DUP_OP_NOTSAVED;
        }
        return $op_id ? $r : 0;
    }
    
    
	/**
	 * Записываем оплату по СМС в отдельную таблицу для расчета корректной прибыли
	 *
	 * @param integer $operation   ИД операции (account_operations)
	 * @param float $profit   прибыль от операции в исходной валюте
	 * @param string $currency_str   код валюты прибыли
	 * @param integer $evtId   уникальный номер SMS по I-Free
	 */
	function saveEvtId($operation, $profit = 0, $currency_str = 'RUB', $evtId = NULL) {
		global $DB;
        if($currency_str != 'RUB') {
            $now = time();
            $dtime = strtotime('-1 month', $now);
            while (date('m', $dtime) == date('m', $now)) {
                // Обход: strtotime("-1 month", strtotime("2011-12-31")) -- выдает 2011-12-01
                $dtime = strtotime('-1 day', $dtime);
            }
            $day = date('t', $dtime);
            $currency = self::getCurrencyForDate(date($day.'/m/Y', $dtime));
            if(isset($currency[$currency_str])){
                $amount = $currency[$currency_str]['units'];
                $kurs = $currency[$currency_str]['kurs'];
                $single_kurs = $kurs / $amount;
                if ($currency_str == 'BYR') {
                    $single_kurs = round($single_kurs, 4);
                }
                $profit = $profit * $single_kurs;
            } else {
                $profit = NULL;
            }
        }
		$DB->query('INSERT INTO sms_operations (operation_id, profit, evt_id) VALUES(?, ?f, ?i)', $operation, $profit, $evtId);
	}
	
	/**
	 * Берем курсы валют на определенный день
	 *
	 * @param string $date Дата (в формате 01/01/2009), по умолчанию текущий день
	 * @return array|boolean Курсы валют где ключ это название валюты а значение это массив [units=>Единиц, kurs=> Курс по отношению к рублю], либо false если не удалось взять курсы валют
	 */
	function getCurrencyForDate($date = false) {
		if(!$date) $date = date("d/m/Y");
        $mb = new memBuff();
        if($tmp = $mb->get('currency_for_date')){
            if($tmp['date'] == $date && $tmp['data']) {
                return $tmp['data'];
            }
        }
        libxml_disable_entity_loader();
		$file  = file_get_contents("http://www.cbr.ru/scripts/XML_daily.asp?date_req=$date&d=1"); // Валюты которые меняются раз в месяц
		$file2 = file_get_contents("http://www.cbr.ru/scripts/XML_daily.asp?date_req=$date&d=0"); // Валюты которые меняются раз в день
		
		$p  = simplexml_load_string($file);
		$p2 = simplexml_load_string($file2);
		
		$v = "Valute";
		
		foreach($p->$v as $key=>$value) {
			$cur[(string)($value->CharCode)] = array("units"=> intval($value->Nominal), "kurs"=> round(str_replace(",", ".", $value->Value), 4));
		}
		foreach($p2->$v as $key=>$value) {
			$cur[(string)($value->CharCode)] = array("units"=> intval($value->Nominal), "kurs"=> round(str_replace(",", ".", $value->Value), 4));
		}
		
		if(!isset($cur)) return false;
		
		$cur['RUB'] = array("units" => 1, "kurs"=>1); // Заглушка для рубля
		$mb->set('currency_for_date', array('date' => $date, 'data' => $cur));
		return $cur;	
	}
	
	/**
	 * Функция пересчитывает корректную прибыль с СМС
	 * @deprecated
	 *
	 * @param string $data  Дата пересчета в формате MM/YYYY (11/2009)
	 * @return string|boolean Сообщение об ошибке, либо false если не удалось взять курсы валют
	 */
	function recalc_sms_operation() {
		global $DB;
		$date_sql = "SELECT date_operation FROM sms_operations WHERE profit IS NULL /*AND date_operation < date_trunc('month', now())*/ ORDER BY date_operation ASC LIMIT 1";
        $date_operations = $DB->row($date_sql);
        if(!$date_operations) return false;
        $date_operation = array_shift($date_operations);
        
        $dtime = strtotime("-1 month", strtotime($date_operation));
        $day = date('t', $dtime);
	    
	    $currency = self::getCurrencyForDate(date($day.'/m/Y', $dtime));
        
	    if(!$currency) return false;
	    
        /* Создаем временную таблицу для хранения курсов на текущий момент (текущую дату)*/
        $insert = "CREATE TEMP TABLE temp_currency (name varchar(3), kurs numeric(8,4)) ON COMMIT PRESERVE ROWS; ";
        foreach($currency as $code=>$val) $insert .= "INSERT INTO temp_currency VALUES('{$code}', '".($val['kurs']/$val['units'])."');";
        
        /* Обновляем данные */
        $insert .= "UPDATE sms_operations so SET profit = (t.profit*tc.kurs) 
                   FROM sms_tarif t INNER JOIN temp_currency tc ON (tc.name ILIKE t.currency) 
                   WHERE so.smstarif_id = t.id AND so.profit IS NULL
                   AND so.date_operation >= '$date_operation'
                   AND so.date_operation < date_trunc('month', timestamp '".$date_operation."' + interval '1 month')";
        
        $res = $DB->query($insert);
        
        return $DB->error;
	}
    
}
