<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/search/search_element.php";
/**
 * Класс для поиска по работам
 *
 */
class searchElementWorks extends searchElement
{
    public $name = 'Работы';
    public $totalwords = array('работа', 'работы', 'работ');
    public $layout = self::LAYOUT_BLOCK;
    protected $_sort = SPH_SORT_EXTENDED;
    protected $_sortby = 'is_pro DESC, post_time DESC';

    public function setResults() {
        $result = $this->getRecords('is_pro DESC, post_time DESC');
        $block = $i = 0;
        if($result) {
            foreach($result as $key=>&$value) {
               list ($name, $descr, $login, $uname, $usurname) = $this->mark(array((string) $value['name'], (string) $value['descr'],(string) $value['login'], (string) $value['uname'], (string) $value['usurname']));
               $value['mark_uname'] = $uname;
               $value['mark_usurname'] = $usurname;
               $value['mark_name']  = reformat($name, 18, 0, 1);
               $value['mark_descr'] = reformat($descr, 20, 0, 1);
               $value['mark_login'] = $login;
               $blocks[$i][$block] = $value;
               $block++;
               if($block >= 3) {
                   $block = 0;
                   $i++;
               }
            }
            if(count($blocks[$i]) != 3) {
                for($k=count($blocks[$i]);$k<3;$k++) {
                    $blocks[$i][$k] = false;
                }
            }
            $this->results = $blocks;   
        } 
    }
	/**
	 */
    public function setHtml() {
        $html = array();
        if ($result = $this->getRecords('is_pro DESC, post_time DESC')) {
            $i = 0;
            foreach($result as $key => $value) {
                list ($name, $descr, $login) = $this->mark(array(
                    (string) $value['name'],
                    (string) $value['descr'],
                    (string) $value['login']
                ));
                if ($value['is_text'] == 't') {
                    $html[$key] .= '<div style="width:200px;">';
                    $html[$key] .= '<div style="text-align:left;padding-top:4px;"><a href="/users/' . $value['login'] . '/viewproj.php?prjid=' . $value['id'] . '" target="_blank" class="blue" style="font-weight: bold;">' . $name . '</a></div>';
                    $html[$key] .= '<div style="text-align:left;padding-top:2px;">' . reformat($descr, 36, 0, 1) . '</div>';
                    $html[$key] .= '</div>';
                } else {
                    $html[$key] .= '<div style="width:200px;">';
                    if (($value['prof_show_preview'] == 't') && ($value['is_pro'] == 't')) {
                        $html[$key] .= '<div style="text-align:left;"><a href="/users/' . $value['login'] . '/viewproj.php?prjid=' . $value['id'] . '" target="_blank">' . view_preview($value['login'], $value['prev_pict'], "upload", $align) . '</a></div>';
                    }
                    $html[$key] .= '<div style="text-align:left;padding-top:4px;"><a href="/users/' . $value['login'] . '/viewproj.php?prjid=' . $value['id'] . '" target="_blank" class="blue" style="font-weight: bold;">' . $name . '</a></div>';
                    $html[$key] .= '<div style="text-align:left;padding-top:4px;">' . reformat($descr, 36, 0, 1) . '</div>';
                }
                $html[$key] .= '<div class="little" style="margin-top: 4px;">Автор: ';
                if ($value['user_id'] > 0) {
                    $html[$key] .= '[<a href="/users/' . $value['login'] . '/" title="' . $value['uname'] . ' ' . $value['usurname'] . '" class="black">' . $login . '</a>]';
                } else {
                    $html[$key] .= '[' . $login . ']';
                }
                $html[$key] .= ' - [' . strftime("%d.%m.%Y | %H:%M", make_timestamp($value['post_time'])) . ']</div>';
                $i++;
            }
        }
        $this->html = $html;
    }
}
