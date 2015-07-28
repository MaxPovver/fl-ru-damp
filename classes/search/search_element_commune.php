<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/search/search_element.php";
/**
 * Класс для работы с поиском в Сообществах
 *
 */
class searchElementCommune extends searchElement
{
    public $name = 'Сообщества';
    //@todo: временно используем индекс commune2
    //после перегенерации на бое переключить на commune
    protected $_indexSfx = '2';
    
    public function setResults() 
    {
        $result = $this->getRecords();
        
        if ($result) {
            foreach ($result as $val) {
                if ($val['pfx'] == 'categories') {
                    $this->last_mess[$val['id']] = $this->getLastMessagesForCat($val['real_id']);
                }
            }
        }
        
        $this->results = $result;    
    }
    
    
    
    function setResult($result) 
    {
        if ($result && $result['total']) {
            $this->words = @implode(' ', @array_keys($result['words']));
            if ($result['matches']) {
                $this->matches = $result['matches'];
            }
            
            $this->total = $result['total'];
            $this->totalStr = ending(
                    (int)$result['total'], 
                    $this->totalwords[0], 
                    $this->totalwords[1], 
                    $this->totalwords[2]);
            $this->setResults();
        }
    }
    
    
    /**
     * Взять информацию по найденным результатам
     *
     * @return array
     */
    function getRecords() 
    {
        if ($this->matches && $this->active_search) {

            $where = array();
            foreach ($this->matches as $id => $match) {
                $where[$match['attrs']['type']][] = $id;
            }
            
            $_where = '';
            
            $i = 0;
            $cnt = count($where)-1;
            foreach ($where as $key => $value) {
                $_in = implode(', ', $value);
                $_where .= "(type = {$key} AND id IN({$_in})) ".(($i < $cnt)?"OR":"");
                $i++;
            }
            
            $sql = "SELECT * FROM search_{$this->_indexes[0]} WHERE {$_where}";

            if (!$res = pg_query(DBConnect(), $sql)) {
                return NULL;
            }

            $rows = pg_fetch_all($res);
            
            if (!$rows) {
                return NULL;
            }
            
            $data = array();
            foreach ($rows as $row) {
                $data[$row['id']] = $row;
            }
            
            $ids = array_keys($this->matches);
            $result = array();
            foreach ($ids as $id){
                if(!isset($data[$id])) {
                    continue;
                }
                
                $result[] = $data[$id];
            }
            
            return $result;
        }
        return NULL;
    }
    
    
    function getLastMessagesForCat($cat) 
    {
        global $DB;
        $sql = "SELECT t.category_id, m.title, m.id, t.commune_id FROM commune_themes t JOIN commune_messages m ON m.theme_id = t.id WHERE t.category_id = $cat ORDER BY m.created_time DESC LIMIT 3;";
        return $DB->rows($sql);
    }
    

    public function setHtml() 
    {
        $html = array();
        if ($result = $this->getRecords()) {
            foreach($result as $key => $value) {
                list ($title, $msgtext, $login, $commune_name) = $this->mark(array(
                    (string) $value['title'],
                    (string) $value['msgtext'],
                    (string) $value['login'],
                    (string) $value['commune_name'],
                ));
                $msgtext = preg_replace('~(https?:/){[^}]+}/~', '$1/', $msgtext);
                if ($title == '') {
                    $title = '<Без темы>';
                }
                if (empty($value['parent_id']) || is_null($value['parent_id'])) {
                    $link = '/commune/?id=' . $value['commune_id'] . '&site=Topic&post=' . $value['id'] . '&om=0';
                } else {
                    $link = '/commune/?id=' . $value['commune_id'] . '&site=Topic&post=' . $value['top_id'] . '.' . $value['id'] . '&om=0#o' . $value['id'];
                }
                $html[$key]  = '<a href="' . $link . '" style="font-weight: bold;" class="blue">' . $title . '</a>';
                $html[$key] .= '<div style="margin-top: 4px;">' . reformat($msgtext,80,0,1) . '</div>';
                $html[$key] .= '<div class="little" style="margin-top: 4px;"><span class="topic">Сообщество:</span> <a href="/commune/?id=' . $value['commune_id'] . '">' . $commune_name . '</a> - ' . (($value['parent_id'] == 0) ? 'топик' : 'комментарий') . ' ';
                if ($value['user_id'] > 0) {
                    $html[$key] .= '[<a href="/users/' . $value['login'] . '/" title="' . $value['uname'] . ' ' . $value['usurname'] . '" class="black">' . $login . '</a>]';
                } else {
                    $html[$key] .= '[' . $login . ']';
                }
                $html[$key] .= ' - [' . strftime("%d.%m.%Y | %H:%M", make_timestamp($value['post_time'])) . ']</div>';
            }
        }
        $this->html = $html;
    }
}
