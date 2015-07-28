<?php
/** 
 * Подлючение файла с основными функциями системы 
 */ 
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php"); 

class verify
{
    
    const VERIFY_DATE_START = 'April 10, 2013 00:00:00';
    
    /**
     * Подписать пользователя на рассылку
     */
    public function addSubscribeUser($uid = null) {
        global $DB;
        if(!$uid) $uid = $_SESSION['uid'];
        $memBuff = new memBuff();
        $memBuff->delete("verify_count"); // Очищаем кеш
        return $DB->insert("verify", array("uid" => $uid));
    }
    
    /**
     * Количество подписавшихся на рассылку
     * @global type $DB
     * @return type
     */
    public function getCountSubscribe() {
        global $DB;
        $memBuff = new memBuff();
        $count   = $memBuff->get('verify_count');
        if( !$count ) {
            $count = $DB->val("SELECT COUNT(*) as cnt FROM verify");
            $memBuff->add('verify_count', $count, 600);
        }
        return $count;
    }
    
    /**
     * Подписан пользователь или нет
     * 
     * @global type $DB
     * @param type $uid ИД пользователя
     * @return type
     */
    public function isSubscribeUser($uid = null) {
        global $DB;
        if(!$uid) $uid = $_SESSION['uid'];
        
        return $DB->val("SELECT id FROM verify WHERE uid = ?", $_SESSION['uid']);
    }
    
    public static function converNumbersTemplate($num) {
        return preg_replace("/(\d{1})/", '<span class="b-promo__digital b-promo__digital_margright_3">$1</span>', $num);
    } 
}
