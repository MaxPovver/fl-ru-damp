<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
/**
 * Класс для работы с отзывами консультантов
 *
 *
 */
class webim {

	/**
	 * количество записей на страницу в админке
	 *
	 */
	const REC_ON_PAGE = 20;

	/**
	 * id текущего диалога
	 * 
	 * @var integer
	 */
	public $thread = 0;

	/**
	 * имя клиента текущего диалога
	 *
	 * @var string
	 */
	public $client = '';

	/**
	 * uid пользователя текущего диалога
	 *
	 * @var integer
	 */
	public $clientid = 0;

	/**
	 * имя оператора текущего диалога
	 *
	 * @var string
	 */
	public $operator = '';

	/**
	 * id оператора текущего диалога
	 *
	 * @var integer
	 */
	public $operatorid = 0;

	/**
	 * текущий диалог
	 *
	 * @var array
	 */
	public $dialog = array();
    
    /**
     * хранит текущие подключение к базе webim (mysql)
     * 
     * @var resource
     */
    protected $_webimConn = NULL;

	/**
	 * Получает диалог между оператором и пользователем с базы webim
	 *
	 * @param  integer  $uid  uid пользователя
	 * @param  integer  $thread  id диалога
	 *
	 * @return array    данные диалога: client - имя клиента, operator - имя оператора, operatorid - id оператора, dialog - диалог
	 */
	public function GetChat($uid, $thread) {
        $this->_webimConnect();
		$res = mysql_query("
            SELECT m.*, o.fullname 
            FROM chatmessage m 
            LEFT JOIN chatoperator o ON m.operatorid = o.operatorid 
            WHERE m.threadid = '{$thread}' ORDER BY m.created
        ", $this->_webimConn);
		$this->thread = $thread;
		$this->clientid = $uid;
		$this->client = '';
		$this->operatorid = 0;
		$this->operator = '';
		$this->dialog = array();
		while ($row = mysql_fetch_assoc($res)) {
			if (!$this->client && $row['sendername']) {
				$this->client = $row['sendername'];
			}
			if (!$this->operatorid && $row['operatorid']) {
				$this->operatorid = $row['operatorid'];
				$this->operator   = $row['fullname'];
			}
			$this->dialog[] = array(
				'client' => ($row['sendername']? $row['sendername']: ''),
				'operator' => ($row['fullname']? $row['fullname']: ''),
				'time' => $row['created'],
				'message' => $row['message']
			);
		}
		return empty($this->dialog)? FALSE: TRUE;
	}

	/**
	 * Оставить отзыв к диалогу с консультантом
	 *
	 * @param integer  $e1 Баллы (от 1 до 5)за "Ожидание ответа"
	 * @param integer  $e2 Баллы (от 1 до 5)за "Доступное содержание"
	 * @param integer  $e3 Баллы (от 1 до 5)за "Общее впечатление"
	 * @param string   $wish Пожелание
	 *
	 * @return string  Возможная ошибка
	 */
	public function Evaluate($e1, $e2, $e3, $wish) {
		if (empty($this->dialog)) {
			return 'Указанного обращения не существует или вы уже оставили отзыв.';
		}
		
		global $DB;
		
		$count = $DB->val("SELECT COUNT(*) FROM webim WHERE thread = ?", $this->thread);
		
		if ($count) {
			return 'Указанного обращения не существует или вы уже оставили отзыв.';
		}
		
		$e1 = intval($e1);
		$e2 = intval($e2);
		$e3 = intval($e3);
		$wish = trim($wish);
		$dialog = '';
		foreach ($this->dialog as $row) {
			$name = $row['client']? ($row['client'].': '): ($row['operator']? ($row['operator'].': '):  '');
			$dialog .= "[".$row['time']."] " . $name . $row['message'] . "\n\n";
		}
		
		$aData = array(
            'thread'        => $this->thread, 
            'user_id'       => !empty($this->clientid) ? $this->clientid : NULL, 
            'user_name'     => $this->client, 
            'operator_id'   => !empty($this->operatorid) ? $this->operatorid: NULL, 
            'operator_name' => $this->operator, 
            'dialog'        => $dialog, 
            'evaluation1'   => $e1, 
            'evaluation2'   => $e2, 
            'evaluation3'   => $e3, 
            'wish'          => !empty($wish) ? $wish : NULL
		);
		
		$DB->insert( 'webim', $aData );
		
		// уведомляем консультанта о факте оценки в его диалоге
		if ( !$DB->error ) {
		    $this->_webimConnect();
		    
		    $mRes = mysql_query( "INSERT INTO chatmessage (threadid, kind, message, created) 
                VALUES ('{$this->thread}', '3', 'Пользователь поставил вам оценку', '".date('Y-m-d H:i:s')."')", 
                $this->_webimConn 
            );
            
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/webim/classes/config.php' );
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/webim/classes/functions.php' );
            
            $filename = $this->thread . HAS_MESSAGES_OPERATOR_FILE_POSTFIX;
            $filename = ONLINE_FILES_DIR . DIRECTORY_SEPARATOR . substr(md5($filename), 0, 1) . DIRECTORY_SEPARATOR . $filename;
            
            set_has_threads( $filename );
		}
		
		return $DB->error;
	}

	/**
	 * Проверка, оценивали ли уже диалог и существует ли он вообще
	 * 
	 * @param  integer  $thread   id диалога в webim
     * @param  string   $visitor  id визитера в webim
	 * @return boolean  TRUE - диалог существует и оценку ему не ставили, FALSE - в противном случае
	 */
	public function Check($thread, $visitor) {
		if ( $GLOBALS['DB']->val("SELECT COUNT(*) FROM webim WHERE thread = ?", $thread) ) {
            return FALSE;
        }
        $this->_webimConnect();
        $sql = "
            SELECT COUNT(*)
            FROM chatthread t 
            INNER JOIN chatvisitsession s ON t.visitsessionid = s.visitsessionid 
            WHERE t.threadid = '{$thread}' AND s.visitorid = '{$visitor}'
        ";
        $res = mysql_query($sql, $this->_webimConn);
        $row = mysql_fetch_row($res);
        return (bool) $row[0];
	}

	/**
	 * Статистика за текущий месяц
	 *
	 * @return array  count - отзывов в этом месяце, pcount - отзывов в прошлом месяце, div - разица отзывов между месяцами, average - средний отзыв за месяц
	 */
	public function MonthlyStat() {
	    global $DB;
		$cur = date('Y-m-01 00:00:00');
		$sql = "SELECT COUNT(*) AS count, SUM(evaluation1 + evaluation2 + evaluation3) AS average FROM webim WHERE post_time >= ?";
		$row = $DB->row( $sql, $cur );
		
		$sql = "SELECT COUNT(*) AS count FROM webim WHERE post_time < ? AND post_time >= (date ? - interval '1 month')";
		$row['pcount'] = $DB->val($sql, $cur, $cur );
		
		$row['div'] = $row['count'] - $row['pcount'];
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
		if (!empty($filter['sdate'])) $where .= " AND f.post_time >= '{$filter['sdate']} 00:00:00' ";
		if (!empty($filter['edate'])) $where .= " AND f.post_time <= '{$filter['edate']} 23:59:59' ";
		if (!empty($filter['kind'])) $where .= " AND f.operator_id = '{$filter['kind']}' ";
		if (!empty($sort)) {
			if ($sort == 'date') {
				$sort = " ORDER BY f.post_time DESC ";
			} else if ($sort == 'average') {
				$sort = " ORDER BY score DESC ";
			}
		}
		
		global $DB;
		$nums = $DB->val( "SELECT COUNT(*) FROM webim f WHERE 1 = 1 $where" );
		
		if ( $nums > 0 ) {
			$sql = "SELECT
					f.*, ((f.evaluation1 + evaluation2 + evaluation3) / 3) AS average, (f.evaluation1 + evaluation2 + evaluation3) AS score, 
					u.login, u.uname, u.usurname, u.email
				FROM 
					webim f 
				LEFT JOIN 
					users u ON f.user_id = u.uid 
				WHERE 
					1 = 1
				$where
				$sort";
			
			return $DB->rows( $sql.' LIMIT '.self::REC_ON_PAGE.' OFFSET '.(($pagenum - 1) * self::REC_ON_PAGE) );
		} else {
			return array();
		}
	}

	/**
	 * Возвращает список консультантов
	 *
	 * @return array   массив с данными
	 */
	public function Consultants() {
        $this->_webimConnect();
		$res = mysql_query("SELECT * FROM chatoperator", $this->_webimConn);
		$rows = array();
		while ($row = mysql_fetch_assoc($res)) {
			$rows[] = $row;
		}
		return $rows;
	}

	/**
	 * Получает данные об отзыве
	 *
	 * @param  integer  $thread  id диалога
	 *
	 * @return array    массив с данными
	 */
	public function Get($thread) {
	    global $DB;
		return $DB->row( "SELECT * FROM webim WHERE thread = ?i", intval($thread) );
	}
    
    /**
     * Подключение к mysql базе webim
     * 
     */
    protected function _webimConnect() {
        if ( empty($this->_webimConn) ) {
            require_once $_SERVER['DOCUMENT_ROOT'].'/webim/classes/config.php';
            $this->_webimConn = mysql_connect(EXTERNAL_DB_HOST, EXTERNAL_DB_USER, EXTERNAL_DB_PASSWORD);
            mysql_select_db(EXTERNAL_DB_NAME, $this->_webimConn);
            mysql_set_charset('CP1251', $this->_webimConn);
        }
    }
    
    
  }