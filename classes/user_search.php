<?php
/**
 * Подключаем предка
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/admin_parent.php");

/**
 * Класс для поиска пользователей
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
class user_search extends admin_parent {    
    /**
     * Сообщения об ошибках
     * 
     * @var array
     */
    static $error = array(
        'fromIP' => '<div style="color: red; padding-top: 10px;">Начальный IP должен состоять из чисел от 0 до 255.<br/>Начинаться с числа. Вместо пропущенных чисел будут подставлены 0</div>',
        'toIP'   => '<div style="color: red; padding-top: 10px;">Конечный IP должен состоять из чисел от 0 до 255.<br/>Начинаться с числа. Вместо пропущенных чисел будут подставлены 255</div>'
    );
    
    /**
     * Конструктор класса
     * 
     * @param int $items_pp Количество пользователей на странице
     */
    function __construct( $items_pp ) {
        parent::__construct( $items_pp );
    }
    
    /**
     * Возвращает информацию о пользователе
     * 
     * @param  int $sUid UID пользователя
     * @return array
     */
    function getUserByUid( $sUid = 0 ) {
        $this->filter = array( 'uid' => $sUid );
        
        $aRet = $this->getUsers( $count );
        
        return $aRet[0];
    }
    
    /**
     * Возвращает список пользователей, удовлетворяющих условиям выборки
     * Внешняя функция
     * 
     * @param  int $count возвращает количество записей удовлтворяющих условиям выборки
     * @param  array $filter Параметры фильтра
     * @param  int $page номер текущей страницы
     * @return array
     */
    function searchUsers( &$count, $filter, $page = 1 ) {
        $this->filter = $filter;
        
        return $this->getUsers( $count, $page );
    }
    
    /**
     * Возвращает список пользователей, удовлетворяющих условиям выборки
     * 
     * @param  int $count возвращает количество записей удовлтворяющих условиям выборки
     * @param  int $page номер текущей страницы
     * @param  string order тип сортировки
     * @param  int $direction порядок сортировки: 0 - по убыванию, не 0 - по возрастанию
     * @param  bool $unlimited опционально. установить в true если нужно получить все записи (без постраничного вывода)
     * @return array
     */
    function getUsers( &$count, $page = 1, $order = 'general', $direction = 0, $unlimited = false ) {
        $this->aSQL = array();
        $offset     = $this->items_pp * ($page - 1);
        
        // строим внутренний запрос
        $this->_setUsersLimit( $offset, $unlimited );
        $this->_setUsersWhere();
        $this->_setUsersFrom( false );
        
        // выбираем историю админских действий
        $sQuery = 'SELECT u.uid, u.uname, u.usurname, u.login, u.role, u.is_pro, u.is_pro_test, u.is_team, u.photo, u.warn, 
            u.email, u.reg_ip, u.last_ip, u.is_banned, u.ban_where, u.self_deleted, u.safety_only_phone, 
            u.safety_bind_ip, u.active, u.pop, u.phone, u.phone_1, u.phone_2, u.phone_3, a.is_block as is_block_money, u.is_verify, 
            sr.is_activate_mob, sr._1_mob_phone as safety_phone, sr.is_safety_mob ' 
            . ' FROM '. $this->aSQL['from'] 
            . ' LEFT JOIN account a ON a.uid = u.uid '
            . ' LEFT JOIN sbr_reqv sr ON sr.user_id = u.uid'    
            . ( ($this->aSQL['where_out'] && $this->aSQL['where']) ? ' WHERE ' . implode(' AND ', $this->aSQL['where']) : '' ) 
            . ' ORDER BY u.uid ' 
            . ( (!$this->isFilter('uid') && ($this->isFilter('search_name') || $this->isFilter('ip_from') || $this->isFilter('ip_to'))) ? '' : $this->aSQL['limit']);
//echo $GLOBALS['DB']->parse( $sQuery );//die;
        $users = $GLOBALS['DB']->rows( $sQuery );
        
        if ( $GLOBALS['DB']->error || !$users ) {
            return array();
        }
        
        // получаем общее количество админских действий
        $sAlias = $this->_setUsersFrom( true );
        
        $sQuery = 'SELECT COUNT('. $sAlias .'.uid) FROM '. $this->aSQL['from']
            . ( ($this->aSQL['where_out'] && $this->aSQL['where']) ? ' WHERE ' . implode(' AND ', $this->aSQL['where']) : '' );
            
        $count = $GLOBALS['DB']->val( $sQuery );
//echo $GLOBALS['DB']->parse( $sQuery );//die;
        
        return $users;
    }
    
    /**
     * Собирает FROM часть SQL запроса
     * 
     * @param  bool $bCount true если запрос строится для получения количества
     * @return string алиас таблицы для получения количества
     */
    function _setUsersFrom( $bCount = false ) {
        $sTable = $this->isFilter('who') ? ( $this->filter['who'] == 'emp' ? 'employer' : 'freelancer' ) : 'users';
        
        if ( !$this->isFilter('uid') && ($this->isFilter('search_name') || $this->isFilter('ip_from') || $this->isFilter('ip_to')) ) {
            $sIpInnerSql    = $this->_getIpInnerSql( $sTable, $bCount );
            $sUsersInnerSql = $this->_getUsersInnerSql( $sTable, $bCount );
            
            if ( $sIpInnerSql && $sUsersInnerSql ) {
            	$sAlias = 'z';
            	$sFrom  = ' ( SELECT x.uid FROM '. $sIpInnerSql . ' INNER JOIN  ' . $sUsersInnerSql . ' ON x.uid = y.uid ' 
                	. ( $bCount ? '' : ' ORDER BY x.uid ' . $this->aSQL['limit'] ) 
                	.' ) AS z ';
            }
            else {
                if ( $sIpInnerSql ) {
                    $sAlias = 'x';
                    $sFrom  = $sIpInnerSql;
                }
                else {
                    $sAlias = 'y';
                    $sFrom  = $sUsersInnerSql;
                }
                
                if ( !$bCount ) {
                	$sFrom  = ' ( SELECT '. $sAlias .'.uid FROM '. $sFrom . ' ORDER BY '. $sAlias .'.uid ' . $this->aSQL['limit'] .' ) AS z ';
                	$sAlias = 'z';
                }
            }
            
            $this->aSQL['from'] = $sFrom . ( $bCount ? '' : ' INNER JOIN users u ON u.uid = '. $sAlias .'.uid ');
            $this->aSQL['where_out'] = false;
        }
        else {
            $sAlias = 'u';
            $this->aSQL['from'] = ' ' . $sTable . ' u ';
            $this->aSQL['where_out'] = true;
        }
        
        return $sAlias;
    }
    
    /**
     * Строит подзапрос по пользователям
     * 
     * @param  string $sTable название таблицы
     * @param  bool $bCount если true - то строится запрос получения количества, а не данных
     * @return string внутренний запрос
     */
    function _getUsersInnerSql( $sTable = '', $bCount = false ) {
        $sSql = '';
        
        if ( $this->isFilter('search_name') ) {
            $aSearchIp = $this->getIpRange( $error, $this->filter['search_name'], $this->filter['search_name'] );
            $sSearch   = pg_escape_string( $this->filter['search_name'] );
            $sCardSQL  = '';
            
            // поиск по контактной информации всегда
            $aThree  = array( 'email', 'icq', 'skype', 'jabber', 'lj', 'site' );
            $aZero   = array( 'lj' );
            $aFields = array( 'uname', 'usurname', 'login', 'second_email', 'ljuser' );
            
            if ( !$this->isFilter('search_phone') ) {
            	$aThree[]  = 'phone';
            	$aFields[] = 'safety_phone';
            }
            
            if ( preg_match('#^[\d]+$#', $sSearch) ) {
            	$sCardSQL = $GLOBALS['DB']->parse( 'SELECT a.uid FROM account_operations AS ao 
                    INNER JOIN account a ON a.id = ao.billing_id 
                    WHERE ao.descr ILIKE ? OR ao.descr ILIKE ? OR ao.descr ILIKE ?', 
            	   "%в ассисте {$sSearch} %", "%с карты %{$sSearch}% %", "%номер покупки - {$sSearch}%"
            	);
            }
                        
        	$sSql = '( SELECT u.uid FROM '. $sTable . ' u WHERE ' . ( $this->aSQL['where'] ? implode(' AND ', $this->aSQL['where']) : '' ) 
        	   . ( $this->aSQL['where'] ? ' AND ' : '' ) . $this->_getWhereOrFields( $aThree, $aZero, $aFields, $sSearch, $this->isFilter('search_name_exact') ) 
        	   . ( !$error ? ' AND (' . $this->_getIpWhere( $aSearchIp['ip_from'], $aSearchIp['ip_to'] ) . ') ' : '' )
        	   . ' UNION ' . ( ($sTable != 'users' || $this->aSQL['where']) ? 'SELECT k.uid FROM ( '  : '' )
        	   . 'SELECT l.user_id AS uid FROM login_change l ' 
        	   . ' WHERE ' . ( $this->isFilter('search_name_exact') ? " LOWER(l.old_login) = LOWER('$sSearch') " : " l.old_login ILIKE '%$sSearch%' " )
        	   . ' UNION SELECT l.uid FROM users_change_emails_log l '
        	   . ' WHERE ' . ( $this->isFilter('search_name_exact') ? " LOWER(l.email) = LOWER('$sSearch') " : " l.email ILIKE '%$sSearch%' " )
        	   . ( !$error ? ' UNION ' . $this->_getIpQuery( '', $aSearchIp['ip_from'], $aSearchIp['ip_to'] ) : '' ) 
        	   . ( $sCardSQL ? ' UNION ' . $sCardSQL : '' ) 
        	   . ( ($sTable != 'users' || $this->aSQL['where']) ? ') AS k INNER JOIN '. $sTable . ' u ON u.uid = k.uid '. ( ($this->aSQL['where']) ? ' WHERE ' . implode(' AND ', $this->aSQL['where']) : '' ) : '' ) 
        	   . ') AS y';
        }
        
        return $sSql;
    }
    
    /**
     * Строит подзапрос по IP адресам
     * 
     * @param  string $sTable название таблицы
     * @param  bool $bCount если true - то строится запрос получения количества, а не данных
     * @return string внутренний запрос
     */
    function _getIpInnerSql( $sTable = '', $bCount = false ) {
        $sSql = '';
        
        if ( $this->isFilter('ip_from') || $this->isFilter('ip_to') ) {
            $bTable = ( !$this->isFilter('search_name') && ($sTable != 'users' || $this->aSQL['where']) );
            $sSql   = '( SELECT uid FROM '. $sTable . ' u WHERE ' 
                . ( (!$this->isFilter('search_name') && $this->aSQL['where']) ? implode(' AND ', $this->aSQL['where']) . ' AND (' : '' ) 
                . $this->_getIpWhere( $this->filter['ip_from'], $this->filter['ip_to'] ) . ( (!$this->isFilter('search_name') && $this->aSQL['where']) ? ')' : '' )
                . ' UNION ' . $this->_getIpQuery( ($bTable ? $sTable : ''), $this->filter['ip_from'], $this->filter['ip_to'] ) 
                . ') AS x';
        }
        
        return $sSql;
    }
    
    /**
     * Собирает WHERE часть SQL запроса
     */
    function _setUsersWhere() {
        $this->aSQL['where'] = array();
        
        // фильтр по UID пользователя
        if ( $this->isFilter('uid') ) {
            $this->aSQL['where'][] = $GLOBALS['DB']->parse( 'u.uid = ?i', $this->filter['uid'] );
        }
        
        // #0015793: Поиск по телефону
        if ( $this->isFilter('search_phone') ) {
            $sSearch = pg_escape_string( $this->filter['search_phone'] );
            $this->aSQL['where'][] = $this->_getWhereOrFields( 
                array('u.phone'), 
                array(), 
                array('u.safety_phone'), 
                $sSearch, 
                $this->isFilter('search_phone_exact') 
            );
        }
        
        // фильтр по статусу пользователя
        if ( $this->isFilter('status') ) {
            if ( $this->filter['status'] == '1' ) { $this->aSQL['where'][] = "u.is_banned = B'1' AND NOT u.self_deleted = TRUE"; }
            if ( $this->filter['status'] == '2' ) { $this->aSQL['where'][] = 'u.active = false'; }
            if ( $this->filter['status'] == '3' ) { $this->aSQL['where'][] = 'u.warn > 0'; }
            if ( $this->filter['status'] == '4' ) { $this->aSQL['where'][] = 'u.self_deleted = TRUE'; }
            if ( $this->filter['status'] == '5' ) { $this->aSQL['where'][] = "u.is_banned = B'0'"; }
        }
    }
    
    /**
     * Собирает WHERE часть SQL запроса для поиска по ключевому слову
     * 
     * @param  array $aThree массив имен полей типа jabber - будет автоматом добавлено jabber jabber_1 jabber_2 jabber_3
     * @param  array $aZero массив имен полей типа lj - будет автоматом добавлено lj_1 lj_2 lj_3, но не lj которого нет в базе
     * @param  array $aFields массив остальных полей
     * @param  string $sSearch ключевое слово
     * @param  bool $bExact установить в true если искать точное совпадение
     * @return string
     */
    function _getWhereOrFields( $aThree = array(), $aZero = array(), $aFields = array(), $sSearch = '', $bExact = false ) {
        foreach ( $aThree as $sFld ) {
        	for ( $i=1; $i<=3; $i++ ) {
        	    $aFields[] = $sFld . '_' . $i;
        	}
        }
        
        $aFields = array_merge( $aFields, $aThree );
        $aFields = array_diff( $aFields, $aZero );
        
        if ( $bExact ) {
            return '(LOWER('. implode(") = LOWER('$sSearch') OR LOWER(", $aFields) .") = LOWER('$sSearch'))";
        }
        else {
            return '('. implode(" ILIKE '%{$sSearch}%' OR ", $aFields) ." ILIKE '%{$sSearch}%')";
        }
    }
    
    /**
     * Собирает WHERE часть SQL запроса для поиска по диапазону IP
     * 
     * @param  string $fromIp начальный IP адрес
     * @param  string $toIp конечный IP адрес
     * @return string
     */
    function _getIpWhere( $fromIp = '', $toIp = '' ) {
        $sReturn  = '';
        $nLongIpF = $fromIp ? ip2long($fromIp) : 0; // некий вид валидации
        $nLongIpT = $toIp   ? ip2long($toIp)   : 0;
        
        if ( $nLongIpF || $nLongIpT ) {
            $sReturn = '('
                . ($nLongIpF ? $GLOBALS['DB']->parse('u.reg_ip >= ?', $fromIp) : '') 
                . ($nLongIpT ? ($nLongIpF ? ' AND ' : '') . $GLOBALS['DB']->parse('u.reg_ip <= ?', $toIp) : '') 
                . ') OR ( '. ($nLongIpF ? $GLOBALS['DB']->parse('u.last_ip >= ?', $fromIp) : '') 
                . ($nLongIpT ? ($nLongIpF ? ' AND ' : '') . $GLOBALS['DB']->parse('u.last_ip <= ?', $toIp) : '') 
                . ' )';
        }
        
        return $sReturn;
    }
    
    /**
     * Собирает подзапрос SQL запроса для поиска по диапазону IP
     * 
     * @param  string $sTable название таблицы
     * @param  string $fromIp начальный IP адрес
     * @param  string $toIp конечный IP адрес
     * @return string
     */
    function _getIpQuery( $sTable, $fromIp = '', $toIp = '' ) {
        $sReturn  = '';
        $nLongIpF = $fromIp ? ip2long($fromIp) : 0;
        $nLongIpT = $toIp   ? ip2long($toIp)   : 0;
        
        if ( $nLongIpF || $nLongIpT ) {
            $sReturn = 'SELECT l.uid FROM users_loginip_log l '
                . ( $sTable ? ' INNER JOIN '. $sTable .' u ON u.uid = l.uid ' : '' ) . ' WHERE ' 
                . ( ($sTable && $this->aSQL['where']) ? implode(' AND ', $this->aSQL['where']) . ' AND ' : '' ) 
                . ($nLongIpF ? $GLOBALS['DB']->parse('l.ip >= ?i', $nLongIpF) : '') 
                . ($nLongIpT ? ($nLongIpF ? ' AND ' : '') . $GLOBALS['DB']->parse('l.ip <= ?i', $nLongIpT) : '');
        }
        
        return $sReturn;
    }
    
    /**
     * Собирает LIMIT часть SQL запроса
     *
     * @param int $offset начальный индекс
     * @param  bool $unlimited опционально. установить в true если нужно получить все записи (без постраничного вывода)
     */
    function _setUsersLimit( $offset = 0, $unlimited = false ) {
        $this->aSQL['limit'] = $unlimited ? '' : ' LIMIT ' . $this->items_pp . ' OFFSET ' . $offset;
    }
    

    /**
     * Поиск по всем пользователям через Sphinx
     * 
     * @param type $count
     * @param boolean $filter
     * @param type $page
     * @return type
     */
    public function searchUsersBySphinx(&$count, $filter, $page = 1)
    {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/search/search_ext.php");
        
        $string_query = trim(@$filter['search_name']);
        $search_name_exact = $filter['search_name_exact'] == 1;
        unset($filter['search_name'], $filter['search_name_exact']);
        $type = 'users_all';
        
        //Любое из слов
        if (!$search_name_exact) {
            
            $aKeyword = array();
            $aRequestString = preg_split('/[\s,]+/', $string_query, 5);
            if ($aRequestString) 
            {
                foreach ($aRequestString as $sValue) 
                {
                    if (strlen($sValue) >= 3) {
                        $aKeyword[] .= '("' . $sValue . '" | "*' . $sValue . '*")';
                    }
                }

                if(!empty($aKeyword)) 
                {
                    $string_query = implode(" | ", $aKeyword);
                }
            }
            
        } 
        
        //Убираем пустые параметры фильра
        $filter = array_filter($filter);

        //Поиск Sphinx
        $search = new searchExt(get_uid(false));
        $search->setUserLimit($this->items_pp);
        $searchElement = $search->addElement($type, true, $this->items_pp);
        $searchElement->setMode($search_name_exact?SPH_MATCH_ALL:SPH_MATCH_ANY);
        $search->searchByType($type, $string_query, $page, $filter);
        $elements = $search->getElements();
        $element = $elements[$type]; 

        $count = $element->total;
        return $element->results;
    }
    
}