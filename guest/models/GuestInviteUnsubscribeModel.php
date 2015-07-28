<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yii/CModel.php');


class GuestInviteUnsubscribeModel extends CModel
{
    const SOLT = 'OaVZ27wJEu';
    
    const BASE_URI = '/guest/unsubscribe/?email=%s&hash=%s';
    
    protected $TABLE = 'invitation_unsubscribe';
    
    protected $data = array();

    
    public function isUnsubscribed($email)
    {
        return (bool)($this->db()->val("SELECT date FROM {$this->TABLE} WHERE email = ?", $email));
    }
    
    public static function getUri($email)
    {
        $hash = self::getHash($email);
        
        return sprintf(self::BASE_URI, $email, $hash);
    }
    
    public static function getHash($email)
    {
        return substr(md5($email . self::SOLT), 0, 12);
    }
    
    public function addEmail($email)
    {
        if (!$this->isUnsubscribed($email)) {
            $this->db()->insert($this->TABLE, array('email' => $email));
        }
    }
    
}