<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/search/search_element.php";

/**
 * Класс для поиска по пользователям с корректным поиском по расширенному фильтру
 *
 */
class searchElementUsers_test extends searchElement
{
    public $name = 'Люди';
    public $totalwords = array('человек', 'человека', 'людей');
    protected $_indexSfx = '';
    protected $_sort   = SPH_SORT_EXTENDED;
    protected $_sortby = 'is_pro DESC, rating DESC, @id';
    
    
    public function setResults() {
        $result = $this->getRecords();
        $this->results = $result;
        
        if($this->results) {
            foreach($this->results as $row) $filter_frl_ids[] = $row['id'];
            if(count($filter_frl_ids) > 0) {
                $this->works = $this->getUsersWorks($filter_frl_ids);
            }
        }
    }
    
    /**
     * Инициализирует параметры поиска данного элемента и производит поиск по заданной фразе.
     *
     * @param string $string   поисковая фраза.
     * @param integer $page   номер текущей страницы (используется при поиске по конкретному элементу).
     */
    function search($string, $page = 0, $filter=false) {
        if(!$this->isActive() || !$this->isAllowed()) return;
        $this->setPage($page);
        $this->setEngine();
        $this->setIndexes();
        $this->resetResult();
        if($filter) {
            $this->setSelectFilter($filter); //#0016532
        }
        $this->setResult($this->_engine->Query($string, implode(';',$this->_indexes)));
        $this->setWords($string);
        // Возвращаем все на место
        if($this->isAdvanced() !== false) {
            $this->_limit = $this->_advanced_limit;
        }
    }
    
    /**
     * Взять информацию по найденным результатам
     *
     * @return array
     */
    function getRecords($order_by = NULL) {
        if ( $this->matches ) {
            $sql = "SELECT * FROM search_users_test WHERE id IN (" . implode(', ', $this->matches) . ')';
            if ( $order_by ) {
                $sql .= " ORDER BY {$order_by}";
            } else if ( $this->_sortby && (($desc = ($this->_sort == SPH_SORT_ATTR_DESC)) || $this->_sort == SPH_SORT_ATTR_ASC) ) {
                $sql .= " ORDER BY {$this->_sortby}".($desc ? ' DESC' : '');
            }
            if ( $res = pg_query(DBConnect(), $sql) ) {
                if ( !$order_by && ($this->_sort == SPH_SORT_RELEVANCE || $this->_sort == SPH_SORT_EXTENDED) ) {
                    $links  = array();
                    $rows   = array();
                    while ( $row = pg_fetch_assoc($res) ) {
                        $links[ $row['id'] ] = $row;
                    }
                    for ( $i=0; $i<count($this->matches); $i++ ) {
                        $rows[] = $links[ $this->matches[$i] ];
                    }
                } else {
                    $rows = pg_fetch_all($res);
                }
                return $rows;
            }
        }
        return NULL;
    }
    
    public function setSelectFilter($filter) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/teams.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
        
        $project_exRates = project_exrates::GetAll();
        $set_select[] = "*";
        
        // Разделы/Подразделы
        if($filter['prof']) {
            if(count($filter['prof'][0])  > 0) $p1 = professions::getProfIdForGroups(array_keys($filter['prof'][0]), true);
            if(count($filter['prof'][1])  > 0) $p2 = professions::GetProfessionOrigin(implode(",", array_keys($filter['prof'][1])));
            $specs = explode(",", (($p2?$p2:"").(($p1 && $p2)?", ":"").($p1?$p1:"")));
            $this->_engine->setFilter("specs", $specs);
            $this->_sortby = 'is_pro DESC, spec_origin IN (' . implode(',', $specs) . ') rating DESC, @id';
        }
        
        //Стоимость
        if($filter['cost']) {
            foreach($filter['cost'] as $val) {
                if($val['cost_from'] || $val['cost_to']) {
                    switch($val['type_date']) {
                        default:
                        case 4: 
                            if($prof_id) {
                                $cf = 'pcost_hour'; $ct = 'pcost_type_hour';
                            } else {
                                $cf = 'cost_hour';  $ct = 'cost_type_hour'; 
                            }
                            break;
                        case 3: $cf = 'cost_from'; $ct = 'cost_type'; break;
                        case 1: $cf = 'cost_month';$ct = 'cost_type_month'; break;
                        case 2: $cf = 'cost_1000'; $ct = 'cost_type'; break;
                    }
                    $cr = (int)$val['cost_type'];
                    $cex = array(freelancer::USD=>project_exrates::USD, freelancer::EUR=>project_exrates::EUR, freelancer::RUR=>project_exrates::RUR, freelancer::FM=>project_exrates::FM);
                    if(($cost_from = (float)$val['cost_from']) < 0) $cost_from = 0;
                    if(($cost_to = (float)$val['cost_to']) < 0)     $cost_to = 0;
                    if($cost_to < $cost_from && $cost_to != 0)      $cost_to = $cost_from;
                    if($cost_to || $cost_from) {
                        $cost_sql = "";
                        for($i=0;$i<4;$i++) {
                            $exfr = round($cost_from * $project_exRates[$cex[$cr].$cex[$i]],4);
                            $exto = round($cost_to * $project_exRates[$cex[$cr].$cex[$i]],4);
                            $cost[] = "({$ct} = {$i} AND {$cf} >= {$exfr}".($cost_to ? " AND {$cf} <= {$exto}" : '') . ")";
                        }
                        $select[] = implode(" OR ", $cost);
                    }
                }
            }
            if($select) {
                $set_select[] = "IF( ( ".implode(" OR ", $select)." ), 1, 0) as cost_filter";
                unset($select);

                $this->_engine->setFilter("cost_filter", array(1));
            }
        }
        
        // Опыт работы
        if($filter['exp'][0] > 0 || $filter['exp'][1] > 0) {
            
            if($filter['exp'][1] == 0 && $filter['exp'][0] > 0) {
                $select[] = "( exp >= {$filter['exp'][0]} )";
            } else if($filter['exp'][1] > 0 && $filter['exp'][0] == 0) {
                $select[] = "( exp <= {$filter['exp'][1]} )";
            } else {
                $select[] = "( exp >= {$filter['exp'][0]} AND exp <= {$filter['exp'][1]} )";
            }
            
            $set_select[] = "IF( ( ".implode(" OR ", $select)." ), 1, 0) as exp_filter";
            unset($select);
            
            $this->_engine->setFilter("exp_filter", array(1));
        }
        
        //Возраст
        if($filter['age'][1] > 0 || $filter['age'][0] > 0) {
            $age_from  = $filter['age'][0];
            $age_to    = $filter['age'][1];
            
            if($age_to == 0 && $age_from > 0) {
                $select[] = "( age >= {$age_from} )";
            } else if($age_to > 0 && $age_from == 0) {
                $select[] = "( age <= {$age_to} )";
            } else {
                $select[] = "( age >= {$age_from} AND age <= {$age_to} )";
            }
            
            $set_select[] = "IF( ( ".implode(" OR ", $select)." ), 1, 0) as age_filter";
            unset($select);
            
            $this->_engine->setFilter("age_filter", array(1));
        }
        
        // Местоположение
        if ($filter['country']) $this->_engine->setFilter("country", array($filter['country']));
        if ($filter['city']) $this->_engine->setFilter("city", array($filter['city']));
        
        // Ищет работу в офисе
        if($filter['in_office']) {
            $this->_engine->setFilter("in_office", array(1));  
        }
        
        // У меня в избранных
        if($filter['in_fav']) {
            $teams = new teams;
            if($tt = $teams->teamsFavorites($uid, $error)) {
                foreach($tt as $t) {
                    $select[] = " ( uid = {$t['uid']} ) ";
                }
                
                $set_select[] = "IF(( ".implode(" OR ", $select)." ), 1, 0) as is_fav";
                unset($select);
                
                $this->_engine->setFilter("is_fav", array(1));
            }
        }
        
        //С PRO аккаунтом
        if($filter['is_pro']) {
            $this->_engine->setFilter("is_pro", array(1));   
        }
        
        //С положительными рекомендациями
        if($filter['sbr_is_positive']) {
            $select[] = '( sbr_opi_plus > 0 )';
            
            $set_select[] = "IF(( ".implode(" OR ", $select)." ), 1, 0) as sbr_positive_filter";
            unset($select);
            
            $this->_engine->setFilter("sbr_positive_filter", array(1));
        }
        
        //Без отрицательных рекомендаций
        if($filter['sbr_not_negative']) {
            $select[] = '( sbr_opi_minus = 0 )';
            
            $set_select[] = "IF(( ".implode(" OR ", $select)." ), 1, 0) as sbr_not_negative_filter";
            unset($select);
            
            $this->_engine->setFilter("sbr_not_negative_filter", array(1));
        }
        
        //С положительными мнениями
        /*if($filter['opi_is_positive']) {
            $select[] = '( ops_emp_plus > 0 )';
            
            $set_select[] = "IF(( ".implode(" OR ", $select)." ), 1, 0) as opi_positive_filter";
            unset($select);
            
            $this->_engine->setFilter("opi_positive_filter", array(1));
        }
        
        //Без отрицательных мнений
        if($filter['opi_not_negative']) {
            $select[] = '( ops_emp_minus = 0 )';
            
            $set_select[] = "IF(( ".implode(" OR ", $select)." ), 1, 0) as opi_not_negative_filter";
            unset($select);
            
            $this->_engine->setFilter("opi_not_negative_filter", array(1));
        }*/
        
        // Только с примерами работ
        if($filter['is_preview']) {
            $select[] = '( o_wrk_factor_a > 0 )';
            
            $set_select[] = "IF(( ".implode(" OR ", $select)." ), 1, 0) as preview_filter";
            unset($select);
            
            $this->_engine->setFilter("preview_filter", array(1));
        }
        
        // Только свободные
        if($filter['only_free']) {
            $select[] = '( status_type = 0)';
            
            $set_select[] = "IF(( ".implode(" OR ", $select)." ), 1, 0) as onlyfree_filter";
            unset($select);
            
            $this->_engine->setFilter("onlyfree_filter", array(1));
        }
        
        //С успешным СБР
        if($filter['success_sbr'][0]==1) {
            $select[] = "( sbr_sum > 0 )";
            for($i=1;$i<4;$i++) {
                if($filter['success_sbr'][$i] == 1)
                    $rank[] = "( rank = {$i} )";
            }
            if($rank) {
                $select[] = "( ". implode(" OR ", $rank). " )";
            }
            
            $set_select[] = "IF(( ".implode(" AND ", $select)." ), 1, 0) as sbr_rank_filter";
            unset($select);
            
            $this->_engine->setFilter("sbr_rank_filter", array(1));
        }
        
        if($set_select) $this->_engine->setSelect(implode(", ", $set_select));
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
}

?>