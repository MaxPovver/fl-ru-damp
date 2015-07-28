<?php
/**
 * Подключаем предка
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/paid_advices.php");

define(DEFAULT_ITEMS_PER_PAGE, 20);
define(MAX_ITEMS_PER_PAGE, 100);
/**
 * Класс для поиска фрилансеров
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
class admin_frl_search {
    //параметры фильтра
    /**
     * наличие специализации
     */
    private $prof;
    /**
     * наличие минимум 3-х подтвержденных мнений
     */
    private $opinions;
    /**
     * наличие минимум 10 работ в портфолио
     */
    private $portfolio;
    /**
     * минимум 5 визитов за месяц и 1 за неделю
     */
    private $visits;
    /**
     * минимум 5 откликов на проекты 
     */
    private $projects;
    /**
     * номер страницы 
     */
    public $page;
    /**
     * количество фрилансеров на странице 
     */
    private $items;
    /**
     * смещение 
     */
    private $offset;
    /**
     * есть ли параметры фильтрации
     */
    public $is_filter;
    /**
     * выбраны все опции фильтра 
     */
    public $full_filter;
    
    /**
     * количество найденных фрилансеров
     */
    public $count;
    /**
     * количество страниц после очередного поиска, зависит от $items 
     */
    public $pages;
    /**
     * список найденых фрилансеров 
     */
    public $totalFrls = array();
    /**
     * список фрилансеров после пагинации? то есть для текущей страницы
     */
    public $pageFrls = array();
    
    public function __construct ($filter) {
        $this->setFilter($filter);
    }
    
    /**
     * устанавливает фильтр для поиска
     * @param $filter - массив с праметрами фильтра
     * @key prof - определиена ли специализация
     * @key opinions - есть ли как минимум 3 подтвержденных мнения
     * @key portfolio - есть ли как минимум 10 работ
     * @key visits - 5 посещений за последний месяц и 1 посещение за последнюю неделю
     * @key projects - 5 проектов за последнюю еделю
     * @key page - страница
     * @key items - фрилансеров на странице
     */
    public function setFilter ($filter) {
        if (!is_array($filter)) {
            return false;
        }
        foreach ($filter as $key=>$value) {
            $this->$key = $value;
        }
        if (!$this->page) $this->page = 1;
        if (!$this->items) $this->items = DEFAULT_ITEMS_PER_PAGE;
        if ($this->items > 100) $this->items = MAX_ITEMS_PER_PAGE;
        $this->offset = ($this->page - 1) * $this->items;
        
        if ($this->prof || $this->opinions || $this->portfolio || $this->visits || $this->projects) {
            $this->is_filter = true;
        } else {
            $this->is_filter = false;
        }
        if ($this->prof && $this->opinions && $this->portfolio && $this->visits && $this->projects) {
            $this->full_filter = true;
        } else {
            $this->full_filter = false;
        }
    }
    
    /**
     * Возвращает количество пользователей, удовлетворяющих условиям выборки
     * Список пользователей сохраняется в $frls, $pageFrls
     * 
     * @param  int $count возвращает количество записей удовлтворяющих условиям выборки
     * @param  array $filter Параметры фильтра
     * @param  int $page номер текущей страницы
     * @return array
     */
    function searchFrls() {
        global $DB;
        
        if ($this->is_filter) {
            $sql_total = $this->getResTotal() . $this->getFrom() . $this->getExt() . $this->getCond();
        } else {
            $sql_total = $this->getResTotal() . $this->getFrom();
        }
        $totalFrls = $DB->col($sql_total);
        if (!is_array($totalFrls) || $DB->error) {
            return 0;
        }
        $this->totalFrls = $totalFrls;
        $this->count = count($totalFrls);
        $this->pages = ceil($this->count / $this->items);
        
        // находим фрилансеров для нужной страницы
        $sql_limit = ' LIMIT ' . $this->items . ' OFFSET ' . $this->offset;
        $sql = $this->getRes() . $this->getFrom() . $this->getExt() . $this->getCond() . $this->getLimit();
        $pageFrls = $DB->rows($sql);
        
        if (!is_array($pageFrls) || $DB->error) {
            return 0;
        }
        
        $this->frls = $frls;
        $this->pageFrls = $pageFrls;
        
        return count($this->pageFrls);
    }
    
    /**
     * возвращает (ресурс) полный список фрилансеров, для excel-отчета
     */
    private function searchFrlsTotal () {
        global $DB;
        
        $sql = $this->getResExcel() . $this->getFrom() . $this->getExt() . $this->getCond();
        $res = $DB->squery($sql);
        return $res;
    }
            
    
    /**
     * генерирует отчет в Excel, данные берет из $frls - полный список фрилансеров 
     */
    public function generateReport () {
        
        require_once( 'Spreadsheet/Excel/Writer.php' );
        
        // поиск фрилансеров
        $res = $this->searchFrlsTotal();
        
        // имя файла
        $fileName = 'фрилансеры (';
        if ($this->is_filter) {
            $fileName .= 'с фильтром';
        } else {
            $fileName .= 'без фильтра';
        }
        $fileName .= ')';
        $fileName .= '.xls';
        
        // создаем документ
        $workbook = new Spreadsheet_Excel_Writer();
        $workbook->setVersion( 8 );
        
        // создаем лист
        $worksheet =& $workbook->addWorksheet( '1' );
        $worksheet->setInputEncoding( 'CP1251' );
        
        // ширина ячеек
        $worksheet->setColumn(1, 2, 20);
        $worksheet->setColumn(3, 6, 25);
        $worksheet->setColumn(7, 7, 30);
        
        // стили
        $th_sty = array('FontFamily'=>'Arial', 'Size'=>10, 'Align'=>'center', 'Border'=>1, 'BorderColor'=>'black', 'Bold'=>1, 'Text_wrap'=>true);
        $format_top   =& $workbook->addFormat( $th_sty );
        
        // заголовок листа
        $worksheet->write( 0, 0, 'ООО "Ваан"' );
        $worksheet->write( 2, 1, 'Фрилансеры' );
        
        $line = 4;
        
        if ($this->is_filter) {
            $worksheet->write($line++, 1, 'Параметры фильтра:');
            if ($this->prof) {
                $worksheet->write($line++, 1, 'только со специальностью');
            }
            if ($this->opinions) {
                $worksheet->write($line++, 1, 'только с 3-мя и более мнениями/рекомендациями');
            }
            if ($this->portfolio) {
                $worksheet->write($line++, 1, 'только с 10-ю и более работами в портфолио');
            }
            if ($this->visits) {
                $worksheet->write($line++, 1, 'только с 5-ю и более заходами на сайт за последний месяц и 1 и более - за последнюю неделю');
            }
            if ($this->projects) {
                $worksheet->write($line++, 1, 'только с 5-ю и более ответами на проекты за последний месяц');
            }
        }
        
        $line = $line + 2;
       
        // заголовок таблицы
        $aHeader = array('№ п/п', 'Фрилансер', 'Специализация', 'Мнений/рекомендаций', 'Работ в портфолио', 'Посещений за месяц', 'Посещений за неделю', 'Ответов на проекты за месяц');
        
        for ( $i = 0; $i<count($aHeader); $i++ ) {
            $worksheet->write( $line, $i, $aHeader[$i], $format_top );
        }
        
        if (!res) {
            $worksheet->write($line, 0, 'Ни одного фрилансера не найдено');
        }
        
        $num = 1;
        while ($frl = pg_fetch_assoc($res)) {
            
            $line++;
            
            $name = $frl['uname'] .' '. $frl['usurname'] .' ['. $frl['login'].']';
            $rowData = array(
                $num,
                $name,
                $frl['param_spec'] ? 'Есть' : 'Нет',
                $frl['param_opinions'] ? $frl['param_opinions'] : 0,
                $frl['param_jobs'] ? $frl['param_jobs'] : 0,
                $frl['param_m_visits'] ? $frl['param_m_visits'] : 0,
                $frl['param_w_visits'] ? $frl['param_w_visits'] : 0,
                $frl['param_projects'] ? $frl['param_projects'] : 0
            );

            $worksheet->writeRow($line, 0, $rowData);
            $num++;
        }
        
        
        // отправляем на скачивание
        $workbook->send($fileName);
        
        // закрываем документ
        $workbook->close();
    }
    
    // составные части запроса
    private function getRes () {
        // колонки результата
        $sql_res  = 'SELECT DISTINCT frl.uid, frl.uname, frl.usurname, frl.login, frl.role, frl.is_pro, frl.is_pro_test, frl.is_team, frl.photo, frl.warn, 
            frl.email, frl.reg_ip, frl.last_ip, frl.is_banned, frl.ban_where, frl.self_deleted, frl.safety_phone, frl.safety_only_phone, 
            frl.safety_bind_ip, frl.active, frl.pop, frl.phone, frl.phone_1, frl.phone_2, frl.phone_3';
        $sql_res .= ', frl.spec_orig param_spec';
        // рекомендации
        $sql_res .= ', (uc.ops_emp_null + uc.ops_emp_plus + uc.ops_emp_minus + uc.sbr_opi_null + uc.sbr_opi_plus + uc.sbr_opi_minus) param_opinions';
        // портфолио
        $sql_res .= ', po.jobs param_jobs';
        // визиты
        $sql_res .= ', vi.m_visits param_m_visits, vi.w_visits param_w_visits';
        // проекты
        $sql_res .= ', p_o.projects param_projects';
        
        return $sql_res;
    }
    private function getResExcel () {
        // колонки результата
        $sql_res  = 'SELECT DISTINCT frl.uid, frl.uname, frl.usurname, frl.login';
        $sql_res .= ', frl.spec_orig param_spec';
        // рекомендации
        $sql_res .= ', (uc.ops_emp_null + uc.ops_emp_plus + uc.ops_emp_minus + uc.sbr_opi_null + uc.sbr_opi_plus + uc.sbr_opi_minus) param_opinions';
        // портфолио
        $sql_res .= ', po.jobs param_jobs';
        // визиты
        $sql_res .= ', vi.m_visits param_m_visits, vi.w_visits param_w_visits';
        // проекты
        $sql_res .= ', p_o.projects param_projects';
        
        return $sql_res;
    }
    private function getResTotal () {
        $sql_res_total = 'SELECT DISTINCT frl.uid';
        return $sql_res_total;
    }
    private function getFrom () {
        $sql_from = ' FROM freelancer frl';
        return $sql_from;
    }
    private function getExt () {
        // мнения
        $sql_ext = " LEFT JOIN users_counters uc
                    ON uc.user_id = frl.uid";
        // портфолио
        $sql_ext .= " LEFT JOIN
                    (SELECT DISTINCT prt.user_id user_id, count(*) jobs
                    FROM portfolio prt
                    GROUP BY prt.user_id) po
                    ON po.user_id = frl.uid";
        
        // визиты
        $sql_ext .= " LEFT JOIN
                    (SELECT DISTINCT    r2m.user_id uid, 
                                        count(r2m.*) m_visits, 
                                        sum(CASE WHEN r2m._date > (NOW() - interval '1 week') THEN 1 ELSE 0 END) w_visits
                    FROM rating_2month_log r2m
                    WHERE r2m._date > (NOW() - interval '1 month')
                        AND r2m.factor = B'000000000001000000000000000000000'
                    GROUP BY r2m.user_id) vi
                    ON vi.uid = frl.uid";

        // портфолио
        $sql_ext .= " LEFT JOIN
                    (SELECT DISTINCT prj.user_id user_id, count(*) projects
                    FROM projects_offers prj
                    WHERE post_date > (NOW() - interval '1 month')
                    GROUP BY prj.user_id) p_o
                    ON p_o.user_id = frl.uid";
        return $sql_ext;
    }
    private function getCond () {
        // условия
        $sql_cond = " WHERE frl.uid > 0
                    AND frl.is_banned = B'0'
                    AND frl.active = TRUE";
        
        if ($this->prof) { // наличие специализации
            $sql_cond .= ' AND spec_orig > 0';
        }        
        if ($this->opinions) { // наличие минимум 3-х подтвержденных мнений
            $sql_cond .= ' AND (uc.sbr_opi_plus + uc.paid_advices_cnt) >= 3';
        }
        if ($this->portfolio) { // наличие работ в портфолио
            $sql_cond .= ' AND po.jobs >= 10';
        }
        if ($this->visits) { // количество посещений
            $sql_cond .= ' AND vi.m_visits >= 5 AND vi.w_visits >= 1';
        }
        if ($this->projects) { // отклики на проекты
            $sql_cond .= ' AND p_o.projects >= 5';
        }
        return $sql_cond;
    }
    private function getLimit () {
        $sql_limit = ' LIMIT ' . $this->items . ' OFFSET ' . $this->offset;
        return $sql_limit;
    }
}
