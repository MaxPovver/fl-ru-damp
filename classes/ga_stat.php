<?php


class GaStat 
{
    const TABLE = 'ga_stat';
    
    /**
     * Вернуть обьект БД
     * 
     * @return type
     */
    protected function db()
    {
        return $GLOBALS['DB'];
    }
    

    /**
     * Проверяем фиксируем UTM метки
     * 
     * @return boolean
     */
    public function checkUtm()
    {
        $sess_id = session_id();
        
        //После авторизации обновляем UID пользователя
        if (!isset($_SESSION['ga_stat_updated']) && 
             isset($_SESSION['uid']) && $_SESSION['uid'] > 0) {
            
            $this->db()->update(self::TABLE, array(
                'user_id' => $_SESSION['uid']
            ), 'user_id IS NULL AND sess_id = ?', $sess_id);
            
            $_SESSION['ga_stat_updated'] = true;
        }
        
        
        
        if (!isset($_GET['utm_source'], 
                   $_GET['utm_medium'], 
                   $_GET['utm_campaign'])) {
            
            return false;
        }

        $utm_source = __paramInit('string', 'utm_source', null, null, 150);
        $utm_medium = __paramInit('string', 'utm_medium', null, null, 150);
        $utm_campaign = __paramInit('string', 'utm_campaign', null, null, 150);
        
        if(empty($utm_source) && 
           empty($utm_medium) &&
           empty($utm_campaign)) {
            
            return false;
        }

        
        //Хеш на ссылку живет 30 минут в течении жизни сессии
        $hash = md5($_SERVER['REQUEST_URI'] . date('d.m.Y H') . (date('i') > 30));
        
        //Не фиксируем метки на одинаковые страницы и исключаем накрутку
        if (isset($_SESSION['ga_stat_url_hash'][$hash])) {
            return false;
        }
        
        $_SESSION['ga_stat_url_hash'][$hash] = true;
        
        //Храним максимум до 100 разных хеш-ссылок в сессии и старые выбрасываем
        if (count($_SESSION['ga_stat_url_hash']) > 100) {
            $_SESSION['ga_stat_url_hash'] = array_slice($_SESSION['ga_stat_url_hash'], -100);
        }

        
        //Фиксируем метку
        $data = array(
            'utm_source' => $utm_source,
            'utm_medium' => $utm_medium,
            'utm_campaign' => $utm_campaign,
            'ip' => getRemoteIP(),
            'url' => parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
            'sess_id' => $sess_id
        );
        
        if (isset($_SESSION['uid']) && 
            $_SESSION['uid'] > 0) {

            $data['user_id'] = $_SESSION['uid'];
        }
        
        $ret = $this->db()->insert(self::TABLE, $data);
        
        return $ret;
    }
    
}