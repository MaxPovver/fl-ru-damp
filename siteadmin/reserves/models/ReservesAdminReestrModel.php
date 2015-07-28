<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesAdmin.php');
require_once(__DIR__ . '/../models/ReservesAdminBankReportGeneratorModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DocGen/DocGenReserves.php');

class ReservesAdminReestrModel extends ReservesAdmin
{
    const MODE_REESTRES = 'operations';
    const MODE_DOCUMENTS = 'documents';
    const MODE_NDFL = 'ndfl';
    
    const TEMPLATE_PROFILE = "<a target='_blank' href='/users/%s/setup/finance/'>%s %s %s [%s]</a>";    
    
    private $menu = array(
        self::MODE_REESTRES => 'Сделки',
        self::MODE_DOCUMENTS => 'Документы',
        self::MODE_NDFL => 'НДФЛ'
    );
    
    private $fields = array(
        self::MODE_REESTRES => array(
            'order_id' => array(
                'name' => '№ дог.', 
                'width' => 60
            ),
            'emp_login' => array(
                'name' => 'Логин заказчика', 
                'width' => 60
            ),
            'emp_id' => array(
                'name' => 'ИД заказчика', 
                'width' => 60
            ),
            'emp_status' => array(
                'name' => 'Статус заказчика', 
                'width' => 60
            ),
            'frl_login' => array(
                'name' => 'Логин исполнителя', 
                'width' => 60
            ),
            'frl_id' => array(
                'name' => 'ИД исполнителя', 
                'width' => 60
            ),
            'frl_status' => array(
                'name' => 'Статус исполнителя', 
                'width' => 60
            ),
            'pay_type' => array(
                'name' => 'Тип платежа', 
                'width' => 60
            ),
            'pay_sum' => array(
                'name' => 'Сумма оплаты', 
                'width' => 60
            ),
            'ndfl_sum' => array(
                'name' => 'НДФЛ', 
                'width' => 60
            ),
            'pay_kind' => array(
                'name' => 'Способ оплаты', 
                'width' => 60
            ),
            'pay_date' => array(
                'name' => 'Дата платежа', 
                'width' => 60
            )
        ),
        self::MODE_DOCUMENTS => array(
            'bs_id' => array(
                'name' => '№ сделки', 
                'width' => 60
            ),
            'profile' => array(
                'name' => 'Фамилия Имя Отчество [логин]', 
                'width' => 120
            ),
            'comp_name' => array(
                'name' => 'Наименование компании', 
                'width' => 300
            ),
            'post_addr' => array(
                'name' => 'Почтовый адрес', 
                'width' => 250
            )
        ), 
        self::MODE_NDFL => array(
            'id_contragent' => array(
                'name' => 'ИД',
                'width' => 60
            ),
            'login' => array(
                'name' => 'Логин',
                'width' => 60
            ),
            'i_name' => array(
                'name' => 'Имя',
                'width' => 80
            ),
            'o_name' => array(
                'name' => 'Отчество',
                'width' => 80
            ),
            'f_name' => array(
                'name' => 'Фамилия',
                'width' => 80
            ),
            'country_name' => array(
                'name' => 'Страна',
                'width' => 60
            ),
            'ser_pass' => array(
                'name' => 'Серия паспорта',
                'width' => 50
            ),
            'num_pass' => array(
                'name' => 'Номер паспорта',
                'width' => 60
            ),
            'date_pass' => array(
                'name' => 'Дата выдачи',
                'width' => 60
            ),
            'org_pass' => array(
                'name' => 'Кем выдан',
                'width' => 120
            ),
            'reg_addr' => array(
                'name' => 'Адрес',
                'width' => 180
            ),
            'dr' => array(
                'name' => 'День рождения',
                'width' => 60
            )
        )
    );
    
    private $mode;
    
    private $generated_file_name;
    
    public function __construct($mode)
    {
        if (!in_array($mode, array_keys($this->menu))) {
            $mode = self::MODE_REESTRES;
        }
        $this->mode = $mode;
        
        parent::__construct();
    }
    
    /**
     * Возвращает массив полей таблицы
     */
    public function getFields()
    {
        if (isset($this->fields[$this->mode])) {
            return $this->fields[$this->mode];
        }
        
        return false;
    }
    
    public function generateReestrs($data)
    {
        if (!$this->initDates($data)) {
            return false;
        }
        
        $this->createPayoutSummary();
        
        return $this->exportReservesToCSV(
                $data['date_start'], 
                $data['time_start'], 
                $data['date_end'], 
                $data['time_end']
            );
    }
    
    private function createPayoutSummary()
    {
        $sql_payout = "SELECT (CASE WHEN ra.id IS NULL THEN r.price ELSE ra.price END) AS price, r.src_id AS order_id,
            (CASE WHEN sre.form_type = 1 THEN sre._1_fio ELSE sre._2_fio END) AS emp_fio, 
            (CASE WHEN srf.form_type = 1 THEN srf._1_fio ELSE srf._2_fio END) AS frl_fio,
            fro.path, fro.fname, r.frl_id, frl.email 
            FROM reserves r
            INNER JOIN reserves_payout_reqv rpr ON r.id = rpr.reserve_id
            INNER JOIN sbr_reqv sre ON sre.user_id = r.emp_id
            INNER JOIN sbr_reqv srf ON srf.user_id = r.frl_id
            LEFT JOIN file_reserves_order fro ON r.src_id = fro.src_id AND fro.doc_type = ?i
            LEFT JOIN reserves_arbitrage ra ON ra.reserve_id = r.id
            INNER JOIN freelancer frl ON frl.uid = r.frl_id
            WHERE rpr.pay_type = 'bank'
            AND r.status IN (?l)
            AND rpr.date > ? AND rpr.date < ?;";

        $data_payments = $this->db()->rows($sql_payout, 
                DocGenReserves::LETTER_FRL_TYPE, 
                array(ReservesModel::STATUS_PAYED, ReservesModel::STATUS_ARBITRAGE),
                $this->start, $this->end);
        
        $sql_back = "SELECT r.src_id AS order_id, r.emp_id,
            (r.price - ra.price) AS price,
            (CASE WHEN sre.form_type = 1 THEN sre._1_fio ELSE sre._2_fio END) AS emp_fio, 
            (CASE WHEN srf.form_type = 1 THEN srf._1_fio ELSE srf._2_fio END) AS frl_fio,
            fro.path, fro.fname, emp.email
            FROM reserves r
            INNER JOIN reserves_bank rb ON r.id = rb.reserve_id
            INNER JOIN reserves_arbitrage ra ON r.id = ra.reserve_id
            INNER JOIN sbr_reqv sre ON sre.user_id = r.emp_id
            LEFT JOIN sbr_reqv srf ON srf.user_id = r.frl_id
            INNER JOIN employer emp ON emp.uid = r.emp_id
            LEFT JOIN file_reserves_order fro ON r.src_id = fro.src_id AND fro.doc_type = ?i
            WHERE r.status_back > 1
            AND r.invoice_id IS NULL
            AND ra.date_close > ? AND ra.date_close < ?;";
        
        $data_payback = $this->db()->rows($sql_back, 
                DocGenReserves::ARBITRAGE_REPORT_TYPE, $this->start, $this->end);
    
        $reportGenerator = new ReservesAdminBankReportGeneratorModel();
        $this->generated_file_name = $reportGenerator->generate2($data_payments, $data_payback);
    }
    
    
    public function getReestr3()
    {
        return $this->reestr3;
    }
    
    public function format($value, $type)
    {
        switch ($type) {
            case 'pay_type':
                $data = self::getPayTypes();
                $value = $data[$value];
                break;
            
            case 'emp_status':
            case 'frl_status':
                $value = $value ? 'ЮЛ' : 'ФЛ';
                break;

            case 'bs_id':
                $order_name = $this->formatOrderName($value);
                $value = '<a target="_blank" href="?action=details&num='.$value.'">'.$order_name.'</a>';
                break;
            
            case 'post_addr':
                
                $message = array();
                $message_txt = null;
                $error_class = 'b-txt_color_darkorange';
                $res = parseAddress($value);
                
                if ($res) {
                    if (!$res['index']) {
                        $message[] = 'индекс';
                    }
                    
                    if (!$res['country_id']) {
                        $message[] = 'страну';
                    }
                    
                    if (!$res['city_id']) {
                        $message[] = 'город';
                    }                    
                }
                
                if (!$res || !empty($message)) {
                    if (!$res || count($message) == 3) {
                        $message_txt = 'Не удалось разобрать адрес.';
                        $error_class = 'b-txt_color_red';
                    } else {
                        $message_txt = sprintf('Не удалось разобрать: %s.', implode(', ', $message));
                    }
                }
                
                if ($message_txt) {
                    $value = "
                        {$value}</br></br>
                        <span class=\"b-txt b-txt_fs_11 {$error_class}\">{$message_txt}</span>
                    ";                    
                }
                
                break;
            
            
            default:
                break;
        }
        return $value;
    }
    
    public static function getPayTypes()
    {
        return array(
            self::OPERATION_RESERVE => 'Резервирование',
            self::OPERATION_PAY => 'Выплата',
            self::OPERATION_BACK => 'Возврат',
            self::OPERATION_TAX => 'Комиссия'
        );
    }
    
    public function getSummary()
    {
        $reestr = $this->getReestr3();
        
        if (!$reestr) {
            return false;
        }
        
        $summary = array(
            'sum_reserve_yk' => 0,
            'sum_pay_yk' => 0,
            'sum_pay_yk_norez' => 0,
            'sum_back_yk' => 0
        );
        foreach ($reestr as $operation) {
            if ($operation['pay_kind'] != self::PAY_KIND_YK) {
                continue;
            }
            
            switch ($operation['pay_type']) {
                case self::OPERATION_RESERVE:
                    $summary['sum_reserve_yk'] += $operation['reserve_price'];
                    break;

                case self::OPERATION_PAY:
                    $summary['sum_pay_yk'] += $operation['pay_sum'];
                    
                    if (in_array($operation['frl_login'], $this->foreign_logins)) {
                        $summary['sum_pay_yk_norez'] += $operation['pay_sum'];
                    }
                    
                    break;

                case self::OPERATION_BACK:
                    $summary['sum_back_yk'] += $operation['pay_sum'];
                    break;

                default:
                    break;
            }
        }
        
        return $summary;
    }
    
    public function getDocuments($data)
    {
        if (!$this->initDates($data)) {
            return false;
        }
        
        $sql = "SELECT r.src_id AS bs_id, 
            split_part(rb.fio, ' ', 1) as f_name, 
            split_part(rb.fio, ' ', 2) as i_name, 
            split_part(rb.fio, ' ', 3) as o_name, 
            e.login, rb.type, rb.full_name AS comp_name, rb.address AS post_addr

            FROM reserves AS r
            INNER JOIN employer e ON e.uid = r.emp_id
            INNER JOIN reserves_bank rb ON rb.reserve_id = r.id

            WHERE r.status IN (?l) AND rb.is_send_docs = TRUE
            AND r.date_complete > ? AND r.date_complete < ?";
        
        $rows = $this->db()->rows($sql, 
                array(ReservesModel::STATUS_PAYED, ReservesModel::STATUS_ARBITRAGE), 
                $this->start, 
                $this->end);
        
        
        
        foreach ($rows as &$row) {
            $row['comp_name'] = $this->getCompanyName($row['type'], $row['comp_name']);
            unset($row['type']);
        }
        
        $this->saveDocsReestr($rows);
        
        foreach ($rows as &$row) {
            $row['profile'] = sprintf(self::TEMPLATE_PROFILE, 
                    $row['login'], 
                    $row['f_name'], 
                    $row['i_name'], 
                    $row['o_name'], 
                    $row['login']);
        }
        
        return $rows;
        
    }
    
    private function saveDocsReestr($data)
    {
        $this->file_prefix = 'docs';
        $this->generated_file_name = $this->writeFile($data);
    }
    
    public function getNdfl($data)
    {
        if (!$this->initDates($data)) {
            return false;
        }
        
        /*
        $sql = "SELECT
            DISTINCT ON (u.uid)
            u.login AS id_contragent, 
            u.login AS login, 
            split_part(sr._1_fio, ' ', 2) AS i_name,
            split_part(sr._1_fio , ' ', 3) AS o_name,
            split_part(sr._1_fio, ' ', 1) AS f_name,
            (CASE WHEN u.country > 0 THEN c.country_name ELSE 'Россия' END) AS country_name,
            0 AS status,
            sr._1_idcard_ser AS ser_pass,
            sr._1_idcard AS num_pass,
            to_char(sr._1_idcard_from, 'DD.MM.YYYY') AS date_pass,
            sr._1_idcard_by AS org_pass,
            sr._1_address_reg AS reg_addr,
            '' AS inn_comp,
            to_char(sr._1_birthday, 'DD.MM.YYYY') AS DR

        FROM freelancer AS u
        INNER JOIN sbr_reqv AS sr ON sr.user_id = u.uid AND sr.form_type = ?i AND sr.rez_type = ?i
        INNER JOIN reserves AS r ON (r.frl_id = u.uid AND r.status_pay > ?i AND r.status_pay != ?i)
        INNER JOIN country AS c ON c.id = u.country
        WHERE r.date_complete > ? AND r.date_complete < ?
        ;";
        
        $rows = $this->db()->rows($sql, 
                sbr::FT_PHYS,
                sbr::RT_RU,
                ReservesModel::SUBSTATUS_NONE,
                ReservesModel::SUBSTATUS_ERR, 
                $this->start, 
                $this->end);        
        */
        
        
        $sql = "
            SELECT 
                DISTINCT ON (r.frl_id)
                u.login AS id_contragent, 
                u.login AS login, 
                rpr.fields
            FROM reserves AS r
            INNER JOIN reserves_payout_reqv AS rpr ON rpr.reserve_id = r.id AND rpr.pay_type != 'bank' 
            INNER JOIN freelancer AS u ON u.uid = r.frl_id
            WHERE 
                r.status_pay = ?i AND 
                r.date_complete > ? AND r.date_complete < ?
        ";
        
        
        $rows = $this->db()->rows($sql, 
                ReservesModel::SUBSTATUS_PAYED,
                $this->start, 
                $this->end);
        
        $data = array();
        
        if ($rows) {
            foreach ($rows as $row) {
                
                if (!$row['fields']) {
                    continue;
                }
                
                $fields = mb_unserialize($row['fields']);
                $is_rt_ru = isset($fields['rez_type']) && in_array($fields['rez_type'], array(sbr::RT_RU, sbr::RT_REFUGEE, sbr::RT_RESIDENCE));
                $is_ft_phys = isset($fields['form_type']) && $fields['form_type'] == sbr::FT_PHYS;
                
                if(!$is_rt_ru || !$is_ft_phys) {
                    continue;
                }
                
                $name = explode(' ', $fields['fio']);
                
                $data[] = array(
                    'id_contragent' => $row['id_contragent'],
                    'login' => $row['login'],
                    'i_name' => @$name[1],
                    'o_name' => @$name[2],
                    'f_name' => @$name[0],
                    'country_name' => empty($fields['country'])?'Россия':$fields['country'],
                    'status' => 0,
                    'ser_pass' => $fields['idcard_ser'],
                    'num_pass' => $fields['idcard'],
                    'date_pass' => $fields['idcard_from'],
                    'org_pass' => $fields['idcard_by'],
                    'reg_addr' => $fields['address_reg'],
                    'inn_comp' => '',
                    'dr' => $fields['birthday']
                );
            }
            
            $this->saveNdflReestr($data);
        }

        return $data;
        
    }
    
    private function saveNdflReestr($data)
    {
        $this->file_prefix = 'ndfl';
        $this->generated_file_name = $this->writeFile($data);
    }
    
    private function initDates($data)
    {
        if (!isset($data['date_start']) || !isset($data['date_end']) 
                || !isset($data['time_start']) || !isset($data['time_end'])) {
            return false;
        }
        
        $this->prepareTime($data['date_start'], $data['time_start'], 
                $data['date_end'], $data['time_end']);
        
        if (!$this->start || !$this->end) {
            return false;
        }
        
        return true;
    }
    
    public function isDocMode()
    {
        return self::MODE_DOCUMENTS === $this->getMode();
    }

    public function getMode()
    {
        return $this->mode;
    }
    
    public function getMenu()
    {
        return $this->menu;
    }
    
    public function getSubmenu()
    {
        $menu = array();
        switch ($this->mode) {
            case self::MODE_REESTRES:
                if ($this->reestr1_filename && $this->reestr1_filename && $this->reestr1_filename){
                    $menu = array(
                        array(
                            'link' => WDCPREFIX . $this->path . $this->reestr1_filename,
                            'anchor' => 'Реестр 1'
                        ),
                        array(
                            'link' => WDCPREFIX . $this->path . $this->reestr2_filename,
                            'anchor' => 'Реестр 2'
                        ),
                        array(
                            'link' => WDCPREFIX . $this->path . $this->reestr3_filename,
                            'anchor' => 'Реестр 3'
                        )
                    );
                }
                if ($this->generated_file_name) {
                    $menu[] = array(
                        'link' => WDCPREFIX . $this->path_payout . $this->generated_file_name,
                        'anchor' => 'Отчет по выплатам'
                    );
                }
                break;
            
            case self::MODE_DOCUMENTS:
                if ($this->generated_file_name) {
                    $menu[] = array(
                        'link' => WDCPREFIX . $this->path . $this->generated_file_name,
                        'anchor' => 'Реестр документов'
                    );
                }
                break;
            
            case self::MODE_NDFL:
                if ($this->generated_file_name) {
                    $menu[] = array(
                        'link' => WDCPREFIX . $this->path . $this->generated_file_name,
                        'anchor' => 'Реестр НДФЛ'
                    );
                    
                    $menu[] = array(
                        'link' => 'https://dadata.ru/#!process_form_from_file',
                        'anchor' => 'Перейти в DaData'
                    );
                }
                break;

            default:
                break;
        }
        return $menu;
    }

}