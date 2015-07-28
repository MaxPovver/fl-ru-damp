<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/search/search_element.php";
/**
 * Класс для поиска по пользователям
 *
 */
class searchElementUsers extends searchElement
{
    public $name = 'Люди';
    public $totalwords = array('человек', 'человека', 'людей');
    protected $_indexSfx = '';
    protected $_mode   = SPH_MATCH_EXTENDED2;
    protected $_sort   = SPH_SORT_EXTENDED;
    protected $_sortby = 'is_pro DESC, rating DESC, @id';

    function setIndexes() {
        $this->_indexSfx = $this->_engine->uid > 0 ? '' : '_na';
        parent::setIndexes();
    }
    
    public function setAdvancedSearch($page=0, $filter) {
        $this->_advanced       = $filter; 
        $this->_advanced_page  = $page;
        $this->_advanced_limit = $this->_limit;  
        
        if (isset($filter['country']) && $filter['country'] > 0) {
            $this->_filtersV[] = array(
                "attr" => 'country',
                "values" => array($filter['country'])
            );
        }
        
        if (isset($filter['city']) && $filter['city'] > 0) {
            $this->_filtersV[] = array(
                "attr" => 'city',
                "values" => array($filter['city'])
            );
        }
        
    }

    function setResults() {
        global $session, $DB;
        if(($filter = $this->isAdvanced())) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
            foreach($this->matches as $val) {
                $frl_ids[] = $val;
            }
            
            $page   = $this->_advanced_page;
            $limit  = $this->_advanced_limit;
            $offset = 0;
            
            if($page > 0) {
                $offset = ($page - 1) * $limit;
            } 
            
            $this->_offset = $offset;
            //if(isset($filter['nt_negative'])) $filter['not_negative'] = $filter['nt_negative'];
            $prof_id = is_array($filter['prof'][1]) ? array_keys($filter['prof'][1]) : array();
            $order_by_spec_orign = "";
            if (count($prof_id)) {
            	$order_by_spec_orign = "s.spec IN (".join(", ", $prof_id).") DESC,";
            }
            
            //@todo: спорная операция так как в запрос не пойдут категории 
            //и вся надежда только на поиск по тексту от Сфинкса
            //
            //пока убираю
            //
            //unset($filter['prof']);
            
            $fprms = freelancer::createCatalogFilterSql($filter, 0);
            $filter_sql = $inner_sql = '';
            if($fprms !== -1) {
                list($filter_sql, $inner_sql) = $fprms;  
                
                if($filter_sql) {
                    $filter_sql = ' AND '.$filter_sql;
                }  
            }
            
            $sql = "SELECT 
                      COUNT(u.uid) as cnt 
                    FROM users u
                    LEFT JOIN 
                      freelancer s ON s.uid = u.uid
                    LEFT JOIN
                      portf_choise pc ON ( pc.prof_id = s.spec_orig AND pc.user_id = s.uid )
                    LEFT JOIN
                      users_counters uc ON ( uc.user_id = u.uid )
                    {$inner_sql} 
                    WHERE 
                      u.uid IN (".implode(", ", $frl_ids).") 
                        AND 
                      u.is_banned = '0'
                      {$filter_sql} 
                    LIMIT 1;";
            
            $this->total = $DB->val($sql);
                
            $sql = "SELECT 
                      u.uid as id, 
                      u.photo, 
                      u.role, 
                      u.is_pro, 
                      u.is_profi, 
                      u.is_team, 
                      u.is_pro_test, 
                      u.info_for_reg, 
                      p.name as name_prof, 
                      u.login, 
                      u.uname, 
                      u.usurname, 
                      u.email, 
                      u.skype, 
                      s.site, 
                      ctr.country_name, 
                      ct.city_name, 
                      s.spec_text, 
                      s.resume, 
                      s.konk, 
                      s.blocks, 
                      s.status_type,  
                      s.cost_month, 
                      s.cost_hour, 
                      s.in_office, 
                      s.exp,
                      pc.cost_from, 
                      pc.cost_to, 
                      pc.cost_1000, 
                      pc.cost_type, 

                      (uc.paid_advices_cnt + uc.ops_emp_plus + uc.ops_frl_plus + uc.sbr_opi_plus + uc.tu_orders_plus + uc.projects_fb_plus) AS all_opi_plus,
                      (uc.ops_emp_null + uc.ops_frl_null + uc.sbr_opi_null) AS all_opi_null,
                      (uc.ops_emp_minus + uc.ops_frl_minus + uc.sbr_opi_minus + uc.tu_orders_minus + uc.projects_fb_minus) AS all_opi_minus,

                      s.cost_type_hour, 
                      s.cost_type_month, 
                      rating_get(s.rating, s.is_pro, s.is_verify, s.is_profi) as rating,
                      (add_spec.additional_specs || ', ' || COALESCE(p_spec.paid_specs, '')) AS additional_specs
                      -- get_additional_specs_as_string(u.uid) AS additional_spec

                    FROM users u
                    LEFT JOIN 
                      freelancer s ON s.uid = u.uid
                    LEFT JOIN
                      portf_choise pc ON ( pc.prof_id = s.spec_orig AND pc.user_id = s.uid )
                    LEFT JOIN
                      professions p ON p.id = s.spec AND p.id > 0
                    LEFT JOIN
                      users_counters uc ON uc.user_id = u.uid
                    LEFT JOIN 
                      country ctr ON ctr.id = s.country AND ctr.id > 0
                    LEFT JOIN 
                      city ct ON ct.id = s.city AND ct.id > 0

                    LEFT JOIN ( SELECT array_to_string(array_agg(p.name), ', '::text)  AS additional_specs, sa.user_id AS uid
                        FROM spec_add_choise AS sa
                    LEFT JOIN professions AS p ON p.id = prof_id
	                   GROUP BY sa.user_id) AS add_spec ON add_spec.uid = u.uid

                    LEFT JOIN ( SELECT array_to_string(array_agg(p.name), ', '::text)  AS paid_specs, sp.user_id AS uid
                        FROM spec_paid_choise AS sp
                    LEFT JOIN professions AS p ON p.id = prof_id
	                    GROUP BY sp.user_id) AS p_spec ON p_spec.uid = u.uid  
                    
                    {$inner_sql}
                    WHERE
                      u.uid IN (".implode(", ", $frl_ids).") 
                        AND 
                      u.is_banned = '0' 
                      {$filter_sql}
                    ORDER BY u.is_pro DESC, $order_by_spec_orign s.rating DESC, u.uid  
                    LIMIT {$limit} OFFSET {$offset}";
            //echo "<pre>".$sql;
            $this->results = $DB->rows($sql);
            if($this->results) {
                foreach($this->results as $row) $filter_frl_ids[] = $row['id'];
                
                if(count($filter_frl_ids) > 0) {
                    $this->works = $this->getUsersWorks($filter_frl_ids);
                }
            }
        } else {
            $this->results = $this->getRecords('is_pro DESC, rating DESC, id');
            if($this->results) {
                foreach($this->results as $row) $frl_ids[] = $row['id'];
                if(count($frl_ids) > 0) {
                    $this->works   = $this->getUsersWorks($frl_ids);
                }
            }
        }
    }
    
    /**
     * Взять информацию по найденным результатам
     *
     * @return array
     */
    function getRecords($order_by = NULL) {
        if ($this->matches && $this->active_search) {
            $sql = "{$set_sql}SELECT * FROM search_users_match WHERE id IN (" . implode(', ', $this->matches) . ')';
            if($order_by)
                $sql .= " ORDER BY {$order_by}";
            else if($this->_sortby && (($desc=$this->_sort==SPH_SORT_ATTR_DESC) || $this->_sort==SPH_SORT_ATTR_ASC))
                $sql .= " ORDER BY {$this->_sortby}".($desc ? ' DESC' : '');
            if($res = pg_query(DBConnect(), $sql))
                return pg_fetch_all($res);
        }
        return NULL;
    }
    
    public function getUsersWorks($users) {
        global $DB;
        
        $sql = "SELECT p.id, p.user_id, p.name, p.descr, p.pict, p.prev_pict, p.show_preview, p.norder, p.prev_type, p.is_video
               FROM portfolio p
             INNER JOIN
               portf_choise pc
                 ON pc.user_id = p.user_id
                AND pc.prof_id = p.prof_id 
             INNER JOIN freelancer f ON f.uid = p.user_id AND substring(f.tabs::text from 1 for 1)::integer = 1
              WHERE p.user_id IN (".implode(',', $users).")
                AND p.prof_id = f.spec_orig
                AND p.first3 = true
              ORDER BY p.norder";
            
        $ret  = $DB->rows($sql);

        if($ret)
            foreach ($ret as $row) $works[$row['user_id']][] = $row;    
       
        return $works;
    }
    
	/**
	 */
    public function setHtml() {
        global $session;
        $html = array();
        if ($result = $this->getRecords('is_pro DESC, id')) {
            foreach($result as $key => $value) {
                list ($login, $uname, $usurname, $spec_text, $country_name, $city_name, $skype, $resume, $konk, $site, $compname, $email) = $this->mark(array(
                    (string) $value['login'],
                    (string) $value['uname'],
                    (string) $value['usurname'],
                    (string) $value['spec_text'],
                    (string) $value['country_name'],
                    (string) $value['city_name'],
                    (string) $value['skype'],
                    (string) $value['resume'],
                    (string) $value['konk'],
                    (string) $value['site'],
                    (string) $value['compname'],
                    (string) $value['email']
                ));
                $html[$key]  = '<table cellpadding="0" cellspacing="0">';
                $html[$key] .= '<tr>';

                $html[$key] .= '<td style="vertical-align: top; padding-right: 8px;">';
                $html[$key] .= '<div class="upic">' . view_avatar($value['login'], $value['photo']) . '</div>';
                $html[$key] .= '</td>';

                $html[$key] .= '<td style="vertical-align: top;">';
                $html[$key] .= view_mark_user($value);
                $html[$key] .= $session->view_online_status($value['login']);
                //if ($value['is_pro'] == 't') $html[$key] .= (is_emp($value['role']) ? view_pro_emp() : view_pro2($value['is_pro_test']=='t'));
                $cls = is_emp($value['role']) ? 'class="empname11"' : 'class="frlname11"';
                $html[$key] .= '&nbsp;<font '.$cls.'><a href="/users/' . $value['login'] . '" title="' . $value['uname'] . " " . $value['usurname'] . '" '.$cls.' >' . $uname . " " . $usurname . '</a> [<a href="/users/' . $value['login'] . '/" title="' . $value['login'] . '" '.$cls.'>' . $login . '</a>]</font>';
                if($value['name_prof'] != 'Нет специализации' && $value['name_prof'] != '') {
                	 $html[$key] .= '<div style="margin-top: 4px;">Специализация: ' . $value['name_prof'] . '</div>';
                }
                if ($spec_text != '') {
                    $html[$key] .= '<div style="margin-top: 4px;">Условия сотрудничества:<br />' . $spec_text . '</div>';
                }

                if($city_name) {
                    $html[$key] .= '<div style="margin-top: 4px;">Местоположение: '.$country_name.", ".$city_name.'</div>';
                }

                if($skype != '') {
                	$html[$key] .= '<div style="margin-top: 4px;">Skype: '.$skype.'</div>';
                }

                if($site != '') {
                    $html[$key] .= '<div style="margin-top: 4px;">Сайт: ' . $site . '</div>';
                }

                if($compname != '') {
                    $html[$key] .= '<div style="margin-top: 4px;">Компания: ' . $compname . '</div>';
                }

                if($email != '') {
                    $html[$key] .= '<div style="margin-top: 4px;">E-mail: ' . $email . '</div>';
                }

                if($resume != '') {
                	$html[$key] .= '<div style="margin-top: 4px;">Резюме:<br />' . $resume . '</div>';
                }

                if($konk != '' && isset($value['blocks'][1]) && $value['blocks'][1]) {
                	$html[$key] .= '<div style="margin-top: 4px;">Участие в конкурсах:<br />' . $konk . '</div>';
                }



                $html[$key] .= '</td>';

                $html[$key] .= '</tr>';
                $html[$key] .= '</table>';
            }
        }
        $this->html = $html;
    }
}
