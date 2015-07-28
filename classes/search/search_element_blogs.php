<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/search/search_element.php";

/**
 * Класс для поиска по блогам
 *
 */
class searchElementBlogs extends searchElement
{
    public $name = 'Блоги';

    public function setResults() {
        $result = $this->getRecords();
        $this->results = $result;    
    }
	/**
	 */
    public function setHtml() {
        $html = array();
        if ($result = $this->getRecords()) {
            foreach($result as $key => $value) {
                $value['msgtext'] = preg_replace('~(https?:/){[^}]+}/~', '$1/', $value['msgtext']);
                list ($title, $message, $login) = $this->mark(array(
                    (string) $value['title'],
                    (string) $value['msgtext'],
                    (string) $value['login']
                ));
                if ($title == '') {
                    $title = '<Без темы>';
                }
                if (empty($value['reply_to']) || is_null($value['reply_to'])) {
                    $link = '/blogs/view.php?tr=' . $value['thread_id'] . '&ord=new';
                } else {
                    $link = '/blogs/view.php?tr=' . $value['thread_id'] . '&ord=new&openlevel=' . $value['id'] . '&ord=new#o' . $value['id'];
                }
                $html[$key]  = '<a href="' . $link . '" style="font-weight: bold;" class="blue">' . $title . '</a>';
                $html[$key] .= '<div style="margin-top: 4px;">' . reformat($message,80,0,1) . '</div>';
                $html[$key] .= '<div class="little" style="margin-top: 4px;"><span class="topic">Закладка:</span> <a href="/blogs/viewgroup.php?gr=' . $value['id_gr'] . '&ord=new">' . $value['group_name'] . '</a> - комментарий - ';
                if ($value['fromuser_id'] > 0) {
                    $html[$key] .= '[<a href="/users/' . $value['login'] . '/" title="' . $value['uname'] . ' ' . $value['usurname'] . '" class="black">' . $login . '</a>]';
                } else {
                    $html[$key] .= '[' . $login . ']';
                }
                //$html[$key] .= '- [' . dateFormat("dd.mm YYYY | H:M", $value['post_time']) . ']</div>';
                $html[$key] .= '- [' . strftime("%d.%m.%Y | %H:%M", make_timestamp($value['post_time'])) . ']</div>';
            }
        }
        $this->html = $html;
    }
}
