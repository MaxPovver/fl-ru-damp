<?
/**
 * Подключаем файл с основными функциями
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Управление фильтрами на странице фрилансеров.
 * 
 */
class freelancers_filters 
{
    /**
     * ИД Пользователя
     *
     * @var integer
     */
    public $uid = -1;
    
    /**
     * ИД Фильтра
     *
     * @var integer
     */
    public $fid = -1;
    
    /**
     * Конструктор класса
     *
     * @param integer $uid ИД пользователя фильтра
     * @param integer $fid ИД фильра если есть (по умолчанию false)
     */
    function __construct($uid=false, $fid=false) {
        if(!$uid) $this->uid = get_uid(false);
        else $this->uid = $uid;
        
        if(!$fid) self::isFilter();
        else $this->fid = $fid;
    }
    
    /**
     * Сохранить фильтр
     *
     * @param array $filter Данные для сохранения
     */
    function saveFilter($filter) {
        if(get_uid(false)) {
            if($this->fid > 0) {
                self::updateFilter($filter['exp'], $filter['age'], $filter['login'], $filter['pf_country_columns'][0], $filter['pf_country_columns'][1], $filter['in_office'], $filter['in_fav'], $filter['only_free'],
                                   $filter['is_pro'], /* $filter['only_online'], */ $filter['sbr_is_positive'], $filter['is_preview'], $filter['sbr_not_negative'], $filter['success_sbr'], $filter['kword'], $filter['sex'], $filter['opi_is_positive'], $filter['opi_not_negative'], $filter['is_verify'], $filter['only_tu']);
            } else {
                self::addFilter($filter['exp'], $filter['age'], $filter['login'], $filter['pf_country_columns'][0], $filter['pf_country_columns'][1], $filter['in_office'], $filter['in_fav'], $filter['only_free'],
                                $filter['is_pro'], /* $filter['only_online'], */ $filter['sbr_is_positive'], $filter['is_preview'], $filter['sbr_not_negative'], $filter['success_sbr'], $filter['kword'], $filter['sex'], $filter['opi_is_positive'], $filter['opi_not_negative'], $filter['is_verify'], $filter['only_tu']);
            }
            
            self::addCost($filter['from_cost'], $filter['to_cost'], $filter['cost_type'], $filter['curr_type']);
            if(!empty($filter['pf_subcategory'])){
                $filter['pf_categofy'][1][(int)$filter['pf_subcategory']] = 1;
            }elseif(!empty($filter['pf_category'])){
                $filter['pf_categofy'][0][(int)$filter['pf_category']] = 1;
            }
            self::addGroup($filter['pf_categofy']); 
        } else {
            self::addFilter($filter['exp'], $filter['age'], $filter['login'], $filter['pf_country_columns'][0], $filter['pf_country_columns'][1], $filter['in_office'], $filter['in_fav'], $filter['only_free'],
                           $filter['is_pro'], /* $filter['only_online'], */ $filter['sbr_is_positive'], $filter['is_preview'], $filter['sbr_not_negative'], $filter['success_sbr'], $filter['kword'], $filter['sex'], $filter['opi_is_positive'], $filter['opi_not_negative'], $filter['is_verify'], $filter['only_tu']);
            self::addCost($filter['from_cost'], $filter['to_cost'], $filter['cost_type'], $filter['curr_type']);
            if(!empty($filter['pf_subcategory'])){
                $filter['pf_categofy'][1][(int)$filter['pf_subcategory']] = 1;
            }elseif(!empty($filter['pf_category'])){
                $filter['pf_categofy'][0][(int)$filter['pf_category']] = 1;
            }
            self::addGroup($filter['pf_categofy']); 
        }
    }

    /**
     * Сохранение фильтра неавторизированного пользоваля при авторизации
     */
    function SaveFromAnon() {
        global $DB;

        $this->uid = get_uid(false);
        self::isFilter();

        $exp_from = $_SESSION['f_freelancers_filter']['exp_from'];
        $exp_to = $_SESSION['f_freelancers_filter']['exp_to'];
        $login = $_SESSION['f_freelancers_filter']['login'];
        $age_from = $_SESSION['f_freelancers_filter']['age_from'];
        $age_to = $_SESSION['f_freelancers_filter']['age_to'];
        $country = $_SESSION['f_freelancers_filter']['country'];
        $city = $_SESSION['f_freelancers_filter']['city'];
        $in_office = $_SESSION['f_freelancers_filter']['in_office'];
        $in_fav = $_SESSION['f_freelancers_filter']['in_fav'];
        $only_free = $_SESSION['f_freelancers_filter']['only_free'];
        $is_pro = $_SESSION['f_freelancers_filter']['is_pro'];
        $sbr_is_positive = $_SESSION['f_freelancers_filter']['sbr_is_positive'];
        $sbr_not_negative = $_SESSION['f_freelancers_filter']['sbr_not_negative'];
        $is_preview = $_SESSION['f_freelancers_filter']['is_preview'];
        $sbr = $_SESSION['f_freelancers_filter']['sbr'];
        $kword = $_SESSION['f_freelancers_filter']['kword'];
        $sex = $_SESSION['f_freelancers_filter']['sex'];
        $opi_is_positive = $_SESSION['f_freelancers_filter']['opi_is_positive'];
        $opi_not_negative = $_SESSION['f_freelancers_filter']['opi_not_negative'];
        $is_verify = $_SESSION['f_freelancers_filter']['is_verify'];
        $only_tu = $_SESSION['f_freelancers_filter']['only_tu'];

        if($this->fid > 0) {
            $sql = "UPDATE freelancers_filters SET 
                    exp_from = ?, exp_to = ?, login = ?, age_from = ?, age_to = ?, country = ?, city = ?, in_office = ?, 
                    in_fav = ?, only_free = ?, is_pro = ?, sbr_is_positive = ?, sbr_not_negative = ?, is_preview = ?, 
                    success_sbr = B?::bit(4), kwords = ?, sex = ?, opi_is_positive = ?, opi_not_negative = ?, is_verify = ?, only_tu = ? 
                    WHERE user_id = ? AND id = ?";
        
            $DB->query( $sql, $exp_from, $exp_to, $login, $age_from, $age_to, $country, $city, $in_office, $in_fav, $only_free, $is_pro, $sbr_is_positive, $sbr_not_negative, $is_preview, $sbr, $kword, $sex, $opi_is_positive, $opi_not_negative, $is_verify, $only_tu, $this->uid, $this->fid);
        } else {
            $sql = "INSERT INTO freelancers_filters (user_id, exp_from, exp_to, login, age_from, age_to, country, city, in_office, in_fav, only_free, is_pro, sbr_is_positive, sbr_not_negative, is_preview, success_sbr, kwords, sex, opi_is_positive, opi_not_negative, is_verify, only_tu)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, B?::bit(4), ?, ?, ?, ?, ?, ?)";
        
            $DB->query( $sql, $this->uid, $exp_from, $exp_to, $login, $age_from, $age_to, $country, $city, $in_office, $in_fav, $only_free, $is_pro, $sbr_is_positive, $sbr_not_negative, $is_preview, $sbr, $kword, $sex, $opi_is_positive, $opi_not_negative, $is_verify, $only_tu);
            $error = $DB->error;
        
            if(!$error) {
                self::isFilter();
            }
        }
        self::addCost($_SESSION['f_freelancers_filter']['f_freelancers_filters_cost'][0], $_SESSION['f_freelancers_filter']['f_freelancers_filters_cost'][1], $_SESSION['f_freelancers_filter']['f_freelancers_filters_cost'][2], $_SESSION['f_freelancers_filter']['f_freelancers_filters_cost'][3]);
        self::addGroup($_SESSION['f_freelancers_filter']['f_freelancers_category']); 
    }
    
    /**
     * Добавить новый фильтр
     *
     * @param array   $exp          опыт [от, до]
     * @param array   $age          лет [от, до]
     * @param string  $login        Логин, имя пользователя
     * @param integer $country      Страна
     * @param integer $city         Город
     * @param boolean $in_office    Ищу работу в офисе
     * @param boolean $in_fav       В избранном  у меня
     * @param boolean $only_free    ТОлько свободный
     * @param boolean $is_pro       только с аккаунотом про
     * @param boolean $only_online  Сейчас на сайте
     * @param boolean $is_positive  С положительными отзывами
     * @param boolean $is_preview   Только с примерами работ
     * @param boolean $not_negative Без отрицательных отзывов
     * @param array   $success_sbr  С успешным СБР
     * @param string  $kword        Ключевые слова
     * @param string  $sex          Пол
     * @return string Сообщение об ошибке
     */
    function addFilter($exp, $age, $login, $country, $city, $in_office, $in_fav, $only_free, $is_pro, /*$only_online,*/ $sbr_is_positive, $is_preview, $sbr_not_negative, $success_sbr, $kword, $sex, $opi_is_positive, $opi_not_negative, $is_verify, $only_tu) {
        $exp_from     = intval($exp[0])<0?0:(int)$exp[0];    
        $exp_to       = intval($exp[1])<0?0:(int)$exp[1];  
        $age_from     = intval($age[0])<0?0:(int)$age[0];
        $age_to       = intval($age[1])<0?0:(int)$age[1];
        $login        = trim(addslashes($login));
        $country      = intval($country);
        $city         = intval($city);
        $in_office    = $in_office?'t':'f';
        $in_fav       = $in_fav?'t':'f';
        $only_free    = $only_free?'t':'f';
        $only_online  = $only_online?'t':'f';
        $is_pro       = $is_pro?'t':'f';
        $sbr_is_positive  = $sbr_is_positive?'t':'f';
        $sbr_not_negative = $sbr_not_negative?'t':'f';
        $opi_is_positive  = $opi_is_positive?'t':'f';
        $opi_not_negative = $opi_not_negative?'t':'f';
        $is_preview   = $is_preview?'t':'f';
        $is_verify = $is_verify ? 't' : 'f';
        $only_tu = $only_tu ? 't' : 'f';
        $sex = intval($sex);
        
        $sbr = "";
        if($success_sbr) {
            $success_sbr[0] = 1;  
            for($i=0;$i<4;$i++) {
                $sbr .= intval($success_sbr[$i]);      
            }
        } else {
            $sbr = "0000";
        }
        
        if(trim($kword) != "") {
            $e = explode(",", $kword);
            foreach($e as $k=>$v)$m[] = stripcslashes(trim($v));
            $kword = substr((implode(",", $m)), 0, 120);
        }
        
        if(get_uid(false)) {
            global $DB;
            $sql = "INSERT INTO freelancers_filters (user_id, exp_from, exp_to, login, age_from, age_to, country, city, in_office, in_fav, only_free, is_pro, sbr_is_positive, sbr_not_negative, is_preview, success_sbr, kwords, sex, opi_is_positive, opi_not_negative, is_verify, only_tu)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, B?::bit(4), ?, ?, ?, ?, ?, ?)";
        
            $DB->query( $sql, $this->uid, $exp_from, $exp_to, $login, $age_from, $age_to, $country, $city, $in_office, $in_fav, $only_free, $is_pro, $sbr_is_positive, $sbr_not_negative, $is_preview, $sbr, $kword, $sex, $opi_is_positive, $opi_not_negative, $is_verify, $only_tu);
            $error = $DB->error;
        
            if(!$error) {
                /* берем идишник фильтра */
                self::isFilter();
                return true;
            }
        } else {
            $data = compact('exp_from', 'exp_to', 'login', 'age_from', 'age_to', 'country', 'city', 'in_office', 'in_fav', 'only_free', 'is_pro', 'sbr_is_positive', 'sbr_not_negative', 'is_preview', 'sbr', 'kword', 'sex', 'opi_is_positive', 'opi_not_negative', 'is_verify', 'only_tu');
            $data['active'] = 't';
            $_SESSION['f_freelancers_filter'] = $data;
            return true;
        }
        
        return false;
    }
    
    /**
     * Обновление фильтра
     * 
     * @param array   $exp          опыт [от, до]
     * @param array   $age          лет [от, до]
     * @param string  $login        Логин, имя пользователя
     * @param integer $country      Страна
     * @param integer $city         Город
     * @param boolean $in_office    Ищу работу в офисе
     * @param boolean $in_fav       В избранном  у меня
     * @param boolean $only_free    ТОлько свободный
     * @param boolean $is_pro       только с аккаунотом про
     * @param boolean $only_online  Сейчас на сайте
     * @param boolean $is_positive  С положительными отзывами
     * @param boolean $is_preview   Только с примерами работ
     * @param boolean $not_negative Без отрицательных отзывов
     * @param array   $success_sbr  С успешным СБР
     * @param string  $kword        Ключевые слова
     * @param integer $sex          Пол
     * @return string Сообщение об ошибке
     */
    function updateFilter($exp, $age, $login, $country, $city, $in_office, $in_fav, $only_free, $is_pro, /*$only_online,*/ $sbr_is_positive, $is_preview, $sbr_not_negative, $success_sbr, $kword, $sex, $opi_is_positive, $opi_not_negative, $is_verify, $only_tu) {
        $exp_from     = intval($exp[0])<0?0:(int)$exp[0];    
        $exp_to       = intval($exp[1])<0?0:(int)$exp[1];  
        $age_from     = intval($age[0])<0?0:(int)$age[0];
        $age_to       = intval($age[1])<0?0:(int)$age[1];
        $login        = trim(addslashes($login));
        $country      = intval($country);
        $city         = intval($city);
        $in_office    = $in_office?'t':'f';
        $in_fav       = $in_fav?'t':'f';
        $only_free    = $only_free?'t':'f';
        $only_online  = $only_online?'t':'f';
        $is_pro       = $is_pro?'t':'f';
        $sbr_is_positive  = $sbr_is_positive?'t':'f';
        $sbr_not_negative = $sbr_not_negative?'t':'f';
        $opi_is_positive  = $opi_is_positive?'t':'f';
        $opi_not_negative = $opi_not_negative?'t':'f';
        $is_preview   = $is_preview?'t':'f';
        $is_verify = $is_verify ? 't' : 'f';
        $only_tu = $only_tu ? 't' : 'f';
        $sex = intval($sex);
        $sbr = "";
        if($success_sbr) {
            $success_sbr[0] = 1;
            for($i=0;$i<4;$i++) {
                $sbr .= intval($success_sbr[$i]);      
            }
        } else {
            $sbr = "0000";
        }
        
        if(trim($kword) != "") {
            $e = explode(",", $kword);
            foreach($e as $k=>$v)$m[] = stripcslashes(trim($v));
            $kword = substr((implode(",", $m)), 0, 120);
        }
        
        global $DB;
        $sql = "UPDATE freelancers_filters SET 
                    exp_from = ?, exp_to = ?, login = ?, age_from = ?, age_to = ?, country = ?, city = ?, in_office = ?, 
                    in_fav = ?, only_free = ?, is_pro = ?, sbr_is_positive = ?, sbr_not_negative = ?, is_preview = ?, 
                    success_sbr = B?::bit(4), kwords = ?, sex = ?, opi_is_positive = ?, opi_not_negative = ?, is_verify = ?, only_tu = ? 
                WHERE user_id = ? AND id = ?";
        
        $DB->query( $sql, $exp_from, $exp_to, $login, $age_from, $age_to, $country, $city, $in_office, $in_fav, $only_free, $is_pro, $sbr_is_positive, $sbr_not_negative, $is_preview, $sbr, $kword, $sex, $opi_is_positive, $opi_not_negative, $is_verify, $only_tu, $this->uid, $this->fid);
        $error = $DB->error;
        
        return $error;
    }
    
    /**
     * Берем данные по фильтру 
     *
     * @return array [Основной фильтр, Фильтр стоимости, фильтр категорий]
     */
    function getFilter() {
        if(get_uid(false)) {
            if($this->filter) return $this->filter;
            
            if($this->fid > 0 && $this->uid > 0) {
                global $DB;
                
                $sql     = "SELECT * FROM freelancers_filters WHERE id = ? AND user_id = ?";
                $mFilter = $DB->row( $sql, $this->fid, $this->uid );
                
                $e = explode(",", $mFilter['kwords']);
                $mFilter['kwords'] = implode(", ", $e);
                
                $sql     = "SELECT * FROM freelancers_filters_cost WHERE filter_id = ?";
                $cFilter = $DB->rows( $sql, $this->fid );
                
                $sql     = "SELECT * FROM freelancers_filters_group WHERE filter_id = ?";
                $ret     = $DB->rows( $sql, $this->fid );
                
                $gFilter = array();
                if($ret) foreach($ret as $k=>$v) {
                    $v['level'] = ($v['level']=='t'?1:0);
                    $gFilter[$v['level']][$v['group_id']] = $v['level'];            
                }
               
                $this->filter = array($mFilter, $cFilter, $gFilter);
                
                return array($mFilter, $cFilter, $gFilter);
            }
        } else {
            $mFilter = $_SESSION['f_freelancers_filter'];

            $e = explode(",", $mFilter['kwords']);
            $mFilter['kwords'] = implode(", ", $e);

            $cFilter = $_SESSION['f_freelancers_filter']['freelancers_filters_cost'];

            $ret = $_SESSION['f_freelancers_filter']['freelancers_filters_group'];

            $gFilter = array();
            if($ret) foreach($ret as $k=>$v) {
                $v['level'] = ($v['level']=='t'?1:0);
                $gFilter[$v['level']][$v['group_id']] = $v['level'];            
            }
            $this->filter = array($mFilter, $cFilter, $gFilter);
            return array($mFilter, $cFilter, $gFilter);
        }
        
        return false;
    }
    
    /**
     * Добавляем стоимость в фильтр
     *
     * @param array $from_cost Суммы (от)
     * @param array $to_cost   Суммы (до)
     * @param array $cost_type Тип суммы (см табл freelancers_filters_cost) 
     * @param array $curr_type Тип валюты
     * @return string|boolean Сообщение об ошибке
     */
    function addCost($from_cost, $to_cost, $cost_type, $curr_type) {
        if(get_uid(false)) {
            if(count($from_cost) > 0 && $this->fid > 0) {
                global $DB;
                $sql = "DELETE FROM freelancers_filters_cost WHERE filter_id = ?";
                $DB->query( $sql, $this->fid );
                
                $sql = "";
                for($i=0;$i<count($from_cost);$i++) {
                    $from_cost[$i] = intval(substr($from_cost[$i],0,6));
                    $to_cost[$i]   = intval(substr($to_cost[$i],0,6));
                    $curr_type[$i] = intval(substr($curr_type[$i],0,6));
                    $cost_type[$i] = intval(substr($cost_type[$i],0,6));
                    
                    if($from_cost[$i] != 0 || $to_cost[$i] != 0) $sql .= "INSERT INTO freelancers_filters_cost (filter_id, cost_from, cost_to, cost_type, type_date) VALUES('{$this->fid}', '{$from_cost[$i]}', '{$to_cost[$i]}', '{$curr_type[$i]}', '{$cost_type[$i]}'); ";
                }
                
                if ( $sql ) $DB->squery( $sql );
                return $DB->error;
            }
        } elseif(count($from_cost) > 0) {
            $_SESSION['f_freelancers_filter']['f_freelancers_filters_cost'][0] = $from_cost;
            $_SESSION['f_freelancers_filter']['f_freelancers_filters_cost'][1] = $to_cost;
            $_SESSION['f_freelancers_filter']['f_freelancers_filters_cost'][2] = $cost_type;
            $_SESSION['f_freelancers_filter']['f_freelancers_filters_cost'][3] = $curr_type;
            $_SESSION['f_freelancers_filter']['freelancers_filters_cost'] = array();
            $data = array();
            for($i=0;$i<count($from_cost);$i++) {
                $from_cost[$i] = intval(substr($from_cost[$i],0,6));
                $to_cost[$i]   = intval(substr($to_cost[$i],0,6));
                $curr_type[$i] = intval(substr($curr_type[$i],0,6));
                $cost_type[$i] = intval(substr($cost_type[$i],0,6));
                  
                if($from_cost[$i] != 0 || $to_cost[$i] != 0) {
                    $tdata = array();
                    $tdata['cost_from'] = $from_cost[$i];
                    $tdata['cost_to'] = $to_cost[$i];
                    $tdata['cost_type'] = $curr_type[$i];
                    $tdata['type_date'] = $cost_type[$i];
                    $data[] = $tdata;
                }
            }
            $_SESSION['f_freelancers_filter']['freelancers_filters_cost'] = $data;
            return;
        }
        
        return false;
    }
    
    /**
     * Добавляем категории профессий в фильтр
     *
     * @param arrya $category Категории
     * @return string Сообщение об ошибке
     */
    function addGroup($category) {
        include_once(realpath(dirname(__FILE__)).'/professions.php');
        $added = array();
        if(get_uid(false)) {
            if($this->fid > 0) {
                global $DB;
                $sql = "DELETE FROM freelancers_filters_group WHERE filter_id = ?";
                $DB->query( $sql, $this->fid );
                $all_mirrored_specs = professions::GetAllMirroredProfsId();
                $mirrored_specs = array();
                for ($is=0; $is<sizeof($all_mirrored_specs); $is++) {
                    $mirrored_specs[$all_mirrored_specs[$is]['main_prof']] = (int)$all_mirrored_specs[$is]['mirror_prof'];
                    $mirrored_specs[$all_mirrored_specs[$is]['mirror_prof']] = (int)$all_mirrored_specs[$is]['main_prof'];
                }
                $sql = "";

                function isMirrored($group_id,$level, $mirrored_specs, $added){
                   if (!empty($mirrored_specs[$group_id]) && isset ($added[$mirrored_specs[$group_id]])) return true;
                }
                if(count($category) > 0) {
                    foreach($category as $level=>$ids) {
                        foreach($ids as $group_id=>$val) {
                            if(!(int)$group_id) continue;
                            if(isMirrored($group_id,$level, $mirrored_specs, $added) || !empty($added[(int)$group_id])) continue;
                            $sql .= "INSERT INTO freelancers_filters_group (filter_id, group_id, level) VALUES ('{$this->fid}', '".intval($group_id)."', '".intval($level)."');";
                            if(!(int)$level){
                                $subs = professions::GetProfs($group_id);
                                foreach($subs as $itm){
                                    $added[$itm['id']] = true;
                                }
                            }else{
                                $added[(int)$group_id] = true;
                            }
                        }
                    }
                    if(!$sql) return false;
                    $DB->squery( $sql );
                    return $DB->error;
                }
            }
        } else {
            $_SESSION['f_freelancers_filter']['f_freelancers_category'] = $category;
            $_SESSION['f_freelancers_filter']['freelancers_filters_group'] = array();
            $all_mirrored_specs = professions::GetAllMirroredProfsId();
            $mirrored_specs = array();
            for ($is=0; $is<sizeof($all_mirrored_specs); $is++) {
                $mirrored_specs[$all_mirrored_specs[$is]['main_prof']] = (int)$all_mirrored_specs[$is]['mirror_prof'];
                $mirrored_specs[$all_mirrored_specs[$is]['mirror_prof']] = (int)$all_mirrored_specs[$is]['main_prof'];
            }
            $sql = "";

            function isMirrored($group_id,$level, $mirrored_specs, $added){
                if (!empty($mirrored_specs[$group_id]) && isset ($added[$mirrored_specs[$group_id]])) return true;
            }
            $data = array();
            if(count($category) > 0) {
                foreach($category as $level=>$ids) {
                    foreach($ids as $group_id=>$val) {
                        $tdata = array();
                        if(!(int)$group_id) continue;
                        if(isMirrored($group_id,$level, $mirrored_specs, $added) || !empty($added[(int)$group_id])) continue;
                        $tdata['group_id'] = intval($group_id);
                        $tdata['level'] = intval($level);
                        $data[] = $tdata;
                        $sql = '1';
                        if(!(int)$level){
                            $subs = professions::GetProfs($group_id);
                            foreach($subs as $itm){
                                $added[$itm['id']] = true;
                            }
                        }else{
                            $added[(int)$group_id] = true;
                        }
                    }
                }
                if(!$sql) return false;
                $_SESSION['f_freelancers_filter']['freelancers_filters_group'] = $data;
                return;
            }
        }
        return false;
    }
    
    /**
     * Проверяем был ли создан фильтр и записываем его ИД
     *
     * @return boolean
     */
    function isFilter() {
        if($this->uid > 0) {
            global $DB;
            $sql = "SELECT id FROM freelancers_filters WHERE user_id=?";
            $fid = $DB->val( $sql, $this->uid );
            
            if($fid > 0) {
                $this->fid = $fid;  
                return true;  
            }
        }
        return false;
    }
    
    /**
     * Проверяем активен ли фильтр, и ставим его в положение активности если того требует
     *
     * @param boolean $act Статус активности
     * @return boolean Состояние активности
     */
    function isActive($act=true) {
        if(get_uid(false)) {
            if($this->fid > 0) {
                global $DB;
                if (!$act) {
                    $_SESSION['region_filter_country'] = 0;
                    $_SESSION['region_filter_city'] = 0;
                }
                $act = $act?"t":"f";
                $DB->update( 'freelancers_filters', array('active' => $act), 'id = ?', $this->fid );
                return $act;
            }
        } else {
                if (!$act) {
                    $_SESSION['region_filter_country'] = 0;
                    $_SESSION['region_filter_city'] = 0;
                }
                $act = $act?"t":"f";
                $_SESSION['f_freelancers_filter']['active'] = $act;
        }
    }
    
    /**
     * Гнерирует переменную с данными всего фильтра
     *
     * @return array Фильтр
     */
    function getAllFilter() {
        if(!$this->filter) self::getFilter();
        
        if($this->filter[0]['active'] == 'f') return false;
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/kwords.php");
        $kword = new kwords();
        $filter['orig_kwords']  = $this->filter[0]['kwords'];
        $filter['kword']        = $kword->getKeys($this->filter[0]['kwords'], $filter['is_kword']);
        $filter['sex']          = $this->filter[0]['sex'];
        $filter['country']      = $this->filter[0]['country'];
        $filter['city']         = $this->filter[0]['city'];
        $filter['age']          = array($this->filter[0]['age_from'], $this->filter[0]['age_to']); 
        $filter['login']        = trim($this->filter[0]['login']);
        $filter['exp']          = array($this->filter[0]['exp_from'], $this->filter[0]['exp_to']);
        $filter['cost']         = $this->filter[1];
        $filter['prof']         = $this->filter[2];
        $filter['in_office']    = $this->filter[0]['in_office']=='t'?true:false;
        $filter['in_fav']       = $this->filter[0]['in_fav']=='t'?true:false;
        $filter['only_free']    = $this->filter[0]['only_free']=='t'?true:false;
        $filter['sbr_is_positive']  = $this->filter[0]['sbr_is_positive']=='t'?true:false;
        $filter['sbr_not_negative'] = $this->filter[0]['sbr_not_negative']=='t'?true:false;
        $filter['opi_is_positive']  = $this->filter[0]['opi_is_positive']=='t'?true:false;
        $filter['opi_not_negative'] = $this->filter[0]['opi_not_negative']=='t'?true:false;
        $filter['is_preview']   = $this->filter[0]['is_preview']=='t'?true:false;
        $filter['success_sbr']  = $this->filter[0]['success_sbr'];
        $filter['is_pro']       = $this->filter[0]['is_pro']=='t'?true:false;
        $filter['is_verify']    = $this->filter[0]['is_verify']=='t'?true:false;
        $filter['only_tu']    = $this->filter[0]['only_tu']=='t'?true:false;
        $filter['only_online']  = $this->filter[0]['only_online']=='t'?true:false;
        
        return $filter;
    }
    
    /**
     * Считаем количество фрилансеров различных разрядов СБР
     *
     * @return array Данные подсчета
     */
    function getRankCount($prof_id = 0) {
        $memBuff = new memBuff();
        if($prof_id) {
            $or_prof = professions::GetProfessionOrigin($prof_id);
            $tbl_s = "
                ( SELECT * FROM fu WHERE spec_orig = '{$or_prof}' UNION ALL
                  SELECT fu.* FROM fu INNER JOIN spec_add_choise sp ON sp.user_id = fu.uid AND sp.prof_id = '{$or_prof}' WHERE fu.is_pro = true
                  UNION ALL
                  SELECT fu.* FROM fu INNER JOIN spec_paid_choise spc ON spc.user_id = fu.uid AND spc.prof_id = '{$or_prof}' AND spc.paid_to > now()
                ) as s
            ";
        }
        else {
            $tbl_s   = "fu s";
            $join_pc = "
              INNER JOIN
                portf_choise pc
                  ON pc.prof_id = s.spec_orig
                 AND pc.user_id = s.uid
            ";
        }
        
        $sql     = "
          SELECT SUM((r.rank=3)::int) as rank3, SUM((r.rank=2)::int) as rank2, SUM((r.rank=1)::int) as rank1
            FROM rating r
          INNER JOIN
            {$tbl_s}
              ON s.uid = r.user_id
             AND s.is_banned = '0'
          {$join_pc}
           WHERE r.rank > 0
        ";
        $rank    = $memBuff->getSql($error, $sql, 3600);
        if($error || !$rank) return NULL;
        return $rank[0];
    }
    
    /**
     * @desc Возвращает JavaScript объект all_mirrored_specs, который используется на страницах содержащих фильтр
     * @param array $all_mirrored_specs - результат DB->rows("SELECT * FROM mirrored_professions")
     * @return string 
    **/
    static public function getMirroredSpecsJsObject($all_mirrored_specs) {
        $result = array();
        foreach ($all_mirrored_specs as $pair) {
            $main_prof   = $pair["main_prof"];
            $mirror_prof = $pair["mirror_prof"];

            if ( !is_array( $result[$mirror_prof] ) ) {
                $result[$mirror_prof] = array( $main_prof );
            } else {
                $result[$mirror_prof][] = $main_prof;
            }

            if ( !is_array( $result[$main_prof] ) ) {
                $result[$main_prof] = array( $mirror_prof );
            } else {
                $result[$main_prof][] = $mirror_prof;
            }
        }
        return json_encode( $result );
    }
    /**
     * Поиск в массиве $set
     * $id или его отражения в $mirrored_specs
     * */
    static public function mirrorExistsInArray($id, $set, $mirrored_specs) {
        if (in_array($mirrored_specs[$id], $set)) return true;
        foreach ($set as $i) {        
            if ($mirrored_specs[$i] == $id) {
                return true;
            }
            else {
                $j = $i;
                $store = array();
                while ($mirrored_specs[$j]) {
                    if ($mirrored_specs[$j] == $id) return true;
                    $j = $mirrored_specs[$j];
                    if (in_array($j, $store)) break;
                    $store[] = $j;
                }
            }
        }
        return false;
    }
}
?>