<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yii/CModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/memBuff2.php');
require_once('GuestConst.php');



class GuestMemoryModel extends CModel
{
    const SOLT = 'Lod3L3VjRb';

    protected $expire;
    protected $memBuff = null;

    public function __construct()
    {
        $this->expire = 7 * 24 * 60 * 60;
        $this->memBuff = new memBuff();
    }


    public function saveData($data)
    {
        $key = substr(md5(self::SOLT . implode(',', $data)), 0, 12);
        $this->memBuff->add($key, $data, $this->expire);
        return $key; 
    }
    
    public function getData($key) {
        return $this->memBuff->get($key);
    }
}