<?php

require_once(ABS_PATH . '/classes/tservices/tservices_helper.php');
require_once('TServiceItem.php');

class TServiceItemIterator extends ArrayIterator
{    
    protected $user;

    public function __construct($user, $array = 'array()', $flags = 0) 
    {
        $this->user = $user;
        parent::__construct($array, $flags);
    }
    
    public function current() 
    {
        $value = parent::current();
        return new TServiceItem($value, $this->user);
    }
}