<?php


abstract class CItem 
{
    protected $_data;
    protected $user;


    public function __construct($_data, $user = array()) 
    {
        $this->_data = $_data;
        $this->user = $user;
    }
    
    
    public function __call($name, $arguments) 
    {
        return false;
    }
    
    public function __get($name) 
    {
        return isset($this->_data[$name])? $this->_data[$name] : null;
    }
    
    public function setUser($user)
    {
        $this->user = $user;
    }
    
    public function isOwner()
    {
        return isset($_SESSION['uid']) && 
               $_SESSION['uid'] > 0 && 
               $this->user_id && 
               $_SESSION['uid'] == $this->user_id;
    }
}