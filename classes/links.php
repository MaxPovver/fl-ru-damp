<?php

/**
 * Класс для обработки ссылок (SEO).
 * Управляет внутренними ссылками, ведущими на пользователей сайта, их портфолио, отдельные блоги, сообщества и т.д.
 * Например, заменяет контент ссылки на сообщество названием этого сообщества.
 * Всегда инициализируется в переменную $GLOBALS[LINK_INSTANCE_NAME], если в модуле нужна поддержка обработки ссылок.
 * @see reformat()
 */
class links
{
    /**
     * Тип ссылки в блоги
     *
     */
    const BLOG_TYPE            = 1;
    
    /**
     * Тип ссылки на страницу пользователя
     *
     */
    const USER_TYPE            = 2;
    
    /**
     * Тип ссылки на страницу сообщества
     *
     */
    const COMMUNE_TYPE         = 3;
    
    /**
     * Тип ссылки на страницу топика сообщества 
     *
     */
    const COMMUNE_TYPE_MESSAGE = 4;
    
    /**
     * Тип ссылки на страницу работы
     *
     */
    const PORTFOLIO_TYPE       = 5;
    
    /**
     * Тип ссылки на страницу проекта
     *
     */
    const PROJECT_TYPE       = 6;
    
    /**
     * тип ссылки на страницу статьи
     */
    const ARTICLES_TYPE = 7;
    
    /**
     * тип ссылки на страницу интервью
     */
    const INTERVIEW_TYPE = 8;
    
    /**
     * Таблица для хранения ссылок в базе. Зависит от модуля, в котором подключается класс (например, есть 'links_blogs').
     * По умолчанию 'links' -- родительская таблица всех подтаблиц.
     *
     * @var string
     */
    public $table              = "links";
    
    /**
     * На каком символе обрезать хвост текста ссылки и заменять его на '...'
     *
     * @var integer
     */
    private $max_link_len      = 25;
    
    
    /**
     * Конструктор.
     *
     * @param string $table постфикс таблицы вызывающего модуля.
     */
    function __construct($table=NULL) {
        if($table) $this->table .= "_".$table;
    }
    
    
    /**
     * Записать ссылка в таблицу
     *
     * @param  integer $dst_id   ИД Объекта
     * @param  integer $dst_type Тип ссылки
     * @param  string  $title    название ссылки
     * @param  string  $url      Ссылка
     * @return string Сообщение об ошибке
     */
    function set_link( $dst_id, $dst_type, $title, $url ) {
        global $DB;
        $title = (string)substr( $title, 0, 256 );
        $url = (string)substr( $url, 0, 2048 );
        $data = compact('dst_id', 'dst_type', 'title', 'url');
        
        return !!$DB->insert( $this->table, $data );
    }
    
    /**
     * Взять данные ссылки из таблицы
     *
     * @param string  $url  Ссылка
     * @return array
     */
    function get_link($url) {
        global $DB;
        $url = substr($url, 0, 2048);
        $sql = "SELECT title, id, url FROM {$this->table} WHERE url = ?";
        
        return $DB->row( $sql, $url );
    }
    
    /**
     * Функция обработки ссылок и запись из в БД
     *
     * @param array $matches   найденные ссылки в исходном тексте {@link stdf.php reformat()}
     * @param boolean $is_found   прошла ли обработку или вернулся исходный адрес?
     * @param integer $max_link_len на каком символе обрезать хвост текста ссылки и заменять его на '...'
     * @return string
     */
    function save_find($matches, &$is_found, $max_link_len = 25) {
        global $host;
        $is_found = false;
        $URI  = $matches[0];
        $url  = parse_url($matches[0]);

        require_once($_SERVER['DOCUMENT_ROOT']."/classes/commune.php");
        if(preg_match("/^\/blogs\/[a-z0-9_\-]{1,}\/([0-9]{1,})\/[a-z0-9_\-]{1,}\.html/", $url['path'], $m)) {
            $friendly_url = getFriendlyURL('blog', $m[1]);
            if(strtolower($url['path'])!=$friendly_url) {
                $url['path'] = $friendly_url;
                $URI = $url['scheme'].'://'.$url['host'].$url['path'].($url['query'] ? $url['query'] : '').($url['fragment'] ? '#'.$url['fragment'] : '');
            }
            $tr = $m[1];
        }
        if(preg_match("/^\/commune\/[a-z0-9_\-]{1,}\/([0-9]{1,})\/[a-z0-9_\-]{1,}\/([0-9]{1,})\/([a-z0-9_\-]{1,}\.html)?/", $url['path'], $m)) {
            $friendly_url = getFriendlyURL('commune', $m[2]);
            if(strtolower($url['path'])!=$friendly_url) {
                $url['path'] = $friendly_url;
                $URI = $url['scheme'].'://'.$url['host'].$url['path'].($url['query'] ? $url['query'] : '').($url['fragment'] ? '#'.$url['fragment'] : '');
            }
            $id=$m[1];
            $post=$m[2];
        } elseif (preg_match("/^\/commune\/[a-z0-9_\-]{1,}\/([0-9]{1,})\/[a-z0-9_\-]{1,}/", $url['path'], $m)) {
            if(intval($m[2])==0) {
                $friendly_url = getFriendlyURL('commune_commune', $m[1]);
                if(strtolower($url['path'])!=$friendly_url) {
                    $url['path'] = $friendly_url;
                    $URI = $url['scheme'].'://'.$url['host'].$url['path'].($url['query'] ? $url['query'] : '').($url['fragment'] ? '#'.$url['fragment'] : '');
                }
                $id = $m[1];
            }
        }

       
        $this->max_link_len = $max_link_len;

        if(isset($url['query'])) parse_str($url['query'], $urlvars); // Разбираем передаваемые данные из ссылки
        
        $exp  = explode("/", $url['path']);
        $type = $exp[1];
        
        $result = self::get_link($URI);

        
        if($result) {
            $link = self::getHrefLink($result['title'], $URI);
        } else {
            switch(true) {
                case ($type == "blogs"): // Обработка ссылок на блоги
                    $e = explode('/', $url['path']);
                    $where = isset($urlvars['openlevel'])?"id = ".intval($urlvars['openlevel']) : "thread_id = " . intval($e[3]) . " AND reply_to IS NULL";
                    $link  = self::get_title($where, $URI, links::BLOG_TYPE);
                    break;
                case ($type == "users" && $exp[3] != "viewproj.php"): // Обработка ссылок на пользователя
                    $where = "lower(login) = '".strtolower(pg_escape_string($exp[2]))."'"; // Мало ли...
                    $link  =  self::get_title($where, $URI, links::USER_TYPE);    
                    break; 
                case ($type == "users" && $exp[3] == "viewproj.php"): // Обработка ссылок на портфолио
                    $where = "id = ".intval($urlvars['prjid']);
                    $link  = self::get_title($where, $URI, links::PORTFOLIO_TYPE);
                    break;
                case ($type == "commune" && !isset($post)): // Обработка ссылок на сообщество
                    $where = "id = ".intval($id);
                    $link  = self::get_title($where, $URI, links::COMMUNE_TYPE);
                    break;
                case ($type == "commune" && isset($post)): // Обработка ссылок на сообщения в сообществах
                    $e = explode(".", $post);
                    $where = count($e)==2?"id = ".intval($e[1]):"id = ".intval($post);
                    $link  = self::get_title($where, $URI, links::COMMUNE_TYPE_MESSAGE);
                    break;
                case ($type === 'projects'):
                    $e = explode('/', $url['path']);
                    $where = 'id = ' . intval($e[2]);
                    $link  = self::get_title($where, $URI, links::PROJECT_TYPE);
                    break;
                case ($type === 'articles'):
                    $e = explode('/', $url['path']);
                    $where = 'id = ' . intval($e[2]);
                    $link  = self::get_title($where, $URI, links::ARTICLES_TYPE);
                    break;
                case ($type === 'interview'):
                    $e = explode('/', $url['path']);
                    $where = 'id = ' . intval($e[2]);
                    $link  = self::get_title($where, $URI, links::INTERVIEW_TYPE);
                    break;
            }
        }

        if($link && $link!=$URI) {
            $is_found = true;
            return $link;
        }
        
        return $URI;
    }
    
    /**
     * Берем данные по ссылки если ее нет в таблице ссылок
     *
     * @param string  $where   Условия выбора
     * @param string  $URI     Полная ссылка
     * @param integer $type    Тип ссылки
     * @return string
     */
    function get_title($where, $URI, $type) {
        switch($type) {
            case links::BLOG_TYPE:
                $sql = "SELECT title, id FROM blogs_msgs WHERE {$where}";
                break;
            case links::USER_TYPE:
                $sql = "SELECT uname||' '||usurname||' ['||login||']' as title, uid as id FROM users WHERE {$where}";
                break;
            case links::PORTFOLIO_TYPE:
                $sql = "SELECT name as title, id FROM portfolio WHERE {$where}";
                break; 
            case links::COMMUNE_TYPE :
                $sql = "SELECT name as title, id FROM commune WHERE {$where}";
                break;     
            case links::COMMUNE_TYPE_MESSAGE:
                $sql = "SELECT title, id FROM commune_messages WHERE {$where}";
                break;
            case links::PROJECT_TYPE:
                $sql = "SELECT name as title, id FROM projects WHERE {$where}";
                break;
            case links::ARTICLES_TYPE:
                $sql = "SELECT title as title, id FROM articles_new WHERE {$where}";
                break;
            case links::INTERVIEW_TYPE:
                $sql = "SELECT ('Интервью ' || u.uname || ' ' || u.usurname || ' [' || u.login || ']') as title, i.id
                        FROM interview_new i
                        LEFT JOIN users u
                            ON i.user_id = u.uid
                        WHERE {$where}";
                break;
            default:
                return $URI;
                break;    
        }
        
        global $DB;
        $result = $DB->row( $sql );

        if ( $result ) {
            if(self::set_link($result['id'], $type, $result['title'], $URI))
                return self::getHrefLink($result['title'], $URI);
        }

        return $URI;
    }
    
    /**
     * Обрабатываем ссылку для замены в тексте
     *
     * @param string $title   Название ссылки
     * @param string $URI     Ссылка    
     * @return string
     */
    function getHrefLink($title, $URI) {
        $attrTitle = $title;
        if ( !$title ) {
            $attrTitle = $URI;
            $title = LenghtFormatEx( $URI, $this->max_link_len );
        }
        
        return "<a href='{$URI}' title='{$attrTitle}' class='blue' target='_blank'>{$title}</a>";
    }
}

