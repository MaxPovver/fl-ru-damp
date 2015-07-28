<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/search/search_element.php";
/**
 * Класс для поиска по заметкам
 *
 */
class searchElementNotes extends searchElement
{

    public $name = 'Личные заметки';
    public $totalwords = array('заметка', 'заметки', 'заметок');
    protected $_sort = SPH_SORT_RELEVANCE;
    public $layout = self::LAYOUT_ROW;


    function setEngine() {
        $this->_filtersV[0] = array('attr'=> 'from_id', 'values'=>array($this->_engine->uid));
        parent::setEngine();
    }

    function isAllowed() {
        return ($this->_engine->uid > 0);
    }
    
    function setResults() {
        $result = $this->getRecords();
        $this->results = $result;
    }
    
    /**
     * Взять информацию по найденным результатам
     *
     * @return array
     */
    function getRecords($order_by = NULL) {
        if ($this->matches && $this->active_search) {
            $plproxy = new DB('plproxy');
            $uid = get_uid();
            $sql = "SELECT * FROM get_search_notes({$uid}, ARRAY[" . implode(', ', $this->matches) . '])';
            if($order_by)
                $sql .= " ORDER BY {$order_by}";
            else if($this->_sortby && (($desc=$this->_sort==SPH_SORT_ATTR_DESC) || $this->_sort==SPH_SORT_ATTR_ASC))
                $sql .= " ORDER BY {$this->_sortby}".($desc ? ' DESC' : '');
            
            return $plproxy->rows($sql);
        }
        return NULL;
    }

	/**
	 */
    public function setHtml() {
        global $session;
        $html = array();
        if ($result = $this->getRecords()) {
            foreach($result as $key => $value) {
                list ($text, $login, $uname, $usurname) = $this->mark(array(
                    (string) $value['n_text'],
                    (string) $value['login'],
                    (string) $value['uname'],
                    (string) $value['usurname']
                ));
                $html[$key]  = '<table cellpadding="0" cellspacing="0" style="width: 100%;">';
                $html[$key] .= '<tr>';

                $html[$key] .= '<td style="vertical-align: top; padding-right: 8px; width: 50px;">';
                $html[$key] .= '<div class="upic">' . view_avatar($value['login'], $value['photosm']) . '</div>';
                $html[$key] .= '</td>';

                $html[$key] .= '<td style="vertical-align: top">';
                $html[$key] .= view_mark_user($value);
                $html[$key] .= $session->view_online_status($value['login']);
                //if ($value['is_pro'] == 't') $html[$key] .= (is_emp($value['role']) ? view_pro_emp() : view_pro2($value['is_pro_test']=='t'));
                $cls = is_emp($value['role']) ? 'class="empname11"' : 'class="frlname11"';
                $html[$key] .= '&nbsp;<font '.$cls.'><a href="/users/' . $value['login'] . '" title="' . $value['uname'] . " " . $value['usurname'] . '" '.$cls.' >' . $uname . " " . $usurname . '</a> [<a href="/users/' . $value['login'] . '/" title="' . $value['login'] . '" '.$cls.'>' . $login . '</a>]</font>';
                $html[$key] .= '</td>';

                $html[$key] .= '<td style="width: 180px;height: 64px;" class="note_' . (is_emp($value['role']) ? 'emp' : 'frl') . '">';
                $html[$key] .= $text;
                $html[$key] .= '</td>';

                $html[$key] .= '</tr>';
                $html[$key] .= '</table>';
            }
        }
        $this->html = $html;
    }
}
