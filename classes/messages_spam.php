<?php
/**
 * Подключаем предка
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/admin_parent.php");

/**
 * Класс для пометки о спаме в личных сообщениях
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
class messages_spam extends admin_parent {    
    /**
     * Текстовая константа
     */
    const COMPLAINT_PENDING_TXT = 'Ваша жалоба на рассмотрении';
    
    public $resolve = array(
        0 => 'Еще не решили', 
        1 => 'Это не спам',
        2 => 'Предупреждение',
        3 => 'Заблокирован',
    );
    
    /**
     * Конструктор класса
     * 
     * @param int $items_pp Количество пользователей на странице
     */
    function __construct( $items_pp = 0 ) {
        parent::__construct($items_pp);
    }
    
    /**
     * Сохраняет жалобу на спам в личных сообщениях
     * 
     * @param  string $sSpamerId UID спамера
     * @param  string $sUserId UID пожаловавшегося пользователя
     * @param  string $aParams массив параметров жалобы на спам
     * @return bool true - успех, false - провал
     */
    function addSpamComplaint( $sSpamerId = '', $sUserId = '', $aParams = array() ) {
        $DB      = new DB('plproxy'); // plproxy
        $bRet    = false;
        $sMsgMd5 = md5( $aParams['msg'] );
        
        if ( $sSpamerId && $sUserId && $aParams ) {
            $DB->val( "SELECT messages_spam_add(?i, ?i, ?i, ?, ?)", 
                $sSpamerId, $sUserId, $aParams['id'], $sMsgMd5, change_q($aParams['txt']) 
            );
            
            if ( !$DB->error ) {
                $bRet    = true;
                $oMemBuf = new memBuff();
                $oMemBuf->delete( 'messages_spam_count' );
            }
        }
        
        return $bRet;
    }
    
    /**
     * Помечает все жалобы на спамера удаленными (решение админа)
     * 
     * @param  array $aSpamerId массив UID спамеров или строка с одним
     * @param  int $nResolve Решение админа: 0 - еще не решил, 1 - это не спам, 2 - предупреждение, 3 - бан
     * @return bool true - успех, false - провал
     */
    function deleteSpamBySpamer( $aSpamerId = array(), $nResolve = 0 ) {
        $bRet = false;
        
        if ( $aSpamerId ) {
            if ( !is_array($aSpamerId) ) {
            	$aSpamerId = array( $aSpamerId );
            }
            
            $DB   = new DB('plproxy');
            $DB->query( "SELECT messages_spam_del_spamer(?a, ?i)", $aSpamerId, $nResolve );
        	
            if ( !$DB->error ) {
                $bRet    = true;
                $oMemBuf = new memBuff();
                $oMemBuf->delete( 'messages_spam_count' );
            }
        }
        
        return $bRet;
    }
    
    /**
     * Помечает все жалобы на сообщение удаленными (решение админа)
     * 
     * @param  int $nSpamerId UID спамера
     * @param  string $sMsgMd5 MD5 хэш текста сообщения
     * @param  int $nResolve Решение админа: 0 - еще не решил, 1 - это не спам, 2 - предупреждение, 3 - бан
     * @return bool true - успех, false - провал
     */
    function deleteSpamByMsg( $nSpamerId = 0, $sMsgMd5 = '', $nResolve = 0 ) {
        $bRet = false;
        
        if ( $nSpamerId && $sMsgMd5 ) {
            $DB   = new DB('plproxy');
            $DB->query( "SELECT messages_spam_del_msg(?i, ?, ?i)", $nSpamerId, $sMsgMd5, $nResolve );
            
        	if ( !$DB->error ) {
                $bRet    = true;
                $oMemBuf = new memBuff();
                
                if ( ($nCount = $oMemBuf->get('messages_spam_count')) !== false ) {
                    $nCount = $nCount - 1;
                    if ($nCount < 0) {
                        $nCount = 0;
                    }
                    $oMemBuf->set( 'messages_spam_count', $nCount, 3600 );
                }
                else {
                    $oMemBuf->delete( 'messages_spam_count' );
                    $this->getSpamCount();
                }
            }
        }
        
        return $bRet;
    }
    
    /**
     * Возвращает список жалоб от определенного пользователя.
     * 
     * @param  string $sUid UID пожаловавшегося пользователя
     * @return array
     */
    function getComplaintsByUser( $sUid = '' ) {
        $DB = new DB; // plproxy
        return $DB->rows( 'SELECT * FROM messages_spam_get_user(?i)', $sUid );
    }
    
    /**
     * Возвращает список жалоб на спам для личного сообщения.
     * 
     * @param  int $nSpamerId UID спамера
     * @param  string $sMsgMd5 MD5 хэш текста сообщения
     * @return array
     */
    function getSpamComplaints( $nSpamerId = 0, $sMsgMd5 = '' ) {
        $DB = new DB; // plproxy
        return $DB->rows( 'SELECT * FROM messages_spam_get_msg(?i, ?)', $nSpamerId, $sMsgMd5 );
    }
    
    /**
     * Возвращает список жалоб о спаме, удовлетворяющих условиям выборки
     * 
     * Оберточная функция
     *
     * @param  int $count возвращает количество записей удовлтворяющих условиям выборки
     * @param  array $filter фильтр
     * @param  int $page номер текущей страницы
     * @return array
     */
    function getSpam( &$count, $filter, $page = 1 ) {
        $this->filter = $filter;
        return $this->_getSpam( $count, $page );
    }
    
    /**
     * Возвращает количество жалоб о спаме, удовлетворяющих условиям выборки
     * 
     * @param  array $filter фильтр
     * @return int
     */
    function getSpamCount( $filter = array() ) {
        $DB      = new DB; // plproxy
        $aFilter = array();
        $oMemBuf = new memBuff();
        $nCount  = 0;
        
        if ( is_array($filter) && count($filter) ) {
        	foreach ( $filter as $sKey => $sVal ) {
        		$aFilter[] = array( $sKey, $sVal );
        	}
        }
        
        if ( empty($aFilter) && ($nCount = $oMemBuf->get('messages_spam_count')) !== false ) {
        	return $nCount;
        }
        else {
            $sQuery = 'SELECT messages_spam_get_count(?a)';
            $nCount = $DB->val( $sQuery, $aFilter );
            
            if ( empty($aFilter) && !$DB->error ) {
            	$oMemBuf->set( 'messages_spam_count', $nCount, 3600 );
            }
        }
                
        return $nCount;
    }
    
    /**
     * Возвращает список жалоб о спаме, удовлетворяющих условиям выборки
     * 
     * Внутренняя функция
     * 
     * @param  int $count возвращает количество записей удовлтворяющих условиям выборки
     * @param  int $page номер текущей страницы
     * @param  string order тип сортировки
     * @param  int $direction порядок сортировки: 0 - по убыванию, не 0 - по возрастанию
     * @param  bool $unlimited опционально. установить в true если нужно получить все записи (без постраничного вывода)
     * @return array
     */
    function _getSpam( &$count, $page = 1, $order = 'general', $direction = 0, $unlimited = false ) {
        $DB         = new DB; // plproxy
        $aFilter    = array();
        $this->aSQL = array();
        $offset     = $this->items_pp * ($page - 1);
        
        // строим запрос
        $this->_setSpamOrderBy( $order, $direction );
        
        if ( is_array($this->filter) && count($this->filter) ) {
        	foreach ( $this->filter as $sKey => $sVal ) {
        		$aFilter[] = array( $sKey, $sVal );
        	}
        }
        
        $sQuery = 'SELECT * FROM messages_spam_get_list(?a) ' 
            . ' ORDER BY ' . implode( ', ', $this->aSQL['order_by'] ) 
            . ( !$unlimited ? ' LIMIT ' . $this->items_pp . ' OFFSET ' . $offset : '' );
        
        $aSpam  = $DB->rows( $sQuery, $aFilter );
        
        if ( $DB->error || !$aSpam ) {
            return array();
        }
        
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
        
        // прикрепленные файлы 
        messages::getMessagesAttaches( $aSpam, 'msg_id' );
        
        $sQuery = 'SELECT messages_spam_get_count(?a)';
        
        $count = $DB->val( $sQuery, $aFilter );
        
        return $aSpam;
    }
    
    /**
     * Собирает ORDER BY часть SQL запроса
     *
     * @param string $order тип сортировки
     * @param int $direction порядок сортировки: 0 - по убыванию, не 0 - по возрастанию
     */
    function _setSpamOrderBy( $order = 'general', $direction = 0 ) {
        $dirSql = ( !$direction ? 'DESC' : 'ASC' );
        
        switch ( $order ) {
            case 'general':
            case 'time':
            default:
                $this->aSQL['order_by'][] = "complaint_time $dirSql";
                break;
        }
    }
    
    ////////////////////////////////////////////////////////
    //                                                    //
    //                  UTILITY FUNCTIONS                 //
    //                                                    //
    ////////////////////////////////////////////////////////
    
    /**
     * Возвращает даты (период)
     * 
     * @param  string $error возвращет сообщение об ошибке или пустую строку
     * @param  string $prefix префикс к полям фильтра
     * @param  string $fromD день 
     * @param  string $fromM месяц 
     * @param  string $fromY год 
     * @param  string $toD день 
     * @param  string $toM месяц 
     * @param  string $toY год 
     * @return array
     */
    function getDatePeriod( &$error, $prefix = '', $fromD = '', $fromM = '', $fromY = '', $toD = '', $toM = '', $toY = '' ) {
        $aRet  = array();
        $error = '';
        
        if ( $fromD == '' && $fromM == '' && $fromY == '' ) {
        	$fromDate = null;
        }
        else {
            if ( $fromD == '' || $fromM == '' || $fromY == '' ) {
            	$error = 'Укажите начальную дату';
            }
            else {
                $fromDate = $fromY.'-'.$fromM.'-'.(strlen($fromD) > 1 ? $fromD : '0'.$fromD);
                
                if ( ($fromRes = strtotime($fromDate)) === false ) {
                    $error = 'Укажите корректную начальную дату';
                }
            }
        }
        
        if ( !$error ) {
            if ( $toD == '' && $toM == '' && $toY == '' ) {
            	$toDate = null;
            }
            else {
                if ( $toD == '' || $toM == '' || $toY == '' ) {
                	$error = 'Укажите конечную дату';
                }
                else {
                    $toDate = $toY.'-'.$toM.'-'.(strlen($toD) > 1 ? $toD : '0'.$toD);
                    
                    if ( ($toRes = strtotime($toDate)) === false ) {
                        $error = 'Укажите корректную конечную дату';
                    }
                }
            }
            
            if ( !$error && $fromDate && $toDate ) {
                if ( $toRes < $fromRes ) {
                	$error = 'Конечная дата не может быть меньше начальной';
                }
            }
        }
        
        if ( !$error ) {
            $sCurrDate = date('Y-m-d');
            $aRet = array( $prefix.'_date_from' => $fromDate, $prefix.'_date_to' => ($toDate < $sCurrDate ? $toDate : null) );
        }
        
        return $aRet;
    }
}