<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Класс управления фильтрами проектов
 *
 */
class projects_filters {
    
    /**
     * Сохранение данных фильтра для последующего его создания или обновления
     *
     * @param integer $filter_id          id пользователя
     * @param integer $cost_from          бюджет ОТ
     * @param integer $cost_to            бюджет ДО
     * @param integer $currency           id валюты поиска (0 - все)
     * @param boolean $wo_cost            поиск по проектам с неуказанным бюджетом
     * @param array $categories           массив с категориями/профессиями
     * @param integer $country            id страны
     * @param integer $city               id города
     * @param integer $keywords           поисковые слова
     * @param boolean $my_specs           использовать поиск только по специализациям пользователя
     * @param integer $page               id страницы фильтра
     * @param boolean $nogeo              обновлять или нет текущие город и страну в фильтре
     * 
     * @param string $konkursEndDaysFrom  конкурс заканчивается через столько дней - нижняя граница
     * @param string $konkursEndDaysTo    конкурс заканчивается через столько дней - верхняя граница
     * 
     * @param  bool $use_main_filter опционально. Для page равно 2 "API мобильного приложения". Если true использвать фильтр page равно 0 "Главная страница"
     * @return string
     */
    function Save($user_id, $cost_from, $cost_to, $currency, $wo_cost, $categories, $country, $city, $keywords, $my_specs, &$rerror, &$error, $force=0, $page=0, $nogeo=false,
            $only_sbr = false, $pro_only = false, $verify_only = false, $less_offers = false, $konkursEndDaysFrom = null, $konkursEndDaysTo = null, $use_main_filter = false, 
            $priceby = 4, $urgent_only = false, $block_only = false, $hide_exec = false)
    {
        $uid = get_uid(false);
        list($cost_from, $cost_to) = projects_filters::preCosts($cost_from, $cost_to);
        $currency = intval($currency);
        $priceby = intval($priceby);
        $country=intval($country);
        $city=intval($city);
        $wo_cost = ($wo_cost)?'t':'f';
        $my_specs = ($my_specs)?'t':'f';
        $only_sbr = ($only_sbr)?'t':'f';
        $pro_only = ($pro_only)?'t':'f';
        $urgent_only = ($urgent_only)?'t':'f';
        $block_only = ($block_only)?'t':'f';
        $verify_only = ($verify_only)?'t':'f';
        $less_offers = ($less_offers)?'t':'f';
        $use_main_filter = $use_main_filter ? 't' : 'f';
        $konkursEndDaysFrom = $konkursEndDaysFrom || $konkursEndDaysFrom === '0' ? (intval($konkursEndDaysFrom) >= 0 ? intval($konkursEndDaysFrom) : 0) : null;
        $konkursEndDaysTo = $konkursEndDaysTo || $konkursEndDaysTo === '0' ? (intval($konkursEndDaysTo) >= 0 ? intval($konkursEndDaysTo) : 0) : null;
        // если первая дата больше второй, то меняем их местами
        if ($konkursEndDaysFrom !== null && $konkursEndDaysTo !== null && $konkursEndDaysFrom > $konkursEndDaysTo) {
            list($konkursEndDaysFrom, $konkursEndDaysTo) = array($konkursEndDaysTo, $konkursEndDaysFrom);
        }
        $hide_exec = $hide_exec?'t':'f';
        
        if (!$force && ($uid!=$user_id)) { $rerror += 1; }
        if (!$error && !$rerror) {
            if(get_uid(false)) {
                global $DB;
                $sql = "SELECT id FROM projects_filters WHERE user_id=? AND page=? LIMIT 1";
            
                $filter_id = $DB->val( $sql, $user_id, $page );
            
                if (!$filter_id)
                    $error = $this->Add($user_id, $cost_from, $cost_to, $currency, $wo_cost, $categories, $country, $city, $keywords, $my_specs, $page, $only_sbr, $pro_only, $verify_only, $less_offers, $konkursEndDaysFrom, $konkursEndDaysTo, $use_main_filter, $priceby, $urgent_only, $block_only, $hide_exec);
                else
                    $error = $this->Update($filter_id, $cost_from, $cost_to, $currency, $wo_cost, $categories, $country, $city, $keywords, $my_specs, $page, $nogeo, $only_sbr, $pro_only, $verify_only, $less_offers, $konkursEndDaysFrom, $konkursEndDaysTo, $use_main_filter, $priceby, $urgent_only, $block_only, $hide_exec);
            } else {
                $f_data = array();
                $c_data = array();
                $f_data['filter'] = compact('cost_from', 'cost_to', 'currency', 'wo_cost', 'country', 'city', 'keywords', 'my_specs', 'page', 'only_sbr', 'pro_only', 'verify_only', 'less_offers', 'konkurs_end_days_from', 'konkurs_end_days_to', 'use_main_filter', 'priceby', 'urgent_only', 'block_only', 'hide_exec');                
                
                if (is_array($categories[0]) && sizeof($categories[0])) {
                    foreach ($categories[0] as $category => $type) {
                        $category = intval($category);
                        $c_data[]   = array( 'filter_id' => $filter_id, 'group_id' => $category, 'group_level' => false );
                    }
                }
                
                if (is_array($categories[1]) && sizeof($categories[1])) {
                    foreach ($categories[1] as $category => $type) {
                        $category = intval($category);
                        $c_data[]   = array( 'filter_id' => $filter_id, 'group_id' => $category, 'group_level' => true );
                    }
                }
                
                if ( count($c_data) ) {
                    $f_data['f_projects_groups'] = $c_data;
                }

                $_SESSION['f_project_filter'] = $f_data;
                $_SESSION['f_project_filter']['filter']['active'] = 't';
                $_SESSION['f_project_filter']['filter']['tcats'] = $categories;
            }
         }
    }
    
    /**
     * Сохранение фильтра неавторизированного пользоваля при авторизации
     */
    function SaveFromAnon() {
        global $DB;
        $user_id = get_uid(false);
        $page = 0;
        $cost_from = $_SESSION['f_project_filter']['filter']['cost_from'];
        $cost_to = $_SESSION['f_project_filter']['filter']['cost_to'];
        $currency = $_SESSION['f_project_filter']['filter']['currency'];
        $priceby = $_SESSION['f_project_filter']['filter']['priceby'];
        $wo_cost = $_SESSION['f_project_filter']['filter']['wo_cost'];
        $categories = $_SESSION['f_project_filter']['filter']['tcats'];
        $country = $_SESSION['f_project_filter']['filter']['country'];
        $city = $_SESSION['f_project_filter']['filter']['city'];
        $keywords = $_SESSION['f_project_filter']['filter']['keywords'];
        $only_sbr = $_SESSION['f_project_filter']['filter']['only_sbr'];
        $pro_only = $_SESSION['f_project_filter']['filter']['pro_only'];
        $verify_only = $_SESSION['f_project_filter']['filter']['verify_only'];
        $urgent_only = $_SESSION['f_project_filter']['filter']['urgent_only'];
        $block_only = $_SESSION['f_project_filter']['filter']['block_only'];
        $less_offers = $_SESSION['f_project_filter']['filter']['less_offers'];
        $konkursEndDaysFrom = $_SESSION['f_project_filter']['filter']['konkursEndDaysFrom'];
        $konkursEndDaysTo = $_SESSION['f_project_filter']['filter']['konkursEndDaysTo'];
        $use_main_filter = $_SESSION['f_project_filter']['filter']['use_main_filter'];
        $hide_exec = $_SESSION['f_project_filter']['filter']['hide_exec'];
        
        
        $sql = "SELECT id FROM projects_filters WHERE user_id=? AND page=? LIMIT 1";
        $filter_id = $DB->val( $sql, $user_id, $page );
        if (!$filter_id)
            $error = $this->Add($user_id, $cost_from, $cost_to, $currency, $wo_cost, $categories, $country, $city, $keywords, $my_specs, 
                                $page, $only_sbr, $pro_only, $verify_only, $less_offers, $konkursEndDaysFrom, $konkursEndDaysTo, $use_main_filter, 
                                $priceby, $urgent_only, $block_only, $hide_exec);
        else
            $error = $this->Update($filter_id, $cost_from, $cost_to, $currency, $wo_cost, $categories, $country, $city, $keywords, $my_specs, 
                                   $page, $nogeo, $only_sbr, $pro_only, $verify_only, $less_offers, $konkursEndDaysFrom, $konkursEndDaysTo, 
                                   $use_main_filter, $priceby, $urgent_only, $block_only, $hide_exec);
    }
    
    /**
     * Обрабатывает ввод пользователя в фильтр бюджета, поля "От" и "До".
     * Меняет местами если нужно.
     */
    static function preCosts($cost_from, $cost_to) {
        $cost_from = intval($cost_from);
        $cost_to = intval($cost_to);
        if($cost_to < 0) $cost_to = 0;
        if($cost_from < 0) $cost_from = 0;
        if($cost_from > ($tmp=$cost_to) && $tmp) {
            $cost_to = $cost_from;
            $cost_from = $tmp;
        }
        return array($cost_from, $cost_to);
    }

    


    /**
     * Создание фильтра
     *
     * @param integer $filter_id          id пользователя
     * @param integer $cost_from          бюджет ОТ
     * @param integer $cost_to            бюджет ДО
     * @param integer $currency           id валюты поиска (0 - все)
     * @param boolean $wo_cost            поиск по проектам с неуказанным бюджетом
     * @param array $categories           массив с категориями/профессиями
     * @param integer $country            id страны
     * @param integer $city               id города
     * @param integer $keywords           поисковые слова
     * @param boolean $my_specs           использовать поиск только по специализациям пользователя
     * @param integer $page               id страницы фильтра
     *
     * @param string $konkurs_end_days_from  конкурс заканчивается через столько дней - нижняя граница
     * @param string $konkurs_end_days_to    конкурс заканчивается через столько дней - верхняя граница
     * 
     * @param bool $use_main_filter опционально. Для page равно 2 "API мобильного приложения". Если true использвать фильтр page равно 0 "Главная страница"
     * @return array
     */
    function Add($user_id, $cost_from, $cost_to, $currency, $wo_cost, $categories, $country, $city, $keywords, $my_specs, $page=0, $only_sbr, 
            $pro_only, $verify_only, $less_offers, $konkurs_end_days_from = null, $konkurs_end_days_to = null, $use_main_filter = 'f', 
            $priceby = 4, $urgent_only, $block_only, $hide_exec) {
        global $DB;
        if($user_id > 0){
            $data = compact( 'user_id', 'cost_from', 'cost_to', 'currency', 'wo_cost', 'country', 'city', 'keywords', 'my_specs', 'page', 'only_sbr', 'pro_only', 'verify_only', 'less_offers', 'konkurs_end_days_from', 'konkurs_end_days_to', 'use_main_filter', 'priceby', 'urgent_only', 'block_only', 'hide_exec');
            
            $filter_id = $DB->insert( 'projects_filters', $data, 'id' );
            
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
                    $DB->insert( 'projects_filters_groups', $data );
                }
            }
        }
        return $DB->error;
    }
    


    /**
     * Обновление данных фильтра
     *
     * @param integer $filter_id          id пользователя
     * @param integer $cost_from          бюджет ОТ
     * @param integer $cost_to            бюджет ДО
     * @param integer $currency           id валюты поиска (0 - все)
     * @param boolean $wo_cost            поиск по проектам с неуказанным бюджетом
     * @param array $categories           массив с категориями/профессиями
     * @param integer $country            id страны
     * @param integer $city               id города
     * @param integer $keywords           поисковые слова
     * @param boolean $my_specs           использовать поиск только по специализациям пользователя
     * @param integer $page               id страницы фильтра
     * @param boolean $nogeo              обновлять или нет текущие город и страну в фильтре
     *
     * @param string $konkurs_end_days_from  конкурс заканчивается через столько дней - нижняя граница
     * @param string $konkurs_end_days_to    конкурс заканчивается через столько дней - верхняя граница
     * 
     * @param bool $use_main_filter опционально. Для page равно 2 "API мобильного приложения". Если true использвать фильтр page равно 0 "Главная страница"
     * @return string
     */
    function Update($filter_id, $cost_from, $cost_to, $currency, $wo_cost, $categories, $country, $city, $keywords, $my_specs, $page=0, $nogeo = false, $only_sbr, 
            $pro_only = false, $verify_only = false, $less_offers = false, $konkurs_end_days_from = null, $konkurs_end_days_to = null, $use_main_filter = 'f', 
            $priceby = 4, $urgent_only, $block_only, $hide_exec)
    {
        global $DB;
        $data = compact('cost_from', 'cost_to', 'currency', 'wo_cost', 'keywords', 'page', 'my_specs', 'only_sbr', 'pro_only', 'verify_only', 'less_offers', 'konkurs_end_days_from', 'konkurs_end_days_to', 'use_main_filter', 'priceby', 'urgent_only', 'block_only', 'hide_exec');
        $data['active'] = true;
        
        if ( !$nogeo ) {
        	$data['country'] = $country;
        	$data['city']    = $city;
        }
        
        $DB->update( 'projects_filters', $data, 'id=?i', $filter_id );
    
        if ( $DB->error ) return $DB->error;
    
        $DB->start();
        
        $sql = "DELETE FROM projects_filters_groups WHERE filter_id=?";
        
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
                $DB->insert( 'projects_filters_groups', $data );
            }
        }
        
        $DB->commit();
        
        return $DB->error;
    }
    


    /**
     * Получение данных филтьтра
     *
     * @param integer $user_id          id пользователя
     * @param integer $page             id страницы фильтра
     *
     * @return array
     */
    function GetFilter($user_id, &$error, $page=0)
    {
        if ($user_id > 0) {
            global $DB;
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
            
            $sql = "SELECT * FROM projects_filters WHERE user_id=? AND page=?";
            $ret = $DB->row( $sql, $user_id, $page );
      
            if ( $DB->error  || !$ret ) {
                $ret['user_specs'] = professions::GetProfessionsByUser($user_id, false, true);
                return $ret;
            }
            $sql = "SELECT group_id, group_level FROM projects_filters_groups WHERE filter_id=?i";
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
        } else {
            $ret = $_SESSION['f_project_filter']['filter'];
            $wrk = $_SESSION['f_project_filter']['f_projects_groups'];
          
            if (is_array($wrk)) {
                for($i=0; $i<sizeof($wrk); $i++) {
                    $level = (($wrk[$i]['group_level']=="t")?1:0);
                    $ret['categories'][$level][$wrk[$i]['group_id']] = $level;
                }
            }
            if (isset($ret['cost_from']) && ($ret['cost_from'] == 0)) $ret['cost_from'] = '';
            if (isset($ret['cost_to']) && ($ret['cost_to'] == 0)) $ret['cost_to'] = '';
        }
        
        return $ret;
    }
    


    /**
     * Удаление фильтра
     *
     * @param integer $user_id          id пользователя
     * @param integer $page             id страницы фильтра
     *
     * @return string
     */
    function DeleteFilter($user_id, $page=0)
    {
        if ( $user_id > 0 ) {
            //$sql = "DELETE FROM projects_filters WHERE (user_id='$user_id')";
            global $DB;
            $DB->update( 'projects_filters', array('active' => false), 'user_id=?i AND page=?i', $user_id, $page );
            $error .= $DB->error;
        } else {
            $_SESSION['f_project_filter']['filter']['active'] = 'f';
        }
        
        return $error;
    }



    /**
     * Активация фильтра
     *
     * @param integer $user_id          id пользователя
     * @param integer $page             id страницы фильтра
     *
     * @return string
     */
    function ActivateFilter($user_id, $page = 0) {
        global $DB;
        if($user_id) {
            $sql = 'UPDATE projects_filters SET active = true WHERE user_id = ?i AND page = ?i';
            $res = $DB->query($sql, $user_id, $page);
            if($res && !pg_affected_rows($res)) {
                $sql = "INSERT INTO projects_filters (user_id, page, active) VALUES (?i, ?i, true)";
                $DB->query($sql, $user_id, $page);
            }
            $ret = $DB->error;
        } else {
            $_SESSION['f_project_filter']['filter']['active'] = 't';
        }
        return $ret;
    }



    /**
     * Проверка существования фильтра конкретного юзера
     *
     * @param integer $user_id          id пользователя
     * @param integer $page             id страницы фильтра
     *
     * @return boolean true, если фмльтр существует, false, если нет
     */
    function IsFilter($user_id, $page=0)
    {
        if ( $user_id > 0 ) {
            global $DB;
            $sql = "SELECT count(*) FROM projects_filters WHERE user_id=? AND page=? LIMIT 1";
            $num = $DB->val( $sql, $user_id, $page );
            $ret = !!$num;
        } else {
            if(is_array($_SESSION['f_project_filter']['filter'])) {
                $ret = true;
            }
        }
        
        return $ret;
    }


    /**
     * Отображение блока скрытых платных проектов в фильтре на главной странице.
     *
     * @param integer $kind   текущая закладка (см. new_projects:getProjects()).
     * @param integer $page   номер текущей страницы.
     * @param integer $filter   используется ли фильтр сейчас (1=Да; 0=Нет).
     *
     * @return string   html-блок с называниями проектов.
     */
    function ShowClosedProjects($kind, $page, $filter) {
        $closed_name = '';
        $str = '<div class="flt-block flt-b-fc flt-b-lc">';

        if($_SESSION['ph']) {
            foreach($_SESSION['ph'] as $id=>$name)
            {
                $closed_name .= "<li><a href=\"/projects/{$id}\">".base64_decode($name)."</a>&nbsp;&nbsp;&nbsp;<a href=\"javascript: void();\" onclick=\"xajax_HideProject({$id}, 'unhide', '{$kind}', '{$page}', {$filter}); return false;\" class=\"flt-lnk\">Восстановить</a></li>";
            }

            $str .= "<div class=\"flt-ppc-div\"><ol class=\"flt-ppc\">{$closed_name}</ol></div>";
        }

        $str .= '<div class="flt-ppc-opt">';

        if ($closed_name)
            $str .= "<span id=\"flt-hide-restore-all\"><a href=\"javascript: void();\" onclick=\"xajax_HideProject('all', 'unhide', '{$kind}', '{$page}', {$filter}); return false;\" class=\"flt-lnk\">Восстановить все</a></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

        if ($_SESSION['top_payed'])
            $str .= "<span id=\"flt-hide-remove-all\"><a href=\"javascript: void();\" onclick=\"xajax_HideProject('all', 'hide', '{$kind}', '{$page}', {$filter}); return false;\"  class=\"flt-lnk\">Скрыть все платные проекты</a></span>";

        $str .= '</div>';
        $str .= '</div>';

        return $str;
    }


    /**
     * Инициализирует сессию массивом закрытых платных (закрепленных наверху) проектов для фильтра.
     * Если у проекта срок закрепления закончился, либо он был снят с публикации, либо удален,
     * то он удаляется из массива.
     * Сразу сохраняет имена проектов, чтобы лишний раз из базы не запрашивать.
     * Нужно запускать когда headers_sent()==false.
     */
    function initClosedProjects() {
        $uid = $_SESSION['uid'];
        if(!$uid) return;
        $ccph = $_COOKIE['ph'][$uid];
        if(!$_SESSION['ph'] && $ccph) {
            if(!isset($ccph[0])) { // старый вариант, удаляем куки (убить обработку в 2011 году).
                $_SESSION['ph'] = $ccph;
                foreach($_SESSION['ph'] as $key => $value)
                    setcookie("ph[{$uid}][{$key}]", '', time()+60*60*24*30, '/');                
            } else {
                $_SESSION['ph'] = array_fill_keys(explode(',', $ccph), 1);
            }
        }
        if($_SESSION['ph']) {
            global $DB;
            $sql = "SELECT id, name FROM projects WHERE id IN (?l) AND top_to >= now() AND closed = false";
            $res = $DB->rows( $sql, array_keys($_SESSION['ph']) );
            
            if( $res ) {
                foreach ( $res as $r)
                    $aph[$r['id']] = $r['name'];
                    
                foreach($_SESSION['ph'] as $id=>$v) {
                    if(!$aph[$id]) {
                        unset($_SESSION['ph'][$id]);
                    }
                    else {
                        // Сохраняем/обновляем заголовок проекта. Кодируем в base64, иначе со спец. символами может
                        // проблема возникнуть при записи сессии.
                        $_SESSION['ph'][$id] = base64_encode($aph[$id]);
                    }
                }
                if(!$_SESSION['ph'])
                    unset($_SESSION['ph']);
                if($_SESSION['ph']) $ccph = array_keys($_SESSION['ph']);
                setcookie("ph[{$uid}]", $ccph ? implode(',',$ccph) : '', time()+60*60*24*30, '/');
            }else{
                unset($_SESSION['ph']);
            }
        }
        if(is_array($_SESSION['ph'])) foreach($_SESSION['ph'] as $key => $name){
            if(!trim($name)) unset($_SESSION['ph'][$key]);
        }
    }

}


class projects_filters_pda extends projects_filters {
	
	
	public function Save($user_id, $cost_from, $cost_to, $my_specs, $active = true) {
		global $DB;
        
        $cost_from  = $this->ValidateCost($cost_from);
        $cost_to    = $this->ValidateCost($cost_to);
		
		list($cost_from, $cost_to) = $this->preCosts($cost_from, $cost_to);		
		$my_specs = ($my_specs)?'t':'f';
		
		if(($filter_id = $this->IsFilter($user_id))) {
			$data = compact('cost_from', 'cost_to', 'my_specs', 'active');
	        $e = $DB->update( 'projects_filters_pda', $data, 'id=?i', $filter_id );
		} else {
			$data = compact('user_id', 'cost_from', 'cost_to', 'my_specs');
            $filter_id = $DB->insert('projects_filters_pda', $data, 'id');
		}
	}
	
	public function UpdateActiveFilter($user_id, $active = true) {
		global $DB;
		if(($filter_id = $this->IsFilter($user_id))) {
			$data = compact('active');
	        $DB->update( 'projects_filters_pda', $data, 'id=?i', $filter_id );
		} else {
			$data = compact('user_id', 'active');
            $filter_id = $DB->insert('projects_filters_pda', $data, 'id');
		}	
	}
	
	function GetFilter($user_id) {
        global $DB;
        if (!$user_id) return false;
        
        $sql = "SELECT * FROM projects_filters_pda WHERE user_id=?i";
        $ret = $DB->row( $sql, $user_id);
        if ($DB->error  || !$ret) return false;
        
        if (isset($ret['cost_from']) && ($ret['cost_from'] == 0)) $ret['cost_from'] = '';
        if (isset($ret['cost_to']) && ($ret['cost_to'] == 0)) $ret['cost_to'] = '';
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
        $ret['user_specs'] = professions::GetProfessionsByUser($user_id, false, true);
                
        return $ret;
    }
	
	function IsFilter($user_id) {
		global $DB;
        if ($user_id > 0) {
            $sql = "SELECT id FROM projects_filters_pda WHERE user_id = ?i LIMIT 1";
            $ret = $DB->val($sql, $user_id);
        }
        return $ret;
    }
    
    /**
     * валидация цены проекта для фильтра
     * если превышает максимально допустимое значение, то возвращает максимально допустимое значение
     * @param int $cost 
     * 
     * @return int 
     */
    private function ValidateCost($cost) {
        if ($cost > 999999) {
            return 999999;
        } else {
            return $cost;
        }
    }
}

?>
