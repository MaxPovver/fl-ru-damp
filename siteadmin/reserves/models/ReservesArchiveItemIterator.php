<?php

require_once('ReservesArchiveItemModel.php');

class ReservesArchiveItemIterator extends ArrayIterator
{
    public function current() 
    {
        $value = parent::current();
        return new ReservesArchiveItemModel($value);
    }
}