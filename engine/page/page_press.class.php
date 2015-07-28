<?php

/**
 * Класс обрабатывающий все действия на странице /press/
 *
 */
class page_press extends page_base {    
	/**
	 * Переменная необходимая для баннеров (определяет какой тип баннеров выводить на данной странице)
	 *
	 * @var unknown_type
	 */
	public $b_page = "0|2";
	
	function __construct() {
	    front::og("tpl")->main_css  = "/css/press-center.css";
		front::og("tpl")->g_page_id = $this->b_page;
        front::og("tpl")->page = 'press';
        front::og("tpl")->page_title = "Удаленная работа (фри-ланс) на Free-lance.ru";
	}
	/**
	 * Обработчик событий главной страницы /press/
	 *
	 */
    function indexAction() {
        $this->contactsAction();
    }
    /**
	 * Обработчик событий страницы /press/news/
	 *
	 */
    /*function newsAction() {
        $db    = front::og("db");
        $first = $this->uri[0];
        
        front::og("tpl")->years = $db->select("SELECT DISTINCT EXTRACT(YEAR FROM post_date) as post_date FROM news ORDER BY post_date DESC;")->fetchColumn();
        
        if($first == "year" && in_array(($selected_year = intval($this->uri[1])), front::og("tpl")->years)) {
            front::og("tpl")->selected_year = $selected_year;
        } elseif(intval($first) > 0) {
            $this->showNews($first);
            return;
        } else {
            front::og("tpl")->selected_year = array_shift(($temp = front::og("tpl")->years));
        }
        
        front::og("tpl")->news = $db->select("SELECT id, post_date, header FROM news WHERE EXTRACT(YEAR FROM post_date) = ? ORDER BY post_date DESC;", front::og("tpl")->selected_year)->fetchAll();
        
        foreach(front::og("tpl")->news as &$news) {
            $d = intval(substr($news["post_date"],5,7));
            $m = intval(substr($news["post_date"],8,10));
            $news["post_date"] = sprintf("%02d.%02d", $m, $d);
        }
        
        front::og("tpl")->display("press_center/press_news.tpl");
    }*/
    /**
     * Показа новость полностью страница вида /press/news/ID/
     *
     * @param integer $id ID Новости
     */
    function showNews($id) {
        $db = front::og("db"); 
        front::og("tpl")->one_news = $db->select("SELECT id, post_date, header, n_text FROM news WHERE id = ?n LIMIT 1;", $id)->fetchRow();
        
        front::og("tpl")->display("press_center/press_news.tpl");
    }
    /**
     * Обработчик событий страницы /press/about/ (О Фри-лансе)
     *
     */
    /*function aboutAction() {
        front::og("tpl")->text = static_pages::get("press_about");
        front::og("tpl")->display("press_center/press_about.tpl");
    }*/
    /**
     * Обработчик событий страницы /press/opinions/ (Отзывы)
     *
     */
    /*function opinionsAction() {
        front::og("tpl")->msgs = sopinions::GetMsgs();
        front::og("tpl")->display("press_center/press_opinions.tpl");
    }*/
    /**
     * Обработчик событий страницы /press/parthners/ (Партнеры)
     *
     */
    /*function partnersAction() {
        $db = front::og("db"); 
        front::og("tpl")->msgs = $db->select("SELECT id, msgtext, sign, logo, link FROM partners ORDER BY post_time DESC")->fetchAll();
        front::og("tpl")->display("press_center/press_partners.tpl");
    }*/
    /**
     * Обработчик событий страницы /press/adv/ (Реклама)
     *
     */
    function advAction() {
        front::og("tpl")->text = static_pages::get("press_adv");
        front::og("tpl")->css  = "/css/main.css";
        front::og("tpl")->display("press_center/press_adv.tpl");
    }
    /**
     * Обработчик событий страницы /press/smi/ (СМИ о Фри-лансе)
     *
     */
    /*function smiAction() {
        front::og("tpl")->msgs = press::GetMsgs($msg_cntr, 1, $num_msgs, $error);
        $id = $this->uri[0];
        if(intval($id) == $id && $id > 0) {            
            $msg = press::GetMsgInfo($id);
            
            front::og("tpl")->title = $msg["title"];
            front::og("tpl")->text = $msg["msgtext"];
            front::og("tpl")->sign = $msg["sign"];
            front::og("tpl")->link = $msg["link"];
            front::og("tpl")->display("press_center/press_smi_text.tpl");
            return;
        }
        
        front::og("tpl")->display("press_center/press_smi.tpl");
    }*/
    /**
     * Обработчик событий страницы /press/contacts/ (Контакты)
     *
     */
    function contactsAction() {
        front::og("tpl")->text = static_pages::get("press_contacts");
        front::og("tpl")->display("press_center/press_contacts.tpl");
    }
}
?>