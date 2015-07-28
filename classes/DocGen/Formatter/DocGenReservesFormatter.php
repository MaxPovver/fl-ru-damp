<?php

require_once('DocGenFormatter.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesHelper.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_helper.php");

class DocGenReservesFormatter extends DocGenFormatter 
{
    const FIO_JURI_TML  = '%s (в лице __________________________________________, действующий на основании ______________)';
    const FIO_JURI_TML2 = '%s «%s» (в лице __________________________________________, действующий на основании ______________)';
    
    const NUM_TMP       = 'БС#%07d';
    
    //--------------------------------------------------------------------------
    
    /**
     * если работа принята 
     * заказчиком без арбитража
     */
    const TEXT3_0_TML   = '
3. Стоимость выполненной Работы составила %s%s.

4. Работа выполнена надлежащим образом и в установленный срок и принята Заказчиком без возражений.';
    
    /**
     * если арбитр вынес решение 
     * о выплате 100% суммы исполнителю
     */
    const TEXT3_1_TML   = '
3. Стоимость выполненной Работы составила %s%s.';
    
    /**
     * если арбитр вынес решение о разделении 
     * суммы между исполнителем и заказчиком
     */
    const TEXT3_2_TML   = '
3. Стоимость выполненной Работы с учетом соразмерного ее уменьшения на основании Отчета о рассмотрении обращения № %s от %s составила %s%s.';
    
    
    //--------------------------------------------------------------------------
    
    /**
     * если арбитр вынес решение 
     * о выплате 100% суммы исполнителю
     */    
    const TEXT5_0_TML = '1.2. Агентские услуги Заказчику, заключающиеся в совершении от своего имени, но за счет Заказчика юридических и фактических действий по исполнению обязанностей, принятых на себя Заказчиком при заключении с Исполнителем Соглашения выполнении работы и/или оказании услуги при использовании онлайн сервиса «Безопасная сделка», а именно: обязанности по перечислению оплаты  за результат выполненной Исполнителем Работы.';
    
    /**
     * если арбитр вынес решение о разделении 
     * суммы между исполнителем и заказчиком
     */
    const TEXT5_1_TML = '1.2. Агентские услуги Заказчику, заключающиеся в совершении от своего имени, но за счет Заказчика юридических и фактических действий по исполнению обязанностей, принятых на себя Заказчиком при заключении с Исполнителем Соглашения выполнении работы и/или оказании услуги при использовании онлайн сервиса «Безопасная сделка», а именно: обязанности по перечислению оплаты  за результат выполненной Исполнителем Работы;
';

    const TEXT6_1_TML = '1.3. Услуги по проведению независимого и объективного анализа результата Работы при обращении к Обществу Заказчика и (или) Исполнителя в порядке, предусмотренном разделом 6 Договора.
';
    
    /**
     * если арбитр вынес решение 
     * о возврате 100% суммы заказчику
     */
    const TEXT5_2_TML = '1.2. Услуги по проведению независимого и объективного анализа результата Работы при обращении к Обществу Заказчика и (или) Исполнителя в порядке, предусмотренном разделом 6 Договора.';
    
    
    /**
     * если работа принята заказчиком 
     * без арбитража
     */
    const TEXT5_3_TML = '1.2. Агентские услуги Заказчику, заключающиеся в совершении от своего имени, но за счет Заказчика юридических и фактических действий по исполнению обязанностей, принятых на себя Заказчиком при заключении с Исполнителем Соглашения выполнении работы и/или оказании услуги при использовании онлайн сервиса «Безопасная сделка», а именно: обязанности по перечислению оплаты  за результат выполненной Исполнителем Работы.';


    //--------------------------------------------------------------------------

    
    
    /**
     * если арбитр вынес решение 
     * о выплате 100% суммы исполнителю
     */
    const TEXT4_0_TML = 'В связи с возникновением между Заказчиком и Исполнителем разногласий Общество в соответствии с разделом 6 Договора провело независимый анализ соответствия Результата Работы Соглашению, Техническому заданию и Договору. Общество на основании проведенного анализа приняло решение, что результат Работы соответствует Техническому заданию, Соглашению и Договору и был представлен Заказчику в срок, определенный Техническом задании. Данное решение было закреплено в Отчете о рассмотрении обращения № {$num_bs} от {$date_close}. В связи с этим на основании п.п. 2.2.2, 4.3., 6.9.1, 6.13 Договора Общество как Агент Заказчика исполнило принятые на себя обязательства, а именно: совершило юридические и фактические действия по Осуществлению выплаты Стоимости Работы Исполнителю в размере {$price}{$ndfl_txt}.';
    

    /**
     * если арбитр вынес решение 
     * о разделении суммы между 
     * исполнителем и заказчиком
     */
    const TEXT4_1_TML = 'В связи с возникновением между Заказчиком и Исполнителем разногласий Общество в соответствии с разделом 6 Договора провело независимый анализ соответствия результата Работы Соглашению, Техническому заданию и Договору. Общество на основании проведенного анализа приняло решение, что результат Работы частично соответствует Техническому заданию, Соглашению и Договору и представлен Заказчику в срок, определенный в Техническом задании. Данное решение было закреплено в Отчете о рассмотрении обращения № {$num_bs} от {$date_close}. В связи с этим на основании п.п. 2.2.2, 4.2., 4.4., 6.9.2, 6.14, 7.1. Договора Общество как Агент Заказчика исполнило принятые на себя обязательства, совершило следующие юридические и фактические действия: 
– по Осуществлению выплаты соразмерно уменьшенной Стоимости Работы Исполнителю в размере {$price}{$ndfl_txt}, 
– по Осуществлению возврата Заказчику части Зарезервированной суммы в размере {$emp_price}.';
    
    
    /**
     * если арбитр вынес решение 
     * о возврате 100% суммы заказчику
     */
    const TEXT4_2_TML = 'В связи с возникновением между Заказчиком и Исполнителем разногласий Общество провело независимый анализ соответствия Результата Работы Соглашению, Техническому заданию и Договору. Общество на основании проведенного анализа приняло решение, что результат Работы полностью не соответствует Техническому заданию и (или) не представлен в срок, определенный в Техническом задании. Данное решение было закреплено в Отчете о рассмотрении обращения № {$num_bs} от {$date_close}. В связи с этим на основании п.п. 6.9.3, 6.15, 7.1. Договора Общество исполнило принятые на себя обязательства, а именно: совершило юридические и фактические действия по Осуществлению возврата Зарезервированной суммы в размере {$emp_price} Заказчику.';
    

    /**
     * если работа принята 
     * заказчиком без арбитража
     */
    const TEXT4_3_TML = 'В соответствии с п.п. 2.2.2., 4.1.1, 4.3 Договора после того, как Заказчик сообщил Обществу о надлежащем выполнении Работы Исполнителем и сдаче результата такой Работы Исполнителем с помощью программно-технических средств Сайта, Общество, действуя как Агент Заказчика, исполнило принятые на себя обязательства, а именно: совершило юридические и фактические действия по Осуществлению выплаты Исполнителю Стоимости Работы в размере {$price}{$nds_txt}{$ndfl_txt}.';
    
    const TEXT4_NDFL = '. При этом Общество исполнило свои обязанности налогового агента и на основании ст. 226 Налогового кодекса Российской Федерации, а также в соответствии с п. 3.6 Договора удержало из Стоимости Работы налог на доходы физических лиц по ставке {$ndfl} процентов в размере {$ndfl_price}';
    
    
    
    const LETTER_NDFL = 'На основании подп.6 п.3 ст.208 Налогового Кодекса РФ, стоимость работ (услуг) Исполнителя относится к доходам полученным от источников за пределами Российской Федерации и не облагается НДФЛ на территории РФ. Исполнитель самостоятельно уплачивает все применимые налоги на территории своего государства.';

    const TEXT_NDFL_PRICE = 'При этом Общество исполнило свои обязанности налогового агента и на основании ст. 226 Налогового кодекса Российской Федерации, а также в соответствии с п. 3.6 Договора удержало из Стоимости Работы налог на доходы физических лиц по ставке 13 процентов в размере %s.';
    
    const TEXT_NDS_PRICE = ', в том числе НДС %s';
    
    //--------------------------------------------------------------------------
    
    /**
     * если исполнитель/заказчик – 
     * физическое лицо, резидент РФ
     */
    const DETAILS_FT_PHYS_RT_RU = '
{$fio}
Адрес регистрации: {$address_reg}
Почтовый адрес: {$address}
Паспорт: {$idcard_ser} {$idcard}
Выдан: {$idcard_from} {$idcard_by}

E-mail: {$email}
Телефон: {$phone}';
    
    /**
     * если исполнитель -
     * физическое лицо, беженец
     */
    const DETAILS_FT_PHYS_RT_REFUGEE = '
{$fio}
Адрес регистрации: {$address_reg}
Почтовый адрес: {$address}
Cвидетельство о предоставлении временного убежища на территории РФ: {$idcard_ser} {$idcard}
Выдано: {$idcard_from} {$idcard_by}

E-mail: {$email}
Телефон: {$phone}';    
    
    /**
     * если исполнитель -
     * физическое лицо, виб на жительство в РФ
     */
    const DETAILS_FT_PHYS_RT_RESIDENCE = '
{$fio}
Адрес регистрации: {$address_reg}
Почтовый адрес: {$address}
Вид на жительство в РФ: {$idcard_ser} {$idcard}
Выдан: {$idcard_from} {$idcard_by}

E-mail: {$email}
Телефон: {$phone}';       
    
    /**
     * если исполнитель/заказчик – 
     * физическое лицо, нерезидент РФ
     */
    const DETAILS_FT_PHYS_RT_UABYKZ = '
{$fio}
Адрес регистрации: {$address_reg}
Почтовый адрес: {$address}
Паспорт: {$idcard_ser} {$idcard}
Выдан: {$idcard_from} {$idcard_by}
{$bank}
E-mail: {$email}
Телефон: {$phone}';
   
    /**
     * если заполнены банковские реквизиты
     */
    const DETAILS_BANK = '
Расчетный счет: {$bank_rs}
в {$bank_name}

Уполномоченный банк: {$bank_rf_name}
Корреспондентский счет: {$bank_rf_ks}
БИК: {$bank_rf_bik}
ИНН: {$bank_rf_inn}
';
    
    
    /**
     * если исполнитель/заказчик – 
     * юридическое лицо, ИП, резидент РФ
     */
    const DETAILS_FT_JURI_IP_RT_RU = '
{$full_name}
Юридический адрес: {$address_jry}
Почтовый адрес: {$address}
ИНН: {$inn}

Расчетный счет: {$bank_rs}
в {$bank_name}
Корреспондентский счет: {$bank_ks}
БИК: {$bank_bik}
ИНН: {$bank_inn}

E-mail: {$email}
Телефон: {$phone}';
    
    /**
     * если исполнитель/заказчик – 
     * юридическое лицо, резидент РФ
     */
    const DETAILS_FT_JURI_RT_RU = '
{$full_name}
Юридический адрес: {$address_jry}
Почтовый адрес: {$address}
ИНН: {$inn}

Расчетный счет: {$bank_rs}
в {$bank_name}
Корреспондентский счет: {$bank_ks}
БИК: {$bank_bik}
ИНН: {$bank_inn}

E-mail: {$email}
Телефон: {$phone}';    
    
    /**
     * если исполнитель/заказчик – 
     * юридическое лицо, нерезидент РФ
     */
    const DETAILS_FT_JURI_RT_UABYKZ = '
{$full_name}
Юридический адрес: {$address_jry}
Почтовый адрес: {$address}
РНН: {$rnn}

Расчетный счет: {$bank_rs}
в {$bank_name}

Уполномоченный банк: {$bank_rf_name}
Корреспондентский счет: {$bank_rf_ks}
БИК: {$bank_rf_bik}
ИНН: {$bank_rf_inn}

E-mail: {$email}
Телефон: {$phone}';    
    

/**
 * если арбитр вынес решение 
 * о возврате 100% суммы заказчику
 */    
    const TEXT2_1 = '
    Заказчик в согласованный в сделке срок не получил результат работы, полностью соответствующий Техническому заданию.

    Таким образом, в результате рассмотрения обращения и на основании Общество приняло нижеследующее решение в соответствии с п. 6.9.3. Договора:

    Результат интеллектуальной деятельности полностью не соответствует Техническому заданию (или) не представлен в срок, определенный в Техническом задании. 

    Данное решение Общества является основанием для возврата Заказчику Зарезервированной суммы в полном размере, а именно: в сумме {$price}, в порядке п. 6.15. и раздела 7 Договора.';
   
/**
 * делим костанту выше из-за выделенной болдом прослойки
 */    
    const TEXT2_1_TOP_1 = 'Заказчик в согласованный в сделке срок не получил результат работы, полностью соответствующий Техническому заданию.';

    const TEXT2_1_TOP_2 = 'Таким образом, в результате рассмотрения обращения и на основании Общество приняло нижеследующее решение в соответствии с п. 6.9.3. Договора:        ';
    
    
    const TEXT2_1_MID = 'Результат интеллектуальной деятельности полностью не соответствует Техническому заданию (или) не представлен в срок, определенный в Техническом задании. ';
  
    const TEXT2_1_BOT = 'Данное решение Общества является основанием для возврата Заказчику Зарезервированной суммы в полном размере, а именно: в сумме {$price}, в порядке п. 6.15. и раздела 7 Договора.';
    
    
    
    
    
/**
 * если арбитр вынес решение 
 * о выплате 100% суммы исполнителю
 */
    const TEXT2_2 = '
    Заказчик в согласованный в сделке срок получил результат работы, полностью соответствующий Техническому заданию.
	
    Таким образом, в результате рассмотрения обращения и на основании Общество приняло нижеследующее решение в соответствии с п. 6.9.1. Договора:

    Работа выполнена надлежащим образом и в срок в соответствии с Соглашением и Техническим заданием.

    Согласно п. 6.13 Договора данное решение Общества является основанием для Осуществления выплаты Стоимости Работы в полном размере Исполнителю в размере {$price} в порядке, установленном в п. 4.3 Договора.';

    
    const TEXT2_2_TOP_1 = 'Заказчик в согласованный в сделке срок получил результат работы, полностью соответствующий Техническому заданию.';
    
    const TEXT2_2_TOP_2 = 'Таким образом, в результате рассмотрения обращения и на основании Общество приняло нижеследующее решение в соответствии с п. 6.9.1. Договора:';    
    
    const TEXT2_2_MID = 'Работа выполнена надлежащим образом и в срок в соответствии с Соглашением и Техническим заданием.';

    const TEXT2_2_BOT = 'Согласно п. 6.13 Договора данное решение Общества является основанием для Осуществления выплаты Стоимости Работы в полном размере Исполнителю в размере {$price} в порядке, установленном в п. 4.3 Договора.';
    
    
    
/**
 * если арбитр вынес решение 
 * о разделении суммы между исполнителем и заказчиком
 */
    const TEXT2_3 = '
    Заказчик в согласованный в сделке срок получил результат работы, лишь частично (на {$persent}) соответствующий Техническому заданию.
	
    Таким образом, в результате рассмотрения обращения и на основании Общество приняло нижеследующее решение в соответствии с п. 6.9.2. Договора:

    Результат работы частично соответствует Техническому заданию и Соглашению и представлен Заказчику в срок, определенный в Техническом задании. Представленный Исполнителем результат Работы соответствует Техническому заданию и Соглашению на {$persent}.

    Данное решение Общества является основанием для:
        - Осуществления выплаты соразмерно уменьшенной Стоимости Работы в пользу Исполнителя в размере {$frl_price} в порядке, предусмотренном п.п. 6.14, 4.2., 4.4 Договора;
        - Осуществления возврата Заказчику части Зарезервированной суммы в размере {$emp_price} в порядке, предусмотренном п. 6.14, раздела 7 Договора.';
    
    
    const TEXT2_3_TOP_1 = 'Заказчик в согласованный в сделке срок получил результат работы, лишь частично (на {$persent}) соответствующий Техническому заданию.';
    
    const TEXT2_3_TOP_2 = 'Таким образом, в результате рассмотрения обращения и на основании Общество приняло нижеследующее решение в соответствии с п. 6.9.2. Договора:';    
    
    
    const TEXT2_3_MID = 'Результат работы частично соответствует Техническому заданию и Соглашению и представлен Заказчику в срок, определенный в Техническом задании. Представленный Исполнителем результат Работы соответствует Техническому заданию и Соглашению на {$persent}.';
    
    const TEXT2_3_BOT = 'Данное решение Общества является основанием для:
- Осуществления выплаты соразмерно уменьшенной Стоимости Работы в пользу Исполнителя в размере {$frl_price} в порядке, предусмотренном п.п. 6.14, 4.2., 4.4 Договора;
- Осуществления возврата Заказчику части Зарезервированной суммы в размере {$emp_price} в порядке, предусмотренном п. 6.14, раздела 7 Договора.';
    
    
    const TEXT_TITLE_FRL_REQV = 'Реквизиты';
    
    const TEXT_TITLE_FRL_DATA = 'Данные';
    
    const TEXT_DATE_WORK = "%d %s до %s.";
    
    
    /*
    public function prevdate($data)
    {
        $timestamp = strtotime($data);
        $timestamp = strtotime('- 1 day', $timestamp);
        
        if(in_array(idate('w', $timestamp),array(0,6))) {
            $timestamp = strtotime('- 1 day', $timestamp);
        }
        
        //если опять выходной то еще раз следующий день
        if(in_array(idate('w', $timestamp),array(0,6))) {
            $timestamp = strtotime('+ 1 day', $timestamp);
        }
        
        return date('j.m.Y', $timestamp);
    }
    */


    public function datereqv(ReservesModel $reserveInstance)
    {
        $time = $reserveInstance->getLastCompleteDate(true);
        return date('j',$time) . ' ' . monthtostr(date('n',$time),true) . ' ' . date('Y',$time);
    }



    public function nds($value)
    {
        if(!$value) return false;
        
        $value = $value * 18 / 118;
        return $this->pricelong($value);
    }


    public function pricends($value)
    {
        if(!$value) return false;
        
        $value = $value * 18 / 118;
        return $this->price($value);
    }
    
    
    public function nonds($value)
    {
        if(!$value) return false;
        $value = $value/1.18;
        return $this->price($value);
    }

    
    public function nondstotal($options)
    {
        extract($options);
        $tax_price = $tax_price/1.18;
        return $this->price($price + $tax_price);
    }

    
    public function tuextra($options)
    {
        extract($options);
        
        $text = '';
        foreach($order_extra as $idx)
        {
            if(!isset($extra[$idx])) continue;
            $text .= $this->reformat30($extra[$idx]['title']) . PHP_EOL;
        }
        
        return $text;
    }

    

    public function text2top1($options)
    {
        extract($options);
        
        $data = array();
        
        if($arbitrage_price == 0) $text = self::TEXT2_1_TOP_1;
        elseif($arbitrage_price == $price) $text = self::TEXT2_2_TOP_1;
        else {
            $text = self::TEXT2_3_TOP_1;
            
            $persent = ($arbitrage_price / $price)*100;
            $data['persent'] = $this->price($persent) . ' ' . ending($persent,'процент','процента','процентов');
        }
        
        $text = $this->template($text, $data);
        
        return $text;        
    }

    
    public function text2top2($options)
    {
        extract($options);
        
        $data = array();
        
        if($arbitrage_price == 0) $text = self::TEXT2_1_TOP_2;
        elseif($arbitrage_price == $price) $text = self::TEXT2_2_TOP_2;
        else {
            $text = self::TEXT2_3_TOP_2;
            
            $persent = ($arbitrage_price / $price)*100;
            $data['persent'] = $this->price($persent) . ' ' . ending($persent,'процент','процента','процентов');
        }
        
        $text = $this->template($text, $data);
        
        return $text;        
    }
    
    
    public function text2mid($options)
    {
        extract($options);
        
        $data = array('price' => $this->pricelong($price));
        
        if($arbitrage_price == 0) $text = self::TEXT2_1_MID;
        elseif($arbitrage_price == $price) $text = self::TEXT2_2_MID;
        else {
            $text = self::TEXT2_3_MID;
            
            $persent = ($arbitrage_price / $price)*100;
            $data['persent'] = $this->price($persent) . ' ' . ending($persent,'процент','процента','процентов');
        }
        
        $text = $this->template($text, $data);
        
        return $text;        
    }
    
    
    
    public function text2bot($options)
    {
        extract($options);
        
        $data = array('price' => $this->pricelong($price));
        
        if($arbitrage_price == 0) $text = self::TEXT2_1_BOT;
        elseif($arbitrage_price == $price) $text = self::TEXT2_2_BOT;
        else {
            $text = self::TEXT2_3_BOT;
            
            $emp_price = $price - $arbitrage_price;
            $data['frl_price'] = $this->pricelong($arbitrage_price);
            $data['emp_price'] = $this->pricelong($emp_price);
        }
        
        $text = $this->template($text, $data);
        
        return $text;        
    }
    
    

    public function text2($options)
    {
        extract($options);
        
        $data = array('price' => $this->pricelong($price));
        
        if($arbitrage_price == 0) $text = self::TEXT2_1;
        elseif($arbitrage_price == $price) $text = self::TEXT2_2;
        else {
            $text = self::TEXT2_3;
            
            $emp_price = $price - $arbitrage_price;
            $persent = ($arbitrage_price / $price)*100;
            
            $data['frl_price'] = $this->pricelong($arbitrage_price);
            $data['emp_price'] = $this->pricelong($emp_price);
            
            $data['persent'] = $this->price($persent) . ' ' . ending($persent,'процент','процента','процентов');
        }
        
        $text = $this->template($text, $data);
        
        return $text;
    }

    

    public function text4(ReservesModel $reserveInstance)
    {
        
        
        $pricePay = $reserveInstance->getPayoutSum();
        $pricePayNDFL = $reserveInstance->getPayoutNDFL();
        $priceBack = $reserveInstance->getPayback();
        $src_id = $reserveInstance->getSrcId();
        
        $data = array(
            'num_bs' => $this->num($src_id)
        );        
        
        if (!$reserveInstance->isArbitrage()){
            $text = self::TEXT4_3_TML;
            $data['price'] = $this->pricelong($pricePay);
        } elseif ($pricePay == 0) {
            $text = self::TEXT4_2_TML;
            $data['emp_price'] = $this->pricelong($priceBack);
        } elseif ($priceBack == 0) {
            $text = self::TEXT4_0_TML;
            $data['price'] = $this->pricelong($pricePay);
        } else {
            $text = self::TEXT4_1_TML;
            $data['price'] = $this->pricelong($pricePay);
            $data['emp_price'] = $this->pricelong($priceBack);
        }
        
        if ($reserveInstance->isArbitrage()) {
            $data['date_close'] = $this->date($reserveInstance->getArbitrageDateClose());
        }
        
        $data['ndfl_txt'] = '';
        if ($pricePayNDFL > 0) {
            $ndfl_txt = $this->template(self::TEXT4_NDFL, array(
                'ndfl_price' => $this->pricelong($pricePayNDFL),
                'ndfl' => $reserveInstance::NDFL * 100
            ));
            $data['ndfl_txt'] = $ndfl_txt;
        }
        
        $text = $this->template($text, $data);
        return $text;
    }

    

    public function text5($options)
    {
        extract($options);
        
        if($arbitrage_price === null) $text = self::TEXT5_3_TML;
        elseif($arbitrage_price == 0) $text = self::TEXT5_2_TML; 
        elseif($arbitrage_price == $price) $text = self::TEXT5_0_TML;
        else $text = self::TEXT5_1_TML;
        
        return $text;
    }
    
    public function text6($options)
    {
        extract($options);
        
        $text = '';
        if ($arbitrage_price > 0 && $arbitrage_price < $price) {
            $text = self::TEXT6_1_TML;
        }
        return $text;
    }


    public function text3($options)
    {
        extract($options['reserve_data']);

        $pricePay = $options['reserve']->getPayoutSumWithOutNDFL();
        
        $ndfl_txt = $this->ndflprice($options['reserve']);
        
        if($arbitrage_price === null) $text = sprintf(self::TEXT3_0_TML, 
                $this->pricelong($pricePay), 
                $ndfl_txt);
        elseif($arbitrage_price == $price) $text = sprintf(self::TEXT3_1_TML, 
                $this->pricelong($pricePay), 
                $ndfl_txt);
        else $text = sprintf(self::TEXT3_2_TML, 
                $this->num($options['order_id']), 
                $this->date($arbitrage_date_close),
                $this->pricelong($pricePay),
                $ndfl_txt);
        
        return $text;
    }

    
    public function kpp($reqv)
    {
        if(!$reqv || !$reqv['form_type']) return false;
        return $reqv['kpp'];        
    }
    
    
    public function inn($reqv)
    {
        if(!$reqv || !$reqv['form_type']) return false;
        return $reqv['inn'];        
    }
    

    public function address($reqv)
    {
        if(!$reqv || !$reqv['form_type']) return false;
        return ($reqv['form_type'] == sbr::FT_PHYS)?$reqv['address']: $reqv['address_jry'];
    }
    

    public function details($options)
    {
        extract($options);
        
        if(!$reqv || !$reqv['form_type']) return false;
        
        $reqv['email'] = $email;
        $form_type = $reqv['form_type'];
        $rez_type  = $reqv['rez_type'];
        
        $is_ip = false;
        if(isset($reqv['type']) && $reqv['type'] !== null)
        {
            $is_ip = ($reqv['type'] == sbr_meta::TYPE_IP);
            if(!$is_ip) $reqv['full_name'] = '«' . $reqv['full_name'] . '»';
            $reqv['full_name'] = sbr_meta::$types_short[(int)$reqv['type']] . ' ' . $reqv['full_name'];
        }
        
        $reqv['bank'] = '';
        if(isset($reqv['bank_rs']))
        {
            $reqv['bank'] = $this->template(self::DETAILS_BANK, $reqv);
        }
        
        //@todo: https://beta.free-lance.ru/mantis/view.php?id=29233
        if (!empty($reqv['mob_phone'])) {
            $reqv['phone'] = $reqv['mob_phone']; 
        }
        
        $details = array(
            sbr::FT_PHYS . sbr::RT_RU => self::DETAILS_FT_PHYS_RT_RU,
            sbr::FT_PHYS . sbr::RT_RESIDENCE => self::DETAILS_FT_PHYS_RT_RESIDENCE,
            sbr::FT_PHYS . sbr::RT_REFUGEE => self::DETAILS_FT_PHYS_RT_REFUGEE,
            sbr::FT_PHYS . sbr::RT_UABYKZ => self::DETAILS_FT_PHYS_RT_UABYKZ,
            sbr::FT_JURI . sbr::RT_RU => ($is_ip)?self::DETAILS_FT_JURI_IP_RT_RU:
                                                  self::DETAILS_FT_JURI_RT_RU,
            sbr::FT_JURI . sbr::RT_UABYKZ => self::DETAILS_FT_JURI_RT_UABYKZ
        );
        
        $code = $form_type . $rez_type;
        if(!isset($details[$code])) return false;
        
        $txt = $this->template($details[$code], $reqv);
        $txt = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $txt);
        
        return $txt;
    }

    
    /*public function phone($uid)
    {
        $reqvs = ReservesHelper::getInstance()->getUserReqvs($uid);
        if(!$reqvs || !$reqvs['form_type']) return false;
        $reqv = $reqvs[$reqvs['form_type']];
        if(empty($reqv['phone'])) $reqv['phone'] = $reqv['mob_phone'];
        return $reqv['phone'];
    }*/

    
    public function fio($reqv)
    {
        if (!$reqv || !$reqv['form_type']) return false;
        
        $fio = $reqv['fio'];
        if ($reqv['form_type'] == sbr::FT_JURI) {
            if ($reqv['type'] === null) {
                $fio = sprintf(self::FIO_JURI_TML, $reqv['full_name']);
            } elseif ($reqv['type'] == sbr_meta::TYPE_IP) {
                $fio = sbr_meta::$types_short[(int)$reqv['type']] . ' ' . $reqv['full_name'];
            } else {
                $fio = sprintf(self::FIO_JURI_TML2, sbr_meta::$types_short[(int)$reqv['type']], $reqv['full_name']);
            }
        }
        
        return $fio;
    }
    
    
    
    public function name($reqv)
    {
        if(!$reqv || !$reqv['form_type']) return false;
        
        $fio = $reqv['fio'];
        
        if($reqv['form_type'] == sbr::FT_JURI)
        {
            if($reqv['type'] === null) $fio = $reqv['full_name'];
            elseif($reqv['type'] == sbr_meta::TYPE_IP) $fio = sbr_meta::$types_short[(int)$reqv['type']] . ' ' . $fio;
            else $fio = sprintf("%s «%s»", sbr_meta::$types_short[(int)$reqv['type']], $reqv['full_name']);            
        }
        
        return html_entity_decode($fio, ENT_QUOTES);
    }

    



    public function num($value)
    {
        return sprintf(self::NUM_TMP, $value);
    }
    
    
    public function orderurl($order_id)
    {
        return $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order_id);
    }
    
    
    public function daytext($value)
    {
        return tservices_helper::days_format($value);
    }
    
    
    public function reformat60($value)
    {
        return reformat($value, 60, 0, 0, 1);
    }
    
    
    public function reformat30($value)
    {
        return reformat($value, 30, 0, 1);
    }
    
    public function text7($reqv) 
    {
        if(!$reqv || !$reqv['form_type']) return '';
        
        if ($reqv['rez_type'] == sbr::RT_UABYKZ) {
            return self::LETTER_NDFL;
        }
        return '';
    }
    
    public function ndflprice(ReservesModel $reserveInstance) 
    {
        $pricePayNDFL = $reserveInstance->getPayoutNDFL();
        
        if ($pricePayNDFL > 0) {
            return $this->template(self::TEXT4_NDFL, array(
                'ndfl_price' => $this->pricelong($pricePayNDFL),
                'ndfl' => $reserveInstance::NDFL * 100
            ));
        }
        return '';
    }
    
    public function ndsprice($options) 
    {
        extract($options);
        
        if(!$reqv || !$reqv['form_type']) return '';
        
        if ($reqv['bank_nds'] == 1) {
            return sprintf(self::TEXT_NDS_PRICE, $this->nds($price));
        }
        return '';
    }
    
    public function dettitle($reqv)
    {
        if(!$reqv || !$reqv['form_type']) return false;
        
        //Реквизиты будут у юрлиц и у физлиц-нерезидентов, заполнивших банковские реквизиты
        $use_reqv = ($reqv['form_type'] == sbr::FT_JURI) || ($reqv['rez_type'] == sbr::RT_UABYKZ && isset($reqv['bank_rs']));
        
        return $use_reqv ? self::TEXT_TITLE_FRL_REQV : self::TEXT_TITLE_FRL_DATA;
    }
    
    public function worktime($options)
    {
        extract($options);
        
        $dateTime = new DateTime($date);
        $dateTime->add(new DateInterval("P" . $days . "D"));
        $date_formatted = $dateTime->format('d.m.Y, H:i');

        return sprintf(self::TEXT_DATE_WORK, 
                $days,
                ending($days, 'день', 'дня', 'дней'), 
                $date_formatted);
    }
    
    public function country($options)
    {
        extract($options);
        
        if(!$reqv || !$reqv['form_type']) return false;
        
        if ($reqv['country']) {
            return $reqv['country'];
        } elseif (in_array($reqv['rez_type'], array(sbr::RT_RU, sbr::RT_REFUGEE, sbr::RT_RESIDENCE))) {
            return 'Россия';
        } else {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/country.php';
            return country::GetCountryName($user_country_id);
        }
    }
}