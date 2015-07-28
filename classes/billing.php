<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/op_codes.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/op_codes_price.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wallet/wallet.php");

/**
 * Новая логика личного счета
 * 
 */
class billing
{
    /**
     * Минимальная сумма платежа в рублях
     */
    const MINIMUM_PAYED_SUM = 10;

    /**
     * Статус новой записи в списке заказов
     */
    const STATUS_NEW      = 'new';
    
    /**
     * Услуга в списке заказов забронирована для оплаты
     */
    const STATUS_RESERVE  = 'reserve';
    
    /**
     * Услуга из списка заказов удалена
     */
    const STATUS_DELETE   = 'delete';
    
    
    /**
     * Статус резерва денег списка операций
     */
    const RESERVE_STATUS = 'reserve';
    
    /**
     * Статус обработки операций
     */
    const RESERVE_PROCESS_STATUS = 'process';
    
    /**
     * Список операций обработан
     */
    const RESERVE_COMPLETE_STATUS = 'complete';
    
    /**
     * Список операций отменен и не должен обрабатываться
     */
    const RESERVE_CANCEL_STATUS = 'cancel';
    
    /**
     *  Псевдокод списка услуг (для определения в истории)
     */
    const RESERVE_OP_CODE = 2000;
    
    /**
     * Услуга из списка заказов выполнена (услуга оказана в полной мере, деньги за нее списаны)
     */
    const STATUS_COMPLETE = 'complete';

    /**
     * сколько дней будет ждать оплаты резерв (таблица bill_reserve)
     */
    const RESERVATION_DAYS = 7;

    /**
     * При тестовом выполнении некоторых функций нужно исключить перенаправление страницы
     * 
     * @var type 
     */
    public $test = false;
    
    /**
     * Название кнопок в той или ином состоянии услуги
     * 
     * @var array 
     */
    public static $btn_name_for_type = array(
        'active'  => 'Купить сейчас',
        'lately'  => 'Заказать еще',
        'notused' => 'Заказать услугу'
    );
    
    /**
     * Сервисы работодателя и их op_code
     * 
     * @var array
     */
    public static $emp_default_service = array(
        'pro'        => array(15, 118, 119, 120),
        'massending' => array(45),
        'contest'    => array(121, 122, 123, 124, 125, 126, 127, 128, 129, 130),
        'projects'   => array(53),
        'sbr'        => array()
    );
    
    /**
     * Сервисы фрилансера и их оп коды
     * 
     * @var array
     */
    public static $frl_default_service = array(
        'pro'        => array(47,48,49,50,51),
        'pay_place'  => array(65,73),
        'massending' => array(45)
    );
   
    /**
     * Ид операций зачисления денег
     * 
     * @var array 
     */
    public static $op_code_transfer_money = array(
        12,13
    );
    
    /**
     * @desc Идентификаторы операций покупки pro 
    */
    public static $pro_op_codes = array(47, 15, 48, 118, 49, 119, 50, 120, 51, 131, 132);
    /**
     * @desc Принимает значение true если в списке заказанных сервисов существует заказ pro
     * @see initAdditionalInfoOrder
    */
    public $pro_exists_in_list_service = true;
    
    
    
    private $_db;
    
    /**
     * ИД резерва списка услуг
     * @var integer 
     */
    public $reserved;
    
    /**
     * Информация по резерву
     * @var array 
     */
    public $reserve;
    
    
    /**
     * Модель прокодов
     * 
     * @var type 
     */
    protected $promoCodeModel = null;


    /**
     * Данные промо-кода
     * @var array
     */
    public $promoCode;
    
    
    /**
     * Массив промо-кодов у заказов в формате service=>promo_code
     * @var array 
     */
    public $ordersPromoCodes = array();
    
    
    /**
     * Общая сумма по текущему заказу
     * 
     * @var type 
     */
    public $payed_sum = 0;


    
    /**
     * Различные параметры какой-либо платежной системы
     * (необязательны и зависят от необходимости в них каких либо услуг при покупке,
     * например резерву БС нужен уникальный ID транзакции платежной системы Яндекс,
     * в этом случае необходимо передать данные параметры)
     * @var type 
     */
    protected $paymentSysParams = array();






    /**
     * Возможные скидки на услуги
     * сопостовление основной услуги (ключ) и опкода скидки (значение)
     * 
     * @todo: https://beta.free-lance.ru/mantis/view.php?id=27996
     */
    public static $discount_op_codes = array(
        //1) Скидки на карусель 
        65 => 165,
        73 => 166,
        
        //3) Скидки при поднятии предложений
        145 => 170,
        146 => 172,
        154 => 171,
        
        //4) Скидки при закреплении/продлении услуг ТУ
        155 => 173,
        156 => 174,
        157 => 175,
        158 => 176,
        
        //5) Скидки при поднятии закреплений услуг
        159 => 177,
        160 => 178,
        161 => 179,
        162 => 180,
        
        //6) Скидки размещение и продление в каталоге фрилансеров
        142 => 181,
        143 => 182,
        144 => 183,
        148 => 184,
        149 => 185,
        150 => 186,        
        
        //7) Скидки на поднятие размещения в каталоге фрилансеров
        151 => 187, 
        152 => 188,
        153 => 189
    );
    
    
    /**
     * Примечание к платежу
     * для простого текста
     */
    const TXT_DESCR_DISCOUNT_20 = 'Оплачено, скидка 20%';
    
    public static $descr_op_codes = array(
        165 => self::TXT_DESCR_DISCOUNT_20,
        166 => self::TXT_DESCR_DISCOUNT_20,
        167 => self::TXT_DESCR_DISCOUNT_20,
        168 => self::TXT_DESCR_DISCOUNT_20,
        169 => self::TXT_DESCR_DISCOUNT_20,
        170 => self::TXT_DESCR_DISCOUNT_20,
        171 => self::TXT_DESCR_DISCOUNT_20,
        172 => self::TXT_DESCR_DISCOUNT_20,
        
        187 => self::TXT_DESCR_DISCOUNT_20,
        188 => self::TXT_DESCR_DISCOUNT_20,
        189 => self::TXT_DESCR_DISCOUNT_20
    );








    /**
     * Конструктор класса
     * 
     * @global type $DB
     * @param integer $uid  ИД Пользователя
     */
    public function __construct($uid) {
        global $DB;
        $this->setUser($uid);
        if(!$this->user['uid']) {
            trigger_error("User not found", E_USER_ERROR);
        }
        $this->initAccount();
        $this->_db = $DB;
    }
    
    /**
     * Старт транзакции
     * 
     * @return type
     */
    public function start() {
        return $this->_db->start();
    }
    
    /**
     * Выполнение транзакции
     * 
     * @return type
     */
    public function commit() {
        return $this->_db->commit();
    }
    
    /**
     * Откат транзакции
     * 
     * @return type
     */
    public function rollback() {
        return $this->_db->rollback();
    }
    
    
    /**
     * Задаем позицию на которой работает данный класс ( для подгрузки необходимых данных )
     * 
     * @todo: метод сейчас используется не полностью максимум в режиме 'orders'
     * @todo: поэтому необходимо постепенно уходить от испоьзования данного метода
     * 
     * @param string $name Название страницы
     */
    public function setPage($name = 'index') {
        $this->getCountListServices();
        $this->getLastHistory();
        $this->name_page = $name;
        switch($name) {
            case 'orders':
                $this->getOrders();
                break;
            case 'success':
                $this->getLastReserve(self::RESERVE_COMPLETE_STATUS);
                $this->initAdditionalInfoOrder();
                break;
            case 'fail':
                $this->getLastReserve(self::RESERVE_STATUS);
                break;
            case 'send':
                if(!hasPermissions('payments')) {
                    header("Location: /404.php");
                    exit;
                }
                
                if(!empty($_POST)) {
                    $action = $_POST['action'];
                    if($action == 'sended') {
                        $tr_id   = $_REQUEST['transaction_id'];
                        $uid     = __paramInit('int', NULL, 'login_db_id');
                        $sum     = __paramInit('float', NULL, 'sum');
                        $comment = __paramInit('string', NULL, 'comment', NULL, 300);
                        
                        $user = new users();
                        $user->GetUserByUID($uid);
                        
                        if($user->login == '') {
                            $this->error['login'] = true;
                        } 
                        if($sum <=0 || $this->acc['sum'] < $sum) {
                            $this->error['sum'] = true;
                        }
                        
                        if(empty($this->error)) {
                            $success = $this->account->transfer(get_uid(), $uid, $sum, $tr_id, $comment, true, $sum);
                            
                            if($success) {
                                $_SESSION['send_success'] = true;
                                header("Location: /bill/send/");
                                exit;
                            }
                        }
                        
                        $this->post = array(
                            'uid'     => $user->uid,
                            'login'   => $user->login,
                            'sum'     => $sum,
                            'comment' => $comment
                        );
                    }
                }
                break;
            case 'index':
            default:
                $this->loadMainData();
                break;
        }
    }
    
    /**
     * Инициализация аккаунта пользователя
     */
    public function initAccount() {
        if($this->user['uid']) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
            $account = new account();
            $account->GetInfo($this->user['uid'], true);
            $this->account = $account;
            $this->acc = get_object_vars($account);
            if (get_uid(false) == $this->user['uid']) {
                $_SESSION['ac_sum'] = $account->sum;
                $_SESSION['bn_sum'] = $account->bonus_sum;
            }

            $this->wallet = WalletTypes::initWalletByType($this->user['uid']);// Берет активный если есть
        }
    }
    
    /**
     * Инициализация пользователя
     * 
     * @staticvar array $user_data
     * @param integer $uid     ИД пользователя
     * @param array   $data    Если есть данные пользователя передаем их чтобы не дергать лишний раз таблицу    
     * @return \billing
     */
    public function setUser($uid, $data = array()) {
        static $user_data;
        
        if( isset($user_data[$uid]) ) {
            $this->user = $user_data[$uid];
            return $this;
        }
        
//        if(empty($data) && get_uid(false) == $uid) {
//            $data = $_SESSION;
//        }
        
        if(empty($data)) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

            $user = new users();
            $user->GetUserByUID($uid);
            $this->obj_user = $user;
            $this->user = array(
                'uid'           => $user->uid,
                'role'          => $user->role,
                'login'         => $user->login,
                'uname'         => $user->uname,
                'email'         => $user->email,
                'city'          => $user->city,
                'country'       => $user->country,
                'usurname'      => $user->usurname,
                'subscr'        => $user->subscr,
                'is_profi'      => $user->is_profi
            );
        } else {
            $this->user = $data;
        }
        
        $user_data[$uid] = $this->user;
        
        return $this;
    }
    
    /**
     * Последние N записей истории
     * 
     * @param integer $limit   Количество записей
     * @return type
     */
    public function getLastHistory($limit = 5) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        
//        $sbr_op_codes = array(sbr::OP_RESERVE, sbr::OP_CREDIT, sbr::OP_DEBIT);
//        $sql = "SELECT 
//                    op_date, op_name, round(ammount, 2) as ammount, comments, op_code, account_operations.id, 
//                    round(balance,2) as balance, trs_sum, payment_sys, null::text as status 
//                FROM account_operations
//				LEFT JOIN account ON account.id=account_operations.billing_id
//				LEFT JOIN op_codes on op_code=op_codes.id 
//                LEFT JOIN account_operations_blocked ob ON ob.operation_id = account_operations.id
//                WHERE 
//                    ( op_code IN (?l) AND ammount = 0 ) = false
//                    AND ob.id IS NULL AND uid = ?i
//                UNION
//                SELECT create_time as op_date, 'Список заказов №'|| id ||' на сумму ' || round(ammount, 2) || ' руб' as op_name,
//                   round(ammount, 2) as ammount, null::text as comments, " . self::RESERVE_OP_CODE . " as op_code, id,
//                   null::numeric as balance, null::integer as trs_sum, payment as payment_sys, status 
//                FROM bill_reserve WHERE uid = ?i AND complete_time IS NULL AND status = ?
//                ORDER BY op_date DESC
//                LIMIT {$limit}
//                ";
//                
//        $this->last_history = $this->_db->rows($sql, $sbr_op_codes, $this->user['uid'], $this->user['uid'], billing::RESERVE_STATUS);
        $result = $this->account->getBillHistory(1, $limit, null, null, false);
        $this->last_history = $result['items'];
        return $this->last_history;
    }
    
    /**
     * Загружаем основную информацию по покупкам
     */
    public function loadMainData() {
        if(is_emp($this->user['role'])) {
            $services = $this->loadMainDataEmp();
        } else {
            $services = $this->loadMainDataFrl();
        }
        if($services) {
            foreach (array('active', 'lately', 'notused') as $type) {
                $this->list_types_services[$type] = self::serviceFilter($services, $type);
            }
        }
    }
    
    /**
     * Информация по покупкам для работодателя
     * 
     *  «Подключенные» – активные услуги, действующие в данный момент; (active)
     *  «Вы недавно покупали» – уже неактивные услуги, которыми юзер пользовался в ближайшие полгода; (lately)
     *  «Вы еще не использовали» – услуги, которыми не пользовались свыше полугода (с даты истечения срока действия прошло больше 6 месяцев); (notused)
     * 
     * 
     * @return array
     */
    public function loadMainDataEmp() {
        require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");

        $sql = "
        WITH lately_service AS (
            SELECT MAX(posted_time) as d, 'massending' as service, 1 as sort 
            FROM mass_sending WHERE user_id = ?i AND (posted_time + interval '6 month') > NOW() 
                UNION
            SELECT MAX(create_date) as d, 'contest' as service, 2 as sort  
            FROM projects WHERE user_id = ?i AND kind = 7 AND (create_date + interval '6 month') > NOW() 
                UNION
            SELECT MAX(create_date) as d, 'projects' as service, 3 as sort
            FROM projects WHERE user_id = ?i AND kind <> 7 AND payed > 0 AND (create_date + interval '6 month') > NOW()
                UNION
            SELECT MAX(reserved_time) as d, 'sbr' as service, 4 as sort  
            FROM sbr WHERE emp_id = ?i AND reserved_id > 0 AND (reserved_time + interval '6 month') > NOW()
                UNION
            SELECT MAX(from_date + to_date) as d, 'pro' as service, 0 as sort 
            FROM orders WHERE from_id = ?i AND (from_date + to_date + interval '6 month') > now()
        ) 
        SELECT MAX(from_date + to_date) as d, 'pro' as service, 1 as sort, 'active' as type FROM orders WHERE from_id = ?i AND from_date + to_date > now()
        UNION
        SELECT lately_service.*, 'lately' as type FROM lately_service
        ORDER BY type, sort ASC
        ";

        $services = $this->_db->rows($sql, $this->user['uid'], $this->user['uid'], $this->user['uid'], $this->user['uid'], $this->user['uid'], $this->user['uid']);

        foreach ($services as $service) {
            if ($service['d'] == null || isset($result[$service['service']])) {
                continue;
            }
            if ($service['service'] == 'pro') {
                // Обновляем сессию
                if($service['type'] == 'active') {
                    $_SESSION['pro_last'] = payed::ProLast($this->user['login']);
                    $_SESSION['pro_last'] = $_SESSION['pro_last']['is_freezed'] ? false : $_SESSION['pro_last']['cnt'];
                    if($_SESSION['pro_last']['is_freezed']) {
                        $_SESSION['payed_to'] = $_SESSION['pro_last']['cnt'];
                    }
                }
                $user = new users();
                $service['expired']          = billing::expiredTime($service['d']);
                $service['is_auto']          = $user->GetField($this->user['uid'], $e, 'is_pro_auto_prolong', false);
                $service['auto']             = $service['is_auto'];
                $service['last_operation']   = $this->getLastOperation($service['service']);
            }
            $result[$service['service']] = $service;
        }

        foreach (self::$emp_default_service as $type => $val) {
            if (!isset($result[$type])) {
                $result[$type] = array('type' => 'notused', 'service' => $type);
            }
        }
        
        $this->services = $result;
        return $this->services;
    }
    
    /**
     * Информация по покупкам для фрилансеров
     * 
     *  «Подключенные» – активные услуги, действующие в данный момент; (active)
     *  «Вы недавно покупали» – уже неактивные услуги, которыми юзер пользовался в ближайшие полгода; (lately)
     *  «Вы еще не использовали» – услуги, которыми не пользовались свыше полугода (с даты истечения срока действия прошло больше 6 месяцев); (notused)
     * 
     */
    public function loadMainDataFrl() {
        $sql = "
        WITH active_service AS (
            SELECT MAX(from_date + to_date) as d, 'pro' as service, 1 as sort 
            FROM orders WHERE from_id = ?i AND from_date + to_date > now()
                UNION
            SELECT MAX(date_create) as d, 'pay_place' as service, 2 as sort 
            FROM paid_places WHERE uid = ?i AND is_done = 0
                UNION
            -- сначала находим сроки окончания для всех активных объявлений
            -- а потом минимальный из них
            SELECT MIN(d) as d, 'first_page' as service, 3 as sort
                FROM (
                    SELECT MAX(from_date + to_date) as d
                    FROM users_first_page
                    WHERE user_id = ?i AND from_date + to_date > now() AND payed = true
                    GROUP BY profession
                ) as ufp
        ), lately_service AS (
            SELECT MAX(from_date + to_date) as d, 'pro' as service, 1 as sort 
            FROM orders WHERE from_id = ?i AND (from_date + to_date + interval '6 month') > NOW() 
                UNION
            SELECT MAX(posted_time) as d, 'massending' as service, 1 as sort 
            FROM mass_sending WHERE user_id = ?i AND (posted_time + interval '6 month') > NOW() 
                UNION
            SELECT MAX(date_create) as d, 'pay_place' as service, 2 as sort 
            FROM paid_places WHERE uid = ?i AND (date_create + interval '6 month') > NOW()
                UNION
            SELECT MAX(from_date + to_date) as d, 'first_page' as service, 3 as sort 
            FROM users_first_page where user_id = ?i AND payed = true AND (from_date + to_date + interval '6 month') > NOW()	
        )
        SELECT active_service.*, 'active' as type FROM active_service
        UNION
        SELECT lately_service.*, 'lately' as type FROM lately_service
        ORDER BY type, sort ASC
        ";

        $services = $this->_db->rows($sql, $this->user['uid'], $this->user['uid'], $this->user['uid'], $this->user['uid'], $this->user['uid'], $this->user['uid'], $this->user['uid']);
        
        foreach($services as $service) {
            if($service['d'] == null || isset($result[$service['service']])) {
                continue;
            }    
            $service['expired']          = billing::expiredTime($service['d']);
            if ($service['service'] == 'pro') {
                // Обновляем сессию
                if($service['type'] == 'active') {
                    $_SESSION['pro_last'] = payed::ProLast($this->user['login']);
                    $_SESSION['pro_last'] = $_SESSION['pro_last']['is_freezed'] ? false : $_SESSION['pro_last']['cnt'];
                    if($_SESSION['pro_last']['is_freezed']) {
                        $_SESSION['payed_to'] = $_SESSION['pro_last']['cnt'];
                    }
                }
                $user = new users();
                $service['is_auto']          = $user->GetField($this->user['uid'], $e, 'is_pro_auto_prolong', false);
                $service['auto']             = $service['is_auto'];
                $service['last_operation']   = $this->getLastOperation($service['service']);
                
                // Тестовый не может быть куплен второй раз
                if($service['last_operation']['op_code'] == 47) $service['last_operation']['op_code'] = 48;
            }
            $result[$service['service']] = $service;
        }
        
        foreach(self::$frl_default_service as $type => $val) {
            if(!isset($result[$type])) {
                $result[$type] = array('type' => 'notused', 'service' => $type);
            }
        }
        
        $this->services = $result;
        return $this->services;
    }
    
    /**
     * Последняя операция в определенном блоке покупок (необходима для определения последнего типа покупки чтобы подставить его при покупке)
     * 
     * @param string $type  Тип операции @see self::$emp_default_service, self::$frl_default_service
     * @return type
     */
    public function getLastOperation($type) {
        $sql = "
            SELECT ao.*, oc.op_name, oc.sum FROM account_operations ao
            INNER JOIN account a ON ao.billing_id = a.id AND a.uid = ?i
            INNER JOIN op_codes oc ON oc.id = ao.op_code
            WHERE ao.op_code IN (?l)
            ORDER BY ao.id DESC LIMIT 1
        ";
        
        $memBuff = new memBuff();
        $last_operation = $memBuff->get("last_operation_" . $this->user['uid'] . "_" . $type);
        if(!$last_operation) {
            $last_operation = $this->_db->row($sql, $this->user['uid'], ( is_emp($this->user['role']) ? self::$emp_default_service[$type] : self::$frl_default_service[$type] ));
            $memBuff->set("last_operation_" . $this->user['uid'] . "_" . $type, $last_operation);
        }
        return $last_operation;
    }
    
    /**
     * Отдает необходимые шаблоны для вывода услуг
     * 
     * @todo: более не используется
     * 
     * @param string $service   Название услуг
     * @return string
     */
    public static function getTemplateByService($service) {
        switch($service) {
            case 'pro':
                return is_emp() ? 'tpl.emp_pro.php' : 'tpl.frl_pro.php';
                break;
            case 'pay_place':
                return 'tpl.pay_place.php';
                break;
            case 'contest':
                return 'tpl.contest.php';
                break;
            case 'projects':
                return 'tpl.projects.php';
                break;
            case 'massending':
                return 'tpl.massending.php';
                break;
            case 'sbr':
                return 'tpl.sbr.php';
                break;
            case 'verify_ff':
                return 'tpl.verify_ff.php';
                break;
            default:
                trigger_error("Template for service '{$service}' not found", E_USER_ERROR);
                break;
        }
    }
    
    /**
     * Инициализируем тип оплаты на странице
     * @todo: более данный механизм не используется, хотя можно было на его базе попробовать использовать ЯДКассу
     * 
     * 
     * @param string $type_payment    Название тип оплаты (передается в $_GET['type'])
     */
    public function setPaymentMethod($type_payment) {
        require_once($_SERVER['DOCUMENT_ROOT']."/classes/exrates.php");
        switch($type_payment) {
            case 'sber_print':
                $this->payment_template = 'bank/tpl.bank_fiz_print.php';
                $this->type_menu_block  = 'bank';
                $this->payment_type     = exrates::BANK;
                break;
            case 'sber':
                require_once($_SERVER['DOCUMENT_ROOT']."/classes/bank_payments.php");
                
                $this->payment_template = 'bank/tpl.bank_fiz.php';
                $this->type_menu_block  = 'bank';
                $this->payment_type     = exrates::BANK;
                
                $this->pm = new bank_payments();
                $this->pm->bank_code = __paramInit('int',NULL,'bc', bank_payments::BC_SB);
                $this->pm->sum       = __paramInit('float',NULL,'Sum');
                $bp_reqv             = bank_payments::GetLastReqv($this->pm->bank_code, $this->user['uid']);
                $this->pm->fio       = $bp_reqv['fio'];
                $this->pm->address   = $bp_reqv['address'];
                if(!$this->pm->bill_num) $this->pm->bill_num  = bank_payments::GenBillNum($this->pm->bank_code, $this->user['uid'], $this->acc['id']);
                
                if(isset($_POST['action']) && $_POST['action'] == 'payment') {
                    $this->pm->fio       = substr(__paramInit('string',NULL,'fio'),0,128);
                    $this->pm->is_gift   = false;
                    $this->pm->address   = substr(__paramInit('string',NULL,'address'),0,255);
                    $this->pm->bank_code = __paramInit('int',NULL,'bc');
                    $this->pm->sum       = __paramInit('float',NULL,'sum');
                    setlocale(LC_ALL, 'en_US.UTF-8'); // гребанная бета! (это не мое)
                    $this->pm->fm_sum    = $bp->sum / EXCH_TR;
                    $id                  = __paramInit('int',NULL,'id');
                    
                    if($this->pm->sum < 10) $alert['sum'] = 'Минимальная сумма платежа 10 рублей';
                    if(!$this->pm->fio) $alert['fio'] = 'Поле заполнено некорректно.';
                    if(!$this->pm->address) $alert['address'] = 'Поле заполнено некорректно.';
                    
                    if(!$alert) {	  	  	
                        if($id) {
                            $this->pm->bank_code = NULL;
                            $this->pm->Update($id, " AND user_id = {$this->user['uid']} AND accepted_time IS NULL");
                        } else {
                            $this->pm->bill_num = bank_payments::GenBillNum($this->pm->bank_code, $this->user['uid'], $this->acc['id']);
                            $this->pm->user_id = $this->user['uid'];
                            $this->pm->op_code = 12;
                            $id = $this->pm->Add($error, TRUE);
                        }

                        if(!$error) {
                            $prepare = $this->preparePayments($this->getTotalAmmountOrders());
                            if($prepare) {
                                header("Location: /bill/payment/print/?type=sber_print&id={$id}");
                                exit;
                            }
                        }
                    }
                    
                    $this->error = $alert;
                }
                $this->bank = bank_payments::GetBank($bp->bank_code);
                
                
                break;
            case 'bank_print':
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/reqv.php");
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/reqv_ordered.php");
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
                
                if($_GET['order'] > 0) {
                    $this->payment_template = 'bank/tpl.bank_jur_transfer.php';
                    $this->tid = intval($_GET['order']);
                } else {
                    $this->payment_template = 'bank/tpl.bank_jur_print.php';
                }
                $this->type_menu_block  = 'bank';
                $this->payment_type     = exrates::BANK;
                $this->bank_sum         = $_SESSION['sum_bank_print'];
                $this->bank_id          = $_SESSION['id_bank_print'];
                unset($_SESSION['sum_bank_print'], $_SESSION['id_bank_print']);
                
                break;
            case 'bank':
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/reqv.php");
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/reqv_ordered.php");
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
                
                $this->payment_template = 'bank/tpl.bank_jur.php';
                $this->type_menu_block  = 'bank';
                $this->payment_type     = exrates::BANK;
                
                $this->pm               =  new reqv();
                $reqvByUid              = $this->pm->GetByUid($this->user['uid']);
                $reqvs_ord              = new reqv_ordered();
                $this->pm->billNum      = sizeof($reqvs_ord->GetByUid($this->user['uid']));
                $this->pm->BindRequest($reqvByUid[0]);
                if(isset($_POST['action']) && $_POST['action'] == 'payment') {
                    $_POST['country'] = country::getCountryName($_POST['country_db_id']);
                    $_POST['city']    = city::getCityName($_POST['city_db_id']);
                    
                    $this->pm->BindRequest($_POST);
                    $this->error = $this->pm->CheckInput();
                    
                    if($_POST['sum'] < 10) $this->error['sum'] = 'Минимальная сумма платежа 10 рублей';
                    
                    if(!$this->error) {
                        $this->pm->user_id = $this->user['uid'];
                        
                        if($reqvByUid[0]['id'] > 0) {
                            $id = $reqvByUid[0]['id'];
                            $this->pm->Update($id, " AND user_id= {$this->user['uid']}");
                        } else {
                            $id = $this->pm->Add($err, true);
                        }
                        
                        $prepare = $this->preparePayments($this->getTotalAmmountOrders());
                        if($prepare) {
                            $_SESSION['id_bank_print']  = $id;     
                            $_SESSION['sum_bank_print'] = intval($_POST['sum']);
                            header("Location: /bill/payment/print/?type=bank_print");
                            exit;
                        }
                    }
                }
                
                break;
            case 'alphabank':
                $this->payment_template = 'bank/tpl.alphabank.php';
                $this->type_menu_block  = 'bank';
                $this->payment_type     = exrates::BANK;
                
                if(isset($_POST['action']) && $_POST['action'] == 'reserve') {
                    header("Location: /bill/");
                    exit;
                }
                break;
            case 'card':
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/settings.php");
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/card_account.php");
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/cardpay.php");
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr_meta.php");
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/onlinedengi_cards.php");
                
                $this->payment_template = 'card/tpl.card.php';
                $this->type_menu_block  = 'card';
                $this->payment_type     = exrates::CARD;
                $this->card_merchant    = settings::GetVariable('billing', 'card_merchant');
                if($this->card_merchant) {
                    $card_account             = new card_account();
                    $card_account->account_id = $this->acc['id'];
                    $this->pm                 = new onlinedengi_cards();
                    if(!$this->not_init_pm) {
                        $this->pm->order_id       = $card_account->Add();
                    }
                } else {
                    $this->pm               = new card_account();
                    $this->pm->account_id   = $this->acc['id'];
                    if(!$this->not_init_pm) {
                        $this->pm->order_id     = $this->pm->Add();
                    }
                    $this->pm->reqv         = sbr_meta::getUserReqvs($this->user['uid']);
                }
                break;
            case 'qiwi':
                $this->payment_template = 'terminal/tpl.qiwi.php';
            case 'svyasnoy':
                $this->payment_template = $this->payment_template ? $this->payment_template : 'terminal/tpl.svyasnoy.php';
            case 'euroset':
                $this->payment_template = $this->payment_template ? $this->payment_template : 'terminal/tpl.euroset.php';
                $this->type_menu_block = 'terminal';
                $this->payment_type    = exrates::OSMP;
                
                if($_POST['action'] == 'osmp') {
                    $prepare = $this->preparePayments($this->getTotalAmmountOrders());
                    if(!$this->test && $prepare !== false) {
                        header("Location: /bill/");
                        exit;
                    }
                }  else {
                    $this->error = "Ошибка создания списка оплаты";
                }
                
                break;
            case 'megafon_mobile':
                $this->payment_template = 'mobile/tpl.m_megafon.php';
            case 'beeline_mobile':
                $this->payment_template = $this->payment_template ? $this->payment_template : 'mobile/tpl.m_beeline.php';
            case 'mts_mobile':
                $this->payment_template = $this->payment_template ? $this->payment_template : 'mobile/tpl.m_mts.php';
            case 'matrix_mobile':
                $this->payment_template = $this->payment_template ? $this->payment_template : 'mobile/tpl.m_matrix.php';
                $this->type_menu_block = 'mobilesys';
                $this->payment_type    = exrates::MOBILE;

                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/qiwipay.php");
                $this->pm = new qiwipay($this->user['uid']);
                if($_POST['operator']=='megafon' || $_POST['operator']=='beeline' || $_POST['operator']=='mts' || $_POST['operator']=='matrix') {
                    $phone = __paramValue('string', $_POST['phone']);
                    $phone = str_replace(array("+7", "+77"), "", $phone);
                    $err = 0;
                    switch($_POST['operator']) {
                        case 'megafon':
                            if( !(strpos($phone, '34')===0 || strpos($phone, '62')===0 || strpos($phone, '82')===0 || strpos($phone, '92')===0 || strpos($phone, '35')===0 || strpos($phone, '63')===0 || strpos($phone, '83')===0 || strpos($phone, '93')===0 || strpos($phone, '69')===0 || strpos($phone, '99')===0) ) {
                                $this->error['phone'] = 'Проверьте, верно ли выбран оператор. Указанный номер не относится в сети Мегафон';
                                $err = 1;
                            }
                            break;
                        case 'beeline':
                            if( !(strpos($phone, '90')===0 || strpos($phone, '96')===0) ) {
                                $this->error['phone'] = 'Проверьте, верно ли выбран оператор. Указанный номер не относится в сети Beeline';
                                $err = 1;
                            }
                            break;
                        case 'mts':
                            if( !(strpos($phone, '91')===0 || strpos($phone, '98')===0) ) {
                                $this->error['phone'] = 'Проверьте, верно ли выбран оператор. Указанный номер не относится в сети МТС';
                                $err = 1;
                            }
                            break;
                        case 'matrix':
                            if( !(strpos($phone, '958')===0) ) {
                                $this->error['phone'] = 'Проверьте, верно ли выбран оператор. Указанный номер не относится в сети Matrix';
                                $err = 1;
                            }
                            break;
                    }

                    if(!$err) {
                        $sum   = __paramValue('float', $_POST['sum']);
                        $request = array(
                            'phone' => $phone,
                            'sum'   => $sum,
                            'oper_code' => $_POST['operator']
                        );
                        $created = $this->pm->createBill($request);
                        
                        if(!$created) {
                            $prepare = $this->preparePayments($this->getTotalAmmountOrders());
                            if(!$this->test && $prepare !== false) {
                                header("Location: /bill/");
                                exit;
                            }
                        } else {
                            $this->error = $created;
                        }
                    }
                }

                break;
            case 'webpay':
                $this->type_menu_block = 'psys';
                $this->payment_type = exrates::WEBM;
                $this->payment_template = 'psys/tpl.webpay.php';
                break;
            case 'qiwipurse':
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/qiwipay.php");
                $this->pm = new qiwipay($this->user['uid']);
                $this->type_menu_block = 'psys';
                $this->payment_type = exrates::QIWIPURSE;
                $this->payment_template = 'psys/tpl.qiwipurse.php';
                
                if($_POST['action'] == 'qiwipurse') {
                    $phone = __paramValue('string', $_POST['phone']);
                    $phone = str_replace(array("+7", "+77"), "", $phone);
                    $sum   = __paramValue('float', $_POST['sum']);
                    $request = array(
                        'phone' => $phone,
                        'sum'   => $sum
                    );
                    $created = $this->pm->createBill($request);
                    
                    if(!$created) {
                        $prepare = $this->preparePayments($this->getTotalAmmountOrders());
                        if(!$this->test && $prepare !== false) {
                            header("Location: /bill/");
                            exit;
                        }
                    } else {
                        $this->error = $created;
                    }
                }
                
                break;
            case 'yandex':
                $this->type_menu_block = 'psys';
                $this->payment_type = exrates::YM;
                $this->payment_template = 'psys/tpl.yandex.php';
                break;
            case 'webmoney':
                $this->type_menu_block = 'psys';
                require_once($_SERVER['DOCUMENT_ROOT']."/classes/pmpay.php");
                $this->payment_type = exrates::WMR;
                $this->payment_template = 'psys/tpl.webmoney.php';
                $this->pm = new pmpay();
                break;
            case 'okpay':
                $this->type_menu_block = 'psys';
                $this->payment_type = exrates::OKPAY;
                $this->payment_template = 'psys/tpl.okpay.php';
                break;
        }
        
        $this->type_payment = $type_payment;
    }
    
    /**
     * Вспомогательная функция вычисляет сколько осталось до окончания услуги
     * 
     * @param string $date    Дата окончания услуги
     * @return string
     */
    public static function expiredTime($date) {
        $expired['is_day_expired'] = strtotime('-7 day', strtotime($date)) <= time();
        if($expired['is_day_expired']) {
            $expired['day_expired'] = floor( ( strtotime($date) - time() ) / 86400 );
            $expired['hour_expired'] = ceil( ( strtotime($date) - time() ) / 3600 );
            if($expired['day_expired'] <= 0) $expired['day_expired'] = 1;
        }
        
        $expired['date_str'] = (
            $expired['is_day_expired']
                ? ( $expired['day_expired'] == 1 && $expired['hour_expired'] < 24
                      ? "Срок действия истекает через {$expired['hour_expired']} " . ending($expired['hour_expired'], 'час', 'часа', 'часов')
                      : "Срок действия истекает через {$expired['day_expired']} " . ending($expired['day_expired'], 'день', 'дня', 'дней') )
                : "Срок действия истекает ".date('d.m.Y', strtotime($date))
        );
        return $expired;
    }
    
    /**
     * Фильтрует сервисы по главному типу на странице из общего массива
     * @todo думаю можно будет что-то попроще придумать
     * 
     * @param array  $array   Массив 
     * @param string $filter  Тип для фильтрации (active, lately, noused)
     * @return array
     */
    public static function serviceFilter($array, $filter='active') {
        foreach($array as $t => $var) {
            if($var['type'] == $filter) {
                $result[$t] = $var;
            }
        }
        return $result;
    }
    
    /**
     * Опции используются для корректной записи информации об услуге @see self::prepareOperationCode()
     * зависит от op_code
     * 
     * @param array $option
     */
    public function setOptions($option) 
    {
        $this->_option = $option;
    } 
    
    
    /**
     * @todo: рекомендуется не использовать замена см addServiceAndCheckout
     * 
     * Создаем запись в "Списке услуг"
     * 
     * @param integer $op_code  ИД операции по которой создаем запись (@see table op_codes)
     * @param integer $auto     Автопродление для этого типа услуг
     * @param boolean $clean_queue Очистить очередь
     * @return integer Возвращает ИД созданной услуги
     */
    public function create($op_code, $auto = 0, $clean_queue = true) 
    {
        //Чистим корзину заказов
        if ($clean_queue) {
            $this->cancelAllNewAndReserved();
        }
        
        //Эти приготовления делаются без учета скидки и 
        //только потом она будет использована если имеется
        if(in_array($op_code, self::getOpcodesByAutopayed('pro'))) { // Определяем автоматическую подставку автопро если не пользовались услугами долгое время
            $this->loadMainData();
            if( isset($this->list_types_services['notused']['pro']) ) {
                $auto = 1;
            }
        }
        
        //Пробуем получить скидку
        $op_code = $this->getDiscountOpCode($op_code);
        //Подготовка к покупке
        $options = $this->prepareOperationCode($op_code);
        
        $log = new log("billing/create-".SERVER.'-%d%m%Y.log', 'a', "%d.%m.%Y %H:%M:%S:\r\n");
        $log->writeln("create order ({$op_code}, {$auto})");
        $log->write("login:{$this->user['login']}, uid:{$this->user['uid']}, account:{$this->acc['id']}, acc_sum:{$this->acc['sum']}\r\n");
        ob_start();
        var_dump($options);
        $out = ob_get_clean();
        $log->write($out);
        $log->write("\r\n--------------------\r\n\r\n");
        if(empty($options)) return false;
        
        $data = array(
            'uid'     => $this->user['uid'],
            'op_code' => $op_code,
            'auto'    => $auto == 1 ? true : false
        );
        
        $insert = array_merge($data, $options);
        
        return $this->_db->insert('bill_queue', $insert, 'id');
    }
    
    /**
     * Поиск определенной услуги/услуг
     * 
     * @param array $op_code      Ид услуги
     * @param string  $status       Статус услуги
     * @param string  $service      Сервис к которой относится услуга
     */
    public function search($op_code, $status = null, $service = null) {
        $where['user']    = $this->_db->parse('uid = ?', $this->user['uid']);
        $where['op_code'] = $this->_db->parse('op_code IN (?l)', $op_code);
        if($status !== null) {
            $where['status'] = $this->_db->parse('status ' . (is_array($status) ? " IN (?l)": " = ?"), $status);
        }
        if($service !== null) {
            $where['service'] = $this->_db->parse('service = ?', $service);
        }
        
        $whereSql = implode(" AND ", $where);
        $sql = "SELECT * FROM bill_queue WHERE {$whereSql}";
        
        return $this->_db->rows($sql);
    }
    
    /**
     * Удаляем запись из "Списка услуг"
     * 
     * @param integer $id  ИД записи для удаления
     * @param boolean $event
     * @return boolean
     */
    public function remove($id, $event = false) {
        $remove = array(
            'status'      => self::STATUS_DELETE,
            'delete_time' => 'NOW()'
        );
        if($event) {
            $this->eventsOrder($id);
        }
        $where = $this->_db->parse('uid = ? AND id = ?', $this->user['uid'], $id);
        return $this->_db->update('bill_queue', $remove, $where);
    }
    
    /**
     * События услуг
     */
    public function eventsOrder($id, $type = 'remove') {
        $order = $this->findOrders($this->_db->parse(" AND id = ? ",  $id));
        $order = !empty($order) ? current($order) : null;
        // Удаляем услугу по проекту/конкурсу
        if($order['parent_table'] == 'draft_projects') {
            switch($order['option']) {
                // Удаляем логотип из проекта
                case 'logo':
                    $sql = "UPDATE draft_projects SET logo_id = NULL, logo_link = NULL WHERE id = ? AND uid = ?";
                    $this->_db->query($sql, $order['parent_id'], $this->user['uid']);
                    break;
                // Удаляем закрепление на верху
                case 'top':
                    $sql = "UPDATE draft_projects SET top_days = ?i WHERE id = ? AND uid = ?";
                    $this->_db->query($sql, ($type == 'remove' ? 0 : $order['op_count']), $order['parent_id'], $this->user['uid']);
                    break;
                // Удаляем все заказы к проекту и конкурсу
                case 'contest':
                case 'office':
                    $sql = "UPDATE draft_projects SET logo_id = NULL, logo_link = NULL, top_days = 0 WHERE id = ? AND uid = ?";
                    $this->_db->query($sql, $order['parent_id'], $this->user['uid']);
                    $where   = $this->_db->parse(' AND parent_table = ? AND parent_id = ? AND status = ? ', 'draft_projects', $order['parent_id'], billing::STATUS_NEW);
                    $remove  = $this->findOrders($where);
                    $ids     = array_map(create_function('$array', 'return $array["id"];' ), $remove);
                    $this->update($ids, array('status' => billing::STATUS_DELETE, 'delete_time' => 'NOW()'));
                    $this->eventRemove['service'] = 'projects';
                    $this->eventRemove['ids']     = $ids;
                    break;
            }
        }
    }
    
    /**
     * Обновляем услугу
     * 
     * @param integer $id      ИД услуги
     * @param array   $data    Данные для обновления
     * @return boolean
     */
    public function update($id, $data, $event = false) {
        $where   = $this->_db->parse('uid = ? AND id ' . ( is_array($id) ? " IN (?l) " : " = ? " ), $this->user['uid'], $id);
        $success = $this->_db->update('bill_queue', $data, $where);
        if($event) {
            $this->eventsOrder($id, 'update');
        }
        return $success;
    }
    
    /**
     * Удаляем все услуги на странице "Список услуг"
     * 
     * @param string $status        Статус который ставим этим услугам
     * @param string $add_where     Дополнительное условие
     * @return boolean
     */
    public function clearOrders($status = billing::STATUS_NEW, $add_where = "") 
    {
        $status = is_array($status)?$status:array($status);
        
        $remove = array(
            'status'      => self::STATUS_DELETE,
            'delete_time' => 'NOW()'
        );
        
        $where = $this->_db->parse('uid = ? AND status IN(?l) ', $this->user['uid'], $status) . $add_where;
        
        return $this->_db->update('bill_queue', $remove, $where);
    }
    
    /**
     * Обновляем все услуги из конкретного списка резерва
     *  
     * @param integer $reserve_id   ИД резерва (@see table.bill_reserve)
     * @param string  $status       Статусы услуг для обновления
     * @return boolean
     */
    public function updateOrderListStatus($reserve_id, $status = billing::STATUS_NEW) {
        $where = $this->_db->parse('uid = ?i AND reserve_id = ?i', $this->user['uid'], $reserve_id);
        $update['status'] =  $status;
        if($status == self::STATUS_NEW) {
            $update['reserve_id'] = NULL;
        }
        return $this->_db->update('bill_queue', $update, $where);
    }
    
    /**
     * Подготовка услуг к оплате
     * 
     * @param integer $ammount         Стоимость всех заказанных услуг
     * @param boolean $is_personal     Будет ли задействована оплата из личного счета
     * @param array   $ids             Если необходимо сформировать список из определенных услуг
     * @return boolean
     */
    public function preparePayments($ammount, $is_personal =  false, $ids = array()) 
    {
        $insert = array(
            'uid'                  => $this->user['uid'],
            'is_personal_account'  => $is_personal,
            'ammount'              => $ammount,
            'payment'              => $this->payment_type
        );
        
        $this->reserved = $this->_db->insert('bill_reserve', $insert, 'id');
        $insert['id'] = $this->reserved; 
        $this->reserve = $insert;
        
        if($this->reserved) {
            $where = $this->_db->parse('uid = ? AND status = ?' . ( !empty($ids) ? " AND id IN (?l)" : "" ), $this->user['uid'], self::STATUS_NEW, $ids);
            $update = array(
                'status'     => self::STATUS_RESERVE,
                'reserve_id' => $this->reserved
            );
            return $this->_db->update('bill_queue', $update, $where);
        }
        
        return false;
    }
    
    /**
     * Загружаем списки готовые для покупки
     * 
     * @param string $status  Статус списка (обычно для покупки готовы в статусе reserve)
     * @return type
     */
    public function getReserveOperationsByStatus($status = billing::RESERVE_STATUS) {
        $sql = "SELECT id, uid, ammount FROM bill_reserve WHERE uid = ? AND status = ? AND complete_time IS NULL ORDER BY create_time ASC";
        return $this->_db->rows($sql, $this->user['uid'], $status);
    }
    
    /**
     * Берем информацию по резерву по его ИД
     * 
     * @param type $reserve ИД Резерва
     * @return type
     */
    public function getReserveInfo($reserve) {
        $sql = "SELECT id, uid, ammount FROM bill_reserve WHERE uid = ? AND id = ?";
        return $this->_db->row($sql, $this->user['uid'], $reserve);
    }
    
    /**
     * При старте оплаты услуг помещяем все списки в статус ожидания (process)
     * 
     * @param mixed  $id         ИД списков   
     * @param string $status     Статус
     * @return boolean
     */
    public function startReserved($id, $status = billing::RESERVE_STATUS) 
    {
        $where = $this->_db->parse(( is_array($id) ? "id IN (?l)" : "id = ?i" ) . " AND status = ?", $id, $status);
        return $this->_db->update("bill_reserve", array('status' => billing::RESERVE_PROCESS_STATUS), $where);
    }
    
    /**
     * После оплаты возвращаем неоплаченные услуги обратно в ожидание
     * 
     * @param mixed  $id      ИД списков
     * @param string $status  Статус
     * @return boolean
     */
    public function stopReserved($id, $status = billing::RESERVE_PROCESS_STATUS) {
        $where = $this->_db->parse(( is_array($id) ? "id IN (?l)" : "id = ?i" ) . " AND status = ?", $id, $status);
        return $this->_db->update("bill_reserve", array('status' => billing::RESERVE_STATUS), $where);
    }
    
    /**
     * Возвращает текущий статус списка
     * 
     * @param integer $id  ИД списков
     * @return string
     */
    public function checkStatusReserve($id) {
        return $this->_db->val('SELECT status FROM bill_reserve WHERE uid = ?i AND id = ?i', $this->user['uid'], $id);
    }
    
    /**
     * Переводи список услуг в другой статус
     * 
     * @param integer $id          ИД списка услуг
     * @param string  $status      Статус перевода
     * @return boolean
     */
    public function setReserveStatus($id, $status) {
        $where = $this->_db->parse(is_array($id) ? "id IN (?l)" : "id = ?i", $id);
        $update = array(
            'status' => $status
        );
        if($status == self::RESERVE_CANCEL_STATUS) {
            $orders = $this->getOrderInfo($id);
            $orders = array_map(create_function('$array', 'return $array["id"];' ), $orders);
            $update['cancel_time'] = 'now()';
            $update['info']        = implode(",", $orders);
        }
        return $this->_db->update("bill_reserve", $update, $where);
    }
    
    
    /**
     * Взять информацию по списку услуг
     * 
     * @param integer $reserve_id  Ид списка услуг
     * @return type
     */
    public function getOrderInfo($reserve_id) {
        $sql = "SELECT * FROM bill_queue WHERE reserve_id IN (?l) AND uid = ?i";
        return $this->_db->rows($sql, is_array($reserve_id) ? $reserve_id : array($reserve_id), $this->user['uid']);
    }
    
    
    /**
     * Возвращает первый список услуг который ожидает оплаты
     * 
     * @param type $personal_account
     * @return type
     */
    public function getLastReserveOperations() 
    {
        $sql = "
            SELECT id, ammount 
            FROM bill_reserve 
            WHERE uid = ? AND status = ? AND complete_time IS NULL 
            ORDER BY create_time DESC LIMIT 1";
        return $this->_db->row($sql, $this->user['uid'], self::RESERVE_STATUS);
    }
    
    
    

    /**
     * Проверка возможности покупки заказа
     * 
     * @param type $params
     * @return boolean
     */
    public function checkOrder($params)
    {
        $success = false;

        //если заказ не врезерве то он не готов к покупке
        if ($this->checkStatusReserve($params['orderId']) != self::RESERVE_STATUS) {
            return $success;
        }
        
        
        //Получаем заказ c услугами за который хотят занести деньги
        $this->getOrder($params['orderId']);
        
        //Перебираем услуги заказа и проверяем их возможность для покупки
        if ($this->list_service) {
            foreach ($this->list_service as $order) {
                $data = @$order['info'];
                
                switch ($order['op_code']) {
                    
                    //Резерв средств по БС
                    case 136:
                        //в данном случае доп.данные должны быть
                        if ($data) {
                            require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');
                            $reserveInstance = ReservesModelFactory::getInstance($data['type']);

                            if ($reserveInstance) {
                                $reserveInstance->setReserveData($data);
                                //Проверяем можно ли зарезервировать БС
                                $success = $reserveInstance->allowChangeStatus(ReservesModel::STATUS_RESERVE);                            
                            }
                        }

                        break;
                    
                    //Если у услги нет проверки возможности покупки
                    //то поумолчанию разрешаем заносить деньги
                    default:
                        $success = true;
                }    
                
                //Если при проверке заказа хотябы одна услуга 
                //не прошла проверку то отказываемся принимать платеж
                if(!$success) {
                    //@todo: можно сделать еще и более детальную формулировку ошибки при отдаче платежной системе
                    //@todo: но зачем ей это? в этом случае ее задача просто вернуть деньги клиенту
                    break;
                }
            }
        }
        
        return $success;
    }

    

    /**
     * @todo: более не используется см completeOrders()
     * 
    * Оплата услуг в момент когда Yandex касса возвращает aviso запрос
    * TODO: Расскидать по методам оплачиваемых услгу
    * вместо if (op_code == nnn) { ... } $usluga->callMethod($order);
    */
    public function completeAvisoOrders($params)
    {
        $success = false;

        $sql    = "SELECT * FROM bill_queue WHERE uid = ? AND status = ? ORDER by id DESC";
        $orders = $this->_db->rows($sql, $this->user['uid'], self::STATUS_NEW);

        if (sizeof($orders) == 1) {         
            
            //@todo: Костыль!!!
            if ($orders[0]['op_code'] == 136) 
            {
                $order = $orders[0];
                $success = false;
                $this->setPage('orders');
                $data = @$this->list_service[$order['id']]['info'];
                if($data)
                {
                    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');
                    $reserveInstance = ReservesModelFactory::getInstance($data['type']);
                    
                    if(!$reserveInstance) {
                        return false;
                    }
                    
                    $reserveInstance->setReserveData($data);
                    
                    //Уже была зарезервирована - ничего не покупаем
                    //деньги остаются на ЛС
                    if(!$reserveInstance->isStatusNew()) {
                        return false;
                    }
                    
                    $this->transaction = $this->account->start_transaction($this->user['uid'], 0);
                    
                    $ret = $this->account->Buy(
                        $id, 
                        $this->transaction, 
                        $order['op_code'], 
                        $this->user['uid'], 
                        $order['descr'], 
                        $order['comment'], 
                        $order['ammount'], 0);
                
                    if($ret == 0)
                    {
                        //Успешно купили услугу и теперь меняем статус резерва
                        $data['invoice_id'] = $params['invoiceId'];
                        $data['acc_op_id'] = $id;
                        $reserveInstance->setReserveData($data);
                        $success = $reserveInstance->changeStatus(ReservesModel::STATUS_RESERVE);
                    }
                    
                    if($success) 
                    {
                        $this->account->commit_transaction($this->transaction, $this->user['uid'], NULL);

                        $update = array('status'     => self::STATUS_COMPLETE);
                        $this->update($order['id'], $update);
                        $memBuff = new memBuff();
                        $memBuff->delete("last_operation_" . $order['uid'] . "_" . $order['service']);
                    } 
                }
                
            } elseif ($orders[0]['op_code'] == 137) { // покупка автоответа

                $order = $orders[0];
                
                $this->completeOrderAutoresponse($order);
                
            }

        }
    }
    
    /**
     * @todo: более не используется см completeOrders()
     * 
     * Завершение покупки автоответов. Вызывается при ответе Яндекс.Кассы 
     * или при полной оплате с личного счета
     * 
     * @param type $order Заказ из bill_queue
     */
    public function completeOrderAutoresponse($order) 
    {
        if (!$order) {
            return false;
        }
        
        $this->setPage('orders');

        $this->transaction = $this->account->start_transaction($this->user['uid'], 0);

        $ret = $this->account->Buy(
            $id, 
            $this->transaction,
            $order['op_code'], 
            $this->user['uid'], 
            $order['descr'], 
            $order['comment'], 
            $order['ammount'],
            1,
            $order['promo_code']
        );
        
        if ($ret == 0 && isset($order['parent_id']) && intval($order['parent_id'])) {
            require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/autoresponse.php');

            autoresponse::$db = $GLOBALS['DB'];

            // Активация покупки услуги автоответа
            if ($autoresponse = autoresponse::get($order['parent_id'])) {
                $autoresponse->activate();
                $success = true;
            }
        }

        if ($success) {
            $this->account->commit_transaction($this->transaction, $this->user['uid'], NULL);

            $update = array(
                'status'     => self::STATUS_COMPLETE
            );
            $this->update($order['id'], $update);
            $memBuff = new memBuff();
            $memBuff->delete("last_operation_" . $order['uid'] . "_" . $order['service']);
        }
    }

    /**
     * Инициализируем оплату услуг
     * 
     * @param integer $reserve_id  ИД списка который оплачиваем
     * @return boolean
     */
    public function completeOrders($reserve_id = null) 
    {
        $this->start();

        if ($reserve_id === null) {
            $last_reserve = $this->getLastReserveOperations();
            $reserve_id   = $last_reserve['id'];
        }

        $orders = $this->getOrder($reserve_id);
        
        //#0024611 в первую очередь покупаем PRO, потом все остальное
        $pro_op_codes = self::$pro_op_codes;
        $_orders = array();
        foreach ($orders as $order) {
            if ( in_array($order["op_code"], $pro_op_codes) ) {
	            $success = $this->paymentOrder($order);
	            if(!$success) {
	                break;
	            }
                continue;
            }
            $_orders[] = $order;
            if ($order['service'] == 'projects') {
                $this->ordersPromoCodes[$order['option']] = (int)$order['promo_code'];
            }
        }
        
        foreach ($_orders as $order) {
	        $success = $this->paymentOrder($order);
	        if (!$success) {
	            break;
	        }
        }

        if ($success) {
            $update = array(
                'complete_time' => 'now()',
                'status'        => self::RESERVE_COMPLETE_STATUS
            );
            $where = $this->_db->parse('id = ?', $order['reserve_id']);
            $upd   = $this->_db->update('bill_reserve', $update, $where);
            if ($upd) {
                if(!empty($this->_afterQuery)) {
                    $sql = implode(";\r\n", $this->_afterQuery);
                    $this->_db->query($sql);
                    $this->clearAfterQuery();
                }
                
                $this->commit();
                return true;
            }
        } else {
            $this->rollback();
        }
        
        return false;
    }
    
    /**
     * Очищаем выполненные запросы
     */
    public function clearAfterQuery() {
        unset($this->_afterQuery);
    }
    
    /**
     * Если необходмо выполнить запрос после покупки всех услуг
     * 
     * @param string $sql Сам запрос
     * @param mixed  $key Ключ
     */
    public function setAfterQuery($sql, $key) {
        $this->_afterQuery[$key] = $sql;
    }
    
    /**
     * Оплата услуг
     * 
     * @param array $order   Данные по оплачивоемой услуге
     * @return boolean
     */
    public function paymentOrder($order) 
    {
        $_op_code = self::getOpCodeByDiscount($order['op_code']);
        
        switch($_op_code) {
            
            // Верификация банковской картой через ЯКассу
            case 191:
                $error = $this->account->Buy(
                            $account_operation_id, 
                            $this->transaction, 
                            $order['op_code'], 
                            $this->user['uid'], 
                            $order['descr'], 
                            $order['comment']);

                if (!$error) {
                    $success = true;
                    
                    //Устанавливаем флаг верификации
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Verification.php");
                    $verify = new Verification();
                    $verify->cardYK($this->user['uid']);
                    
                    //Обновляем сессию
                    //@todo: эта штука не работает!
                    $session = new session();
                    $session->UpdateVerification($this->user['login']);
                    
                    //Обновляем имя и фамилию
                    $fio = mb_unserialize($order['option']);
                    if (isset($fio['uname']) && isset($fio['usurname'])) {
                        require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
                        $u = new users();
                        $u->GetUserByUID($this->user['uid']);
                        $u->uname = $fio['uname'];
                        $u->usurname = $fio['usurname'];
                        $u->Update($this->user['uid'], $db_errors);
                    }
                    
                    //Назначаем возврат
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing/BillPayback.php");
                    BillPayback::getInstance()->requestPayback(
                            $order['id'],
                            $this->paymentSysParams['invoiceId'],
                            $order['ammount']
                        );
                }
                break;
            
            //------------------------------------------------------------------    
                
            // Верификация через FF
            case 117:
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Verification.php");
                $error        = $this->account->Buy($account_operation_id, $this->transaction, Verification::FF_OP_CODE, $this->user['uid'], $order['comment'], $order['descr'], 1, 0);
                $verify = new Verification();
                $verify->data = unserialize($order['option']);
                $this->_db->query("UPDATE verify_ff SET is_pro = ?, bill_id = ?  WHERE id = ?", false, $account_operation_id, $order['src_id']);
                if ( $verify->verify($this->user['uid']) ) {
                    $this->_db->query("UPDATE verify_ff SET result = TRUE WHERE id = ?", $order['src_id']);
                    $success = true;
                }
                break;
                
            //------------------------------------------------------------------    
                
            // Конкурс
            case 9: 
            case 106:
            case 121:
            case 122:
            case 123:
            case 124:
            case 125: 
            case 126:
            case 127: 
            case 128: 
            case 129: 
            case 130:
                
            // Платный конкурс    
            case 86: 
                
            // Платный проект (вакансия, устаревший опкод)
            case 53:
            
            //Покупка вакансии
            case 113://пользователь не PRO
            case 192://пользователь PRO
                
            
            //Платные услуги проектов
            case 138:
            case 139:
            case 140:
            case 141:
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/uploader/uploader.php");
                // Публикуем платный проект
                if($order['parent_table'] == 'draft_projects') {
                    $draft_id = $order['parent_id'];
                    $this->project_key = md5(uniqid($this->user['uid']));
                    $tmpPrj = new tmp_project($this->project_key);
                    $tmpPrj->initFromDraft($draft_id, $this->user['uid']);
                    $tproject = $tmpPrj->getProject();
                    $isMovedToVacancy = false;
                            
                    // Нет конкурса значит его уже опубликовали и оплатили, оставляем деньги на личном счете
                    if( (int) $tproject['prj_id'] <= 0) {
                        // Не тратим деньги на такой конкурс
                        if($tmpPrj->isKonkurs() && strtotime($tproject['end_date']) <= time()) {
                            $success = true;
                            break;
                        }
                        $error = $tmpPrj->saveProject($this->user['uid'], $proj, $this->ordersPromoCodes);
                        $success = !$error;
                        // Отработали все по конкурсу удаляем его чтобы не мешал (после отработки всех операций)
                        $this->setAfterQuery($this->_db->parse("DELETE FROM draft_projects WHERE id = ? AND uid = ?", $draft_id, $this->user['uid']), $order['parent_id']);
                        $sql = "UPDATE draft_projects SET prj_id = ? WHERE id = ? AND uid = ?";
                        $this->_db->query($sql, $proj['id'], $draft_id, $this->user['uid']);
                    } else {
                        $success = true;
                    }
                } elseif($order['parent_table'] == 'projects') {
                    $prj_id = $order['parent_id'];
                    $this->project_key = md5(uniqid($this->user['uid']));
                    $tmpPrj = new tmp_project($this->project_key);
                    $tmpPrj->setInitFromDB($prj_id);
                    $tproject = $tmpPrj->getProject();
                    $isMovedToVacancy = $tmpPrj->isStateMovedToVacancy();
                    
                    // Если закрыт или заблокирован не тратим деньги
                    if($tproject['closed'] == 't' || $tproject['is_blocked'] == 't') {
                        $success = true;
                        break;
                    }
                    
                    switch($order['option']) {
                        case 'top':
                            $tmpPrj->setAddedTopDays($order['op_count']);
                            break;
                        
                        case 'logo':
                            $LogoFile = new CFile($order['src_id']);
                            $tmpPrj->initLogo($LogoFile, $order['descr']);
                            break;
                        
                        case 'urgent':
                            $tmpPrj->setProjectField('urgent', 't');
                            break;
                        
                        case 'hide':
                            $tmpPrj->setProjectField('hide', 't');
                            break;
                        
                        case 'office':
                            $tmpPrj->setProjectField('old_state', $tproject['state']);
                            $tmpPrj->setProjectField('state', projects::STATE_PUBLIC);
                            
                            //Если оплачивается не перемещенная вакансия 
                            //то работаем как обычно и подымаем вверх
                            if($tproject['state'] != projects::STATE_MOVED_TO_VACANCY) {
                                $tmpPrj->setProjectField('post_now', true);
                            }
                            break;
                    }
                    

                    $error   = $tmpPrj->saveProject($this->user['uid'], $proj, $this->ordersPromoCodes);
                    $success = !$error;

                    
                    if($success) {
                        
                        if (isset($tmpPrj->account_operation_id) && 
                            $tmpPrj->account_operation_id > 0) {
                            
                            $account_operation_id = $tmpPrj->account_operation_id;
                        }
                        
                        switch ($order['option']) {
                            
                            case 'office':
                                if($tproject['state'] == projects::STATE_MOVED_TO_VACANCY) {
                                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
                                    $smail = new smail();
                                    $smail->sendMovedToVacancySuccessPayed($tproject);
                                    
                                    require_once($_SERVER['DOCUMENT_ROOT'] . "/guest/models/GuestInviteModel.php");
                                    $guestInviteModel = new GuestInviteModel();
                                    $guestInviteModel->updateDatePublicBySrc($prj_id, array(
                                        GuestConst::TYPE_PROJECT,
                                        GuestConst::TYPE_VACANCY
                                    ));
                                    
                                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/autoresponse.php");
                                    $autoresponse = new autoresponse();
                                    $autoresponse->reduceByProject($prj_id);
                                }
                                break;
                        }
                    }
                    
                }
                
                //Сохраняем, чтобы показать попап юзеру
                $memBuff = new memBuff();
                $memBuff->add('bill_ok_project_'.$this->user['uid'], $proj['id']);
                //Если это оплата вакансии или конкусра то предлагаем еще купить платные опции
                if (in_array($order['option'], array('office','contest')) && 
                    !$isMovedToVacancy) {
                    
                    $memBuff->add('bill_ok_project_payed_'.$this->user['uid'], true);
                }
                
                break;
                
                
            //------------------------------------------------------------------    
                
                
            // Платное место в карусели
            case 65: // На главной странице
                $catalog = 0;
            case 73: // В каталоге
                require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php";
                $payPlace = new pay_place(isset($catalog) ? $catalog : 1);
                $buyMain  = $this->account->Buy($account_operation_id, $this->transaction, $order['op_code'], $this->user['uid'], $order['comment'], $order['descr'], $order['op_count'], 0, $order['promo_code']);
                if ($buyMain === 0) {
                    $options = unserialize($order['option']);
                    if (isset($options['adHead'])) {
                        //поддержка старого режима
                        $success = $payPlace->addUser($this->user['uid'], $options['adHead'], $options['adText'], $options['adImg']);
                    } else {
                        $success = $payPlace->addUserRequest($this->user['uid'], $options);
                    }
                }
                break;
                
            //------------------------------------------------------------------    
                
            // ПРО аккаунт фрилансеры
            case 47: // Тестовый ПРО на 1 неделю
                if(payed::IsUserWasPro($this->user['uid'])) {
                    return false;
                    break;
                }
            case 15: // Про на 1 месяц (emp)   
            case 48: // Про на 1 месяц (frl)
            case 118: // Про на 3 месяца (emp)
            case 49: // Про на 3 месяца (frl)
            case 119: // Про на 6 месяцeв (emp)
            case 50: // Про на 6 месяцев (frl)
            case 120: // Про на 1 год (emp)
            case 51: // Про на 1 год (frl)
            case 132: // Про на 1 день (frl)
            case 131: // Про на 1 неделю (frl)
            case 163: // Тестовый ПРО на месяц
            case 164: // PROFI на 1 месяц
                require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php";
                require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php";
                require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php";
                
                $payed   = new payed();
                $user    = is_emp($this->user['role']) ? new employer() : new freelancer();
                
                $success = $payed->SetOrderedTarif(
                        $this->user['uid'], 
                        $this->transaction, 
                        $order['op_count'], 
                        $order['comment'], 
                        $order['op_code'], 
                        $order['promo_code'],
                        $error);
                
                if ($success) {
                   
                    if (isset($payed->account_operation_id) && 
                        $payed->account_operation_id > 0) {
                        
                        $account_operation_id = $payed->account_operation_id;
                    }
                    
                    
                    // Обновим сессию сразу!
                    if (get_uid(false) == $this->user['uid']) {
                        $_SESSION['pro_last'] = payed::ProLast($this->user['login']);
                        $_SESSION['pro_last'] = $_SESSION['pro_last']['is_freezed'] ? false : $_SESSION['pro_last']['cnt'];
                        if($_SESSION['pro_last']['is_freezed']) {
                            $_SESSION['payed_to'] = $_SESSION['pro_last']['cnt'];
                        }
                    } else {
                        $membuff = new memBuff();
                        $membuff->set('is_changed_pro_'.$this->user['uid'], true);
                
                        //Этот подход не работает
                        //$session = new session();
                        //$session->UpdateProEndingDate($this->user['login']);
                    }
                    
                    if ($order['auto'] == 't') {
                        $user->setPROAutoProlong('on', $this->user['uid']);
                    } else {
                        $user->setPROAutoProlong('off', $this->user['uid']);
                    }
                    
                    
                    //Опубликовать перемещенные вакансии при покупке ПРО
                    //@todo: Теперь вакансии для всех платные разница лишь в цене для ПРО дешевле
                    //поэтому отключаем публикацию после покупки ПРО
                    //https://beta.free-lance.ru/mantis/view.php?id=28579
                    /*
                    if (is_emp($this->user['role'])) { 	 
                        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php"); 	
                        $project = new projects();	 
                        $project->publishedMovedToVacancy($this->user); 	
 	                }*/
                    
                    
                    //Чистим кеш каталога PROFI пользователей
                    if ($order['op_code'] == 164) {
                        freelancer::clearCacheProfiCatalog();
                    }
                    
                }
                
                break;
                
            //------------------------------------------------------------------    
                
            case 45: // рассылка по каталогу
                require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/masssending.php";
                require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php";

                $masssending = masssending::Get($order['parent_id']);
                $masssending = $masssending[0];

                $error = $this->account->Buy(
                        $account_operation_id,
                        $this->transaction, 
                        masssending::OPER_CODE, 
                        $this->user['uid'], 
                        $order['descr'], 
                        $order['comment'], 
                        $masssending['pre_sum'], 
                        0, 
                        $order['promo_code']);

                if ($error) {
                    break;
                }

                masssending::UpdateAcOpID($order['parent_id'], $account_operation_id);
                $success = (bool)messages::Masssending($masssending['user_id'], $masssending['id'], $masssending['msgtext'], $masssending['posted_time']);
                break;
                
            
            //------------------------------------------------------------------    
                
                
            /**
             * Погашение задолженности
             */    
            case 135:
                $error = $this->account->Buy(
                            $account_operation_id, 
                            $this->transaction, 
                            $order['op_code'], 
                            $this->user['uid'], 
                            $order['descr'], 
                            $order['comment'], 
                            1, 1, 0, 0, 
                            $order['ammount']);
                
                if (!$error) {
                    
                    $success = true;
                    
                    //Начисление погашенной комиссии и удаление блокировки ТУ
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/tu/models/TServiceOrderModel.php");
                    TServiceOrderModel::model()->clearDebt($this->user['uid']);        
                    
                }
                break;
            
                
           //-------------------------------------------------------------------     
                
            
            /**
             * Операции над бизнес логикой резерва средств 
             * при успешном зачислении денег
             */
            case 136:

                $success = false;
                $data = @$this->list_service[$order['id']]['info'];
                
                if (!$data) {
                    break;
                }
                
                require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');
                $reserveInstance = ReservesModelFactory::getInstance($data['type']);
                
                //Уже была зарезервирована или нет нужный параметров то 
                //ничего не покупаем деньги остаются на ЛС
                if (!$reserveInstance || 
                    !isset($this->paymentSysParams['invoiceId'])) {
                    
                    break;
                }

                //Невозможно сменить статус сделки выходим
                $reserveInstance->setReserveData($data);
                if (!$reserveInstance->allowChangeStatus(ReservesModel::STATUS_RESERVE)) {
                    
                    break;
                }

                $ret = $this->account->Buy(
                        $account_operation_id, 
                        $this->transaction, 
                        $order['op_code'], 
                        $this->user['uid'], 
                        $order['descr'], 
                        $order['comment'], 
                        $order['ammount']);

                if($ret === 0) {
                    //Успешно купили услугу и теперь меняем статус резерва
                    $data['invoice_id'] = $this->paymentSysParams['invoiceId'];
                    $data['acc_op_id'] = $account_operation_id;
                    $reserveInstance->setReserveData($data);
                    $success = $reserveInstance->changeStatus(ReservesModel::STATUS_RESERVE);
                }
                
                break;
            
                
            //------------------------------------------------------------------    
                
            
            /**
             * Завершение покупки автоответов.
             */
            case 137:
                
                $success = false;
                
                $ret = $this->account->Buy(
                    $account_operation_id, 
                    $this->transaction,
                    $order['op_code'], 
                    $this->user['uid'], 
                    $order['descr'], 
                    $order['comment'], 
                    $order['ammount'],
                    1,
                    $order['promo_code']
                );                
                
                if ($ret === 0 && isset($order['parent_id']) && intval($order['parent_id'])) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/autoresponse.php');

                    autoresponse::$db = $GLOBALS['DB'];

                    // Активация покупки услуги автоответа
                    if ($autoresponse = autoresponse::get($order['parent_id'])) {
                        $autoresponse->activate();
                        $success = true;
                    }
                }                
                
                break;
                
            
            //------------------------------------------------------------------    
                
                
            // Закрепление в каталоге фрилансеров
            case 142: // В корневом разделе
            case 143: // В разделе
                $is_spec = false;
            case 144: // В подразделе
                if (!isset($is_spec)) {
                    $is_spec = true;
                }
                
                $success = false;
                
                require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer_binds.php');
                $freelancer_binds = new freelancer_binds();
                $freelancer_binds->prepare($this->user['uid'], $order['src_id'], $is_spec, $order['op_count']);

                $ret = $this->account->Buy(
                    $account_operation_id, 
                    $this->transaction, 
                    $order['op_code'], 
                    $this->user['uid'], 
                    $freelancer_binds->bind_info['descr'], 
                    $freelancer_binds->bind_info['comment'], 
                    $order['ammount'],
                    1,
                    $order['promo_code']
                );

                if($ret === 0) {
                    $success = $freelancer_binds->create();
                }
                
                break;
                
                
            //------------------------------------------------------------------    
                
                
            // Продление закрепления в каталоге фрилансеров
            case 148: // В корневом разделе
            case 149: // В разделе
                $is_spec = false;
            case 150: // В подразделе
                if (!isset($is_spec)) {
                    $is_spec = true;
                }
                
                $success = false;

                require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer_binds.php');
                $freelancer_binds = new freelancer_binds();

                $bind_id = $freelancer_binds->isUserBinded(
                        $this->user['uid'], 
                        $order['src_id'], 
                        $is_spec);

                $ret = true;
                if ($bind_id) {
                    $freelancer_binds->getProlongInfo(
                            $this->user['uid'], 
                            $order['src_id'], 
                            $is_spec, 
                            $order['op_count']);

                    $ret = $this->account->Buy(
                        $account_operation_id, 
                        $this->transaction, 
                        $order['op_code'], 
                        $this->user['uid'], 
                        $freelancer_binds->bind_info['descr'], 
                        $freelancer_binds->bind_info['comment'], 
                        $order['ammount']
                    );
                }

                if($ret === 0) {
                    $success = $freelancer_binds->prolong(
                            $bind_id, 
                            $order['op_count'], 
                            $order['src_id'], 
                            $is_spec);
                }
                
                break;
                
                
            //------------------------------------------------------------------    
                
                
            // Поднятие закрепления в каталоге фрилансеров
            case 151: // В корневом разделе
            case 152: // В разделе
                $is_spec = false;
            case 153: // В подразделе
            case 194: //Оплата из буфера
                if (!isset($is_spec)) {
                    $is_spec = $order['src_id'] > 0;
                }
                
                $success = false;
                
                require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer_binds.php');
                $freelancer_binds = new freelancer_binds();
                
                $bind_id = $freelancer_binds->isUserBinded(
                        $this->user['uid'], 
                        $order['src_id'], 
                        $is_spec);
                
                if ($bind_id) {
                    $freelancer_binds->getUpInfo(
                            $this->user['uid'], 
                            $order['src_id'], 
                            $is_spec);
                  
                    $ret = $this->account->Buy(
                        $account_operation_id, 
                        $this->transaction, 
                        $order['op_code'], 
                        $this->user['uid'], 
                        $freelancer_binds->bind_info['descr'], 
                        '', 
                        $order['ammount']
                    );
                    

                    if ($ret === 0) {
                        $success = $freelancer_binds->up(
                                $bind_id, 
                                $order['src_id'], 
                                $is_spec);
                    }
                }

                break;
                
                
            //------------------------------------------------------------------
                
                
            // Закрепление/продление в каталоге услуг
            case 155: // В лендинге
            case 156: // В корневом разделе
            case 157: // В разделе
            case 158: // В подразделе
                $success = false;
                
                require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_binds.php');
                $tservices_binds = new tservices_binds(tservices_binds::KIND_LANDING);
                $tservices_binds->setKindByOpCode($_op_code);

                $option = unserialize($order['option']);
                $tservice_id = isset($option['tservice_id']) ? $option['tservice_id'] : $order['option'];
                $is_prolong = isset($option['is_prolong']) ? $option['is_prolong'] : false;

                $tservices_binds->prepare(
                        $this->user['uid'], 
                        $tservice_id, 
                        $order['src_id'], 
                        $order['op_count'], 
                        $is_prolong);

                if ($tservices_binds->bind_info) {
                    $ret = $this->account->Buy(
                        $account_operation_id, 
                        $this->transaction, 
                        $order['op_code'], 
                        $this->user['uid'], 
                        $tservices_binds->bind_info['descr'], 
                        $tservices_binds->bind_info['comment'], 
                        $order['ammount']
                    );

                    if($ret === 0) {
                        $success = $is_prolong ? $tservices_binds->update() : $tservices_binds->create();
                    }
                }
                    
                break;
                
            //------------------------------------------------------------------    
                
            // Поднятие закрепления в каталоге услуг
            case 159: // В лендинге
            case 160: // В корневом разделе
            case 161: // В разделе
            case 162: // В подразделе
            case 193: //Оплата из буфера
                $success = false;
                
                require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_binds.php');
                $tservices_binds = new tservices_binds(tservices_binds::KIND_LANDING);
                $tservices_binds->setKindByOpCode($_op_code);
                
                $bind = $tservices_binds->getItemById($order['src_id']);
                $tservices_binds->makeUpInfo($bind);
                
                if ($tservices_binds->bind_info) {
                    $ret = $this->account->Buy(
                        $account_operation_id, 
                        $this->transaction, 
                        $order['op_code'], 
                        $this->user['uid'], 
                        $tservices_binds->bind_info['descr'], 
                        $tservices_binds->bind_info['comment'], 
                        $order['ammount']
                    );

                    if($ret === 0) {
                        $success = $tservices_binds->update();
                    }
                }
                break;
        }

        if($success) {
            $update = array(
                'status'     => self::STATUS_COMPLETE
            );
            $this->update($order['id'], $update);
            $memBuff = new memBuff();
            $memBuff->delete("last_operation_" . $order['uid'] . "_" . $order['service']);
            
            
            //Если только что была оплата из яндекс.кассы
            if ($this->paymentSysParams['invoiceId']) {
                
                
                //Фиксируем ID транзакции
                $label = op_codes::getLabel($order['op_code']);
                if (isset($account_operation_id) && 
                    $account_operation_id > 0) {
                    
                    $label = (empty($label)?'':"{$label},") . "trans_{$account_operation_id}"; 
                }
                
                
                // Посылаем данные о покупке в google analytics
                $this->_db->query("SELECT pgq.insert_event('statistic', 'service_payed', ?)", 
                http_build_query(array(
                    'is_emp' => is_emp($this->user['role']), 
                    'label' => $label, 
                    'ammount' => floatval($order['ammount']), 
                    'cid' => $this->getCid()
                )));
            }
            
        }
        
        return $success;
    }
    
    /**
     * Переключает у всех услуг атопродление (т.к. автопродление для некоторых услуг едино)
     * 
     * @param string    $service    Название сервиса
     * @param boolean   $auto       Автопродление (включено/выключено)
     * @param integer   $id         Ид определенной услуги
     * @return \billing
     */
    public function setUpdateAuto($service, $auto = false, $id = null) {
        $where = $this->_db->parse('uid = ? AND status = ? AND service = ?' . ( $id != null ? " AND id = ?" : "" ), $this->user['uid'], self::STATUS_NEW, $service, $id);
        $this->_db->update('bill_queue', array('auto' => $auto), $where);
    }
    
    /**
     * Сумма покупок в списке услуг
     * 
     * @param string $status  Статус услуг
     * @return integer
     */
    public function getTotalAmmountOrders($status = billing::STATUS_NEW) {
        $sql = "SELECT SUM(ammount) as ammount FROM bill_queue WHERE uid = ?i AND status = ?";
        return $this->_db->val($sql, $this->user['uid'], $status);
    }
    
    /**
     * Поиск определенных услуг из списка
     */
    public function findOrders($where = "") {
        $sql = "SELECT * FROM bill_queue WHERE uid = ?i" . $where;
        return $this->_db->rows($sql, $this->user['uid']);
    }
    
    /**
     * Отмена и удаление операций
     * @param array $orders
     */
    public function cancelAndRemoveOrders($orders) {
        if(!empty($orders)) { 
            $reserve = array_filter( array_map(create_function('$array', 'if($array["status"] == "reserve") return $array["reserve_id"];' ), $orders) );
            $ids     = array_map(create_function('$array', 'return $array["id"];' ), $orders);
            // Отменяем резерв если он уже инициирован к этому черновику
            if(!empty($reserve)) {
                $this->setReserveStatus($reserve, billing::RESERVE_CANCEL_STATUS);
            }
            // Удаляем все из списка (будем заносить заного
            $this->update($ids, array('status' => billing::STATUS_DELETE, 'delete_time' => 'NOW()'));
        }
    }
    
    
    
    
    
    /**
     * Список услуг по статусу
     * 
     * @param string $status  Статус услуг
     * @return array
     */
    public function getOrders($status = billing::STATUS_NEW) 
    {
        $sql = "
            SELECT * 
            FROM bill_queue 
            WHERE uid = ?i AND status = ? 
            ORDER by id ASC";
        $this->list_service = $this->_db->rows($sql, $this->user['uid'], $status);
        $this->initAdditionalInfoOrder();
        return $this->list_service;
    }
    
    
    /**
     * Список услуг по заказу
     * 
     * @param string $status  Статус услуг
     * @return array
     */
    public function getOrder($order_id, $status = billing::STATUS_RESERVE) 
    {
        $sql = "
            SELECT * 
            FROM bill_queue 
            WHERE uid = ?i AND status = ? AND reserve_id = ?i 
            ORDER by id ASC";
        $this->list_service = $this->_db->rows($sql, $this->user['uid'], $status, $order_id);
        $this->initAdditionalInfoOrder();
        return $this->list_service;
    }    
    
    
    
    /**
     * Инциализация дополнительной информации для платежей
     */
    public function initAdditionalInfoOrder() 
    {
    	$this->pro_exists_in_list_service = false;
        $get_parents_info = array();
        
        foreach($this->list_service as $order) {
            $services[$order['id']] = $order;
            
            if($order['parent_table'] != '') {
                $get_parents_info[$order['parent_table']][$order['id']] = $order['parent_id'];
            }
            
            if ( in_array( $order["op_code"], self::$pro_op_codes ) ) {
                $this->pro_exists_in_list_service = true;
            }
        }
        
        $this->list_service = $services;
        
        
        if(!empty($get_parents_info)) {
            foreach($get_parents_info as $table => $ids) {
                $sql  = "SELECT * FROM {$table} WHERE id IN (?l)";
                $info = $this->_db->rows($sql, $ids);
                
                foreach($info as $k=>$val) {
                    $result[$val['id']] = $val;
                }
                
                foreach($ids as $k=>$val) {
                    $this->list_service[$k]['info'] = $result[$val];
                }
            }
        }
    }
    
    
    
    /**
     * Количество услуг по статусу
     * 
     * @param string $status  Статус услуг
     * @return array
     */
    public function getCountListServices($status = billing::STATUS_NEW) {
        $sql = "SELECT count(*) FROM bill_queue WHERE uid = ?i AND status = ?";
        $this->count = $this->_db->val($sql, $this->user['uid'], $status);
        return $this->count;
    }
    
    /**
     * Обработка входящей операции помещения услуги в "Список услуг" согласно входящей op_code
     * 
     * @param integer $op_code      ИД операции
     * @param array   $code         Данные операции (если есть на момент вызова функции)
     * @return array
     */
    public function prepareOperationCode($op_code, $code = null) {
        $code = ( $code == null ? current( op_codes::getCodes($op_code) ) : $code );
        $_op_code = self::getOpCodeByDiscount($op_code);
        $data = array();
        switch($_op_code) {
            // Конкурс
            case 9:
            case 106:
            case 121:
            case 122:
            case 123:
            case 124:
            case 125: 
            case 126:
            case 127: 
            case 128: 
            case 129: 
            case 130:
                
            //Платный конкурс    
            case 86: 
                
            //Платный проект (вакансия, устаревший опкод)
            case 53:
            
            //Покупка вакансии
            case 113://пользователь не PRO
            case 192://пользователь PRO
                

            //Платные услуги проектов
            case 138:
            case 139:
            case 140:
            case 141:
                if(is_emp($this->user['role'])) {
                    $data = array(
                        'ammount' => 0,
                        'pro_ammount' => 0,
                        'descr'   => "",
                        'comment' => "Платный проект / ",
                        'service' => 'projects'
                    );
                    
                    if(!empty($this->_option)) {
                        $i = 0;
                        if($this->_option['items']['bold'] > 0) {
                            $data['option']  = 'bold';
                            $data['comment'] .= ($i++?', ':'').'жирный шрифт';
                            $data['ammount'] += $this->_option['items']['bold'];
                        }
                        if($this->_option['items']['color'] > 0) {
                            $data['option']   = 'color';
                            $data['comment'] .= ($i++?', ':'').'подсветка фоном';
                            $data['ammount'] += $this->_option['items']['color'];
                            
                        }
                        if($this->_option['items']['urgent'] > 0) {
                            $data['option']   = 'urgent';
                            $data['comment'] .= ($i++?', ':'').'срочный';
                            $data['ammount'] += $this->_option['items']['urgent']["no_pro"];
                            $data['pro_ammount'] += (int)$this->_option['items']['urgent']["pro"];
                        }
                        if($this->_option['items']['hide'] > 0) {
                            $data['option']   = 'hide';
                            $data['comment'] .= ($i++?', ':'').'скрытый';
                            $data['ammount'] += $this->_option['items']['hide']["no_pro"];
                            $data['pro_ammount'] += (int)$this->_option['items']['hide']["pro"];
                        }
                        if($this->_option['items']['logo'] > 0) {
                            $data['option']   = 'logo';
                            $data['descr']    = $this->_option['logo_link'];
                            $data['src_id']   = $this->_option['logo_id'];
                            $data['comment'] .= ($i++?', ':'').'логотип';
                            $data['ammount'] += $this->_option['items']['logo']["no_pro"];
                            $data['pro_ammount'] += (int)$this->_option['items']['logo']["pro"];
                        }
                        if($this->_option['items']['office'] > 0) {
                            $data['option']   = 'office';
                            $data['comment'] .= ($i++?', ':'').'в офис';
                            $data['ammount'] += $this->_option['items']['office'];
                        }
                        if($this->_option['items']['top'] > 0) {
                            $data['option']   = 'top';
                            $data['op_count'] = $this->_option['addTop'];
                            $topDays = $this->_option['addTop'];
                            $data['comment'] .= ($i++?', ':'').'закрепление наверху на '.$topDays.' '.  getTermination($topDays, array(0 => 'день', 1 => 'дня', 2=> 'дней'));
                            $data['ammount'] += $this->_option['items']['top']["no_pro"];
                            $data['pro_ammount'] += (int)$this->_option['items']['top']["pro"];
                        }
                        if($this->_option['items']['contest'] > 0) {
                            $data['option']   = 'contest';
                            $data['comment']  = 'Публикация конкурса';
                            $data['ammount'] += is_pro() ? $this->_option['items']['contest']["pro"] : $this->_option['items']['contest']["no_pro"];
                            $data['pro_ammount'] += $this->_option['items']['contest']["pro"];
                        }
                        
                        $data['parent_id'] = $this->_option['prj_id'];
                        if($this->_option['is_edit']) {
                            $data['parent_table'] = 'projects';
                        } else {
                            $data['parent_table'] = 'draft_projects';
                        }
                    }
                }
                break;
            // Поднятие платных мест
            case 21:
                if(!is_emp($this->user['role'])) {
                    $data = array(
                        'ammount' => round( $code['sum'], 2),
                        'descr'   => "",
                        'comment' => $code['op_name'],
                        'service' => 'first_page_up'
                    );
                    if(!empty($this->_option)) {
                        $data['descr']    = $this->_option['prof_id'];
                        $data['src_id']   = $this->_option['prof_id'];
                        $data['ammount']  = $this->_option['sum'];
                        if ($this->_option['prof_id'] == -1) {
                            $data['comment'] = 'Поднятие платного места на главной';
                        } else {
                            $data['comment'] = 'Поднятие платного места в каталоге';
                        }
                    }
                }
                break;
               
            // Поднятие на первое место в платных местах
            case 145:
            case 146:
            case 154:
                if(!is_emp($this->user['role'])) {
                    $data = array(
                        'ammount' => $code['sum'],
                        'descr'   => "",
                        'comment' => $code['op_name'],
                        'service' => 'first_page_top'
                    );
                    if(!empty($this->_option)) {
                        $data['descr']    = $this->_option['prof_id'];
                        $data['src_id']   = $this->_option['prof_id'];
                        $data['comment'] = $this->_option['comment'];
                    }
                }
                break;
                
                
            // Пополнение счета
            case 135:
                    $data = array(
                        'ammount'  => $this->_option['acc_sum'],
                        'descr'    => "",
                        'comment'  => "Погашение задолженности - ".$this->_option['acc_sum']
                    );
                break;
            
            //Резервирование средств
            case 136:
                    
                    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');

                    $reserve_data = @$this->_option['reserve_data'];
                    if (!$reserve_data) { 
                        break;
                    }
                    
                    $comment = ReservesModelFactory::getInstance($reserve_data['type'])
                                    ->getBillComment($reserve_data['src_id']);

                    $data = array(
                        'ammount'  => $this->_option['acc_sum'],
                        'parent_id' => $reserve_data['id'],
                        'parent_table' => 'reserves',
                        //'descr' => $code['op_name'],
                        'comment' => $comment
                    );
                break;

            // Автоответы
            case 137: 
                    $data = array(
                        'ammount'  => $this->_option['acc_sum'],
                        'comment' => 'Покупка автоответа',
                        'parent_id' => $this->_option['autoresponse_id'],
                        'parent_table' => 'autoresponse',
                    );
                break;
            
            
            // Платное место в карусели
            case 65: // На главной странице
            case 73: // В каталоге
                if(!is_emp($this->user['role'])) {
                    $data = array(
                        'ammount' => round( $code['sum'], 2),
                        'descr'   => "Оплачено",
                        'comment' => "Оплата новых платных мест",
                        'service' => 'pay_place'
                    );
                    if(!empty($this->_option)) {
                        $data['option'] = serialize($this->_option);
                        //поддержка старого режима где небыло возможности 
                        //приобрести несколько размещений
                        if (isset($this->_option['num'])) {
                            $data['ammount']  = $data['ammount'] * $this->_option['num'];
                            $data['op_count'] = $this->_option['num'];
                        }
                    }
                }
                break;
                
            // Аккаунт PROFI для фрилансера    
            case 164:// Аккаунт PROFI на 1 месяц
                $to = 'на 1 месяц';
                
                if(!is_emp($this->user['role'])) {
                    $data = array(
                        'ammount'  => round( $code['sum'] * 10, 2),
                        'descr'    => "Все разделы",//???
                        'comment'  => "Аккаунт PROFI {$to}",
                        'service'  => 'pro'
                    );
                }                
                break;
                
            // Аккаунт ПРО для фрилансера
            case 47: // Тестовый аккаунт ПРО
                if($this->IsUserWasPro()) {
                    break;
                }
                $to = 'на 1 неделю';
            case 132: // Аккаунт ПРО на 1 день
                $to = !$to ? 'на 1 день' : $to;
            case 131: // Аккаунт ПРО на 1 неделю
                $to = !$to ? 'на 1 неделю' : $to;   
            case 163: //Тестовый ПРО на месяц    
            case 48:  // Аккаунт ПРО на 1 месяц
                $to = !$to ? 'на 1 месяц' : $to;
            case 49: // Аккаунт ПРО на 3 месяца
                $to = !$to ? 'на 3 месяца': $to;
            case 50: // Аккаунт ПРО на 6 месяцев
                $to = !$to ? 'на 6 месяцев' : $to;
            case 51: // Аккаунт ПРО на 12 месяцев
                $to = !$to ? 'на 1 год' : $to;
                if(!is_emp($this->user['role'])) {
                    $data = array(
                        'ammount'  => round( $code['sum'] * 10, 2),
                        'descr'    => "Все разделы",
                        'comment'  => "Аккаунт PRO {$to}",
                        'service'  => 'pro'
                    );
                }
                break;  
            // Аккаунт ПРО для работодателей    
            case 15:  // Аккаунт ПРО на 1 месяц
                $code['sum'] = ( $code['sum'] * payed::PRICE_EMP_PRO) / 10;
                $to = 'на 1 месяц';
            case 118: // Аккаунт ПРО на 3 месяца
                $to = !$to ? 'на 3 месяца': $to;
            case 119: // Аккаунт ПРО на 6 месяцев
                $to = !$to ? 'на 6 месяцев' : $to;
            case 120: // Аккаунт ПРО на 12 месяцев
                $to = !$to ? 'на 1 год' : $to;
                if(is_emp($this->user['role'])) {
                    $data = array(
                        'ammount' => round( $code['sum'] * 10, 2),
                        'descr'   => "Все разделы",
                        'comment' => "Аккаунт PRO {$to}",
                        'service' => 'pro'
                    );
                }
                break;
            case 45:
                $data = array(
                    'ammount' => round($this->_option['amount'], 2),
                    'parent_id' => $this->_option['masssending_id'],
                    'parent_table' => 'mass_sending',
                    'descr'   => "Рассылка по каталогу",
                    'comment' => "Рассылка по каталогу",
                    'service' => 'massending',
                );
                break;
            case 117: // Оплата верификации
                $data = array(
                    'option'  => serialize($this->_option['data']),
                    'src_id'  => $this->_option['prev']['id'],
                    'ammount' => round( $code['sum'], 2),
                    'descr'   => "Верификация через сервис FF.RU",
                    'comment' => "Верификация через сервис FF.RU",
                    'service' => 'verify_ff'
                );
                break;
            case 133: 
                    $data = array(
                        'ammount'  => round( $code['sum'] * 10, 2),
                        'descr'    => "Верификация WebMoney",
                        'comment'  => "Верификация WebMoney",
                        'service'  => 'verify_webmoney'
                    );
                break;
            
            //Верификация банковской картой через ЯКассу
            case 191:
                    $data = array(
                       'ammount'  => round($code['sum'], 2),
                       'descr'    => "Верификация банковской картой",
                       'comment'  => "Верификация банковской картой",
                       'option'   => serialize($this->_option)
                    );
                break;         

            // Закрепление в каталоге фрилансеров
            case 142: // Корневой каталог
            case 143: // Раздел
            case 144: // Подраздел
            case 148: // Продление в корневом каталоге
            case 149: // Продление в разделе
            case 150: // Продление в подразделе
                if(!is_emp($this->user['role'])) {
                    
                    //Получаем диф.цену
                    $sum = op_codes_price::getOpCodePrice(
                            $op_code, 
                            $this->_option['prof_id']);                    
                    
                    $code['sum'] = $sum?$sum:$code['sum'];
                    
                    $data = array(
                        'ammount' => $code['sum'],
                        'descr'   => "",
                        'comment' => "Закрепление в каталоге фрилансеров",
                        'service' => 'frlbind'
                    );
                    
                    if(!empty($this->_option)) {
                        $data['src_id']   = $this->_option['prof_id'];
                        $data['ammount']  = $data['ammount'] * $this->_option['weeks'];
                        $data['op_count'] = $this->_option['weeks'];
                    }
                }
                break;
                
             // Поднятие закрепления в каталоге фрилансеров
            case 151: // Корневой каталог
            case 152: // Раздел
            case 153: // Подраздел
            case 194: // Оплата из буфера
                if(!is_emp($this->user['role'])) {
                    
                    //Получаем диф.цену
                    $sum = op_codes_price::getOpCodePrice(
                            $op_code, 
                            $this->_option['prof_id']);
                    
                    $code['sum'] = $sum?$sum:$code['sum'];
                    
                    $data = array(
                        'ammount' => $code['sum'],
                        'descr'   => "",
                        'comment' => "Поднятие закрепления в каталоге фрилансеров",
                        'service' => 'frlbindup'
                    );
                    
                    if(!empty($this->_option)) {
                        $data['src_id']   = $this->_option['prof_id'];
                        $data['op_count'] = 1;
                    }
                }
                break;
                
            // Закрепление в каталоге услуг
            case 155: // Лендинг
            case 156: // Корневой каталог
            case 157: // Раздел
            case 158: // Подраздел
                if(!is_emp($this->user['role'])) {
                    
                    //Получаем диф.цену
                    $sum = op_codes_price::getOpCodePrice(
                            $op_code, 
                            $this->_option['prof_id']);                    
                    
                    $code['sum'] = $sum?$sum:$code['sum'];
                    
                    $data = array(
                        'ammount' => $code['sum'],
                        'descr'   => "",
                        'comment' => "Закрепление в каталоге услуг",
                        'service' => 'tservicebind'
                    );
                    
                    if(!empty($this->_option)) {
                        $data['src_id']   = $this->_option['prof_id'];
                        $data['ammount']  = $data['ammount'] * $this->_option['weeks'];
                        $data['op_count'] = $this->_option['weeks'];
                        $data['option'] = serialize(array(
                            'tservice_id' => $this->_option['tservice_id'],
                            'is_prolong' => $this->_option['is_prolong']
                        ));
                    }
                }
                break;
                
            // Поднятие закрепления в каталоге услуг
            case 159: // Лендинг
            case 160: // Корневой каталог
            case 161: // Раздел
            case 162: // Подраздел
            case 193: // Оплата из буфера
                if(!is_emp($this->user['role'])) {
                    
                    //Получаем диф.цену
                    $sum = op_codes_price::getOpCodePrice(
                            $op_code, 
                            $this->_option['prof_id']);                    
                    
                    $code['sum'] = $sum?$sum:$code['sum'];
                    
                    $data = array(
                        'ammount' => $code['sum'],
                        'descr'   => "",
                        'comment' => "Поднятие закрепления в каталоге услуг",
                        'service' => 'tservicebindup'
                    );
                    
                    if(!empty($this->_option)) {
                        $data['src_id']   = $this->_option['bind_id'];
                        $data['op_count'] = 1;
                    }
                }
                break;
        }
        
        unset($this->_option); // Сбрасываем опции чтобы не путалось при покупке сразу нескольких услуг на странице
        
        //Перебиваем примечание к платежу по опкоду (любому и скидочному тоже)
        if($_descr = $this->getDescrByOpCode($op_code)) {
            $data['descr'] = $_descr;
        }
        
        if ($this->promoCode) {
            $data['promo_code'] = $this->promoCode['id'];
            if (!isset($this->promoCode['is_original_price'])) {
                $promoCodes = new PromoCodes();
                $data['ammount'] = $data['ammount'] - $promoCodes->getDiscount($this->promoCode, $data['ammount']);
                
                //@todo: для чего pro_ammount ?
                if (isset($data['pro_ammount']) && $data['pro_ammount'] > 0) {
                    $data['pro_ammount'] = $data['pro_ammount'] - $promoCodes->getDiscount($this->promoCode, $data['pro_ammount']);
                }
            }
        }
        
        return $data;
    }
    
    public function IsUserWasPro($status = self::STATUS_RESERVE) {
        $use_reserve_pro = $this->search(self::$frl_default_service['pro'], $status);
        return ( payed::IsUserWasPro($this->user['uid']) || !empty($use_reserve_pro) );
    }


    /**
     * проверяет не вышел ли срок давности у зарезервированных услуг
     */
    public static function checkOldReserve() 
    {
        //require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
        
        global $DB;

        $reservationDays = billing::RESERVATION_DAYS;

        $sql = "
            SELECT br.id as reserve_id, u.uid, u.login, u.uname, u.usurname, u.email
            FROM bill_reserve br
            INNER JOIN users u ON u.uid = br.uid
            WHERE br.status = 'reserve'
            AND (NOW() - br.create_time) > interval '$reservationDays days'
        ";
        $res = $DB->rows($sql);
        
        if (!$res) {
            return false;
        }
        
        //$log = new log("billing/cancel-".SERVER.'-%d%m%Y.log', 'a', "%d.%m.%Y %H:%M:%S:\r\n");
        //$reserves = array();
        
        foreach($res as $reserve) {
            $bill = new billing($reserve['uid']);
            //$log->writeln("login:{$reserve['login']}, uid:{$reserve['uid']}, account:{$bill->acc['id']}, acc_sum:{$bill->acc['sum']}, reserve:{$reserve['reserve_id']}\r\n");
            $bill->setReserveStatus($reserve['reserve_id'], billing::RESERVE_CANCEL_STATUS);
            //$log->write("success:{$success}");
            /* Отключаем уведомление
            if ($success) {
                if ($bill->updateOrderListStatus($reserve['reserve_id'], billing::STATUS_NEW)) {
                    $reserves[] = $reserve;
                    $barNotify = new bar_notify($reserve['uid']);
                    $barNotify->addNotify('bill', 'orders', 'Список заказов автоматически отменен.');
                }
            }
            */
        }
        
        /* Отключаем уведомление
        $smail = new smail();
        $smail->sendCancelReserve($reserves, $reservationDays);
        */
        
        return count($res);
    }
    
    /**
     * сохраняет последний список счетов с заданным статусом в $this->list_service
     * @param string $status
     */
    public function getLastReserve($status = self::RESERVE_COMPLETE_STATUS) {
        $sql = '
            WITH last_operations as (
                SELECT *
                FROM bill_reserve
                WHERE uid = ?i
                    AND status = ?
                ORDER BY complete_time DESC, id DESC
                LIMIT 1
            )
            SELECT lo.ammount as res_ammount, lo.complete_time as res_complete_time, lo.id as res_id, bq.*
            FROM last_operations lo
            INNER JOIN bill_queue bq ON bq.reserve_id = lo.id';
        $this->list_service = $this->_db->rows($sql, $this->user['uid'], $status);

        return $this->list_service;
    }


    /**
     * Функция для автооплаты (учитывает состояние личного счета)
     *
     * @param billing $bill     Объект биллинга (должен быть подготовлен резерв перед оплатой @see self::preparePayments())
     * @param float   $ammount  Сумма для списывания
     */
    static public function autoPayed(billing $bill, $ammount) {
        if($bill->reserved <= 0) return false;
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wallet/wallet.php");

        $complete = false;
        $wallet   = WalletTypes::initWalletByType($bill->user['uid']);
        // Пытаемся оплатить из личного счета иначе автооплатой если она включена
        // @todo при неудаче покупки пробовать ли оплатить через привязанный кошелек?
        if($bill->acc['sum'] >= $ammount) {
            $bill->transaction = $bill->account->start_transaction($bill->user['uid'], 0);
            $complete = $bill->completeOrders($bill->reserved);
            if($complete) {
                $bill->account->commit_transaction($bill->transaction, $bill->user['uid'], NULL);
            }
        } else if(WalletTypes::checkWallet($wallet)) {
            if($wallet->api->checkToken()) {
                // Проводим списывание денег
                $complete = $wallet->payment($ammount);
            } else {
                // Ключ не действителен сообщаем об этом владельцу
                $complete = false;
            }
        }

        // Если все плохо отменяем операции
        if(!$complete) {
            $success = $bill->setReserveStatus($bill->reserved, billing::RESERVE_CANCEL_STATUS);
            if($success) {
                $bill->updateOrderListStatus($bill->reserved, billing::STATUS_DELETE);
            }
        }

        return $complete;
    }

    
    
    /**
     * Отменить заказ по ID
     * 
     * @param type $id
     * @return boolean
     */
    public function cancelReserveById($id)
    {
        if ($this->setReserveStatus($id, self::RESERVE_CANCEL_STATUS)) {
            return $this->updateOrderListStatus($id, self::STATUS_DELETE);
        }
        
        return false;
    }

    
    /**
     * По услуге найти и отменить заказ
     * 
     * @param type $parent_table
     * @param type $parent_id
     * @return boolean
     */
    public function cancelReserveByOrder($parent_table, $parent_id)
    {
        $reserve_id = $this->_db->val("SELECT reserve_id 
                            FROM bill_queue 
                            WHERE uid = ?i 
                            AND parent_table = ? 
                            AND parent_id = ?i", 
                $this->user['uid'], 
                $parent_table, 
                $parent_id);
                
        if ($reserve_id > 0) {
            return $this->cancelReserveById($reserve_id);
        }
        
        return false;
    }

    



    /**
     * Проверяем есть ли у пользователя хотя бы одно включенное автопроделние
     *
     * @param $uid
     */
    static public function isAnyAutoPayed($uid) {
        global $DB;

        $sql = "
        SELECT
        (CASE
            WHEN is_pro_auto_prolong THEN true
            WHEN ua.user_id>0 THEN true
            ELSE false END) as auto_pay
        FROM users u
        LEFT JOIN users_first_page_autopay ua ON ua.user_id = u.uid
        WHERE uid = ?i LIMIT 1";

        return $DB->val($sql, $uid);
    }

    /**
     * Возвращает все услуги которые включены в автопродление
     *
     * @param $uid
     * @return mixed
     */
    static public function getAllAutoPayed($uid) {
        global $DB;

        $sql = "
        SELECT -2 as id, u.is_pro_auto_prolong, 'Аккаунт PRO' as name, 570 as cost FROM users u WHERE uid = ?i AND is_pro_auto_prolong = true

        UNION ALL

        SELECT ua.profession as id, true,
        (CASE
          WHEN ua.profession = 0 THEN 'Каталог фрилансеров'
          ELSE p.name END) as name,
        (CASE
          WHEN ua.profession = 0 THEN 750
          WHEN ua.profession = -1 THEN 4500
          ELSE 300 END) as cost
        FROM users_first_page_autopay ua
        INNER JOIN professions p ON p.id = ua.profession
        WHERE user_id = ?i";

        return $DB->rows($sql, $uid, $uid);
    }

    /**
     * Возвращает ИП операций по названию сервиса
     *
     *
     * @param string $service
     * @return array
     */
    static public function getOpcodesByAutopayed($service = 'all') {
        if($service == 'pro') {
            $result = array(
                47, 48, 49, 50, 51, // ПРО фрилансер
                15, 118, 119, 120 // ПРО работодатель
            );
        } else {
            $result = array(
                47, 48, 49, 50, 51, // ПРО фрилансер
                15, 118, 119, 120 // ПРО работодатель
            );
        }

        return $result;
    }

    /**
     * Считаем что по чем
     *
     * @todo: непонятно назначение данного метода, неиспользуется в общем функционале
     * @todo: возможно для расчета суммы списания с ЛС и остаток на оплату
     * 
     * @param $Y        Сумма Итого к оплате
     * @param null $X   Сумма на личном счете
     */
    public function calcPayedSum($Y, $X = null) {
        if($X === null) {
            $X = $this->acc['sum'];
        }

        $R = $Y - $X;
        if($R <= 0) {
            $this->payed_sum = array(
                'pay'   => $Y, // Сумма на кнопке
                'acc'   => $Y, // С личного счета будет списано
                'ref'   => -1  // Возврат на счет
            );
        } else {
            $N = ceil($R) < self::MINIMUM_PAYED_SUM  ? self::MINIMUM_PAYED_SUM : ceil($R);

            if($N == $Y) {
                $this->payed_sum = array(
                    'pay'   => $N,
                    'acc'   => -1,
                    'ref'   => -1
                );
            }

            if($N != $Y && $Y > self::MINIMUM_PAYED_SUM) {
                if($Y - $N < 0) {
                    $this->payed_sum = array(
                        'pay'   => $N,
                        'acc'   => -1,
                        'ref'   => ($N - $Y)
                    );
                } else {
                    $this->payed_sum = array(
                        'pay'   => $N,
                        'acc'   => ($Y - $N),
                        'ref'   => -1
                    );
                }
            }

            if($N != $Y && $Y < self::MINIMUM_PAYED_SUM) {
                $this->payed_sum = array(
                    'pay'   => self::MINIMUM_PAYED_SUM,
                    'acc'   => -1,
                    'ref'   => ($N - $Y)
                );
            }
        }
    }
    
    
    /**
     * Очистить очередь заказов 
     * будут помечены как удаленные - новые и зарезервированные записи
     * 
     * @todo: рекомендуется более не использовать так как теперь мы сохраняем
     * все заказы и только чтобы была возможность у платежных систем занести деньги
     * если неполучилось это сделать сразу.
     * 
     * Но совсем старые не оплаченные заказы с теченимем времени (self::RESERVATION_DAYS) 
     * по крону очиoщается из истории
     * 
     */
    function cancelAllNewAndReserved() 
    {
        $this->clearOrders(array(self::STATUS_NEW, self::STATUS_RESERVE));
        $reserved = $this->getReserveOperationsByStatus(self::RESERVE_STATUS);
        if ($reserved) {
            $reserved = array_map(create_function('$array', 'return $array["id"];' ), $reserved);
            $this->setReserveStatus($reserved, self::RESERVE_CANCEL_STATUS);
        }
        
        //@todo: пока так чистим старые ключи редиректа в сесии
        if (count($_SESSION)) {
            foreach($_SESSION as $key => $value) {
                if (strpos($key, 'quick') === 0) {
                    unset($_SESSION[$key]);
                }
            }
        }
    }
    
    
    
    
    function getDescrByOpCode($opCode)
    {
        $_descr = false;
        
        if (isset(self::$descr_op_codes[$opCode])) {
            $_descr = self::$descr_op_codes[$opCode];
        }
        
        return $_descr;
    }



    /**
     * Получить OpCode услуги из OpCode ее скидки
     * 
     * @param type $opCode
     * @return type
     */
    static function getOpCodeByDiscount($opCode)
    {
        $saleOpCode = $opCode;
        $_op_codes = array_flip(self::$discount_op_codes);
        
        if (isset($_op_codes[$opCode])) {
            $saleOpCode = $_op_codes[$opCode];
        }
                                
        return $saleOpCode;
    }
    
    /**
     * Расширяет массив op_codes, добавляя в него скидочные значения, 
     * соответствующие его элементам
     * @param array $op_codes
     * @return array
     */
    public static function extendOpCodes($op_codes)
    {
        $discount_op_codes = self::$discount_op_codes;
        foreach ($discount_op_codes as $op_code => $discount_op_code) {
            if (in_array($op_code, $op_codes)) {
                $op_codes[] = $discount_op_code;
            } elseif (in_array($discount_op_code, $op_codes)) {
                $op_codes[] = $op_code;
            }
        }
        return $op_codes;
    }


    /**
     * Проверить и получить OpCode скидки для указанной услуги
     * иначе вернуть передаваемый OpCode
     * 
     * @todo: сделать попроще если для множества опкодов одно простое условие скидки
     * 
     * @param type $opCode
     * @return int
     */
    function getDiscountOpCode($opCode)
    {
        $saleOpCode = $opCode;
        
        if (isset(self::$discount_op_codes[$opCode])) {
            switch ($opCode) {

                //1) Скидки на карусель - для PROFI цена 239р
                case 65:
                case 73:    
                    
                //3) При поднятии предложений (в общем разделе / в разделах / в подразделах):
                //- для PROFI цена 800р / 320р / 160р
                case 145:
                case 146:
                case 154:
                    
                //4) Скидки при закреплении/продлении услуг ТУ   
                case 155:
                case 156:
                case 157:
                case 158:
        
                //5) Скидки при поднятии закреплений услуг
                case 159:
                case 160:
                case 161:
                case 162:
                    
                //6) Скидки размещение и продление в каталоге фрилансеров
                case 142:
                case 143:
                case 144:
                case 148:
                case 149:
                case 150: 
                    
                //7) Скидки поднятие размещения в каталоге фрилансеров
                case 151: 
                case 152:
                case 153:
                    
                    if ($this->user['is_profi'] == 't') {
                        $saleOpCode = self::$discount_op_codes[$opCode];
                    }

                    break;

            }
        }
        
        return $saleOpCode;
    }
    
    
    //--------------------------------------------------------------------------
    // Новые методы для работы с биллингом
    //--------------------------------------------------------------------------
    
    
    /**
     * Счет ЛС
     * 
     * @return type
     */
    public function getAccSum()
    {
        return @$this->acc['sum'];
    }
    
    
    /**
     * ID аккаунта пользователя
     * 
     * @return type
     */
    public function getAccId()
    {
        return $this->acc['id'];
    }

    
    
    
    
    
    /**
     * Попытка купить зарезервированный заказ
     * путем списания с ЛС
     * 
     * @param type $order_id
     * @param type $op_code
     * @return boolean
     */
    public function buyOrder($order_id, $op_code = null, $paymentSysParams = array())
    {
        $this->paymentSysParams = $paymentSysParams;
        
        //Проверка того что ранее была операция 
        //зачисления средств перед покупкой, иначе выход
        if ($op_code > 0 && 
            !in_array($op_code, billing::$op_code_transfer_money)) {
            return false;
        }
        
        $sql = "
            SELECT * 
            FROM bill_reserve 
            WHERE id = ?i AND uid = ?i AND status = ? AND complete_time IS NULL 
        ";
        
        $reserve = $this->_db->row($sql, $order_id, $this->user['uid'], self::RESERVE_STATUS);
        
        if (!$reserve) {
            return false;
        }
        
        //@todo: из-за проблем с учетом транзакций в классе DB 
        //может возникать ошибка в completeOrders выключаем их вывод
        $this->_db->error_output = false;
        
        $this->startReserved($order_id);
        $this->transaction = $this->account->start_transaction($this->user['uid'], 0);
        $success = $this->completeOrders($order_id);
        
        if ($success) {
            $this->account->commit_transaction($this->transaction, $this->user['uid'], NULL);
            $this->acc['sum'] = $this->acc['sum'] - $reserve['ammount'];
            //@todo: обычно покупка проходит по стороннему запросу от платежного сервиса поэтому сесии там нет
            $_SESSION['ac_sum'] = $this->acc['sum'];
            
            //Обновляем сесию пользователя
            //пока нет необходимости
            //$session = new session();
            //$session->UpdateAccountSum($this->user['login']);
        }
        
        $this->stopReserved($order_id); 
        
        return $success;
    }





    /**
     * Приготовить заказ к оплате
     * 
     */
    public function checkoutOrder($is_personal =  false, $ids = array())
    {
        //Получить весь список услуг для покупки
        $this->getOrders();
        
        $this->payed_sum = 0;
        
        if (!empty($ids)) {
            foreach($this->list_service as $id => $service) {
                if (!in_array($id, $ids)) {
                    continue;
                }
                
                $this->payed_sum += $service['ammount'];
            }
        } else {
            foreach($this->list_service as $service) {
                $this->payed_sum += $service['ammount'];
            }
        }
        
        $insert = array(
            'uid'                  => $this->user['uid'],
            //@todo: флаг в дальнейшей обработке никак не используется
            'is_personal_account'  => $is_personal,
            'ammount'              => $this->payed_sum,
            
            //@todo: нужно передавать каким образом будет проходить платеж
            'payment'              => $this->payment_type
        );
        
        $this->reserved = $this->_db->insert('bill_reserve', $insert, 'id');
        $insert['id'] = $this->reserved; 
        $this->reserve = $insert;
        
        if($this->reserved) {
            $where = $this->_db->parse('uid = ? AND status = ?' . ( !empty($ids) ? " AND id IN (?l)" : "" ), $this->user['uid'], self::STATUS_NEW, $ids);
            $update = array(
                'status'     => self::STATUS_RESERVE,
                'reserve_id' => $this->reserved
            );
            
            if ($this->_db->update('bill_queue', $update, $where)) {
                return $this->reserved;
            }
        }
        
        return false;
    }


    
    /**
     * Сумма к оплате - это сумма заказа минус средства на ЛС
     * Но минимум 10 рублей!
     */
    public function getRealPayedSum()
    {
        $payed_sum = $this->payed_sum;
        
        if (isset($this->promoCode['is_original_price']) && 
            $this->promoCode['is_original_price'] == true) {
            
            $payed_sum = $payed_sum - $this->getPromoCodeModel()->getDiscount(
                    $this->promoCode, 
                    $payed_sum);
        }
        
        $payed_sum = $payed_sum - ($this->acc['sum'] < 0 ? 0 : $this->acc['sum']);
        $payed_sum = ($payed_sum < self::MINIMUM_PAYED_SUM ? 
                self::MINIMUM_PAYED_SUM : 
                $payed_sum);
        
        return ceil($payed_sum);
    }

    
    /**
     * Текущий заказ услуг с нулевой стоимостью
     * 
     * @return type
     */
    public function isPayZero()
    {
        return $this->payed_sum == 0;
    }

    

    /**
     * Достаточно ли средств для оплаты с ЛС
     * 
     * @return type
     */
    public function isAllowPayFromAccount()
    {
        return $this->acc['sum'] >= $this->payed_sum;
    }

    

    /**
     * Общая сумма текущего заказа
     * 
     * @return type
     */
    public function getOrderPayedSum()
    {
        return $this->payed_sum;
    }

    


    /**
     * Получить ID текущего сформированного заказа
     * для передачи в систему оплаты чтобы по ответу 
     * понимать за что пришли средства
     * 
     * @todo: пока не использовать, пока передаю bill_queue id
     * 
     * @return type
     */
    public function getReservedId()
    {
        return $this->reserved;
    }

    


    /**
     * Добавить услугу в корзину
     * 
     * @param type $op_code
     */
    public function addServiceToCart($op_code)
    {
        //Пробуем получить скидку
        $op_code = $this->getDiscountOpCode($op_code);
        //Подготовка к покупке
        $options = $this->prepareOperationCode($op_code);
        
        if (empty($options)) {
            return false;
        }
        
        $data = array(
            'uid'     => $this->user['uid'],
            'op_code' => $op_code,
            'auto'    => false
        );
        
        $insert = array_merge($data, $options);
        $id = $this->_db->insert('bill_queue', $insert, 'id');
        
        return $id;     
    }    
    
    
    
    /**
     * Добавить в заказ только одну услугу 
     * предварительно очистив заказ
     * и приготовить его к оплате
     * 
     * @param int $op_code - код услуги
     * @param array $option - опции заказа
     * 
     * @return int - id заказа
     */
    public function addServiceAndCheckout($op_code, $option = array())
    {
        //Опции заказа
        if(!empty($option)) {
            $this->setOptions($option);
        }
        
        //Удаляем ранее добавленные услуги в заказе со статусом new
        //те не свормированные еще в заказ для опталы
        $this->clearOrders();
        //Добаить услугу в заказ
        $this->addServiceToCart($op_code);
        //Запомнить cid для статистики
        $this->saveCid();
        //Подготовить заказ к оплате
        return $this->checkoutOrder();
    }
    
    
    /**
     * Добавить в заказ только одну услугу
     * предварительно очистив заказ
     * и купить ее при наличии средств на ЛС
     * 
     * @param int $op_code - код услуги
     * @param array $option - опции заказа
     * @return boolean - удалось ли оплатить с ЛС
     */
    public function addServiceAndPayFromAccount($op_code, $option = array())
    {
        $billReserveId = $this->addServiceAndCheckout($op_code, $option);
        
        if ($billReserveId && $this->isAllowPayFromAccount()) {
            return $this->buyOrder($billReserveId);
        }
        
        return false;
    }
    

    
    /**
     * Добавить в заказ только одну услугу
     * предварительно очистив заказ
     * и купить услугу смоимостью 0 рублей
     * 
     * @param int $op_code - код услуги
     * @param array $option - опции заказа
     * @return boolean - удалось ли оплатить с ЛС
     */
    public function addServiceAndPayZero($op_code, $option = array())
    {
        $billReserveId = $this->addServiceAndCheckout($op_code, $option);

        if ($billReserveId && $this->isPayZero()) {
            return $this->buyOrder($billReserveId);
        }        
        
        return false;
    }

    



    /**
     * Опции какой-либо платежной системы
     * могут пригодиться при активации услуги
     * 
     * @param type $value
     */
    public function setPaymentSysParams($value)
    {
        $this->paymentSysParams = $value;
    }
    
    
    /**
     * Получить модель промокодов
     * 
     * @return type
     */
    public function getPromoCodeModel()
    {
        if (!$this->promoCodeModel) {
            require_once(ABS_PATH . "/classes/PromoCodes.php");
            $this->promoCodeModel = new PromoCodes();
        }
        
        return $this->promoCodeModel;
    }

    


    /**
     * Передать инфу о наличии промокода
     * 
     * @param type $type
     * @param type $code
     * @param type $option
     * @return boolean
     */
    public function setPromoCodes($type, $code, $option = array())
    {
        require_once(ABS_PATH . "/classes/PromoCodes.php");
        if (!PromoCodes::IS_ACTIVE) {
            return false;
        }

        $this->getPromoCodeModel();
        $serviceId = constant("PromoCodes::{$type}");
        
        if (!$serviceId) {
            return false;
        }

        $promoCode = $this->getPromoCodeModel()->getByCode($code, $serviceId);
        
        if ($promoCode) {
            $this->promoCode = array_merge($promoCode, $option);
            return true;
        }        

        
        return false;
    }
    
    public function unsetPromoCodes()
    {
        $this->promoCode = null;
    }
    
    /**
     * Сохраняет идентификатор пользователя для статистики
     */
    private function saveCid()
    {
        if (isset($_COOKIE['_ga_cid']) && $_COOKIE['_ga_cid']) {
            $cid = $_COOKIE['_ga_cid'];
            
            $memBuff = new memBuff();
            $memBuff->set("_ga_cid_" . $this->user['id'], $cid);
        }
    }
    
    /**
     * Возвращает идентификатор пользователя для статистики
     * @return string
     */
    private function getCid()
    {
        $memBuff = new memBuff();
        return $memBuff->get("_ga_cid_" . $this->user['id']);
    }
    
    
}