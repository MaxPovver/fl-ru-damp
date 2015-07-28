<?php

/**
 * Общий класс для работы с СБР, в основном со всякими статичными фукнциями, для получения информации о сделках с разных мест сайта, без
 * инициализации и проверок доступа.
 * Также играет роль родительского класса для остальных классов sbr*. Содержит методы работы с БД и общие методы, доступные для перегрузки.
 */
class sbr_meta
{
    const ADMIN_ACCESS = 'A'; // значение параметра ?access для админа.
    const USER_ACCESS  = 'U'; // значение параметра ?access для юзера.
    
    const FRL_PERCENT_TAX = 0.03;
    const EMP_PERCENT_TAX = 0.07;
    
    const FEEDBACK_MAX_LENGTH = 500;
    
    /**
     * время жизни кэша для статистики по СБР для промо-блока в секундах
     */
    const PROMO_STATS_CACHE_TIME = 21600; // 6 часов

    /**
     * дата с которой делаем новые ссылки на договора, и новые пункты договоров
     */
    const NEW_CONTRACT_DATE = '2013-08-01';
    
    /*
     * Типы организации
     */
    const TYPE_IP = 1;
    const TYPE_OOO = 9;
    const TYPE_OAO = 2;
    const TYPE_ZAO = 3;
    const TYPE_PK = 4;
    const TYPE_OO = 5;
    const TYPE_FOND = 6;
    const TYPE_UCHR = 7;
    const TYPE_ASS = 8;
    const TYPE_AO = 10;
    const TYPE_LAW = 11;
    
    /**
     * Статусы проверки данных модератором
     * @todo числа в коде заменить на эти константы
     */
    const VALIDATE_STATUS_DEFAULT = 0;//не проверенные
    const VALIDATE_STATUS_WAIT = 1;//ожидающие проверки
    const VALIDATE_STATUS_OK = 2;//проверенные
    const VALIDATE_STATUS_DECLINE = -1;//отклоненные данные
    const VALIDATE_STATUS_DELETED = -2;//данные помечены как удаленные пользователем
    const VALIDATE_STATUS_BLOCKED = -3;//данные запрещены к редактированию и использованию

    /**
     * Соединение к БД.
     */
    static public $connect = NULL;
    /**
     * Ид. текущей транзакции событий. Присутсвие говорит о том, то происходят события.
     */
    static protected $XACT_ID = NULL;

    static private $_xactConn;


    static $reqv_fields = array();
    
    /**
     * Сохранили или нет реквизиты в историю фрилансеру
     *
     * @var boolean
     */
    static $save_reqv_frl = false;
    /**
     * Сохранили или нет реквизиты в историю фрилансеру.
     *
     * @var boolean
     */
    static $save_reqv_emp = false;

    /**
     * Префикс для ключа в мемкеше хранящего количество сделок в СБР
     * 
     * @var string
     */
    static $memBuff_prefix = 'CountCurSbr';
    
    /**
     * Префикс для ключа в мемкеше хранящего количество скрытых завершенных сделок в СБР
     * 
     * @var string
     */
    static $memBuff_prefix_compl = 'CountComSbr';
    
    /**
     * Классы для отображения той или иной информации
     */
    static $selectors = array(
        'field' => array(
            'phone'   => array('b-combo__input_width_170', 'b-combo__input-text_fontsize_18 b-combo__input-text_color_a7'),
            'default' => array('', '')
        ),
        'table' => array(
            'phone'   => array('', 'b-layout__left_width_160'),
            'default' => array('b-layout__table_width_full b-layout__table_margbot_20', 'b-layout__left_width_175'),
        )
    );
    
    /**
     * Настройка вывода полей финансов по умолчанию
     * 
     * @var type 
     */
    static $setting_finance_tbl = array(
        'theme'    => 'old',       // Тема отображения 
        'field'    => 'default',   // Отображения обычного инпута @see self::$selectors['field']
        'table'    => 'default',   // Отображение таблицы @see self::$selectors['table']
        'subdescr' => array(),     // Дополнительная информация по полю (расшифровка значния например)
        'notexample' => array()    // Список позиций, где не использовать слово "например"
    ); 
    
    
   /**
    * Типы организаций
    */
    static $types = array(
        self::TYPE_IP => "Индивидуальный предприниматель", 
        self::TYPE_OOO => "Общество с ограниченной ответственностью", 
        self::TYPE_OAO => "Открытое акционерное общество", 
        self::TYPE_ZAO => "Закрытое акционерное общество", 
        self::TYPE_PK => "Потребительский кооператив", 
        self::TYPE_OO => "Общественное объединение", 
        self::TYPE_FOND => "Фонд", 
        self::TYPE_UCHR => "Учреждение", 
        self::TYPE_ASS => "Ассоциация",
        self::TYPE_AO => "Акционерное общество",
        self::TYPE_LAW => "Коллегия"
    );
    
    static $types_short = array(
        self::TYPE_IP => "ИП", 
        self::TYPE_OOO => "ООО", 
        self::TYPE_OAO => "ОАО", 
        self::TYPE_ZAO => "ЗАО", 
        self::TYPE_PK => "Потребительский кооператив", 
        self::TYPE_OO => "Общественное объединение", 
        self::TYPE_FOND => "Фонд", 
        self::TYPE_UCHR => "Учреждение", 
        self::TYPE_ASS => "Ассоциация",
        self::TYPE_AO => "АО",
        self::TYPE_LAW => "Коллегия"
    );

    //Кеш запросов реквизитов на время выполнения скрипта
    static protected $users_reqv_cache = array();
    //Кеш запросов счетчиков на время выполнения скрипта
    static protected $users_info_cache = array();
    //Кеш запросов валидации параметров на время выполнения скрипта
    static protected $users_reqv_is_valid_cache = array();

    
    static function getInstanceLocal($uid = false) {
        $u = new users();
        $u->GetUserByUID($uid);
        
        if(is_emp($u->role)) {
            return hasPermissions('sbr') ? new sbr_adm($u->uid, $u->login) : new sbr_emp($u->uid, $u->login);  
        } else {
            return hasPermissions('sbr') ? new sbr_adm($u->uid, $u->login) : new sbr_frl($u->uid, $u->login); 
        }
    }
    /**
     * Выдает объект sbr в зависимости от роли пользователя и запрошенной страницы.
     * Админ может выступать от роли фрилансера/работодателя чужой сделки, админа или обычного участника сделки.
     * 
     * @return object экземпляр класса sbr_emp|sbr_frl|sbr_adm.
     */
    static function getInstance($access = NULL, $U = NULL, $is_emp = NULL) {
        $req = &$_REQUEST;
        $sess = &$_SESSION;
        $site = $req['site'];
        $sbr = NULL;
        if(!$sess['uid'] && !$U) return NULL;
        if(($is_adm =  hasPermissions('sbr', $sess['uid'])) || hasPermissions('sbr_finance', $sess['uid'])) {
            if(isset($req['access'])) {
                if($req['access'] == self::USER_ACCESS) {
                    unset($sess['F']);
                    unset($sess['E']);
                    unset($sess['access']);
                } else {
                    $sess['access'] = $req['access'];
                    if($req['F']) { $sess['F'] = $req['F']; unset($sess['E']); }
                    else if($req['E']) { $sess['E'] = $req['E']; unset($sess['F']); } 
                    else { unset($sess['F']); unset($sess['E']); } 
                }
            }
            if(!$access) $access = $sess['access'];
        }
        if(hasPermissions('sbr_finance', $sess['uid'])) {
            $is_adm_sbr = true;
        }
        if($is_adm && $site=='admin') {
            // доступ А.
            $sbr = new sbr_adm($sess['uid'], $sess['login']);
        } else if($is_adm_sbr && $site == 'admin') {
            $sbr = new sbr_adm_finance($sess['uid'], $sess['login']);
        } else if($access == self::ADMIN_ACCESS) {
            if($U && $is_emp !== NULL) {
                if($is_emp) $E = $U;
                else $F = $U;
            } else {
                $E = $sess['E'];
                $F = $sess['F'];
            }
            if($E) {
                $cls = 'sbr_emp';
                if($E instanceof users) {
                    $u = $E;
                } else {
                    $u = new employer();
                    $u->GetUser($E);
                }
            } 
            else if($F) {
                $cls = 'sbr_frl';
                if($F instanceof users) {
                    $u = $F;
                } else {
                    $u = new freelancer();
                    $u->GetUser($F);
                }
            }
            if($u && !$err) {
                // отдаем доступ Ф или Р.
                $sbr = new $cls($u->uid, $u->login, $sess['uid']);
            }
        }
        if(!$sbr && $sess['uid']) {
            $cls = $access == self::ADMIN_ACCESS && $is_adm ? 'sbr_adm' :  ( $access == self::ADMIN_ACCESS && $is_adm_sbr ? 'sbr_adm_finance' : ( is_emp($sess['role']) ? 'sbr_emp' : 'sbr_frl' ) );
            $sbr = new $cls($sess['uid'], $sess['login']);
        }
        return $sbr;
    }

    /**
     * Перегружатель $this->data.
     * @return mixed
     */
    function __get($data_field) {            // !!!
        if(isset($this->data[$data_field]))
            return $this->data[$data_field];
        return $this->$data_field;
    }

    /**
     * Получает СБР-счетчики пользователя.
     *
     * @param integer $user_id    ид. пользователя.
     * @return array
     */
    function getUserInfo($user_id) 
    {
        global $DB;
        
        if(isset(self::$users_info_cache[$user_id])) {
            return self::$users_info_cache[$user_id];
        }
        
        $result = NULL;
        
        $sql = $DB->parse("SELECT *, completed_cnt - lost_cnt as success_cnt FROM sbr_meta WHERE user_id = ?i", $user_id);
        if($res = pg_query(self::connect(true), $sql))
            $result = pg_fetch_assoc($res);
        
        
        //Добавляем количество из сделок по резерву средств
        //@todo: нужно обьеденить в один запрос и/или сделать кеширование в мемкеше 
        $result_reserves = $DB->row("
            SELECT *,completed_cnt - lost_cnt as success_cnt
            FROM reserves_meta WHERE user_id = ?i
        ",$user_id);
        
        if($result_reserves)
        {
            $result['completed_cnt'] += $result_reserves['completed_cnt'];
            $result['lost_cnt'] += $result_reserves['lost_cnt'];
            $result['success_cnt'] += $result_reserves['success_cnt'];
            $result['all_cnt'] += $result_reserves['all_cnt'];
        }
        
        self::$users_info_cache[$user_id] = $result;
        
        return $result;
    }
    
    /**
     * Имеет ли пользователь сделки по резерву
     * 
     * @todo: 
     *      - функция все же используется в view_mark_user2 для определения наличия БС но название у нее неудачное!
     *      - для чего отдельный ключ мемкеша кто-то будет чистить? почему не ->cache(1800) ?
     *      
     * 
     * @param type $user_id ИД юзера
     */
    public static function hasReserves($user_id)
    {
        $oMemBuff = new memBuff();
        $hasReserves = $oMemBuff->get("UserHasReserves{$user_id}" );
        
        if ($hasReserves === FALSE) {
            $sbr_info = self::getUserInfo($user_id);
            $hasReserves = (isset($sbr_info["completed_cnt"]) && $sbr_info["completed_cnt"] > 0);
            $oMemBuff->set( "UserHasReserves{$user_id}", (int)$hasReserves, 1800 );
        }
        
        return $hasReserves;
    }
    
    /**
     * Получает количество новых сообщений в СБР
     * 
     * @param  int     $sUserId  UID пользователя для которого делается подсчет
     * @param  boolean $nocache  Если true, то всегда делает запрос к бд
     * @return int количество новых сообщений в СБР
     */
    function getNewMsgCount( $sUserId = 0, $nocache = FALSE ) {
        global $DB;
        
        $oMemBuff = new memBuff();
        if ( $nocache ) {
            $nCount = FALSE;
        } else {
            $nCount   = $oMemBuff->get( "sbrMsgsCnt{$sUserId}" );
        }
        
        if ( $nCount === FALSE ) {
            $sQuery = "
                SELECT
                    SUM(ss.msgs_cnt - su.read_msgs_count)
                FROM
                    sbr_stages_users su
                INNER JOIN
                    sbr_stages ss ON su.stage_id = ss.id
                INNER JOIN
                    sbr s ON (s.emp_id = su.user_id OR s.frl_id = su.user_id) AND s.id = ss.sbr_id
                WHERE
                    su.user_id = ?i AND (ss.msgs_cnt - su.read_msgs_count > 0)
            ";
			$nCount = (int) $DB->val( $sQuery, $sUserId );
			
            $oMemBuff->set( "sbrMsgsCnt{$sUserId}", $nCount, 1800 );
		}
		
		return $nCount;
    }
    /**
     * Получает ИД СБР непрочитанных сообщений
     * 
     * @param  int $sUserId UID пользователя для которого делается подсчет
     * @return array ИД СБР непрочитанных сообщений
     */
    function getIdSBRNewMsg( $sUserId = 0 ) {
        global $DB;
        
        if($sUserId) {
            $sQuery = "
                SELECT
                    s.id
                FROM
                    sbr_stages_users su
                INNER JOIN
                    sbr_stages ss ON su.stage_id = ss.id
                INNER JOIN
                    sbr s ON (s.emp_id = su.user_id OR s.frl_id = su.user_id) AND s.id = ss.sbr_id
                WHERE
                    su.user_id = ?i AND (ss.msgs_cnt - su.read_msgs_count > 0)
            ";
			$return = $DB->rows( $sQuery, $sUserId );
        } else {
            $return = false;
        }
        
		return $return;    
    }
    
    /**
     * Получает признак наличия новых событий в СБР
     * 
     * @param  int     $sUserId  UID пользователя для которого делается подсчет
     * @param  boolean $nocache  Если true, то всегда делает запрос к бд
     * @param  string  $interface События какого интерфейса брать (старой СБР. или новой СБР) @todo Убрать когда закончатся старые СБР
     * @return int 1 - новые события есть, 0 - новых событий нет.
     */
    function getNewEventCount( $sUserId = 0, $nocache = FALSE , $interface = 'new') {
        global $DB;

        $oMemBuff = new memBuff();
        if ( $nocache ) {
            $nCount = FALSE;
        } else {
            $nCount = $oMemBuff->get( $interface == 'old' ? "sbrEventOldCnt{$sUserId}" : "sbrEventCnt{$sUserId}" );
        }
        
        if ( $nCount === FALSE ) {
            $sQuery = 'SELECT last_view, last_action, all_cnt, last_view_old, last_action_old FROM sbr_meta WHERE user_id = ?i';
            $aRow   = $DB->row( $sQuery, $sUserId );
            
            if($interface == 'old') {
                $nCount = ( $aRow['all_cnt'] > 0 && strtotime($aRow['last_view_old']) < strtotime($aRow['last_action_old']) ) ? 1 : 0;
                $oMemBuff->set( "sbrEventOldCnt{$sUserId}", $nCount, 1800 );
            } else {
                $nCount = ( $aRow['all_cnt'] > 0 && strtotime($aRow['last_view']) < strtotime($aRow['last_action']) ) ? 1 : 0;
                $oMemBuff->set( "sbrEventCnt{$sUserId}", $nCount, 1800 );
            }
            //$oMemBuff->set( "sbrEventCnt{$sUserId}", $nCount, 1800 );
        }
        
        return (int)$nCount;
    }
    
    /**
     * Шаблон бюджета, используемый в интерфейсе.
     * @param float   $cost       сумма
     * @param integer $cost_sys   код валюты {@link exrates}
     *
     * @return string   html-блок с бюджетом.
     */
    static function view_cost($cost, $cost_sys = NULL, $nozeros = true, $ds='.', $sp='&nbsp;') {
        global $EXRATE_CODES;
        if($cost_sys == exrates::FM) {
            $cost = _bill($cost);
        }
        $cost = number_format($cost, 2, $ds, ' ');
        //if(strpos($cost, '.') == 5) $cost = str_replace(' ', '', $cost);
        if($nozeros) {
            $cost = str_replace($ds.'00', '', $cost);
        }
        $cost = str_replace(' ', $sp, $cost);
        return $cost . ($cost_sys ? $sp.$EXRATE_CODES[$cost_sys][1] : '');
    }

    /**
     * Такой метод округления НДФЛ.
     * @param float $sum   сумма
     * @return integer   огругленная сумма.
     */
    static function ndfl_round($sum) {
        $isum = floor($sum);
        if($sum - $isum > 0.5)
            return ceil($sum);
        return $isum;
    }
    
    /**
     * Шаблон блока фрилансера, находящегося на странице создания/редактирования СБР.
     * @param object $frl   информация о фрилансере.
     *
     * @return string   html-блок.
     */
    static function view_frl($frl) {
        global $session;
        if(!$frl->uid) return '';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/opinions.php';
        $frl_link = "/users/{$frl->login}";
        $frl_name = $frl->uname.' '.$frl->usurname.' ['.$frl->login.']';
        $sbr_info = self::getUserInfo($frl->uid);
        $ocnt = opinions::GetCounts($frl->uid, array('norisk', 'emp', 'all'));
        ob_start();
        ?>
            <a href="<?=$frl_link?>" target="_blank"><?=view_avatar($frl->login, $frl->photo)?></a>
            <div class="user-info">
                <div class="user-stat"><?=($frl->is_pro=='t' ? view_pro2($frl->is_pro_test=='t') : '')?>
                <?=$session->view_online_status($frl->login)?>
                <a href="<?=$frl_link?>" class="freelancer-name" target="_blank"><?=$frl_name?></a></div>
                «Безопасных Сделок»: <?=(int)$sbr_info['success_cnt']?><br />
                <a href="/users/<?= $frl->login?>/opinions/?from=norisk#op_head" class="lnk-nr-reviews" target="_blank">Рекомендации работодателей</a>:
                <a href="<?=$frl_link?>/opinions/?from=norisk&sort=1#op_head" class="ops-plus" target="_blank">+<?=(int)$ocnt['norisk']['p']?></a> / 
                <a href="<?=$frl_link?>/opinions/?from=norisk&sort=2#op_head" class="ops-neitral" target="_blank"><?=(int)$ocnt['norisk']['n']?></a> / 
                <a href="<?=$frl_link?>/opinions/?from=norisk&sort=3#op_head" class="ops-minus" target="_blank">-<?=(int)$ocnt['norisk']['m']?></a>
                <br/>
                <a href="<?=$frl_link?>/opinions/?from=users#op_head" class="lnk-nr-reviews" target="_blank">Мнения пользователей</a>:
                <a href="<?=$frl_link?>/opinions/?from=users&sort=1#op_head" class="ops-plus" target="_blank">+<?=(int)$ocnt['all']['p']?></a> / 
                <a href="<?=$frl_link?>/opinions/?from=users&sort=2#op_head" class="ops-neitral" target="_blank"><?=(int)$ocnt['all']['n']?></a> / 
                <a href="<?=$frl_link?>/opinions/?from=users&sort=3#op_head" class="ops-minus" target="_blank">-<?=(int)$ocnt['all']['m']?></a>
                <input type="hidden" name="frl_login_added" value="<?=$frl->login?>"/>
            </div>
        <?
        return ob_get_clean();
    }

    /**
     * Возвращает соединение к БД.
     * @param boolean $new   необходимо создать новое подключение?
     *
     * @return resource   соединение к БД.
     */
    static function connect() {
        global $DB;
        $cn = $DB->connect(false, false);
        pg_set_client_encoding($cn, 'WIN1251');
        return $cn;
    }

    /**
     * Открывает транзакцию БД и транзакцию событий (если нужно).
     * @see sbr_meta::_beginEvents()
     *
     * @param boolean $with_events   стартовать сразу транзакцию событий?
     * @return boolean   
     */
    protected function _openXact($with_events = false) {
        global $DB;
        if($with_events)
            return $this->_beginEvents();
        return $DB->start();
    }

    /**
     * Откатывает транзакцию.
     */
    protected function _abortXact() {
        global $DB;
        if($this) $this->error['sql'] = pg_last_error();
        return $DB->rollback();
    }

    /**
     * Фиксирует транзакцию.
     * @return boolean   успешно?
     */
    protected function _commitXact() {
        global $DB;
        if(self::$XACT_ID)
            return $this->_commitEvents();
        return $DB->commit();
    }


    /**
     * Начинает транзакцию событий. Чтобы писалась история, менялись версии, шли уведоления о изменении параметров сделки и т.д. необходимо
     * вызывать эту функцию перед выполнением запросов. (см. постгрес-функцию sbr_begin_events())
     * Второе условие записи событий -- все запросы должны идти в одной транзакции БД.
     *
     * @param boolean $begin_transaction   стартовать ли транзакцию БД перед началом запросов (обычно false, если мы уже внутри транзакции). 
     * @return integer   ид. транзакции событий СБР (sbr_xacts.id)
     */
    protected function _beginEvents($begin_transaction = true) {
        if(!self::$XACT_ID) {
            if($begin_transaction) {
                $this->_openXact(false);
            }
            if(!($res = pg_query(self::connect(false), 'SELECT sbr_begin_events()')) || !pg_num_rows($res)) {
                $this->_abortXact();
                return false;
            }
            self::$XACT_ID = pg_fetch_result($res,0,0);
        }
        return self::$XACT_ID;
    }

    /**
     * Фиксирует транзакцию событий (см. постгрес-функцию sbr_commit_events()). На это срабатывают триггеры, отсылаются уведомления и т.п.
     * @see sbr_meta::_beginEvents()
     *
     * @param boolean $commit_transaction   фиксировать ли транзакцию БД (если все запросы закончились, например).
     * @return integer|boolean   информационный флаг (-1: транзакция удалена, т.к. не содержала событий; >0: ид. транзакции; false: ошибка)
     */
    protected function _commitEvents($commit_transaction = true) {
        $ret = true;
        if(self::$XACT_ID) {
            if(!($res = pg_query(self::connect(false), 'SELECT sbr_commit_events()')) || !pg_num_rows($res)) {
                $this->_abortXact();
                return false;
            }
            self::$XACT_ID = NULL;
            $ret = pg_fetch_result($res,0,0);
        }
        if($commit_transaction && !$this->_commitXact())
            $ret = false;
        return $ret;
    }

    /**
     * Выполнить запрос внутри транзакции.
     * @see sbr_meta::_beginEvents()
     *
     * @param string $sql   SQL-запрос
     * @param boolean $with_events   true, если запрос содержит события, которые нужно записать в БД.
     * @param boolean $commit   зафиксировать транзакцию (в том числе тр. событий) после выполнения?
     * @param integer $res_check   проверить результат запроса (1: если мод-запрос не произвел изменений, то откатываем всю транзакцию; 2: если в селекте нет данных -- откатываем; 0: не проверяем)
     *
     * @return boolean   успешно? При откате всегда false.
     */
    protected function _xactQuery($sql, $with_events = false, $commit = true, $res_check = 0) {
        if($this->_openXact($with_events)) {
            if( ($res = pg_query(self::connect(), $sql)) && ($res_check !=1 || pg_affected_rows($res)) && ($res_check !=2 || pg_num_rows($res)) ) {
                if($commit && !$this->_commitXact())
                    return false;
                return $res;
            }
            $this->_abortXact();
        }
        return false;
    }

    /**
     * Выполнить запрос, содержащий события СБР.
     * @see sbr_meta::_xactQuery()
     *
     * @param string $sql   SQL-запрос
     * @param boolean $commit   зафиксировать транзакцию (в том числе тр. событий) после выполнения?
     * @param integer $res_check   проверить результат запроса (1: если мод-запрос не произвел изменений, то откатываем всю транзакцию; 2: если в селекте нет данных -- откатываем; 0: не проверяем)
     *
     * @return boolean   успешно? При откате всегда false.
     */
    protected function _eventQuery($sql, $commit = true, $res_check = 0) {
        return $this->_xactQuery($sql, TRUE, $commit, $res_check);
    }


    /**
     * Формирует код с сообщениями об ошибках, для присвоения JS-переменной. Сообщение может быть массивом.
     *
     * @param string $errs   массив сообщений ключ=>сообщение.
     * @param string $pfx   префикс к имени ключа.
     * @param string $sfx   постфикс к имени ключа.
     * @param boolean $fb   завернуть строку в фигурные скобки -- {строка}
     * @return string   js-код.
     */
    function jsInputErrors($errs, $pfx='', $sfx='', $fb=true) {
        $js = $fb ? '{' : '';
        if($errs) {
            $i=0;
            foreach($errs as $f=>$m) {
                $js .= ($i++ ? ',' : '') . "'{$pfx}{$f}{$sfx}':";
                $js .= is_array($m) ? "['{$m[0]}','{$m[1]}']" : "'$m'";
            }
        }
        return $js.($fb ? '}' : '');
    }

    /**
     * Возвращает коэффициенты для расчета налогов в виде JS строки.
     * 
     * @param  array $schemes
     * @param  array $frl_reqvs СБР-реквизиты фрилансера
     * @param  array $emp_reqvs СБР-реквизиты работодателя
     * @return string
     */
    function jsSchemeTaxes(&$schemes, $frl_reqvs, $emp_reqvs, $usr = sbr::EMP, $psys_default = NULL, $intf = false) {
        $js = '{';
        $j=0;
        foreach($schemes as &$sch) {
            if(!$sch['taxes'][$usr]) continue;
            $i=0;
            $js .= ($j++ ? ',' : '') . $sch['type'] . ':{';
            $ttper = 0;
            foreach($sch['taxes'][$usr] as $id=>$tax) {
                $tcost = 100000000.0;
                // Получаем коэффициенты для расчета налогов в JS при создании сделки.
                $tsum = sbr_meta::calcAnyTax($tax['tax_id'], $tax['scheme_id'], $tcost, array('P' => $psys_default, 'U'=>$usr, 'Ff'=>$frl_reqvs['form_type'], 'Re'=>$emp_reqvs['rez_type'], 'Rf'=>$frl_reqvs['rez_type']));
                $tper = $tsum/$tcost;
                if(!$tper) {
                    continue;
                }
                $ttper += $tper;
                if($sch['type'] == sbr::SCHEME_PDRD2) {
                    $js .= ($i++ ? ',' : '') . "{$id}:[{$tper}," . (float)$tax['percent'] . ']';
                } else if($sch['type'] == sbr::SCHEME_LC) {
                    $tax_percent += $tax['percent'];
                    $tax_tper += $tper; 
                    $tid = 100;
                }
            }
            if($sch['type'] == sbr::SCHEME_LC && $tid) {
               $js .= ($i++ ? ',' : '') . "{$tid}:[{$tax_tper}," . (float)$tax_percent . ']';
            }
            $ttper+=1;
            $sch['taxes'][$usr]['t'] = array('name'=>'Итого к оплате', 'percent'=>0);
            $js .= ($i++ ? ',' : '') . "t:[{$ttper},0]";
            $js .= '}';
        }
        return $js.'}';
    }


    /**
     * Возвращает информацию по всем отзывам, оставленным определенному пользователю.
     *
     * @param integer $uid    ИД пользователя
     * @param integer $type   false: юзер -- фрилансер, true -- работодатель.
     * @param integer $sort   Сортировка по виду отзыва (1 - положительный, 0 - нейтральные, -1 - отрицательные, false - нет сортировки)
     * @param integer $period           период за который отображать отзывы: 0 - все, 1 - за год, 2 - за пол года, 3 - за месяц
     * @param integer $new_format группировать или нет отзывы по sbr_id (для нового формата вывода)
     * @param bool $deleted - если false, то не возвращаются рекомендации помеченые как удаленные
     * 
     * @return array Данные выборки
     */
    function getUserFeedbacks($uid, $type, $sort=false, $period = 0, $new_format = true, $deleted = true) {
        $u_col = $type ? 'emp_id' : 'frl_id';
        $a_pfx = $type ? 'frl_' : 'emp_';
        $a_tbl = $type ? 'freelancer' : 'employer';
        
        switch ($sort) {
            case 1: $rating = 1;
                break;
            case 2: $rating = 0;
                break;
            case 3: $rating = -1;
                break;
            default: $rating = false;
                break;
        }
        
        switch($period) {
            case 1:
                $periodSQL = "AND sf.posted_time > NOW()-interval '1 year'";
                $periodSQL2 = "AND pa.create_date > NOW()-interval '1 year'";
                $periodSQL3 = "AND fb.posted_time > NOW()-interval '1 year'";
                $periodSQL4 = "AND pfb.posted_time > NOW()-interval '1 year'";
                break;
            case 2:
                $periodSQL = "AND sf.posted_time > NOW()-interval '6 month'";
                $periodSQL2 = "AND pa.create_date > NOW()-interval '6 month'";
                $periodSQL3 = "AND fb.posted_time > NOW()-interval '6 month'";
                $periodSQL4 = "AND pfb.posted_time > NOW()-interval '6 month'";
                break;
            case 3:
                $periodSQL = "AND sf.posted_time > NOW()-interval '1 month'";
                $periodSQL2 = "AND pa.create_date > NOW()-interval '1 month'";
                $periodSQL3 = "AND fb.posted_time > NOW()-interval '1 month'";
                $periodSQL4 = "AND pfb.posted_time > NOW()-interval '1 month'";
                break;
            default:
                $periodSQL = $periodSQL2 = $periodSQL3 = $periodSQL4 = '';
        }

        $deletedCondition = !$deleted ? ' AND sf.deleted IS NOT TRUE' : '';
        $deletedCondition2 = !$deleted ? ' AND fb.deleted = FALSE' : '';
        $deletedCondition3 = !$deleted ? ' AND pfb.deleted = FALSE' : '';
               
        $sql = "
          SELECT 
            0 AS opinion_type, -- Тип отзыва в обьединении
            sf.id as id, 
            (sf.id * 2 + 1)::text as uniq_id, -- Если не используется как число предлагаю добавлять opinion_type
            s.id as sbr_id, 
            s.name as sbr_name, 
            s.frl_id, 
            s.emp_id, 
            s.project_id,
            ss.id as stage_id, 
            ss.name as stage_name, 
            ss.status as stage_status, 
            ss.created as stage_created, 
            ss.closed_time as stage_closed,
            ss.category, 
            ss.sub_category, 
            sf.rating AS sbr_rating, 
            sf.is_new_rating, 
            sf.update_time, 
            sf.posted_time,
            u.uid as fromuser_id, 
            sf.a_rate, 
            sf.p_rate, 
            sf.n_rate, 
            sf.posted_time as post_time, 
            sf.descr, 
            {$uid} as touser_id, 
            u.reg_date as ago, 
            u.login, 
            u.uname, 
            u.usurname, 
            u.role, 
            u.photo, 
            u.is_pro,
            u.is_profi,
            u.is_pro_test, 
            u.is_team, 
            ss.num, 
            0 as is_payed, 
            u.is_banned, 
            s.scheme_id, 
            s.scheme_type,
            sfc.user_id as comm_user_id, 
            sfc.comment as comm_text, 
            sfc.date_create as comm_date_create, 
            sfc.id as comm_id,
            NULL::boolean as hidden,
            0 AS kind,
            0 AS type
          FROM sbr s
          INNER JOIN sbr_stages ss ON ss.sbr_id = s.id
          INNER JOIN sbr_feedbacks sf ON sf.id = ss.{$a_pfx}feedback_id
          INNER JOIN {$a_tbl} u ON u.uid = s.{$a_pfx}id
          LEFT JOIN sbr_feedbacks_comments sfc ON sfc.feedback_id = sf.id
          WHERE 
            s.{$u_col} = {$uid} 
            ".($rating !== false ? " AND sf.rating = $rating" : "")."    
            {$periodSQL} {$deletedCondition}
          
          UNION ALL
          
          SELECT 
            1 AS opinion_type,
            pa.id as id, 
            (pa.id * 2)::text as uniq_id, 
            pa.id as sbr_id, 
            NULL::character as sbr_name, 
            pa.user_to frl_id, 
            pa.user_from as emp_id, 
            NULL::integer as project_id,
            NULL::integer as stage_id, 
            NULL::character as stage_name, 
            NULL::integer as stage_status, 
            pa.create_date as stage_created,
            NULL::timestamp without time zone as stage_closed, 
            NULL::integer as category, 
            NULL::integer as sub_category, 
            1 as sbr_rating, 
            NULL::boolean as is_new_rating,
            pa.accept_date as update_time, 
            pa.create_date as posted_time,
            pa.user_from as fromuser_id, 
            NULL::integer as a_rate, 
            NULL::integer as p_rate, 
            NULL::integer as n_rate, 
            pa.create_date as post_time, 
            pa.msgtext as descr,
            pa.user_to as touser_id, 
            e.reg_date as ago, 
            e.login, 
            e.uname, 
            e.usurname, 
            e.role, e.photo, 
            e.is_pro, 
            e.is_profi,
            e.is_pro_test, 
            e.is_team, 
            0 as num, 
            1 as is_payed, 
            e.is_banned, 
            NULL as scheme_id, 
            NULL as scheme_type,
            0 as comm_user_id, 
            '' as comm_text, 
            '1970-01-01' as comm_date_create, 
            0 as comm_id,
            NULL::boolean as hidden,
            0 AS kind,
            0 AS type
          FROM paid_advices pa
          INNER JOIN users e ON e.uid = pa.user_from
          WHERE 
            pa.user_to = {$uid} AND pa.status = ".paid_advices::STATUS_PAYED ." AND pa.op_id IS NOT NULL
            ".($rating !== false && $rating != 1 ? " AND 1 = 0" : "")."    
            {$periodSQL2}    

          UNION ALL

          SELECT 
            2 AS opinion_type,-- Тип отзыва в обьединении
            fb.id as id, 
            (fb.id || '-2') as uniq_id,-- Если не используется как число предлагаю добавлять opinion_type
            fb.id as sbr_id, 
            o.title as sbr_name, 
            o.frl_id, 
            o.emp_id, 
            o.tu_id as project_id,
            NULL::integer as stage_id, 
            NULL::character as stage_name, 
            NULL::integer as stage_status, 
            o.date as stage_created,
            o.close_date as stage_closed, 
            NULL::integer as category, 
            NULL::integer as sub_category, 
            fb.rating as sbr_rating, 
            NULL::boolean as is_new_rating,
            o.accept_date as update_time, 
            fb.posted_time as posted_time,
            u.uid as fromuser_id, 
            NULL::integer as a_rate, 
            NULL::integer as p_rate, 
            NULL::integer as n_rate, 
            fb.posted_time as post_time, 
            fb.feedback as descr,
            u.uid as touser_id, 
            u.reg_date as ago, 
            u.login, 
            u.uname, 
            u.usurname, 
            u.role, 
            u.photo, 
            u.is_pro, 
            u.is_profi,
            u.is_pro_test, 
            u.is_team, 
            o.order_price as num, 
            0 as is_payed, 
            u.is_banned, 
            NULL as scheme_id, 
            NULL as scheme_type,
            fbc.user_id as comm_user_id, 
            fbc.comment as comm_text, 
            fbc.date_create as comm_date_create, 
            fbc.id as comm_id,
            NULL::boolean as hidden,
            0 AS kind,
            o.type AS type
          FROM tservices_orders_feedbacks AS fb 
          INNER JOIN tservices_orders AS o ON o.{$a_pfx}feedback_id = fb.id
          INNER JOIN {$a_tbl} u ON u.uid = o.{$a_pfx}id 
          LEFT JOIN tservices_orders_feedbacks_comments fbc ON fbc.feedback_id = fb.id
          WHERE
            o.{$u_col} = {$uid} 
            ".($rating !== false ? " AND fb.rating = $rating" : "")." 
            {$periodSQL3} 
            {$deletedCondition2}

        UNION ALL
          
          SELECT 
            3 AS opinion_type,-- Тип отзыва в обьединении
            pfb.id as id, 
            (pfb.id || '-3') as uniq_id,
            p.id as sbr_id, 
            p.name as sbr_name, 
            po.user_id as frl_id,
            p.user_id as emp_id, 
            p.id as project_id,
            NULL::integer as stage_id, 
            NULL::character as stage_name, 
            NULL::integer as stage_status, 
            po.post_date as stage_created,
            p.close_date as stage_closed,
            NULL::integer as category,
            NULL::integer as sub_category,
            pfb.rating as sbr_rating,
            NULL::boolean as is_new_rating,
            NULL::timestamp as update_time,  --TODO
            pfb.posted_time as posted_time,
            u.uid as fromuser_id,
            NULL::integer as a_rate, 
            NULL::integer as p_rate, 
            NULL::integer as n_rate, 
            pfb.posted_time as post_time, 
            pfb.feedback as descr,
            u.uid as touser_id, 
            u.reg_date as ago, 
            u.login, 
            u.uname, 
            u.usurname, 
            u.role, 
            u.photo, 
            u.is_pro, 
            u.is_profi,
            u.is_pro_test, 
            u.is_team, 
            p.cost as num, 
            0 as is_payed, 
            u.is_banned, 
            NULL as scheme_id, 
            NULL as scheme_type,
            NULL as comm_user_id, 
            NULL as comm_text, 
            NULL as comm_date_create, 
            NULL as comm_id,
            pfb.show='f' as hidden,
            p.kind AS kind,
            0 AS type
          FROM projects_feedbacks AS pfb 
          INNER JOIN projects AS p ON p.id = pfb.project_id
          INNER JOIN projects_offers AS po ON p.id = po.project_id AND p.exec_id = po.user_id
          INNER JOIN {$a_tbl} u ON u.uid = pfb.user_id 
          WHERE
            ".($type?'p.user_id':'po.user_id')." = {$uid} 
            ".($rating !== false ? " AND pfb.rating = $rating" : "")." 
            {$periodSQL4} 
            {$deletedCondition3}
            AND (pfb.show = TRUE OR p.user_id=".get_uid(false)." OR p.exec_id=".get_uid(false)." OR ".(is_moder()?'TRUE':'FALSE').")
          ORDER BY posted_time DESC, id ASC, num
        ";

        //$GLOBALS['DB']->query($sql);
        if($res = pg_query(DBConnect(),$sql))
                $data = pg_fetch_all($res);
        
        if(!count($data)) return null; 
        if(!$new_format) return $data;
        return self::groupBySBRId($data);
    }
    
    /**
     * возвращает 3 отзыва сервису от фрилансеров
     */
    function getServiceFeedbacksFromFrl ($limit = 3) {
        global $DB;
        $sql = "
            SELECT u.login, u.uname, u.usurname, u.photo, sf.descr
            FROM sbr s
            INNER JOIN sbr_feedbacks sf
                ON sf.id = s.frl_feedback_id
            INNER JOIN users u
                ON u.uid = s.frl_id
            WHERE sf.in_promo = TRUE
            ORDER BY RANDOM()
            LIMIT 30
        ";
        $result =  $DB->cache(600)->rows($sql);
        
        if($result) {
            if(count($result) > $limit) {
                //выбираем произвольно первые 3 проекта из запроса
                $rnd = array_rand($result, $limit);
                foreach($rnd as $key) {
                    $feedback[] = $result[$key];
                }
                return $feedback;
            } else {
                return $result;
            }
        } else {
            return false;
        }
    }
    
    /**
     * возвращает 3 отзыва сервису от работодателей
     */
    function getServiceFeedbacksFromEmp ($limit = 3) {
        global $DB;
        $sql = "
            SELECT u.login, u.uname, u.usurname, u.photo, sf.descr
            FROM sbr s
            INNER JOIN sbr_feedbacks sf
                ON sf.id = s.emp_feedback_id
            INNER JOIN users u
                ON u.uid = s.emp_id
            WHERE sf.in_promo = TRUE
            ORDER BY RANDOM()
            LIMIT 30
        ";
        $result =  $DB->cache(600)->rows($sql);
        
        if($result) {
            if(count($result) > $limit) {
                //выбираем произвольно первые 3 проекта из запроса
                $rnd = array_rand($result, $limit);
                foreach($rnd as $key) {
                    $feedback[] = $result[$key];
                }
                return $feedback;
            } else {
                return $result;
            }
        } else {
            return false;
        }
    }
    
    /**
     * помечает отзыв (или отзывы) разрешенными для показа в промо-блоке
     * @param integer $feedbackID ID отзыва или массив с ID отзывов
     * @param bool $check true - можно показывать в промо-блоке, false - нельзя
     */
    function feedbackToPromo ($feedbackID, $check) {
        global $DB;
        if (is_array($feedbackID)) {
            $sql = "
                UPDATE sbr_feedbacks
                SET in_promo = ?b
                WHERE id IN (?l)";
            $DB->query($sql, $check, $feedbackID);
        } else {
            $sql = "
                UPDATE sbr_feedbacks
                SET in_promo = ?b
                WHERE id = ?i";
            $DB->query($sql, $check, $feedbackID);
        }
    }
    
    /**
     * возвращает массив array('count' => xxxx, 'frl_sum' => yyyy, 'emp_sum' => zzzz)
     * где xxxx - это количество успешных сделок за год,
     * yyyy - сумма выплаченная фрилансерам по этим сделкам,
     * zzzz - сумма бюджетов этих сделок
     */
    function getPromoStats () {
        global $DB;
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        
        $sql = "
            -- выплаченные исполнителю (аккредитив)
            SELECT 1, count(*), sum(ss.credit_sum) as sum
            FROM sbr s
            LEFT JOIN pskb_lc p ON p.sbr_id = s.id
            LEFT JOIN sbr_stages sst ON sst.sbr_id = s.id
            LEFT JOIN sbr_stages_payouts ss ON ss.stage_id = sst.id AND ss.user_id = s.frl_id
            WHERE ss.bank_completed >= (date('now') - interval '1 year')
            
            UNION
            
            -- выплачено исполнителю (подряд)
            SELECT 2, count(*), sum(ss.credit_sum) as sum
            FROM sbr s
            LEFT JOIN sbr_stages sst ON sst.sbr_id = s.id
            LEFT JOIN sbr_stages_payouts ss ON ss.stage_id = sst.id AND ss.user_id = s.frl_id
            WHERE s.scheme_type = " . sbr::SCHEME_PDRD2 . "
            AND ss.completed >= (date('now') - interval '1 year')

            UNION

            -- возвращенные заказчику (аккредитив)
            SELECT 3, 0, sum(ss.credit_sum) as sum
            FROM sbr s
            LEFT JOIN pskb_lc p ON p.sbr_id = s.id
            LEFT JOIN sbr_stages sst ON sst.sbr_id = s.id
            LEFT JOIN sbr_stages_payouts ss ON ss.stage_id = sst.id AND ss.user_id = s.emp_id
            WHERE ss.completed >= (date('now') - interval '1 year')
            
            UNION
            
            -- возвращенные заказчику (подряд)
            SELECT 4, 0, sum(ss.credit_sum) as sum
            FROM sbr s
            LEFT JOIN sbr_stages sst ON sst.sbr_id = s.id
            LEFT JOIN sbr_stages_payouts ss ON ss.stage_id = sst.id AND ss.user_id = s.emp_id
            WHERE s.scheme_type = " . sbr::SCHEME_PDRD2 . "
            AND ss.completed >= (date('now') - interval '1 year')
            
            ORDER BY 1";
        
        $rows = $DB->cache(self::PROMO_STATS_CACHE_TIME)->rows($sql);
        
        $stat = array(
            'count' => $rows[0]['count'] + $rows[1]['count'],
            'frl_sum' => $rows[0]['sum'] + $rows[1]['sum'],
            'emp_sum' => ($rows[0]['sum'] + $rows[1]['sum'] + $rows[3]['sum'] + $rows[4]['sum']),
        );
        
        return $stat;
    }
    
    /**
     * Праильно группируем отзывы, согласно этапам СБР (их в одну кучу, если СБР один и тот же)
     *
     * @param array $data
     */
    function groupBySBRId($data) {
        if(!is_array($data)) return null;
        foreach($data as $m) {
            $a["{$m['is_payed']}_{$m['sbr_id']}"][] = $m;
        }
        foreach($a as $k=>$m) {
            usort($m, array('sbr_meta', 'sortOpinions'));
            $a[$k] = $m;
        }  
        unset($data);
        foreach($a as $k=>$m) {
            foreach($m as $i=>$j) {
                $data[] = $j;
            }
        }  
        return $data;
    }
    /**
     * Сортируем по этапам
     *
     * @param array $a
     * @param array $b
     * @return unknown
     */
    function sortOpinions($a, $b) {
        if($a['num'] == $b['num']) return 0;
        return ($a['num'] < $b['num']) ? -1 : 1;       
    }
 
    /**
     * Выдает количество отзывов по СБР пользователю.
     *
     * @param integer $uid   ид. юзера.
     * @param boolean $type   true, если он работодатель, иначе фрилансер.
     * @return integer   кол-во отзывов.
     */
    function countUserFeedbacks($uid, $type) {
        $u_col = $type ? 'emp_id' : 'frl_id';
        $a_pfx = $type ? 'frl_' : 'emp_';
        $a_tbl = $type ? 'freelancer' : 'employer';

        $sql = "
          SELECT COUNT(1)
            FROM sbr s
          INNER JOIN
            sbr_stages ss
              ON ss.sbr_id = s.id
          INNER JOIN
            sbr_feedbacks sf
              ON sf.id = ss.{$a_pfx}feedback_id
          INNER JOIN
            {$a_tbl} u 
              ON u.uid = s.{$a_pfx}id
             AND u.is_banned = '0'
          WHERE s.{$u_col} = {$uid}
        ";
        if($res = pg_query(DBConnect(),$sql))
            return pg_fetch_result($res,0,0);
        return 0;
    }

    /**
     * Выдает отзывы СБР пользователя + рейтинг, полученный за каждую сделку.
     * Отзывы группируются в сделки, потом в этапы сделок.
     *
     * @param integer $uid   ид. юзера.
     * @param boolean $type   true, если он работодатель, иначе фрилансер.
     * @param integer $limit   сколько сделок(!) нужно вывести.
     * @return array   этапы с информацией об отзывах и пользователе, который их оставил.
     */
    function getUserRatings($uid, $type, $limit = 0, $offset = 0, &$count_success=0) {
        global $DB;
        
        $u_pfx = $type ? 'emp_' : 'frl_';
        $a_pfx = $type ? 'frl_' : 'emp_';
        $a_tbl = $type ? 'freelancer' : 'employer';
        
        if($limit != 'ALL') {
            $cnt_sql = "SELECT SUM(CASE ss.num WHEN 0 THEN 1 ELSE (CASE s.scheme_type WHEN 4 THEN 0 WHEN 5 THEN 0 ELSE 1 END) END) as cnt FROM sbr s
                INNER JOIN sbr_stages ss ON ss.sbr_id = s.id
                INNER JOIN {$a_tbl} u  ON u.uid = s.{$a_pfx}id
                LEFT JOIN sbr_feedbacks sf ON sf.id = ss.{$a_pfx}feedback_id
                WHERE s.{$u_pfx}id = {$uid} AND ( (s.status = 700 AND s.{$u_pfx}rating > 0 AND s.scheme_type IN (4,5)) OR (sf.id IS NOT NULL AND s.scheme_type IN (1,2,10))) ";

            $count_success = $DB->val($cnt_sql);
        }
        
        $sql = "
          SELECT ss.*, sf.a_rate, sf.p_rate, sf.n_rate, 
                 u.login, u.uname, u.usurname, u.role, u.photo, u.is_pro, u.is_pro_test, u.is_team,
                 s.id as sbr_id, s.completed, s.name as sbr_name, s.project_id, s.scheme_type,
                 s.{$u_pfx}rating as to_rating, ss.{$u_pfx}rating as sto_rating
            FROM sbr s
            INNER JOIN sbr_stages ss ON ss.sbr_id = s.id
            INNER JOIN {$a_tbl} u  ON u.uid = s.{$a_pfx}id
            LEFT JOIN sbr_feedbacks sf ON sf.id = ss.{$a_pfx}feedback_id
           WHERE s.{$u_pfx}id = {$uid} AND ( (s.status = 700 AND s.{$u_pfx}rating > 0 AND s.scheme_type IN (4,5)) OR (sf.id IS NOT NULL AND s.scheme_type IN (1,2,10))) 
           ORDER BY s.sended DESC, s.completed DESC, ss.num ASC
           LIMIT {$limit} OFFSET {$offset}
        ";
        
        $rows = $DB->rows($sql);
        $ret  = array();
        foreach($rows as $row) {
            if(!array_key_exists($row['sbr_id'], $ret)) {
                $ret[$row['sbr_id']] = $row;
            }
            $ret[$row['sbr_id']]['stages'][] = $row;
        }
        
        return $ret;
    }
    
    function getCountSuccessRatingSbr($uid, $type) {
        global $DB;
        
        $u_pfx = $type ? 'emp_' : 'frl_';
        $a_pfx = $type ? 'frl_' : 'emp_';
        $a_tbl = $type ? 'freelancer' : 'employer';
        
        $sql = "
          SELECT COUNT(*)
            FROM sbr s
            INNER JOIN {$a_tbl} u  ON u.uid = s.{$a_pfx}id AND u.is_banned = '0'
           WHERE s.{$u_pfx}id = {$uid} AND s.status = 700 AND s.{$u_pfx}rating > 0
           ";
           
        $ret = $DB->cache(60)->val($sql);
        
        return $ret;
    }


    /**
     * Обрабатывает поля http-запроса для выставления/редактирования отзыва СБР.
     *
     * @param array $request   массив с полями
     * @param array $error   вернет массив ошибок, индексированный именем ошибочного поля.
     * @return array    обработанные данные для отправки в базу.  
     */
    private function _feedbackInitFromRequest($request, &$error = NULL) {
        $feedback = NULL;
        $error = NULL;
        
        foreach($request as $field=>$value) {
            if(is_scalar($value))
                $value = stripslashes($value);
            $rate_ferr = 'a_rate_srv';
            switch($field) {
                case 'id' :
                    $value = (int)$value;
                    break;
                case 'p_rate' :
                case 'n_rate' :
                case 'a_rate' :
                    $rate_ferr = 'a_rate';
                    $value = (int)$value > 10 ? 10 : (int)$value;
                    if($value <= 0) {
                        $value = 0;
                        $error[$rate_ferr] = array('Пожалуйста, поставьте оценки по всем критериям', 'Зафиксируйте оценку щелчком мыши');
                    }
                    break;
                case 'descr' :
                    $value = trim($value);
                    if($value == '')
                        $error[$field] = 'Пожалуйста, оставьте отзыв';
                    break;
                case 'ops_type' :
                    if(intval($value) > 1 || $value < -1) {
                        $error[$field] = 'Неверное значение';
                    }
                    if($value == "") {
                        $error[$field] = 'Выберите значение';    
                    }
                    break;
            }
            $feedback[$field] = $value;
        }
        
        return $feedback;
    }

    /**
     * Пишет отзыв в базу.
     *
     * @param array $request   массив с полями.
     * @param array $feedback   вернет обработанный массив {@link sbr_meta::_feedbackInitFromRequest()}
     * @param array $error   вернет массив ошибок, индексированный именем ошибочного поля.
     * @return array   вернет этот же отзыв, уже пропущенный через БД.
     */
    function addFeedback($request, &$feedback, &$error) {
        $feedback = self::_feedbackInitFromRequest($request, $error);
        if($error)
            return false;
        $sql_data = $feedback;
        $dontupd_rate = !isset($request['p_rate']);
        $sql_data['p_rate'] = (int)$sql_data['p_rate'];
        $sql_data['n_rate'] = (int)$sql_data['n_rate'];
        $sql_data['a_rate'] = (int)$sql_data['a_rate'];
        $sql_data['ops_type'] = (int)$sql_data['ops_type'];
        //$sql_data['descr'] = pg_escape_string(change_q_x($sql_data['descr'], true, false));
        $sql_data['descr'] = __paramValue('string', $sql_data['descr'], null, true);
        $sql_data['descr'] = htmlspecialchars(substr(htmlspecialchars_decode($sql_data['descr']), 0, self::FEEDBACK_MAX_LENGTH));
        $sql_data['descr_srv'] = pg_escape_string(change_q_x($sql_data['descr_srv'], true, false));
        if($sql_data['id']) {
            $where = "WHERE sf.id = {$sql_data['id']}";
            $set = "descr = '{$sql_data['descr']}', rating = {$sql_data['ops_type']}";
            if(!$dontupd_rate)
                $set .= ", p_rate = {$sql_data['p_rate']}, n_rate = {$sql_data['n_rate']}, a_rate = {$sql_data['a_rate']}"; 
            $sql = "UPDATE sbr_feedbacks sf SET {$set}, update_time = NOW() WHERE sf.id = {$sql_data['id']} RETURNING sf.*";
        } else {
            $sql = "
              INSERT INTO sbr_feedbacks (descr, p_rate, n_rate, a_rate, rating)
              VALUES ( '{$sql_data['descr']}', {$sql_data['p_rate']}, {$sql_data['n_rate']}, {$sql_data['a_rate']}, {$sql_data['ops_type']} )
              RETURNING *
            ";
        }
        if($sql && ($res = pg_query(self::connect(false), $sql))) {
            return pg_fetch_assoc($res);
        }

        return false;
    }

    /**
     * Взять отзыв из базы.
     * @param integer $feedback_id   ид. отзыва.
     * @param bool    $extended вернуть расширенную информацию
     * @return array   информация по отзыву.
     */
    function getFeedback($feedback_id, $extended = false) {
        if (!($feedback_id = intval($feedback_id))) return;
        if ($extended) {
            $sql = "
                SELECT sf.*,
                CASE WHEN ss.frl_feedback_id = sf.id THEN s.emp_id ELSE s.frl_id END as touser_id,
                CASE WHEN ss.frl_feedback_id = sf.id THEN s.frl_id ELSE s.emp_id END as fromuser_id
                FROM sbr_feedbacks sf
                LEFT JOIN sbr_stages ss ON ss.frl_feedback_id = sf.id OR ss.emp_feedback_id = sf.id
                LEFT JOIN sbr s ON s.id = ss.sbr_id
                WHERE sf.id = {$feedback_id}";
        } else {
            $sql = "SELECT * FROM sbr_feedbacks WHERE id = {$feedback_id}";
        }
        if($res = pg_query(self::connect(), $sql)) {
            if(pg_num_rows($res) > 1)
                return pg_fetch_all($res);
            return pg_fetch_assoc($res);
        }
        return NULL;
    }
    
    /**
     * Удалить отзыв из базы.
     * 
     * @param integer $feedback_id   ид. отзыва.
     * @return bool   результат 
     */
    function deleteFeedback ($feedback_id) {
        if (!intval($feedback_id)) return;
        $sql = "DELETE FROM sbr_feedbacks WHERE id = {$feedback_id}";
        return !!pg_query(self::connect(false), $sql);
    }
    
    /**
     * помечает рекомендацию как удаленную
     * @param integer $feedbackID ID рекомендации в базе sbr_feedbacks
     */
    function setDeletedFeedback ($feedbackID) {
        if (!intval($feedbackID)) return;
        $sql = "UPDATE sbr_feedbacks SET deleted = TRUE, r_switch = FALSE WHERE id = {$feedbackID}";
        return !!pg_query(self::connect(false), $sql);
    }

    /**
     * Установить время последнего просмотра страницы СБР-проектов пользователем.
     *
     * @param integer $user_id   ид. пользователя.
     * @return boolean   успешно?
     */
    function setLastView($user_id, $interface = 'new') {
        $oMemBuff = new memBuff();
        $oMemBuff->delete( 'sbrEventCnt'.$user_id );
        
        // @todo Необходимо убрать когда закончатся старые СБР
        if($interface == 'old') {
            $sql = "UPDATE sbr_meta SET last_view_old = now() WHERE user_id = {$user_id}";
        } else {
            $sql = "UPDATE sbr_meta SET last_view = now() WHERE user_id = {$user_id}";
        }
        return !!pg_query(self::connect(),$sql);
    }

    
    
    /**
     * Вернуть причину блокировки реквизитов
     * 
     * @global object $DB
     * @param int $src_id
     * @return string
     */
    function getReqvBlockedReason($src_id)
    {
        global $DB;
        
        return $DB->val('
            SELECT reason 
            FROM sbr_reqv_blocked 
            WHERE src_id = ?i 
            ORDER BY id DESC', 
        $src_id);
    }

    
    /**
     * Блокировка реквизитов модератором
     * 
     * @global object $DB
     * @param int $src_id
     * @param int $moderator_uid
     * @param string $reason
     * @param int $reason_id
     * @param string $moderator_login
     * 
     * @return boolean
     */
    function reqvBlocked($src_id, $moderator_uid, $reason, $reason_id = 0, $moderator_login = '')
    {
        global $DB;
        
        $DB->update('moderation',
                array('status' => 2),
                'rec_type = ?i AND rec_id = ?i',
                user_content::MODER_SBR_REQV,
                $src_id);        
        
        //блокируем
        $sBlockId = $DB->val("
            INSERT INTO sbr_reqv_blocked (
                src_id, 
                admin, 
                reason, 
                reason_id, 
                blocked_time) 
            VALUES(?i, ?i, ?, ?i, NOW()) RETURNING id
         ",$src_id, $moderator_uid, $reason, $reason_id);                    

        if (!$sBlockId) {
            return false;
        }
        
        //Обновляем статус на "Отклоненные данные"
        return $DB->update('sbr_reqv',array(
            'validate_status' => -1,
            'moderator_uid' => $moderator_uid,
            'moderator_login' => $moderator_login
        ),'user_id = ?i', $src_id);
    }




    /**
     * Разблокировать реквизиты финансов
     * 
     * @global object $DB
     * @param int $scr_id
     */
    function reqvUnBlocked($src_id, $moderator_uid, $moderator_login = '')
    {
        global $DB;
        
        $DB->update('moderation',
                array('status' => 1),
                'rec_type = ?i AND rec_id = ?i',
                user_content::MODER_SBR_REQV,
                $src_id);
        
        //разблокируем если было блокированно
        $DB->query("
            DELETE FROM sbr_reqv_blocked 
            WHERE src_id = ?i
        ",$src_id);

        //Обновляем статус на "Проверенные данные"
        return $DB->update('sbr_reqv',array(
            'validate_status' => 2,
            'moderator_uid' => $moderator_uid,
            'moderator_login' => $moderator_login
        ),'user_id = ?i', $src_id);        
    }



    /**
     * Взять СБР-реквизиты пользователя (со страницы информации, вкладка "Финансы")
     *
     * @param integer $user_id   ид. пользователя.
     * @return array   массив с реквизитами, индексированный: [1] -- реквизиты физ. лица, [2] -- реквизиты юр. лица, [any] -- др. поля, флаги.
     */
    function getUserReqvs($user_id) {

        $user_id = (int) $user_id;
        
        if(isset(self::$users_reqv_cache[$user_id])) {
            return self::$users_reqv_cache[$user_id];
        }
        
        $sql = "SELECT r.*, to_char(_1_birthday, 'DD.MM.YYYY') as _1_birthday,  to_char(_1_idcard_from, 'DD.MM.YYYY') as _1_idcard_from,
                            to_char(_1_idcard_to, 'DD.MM.YYYY') as _1_idcard_to, to_char(_2_reg_date, 'DD.MM.YYYY') as _2_reg_date,
                            to_char(_1_el_doc_from, 'DD.MM.YYYY') as _1_el_doc_from, to_char(_2_birthday, 'DD.MM.YYYY') as _2_birthday
                FROM sbr_reqv r
                WHERE r.user_id = {$user_id}";
        $ret = NULL;
        $fre = '/^_(\d)_(.*)$/';
        if($res = pg_query(self::connect(),$sql)) {
            if(pg_num_rows($res)) {
                $row = pg_fetch_assoc($res);
            } else {
                $sql = "SELECT * FROM information_schema.columns WHERE information_schema.columns.table_name='sbr_reqv'";
                $res = pg_query(self::connect(),$sql);
                while($col = pg_fetch_assoc($res)) {
                    $row[$col['column_name']] = NULL;
                }
            }
            foreach($row as $n=>$v) {
                if(preg_match($fre, $n, $m)) {
                    $ret[$m[1]][$m[2]] = $v;
                } else {
                    $ret[$n] = $v;
                }
            }
        }
        // устанавливаем по-умолчанию "физика"
        $ret['form_type'] = $ret['form_type'] ? $ret['form_type'] : 1;
        
        //Для пользователей-резидентов, у которых серия паспорта в одном поле с номером
        if ($ret['form_type'] == 1 && $ret['rez_type'] == 1 && !$ret[1]['idcard_ser'] && strlen($ret[1]['idcard']) > 6) {
            $ret[1]['idcard_ser'] = substr($ret[1]['idcard'], 0, 4);
            $ret[1]['idcard'] = substr($ret[1]['idcard'], 4);
        }
        
        self::$users_reqv_cache[$user_id] = $ret;
        
        return $ret;
    }

    /**
     * @deprecated   убить все по rezdoc после того, как уйдут сделки с налогом на прибыль.
     */
    function setRezDoc($user_id, $comment, $status = NULL) {
        $ustatus = $status===NULL ? 'rezdoc_status' : $status;
        $sql = "UPDATE sbr_reqv SET rezdoc_comment = '$comment', rezdoc_status = {$ustatus} WHERE user_id = {$user_id}";
        if($res = pg_query(self::connect(),$sql)) {
            if(!pg_affected_rows($res)) {
                $istatus = $status===NULL ? 0 : $status;
                $sql = "INSERT INTO sbr_reqv (user_id, rezdoc_comment, rezdoc_status) VALUES ({$user_id}, '$comment', {$status})";
                $res = pg_query(self::connect(),$sql);
            }
        }
        return !!$res;
    }

    /** Проверяет находится файл в сохранненной истории (страница информации, вкладка "Финансы")
     *
     * @param   integer $uid    ID пользователя
     * @param   integer $fid    ID файла
     * @return  boolean
     */
    /*function isFileInReqvHistory($uid, $fid) {
        global $DB;

        $account = new account();
        $account->GetInfo($uid, true);
        $attach = $account->getAllAttach($fid);

        $sql = "SELECT 1 FROM sbr_reqv_history WHERE user_id={$uid} AND (attaches='{$attach[$fid]['file_id']}' OR attaches LIKE '{$attach[$fid]['file_id']},%' OR attaches LIKE '%,{$attach[$fid]['file_id']}' OR attaches LIKE '%,{$attach[$fid]['file_id']},%')";
        $ret = ($DB->val($sql)==1?true:false);

        if($ret) {
            $sql = "DELETE FROM account_attach WHERE id={$fid}";
            $DB->query($sql);
        }

        return $ret;
    }*/

    /**
     * Получить историю СБР-реквизитов пользователя (страница информации, вкладка "Финансы")
     *
     * @param   integer $stage_id    ID СБР
     * @param   integer $user_id  ID пользователя
     * @return array             история изменения СБР-реквизитов
     */
    function getUserReqvHistory($stage_id, $user_id) {
        global $DB;

        $sql = "SELECT * 
                FROM sbr_reqv_history
                WHERE stage_id = ?i AND user_id = ?i";
        $history = $DB->rows($sql, $stage_id, $user_id);
        
        $reqvs = array();
        if($history) {
            foreach($history as $k=>$v) {
                if ( !empty($v['attaches']) ) {
                    $v['attaches'] = $DB->rows("SELECT * FROM account_attach WHERE id IN (?l)", $DB->array_to_php($v['attaches']));
                } else {
                    $v['attaches'] = array();
                }
                if($v['history_type']==0) {
                    $reqvs['b'] = $v;
                } else {
                    $reqvs['e'] = $v;
                }
            }
        }
        return $reqvs;
    }
    
    function getUserReqvHistoryData($stage_id, $type = 'emp', $force = false) {
        if( ($type == 'emp' && $this->emp_reqvs === false && $this->emp_id) || 
            ($type == 'frl' && $this->frl_reqvs === false && $this->frl_id) || 
             $force ) {
            $hreqvs = $this->getUserReqvHistory($stage_id, $type == 'emp' ? $this->emp_id : $this->frl_id);
        
            $fre = '/^_(\d)_(.*)$/';
            if($hreqvs['b']) {
                foreach($hreqvs['b'] as $n=>$v) {
                    if(preg_match($fre, $n, $m)) {
                        $ret[$m[1]][$m[2]] = $v;
                    } else {
                        $ret[$n] = $v;
                    }
                }
                
                if($type == 'emp') {
                    $this->emp_reqvs = $ret;
                    if($this->isAdmin()) { // Если берет админ данные, то берем из финансов кроме формы, и резидентства
                        $form_type = $this->emp_reqvs['form_type'];
                        $rez_type  = $this->emp_reqvs['rez_type'];
                        $this->emp_reqvs = self::getUserReqvs($this->emp_reqvs['user_id']);
                        $this->emp_reqvs['form_type'] = $form_type;
                        $this->emp_reqvs['rez_type']  = $rez_type;
                    }
                } else {
                    $this->frl_reqvs = $ret;
                    if($this->isAdmin()) { // Если берет админ данные, то берем из финансов кроме формы, и резидентства
                        $form_type = $this->frl_reqvs['form_type'];
                        $rez_type  = $this->frl_reqvs['rez_type'];
                        $this->frl_reqvs = self::getUserReqvs($this->frl_reqvs['user_id']);
                        $this->frl_reqvs['form_type'] = $form_type;
                        $this->frl_reqvs['rez_type']  = $rez_type;
                    }
                }
            }  
        }
    }

    /**
     * Записать в историю СБР-реквизиты пользователя (страница информации, вкладка "Финансы")
     *
     * @param integer $user_id      ID пользователя.
     * @param integer $stage_id     ID Этапа СБР
     * @param integer $history_type 0 - начала СБР, 1 - завершение СБР
     */
    function setUserReqvHistory($user_id, $stage_id, $history_type) {
        global $DB;

        $sql = "DELETE FROM sbr_reqv_history WHERE user_id=?i AND stage_id=?i AND history_type=?i";
        $DB->query($sql, $user_id, $stage_id, $history_type);

        $sql = "INSERT INTO sbr_reqv_history 
                    SELECT nextval('sbr_reqv_history_id_seq'),
                           ?i,
                           NOW(),
                           ?i,
                           (SELECT array_agg(id) FROM account_attach WHERE account_id = (SELECT id FROM account WHERE uid = sbr_reqv.user_id) AND deleted = FALSE),
                           sbr_reqv.*
                    FROM sbr_reqv
                    WHERE user_id=?i
                ";
        $DB->query($sql, $stage_id, $history_type, $user_id);
    }


    /**
     * Записать СБР-реквизиты пользователя (страница информации, вкладка "Финансы")
     *
     * @param integer $user_id   ид. пользователя.
     * @param integer $form_type   1: это реквизиты физ. лица; 2: юр. лица.
     * @param integer $rez_type   1: резидент РФ; 2: резидент Украины, Беларуси, Казахстна; NULL: не трогать поле; -1: сбросить в NULL (резиденство неопределено)
     * @param boolean $ft_disabled   true, если нельзя изменить тип лица (меняем только данные этого типа).
     * @param boolean $is_agree_view_sbr Разрешаю использовать мои паспортные данные в документах сервиса «Безопасная Сделка»
     * @param array $request   массив с реквизитами. Вернется обработанный (убьет лишние слеши и т.д.)
     * @return mixed   0: успешно; иначе строка/массив с ошибками.
     */
    function setUserReqv($user_id, $rez_type = NULL, $form_type, &$request, $ft_disabled = false, $is_agree_view_sbr = false, $error = array()) {
        global $DB;
        
        if(!$form_type) $form_type = sbr::FT_PHYS;
        if(!$request && !$rez_type) return 0;
        if($rez_type==-1) $rez_type = 'NULL';
        
        if(!$request) $request = array();
        
        setlocale(LC_ALL, 'ru_RU.CP1251');
        
        //Передаю статус
        $validate_status = (isset($request['validate_status']))?$request['validate_status']:false;
        unset($request['validate_status']);
        
        //Если беженец или вид на жительство то всегда физик
        if (in_array($rez_type, array(sbr::RT_REFUGEE, sbr::RT_RESIDENCE))) {
            $form_type = sbr::FT_PHYS;
        }
        
        
        $sql_u  = 'UPDATE sbr_reqv SET form_type = ' . ($ft_disabled ? 'form_type' : $form_type) . ', rez_type = '.($rez_type ? $rez_type : 'rez_type');
        $sql_u .= ', is_agree_view_sbr = '. ($is_agree_view_sbr === false ? "is_agree_view_sbr" : "'" . $is_agree_view_sbr ."'");
        $sql_u .= ', last = NOW()';
        if($validate_status !== false) $sql_u .= ", validate_status = {$validate_status}";
        
        $sql_i = "INSERT INTO sbr_reqv";
        $sql_ic = "user_id, form_type, rez_type, is_agree_view_sbr, validate_status";
        $sql_iv = "{$user_id}, {$form_type}, ".($rez_type ? $rez_type : 'NULL') . ", " . ($is_agree_view_sbr === false ? "false" : "'" . $is_agree_view_sbr ."'" );
        $sql_iv .= ", " . ($validate_status !== false? $validate_status : 'NULL');
        

        $user = new users;
        $user->GetUserByUID($user_id);
        
        sbr_meta::getReqvFields();
        
        if($form_type == sbr::FT_JURI && $rez_type == sbr::RT_RU) {
            $request['bank_nds'] = $request['bank_nds'] ? 1 : 0;
        }
        
        $bik = $request['bank_bik'];
        
        require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/country.php');
        $countryObject = new country();
        
        
        
        foreach ($request as $field=>&$value) {
            // не даем менять верифицированным пользователям некоторые поля
            // Админ изменить может
            
            /*0024645 if (!hasPermissions('users') && $user->is_verify == 't' && in_array($field, array('fio', 'birthday', 'idcard_name', 'idcard', 'idcard_from', 'idcard_to', 'idcard_by', 'mob_phone')) ) {
                continue;
            }*/
            
            $err = NULL;
            
            
            
            if( !($finf = sbr_meta::$reqv_fields[$form_type][$field]) || 
                 ($finf['rez_type'] && 
                  !( ($finf['rez_type'] & ($rez_type ? $rez_type : sbr::RT_RU)) || in_array($rez_type, $finf['rez_type_new']) )) ) {
                
                continue;
            }

            if( $value = trim($value )) {
                if(is_scalar($value)) {
                    $value = stripslashes($value);
                    $value = substr($value, 0, $finf['maxlength']+($rez_type==2 ? 3 : 0));
                }
                if ($rez_type==sbr::RT_RU && $field == 'full_name') {
                    //У резидентов отслеживаем и убираем внешние кавычки, 
                    //аббревиатуры (ООО, ЗАО, ОАО, ИП, ПК), 
                    //внутренние кавычки не отслеживаем.
                    $value = preg_replace('/\b(ООО|ЗАО|ОАО|ИП|ПК)\b/i', '', $value);
                    $value = trim($value);
                    $quotes = array('"'=>'"', "'"=>"'", '«'=>'»');
                    foreach ($quotes as $start=>$end) {
                        if (substr($value, 0, 1) == $start) {
                            if (substr($value, strlen($value)-1) == $end) {
                                $value = trim($value, $start.$end);
                            }
                        }
                    }
                    //$value = trim($value, '"\'«»');
                }
                
                
                //var_dump($field);
                
                
                
                switch($field) {
                    
                    case 'bank_rf_name':
                        $bank_rf_city_key = 'bank_rf_city';
                        
                    case 'bank_name':
                        
                        $bank_rf_city_key = !isset($bank_rf_city_key)?'bank_city':$bank_rf_city_key;
                        $parts = explode(',', $value);
                        
                        if(count($parts) == 2) {
                            $request[$bank_rf_city_key] = trim($parts[1]);
                        } else {
                            $err = 'Поле заполнено некорректно.';
                        }
                        
                        break;
                    
                    
                    case 'address':
                        $save_iso = true;
                        
                    case 'address_reg':
                    case 'address_fct':
                    case 'address_jry':    
                        
                        $parts = explode(',', $value);
                        
                        if (count($parts) > 3) {
                            $index = trim($parts[0]);
                            if(!preg_match('/[0-9]+/i', $index)) {
                                $err = 'Поле заполнено некорректно. Укажите индекс.';
                            } else {
                                $country_name = trim($parts[1]);
                                $country_iso = $countryObject->getCountryISO($country_name);

                                if (!$country_iso) {
                                    $err = 'Поле заполнено некорректно. Не найдена указанная страна.';
                                } elseif ($rez_type == sbr::RT_UABYKZ && $country_iso == country::ISO_RUSSIA) {
                                    $err = 'Так как вы нерезидент РФ, ваш адрес должен быть за пределами России.';
                                } elseif(isset($save_iso)) {
                                    $request['country_iso'] = $country_iso;
                                    $request['country'] = $country_name;
                                    $request['city'] = trim($parts[2]);
                                    $request['index'] = trim($parts[0]);
                                }
                            }
                        } else {
                            $err = 'Поле заполнено некорректно.';
                        }
                        
                        break;
                    
                    case 'fio':
                        $symbols = $rez_type == sbr::RT_RU ? '/^[а-яА-ЯёЁ-]+$/i' : '/^[a-zA-Zа-яА-ЯёЁ-]+$/i';
                        $fio_parts = preg_split('/ /', $value);
                        $fio_parts_count = 0;
                        if($fio_parts) {
                            foreach($fio_parts as $fio_part) {
                                if(trim($fio_part)!='' && preg_match($symbols,$fio_part)) $fio_parts_count++;
                            }
                        }
                        if($fio_parts_count == 3) break;
                        if($fio_parts_count!=3 && $rez_type == sbr::RT_RU) $err = 'Поле заполнено некорректно. Введите ФАМИЛИЮ, ИМЯ и ОТЧЕСТВО';
                        if($fio_parts_count!=2 && $rez_type == sbr::RT_UABYKZ) $err = 'Поле заполнено некорректно. Введите ФАМИЛИЮ, ИМЯ';
                        break;
                        
                    case 'idcard_to' :
                        $time = strtotime($value);

                        if (!$time) {
                            $err = 'Поле заполнено некорректно';
                            break;
                        }
                        $value = date('d.m.Y', $time);
                        // дата окончания действия документа не должна быть меньше даты выдачи
                        if ($time < strtotime($request['idcard_from'])) {
                            $err = 'Дата окончания действия документа не может быть раньше даты выдачи';
                        } elseif ($time < time()) { // и меньше сегодняшней даты
                            $err = 'Срок действия документа истек';
                        }
                        
                        
                        
                        
                        break;
                    case 'el_doc_from' :
                    case 'idcard_from' :
                    case 'reg_date' :
                    case 'birthday' :
                        $time = strtotime($value);
                        if(!$time || $time > time() || $time <= strtotime('01.01.1910')) 
                            $err = 'Поле заполнено некорректно';
                        elseif($field=='birthday' && floor((time()-$time)/(60*60*24*365.25)) < 14)
                            $err = 'Вам должно быть не меньше 14 лет';
                        else
                            $value = date('d.m.Y', $time);
                        break;
                    case 'idcard_ser' :

                        if ($rez_type == sbr::RT_RU) {
                            $regexp = is_crimea_people($request)?'/^[a-zA-Zа-яА-Я0-9-]{1,30}$/':'/^[0-9]{4}$/';
                            if(!preg_match($regexp, $value)) $err = 'Поле заполнено некорректно';
                        }

                        if($rez_type == sbr::RT_UABYKZ && !preg_match('/^[a-zA-Zа-яА-Я0-9-]{1,30}$/', $value)) $err = 'Поле заполнено некорректно';
                        break;
                        
                    case 'idcard' :

                        if($rez_type == sbr::RT_RU && !preg_match('/^[0-9]{6}$/', $value)) $err = 'Поле заполнено некорректно';
                        if($rez_type == sbr::RT_UABYKZ && !preg_match('/^[a-zA-Zа-яА-Я0-9-]{1,30}$/', $value)) $err = 'Поле заполнено некорректно';
                        
                        break;
                    case 'pss'      : if(!preg_match('/^[ 0-9_-]+$/', $value)) $err = 'Поле заполнено некорректно'; break;
                    case 'bank_rf_inn':
                    case 'inn'      : if(!preg_match('/^[0-9]{10,12}$/', $value)) $err = 'Поле заполнено некорректно'; break;
                    case 'kpp'      : if(!preg_match('/^[0-9]{9}$/', $value)) $err = 'Поле заполнено некорректно'; break;
                    case 'okpo'     : if(!preg_match('/^(?:\d{8,10})$/', $value)) $err = 'Поле заполнено некорректно'; break;
                    case 'ogrn'     : if(!(preg_match('/^[0-9]{13}$/', $value) || preg_match('/^[0-9]{15}$/', $value))) $err = 'Поле заполнено некорректно'; break;
                    case 'okved'    : if(!preg_match('/^\d\d(?:\.\d|\.\d\d(?:\.\d\d?)?)?$/', $value)) $err = 'Поле заполнено некорректно'; break;
                    
                    case 'el_doc_series':
                        if (!preg_match('/^[a-zA-Zа-яА-Я0-9-\/\s]{1,6}$/', $value)) {
                            $err = 'Поле заполнено некорректно';
                        }
                        break;
                    case 'el_doc_number':
                        if (!preg_match('/^[a-zA-Zа-яА-Я0-9-\/\s]{1,10}$/', $value)) {
                            $err = 'Поле заполнено некорректно';
                        }
                        break;
                    case 'bank_ks':
                    case 'bank_rf_ks':
                        if($rez_type == sbr::RT_UABYKZ) {
                            // кор счет банка должен начинаться на 30111810 для нерезидентов
                            if ( ! preg_match('/^30111810[0-9]{12}$/', $value) ) {
                                $err = 'Корреспондентский счет должен начинаться с 30111810...';
                            }
                        }
                        break;                       
                    case 'bank_rf_bik':
                        if($rez_type == sbr::RT_UABYKZ && !preg_match('/^044/', $value) ) {
                            $err = 'БИК уполномоченного банка должен начинаться с 044...';
                        }
                    case 'bank_bik' : 
                    case 'bank_kpp' : 
                        if ( !preg_match('/^[0-9]{9}$/', $value) ) {
                            $err = 'Поле заполнено некорректно';
                        }
                        break;
                    case 'bank_rs'  :
                        if($rez_type == sbr::RT_RU) {
                            if ( preg_match('/^[0-9]{20,40}$/', $value) ) {
                                // проверяем расчетный код по БИК
                                // алгоритм https://beta.free-lance.ru/mantis/view.php?id=19983
                                $coef = "71371371371371371371371";
                                if ( preg_match("/\d{6}[0]{2}[012]/", $bik) ) {
                                    $check = "0" . substr($bik, 4, 2) . $value;
                                } else {
                                    $check = substr($bik, 6, 3) . $value;
                                }
                                $csum = 0;
                                for ( $i=0; $i<strlen($check); $i++ ) {
                                    $csum += ((int) $check{$i} * (int) $coef{$i}) % 10;
                                }
                                if ( $csum % 10 != 0 ) {
                                    $err = 'Расчетный счет или БИК указан неверно';
                                }
                                
                                if($form_type == sbr::FT_PHYS &&  !preg_match('/^40817|42301/', $value)) {
                                    $err = 'Поле заполнено некорректно';
                                } elseif($form_type == sbr::FT_JURI && !preg_match('/^40802|40807|407|406|405/', $value)) {
                                    $err = 'Поле заполнено некорректно';
                                }
                                
                            } else {
                                $err = 'Поле заполнено некорректно';
                            }
                        } else {
                            if (strlen($value) > 40) {
                                $err = 'Поле заполнено некорректно';
                            }
                        }
                        break;
                    case 'bank_assignment' : 
                        if ( !preg_match('/^[0-9]{1,30}$/', $value) ) {
                            $err = 'Поле заполнено некорректно';
                        }
                        break;
                    case 'email' :
                        if(!is_email($value))
                            $err = 'Поле заполнено некорректно';
                        break;
                    case 'el_yd' :
                        if(!preg_match('/^[\d]{12,15}$/i', $value))
                            $err = 'Поле заполнено некорректно';
                        break;
                    case 'el_wmr' :

                        if(!preg_match('/^R[\d]{12}$/i', $value))
                            $err = 'Поле заполнено некорректно';
                        else
                            $value = strtoupper($value);
                        break;
                    case 'el_ccard' :
                        if(!preg_match('/^([\d]{16}|[\d]{18})$/i', $value))
                            $err = 'Поле заполнено некорректно';
                        break;
                        
                    case 'mob_phone' :
                        $value = str_replace(array('(', ')', ' ', '-'), '', $value);
                        $_phone = intval(substr(str_replace('+', '', $value), 0, 1));
                        // валидация как в банке
                        $validForBank = preg_match('/^[+]?7[0-9]{10}$/', $value) || preg_match('/^[+]?[^+7][0-9]{9,15}$/', $value);
                        if( $validForBank ) {
                            $roleBit = is_emp($user->role) ? "B'100000'" : "B'000000'";
                            // один телефонный номер одновременно может быть привязан только к двум аккаунтам - один из них фрилансер, другой работодатель
                            $c = $DB->val("
                                SELECT 
                                    COUNT(sr.*)
                                FROM 
                                    sbr_reqv sr
                                INNER JOIN users u ON sr.user_id = u.uid
                                WHERE 
                                    (( regexp_replace(regexp_replace(regexp_replace(?, '[-\\s]', '', 'g'), '^8', '+7'), '^00', '+') =
                                        regexp_replace(regexp_replace(regexp_replace(sr._1_mob_phone, '[-\\s]', '', 'g'), '^8', '+7'), '^00', '+') )
                                    OR
                                    ( regexp_replace(regexp_replace(regexp_replace(?, '[-\\s]', '', 'g'), '^8', '+7'), '^00', '+') =
                                        regexp_replace(regexp_replace(regexp_replace(sr._2_mob_phone, '[-\\s]', '', 'g'), '^8', '+7'), '^00', '+') ))
                                    AND sr.user_id <> ?
                                    AND u.role = $roleBit
                            ", $value, $value, $user_id);
                            if ( $c ) {
                                $err = 'Номер мобильного телефона уже сохранен в другом аккаунте, '
                                    . 'обратитесь в <a href="https://feedback.fl.ru/" target="_blank">cлужбу поддержки</a>';
                            }
                        } else {
                            $err = 'Поле заполнено некорректно';
                        }
                        $value = "+" . str_replace("+", "", trim($value));
                        break;
                        
                    case 'phone' : 
                        if(!preg_match('/^[0-9-+]{8,20}$/', $value)) 
                                $err = 'Поле заполнено некорректно';
                        break;
                }
            }
            if($err)
                $error[$field] = $err;
            
            // mob_phone записываем для обоих лиц
            $qvalue = $value ? "'".pg_escape_string(change_q_x($value,true,false))."'" : 'NULL';
            if ($field === 'mob_phone') {
                $sql_u  .= ",_1_mob_phone = $qvalue, _2_mob_phone = $qvalue";
                $sql_ic .= ",_1_mob_phone,_2_mob_phone";
                $sql_iv .= ",$qvalue,$qvalue";
            } else {
                $qfield = "_{$form_type}_{$field}";
                $sql_u  .= ",{$qfield} = {$qvalue}";
                $sql_ic .= ",{$qfield}";
                $sql_iv .= ",{$qvalue}";
            }
        }
        
        if($error) return $error;

        $sql_i = "{$sql_i} ({$sql_ic}) VALUES ({$sql_iv})";
        $sql_u = "{$sql_u} WHERE user_id = {$user_id}";
        
        if(!($res = pg_query(self::connect(),$sql_u))) {
            return 'Ошибка';
        }
        if(!pg_affected_rows($res)) {
            if(!($res = pg_query(self::connect(),$sql_i)))
                return 'Ошибка';
        }
        return 0;
    }
    
    /**
     * Обязательные поля. Обрабатываем даже если не пришли в запросе.
     * 
     * @param type $form_type физ/юр лицо
     * @param type $rez_type резидент/не резидент
     * @param type $request запрос
     */
    function checkRequired($form_type, $rez_type, $request, $is_emp = false) {
        $error = array();
        $required = array('fio', 'birthday');
        
        //Если телефон не привязан, требуем указать контактный
        if (!isset($request['mob_phone']) || empty($request['mob_phone'])) {
            $required[] = 'phone';
        }
        
        if ($form_type == sbr::FT_PHYS) {
            
            $required[] = 'idcard_ser';
            $required[] = 'idcard';
            $required[] = 'idcard_from';
            $required[] = 'idcard_by';
            $required[] = 'address_reg';
            $required[] = 'address';
            
            //Для заказчика-физика не обязательно
            if(!$is_emp) {
                
                //Если беженец то дата действия паспорта обязательна
                if (in_array($rez_type, array(sbr::RT_REFUGEE, sbr::RT_RESIDENCE))) {
                    $required[] = 'idcard_to';
                }
                
                //Банковские реквизиты у резидентов РФ. при заполнении хотя бы одного поля остальные поля обязательны.
                if ($rez_type == sbr::RT_RU && ($request['bank_rs'] || $request['bank_ks'] 
                        || $request['bank_name'] || $request['bank_bik'] 
                        || $request['bank_inn'] || $request['bank_kpp'])) {
                    $required[] = 'bank_rs';
                    $required[] = 'bank_ks';
                    $required[] = 'bank_name';
                    $required[] = 'bank_bik';
                    $required[] = 'bank_inn';
                    $required[] = 'bank_kpp';
                }

                //Банковские реквизиты у нерезидентов РФ. при заполнении хотя бы одного поля остальные поля обязательны.
                if ($rez_type == sbr::RT_UABYKZ && ($request['bank_rs']
                        || $request['bank_name'] || $request['bank_rf_name']
                        || $request['bank_rf_ks'] || $request['bank_rf_bik'] || $request['bank_rf_inn'])) {
                    $required[] = 'bank_rs';
                    $required[] = 'bank_name';
                    $required[] = 'bank_rf_name';
                    $required[] = 'bank_rf_ks';
                    $required[] = 'bank_rf_bik';
                    $required[] = 'bank_rf_inn';
                }            
            
                if (!$request['el_yd'] && !$request['el_wmr'] && !$request['el_ccard'] && !$request['bank_rs']) {
                    $required[] = 'el_yd';
                    $required[] = 'el_wmr';
                    $required[] = 'el_ccard';
                    $required[] = 'bank_rs';                
                }
            }
        }
        
        
        if ($form_type == sbr::FT_JURI) {
            $required[] = 'full_name';
            $required[] = 'address_jry';
            $required[] = 'address';
            
            $required[] = 'bank_rs';
            $required[] = 'bank_name';
            
            if ($rez_type == sbr::RT_RU) {
                $required[] = 'inn';
                if ($request['type']!=1) $required[] = 'kpp';
            
                $required[] = 'bank_ks';
                $required[] = 'bank_bik';
                $required[] = 'bank_inn';
                $required[] = 'type';
            }
            
            if ($rez_type == sbr::RT_UABYKZ) {
                $required[] = 'bank_rf_name';
                $required[] = 'bank_rf_ks';
                $required[] = 'bank_rf_bik';
                $required[] = 'bank_rf_inn';
            }  
        }
        
        foreach ($required as $field) {
            if (!$request[$field]) $error[$field] = 'Поле обязательно для заполнения';
        }
        return $error;
    }
    
    /**
     * Возвращает реквизиты физ. и юр. лица
     * 
     * @return array
     */
    function getReqvFields() {
        if(!sbr_meta::$reqv_fields) {
            $memBuff = new memBuff();
            $mlife = 1800;
            $rows = $memBuff->getSql($err, 'SELECT * FROM sbr_reqv_fields  ORDER BY pos, name  ', $mlife);
            $ro   = $memBuff->getSql($err, 'SELECT * FROM reqv_ordered   LIMIT 1', $mlife);
            $bp   = $memBuff->getSql($err, 'SELECT * FROM bank_payments  LIMIT 1', $mlife);
            $fre = '/^_(\d)_(.*)$/'; // $1:тип лица (физ. или юр.), $2:имя поля

            if($rows) {
                foreach($rows as $row) {
                    if(!preg_match($fre, $row['idname'], $m)) continue;
                    $ft = $m[1];
                    $nm = $m[2];
                    $row['bill_bound'] = ($ft == sbr::FT_JURI && @array_key_exists($nm, $ro[0]) || $ft == sbr::FT_PHYS && @array_key_exists($nm, $bp[0]));
                    
                    //Переводим битарный тип в массив форм резиденства 
                    //которые поддреживает данное поле
                    $rez_type_new = $row['rez_type_new'];
                    $row['rez_type_new'] = array();
                    if (bindec($rez_type_new) > 0) {
                        for($idx = 0; $idx < strlen($rez_type_new); $idx ++) {
                            if (substr($rez_type_new, $idx, 1) == 1) {
                                $row['rez_type_new'][] = $idx+1; //сопостовление sbr::RT_ ...
                            }
                        }
                    }

                    sbr_meta::$reqv_fields[$ft][$nm] = $row;
                }
            }
        }
        return sbr_meta::$reqv_fields;
    }

    /**
     * Возвращает ФИО из реквизитов
     * 
     * @param  array $reqvs реквизиты
     * @return string
     */
    function getFioFromReqvs($reqvs) {
        if(!$reqvs) return NULL;
        return html_entity_decode($reqvs['form_type'] == sbr::FT_JURI ? ($reqvs[sbr::FT_JURI]['full_name'] ? $reqvs[sbr::FT_JURI]['full_name'] : $reqvs[sbr::FT_JURI]['org_name']) : $reqvs[sbr::FT_PHYS]['fio'], ENT_QUOTES, 'cp1251');
    }
    
    /**
     * Возвращает текстовый блок банковских реквизитов
     *
     * @param  array $reqvs реквизиты
     * @return string
     */
    function getBankReqvsStr($reqvs) {
        $rq = $reqvs[$reqvs['form_type']];
        $r  = "Расчетный счет: {$rq['bank_rs']}"
            . "\r\nв {$rq['bank_name']} г. {$rq['bank_city']}"
            . "\r\nКорреспондентский счет: {$rq['bank_ks']}";
        if($reqvs['rez_type']==sbr::RT_RU) {
            $r .= "\r\nБИК {$rq['bank_bik']}";
            if($rq['bank_inn']) $r .= "\r\nИНН {$rq['bank_inn']}";
        } 

        if($reqvs['rez_type']!=sbr::RT_RU) {
            if($rq['bank_swift'])
                $r .= "\r\nS.W.I.F.T: {$rq['bank_swift']}";
            $r .= "\r\n\r\nУполномоченный банк в РФ:\r\n{$rq['bank_rf_name']} в г. {$rq['bank_rf_city']}\r\n"
               .  "Корреспондентский счет: {$rq['bank_rf_ks']}";
            if($rq['bank_rf_bik'])
                $r .= "\r\nБИК: {$rq['bank_rf_bik']}";
            if($rq['bank_rf_inn'])
                $r .= "\r\nИНН: {$rq['bank_rf_inn']}";
        }
        return $r;
    }
    
    /**
     * Возвращает текстовый блок банковских реквизитов
     *
     * @param  array $reqvs реквизиты
     * @param  string $bossname возвращает Генерального директора, если указан в реквизитах
     * @return string
     */
    function getReqvsStr($reqvs, &$bossname = NULL) {
        $rq = $reqvs[$reqvs['form_type']];
        $norf = ($reqvs['rez_type'] != sbr::RT_RU);
        $r = sbr_meta::getFioFromReqvs($reqvs)."\r\n";
        if($reqvs['form_type']==sbr::FT_JURI) {
            if(!$norf) {
                if($rq['adress_jry']) $r .= "\r\nЮридический адрес: {$rq['address_jry']}";
                if($rq['address']) $r .= "\r\nПочтовый адрес: {$rq['address']}";
                if($rq['inn']) $r .= "\r\nИНН {$rq['inn']}";
                if($rq['kpp']) $r .= " / КПП {$rq['kpp']}";
                $r .= "\r\n".$this->getBankReqvsStr($reqvs);
                if($rq['okpo']) $r .= "\r\nОКПО {$rq['okpo']}";
                if($rq['ogrn']) $r .= "\r\nОГРН {$rq['ogrn']}";
                if($rq['orved']) $r .= "\r\nОКВЭД {$rq['okved']}";
            }
            if($norf) {
                if($rq['country'] && $rq['city']) $r .= "\r\nСтрана и город регистрации организации: {$rq['country']}, г. {$rq['city']}";
                if($rq['address_fct']) $r .= "\r\nАдрес: {$rq['address_fct']}";
                if($rq['address']) $r .= "\r\nПочтовый адрес: {$rq['address']}";
               // if($rq['reg_num'] && $rq['reg_date']) $r .= "\r\nСвидетельство о регистрации № {$rq['reg_num']} от {$rq['reg_date']}";
                if($rq['rnn']) $r .= "\r\nРегистрационный номер в налоговом органе: {$rq['rnn']}";
                $r .= "\r\n".$this->getBankReqvsStr($reqvs);
            }
            if($rq['bossname']) {
                $bossname = "Генеральный директор: ".$rq['bossname'];
            } else if(sbr_meta::getFioFromReqvs($reqvs)) {
                $bossname = sbr_meta::getFioFromReqvs($reqvs);
            }
        } else {
            if(!$norf) {
                //if($rq['inn'] && $rq['inn'] != '0000000000')
                //    $r .= "\r\nИНН {$rq['inn']}";
            }
            // #0023353
            if($rq['idcard_name'] && $rq['idcard'] && $rq['idcard_from'] && $rq['idcard_by'] && $rq['address_reg'] && $reqvs['is_agree_view_sbr'] == 't') { // Если паспортные данные заполнены
                $rq['idcard_from'] = date('d.m.Y', strtotime($rq['idcard_from']));
                $r .= "\r\n{$rq['idcard_name']}: № {$rq['idcard']}, выдан {$rq['idcard_from']} {$rq['idcard_by']}"
                   .  "\r\nАдрес регистрации: {$rq['address_reg']}\r\n\r\n";
            }
        }
        return $r;
    }

    /**
     * Возвращает текстовый блок о способе оплаты
     * 
     * @param  array $reqvs реквизиты
     * @param  int $payout_sys код способа оплаты
     * @return string
     */
    function getPayoutReqvsStr($reqvs, $payout_sys) {
        $rq = $reqvs[$reqvs['form_type']];
        $reqvs_str = '';
        switch (intval($payout_sys)) {
            case exrates::BANK:
                $reqvs_str = $this->getBankReqvsStr($reqvs)."\r\n";
                if($reqvs[$reqvs['form_type']]['bank_assignment'] != '') {
                    $reqvs_str .= "Назначение платежа: {$reqvs[$reqvs['form_type']]['bank_assignment']}";
                }
                break;
            case exrates::YM:
                $reqvs_str = "Яндекс.Деньги {$rq['el_yd']}";
                break;
            case exrates::WMR:
                $reqvs_str = "WebMoney {$rq['el_wmr']}";
                break;
        }
            
        return $reqvs_str;
    }
    
    /**
     * Возвращает текстовый блок о способе оплаты
     * 
     * @param  array $reqvs реквизиты
     * @param  int $payout_sys код способа оплаты
     * @param  string $pfx опционально. текст который будет помещен в начало блока.
     * @return string
     */
    function getPayoutMethodStr($reqvs, $payout_sys, $pfx = '') {
        if($payout_sys && $payout_sys != exrates::FM) {
            return $pfx.($payout_sys==exrates::BANK ? "безналичный расчет\r\nБанковские реквизиты:" : '')."\r\n".sbr_meta::getPayoutReqvsStr($reqvs, $payout_sys);
        }
        return NULL;
    }



    /**
     * SQL-шаблон для выборки документов.
     * @todo перетащить в sbr.
     *
     * @param string $where   условие WHERE
     * @param string $order   условие ORDER BY
     * @param boolean $get_file   взять ли информацию по файлу документа
     * @return array   данные о документах
     */
    function getDocs($where = NULL, $order = NULL, $get_file = true, $get_diff = false) {
        if($order) $order_by = "ORDER BY {$order}";
        if($get_file) {
            $cols_f = ', f.fname as file_name, f.path as file_path, f.size as file_size';
            $join_f = 'INNER JOIN file_sbr f ON f.id = sd.file_id';
        }
        if($get_diff) {
            $cols_df = ', sf.first_doc_id, sf.second_doc_id, sf.type as diff_type';
            $join_df = 'LEFT JOIN sbr_docs_diff sf ON ( sf.first_doc_id = sd.id OR sf.second_doc_id = sd.id )';
        }
        $sql = "
          SELECT sd.*
                 {$cols_f}
                 {$cols_df}
            FROM sbr_docs sd
          {$join_f}
          {$join_df}
          {$where}
          {$order_by}
        ";
        if($res = pg_query(DBConnect(), $sql)) 
            return pg_fetch_all($res);
        return NULL;
    }

    /**
     * Взять один документ по ид.
     * @todo перетащить в sbr.
     *
     * @param integer $doc_id   ид. документа
     * @param boolean $get_file   взять ли информацию по файлу документа
     * @return array   данные о документе
     */
    static function getDoc($doc_id, $get_file = true, $diff = false) {
        $docs = self::getDocs("WHERE sd.id = '{$doc_id}'", NULL, $get_file, $diff);
        return $docs[0];
    }

    /**
     * Возвращает комментарии СБР для отправки уведомлений.
     * @see pmail::SbrNewComment()
     *
     * @param integer $ids   идентификаторы комментариев.
     * @param resource $connect   коннект к БД (см. pgq -- mail_cons.php)
     * @return array   комментарии.
     */
    static function getComments4Sending($ids, $connect = NULL) {
        $ids = implode(',', $ids);
        if(!$ids) return NULL;
        $sql = "
          SELECT ms.id, ms.stage_id, ms.msgtext, ms.user_id, ms.is_admin,
                 ss.sbr_id, s.name as sbr_name, ss.name as stage_name, ss.num as stage_num, s.scheme_type, s.posted,
                 e.uid as e_uid, e.login as e_login, e.uname as e_uname, e.usurname as e_usurname, e.email as e_email,
                 f.uid as f_uid, f.login as f_login, f.uname as f_uname, f.usurname as f_usurname, f.email as f_email
            FROM sbr_stages_msgs ms
          INNER JOIN
            sbr_stages ss
              ON ss.id = ms.stage_id
          INNER JOIN sbr s
              ON s.id = ss.sbr_id
          INNER JOIN
            employer e
              ON e.uid = s.emp_id
          INNER JOIN
            freelancer f
              ON f.uid = s.frl_id
           WHERE ms.id IN ({$ids})
        ";
        if($res = pg_query($connect ? $connect : DBConnect(), $sql))
            return pg_fetch_all($res);
        return NULL;
    }


    /**
     * Возвращает события СБР, связанные с заданной транзакцией для отправки уведомлений.
     * Группируется по ид. транзакции и по pmail-функции.
     * @see pmail::SbrNewEvents(()
     *
     * @param integer $xids   идентификаторы транзакций.
     * @param resource $connect   коннект к БД (см. pgq -- mail_cons.php)
     * @return array   куча инфы по событию, включая изменения, произошедшие в нем.
     */
    static function getEventsInfo4Sending($xids, $connect = NULL) {
        self::$connect = $connect ? $connect : DBConnect();
        $xids = implode(',', $xids);
        if(!$xids) return NULL;
        $sql = "
          SELECT se.id, ec.level, se.sbr_id, ec.abbr, se.version, se.ev_code, se.xact_id, se.foronly_role, sx.xtime as ev_time, sx.xtype, ec.name as ev_name, ec.own_rel, ec.own_role, ec.pmail_fn,
                 s.name as sbr_name, ss.name as stage_name, se.own_id, st.rel, st.col, sv.old_val, sv.new_val, sv.note,
                 ss.frl_feedback_id, ss.emp_feedback_id, ss.id as stage_id,
                 e.uid as e_uid, e.login as e_login, e.uname as e_uname, e.usurname as e_usurname, e.email as e_email,
                 f.uid as f_uid, f.login as f_login, f.uname as f_uname, f.usurname as f_usurname, f.email as f_email,
                 s.reserved_id, s.scheme_type, s.posted
            FROM sbr_xacts sx
          INNER JOIN
            sbr_events se
              ON se.xact_id = sx.id
             AND COALESCE(se.foronly_role, -100) <> 0
          INNER JOIN
            sbr_ev_codes ec
              ON ec.id = se.ev_code
             AND ec.pmail_fn IS NOT NULL
          INNER JOIN sbr s
              ON s.id = se.sbr_id
             AND (s.status NOT IN (" . sbr::STATUS_REFUSED . "," . sbr::STATUS_CANCELED . ") OR ec.id IN (sbr_evc('sbr.REFUSE'), sbr_evc('sbr.CANCEL')))
          INNER JOIN
            employer e
              ON e.uid = s.emp_id
          INNER JOIN
            freelancer f
              ON f.uid = s.frl_id
          LEFT JOIN
            sbr_stages ss
              --ON ss.id = se.own_id
             --AND ec.own_rel = 'sbr_stages'
             -- если событие не связано с этапом, а со всей сделкой, то подключается первый этап
              ON ( ss.id = se.own_id AND ec.own_rel = 'sbr_stages' ) OR (ss.sbr_id = se.own_id AND ec.own_rel = 'sbr' AND ss.num = 0 )
          LEFT JOIN
            sbr_versions sv
          INNER JOIN
            sbr_types st
              ON st.id = sv.src_type_id
              ON sv.event_id = se.id
           WHERE sx.id IN ({$xids})
           ORDER BY se.xact_id, se.sbr_id, ec.level, se.id
        ";
        if($res = pg_query(self::connect(), $sql)) {
            $ret = array();
            while($row = pg_fetch_assoc($res)) {
                if($row['pmail_fn'])
                    $ret[$row['xact_id']][$row['pmail_fn']][] = $row;
            }
        }
        return $ret;
    }

    /**
     * Разбирает события, изменения в читаемый вид (например, для истории проекта СБР)
     * @see sbr::getHistory()
     * @see sbr_meta::getEventsInfo4Sending()
     * 
     * @params array $rows   события в определенном порядке, с необходимыми полями (см. для примера sbr::getHistory())
     * @params array $filter   фильтр по коду события, дате или этапу, в котором произошло событие.
     * @return array    массив событий, см. на примере истории СБР.
     */
    static function parseEvents($rows, $filter = NULL) {
        global $EXRATE_CODES;

        $events = array();
        $ev_opts = array();
        $ev_note = NULL;
        $ev_col = NULL;
        foreach($rows as $row) {
            $xid = $row['xact_id'];
            $xxid = $row['col'].$xid;
            $ev = NULL;
            if($row['rel'] == 'sbr_stages' && $row['col']=='start_time') continue; // !!! обычно не меняется самостоятельно, только с изменением статусов (заморожен, в разработке, в арбитраже), но продумать.
            $ev_date = date('d.m.Y', strtotime($row['ev_time']));
            $ev_code = $row['ev_code'];
            $stage_id = NULL;
            $ev_opts['ev_date'][$ev_date] = 1;
            $ev_opts['ev_code'][$ev_code] = $row['ev_name'];
            if($row['stage_name']) {
                $stage_id = $row['own_id'];
                $ev_opts['stage_id'][$stage_id] = $row['stage_name'];
            }
            if($filter['ev_date'] && $filter['ev_date'] != $ev_date)    continue;
            if($filter['ev_code'] && $filter['ev_code'] != $ev_code)    continue;
            if($filter['stage_id'] && $filter['stage_id'] != $stage_id) continue;

            if($row['note'])
                $ev_note[$xxid] = $row['note'];
            if($row['rel'] == 'sbr' || $row['rel'] == 'sbr_stages') {
                switch($row['col']) {
                    case 'descr' : break;
                    case 'cost_sys' :
                        $xxid = 'cost'.$xid;
                        $ev['old_val'] = $EXRATE_CODES[$row['old_val']][1];
                        $ev['new_val'] = $EXRATE_CODES[$row['new_val']][1];
                        $ev_note[$xxid] = $ev_col[$xxid]['cost']['old_val'] . ' '  . $ev['old_val'] . ' &mdash; ' . $ev_col[$xxid]['cost']['new_val'] . ' ' . $ev['new_val'];
                        break;
                    case 'cost' :
                        $xxid = 'cost'.$xid;
                        $ev['old_val'] = self::view_cost($row['old_val']);
                        $ev['new_val'] = self::view_cost($row['new_val']);
                        $ev_note[$xxid] = $ev['old_val'] . ' ' .  $ev_col[$xxid]['cost_sys']['old_val'] . ' &mdash; ' . $ev['new_val'] . ' ' .  $ev_col[$xxid]['cost_sys']['new_val'];
                        break;
                    case 'work_time' :
                        $ev_note[$xxid] = (int)$row['old_val'] . ' &mdash; ' . (int)$row['new_val'];
                        break;
                    case 'status' :
                        $ev_note[$xxid] = sbr_stages::$ss_classes[$row['old_val']][1] . ' &mdash; ' . sbr_stages::$ss_classes[$row['new_val']][1];
                        break;
                    case 'scheme_type' :
                        $ev_note[$xxid] = sbr::$scheme_types[$row['old_val']][0] . ' &mdash; ' . sbr::$scheme_types[$row['new_val']][0];
                        break;
                    case 'frl_refuse_reason' :
                        $ev_note[$xxid] = $row['new_val'];
                        break;
                }
            }
            if($row['rel'] == 'sbr_docs') {
                $doc = self::getDoc($row['src_id'], false);;
                switch($row['col']) {
                    case 'id' :
                        $ev_note[$xxid] = $doc['name'] . ($ev_note[$xxid] ? '; '.$ev_note[$xxid] : '');
                        break;
                    case 'status' :
                        $ev_note[$xxid] = $doc['name'] . '; ' . sbr::$docs_ss[$row['old_val']][2] . ' &mdash; ' . sbr::$docs_ss[$row['new_val']][2];
                        break;
                    case 'access_role' :
                        $ev_note[$xxid] = $doc['name'] . '; ' . sbr::$docs_access[$row['old_val']][1] . ' &mdash; ' . sbr::$docs_access[$row['new_val']][1];
                        break;
                    case 'name' :
                        $ev_note[$xxid] = reformat($row['old_val'],20,0,1) . ' &mdash; ' . reformat($row['new_val'],20,0,1);
                        break;
                    case 'file_id' :
                        $ev_note[$xxid] = $doc['name'];
                        break;
                }
            }

            $ev_col[$xxid][$row['col']] = $ev;
            $events[$row['id']] = $row;
            $events[$row['id']]['note'] = $ev_note[$xxid];
        }

        return array('events' => $events, 'options' => $ev_opts, 'filter' => $filter);
    }

    /**
     * Возвращяет этапы сделок, которые должны завершиться завтра или уже завершились вчера.
     * И участников сделок для отправки им соотвествующих уведомлений.
     * @see smail::SbrDeadlineAlert()
     *
     * @return array
     */
    static function getDeadlines() {
        $sql = "
          SELECT ss.id, ss.name, ss.sbr_id, s.name as sbr_name,
                 ss.start_time + ss.work_time < now() as is_dead,
                 e.uid as e_uid, e.login as e_login, e.uname as e_uname, e.usurname as e_usurname, e.email as e_email, e.is_banned as e_banned,
                 f.uid as f_uid, f.login as f_login, f.uname as f_uname, f.usurname as f_usurname, f.email as f_email, f.is_banned as f_banned
            FROM sbr_stages ss
          INNER JOIN sbr s
              ON s.id = ss.sbr_id
          INNER JOIN employer e ON e.uid = s.emp_id
          INNER JOIN freelancer f ON f.uid = s.frl_id
           WHERE ss.status = " . sbr_stages::STATUS_PROCESS . "
             AND ss.version = ss.frl_version
             AND ss.start_time IS NOT NULL
             AND (ss.start_time + ss.work_time)::date IN (now()::date - 1, now()::date + 1)
        ";
        if($res = pg_query(DBConnect(), $sql))
            return pg_fetch_all($res);
        return NULL;
    }

    /**
     * Возвращяет пользователей, у которых не заполненны все необходимые реквизиты на стр. "Финансы" для отправки им уведомлений об этом.
     * @see smail::SbrReqvAlerts()
     * @deprecated
     * @return array
     */
    static function getReqvAlerts() {
        $sql = "
          SELECT u.login, u.uname, u.usurname, u.email
            FROM (
              SELECT sx.user_id
                FROM (
                  WITH w_sbr AS (SELECT * FROM sbr WHERE reserved_id IS NOT NULL AND scheme_type = " . sbr::SCHEME_PDRD . ")

                  SELECT emp_id as user_id FROM w_sbr WHERE status <> " . sbr::STATUS_COMPLETED . " UNION
                  SELECT frl_id
                    FROM w_sbr s
                  INNER JOIN
                    sbr_stages ss
                      ON ss.sbr_id = s.id
                  LEFT JOIN
                    sbr_stages_payouts sp
                      ON sp.stage_id = ss.id
                     AND sp.user_id = s.frl_id
                  LEFT JOIN
                    sbr_stages_arbitrage sa
                      ON sa.stage_id = ss.id
                     AND sa.resolved IS NOT NULL
                   WHERE sp.completed IS NULL
                     AND COALESCE(sa.frl_percent, 1) > 0
                ) as sx
              LEFT JOIN
                sbr_reqv sr
                  ON sr.user_id = sx.user_id
               WHERE NULLIF(sr.is_filled[sr.form_type], false) IS NULL
           ) as r
         INNER JOIN
           users u
             ON u.uid = r.user_id
            AND u.is_banned = '0'
        ";

        
        if($res = pg_query(DBConnect(), $sql))
            return pg_fetch_all($res);
        return NULL;
    }

    /**
     * Парсит строку комментария к операции резервирования/списания/перевода денег СБР -- вешает гиперссылку на номер сделки.
     * 
     * @param string $comment   комментарий.
     * @param string $login   логин владельца счета. Если указан, то просматривает админ и для него формируется спец. ссылка на сделку.
     * @param string $is_emp   владелец счета работодатель? Используется в паре с $login.
     * @param int    $scheme   идентификатор схемы СБР, определяет, к какому типу она относится
     * @return string   готовый коммент со ссылкой.
     */
    function parseOpComment($comment, $login = NULL, $is_emp = NULL, $scheme_type = sbr::SCHEME_AGNT) {
        if($login && $is_emp!==NULL) {
        	$access = "&access=A&".($is_emp ? 'E' : 'F')."={$login}";
        }
        $folder = 'sbr';
        if (!self::isNewVersionSbr($scheme_type)) {
            $folder = 'norisk2';
        }
        return preg_replace('~СБР-(\d+)-[ТАПБ]/О~', '<a href="/'.$folder.'/?id=$1'.$access.'" target="_blank" class="blue">$0</a>', $comment);
    }


    static private $_taxSumsCache = array();
    static protected $_taxDepends = array('Ff' => '?i', 'Re' => '?i', 'Rf' => '?i', 'P'  => '?i', 'A'  => '?f::decimal', 'nNP' => '?b'); // порядок важен.

    /**
     * Вычисляет налог
     * 
     * @param  int $tax_id ID налога (таблица sbr_taxes)
     * @param  int $scheme_id ID схемы (таблица sbr_schemes)
     * @param  float $cost цена
     * @param  array $dvals реквизиты
     * @return float
     */
    function calcAnyTax($tax_id, $scheme_id, $cost, $dvals = array()) {
        global $DB;
        $args = func_get_args();
        array_pop($args);
        $hash = md5(implode('=', $args).'='.implode('=', $dvals));
        if(!isset(sbr_meta::$_taxSumsCache[$hash])) {
            $sql = 'SELECT sbr_calctax(?i, ?i, ?f::decimal, ' . implode(', ', sbr_meta::$_taxDepends) . ')';
            $sql_args = array($sql, $tax_id, $scheme_id, $cost);
            foreach(sbr_meta::$_taxDepends as $chr=>$dt)
                $sql_args[] = $dvals[$chr];
            $sql = call_user_func_array(array($DB, 'parse'), $sql_args);
            sbr_meta::$_taxSumsCache[$hash] = $DB->val($sql);
        }
        return sbr_meta::$_taxSumsCache[$hash];
    }
    
    /**
     * Выводит HTML код формы заполнения реквизитов
     * 
     * @param array $reqvs существующие реквизиты
     * @param int $form_type 1 - физ. лицо, 2 - юр. лицо.
     * @param string $grp к какой группе полей относится данное поле (BANK:банковские реквизиты; EL:электронные кошельки)
     * @param string $tbl_caption заголовок формы
     * @param string $tbl_header краткое описание формы
     * @param string $tbl_subheader описание подраздела блока, имеет вид array('pos'=>1, 'title'=>'Описание'), 
     *                              где pos - Позиция перед которой будет описание, title - само описание
     * @param array $setting    Всевозможнные настройки отображения полей по умолчанию sbr_meta::$setting_finance_tbl
     *                          В теме new - действуют:
     *                          theme => string  -- Тип вывода таблицы (принимает пока два значение new, old)
     *                          group => array(a1, a2) -- группировка по позициям где a1 - начальная позиция, a2 - конечная позиция @see table sbr_reqv_fields
     *                          abbr_block => string   -- Название всего блока который выводится через функцию
     *                          caption_expand => boolean -- Если true - то название блока становися ссылкой и сам блок можно свернуть нажав на нее (работает в theme => new)
     *                          caption_descr  => string  -- Расшифровка заголовка
     *                          subdescr => array(pos => string) -- Дополнительное описание поля, где pos -- это позиция поля по значению из таблицы (table sbr_reqv_fields.pos), string - само описание
     *                          name_descr => array(pos => string) -- Под название поля, где pos -- это позиция поля по значению из таблицы (table sbr_reqv_fields.pos), string - само описание
     *                          @todo - group -- очень связано будет с позициями в таблице, при изменении этих позиций нужно редактировать код где есть group -- надо как то от этого избавиться
     */
    function view_finance_tbl($reqvs, $form_type, $grp, $tbl_caption, $tbl_header, $tbl_subheader=array(), $setting = false) {
        sbr_meta::getReqvFields();
        if(!$setting) {
            $setting = sbr_meta::$setting_finance_tbl;
        } elseif(is_array($setting)) {
            $setting = array_merge(sbr_meta::$setting_finance_tbl, $setting);
        }
        
        $tbl = array(
            'rez_type' => -1,
            'rez_type_new' => array()
        );
        
        $rtv = 0;

        foreach(sbr_meta::$reqv_fields[$form_type] as $key=>$field) {
            if($grp!==-1 && $field['grp'] != $grp) {
                continue;
            }
            if($setting['group']) {
                if($grp!==-1 && ( $field['pos'] < $setting['group'][0] || $field['pos'] > $setting['group'][1] ) ) {
                    continue;
                }
            }

            $tbl[$key] = $field;
            if((int)$field['rez_type'] != $tbl['rez_type']) {
                $tbl['rez_type'] = (int)$field['rez_type'];
                $rtv++;
                
                if (!empty($field['rez_type_new'])) {
                    $tbl['rez_type_new'] = $field['rez_type_new'] + $tbl['rez_type_new'];
                }
            }
        }
        
        if ($rtv > 1) {
            $tbl['rez_type'] = 0;
            $tbl['rez_type_new'] = array();
        }
        
        $rez_type = $reqvs['rez_type'] ? $reqvs['rez_type'] : sbr::RT_RU;

        switch($setting['theme']) {
            case '':
                include($_SERVER['DOCUMENT_ROOT'].'/sbr/tpl.form_element.php');
                break;
            default:
            case 'old':
                include($_SERVER['DOCUMENT_ROOT'].'/norisk2/tpl.finance_tbl.php');
                break;
            case 'new':
                include($_SERVER['DOCUMENT_ROOT'].'/sbr/tpl.finance_tbl.php');
                break;            
        }
        
    }
    
    /**
     * Расчет рейтинга за СБР
     * 
     * @global type $DB     Подключение к БД
     * @param integer $sum     Сумма бюджета всей СБР (в рублях)
     * @param date   $date
     * @return integer      Рейтинг
     */
    public function getSBRRating($sum, $date = 'NOW()') {
        global $DB;
        $sql = "SELECT * FROM sbr_rating_get_new(?, ?)";
        return $DB->val($sql, $sum, $date);
    }
    
    /**
     * Парсит значение поля _1_idcard на серию и номер (если это паспорт).
     * 
     * @param string $idcard   исходное значение
     * @param integer $rez_type   тип резиденства (1:Россия; 2:СНГ; остальное -- неизвестно).
     * @param string $country   значение поля _1_country
     * @return array  [серия, номер]
     */
    function parse_idcard($idcard, $rez_type, $country) {
        $idcard = trim($idcard);
        $ps = NULL;
        $pn = $idcard;
        if($rez_type == 1 || !$rez_type && strtolower(trim($country)) == 'россия') {
            $idcard = str_replace(' ', '', $idcard);
            if(($len = strlen($idcard)) >= 10) {
                $ps = substr($idcard, 0, 4);
                $pn = substr($idcard, 4);
            }
        }
        else {
            if(preg_match('/^(.*\D)?(\d+)$/', $idcard, $m)) {
                $ps = $m[1];
                $pn = $m[2];
            }
        }
        return array($ps, $pn);
    }
    
    function checkWMDoc($reqv) {
        return ($reqv['form_type'] == sbr::FT_PHYS && ($reqv[1]['el_doc_series'] == '' || $reqv[1]['el_doc_number'] == '' || $reqv[1]['el_doc_from'] == '')); 
    } 
    
    /**
     * Иконка рекомендации
     * 
     * @param integer $rating
     * @return string 
     */
    function getAdviceICO($rating) {
        switch($rating) {
            case 1:
                return 'b-button_poll_plus';
                break;
            case 0:
                return 'b-button_poll_multi';
                break;
            case -1:
                return 'b-button_poll_minus';
                break;
        }
    }
    
    function view_type_payment($payment, $add='') {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/exrates.php';
        
            switch($payment) {
                case exrates::WMR:
                    return $add . 'кошелек Webmoney';
                    break;
                case exrates::YM:
                    return $add . 'кошелек Яндекс.Деньги';
                case exrates::BANK:
                    return $add . 'банковский счет';
                case exrates::FM:
                    return $add . 'личный счет на сайте';
                case exrates::WEBM:
                    return $add . 'Веб-кошелек';
                case exrates::CARD:
                    return $add . 'банковскую карту';
                default:
                    return $add . 'банковский счет';
            }
        }
    
    /**
     * Функция для вывода и обработки редактирования финансов в попап окне
     *  
     */
    public static function view_finance_popup($redirect_url = "") {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
        $action = __paramInit('string', NULL, 'action');
        $sbr_id = __paramInit('int', 'id');
        $account = new account();
        $uid = $_SESSION['uid'];
        $ok = $account->GetInfo($uid, true);
        
        $reqvs = sbr_meta::getUserReqvs($uid);
        $sbr = sbr_meta::getInstance();
        
        $form_type = $reqvs['form_type'];
        $rez_type = __paramInit('int', NULL, 'rez_type');
        if($rt_disabled = $sbr->checkChangeRT()) {
            if(!($rez_type = $reqvs['rez_type']))
                $rez_type = sbr::RT_RU;
            $reqvs['rez_type'] = $rez_type;
        }
        if(!isset($rez_type))
            $rez_type = $reqvs['rez_type'];
        $reqvs['rez_type'] = $rez_type; // !!!
        
        
        if ($action == 'updfin') {
            $popup_open = true;
            $error = array();
            $form_type = __paramInit('int', NULL, 'form_type');
            if ($form_type || $rez_type || isset($_POST['ft' . $form_type])) {
                if (!$ft_disabled)
                    $reqvs['form_type'] = $form_type;
                $reqvs[$form_type] = $_POST['ft' . $form_type];
                
                //@todo: запрещаем изменять финансы в старой СБР #29196
                $error['sbr'] = 'Прекращена поддержка СБР.';
                //if ($err = sbr_meta::setUserReqv($uid, $rez_type, $form_type, $reqvs[$form_type], $ft_disabled))
                //    $error['sbr'] = $err;
            }
            
            // обработка загруженных и удаленных файлов
            $attachedFiles = new attachedfiles($_POST['attachedfiles_session']);
            $attachedFiles_files = $attachedFiles->getFiles(array(1,4));
            $err = $account->addAttach2($attachedFiles_files); // сохраняем файлы
            if ($err) {
                $error['all']['err_attach'] = $err;
            }
            
            if (!$error) {
                if ($stage) {
                    $stage->setPayoutSys((int) $_POST['credit_sys'], true);
                }
                //$_SESSION['users.setup.fin_success'] = 1;
                if (!hasPermissions('users')) {
                    $smail = new smail();
                    $smail->FinanceChanged($login);
                }
                header_location_exit($redirect_url, 1);
            }
            $finance_error = $error;
        }
        
        $attach = $account->getAllAttach();
        $prepared = sbr_meta::prepareFinanceFiles ($attach);
        $attachDoc = $prepared['attachDoc'];
        $attachOther = $prepared['attachOther'];
        $attachedFilesDoc = $prepared['attachedFilesDoc'];
        $attachedFilesOther = $prepared['attachedFilesOther'];

        include $_SERVER['DOCUMENT_ROOT'].'/sbr/tpl.finance.php';
    }
    
    /**
     * подготавливает файлы для страницы финансы
     * @param array $attach список файлов полученный из account::getAllAttach()
     * 
     * возвращает массив со слдующими ключами
     * @key attachedfiles $attachedFilesDoc сюда запишется объект класса attachedfilec для сканов документа
     * @key array $attachDoc сюда запишется список файлов - сканов документа
     * @key attachedfiles $attachedFilesOther сюда запишется объект класса attachedfilec для сканов страхового свидетельства
     * @key array $attachOther сюда запишется список файлов - сканов свидетельства
     */
    function prepareFinanceFiles ($attach, $login = null) {
        if (!$attach) {
            $attach = array();
        }
        // разделяем файлы на группы (сканы документа и сканы свидетельства) и подготавливаем для вывода через attachedfiles2.js
        $attachDoc = array();
        $attachOther = array();
        $cfile = new CFile();
        $attachedFilesDoc = new attachedfiles(); // файлы документа
        $attachedFilesOther = new attachedfiles(); // страховое свидетельство
        foreach ($attach as $key => $file) {
            $file['type'] = $cfile->getext($file['name']);
            $file['tsize'] = iconv("CP1251", "UTF-8", ConvertBtoMB($file['size']));
            $file['orig_name'] = iconv("CP1251", "UTF-8", $file['orig_name']);
            $file['id'] = md5($file['file_id']);
            if (preg_match('/finance_other\/$/', $file['path'])) {
                $attachOther[] = $file;
                $attachedFilesOther->setFiles(array($file['file_id'])); // добавляем файл в сессию
            } else {
                $attachDoc[] = $file;
                $attachedFilesDoc->setFiles(array($file['file_id'])); // добавляем файл в сессию
            }
        }
        
        return array(
            'attachDoc' => $attachDoc,
            'attachOther' => $attachOther,
            'attachedFilesDoc' => $attachedFilesDoc,
            'attachedFilesOther' => $attachedFilesOther,
        );
    }
    
    /**
     * Берем селектор по имени и типу
     * 
     * @param string  $name Нащвание селекторов
     * @param string  $type Тип селектора
     * @param integer $pos  Позиция в какой элемент (изза верстки их 2)
     * @return string 
     */
    public static function getSelector($name='field', $type='default', $pos=0) {
        if(!isset(sbr_meta::$selectors[$name][$type])) return sbr_meta::$selectors[$name]['default'][$pos];
        return sbr_meta::$selectors[$name][$type][$pos];
    }
    
    /**
     * Показываем данные для загрузки файла, а также список файлов если уже есть загруженные
     * @param $fileCategory категория файлов (документ удостоверяющий личность, ....)
     * @param $attachedFiles объект класса attachedfiles
     * @param $attached массив с данными о ранее добавленных файлах
     * @param $param различные параметры для настройки шаблона
     */
    public static function view_finance_files($fileCategory, $attachedFiles, $attached, $params = array()) {
        include $_SERVER['DOCUMENT_ROOT'].'/sbr/tpl.finance_file.php';
    }
    
    public static function getNameForMail($sbr, $type='stage') {
    	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $_sbr = new sbr($sbr['e_uid']);
        $sbr_num = $_sbr->getContractNum($sbr['sbr_id'], $sbr['scheme_type'], $sbr['posted']);
        return "{$sbr_num} - {$sbr[$type . '_name']}";
    }
    
    /**
     * Функция для проверки просроченных сделок (сделки в которых кончилось время на выполнения работы)
     * 
     * @global type $DB
     * @return boolean 
     */
    public function checkStageOvertime() {
        global $DB;
        
        /**
         * se,sve необходимы для того чтобы посмотреть согласился ли пользователь с изменениями или нет (изменения конкретные -- постановка на паузу)
         * se_time,sve_time необходимо для того что если пользователь не согласился с изменениями,
         * то нам необходимо старое значение start_time для расчета времени потраченного на этап тк при паузу start_time обнуляется
         */
        $sql = "SELECT 
                    ss.*
                FROM sbr_stages ss 
                    INNER JOIN sbr s ON s.id = ss.sbr_id
                    LEFT JOIN sbr_events se ON se.sbr_id = s.id AND se.own_id = ss.id AND se.ev_code = 14 AND se.version = ss.version AND se.version <= ss.frl_version
                    LEFT JOIN sbr_versions sve ON sve.event_id = se.id AND sve.src_type_id = 6 AND sve.new_val = '2'
                    LEFT JOIN sbr_events se_time ON se_time.sbr_id = s.id AND se_time.own_id = ss.id AND se_time.ev_code = 12 AND se_time.version = ss.version AND se_time.version >= ss.frl_version
                    LEFT JOIN sbr_versions sve_time ON sve_time.event_id = se_time.id AND sve_time.src_type_id = 8
                WHERE 
                    ( ( ss.status = ? AND NOW() > (ss.start_time - ss.worked_time) + ss.work_time ) 
                      OR  
                      ( ss.status = ? AND NOW() > (sve_time.old_val::timestamp - ss.worked_time) + ss.work_time AND sve.event_id IS NULL) 
                    )
                    AND ss.is_overtime IS NULL
                    AND s.scheme_type = ?";
        $stages  = $DB->rows($sql, sbr_stages::STATUS_PROCESS, sbr_stages::STATUS_FROZEN, sbr::SCHEME_LC);
        if(!$stages) return false;
        
        $updated = array();
        foreach($stages as $k => $stage) {
            if(!$XACT_ID = $this->_openXact(true)) 
                return false;
            
            $result = sbr_notification::sbr_add_event($XACT_ID, $stage['sbr_id'], $stage['id'], 'sbr_stages.OVERTIME', $stage['frl_version'], null, null);

            if(!$result) {
                if(count($updated) > 0) {
                    $this->updateOvertimeStages($updated); // Обновляем сделки в котоыре ушли уведомления
                }
                $this->_abortXact();
                return false;
            } else {
                $updated[] = $stage['id'];
            }

            $this->_commitXact();
        }
        $this->updateOvertimeStages($updated);
    }
    
    /**
     * Обновляем сделки по которым прошли уведомления
     * 
     * @global type $DB
     * @param array $stages  Ид сделок
     * @return boolean 
     */
    public function updateOvertimeStages($stages) {
        global $DB;
        if(!$stages) return false;
        $sql = "UPDATE sbr_stages SET is_overtime = true WHERE id IN (?l)";
        return $DB->query($sql, $stages);
    }
    
    /**
     * Проверяем версию СБР (была создана в новом интерфейсе или старом интерфейсе)
     * @return type 
     */
    public function isNewVersionSbr($scheme_type = null) {
        return  ($scheme_type == null || $scheme_type == sbr::SCHEME_LC || $scheme_type == sbr::SCHEME_PDRD2);
    }
    
    /**
     * Реквизиты необходмые для сделки по аккредитиву (для подстановки в генерируемые документы)
     * 
     * @param array $lc      @see pskb::getLC();
     * @param string $role    Тип пользователя (emp - Заказчик, frl - Исполнитель)
     * @return boolean|string 
     */
    public function getUserReqvAgnt($lc, $role = 'emp') {
        if(!$lc) return false;
        if($role == 'emp') {
            $type = 'Cust';
        } else {
            $type = 'Perf';
        }
        
        $reqv = $lc['name'.$type]."\r\n\r\n";
        if($lc['inn'.$type]) $reqv .= "ИНН: {$lc['inn'.$type]}\r\n";
        if($lc['kpp'.$type]) $reqv .= "КПП: {$lc['kpp'.$type]}\r\n";
        
        return $reqv;
    }
    
    /**
     * Поиск по телефонам функция для админки
     * 
     * @global type $DB
     * @param type $count
     * @param type $filter
     * @param type $page
     * @param type $unlimited
     * @return type 
     */
    public function searchUsersPhone(&$count, $filter, $page = 1, $unlimited = false) {
        global $DB;
        
        $user_per_page = 50;
        $offset = $user_per_page  * ($page - 1); 
        
        $limit  = $unlimited ? '' : ' LIMIT ' . $user_per_page . ' OFFSET ' . $offset;
        
        if($filter['search_phone_exact']) {
            $where = " ( LOWER(_1_mob_phone) = LOWER('{$filter['search_phone']}') OR LOWER(_2_mob_phone) = LOWER('{$filter['search_phone']}') ) ";
        } else {
            $where = " ( _1_mob_phone ILIKE '%{$filter['search_phone']}%' OR _2_mob_phone = '%{$filter['search_phone']}%' ) ";
        }
        
        
        $cSql = "SELECT COUNT(*)
                FROM users u
                LEFT JOIN sbr_reqv sr ON sr.user_id = u.uid
                WHERE {$where}";
        
        $count = $DB->val( $cSql );  
                
        $sql = "SELECT uid, role, login, uname, usurname, _1_mob_phone, _2_mob_phone
                FROM users u
                LEFT JOIN sbr_reqv sr ON sr.user_id = u.uid
                WHERE {$where}
                {$limit}";
                
         return $DB->rows( $sql );   
    }

    /**
     * Возвращает массив пар sbrId => scheme_type
     * @param  array $sbrIds массив идентификаторов сбр
     * @return mixed array|bool; 
     * */
    static public function getShemesSbr($sbrIds) {
        if (count($sbrIds)) {
            $in = 'IN ('.join(', ', $sbrIds).')';
            global $DB;
            $rows = $DB->rows("SELECT id, scheme_type FROM sbr WHERE id $in");
            $result = array();
            foreach ($rows as $i) {
                $result[$i['id']] = $i['scheme_type'];
            }
            return $result;
        }
        return false;
    }
    
    // @todo перенести вывод в sbr::$scheme_types
    public static function getNameScheme($scheme) {
        switch($scheme) {
            case sbr::SCHEME_LC:
                return "Аккредитив";
                break;
            case sbr::SCHEME_PDRD2:
            case sbr::SCHEME_PDRD:
                return "Подряд";
                break;
        }
    }
    
    /**
     * Функция выдает общее данные по СБР за определенный период
     * 
     * @global type $DB
     * @param array $period     Период
     * @return array 
     */
    public function getStatsCountsLC($period) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
        global $DB;
        
        $scheme_type = sbr::SCHEME_LC;
        
        $sql = "-- Общее количество СБР созданных в данный период времени
        SELECT 
          1 as type, COUNT(*) as cnt, null::numeric  as sum
        FROM 
          sbr 
        WHERE scheme_type = {$scheme_type} 
        AND posted >= '{$period[0]}' 
        AND posted <= '{$period[1]}' 

        UNION

        -- Количество покрытых, Сумма покрытия;
        SELECT 
          2, COUNT(*), SUM(p.sum) 
        FROM 
          sbr s
          LEFT JOIN pskb_lc p ON p.sbr_id = s.id
        WHERE s.scheme_type = {$scheme_type} 
        AND p.covered >= '{$period[0]}' 
        AND p.covered <= '{$period[1]}'

        UNION

        -- Количество открытых (исполнителю переведено), Сумма открытия (Исполнителю переведено);
        SELECT 
          3, COUNT(*), SUM(ss.credit_sum) 
        FROM 
          sbr s
          INNER JOIN sbr_stages stg ON stg.sbr_id = s.id
          LEFT JOIN pskb_lc p ON p.sbr_id = s.id
          LEFT JOIN sbr_stages_payouts ss ON ss.stage_id = stg.id AND ss.user_id = s.frl_id
        WHERE s.scheme_type = {$scheme_type} 
        AND ss.completed >= '{$period[0]}' 
        AND ss.completed <= '{$period[1]}'

        UNION

        -- Количество возвратов по СБР (вернули Работодателю), Сумма возвратов (вернули Работодателю);
        SELECT
          4, COUNT(*), SUM(ss.credit_sum) 
        FROM 
          sbr s
          INNER JOIN sbr_stages stg ON stg.sbr_id = s.id
          LEFT JOIN pskb_lc p ON p.sbr_id = s.id
          LEFT JOIN sbr_stages_payouts ss ON ss.stage_id = stg.id AND ss.user_id = s.emp_id
        WHERE s.scheme_type = {$scheme_type} 
        AND ss.completed >= '{$period[0]}' 
        AND ss.completed <= '{$period[1]}'

        UNION 

        -- Процент от Работодателей
        SELECT
            5, 0, SUM(sbr_calctax( sbr_taxes_id( sbr_exrates_map(p.ps_emp), null, null, s.scheme_id, p.sum), s.scheme_id, p.sum, (p.\"tagPerf\" + 1), 1, 1, sbr_exrates_map(p.ps_emp), null, null))
        FROM 
          sbr s
          INNER JOIN sbr_stages stg ON stg.sbr_id = s.id
          LEFT JOIN pskb_lc p ON p.sbr_id = s.id
          LEFT JOIN sbr_stages_payouts ss ON ss.stage_id = stg.id AND ss.user_id = s.emp_id
        WHERE s.scheme_type = {$scheme_type} 
        AND ss.completed >= '{$period[0]}' 
        AND ss.completed <= '{$period[1]}'

        UNION 

        -- Процент от Исполнителей
        SELECT
          6, 0, SUM(sbr_calctax( sbr_taxes_id( sbr_exrates_map(p.ps_frl), 0, null ), s.scheme_id, p.sum, (p.\"tagPerf\" + 1), 1, 1, sbr_exrates_map(p.ps_frl), null, null))
        FROM 
          sbr s
          LEFT JOIN pskb_lc p ON p.sbr_id = s.id
          LEFT JOIN sbr_stages sst ON sst.sbr_id = s.id
          LEFT JOIN sbr_stages_payouts ss ON ss.stage_id = sst.id AND ss.user_id = s.frl_id
          LEFT JOIN sbr_stages_arbitrage sa ON sa.stage_id = sst.id
        WHERE s.scheme_type = {$scheme_type} 
        AND ss.completed >= '{$period[0]}' 
        AND ss.completed <= '{$period[1]}'
        
        ORDER BY type";
        
        
        $rows = $DB->rows($sql);
        
        if(!$rows) return array();
        
        foreach($rows as $k=>$v) {
            $count[$v['type']] = $v;
        }
        
        return $count;
    } 
    
    /**
     * возвращает статистику по сделкам СБР
     * @param array $period array('0' => начало периода, '1' => конец периода)
     * @param string $groupBy 'day', 'month' или 'year'
     * @param bool $akkr выводить данные по аккредитиву
     * @param bool $pdrd выводить данные по подряду
     */
    public function getSbrStats ($period, $groupBy, $akkr, $pdrd) {
        
        $akkrStats = $akkr ? $this->getStatsAkkr($period, $groupBy) : array();
        $pdrdStats = $pdrd ? $this->getStatsPdrd($period, $groupBy) : array();
        // если нужны данные по аккредитиву и по подряду - то суммируем все
        if ($akkr && $pdrd) {
            $keys = array('sum', 'cnt', 'avg', 'sum_wmr', 'sum_yd', 'sum_fm', 'sum_bank', 'cnt_wmr', 'cnt_yd', 'cnt_fm', 'cnt_bank');
            foreach ($pdrdStats as $index => $stats) {
                foreach ($stats as $date => $pdrdDateData) {
                    $akkrDateData = $akkrStats[$index][$date];
                    foreach ($keys as $key) {
                        $akkrStats[$index][$date][$key] = $akkrDateData[$key] + $pdrdDateData[$key];
                    }
                }
            }
            return $akkrStats;
        } elseif ($akkr) { // только аккредитив
            return $akkrStats;
        } elseif ($pdrd) { // только подряд
            return $pdrdStats;
        } else {
            return array();
        }        
    }
    
    /**
     * Функция выдает данные по СБр за определенный период, данные разбиты на каждый день
     * 
     * @global type $DB
     * @param array $period     Период
     * @param string $groupBy 'day', 'month' или 'year'
     * @return type 
     */
    public function getStatsAkkr($period, $groupBy) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/onlinedengi.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pskb.php");
        global $DB;
        
        if ($groupBy === 'day') {
            $groupByPattern = 'YYYYMMDD';
        } elseif ($groupBy === 'month') {
            $groupByPattern = 'YYYYMM';
        } elseif ($groupBy === 'year') {
            $groupByPattern = 'YYYY';
        }
        
        $noPeriod = !$period[0] && !$period[1] ? true : false;
        
        $psPatternEmp = "
            SUM((p.ps_emp = " . onlinedengi::WMR .        ")::integer) as cnt_wmr,           -- количество сделок с оплатой через webmoney
            SUM((p.ps_emp = " . onlinedengi::YD .         ")::integer) as cnt_yd,            -- количество сделок с оплатой через яндекс деньги
            SUM((p.ps_emp = " . onlinedengi::CARD .       ")::integer) as cnt_card,          -- количество сделок с оплатой пластиковой картой
            SUM((p.ps_emp in(" . onlinedengi::BANK_YL . ", " . onlinedengi::BANK_FL . "))::integer) as cnt_bank,      -- количество сделок с оплатой через банк
            SUM((p.ps_emp = " . pskb::WW .                ")::integer) as cnt_ww,            -- количество сделок с оплатой через веб кошелек
            SUM(CASE WHEN (p.ps_emp = " . onlinedengi::WMR .        ") THEN p.sum ELSE 0 END) as sum_wmr,       -- сумма сделок с оплатой через webmoney
            SUM(CASE WHEN (p.ps_emp = " . onlinedengi::YD .         ") THEN p.sum ELSE 0 END) as sum_yd,        -- сумма сделок с оплатой через яндекс деньги
            SUM(CASE WHEN (p.ps_emp = " . onlinedengi::CARD .       ") THEN p.sum ELSE 0 END) as sum_card,      -- сумма сделок с оплатой пластиковой картой
            SUM(CASE WHEN (p.ps_emp in(" . onlinedengi::BANK_YL . ", " . onlinedengi::BANK_FL . ")) THEN p.sum ELSE 0 END) as sum_bank,  -- сумма сделок с оплатой через банк
            SUM(CASE WHEN (p.ps_emp = " . pskb::WW .                ") THEN p.sum ELSE 0 END) as sum_ww         -- сумма сделок с оплатой через веб кошелек";
        $psPatternFrl = "
            SUM((p.ps_frl = " . onlinedengi::WMR .        ")::integer) as cnt_wmr,           -- количество сделок с оплатой через webmoney
            SUM((p.ps_frl = " . onlinedengi::YD .         ")::integer) as cnt_yd,            -- количество сделок с оплатой через яндекс деньги
            SUM((p.ps_frl = " . onlinedengi::CARD .       ")::integer) as cnt_card,          -- количество сделок с оплатой пластиковой картой
            SUM((p.ps_emp in(" . onlinedengi::BANK_YL . ", " . onlinedengi::BANK_FL . "))::integer) as cnt_bank,      -- количество сделок с оплатой через банк
            SUM((p.ps_frl = " . pskb::WW .                ")::integer) as cnt_ww,            -- количество сделок с оплатой через веб кошелек
            SUM(CASE WHEN (p.ps_frl = " . onlinedengi::WMR .        ") THEN ss.credit_sum ELSE 0 END) as sum_wmr,       -- сумма сделок с оплатой через webmoney
            SUM(CASE WHEN (p.ps_frl = " . onlinedengi::YD .         ") THEN ss.credit_sum ELSE 0 END) as sum_yd,        -- сумма сделок с оплатой через яндекс деньги
            SUM(CASE WHEN (p.ps_frl = " . onlinedengi::CARD .       ") THEN ss.credit_sum ELSE 0 END) as sum_card,      -- сумма сделок с оплатой пластиковой картой
            SUM(CASE WHEN (p.ps_emp in(" . onlinedengi::BANK_YL . ", " . onlinedengi::BANK_FL . ")) THEN ss.credit_sum ELSE 0 END) as sum_bank,  -- сумма сделок с оплатой через банк
            SUM(CASE WHEN (p.ps_frl = " . pskb::WW .                ") THEN ss.credit_sum ELSE 0 END) as sum_ww         -- сумма сделок с оплатой через веб кошелек";
        // заглушка если подробные данные о платежных системах не нужны
        $psPatternBlank = "
            0 as cnt_wmr, 0 as cnt_yd, 0 as cnt_card, 0 as cnt_bank, 0 as cnt_ww,
            0 as sum_wmr, 0 as sum_yd, 0 as sum_card, 0 as sum_bank, 0 as sum_ww";
        
        $scheme_type = sbr::SCHEME_LC;
        
        $sql = "
        WITH range_sum as (
            SELECT 
            st.*,
            sts.scheme_id,
            (CASE WHEN (st.formula LIKE '%#C*(%#C<=%') THEN
              replace(replace( regexp_matches( regexp_matches(st.formula, '\\(#C.*\\)')::text , '<=(\\\d+)' )::text, '{', ''), '}', '')::int
            ELSE 
              100000000000000
            END) as max,

            (CASE WHEN (st.formula LIKE '%#C*(#C>%') THEN
              replace(replace( regexp_matches( regexp_matches(st.formula, '\\(#C.*\\)')::text , '>(\\\d+)' )::text, '{', ''), '}', '')::int
            ELSE 
              0
            END) as min
            FROM sbr_taxes st
            INNER JOIN sbr_taxes_schemes as sts ON sts.tax_id = st.id
        )
        ";
        
        $sql .= "-- Общее количество СБР созданных в данный период времени
        SELECT 
            1 as type, COUNT(*) as cnt, null::numeric as sum, to_char(posted, '$groupByPattern') as _day,
            0 as avg,
            $psPatternBlank
        FROM 
            sbr 
        WHERE scheme_type = {$scheme_type} ";
        if ($noPeriod) {
            $sql .= " AND posted IS NOT NULL";
        } else {
            $sql .= $period[0] ? " AND posted >= '$period[0]'" : "";
            $sql .= $period[1] ? " AND posted <= '$period[1]'" : "";
        }
        // p.covered -- на бете тк мы не пишем bank_covered в таблицу
        $fld  = is_release() ? "bank_covered" : "covered";
        $sql .= " GROUP BY to_char(posted, '$groupByPattern')

        UNION ALL

        -- Количество покрытых, Сумма покрытия;
        SELECT 
            2, COUNT(*), SUM(p.sum), to_char(p.{$fld}, '$groupByPattern') as _day,
            AVG(p.sum) as avg,
            $psPatternEmp
        FROM 
            sbr s
            LEFT JOIN pskb_lc p ON p.sbr_id = s.id
        WHERE s.scheme_type = {$scheme_type} ";
        if ($noPeriod) {
            $sql .= " AND p.{$fld} IS NOT NULL";
        } else {
            $sql .= $period[0] ? " AND p.{$fld} >= '{$period[0]}'" : "";
            $sql .= $period[1] ? " AND p.{$fld} <= '{$period[1]}'" : "";
        }
        $sql .= " GROUP BY to_char(p.{$fld}, '$groupByPattern') 

        UNION ALL

        -- Количество открытых (исполнителю переведено), Сумма открытия (Исполнителю переведено);
        SELECT 
            3, COUNT(*), SUM(ss.credit_sum), to_char(ss.bank_completed, '$groupByPattern') as _day,
            AVG(ss.credit_sum) as avg,
            $psPatternFrl
        FROM 
            sbr s
            INNER JOIN pskb_lc p ON p.sbr_id = s.id
            INNER JOIN sbr_stages sst ON sst.sbr_id = s.id
            INNER JOIN sbr_stages_payouts ss ON ss.stage_id = sst.id AND ss.user_id = s.frl_id
        WHERE s.scheme_type = {$scheme_type} ";
        if ($noPeriod) {
            $sql .= " AND ss.bank_completed IS NOT NULL";
        } else {
            $sql .= $period[0] ? " AND ss.bank_completed >= '{$period[0]}'" : "";
            $sql .= $period[1] ? " AND ss.bank_completed <= '{$period[1]}'" : "";
        }
        $sql .= " GROUP BY to_char(ss.bank_completed, '$groupByPattern') 

        UNION ALL

        -- Количество возвратов по СБР (вернули Работодателю), Сумма возвратов (вернули Работодателю);
        SELECT
            4, COUNT(*), SUM(ss.credit_sum), to_char(ss.completed, '$groupByPattern') as _day,
            AVG(ss.credit_sum) as avg,
            $psPatternBlank  
        FROM 
            sbr s
            INNER JOIN pskb_lc p ON p.sbr_id = s.id
            INNER JOIN sbr_stages sst ON sst.sbr_id = s.id
            INNER JOIN sbr_stages_payouts ss ON ss.stage_id = sst.id AND ss.user_id = s.emp_id
        WHERE s.scheme_type = {$scheme_type} ";
        if ($noPeriod) {
            $sql .= " AND ss.completed IS NOT NULL";
        } else {
            $sql .= $period[0] ? " AND ss.completed >= '{$period[0]}'" : "";
            $sql .= $period[1] ? " AND ss.completed <= '{$period[1]}'" : "";
        }
        
        // p.covered -- на бете тк мы не пишем bank_covered в таблицу
        $fld  = is_release() ? "bank_covered" : "covered";
        $sql .= " GROUP BY to_char(ss.completed, '$groupByPattern') 

        UNION ALL

        -- Процент от Работодателей
        -- cчитаем от резерва (покрытие)
        SELECT
            5, 0, SUM(round(sbr_calctax( rs.id, s.scheme_id, sst.cost, (p.\"tagPerf\" + 1), 1, 1, sbr_exrates_map(p.ps_emp), null, null), 2))
            , to_char(p.{$fld}, '$groupByPattern') as _day,
            AVG(sbr_calctax( rs.id, s.scheme_id, sst.cost, (p.\"tagPerf\" + 1), 1, 1, sbr_exrates_map(p.ps_emp), null, null)) as avg,
            $psPatternBlank
        FROM 
          sbr s
          INNER JOIN pskb_lc p ON p.sbr_id = s.id
          INNER JOIN sbr_stages sst ON sst.sbr_id = s.id
          LEFT JOIN range_sum rs ON strpos(rs.formula, 'P=' || sbr_exrates_map(p.ps_emp)) > 0 AND rs.role = 1 AND rs.tax_code = 'TAX_FL' AND rs.scheme_id = s.scheme_id AND sst.cost > rs.min AND sst.cost <= rs.max
        WHERE s.scheme_type = {$scheme_type}";
        if ($noPeriod) {
            $sql .= " AND p.{$fld} IS NOT NULL";
        } else {
            $sql .= $period[0] ? " AND p.{$fld} >= '{$period[0]}'" : "";
            $sql .= $period[1] ? " AND p.{$fld} <= '{$period[1]}'" : "";
        }
        $sql .= " GROUP BY to_char(p.{$fld}, '$groupByPattern')

        UNION ALL

        -- Процент от Исполнителей
        -- считается только от выплат исполнителю, учитывается решение арбитража
        SELECT
            6, 0, SUM(round(sbr_calctax( rs.id, s.scheme_id, (sst.cost * COALESCE(sa.frl_percent, 1)), (p.\"tagPerf\" + 1), 1, 1, sbr_exrates_map(CASE WHEN sst.cost < ".pskb::WW_ONLY_SUM." AND p.\"tagPerf\" = 0 THEN " . pskb::WW . " ELSE p.ps_frl END), null, null),2))
            , to_char(ss.bank_completed, '$groupByPattern') as _day,
            AVG(sbr_calctax( rs.id, s.scheme_id, (sst.cost * COALESCE(sa.frl_percent, 1)), (p.\"tagPerf\" + 1), 1, 1, sbr_exrates_map(CASE WHEN sst.cost < ".pskb::WW_ONLY_SUM." AND p.\"tagPerf\" = 0 THEN " . pskb::WW . " ELSE p.ps_frl END), null, null)),
            $psPatternBlank
        FROM 
            sbr s
            INNER JOIN pskb_lc p ON p.sbr_id = s.id
            INNER JOIN sbr_stages sst ON sst.sbr_id = s.id
            INNER JOIN sbr_stages_payouts ss ON ss.stage_id = sst.id AND ss.user_id = s.frl_id
            LEFT JOIN sbr_stages_arbitrage sa ON sa.stage_id = sst.id
            LEFT JOIN sbr_taxes rs ON strpos(formula, 'P=' || sbr_exrates_map(CASE WHEN sst.cost < ".pskb::WW_ONLY_SUM." AND p.\"tagPerf\" = 0 THEN " . pskb::WW . " ELSE p.ps_frl END)) > 0 AND role = 0 AND tax_code = 'TAX_FL'
        WHERE s.scheme_type = {$scheme_type} ";
        if ($noPeriod) {
            $sql .= " AND ss.bank_completed IS NOT NULL";
        } else {
            $sql .= $period[0] ? " AND ss.bank_completed >= '{$period[0]}'" : "";
            $sql .= $period[1] ? " AND ss.bank_completed <= '{$period[1]}'" : "";
        }
        $sql .= " GROUP BY to_char(ss.bank_completed, '$groupByPattern') 

        ORDER BY type, _day;";
        
        $rows =  $DB->rows($sql);
        
        if(!$rows) return array();
        
        foreach($rows as $k=>$v) {
            $stat[$v['type']][$v['_day']] = $v;
        }
        
        return $stat;
    }
    
    /**
     * возвращает статистические данные по подряду
     */
    public function getStatsPdrd ($period, $groupBy) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/exrates.php");
        global $DB;
        
        if ($groupBy === 'day') {
            $groupByPattern = 'YYYYMMDD';
        } elseif ($groupBy === 'month') {
            $groupByPattern = 'YYYYMM';
        } elseif ($groupBy === 'year') {
            $groupByPattern = 'YYYY';
        }
        
        $noPeriod = !$period[0] && !$period[1] ? true : false;
        
        $psPatternEmp = "
            SUM((cost_sys = " . exrates::WMR . ")::integer) as cnt_wmr,           -- количество сделок с оплатой через webmoney
            SUM((cost_sys = " . exrates::YM . ")::integer) as cnt_yd,              -- количество сделок с оплатой через яндекс деньги
            SUM((cost_sys = " . exrates::FM . ")::integer) as cnt_fm,          -- количество сделок с оплатой FM
            SUM((cost_sys = " . exrates::BANK . ")::integer) as cnt_bank,      -- количество сделок с оплатой через банк
            SUM(CASE WHEN (cost_sys = " . exrates::WMR . ") THEN cost ELSE 0 END) as sum_wmr,       -- сумма сделок с оплатой через webmoney
            SUM(CASE WHEN (cost_sys = " . exrates::YM . ") THEN cost ELSE 0 END) as sum_yd,          -- сумма сделок с оплатой через яндекс деньги
            SUM(CASE WHEN (cost_sys = " . exrates::FM . ") THEN cost ELSE 0 END) as sum_fm,      -- сумма сделок с оплатой FM
            SUM(CASE WHEN (cost_sys = " . exrates::BANK . ") THEN cost ELSE 0 END) as sum_bank  -- сумма сделок с оплатой через банк";
        $psPatternFrl = "
            SUM((ss.credit_sys = " . exrates::WMR . ")::integer) as cnt_wmr,           -- количество сделок с оплатой через webmoney
            SUM((ss.credit_sys = " . exrates::YM . ")::integer) as cnt_yd,              -- количество сделок с оплатой через яндекс деньги
            SUM((ss.credit_sys = " . exrates::FM . ")::integer) as cnt_card,          -- количество сделок с оплатой FM
            SUM((ss.credit_sys = " . exrates::BANK . ")::integer) as cnt_bank,      -- количество сделок с оплатой через банк
            SUM(CASE WHEN (ss.credit_sys = " . exrates::WMR . ") THEN ss.credit_sum ELSE 0 END) as sum_wmr,       -- сумма сделок с оплатой через webmoney
            SUM(CASE WHEN (ss.credit_sys = " . exrates::YM . ") THEN ss.credit_sum ELSE 0 END) as sum_yd,          -- сумма сделок с оплатой через яндекс деньги
            SUM(CASE WHEN (ss.credit_sys = " . exrates::FM . ") THEN ss.credit_sum ELSE 0 END) as sum_card,      -- сумма сделок с оплатой FM
            SUM(CASE WHEN (ss.credit_sys = " . exrates::BANK . ") THEN ss.credit_sum ELSE 0 END) as sum_bank  -- сумма сделок с оплатой через банк";
        // заглушка если подробные данные о платежных системах не нужны
        $psPatternBlank = "
            0 as cnt_wmr, 0 as cnt_yd, 0 as cnt_card, 0 as cnt_bank,
            0 as sum_wmr, 0 as sum_yd, 0 as sum_card, 0 as sum_bank";
        
        $scheme_type = sbr::SCHEME_PDRD2;
        
        $sql = "-- Общее количество СБР созданных в данный период времени
            SELECT 
                1 as type, COUNT(*) as cnt, null::numeric as sum, to_char(posted, '$groupByPattern') as _day,
                0 as avg,
                $psPatternBlank
            FROM 
                sbr 
            WHERE scheme_type = {$scheme_type} ";
            if ($noPeriod) {
                $sql .= " AND posted IS NOT NULL";
            } else {
                $sql .= $period[0] ? " AND posted >= '$period[0]'" : "";
                $sql .= $period[1] ? " AND posted <= '$period[1]'" : "";
            } 
            $sql .= " GROUP BY to_char(posted, '$groupByPattern')";
            
        $sql .= " 
            UNION ALL
            
            -- Количество покрытых, Сумма покрытия;
            SELECT 
                2, COUNT(*), SUM(s.cost) as sum, to_char(reserved_time, '$groupByPattern') as _day,
                AVG(s.cost) as avg,
                $psPatternEmp
            FROM 
                sbr s
            --LEFT JOIN account_operations ao
            --    ON ao.id = s.reserved_id
            WHERE s.scheme_type = {$scheme_type} ";
            if ($noPeriod) {
                $sql .= " AND reserved_time IS NOT NULL";
            } else {
                $sql .= $period[0] ? " AND reserved_time >= '{$period[0]}'" : "";
                $sql .= $period[1] ? " AND reserved_time <= '{$period[1]}'" : "";
            }
            $sql .= " GROUP BY to_char(reserved_time, '$groupByPattern')";
            
        $sql .= "
            UNION ALL
             
            -- Количество открытых (исполнителю переведено), Сумма открытия (Исполнителю переведено);
            SELECT 
                3, COUNT(*), SUM(ss.credit_sum), to_char(ss.completed, '$groupByPattern') as _day,
                AVG(ss.credit_sum) as avg,
                $psPatternFrl
            FROM 
                sbr s
                INNER JOIN sbr_stages sst ON sst.sbr_id = s.id
                INNER JOIN sbr_stages_payouts ss ON ss.stage_id = sst.id AND ss.user_id = s.frl_id
            WHERE s.scheme_type = {$scheme_type} ";
            if ($noPeriod) {
                $sql .= " AND ss.completed IS NOT NULL";
            } else {
                $sql .= $period[0] ? " AND ss.completed >= '{$period[0]}'" : "";
                $sql .= $period[1] ? " AND ss.completed <= '{$period[1]}'" : "";
            }
            $sql .= " GROUP BY to_char(ss.completed, '$groupByPattern')";
            
        $sql .= "
            UNION ALL
             
            -- Количество возвратов по СБР (вернули Работодателю), Сумма возвратов (вернули Работодателю);
            SELECT
                4, COUNT(*), SUM(ss.credit_sum), to_char(ss.completed, '$groupByPattern') as _day,
                AVG(ss.credit_sum) as avg,
                $psPatternBlank  
            FROM 
                sbr s
                INNER JOIN sbr_stages sst ON sst.sbr_id = s.id
                INNER JOIN sbr_stages_payouts ss ON ss.stage_id = sst.id AND ss.user_id = s.emp_id
            WHERE s.scheme_type = {$scheme_type} ";
            if ($noPeriod) {
                $sql .= " AND ss.completed IS NOT NULL";
            } else {
                $sql .= $period[0] ? " AND ss.completed >= '{$period[0]}'" : "";
                $sql .= $period[1] ? " AND ss.completed <= '{$period[1]}'" : "";
            }
            $sql .= " GROUP BY to_char(ss.completed, '$groupByPattern')";
            
        $sql .= "
             UNION ALL

            -- Процент от Работодателей
            -- считается от всех выплат, не учитывается арбитраж
            SELECT
                5, 0, SUM(sst.cost * 0.07),
                to_char(ss.completed, '$groupByPattern') as _day,
                AVG(sst.cost * 0.07) as avg,
                $psPatternBlank
            FROM 
                sbr s
            INNER JOIN sbr_stages sst ON sst.sbr_id = s.id
            INNER JOIN sbr_stages_payouts ss ON ss.stage_id = sst.id AND ss.user_id = s.emp_id
            WHERE s.scheme_type = {$scheme_type} ";
            if ($noPeriod) {
                $sql .= " AND ss.completed IS NOT NULL";
            } else {
                $sql .= $period[0] ? " AND ss.completed >= '{$period[0]}'" : "";
                $sql .= $period[1] ? " AND ss.completed <= '{$period[1]}'" : "";
            }
            $sql .= " GROUP BY to_char(ss.completed, '$groupByPattern')";
        
        $sql .= "
            UNION ALL
             
            -- Процент от Исполнителей
            -- считается только от выплат исполнителю, учитывается решение арбитража
            SELECT
                6, 0, SUM(sst.cost * COALESCE(sa.frl_percent, 1) * 0.03),
                to_char(ss.completed, '$groupByPattern') as _day,
                AVG(sst.cost * COALESCE(sa.frl_percent, 1) * 0.03),
                $psPatternBlank
            FROM 
                sbr s
            INNER JOIN sbr_stages sst ON sst.sbr_id = s.id
            INNER JOIN sbr_stages_payouts ss ON ss.stage_id = sst.id AND ss.user_id = s.frl_id
            INNER JOIN sbr_stages_arbitrage sa ON sa.stage_id = sst.id
            WHERE s.scheme_type = {$scheme_type} ";
            if ($noPeriod) {
                $sql .= " AND ss.completed IS NOT NULL";
            } else {
                $sql .= $period[0] ? " AND ss.completed >= '{$period[0]}'" : "";
                $sql .= $period[1] ? " AND ss.completed <= '{$period[1]}'" : "";
            }
            $sql .= " GROUP BY to_char(ss.completed, '$groupByPattern')";
                
        $sql .= " ORDER BY type, _day;";
        
        $rows =  $DB->rows($sql);
        
        if(!$rows) return array();
        
        foreach($rows as $k=>$v) {
            $stat[$v['type']][$v['_day']] = $v;
        }
        
        return $stat;
    }
    
    /**
     * Функция выборки сгенерированных документов ИТО
     * 
     * @global type $DB
     * @return array
     */
    static function getITODocs($id = null) {
        global $DB;
        $where = '';
        if($id !== null) $where = "WHERE " . $DB->parse('ito.id = ?i', $id);
        $sql = "SELECT f.*, ito.* FROM sbr_admin_doc_ito ito INNER JOIN file f ON f.id = ito.file_id {$where} ORDER BY date_period DESC";
        
        return $DB->rows($sql);
    }
    
    /**
     * Генерируем документы ИТО
     * 
     * @global type $DB
     * @param array   $period       Период генерации (задается месяц год (01-01-2012, 30-01-2012))
     * @param boolean $return_file  Возвращать сгенерированный файл или нет
     * @return type
     */
    static function generateDocITO($period, $return_file = false, $doc_type = 'odt') {
        global $DB;
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/odt2pdf.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/ODTDocument.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/XLSXDocument.php");
        
        switch($doc_type) {
            case 'odt':
                $DOC = new ODTDocument('tpl_ito.odt');
                $ito = new ODTDocument_ITO();
                $ito->setPeriod($period);
                $DOC->setAdapter($ito);
                break;
            case 'xlsx':
                $DOC = new XLSXDocument_ITO();
                $DOC->setPeriod($period);
                break;
        }
        $file = $DOC->generateDocument(true);
        
        $old_file = $DB->val("SELECT file_id FROM sbr_admin_doc_ito WHERE date_period = ?", $period[0]);
        
        if($old_file) {
            $OFile = new CFile();
            $OFile->Delete($old_file);
            
            $sql = "UPDATE sbr_admin_doc_ito SET date_create = NOW(), file_id = ? WHERE date_period = ?";
            $res = $DB->query($sql, $file->id, $period[0]);
        } else {
            $sql = "INSERT INTO sbr_admin_doc_ito (date_create, date_period, file_id) VALUES(NOW(), ?, ?)";
            $res = $DB->query($sql, $period[0], $file->id);
        }
        
        return $return_file ? $file : $res;
    }
    
    static function updateITOFile($file_id, $doc_id) {
        global $DB;
        
        $sql = "UPDATE sbr_admin_doc_ito SET date_create = NOW(), file_id = ? WHERE id = ?";
        return $DB->query($sql, $file_id, $doc_id);
    }
    
    /**
     * Берем все средства зарезервированные через аккредитив
     * 
     * @global type $DB
     * @param array $period  Период (2012-01-01, 2012-30-01))
     * @return array
     */
    static function getReservedSbr($period) {
        global $DB;
        
        $sql = "SELECT s.cost, p.\"nameCust\", p.sbr_id, p.bank_covered as covered, p.lc_id 
                FROM sbr s
                INNER JOIN pskb_lc p ON p.sbr_id = s.id 
                WHERE s.reserved_id IS NOT NULL AND s.scheme_type = ?
                AND to_char(p.bank_covered, 'YYYY-MM-DD') >= ? AND to_char(p.bank_covered, 'YYYY-MM-DD') <= ?
                ORDER BY p.bank_covered ASC";
        
       return $DB->rows($sql, sbr::SCHEME_LC, $period[0], $period[1]);   
    }
    
    static function authMobPhone($uid, $status = true) {
        global $DB;
        return $DB->update('sbr_reqv', array('is_activate_mob' => $status), "user_id = {$uid}");
    }
    
    static function safetyMobPhone($uid, $status = true) {
        global $DB;
        return $DB->update('sbr_reqv', array('is_safety_mob' => $status), "user_id = {$uid}");
    }

    /**
     * ищет номер телефона безопасности
     * @param $phone номер телефона
     * @param string $role 'emp' или 'frl'
     */
    static function findSafetyPhone($phone, $role = null) {
        global $DB;
        $phone = preg_replace("#^\+#", "", $phone);
        if ($role) {
            $whereRole = " AND u.role = B'" . ($role === 'emp' ? '100000' : '000000') . "' ";
        }

        $sql = "SELECT u.safety_only_phone, u.uid FROM sbr_reqv s
                INNER JOIN users u ON u.uid = s.user_id
                WHERE (_1_mob_phone = ? OR _1_mob_phone = ? OR _2_mob_phone = ? OR _2_mob_phone = ?)
                $whereRole";
        return $DB->row($sql, $phone, '+'.$phone, $phone, '+'.$phone);
    }

    /**
     * возвращает массив с данными соответствующими указанному телефонному номеру для восстановления доступа к аккаунту
     * @param string $phone
     */
    static function findSafetyPhones($phone) {
        global $DB;
        $phone = preg_replace("#^\+#", "", $phone);
        $sql = "SELECT u.safety_only_phone, u.uid, u.login FROM sbr_reqv s
                INNER JOIN users u ON u.uid = s.user_id
                WHERE _1_mob_phone = ? OR _1_mob_phone = ? OR _2_mob_phone = ? OR _2_mob_phone = ?";
        return $DB->rows($sql, $phone, '+'.$phone, $phone, '+'.$phone);
    }

    /**
     * возвращает массив с информацией для восстановления доступа связанной с указанным логином
     * @param string $login
     */
    static function findSafetyPhoneByLogin ($login) {
        global $DB;
        $sql = "
            SELECT u.safety_only_phone, u.uid, u.login, COALESCE(sr._1_mob_phone, sr._2_mob_phone) as phone
            FROM sbr_reqv sr
            INNER JOIN users u
                ON u.uid = sr.user_id
            WHERE u.login = ?";
        $mob = $DB->row($sql, $login);
        return $mob;
    }
    
    /**
     * Берем статус выплаты текущий
     * 
     * @param array $id  [sbr_id, num]
     */
    public static function getStatePayout($id, $not_num = false) {
        global $DB;
        $sql = "SELECT sp.*, ss.num, s.emp_id, s.frl_id FROM 
                sbr_stages ss
                INNER JOIN sbr_stages_payouts sp ON sp.stage_id = ss.id
                INNER JOIN sbr s ON s.id = ss.sbr_id
                WHERE ss.sbr_id = ?" . ($not_num ? "" : " AND ss.num = ?");
        $res = ($not_num ? $DB->rows($sql, $id[0]) : $DB->row($sql, $id[0], $id[1]));
        return $res;
    }
    
    public static function getTaxPercent($role, $paysys, $abbr, $scheme_type) {
        global $DB;
        
        $paysys = intval($paysys);
        
        $sql = "SELECT sts.percent FROM sbr_taxes  st
                 INNER JOIN sbr_taxes_schemes sts ON sts.tax_id = st.id
                 WHERE st.role = ? 
                 AND strpos(st.formula, 'P={$paysys}') > 0 
                 AND st.abbr = ? 
                 AND sts.scheme_id = (SELECT MAX(id) FROM sbr_schemes WHERE type = ?)
                ";
        $percent = $DB->val($sql, $role, $abbr, $scheme_type) * 100;
        return $percent;
    }
    
    public static function fsort($a, $b) {
       $at = strtotime($a['sign_time'] ? $a['sign_time'] : $a['publ_time']);
       $bt = strtotime($b['sign_time'] ? $b['sign_time'] : $b['publ_time']);
       if($at == $bt) {
           return 0;
       }
       return $at < $bt ? -1 : 1;
    }
    
    public function sortFiles() {
        if(!$this->all_docs) return;
        usort($this->all_docs, array('sbr_meta', 'fsort'));
    }
    
    /**
     * Редактирование  названия сделки
     * 
     * @global type $DB
     * @param type $sbr_name
     * @param type $stage_name
     * @param type $sbr_id
     * @param type $stage_id
     * @return type
     */
    static public function setNamesSBR($sbr_name, $stage_name, $sbr_id, $stage_id) {
        global $DB;
        
        $sql = 'UPDATE sbr SET name = ? WHERE id = ?';
        $DB->query($sql, $sbr_name, $sbr_id);
        
        $sql = 'UPDATE sbr_stages SET name = ? WHERE id = ?';
        return $DB->query($sql, $stage_name, $stage_id);
    }
    
    /**
     * Возвращаем в работу сделки у которых истекли сроки действия паузы
     * 
     * @global type $DB
     */
    public function renewalWorkStagesByFrozen() {
        global $DB;
        
        // Обновляем тех кто не согласился с событий паузы (удаляем само событие)
        $sql = "DELETE FROM sbr_events WHERE id IN (
                SELECT se.id FROM 
                sbr_stages ss
                INNER JOIN sbr_events se ON se.own_id = ss.id
                INNER JOIN sbr_versions sv ON sv.event_id = se.id
                WHERE 
                ss.status = ?i
                AND ss.days_pause IS NOT NULL
                AND ( ss.start_pause + ( CAST(ss.days_pause as varchar) || ' days' )::interval ) <= NOW()
                AND se.ev_code IN(14,12) AND se.fstatus IS NULL AND sv.src_type_id IN( 23, 8) )
                RETURNING own_id, sbr_id, version, ev_code";
        $deleted = $DB->rows($sql, sbr_stages::STATUS_FROZEN);
        $pause_reset = array();
        if( !empty($deleted) ) {
            //@todo нужна оптимизация но мешает то что XACT_ID должен быть для каждой сделкий свой
            foreach($deleted as $event) {
                if($event['ev_code'] == 12) continue;
                if($XACT_ID = $this->_openXact(true)) {
                    $result = sbr_notification::sbr_add_event($XACT_ID, $event['sbr_id'], $event['own_id'], "sbr_stages.PAUSE_RESET", $event['version'], null, null);
                    if(!$result) {
                        $this->_abortXact();
                    }
                    $this->_commitXact();
                    $pause_reset_ids[] = $event['own_id'];
                    $pause_reset[] = $event['sbr_id']."_".$event['own_id'];
                }
            }
        }
        // Фиксируем дни паузы за рабочие у отмененных пауз
        $sql = "UPDATE sbr_stages SET worked_time = worked_time + (days_pause::text || ' days')::interval WHERE id IN(?l)";
        $DB->query($sql, $pause_reset_ids);
        
        // Возвращаем в работу
        $sql = "UPDATE sbr_stages SET status = ?i, days_pause = NULL, start_pause = NULL, frl_version = version 
                WHERE status = ?i 
                AND days_pause IS NOT NULL
                AND ( start_pause + ( CAST(days_pause as varchar) || ' days' )::interval ) <= NOW()
                RETURNING sbr_id, id, version";
        
        $result = $DB->rows($sql, sbr_stages::STATUS_PROCESS, sbr_stages::STATUS_FROZEN);
        foreach($result as $event) {
            if(in_array($event['sbr_id'] . "_" . $event['id'], $pause_reset)) continue;
            //@todo нужна оптимизация но мешает то что XACT_ID должен быть для каждой сделкий свой
            if($XACT_ID = $this->_openXact(true)) {
                $result = sbr_notification::sbr_add_event($XACT_ID, $event['sbr_id'], $event['id'], "sbr_stages.PAUSE_OVER", $event['version'], null, null);
                if(!$result) {
                    $this->_abortXact();
                }
                $this->_commitXact();
            }
        }
    }
    
    /**
     * Получаем данные на которые согласился фрилансер
     * 
     * @global type $DB
     * @param integer $id       ИД события согласия с изменениями
     * @param integer $sbr_id      ИД Сделки
     * @param integer $own_id      ИД Этапа
     * @return array
     */
    public static function getChangedDataForFreelancer($id, $sbr_id, $own_id) {
        global $DB;
        
        $membuf = new memBuff();
        $memkey = "sbr_changed_events_{$id}_{$sbr_id}_{$own_id}";
        $result = $membuf->get($memkey);
        
        if (!$result) {
            $sql = "SELECT DISTINCT ON (src_type_id) src_type_id, se1.id, old_val, new_val, ev_code, version, xtime FROM sbr_events se1
                    INNER JOIN sbr_versions sv ON sv.event_id = se1.id
                    INNER JOIN sbr_xacts sx ON sx.id = se1.xact_id 
                    WHERE se1.sbr_id = ?i AND se1.own_id = ?i AND se1.id < ?i AND se1.id > COALESCE(
                    (SELECT id FROM sbr_events se2 WHERE se2.ev_code = 5 AND se2.sbr_id = ?i AND se2.own_id = ?i AND se2.id < ?i ORDER BY id DESC LIMIT 1) , 0)
                    AND se1.ev_code IN (11, 12, 13, 14) -- События изменения
                    ORDER BY src_type_id, id DESC, old_val, new_val, ev_code, version, xtime;";

            $rows = $DB->rows($sql, $sbr_id, $own_id, $id, $sbr_id, $own_id, $id);
            $result = array();
            foreach($rows as $k=>$v) {
                $result[$v['src_type_id']] = $v;
            }
            $membuf->set($memkey, $result, 600);
        }
        
        return $result;
    }

    /**
     * возвращает данные о документе по его типу
     * @param $type тип документа ('contract')
     */
    public function getDocument ($type) {
        if (!$this->all_docs || !is_array($this->all_docs)) {
            return;
        }
        foreach($this->all_docs as $doc) {
            switch ($type) {
                case 'contract': // договор
                    if ($doc['type'] == 16 && mb_stripos($doc['name'], 'договор') !== false) {
                        return $doc;
                    }
                    break;
            }
        }
    }

    /**
     * возвращает ссылку на документ по его типу
     * @param $type тип документа ('contract')
     */
    public function getDocumentLink ($type) {
        if (!$this->isNewContract()) {
            switch ($type) {
                case 'contract':
                    return '/offer_lc.pdf';
                    break;
            }
        }
        $doc = self::getDocument($type);
        if (!$doc) {
            return;
        }
        $docLink = WDCPREFIX . '/' . $doc['file_path'] . $doc['file_name'];
        return $docLink;
    }


    /**
     * если true - значит новые договора
     */
    public function isNewContract() {
        //return true;
        return strtotime($this->data['sended']) > strtotime(self::NEW_CONTRACT_DATE);
    }
    
    
    /**
     * Проверить все ли реквизиты заполнены у данного пользователя
     * 
     * @param type $uid
     * @param type $is_emp
     * @return boolean
     */
    public static function isValidUserReqvs($uid, $is_emp = false)
    {
        if (isset(self::$users_reqv_is_valid_cache[$uid])) {
            return self::$users_reqv_is_valid_cache[$uid];
        }
        
        $reqvs = self::getUserReqvs($uid);
        if (!$reqvs || !$reqvs['form_type']) {
            return false;
        }
        
        $reqv = $reqvs[$reqvs['form_type']];
        $errors = self::checkRequired($reqvs['form_type'], $reqvs['rez_type'], $reqv, $is_emp);
        $is_valid = empty($errors);
        
        self::$users_reqv_is_valid_cache[$uid] = $is_valid;
        return $is_valid;
    }
    
    /**
     * Прользователь юрик?
     * 
     * @param type $uid
     * @return type
     */
    public static function isFtJuri($uid)
    {
        $reqvs = self::getUserReqvs($uid);
        return (@$reqvs['form_type'] == sbr::FT_JURI);
    }
    
    
    
    
    /**
     * Помечаем финансы как удаленные если пользователь
     * 
     * @global object $DB
     * @param type $uid
     * @return type
     */
    public static function deleteFinance($uid)
    {
        global $DB;

        return $DB->update('sbr_reqv',array(
            'validate_status' => self::VALIDATE_STATUS_DELETED,
        ),'user_id = ?i', $uid);          
    }


    /**
     * Восстановление удаленных финансовых данных
     * 
     * @global object $DB
     * @param type $uid
     * @return type
     */
    public static function repairFinance($uid)
    {
        global $DB;
        
        $data = array('validate_status' => self::VALIDATE_STATUS_BLOCKED);
        return $DB->update('sbr_reqv', $data,'validate_status = ?i AND user_id = ?i', self::VALIDATE_STATUS_DELETED, $uid);          
    }

    

    /**
     * Когда можно редактировать финансы
     * - если новые данные
     * - если данные отклонены
     * 
     * @param type $validate_status
     * @return type
     */
    public static function isStatusAllowEditFinance($validate_status)
    {
        return in_array($validate_status, array(
            self::VALIDATE_STATUS_DEFAULT,
            self::VALIDATE_STATUS_DECLINE
        ));
    }

    




    /**
     * @return DB
     */
    public function db()
    {
        return $GLOBALS['DB'];
    }
   
}