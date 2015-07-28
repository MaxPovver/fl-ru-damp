<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/search/search_element_users.php";

/**
 * Класс для поиска по пользователям
 *
 */
class searchElementUsers_ext extends searchElementUsers
{

    function setIndexes() {
        if(!$this->_indexes) {
            $this->_indexes[0] = $this->_indexPfx.'users'.$this->_indexSfx;
            $this->_indexes[1] = 'delta_'.$this->_indexes[0];
        }
    }
    
    
    function setResults() 
    {
        global $DB;
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
        
        if(($filter = $this->isAdvanced())) {

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
            $prof = $filter['prof'];
            unset($filter['prof']);
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
                      u.uid as uid, 
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
                      s.cost_hour AS frl_cost_hour, 
                      s.cost_type_hour as frl_cost_type_hour,
                      s.in_office, 
                      s.exp, 
                      s.tabs,
                      
                      uc.sbr_opi_plus, 
                      uc.sbr_opi_minus, 
                      uc.sbr_opi_null,
                      
                      pc.cost_from, 
                      pc.cost_to, 
                      pc.cost_1000, 
                      pc.cost_type, 
                      uc.ops_emp_null + uc.ops_frl_null AS se, 
                      uc.ops_emp_plus + uc.ops_frl_plus AS sg, 
                      uc.ops_emp_minus + uc.ops_frl_minus AS sl, 
                      s.cost_type_hour, 
                      s.cost_type_month, 
                      rating_get(s.rating, s.is_pro, s.is_verify, s.is_profi) as t_rating,
                      (add_spec.additional_specs || ', ' || COALESCE(p_spec.paid_specs, '')) AS additional_specs,
                      -- get_additional_specs_as_string(u.uid) AS additional_spec,
                      
                      (uc.paid_advices_cnt + uc.sbr_opi_plus + uc.tu_orders_plus + uc.projects_fb_plus) AS total_opi_plus,
                      (uc.sbr_opi_null) AS total_opi_null,
                      (uc.sbr_opi_minus + uc.tu_orders_minus + uc.projects_fb_minus) AS total_opi_minus,
                      
                      COALESCE(smeta.completed_cnt, 0) + COALESCE(uc.reserves_completed_cnt, 0) AS completed_cnt -- старые БС + новые БС
                      
                    FROM users u
                    LEFT JOIN freelancer s ON s.uid = u.uid
                    LEFT JOIN portf_choise pc ON ( pc.prof_id = s.spec_orig AND pc.user_id = s.uid )
                    LEFT JOIN professions p ON p.id = s.spec AND p.id > 0
                    LEFT JOIN users_counters uc ON uc.user_id = u.uid
                    LEFT JOIN country ctr ON ctr.id = s.country AND ctr.id > 0
                    LEFT JOIN city ct ON ct.id = s.city AND ct.id > 0
                    LEFT JOIN sbr_meta AS smeta ON smeta.user_id = u.uid -- старые БС
                    LEFT JOIN 
                    ( 
                        SELECT 
                            array_to_string(array_agg(p.name), ', '::text) AS additional_specs, 
                            sa.user_id AS uid
                        FROM spec_add_choise AS sa
                        LEFT JOIN professions AS p ON p.id = prof_id
	                GROUP BY sa.user_id
                    ) AS add_spec ON add_spec.uid = u.uid
                    LEFT JOIN 
                    ( 
                        SELECT 
                            array_to_string(array_agg(p.name), ', '::text) AS paid_specs, 
                            sp.user_id AS uid
                        FROM spec_paid_choise AS sp
                        LEFT JOIN professions AS p ON p.id = prof_id
	                GROUP BY sp.user_id
                    ) AS p_spec ON p_spec.uid = u.uid  
                    
                    {$inner_sql}
                    WHERE
                      u.uid IN (".implode(", ", $frl_ids).") AND 
                      u.is_banned = '0' 
                      {$filter_sql}
                    ORDER BY u.is_pro DESC, $order_by_spec_orign s.rating DESC, u.uid  
                    LIMIT {$limit} OFFSET {$offset}";
                    
            //echo "<pre>".$sql;
                    
            $this->results = $DB->rows($sql);
        } else {
            $this->results = $this->getRecords('is_pro DESC, rating DESC, id');
        }
        
        if ($this->results) {
            
            $frl_ids_map = array();
            $tu_frl_ids = array();
            
            foreach ($this->results as $key => $row) {
                $frl_ids[] = $row['uid'];
                $frl_ids_map[$row['uid']] = $key;
                
                //Если вкладка ТУ выключена то сразу исключаем такие UID
                if (substr($row['tabs'], 7, 1) == 1) {
                    $tu_frl_ids[$key] = $row['uid'];
                }                
            }


            
            $uid = isset($_SESSION["uid"])?$_SESSION["uid"]:0;
            
            $is_spec = isset($prof[0]);
            $categoty_id = $is_spec?key($prof[0]):0;
            $prof_id = !$is_spec && isset($prof[1])?key($prof[1]):0;
            
            
            
            //print_r($current_categoty_id);
            //exit;
            
            
            //Получение пользовательский превью работ/услуг
            require_once(ABS_PATH . '/freelancers/widgets/FreelancersPreviewWidget.php');
            require_once(ABS_PATH . '/freelancers/models/FreelancersPreviewModel.php');

            $freelancersPreviewModel = new FreelancersPreviewModel();
            $list = $freelancersPreviewModel->getListByUids(
                        $frl_ids,
                        $categoty_id,
                        $prof_id);

            $tmp_tu_uids = $tu_frl_ids;
            foreach ($list as $item) {
                //Если отключена вкладка ТУ то их исключаем
                if (!$item || ($item->type == FreelancersPreviewModel::TYPE_TU && 
                    !in_array($item->user_id, $tmp_tu_uids))) {
                        continue;
                }

                //Инициализируем данные юзера в работе/услуге пока только логин нужен
                $key = $frl_ids_map[$item->user_id];
                $item->setUser(array('login' => $this->results[$key]['login']));

                //Инитим виджет если его нет
                if (!isset($this->results[$key]['preview'])) {
                    $this->results[$key]['preview'] = new FreelancersPreviewWidget();
                }

                //Добавляем работу в виджет
                $this->results[$key]['preview']->addItem($item);

                //Исключаем из дальнейшей обработки
                unset($frl_ids[$key], $tu_frl_ids[$key]);
            }            
            
            

            //------------------------------------------------------------------
            
            
            $this->works = null;
            if ($frl_ids) {
                $this->works = $this->getUsersWorks($frl_ids, $uid);
            }
            
            //------------------------------------------------------------------
        
            
            //Если у пользователя не отображатся портфолио 
            //то можно показать 3 последнии ТУ
            $exist_uids = ($this->works)? array_keys($this->works) : array();
            $tu_uids = array_diff($frl_ids, $exist_uids);

            if ($tu_uids) {
                require_once(ABS_PATH . '/tu/models/TServiceModel.php');

                $tserviceModel = new TServiceModel();
                if($list = $tserviceModel->getListByUids(
                        $tu_uids, 3,
                        freelancer::CATALOG_PORTFOLIO_MEM_LIFE)) {

                    $current_user_tu_ids = array();
                    
                    foreach ($list as $item) {
                        $key = $frl_ids_map[$item['user_id']];
                        $item['login'] = $this->results[$key]['login'];
                        $this->results[$key]['tservices'][] = $item;
                        
                        if ($item['user_id'] == $uid) {
                            $current_user_tu_ids[] = $item['id'];
                        }                         
                    }
                    
                    if (!empty($current_user_tu_ids)) {
                        FreelancersPreviewModel::setExistPreviewData(
                            FreelancersPreviewModel::TYPE_TU, $current_user_tu_ids);                    
                    }                    
                }
            }
        }
        
        
    }
 
    
    public function getUsersWorks($users, $uid) 
    {
        global $DB;
        
        require_once(ABS_PATH . '/freelancers/models/FreelancersPreviewModel.php');
        
        
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
        $works = null;
        
        if($ret) {
            $current_user_pf_ids = array();
            
            foreach ($ret as $row)  {
               $works[$row['user_id']][] = $row;   
                
               if ($row['user_id'] == $uid) {
                   $current_user_pf_ids[] = $row['id'];
               }                
            }
            
           if (!empty($current_user_pf_ids)) {
               FreelancersPreviewModel::setExistPreviewData(
                       FreelancersPreviewModel::TYPE_PF, $current_user_pf_ids);
           }            
        }
        
        return $works;
    }    
    
    
}