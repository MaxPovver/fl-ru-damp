<?php
/**
 * Подключаем файл с основными функциями
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

/**
 * Класс для контроля отправления уведомлений который отправляются только один раз
 *  
 */
class single_send
{
    // Уведомление о веб-кошельке @see pmail::SbrMoneyPaidFrl()
    const NOTICE_WEBM   = 0x0001;
    
    protected $_bit = 0;
    protected $_user;
    
    function __construct($user = false) {
        $this->setUser($user);
        $this->setBit($this->_user->single_send);
    }
    
    function setUser($user) {
        $this->_user = $user ? $user : new users();
    }
    
    function setBit($bit) {
        $this->_bit += $bit;
    }
    
    function getBit() {
        return $this->_bit;
    }   
    
    function is_send ($type) {
        return ( $this->getBit() & $type );
    }
    
    function setUpdateBit($type) {
        $this->setBit($type);
        $this->_user->single_send = $this->getBit();
        $this->_user->update($this->_user->uid, $error);
    }
}

?>