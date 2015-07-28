<?php
/**
 * Подключаем файл с основными функциями системы
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/stdf.php';
/**
 * Класс для работы с предложениями и комментариями в конкурсах.
 */
class contest {

    /**
     * id проекта с которым в текущий момент идет работа
     *
     * @var integer
     */
	public $pid = 0;

    /**
     * uid пользователя, который работает с конкурсом
     *
     * @var integer
     */
	public $uid = 0;

    /**
     * пользователь работодатель?
     *
     * @var boolean
     */
	public $is_emp = FALSE;

    /**
     * пользователь автор этого конкурса?
     *
     * @var boolean
     */
	public $is_owner = FALSE;

    /**
     * пользователь модератор, админ или малый админ?
     *
     * @var boolean
     */
	public $is_moder = FALSE;

    /**
     * пользователь со статусом pro?
     *
     * @var boolean
     */
	public $is_pro = FALSE;
	
    /**
     * пользователь $this->uid забанен в этом конкурсе?
     *
     * @var boolean
     */
	public $is_banned = FALSE;

    /**
     * пользователь $this->uid уже добавлял предложение в этом конкурсу?
     *
     * @var boolean
     */
	public $has_offer = FALSE;

    /**
     * id нового предложения (устанавливается после $this->CreateOffer)
     *
     * @var integer
     */
	public $new_oid = 0;

    /**
     * id нового комментария (устанавливается после $this->CreateComment)
     *
     * @var integer
     */
	public $new_cid = 0;

    /**
     * массив с выбранными предложениями и комментариями (устанавливается после $this->GetOffers)
     *
     * @var array
     */
	public $offers = array();

    /**
     * массив с выбранным предложением и комментариями к нему (устанавливается после $this->GetOffer)
     *
     * @var array
     */
	public $offer = array();

    /**
     * статистика по конкурсу (устанавливается после $this->GetOffers с учетом фильтра или $this->GetStat для всех предложений)
     *
     * @var array
     */
	public $stat = array();

    /**
     * если конкурс завершен и победители объявлены, здесь хранятся данные о побежденных предложениях (ссылки на элементы в $this->offers)
	 * 1-ый элемент -> 1 место и т.д.
     *
     * @var array
     */
	public $postions = array();
	
	/*
	
	В массиве $this->offers кроме предложений, хранится дерево комментариев, выглядит это примерно так
	  $this->offers[0]['comments'] <- массив комментариев для первого предложения
	  $this->offers[0]['comments'][0] <- первый комментарий для первого предложения
	  $this->offers[0]['comments'][0]['comments'][0] <- первый комментарий являющийся ответом на $this->offers[0]['comments'][0]
	  ну и т.д. :)
	  
	Массив $this->offer аналогичен $this->offers, только содержит одно предложение
	
	Массиве $this->stat содержит следующие индексы:
	  offers  -  количество предложений
	  candidates  -  количество кандидатов
	  banned  -  количество забанненный
	  offer  -  массив, offer['id'] - id самой популярной работы, offer['rating'] - рейтинг этой работы
	  leader -  массив с данными самого активного пользователя array('uid', 'login', 'uname', 'usurname', 'photo')
	  
	*/
	
	const CHARS_ON_OFFER = 3000;


	
	/**
	 * Конструктор.
	 * @param  integer  $pid       id проекта (для некоторых методов, его можно не указывать)
	 * @param  integer  $uid       uid пользотеля, который работает с конкурсом
	 * @param  boolean  $is_emp    пользователь работодатель?
	 * @param  boolean  $is_owner  пользователь автор этого конкурса?
	 * @param  boolean  $is_moder  пользователь модератор, админ или малый админ?
	 * @param  boolean  $is_pro    у пользователя статус pro?
	 */
	public function __construct($pid, $uid, $is_emp=FALSE, $is_owner=FALSE, $is_moder=FALSE, $is_pro=FALSE) {
        global $DB;
		$this->pid = intval($pid);
		$this->uid = intval($uid);
		$this->is_emp   = (bool) $is_emp;
		$this->is_owner = (bool) $is_owner;
		$this->is_moder = (bool) $is_moder;
		$this->is_pro   = (bool) $is_pro;
		$sql = "SELECT COUNT(*) FROM projects_contest_blocked WHERE project_id = ?i AND user_id = ?i";
		$this->is_banned = (bool) $DB->val($sql, $this->pid, $this->uid);
	}

    /**
     * Получение статуса существования предложения конкретного пользователя по конкретному проекту (есть/нет)
     *
     * @param integer $prj_id          id проекта
     * @param integer $user_id         id пользователя
     *
     * @return boolean                 да/нет
     */
	public function IsContestOfferExists($prj_id, $user_id) {
        global $DB;
        $ret = false;
        if ($user_id > 0) {
			$sql = "
				SELECT
					offers.id
				FROM
					projects_contest_offers AS offers
				LEFT JOIN
					freelancer f ON offers.user_id = f.uid
				LEFT JOIN
					projects_contest_blocked AS blocked ON blocked.user_id = offers.user_id AND blocked.project_id = ?i
				WHERE 
				    offers.project_id = ?i AND offers.user_id=?i AND f.is_banned = '0' 
			";
          $ret = $DB->row($sql, $prj_id, $prj_id, $user_id);
      
        }
  
      if ($ret['id']) return true;
      else            return false;
	}
	
	/**
	 * Инициализирует список предложений с комментариями ($this->offers) и статистику ($this->stat)
	 * @param   string   $filter  фильтр, может принимать значение candidates для выбора предложений только кандидатов
	 * @return  boolean           успех операции
	 */
	public function GetOffers($filter='', $set_read=true) {
        global $DB;
		// дата последнего посещения
        if(hasPermissions('projects')) {
    		$sql = "SELECT * FROM projects_watch WHERE prj_id = ?i AND user_id IN (SELECT user_id FROM permissions_groups_users) ORDER BY last_view DESC LIMIT 1";
        } else {
    		$sql = "SELECT * FROM projects_watch WHERE prj_id = ?i " . $DB->parse("AND user_id = ?i", $this->uid);
        }
        $watch = $DB->row($sql, $this->pid);
		if (!$watch) $watch = array('status'=>'f', 'last_view'=>'1970-01-01 00:00:00');
        if(hasPermissions('projects') && $watch['user_id']) $watch['user_id'] = $this->uid;

		// предложения
		$sql = "
			SELECT
				offers.*, 
				f.uid, f.login, f.uname, f.usurname, f.photo, f.is_pro, f.is_profi, f.is_pro_test, f.role, f.is_banned as usr_banned, f.ban_where as usr_ban_where, f.warn, f.spec, f.is_team, f.is_verify, f.photo_modified_time, f.reg_date, f.modified_time,
				blocked.user_id::boolean AS user_blocked, sbr_meta.completed_cnt
			FROM
				projects_contest_offers AS offers
			LEFT JOIN
				freelancer f ON offers.user_id = f.uid
			LEFT JOIN
				sbr_meta ON sbr_meta.user_id = f.uid
			LEFT JOIN
				projects_contest_blocked AS blocked ON blocked.user_id = offers.user_id AND blocked.project_id = ?i
			WHERE 
			    offers.project_id = ?i AND f.is_banned = '0' 
			ORDER BY
				f.is_pro DESC, post_date
		";

		$i = 0;
		$today = mktime(0, 0, 0);
		
		$offidx = array();
		$in = array();
        $res = $DB->rows($sql, $this->pid, $this->pid);
        if($res) {
            foreach($res as $row) {
    			$this->offers[$i] = $row;
    			$in[] = $this->offers[$i]['id'];
    			if ($this->offers[$i]['user_id'] == $this->uid) $this->has_offer = TRUE;  // делал ли уже пользователь предложение в этом конкурсе
    			if (!$this->is_moder) $this->offers[$i]['msg_count'] = 0;                 // сброс кол-ва сообещний, для правильно пересчета
    			if ($this->uid && $this->offers[$i]['post_date'] > $watch['last_view'] && $this->offers[$i]['user_id'] != $this->uid) $this->offers[$i]['is_new'] = TRUE;  // видели ли это предложение?
    			//if (strtotime($this->offers[$i]['post_date']) > $today) $this->stat['offers_today']++;  // кол-во предложений сегодня (стастика)
                if($this->offers[$i]['is_deleted']=='f') {
        			$this->stat['offers']++;  // кол-во предложений (статистика)
                }
    			if ($this->offers[$i]['selected'] == 't' && $this->offers[$i]['is_deleted'] == 'f') $this->stat['candidates']++;  // кол-во кандидатов (статистика)
    			// если победители уже определены
    			if ($this->offers[$i]['position']) {
    				$this->positions[ $this->offers[$i]['position'] ] = &$this->offers[$i];
    			}
    			$offidx[$row['id']] = $i++;  // массив ссылок на предложения по их id
    		}
        }
		if ($filter) {
			//$this->GetStat();
			$sql = "SELECT COUNT(*) FROM projects_contest_offers 
			LEFT JOIN freelancer f ON projects_contest_offers.user_id = f.uid
			WHERE project_id = ?i AND user_id = ?i AND f.is_banned = '0' ";
			$this->has_offer = (bool) $DB->val($sql, $this->pid, $this->uid);
		}
		if (!$this->offers) return TRUE;  // если нет предложений, идти дальше нет смысла
		// кол-во заблокированых пользователей (статистика)
		$sql = "SELECT COUNT(*) FROM projects_contest_blocked b 
		INNER JOIN freelancer f ON b.user_id = f.uid 
		INNER JOIN projects_contest_offers o ON o.user_id = f.uid AND o.project_id = b.project_id 
		 WHERE b.project_id = ?i AND f.is_banned = '0' AND o.is_deleted = false";
		$this->stat['banned'] = $DB->val($sql, $this->pid);

		// комментарии
		$sql = "
			SELECT
				msgs.*, 
				users.login, users.uname, users.usurname, users.photo, users.is_profi, users.is_pro, users.is_team, users.is_pro_test, users.role, users.is_banned, users.ban_where, users.warn, 
				blocked.user_id::boolean AS user_blocked, sbr_meta.completed_cnt
			FROM
				projects_contest_msgs AS msgs
			LEFT JOIN
				users ON msgs.user_id = users.uid
			LEFT JOIN
				sbr_meta ON sbr_meta.user_id = msgs.user_id
			LEFT JOIN
				projects_contest_blocked AS blocked ON blocked.user_id = msgs.user_id AND blocked.project_id = ?i
			WHERE offer_id IN (?l)
			ORDER BY
				post_date
		";

		// первый проход - привязываем комментарии без родителя к предложениям
		$i = 0;
		$comments = array();
		$commidx  = array();
        $res = $DB->rows($sql, $this->pid, array_keys($offidx));
        if($res) {
            foreach($res as $row) {
    			$comments[$i] = $row;
			    $commidx[$row['id']] = $i; // массив ссылок на комментарии по их id
    			if (!$comments[$i]['reply_to']) {
				    //if ($is_owner || $comments[$i]['user_blocked'] != 't') {
    					$idx = $offidx[$comments[$i]['offer_id']];
    					if (isset($this->offers[$idx]['comments'])) {
    						$j = count($this->offers[$idx]['comments']);
    						$this->offers[$idx]['comments'][$j] = &$comments[$i];
    					} else {
    						$j = 0;
    						$this->offers[$idx]['comments'] = array(&$comments[$i]);
    					}
    					if (!$this->is_moder) $this->offers[$idx]['msg_count']++;
    					$this->offers[$idx]['comments'][$j]['hidden'] = $comments[$i]['deleted']? 1: 0;
    					$this->offers[$idx]['comments'][$j]['is_new'] = ($this->uid && $comments[$i]['post_date'] > $watch['last_view'] && $comments[$i]['user_id'] != $this->uid)? 1: 0;
    					if ($this->offers[$idx]['comments'][$j]['is_new']) $this->offers[$idx]['new_comments'] = 1;
    					//if (strtotime($comments[$i]['post_date']) > $today) $this->stat['comments_today']++;
    					//$offers[$idx]['comments'][$j]['hidden'] = ($comments[$i]['user_blocked'] == 't')? 1: 0;
        				//}
    			}
    			++$i;
    		}
        }
		// второй проход - привязываем комментарии к комментариям
		for ($i=0,$c=count($comments); $i<$c; $i++) {
			if (($idx = $comments[$i]['reply_to']) && isset($commidx[$idx])) {
				//if ($is_owner || ($comments[$i]['user_blocked'] != 't' && $comments[$commidx[$idx]]['user_blocked'] != 't')) {
					if (isset($comments[$commidx[$idx]]['comments'])) {
						$j = count($comments[$commidx[$idx]]['comments']);
						$comments[$commidx[$idx]]['comments'][$j] = &$comments[$i];
					} else {
						$j = 0;
						$comments[$commidx[$idx]]['comments'] = array(&$comments[$i]);
					}
					if (!$this->is_moder) $this->offers[ $offidx[$comments[$i]['offer_id']] ]['msg_count']++;
					//$comments[$commidx[$idx]]['comments'][$j]['hidden'] = ($comments[$i]['user_blocked'] == 't' || $comments[$commidx[$idx]]['user_blocked'] == 't')? 1: 0;
					$comments[$commidx[$idx]]['comments'][$j]['hidden'] = ($comments[$i]['deleted'] || $comments[$commidx[$idx]]['deleted'])? 1: 0;
					$comments[$commidx[$idx]]['comments'][$j]['is_new'] = ($this->uid && $comments[$i]['post_date'] > $watch['last_view'] && $comments[$i]['user_id'] != $this->uid)? 1: 0;
					if ($comments[$commidx[$idx]]['comments'][$j]['is_new']) $this->offers[ $offidx[$comments[$i]['offer_id']] ]['new_comments'] = 1;
					//if (strtotime($comments[$i]['post_date']) > $today) $this->stat['comments_today']++;
				//}
			}
		}
		// аттачи
		$sql = "
			SELECT
				attach.*,
				file.fname AS filename, preview.fname AS prevname,
				f.login AS upload_login, file.virus
			FROM
				projects_contest_attach AS attach
			JOIN
				freelancer f ON attach.user_id = f.uid
			JOIN 
				file ON attach.file_id = file.id
			LEFT JOIN 
				file AS preview ON attach.prev_id = preview.id
			WHERE
				offer_id IN (?l) ORDER BY offer_id, sort
		";
		$res = $DB->rows($sql, array_keys($offidx));
        if($res) {
            foreach($res as $row) {
    			$row['is_new'] = ($row['post_date'] > $watch['last_view'] && $row['user_id'] != $this->uid);
    			$this->offers[$offidx[$row['offer_id']]]['attach'][] = $row;
    		}
        }
		// устанавливаем в ноль флаг количества непрочитанных комментариев
		if ($set_read) {
            if(hasPermissions('projects')) {
                $sql = "UPDATE projects_contest_offers SET mod_new_msg_count = 0 WHERE project_id = ?i";
                $DB->query($sql, $this->pid);
            }
			if ($this->is_owner) {
				$sql = "UPDATE projects_contest_offers SET emp_new_msg_count = 0 WHERE project_id = ?i";
                $DB->query($sql, $this->pid);
			} else if (!$this->is_emp) {
				$sql = "UPDATE projects_contest_offers SET frl_new_msg_count = 0 WHERE project_id = ?i AND user_id = ?i";
                $DB->query($sql, $this->pid, $this->uid);
			}
		}
		
		$this->getStatContest();
		// all right )
		return TRUE;
	}

	
	/**
	 * Выбирает данные о конкретном предложении и заполняет этими данными массив $this->offer
	 * @param   integer  $oid        id предложения
	 * @param   bool $check_access нужно ли проверять права доступа (например при редактировании предложения)
	 * @return  boolean              успех операции
	 */
	public function GetOffer($oid, $check_access = false) {
        global $DB;
		$oid = intval($oid);
		$sWhere = '';
		
		if ( $check_access ) {
			if ( !hasPermissions('projects', $this->uid) ) {
				$sWhere = $DB->parse( ' AND offers.user_id = ?i', $this->uid );
			}
		}
		
		$sql = "
			SELECT
				offers.*, 
				f.login, f.uname AS name, f.usurname AS uname, f.photo, f.is_banned, 
				projects.user_id AS owner_id, projects.end_date, projects.win_date, projects.closed, projects.name AS project_name
			FROM
				projects_contest_offers AS offers
			JOIN
				projects ON projects.id = offers.project_id
			JOIN
				freelancer f ON f.uid = offers.user_id
			WHERE
				offers.id = ?i $sWhere
		";
		$this->offer = $DB->row($sql, $oid);
		if (!$this->offer) return TRUE;
		// файлы
		$sql = "
			SELECT
				file.id, file.fname, file.size, file.modified, preview.fname AS prev_fname, preview.size AS prev_size, 
				preview.modified AS prev_modified, attach.orig_name, f.login AS upload_login
			FROM
				projects_contest_attach AS attach
			JOIN
				freelancer f ON f.uid = attach.user_id
			JOIN
				file ON attach.file_id = file.id
			LEFT JOIN
				file AS preview ON attach.prev_id = preview.id
			WHERE
				attach.offer_id = ?i
			ORDER BY
				attach.sort
		";
		$this->offer['attach'] = $DB->rows($sql, $oid);
		return TRUE;
	}
	
	
	/**
	 * Выбирает строку данных из projects_contest_offers
	 * @param   integer  $oid   id предложения
	 * @return  array           строка данных из projects_contest_offers
	 */
	public function GetOfferRow($oid) {
        global $DB;
		$oid = intval($oid);
		$sql = "SELECT * FROM projects_contest_offers WHERE id = ?i";
		return $DB->row($sql, $oid);
	}

	
	/**
	 * Выбирает строку данных из projects_contest_offers со связими в projects и users
	 * Используется в рассылках
	 * @param   integer  $oid   id предложения
	 * @return  array           строка данных из projects_contest_offers
	 */
	public function GetOfferFullRow($oid) {
        global $DB;
		$oid = intval($oid);
		$sql = "
			SELECT
				offers.*, 
				f.login, f.uname AS name, f.usurname AS uname, f.photo, f.email, f.subscr,
				projects.user_id AS owner_id, projects.end_date, projects.win_date, projects.closed, projects.name AS project_name
			FROM
				projects_contest_offers AS offers
			JOIN
				projects ON projects.id = offers.project_id
			JOIN
				freelancer f ON f.uid = offers.user_id
			WHERE
				offers.id = ?i
		";
		return $DB->row($sql, $oid);
	}
	
	/**
	 * Создает предложение
	 * @param   string   $descr         описание
	 * @param   string   $files         данные о загруженных файлах примерно следующего вида: "u34356/u37857/o983982"
	 *                                  префикс "u" обозначает что файл был загружен, префикс "o", что файл не именялся, т.е.
	 *                                  был загружен ранее ("o" используется только в ChangeOffer). цифры это id файла в таблице projects_contest_attach
	 * @param   boolean  $comm_blocked  автор запретил комментирование?
	 * @return  string                  сообщение об ошибке или пустая строка, если все нормально
	 */
	public function CreateOffer($descr, $files, $comm_blocked) {
        global $DB;
		if ($this->is_banned) return "Забаненые пользователи не могут добавлять комментарии";
		/*if (!$this->is_pro) {
			require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects_offers_answers.php';
			$answers = new projects_offers_answers;
			$answers->getInfo($this->uid, TRUE);
			if (!$answers->offers) return "У Вас закончились ответы на проекты";
		}*/
		$sql = "SELECT COUNT(*) FROM projects_contest_offers WHERE user_id = ?i AND project_id = ?i";
		$row[0] = $DB->val($sql, $this->uid, $this->pid);
		if ($row[0]) return "У Вас уже есть предложение";
        
		$sql = 'SELECT p.end_date, e.is_pro FROM projects p 
            LEFT JOIN employer e ON e.uid = p.user_id 
            WHERE id = ?i';
        
		$aData = $DB->row( $sql, $this->pid );
        
		if (strtotime($aData['end_date']) < time()) return "Конкурс окончен";
		//$descr = substr(change_q(trim($descr), true, 90), 0, 3000);
		// добавление предложения
        $nStopWordsCnt = 0;
        
        if ( !is_pro() && $aData['is_pro'] != 't') {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );

            $stop_words    = new stop_words();
            $nStopWordsCnt = $stop_words->calculate( $descr );
        }
                
        $sModVal = (is_pro() || $aData['is_pro'] == 't' || !$nStopWordsCnt) ? 'NULL' : '0';
        $descr   = $descr ? $descr : null;
		$sql = "
			INSERT INTO projects_contest_offers
				(project_id, user_id, post_date, descr, comm_blocked, po_frl_read, msg_count, moderator_status)
			VALUES
				(?i, ?i, NOW(), ?, ".($comm_blocked? 'true': 'false') . ", true, 0, $sModVal) RETURNING id;
		";
		$this->new_oid = $DB->val( $sql, $this->pid, $this->uid, $descr );
		if ($error = $DB->error) return $error;
        
        if ( !is_pro() && $aData['is_pro'] != 't' && $nStopWordsCnt) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            $DB->insert( 'moderation', array('rec_id' => $this->new_oid, 'rec_type' => user_content::MODER_PRJ_OFFERS, 'stop_words_cnt' => $nStopWordsCnt) );
        }

		// добавление файлов
		$s = array();
		if ($files) {
			$files = explode('/', $files);
			if (is_array($_SESSION['contest_files'])) $k = array_keys($_SESSION['contest_files']); else $k = array();
			foreach ($files as $file) {
				if ($file) {
					$type = $file{0};
					$file = substr($file, 1);
					if ($type == 'u' && in_array($file, $k)) {
						$s[] = array('file'=>$file, 'prev'=>$_SESSION['contest_files'][$file]['prev_id'], 'orig_name'=>$_SESSION['contest_files'][$file]['orig_name']);
						unset($_SESSION['contest_files'][$file]);
					}
				}
			}
			$sql = '';
			for ($i=0; $i<count($s); $i++) {
                $nPrev = ( $s[$i]['prev'] && $s[$i]['prev'] != $s[$i]['file'] )? $s[$i]['prev']: null;
                $sql  .= $DB->parse( ', (?i, ?i, ?i, ?i, ?, NOW(), ?i)', $this->new_oid, $this->uid, $s[$i]['file'], $nPrev, $s[$i]['orig_name'], $i );
			}
			if ($sql) {
				$DB->squery("INSERT INTO projects_contest_attach (offer_id, user_id, file_id, prev_id, orig_name, post_date, sort) VALUES " . substr($sql, 1));
			}
		}
		$this->ClearTempFiles();
        
        // id работодателя-создателя конкурса
        $sql = "SELECT p.user_id FROM projects p WHERE p.id = ?i";
        $emp_id = $DB->val($sql, $this->pid);
        // сброс количества непросмотренных событий в проектах
        $mem = new memBuff();
        $mem->delete("prjMsgsCnt{$emp_id}");
        $mem->delete('prjEventsCnt' . $emp_id);
        
		return '';
	}	

	
	/**
	 * Создает предложение
	 * @param   integer  $oid           id редактируемого предложения
	 * @param   string   $descr         описание
	 * @param   string   $files         данные о загруженных файлах примерно следующего вида: "u34356/u37857/o983982"
	 *                                  префикс "u" обозначает что файл был загружен, префикс "o", что файл не именялся, т.е.
	 *                                  был загружен ранее. цифры это id файла в таблице projects_contest_attach
	 * @param   boolean  $comm_blocked  автор запретил комментирование?
	 * @return  string                  сообщение об ошибке или пустая строка, если все нормально
	 */
	public function ChangeOffer($oid, $descr, $files, $comm_blocked) {
        global $DB;
		$oid = intval($oid);
		if ($this->is_banned) return "Забаненые пользователи не могут добавлять комментарии";
		if (!($offer = $this->GetOfferRow($oid))) return "Нет предложения для редактирования";
		if ($offer['user_id'] != $this->uid && !$this->is_moder) return "Нет предложения для редактирования";
        
		$sql = 'SELECT p.end_date, e.is_pro FROM projects p 
            LEFT JOIN employer e ON e.uid = p.user_id 
            WHERE id = ?i';
        
		$aData = $DB->row( $sql, $this->pid );
        
		if (strtotime($aData['end_date']) < time()) return "Конкурс окончен";
		//$descr = substr(change_q(trim($descr), true, 90), 0, 3000);
		
		// редактирование файлов
		$s = array(); // новые файлы
		$o = array(); // айдишки старых файлов
		$p = array(); // старые файлы
		$h = array(); // айдишки старых файлов, которые не нужно удалять
		$a = array(); // старые файлы, которые нужно заново положить в базу
        $u = array(); // финальный список файлов
		$res = $DB->rows("SELECT * FROM projects_contest_attach WHERE offer_id = ?i ORDER BY sort ASC", $oid);
        if($res) {
            foreach($res as $row) {
    			$o[] = $row['file_id'];
    			$p[$row['file_id']] = $row;
    		}
        }
		if ($files)	$files = explode('/', $files); else $files = array();
		if (is_array($_SESSION['contest_files'])) $k = array_keys($_SESSION['contest_files']); else $k = array();
		foreach ($files as $file) {
			if ($file) {
				$type = $file{0};
				$file = substr($file, 1);
				if ($type == 'u' && in_array($file, $k)) {
					$s[] = array('file'=>$file, 'prev'=>$_SESSION['contest_files'][$file]['prev_id'], 'uid'=>$this->uid, 'orig_name'=>$_SESSION['contest_files'][$file]['orig_name']);
                    $u[] = array('file'=>$file, 'prev'=>$_SESSION['contest_files'][$file]['prev_id'], 'uid'=>$this->uid, 'orig_name'=>$_SESSION['contest_files'][$file]['orig_name']);
					unset($_SESSION['contest_files'][$file]);
				} else if ($type == 'o' && in_array($file, $o)) {
					$a[] = array('file'=>$file, 'prev'=>$p[$file]['prev_id'], 'uid'=>$p[$file]['user_id'], 'orig_name'=>$p[$file]['orig_name'], 'post_date'=>$p[$file]['post_date']);
					$h[] = $file;
                    $u[] = array('file'=>$file, 'prev'=>$p[$file]['prev_id'], 'uid'=>$p[$file]['user_id'], 'orig_name'=>$p[$file]['orig_name'], 'post_date'=>$p[$file]['post_date']);
				}
			}
		}
		$DB->query("DELETE FROM projects_contest_attach WHERE offer_id = ?i", $oid);
		// удаляем удаленные
		$cn = new CFile();
		for ($i=0; $i<count($o); $i++) {
			if (!in_array($o[$i], $h)) {
				$cn->Delete($o[$i]);
				if ($p[$o[$i]]['prev_id'] && ($p[$o[$i]]['prev_id'] != $o[$i])) $cn->Delete($p[$o[$i]]['prev_id']);
			}
		}

        $this->addOfferFiles( $oid, $u );
        
        $sMod = '';
        $sql  = '';
        
        if ( $aData['is_pro'] != 't' && $offer['user_id'] == $_SESSION['uid'] && !hasPermissions('projects') && !is_pro() && ($s || $offer['descr'] != $descr) ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            
            $stop_words    = new stop_words();
            $nStopWordsCnt = $stop_words->calculate( $descr );
            $sMod          = ' , moderator_status =' . ( $nStopWordsCnt ? ' 0 ' : ' NULL ' );
            
            if ( $nStopWordsCnt ) {
                $DB->insert( 'moderation', array('rec_id' => $oid, 'rec_type' => user_content::MODER_PRJ_OFFERS, 'stop_words_cnt' => $nStopWordsCnt) );
            }
            else {
                $DB->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i;', $oid, user_content::MODER_PRJ_OFFERS );
            }
        }
        
        // редактирование предложения
		$sql .= "UPDATE projects_contest_offers SET descr = ?, comm_blocked = ".($comm_blocked? 'true': 'false').", modified = NOW(), moduser_id = ?i $sMod WHERE id = ?i";
		$DB->query($sql, $descr, $this->uid, $oid);

		$this->ClearTempFiles();
        return '';
	}
	
	/**
	 * Добавляет файлы к предложению
	 * 
	 * @param integer $oid ID редактируемого предложения
	 * @param array $s список файлов.
     * @param integer $pos номер файла по порядку
	 */
	function addOfferFiles( $oid, $s, $pos=0 ) {
	    global $DB;
	    
	    if ($s) {
			$data = array();
            $data_pdate = array();
			
			for ($i=0; $i<count($s); $i++) {
			    $tmp = array(
                    'offer_id' => $oid, 
                    'user_id' => $s[$i]['uid'], 
                    'file_id' => $s[$i]['file'], 
                    'prev_id' => (($s[$i]['prev']!=$s[$i]['file'] && $s[$i]['prev'])? $s[$i]['prev']: NULL), 
                    'orig_name' => $s[$i]['orig_name'], 
                    'sort' => ($i+$pos)
			    );
			    
			    if ( $s[$i]['post_date'] ) {
                    $tmp['post_date'] = $s[$i]['post_date'];
                    $data_pdate[] = $tmp;
			    } else {
    			    $data[] = $tmp;
                }
			}
			
			if($data) { $DB->insert( 'projects_contest_attach', $data ); }
			if($data_pdate) { $DB->insert( 'projects_contest_attach', $data_pdate ); }
		}
	}
    
	/**
	 * Удаляет предложение
	 * @param   integer  $oid   id предложения
	 * @return  string          сообщение об ошибке или пустая строка, если все нормально
	 */
	public function DeleteOffer($oid) {
        global $DB;
		$oid = intval($oid);
		// проверка наличия предложения
		if (!($offer = $this->GetOfferRow($oid))) return "Несуществующее предложение";
		// файлы



		$cf = new CFile();
		$res = $DB->rows("SELECT file_id FROM projects_contest_attach WHERE offer_id = ?i", $oid);
        if($res) {
            foreach($res as $row) {
    			$cf->Delete($row['file_id']);
    			$cf->Delete($row['prev_id']);
    		}
        }
		// предложение
		$DB->query("DELETE FROM projects_contest_offers WHERE id = ?i", $oid);
		return 0;
	}

    /**
     * Удаление предложения(пометка как удаленное)
     *
     * @param   integer $offer_id   ID предложения
     * @return  string              сообщение об ошибке или пустая строка, если все нормально
     */
    public function RemoveOffer($offer_id) {
        return $this->setOfferDeleted( $offer_id, true );
    }

    /**
     * Восстановление предложения
     *
     * @param   integer $offer_id   ID предложения
     * @return  string              сообщение об ошибке или пустая строка, если все нормально
     */
    public function RestoreOffer($offer_id) {
        return $this->setOfferDeleted( $offer_id, false );
    }
    
    /**
     * Удаление или восстановление предложения (устанавливает флаг is_deleted)
     * 
     * @param  int $offer_id ID предложения
     * @param  bool $is_deleted новое значение поля is_deleted в projects_contest_offers
     * @return string сообщение об ошибке или пустая строка, если все нормально
     */
    function setOfferDeleted( $offer_id, $is_deleted = false ) {
        global $DB;
        
        $offer_id = intval( $offer_id );
        $user_id  = get_uid( false );
        
        if ( !($offer = $this->GetOfferRow($offer_id)) ) {
            return "Несуществующее предложение";
        }
        
        $aUser = $DB->row( 'SELECT f.uid, f.login, f.uname, f.usurname, 
                pco.project_id, pco.descr, p.name AS prj_name, p.user_id 
            FROM projects_contest_offers as pco  
            INNER JOIN projects p ON p.id = pco.project_id 
            INNER JOIN freelancer f ON f.uid = pco.user_id 
            WHERE pco.id=?i', $offer_id );
        
        if ( $aUser['uid'] == $user_id || hasPermissions('projects') ) {
            $deluser_id = $is_deleted ? $user_id : null;
            
            $sModer = '';
            $sql = '';
            
            if ( $deluser_id ) {
                $sModer = ' , moderator_status = '. ( $aUser['uid'] != $user_id ? $user_id : 'NULL' ) .' ';
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                $sql .= $DB->parse( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i;', $offer_id, user_content::MODER_PRJ_OFFERS );
            }
            
            if ( $aUser['uid'] == $user_id && !hasPermissions('projects') && !is_pro() && !$deluser_id ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                
                $stop_words    = new stop_words();
                $nStopWordsCnt = $stop_words->calculate( $aUser['descr'] );
                $sModer = ' , moderator_status = ' . ( $nStopWordsCnt ? ' 0 ' : ' NULL ' );
                
                if ( $nStopWordsCnt ) {
                    $DB->insert( 'moderation', array('rec_id' => $offer_id, 'rec_type' => user_content::MODER_PRJ_OFFERS, 'stop_words_cnt' => $nStopWordsCnt) );
                }
                else {
                    $sql .= $DB->parse( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i;', $offer_id, user_content::MODER_PRJ_OFFERS );
                }
            }
            
            $sql .= 'UPDATE projects_contest_offers SET is_deleted = ?b, deluser_id = ?i '. $sModer .' WHERE id = ?i';
            $DB->query( $sql, $is_deleted, $deluser_id, $offer_id );
            
            // пишем лог админских действий: удаление предложения в проекте
            if ( !$DB->error && $aUser['uid'] != $user_id ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );
                
                $sPrjLink = getFriendlyURL('project', $aUser['project_id']);
                $sOffLink = $sPrjLink . "?offer={$offer_id}#offer-{$offer_id}";
                
                $sReason = '<a href="' . $sOffLink . '" target="_blank">Предложение</a> от <a href="' . $GLOBALS['host'] 
                    . '/users/' . $aUser['login'] . '" target="_blank">' . $aUser['uname'] . ' ' . $aUser['usurname'] 
                    . ' [' . $aUser['login'] . ']</a>';
                
                $sActId = $is_deleted ? admin_log::ACT_ID_PRJ_DEL_OFFER : admin_log::ACT_ID_PRJ_RST_OFFER;
                
                admin_log::addLog( 
                    admin_log::OBJ_CODE_PROJ, $sActId, $aUser['user_id'], $aUser['project_id'], $aUser['prj_name'], 
                    $sPrjLink, 0, '', 0, $sReason 
                );
            }
        }
        
        return '';
    }
	
	/**
	 * Возвращает данные комментария
	 * @param   integer  $cid   id комментария
	 * @return  array           данные комментария
	 */
	public function GetComment($cid) {
        global $DB;
		$cid = intval($cid);
		$sql = "
			SELECT 
				msgs.*, users.login, users.uname, users.usurname, users.photo, users.is_profi, users.is_pro, users.is_team, users.is_pro_test, 
                po.project_id, completed_cnt 
			FROM 
				projects_contest_msgs AS msgs
            INNER JOIN projects_contest_offers po ON po.id = msgs.offer_id 
			LEFT JOIN 
				users ON msgs.user_id = users.uid
			LEFT JOIN 
				sbr_meta ON sbr_meta.user_id = users.uid
			WHERE 
				msgs.id = ?i
		";
		return $DB->row($sql, $cid);
	}
	
	
	/**
	 * Создает комментарий
	 * @param   integer   $oid       id предложения, который комментируют
	 * @param   string    $comment   комментарий
	 * @param   integer   $reply     id комментария на который отвечают или 0, если комментарий 1-го уровня
	 * @return  string               сообщение об ошибке или пустая строка, если все нормально
	 */
	public function CreateComment($oid, $comment, $reply=0) {
        global $DB;
		$oid = intval($oid);
		$reply = intval($reply);
		//$comment = substr(change_q(trim($comment), true, 90), 0, 1000);
		if (!($offer = $this->GetOffer($oid))) return 'Несуществующее предложение';

		if ($offer['closed'] == 't') return "Проект завершен";
		// пользователь забанен в текущем предложении?
		if ($this->is_banned) return "Забаненые пользователи не могут добавлять комментарии";
		// пользователь запретил комментировать свое предложение?
		if ($offer['comm_blocked'] == 't') return 'Автор предложения отключил возможность комментирования';
		// добавляем комментарий
		$ip = getRemoteIP();
                
            $nStopWordsCnt = 0;
            
            if ( !is_pro() ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );

                $stop_words    = new stop_words();
                $nStopWordsCnt = $stop_words->calculate( $comment );
            }
                
            $sModVal = ( is_pro() || !$nStopWordsCnt ) ? 'NULL' : '0';
        
		$sql = "
			INSERT INTO projects_contest_msgs
				(offer_id, user_id, reply_to, from_ip, msg, post_date, moderator_status)
			VALUES
				(?i, ?i, ".($reply? $reply: 'NULL').", ?, ?, NOW(), $sModVal)
			RETURNING id
		";
		$this->new_cid = $DB->val($sql, $oid, $this->uid, $ip, $comment);
		if ($error = $DB->error) return $error;
        
            if ( !is_pro() && $nStopWordsCnt ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                $DB->insert( 'moderation', array('rec_id' => $this->new_cid, 'rec_type' => user_content::MODER_CONTEST_COM, 'stop_words_cnt' => $nStopWordsCnt) );
            }
                
                $sql = 'SELECT po.user_id AS frl, p.user_id AS emp, e.is_pro AS emp_is_pro, f.is_pro AS frl_is_pro 
                FROM projects_offers po
                LEFT JOIN projects p ON p.id = po.project_id 
                LEFT JOIN employer e ON e.uid = p.user_id 
                LEFT JOIN freelancer f ON f.uid = po.user_id 
                WHERE po.id = ?i LIMIT 1';
        
                $users = $DB->row($sql, $oid);
                
                // стираем мемкеш
                $memBuff = new memBuff();
                if ((int)$users['frl'] !== (int)$this->uid) {
                    $memBuff->delete("prjMsgsCnt{$users['frl']}");
                    $memBuff->delete("prjMsgsCntWst{$users['frl']}");
                } elseif ((int)$users['emp'] !== (int)$this->uid) {
                    $memBuff->delete("prjMsgsCnt{$users['emp']}");
                    $memBuff->delete("prjLastMess{$users['emp']}");
                }
                
		return '';
	}

	
	/**
	 * Редактирует комментарий
	 * @param   integer  $cid       id комментария
	 * @param   string   $comment   новый комментарий
	 * @return  string              сообщение об ошибке или пустая строка, если все нормально
	 */
	public function ChangeComment($cid, $comment) {
        global $DB;
		$cid = intval($cid);
		if ($this->is_banned && !$this->is_moder) return "Забаненые пользователи не могут редактировать комментарии";
		if (!($p_comment = $this->GetComment($cid))) return "Несуществующий комментарий. $cid";
		if ($p_comment['user_id'] != $this->uid && !$this->is_moder) return "Несуществующий комментарий!";
		$offer = $this->GetOffer($p_comment['offer_id']);
		if ($offer['closed'] == 't' && !$this->is_moder) return "Проект завершен";
		//$comment = substr(change_q(trim($comment), true, 90), 0, 3000);
        
        $sModer = '';
        $sql    = '';
        
        if ( $p_comment['user_id'] == $_SESSION['uid'] && !hasPermissions('projects') && !is_pro() && $p_comment['msg'] != $comment ) {
            // автор, не админ, не про меняет текст - отправить на модерирование
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            
            $stop_words    = new stop_words();
            $nStopWordsCnt = $stop_words->calculate( $comment );
            $sModer        = ' , moderator_status =' . ( $nStopWordsCnt ? ' 0 ' : ' NULL ' );
            
            if ( $nStopWordsCnt ) {
                $DB->insert( 'moderation', array('rec_id' => $cid, 'rec_type' => user_content::MODER_CONTEST_COM, 'stop_words_cnt' => $nStopWordsCnt) );
            }
            else {
                $DB->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i;', $cid, user_content::MODER_CONTEST_COM );
            }
        }
        
		$sql .= "UPDATE projects_contest_msgs SET msg = ?, moduser_id = ?i, modified = NOW() $sModer WHERE id = ?i";
		$DB->query($sql, $comment, $this->uid, $cid);
		return $DB->error;
	}
	
	
	/**
	 * Удаляет комментарий (помечает удаленным)
	 * @param   integer   $cid   id комментария
	 * @return  string           сообщение об ошибке или пустая строка, если все нормально
	 */
	public function DeleteComment($cid) {
        global $DB;
		if ($this->is_moder) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            
			$cid       = intval($cid);
            $p_comment = $this->GetComment( $cid );
            $sModer    = ' , moderator_status = '. ( $p_comment['user_id'] != $_SESSION['uid'] ? $_SESSION['uid'] : 'NULL' ) .' ';
            
			$DB->query( "UPDATE projects_contest_msgs SET deleted = NOW(), deluser_id = ?i $sModer WHERE id = ?i", $this->uid, $cid );
            $DB->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i;', $cid, user_content::MODER_CONTEST_COM );
            
			return $DB->error;
		}
	}

	
	/**
	 * Восстанавливает комментарий
	 * @param   integer  $cid    id комментария
	 * @return  string           сообщение об ошибке или пустая строка, если все нормально
	 */
	public function RestoreComment($cid) {
        global $DB;
		if ($this->is_moder) {
			$cid = intval($cid);
            $sModer = '';
            
            if ( !hasPermissions('projects') && !is_pro() ) {
                $p_comment = $this->GetComment( $cid );
                if ( $p_comment['user_id'] == $_SESSION['uid'] ) {
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                    
                    $stop_words    = new stop_words();
                    $nStopWordsCnt = $stop_words->calculate( $p_comment['msg'] );
                    $sModer        = ' , moderator_status =' . ( $nStopWordsCnt ? ' 0 ' : ' NULL ' );
                    
                    if ( $nStopWordsCnt ) {
                        $DB->insert( 'moderation', array('rec_id' => $cid, 'rec_type' => user_content::MODER_CONTEST_COM, 'stop_words_cnt' => $nStopWordsCnt) );
                    }
                    else {
                        $DB->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i;', $cid, user_content::MODER_CONTEST_COM );
                    }
                }
            }
            
			$DB->query("UPDATE projects_contest_msgs SET deleted = NULL, deluser_id = NULL $sModer WHERE id = ?i", $cid);
			return $DB->error;
		}
	}
	

	/**
	 * Возвращает список забанненных пользователей в конкурсе $this->pid
	 * @return  array  список забанненных
	 */
	public function GetBanned() {
        global $DB;
		$sql = "
			SELECT users.*
			FROM projects_contest_blocked AS blocked
			JOIN users ON users.uid = blocked.user_id
			JOIN projects_contest_offers offers ON offers.user_id = users.uid AND offers.project_id = blocked.project_id 
			WHERE blocked.project_id = ?i  AND users.is_banned = '0' AND offers.is_deleted = false
		";
		return $DB->rows($sql, $this->pid);
	}


	/**
	 * Добавляет или удаляет кандидата
	 * @param  integer  $oid   id предложения
	 * @return array           список кандидатов
	 */
	public function Candidate($oid) {
        global $DB;
		if ($this->is_owner) {
			$oid = intval($oid);
			if (!($offer = $this->GetOfferRow($oid))) return "Несуществующее предложение";
			$frl_id = $DB->val("UPDATE projects_contest_offers SET selected = NOT selected WHERE id = ?i RETURNING user_id", $oid);
            
            // сброс количества непросмотренных событий в проектах
            $mem = new memBuff();
            $mem->delete('prjEventsCnt' . $frl_id);
            
			return $DB->error;
		} else {
			return "У Вас нет прав";
		}
	}
	
	
	/**
	 * Банит пользователя в конкурсе $this->pid
	 * @param   integer   $uid       uid пользователя
	 * @param   integer   $blocked   возвращает TRUE, если пользователя заблокировали или FALSE если разблокировали
	 * @return  string               сообщение об ошибке или пустая строка, если все нормально
	 */
	public function BlockUser($uid, &$blocked) {
        global $DB;
		if (!($this->is_moder || $this->is_owner)) return "У Вас нет прав для проведения этой операции";
		$uid = intval($uid);
		if ($row = $DB->row("SELECT * FROM projects_contest_blocked WHERE project_id = ?i AND user_id = ?i", $this->pid, $uid)) {
			$DB->query("DELETE FROM projects_contest_blocked WHERE id = ?i", $row['id']);
			$blocked = FALSE;
		} else {
			$sql = "
				INSERT INTO
					projects_contest_blocked
					(project_id, user_id, admin_id, post_date)
				VALUES
					({$this->pid}, $uid, {$this->uid}, NOW())
			";
			$DB->query($sql, $this->pid, $uid, $this->uid);
			$DB->query("UPDATE projects_contest_offers SET selected = 'f' WHERE project_id = ?i AND user_id = ?i", $this->pid, $uid);
			$blocked = TRUE;
		}
		return $DB->error;
	}
	
	
	/**
	 * Заполняет массив $this->stat статистикой по конкурсу $this->pid
	 * @return  string    сообщение об ошибке или пустая строка, если все нормально
	 */
	public function GetStat() {
        global $DB;
		$this->stat = array();
		$sql = "SELECT * FROM projects_contest_offers 
		LEFT JOIN freelancer f ON projects_contest_offers.user_id = f.uid 
		WHERE project_id = ?i AND f.is_banned = '0' ";
		$offers = $DB->rows($sql, $this->pid);
		$in = array();

		for ($i=0; $i<count($offers); $i++) {
			$in[] = $offers[$i]['id'];
			$this->stat['offers']++;
			if ($offers[$i]['selected'] == 't') $this->stat['candidates']++;
		}
		if (!$this->stat['offers']) return;
		$sql = "SELECT COUNT(*) FROM projects_contest_blocked 
		LEFT JOIN freelancer f ON projects_contest_blocked.user_id = f.uid 
		WHERE project_id = {$this->pid} AND f.is_banned = '0' ";
		$this->stat['banned'] = $DB->val($sql, $this->pid);

		return '';
	}

	
	/**
	 * Удаляет временные файлы, которые могут появиться при загрузке/редактировании предложения.
	 * Например, пользователь прикрепил картинку, но потом передумал и удалил ее до добавления работы.
	 */
	public function ClearTempFiles() {
		if (!empty($_SESSION['contest_files'])) {
			$cf = new CFile();
			foreach ($_SESSION['contest_files'] as $file=>$s) {
				if ($k) {
					$cf->Delete($k);
					if ($s['prev_id'] && ($k != $s['prev_id'])) $cf->Delete($s['prev_id']);
				}
			}
		}
		unset($_SESSION['contest_files']);
	}

	
	/**
	 * Устанавливает дату окончания конкурса
	 * @param   integer   $time   дата в формате unixtime
	 * @return  string            сообщение об ошибке или пустая строка, если все нормально
	 */
	public function ChangeEndDate($time) {
        global $DB;
		$DB->query("UPDATE projects SET end_date = ? WHERE id = ?i", date("Y-m-d",$time), $this->pid);
		return $DB->error;
	}

	
	/**
	 * Устанавливает дату выбора победителей
	 * @param   integer   $time   дата в формате unixtime
	 * @return  string            сообщение об ошибке или пустая строка, если все нормально
	 */
	public function ChangeWinDate($time) {
        global $DB;
		$DB->query("UPDATE projects SET win_date = ?, exec_id = 0 WHERE id = ?i", date("Y-m-d",$time), $this->pid);
		$DB->query("UPDATE projects_contest_offers SET position = 0 WHERE project_id = ?i", $this->pid);
		return $DB->error;
	}
	
	
	/**
	 * Выбор победителей
	 * @param   integer   $uid1   uid пользователя занявшего первое место
	 * @param   integer   $uid2   uid пользователя занявшего второе место
	 * @param   integer   $uid3   uid пользователя занявшего третье место
	 * @return  string            сообщение об ошибке или пустая строка, если все нормально
	 */
	public function SetWinners($uid1, $uid2, $uid3) 
    {
        global $DB;
        
        require_once(ABS_PATH . '/classes/messages.php');
        
		$uid1 = intval($uid1);
		$uid2 = intval($uid2);
		$uid3 = intval($uid3);
        
		$DB->query("UPDATE projects_contest_offers SET position = 0 WHERE project_id = ?i", $this->pid);
		$DB->query("UPDATE projects SET exec_id = 0 WHERE id = ?i", $this->pid);
        
		if ($uid1) {
			$DB->query("UPDATE projects SET win_date = NOW() WHERE id = ?i", $this->pid);
    		$DB->query("UPDATE projects SET exec_id = ?i WHERE id = ?i", $uid1, $this->pid);
			$DB->query("UPDATE projects_contest_offers SET position = 1 WHERE project_id = ?i AND user_id = ?i", $this->pid, $uid1);
                     
            messages::setIsAllowed($this->uid, $uid1);
		} else {
			$DB->query("UPDATE projects SET closed = 'f' WHERE id = ?i", $this->pid); // помойму это не недо.
			return 0;
		}
        
		if ($uid2) {
            $DB->query("UPDATE projects_contest_offers SET position = 2 WHERE project_id = ?i AND user_id = ?i", $this->pid, $uid2); 
            messages::setIsAllowed($this->uid, $uid2);
        } else return 0;
        
		if ($uid3) { 
            $DB->query("UPDATE projects_contest_offers SET position = 3 WHERE project_id = ?i AND user_id = ?i", $this->pid, $uid3); 
            messages::setIsAllowed($this->uid, $uid3);
        } else return 0;
        
		return 0;
	}
	
	
	/**
	 * Возвращает uid победителей конкурса
	 * @param   integer   $pid   id конкурса. можно не указывать, тогда беретса $this->pid
	 * @return  array            массив с uid победителей в порядке занятого места
	 */
	public function GetWinners($pid=FALSE) {
        global $DB;
		$pid = $pid? intval($pid): $this->pid;
		$res = $DB->rows("SELECT user_id FROM projects_contest_offers WHERE project_id = ?i AND position > 0 ORDER BY position", $pid);
		$result = array();
        if($res) {
    		foreach ($res as $row) $result[] = $row['user_id'];
        }
		return $result;
	}

	
	/**
	 * В отличии от GetWinners возвращает более полные данные о победителях в двумерном массиве
	 * @param   integer   $pid   id конкурса. можно не указывать, тогда беретса $this->pid
	 * @return  array            массив с uid победителей в порядке занятого места
	 */
	public function GetWinnersFullInfo($pid=FALSE) {
        global $DB;
		$pid = $pid? intval($pid): $this->pid;
		$sql = "
			SELECT 
				freelancer.*, pco.position, pco.id as offer_id
			FROM 
				projects_contest_offers pco
			JOIN
				freelancer ON freelancer.uid = pco.user_id
			WHERE 
				project_id = ?i AND position > 0 ORDER BY position
		";
		return $DB->rows($sql, $pid);
	}
	
	
	/**
	 * Количество закончившихся конкурсов для конкретного пользователя
	 * @param   integer  $uid   uid пользователя
	 * @return  integer         количество закончившихся конкурсов
	 */
	public function CompleteCount($uid) {
        global $DB;
		$ret = $DB->val("SELECT COUNT(*) FROM projects_contest_offers WHERE project_id IN (SELECT project_id FROM projects_contest_offers WHERE user_id = ?i ) AND position = 1", intval($uid));
		return $ret;
	}
	
	/**
	 * Возвращает пользователей в конкурсах на объявление победителей в котором остется указанное количество времени. ($date = 'win_date')
	 * Возвращает пользователей в конкурсах на окончание в котором остется указанное количество времени. ($date = 'end_date')
	 * Используется в рассылке.
	 * @param    string   временной интервал в формате postgresql
	 * @param    string   По какой дате окончания брать (win_date - По дате объясвления победителей, end_date - По дате окончания конкурса)
	 * @return   string   массив, индексы которого - id конкурсов содеражащие uid работодателя и uid фрилансеров.
	 */
	public function WInterval($interval='1 day', $date='win_date') {
        global $DB;
		$sql = "
			SELECT
				projects.id, projects.name AS project_name, projects.user_id AS emp_id, co.user_id AS frl_id, co.id as offer_id,
				freelancer.login AS frl_login, freelancer.uname AS frl_uname, freelancer.usurname AS frl_usurname, freelancer.email AS frl_email, freelancer.subscr AS frl_subscr,
     freelancer.is_banned, pb.admin AS is_blocked
			FROM
				projects 
			JOIN
				projects_contest_offers AS co ON projects.id = co.project_id
			JOIN
				freelancer ON freelancer.uid = co.user_id
		    LEFT JOIN
				projects_blocked AS pb ON pb.project_id = projects.id
			WHERE
				projects.id IN (
					SELECT
						p.id
					FROM
						projects AS p
					JOIN
						projects_contest_offers AS pco ON p.id = pco.project_id
					WHERE
						p.kind = 7 AND 
						(p.{$date} - CURRENT_DATE) = interval '$interval' AND (p.{$date} - CURRENT_DATE) >= '0 days'
					GROUP BY
						p.id
					HAVING
						MAX(pco.position) = 0
				)
		";
		$res = $DB->rows($sql);
		$pid = 0;
		$result = array();
        if($res) {
    		foreach ($res as $row) {
    			if ($pid != $row['id']) {
    				$pid = $row['id'];
    				$result[$pid] = array('project_name'=>$row['project_name'], 'employer'=>$row['emp_id'], 'freelancer'=>array(), 'is_blocked' => $row['is_blocked']);
    			}
    			$result[$pid]['freelancer'][] = array(
    				'id'       => $row['frl_id'], 
    				'login'    => $row['frl_login'], 
    				'uname'    => $row['frl_uname'], 
    				'usurname' => $row['frl_usurname'], 
	    			'email'    => $row['frl_email'], 
    				'subscr'   => $row['frl_subscr'],
    				'offer_id' => $row['offer_id'],
    				'is_banned' => $row['is_banned']
    			);
    		}
        }
		return $result;
	}

	/**
	 * Возвращает данные нескольких конкурсах по их id.
	 * Используется для рассылки почты в smail
	 * @param   array     $prj_ids   список id проектов
	 * @param   resource  $connect   соединение к БД (необходимо в PgQ) или NULL -- создать новое.
	 * @return  array                массив с данными проектов
	 */
	function GetContests4Sending($prj_ids, $connect=NULL) {
        global $DB;
		if (is_array($prj_ids)) $prj_ids = implode(',', array_map('intval', $prj_ids));
		if (!$prj_ids) return NULL;
		$sql = "
			SELECT 
				projects.*, pco.id as offer_id, freelancer.login, freelancer.uname, freelancer.usurname, freelancer.email, freelancer.subscr, freelancer.is_banned
			FROM
				projects_contest_offers pco
			JOIN
				projects ON projects.id = pco.project_id
			JOIN
				freelancer ON pco.user_id = freelancer.uid	
			WHERE
				pco.project_id IN ($prj_ids)
		";
		return $DB->rows($sql);
	}
	
	/**
	 * Выбирает строку данных из projects_contest_offers
	 * 
	 * @param   integer  $prj   id проекта
	 * @param   integer  $uid   id пользователя
	 * @return  array           строка данных из projects_contest_offers
	 */
	public function GetOfferByProject($prj, $uid) {
        global $DB;
		$oid = intval($oid);
		$sql = "SELECT * FROM projects_contest_offers WHERE project_id = ?i AND user_id = ?i";
		return $DB->row($sql, $prj, $uid);
	}
	
	/**
     * Выборка информации о добавлении комментария к его предложению в конкурсе.
     * 
     * После изменения этой функции, необходимо перезапустить консьюмер /classes/pgq/mail_cons.php на сервере.
     * Если нет возможности, то сообщить админу.
     * @see pmail::ContestNewComment()
     * @see PGQMailSimpleConsumer::finish_batch()
     *
     * @param string|array  $comment_ids    Идентификаторы комментариев
     * @param resource      $connect        Соединение к БД (необходимо в PgQ) или NULL -- создать новое.
     * @return array|mixed                  Если есть ответы к проектам то возвращает массив их, если нет то NULL
     */
    public function getContestNewComment($comment_ids, $connect=NULL) {
        global $DB;
        if(!$comment_ids) return NULL;
        if(is_array($comment_ids))
        $comment_ids = implode(',', array_unique($comment_ids));
        
        // u  - автор нового комментария
        // um - автор родительского комментария
        // uo - автор предложения по проекту
        // up - автор проекта
        $sql = "SELECT 
                    p.name AS project_name, p.id AS project_id, cm.offer_id, cm.id AS comment_id, 
                    u.uid, u.uname, u.usurname, u.login, u.email, u.subscr, u.is_banned AS banned, 
                    um.uid AS m_uid, um.uname AS m_uname, um.usurname AS m_usurname, um.login AS m_login, um.email AS m_email, um.subscr AS m_subscr, um.is_banned AS m_banned, 
                    uo.uid AS o_uid, uo.uname AS o_uname, uo.usurname AS o_usurname, uo.login AS o_login, uo.email AS o_email, uo.subscr AS o_subscr, uo.is_banned AS o_banned, 
                    up.uid AS p_uid, up.uname AS p_uname, up.usurname AS p_usurname, up.login AS p_login, up.email AS p_email, up.subscr AS p_subscr, up.is_banned AS p_banned 
                FROM projects_contest_msgs cm 
                INNER JOIN projects_contest_offers co ON co.id = cm.offer_id 
                INNER JOIN projects p ON p.id = co.project_id 
                INNER JOIN users u ON u.uid = cm.user_id 
                INNER JOIN users uo ON uo.uid = co.user_id 
                INNER JOIN users up ON up.uid = p.user_id 
                LEFT JOIN projects_contest_msgs cp ON cp.id = cm.reply_to AND cp.user_id <> cm.user_id 
                LEFT JOIN users um ON um.uid = cp.user_id AND um.is_banned = '0' 
                WHERE cm.id IN({$comment_ids})"; 
        
        return $DB->rows($sql);  
    }



   /**
    * Выбрать данные по заблокированным пользователям для рассылки
    *
    * После изменения этой функции, необходимо перезапустить консьюмер /classes/pgq/mail_cons.php на сервере.
    * Если нет возможности, то сообщить админу.
    * @see pmail::ContestUserBlocked()
    * @see PGQMailSimpleConsumer::finish_batch()
    *
    * @param string|array  $ids  Идентификаторы проектов
    * @param resource      $connect Соединение к БД (необходимо в PgQ) или NULL -- создать новое.
    * @return array|mixed
    */
   function getContestsBlockedUsers($ids, $connect=NULL) {
       global $DB;
       if(!$ids) return NULL;
       if(is_array($ids))
           $ids = implode(',', array_unique($ids));

       $sql = "SELECT
                    p.name as project_name, p.id as project_id,
                    f.usurname, f.uname, f.login, f.email, f.subscr,
                    e.usurname as emp_uname, e.uname as emp_name, e.login as emp_login
            FROM projects_contest_blocked c
            INNER JOIN
                projects p
                ON p.id = c.project_id
            INNER JOIN
                freelancer f
                ON f.uid = c.user_id
            INNER JOIN
                employer e
                ON e.uid = p.user_id
                AND e.is_banned = '0'
            WHERE c.id IN ({$ids})";


       return $DB->rows($sql);
   }


    /**
     * Выбрать конкурсы по пользователям, для рассылки
     *
     * После изменения этой функции, необходимо перезапустить консьюмер /classes/pgq/mail_cons.php на сервере.
     * Если нет возможности, то сообщить админу.
     * @see pmail::ContestUserUnblocked()
     * @see PGQMailSimpleConsumer::finish_batch()
     *
     * @param string|array  $params  Идентификаторы проектов
     * @param resource      $connect Соединение к БД (необходимо в PgQ) или NULL -- создать новое.
     * @return array|mixed
     */
    function getContestsUnblocked($params, $connect=NULL) {
        global $DB;
        if(!$params) return NULL;
        
        $uids = array();
        $pids = array();

        $all = array();

        foreach($params as $param) {
            $uids[] = $param['id'];
            $pids[] = $param['project_id'];
            $all[$param['id']][] = $param['project_id'];
        }

        $uids = implode(',', array_unique($uids));
        $pids = implode(',', array_unique($pids));

        $sql = "SELECT
                    f.uid, f.usurname, f.uname, f.login, f.email, f.subscr
                FROM freelancer f
                WHERE f.uid IN ({$uids})";

        $sqlp = "SELECT
                    p.name as project_name, p.id as project_id,
                    e.usurname as emp_uname, e.uname as emp_name, e.login as emp_login
                FROM projects p
                INNER JOIN
                    employer e
                    ON e.uid = p.user_id
                    AND e.is_banned = '0'
                WHERE p.id IN ({$pids})";

 
        $users = array();
        $projects = array();

        $res = $DB->rows($sql);
        if($DB->error) return NULL;

        $resp = $DB->rows($sqlp);
        if($DB->error) return NULL;

        if($res) {
            foreach($res as $row) {
                $users[$row['uid']] = $row;
            }
        }
        if($resp) {
            foreach($resp as $row) {
                $projects[$row['project_id']] = $row;
            }
        }

        $result = array();
        foreach($all as $user => $prs) {
            if(!isset($users[$user])) continue;
            $result[$user]['user'] = $users[$user];
            foreach(array_unique($prs) as $proj) {
                if(!isset($projects[$proj])) continue;
                $result[$user]['projects'][] = $projects[$proj];
            }
        }

        return $result;
    }


    /**
     * Выборка информации по кандидатам к конкурсам для отправки уведомлений.
     *
     * После изменения этой функции, необходимо перезапустить консьюмер /classes/pgq/mail_cons.php на сервере.
     * Если нет возможности, то сообщить админу.
     * @see pmail::ContestAddCandidate()
     * @see PGQMailSimpleConsumer::finish_batch()
     *
     * @param string|array  $offer_ids      Идентификаторы ответов
     * @param resource      $connect        Соединение к БД (необходимо в PgQ) или NULL -- создать новое.
     * @return array|mixed                  Если есть ответы к проектам то возвращает массив их, если нет то NULL
     */
    function getSelectedOffers($offer_ids, $connect=NULL) {
        global $DB;
        if(!$offer_ids) return NULL;
        if(is_array($offer_ids))
            $offer_ids = implode(',', array_unique($offer_ids));

        $sql = "SELECT
                    f.usurname, f.uname, f.login, f.email, f.subscr, f.is_banned,
                    po.id, po.project_id, p.name as project_name, p.kind,
                    e.usurname as emp_uname, e.uname as emp_name, e.login as emp_login
                FROM
                    projects_contest_offers po
                INNER JOIN
                    projects p ON (p.id = po.project_id)
                INNER JOIN
                    freelancer f ON (f.uid = po.user_id)
                INNER JOIN
                  employer e
                    ON e.uid = p.user_id
                   AND e.is_banned = '0'
                WHERE
                    po.id IN({$offer_ids})
                    AND po.selected = TRUE";

        return $DB->rows($sql);
    }


    /**
     * Выборка информации по победителям в конкурсах для отправки уведомлений.
     *
     * После изменения этой функции, необходимо перезапустить консьюмер /classes/pgq/mail_cons.php на сервере.
     * Если нет возможности, то сообщить админу.
     * @see pmail::ContestWinners()
     * @see PGQMailSimpleConsumer::finish_batch()
     *
     * @param string|array  $offer_ids      Идентификаторы ответов
     * @param resource      $connect        Соединение к БД (необходимо в PgQ) или NULL -- создать новое.
     * @return array|mixed                  Если есть ответы к проектам то возвращает массив их, если нет то NULL
     */
    function getWinnerOffers($offer_ids, $connect=NULL) {
        global $DB;
        if(!$offer_ids) return NULL;
        if(is_array($offer_ids))
            $offer_ids = implode(',', array_unique($offer_ids));

        $sql = "SELECT
                    f.usurname, f.uname, f.login, f.email, f.subscr, f.is_banned,
                    po.id, po.project_id, p.name as project_name, p.kind,
                    e.usurname as emp_uname, e.uname as emp_name, e.login as emp_login,
                    po.position
                FROM
                    projects_contest_offers po
                INNER JOIN
                    projects p ON (p.id = po.project_id)
                INNER JOIN
                    freelancer f ON (f.uid = po.user_id)
                INNER JOIN
                  employer e
                    ON e.uid = p.user_id
                   AND e.is_banned = '0'
                WHERE
                    po.id IN({$offer_ids})
                    AND po.position > 0";

        return $DB->rows($sql);
    }
    
    /**
     * Статистика конкурса за текущий день
     *
     */
    public function getStatContest() {
        global $DB;
        
        $now = date('c', mktime(0, 0, 0));
		$stat_sql = "SELECT COUNT(o.*) as cnt FROM projects_contest_offers as o LEFT JOIN freelancer f ON o.user_id = f.uid 
		             WHERE o.project_id = {$this->pid} AND f.is_banned = '0' AND o.is_deleted = false AND o.post_date > '{$now}'";
		
		$this->stat['offers_today']  = $DB->val($stat_sql);
		$stat_sql = "SELECT COUNT(m.*) as cnt FROM projects_contest_offers as o JOIN projects_contest_msgs as m ON m.offer_id = o.id LEFT JOIN freelancer f ON o.user_id = f.uid 
                     WHERE o.project_id = {$this->pid} AND f.is_banned = '0' AND o.is_deleted = false AND m.post_date > '{$now}' ";
        $this->stat['comments_today']  = $DB->val($stat_sql);
        
        $stat_sql = "SELECT COUNT(o.*) as cnt FROM projects_contest_offers as o LEFT JOIN freelancer f ON o.user_id = f.uid 
		             WHERE o.project_id = {$this->pid} AND f.is_banned = '0' AND o.is_deleted = false";
		$this->stat['offers']  = $DB->val($stat_sql);
    }
    
    /**
     * Удаляем кеш тк прочитали сообщения
     * @param type $user_id
     */
    public function markReadComments($user_id) {
        $memBuff = new memBuff();
        $memBuff->delete("prjMsgsCnt" . $user_id);
        $memBuff->delete("prjMsgsCntWst" . $user_id);
    }
    
    /**
     * возвращает информацию о проекте (конкурсе) по ID комментария
     * @param integer $commentID
     */
    public function getProjectByCommentID ($commentID) {
        global $DB;
        $sql = "
            SELECT p.pro_only, p.verify_only
            FROM projects p
            INNER JOIN projects_contest_offers pco
                ON pco.project_id = p.id
            INNER JOIN projects_contest_msgs pcm
                ON pcm.offer_id = pco.id
            WHERE pcm.id = ?i";
        $row = $DB->row($sql, $commentID);
        return $row;
    }
    
    
    
    /**
     * Указанный фрилансер занимал призовые места у заказчика
     * 
     * @global type $DB
     * @param type $to_id
     * @param type $from_id
     * @return type
     */
    public static function isPrizePlace($from_id, $to_id)
    {
        global $DB;
        return $DB->val("SELECT 1 
                         FROM projects_contest_offers AS pco
                         INNER JOIN projects AS p ON p.id = pco.project_id
                         WHERE 
                            p.kind = 7 
                            AND pco.position > 0 
                            AND p.user_id = ?i 
                            AND pco.user_id = ?i
                         LIMIT 1", $to_id, $from_id);
    }
    
    
}