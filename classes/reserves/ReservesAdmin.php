<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/BaseModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DocGen/DocGenReserves.php');

/*
 * Класс для работы с данными сделок в админке
 * Подробности в тикете #0027147
 */
class ReservesAdmin extends BaseModel {
    
    const OPERATION_RESERVE = 0;
    const OPERATION_PAY = 1;
    const OPERATION_BACK = 2;
    const OPERATION_TAX = 3;
    
    const STATUS_OK = 1;
    
    const PAY_KIND_YK = 0;
    const PAY_KIND_BANK = 1;
    
    protected $TABLE = 'file_reestr_1c';
    
    protected $file_prefix = 'reestr';
    
    protected $start;
    protected $end;
    
    private $uids;
    
    public $path = '/reestrs/';
    public $temp_path = '/temp/upload/';
    
    protected $temp_file_dir;
    
    protected $operations = array(
        self::OPERATION_RESERVE,
        self::OPERATION_PAY,
        self::OPERATION_BACK
    );
    
    protected $reestr1 = array();
    protected $reestr2 = array();
    protected $reestr3 = array();
    
    protected $reestr1_filename;
    protected $reestr2_filename;
    protected $reestr3_filename;

    protected $foreign_logins = array();
    
    private $extra_fields = array(
        3 => array(
            'reserve_price'
        )
    );
            
    function __construct() 
    {
        $this->temp_file_dir = ABS_PATH . $this->temp_path;
        $this->uids = array();
    }
    
    /**
     * Формирует csv-файлы с данными за указанный период. Возвращает массив с названиями файлов
     * @param type $date_s Дата начала периода
     * @param type $time_s Время периода
     * @param type $date_e Дата окончания периода
     * @param type $time_e Время окончания периода
     */
    public function exportReservesToCSV($date_s, $time_s, $date_e, $time_e) {
        $this->prepareTime($date_s, $time_s, $date_e, $time_e);
        
        $this->reestr3 = $this->getOperations();
        if (!$this->reestr3) return false;
        $this->reestr3_filename = $this->writeFile($this->reestr3, 3);
        
        if ($this->uids) {
            $users = $this->getUsers();
            if (!$users) return false;
            $this->reestr1 = $users[1];
            $this->reestr2 = $users[4];
                    
            $this->reestr1_filename = $this->writeFile($this->reestr1, 1);
            $this->reestr2_filename = $this->writeFile($this->reestr2, 2);
        }
        
        return array(
            $this->reestr1_filename, 
            $this->reestr2_filename, 
            $this->reestr3_filename
        );
    }
    
    /**
     * возвращает список файлов
     * @return array
     */
    public function getReestrs($imported = false) {
        $where = " WHERE fname LIKE '".($imported ? "f":"reestr")."_%' ";
        $sql = "SELECT fname, modified FROM {$this->TABLE} {$where} ORDER BY modified DESC";
        $filenames = $this->db()->rows($sql);
        
        if ($imported)
            return $filenames;
        
        $files = array();
        foreach ($filenames as $file) {
            $filename = $file['fname'];
            if(in_array($filename, array('.', '..')))                
                    continue;
           
            $fa = explode('_', str_replace('.csv', '', $filename));
            $dates = explode('-', $fa[2]);
            $files[$dates[0]][$dates[1]]['date'] = $file['modified'];
            $files[$dates[0]][$dates[1]]['reestr'.$fa[1]] = $filename;
        }
        return $files;
    }
    
    /**
     * Производит проверку и задает значения даты начала и конца в полях класса
     * @param type $date_s
     * @param type $time_s
     * @param type $date_e
     * @param type $time_e
     */
    protected function prepareTime($date_s, $time_s, $date_e, $time_e) {
        if(!$time_s) $time_s = '00:00:00';
        if(!$time_e) $time_e = '23:59:59';
        
        $start = $this->validateTime($date_s, $time_s);
        $end   = $this->validateTime($date_e, $time_e);

        if ($start < $end) {
            $this->start = $start;
            $this->end = $end;
        } else {
            $this->start = $end;
            $this->end = $start;
        }
    }
    
    /**
     * Проверяет дату и приводит ее к нужному формату
     * @param type $date
     * @param type $time
     * @return boolean
     */
    private function validateTime($date, $time) {
        if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $date)
                || !preg_match("/^[0-9]{1,2}:[0-9]{2}(:[0-9]{2})?$/", $time)) {
            return false;
        }

        $timestamp = strtotime($date . ' ' . $time);
        return date('Y-m-d H:i:s', $timestamp);
    }
    
    /**
     * Возвращает название заказа в нужном формате
     * @param type $oid ИД заказа
     * @return string
     */
    protected function formatOrderName($oid) {
        return 'БС#'.str_pad($oid, 7, '0', STR_PAD_LEFT);        
    }
    
    
    protected function getOrderId($orderName) 
    {
        return (int)preg_replace("/[^0-9]/", "", $orderName);
    }
    
    /**
     * Формирует массив данных для экспорта
     * @param array $reserve Массив с данными транзакции
     * @param int $type Тип операции: 
     *     0 - зачисление суммы заказчиком, 
     *     1 - оплата исполнителю
     *     2 - возврат суммы заказчику
     *     3 - сумма комиссии Ваан
     * @param float $sum Сумма оплаты
     * @param string $date Дата совершения операции
     * @return array
     */
    private function formatOperation($reserve, $type, $sum) {
        
        $ndfl = 0;
        if ($type == self::OPERATION_PAY && $reserve['frl_status'] == sbr::FT_PHYS && in_array($reserve['frl_rez'], array(sbr::RT_RU, sbr::RT_REFUGEE, sbr::RT_RESIDENCE))) {
            $ndfl = round($sum * 0.13);
            $sum -= $ndfl;
        }
        
        if ($type == self::OPERATION_PAY && isset($reserve['pay_type'])) {
            $pay_kind = $reserve['pay_type'] == 'bank' ? self::PAY_KIND_BANK : self::PAY_KIND_YK;
        } else {
            $pay_kind = $this->getPayKind(
                $reserve['emp_rez'], 
                $reserve['emp_status'], 
                $type
            );
        }

        $emp_id = ($reserve['emp_status'] == sbr::FT_JURI  && $reserve['emp_rez'] == sbr::RT_RU ? $reserve['emp_inn'] : $reserve['emp_login']);
        $frl_id = ($reserve['frl_status'] == sbr::FT_JURI && $reserve['frl_rez'] == sbr::RT_RU ? $reserve['frl_inn'] : $reserve['frl_login']);
        
        return array(
            'order_id' => $this->formatOrderName($reserve['src_id']),
            'emp_login' => $reserve['emp_login'],
            'emp_id' => $emp_id,
            'emp_status' => $this->overrideStatus($reserve['emp_status']),
            'frl_login' => $reserve['frl_login'],
            'frl_id' => $frl_id,
            'frl_status' => $this->overrideStatus($reserve['frl_status']),
            'pay_type' => $type, 
            'pay_sum' => (string)number_format((float)$sum, 2, '.', ''),
            'reserve_price' => $type == self::OPERATION_RESERVE ? $reserve['reserve_price'] : 0,
            'ndfl_sum' => $ndfl,
            'pay_kind' => $pay_kind,
            'pay_date' => $reserve['operation_date']
        );
    }
    
    /**
     * Определение способа выплаты
     * Зачисление сумм:
     * у физиков (резидентов и нерезидентов) - через Кассу
     * у юриков (резидентов и нерезидентов) - безналом
     * 
     * Выплата сумм:
     * у физиков (резидентов) - через Кассу
     * у физиков (нерезидентов) - безналом
     * у юриков (резидентов и нерезидентов) - безналом
     * 
     * @param type $rezident
     * @param type $form_type
     * @param type $operation_type
     * @return type
     */
    private function getPayKind($rezident, $form_type, $operation_type) {
        switch ($operation_type) {
            case self::OPERATION_RESERVE:
            case self::OPERATION_TAX:
            case self::OPERATION_BACK:
                return $form_type == sbr::FT_PHYS 
                    ? self::PAY_KIND_YK 
                    : self::PAY_KIND_BANK;

            // Данный кусок, возможно, больше не будет выполняться, 
            // т.к. способ выплаты берется из таблицы reserves_payout_reqv
            case self::OPERATION_PAY:
                return in_array($rezident, array(sbr::RT_RU, sbr::RT_REFUGEE, sbr::RT_RESIDENCE)) && $form_type == sbr::FT_PHYS 
                    ? self::PAY_KIND_YK 
                    : self::PAY_KIND_BANK;
        }
    }
    
    /**
     * Возвращает информацию об операциях в сделках за указанный период
     * @return array
     */
    private function getOperations(){
        $data = array();

        $query = "SELECT DISTINCT ON (r.id) r.*, to_char(%s, 'DD.MM.YYYY HH24:MI') as operation_date,
                e.login AS emp_login, f.login AS frl_login,
                sre.form_type AS emp_status, srf.form_type AS frl_status,
                sre.rez_type AS emp_rez, srf.rez_type AS frl_rez,
                sre._2_inn AS emp_inn, srf._2_inn AS frl_inn
                %s
            FROM reserves r
            INNER JOIN employer e ON r.emp_id = e.uid
            INNER JOIN freelancer f ON r.frl_id = f.uid
            INNER JOIN sbr_reqv sre ON sre.user_id = r.emp_id
            INNER JOIN sbr_reqv srf ON srf.user_id = r.frl_id
            %s
            WHERE r.status >= ?i AND %s;";
        
        //Выгрузка только для не отмененных резервов
        $query = $this->db()->parse($query, ReservesModel::STATUS_NEW);
        
        
        /**
         * Формируем список сделок, в которых зарезервировали сумму - заказчиком нажата 
         * "Зарезервировать ..." и сумма успешно зарезервирована через Яндекс.Кассу
         * Фильтруем по дате платежа
         */
        $sql1 = sprintf($query, 'r.date_reserve', '', '', 
                'r.date_reserve > ? AND r.date_reserve < ? AND r.invoice_id > 0');
        //Допускается, что финансы исполнителя могут быть незаполненными
        $sql1 = str_replace('INNER JOIN sbr_reqv srf', 'LEFT JOIN sbr_reqv srf', $sql1);

        $reserves1  = $this->db()->rows($sql1, $this->start, $this->end);
        foreach ($reserves1  as $reserve) {
            $this->addUid($reserve['emp_id']);
            $this->addUid($reserve['frl_id']);
            $data[] = $this->formatOperation($reserve, self::OPERATION_RESERVE, $reserve['price']);
            $data[] = $this->formatOperation($reserve, self::OPERATION_TAX, $reserve['tax_price']);
        }

        /**
         * Формируем список сделок, в которых инициировали резервирование - выписан счет 
         * на оплату банковским переводом, и не зарезервирована через кассу.
         * Фильтруем по дате выписки счета на оплату
         */
        $sql2 = sprintf($query, 'rb.date', '',
                'INNER JOIN reserves_bank rb ON rb.reserve_id = r.id', 
                'rb.date > ? AND rb.date < ? AND r.invoice_id IS NULL');
        //Допускается, что финансы исполнителя могут быть незаполненными
        $sql2 = str_replace('INNER JOIN sbr_reqv srf', 'LEFT JOIN sbr_reqv srf', $sql2);
        $reserves2 = $this->db()->rows($sql2, $this->start, $this->end);
        foreach ($reserves2  as $reserve) {
            $this->addUid($reserve['emp_id']);
            $this->addUid($reserve['frl_id']);
            $data[] = $this->formatOperation($reserve, self::OPERATION_RESERVE, $reserve['price']);
            $data[] = $this->formatOperation($reserve, self::OPERATION_TAX, $reserve['tax_price']);
        }
        
        /**
         * Формируем список сделок, в которых исполнителю выплачена сумма - 
         * исполнителем нажата "Подтвердить выплату" и сумма успешно выплачена 
         * через Яндекс.Кассу
         * Фильтруем по дате осуществления выплаты
         */
        $sql3 = sprintf($query, 
                'rp.last', 
                ',rpr.pay_type as pay_type, ra.id AS arbitrage_id, ra.price AS arbitrage_price',
                'INNER JOIN reserves_payout rp ON rp.reserve_id = r.id
                 INNER JOIN reserves_payout_reqv rpr ON rpr.reserve_id = r.id
                 LEFT JOIN reserves_arbitrage ra ON ra.reserve_id = r.id', 
                'rp.last > ? AND rp.last < ? AND r.status_pay = ?i AND rpr.pay_type != ? ORDER BY r.id, rp.date DESC');
        $reserves3 = $this->db()->rows($sql3, $this->start, $this->end, ReservesModel::SUBSTATUS_PAYED, 'bank');
        foreach ($reserves3  as $reserve) {
            $this->addUid($reserve['emp_id']);
            $this->addUid($reserve['frl_id']);
            $price = $reserve['arbitrage_id'] > 0 ? $reserve['arbitrage_price'] : $reserve['price'];
            $data[] = $this->formatOperation($reserve, self::OPERATION_PAY, $price);
        }

        /**
         * Формируем список сделок, в которых исполнителю инициировали выплату - 
         * подана заявка на выплату банковским переводом.
         * Фильтруем по дате формирования запроса на выплату банковским переводом
         */
        $sql4 = sprintf($query, 
                'rpr.date', 
                ',rpr.pay_type as pay_type, ra.id AS arbitrage_id, ra.price AS arbitrage_price',
                'INNER JOIN reserves_payout_reqv rpr ON rpr.reserve_id = r.id
                LEFT JOIN reserves_arbitrage ra ON ra.reserve_id = r.id',                 
                'rpr.date > ? AND rpr.date < ? AND r.status_pay IN(?l) AND rpr.pay_type = ?');
        $reserves4 = $this->db()->rows($sql4, $this->start, $this->end, 
                array(ReservesModel::SUBSTATUS_NEW, ReservesModel::SUBSTATUS_INPROGRESS), 'bank');
        
        foreach ($reserves4 as $reserve) {
            $this->addUid($reserve['emp_id']);
            $this->addUid($reserve['frl_id']);
            $price = $reserve['arbitrage_id'] > 0 ? $reserve['arbitrage_price'] : $reserve['price'];
            $data[] = $this->formatOperation($reserve, self::OPERATION_PAY, $price);
        }
        
        
        /**
         * Формируем список сделок, в которых заказчику возвращена сумма - 
         * автоматически успешно возвращена после арбитража. Тут в том числе 
         * учитываем и те сделки, в которых изначально возврат оканчивается 
         * ошибкой платежа, но заказчик нажимает "Повторить выплату", и возврат 
         * успешно проводится. 
         * Фильтруем по дате осуществления возврата
         */
        $sql5 = sprintf($query, 
                'ra.date_close', 
                ', ra.id AS arbitrage_id, ra.price AS arbitrage_price',
                'INNER JOIN reserves_payback rp ON rp.reserve_id = r.id
                 INNER JOIN reserves_arbitrage ra ON ra.reserve_id = r.id', 
                'ra.date_close > ? AND ra.date_close < ? AND r.status_back = ?i AND ra.price < r.price');
        //Допускается, что финансы исполнителя могут быть незаполненными
        $sql5 = str_replace('INNER JOIN sbr_reqv srf', 'LEFT JOIN sbr_reqv srf', $sql5);
        $reserves5 = $this->db()->rows($sql5, $this->start, $this->end, ReservesModel::SUBSTATUS_PAYED);
        foreach ($reserves5  as $reserve) {
            $this->addUid($reserve['emp_id']);
            $this->addUid($reserve['frl_id']);
            $price = $reserve['price'] - $reserve['arbitrage_price'];
            $data[] = $this->formatOperation($reserve, self::OPERATION_BACK, $price);
        }


        /**
         * Формируем список сделок, в которых инициировали возврат - автоматически 
         * подан запрос на возврат суммы на банковский счет. Тут в том числе 
         * учитываем и те сделки, в которых выставляется повторный запрос на возврат 
         * суммы на банковский счет. 
         * Фильтруем по дате формирования запроса на возврат банковским переводом
         */
        $sql6 = sprintf($query, 
                'ra.date_close', 
                ', ra.id AS arbitrage_id, ra.price AS arbitrage_price',
                'INNER JOIN reserves_arbitrage ra ON ra.reserve_id = r.id', 
                'ra.date_close > ? AND ra.date_close < ? AND r.status_back IN (?l) AND ra.price < r.price');
        //Допускается, что финансы исполнителя могут быть незаполненными
        $sql6 = str_replace('INNER JOIN sbr_reqv srf', 'LEFT JOIN sbr_reqv srf', $sql6);
        $reserves6 = $this->db()->rows($sql6, $this->start, $this->end, 
                array(ReservesModel::SUBSTATUS_NEW, ReservesModel::SUBSTATUS_INPROGRESS));
        foreach ($reserves6  as $reserve) {
            $this->addUid($reserve['emp_id']);
            $this->addUid($reserve['frl_id']);
            $price = $reserve['price'] - $reserve['arbitrage_price'];
            $data[] = $this->formatOperation($reserve, self::OPERATION_BACK, $price);
        }
        
        return $data;
    }
    
    /**
     * Приводит статусы к нужным значениям
     */
    private function overrideStatus($status) {
        return $status == sbr::FT_JURI ? 1 : 0;
    }
    
    /**
     * Получает информацию о пользователях, участвующих в сделках
     * @return array
     */
    private function getUsers() {
        $sql = "SELECT u.uid, u.login, u.email
            FROM users u
            WHERE u.uid IN (?l)";
        $users = $this->db()->rows($sql, $this->uids);
        
        $form_types = sbr_meta::$types;
        
        $data1 = $data4 = array();
        
        foreach ($users as $user) {
            
            $user_reqvs = sbr_meta::getUserReqvs($user['uid']);
            $reqvs = $user_reqvs[$user_reqvs['form_type']];
            
            $fio = explode(' ', $reqvs['fio']);
            if (!isset($fio[0])) $fio[0] = '';
            if (!isset($fio[1])) $fio[1] = '';
            if (!isset($fio[2])) $fio[2] = '';
            if (count($fio > 3)) {
                $max = count($fio) - 2;
                $fio[2] = implode(' ', array_slice($fio, 2, $max));
            }
            
            $is_phis = $user_reqvs['form_type'] == sbr::FT_PHYS;
            $is_rus = in_array($user_reqvs['rez_type'], array(sbr::RT_RU, sbr::RT_REFUGEE, sbr::RT_RESIDENCE));
            
            if (!$is_rus) {
                $this->foreign_logins[] = $user['login'];
            }
            
            
            $q = $reqvs['type']==sbr_meta::TYPE_IP ? '' : '"';
            $name_corp = $is_phis ? '' : ($is_rus ? $form_types[(int)$reqvs['type']].' '.$q.$reqvs['full_name'].$q : $reqvs['full_name']);
            
            if ($is_phis && $user_reqvs['validate_status'] != sbr_meta::VALIDATE_STATUS_OK) {
                $data1[] = array(
                    'id_contragent' => (!$is_phis && $is_rus) ? $reqvs['inn'] : $user['login'],
                    'login' => $user['login'],
                    'i_name' => '',
                    'o_name' => '',
                    'f_name' => '',
                    'country' => 'Россия',
                    'status' => 0,
                    'ser_pass' => '',
                    'num_pass' => '',
                    'date_pass' => '',
                    'org_pass' => '',
                    'reg_addr' => '',
                    'post_addr' => '',
                    'name_comp' => '',
                    'short_name' => '',
                    'inn_comp' => '',
                    'kpp_comp' => '',
                    'pay_nds' => 0,
                    'rs_bank' => '',
                    'bik_bank' => '',
                    'name_bank' => '',
                    'ks_bank' => '',
                    'name_ubank' => '',
                    'ks_ubank' => '',
                    'inn_ubank' => '',
                    'bik_ubank' => ''
                );
            } else {
                $data1[] = array(
                    'id_contragent' => (!$is_phis && $is_rus) ? $reqvs['inn'] : $user['login'],
                    'login' => $user['login'],
                    'i_name' => $fio[1],
                    'o_name' => $fio[2],
                    'f_name' => $fio[0],
                    'country' => ($is_rus ? 'Россия' : $reqvs['country']),
                    'status' => ($is_phis ? 0 : 1),
                    'ser_pass' => ($is_phis ? $reqvs['idcard_ser'] : ''),
                    'num_pass' => ($is_phis ? $reqvs['idcard'] : ''),
                    'date_pass' => ($is_phis ? $reqvs['idcard_from'] : ''),
                    'org_pass' => ($is_phis ? $reqvs['idcard_by'] : ''),
                    'reg_addr' => ($is_phis ? $reqvs['address_reg'] : $reqvs['address_jry']),
                    'post_addr' => $reqvs['address'],
                    'name_comp' => $name_corp,
                    'short_name' => $reqvs['full_name'],
                    'inn_comp' => ($is_phis ? '' : ($is_rus ? $reqvs['inn'] : $reqvs['rnn'])),
                    'kpp_comp' => ($is_phis || !$is_rus ? '' : $reqvs['kpp']),
                    'pay_nds' => ($is_phis || !$is_rus ? 0 : 1),
                    'rs_bank' => ($is_phis && $is_rus ? '' : $reqvs['bank_rs']),
                    'bik_bank' => ($is_phis || !$is_rus ? '' : $reqvs['bank_bik']),
                    'name_bank' => ($is_phis && $is_rus ? '' : $reqvs['bank_name']),
                    'ks_bank' => ($is_phis || !$is_rus ? '' : $reqvs['bank_ks']),
                    'name_ubank' => ($is_rus ? '' : $reqvs['bank_rf_name']),
                    'ks_ubank' => ($is_rus ? '' : $reqvs['bank_rf_ks']),
                    'inn_ubank' => ($is_rus ? '' : $reqvs['bank_rf_inn']),
                    'bik_ubank' => ($is_rus ? '' : $reqvs['bank_rf_bik'])
                );
            }
            
            
            
            $data4[] = array(
                'login' => $user['login'],
                'i_name' => $fio[1],
                'o_name' => $fio[2],
                'f_name' => $fio[0],
                'phone' => $reqvs['mob_phone'],
                'email' => $user['email']
            );
        }
        
        return array(1 => $data1, 4 => $data4);
    }
    
    /**
     * Записывает данные реестра в файл и возвращает его название в случае успеха.
     * @param type $data Данные для записи
     * @param type $num Номер реестра
     */
    protected function writeFile($data, $num=1) {
        $filename = $this->file_prefix.'_'.$num.'_'.strtotime($this->start) .'-'. strtotime($this->end).'.csv';
        $handle = fopen($this->temp_file_dir . $filename, 'w');
        
        $fields_exclude = isset($this->extra_fields[$num]) ? $this->extra_fields[$num] : array();
        
        $headers = true;
        foreach ($data as $line) {
            foreach ($fields_exclude as $field) {
                unset($line[$field]);
            }
            
            if ($headers) {
                fputs($handle, implode(array_keys($line), ';')."\n");
                $headers = false;
            }
            foreach ($line as &$text) {
                $text = html_entity_decode($text);
                $text = str_replace(';', ',', $text);
            }
            fputs($handle, implode($line, ';')."\n");
        }
        fclose($handle);
        
        if ($this->uploadFile($filename)) {
            return $filename;
        }
        return false;        
    }
    
    private function uploadFile($filename) {
        $file = array(
          'tmp_name' => $this->temp_file_dir . $filename,
           'size' => filesize($this->temp_file_dir . $filename),
            'name' => $filename
        );
        $cf = new CFile($file, $this->TABLE);
		if ($cf) {
			$cf->server_root = true;
			$cf->max_size = 104857600; //100Mb
			if($filename = $cf->MoveUploadedFile($this->path, true, $filename)) {
				return $filename;
			}
		}
        return false;
    }


    /**
     * При необходимости добавляет пользователя в список
     * @param type $uid
     */
    private function addUid($uid) 
    {
        if (!in_array($uid, $this->uids))
            $this->uids[] = $uid;
    }
    
    /**
     * Сохраняет загруженный файл
     * @return boolean
     */
    public function saveUploadedFile($input = 'reestr3', $allowed_ext = array()) 
    {
        if (!isset($_FILES[$input])) {
            return false;
        }

        $cf = new CFile($_FILES[$input], $this->TABLE);
        
		if ($cf) {
            $cf->allowed_ext = is_array($allowed_ext)?$allowed_ext:array($allowed_ext);
			$cf->server_root = true;
			$cf->max_size = 104857600; //100Mb
			if ($filename = $cf->MoveUploadedFile($this->path)) {
				return $filename;
			}
		}
        
        return false;
    }
    
    /**
     * Читает загруженный файл и обновляет данные резерва
     * @param string $file
     */
    public function parseFile($file) {
        $uri = WDCPREFIX_LOCAL.$this->path . $file;
        $list = array();
        $handle = fopen($uri, 'r');
		while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
			if ($data[0] == 'order_id' || count($data) != 5) {
				continue;
			}
            
            $dateObject = DateTime::createFromFormat('d.m.Y H:i', $data[3]);
            
			$res = array(
				'order_id' => $this->getOrderId($data[0]), //номер сделки
				'pay_type' => (int)$data[1], //тип платежа (0 – резервирование, 1 – выплата исполнителю, 2 – возврат заказчику)
				'pay_sum' => (float)$data[2], //сумма платежа
				'pay_date' => $dateObject->getTimestamp(), //дата и время проведения платежа в 1С
				'pay_status' => (int)$data[4] //статус платежа (0 – не проведен, 1 – проведен, 2, 3 – не проведен)
			);
            $list[$res['order_id']][$res['pay_type']] = $res['pay_status'];
		}
        fclose($handle);
        
        $employer = new employer();
        foreach($list as $oid=>$operations) {
            $reserveModel = ReservesModelFactory::getInstance(ReservesModelFactory::TYPE_TSERVICE_ORDER);
            $reserveData = $reserveModel->getReserve($oid);
            if(!$reserveData) continue;
            
            
            foreach ($operations as $type =>$status) {
                switch ($type) {
                    case self::OPERATION_RESERVE:
                        $new_status = $status == self::STATUS_OK 
                            ? $reserveModel::STATUS_RESERVE 
                            : $reserveModel::STATUS_ERR;
                        $reserveModel->changeStatus($new_status);
                        break;
                    
                    case self::OPERATION_PAY:
                        $new_status = $status == self::STATUS_OK 
                            ? $reserveModel::SUBSTATUS_PAYED 
                            : $reserveModel::SUBSTATUS_ERR;
                        $reserveModel->changePayStatus($new_status);
                        break;
                    
                    case self::OPERATION_BACK:
                        $new_status = $status == self::STATUS_OK 
                            ? $reserveModel::SUBSTATUS_PAYED 
                            : $reserveModel::SUBSTATUS_ERR;
                        $reserveModel->changeBackStatus($new_status);
                        break;
                }
                
            }
        }
		return $list;
    }
    
    protected function getCompanyName($type, $name, $is_phis = false)
    {
        $company_name = '';
        
        if (!$is_phis) {
            if ($type === null) {
                return $name;
            } elseif ($type != sbr_meta::TYPE_IP) {
                $name = ' «' . $name . '»';
            }

            $form_types = sbr_meta::$types;
            $company_name = $form_types[(int)$type] . $name;
        }

        return $company_name;
    }
        
}
