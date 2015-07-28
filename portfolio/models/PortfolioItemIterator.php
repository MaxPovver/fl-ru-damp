<?php

require_once('PortfolioItem.php');

class PortfolioItemIterator extends ArrayIterator
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
        return new PortfolioItem($value, $this->user);
    }
}