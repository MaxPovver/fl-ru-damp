<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/search/search_element.php";
/**
 * Класс для поиска по сообщениям
 *
 */
class searchElementMessages extends searchElement
{
    public $name = 'Личные сообщения';
    public $totalwords = array('сообщение', 'сообщения', 'сообщений');


    function setEngine() {
        $this->_select      = "*, IF(to_id={$this->_engine->uid} OR from_id={$this->_engine->uid}, 1, 0) as __is_ok";
        $this->_filtersV[0] = array('attr'=> '__is_ok', 'values'=>array(1));
        parent::setEngine();
    }

    function isAllowed() {
        return ($this->_engine->uid > 0);
    }
    
    function setResults() {
        $result = $this->getRecords();
        $this->results = $result;
    }
    
    function setIndexes() {
        $this->_indexes = array('messages', 'messages_arc', 'delta_messages');
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
            $sql = "SELECT * FROM get_search_messages({$uid}, ARRAY[" . implode(', ', $this->matches) . '])';
            if($order_by)
                $sql .= " ORDER BY {$order_by}";
            else if($this->_sortby && (($desc=$this->_sort==SPH_SORT_ATTR_DESC) || $this->_sort==SPH_SORT_ATTR_ASC))
                $sql .= " ORDER BY {$this->_sortby}".($desc ? ' DESC' : '');
            
            return $plproxy->rows($sql);
        }
        return NULL;
    }

    public function setHtml() {
        global $session;
        $html = array();
        if ($result = $this->getRecords()) {
            foreach($result as $key => $value) {
                $pfx = $value['from_id'] == $this->_engine->uid ? 't_' : 'f_';

                list ($msg_text, $login, $uname, $usurname) = $this->mark(array(
                    (string) $value['msg_text'],
                    (string) $value[$pfx.'login'],
                    (string) $value[$pfx.'uname'],
                    (string) $value[$pfx.'usurname']
                ));
                $msg_text = preg_replace('~(https?:/){[^}]+}/~', '$1/', $msg_text);

                $html[$key]  = '<table cellpadding="0" cellspacing="0">';
                $html[$key] .= '<tr>';
                $html[$key] .= '<td style="vertical-align: top; padding-right: 8px;">';
                $html[$key] .= '<div class="upic">' . view_avatar($value[$pfx . 'login'], $value[$pfx . 'photo']) . '</div>';
                $html[$key] .= '</td>';

                $html[$key] .= '<td style="vertical-align: top;">';
                $html[$key] .= view_mark_user($value, $pfx);
                $html[$key] .= $session->view_online_status($value[$pfx . 'login']);
                //if ($value[$pfx . 'is_pro'] == 't') $html[$key] .= (is_emp($value[$pfx . 'role']) ? view_pro_emp() : view_pro2($value[$pfx . 'is_pro_test']=='t'));
                $cls = is_emp($value[$pfx . 'role']) ? 'class="empname11"' : 'class="frlname11"';
                $html[$key] .= '&nbsp;<font '.$cls.'><a href="/users/' . $value[$pfx . 'login'] . '" title="' . $value[$pfx . 'uname'] . " " . $value[$pfx . 'usurname'] . '" '.$cls.' >' . $uname . " " . $usurname . '</a> [<a href="/users/' . $value[$pfx . 'login'] . '/" title="' . $value[$pfx . 'login'] . '" '.$cls.'>' . $login . '</a>]</font>';
                if ($msg_text != '') {
                    $html[$key] .= '<div style="margin-top: 4px;"><a href="/contacts/?from='.$value[$pfx . 'login'].'">' . reformat($msg_text,80,0,1) . '</a></div>';
                }
                $html[$key] .= '</td>';

                $html[$key] .= '</tr>';
                $html[$key] .= '</table>';
            }
        }
        $this->html = $html;
    }
}
