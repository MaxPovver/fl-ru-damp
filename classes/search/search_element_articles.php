<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/search/search_element.php";

/**
 * Класс для поиска по статьям
 *
 */
class searchElementArticles extends searchElement
{
    public $name = 'Статьи и интервью';

    
    public function setResults() {
        $result = $this->getRecords();
        if($result) {
            foreach($result as $key=>$value) {
                $res[$value['id']] = $value;
                $res[$value['id']]['authors'][] = array("uname"=>$value['uname'], "usurname"=>$value['usurname'], "login"=>$value['login']);
            }
            $this->results = $res;
        }
    }
	/**
	 */
    public function setHtml() {
        $html = array();
        if ($result = $this->getRecords()) {
            foreach($result as $key => $value) {
                $is_articles = $value['id'] % 2;
                $id = ($value['id'] - $is_articles) / 2;
                list ($title, $login, $uname, $usurname,  $message) = $this->mark(array(
                    (string) $value['title'],
                    (string) $value['login'],
                    (string) $value['uname'],
                    (string) $value['usurname'],
                    (string) strip_tags($value['msgtext'])
                ));

                $html[$key] = '';
                if($is_articles) {
                    $link = '/articles/?id='.$id;
                    if($value['logo'])
                        $logo = '<img src="'.WDCPREFIX.'/about/articles/'.$value['logo'].'" width="50" hspace="0"/>';
                    $title = '<a href="'.$link.'" style="font-weight: bold;" class="blue">' . ($title ? $title : '<Без заголовка>') . '</a>';
                    $footer = '<div class="little" style="margin-top: 4px;"><span class="topic">Статьи:</span> <a href="'.$link.'">'.$value['title'].'</a> - [' . strftime("%d.%m.%Y | %H:%M", make_timestamp($value['post_time'])) . ']</div>';
                }
                else {
                    $link = '/interview/?id='.$id;
                    $logo = '<img src="'.WDCPREFIX.'/users/'.$value['login'].'/upload/'.$value['logo'].'" width="50"/>';
                    $footer = '<div class="little" style="margin-top: 4px;"><span class="topic">Интервью:</span> <a href="'.$link.'">'.$uname.' '.$usurname.'</a> - [<a href="/users/'.$value['login'].'">'.$login.'</a>]</div>';
                }

                if($logo)
                    $logo = '<a href="'.$link.'">'.$logo.'</a>';

                $html[$key] .= '<table cellpadding="0" cellspacing="0" width="100%">';
                $html[$key] .= '<col style="width:58px"/>';
                $html[$key] .= '<col />';
                $html[$key] .= '<tr valign="top">';
                $html[$key] .= '<td>';
                $html[$key] .= $logo;
                $html[$key] .= '</td>';
                $html[$key] .= '<td>';
                $html[$key] .= '<div>' . $title . '</div>';
                $html[$key] .= '<div>' . $message . '</div>';
                $html[$key] .= '</td>';
                $html[$key] .= '</tr>';
                $html[$key] .= '<tr valign="top">';
                $html[$key] .= '<td colspan="2">';
                $html[$key] .= $footer;
                $html[$key] .= '</td>';
                $html[$key] .= '</tr>';
                $html[$key] .= '</table>';
            }
        }
        $this->html = $html;
    }
}
