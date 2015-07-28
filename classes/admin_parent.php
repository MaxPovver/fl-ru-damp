<?php

/**
 * Родительский класс для новой модераторской.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
class admin_parent {
    /**
     * Количество элементов на странице в админке
     */
    public $items_pp = 20;
    
    /**
     * Параметры фильтра
     *
     * @var array
     */
    protected $filter = array();
    
    /**
     * Составные части SQL запроса 
     * 
     * @var array
     */
    protected $aSQL = array();
    
    /**
     * UID текущего пользователя
     *
     * @var int
     */
    protected $curr_uid = 0;
    
    /**
     * Права пользователя
     * 
     * @var array
     */
    protected $user_permissions = array();
    
    /**
     * Конструктор класса
     * 
     * @param int $items_pp Количество элементов на странице
     */
    function __construct( $items_pp ) {
        $items_pp = intval( $items_pp );
        
        if ( $items_pp > 0 ) {
        	$this->items_pp = $items_pp;
        }
    }
    
    /**
     * Проверяет установлен ли определенный фильтр
     * 
     * @param  string $key ключ в массиве фильтра
     * @return bool true - установлен, false - нет
     */
    function isFilter( $key ) {
        return ( isset($this->filter[$key]) && $this->filter[$key] );
    }
    
    /**
     * Возвращает диапазон IP адресов
     * 
     * Части IP адресов можно заменять * тогда в начальном IP подставляются 0, в конечном - 255
     * Начинаться каждый IP должен с числа
     * 
     * звездочки работают по следующему принципу (в начальном IP):
     * 255.255.255.255 = 255.255.255.255
     * 255.*.255.255   = 255.0.255.255
     * 255.255.*.255   = 255.255.0.255
     * 255.255.255.*   = 255.255.255.0
     * 255.*.255.*     = 255.0.255.0
     * 255.*.*.255     = 255.0.0.255
     * 255.*.255       = 255.0.0.255
     * 255.255.*.*     = 255.255.0.0
     * 255.255.*       = 255.255.0.0
     * 255.*.*.*       = 255.0.0.0
     * 255.*.*         = 255.0.0.0
     * 255.*           = 255.0.0.0
     * 255             = 255.0.0.0
     * 
     * @param  string $error возвращет сообщение об ошибке или пустую строку
     * @param  string $fromIp начальный IP адрес
     * @param  string $toIp конечный IP адрес
     * @return array 
     */
    function getIpRange( &$error, $fromIp = '', $toIp = '' ) {
        $aRet  = array();
        $error = '';
        
        $fromIp = trim( $fromIp );
        if ( $error = $this->validIp($fromIp, 'fromIP') ) {
            return array();
        }
        
        $toIp = trim( $toIp );
        if ( $error = $this->validIp($toIp, 'toIP') ) {
            return array();
        }
        
        $sIpFrom = $this->getIp( $fromIp, '0' );
        $sIpTo   = $this->getIp( $toIp, '255' );
        
        return array( 'ip_from' => $sIpFrom, 'ip_to' => $sIpTo );
    }
    
    /**
     * Проверяет часть IP адреса
     * 
     * @param  string $ip IP адрес
     * @param  string $errMsg сообщение об ошибке
     * @return string сообщение об ошибке или пустая строка
     */
    function validIp( $ip, $errMsg ) {
        if ( trim($ip) ) {
            if ( !preg_match('/^([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\.(\*|[1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]|)){0,3}$/', $ip) ) {
                return $errMsg;
            }
        }
        return '';
    }
    
    /**
     * Формирует IP адрес по шаблону
     * 
     * @param  string $sIp шаблон IP адреса
     * @param  string $sReplace на что заменять шаблон (обычно 0 или 255)
     * @return string
     */
    function getIp( $sIp, $sReplace = '0' ) {
        $ip = '';
        
        if ( trim($sIp) ) {
            $ip = preg_replace('/(\.\*)+$/', '.*', $sIp);
            $cnt = substr_count( $ip, '.' );
            
            if ( !$cnt ) {
            	$ip .= '.*';
            	$cnt++;
            }
            
            $replace = str_repeat( '.'.$sReplace, 4 - $cnt );
            $ip      = str_replace( '.*', $replace, $ip );
        }
        
        return $ip;
    }
    
    
    /**
     * Имеет ли право
     * 
     * @param  string $permission иголка
     * @param  array $user_permissions стог сена
     * @return bool
     */
    function isAllowed( $permission,  $user_permissions ) {
        return ( in_array($permission, $user_permissions) || in_array('all', $user_permissions) );
    }
    
}