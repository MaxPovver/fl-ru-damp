<?php
/**
 * Подключаем предка
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/admin_parent.php");

/**
 * Класс для работы с серым списком IP
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
class gray_ip extends admin_parent {
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
     * @param int $items_pp Количество элементов на странице
     */
    function __construct( $items_pp ) {
        parent::__construct( $items_pp );
    }
    
    /**
     * Возвращает список админов, которые добавляли IP в серый список
     * 
     * @param  int $objCode опционально. код объекта
     * @return array
     */
    function getAdmins() {
        return $GLOBALS['DB']->rows( 'SELECT u.uid, u.login FROM users u 
            INNER JOIN gray_ip_primary p ON p.admin_id = u.uid
            GROUP BY u.uid, u.login
            ORDER BY u.login' 
        );
    }
    
    /**
     * Возвращает адреса пользователя из серого списка IP
     * 
     * @param  int $nUid UID пользователя
     * @return array
     */
    function getPrimaryIpByUid( $nUid = 0 ) {
        return $GLOBALS['DB']->rows( 'SELECT admin_id, ip, user_login FROM gray_ip_primary WHERE user_id = ?i', $nUid );
    }
    
    /**
     * Добавляет адреса пользователя в серый список IP
     * 
     * @param  int $nUserId UID пользователя которого добавляем в серый список
     * @param  string $sUserLogin Логин пользователя которого добавляем в серый список
     * @param  int $nAdminId UID админа
     * @param  array $aIp массив IP адресов или строка с одним IP адресом
     * @return bool true - успех, false - провал
     */
    function addPrimaryIp( $nUserId = 0, $sUserLogin = '', $nAdminId = 0, $aIp = array() ) {
        $bRet  = true;
        $aData = array();
        
        if ( $nUserId && $nAdminId && $aIp ) {
        	if ( !is_array($aIp) ) {
        		$aIp = array( $aIp );
        	}
        	
        	foreach ( $aIp as $sIp ) {
        		$aData[] = array( 
                    'user_id'    => $nUserId, 
                    'user_login' => $sUserLogin, 
                    'admin_id'   => $nAdminId, 
                    'ip'         => $sIp 
        		);
        	}
        	
        	$GLOBALS['DB']->insert( 'gray_ip_primary', $aData );
        	$bRet = ( !$GLOBALS['DB']->error ) ? true : false;
        }
        
        return $bRet;
    }
    
    /**
     * Удаляет адреса пользователя из серого списка IP
     * 
     * @param  int $nUserId UID пользователя
     * @param  array $aIp массив IP адресов или строка с одним IP адресом
     * @return bool true - успех, false - провал
     */
    function deletePrimaryIp( $nUserId = 0, $aIp = array() ) {
        $bRet = true;
        
        if ( $nUserId && $aIp ) {
            if ( !is_array($aIp) ) {
        		$aIp = array( $aIp );
        	}
        	
        	$GLOBALS['DB']->query( 'DELETE FROM gray_ip_primary WHERE user_id = ?i AND ip IN (?l)', $nUserId, $aIp );
        	$bRet = ( !$GLOBALS['DB']->error ) ? true : false;
        }
        
        return $bRet;
    }
    
    /**
     * Обновляет адреса пользователя в сером списке IP
     * 
     * Сюда передается новый список адресов. Функция сравнивает новый список со старым.
     * Не нужные адреса из старого списка удаляются - больше не отслеживаются, новые добавляются.
     * Общие для старого и нового списков - не трогаются так как за ними уже могут числиться подозрительные регистрации.
     * 
     * @param  int $nUserId UID пользователя
     * @param  string $sUserLogin Логин пользователя
     * @param  int $nAdminId UID админа
     * @param  array $aIp массив IP адресов или строка с одним IP адресом
     * @param  bool $bDel возвращает было ли удаление адресов
     * @return bool true - успех, false - провал
     */
    function updatePrimaryIp( $nUserId = 0, $sUserLogin = '', $nAdminId = 0, $aIp = array(), &$bDel ) {
        $bRet = true;
        $bDel = false;
        
        if ( $nUserId && $nAdminId ) {
            if ( !is_array($aIp) ) {
        		$aIp = array( $aIp );
        	}
        	
        	$aOldIp = $GLOBALS['DB']->col( 'SELECT ip FROM gray_ip_primary WHERE user_id = ?i', $nUserId );
        	
        	if ( !$GLOBALS['DB']->error ) {
            	if ( $aAdd = array_diff($aIp, $aOldIp) ) {
            	    $bRet = self::addPrimaryIp( $nUserId, $sUserLogin, $nAdminId, $aAdd );
            	}
            	
            	if ( $bRet && $aDel = array_diff($aOldIp, $aIp) ) {
            	    $bDel = true;
            	    $bRet = self::deletePrimaryIp( $nUserId, $aDel );
            	}
        	}
        }
        
        return $bRet;
    }
    
    /**
     * Убрать все IP пользователя из серого списка и убрать все его пдозрительные регистрации
     * 
     * @param  int $nUserId UID пользователя
     * @return bool true - успех, false - провал
     */
    function deletePrimaryUser( $nUserId = 0 ) {
        return self::updatePrimaryIp( $nUserId, '', -1, array() );
    }
    
    /**
     * Возвращает список ID записей из серого списка которые совпадают с IP регистрации пользователя
     * 
     * @param  string $sRegIp IP адрес при регистрации пользователя
     * @return array
     */
    function getGrayListByRegIp( $sRegIp ) {
        $aRet = $GLOBALS['DB']->col( 'SELECT id FROM gray_ip_primary WHERE ip = ?', $sRegIp );
        
        return $aRet;
    }
    
    /**
     * Добавить пользователя зарегистрированного с IP из серого списка
     * 
     * @param  int $nUserId UID пользователя
     * @param  string $sUserLogin Логин пользователя
     * @param  int $sUserRole Роль пользователя: 1 - работодатель, 0 - фрилансер
     * @param  array $aPrimaryId массив ID записей из серого списка @see self::getGrayListByRegIp
     * @return bool true - успех, false - провал
     */
    function addSecondaryIp( $nUserId = 0, $sUserLogin = '', $sUserRole = '', $aPrimaryId = array() ) {
        $bRet  = true;
        $sDate = date('Y-m-d H:i:s');
        
        if ( $nUserId && $aPrimaryId ) {
            if ( !is_array($aPrimaryId) ) {
        		$aPrimaryId = array( $aPrimaryId );
        	}
        	
        	foreach ( $aPrimaryId as $sId ) {
        		$aData[] = array( 
                    'user_id'    => $nUserId, 
                    'user_login' => $sUserLogin, 
                    'is_emp'     => $sUserRole, 
                    'primary_id' => $sId, 
                    'reg_date'   => $sDate 
        		);
        	}
        	
        	$GLOBALS['DB']->insert( 'gray_ip_secondary', $aData );
        	$bRet = ( !$GLOBALS['DB']->error ) ? true : false;
        }
        
        return $bRet;
    }
    
    /**
     * Удалить определенные подозрительые регистрации
     * 
     * @param  array $aSecondaryId массив UID пользователей из gray_ip_secondary или строка с одним UID
     * @return bool true - успех, false - провал
     */
    function deleteSecondaryIp( $aSecondaryId = array() ) {
        $bRet = true;
        
        if ( $aSecondaryId ) {
        	if ( !is_array($aSecondaryId) ) {
        		$aSecondaryId = array( $aSecondaryId );
        	}
            
        	$GLOBALS['DB']->query( 'DELETE FROM gray_ip_secondary WHERE user_id IN (?l)', $aSecondaryId );
        	$bRet = ( !$GLOBALS['DB']->error ) ? true : false;
        }
        
        return $bRet;
    }
    
    /**
     * Удалить все подозрительые регистрации определенного первичного IP
     * 
     * @param  array $aSecondaryId массив UID пользователей из gray_ip_secondary
     * @return bool true - успех, false - провал
     */
    function deleteSecondaryIpByPrimary( $aSecondaryId = 0 ) {
        $GLOBALS['DB']->query( 'DELETE FROM gray_ip_secondary WHERE user_id IN (?l)', $aSecondaryId );
        return ( !$GLOBALS['DB']->error ) ? true : false;
    }
    
    /**
     * Возвращает серый список IP
     * 
     * @param  int $count возвращает количество записей удовлтворяющих условиям выборки
     * @param  array $filter Параметры фильтра лога
     * @param  int $page номер текущей страницы
     * @return array
     */
    function getGrayIpList( &$count, $filter, $page = 1 ) {
        $this->filter = $filter;
        
        return $this->getGrayIp( $count, $page );
    }
    
    /**
     * Возвращает серый список IP
     * 
     * @param  int $count возвращает количество записей удовлтворяющих условиям выборки
     * @param  int $page номер текущей страницы
     * @param  string order тип сортировки
     * @param  int $direction порядок сортировки: 0 - по убыванию, не 0 - по возрастанию
     * @param  bool $unlimited опционально. установить в true если нужно получить все записи (без постраничного вывода)
     * @return array
     */
    function getGrayIp( &$count, $page = 1, $order = 'general', $direction = 0, $unlimited = false ) {
        $this->aSQL = array();
        $offset     = $this->items_pp * ($page - 1);
        
        // строим запрос
        $this->_setWhere();
        $this->_setOrderBy( $order, $direction );
        
        // выбираем историю админских действий
        $sQuery = 'SELECT p.id AS p_id, p.user_id AS p_uid, p.ip, p.user_login AS p_login, 
            s.id AS s_id, s.user_id AS s_uid, date_trunc(\'day\', s.reg_date) AS reg_date, s.user_login AS s_login, s.is_emp 
            FROM ( SELECT gp.id, MAX(gs.reg_date) AS max_date FROM gray_ip_primary gp 
                INNER JOIN gray_ip_secondary gs ON gs.primary_id = gp.id '
            . ( $this->aSQL['where'] ? ' WHERE ' . implode(' AND ', $this->aSQL['where']) : '' ) 
            . ' GROUP BY gp.id ORDER BY max_date DESC '
            . ( $unlimited ? '' : ' LIMIT ' . $this->items_pp . ' OFFSET ' . $offset)
            .') AS p1 
            INNER JOIN gray_ip_primary as p ON p1.id = p.id
            INNER JOIN gray_ip_secondary s ON s.primary_id = p.id'
            . ' ORDER BY ' . implode(', ', $this->aSQL['order_by']);
//echo $GLOBALS['DB']->parse( $sQuery );
        $log = $GLOBALS['DB']->rows( $sQuery );
        
        if ( $GLOBALS['DB']->error || !$log ) {
            return array();
        }
        
        // получаем общее количество админских действий
        $sQuery = 'SELECT COUNT(gp.id) FROM gray_ip_primary gp '
            . ( $this->aSQL['where'] ? ' WHERE ' . implode(' AND ', $this->aSQL['where']) : '' ) ;
            
        $count = $GLOBALS['DB']->val( $sQuery );
//echo $GLOBALS['DB']->parse( $sQuery );
        
        return $log;
    }
    
    /**
     * Собирает WHERE часть SQL запроса серого списка IP
     */
    function _setWhere() {
        $this->aSQL['where'][] = 'secondary_cnt > 0';
        
        if ( self::isFilter('primary_id') ) {
            $this->aSQL['where'][] = $GLOBALS['DB']->parse( 'gp.id  = ?i', $this->filter['primary_id'] );
        }
        
        if ( self::isFilter('admin_id') ) {
            $this->aSQL['where'][] = $GLOBALS['DB']->parse( 'gp.admin_id = ?i', $this->filter['admin_id'] );
        }
        
        if ( self::isFilter('search_name') ) {
            $sSearch = pg_escape_string( $this->filter['search_name'] );
            $this->aSQL['where'][] = "(gp.user_login ILIKE '%{$sSearch}%' OR EXISTS (SELECT 1 FROM gray_ip_secondary gs WHERE gs.primary_id = gp.id AND gs.user_login ILIKE '%{$sSearch}%'))";
        }
        
        if ( $this->isFilter('ip_from') || $this->isFilter('ip_to') ) {
            $nLongIpF = $this->isFilter('ip_from') ? ip2long($this->filter['ip_from']) : 0;
            $nLongIpT = $this->isFilter('ip_to')   ? ip2long($this->filter['ip_to'])   : 0;
            
            $this->aSQL['where'][] = '('
                . ($nLongIpF ? $GLOBALS['DB']->parse('gp.ip >= ?', $this->filter['ip_from']) : '') 
                . ($nLongIpT ? ($nLongIpF ? ' AND ' : '') . $GLOBALS['DB']->parse('gp.ip <= ?', $this->filter['ip_to']) : '') 
                . ')';
        }
    }
    
    /**
     * Собирает ORDER BY часть SQL запроса серого списка IP
     *
     * @param string $order тип сортировки - не используется
     * @param int $direction порядок сортировки: 0 - по убыванию, не 0 - по возрастанию - не используется
     */
    function _setOrderBy( $order = "general", $direction = 0 ) {
        $dirSql = ( !$direction ? 'DESC' : 'ASC' );
        
        switch ( $order ) {
            default:
                $this->aSQL['order_by'][] = "p1.max_date DESC";
                $this->aSQL['order_by'][] = "p.ip ASC";
                $this->aSQL['order_by'][] = "p.id ASC";
                $this->aSQL['order_by'][] = "s.reg_date DESC NULLS LAST";
                $this->aSQL['order_by'][] = "s.user_id ASC";
                break;
        }
    }
}