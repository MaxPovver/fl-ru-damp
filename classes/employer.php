<?
/**
 * Подключаем файл основных функиця системы
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
/**
 * Подключаем файл для работы с пользователями
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

/**
 * Класс для работы с работодателями
 *
 */
class employer extends users 
{
	/**
	 * Закладки, битовое поле (1000 - проекты, 0100 - Информация, 0010 - Блог, 0001 - Дефиле)
	 *
	 * @var bit(4) 
	 */
    public $tabs;
    /**
     * Настройка отображение блоков 11111 = [Команда|Коммуна|Приглашение в коммуну|В избранном у работодателей|В избранном у фрилансеров]
     *
     * @var bit(5)
     */
	public $blocks;
	/**
	 * Дата дня рождения
	 *
	 * @var string
	 */
	public $birthday;
	/**
	 * Ссылка на сайт пользователя
	 *
	 * @var string
	 */
	public $site;
	/**
	 * Телефон
	 *
	 * @var string
	 */
	public $phone;
	/**
	 * Ссылка на юзера в livajournal
	 *
	 * @var string
	 */
	public $ljuser;
	/**
	 * Резюме
	 *
	 * @var string
	 */
	public $resume;
	/**
	 * Компания
	 *
	 * @var string
	 */
	public $company;
	/**
	 * Название компании
	 *
	 * @var string
	 */
	public $compname;
	/**
	 * Куплена ли услуга ПРО
	 *
	 * @var boolean
	 */
	public $is_pro;
	
	/**
	 * Обновление информации пользователя
	 *
	 * @param string $fid ИД пользователя
	 * @return string Сообщение об ошибке
	 */
	function UpdateInform($fid){
		while (strlen($this->blocks) < 1)
		$this->blocks .= '1';
		$error = $this->Update($fid, $res);
		return ($error);
	}

	/**
	 * Возвращает список работодателей для каталога работодателей.
	 * Кешируется! Время - 1800 сек
	 *
	 * @param integer $emp_pp		кол-во записей для вывода каталога
	 * @param integer $offset		с какой начинать вывод каталога
	 * @param integer $count		вернуть сюда общее кол-во работодателей в каталоге.
	 * @return array				массив с каталогом
	 */
	function GetAllMain($emp_pp, $offset, &$count = false) {
        global $DB, $ourUserLoginsInCatalog;
        /*if($ourUserLoginsInCatalog) {
           $our_cond = "OR login IN ('" . implode("','", $ourUserLoginsInCatalog) . "')";
        }*/
        
        // Оптимизировано под индекс "ix employer/catalog"!
        $where = "WHERE is_banned = '0' AND ignore_in_stats = false AND (role::bit(6) & B'010111')::int = 0 AND is_team = FALSE";

		$sql = "
          SELECT t.is_pro as payed, t.uname, t.usurname, t.login, t.uid, t.photo, t.last_time, t.reg_date, t.logo,
                 t.compname, t.site, t.company, rating_get(t.rating, t.is_pro, t.is_verify) as rating, t.is_team, t.role, t.is_verify,
                 uc.ops_frl_null as se, uc.ops_frl_plus as sg, uc.ops_frl_minus as sl,
                 c.pay_projects as projects,
                 sm.completed_cnt as sbr_count, ch.id AS is_on_mod 
          FROM (
            SELECT * FROM employer {$where} ORDER BY is_pro DESC, rating DESC LIMIT $emp_pp OFFSET $offset
          ) as t 
          LEFT JOIN users_counters uc ON uc.user_id = t.uid 
          LEFT JOIN sbr_meta sm ON sm.user_id = t.uid
          LEFT JOIN employer_counters c ON c.user_id = t.uid
          LEFT JOIN users_change ch ON ch.user_id = t.uid AND ch.ucolumn = 'company' 
          ORDER BY t.is_pro DESC, t.rating DESC
        ";
        
		$ret = $DB->cache(1800)->rows($sql);
		if($ret && $count !== false) {
		    $count = (int)$DB->cache(1800)->val("SELECT COUNT(1) FROM employer {$where}");
		}
		return $ret;
	}
	
	
	/**
	 * Обновление логотипа пользователя
	 *
	 * @param integer $fid ИД пользователя
	 * @param object  $logo CFile, @see class CFile();
	 * @param integer $del  Удалить либо обновить (1,0)
	 * @return string $error Сообщение об ошибке
	 */
    function UpdateLogo($fid, $logo, $del){
        // если юзер меняет логотип пока предыдущий еще не отмодерирован - будет история изменений
        $aChange  = $GLOBALS['DB']->row( "SELECT id, old_val, new_val FROM users_change WHERE user_id = ?i AND ucolumn = 'logo'", $fid );
        $aDelFile = array(); // файлы которые нужно будет удалять сразу
        
        $dir = get_login($fid);
        $err = "";
        if (!$dir) $error = "Имя пользователя не определено";
        $this->logo = $this->GetField($fid, $err, "logo");
        $old_logo = $this->logo;
        $error .= $err;
        if ($del == 1){
            $this->logo = "";
        } else {
            if ($logo && !$error) {
                $logo->max_size = 50000;
                $logo->max_image_size = array('width'=>150, 'height'=>100);
                $logo->resize = 0;
                $logo->proportional = 1;
                $logo->topfill = 1;
                $logo->allowed_ext = $GLOBALS['graf_array'];
                $this->logo = $logo->MoveUploadedFile($dir."/logo");
                $error .= $logo->StrError('<br />');
                if (!$error && !$logo->img_to_small("sm_".$this->logo,array('width'=>50, 'height'=>50)))
                    $error .= "Невозможно уменьшить картинку.";
            }
        }
        
        if ( !$error ) {
            $error .= $this->Update($fid, $res);
            
            // определяемся какие файлы нужно удалить
            if ( $del == 1 ) { // удаляем логотип
                if ( $aChange ) { // если хранили версии логотипа на случай возврата - то грохаем обе
                    if ( $aChange['old_val'] ) {
                        $aDelFile[] = $aChange['old_val'];
                    }
                    
                    $aDelFile[] = $aChange['new_val'];
                }
                else { // иначе просто грохаем логотип
                    $aDelFile[] = $old_logo;
                }
            }
            elseif ( $logo ) { // меняем логотип
                if ( $aChange && $aChange['new_val'] ) { // грохаем только промежуточную версию, если была
                    $aDelFile[] = $aChange['new_val'];
                }
            }
        }
        
        // удаление не нужных файлов (если нет ошибок при сохранении разумеется)
        if ( $aDelFile && !$error ) {
            foreach ( $aDelFile as $file ) {
                $logo->Delete(0, "users/".substr($dir, 0, 2)."/".$dir."/logo/", $file);
                $logo->Delete(0, "users/".substr($dir, 0, 2)."/".$dir."/logo/", "sm_".$file);
            }
        }
        return ($error);
    }
    

	/**
	 * Информация о рейтинге работадателя и кол-ве хитов на его страницу.
	 * @param    string   $login   login работадателя
	 * @param    string   $err     возвращает возможную ошибку
	 * @return   array             массив с данными
	 */
	function GetAdditInfo($login, &$err)
    {
        global $DB;

        $ret = $DB->row('
            SELECT 
                hits, 
                rating_get(rating, is_pro, is_verify) as rating, 
                hitstoday 
            FROM employer 
            WHERE login = ?', $login);
        
        $err .= $DB->error;
        if ($err)  {
            $err = parse_db_error($err);
        }
        
		return $ret;
	}

     /**
	 * Обновление закладок пользователя
	 *
	 * @param integer  $fid    Ид пользователя
	 * @param integer  $prjs   Проекты (0 либо 1)
	 * @param integer  $info   Информация (0 либо 1)
	 * @param integer  $jornal Блоги (0 либо 1)
	 * @param integer  $defile Дефиле (0, 1)
	 * @return string Сообщение об ошибке
	 */
    function UpdateTabs($fid, $prjs, $info, $jornal, $defile, $shop){
        $this->tabs = (int)$prjs.(int)$info.(int)$jornal.(int)$defile.(int)$shop;
		while (strlen($this->tabs) < 5)
            $this->tabs .= '0';
		$error = $this->Update($fid, $res);
		return ($error);
	}


    /**
     * Пересчет счетчиков работодателей (проекты и платные проекты)
     * Выполняется раз в пол часа
     *
     * @return boolen  Успех
     */
    function UpdateCounters() {
        $GLOBALS['DB']->query("
            INSERT INTO
                employer_counters (user_id, projects, pay_projects)
            SELECT
                --user_id, COUNT(id), COUNT(NULLIF(payed, 0))
                user_id, 0, COUNT(id)
            FROM
                projects
            WHERE
                payed <> 0
            GROUP BY
                user_id
        ");
        return TRUE;
    }
    
    /**
     * сохраняет информацию о состоянии блока "Рекомендованные фрилансеры" 
     * @param bool $status
     * 
     * @return bool "Статус сохранен"
     */
    public static function SetRcmdFrlStatus($status) {
        if (!isset($status)) return false;
        global $DB;
        $data = array('recommended_frl_status' => $status);
        $uid = get_uid(0);
        $DB->update("employer", $data, "uid = ?i", $uid);
        return true;
    }
    
    /**
     * возвращает статус блока "Рекомендованные фрилансеры"
     * true - открыт/error, false - скрыт
     * @global type $DB 
     */
    public static function GetRcmdFrlStatus() {
        global $DB;
        $uid = get_uid(0);
        $sql = "SELECT recommended_frl_status FROM employer WHERE uid = ?i";
        $val = $DB->val($sql, $uid);
        if ($val === "f") {
            return false;
        } else {
            return true;
        }
    }
    /**
     * Получить PRO работодателей создавших конкурсы без бюджета
     * */
    public static function GetPROEmployersCreatedProjectsWithoutPrice() {
    	global $DB;
        $sql = "SELECT p.id as prj_id, e.uid, e.uname, e.usurname, e.login, e.subscr, e.email 
                FROM projects p 
                INNER JOIN employer e ON e.uid = p.user_id  
                LEFT JOIN
                    orders o ON o.from_id = e.uid
                WHERE (kind = 2 OR kind = 7) AND cost = 0 AND post_date + interval '1 day' > NOW()
                AND o.payed=true AND o.from_date<=now() AND o.from_date+o.to_date+COALESCE(freeze_to, '0')::interval >= now() AND o.active='true'
                AND NOT (freeze_from_time IS NOT NULL AND NOW() >= freeze_from_time::date AND NOW() < freeze_to_time)
                ";
        $result   = $DB->rows($sql);
        return $result;
    }
    /**
     * Получить не PRO работодателей создавших конкурсы без бюджета
     * */
    public static function GetNoPROEmployersCreatedProjectsWithoutPrice() {
    	global $DB;
        $sql = "SELECT p.id as prj_id, e.uid, e.uname, e.usurname, e.login, e.subscr, e.email 
                FROM projects p 
                INNER JOIN employer e ON e.uid = p.user_id
                LEFT JOIN
                    orders o ON o.from_id = e.uid  
                WHERE (kind = 2 OR kind = 7) AND cost = 0 AND post_date + interval '1 day' > NOW()
                AND                 
                (o.payed IS NULL
                 OR o.from_date+o.to_date+COALESCE(freeze_to, '0')::interval <= now()
                 OR o.active='false'
                 OR (NOW() <= freeze_from_time::date AND NOW() > freeze_to_time)
                )
                ";
        $result   = $DB->rows($sql);
        return $result;
    }
    /**
     * Получить зарегистрировавшихся за последние сутки работодателей
     * */
    public static function GetNewEmployer() {
    	global $DB;
    	$rows = $DB->rows("SELECT e.uid, e.email, e.uname, e.usurname, e.login, usk.key AS unsubscribe_key 
                           FROM employer AS e
                           LEFT JOIN users_subscribe_keys AS usk
                             ON e.uid = usk.uid
                           WHERE NOW() - reg_date <= '24 hours'");
    	return $rows;
    }
    /**
     * Функция делает запрос данных пользователей, которым необходимо отправить уведомление о том, что кандидатов / исполнителей в проекте заблокировали
     * @param $ids идентификаторы пользователей, которые были забанены 
     * @return sql resource
     * */
    public static function GetEmployersBlockedCandidates($ids) {
        $ids = is_array( $ids ) ? array_unique( $ids ) : array( $ids );
        
        $sQuery = "SELECT m.* FROM (
            SELECT p.id, p.name, f.login, f.uname, f.usurname, e.email, e.login AS e_login, e.uname AS e_name, e.usurname AS e_surname, po.position, 'project' AS lnk, 4 AS cnf 
            FROM projects_contest_offers po 
            INNER JOIN projects p ON p.id = po.project_id 
            INNER JOIN freelancer f ON f.uid = po.user_id 
            INNER JOIN employer e ON e.uid = p.user_id 
            LEFT JOIN projects_blocked pb ON pb.project_id = p.id 
            WHERE po.user_id IN (?l) AND po.position > 0 
                AND pb.project_id IS NULL AND p.no_risk = false AND p.closed = false 
                AND e.is_banned = '0' AND e.is_active = true
            
            UNION 
            
            SELECT p.id, p.name, f.login, f.uname, f.usurname, e.email, e.login AS e_login, e.uname AS e_name, e.usurname AS e_surname, NULL AS position, 'project' AS lnk, 1 AS cnf 
            FROM projects p 
            INNER JOIN freelancer f ON f.uid = p.exec_id 
            INNER JOIN employer e ON e.uid = p.user_id 
            LEFT JOIN projects_blocked pb ON pb.project_id = p.id 
            WHERE p.kind NOT IN (2,7) AND pb.project_id IS NULL AND p.no_risk = false AND p.closed = false AND p.exec_id IN (?l) 
                 AND e.is_banned = '0' AND e.is_active = true
            
            UNION 
            
            SELECT p.id, p.name, f.login, f.uname, f.usurname, e.email, e.login AS e_login, e.uname AS e_name, e.usurname AS e_surname, NULL AS position, 'project' AS lnk, 2 AS cnf 
            FROM projects_offers po 
            INNER JOIN projects p ON p.id = po.project_id 
            INNER JOIN freelancer f ON f.uid = po.user_id 
            INNER JOIN employer e ON e.uid = p.user_id 
            LEFT JOIN projects_blocked pb ON pb.project_id = p.id 
            WHERE p.kind NOT IN (2,7) AND p.exec_id IS NULL AND po.user_id IN (?l) AND po.selected = true 
                AND pb.project_id IS NULL AND p.no_risk = false AND p.closed = false 
                AND e.is_banned = '0' AND e.is_active = true
            
            UNION 
            
            SELECT p.id, p.name, f.login, f.uname, f.usurname, e.email, e.login AS e_login, e.uname AS e_name, e.usurname AS e_surname, NULL AS position, 'project' AS lnk, 3 AS cnf 
            FROM projects_contest_offers po 
            INNER JOIN projects p ON p.id = po.project_id 
            INNER JOIN freelancer f ON f.uid = po.user_id 
            INNER JOIN employer e ON e.uid = p.user_id 
            LEFT JOIN projects_blocked pb ON pb.project_id = p.id 
            LEFT JOIN projects_contest_offers pp ON pp.project_id = p.id AND pp.position > 0 
            WHERE po.user_id IN (?l) AND po.selected = true AND pp.project_id IS NULL 
                AND pb.project_id IS NULL AND p.no_risk = false AND p.closed = false 
                AND e.is_banned = '0' AND e.is_active = true
        ) AS m
        ORDER BY m.cnf ASC";
        
        $mRes = $GLOBALS['DB']->query( $sQuery, $ids, $ids, $ids, $ids, $ids );
        return $mRes;
    }
    
    
    
    
    /**
     * Получаем работодателей подписанных на рассылку "Получать ежедневную рассылку"
     * 
     * @param array $error
     * @param int $page
     * @param int $offset
     * 
     * 
     */
    public static function GetPrjRecps(&$error, $page, $offset = 1000){
        
        global $DB;
        
        $from = $offset;
        $to = ($page-1)*$offset;
        
	$sql = "SELECT 
                    EXTRACT(YEAR FROM e.reg_date) AS reg_date_year,
                    EXTRACT(DAY FROM NOW() - e.reg_date) AS reg_days_ago,
                    DATE_PART('year', NOW()) - DATE_PART('year', e.last_time) AS last_years_ago,
                    --e.reg_date, 
                    --e.last_time, 
                    e.uname, 
                    e.usurname, 
                    e.login, 
                    e.email, 
                    usk.key AS unsubscribe_key, 
                    e.uid 
                FROM employer AS e
                LEFT JOIN users_subscribe_keys AS usk ON e.uid = usk.uid 
                WHERE 
                    e.subscr & B'0000000000001000' = B'0000000000001000'
                    AND e.is_banned = B'0'
                ORDER BY e.uid 
                LIMIT {$from} OFFSET {$to}";
        
        //$ret = $DB->rows($sql, $offset, ($page-1)*$offset);
        //$error = $DB->error;
        
        $res = $DB->query($sql);          
        $ret = pg_fetch_all($res);       
  
        return $ret;
    }
    
    
    
    
    
    
    
    
    
    
  /**
   * Возвращает фрилансеров, которые подписаны на уведомления в новых проектах.
   * @param    string    $error      возвращает возможную ошибку
   * @param    integer   $page       номер страницы с которой начать вывод (в каждой странице $offset пользователей)
   * @param    integer   $offset     количество пользователей, которых необходимо получить
   * @param    array     $exclude_users      идентификаторы пользователей, которым уже отправлена рассылка о новых проектах
   * @return   array                 данные о подписаных пользователях
   */
    
    /*
  function GetPrjRecps(&$error, $page, $offset = 1000, $exclude_users = array()) {
    global $DB;
    // поле mailer это сумма всех выбранных pow(2, id специализации)
    $exclude = "";
    if ( count($exclude_users) ) {
        $exclude = "AND f.uid NOT IN (" . join(",", $exclude_users) . ")";
    }
    
	$sql = "SELECT 
                    f.reg_date, 
                    f.last_time, 
                    f.uname, 
                    f.usurname, 
                    f.login, 
                    f.email, 
                    f.mailer, 
                    f.mailer_str, 
                    usk.key AS unsubscribe_key, 
                    f.uid 
                FROM freelancer AS f 
                LEFT JOIN users_subscribe_keys AS usk ON f.uid = usk.uid 
                WHERE 
                    mailer > 0  
                    AND is_banned = '0' {$exclude} 
                ORDER BY f.uid 
                LIMIT ?i OFFSET ?i";
                    
		$ret = $DB->mrows($sql, $offset, ($page-1)*$offset);
                
                $error = $DB->error;
                
		return $ret;
	}    
    
    
    */
    
    
    /**
     * Возвращает id заказчика по его email
     *
     * @param string $email             почта пользователя
     *
     * @return integer                  id пользователя или 0 в случае неуспеха
     */
    function GetUidByEmail($email=''){
        global $DB;
        if (!$email) return 0;
        $sql = "SELECT uid FROM employer WHERE (lower(email)=?) LIMIT 1";
        return $DB->val($sql, mb_strtolower($email));
    }
    
    
    
    
}
?>
