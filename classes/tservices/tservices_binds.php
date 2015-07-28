<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/atservices_model.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_categories.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/op_codes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/billing.php');

/**
 * Закрепления услуг в каталогах
 *
 * @author danil
 */
class tservices_binds extends atservices_model 
{
    
    private $TABLE = 'tservices_binds';
    private $TABLE_TSERVICES = 'tservices';
    
    static $_TABLE = 'tservices_binds';
    static $_TABLE_TSERVICES = 'tservices';
    static $_TABLE_FREELANCER = 'freelancer';         




    /**
     * Типы закреплений
     */
    const KIND_LANDING = 1;
    const KIND_ROOT = 2;
    const KIND_GROUP = 3;
    const KIND_SPEC = 4;
    
    const DESCR = 'Закрепление услуги %d %s';
    const DESCR_PROLONG = 'Продление закрепления услуги %d %s';
    const DESCR_UP = 'Поднятие услуги %d на 1 место %s';
    const COMMENT = 'до %s';
    
    const OP_CODE_LANDING = 155;
    const OP_CODE_ROOT = 156;
    const OP_CODE_GROUP = 157;
    const OP_CODE_SPEC = 158;
    
    const OP_CODE_UP_LANDING = 159;
    const OP_CODE_UP_ROOT = 160;
    const OP_CODE_UP_GROUP = 161;
    const OP_CODE_UP_SPEC = 162;
    const OP_CODE_UP_BUFFER = 193;    
    
    private $_kind_available = array(
        self::KIND_LANDING,
        self::KIND_ROOT,
        self::KIND_GROUP,
        self::KIND_SPEC
    );
    
    static $_kinds = array(
        self::KIND_LANDING,
        self::KIND_ROOT,
        self::KIND_GROUP,
        self::KIND_SPEC    
    );


    /**
     * Стоимость размещения
     */
    private $prices = array(
        self::KIND_LANDING => 7000,
        self::KIND_ROOT => 5000,
        self::KIND_GROUP => 3000,
        self::KIND_SPEC => 1000
    );
    
    /**
     * Стоимость поднятия
     */
    private $prices_up = array(
        self::KIND_LANDING => 1400,
        self::KIND_ROOT => 1000,
        self::KIND_GROUP => 600,
        self::KIND_SPEC => 200
    );
    
    /**
     * Коды операций покупки закреплений/продления (таблица op_codes)
     */
    private $op_codes = array(
        self::KIND_LANDING => self::OP_CODE_LANDING,
        self::KIND_ROOT => self::OP_CODE_ROOT,
        self::KIND_GROUP => self::OP_CODE_GROUP,
        self::KIND_SPEC => self::OP_CODE_SPEC
    );
    
    /**
     * Коды операций поднятия закреплений (таблица op_codes)
     */
    private $op_codes_up = array(
        self::KIND_LANDING => self::OP_CODE_UP_LANDING,
        self::KIND_ROOT => self::OP_CODE_UP_ROOT,
        self::KIND_GROUP => self::OP_CODE_UP_GROUP,
        self::KIND_SPEC => self::OP_CODE_UP_SPEC
    );
    
    private $op_codes_groups = array(
        array(self::OP_CODE_LANDING, self::OP_CODE_UP_LANDING),
        array(self::OP_CODE_ROOT, self::OP_CODE_UP_ROOT),
        array(self::OP_CODE_GROUP, self::OP_CODE_UP_GROUP),
        array(self::OP_CODE_SPEC, self::OP_CODE_UP_SPEC)
    );
    
    /**
     * Тип размещения
     * @var int 
     */
    private $kind;
    
    /**
     * @var int ИД записи, с которой будем работать
     */
    private $id;
    
    /**
     * Данные для вставки
     * @var type 
     */
    private $bind_data = array();

    /**
     * Данные для операции
     * @var type 
     */
    public $bind_info = array();
    
    
    /**
     * Обьекты биллинга для пользователей
     * 
     * @var type 
     */
    static protected $_bills = array();


    /**
     * Получить обьект биллинга для указанного пользователя
     * 
     * @param type $uid
     * @return type
     */
    static protected function getBilling($uid)
    {
        if (!isset(self::$_bills[$uid])) {
            self::$_bills[$uid] = new billing($uid);
        }
        
        return self::$_bills[$uid];
    }
    
    
    
    public function __construct($kind = 1)
    {
        if (!in_array($kind, $this->_kind_available)) {
            $kind = 1;
        }
        $this->kind = $kind;
    }

    
    public function setKindByOpCode($op_code)
    {
        $kind = array_search($op_code, $this->op_codes);
        
        if (!$kind) {
            $kind = array_search($op_code, $this->op_codes_up);
        }
        
        if ($kind) {
            $this->kind = $kind;
        } else {
            $kind = 1;
        }
    }

    
    /**
     * Формирует строку с названием раздела, где будет закрепление
     * @param type $kind
     * @return string
     */
    public function getProfessionText($for_history = false, $prof_id = 0)
    {
        if (in_array($this->kind, array(tservices_binds::KIND_GROUP, tservices_binds::KIND_SPEC))) {
            $tservices_categories = new tservices_categories();
            $category = $tservices_categories->getCategoryById($prof_id);
            
            $part_name = '';
            if ($this->kind == tservices_binds::KIND_SPEC) {
                $cat_group = $tservices_categories->getCategoryById($category['parent_id']);
                $part_name .= $cat_group['title'] . ' &mdash; '; 
            } 
            $part_name .= $category['title'];
            
            if ($for_history) {
                return 'в разделе ' . $part_name . ' каталога ТУ';
            } else {
                return $part_name;
            }
        } elseif ($for_history) {
            if ($this->kind == tservices_binds::KIND_LANDING) {
                return 'на главной странице';
            } elseif ($this->kind == tservices_binds::KIND_ROOT) {
                return 'в общем разделе каталога ТУ';
            }
        } else {
            if ($this->kind == tservices_binds::KIND_LANDING) {
                return 'Главная страница';
            } elseif ($this->kind == tservices_binds::KIND_ROOT) {
                return 'Общий раздел каталога';
            }
        }
    }
    
    /**
     * Возвращает стоимость покупки или поднятия
     * @return type
     */
    public function getPrice($up = false, $uid = 0, $prof_id = null)
    {
        //кеш для нескольких вызовов в течении сесии
        static $_cache = array();
        $key = md5(print_r(func_get_args(), true));
        
        if(isset($_cache[$key])) {
            return $_cache[$key];
        }
        
        $opCode = $this->getOpCode($up);
        
        //Пробуем получить скидку для конкретного пользователя
        if ($uid > 0) {
            $bill = self::getBilling($uid);
            $opCode = $bill->getDiscountOpCode($opCode);
        }
        
        $data = op_codes::getDataByOpCode($opCode, $prof_id);
        
        if (@$data['sum']) {
            $_cache[$key] = @$data['sum']; 
        }
        
        return @$data['sum'];
    }
    
    /**
     * Код операции (таблица op_codes)
     * @param type $up
     * @return type
     */
    public function getOpCode($up = false)
    {
        return $up
            ? $this->op_codes_up[$this->kind]
            : $this->op_codes[$this->kind];
    }
    
    /**
     * Доступно ли создание закрепления
     * @param type $uid
     * @param type $tservice_id
     * @param type $kind
     * @param type $prof_id
     * @return type
     */
    public function isAllowBind($uid, $tservice_id, $kind, $prof_id = 0)
    {
        $sql = "SELECT id FROM {$this->TABLE} WHERE user_id = ?i AND tservice_id = ?i AND kind = ?i AND prof_id = ?i AND date_stop > now()";
        
        $query  = $this->db()->parse($sql, $uid, $tservice_id, $kind, $prof_id);

        $id = $this->db()->val($query);
        
        return !($id > 0);
    }
    
    /**
     * Возвращает данные по конкретному закреплению
     * @param type $user_id
     * @param type $tservice_id
     * @param type $prof_id
     */
    public function getItem($user_id, $tservice_id, $prof_id)
    {
        $sql = "SELECT * FROM {$this->TABLE} WHERE user_id = ?i AND tservice_id = ?i AND kind = ?i AND prof_id = ?i AND date_stop > now() LIMIT 1;";
        
        $query  = $this->db()->parse($sql, $user_id, $tservice_id, $this->kind, $prof_id);

        return $this->db()->row($query);
    }
    
    /**
     * Возвращает данные по конкретному закреплению по ID
     * @param type $id
     */
    public function getItemById($id)
    {
        $sql = "SELECT * FROM {$this->TABLE} WHERE id = ?i AND date_stop > now() LIMIT 1;";
        
        $query  = $this->db()->parse($sql, (int)$id);

        return $this->db()->row($query);
    }
    
    /**
     * Имеет ли пользователь закрепленные услуги в текущем разделе
     * @param type $uid
     */
    public function countBindedTu($uid, $prof_id)
    {
        $sql = "SELECT COUNT(id) FROM {$this->TABLE} WHERE kind = ?i AND user_id = ?i AND prof_id = ?i AND date_stop > now();";
        return $this->db()->val($sql, $this->kind, $uid, $prof_id);
    }
    
    /**
     * Подготавливает данные для проведения операций
     * @param type user_id
     * @param type $tservice_id
     * @param type $prof_id
     * @param type $weeks
     * @return boolean
     */
    public function prepare($user_id, $tservice_id, $prof_id, $weeks, $is_prolong = false)
    {
        $allow = $this->isAllowBind(
            $user_id, 
            $tservice_id,
            $this->kind,
            $prof_id
        );
        
        if ($is_prolong) {
            if ($allow) return;
            
            $old_bind = $this->getItem($user_id, $tservice_id, $prof_id);
            
            $date_stop_old = dateFormat('U', $old_bind['date_stop']);
            $date_stop = $date_stop_old + $weeks * 7 * 24 * 60 * 60;
            
            $this->id = $old_bind['id'];
            
            $this->bind_data = array(
                'date_start' => 'NOW()',
                'date_stop' => date('Y-m-d H:i:s', $date_stop),
                'status' => true,
                'sent_prolong' => false,
                'sent_up' => false
            );

            $this->bind_info = array(
                'descr' => sprintf(self::DESCR_PROLONG, $tservice_id, $this->getProfessionText(true, $prof_id)),
                'comment' => sprintf(self::COMMENT, date('d.m.Y', $date_stop))
            );
        } else {
            if (!$allow) return;
        
            $date_stop = time() + $weeks * 7 * 24 * 60 * 60;
            $this->bind_data = array(
                'user_id' => $user_id,
                'tservice_id' => $tservice_id,
                'kind' => $this->kind,
                'prof_id' => $prof_id,
                'date_start' => 'NOW()',
                'date_stop' => date('Y-m-d H:i:s', $date_stop),
                'status' => true,
                'sent_prolong' => false,
                'sent_up' => false
            );

            $this->bind_info = array(
                'descr' => sprintf(self::DESCR, $tservice_id, $this->getProfessionText(true, $prof_id)),
                'comment' => sprintf(self::COMMENT, date('d.m.Y', $date_stop))
            );
        }
        return true;
    }
    
    /**
     * Подготавливает информацию для платежа по поднятию закрепления
     * @param type $bind
     */
    public function makeUpInfo($bind)
    {
        if (is_array($bind)) {
            $this->id = $bind['id'];
            
            $this->bind_data = array(
                'date_start' => 'NOW()',
                'status' => true,
                'sent_up' => false
            );

            $this->bind_info = array(
                'descr' => sprintf(self::DESCR_UP, $bind['tservice_id'], $this->getProfessionText(true, $bind['prof_id'])),
                'comment' => ''
            );
        }
    }
    
    /**
     * Создает запись в таблице
     * @return type
     */
    public function create()
    {
        $allow = $this->isAllowBind(
            $this->bind_data['user_id'], 
            $this->bind_data['tservice_id'],
            $this->bind_data['kind'],
            $this->bind_data['prof_id']
        );
        
        if ($allow) {
            $ok = $this->db()->insert($this->TABLE, $this->bind_data);
        
            if ($ok) {
                /**
                 * @todo почистить кеш
                 */
                return true;
            }
        }
        return false;
    }
    
    
    
    /**
     * Выборка закрепленных услуг позиция которых 4 и ниже
     * 
     * @global type $DB
     * @param type $page
     * @param type $offset
     * @return boolean
     */
    public static function getDowned($page = 1, $offset = 50)
    {
        global $DB;
        
        $kind_cnt = count(self::$_kinds);
        
        if (!$kind_cnt) {
            return false;
        }
        
        $from = $offset;
        $to = ($page-1)*$offset; 
        $to = ($to < 3)?3:$to;
        
        $base_sql = "
            SELECT
                *
            FROM
            (
                SELECT 
                    tb.id,
                    tb.kind,
                    tb.prof_id,
                    tb.date_stop,
                    tb.tservice_id,
                    t.title,
                    fu.uname,
                    fu.usurname,
                    fu.login,
                    fu.email            
                FROM tservices_binds AS tb 
                INNER JOIN tservices AS t ON t.id = tb.tservice_id 
                LEFT JOIN tservices_blocked AS sb ON sb.src_id = tb.tservice_id 
                INNER JOIN freelancer AS fu ON fu.uid = tb.user_id
                WHERE 
                    t.deleted = FALSE 
                    AND t.active = TRUE 
                    AND sb.src_id IS NULL 
                    AND fu.is_banned = '0' 
                    AND (tb.sent_up IS NULL OR tb.sent_up = FALSE)
                    AND tb.kind = ?i
                ORDER BY tb.date_start DESC, t.id DESC 
                LIMIT {$from} OFFSET {$to}
             ) AS s_?i
        ";
        
        
        $_sql = array();

        foreach (self::$_kinds as $kind) {
            $_sql[] = $DB->parse($base_sql, $kind, $kind);
        }       
                
        $sql = implode(' UNION ', $_sql);
        $res = $DB->query($sql);
        
        //@todo: быстрее чем rows()
        $ret = pg_fetch_all($res);
        return $ret;
    }




    /**
     * Выборка закрепленных услуг срок закрепления 
     * которых истекает через 24 часа
     * 
     * @global type $DB
     * @param type $page
     * @param type $offset
     * @return type
     */
    public static function getExpiring($page = 1, $offset = 200)
    {
        global $DB;
        
        $from = $offset;
        $to = ($page-1)*$offset;    
        
        $res = $DB->query("
            SELECT 
                tb.id,
                tb.kind,
                tb.prof_id,
                tb.date_stop,
                tb.tservice_id,
                t.title,
                fu.uname,
                fu.usurname,
                fu.login,
                fu.email
            FROM ".self::$_TABLE." AS tb 
            INNER JOIN ".self::$_TABLE_TSERVICES." AS t ON t.id = tb.tservice_id 
            INNER JOIN ".self::$_TABLE_FREELANCER." AS fu ON fu.uid = tb.user_id 
            WHERE 
                tb.date_stop BETWEEN NOW() AND NOW() + interval '24 hours'
                AND (tb.sent_prolong IS NULL OR tb.sent_prolong = FALSE)
                AND fu.is_banned = '0'
            ORDER BY tb.id            
            LIMIT ?i OFFSET ?i
        ", $from, $to);
        
        //@todo: быстрее чем rows()
        $ret = pg_fetch_all($res);
        return $ret;
    }
    
    
    
    /**
     * Обновляет запись
     * @return type
     */
    public function update()
    {
        
        if ($this->id && $this->bind_data) {
            $ok = $this->db()->update($this->TABLE, $this->bind_data, 'id = ?i', $this->id);
        
            if ($ok) {
                /**
                 * @todo почистить кеш
                 */
                return true;
            }
        }
        return false;
    }
    

    /**
     * Помечает флаг после отправки соответствующего уведомления (о продлении или поднятии)
     * 
     * @global type $DB
     * @param type $type
     * @param type $ids
     * @return boolean
     */
    public static function markSent($type, $ids)
    {
        if (!in_array($type, array('prolong', 'up'))) {
            return false;
        }
        
        global $DB;
        
        $ids = (!is_array($ids))?array($ids):$ids;
        
        return $DB->update(
            self::$_TABLE, 
            array('sent_' . $type => true), 
            'id IN(?l)', 
            $ids
        );        
    }
    
    
    
    /** Удаление закрепления по id в account_operations
     * @see account::DelByOpid()
     *
     * @param  intr $uid uid пользователя
     * @param  int $opid id операции в биллинге
     * @return int 0
     */
    public function DelByOpid($uid, $opid)
    {
        $sql = "SELECT bq.id, bq.op_count, bq.option, bq.service, bq.src_id, ao.op_code
            FROM account_operations ao
            INNER JOIN bill_reserve br ON br.uid = ?i
                AND br.ammount = -(ao.ammount) 
                AND br.complete_time::timestamp = ao.op_date::timestamp
            INNER JOIN bill_queue bq ON bq.reserve_id = br.id AND bq.op_code = ao.op_code
            WHERE ao.id = ?i
                AND ao.billing_id = (SELECT id FROM account WHERE uid = ?i);";
        $operation = $this->db()->row($sql, $uid, $opid, $uid);
        if (!$operation) return 0;
        
        $is_up = $operation['service'] == 'tservicebindup';
        
        if ($is_up) {
            $origOpCode = billing::getOpCodeByDiscount($operation['op_code']);
            $this->kind = array_search($origOpCode, $this->op_codes_up);
            $bindInfo = $this->getItemById($operation['src_id']);
        } else {
            $origOpCode = billing::getOpCodeByDiscount($operation['op_code']);
            $this->kind = array_search($origOpCode, $this->op_codes);
            $options = mb_unserialize($operation['option']);
            $is_prolong = isset($options['is_prolong']) && $options['is_prolong'] == 1;
            if ($is_prolong) {
                $bindInfo = $this->getItem($uid, (int)$options['tservice_id'], $operation['src_id']);
            } else {
                $this->db()->query(
                    "DELETE FROM {$this->TABLE} WHERE user_id = ?i AND tservice_id = ?i AND kind = ? AND prof_id = ?i", 
                    $uid, (int)$options['tservice_id'], $this->kind, $operation['src_id']
                ); 
            }
        }
        
        if (isset($bindInfo['id']) && $bindInfo['id'] > 0) {
            $dates = $this->recalcBindDates($uid, array(
                'bind_id' => $bindInfo['id'],
                'op_code' => $operation['op_code'],
                'op_id' => $operation['id'],
                'tservice_id' => $bindInfo['tservice_id'],
                'prof_id' => $bindInfo['prof_id']
            ));
            
            $this->db()->update(
                $this->TABLE, 
                array(
                    'date_start' => $dates['start'],
                    'date_stop' => $dates['stop']
                ), 
                'id = ?i', 
                $bindInfo['id']
            );
        } 
        return 0;
    }
    
    
    /**
     * Пересчитывает даты начала и окончания закрепления по операции
     * @param int $uid
     * @param array $params
     * @return type
     */
    private function recalcBindDates($uid, $params)
    {
        //Получаем коды операций, которые могли быть применены по данному закреплению
        $op_codes_allow = array();
        foreach ($this->op_codes_groups as $group) {
            $op_codes_in_group = billing::extendOpCodes($group);
            if (in_array($params['op_code'], $op_codes_in_group)) {
                $op_codes_allow = $op_codes_in_group;
            }
        }
        
        //Получаем информацию об остальных платежах по данному закреплению
        $sql2 = "SELECT bq.op_code, bq.op_count, bq.src_id, bq.option, bq.service, br.complete_time::timestamp as date
            FROM bill_queue bq
            INNER JOIN bill_reserve br ON bq.reserve_id = br.id
            INNER JOIN account_operations ao ON br.ammount = -(ao.ammount) 
                AND br.complete_time::timestamp = ao.op_date::timestamp
                AND ao.billing_id = (SELECT id FROM account WHERE uid = ?i)
            WHERE bq.uid = ?i AND bq.op_code IN (?l)
                AND ((bq.service = 'tservicebind' AND bq.src_id = ?i) OR (bq.service = 'tservicebindup' AND bq.src_id = ?i))
                AND bq.status = 'complete' AND bq.id != ?i
            ORDER BY br.complete_time ASC;";
        $operations = $this->db()->rows($sql2, $uid, $uid, $op_codes_allow, $params['prof_id'], $params['bind_id'], $params['op_id']);

        foreach ($operations as $operation) {
            $is_prolong = $is_up = false;
            
            //Устанавливаем даты начала при любой операции
            $date_start = DateTime::createFromFormat("Y-m-d H:i:s.u", $operation['date']);
            
            if ($operation['service'] == 'tservicebind') {
                $options = mb_unserialize($operation['option']);
                if ($options['tservice_id'] != $params['tservice_id']) {
                    continue;
                }
                $is_prolong = $options['is_prolong'];
            } else {
                $is_up = true;
            }
            
            if (!$is_prolong && !$is_up || !isset($date_stop)) {
                //Если покупка, то дату окончания считаем от даты покупки
                $date_stop = clone $date_start;
                $date_stop->add(new DateInterval('P'.($operation['op_count']*7).'D'));
            } elseif ($is_prolong) {
                //Если продление - продляем дату окончания
                $date_stop->add(new DateInterval('P'.($operation['op_count']*7).'D'));
            }
        }

        if (!isset($date_stop)) {
            $date_stop = clone $date_start;
        }
        return array(
            'start' => $date_start->format('Y-m-d H:i:s'),
            'stop' => $date_stop->format('Y-m-d H:i:s')
        );
    }
    
    
    
    /**
     * Закреплена ли указанная услуга
     * 
     * @global type $DB
     * @param type $tu_id
     * @param type $uid
     * @return type
     */
    public static function isBinded($tu_id)
    {
        global $DB;
        
        return $DB->val("
            SELECT 1 FROM " . self::$_TABLE . " 
            WHERE 
                tservice_id = ?i 
                AND status = true 
                AND date_stop > now() 
            LIMIT 1", $tu_id);
    }
    
    
}
