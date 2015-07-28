<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/search/search_element.php";

/**
 * Класс для простого поиска по имени пользователя (для комбобоксов с пользователями)
 *
 */
class searchElementUsers_simple extends searchElement
{
    protected $_indexSfx = '';
    protected $_mode = SPH_MATCH_EXTENDED;
    protected $_sort   = SPH_SORT_RELEVANCE;
    protected $_sortby = '';
    
    public function setResults() {
        $result = $this->getRecords();
        $this->results = $result;
    }
    
    
    /**
     * Инициализирует параметры поиска данного элемента и производит поиск по заданной фразе.
     *
     * @param string $string  поисковая фраза.
     * @param integer $page  номер текущей страницы (используется при поиске по конкретному элементу).
     * @param array $filter  фильтр
     * @param integer $llimit  количество записей которые нужно вернуть
     */
    function search($string, $page = 0, $filter=false, $limit = 5) {
        if( !$this->isActive() || !$this->isAllowed() ) {
            return;
        }
        $this->_limit = $limit;
        if ( !empty($filter['uids']) ) {
            $this->_filtersV[] = array('attr' => 'uid', 'values' => $filter['uids']);
        }
        if ( !empty($filter['nouids']) ) {
            $this->_filtersV[] = array('attr' => 'uid', 'values' => $filter['nouids'], 'exclude' => TRUE);
        }
        if ( isset($filter['utype']) ) {
            $this->_filtersV[] = array('attr' => 'is_emp', 'values' => array($filter['utype']));
        }
        $this->_engine->SetFieldWeights(array('login' => 40, 'usurname' => 30, 'uname' => 20));
        $this->setEngine();
        $this->setIndexes();
        $this->resetResult();
        $this->setResult($this->_engine->Query($string, implode(';',$this->_indexes)));
        $this->setWords($string);
    }
    
    
    /**
     * Взять информацию по найденным результатам
     *
     * @return array массив с пользователями
     */
    function getRecords($order_by = NULL) {
        if ( $this->matches ) {
            $sql = "SELECT * FROM search_users_simple WHERE id IN (" . implode(', ', $this->matches) . ')';
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
        return array();
    }
    

}

