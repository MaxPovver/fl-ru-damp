<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_helper.php");

define('SITEMAP_PUBLIC_HOST', 'https://www.fl.ru');

/**
 * Класс для создания карты сайта
 *
 */
class sitemap
{

    /**
     * Адрес всех генерируемых ссылок
     *
     */
    const PUBLIC_HOST = SITEMAP_PUBLIC_HOST;
    /**
     * Начало файла карты сайта
     *
     */
    const SITEMAP_HEADER = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
    /**
     * Конец файла карты сайта
     *
     */
    const SITEMAP_FOOTER = "</urlset>";
    /**
     * Начало файла индекса
     *
     */
    const SITEINDEX_HEADER = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">"; 

    /**
     * Конец файла индекса
     *
     */
    const SITEINDEX_FOOTER = "</sitemapindex>";
    
    /**
     * Максимальный размер одного файла
     *
     */
    const MAX_SIZE_FILE  = 10485760;
    
    /**
     * Максимальное количество ссылок в одном файле
     *
     */
    const MAX_SIZE_COUNT = 50000;
    
    /**
     * Папка где будут находится файлы карты сайта
     *
     * @var unknown_type
     */
    public $folder_name = "/sitemap/";
    public $folder_name2 = "/flru/sitemap/";
    /**
     * Шаблон названия файла карты
     *
     * @var string
     */
    public $fname_sitemap   = '%s%s.xml';
    /**
     * Шаблон названия файла индекса карты 
     *
     * @var string
     */
    public $fname_siteindex = '%s_index.xml';
    
    /**
     * Имя индексного файла.
     * @var string
     */
    public $index_name;
    
    public $limit_one_operation = 1000;
    /**
     * Проверка окончания генерации карты 
     *
     * @var boolean
     */
    public $end = false;
    public $priority;
    public $sitemap_lngth;
    public $siteindex_lngth;
    

    // Типы карт. 
    const TYPE_BLOGS     = 'blogs';
    const TYPE_PROJECTS  = 'projects';
    const TYPE_COMMUNE   = 'commune';
    const TYPE_USERS     = 'users';
    const TYPE_PORTFOLIO = 'portfolio';
    const TYPE_ARTICLES  = 'articles';
    const TYPE_INTERVIEW = 'interview';
//    const TYPE_REGIONS   = 'regions';
    const TYPE_CATALOG   = 'catalog';
    const TYPE_USERPAGES = 'userpages';
    const TYPE_TSERVICES = 'tservices';
    
    
    // Параметры типов карт.
    static $types = array (
       self::TYPE_BLOGS     => array('priority'=> 0.8),
       self::TYPE_PROJECTS  => array('priority'=> 0.9),
       self::TYPE_COMMUNE   => array('priority'=> 0.5),
       self::TYPE_USERS     => array('priority'=> 0.5),
       self::TYPE_PORTFOLIO => array('priority'=> 0.5),
       self::TYPE_ARTICLES  => array('priority'=> 0.5),
       self::TYPE_INTERVIEW => array('priority'=> 0.5),
//       self::TYPE_REGIONS   => array('priority'=> 0.5),
       self::TYPE_CATALOG   => array('priority'=> 0.5),
       self::TYPE_USERPAGES => array('priority'=> 0.5),
       self::TYPE_TSERVICES  => array('priority'=> 0.9),
    );

    
    /**
     * Конструктор карты.
     * @param string $type   тип. карты.
     */
    function __construct($type) {
        $this->type = $type;
        $this->sql = "SELECT * FROM vw_sitemap_{$this->type}";
        $this->priority = self::$types[$type]['priority'];
        $this->sitemap_lngth    = strlen(sitemap::SITEMAP_HEADER.sitemap::SITEMAP_FOOTER); 
        $this->siteindex_lngth  = strlen(sitemap::SITEMAP_HEADER.sitemap::SITEMAP_FOOTER);
        $root = str_replace("classes", "", dirname(__FILE__));
        $this->folder = $root.$this->folder_name;
        $this->folder2 = $root.$this->folder_name2;
        $this->index_name = sprintf($this->fname_siteindex, $this->type);

        if(!is_dir($this->folder)) {
            mkdir($this->folder);
        }
        
        // Для отладки.
        if($this->limit_one_operation > self::MAX_SIZE_COUNT) {
            $this->limit_one_operation = self::MAX_SIZE_COUNT;
        }
    }
    
    
    /**
     * Создает карту нужного типа с нуля.
     *
     * @param string $type   тип. карты.
     * @param boolean $send   отправлять ли в гугул индекс.
     */
    static function create($type, $send = true) {
       $smap = new sitemap($type);
       $smap->_create($send);
    }
    
    /**
     * Обновляет (дописывает новыми данными) карту нужного типа.
     *
     * @param string $type   тип. карты.
     * @param boolean $send   отправлять ли в гугул индекс.
     */
    static function update($type, $send = true) {
       $smap = new sitemap($type);
       $smap->_update($send);
    }
    
    /**
     * Создает с нуля карту
     * @param boolean $send   отправлять ли в гугул индекс.
     */
    function _create($send = true) {
        echo "creating {$this->type} sitemap...\r\n";
        $this->clear_count();
        $start = 0;
        $key = 0;
        while(!$this->end)
            $start = $this->createOneFilesSitemap($start, $key); 
        if($this->createFilesSiteindex() && $send)
            $this->send();
    }

    
    /**
     * Проверка на новые ссылки и обновление файлов карты сайта
     * @param boolean $send   отправлять ли в гугул индекс.
     */
    public function _update($send = true) {
        global $DB;
		$data = $DB->row("SELECT * FROM sitemap WHERE type = ?", $this->type);
        if($data) {
            $this->_rtSql = $this->sql . " WHERE posttime > '{$data['end_date']}'";
            $this->update_file($data);
        }
    }
    
    
    /**
     * Отправляет файл индекса в гугл.
     */
    function send() {
        if(!is_release()) return;
        $surl = urlencode(self::PUBLIC_HOST.'/sitemap.xml');
        file_get_contents("http://www.google.ru/webmasters/tools/ping?sitemap={$surl}");

        $surl2 = urlencode(self::PUBLIC_HOST.'/flru/sitemap.xml');
        file_get_contents("http://www.google.ru/webmasters/tools/ping?sitemap={$surl}");
    }
    
    
    /**
     * Создание файла индексов
     *
     */
    function createFilesSiteindex() {
        if(count($this->index_items) < 1) $idx = $this->createFilesSitemap();
        else $idx = $this->index_items;
        return file_put_contents($this->folder.$this->index_name, $this->getSiteindexXml($idx));
    }
    
    /**
     * Создание xml индексов
     *
     * @param array $idx Данные по файлам
     * @return string
     */
    function getSiteindexXml($idx) {
        //$xml = sitemap::SITEINDEX_HEADER;
        if(count($idx)>0) $xml .= implode("", $idx);
        //$xml .= sitemap::SITEINDEX_FOOTER; 
        return $xml;
    }
    
    /**
     * Создание xml карты сайта
     *
     * @param array $url Данные по ссылки
     * @return string
     */
    function getSitemapXml($url) {
        $xml = sitemap::SITEMAP_HEADER;
        if(count($url)>0) $xml .= implode("", $url);
        $xml .= sitemap::SITEMAP_FOOTER;
        return $xml;
    }
    
    /**
     * Создать файлы карты сайта и индексов
     *
     * @param array $urls    Ссылки для создания
     * @return array
     */
    function createFilesSitemap($urls=false) {
        if(!$urls) $urls = $this->getSitemapUrls();
        foreach($urls as $k=>$value) {
            if($this->new) {
                $count    = sizeof($urls[$k]);
                if(!$this->saveCount($count, $this->end_date, $k))
                    break;
                $fname = sprintf(sprintf($this->fname_sitemap, $this->type, "_new"));
                $this->new = false;
            } else {
                $fname = sprintf($this->fname_sitemap, $this->type, $k);
            }
            file_put_contents($this->folder.$fname, $this->getSitemapXml($urls[$k]));
            $loc    = sitemap::PUBLIC_HOST.$this->folder_name.$fname;
            $index  = "<sitemap>";
            $index .= "<loc>{$loc}</loc>";
            $index .= "<lastmod>".date('c')."</lastmod>";
            $index .= "</sitemap>";
            $this->index_items[] = $index;
        }
        return $this->index_items;
    }
    
    /**
     * Создание одного файла карты сайта
     *  
     * @param integer $x   Стартовая позиция
     * @param integer $key Номер файла
     * @param integer $end Условие конца работы создания
     * @return integer Возвращает следующую стартовую позицию
     */
    function createOneFilesSitemap($x=0, &$key=0, $end=false) {
        if($this->end) return false;
        $url = array();
        $m = ( (sitemap::MAX_SIZE_COUNT / ($this->type=='userpages' ? 5 : 1)) / $this->limit_one_operation - 1);
        if($m<0) $m = 1;
        $this->end = $end;
        for($i=$x;$i<=$x+$m;$i++) {
            $offset = $i*$this->limit_one_operation;
            $this->_rtSql = $this->sql . " LIMIT {$this->limit_one_operation} OFFSET {$offset};";
            $ret = $this->getSitemapUrls($key);
            if(is_array($ret[$key])) $url = array_merge($url, $ret[$key]);
            if(count($ret[$key]) < $this->limit_one_operation || $offset > 20000000) {
                // Больше в базе данных нет
                $this->end = true;
                $this->new = true;
                break; 
            }
        }
        $res[$key] = $url;
        $key++;
        
        $this->createFilesSitemap($res);
        return ($x+$m+1);
    }
    
    /**
     * Генерирует данные для карты сайта
     *
     * @param inetger $key Номер файла
     * @return array
     */
    function getSitemapUrls($key=0) {
        global $DB;
        switch($this->type) {
            case 'freelancers':
                $result = array();
                require_once($_SERVER['DOCUMENT_ROOT'].'/classes/professions.php');
                $prfs  = new professions();
                $profs = $prfs->GetAllProfessions("",0, 1);
                foreach($profs as $prof) {
                    $result[] = array(
                                    'loc' => '/freelancers/'.$prof['link'].'/',
                                    'lastmod' => date('Y-m-d H:i:s'),
                                    'posttime' => date('Y-m-d H:i:s')
                                    );
                    $sql = "SELECT COUNT(s.uid) as count  
                            FROM ( SELECT * FROM fu WHERE spec_orig = '81' UNION ALL SELECT fu.* FROM fu INNER JOIN spec_add_choise sp ON sp.user_id = fu.uid AND sp.prof_id = '81' WHERE fu.is_pro = true 
                            UNION ALL 
                            SELECT fu.* FROM fu INNER JOIN spec_paid_choise pc ON pc.user_id = fu.uid AND pc.prof_id = '81' AND pc.paid_to > NOW() ) as s WHERE s.is_banned = '0'";
                    $count_pages = ceil($DB->val($sql) / FRL_PP);
                    for($n=$count_pages; $n>1; $n--) {
                        $result[] = array(
                                        'loc' => '/freelancers/'.$prof['link'].'/?page='.$n,
                                        'lastmod' => date('Y-m-d H:i:s'),
                                        'posttime' => date('Y-m-d H:i:s')
                                        );
                    }
                }
                break;
            case 'other':
                $sql = "SELECT loc, NOW() AS lastmod, NOW() AS posttime FROM vw_sitemap_other";
                $result = $DB->rows($sql);
                break;
            default:
                $sql = $this->_rtSql ? $this->_rtSql : $this->sql;
                $result = $DB->rows($sql);
                break;
        }

        $strlen = $this->sitemap_lngth;

        if(!$result) return false;
        foreach($result as $val) {
            switch($this->type) {
                case 'projects':
                    $loc = sitemap::PUBLIC_HOST.getFriendlyURL('project', $val['p_id']);
                    break;
                case 'blogs':
                    $loc = sitemap::PUBLIC_HOST.getFriendlyURL("blog", $val['b_id']);
                    break;
                case 'commune':
                    $loc = sitemap::PUBLIC_HOST.getFriendlyURL('commune', $val['m_id']);
                    break;
                case 'articles':
                    $loc = sitemap::PUBLIC_HOST.getFriendlyURL('article', $val['a_id']);
                    break;
                case 'interview':
                    $loc = sitemap::PUBLIC_HOST.getFriendlyURL('interview', $val['i_id']);
                    break;
                case 'regions':
                    $loc = sitemap::PUBLIC_HOST.'/freelancers/'.($val['link'] ? $val['link'].'/' : '').$val['translit_country_name'].'/'.($val['translit_city_name'] ? $val['translit_city_name'].'/' : '');
                    break;
                case 'tservices':
                    $loc = sitemap::PUBLIC_HOST.tservices_helper::card_link($val['t_id'], $val['t_name']);;
                    break;
                default:
                    $loc = sitemap::PUBLIC_HOST.$val['loc'];
                    break;
            }
            
            if($this->type=='userpages') {
                $x = "<url>";
                $x.= "<loc>{$loc}/info/</loc>";
                $x.= "<lastmod>".date("c", strtotime($val['lastmod']))."</lastmod>";
                $x.= "<priority>{$this->priority}</priority>";
                $x.= "</url>";
                $x.= "<url>";
                $x.= "<loc>{$loc}/opinions/</loc>";
                $x.= "<lastmod>".date("c", strtotime($val['lastmod']))."</lastmod>";
                $x.= "<priority>{$this->priority}</priority>";
                $x.= "</url>";
                $x.= "<url>";
                $x.= "<loc>{$loc}/journal/</loc>";
                $x.= "<lastmod>".date("c", strtotime($val['lastmod']))."</lastmod>";
                $x.= "<priority>{$this->priority}</priority>";
                $x.= "</url>";
            } else {
                $x = "<url>";
                $x.= "<loc>{$loc}</loc>";
                $x.= "<lastmod>".date("c", strtotime($val['lastmod']))."</lastmod>";
                $x.= "<priority>{$this->priority}</priority>";
                $x.= "</url>";
            }            
            $strlen = $strlen+strlen($x);
            
            // Максимальный вес файла
            if($strlen >= sitemap::MAX_SIZE_FILE) {
                $strlen = $this->sitemap_lngth;
                $key = $key+1;
            }
            
            $ret[$key][] = $x;
            
            // Максимальное количество ссылок
            if(count($ret[$key]) >= (sitemap::MAX_SIZE_COUNT/($this->type=='userpages' ? 5 : 1)) ) {
                $strlen = $this->sitemap_lngth;
                $key = $key+1;        
            }
            
            $this->end_date = $val['lastmod'];
        }     

        return $ret;
    }
    
    /**
     * Очищаем данные счетчик
     *
     */
    public function clear_count() {
        global $DB;
        return $DB->query("DELETE FROM sitemap WHERE type = ?", $this->type);
    }
    
    /**
     * Сохраняем данные счетчика
     *
     * @param integer $count    Количество ссылок в файле
     * @param integer $end_date Последняя дата
     * @param integer $n        Количество файлов    
     */
    public function saveCount($count, $end_date, $n) {
        global $DB;
		$sql = "UPDATE sitemap SET count = ?, end_date = ?, n = ? WHERE type = ?";
        if($res = $DB->query($sql, $count, $end_date, $n, $this->type)) {
            if(!pg_affected_rows($res)) {
                $res = $DB->insert('sitemap', array(
					  'type'     => $this->type,
					  'count'    => $count,
					  'end_date' => $end_date,
					  'n'        => $n
					)
				);
            }
        }
        return !!$res;
    }

    
    /**
     * Обновляем сами файлы
     *  
     * @param array   $data    Данные для обновления
     * @return bolean
     */
    public function update_file($data) {
        $result = $this->getSitemapUrls();
        if(!$result) return false;
        $sizeof    = sizeof($result[0]);
        $new_count = $data['count']+$sizeof;
        
        if($new_count >= sitemap::MAX_SIZE_COUNT / ($this->type=='userpages' ? 5 : 1)) {
            $new_file_count = $new_count - sitemap::MAX_SIZE_COUNT / ($this->type=='userpages' ? 5 : 1);
            
            // Эти данные дозапишут _new файл который после записи переименуем в N+1
            for($i=0;$i<$sizeof-$new_file_count;$i++) {
                $old_file_data[] = $result[0][$i];
            }
            // Эти данные пойдут в новый _new файл
            for($i=$sizeof-$new_file_count;$i<$sizeof;$i++) {
                $new_file_data[] = $result[0][$i];
            }

            
            $old_xml = implode("", $old_file_data).sitemap::SITEMAP_FOOTER;
            
            
            $filename = $this->folder.sprintf($this->fname_sitemap, $this->type, "_new");//"site_map_blogs_new.xml";
            $fileold  = $this->folder.sprintf($this->fname_sitemap, $this->type, $data['n']);//"site_map_blogs_{$data['n']}.xml";
                    
            $this->save_new_file($filename, $old_xml);
            rename($filename, $fileold);
            
            file_put_contents($filename, $this->getSitemapXml($new_file_data));
            
            $this->update_index_file(sitemap::PUBLIC_HOST.$this->folder_name.sprintf($this->fname_sitemap, $this->type, $data['n']), true);
            $this->update_index_file(sitemap::PUBLIC_HOST.$this->folder_name.sprintf($this->fname_sitemap, $this->type, "_new"));

            //-- sitemap2
            /*
            $filename2 = $this->folder2.sprintf($this->fname_sitemap, $this->type, "_new");//"site_map_blogs_new.xml";
            $fileold2  = $this->folder2.sprintf($this->fname_sitemap, $this->type, $data['n']);//"site_map_blogs_{$data['n']}.xml";
                    
            $this->save_new_file($filename2, str_replace("https://www.free-lance.ru/", "https://fl.ru/", $old_xml));
            rename($filename2, $fileold2);
            
            file_put_contents($filename2, str_replace("https://www.free-lance.ru/", "https://fl.ru/", $this->getSitemapXml($new_file_data)));
            
            $this->update_index_file(sitemap::PUBLIC_HOST.$this->folder_name2.sprintf($this->fname_sitemap, $this->type, $data['n']), true);
            $this->update_index_file(sitemap::PUBLIC_HOST.$this->folder_name2.sprintf($this->fname_sitemap, $this->type, "_new"));
            */
            //-- sitemap2
            
            $this->saveCount(count($new_file_data), $this->end_date, $data['n']+1);
        } else {
            $xml = implode("", $result[0]).sitemap::SITEMAP_FOOTER;
            
            $filename = $this->folder.sprintf($this->fname_sitemap, $this->type, "_new");
            $this->save_new_file($filename, $xml);
            
            $this->update_index_file(sitemap::PUBLIC_HOST.$this->folder_name.sprintf($this->fname_sitemap, $this->type, "_new"), true);

            //-- sitemap2
            /*
            $filename2 = $this->folder2.sprintf($this->fname_sitemap, $this->type, "_new");
            $this->save_new_file($filename2, str_replace("https://www.free-lance.ru/", "https://fl.ru/", $xml));
            
            $this->update_index_file(sitemap::PUBLIC_HOST.$this->folder_name2.sprintf($this->fname_sitemap, $this->type, "_new"), true);
            */
            //-- sitemap2            

            $this->saveCount($new_count, $this->end_date, $data['n']);
        }   
    }
    
    /**
     * Дописываем данные в файл типа _new
     *
     * @param string $filename    Имя файла
     * @param string $xml         Данные которые необходимо дописать
     */
    function save_new_file($filename, $xml) {
        $filesize = filesize($filename);
        $fp = fopen($filename, 'r+');
        fseek($fp, $filesize-strlen(sitemap::SITEMAP_FOOTER));
        fwrite($fp, $xml);
        fclose($fp);
    }
    
    /**
     * Обновление данных в файле индекса
     *
     * @param string  $loc         Ссылка на карту сайта    
     * @param boolean $delete      Удалять или нет последний индекс (для перезаписи)  
     */
    function update_index_file($loc, $delete=false) {
        libxml_disable_entity_loader();

        $xml = file_get_contents($this->folder.$this->index_name);
        $xml = sitemap::SITEINDEX_HEADER . $xml;
        $xml = $xml . sitemap::SITEINDEX_FOOTER;

        $sxe = new SimpleXMLElement($xml, NULL, FALSE);
        $cnt = count($sxe->sitemap);
        if($delete) unset($sxe->sitemap[$cnt-1]);
        
        $sitemap = $sxe->addChild('sitemap');
        $loc     = $sitemap->addChild('loc', $loc);
        $lmod    = $sitemap->addChild('lastmod', date('c'));

        $xml = $sxe->asXML();
        $xml = str_replace(sitemap::SITEINDEX_HEADER, '', $xml);
        $xml = str_replace(sitemap::SITEINDEX_FOOTER, '', $xml);
        
        file_put_contents($this->folder.$this->index_name, $xml);

        //file_put_contents($this->folder2.$this->index_name, str_replace("https://www.free-lance.ru/", "https://www.fl.ru/", $xml));
    }

    /**
     * Генерация главного файла sitemap.xml
     *
     */
    function generateMainSitemap() {
        $xml = sitemap::SITEINDEX_HEADER;
        $root = str_replace("classes", "", dirname(__FILE__));
        $d = dir($root.'/sitemap/');
        while(false !== ($entry=$d->read())) {
            if(preg_match("/_index\.xml$/",$entry)) {
                $xml .= "\n".file_get_contents($root.'/sitemap/'.$entry)."\n";
            }
        }
        $xml .= sitemap::SITEINDEX_FOOTER; 
        file_put_contents($root.'/sitemap.xml', $xml);
    }
}


?>