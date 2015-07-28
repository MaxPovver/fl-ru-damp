<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yii/CModel.php');
require_once('GuestConst.php');



class GuestInviteModel extends CModel
{
    protected $TABLE = 'invitation';

    
    public function isExistLink($link)
    {
        return (bool)($this->db()->val("SELECT id FROM {$this->TABLE} WHERE link = ?", $link));
    }
    
    
    public function addInvite($type, $email, $link)
    {
        return $this->db()->insert($this->TABLE, array(
            'type' => $type,
            'email' => $email,
            'link' => $link
        ), 'id');
    }
    
    
    /**
     * Обновляем запись и фиксируем 
     * дату и время перехода по ссылке
     * 
     * @param type $id
     * @param type $data
     * @return type
     */
    public function updateDateComeInvite($id, $data = array())
    {
        $data = array_filter($data);
        return $this->db()->update(
                $this->TABLE, 
                array('date_come' => 'NOW()') + $data, 
                'id = ?i', $id);
    }
    
    /**
     * Обновляем дату публикации указанной сущности и ее типа 
     * 
     * @param type $src_id
     * @param type $type
     * @return type
     */
    public function updateDatePublicBySrc($src_id, $type)
    {
        $type = !is_array($type)?array($type):$type;
        return $this->db()->query("
            UPDATE {$this->TABLE} SET
                date_public = NOW()
            WHERE 
                src_id = ?i AND type IN(?l)
        ", $src_id, $type);
        
        
        return $this->db()->update($this->TABLE, array('date_public' => 'NOW()'), 'src_id = ?i', $src_id);
    }
}