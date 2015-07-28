<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/blogs.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/smail.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
/**
 * Класс для работы с обратной связью
 *
 */
class feedback {

	/**
	 * Максимальный размер файла для загрузки
	 */
	const MAX_FILE_SIZE = 5242880; //5Mb
	
	/**
	 * Максимальное число вложенных файлов
	 *
	 */
	const MAX_FILES = 10;
	
	/**
	 * Количество записей на страницу (в админке)
	 */
	const REC_ON_PAGE = 20;

	/**
	 * Максимальное количество символов в пожелании
	 */
	const MAX_WISH_CHARS = 5000;
	
    /**
     * Коды отделов в helpdesk
     *
     * @var array
     */
	public $departaments = array(
		'er' => 'Ошибки на сайте',
		'fi' => 'Финансы и реклама',
		'io' => 'Общие вопросы',
		'MN' => 'Личный менеджер',
		'NR' => '«Безопасная Сделка»'
	);
	
	/**
	 * Добавляет сообщение в обратную связь и отсылает письмо в необходимый отдел
	 * 
	 * @param integer $uid    uid пользователя, если он авторизован
	 * @param string  $login  имя пользователя, если он не авторизован
	 * @param string  $email  email пользователя, если он не авторизован
	 * @param integer $kind   id отдела (1-общие вопросы, 2-ошибки на сайте, 3-финансовый вопрос, 4-лич.менеджер, 5-сбр)
	 * @param string  $msg    сообщение
	 * @param CFile   $files   прикрепленный файл
     *
     * @return string         возможная ошибка
	 */
	public function Add($uid, $login, $email, $kind, $msg, $files,$additional=false) {
	    global $DB;
        mt_srand();
		$uc = md5(microtime(1).mt_rand());
		$uc = substr($uc, 0, 6).substr($uc, 12, 6);
        $login = substr($login, 0, 64);
		$uid = intval($uid);
		$kind = intval($kind);
		if (intval($uid)) {
			$user = new users;
			$user->GetUserByUID($uid);
			$login = $user->login;
			$email = $user->email;
		}
		$sql = 'INSERT INTO feedback 
				( uc, dept_id, user_id, user_login, email, question, request_time ) 
			VALUES
				( ?, ?, ?, ?, ?, ?, NOW() ) RETURNING id';
        if ( strtolower( mb_detect_encoding($login, array("utf-8")) ) == "utf-8" ) {
            $login = iconv("UTF-8", "WINDOWS-1251//IGNORE", $login);
        }
		$sId = $DB->val( $sql, $uc, $kind, $uid, $login, $email, $msg );
		
		if ( $DB->error ) {
			return 'Ошибка при отправке сообщения (db)';
		}
		
		$mail = new smail;
		if (count($files)){
                    foreach ($files as $attach) {
                        $msg .= "\n\n=============================================\n";
			$msg .= "К этому письму прикреплен файл ".WDCPREFIX."/upload/about/feedback/{$attach->name}";
			$msg .= "\n=============================================\n";
                    }
		}
                if($kind == 2){
                        $msg .= "\n\n=============================================\n";
			$msg .= "Дополнительная информация: браузер: ". (!empty($additional['browser']) ? $additional['browser'] : 'N/A').' ОС: '.(!empty($additional['os']) ? $additional['os'] : 'N/A');
			$msg .= "\n=============================================\n";
                }
		$mail->FeedbackPost( $login, $email, $kind, $msg, $uc, $sId );
		
		// Пишем статистику ображений в feedback
		$date  = date('Y-m-d H:01:00');
		$sql   = 'SELECT date FROM stat_feedback WHERE date=? AND type=?';
		$exist = $DB->val( $sql, $date, $kind );
		
		if ( $exist ) {
			$sql = "UPDATE stat_feedback SET count=count+1 WHERE date = ? AND type = ?";
		} else {
			$sql = "INSERT INTO stat_feedback(date,type,count) VALUES( ?, ?, 1 )";
		}
		
		$DB->query( $sql, $date, $kind );
		
		return '';
	}
	
	/**
	 * Проверяет, оставляли ли отзыв к тикету в обратой связи
	 * 
	 * @param  string  $uc уникальные номер тикета обратной связи
	 * @param  integer $deskid номер тикета в helpdesk
	 * @return boolean TRUE если отзыв не оставляли, FALSE если оставляли или отзыва не существует
	 */
	public function Check( $uc, $deskid ) {
	    global $DB;
	    
	    $bRet = false;
	    // текущий вариант: проверяем метку времени голосования для пары uc и desk_id
		$aRow = $DB->row( 'SELECT id, evaluation_time FROM feedback WHERE uc = ? AND desk_id = ?', $uc, $deskid );
		
		if ( $aRow ) {
			$bRet = empty( $aRow['evaluation_time'] );
		}
		else {
		    // для совместимости со старым вариантом: если есть uc, но нет desk_id - значит еще не оценивали
		    $bRet = (bool) $DB->val( 'SELECT COUNT(*) FROM feedback WHERE uc = ? AND desk_id IS NULL', $uc );
		}
		
		return $bRet;
	}
	

	/**
	 * Оставить отзыв к тикету обратной связи
	 *
	 * @param string   $uc уникальный номер тикета обратной связи
	 * @param integer  $deskid номер тикета в helpdesk
	 * @param integer  $e1 Баллы (от 1 до 5)за "Ожидание ответа"
	 * @param integer  $e2 Баллы (от 1 до 5)за "Доступное содержание"
	 * @param integer  $e3 Баллы (от 1 до 5)за "Общее впечатление"
	 * @param string   $wish Пожелание
	 *
	 * @return string  Возможная ошибка
	 */
	public function Evaluate($uc, $deskid, $e1, $e2, $e3, $wish) {
		$e1 = intval($e1);
		$e2 = intval($e2);
		$e3 = intval($e3);
		global $DB;
		// текущий вариант: проверяем метку времени голосования для пары uc и desk_id
		$row = $DB->row( 'SELECT id, evaluation_time FROM feedback WHERE uc = ? AND desk_id = ?', $uc, $deskid );
		
		if ( !$row ) {
		    // для совместимости со старым вариантом: если есть uc, но нет desk_id - значит еще не оценивали
		    $row = $DB->row( 'SELECT id FROM feedback WHERE uc = ? AND desk_id IS NULL', $uc );
		    
		    if ( !$row ) {
    			return 'Указанного обращения не существует или вы уже оставили отзыв.';
    		}
		}
		elseif ( !empty($row['evaluation_time']) ) {
		    return 'Указанного обращения не существует или вы уже оставили отзыв.';
		}
		
		$sql = "
			UPDATE
				feedback 
			SET
				desk_id = ?, evaluation1 = ?, evaluation2 = ?, evaluation3 = ?, wish = ?, evaluation_time = NOW() 
			WHERE
				id = ?";
		
		$DB->query( $sql, $deskid, $e1, $e2, $e3, $wish, $row['id'] );
		
		return '';
	}

	/**
	 * Расшифровка номера вопроса с helpdesk
	 *
	 * @param  string  $code уникальный код тикета обратной свзи + зашифрованный номер из helpdesk
	 * 
	 * @return array   id-номер из helpdesk, uc-уникальный код тикета обратной связи
	 */
	public function DecodeUCode($code) {
		// используется для шифрования номера тикета из helpdesk, для того, чтобы не лесть в базу helpdesk'а
		// не опасно, даже если код расшифруют, то все-равно не смогут проголосовать за чужой тикет, т.к. для
		// его определения используется уникальный номер тикета обратной связи ($uc)
		$c = array(
			'e'=>'0', 'i'=>'0', 'b'=>'0', 'z'=>'0', '2'=>'0', '9'=>'0',
			'j'=>'1', 'm'=>'1', 'c'=>'1', 'p'=>'2', '7'=>'2', 'v'=>'2',
			'f'=>'3', '8'=>'3', 's'=>'3', 'u'=>'4', 'r'=>'4', 'd'=>'4',
			'y'=>'5', '0'=>'5', 'h'=>'5', 'w'=>'6', 'k'=>'6', 'l'=>'6',
			'n'=>'7', 'g'=>'7', 'q'=>'7', '3'=>'8', '1'=>'8', 't'=>'8',
			'x'=>'9', 'a'=>'9', 'o'=>'9'
		);
		$n = array('fi'=>'fi', 'io'=>'io', 'er'=>'er', 'mn'=>'MN', 'nr'=>'NR');
		$strlen = strlen($code);
		if ($strlen < 20 || $strlen > 30) {
			return FALSE;
		}
		$id = substr($code, 0, 6) . substr($code, 18);
		$uc = substr($code, 6, 12);
		$lenid = strlen($id);
		if (!isset($n[strtolower(substr($id, 0, 2))])) {
			return FALSE;
		}
		for ($i=2; $i<$lenid; $i++) {
			if (!isset($c[$id{$i}])) {
				return FALSE;
			}
			$id{$i} = $c[$id{$i}];
		}
		return array('id'=>$id, 'uc'=>$uc);
	}
	
	/**
	 * Статистика за текущий месяц
	 * 
	 * @param  array  $filter содержит фильтр запроса (для WHERE)
	 * @return array  count - отзывов в этом месяце, pcount - отзывов в прошлом месяце, div - разица отзывов между месяцами, average - средний отзыв за месяц
	 */
	public function MonthlyStat( $filter = array() ) {
	    $where = "";
	    
	    if ( !empty($filter['kind']) ) $where .= " AND dept_id = '{$filter['kind']}' ";
	    
	    global $DB;
		$cur = date('Y-m-01 00:00:00');
		$sql = "SELECT COUNT(*) AS count, SUM(evaluation1 + evaluation2 + evaluation3) AS average FROM feedback WHERE evaluation_time IS NOT NULL AND request_time >= ? $where";
		$row = $DB->row( $sql, $cur );
		$sql = "SELECT COUNT(*) AS count FROM feedback WHERE evaluation_time IS NOT NULL AND request_time < ? AND request_time >= (date ? - interval '1 month') $where";
		
		$row['pcount']  = $DB->val($sql, $cur, $cur );
		$row['div']     = $row['count'] - $row['pcount'];
		$row['average'] = $row['count']? ($row['average'] / ($row['count'] * 3)): 0;
		
		return $row;
	}
	
	/**
	 * Показать список тикетов обратной связи с отзывами
	 *
	 * @param integer  $nums возвращает количество найдениых тикетов
	 * @param array    $filter содержит фильтр запроса (для WHERE)
	 * @param string   $sort параметр сортировки
	 * @param integer  $pagenum номер отображаемой страницы
	 *
	 * @return array   массив с данными
	 */
	public function ShowAll(&$nums, array $filter, $sort, $pagenum) {
		$where = "";
		if (!empty($filter['sdate'])) $where .= " AND f.request_time >= '{$filter['sdate']} 00:00:00' ";
		if (!empty($filter['edate'])) $where .= " AND f.request_time <= '{$filter['edate']} 23:59:59' ";
		if (!empty($filter['kind'])) $where .= " AND f.dept_id = '{$filter['kind']}' ";
		if (!empty($sort)) {
			if ($sort == 'date') {
				$sort = " ORDER BY f.request_time DESC ";
			} else if ($sort == 'average') {
				$sort = " ORDER BY score DESC ";
			}
		}
		
		global $DB;
		$nums = $DB->val( "SELECT COUNT(*) FROM feedback f WHERE evaluation_time IS NOT NULL $where" );
		
		if ( $nums ) {
			$sql = "
				SELECT
					f.*, ((f.evaluation1 + evaluation2 + evaluation3) / 3) AS average, (f.evaluation1 + evaluation2 + evaluation3) AS score, 
					u.login, u.uname, u.usurname, u.email
				FROM 
					feedback f 
				LEFT JOIN 
					users u ON f.user_id = u.uid 
				WHERE 
					evaluation_time IS NOT NULL
				$where
				$sort
			";
			
			return $DB->rows( $sql.' LIMIT '.self::REC_ON_PAGE.' OFFSET '.(($pagenum - 1) * self::REC_ON_PAGE) );
		} else {
			return array();
		}
	}

}