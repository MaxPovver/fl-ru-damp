<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/search/search_element.php";
/**
 * Класс для поиска по всем пользователям
 *
 */
class searchElementUsers_all extends searchElement
{
    public $name = 'Люди';
    public $totalwords = array('человек', 'человека', 'людей');
    protected $_indexSfx = '';
    protected $_mode   = SPH_MATCH_ANY; //SPH_MATCH_EXTENDED2;
    protected $_sort   = SPH_SORT_EXTENDED;
    protected $_sortby = '@weight DESC';
    protected $_maxmatches  = 500;
    protected $_fieldweights = array(
        'login' => 4,
        'name' => 3,
        'email' => 2,
        'second_email' => 2,
        'ljuser' => 2,
        'skype' => 2,
        'icq' => 2,
        'jabber' => 2,
        'site' => 2,
        'phone' => 2,
        'safety_phone' => 2,
        'email_1' => 1,
        'email_2' => 1,
        'email_3' => 1,
        'site_1' => 1,
        'site_2' => 1,
        'site_3' => 1,
        'phone_1' => 1,
        'phone_2' => 1,
        'phone_3' => 1,
        'icq_1' => 1,
        'icq_2' => 1,
        'icq_3' => 1,
        'skype_1' => 1,
        'skype_2' => 1,
        'skype_3' => 1,
        'jabbers' => 1, //доп jabber1-3
        'ljs' => 1 //доп lj1-3
    );

    
    /**
     * Пока поиск по всем юзерам доступен только админу с правами
     * 
     * @return type
     */
    public function isAllowed() 
    {
        return hasPermissions('users');
    }
    
    
    /**
     * Установка атрибутов из фильтра
     * 
     * @param type $page
     * @param type $filter
     */
    public function setAdvancedSearch($page = 0, $filter = array()) 
    {
        $this->_advanced       = $filter; 
        $this->_advanced_page  = $page;
        $this->_advanced_limit = $this->_limit;  
        
        
        if (isset($filter['who']) && !empty($filter['who'])) {
            $this->_filtersV[] = array(
                "attr" => 'is_emp',
                "values" => array((int)($filter['who'] == 'emp'))
            );
        }
        
        
        if (isset($filter['status']) && !empty($filter['status'])) {
            switch($filter['status']) {
                case 1:
                    $this->_filtersV[] = array("attr" => 'is_banned',"values" => array(1));
                    $this->_filtersV[] = array("attr" => 'self_deleted',"values" => array(0));
                    break;
                
                case 2:
                    $this->_filtersV[] = array("attr" => 'active',"values" => array(0));
                    break;
                
                case 3:
                    $this->_filtersR[] = array("attr" => 'warn','min' => 1, 'max' => 9999999);
                    break;
                
                case 4:
                    $this->_filtersV[] = array("attr" => 'self_deleted',"values" => array(1));
                    break;
                
                case 5:
                    $this->_filtersV[] = array("attr" => 'is_banned',"values" => array(0));
                    break;
            }
        }
        
        
        $nLongIpF = isset($filter['ip_from']) ? ip2long($filter['ip_from']) : 0;
        $nLongIpT = isset($filter['ip_to']) ? ip2long($filter['ip_to']) : ip2long('255.255.255.255');
        
        if ($nLongIpF || $nLongIpT) {
            if ($nLongIpF == $nLongIpT) {
                $this->_filtersV[] = array("attr" => 'log_ip',"values" => array($nLongIpF));
            } else {
                $this->_filtersR[] = array("attr" => 'log_ip','min' => $nLongIpF, 'max' => $nLongIpT);
            }
        }
        
        if (isset($filter['uid']) && $filter['uid'] > 0) {
            $this->_min_id = $filter['uid'];
            $this->_max_id = $filter['uid'];
        }
                
    }    
    
    
    /**
     * Выборка по результатам поиска
     * 
     * @global type $DB
     */
    public function setResults() 
    {
        global $DB;

        $sqlLimit = '';
        
        //Фильтрация на уровне выборки из БД
        if (($filter = $this->isAdvanced())) {
        
            $page   = $this->_advanced_page;
            $limit  = $this->_advanced_limit;
            $offset = 0;

            if($page > 0) {
                $offset = ($page - 1) * $limit;
            } 

            $this->_offset = $offset;
            $sqlLimit = $DB->parse("LIMIT ?i OFFSET ?i", $limit, $offset);
            
        }
        
        
        //Базовый запрос
        $sqlBase = "
           FROM users AS u
           LEFT JOIN account a ON a.uid = u.uid
           LEFT JOIN sbr_reqv sr ON sr.user_id = u.uid
           WHERE u.uid IN(?l)
        ";
        
        
        //Если есть ограничения то нужно посчитать кол-во из выбокри БД
        if(!empty($sqlLimit)) {
            $sqlCnt = "
                SELECT
                    COUNT(u.uid) as cnt
                {$sqlBase}
            ";
                
            $this->total = $DB->val($sqlCnt, $this->matches);
        }   
           
        //Основная выборка по результатам поиска
        $sql = "
            SELECT 
                u.uid, 
                u.uname, 
                u.usurname, 
                u.login, 
                u.role, 
                u.is_pro, 
                u.is_pro_test, 
                u.is_team, 
                u.photo, 
                u.warn, 
                u.email, 
                u.reg_ip, 
                u.last_ip, 
                u.is_banned, 
                u.ban_where, 
                u.self_deleted, 
                u.safety_only_phone, 
                u.safety_bind_ip, 
                u.active, 
                u.pop, 
                u.phone, 
                u.phone_1, 
                u.phone_2, 
                u.phone_3, 
                a.is_block as is_block_money, 
                u.is_verify, 
                sr.is_activate_mob, 
                sr._1_mob_phone as safety_phone, 
                sr.is_safety_mob 
           {$sqlBase}
           {$sqlLimit}
        ";
        
        $this->results = $DB->rows($sql, $this->matches);
    }

}