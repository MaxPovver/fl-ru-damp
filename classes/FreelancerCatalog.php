<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");

class FreelancerCatalog {

    const CATALOG_MEM_TAG               = 'freelancer_getCatalog';
    const CATALOG_GROUP_MEM_TAG         = 'freelancer_getCatalog_GroupId_%s';
    const CATALOG_PROFF_MEM_TAG         = 'freelancer_getCatalog_ProfId%s';
    const CATALOG_MEM_LIFE              = 1800;
    const CATALOG_PORTFOLIO_MEM_LIFE    = 600;

    const CATALOG_PROFI_MEM_TAG         = 'freelancer_getProfiCatalog';
    const CATALOG_PROFI_MEM_LIFE        = 3600;//1 час
    
    private $TABLE_COUNTERS = 'freelancer_catalog_counters';
    
    const WHERE_BASE = "WHERE f.is_active 
            AND f.is_banned = '0' 
            AND (f.cat_show = 't' OR f.is_pro = 'f')
            AND f.last_time > now() - '6 months'::interval
            ";
    const ORDER_BASE = "f.is_pro DESC, f.rating_displayed DESC";
    
    //Экземпляр БД 
    private $db;
    
    // Код сортировки
    private $order;
    
    //Направление сортировки
    private $direction;

    //Массив uid закрепленных юзеров
    private $bind_ids;
    
    //Массив uid юзеров для отображения
    private $uids;
    
    private $prof_group_id;
    private $prof_id;
    
    private $order_by_pc_field;
    
    private $prof_list;
        
    
    public function __construct()
    {
        global $DB;
        $this->db = $DB;
        
    }
    
    /**
     * Инициализирует переменные класса
     * @param int prof_group_id ИД Раздела
     * @param int prof_id ИД Подраздела
     * @param int $page Страница
     * @param string $order Сортировка. Допустимы: "sbr", "ops", "pph", "ppp", "pp1", "ppm", "gnr"
     * @param type $direction
     */
    public function initSearch($prof_group_id, $prof_id, $page = 1, $order = "gnr", $direction = 0)
    {
        $this->order = $order;
        $this->direction = $direction;
        $this->prof_group_id = $prof_group_id;
        $this->prof_id = $prof_id;        
        
        $this->bind_ids = $this->getBindIds();
        
        $this->count = $this->getCounter();
        
        $this->initList($page);
    }
    
    
    /**
     * Проверяет, удалось ли заполнить данные
     * @return bool
     */
    public function isFound()
    {
        return count($this->uids) > 0;
    }
    
    private function getOriginProf()
    {
        if (!$this->or_prof) {
            $this->or_prof = professions::GetProfessionOrigin($this->prof_id);
        }
        return $this->or_prof;
    }
    
    /**
     * Формирует строку сортировки
     */
    private function getSorting()
    {
        $order = "";
        
        $dir_sql = (!$this->direction ? 'DESC' : 'ASC');
        
        switch($this->order)
        {
            case "ops":
                //Это запишем сразу в таблицу freelancer и замутим индекс
                //zin(uc.ops_emp_plus)-zin(uc.ops_emp_minus) as osum,
                $order .= "f.osum {$dir_sql}, s.rating_displayed {$dir_sql}";
                $this->order_fields = 'f.osum';
                break;
            case "sbr":
                //Это запишем сразу в таблицу freelancer и замутим индекс
                //zin(uc.sbr_opi_plus + uc.tu_orders_plus + uc.projects_fb_plus)-zin(uc.sbr_opi_minus + uc.tu_orders_minus + uc.projects_fb_minus) as ssum, 
                $order .= "f.ssum {$dir_sql}, s.rating_displayed {$dir_sql}";
                $this->order_fields = 'f.ssum';
                break;
            case "pph":
                $order .= "pc.cost_hour_rur {$dir_sql} NULLS LAST, f.rating_displayed DESC";
                $this->order_by_pc_field = true;
                $this->order_fields = 'pc.cost_hour_rur';
                break;
            case "ppm":
                $order .= "f.cost_month_rur > 0 DESC NULLS LAST, f.cost_month_rur {$dir_sql} NULLS LAST, f.rating_displayed DESC";
                $this->order_fields = 'f.cost_month_rur';
                break;
            case "ppp":
                $order .= "pc.cost_project_rur {$dir_sql}, f.rating_displayed DESC";
                $this->order_by_pc_field = true;
                $this->order_fields = 'pc.cost_project_rur';
                break;
            case "pp1":
                $order .= "pc.cost_1000_rur {$dir_sql}, f.rating_displayed DESC";
                $this->order_by_pc_field = true;
                $this->order_fields = 'pc.cost_1000_rur';
                break;
            case "gnr":
            default:
                $order .= "f.is_pro DESC, f.rating_displayed {$dir_sql}";
                $this->order_fields = 'f.is_pro';
                break;
        }

        return $order;
    }
    
    private function getWhere()
    {
        $where = self::WHERE_BASE;
        
        if ($this->prof_group_id) {
            //Раздел
            $profs = professions::getProfIdForGroups($this->prof_group_id, true);
            if ($profs) {
                $this->prof_list = $profs;
                $where .= " AND f.spec_orig IN ({$profs})";
            }
        }
        
        if (count($this->bind_ids)) {
            $where .= $this->db->parse(" AND f.uid NOT IN (?l)", $this->bind_ids);
        }
        
        return $where;
    }
    
    /**
     * Получить закрепленные в текущей категории
     * @todo Профессия должна быть также указана у пользователя (он мог ее сменить)
     */
    private function getBindIds()
    {
        $this->bind_is_spec = $this->prof_id > 0;
        
        $profs = "";
        if ($this->bind_is_spec) {
            $this->bind_prof_use = $this->getOriginProf();
            $profs = $this->bind_prof_use;
        } else {
            $this->bind_prof_use = $this->prof_group_id;
            if ($this->bind_prof_use) {
                $profs = professions::getProfIdForGroups($this->prof_group_id, true);
            }
        }

        $sql = "SELECT f.uid 
            FROM freelancer AS f
            INNER JOIN freelancer_binds fb ON fb.user_id = f.uid AND fb.prof_id = ?i AND fb.is_spec = ?b
            ".($profs ? "LEFT JOIN spec_add_choise sp ON sp.user_id = f.uid AND sp.prof_id IN ({$profs})" : "")."
            ".self::WHERE_BASE." AND fb.date_stop > NOW()
            ".($profs ? "AND (f.spec_orig IN ({$profs}) OR (sp.prof_id::integer > 0 AND f.is_pro = TRUE))" : "")."
            ORDER BY fb.date_start DESC, f.is_pro DESC";
        $uids = $this->db->cache(300)->col($sql, $this->bind_prof_use, $this->bind_is_spec);

        return array_unique($uids);
    }

    /**
     * Ищет uid пользователей по условию и сохраняет результат в поле $this->uids
     * @param type $page
     */
    public function initList($page = 1) {
        $offset = FRL_PP * ($page - 1);
        $display_binds = array_slice($this->bind_ids, $offset, FRL_PP);
        
        $limit = FRL_PP - count($display_binds);
        
        if ($limit > 0) {
            
            $count_binds = count($this->bind_ids);
            $offset = $offset - ($count_binds - count($display_binds));
        
            $join = "";
            $sort = $this->getSorting();
            $where = $this->getWhere();
        
            if ($this->pc_field_not_null) {
                $join .= " LEFT JOIN portf_choise pc ON pc.prof_id = f.spec_orig AND pc.user_id = f.uid";
            }
            
            if ($this->prof_id) {
                $sql = "SELECT f.uid 
                    FROM 
                    ((SELECT f.uid, TRUE as is_main_spec, f.rating_displayed".($this->order_fields ? ', '.$this->order_fields : '')."
                    FROM freelancer AS f
                        {$join}
                        {$where} AND f.spec_orig = '{$this->getOriginProf()}'
                        ORDER BY {$sort}
                    )

                    UNION ALL

                    (SELECT f.uid, FALSE as is_main_spec, f.rating_displayed".($this->order_fields ? ', '.$this->order_fields : '')."
                        FROM freelancer AS f
                        {$join}
                        INNER JOIN spec_add_choise sp ON sp.user_id = f.uid AND sp.prof_id = '{$this->getOriginProf()}'
                        {$where} AND f.is_pro = TRUE
                        GROUP BY f.uid
                        ORDER BY {$sort}
                    )) AS f
                    ORDER BY is_main_spec DESC, {$sort}
                    LIMIT ?i OFFSET ?i";
                    
                $uids = $this->db->cache(300)->col($sql, $limit, $offset); 
                
            } else {
                $sql = "SELECT f.uid 
                    FROM freelancer AS f
                    {$join}
                    {$where} 
                    ORDER BY {$sort}
                    LIMIT ?i OFFSET ?i";

                $uids = $this->db->cache(300)->col($sql, $limit, $offset); 
                
            }
            print_r($this->db->parse($sql, $limit, $offset));
        }
        
        $this->uids = array_merge($display_binds, $uids);
        
    }
    
    /**
     * Получает все необходимые данные по массиву uids
     * @return array
     */
    public function getUsers()
    {
        $users = array();
        if ($this->isFound()) {
            $sql = "SELECT f.*, fb.id > 0 AS is_binded,
                spec_orig = '{$this->getOriginProf()}' as its_his_main_spec,
                (COALESCE(pc.cost_hour, 0) = 0) as cost_hour_is_0, 
                pc.cost_hour AS frl_cost_hour, pc.cost_type_hour AS frl_cost_type_hour, 
                pc.cost_from, pc.cost_type, 
                pc.cost_1000,
                p.name as profname,
                rating_get(f.rating, f.is_pro, f.is_verify, f.is_profi) as t_rating,
                
                uc.ops_emp_plus + uc.ops_frl_plus as sg, 
                uc.ops_emp_minus + uc.ops_frl_minus as sl, 
                uc.ops_emp_null + uc.ops_frl_null as se,

                (uc.paid_advices_cnt + uc.sbr_opi_plus + uc.tu_orders_plus + uc.projects_fb_plus) AS total_opi_plus, 
                (uc.sbr_opi_null) AS total_opi_null,
                (uc.sbr_opi_minus + uc.tu_orders_minus + uc.projects_fb_minus) AS total_opi_minus
          
                FROM freelancer AS f
                LEFT JOIN freelancer_binds fb 
                    ON fb.user_id = f.uid AND fb.prof_id = ?i AND fb.is_spec = ?b AND fb.date_stop > NOW()
                LEFT JOIN professions p ON p.id = f.spec 
                LEFT JOIN portf_choise pc ON pc.prof_id = f.spec_orig AND pc.user_id = f.uid
                LEFT JOIN users_counters uc ON uc.user_id = f.uid
                WHERE uid IN (?l)";
            $rows = $this->db->cache(300)->rows($sql, $this->bind_prof_use, $this->bind_is_spec, $this->uids);
            foreach ($rows as $user) {
                $key = array_search($user['uid'], $this->uids);
                $users[$key] = $user;
            }
            ksort($users);
        }
        return $users;
    }
    
    /**
     * Получение данных для отображения портфолио
     * @return type
     */
    public function getWorks()
    {
        $works = array();
        $sql = "SELECT 
            p.id, p.user_id, p.name, p.descr, p.pict, p.prev_pict, p.show_preview, 
            p.norder, p.prev_type, p.is_video
            FROM portfolio p 
            INNER JOIN portf_choise pc ON pc.user_id = p.user_id AND pc.prof_id = p.prof_id 
            ".( $this->prof_id ? '' : 'INNER JOIN freelancer f ON f.uid = p.user_id' )."
            LEFT JOIN portfolio_blocked pb ON p.id = pb.src_id 
            WHERE p.user_id IN (".implode(',', $this->uids).")";

        if ($this->prof_list) {
            $sql .= " AND p.prof_id IN ({$this->prof_list})";
        } else {
            $sql .= " AND p.prof_id = ".($this->prof_id ? "'{$this->getOriginProf()}'" : 'f.spec_orig');            
        }
        $sql .= " AND p.first3 = true AND pb.src_id IS NULL ORDER BY p.user_id, p.norder";

        $ret = $this->db->cache(300)->rows($sql);
        if ($ret) {
            foreach ($ret as $row) {
               $works[$row['user_id']][] = $row;
            }
        }
        return $works;
    }
    
    /**
     * Общее количество найденных элементов. Необходимо для постраничной навигации
     * @todo Добовить реализацию метода
     * @return int
     */
    public function getPages($count = null)
    {
        if ($count === null) {
            $count = $this->count;
        }
        return ceil($count / FRL_PP);
    }
    
    /**
     * Получить массив тегов для подписи к кешу
     * 
     * @param type $prof_id
     * @param type $is_spec
     * @return type
     */
    public static function getCatalogMemTags($prof_id = 0, $is_spec = true)
    {
        $key = sprintf(($is_spec)?static::CATALOG_PROFF_MEM_TAG:static::CATALOG_GROUP_MEM_TAG, $prof_id);
        return array(static::CATALOG_MEM_TAG, $key);
    }
    
    private function getCounter()
    {
        $sql = "SELECT counter 
            FROM {$this->TABLE_COUNTERS} 
            WHERE prof_group_id = ?i AND profession_id = ?i";
        return (int) $this->db->val(
                $sql, 
                (int) $this->prof_group_id, 
                (int) $this->prof_id
            );
    }
    
    
    public function recalcCounters()
    {
        $sql = "SELECT COUNT(*) 
            FROM freelancer AS f 
            WHERE f.is_active AND f.is_banned = '0' 
                AND (f.cat_show = 't' OR f.is_pro = 'f') 
                AND f.last_time > now() - '6 months'::interval
        ";
        
        $professions = new professions();
        
        $prof_groups = $professions->getProfGroupIds();        
        foreach ($prof_groups as $group_id) {
            $profs_str = $professions->getProfIdForGroups($group_id, true);
            if ($profs_str) {
                $this->updateCounter($group_id, 0, $this->db->val($sql . " AND f.spec_orig IN ({$profs_str})"));
            }
            
        }
        
        $profs = $professions->getOriginProfsIds();
        foreach ($profs as $prof_id) {
            $this->updateCounter(0, $prof_id, $this->db->val($sql . " AND f.spec_orig = ?i", $prof_id));
        }
        
        $this->updateCounter(0, 0, $this->db->val($sql));
    }
    
    private function updateCounter($group_id, $prof_id, $counter)
    {
        $id = $this->db->val("SELECT id 
            FROM {$this->TABLE_COUNTERS} 
            WHERE prof_group_id = ?i AND profession_id = ?i", (int)$group_id, (int)$prof_id);
        
        if ($id > 0) {
            $this->db->update($this->TABLE_COUNTERS, array('counter' => $counter), 'id = ?i', $id);
        } else {
            $this->db->insert($this->TABLE_COUNTERS, array(
                'prof_group_id' => $group_id, 
                'profession_id' => $prof_id, 
                'counter' => $counter
            ));
        }
    }
}
