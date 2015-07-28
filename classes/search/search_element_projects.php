<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/search/search_element.php";
/**
 * Тестовый класс для поиска по проектам  http://beta.free-lance.ru/mantis/view.php?id=14689
 *
 */
class searchElementProjects extends searchElement
{
    public $name = 'Проекты';
    protected $_limit = 5;
    protected $_indexSfx = '_test';

    public function setResults() {
        $result = $this->getRecords();
        $this->results = $result;
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
            $this->setSelectFilter($filter); //http://beta.free-lance.ru/mantis/view.php?id=14689
        }
        $this->setResult($this->_engine->Query($string, implode(';',$this->_indexes)));
        $this->setWords($string);
    }
     
	/**
     * Взять информацию по найденным результатам
     *
     * @return array
     */
    function getRecords($order_by = NULL) {
        if ($this->matches && $this->active_search) {
            $sql = "SELECT * FROM search_projects WHERE id IN (" . implode(', ', $this->matches) . ')';
            if($order_by)
                $sql .= " ORDER BY {$order_by}";
            else if($this->_sortby && (($desc=$this->_sort==SPH_SORT_ATTR_DESC) || $this->_sort==SPH_SORT_ATTR_ASC))
                $sql .= " ORDER BY {$this->_sortby}".($desc ? ' DESC' : '');
            if($res = pg_query(DBConnect(), $sql))
                return pg_fetch_all($res);
        }
        return NULL;
    }
    
    public function setSelectFilter($filter) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
        
        $project_exRates = project_exrates::GetAll();
        $set_select[] = "*";
        // Мои специализации
        if ($filter['my_specs'] == 't' && $filter['user_specs']) {
            foreach($filter['user_specs'] as $spec) {
                $select[] = "(prj_subcategory1 = {$spec} OR prj_subcategory2 = {$spec} OR prj_subcategory3 = {$spec})"; 
            }
            $set_select[] = "IF(".implode(" OR ", $select).", 1, 0) as my_spec_filter";
            unset($select);
        }   
        
        //Если не нужны завершенные конкурсы
        if($filter['is_closed_contest']) {
            $set_select[] = "IF(NOT end_date OR end_date > NOW(), 1, 0) as closed_contest";
        }
        
        // Бюджет
        if($filter['cost_from'] || $filter['cost_to']) {
            $cr = (int)$filter['currency'];
            $cex = array(2,3,4,1); 
            
            $cost_from = (($cost_from = (float)$filter['cost_from']) < 0)?0:(float)$filter['cost_from'];
            $cost_to   = (($cost_to = (float)$filter['cost_to']) < 0)?0:(float)$filter['cost_to'];
            $cost_to   = ($cost_to < $cost_from && $cost_to != 0)?$cost_from:$cost_to;
            
            if($cost_to || $cost_from) {
                for($i=0;$i<4;$i++) {
                    $exfr = round($cost_from * $project_exRates[$cex[$cr].$cex[$i]],4);
                    $exto = round($cost_to * $project_exRates[$cex[$cr].$cex[$i]],4);
                    $fSql .= ($i ? ' OR ' : '')."(p.currency = {$i} AND p.cost >= {$exfr}".($cost_to ? " AND p.cost <= {$exto}" : '').')';
                    $select[] = "(currency = {$i} AND cost >= {$exfr}".($cost_to ? " AND cost <= {$exto}" : '').")";
                }
                if($filter['wo_cost']=='t') {
                    $select[] = '(cost = 0)';
                } 
                $set_select[] = "IF(".implode(" OR ", $select).", 1, 0) as cost_filter";
                unset($select);
            }
        } elseif($filter['cost_from'] === '0' && $filter['cost_to'] === '0') {
            $set_select[] = "IF(cost = 0, 1, 0) as cost_filter";
        }  else {
            $set_select[] = "IF(cost = 0 OR cost > 0, 1, 0) as cost_filter";
        }
        
        // Разделы/Подразделы
        if($filter['categories'] && $filter['my_specs'] == 'f') {
            $categories = array();
            for ($ci=0; $ci<2; $ci++) {
                if (sizeof($filter['categories'][$ci])) {
                    foreach($filter['categories'][$ci] as $ckey => $cvalue) {
                        $categories[$ci][] = (int)$ckey;
                    }
                }
            }
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
            
            foreach($aProf as $prof) {
                $select[] = "(prj_subcategory1 = {$prof} OR prj_subcategory2 = {$prof} OR prj_subcategory3 = {$prof})";
            }
            
            if(sizeof($categories[0])) {
                foreach($categories[0] as $cat) {
                    $select[] = "(prj_category1 = {$cat} OR prj_category2 = {$cat} OR prj_category3 = {$cat})";    
                }
            }
            
            $set_select[] = "IF(".implode(" OR ", $select).", 1, 0) as category_filter";
            unset($select);
        }
        
        
        $this->_engine->setSelect(implode(", ", $set_select));
        $this->_engine->setFilter("cost_filter", array(1));
        if ($filter['is_closed_contest']) $this->_engine->setFilter("closed_contest", array(1));
        if ($filter['only_sbr'] == 't') $this->_engine->setFilter("prefer_sbr", array(1));
        if ($filter['my_specs'] == 't' && $filter['user_specs']) $this->_engine->setFilter("my_spec_filter", array(1));
        if ($filter['categories'] && $filter['my_specs'] == 'f') $this->_engine->setFilter("category_filter", array(1));
        if ($filter['country']) $this->_engine->setFilter("country", array($filter['country']));
        if ($filter['city']) $this->_engine->setFilter("city", array($filter['city']));
    }
}
