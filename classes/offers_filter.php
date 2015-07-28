<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Класс управления фильтрами предложений
 *
 */
class offers_filter
{
    /**
     * Сохранение данных фильтра для последующего его создания или обновления
     *
     * @param integer $user_id            id пользователя
     * @param array   $categories         массив с категориями/профессиями
     * @param bool    ;only_my_offs       только мои предложения
     * @return string
     */
    function Save($user_id, $categories, $only_my_offs) {
        $uid = get_uid(false);
        list($cost_from, $cost_to) = projects_filters::preCosts($cost_from, $cost_to);
        $currency = intval($currency);
        $country=intval($country);
        $city=intval($city);
        $wo_cost = ($wo_cost)?'t':'f';
        $my_specs = ($my_specs)?'t':'f';
        if (!$force && ($uid!=$user_id)) { $rerror += 1; }
        //if (!preg_match( "/^[-^!#$%&'*+\/=?`{|}~.\w]+@[-a-zA-Z0-9]+(\.[-a-zA-Z0-9]+)+$/", $email )) { $rerror += 2; }
        //if (!in_array($currency, array(0, 1, 2))) { $rerror += 4; }
        if (!$error && !$rerror) {
            global $DB;
            $sql = "SELECT id FROM offers_filter WHERE user_id=? LIMIT 1";
            
            $filter_id = $DB->val( $sql, $user_id);
            
            if (!$filter_id)
                $error = $this->Add($user_id, $categories, $only_my_offs);
            else
                $error = $this->Update($filter_id, $categories, $only_my_offs);
         }
    }

    /**
     * Создание фильтра
     *
     * @param integer $user_id          id пользователя
     * @param array   $categories       массив с категориями/профессиями
     * @param bool    $only_my_offs     показать только мои предложения
     * 
     * @return array
     */
    function Add($user_id, $categories, $only_my_offs) {
        global $session;
        global $DB;
        
        $sql = "SELECT id FROM offers_filter WHERE user_id=? LIMIT 1";
        if($user_id && $filter_id = $DB->val( $sql, $user_id )){
            return $this->Update($filter_id, $categories);
        } else if($user_id > 0){
            $data = compact('user_id', 'only_my_offs');
            
            $filter_id = $DB->insert('offers_filter', $data, 'id');
            
            if ( !$DB->error && $filter_id && $categories && is_array($categories) ) {
                $data = array();
                
                if (sizeof($categories[0])) {
                    foreach ($categories[0] as $category => $type) {
                        $category = intval($category);
                        $data[]   = array( 'filter_id' => $filter_id, 'group_id' => $category, 'group_level' => false );
                    }
                }
                
                if (sizeof($categories[1])) {
                    foreach ($categories[1] as $category => $type) {
                        $category = intval($category);
                        $data[]   = array( 'filter_id' => $filter_id, 'group_id' => $category, 'group_level' => true );
                    }
                }
                
                if ( count($data) ) {
                    $DB->insert( 'offers_filter_groups', $data );
                }
            }
        }
        return $DB->error;
    }
    
    /**
     * Обновление данных фильтра
     *
     * @param integer $filter_id          id фильтра
     * @param array   $categories         массив с категориями/профессиями
     * @param bool    $only_my_offs       показать только мои предложения
     * 
     * @return string
     */
    function Update($filter_id, $categories, $only_my_offs) {
        global $DB;
        $data['active'] = true;
        $data['only_my_offs'] = $only_my_offs;
        
        $DB->update( 'offers_filter', $data, 'id=?i', $filter_id );
    
        if ( $DB->error ) return $DB->error;
    
        $sql = "DELETE FROM offers_filter_groups WHERE filter_id=?";
        
        $DB->query( $sql, $filter_id );
        
        if ( !$DB->error  && $categories && is_array($categories) ) {
            $data = array();
            
            if (sizeof($categories[0])) {
                foreach ($categories[0] as $category => $type) {
                    $category = intval($category);
                    $data[]   = array( 'filter_id' => $filter_id, 'group_id' => $category, 'group_level' => false );
                }
            }
            
            if (sizeof($categories[1])) {
                foreach ($categories[1] as $category => $type) {
                    $category = intval($category);
                    $data[]   = array( 'filter_id' => $filter_id, 'group_id' => $category, 'group_level' => true );
                }
            }
            
            if ( count($data) ) {
                $DB->insert( 'offers_filter_groups', $data );
            }
        }
        
        return $DB->error;
    }
    
    /**
     * Получение данных филтьтра
     *
     * @param integer $user_id          id пользователя
     *
     * @return array
     */
    function GetFilter($user_id) {
        global $session;
        
        if ($user_id > 0) {
            global $DB;
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
            $sql = "SELECT * FROM offers_filter WHERE user_id=?";
            $ret = $DB->row( $sql, $user_id);
      
            if ( $DB->error  || !$ret ) {
                $ret['user_specs'] = professions::GetProfessionsByUser($user_id, false, true);
                return $ret;
            }
            $sql = "SELECT group_id, group_level FROM offers_filter_groups WHERE filter_id=?i";
            $wrk = $DB->rows( $sql, $ret['id'] );
          
            if (is_array($wrk)) {
                for($i=0; $i<sizeof($wrk); $i++) {
                    $level = (($wrk[$i]['group_level']=="t")?1:0);
                    $ret['categories'][$level][$wrk[$i]['group_id']] = $level;
                }
            }
            
            $error .= $DB->error;
            
            if (isset($ret['cost_from']) && ($ret['cost_from'] == 0)) $ret['cost_from'] = '';
            if (isset($ret['cost_to']) && ($ret['cost_to'] == 0)) $ret['cost_to'] = '';
            
            $ret['user_specs'] = professions::GetProfessionsByUser($user_id, false, true);
        }
        
        return $ret;
    }
    
    /**
     * Удаление фильтра
     *
     * @param integer $user_id          id пользователя
     *
     * @return string
     */
    function DeleteFilter($user_id) {
        if ( $user_id > 0 ) {
            global $DB;
            $DB->update( 'offers_filter', array('active' => false), 'user_id=?i', $user_id);
            $error .= $DB->error;
        }
        
        return $error;
    }
    
    /**
     * Активация фильтра
     *
     * @param integer $user_id          id пользователя
     *
     * @return string
     */
    function ActivateFilter($user_id) {
        if ( $user_id > 0 ) {
            global $DB;
            if(!$this->IsFilter($user_id, $page)){
                $sql = "INSERT INTO offers_filter (user_id, active) VALUES (?i, true)";
                $DB->query($sql, $user_id);
            }
            
            $DB->update( 'offers_filter', array('active' => true), 'user_id=?i', $user_id);
            $error .= $DB->error;
        }
        
        return $error;
    }

    /**
     * Проверка существования фильтра конкретного юзера
     *
     * @param integer $user_id          id пользователя
     *
     * @return boolean true, если фильтр существует, false, если нет
     */
    function IsFilter($user_id) {
        if ( $user_id > 0 ) {
            global $DB;
            $sql = "SELECT count(*) FROM offers_filter WHERE user_id=? LIMIT 1";
            $num = $DB->val( $sql, $user_id);
            $ret = !!$num;
        }
        return $ret;
    }
    
    function createSqlFilter($filter, $cl="WHERE") {
        if($filter['categories']) {
                $categories = array();
    
                for ($ci=0; $ci<2; $ci++) {
                    if (sizeof($filter['categories'][$ci])) {
                        foreach($filter['categories'][$ci] as $ckey => $cvalue) {
                            $categories[$ci][] = (int)$ckey;
                        }
                    }
                }
    
                $fSql .= "{$cl} (";
    
                $sProfCat    = '';
                $sProfSubcat = '';
                
                // собираем подразделы выбранных разделов
                if (sizeof($categories[0])) {
                    $sProfCat = professions::getProfIdForGroups( $categories[0] );
                }
                
                // собираем выбранные подразделы
                if (sizeof($categories[1])) {
                    $sProfSubcat = implode( ',', $categories[1] );
                }
                
                // склеиваем и получаем все подразделы вместе с зеркалами
                $sProf = $sProfCat . (($sProfCat && $sProfSubcat) ? ',' : '') . $sProfSubcat;
                $aProf = professions::GetMirroredProfs( $sProf );
                
                $fSql .= ' subcategory_id in ('.implode(',', $aProf).') ';
                if(sizeof($categories[0])) {
                    $fSql .= 'OR category_id IN ('.implode(',', $categories[0]).')';
                } 
                $fSql .= ') ';
        }
        
        if ($filter['only_my_offs'] == 't') {
            $fSql .= ' AND fo.user_id = ' . get_uid(0);
        }
        
        return $fSql;
    }
}

?>
