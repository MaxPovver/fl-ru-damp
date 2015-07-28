<?
/**
 * Подключаем файл с основными функциями
 *
 */
require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
/**
 * Класс для работы с блогами
 *
 */
class blogs {
    
    const FILE_TABLE = 'file_blogs';
    /**
     * Допустимые параметры подразделов вкладки "Мои"
     *
     * @var array
     */
    static $nav_my = array( 'my_all', 'my_posts', 'my_comments' );
    
    /**
     * Допустимые параметры подразделов вкладки "Закладки"
     *
     * @var array
     */
    static $nav_favs = array( 'favs_list', 'favs_std' );
    
    /**
     * Названия приоритетов важности закладок
     *
     * @var array
     */
    static $priority_name = array( 'Низкая', 'Средняя', 'Высокая', 'Высшая' );
    
    /**
     * Картинки приоритетов важности закладок
     *
     * @var array
     */
    static $priority_img  = array( 'bsg.png', 'bsgr.png', 'bsy.png', 'bsr.png' );
    
    /**
     * ИД разделов ссылки которых ведут на копини
     * 
     * @var type 
     */
    static $copiny_group  = array(10,17,57);

    /**
     * Массив тем(групп) блогов
     *
     * @var array
     */
    public $thread;

    /**
     * Количество сообщений в блоге
     *
     * @var integer
     */
    var $msg_num;

    /**
     * Идентификатор сообщения
     *
     * @var integer
     */
    var $id;

    /**
     * Идентификатор темы(групп) блогов
     *
     * @var integer
     */
    var $id_gr;

    /**
     * Идентификатор "базы" блогов (см. таблицу blogs_themes)
     *
     * @var integer
     */
    var $base;

    /**
     * Время публикации сообщения
     *
     * @var string
     */
    var $post_time;

    /**
     * Текст сообщения
     *
     * @var string
     */
    var $msgtext;

    /**
     * Прикрепленный к сообщению файл
     *
     * @var string
     */
    var $attach;

    /**
     * Ссылка на YouTube видео.
     *
     * @var string
     */
    var $yt_link;

    /**
     * Заголовок сообщение
     *
     * @var string
     */
    var $title;

    /**
     * Имя пользователя, оставившего сообщение
     *
     * @var string
     */
    var $uname;

    /**
     * Фамилия пользователя, оставившего сообщение
     *
     * @var string
     */
    var $usurname;

    /**
     * Логин пользователя, оставившего сообщение
     *
     * @var string
     */
    var $login;

    /**
     * Идентификатор сообщения ответом на которое является данное сообщение
     *
     * @var integer
     */
    var $reply;

    /**
     * Фотка пользователя
     *
     * @var char
     */
    var $photo;

    /**
     * Проверка статуса аккаунта (ПРО Тест, или не ПРО Тест)
     *
     * @var boolean
     */
    var $is_pro_test;

    /**
     * Публичная или закрытое сообщение
     *
     * @var boolean
     */
    var $is_private;

    /**
     * Закрыть возможность комментирования
     *
     * @var boolean
     */
    var $close_comments;
  /**
	 * Количество голосований
	 *
	 * @var integer
	 */
    var $cnt_role;
	/**
	 * Модификация сообщения, 0 - не модифицировалось 1- модицицировалось
	 *
	 * @var date
	 */
    var $modified;
    /**
     * Кто модифицировал сообщение (ИД Пользователя)
     *
     * @var integer
     */
    var $modified_id;
	/**
	 * Метод показа
	 *
	 * @var char
	 */
    var $small;
	/**
	 * Кто удаливший сообщение (ИД Пользователя)
	 *
	 * @var integer
	 */
    var $deluser_id;
	/**
	 * Удаленное сообщение, 0 - не удалено, 1 - Удалено
	 *
	 * @var boolean
	 */
    var $deleted;
	/**
	 * Количество предупреждений сообщению
	 *
	 * @var integer
	 */
    var $warn;
	/**
	 * Бан сообщения 0 - не забанено, 1 - забанено
	 *
	 * @var boolean
	 */
    var $is_banned;
	/**
	 * Кто написавший сообщение (ИД Пользователя)
	 *
	 * @var uinteger
	 */
    var $fromuser_id;
	/**
	 * Уровень вложенности комментариев
	 *
	 * @var integer
	 */
    var $level = - 1;
	/**
	 * Последний индекс сообщения
	 *
	 * @var integer
	 */
    var $last_inx = 0;
	/**
	 * Специализация фрилансера
	 *
	 * @var string
	 */
    public $spec;
    /**
     * Название професии фрилансера
     *
     * @var string
     */
    public $prof_name;
    /**
     * Дата регистрации
     *
     * @var date
     */
    public $reg_date;
    
    
    public $completed_cnt;
	/**
	 * Максимально допустимый размер вложенных в сообщение файлов
	 *
	 */
    const MAX_FILE_SIZE = 5242880;
	/**
	 * Максимальное число файлов вложенных с ообщение
	 *
	 */
	const MAX_FILES = 10;
	/**
	 * Максимально допустимый размер картинки
	 *
	 */
    const MAX_IMAGE_SIZE = 307200;
	/**
	 * Максимальное кол-во символов в сообщении
	 *
	 */
    const MAX_DESC_CHARS = 20000;
	/**
	 * максимальное кол-во вариантов ответа в опросе
	 *
	 */
	const MAX_POLL_ANSWERS = 10;
	/**
	 * Максимальное кол-во символов вопроса
	 *
	 */
	const MAX_POLL_CHARS = 256;
	/**
	 * Максимальное кол-во символов ответа
	 *
	 */
    const MAX_POLL_ANSWER_CHARS = 96;
    /**
	 * Максимальная ширина изображения
	 *
	 */
    const MAX_IMAGE_WIDTH = 600;
    /**
	 * Максимальное кол-во символов ответа
	 *
	 */
    const MAX_IMAGE_HEIGHT = 1000;
    /**
     * Возвращает данные необходимые для построения ЧПУ ссылки поста
     *
     * @param     integer    $id    ID поста
     * @return    array             Данные для ЧПУ
     */
    public function getMsgInfoForFriendlyURL($id) {
        global $DB;
        $sql = "SELECT msgs.thread_id as id, msgs.title as name, groups.t_name as category 
                FROM blogs_msgs as msgs 
                INNER JOIN blogs_themes as themes ON themes.thread_id = msgs.thread_id
                INNER JOIN blogs_groups as groups ON groups.id = themes.id_gr 
                WHERE msgs.thread_id=?i and msgs.reply_to is null
                
                UNION ALL
                
                SELECT msgs.thread_id as id, msgs.title as name, 
                       (CASE WHEN themes.base = 3 THEN 'project' 
                             WHEN themes.base = 5 THEN 'contest' 
                             ELSE '' 
                        END) as category
                FROM blogs_msgs as msgs 
                INNER JOIN blogs_themes_old as themes ON themes.thread_id = msgs.thread_id
                WHERE msgs.thread_id=?i and msgs.reply_to is null    
                ";
        return $DB->cache(1800)->row($sql, $id, $id);
    }

    /**
     * Возвращает список разделов блогов и кол-во сообщений в них
     *
     * @param string $error				возвращает сообщение об ошибке
     * @param integer $mod				тип пользователя (1 - простой, 0 - админ)
     * @param boolean $usecache         использовать кеш или нет
     * @return array					[идентификатор группы, название группы, позиция в списке, 0, разрешено ли всем читать этот раздел, номер строки (больше не используется)]
     */
    function GetThemes(&$error, $mod = 1, $usecache = true) {
        global $DB;
        
        $sql = "
          SELECT t.*, g.*, g.perm as in_blogs, 0 as t
            FROM blogs_groups g
          LEFT JOIN (
            SELECT COUNT(1) as num, id_gr
              FROM blogs_themes
             GROUP BY id_gr
            ) as t
              ON t.id_gr = g.id
           WHERE g.perm & ? = ?
           ORDER BY g.str,  g.n_order
        ";
        
        return ($usecache ? $DB->cache(1800)->rows($sql, $mod, $mod) : $DB->rows($sql, $mod, $mod));
    }
    
    function getRandomThemes($size = 3, $mod = 1) {
        global $DB;
        
        $sql = "
          SELECT g.*, g.perm as in_blogs, 0 as t
            FROM blogs_groups g
           WHERE g.perm & ? = ?
           ORDER BY RANDOM()
           LIMIT 10;
        ";
        
        $themes = $DB->cache(7200)->rows($sql, $mod, $mod);
        
        if($themes) {
            if($size > count($themes)) $size = count($themes);
            $rand_keys = array_rand($themes, $size);
            foreach($rand_keys as $k=>$v) $result[] = $themes[$v];
            
            return $result;
        }
        
        return false;
    }
    
    /**
     * Возвращает список тем в разделе
     *
     * -------------------------------------------------------------------------------------
     * Внимание! Замечание к подзапросу на основные вкладки (новые, популярные, актуальные).
     *
     * Подзапрос настроен на работу индексов типа "ix blogs_themes/viewgroup*", поэтому любое
     * добавление условий или (тем более с привязкой дополнительных таблиц!) испортит его.
     * Суть заключается в том, что мы все условия копируем в таблицы blogs_themes из таблиц blogs_msgs и других,
     * для того, чтобы использовать индексы и не делать лишних джойнов, т.е. все проверки делаются в рамках 
     * единственной
     * таблицы. Поэтому мы быстро выбираем все необходимые N тем и только потом связываем их с остальными таблицами.
     * Чтобы добавить условие, нужно по аналогии, например, с deleted, is_blocked и т.п.), добавить клон 
     * необходимого
     * поля в blogs_themes и скорректировать индекс(ы) (протестировав предварительно).
     * -------------------------------------------------------------------------------------
     *
     *
     * @param integer $gr_num				идентификатор раздела
     * @param string  $gr_name				возвращает название раздела
     * @param integer $num_treads			возвращает кол-во тем в данном разделе
     * @param integer $page					номер страницы из списка тем, которую надо выводить [получает или возвращает, в зависимости от $having_message]
     * @param string  $error				возвращает сообщение об ошибке
     * @param integer $fid					UID текущего юзера
     * @param integer $mod					имеет ли текущий юзер права на просмотр данного раздела [1 - да, 0 - нет]
     * @param integer $having_message		УСТАРЕЛО. id сообщения, страницу с которым необходимо отобразить (0 - показать страницу $page)
     * @param integer $read_only			возвращает параметр - является ли данный раздел разделом "только для чтения"
     * @param string  $ord					параметр сортировки ("my", "relevant", "best", "new", "favs")
     * @param string  $sort_order           позволяет передать дополнительные условия сортировки
     * @return array						[[информация о сообщении]]
     */
    function GetGroup($gr_num, &$gr_name, &$num_treads, $page, &$error, $fid = 0, $mod = 1, $having_message = 0, &$read_only, $ord = "new", $is_ban=true, $sort_order = '') {
      	global $DB;
        $cachedelay = 300; 
        if (hasPermissions("blogs")) $cachedelay = 0;        
        $memBuff = new memBuff();

        $fid = (int)$fid;
        $gr_num = (int)$gr_num;
        $num_treads = 0;
        $year = date('Y');
        $can_prevyear_ontops = date('n') <= 2;
        $limit = $GLOBALS['blogspp'];
        $group = ($gr_num != 0) ? "id_gr = {$gr_num} " : "";
        $offset = $limit * ($page - 1);
        $offset = intvalPgSql( (string) $offset );
        $limit_str = "LIMIT $limit OFFSET $offset";
        $order = "post_time DESC";
		$ids = array();
		$idx = array();
        $get_ontops = $ord == 'ontop';
        
        
        if(!$get_ontops) {
            if ($gr_num != 0) {
                $gr_name = $this->GetGroupName($gr_num, 0, $mod);
                if (! $gr_name) {
                    $error = "У вас недостаточно прав для просмотра этого форума";
                    return 0;
                }
            } else {
                $gr_name = "Все вместе";
            }
        }
        

        // смотрим баны
        if ($fid && $fid == $_SESSION['uid']) {
            $role = $_SESSION['role'];
        } else {
            if ($fid) {
                $users = new users();
                $role = $users->GetField($fid, $error, 'role');
            } else {
                $role = 0;
            }
        }
        
        $is_moder = hasPermissions('blogs', $fid);
        
        if ($is_moder) {
            $sel_blocked  = ", moderators.login as moder_login, moderators.uname AS moder_name, moderators.usurname as moder_uname";
            $join_blocked = " LEFT JOIN users AS moderators ON blogs_blocked.admin = moderators.uid ";
        } else {
            $where_blocked = '(t.is_blocked = false) ';
            $where_private = ' AND (' . ($fid ? "t.fromuser_id = {$fid} OR " : '') . 't.is_private = false)';
        	$where_deleted = ' AND (t.deleted IS NULL )';
        	$where_deleted_my = ' AND t.deleted IS NULL AND m.deleted IS NULL';
            // контролим баны
            if(!$is_ban) {
                $where_is_blocked = $where_blocked;
            }
        }
        $_group = ($group ? " $group AND " : '');
        switch ($ord) {
            case "my_all" :
            case "my_posts" :
            case "my_comments" :
            case 'favs_list':
            case 'favs_std':
                if ( $ord != 'favs_list' &&  $ord != 'favs_std' ) {
                    $sReplyTo = ( $ord != 'my_all' ) ? (($ord == 'my_posts') ? ' AND m.reply_to IS NULL' : ' AND m.reply_to IS NOT NULL') : '';
                    
                	$join_banned = $where_deleted ? 'INNER JOIN users mtu 
                	   ON mtu.uid = t.fromuser_id AND mtu.is_banned = 0::bit(1)' : '';
                    $where = 'WHERE';
                    $and = ' AND ';
                    /*if (!$where_blocked && !$_group && !$where_private && !$sReplyTo && !$where_deleted_my) {
                        $and = $where = '';
                    }*/
                    if (!$where_blocked) {
                        $and = '';
                    }
                	$sSelectQuery = "SELECT
                        ontop, users.warn, msgs.id, messages_cnt as num, post_time, msgtext, yt_link, msgs.title, close_comments, is_private, users.uid,
                        base as t, msgs.id_gr, users.uname, modified, modified_id, fromuser_id, users.usurname, users.login, users.email, users.photo, users.is_team,
                        users.is_pro as payed, users.is_pro_test as payed_test, users.role, users.is_banned, users.ban_where, msgs.thread_id, users.reg_date, users.is_chuck,
                        blogs_blocked.thread_id as is_blocked, blogs_blocked.reason, blogs_blocked.blocked_time,
                        blogs_poll.thread_id::boolean as has_poll, blogs_poll.question as poll_question, blogs_poll.closed as poll_closed, blogs_poll.multiple as poll_multiple,
						votes._cnt as poll_votes, reply_to, msgs.deleted, msgs.deluser_id, msgs.deleted_reason, fav_cnt, w.status, moderator_status, sbr_meta.completed_cnt
                    FROM (
                        SELECT m.*, t.messages_cnt, t.base, t.id_gr, t.close_comments , t.is_private, t.fav_cnt 
                        FROM blogs_themes as t
                        INNER JOIN blogs_msgs as m ON t.thread_id = m.thread_id
                        $join_deleted
                        $join_banned
                        $where {$where_blocked} $and ({$_group} m.fromuser_id = '$fid' $where_private $sReplyTo) $where_deleted_my
                        ORDER BY {$order} {$limit_str}
                    ) as msgs
                    LEFT JOIN users ON fromuser_id=uid
                    LEFT JOIN sbr_meta ON sbr_meta.user_id=uid
                    LEFT JOIN blogs_blocked ON msgs.thread_id = blogs_blocked.thread_id
                    LEFT JOIN blogs_poll ON blogs_poll.thread_id = msgs.thread_id
					LEFT JOIN (SELECT thread_id, COUNT(answer_id) AS _cnt FROM blogs_poll_votes WHERE user_id = '$fid' GROUP BY thread_id) AS votes ON votes.thread_id = msgs.thread_id
                                        LEFT JOIN blogs_themes_watch w ON w.user_id = '{$fid}' AND w.theme_id = msgs.thread_id
                    ORDER BY {$order}";
                    
                	$sCountQuery = "
                    SELECT COUNT(*) as num
                    FROM blogs_msgs m 
                    LEFT JOIN blogs_themes t ON t.thread_id = m.thread_id
                    $join_deleted
                	$join_banned
                    $where $where_blocked $and ($_group m.fromuser_id = ?i $where_private $sReplyTo) $where_deleted_my";
                }
                else {
                    if ( $sort_order == "priority" ) {
                        $order = " priority DESC"; // важности
                    } 
                    elseif ( $sort_order == "abc" ) {
                        $order = " calc_title"; // алфавиту
                    } 
                    else {
                        $order = " add_time DESC NULLS LAST"; // дате
                    }
                    $where = 'WHERE';
                    $and = ' AND ';
                    /*if (!$where_blocked && !$_group && !$where_private  && !$where_deleted) {
                        $and = $where = '';
                    }*/
                    if (!$where_blocked) {
                        $and = '';
                    }
                    $sSelectQuery = "SELECT 
                        ontop, users.warn, msgs.id, messages_cnt as num, post_time, msgtext, yt_link, msgs.calc_title, close_comments, 
                        is_private, users.uid, base as t, msgs.id_gr, users.uname, modified, modified_id, users.is_team,
                        fromuser_id, users.usurname, users.login, users.email, users.photo, users.is_pro as payed, reply_to, 
                        users.is_pro_test as payed_test, users.role, users.is_banned, users.ban_where, msgs.thread_id, users.reg_date, 
                        users.is_chuck, blogs_blocked.thread_id as is_blocked, blogs_blocked.reason, blogs_blocked.blocked_time, 
                        blogs_poll.thread_id::boolean as has_poll, blogs_poll.question as poll_question, blogs_poll.closed as poll_closed, blogs_poll.multiple as poll_multiple,
                        votes._cnt as poll_votes, msgs.priority, msgs.add_time, msgs.deleted, msgs.deluser_id, msgs.deleted_reason, fav_cnt, w.status, moderator_status, sbr_meta.completed_cnt
                    FROM ( 
                        SELECT m.*, COALESCE(NULLIF(f.title,''), m.title) as calc_title, t.messages_cnt, t.base, t.id_gr, t.close_comments , t.is_private, f.priority, f.add_time, t.fav_cnt  
                        FROM blogs_fav f 
                        INNER JOIN blogs_themes as t ON f.thread_id = t.thread_id 
                        INNER JOIN blogs_msgs as m ON t.thread_id = m.thread_id 
                        $where $where_blocked $and ({$_group}  m.reply_to IS NULL AND f.user_id = '$fid' $where_private) $where_deleted 
                        ORDER BY {$order} ".( ($ord=='favs_std') ? $limit_str : '' )." 
                    ) as msgs 
                    LEFT JOIN users ON fromuser_id=uid 
                    LEFT JOIN blogs_blocked ON msgs.thread_id = blogs_blocked.thread_id 
                    LEFT JOIN blogs_poll ON blogs_poll.thread_id = msgs.thread_id
                    LEFT JOIN sbr_meta ON sbr_meta.user_id=users.uid
					LEFT JOIN (SELECT thread_id, COUNT(answer_id) AS _cnt FROM blogs_poll_votes WHERE user_id = '$fid' GROUP BY thread_id) AS votes ON votes.thread_id = msgs.thread_id
					LEFT JOIN blogs_themes_watch w ON w.user_id = '{$fid}' AND w.theme_id = msgs.thread_id
                    ORDER BY {$order}";
                    
                    $sCountQuery = "
                    SELECT COUNT(*) as num 
                    FROM blogs_fav f 
                    INNER JOIN blogs_themes as t ON f.thread_id = t.thread_id 
                    INNER JOIN blogs_msgs as m ON t.thread_id = m.thread_id 
                    $where $where_blocked $and ({$_group}  m.reply_to IS NULL AND f.user_id = ?i $where_private) $where_deleted";
                }
                $ret   = $DB->rows( $sSelectQuery );
                $error = $DB->error;
                
                if ( $error ) {
                    $error = parse_db_error($error);
                } else {
                    for ( $i=0,$max=count($ret); $i<$max; $i++ ) {
						if ( $ret[$i]['has_poll'] == 't' ) {
							$ids[] = $ret[$i]['thread_id'];
							$idx[$ret[$i]['thread_id']] = &$ret[$i];
						}
                    }
                    
                    $this->AddAttach($ret);
                    
                    $num_treads = $DB->val($sCountQuery, $fid);
                }
				if ($ids) {
					$res = $DB->rows("SELECT * FROM blogs_poll_answers WHERE thread_id IN (".implode(',', $ids).") ORDER BY id");
                    if($res) {
    					foreach ($res as $row) {
    						$idx[$row['thread_id']]['poll'][] = $row;
    					}
                    }
				}
				
                return $ret;
                break;
            case "new" :
            case "relevant" :
            case "best" :
            case "ontop" :
            default :
                $group = ($group ? $group : "id_gr!=7 ");
                $_group = ($group? " AND $group" : '');
                if($ord=='relevant')
                    $order = "last_activity DESC";
                else if($ord=='best')
                    $order = "messages_cnt DESC," . $order;
                else {
                    if(!$get_ontops) {
                        $y_start = $year;
                        $ycnt = 0;
                        $ylcnt = 0;
                        if(!$fid) {
                            for($year,$ycnt=0; $year>=2008; $year--) {
                                $ylcnt = blogs::getThemesCount($year, $gr_num);
                                $ycnt += $ylcnt;
                                if($ycnt > $offset)
                                    break;
                            }
                        }
                            $ontops = $this->GetGroup($gr_num, $gr_name, $num_treads, 1, $error, $fid, $mod, 0, $read_only, 'ontop', $is_ban, $sort_order);
                            $ontops_cnt = $ontops ? count($ontops) : 0;
                            if($offset >= $ontops_cnt)
                                $offset -= $ontops_cnt;

                        $where_ontop_i = 'AND t.ontop = false';
                        if (!$group && !$where_is_blocked && !$where_private && !$where_deleted) {
                            $where_ontop_i = ' t.ontop = false';
                        }
                        if($year >= 2008) {
                            $y_offset = $offset - ($ycnt - $ylcnt);
                            $y_offset = intvalPgSql( (string) $y_offset);
                            $limit_str = "LIMIT $limit OFFSET $y_offset";
                        } else {
                            $sql = NULL; // прыгаем на общий запрос.
                            break;
                        }
                    } 
                    else { // берем только закрепленные.
                        $look_prev_year = $can_prevyear_ontops;
                        $where_ontop_i = 'AND t.ontop = true';
                        if (!$group && !$where_is_blocked && !$where_private && !$where_deleted) {
                            $where_ontop_i = ' t.ontop = true';
                        }
                    }
                }
                $where = 'WHERE';
                if (!$where_blocked && !$group && !$where_private  && !$where_deleted && !$where_ontop_i) {
                    $where = '';
                }
                if (!$where_is_blocked) {
                    $_group = $group;
                }
                $sql = "
                  SELECT ontop, users.warn, msgs.id, messages_cnt as num, last_activity, post_time, msgtext, yt_link, msgs.title, is_private, close_comments, users.uid,
                         base as t, msgs.id_gr, users.uname, modified, modified_id, fromuser_id, users.usurname, users.login, users.email, users.photo, users.is_team,
                         users.is_pro as payed, users.is_pro_test as payed_test, users.role, users.is_banned, users.ban_where, msgs.thread_id, users.reg_date, users.is_chuck,
                         blogs_blocked.thread_id as is_blocked, blogs_blocked.reason, blogs_blocked.blocked_time, msgs.deleted_reason,
						 blogs_poll.thread_id::boolean as has_poll, blogs_poll.question as poll_question, blogs_poll.closed as poll_closed, blogs_poll.multiple as poll_multiple,
						 votes._cnt as poll_votes, msgs.deleted, msgs.deluser_id, fav_cnt, w.status, sbr_meta.completed_cnt, moderator_status $sel_blocked
                    FROM (
                       -- Внимание! Данный подзапрос заточен под индексы blogs_themes (см. замечание в описании функции).
                       SELECT m.*, t.close_comments, t.is_private, t.messages_cnt, t.last_activity, t.base, t.id_gr, t.fav_cnt 
                        FROM (
                          SELECT t.* FROM blogs_themes as t
                          $where /*where_is_blocked*/{$where_is_blocked} /*group:*/{$_group} /*where_private*/{$where_private} /*where_deleted:*/{$where_deleted} /*where_ontop_i*/{$where_ontop_i}
                          ORDER BY {$order} {$limit_str}
                        ) as t
                       INNER JOIN
                         blogs_msgs" . (!$look_prev_year ? "_{$year}" : '') . " m
                           ON t.thread_id = m.thread_id AND m.reply_to IS NULL
                    ) as msgs
					LEFT JOIN users ON fromuser_id=users.uid
					LEFT JOIN sbr_meta ON sbr_meta.user_id=users.uid
					LEFT JOIN blogs_blocked ON msgs.thread_id = blogs_blocked.thread_id
					LEFT JOIN blogs_poll ON blogs_poll.thread_id = msgs.thread_id
					LEFT JOIN (SELECT thread_id, COUNT(answer_id) AS _cnt FROM blogs_poll_votes WHERE user_id = '$fid' GROUP BY thread_id) AS votes ON votes.thread_id = msgs.thread_id
					LEFT JOIN blogs_themes_watch" . (!$look_prev_year ? "_{$year}" : '') . " w ON w.user_id = '{$fid}' AND w.theme_id = msgs.thread_id
                    {$join_blocked}
                    ORDER BY {$order}
				";
                $ret = $DB->rows($sql);
                $error = $DB->error;
                if($error || ((!$ret || count($ret) < $limit) && !$get_ontops)) {
                    $sql = '';
                }
                break;
        }
        if (!$sql) {
			$offset = intvalPgSql( (string) $offset );
            $where = 'WHERE';
            if (!$where_blocked && !$group && !$where_private  && !$where_deleted && !$where_ontop_i) {
                $where = '';
            }
            $sql = "
              SELECT ontop, users.warn, msgs.id, messages_cnt as num, last_activity, post_time, msgtext, yt_link, msgs.title, is_private, close_comments, users.uid,
                     base as t, msgs.id_gr, users.uname, modified, modified_id, fromuser_id, users.usurname, users.login, users.email, users.photo, users.is_team,
                     users.is_pro as payed, users.is_pro_test as payed_test, users.role, users.is_banned, users.ban_where, msgs.thread_id, users.reg_date, users.is_chuck,
                     blogs_blocked.thread_id as is_blocked, blogs_blocked.reason, blogs_blocked.blocked_time,
					 blogs_poll.thread_id::boolean as has_poll, blogs_poll.question as poll_question, blogs_poll.closed as poll_closed, blogs_poll.multiple as poll_multiple,
					 votes._cnt as poll_votes, msgs.deleted, msgs.deluser_id, msgs.deleted_reason, sbr_meta.completed_cnt, fav_cnt, w.status, moderator_status $sel_blocked
                FROM (
                  SELECT m.*, t.close_comments, t.is_private, t.messages_cnt, t.last_activity, t.base, t.id_gr, t.fav_cnt 
                   FROM (
                     -- Внимание! Данный подзапрос заточен под индексы blogs_themes (см. замечание в описании функции).
                     SELECT t.* FROM blogs_themes as t
                     $where {$where_is_blocked} {$_group} {$where_private} {$where_deleted} {$where_ontop_i}
                     ORDER BY {$order}
                     LIMIT $limit OFFSET $offset
                   ) as t
                  INNER JOIN blogs_msgs as m ON t.thread_id = m.thread_id AND m.reply_to IS NULL
                ) as msgs
              LEFT JOIN users ON fromuser_id=uid
              LEFT JOIN blogs_blocked ON msgs.thread_id = blogs_blocked.thread_id
			  LEFT JOIN blogs_poll ON blogs_poll.thread_id = msgs.thread_id
			  LEFT JOIN sbr_meta ON sbr_meta.user_id = users.uid
	  	      LEFT JOIN (SELECT thread_id, COUNT(answer_id) AS _cnt FROM blogs_poll_votes WHERE user_id = '$fid' GROUP BY thread_id) AS votes ON votes.thread_id = msgs.thread_id
              LEFT JOIN blogs_themes_watch w ON w.user_id = '{$fid}' AND w.theme_id = msgs.thread_id
              $join_blocked
               ORDER BY $order
            ";
			$ret = $DB->rows($sql);
            $error = $DB->error;
            if($error) {
                $error = parse_db_error($error);
            }
        }
        
        if(!$error && $ret && !$num_treads) {
            for ($i = 0, $max = count($ret); $i < $max; $i++) {
                if (!$is_moder && $ret[$i]['is_blocked'] && $fid && $ret[$i]['fromuser_id'] != $fid)
                    unset($ret[$i]);
                if ($ret[$i]['has_poll'] == 't') {
                    $ids[] = $ret[$i]['thread_id'];
                    $idx[$ret[$i]['thread_id']] = &$ret[$i];
                }
            }
            if ($ret)
                $ret = array_values($ret);
            else
                $ret = array();
            $this->AddAttach($ret);
            if(!$get_ontops) {
                // Из $where_private специально убрана проверка по $fid!
                // Иначе оно слишком затратно, т.к. не дает кэшировать этот запрос для всех юзеров сразу
                // при его довольно малой значимости.
                // То же с $where_is_blocked (убираем проверку fromuser_id).
                // Если в связи с этим будут выписаны баги в мантисе и т.п., нужно обсудить, прежде чем втыкать его обратно.
                if ($where_private) {
                    $where_private = ' AND t.is_private = false';
                }
                if ($where_is_blocked) {
                    $where_is_blocked = 't.is_blocked = false ';
                }
                $where_on_top = " AND t.ontop = false ";
                if (!$where_is_blocked && !$group && !$where_deleted && !$where_private) {
                    $where_on_top = " t.ontop = false ";
                }
                $where = 'WHERE';
                if (!$where_is_blocked && !$group && !$where_deleted && !$where_private && !$where_on_top) {
                    $where = '';
                }
                $sql = "SELECT COUNT(1) FROM blogs_themes t 
                    $where $where_is_blocked $_group $where_deleted $where_private $where_on_top";
                $num_treads = $DB->cache(1800)->val($sql);
            }
        }
        
        if ($ids) {
			$res = $DB->rows("SELECT * FROM blogs_poll_answers WHERE thread_id IN (".implode(',', $ids).") ORDER BY id");
			if($res) {
    			foreach ($res as $row) {
    				$idx[$row['thread_id']]['poll'][] = $row;
    			}
            }
		}
		
		if($ontops) { 
		    if($page == 1) {
    		    foreach($ontops as $ot)
    		        array_unshift($ret, $ot);
		    }
		    $c = count($ret);
		    while($c-- > $limit) array_pop($ret);		        
		}
		return $ret;
    }
    
    /**
     * Количество тем за определенный год
     * 
     * @param  int $year год
     * @return int
     */
    function getThemesCount($year, $gr_num = 0) {
        global $DB;
        $gr_cond = ( !$gr_num ? 'id_gr <> 7' : 'id_gr = '.(int)$gr_num );
        $sql = "
            SELECT COUNT(1) FROM blogs_themes
            WHERE date_trunc('year', post_time) = '{$year}-01-01'
              AND deleted IS NULL
              AND is_private = false
              AND ontop = false
              AND {$gr_cond}
        ";
        return $DB->cache(1800)->val($sql);
    }

    /**
     * Возвращает заблокированные группы
     *
     * @param integer $num_threads	возвращает кол-во групп
     * @param string  $error		возвращает сообщение об ошибке
     * @param integer $page			номер страницы
     * @param string  $sort			тип сортировки
     * @param string  $search       строка для поиска
     * @param int     $admin        uid админа, блоги которого нужно смотреть
     * @return array				[[массив с темами(группами)]]
     */
    function GetBannedThreads(&$nums, &$error, $page, $sort, $search = '', $admin = 0) {
        global $DB;
        $limit = $GLOBALS['blogspp'];
        $offset = $limit * ($page - 1);
        $limit_into = false;
        $count_cache = false;
        // сортировка
        if ($search) {
            switch ($sort) {
                case 'btime' :
                    $order = "ORDER BY blocked_time DESC";
                    break;
                case 'login' :
                    $order = "ORDER BY login";
                    break;
                default :
                    $order = "ORDER BY relevant DESC";
                    break;
            }
        } else {
            switch ($sort) {
                case 'btime' :
                    $order = "ORDER BY blogs_blocked.blocked_time DESC";
                    $limit_into = true;
                    break;
                case 'login' :
                    $order = "ORDER BY users.login";
                    break;
                default :
                    $order = "ORDER BY blogs_blocked.thread_id DESC";
                    $limit_into = true;
                    break;
            }
        }
        $select = "
                SELECT
                    blogs_msgs.*,
                    blogs_blocked.reason, blogs_blocked.blocked_time, blogs_blocked.admin,
                    blogs_themes.messages_cnt, blogs_themes.base, blogs_themes.id_gr,
                    users.uid, users.login, users.uname, users.usurname, users.photo, users.role, users.email, users.is_team,
					users.is_pro as payed, users.is_pro_test as payed_test, users.is_banned, users.ban_where, users.is_chuck, users.warn
		";
		$from = "
                FROM
                    blogs_msgs
                INNER JOIN
                    blogs_themes ON blogs_themes.thread_id = blogs_msgs.thread_id
                INNER JOIN
                    users ON blogs_msgs.fromuser_id = users.uid
                INNER JOIN
                    blogs_blocked ON blogs_msgs.thread_id = blogs_blocked.thread_id
		";
		$where = "WHERE	blogs_msgs.reply_to IS NULL " . ($admin? " AND blogs_blocked.admin = '$admin'": "");

		if ($search) {
            $w = preg_split("/\\s/", $search);
            for($i = 0; $i < count($w); $i ++) {
                $s .= "(
                        CASE
                        WHEN
                            (LOWER(login) = LOWER('{$w[$i]}') OR LOWER(uname) = LOWER('{$w[$i]}') OR LOWER(usurname) = LOWER('{$w[$i]}') OR LOWER(title) = LOWER('{$w[$i]}')) THEN 2
                        WHEN
                            (LOWER(login) LIKE LOWER('%{$w[$i]}%') OR LOWER(uname) LIKE LOWER('%{$w[$i]}%') OR LOWER(usurname) LIKE LOWER('%{$w[$i]}%') OR LOWER(title) LIKE LOWER('%{$w[$i]}%')) THEN 1
                        ELSE 0
                        END
                    ) + ";
                $t .= "(LOWER(login) LIKE LOWER('%{$w[$i]}%') OR LOWER(uname) LIKE LOWER('%{$w[$i]}%') OR LOWER(usurname) LIKE LOWER('%{$w[$i]}%') OR LOWER(title) LIKE LOWER('%{$w[$i]}%')) OR ";
            }
            $s = substr($s, 0, strlen($s) - 3);
            $t = substr($t, 0, strlen($t) - 4);
			$select .= ", ($s) AS relevant";
			$where .= " AND $t";
        } else {
            $count_cache = true;
        }
		$csql = "SELECT COUNT(*) $from $where";
		$sql = "
			SELECT
				p.*, users.uid as admin_uid, users.login as admin_login, users.uname as admin_name, users.usurname as admin_uname
			FROM (
				$select $from $where $order LIMIT $limit OFFSET $offset
			) p
			INNER JOIN
				users ON users.uid = p.admin
		";
		//echo "<pre>$sql</pre>";
		$rows = $DB->rows($sql);
		$this->AddAttach($rows);
        if (FALSE && $count_cache) {
            $memBuff = new memBuff();
            $row  = $memBuff->getSql($error, $csql, 180);
            $nums = (int) $row[0]['cnt'];
        } else {
            $nums = $DB->val($csql);
        }
        
        return $rows;
    }

    /**
     * Возвращает кол-во заблокированных Тем(групп)
     *
     * @return integer Количество заблокированных
     */
    function NumsBlockedThreads() {
        global $DB;
        $sql = "SELECT COUNT(*) AS cnt FROM blogs_blocked JOIN blogs_msgs ON blogs_msgs.thread_id = blogs_blocked.thread_id AND blogs_msgs.reply_to IS NULL";
        $memBuff = new memBuff();
        $row = $memBuff->getSql($error, $sql, 180);
        return (int) $row[0]['cnt'];
    }

    /**
     * Получить список тем для "Ленты" (личные блоги)
     *
     * @param integer $fid				ID пользователя, блог которого нужно показать
     * @param integer $page				номер страницы, которую надо отобразить
     * @param integer $num_treads		возвращает общее число сообщений в "Ленте"
     * @param string $error				возвращает сообщение об ошибке
     * @param integer $mod				имеет ли текущий юзер права на просмотр данного раздела [1 - да, 0 - нет] (нахрена это тут?)
     * @return array					[[информация о сообщении]]
     */
    function GetMsgs($fid, $page, &$num_treads, &$error, $mod = 1) {
        global $DB;
        $limit = $GLOBALS['blogspp'];
        $msg_offset = $limit * ($page - 1);
        // смотрим баны
        if ($fid == $_SESSION['uid']) {
            $role = $_SESSION['role'];
        } else {
            if ($fid) {
                $users = new users();
                $role = $users->GetField($fid, $error, 'role');
            } else {
                $role = 0;
            }
        }
        if (bindec(substr($_SESSION['role'], 0, 5)) & bindec('01011')) {
            $sel_blocked  = ",
                blogs_blocked.thread_id as is_blocked, blogs_blocked.reason, blogs_blocked.blocked_time,
                moderators.login as moder_login, moderators.uname AS moder_name, moderators.usurname as moder_uname
            ";
            $join_blocked = " LEFT JOIN users AS moderators ON blogs_blocked.admin = moderators.uid ";
        } else if ($fid == $_SESSION['uid']) {
            $sel_blocked  = ", blogs_blocked.thread_id as is_blocked, blogs_blocked.reason, blogs_blocked.blocked_time";
        } else {
            $where_blocked = " AND blogs_blocked.thread_id IS NULL";
        }

        //приватные записи
        if (hasPermissions('blogs') || $fid == get_uid(false))  $where_private = "";
        else $where_private = " AND (is_private IS NOT true) ";

        if(hasPermissions('blogs')) {
            $where_deleted = "";
        } else {
            $where_deleted = " AND blogs_msgs.deleted IS NULL ";
        }

        $sql = "
                SELECT blogs_msgs.id, blogs_msgs.fromuser_id, blogs_msgs.deleted, blogs_msgs.deluser_id, blogs_msgs.post_time, msgtext, yt_link, title, is_private, close_comments, blogs_msgs.thread_id, id_gr, base, messages_cnt,
				blogs_poll.thread_id::boolean as has_poll, blogs_poll.question as poll_question, blogs_poll.closed as poll_closed, blogs_poll.multiple as poll_multiple, votes._cnt as poll_votes, blogs_themes.fav_cnt, moderator_status $sel_blocked
                FROM blogs_msgs
                INNER JOIN blogs_themes ON blogs_themes.thread_id = blogs_msgs.thread_id
                LEFT JOIN blogs_blocked ON blogs_blocked.thread_id = blogs_msgs.thread_id
                $join_blocked
				LEFT JOIN blogs_poll ON blogs_poll.thread_id = blogs_msgs.thread_id
				-- LEFT JOIN blogs_poll_votes ON blogs_poll_votes.thread_id = blogs_msgs.thread_id AND blogs_poll_votes.user_id = '".intval($_SESSION['uid'])."'
				LEFT JOIN (SELECT thread_id, COUNT(answer_id) AS _cnt FROM blogs_poll_votes WHERE user_id = '".intval($_SESSION['uid'])."' GROUP BY thread_id) AS votes ON votes.thread_id = blogs_msgs.thread_id
                WHERE (blogs_msgs.fromuser_id='$fid' AND {$ban_sql['where']} reply_to IS NULL AND id_gr = 7 $where_blocked {$where_private} {$where_deleted})
                ORDER BY blogs_msgs.post_time DESC
                LIMIT $limit OFFSET $msg_offset
        ";


        $ret = $DB->rows($sql);
        $error = $DB->error;
        if ($error)
            $error = parse_db_error($error);
        else {
            for ($i=0,$max=count($ret); $i<$max; $i++) {
				if ($ret[$i]['has_poll'] == 't') {
					$ids[] = $ret[$i]['thread_id'];
					$idx[$ret[$i]['thread_id']] = &$ret[$i];
				}
            }
			// опросы
			if ($ids) {
				$res = $DB->rows("SELECT * FROM blogs_poll_answers WHERE thread_id IN (".implode(',', $ids).") ORDER BY id");
                if($res) {
    				foreach ($res as $row) {
    					$idx[$row['thread_id']]['poll'][] = $row;
    				}
                }
			}
            self::AddAttach($ret);
            $sql = "
                    SELECT COUNT(*) as num
                    FROM blogs_msgs
                    LEFT JOIN blogs_themes ON blogs_themes.thread_id = blogs_msgs.thread_id
                    " . (($fid != $_SESSION['uid'])? "LEFT JOIN blogs_blocked ON blogs_blocked.thread_id = blogs_msgs.thread_id": "") . "
                    WHERE (blogs_msgs.fromuser_id='$fid' AND reply_to IS NULL AND id_gr = 7 $where_blocked {$where_private} {$where_deleted})
                ";
            $num_treads = $DB->val($sql);
        }
        return $ret;
    }

    /**
     * Инициализирует дерево сообщений в данной теме
     *
     * @param integer $thread_id		идентификатор темы
     * @param string $error				сообщение об ошибке
     * @param integer $mod				имеет ли текущий юзер права на просмотр данного раздела [1 - да, 0 - нет]
     * @param integer $fid				UID текущего юзера
     * @return array					[название раздела блогов, идентификатор раздела, идентификатор "базы"]
     */
    function GetThread($thread_id, &$error, $mod = 1, $fid = 0) {
        global $DB;
        $sql = "SELECT id_gr, base, is_private::int, close_comments::int, fav_cnt FROM blogs_themes WHERE thread_id='$thread_id'
                UNION ALL
                SELECT id_gr, base, null::int as is_private, null::int as close_comments, null as fav_cnt FROM blogs_themes_old WHERE thread_id='$thread_id'";
        $res = $DB->row($sql);
        if (!$res) {
            $error = "Группа не найдена или недоступна.";
            return 0;
        }
        $error = $DB->error;
        if ( $fid ) {
            $r = $DB->row("SELECT last_view, status FROM blogs_themes_watch WHERE user_id = ? AND theme_id = ?", $fid, $thread_id);
            if ( $r['last_view'] ) {
                $new = $DB->parse(", (? < post_time) AS new, ?i AS read_comments", $r['last_view'], (int) $r['status']);
            }
        }
        $this->id_gr = $res['id_gr'];
        $this->base = $res['base'];
        $this->is_private = $res['is_private'];
        $this->close_comments = $res['close_comments'];
        $this->fav_cnt = $res['fav_cnt'];
        $name = $this->GetGroupName($this->id_gr, $this->base, $mod);
        if (! $name) {
            $error = "Группа не найдена или недоступна.";
            return 0;
        }
        $sql = "
					SELECT
						blogs_msgs.id, deleted_reason, fromuser_id, reply_to, post_time, msgtext, yt_link, blogs_msgs.title, modified, modified_id, deluser_id, deleted,
						users.uname, users.usurname, users.login, users.photo, users.is_pro_test, users.role, users.is_chuck, users.is_team,
						users.warn, users.is_banned, users.ban_where, users.is_pro as payed, users.is_pro_test as payed_test, users.reg_date, freelancer.spec, -- p.name as prof_name,
						admins.uname AS modername, admins.usurname AS modersurname, admins.login AS moderlogin,
						blogs_poll.question as poll_question, blogs_poll.closed as poll_closed, blogs_poll.multiple as poll_multiple, sbr_meta.completed_cnt, moderator_status $new
					FROM blogs_msgs
					INNER JOIN users ON fromuser_id=users.uid
					LEFT JOIN freelancer ON fromuser_id=freelancer.uid
					LEFT JOIN users AS admins ON moderator_status = admins.uid
                    LEFT JOIN sbr_meta ON sbr_meta.user_id=fromuser_id
					-- LEFT JOIN professions p ON p.id = freelancer.spec_orig
					LEFT JOIN blogs_poll ON blogs_poll.thread_id = blogs_msgs.thread_id
					$join
					WHERE blogs_msgs.thread_id= ?i ORDER BY reply_to, post_time
				";
        $this->thread = $DB->rows($sql, $thread_id);
        $error .= $DB->error;
        if ($error)
            $error = parse_db_error($error);
        else {
            $this->msg_num = count($this->thread);
            if ($this->msg_num > 0) {
                // аттач файлов
                $this->AddAttach($this->thread);
                // заблокирован ли топик
                if ($fid && ($fid == $_SESSION['uid'])) {
                    $role = $_SESSION['role'];
                } else
                    if ($fid) {
                        $users = new users();
                        $role = $users->GetField($fid, $error, 'role');
                    } else {
                        $role = 0;
                    }
                $is_moder = hasPermissions('blogs');
                if ($is_moder) {
                    $row = $DB->row("SELECT blogs_blocked.admin, blogs_blocked.reason, blogs_blocked.blocked_time, users.login as admin_login, users.uname as admin_name, users.usurname as admin_uname FROM blogs_blocked JOIN users ON blogs_blocked.admin = users.uid WHERE thread_id = ?i", $thread_id);
                } else {
                    $row = $DB->row("SELECT admin, reason, blocked_time FROM blogs_blocked WHERE thread_id = ?i", $thread_id);
                }
                if ($row) {
                    $this->is_blocked = 1;
                    $this->thread[$this->msg_num - 1] = array_merge($this->thread[$this->msg_num - 1], $row);
                } else {
                    $this->is_blocked = 0;
                }
				if ($this->thread[$this->msg_num - 1]['poll_question']) {
					$r = $DB->rows("SELECT * FROM blogs_poll_answers WHERE thread_id = '$thread_id' ORDER BY id", $thread_id);
					$this->thread[$this->msg_num - 1]['poll'] = $r;
				}
                // читаем тред
                $this->SetVars($this->msg_num - 1);
                if ($mod) {
                    require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
                    $user = new users();
                    $user->GetUser($this->login);
                    if ($user->is_banned && $user->ban_where <= 1) {
                        $error = "Тред не найден или недоступен.";
                        return 0;
                    }
                }
                // доступ
                if ($this->is_blocked && ! (($fid && $fid == $this->fromuser_id) || $is_moder)) {
                    $error = "Тред заблокирован администрацией";
                    return 0;
                }
            }
        }
        return array($name, $this->id_gr, $this->base);
    }

    /**
     * Возвращает следующее сообщение для вывода (через члены класса)
     *
     * @return integer		идентификатор родительского сообщения
     */
    function GetNext() {
        $ind = $this->SearchFirstChild($this->id);
        $i = 0; // на всякий случай
        while ($ind == - 1 && $this->thread[$this->last_inx]['reply_to'] != 0) {
            $last = $this->thread[$this->last_inx]['reply_to'];
            $this->thread[$this->last_inx]['reply_to'] = - 1;
            //print_r($this->thread);
            $ind = $this->SearchFirstChild($last);
            $this->last_inx = $this->GetInxById($last);
            $this->level --;
            //if ($i++ > 100) die("Ошибка! сообщите разработчикам!");
        }
        $this->level ++;
        $this->SetVars($ind);
        $this->last_inx = $ind;
        $this->reply = $this->thread[$this->last_inx]['reply_to'];
        $this->deleted_reason = $this->thread[$this->last_inx]['deleted_reason'];
        return $this->thread[$this->last_inx]['reply_to'];
    }

    /**
     * Возвращает индекс первого сообщения в массиве тем, являющегося комментарием к данному
     *
     * @param integer $id		идентификатор сообщения
     * @return integer			индекс первого сообщения в массиве тем, являющегося комментарием к данному
     */

    function SearchFirstChild($id) {
        $ret = - 1;
        $i = 0;
        foreach ($this->thread as $ikey => $node) {
            if ($node['reply_to'] == $id) {
                $ret = $i;
                break;
            } else
                $i ++;
        }
        //print $id." : ".$ret;
        return ($ret);
    }

    /**
     * Возвращает индекс последнего сообщения в массиве тем, являющегося комментарием к данному
     *
     * @param integer $id		идентификатор сообщения
     * @return integer			индекс последнего сообщения в массиве тем, являющегося комментарием к данному
     */
    function SearchLastChildId($id) {
        $count = 0;
        $tmpid = $id;
        foreach ($this->thread as $ikey => $node) {
            if ($node['reply_to'] == $tmpid) {
                $id = $node['id'];
                $count ++;
            }
        }
        //print $id." : ";
        if ($count) {
            $id = $this->SearchLastChildId($id);
        }
        return ($id);
    }

    /**
     * Да все с ней ясно - рекурсивно обходит дерево и вычисляет окончание его веток
     *
     * @param integer $id        ИД сообещния
     * @param array   $threearr  Возвращает массив идентификаторов сообщений
     * @param integer $cc	     Количество сообщений
     * @return boolean	true
     */
    function GetThreeId($id, &$threearr, $cc = 0) {
        $counter = 0;
        $from = 0;
        if ($cc) {
            $from = $cc;
            $cc = count($threearr);
        } else {
            $threearr[] = $id;
            $cc = 1;
        }
        for($i = $from; $i < count($threearr); $i ++) {
            foreach ($this->thread as $ikey => $node) {
                if ($node['reply_to'] == $threearr[$i]) {
                    $threearr[] = $node['id'];
                    $counter ++;
                }
            }
        }
        if ($counter) {
            $this->GetThreeId($id, $threearr, $cc);
        }
        return true;
    }

    /**
     * Возвращает индекс сообщения в массиве треда по идентификатору сообщения
     *
     * @param integer $id		идентификатор сообщения
     * @return integer			индекс сообщения в массиве треда
     */
    function GetInxById($id) {
        $ret = 0;
        foreach ($this->thread as $ikey => $node) {
            if ($node['id'] == $id)
                break;
            else
                $ret ++;
        }
        if ($ret > $this->msg_num - 1)
            $ret = - 1;
        return ($ret);
    }

    /**
     * Инициализирует члены класса в соответствии с текущим индексом сообщения в массиве тем
     *
     * @param integer $idx	индекс сообщения в массиве тем
     */
    function SetVars($idx) {
        $node = $this->thread[$idx];
        $this->id = $node['id'];
        $this->fromuser_id = $node['fromuser_id'];
        $this->post_time = $node['post_time'];
        $this->msgtext = $node['msgtext'];
        $this->attach = $node['attach'];
        $this->yt_link = $node['yt_link'];
        $this->title = $node['title'];
        $this->uname = $node['uname'];
        $this->usurname = $node['usurname'];
        $this->is_chuck = $node['is_chuck'];
        $this->login = $node['login'];
        $this->photo = $node['photo'];
        $this->is_pro_test = $node['is_pro_test'];
        $this->modified = $node['modified'];
        $this->modified_id = $node['modified_id'];
        $this->deleted = $node['deleted'];
        $this->deluser_id = $node['deluser_id'];
        $this->small = $node['small'];
        $this->payed = $node['payed'];
        $this->payed_test = $node['payed_test'];
        $this->new = $node['new'];
        $this->warn = $node['warn'];
        $this->is_banned = $node['is_banned'];
        $this->ban_where = $node['ban_where'];
        $this->admin = $node['admin'];
        $this->reason = $node['reason'];
        $this->blocked_time = $node['blocked_time'];
        $this->admin_login = $node['admin_login'];
        $this->admin_name = $node['admin_name'];
        $this->admin_uname = $node['admin_uname'];
        $this->cnt_role = (substr($node['role'], 0, 1) == '0') ? "fd6c30" : "6db335";
        $this->role = substr($node['role'], 0, 1) ? 'emp' : 'frl';
        $this->spec = $node['spec'];
        //$this->prof_name = $node['prof_name'];
        $this->reg_date = $node['reg_date'];
		$this->poll_question = $node['poll_question'];
		$this->poll_closed = $node['poll_closed'];
		$this->poll_multiple = $node['poll_multiple'];
		$this->poll = $node['poll'];
		$this->is_team = $node['is_team'];
		$this->moderator_status = $node['moderator_status'];
        $this->completed_cnt = $node['completed_cnt'];
    }

    /**
     * Добавить _комментарий_ в блоги (для новой темы - см. NewThread)
     *
     * @param integer $fid		ID Пользователя
     * @param integer $reply	идентификатор родительского сообщения
     * @param integer $thread   идентификатор
     * @param string  $msg		текст сообщения
     * @param string  $name		название сообщения
     * @param string  $attach	имя присоединенного файла или массив присоединенных файлов
     * @param string  $ip		ip, с которого запостили
     * @param string  $error	возвращает сообщение об ошибке
     * @param integer $small	1 - присоединенный файл является маленькой картинкой, 2 - большой картинкой (есть превью), 0 - другое (либо массив, если файлов несколько)
     * @param string  $yt_link	сылка на YouTube видео
     * @return integer			возвращает id нового сообщения
     */
    function Add($fid, $reply, $thread, $msg, $name, $attach, $ip, &$error, $small, $yt_link = '') {
        global $DB;
		$sql =
		"SELECT t.thread_id as thread_id, t.close_comments as close_comments, t.is_private as is_private, b.fromuser_id as fromuser_id, blogs_blocked.thread_id as bthread_id
		   FROM blogs_msgs b
		 INNER JOIN
		   blogs_themes t
		     ON t.thread_id = b.thread_id
         LEFT JOIN blogs_blocked ON t.thread_id = blogs_blocked.thread_id
		  WHERE b.thread_id = ?i
		    AND b.reply_to IS NULL AND b.deleted IS NULL";
        $res = $DB->row($sql, $thread);
        if(!$res)
             return NULL;
        $thread_id = $res['thread_id'];
        $close_comments = $res['close_comments'];
        $is_private = $res['is_private'];
        $theme_author = $res['fromuser_id'];
        $thread_blocked_id = $res['bthread_id'];
        if(!hasPermissions('blogs') // расчитано на то, что $fid -- это всегда $_SESSION['uid']
           && ($close_comments=='t' || $is_private=='t')
           && $theme_author != $fid
           || $thread_blocked_id)
        {
            $error = 403;
            return NULL;
        }
        
        if ($attach) {
            $error = $this->UploadFiles($attach, array('width' => 470, 'height' => 1000, 'less' => 0));
        }
        
        if (!$error) {
            $blogs_msgs = 'blogs_msgs_' . date('Y');
            //$sModVal = is_pro() ? 'NULL' : '0';
            $sModVal = '0';
            $sql = "INSERT INTO {$blogs_msgs} (fromuser_id, reply_to, from_ip, post_time, thread_id, msgtext, title, yt_link, moderator_status)
                    VALUES (?i, ?i, ?, NOW(), ?i, ?, ?, ?, $sModVal) RETURNING id;";
            $id = $DB->val($sql, $fid, $reply, $ip, $thread_id, $msg, $name, $yt_link);
            $error = $DB->error;
            
            /*if ( $id ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                $DB->insert( 'moderation', array('rec_id' => $id, 'rec_type' => user_content::MODER_BLOGS) );
            }*/
            
            if ($id && $attach) {
                foreach($attach as $att) {
                    $att->updateFileParams(array('src_id'=>$id, 'small'=>$att->small), false);
                }
            }
        }
        return $id;
    }

    /**
     * Редактирует собщение
     *
     * @param integer $fid		 	  ID Пользователя
     * @param integer $edit_id	 	  идентификатор сообщения, которое надо редактировать
     * @param string  $msg		 	  текст сообщения
     * @param string  $name		 	  название сообщения
     * @param string  $attach	 	  имя присоединеного файла (или массив, если файлов несколько)
     * @param string  $ip		 	  ip, с которого запостили
     * @param string  $error		  возвращает сообщение об ошибке
     * @param integer $mod			  имеет ли юзер права на редактирование чужих сообщений (0 - да, 1 - нет)
     * @param array   $tags		 	  массив тегов  @deprecated
     * @param integer $group	 	  новый раздел для сообщения (имеет смысл только для тем, если не надо переносить - можно не определять этот параметр)
     * @param integer $base		 	  новая "база" для сообщения (имеет смысл только для тем, если не надо переносить - можно не определять этот параметр)
     * @param boolean $deleteattach   список id присоединенных файлов, которые необходимо удалить
     * @param boolean $olduserlogin   логин последнего юзера писавшего сообщения(если есть, иначе false)
     * @param string  $yt_link		  ссылка на YouTube видео
     * @param integer $close_comments запрет комментирования
     * @param integer $is_private	  приватность записи
     * @param integer $small          1 - присоединенный файл является маленькой картинкой, 2 - большой картинкой (есть превью), 0 - другое (либо массив, если файлов несколько)
     * @param string  $modified_reason причина редактирования
     * @return integer			идентификатор темы
     */
    function Edit($fid, $edit_id, $msg, $name, $attach, $ip, &$error, $mod = 1, $tags = "", &$group = null, $base = null, $deleteattach = false, $olduserlogin = false, $yt_link = '', $close_comments = 'f', $is_private = 'f', $ontop = null, $small = null, $poll_question = null, $poll_answers = null, $poll_answers_has = null, $poll_multiple = null, $modified_reason = '') {
        global $DB;
        $sSel  = !empty($group) ? ' blogs_themes.id_gr ' : ' 0 AS id_gr ';
        $sJoin = !empty($group) ? ' INNER JOIN blogs_themes ON blogs_themes.thread_id = blogs_msgs.thread_id ' : '';
        $sql = "
			SELECT blogs_msgs.fromuser_id, blogs_msgs.title, blogs_msgs.msgtext, blogs_msgs.yt_link, blogs_msgs.thread_id, users.login, blogs_blocked.thread_id as is_blocked, blogs_poll.question as poll, $sSel 
			FROM blogs_msgs
			$sJoin
			LEFT JOIN blogs_blocked ON blogs_msgs.thread_id = blogs_blocked.thread_id
			LEFT JOIN blogs_poll ON blogs_poll.thread_id = blogs_msgs.thread_id
			LEFT JOIN users ON users.uid=blogs_msgs.fromuser_id
			WHERE blogs_msgs.id = '$edit_id'
		";
        
        $res = $DB->query($sql, $edit_id);
        list($from_id, $old_title, $old_msgtext, $last_yt_link, $thread_id, $owner_login, $is_blocked, $poll, $prev_gr) = pg_fetch_row($res);
        if ($olduserlogin) {
            $oldlogin = $olduserlogin;
        }
        if ($from_id != $fid && $mod == 1)
            return ("Вы не можете править чужие сообщения!");
        if ($is_blocked && $mod == 1)
            return ("Изменения заблокированы");
        if ($group == 7) {
            $max_image_size = array('width' => 400, 'height' => 600, 'less' => 0);
        } else {
            $max_image_size = array('width' => 470, 'height' => 1000, 'less' => 0);
        }
        $error = $this->UploadFiles($attach, $max_image_size, $owner_login);
        if (! $error_flag) {
	        if (is_array($deleteattach) && $deleteattach) {
	            $deleteattach = array_map('intval', $deleteattach);
                $res = CFile::selectFilesById(self::FILE_TABLE, $deleteattach);
                if($res) {
    	            foreach ($res as $row) {
    	                $file = new CFile();
                        $file->Delete(0, "users/" . substr($oldlogin, 0, 2) . "/" . $oldlogin . "/upload/", $row['fname']);
    	                if ($row['small'] == 2)
                            $file->Delete(0, "users/" . substr($owner_login, 0, 2) . "/" . $owner_login . "/upload/", "sm_" . $row['fname']);
    	            }
                }
	        }
	        if (! is_null($ontop)) {
	            $ontop_sql = ", ontop = '$ontop'";
	        } else {
	            $ontop_sql = '';
	        }
	        if ($is_private == "") $is_private = 'f';
	        if ($close_comments == "") $close_comments = 'f';
            
            $sModer = '';
            $sql    = '';

            if ( $fid == $from_id && !hasPermissions('blogs') /*&& !is_pro()*/ ) {
                // автор, не админ, не про меняет заголовок либо текст - отправить на модерирование
                $sModer = ' , moderator_status = 0 ';
                /*require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                $DB->insert( 'moderation', array('rec_id' => $edit_id, 'rec_type' => user_content::MODER_BLOGS) );*/
            }
            
	        $sql .= "UPDATE blogs_msgs SET from_ip = '$ip', msgtext = '$msg', modified=NOW(), modified_id=$fid, modified_reason = '$modified_reason', title = '$name', yt_link = '$yt_link' $ontop_sql $sModer WHERE id = '$edit_id' RETURNING reply_to";
            
            $reply_to = $DB->val($sql);
	        $error = $DB->error;

            if (!$error && $attach) {
                foreach($attach as $att) {
                    $att->updateFileParams(array('src_id'=>$edit_id, 'small'=>$att->small), false);
                }
            }

	        if (!$error) {
	            $sql =  (!is_null($group) && !is_null($base)) ? "id_gr = {$group}" : '';
	            if($close_comments && $close_comments!='n') $sql .= ($sql ? ',' : ''). "close_comments = '{$close_comments}'";
	            if($is_private && $is_private!='n')     $sql .= ($sql ? ',' : ''). "is_private = '{$is_private}'";
	            if($sql) {
    	            $sql = "UPDATE blogs_themes SET {$sql} WHERE thread_id = ?i RETURNING id_gr";
    	            $group = $DB->val($sql, $thread_id);
    	            $error .= (($error) ? ' ' : '') . $DB->error;
    	        }
    	        
                // лог админских действий и отправка сообщения о редактировании топика админом или модератором
                if ($from_id != $fid && hasPermissions('blogs')) {
    		    	require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );
                    $sLink    = getFriendlyURL( 'blog', $thread_id );
                    admin_log::addLog(admin_log::OBJ_CODE_BLOG, admin_log::ACT_ID_EDIT_BLOGS, $from_id, $thread_id, $name , $sLink, 2, '', null, '');
                }
                
    	        // пишем лог админских действий: смена раздела блога
    		    if ( !empty($prev_gr) && $prev_gr <> $group && $from_id != $fid && hasPermissions('blogs')) { 
    		    	
    		    	$sPrevGr = '';
    		    	$sCurrGr = '';
    		    	$aBlogGr = self::GetThemes( $sErrorGr, 0 );
    		    	
    		    	foreach ( $aBlogGr as $aOne ) {
    		    		if ( $aOne['id'] == $prev_gr ) {
    		    			$sPrevGr = $aOne['t_name'];
    		    		}
    		    		
    		    		if ( $aOne['id'] == $group ) {
    		    			$sCurrGr = $aOne['t_name'];
    		    		}
    		    	}
    		    	
    		    	$sReason  = "Перенос блога из раздела &quot;$sPrevGr&quot; в раздел &quot;$sCurrGr&quot;";
    		    	$sReason .= $close_comments == 't' ? ' (запрет комментирования)' : '';
    		    	
    		    	admin_log::addLog( admin_log::OBJ_CODE_BLOG, admin_log::ACT_ID_BLOG_CH_GR, $from_id, $thread_id, $name, $sLink, 0, '', 0, $sReason );
    		    }
                
	        }
	        // опрос
	    if(!$error && !$GLOBALS['PDA']) {
			if ($poll_question!=='') {
                $bModeration = false;
                
				if ( $poll && (!$mod || $from_id == $fid) ) {
                    $bModeration = true;
					$data = array('question' => $poll_question);
					if ($poll_multiple !== null) {
						$data['multiple'] = (bool) $poll_multiple;
					}
					$DB->update('blogs_poll', $data, "thread_id = ?", $thread_id);
				} else if (!$poll) {
                    $bModeration = true;
					$DB->query("INSERT INTO blogs_poll (thread_id, question) VALUES(?i, ?)", $thread_id, $poll_question);
				}
				if (!is_null($poll_answers)) {
                    $aUpId = array();
                    
                    if ( (!$mod || $from_id == $fid) && is_array($poll_answers_has) ) {
                        foreach ( $poll_answers_has as $key => $answer ) {
                            if ( ($answer = substr((string) $answer, 0, 255)) !== '' ) {
                                $aUpId[] = intval( $key );
                            }
                        }
                    }
                    
					$DB->query("DELETE FROM blogs_poll_answers WHERE thread_id = '$thread_id'" . ($aUpId ? ' AND id NOT IN (' . implode(',', $aUpId) . ')' : '') );
                    
					if ( (!$mod || $from_id == $fid) && $aUpId ) {
                        $aAnswers = $DB->rows( 'SELECT id, answer FROM blogs_poll_answers WHERE id IN (?l)', $aUpId );
                        
						foreach ( $aAnswers as $aOne ) {
                            $answer = $poll_answers_has[$aOne['id']];
							if ( ($answer = substr((string) $answer, 0, 255)) !== '' && $answer != $aOne['answer'] ) {
                                $bModeration = true;
								$DB->query("UPDATE blogs_poll_answers SET answer = ? WHERE id = ?i ", $answer, intval($aOne['id']));
							}
						}
					}
					if ($poll_answers && is_array($poll_answers)) {
						$sql = '';
						foreach ($poll_answers as $answer) {
							if (($answer = substr((string) $answer, 0, 255)) !== '') $sql .= ",('$thread_id', '$answer')";
						}
						if ($sql) {
                            $bModeration = true;
							if (!( $res = $DB->query("INSERT INTO blogs_poll_answers(thread_id, answer) VALUES ".substr($sql, 1)) )) {
								$error = $DB->error;
							}
						}
					}
				}
                
                if ( !$sModer && $bModeration && $from_id == $fid && !hasPermissions('blogs') /*&& !is_pro()*/ ) {
                    // если еще не отправили на модерирование при основном обновлении
                    // появились новые вопросы/ответы и это правит автор, и он не админ и не ПРО - отправить на модерирование
                    /*require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                    $DB->query( 'UPDATE blogs_msgs SET moderator_status = 0 WHERE id = ?i', $edit_id );
                    $DB->insert( 'moderation', array('rec_id' => $edit_id, 'rec_type' => user_content::MODER_BLOGS) );*/
                }
			} else if(!$reply_to){
			  // Удаляем, только если редактируется топик.
				$DB->query("DELETE FROM blogs_poll WHERE thread_id = ?i", $thread_id);
			}
			}
        }
        
        return $thread_id;
    }

    /**
     * Создает новую тему
     *
     * @param integer $fid			  ID Пользователя
     * @param integer $group		  раздел для темы
     * @param integer $base			  "база" для темы
     * @param string  $name			  название
     * @param string  $msg			  текст
     * @param string  $attach		  массив файлов типа CFile
     * @param string  $ip			  ip, с которого запостили
     * @param integer $mod			  имеет ли юзер права на пост тем в этот раздел (0 - да, 1 - нет)
     * @param integer $small		  1 - присоединенный файл является маленькой картинкой, 2 - большой картинкой (есть превью), 0 - другое
     * @param array   $tags			  массив тегов @deprecated
     * @param string  $yt_link		  ссылка на видео в YouTube
     * @param integer $close_comments запрет комментирования
     * @param integer $is_private	  приватность записи @see self::is_private
     * @return string				  Сообщение об ошибке
     */
    function NewThread($fid, $group, $base, $name, $msg, $attach, $ip, $mod, $small = 0, $tags = "", $yt_link = "", $close_comments ='f', $is_private = 'f', $ontop = 'f', $poll_question = '', $poll_answers = '', $poll_multiple = false) {
        global $DB;
        if ($group == 7) {
            $max_image_size = array('width' => 400, 'height' => 600, 'less' => 0);
        } else {
            $max_image_size = array('width' => 470, 'height' => 1000, 'less' => 0);
        }

        $base = 0; // всегда 0, поле устарело.
        $alert = self::UploadFiles($attach, $max_image_size);
        if (!$alert) {
            if ($base == 0) {
                $sql = "SELECT read_only FROM blogs_groups WHERE id = ?i";
                $read_only = $DB->val($sql, $group);
            } else
                $read_only = 0;
            if ($read_only && $mod == 1)
                return "Вы не можете писать в этот блог";
            
            $sql = array(
                'id_gr'          => $group,
                'base'           => $base,
                'close_comments' => $close_comments,
                'is_private'     => $is_private,
            	'fromuser_id'    => $fid
            );
            if ($close_comments == 't') {
                $users = new users;
                if ($fid == $users->GetUid($uerr, 'admin')) {
                    $sql['id_gr_public'] = $group;
                }
            }
            
            $year = date("Y");
            
            $trtmp = $DB->insert('blogs_themes', $sql, "id_gr||'-'||thread_id");
            list($id_gr, $trid) = explode('-', $trtmp);
            
            //$sModVal = is_pro() ? 'NULL' : '0';
            $sModVal = '0';
            
            $msg_id = $DB->val("
                INSERT INTO blogs_msgs_{$year} 
                    (fromuser_id, reply_to, from_ip, post_time, thread_id, msgtext, title, yt_link, ontop, moderator_status)
                VALUES
                    (?, NULL, ?, NOW(), ?, ?, ?, ?, ?, $sModVal)
                RETURNING
                    id
            ", $fid, $ip, $trid, $msg, $name, $yt_link, $ontop);
            
            /*if ( $msg_id ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                $DB->insert( 'moderation', array('rec_id' => $msg_id, 'rec_type' => user_content::MODER_BLOGS) );
            }*/
            
            if ($attach) {
                foreach($attach as $att) {
                    $att->updateFileParams(array('src_id'=>$msg_id, 'small'=>$att->small), false);
                }
            }
        }
		// опрос
		if ($poll_question!=='' && is_array($poll_answers) && !empty($poll_answers)) {
			$DB->insert('blogs_poll', array(
				'thread_id' => $trid,
				'question'  => $poll_question,
				'multiple'  => (bool) $poll_multiple
			));
			$data = array();
			foreach ($poll_answers as $answer) {
				$data[] = array('thread_id' => $trid, 'answer' => $answer);
			}
			$DB->insert('blogs_poll_answers', $data);
		}
		return array($alert, $error_flag, $error, $msg_id, $trid, $id_gr);
    }
    
    /**
     * Отправить сообщение на модерирование
     * 
     * @param int $msg_id ID сообщения
     * @param int $stop_words_cnt Подсчитанное количество стоп-слов в записи
     * @param int $sort_order 0 - пост, 1 - камент. для сортировки в потоках
     */
    function insertIntoModeration( $msg_id = 0, $stop_words_cnt = 0, $sort_order = 0 ) {
        /*if ( $msg_id ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            $GLOBALS['DB']->insert( 'moderation', array('rec_id' => $msg_id, 'rec_type' => user_content::MODER_BLOGS, 'stop_words_cnt' => $stop_words_cnt, 'sort_order' => $sort_order) );
        }*/
    }

	/**
	 * Пометить тему блога удаленной
	 *
	 * @param integer $fid       ID Пользователя
	 * @param integer $id        ИД блога
	 * @param integer $group     раздел для тем
	 * @param integer $base      "база" для темы
	 * @param integer $thread_id идентификатор сообщения
	 * @param integer $page      Страница
	 * @param string  $msg       Текст сообщения
	 * @param integer $mod       Проверка прав на удаление
	 * @param string  $reason    Причина удаления
	 * @return string Сообщение об ошибке
	 */
    function MarkDeleteBlog($fid, $id, &$group, &$base, &$thread_id, &$page, &$msg, $mod = 1, $reason = '') {
        global $DB;
        $fid = intval($fid);
        $id  = intval($id);
        $sql = "SELECT id_gr, base, blogs_msgs.fromuser_id, blogs_msgs.thread_id, reply_to, blogs_blocked.thread_id as is_blocked FROM blogs_themes LEFT JOIN blogs_msgs ON blogs_msgs.thread_id=blogs_themes.thread_id LEFT JOIN blogs_blocked ON blogs_themes.thread_id = blogs_blocked.thread_id WHERE blogs_msgs.id=?i";
        $res = $DB->query($sql, $id);
        list($group, $base, $from_id, $thread_id, $reply, $is_blocked) = pg_fetch_row($res);
        if ($fid != $from_id && $mod == 1)
            return 'Вы не можете удалять чужие сообщения';
        if ($is_blocked && $mod == 1)
            return 'Вы не можете удалять заблокированные треды';
        $msg = $thread_id;
        
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        $userSubscribe = $this->getUsersSubscribe(array($id));
        $sModer = ' , moderator_status = '. ( $from_id != $fid ? $fid : 'NULL' ) .' ';
        $sql    = $DB->parse( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i;', $id, user_content::MODER_BLOGS );
        
        if ($from_id != $fid) {
            $addit = "(id = '$id' AND reply_to = (SELECT id FROM blogs_msgs WHERE id = '$reply' AND fromuser_id='$fid')) OR (id = '$id' AND thread_id = (SELECT id FROM blogs_msgs WHERE thread_id = '$thread_id' AND fromuser_id='$fid' AND reply_to IS NULL))";
        }
        else {
            $addit = "id = '$id' AND fromuser_id = '$fid'";
        }
        
        if (! $mod)
            $addit = "id = '$id'";
        
        $sql .= "UPDATE blogs_msgs SET deleted=NOW(), deluser_id=?i, deleted_reason = ? $sModer WHERE ($addit)";

        $res = $DB->query($sql, $fid, $reason);
        $DB->query("DELETE FROM draft_blogs WHERE post_id = ?", $id);
        $error = $DB->error;
        if ($error) {
            $group = - 1;
        } else {
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/smail.php";
            $s = new smail();
            $s->sendBlogPostDeleted( get_uid(false), $userSubscribe );
        }
        return $error;
    }


	/**
	 * Полностью удалить сообщение
	 *
	 * @param integer $fid       ID Пользователя
	 * @param integer $id        ИД блога
	 * @param integer $group     раздел для тем
	 * @param integer $base      "база" для темы
	 * @param integer $thread_id идентификатор сообщения
	 * @param integer $page      Страница
	 * @param string  $msg       Текст сообщения
	 * @param integer $mod       Проверка прав на удаление
	 * @return string Сообщение об ошибке
	 */
    function DeleteMsg($fid, $id, &$group, &$base, &$thread_id, &$page, &$msg, $mod = 1) {
        global $DB;
        $sql = "SELECT id_gr, base, blogs_msgs.fromuser_id, blogs_msgs.thread_id, reply_to, blogs_blocked.thread_id as is_blocked FROM blogs_themes LEFT JOIN blogs_msgs ON blogs_msgs.thread_id=blogs_themes.thread_id LEFT JOIN blogs_blocked ON blogs_themes.thread_id = blogs_blocked.thread_id WHERE blogs_msgs.id=?i";
        $res = $DB->query($sql, $id);
        list($group, $base, $from_id, $thread_id, $reply, $is_blocked) = pg_fetch_row($res);
        if ($fid != $from_id && $mod == 1)
            return 'Вы не можете удалять чужие сообщения';
        if ($is_blocked && $mod == 1)
            return 'Вы не можете удалять заблокированные треды';
        // список файлов
        $files = CFile::selectFilesById(self::FILE_TABLE, $id);
        //$page = 1;
        $msg = $thread_id;
        if ($from_id != $fid)
            $addit = "(id = '$id' AND reply_to = (SELECT id FROM blogs_msgs WHERE id = '$reply' AND fromuser_id='$fid')) OR (id = '$id' AND thread_id = (SELECT id FROM blogs_msgs WHERE thread_id = '$thread_id' AND fromuser_id='$fid' AND reply_to IS NULL))";
        else
            $addit = "id = '$id' AND fromuser_id = '$fid'";
        if (! $mod)
            $addit = "id = '$id'";

        $sql = "DELETE FROM blogs_msgs WHERE ($addit)";
        $res = $DB->query($sql);
        $error = $DB->error;
        // удаляем файлы

        if ($files) {
            foreach ($files as $attach) {
                $file->Delete(0, "users/" . substr($dir, 0, 2) . "/" . $dir . "/upload/", $attach['fname']);
                if ($attach['small'] == 2)
                    $file->Delete(0, "users/" . substr($dir, 0, 2) . "/" . $dir . "/upload/", "sm_" . $attach['fname']);
            }
        }

        if ($error)
            $group = - 1;
        return $error;
    }

	/**
	 * Восстановить сообщение
	 *
	 * @param integer $fid       ID Пользователя
	 * @param integer $id        ИД блога
	 * @param integer $group     раздел для тем
	 * @param integer $base      "база" для темы
	 * @param integer $thread_id идентификатор сообщения
	 * @param integer $page      Страница
	 * @param string  $msg       Текст сообщения
	 * @param integer $mod       Проверка прав на удаление
	 * @return string Сообщение об ошибке
	 */
    function RestoreMsg($fid, $id, &$group, &$base, &$thread_id, &$page, &$msg, $mod = 1) {
        global $DB;
        $sql = "SELECT id_gr, base, msgtext, title, blogs_msgs.fromuser_id, blogs_msgs.thread_id, reply_to, blogs_blocked.thread_id as is_blocked, blogs_msgs.deluser_id FROM blogs_themes LEFT JOIN blogs_msgs ON blogs_msgs.thread_id=blogs_themes.thread_id LEFT JOIN blogs_blocked ON blogs_themes.thread_id = blogs_blocked.thread_id WHERE blogs_msgs.id=?i";
        $res = $DB->query($sql, $id);
        list($group, $base, $from_id, $thread_id, $reply, $is_blocked, $deluser_id) = pg_fetch_row($res);
        if (!($fid == $deluser_id || hasPermissions('blogs'))) return 'Вы не можете восстановить сообщение';

        $addit = "id = '$id'";
        
        $sModer = '';
        
        if ( $fid == $from_id && !hasPermissions('blogs') /*&& !is_pro()*/ ) {
            /*require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
            
            $sModer        = ' , moderator_status = 0 ';
            $stop_words    = new stop_words( hasPermissions('blogs') );
            $nStopWordsCnt = $stop_words->calculate( $res['msgtext'], $res['title'] );
            $nSortOrder    = $res['reply_to'] ? 1 : 0;
            
            $DB->insert( 'moderation', array('rec_id' => $id, 'rec_type' => user_content::MODER_BLOGS, 'stop_words_cnt' => $nStopWordsCnt, 'sort_order' => $nSortOrder) );*/
        }
        
        $sql = "UPDATE blogs_msgs SET deleted=NULL, deluser_id=NULL, deleted_reason = NULL $sModer WHERE ($addit)";
        $res = $DB->query($sql);
        $error = $DB->error;
        if ($error) {
            $group = - 1;
        }
        return $error;
    }

	/**
	 * Отметить удаленный блог, что он удален, для дальнейшего модерирования)
	 *
	 * @param integer $fid     ID Пользователя
	 * @param integer $edit_id ИД Сообщения
	 * @param string  $ip      ИП того кто удаляет
	 * @param mixed   $error   Сообщение об ошибке
	 * @param boolean $mod     Имеет ли юзер права на удаление
     * @param string  $reason    Причина удаления
	 * @return integer $thread_id ИД удаленного сообщения
	 */
    function MarkDeleteMsg($fid, $edit_id, $ip, &$error, $mod = 1, $reason = '') {
        global $DB;
        $sql = "SELECT fromuser_id, thread_id from blogs_msgs WHERE id = ?i";
        $res = $DB->row($sql, $edit_id);
        $from_id = $res['fromuser_id'];
        $thread_id = $res['thread_id'];
        $sql = "SELECT fromuser_id from blogs_msgs WHERE thread_id = ?i AND reply_to ISNULL";
        $buser_id = $DB->val($sql, $thread_id);
        if (($fid != $from_id && $mod == 1) && ($fid != $buser_id && $mod == 1)) {
            $err = "Вы не можете удалять чужие сообщения!";
            return $thread_id;
        }
        
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        $sModer = ' , moderator_status = '. ( $fid == $from_id ? 'NULL' : $fid ) .' ';
        $sql    = $DB->parse( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i;', $edit_id, user_content::MODER_BLOGS );
        
        $sql .= "UPDATE blogs_msgs SET deleted=NOW(), deluser_id=?i, deleted_reason = ? $sModer WHERE id = ?i";
        $userSubscribe = self::getUsersSubscribe( array($edit_id) );
        $res = $DB->query($sql, $fid, $reason, $edit_id);
        $error = $DB->error;
        
        if ( !$error ) {
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/smail.php";
            $s = new smail();
            $s->sendBlogPostDeleted( get_uid(false), $userSubscribe );
        }
        return $thread_id;
    }
	/**
	 * Восстановить сообщение
	 *
	 * @param integer $fid     UID
	 * @param integer $edit_id ID Blog
	 * @param string  $ip      ИП того кто удаляет
	 * @param mixed   $error   Массив ошибок
	 * @param boolean $mod     Имеет ли юзер права на удаление
	 * @return integer $thread_id ИД удаленного сообщения
	 */
    function RestoreDeleteMsg($fid, $edit_id, $ip, &$error, $mod = 1) {
        global $DB;
        $sql = "SELECT fromuser_id, thread_id, msgtext, title, deluser_id, reply_to FROM blogs_msgs WHERE id = ?i AND deleted IS NOT NULL";
        $res = $DB->row($sql, $edit_id);
        if (!$res) {
            $err = "Нечего востанавливать";
            return 0;
        }
        if(!($res['deluser_id']==$fid || hasPermissions('blogs'))) {
            $err = "Вы не можете восстановить сообщение";
            return 0;
        }
        $from_id = $res['fromuser_id'];
        $thread_id = $res['thread_id'];
        $sql = "SELECT fromuser_id from blogs_msgs WHERE thread_id = ?i AND reply_to ISNULL";
        $buser_id = $DB->val($sql, $thread_id);
        if (($fid != $from_id && $mod == 1) && ($fid != $buser_id && $mod == 1)) {
            $err = "Вы не можете удалять чужие сообщения!";
            return $thread_id;
        }
        
        $sModer = '';
        
        if ( $fid == $from_id && !hasPermissions('blogs') /*&& !is_pro()*/ ) {
            /*$sModer = ' , moderator_status = 0 ';
            
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );

            $stop_words    = new stop_words( hasPermissions('blogs') );
            $nStopWordsCnt = $stop_words->calculate( $res['msgtext'], $res['title'] );
            $nSortOrder    = $res['reply_to'] ? 1 : 0;
            
            $DB->insert( 'moderation', array('rec_id' => $edit_id, 'rec_type' => user_content::MODER_BLOGS, 'stop_words_cnt' => $nStopWordsCnt, 'sort_order' => $nSortOrder) );*/
        }
        
        $sql = "UPDATE blogs_msgs SET deleted=NULL, deluser_id=NULL, deleted_reason = NULL $sModer WHERE id = ?i";
        $res = $DB->query($sql, $edit_id);
        $error = $DB->error;
        return $thread_id;
    }

	/**
	 * Взять имя группы по ИД и по "базе"
	 *
	 * @param integer $id    ИД группы
	 * @param integer $base  "база" для тем
	 * @param boolean $mod   Право на выбор имени группы по ИД
	 * @return string $name  Имя группы
	 */
    function GetGroupName($id, $base = 0, $mod = 1) {
        global $DB;
        if ($base == 3)
            return "Комментарии к проекту:";
        if ($base == 4)
            return "Комментарии к работе:";
        if ($base == 5)
            return "Комментарии и конкурсные работы:";
        if ($base == 1) {
            $sql = "SELECT name FROM prof_group WHERE id='$id'";
            $base = "prof";
        } else {
            $sql = "SELECT t_name FROM blogs_groups WHERE id='$id' AND perm&'$mod'='$mod'";
            $base = "";
        }
        $name = $DB->cache(21600)->val($sql);
        $error .= $DB->error;
        return $name;
    }

    /**
     * Определение id группы по seo ссылке
     * 
     * @param  string  $link  имя ссылки
     * @return integer        id группы
     */
    function getGroupId($link) {
        return (int) $GLOBALS['DB']->cache(21600)->val("SELECT id FROM blogs_groups WHERE link = ? LIMIT 1", $link);
    }

	/**
	 * Отмечаем прочитанные сообщения(комментарии)
	 *
	 * @param integer $thread_id  ИД потока
	 * @param integer $fid        UID
	 * @param integer $status     Статус прочтения 0 - не прочитал, 1 - прочитал
	 */
    function SetRead($thread_id, $fid, $status = 0) {
        global $DB;
        $sql = "UPDATE blogs_themes_watch SET status = '$status', last_view = now() WHERE theme_id = ?i AND user_id = ?i";
        if($res = $DB->query($sql, $thread_id, $fid)) {
            if(!pg_affected_rows($res)) {
                $sql = "INSERT INTO blogs_themes_watch_" . date("Y") . " (theme_id, user_id, status) VALUES (?, ?, ?)";
                $DB->query($sql, $thread_id, $fid, $status);
            }
        }
    }

    /**
	 * Отписываемся от темы
	 *
	 * @param integer $thread_id  ИД потока
	 * @param integer $fid        UID
	 * @param boolean $mail       Подписать или нет
	 * @return string Сообщение об ошибке
	 */
    function setMail($thread_id, $fid, $mail='f') {
        global $DB;
        $sql = "UPDATE blogs_themes_watch SET is_mail='{$mail}' WHERE theme_id = ?i AND user_id = ?i";
        if($res = $DB->query($sql, $thread_id, $fid)) {
            if(!pg_affected_rows($res)) {
                $sql = "INSERT INTO blogs_themes_watch_" . date("Y") . " (theme_id, user_id, is_mail) VALUES (?, ?, ?)";
                $DB->query($sql, $thread_id, $fid, $mail);
            }
        }

        return $DB->error;
    }

	/**
	 * Взять информацию о сообщении по его ИД
	 *
	 * @param integer $msg_id ИД сообщения
	 * @param integer $error  Ошибки
	 * @param mixed   $perm   Дополнительная переменная для записи информации
 	 * @return array  Информация сообщения
	 */
    function GetMsgInfo($msg_id, &$error, &$perm) {
        global $DB;
        $sql = "
			SELECT 
              blogs_msgs.*, 
              blogs_themes.id_gr, blogs_themes.base, blogs_themes.messages_cnt, blogs_themes.karma, blogs_themes.last_activity,
              blogs_themes.is_private, blogs_themes.close_comments, blogs_themes.id_gr_public, blogs_themes.fav_cnt, blogs_themes.ontop,
              users.*, blogs_poll.question AS poll_question, blogs_poll.closed AS poll_closed, blogs_poll.multiple AS poll_multiple
			FROM blogs_msgs
			INNER JOIN blogs_themes ON blogs_themes.thread_id = blogs_msgs.thread_id
			INNER JOIN users ON users.uid=blogs_msgs.fromuser_id
			LEFT JOIN blogs_poll ON blogs_poll.thread_id = blogs_msgs.thread_id
			WHERE id=?i
		";
        $ret = $DB->row($sql, $msg_id);
        $error = $DB->error;
        if (! $error && $ret) {
            if ($ret) {
                $res = CFile::selectFilesBySrc(self::FILE_TABLE, intval($ret['id']), 'id');
                $ret['attach'] = $res;
				if ($ret['poll_question']) {
					$res = $DB->rows("SELECT * FROM blogs_poll_answers WHERE thread_id = ?i ORDER BY id", $ret['thread_id']);
					$ret['poll'] = $res;
				}
            }
            switch ($ret['base']) {
                case 0 :
                    $sql = "SELECT perm FROM blogs_groups WHERE id=?i";
                    $perm = $DB->val($sql, $ret['id_gr']);
                    break;
                case 3 :
                case 5 :
                    $sql = "SELECT kind FROM projects where id=?i";
                    $kind = $DB->val($sql, $ret['id_gr']);
                    $ret['kind'] = $kind;
                    $perm = 1;
                    break;
                default :
                    break;
            }
        }
        return $ret;
    }

    /**
     * Находит комментарии и собирает данные по ним для отправки уведомлений.
     * После изменения этой функции, необходимо перезапустить консьюмер /classes/pgq/mail_cons.php на сервере.
     * Если нет возможности, то сообщить админу.
     * @see smail::BlogNewComment()
     * @see PGQMailSimpleConsumer::finish_batch()
     *
     * @param string|array $message_ids   идентификаторы комментариев.
     * @param resource $connect           соединение к БД (необходимо в PgQ) или NULL -- создать новое.
     * @return array|mixed                Если есть комментарии то возвращает массив их, если нет то NULL
     */
    function GetComments4Sending($message_ids, $connect = NULL) {
        global $DB;
        if(!$message_ids) return NULL;
        if(is_array($message_ids))
          $message_ids = implode(',', array_unique($message_ids));

        $sql =
        "SELECT b.id, b.thread_id, b.title, b.msgtext, b.modified_id, b.reply_to,
                ub.uid,
                ub.login,
                ub.uname,
                ub.usurname,
                ub.email,
                up.uid as p_uid,
                up.login as p_login,
                up.uname as p_uname,
                up.usurname as p_usurname,
                up.email as p_email,
                up.role as p_role,
                up.subscr as p_subscr,
                up.is_banned as p_banned,
                ut.uid as t_uid,
                ut.login as t_login,
                ut.uname as t_uname,
                ut.usurname as t_usurname,
                ut.email as t_email,
                ut.role as t_role,
                ut.subscr as t_subscr,
                ut.is_banned as t_banned,
                moder.login as m_login,
                moder.uname as m_uname,
                moder.usurname as m_usurname
           FROM
           (
             SELECT *
               FROM blogs_msgs
              WHERE id IN ({$message_ids})
              LIMIT ALL -- именно так, чтобы указать планировщику, что сначала нужно выполнить данный подзапрос.
                        -- Иначе он начинает с тяжелейших JOIN (b=bt).
           ) as b
         INNER JOIN
           users ub
             ON ub.uid = b.fromuser_id
         INNER JOIN
           blogs_msgs bt
             ON bt.thread_id = b.thread_id
            AND bt.reply_to IS NULL
         INNER JOIN
           blogs_themes bth
             ON bth.thread_id = b.thread_id
         INNER JOIN
           users ut
             ON ut.uid = bt.fromuser_id
            AND ut.is_banned = '0'
         LEFT JOIN
           blogs_msgs bp
             ON bp.id = b.reply_to
            AND bp.fromuser_id <> b.fromuser_id
         LEFT JOIN
           users up
             ON up.uid = bp.fromuser_id
            AND up.is_banned = '0'
         LEFT JOIN
           users moder
             ON b.modified_id = moder.uid";

        return $DB->rows($sql);
    }
	/**
	 * Взять информацию о потоке сообщений по ИД потока
	 *
	 * @param integer $thread_id ИД Потока
	 * @param string  $error  Возвращает сообщение об ошибке
	 * @param mixed   $perm   Возвращает иформацию о группах
	 * @return array Результат выборки
	 */
    function GetThreadMsgInfo($thread_id, &$error, &$perm) {
        global $DB;
        $sql = "
          SELECT *
            FROM blogs_msgs b
          INNER JOIN blogs_themes t ON t.thread_id = b.thread_id
          INNER JOIN users u ON u.uid = b.fromuser_id
          LEFT JOIN blogs_blocked bb ON bb.thread_id = t.thread_id
           WHERE b.thread_id = ?i AND b.reply_to IS NULL
        ";
        $ret = $DB->row($sql, $thread_id);
        $perm = (int)(!!$ret); // устарело, поэтому всегда 1.
        $error = $DB->error;
        return $ret;
    }
	/**
	 * Создать новую группу
	 *
	 * @param string $name Имя группы
	 * @return string Сообщение об ошибке
	 */
    function NewGroup($name) {
        global $DB;
        $sql = "INSERT INTO blogs_groups (t_name, link) VALUES ( ?, ? )";
        $DB->query($sql, $name, translit(strtolower(htmlspecialchars_decode($name, ENT_QUOTES))));
        return $DB->error;
    }
	/**
	 * Удалить группу
	 *
	 * @param integer $id ИД группы
	 * @return string Сообщение об ошибке
	 */
    function DeleteGroup($id) {
        global $DB;
        if ($id == 3)
            exit();
        $sql = "DELETE FROM blogs_groups WHERE id=?i";
        $DB->query($sql, $id);
        return $DB->error;
    }
	/**
	 * Редактировать название группы
	 *
	 * @param integer $id    ИД Группы
	 * @param string  $name  Новое название группы
	 * @return string Сообщение об ошибке
	 */
    function EditGroup($id, $name) {
        global $DB;
        $sql = "UPDATE blogs_groups SET t_name=?, link=? WHERE id=?i";
        $DB->query($sql, $name, translit(strtolower(htmlspecialchars_decode($name, ENT_QUOTES))), $id);
        return $DB->error;
    }
	/**
	 * Изменить шаблон отображения групп
	 *
	 * @param integer $pos        Позиция отображения
	 * @param integer $hide       Показать/Скрыть
	 * @param integer $read_only  Только для чтения
	 * @return string Сообщение об ошибке
	 */
    function LayoutChange($pos, $hide, $read_only) {
        global $DB;
        $sql = "PREPARE upd(int, int) AS UPDATE blogs_groups SET n_order=$1 WHERE id=$2;";
        foreach ($pos as $newpos => $id)
            $sql .= "EXECUTE upd(" . $newpos . "," . $id . ");";
        if ($hide) {
            $sql .= "UPDATE blogs_groups SET perm='0'; UPDATE blogs_groups SET perm='1' WHERE id IN (";
            $sql .= implode(", ", $hide) . ");";
        }
        if ($read_only) {
            $sql .= "UPDATE blogs_groups SET read_only='0'; UPDATE blogs_groups SET read_only='1' WHERE id IN (";
            $sql .= implode(", ", $read_only) . ")";
        } else
            $sql .= "UPDATE blogs_groups SET read_only='0';";
        $res = $DB->query($sql);
        $error = $DB->error;
        return $error;
    }
    
	/**
	 * Изменение избранных блогов
	 *
	 * @param integer $thread_id  ИД потока
	 * @param integer $priority   Приоритет
	 * @param integer $uid        ИД Юзера
	 * @param string  $action     Действие изменения Добавить(add)/Удалить(delete)
	 * @return array  Результат работы функции
	 */
    function ChangeFav($thread_id, $priority = 0, $uid, $action = "add") {
        global $DB;
        $thread_id = intval($thread_id);
        $uid = intval($uid);
        $priority = intval($priority);
        $sql = "SELECT * FROM blogs_fav WHERE (thread_id = ?i AND user_id = ?i)";
        $res = $DB->query($sql, $thread_id, $uid);
        //delete fav
        if ($action == "delete") {
            $sql = "DELETE FROM blogs_fav WHERE (thread_id = ?i AND user_id = ?i);";
            $res = $DB->query($sql, $thread_id, $uid);
            list($out) = pg_fetch_row($res);
            $ret[0] = ($out);
            $ret[1] = 0;
        } else {
            //insert fav
            if (pg_numrows($res) == 0) {
                $sql = "INSERT INTO blogs_fav (thread_id, user_id, priority) VALUES (?, ?, ?);";
                $res = $DB->query($sql, $thread_id, $uid, $priority);
                list($out) = pg_fetch_row($res);
                $ret[0] = ($out) ? $out : "<без темы>";
                $ret[1] = 1;
                //update fav priority
            } else {
                $sql = "UPDATE blogs_fav SET priority = ?i WHERE (thread_id = ?i AND user_id = ?i);";
                $res = $DB->query($sql, intval($priority), $thread_id, $uid);
                list($out) = pg_fetch_row($res);
                $ret[0] = ($out);
                $ret[1] = 0;
            }
        }
        return $ret;
    }
	/**
	 * Обновление избранного
	 *
	 * @param integer $thread_id  ИД потока
	 * @param integer $priority   Приоритет
	 * @param integer $uid        ИД Юзера
	 * @param string  $title      Заголовок
	 * @return integer 1
	 */
    function UpdateFav($thread_id, $uid, $priority = 0, $title = "") {
        global $DB;
        $thread_id = intval($thread_id);
        $uid = intval($uid);
        $priority = intval($priority);
        $sql = "UPDATE blogs_fav SET priority=?, title=? WHERE thread_id = ? AND user_id = ? ;";
        $res = $DB->query($sql, $priority, $title, $thread_id, $uid);
        return 1;
    }
    
    /**
     * Взять избранные блоги для верстки типа "Списком"
     */
    function getFavoritesList() {
        
    }
    
	/**
	 * Взять избранные блоги
	 *
	 * @param integer $uid UID
	 * @param string $refresh_order Сортировка - По приоритету(priority), По заголовку(abc), По времени добавления(по умолчанию)
	 * @return array Массив избранных блогов
	 */
    function GetFavorites($uid, $refresh_order = "", $gr_num = 0) {
        global $DB;
        //$sql = "SELECT blogs_msgs.thread_id, title FROM blogs_fav LEFT JOIN blogs_msgs ON blogs_msgs.thread_id=blogs_fav.thread_id WHERE blogs_fav.user_id='$uid' AND blogs_msgs.reply_to IS NULL";
        if ($refresh_order == "priority") {
            $sql_order = " a.priority DESC";
        } elseif ($refresh_order == "abc") {
            $sql_order = " lower(COALESCE(NULLIF(a.title,''), b.title))";
        } else {
            $sql_order = " a.add_time DESC NULLS LAST";
        }
        
        $group = ($gr_num != 0) ? " AND c.id_gr='$gr_num'" : " AND c.id_gr <> 7";
        
        //Запрос с сортировкой коментариев
        $sql = "SELECT COALESCE(NULLIF(a.title,''), b.title) as title, a.priority, b.thread_id, c.fav_cnt FROM blogs_fav a, blogs_msgs b, blogs_themes c WHERE a.thread_id=b.thread_id AND a.user_id='$uid' AND b.reply_to IS NULL AND c.thread_id=a.thread_id ORDER BY " . $sql_order;
        $ret = $DB->rows($sql);
        if ($ret)
            foreach ($ret as $ikey => $value) {
                $out[$value['thread_id']]['title'] = $value['title'];
                $out[$value['thread_id']]['priority'] = $value['priority'];
                $out[$value['thread_id']]['fav_cnt'] = $value['fav_cnt'];
            }
        return $out;
    }
	/**
	 * Взять поток избранных блогов
	 *
	 * @param integer $uid       UID
	 * @param integer $thread_id ИД Потока
	 * @return array Массив потока избранных блогов
	 */
    function GetFavoriteByThreadID($uid, $thread_id) {
        global $DB;
        $uid = intval($uid);
        $thread_id = intval($thread_id);
        $sql = "SELECT a.title as favtitle, a.priority, b.thread_id, b.title, c.messages_cnt FROM blogs_fav a, blogs_msgs b, blogs_themes c WHERE a.thread_id=b.thread_id AND a.user_id=? AND a.thread_id=? AND b.reply_to IS NULL AND c.thread_id=a.thread_id LIMIT 1";
        $ret = $DB->rows($sql, $uid, $thread_id);
        if ($ret)
            foreach ($ret as $ikey => $value) {
                $out['thread_id'] = $value['thread_id'];
                $out['title'] = (($value['favtitle'] == "") ? $value['title'] : $value['favtitle']);
                $out['priority'] = $value['priority'];
            }
        return $out;
    }
    
	/**
	 * Добавить вложение
	 *
	 * @param array $ret Информация по файлам вложений, а так же переписывает информацию определенным образом работы функции
	 */
    function AddAttach(&$ret) {
        if (!$ret) return;
        $ids = array();
        $tmp = array();
        for ($i=0; $i<count($ret); $i++) {
            $ids[] = ($id = $ret[$i]['id']);
            $tmp[$id] = &$ret[$i];
        }
        if ($rows = CFile::selectFilesBySrc(self::FILE_TABLE, $ids, 'src_id, id')) {
            foreach ($rows as $row)
                $tmp[$row['src_id']]['attach'][] = $row;
        }
    }
	/**
	 * Количество файлов вложенных в сообщение
	 *
	 * @param integer $msg_id ИД сообщения
	 * @return integer количество файлов
	 */
    function GetAttachCount($msg_id) {
        $rows = CFile::selectFilesBySrc(self::FILE_TABLE, $msg_id);
        return ($rows ? count($rows) : 0);
    }

    /**
     * Блокирует тему
     *
     * @param integer $thread_id  треда
     * @param string  $reason     причина
     * @param int     $reason_id  ID причины, если она выбрана из списка (таблица admin_reasons, где act_id = 7)
     * @param integer $uid        ИД администратора (если 0, используется $_SESSION['uid'])
     * @param boolean $is_stream  блокиировка происходит из потока модерирования
     * @return int                ID блокировки
     */
    function Blocked ($thread_id, $reason, $reason_id = null, $uid = 0, $is_stream = true ) {
        global $DB;
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php';
        if (! $uid && ! ($uid = $_SESSION['uid']))
            return 'Недостаточно прав';
        
        if ( !$is_stream ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            $sId = $DB->val( 'UPDATE blogs_msgs SET moderator_status = ?i WHERE thread_id = ?i AND reply_to IS NULL RETURNING id', $uid, $thread_id );
            $DB->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i;', $sId, user_content::MODER_BLOGS );
        }
        
        $sId = $DB->val("INSERT INTO blogs_blocked (thread_id, \"admin\", reason, reason_id, blocked_time) VALUES(?, ?, ?, ?, NOW()) RETURNING id", $thread_id, $uid, $reason, $reason_id);
        messages::SendBlockedThread($thread_id, $reason);
        
        return $sId;
    }

    /**
     * Разблокирует тему
     *
     * @param integer $thread_id  id треда
     * @return string Сообщение об ошибке
     */
    function UnBlocked($thread_id) {
        global $DB;
        $DB->query("DELETE FROM blogs_blocked WHERE thread_id = ?i", $thread_id);
        return $DB->error;
    }

    /**
     * Подгрузка аттачей
     *
     * @param array $attach			массив с элементами типа CFile
     * @param array $max_image_size	массив с максимальными размерами картинки (см. CFile). Один для всех элементов attach
     * @param string $login			логин юзера, которому загрузить картинку. По умолчанию - юзер из $_SESSION['login']
     * @return array                сообщение об ошибке
     */
    function UploadFiles($attach, $max_image_size, $login = '') {
        if (!$login) {
            $login = $_SESSION['login'];
        }
            
        if (!$attach || !$login) {
            return NULL;
        }

        $i=0;
        foreach ($attach as $file) {
            $file->max_size = blogs::MAX_FILE_SIZE;
            $file->proportional = 1;
            $file->table = self::FILE_TABLE;
            $f_name = $file->MoveUploadedFile($login . "/upload");
            $i++;

            $ext = $file->getext();
            if (in_array($ext, $GLOBALS['graf_array']))
                $is_image = TRUE;
            else
                $is_image = FALSE;
            if ( !isNulArray($file->error) ) {
                if(count($attach) == 1) $alert[3] = "Файл не удовлетворяет условиям загрузки";
                else $alert[3] = "Один или несколько файлов не удовлетворяют условиям загрузки.";
                break;
            } else {
                if ($is_image && $ext != 'swf' && $ext != 'flv') {
                    if ( !$file->image_size['width'] || ! $file->image_size['height'] ) {
                        $alert[3] = 'Невозможно уменьшить картинку';
                        break;
                    }
                    if ( !$alert && ($file->image_size['width'] > $max_image_size['width'] || $file->image_size['height'] > $max_image_size['height']) ) {
                        $tmp = clone $file;
                        if ( !$tmp->img_to_small("sm_" . $f_name, $max_image_size)) {
                            $alert[3] = 'Невозможно уменьшить картинку.';
                            break;
                        } else {
                            $file->small = 2;
                        }
                    } else {
                        $file->small = 1;
                    }
                } else {
                    $file->small = ($ext == 'flv' ? 2 : 0);
                }
            }
        }
        return $alert;
    }

     /**
     * Выборка блогов из таблицы
     *
     * @param integer $page  Текущая страница
     * @param integer $count Количество на странице
     * @return array [массив блогов, инфа о юзерах в блогах]
     */
    function getCorporateBlog($page=1, $count=10) {
        global $DB;
    	$page--;
    	$sql_page =$page*$count;

    	$sql   = "SELECT * FROM corporative_blog WHERE id_blog = 0 AND (id_deleted IS NULL OR id_deleted = 0) ORDER BY id DESC LIMIT $count OFFSET $sql_page";

        $blogs = $DB->rows($sql);

        foreach($blogs as $k=>$v) {
        	$uids[$v['id_user']] = $v['id_user'];
        }

    	if ($uids){
        	$sql   = "SELECT uname, usurname, login, uid, role, is_pro, is_pro_test, boss_rate FROM users WHERE uid IN(".implode(", ", $uids).")";
        	$user = $DB->rows($sql);

        	foreach($user as $k=>$v)  $usr[$v['uid']]= $v;
    	}

    	return array($blogs, $usr);
    }

    /**
     * Возвращает названия файлов аттачей для этого сообщения
     *
     * @param  integer $msg_id	id сообщения
     * @param  array   $deleteattach список id присоединенных файлов, которые необходимо удалить
     * @return array   масив имен 
     */
    function GetAttach( $msg_id, $deleteattach = array() ) {
        if ( is_array($deleteattach) && count($deleteattach) ) {
            $where = 'id NOT IN (' . implode(',', $deleteattach) . ')';
        }

        $out1 = CFile::selectFilesBySrc(self::FILE_TABLE, $msg_id, NULL, $where);
        if ($out1) {
        	foreach ($out1 as $val){
                $out[] = $val['fname'];
        	}
        }

        return $out;
    }

    /**
     * Показывает количество прикрепленных аттачей к теме блога, помимо первого
     *
     * @param integer $num		количество аттачей, исключая первый
     * @return string			ссылка внутрь темы
     */
    function ShowMoreAttaches($num){
        $num = intval($num-1);
        if ($num)
        {
            if ($num == 1)
            {
                return "Внутри ещё 1 файл";
            }
            elseif($num <= 4)
            {
                return "Внутри ещё $num файла";
            }
            elseif($num <= 20)
            {
                return "Внутри ещё $num файлов";
            }
        }
    }

    /**
     * Голосовал ли пользователь в этом опросе?
     *
     * @param integer $uid         uid пользователя
     * @param integer $thread_id   id треда в блоге
     * @return integer Количество ответов
     */
	function Poll_Voted($uid, $thread_id) {
        global $DB;
		$sql = "SELECT COUNT(*) FROM blogs_poll_votes WHERE user_id = ?i AND thread_id = ?i";
		$p = $DB->val($sql, intval($uid), intval($thread_id));
		return $p;
	}

    /**
     * Проголосовать
     *
     * @param integer $uid         uid пользователя
     * @param array   $answers     ответы
     * @param integer $error       причина невозможности голосования
     * @return boolean             true - если все ок и ноборот
     */
	function Poll_Vote($uid, $answers, &$error) {
        global $DB;
		$error = "";
		$poll = $DB->row("
			SELECT
				p.thread_id, p.closed, p.multiple, COUNT(a.id) AS answers
			FROM
				blogs_poll_answers a
			INNER JOIN
				blogs_poll p ON a.thread_id = p.thread_id
			WHERE
				a.id IN (?l)
			GROUP BY
				p.thread_id, p.closed, p.multiple
		", $answers);
		if (count($answers) != $poll['answers']) {
			$error = "Опрос не найден";
			return FALSE;
		}
		elseif ($answer['closed'] == "t") {
			$error = "Опрос закрыт";
			return FALSE;
		}
		elseif ($this->Poll_Voted($uid, $poll['thread_id'])) {
			$error = "Вы уже голосовали в этом опросе";
			return FALSE;
		}
		$data = array();
		$max = ($poll['multiple'] == 't')? count($answers): 1;
		for ($i=0; $i<$max; $i++) {
			$data[$i] = array(
				'user_id'   => $uid,
				'thread_id' => $poll['thread_id'],
				'answer_id' => $answers[$i]
			);
		}
		if ($data) {
			$DB->insert('blogs_poll_votes', $data);
		}
		return TRUE;
	}

    /**
     * Получить список вариантов ответот с данными для голования
     *
     * @param integer $uid         uid пользователя
     * @param integer $thread_id   id треда в блоге
     * @return array  Данные выборки
     */
	function Poll_Answers($thread_id) {
        global $DB;
		$sql = "SELECT * FROM blogs_poll_answers WHERE thread_id = ?i ORDER BY id";
		return $DB->rows($sql, intval($thread_id));
	}

    /**
     * Закрыть/Открыть голование
     *
     * @param integer $thread_id   id треда в блоге
     * @return boolean             true - закрылось, false - открылось
     */
	function Poll_Close($thread_id) {
        global $DB;
		$sql = "UPDATE blogs_poll SET closed = NOT closed WHERE thread_id = ?i RETURNING closed";
		$r = $DB->val($sql, intval($thread_id));
		return ($r == 't');
	}

    /**
     * Закрыто ли голосование
     *
     * @param integer $thread_id   id треда в блоге
     * @return boolean
     */
	function Poll_Closed($thread_id) {
        global $DB;
		$sql = "SELECT closed FROM blogs_poll WHERE thread_id = ?i";
		$r = $DB->val($sql, intval($thread_id));
		return ($r == 't');
	}

    /**
     * Удалить голосование
     *
     * @param integer $thread_id   id треда в блоге
     * @param string  $msgtext     текст которым следует заменить сообщение в блоге или FALSE, если ничего менять не нужно
     */
	function Poll_Remove($thread_id, &$msgtext) {
        global $DB;
		$msgtext = FALSE;
        $sql = 'SELECT id, title, msgtext FROM blogs_msgs WHERE thread_id = ?i AND reply_to IS NULL';
        if($row = $DB->row($sql, $thread_id)) {
            if (!$row['title'] && !$row['msgtext'] && !self::GetAttachCount($row['id'])) {
                $DB->query("UPDATE blogs_msgs SET msgtext = 'Опрос удален' WHERE thread_id = ?i AND reply_to IS NULL", $thread_id);
                $msgtext = 'Опрос удален';
            }
            $DB->query("DELETE FROM blogs_poll WHERE thread_id = ?i", $thread_id);
        }
	}


	/**
	 * Формирует массив ответов для формы редактирования.
	 * @param array $edit_msg   редактируемое сообщение
	 * @return array
	 */
	function Poll_GetPostAnswers($edit_msg) {
        $answers = array();
        $has = is_array($_POST['answers_exists'])? $_POST['answers_exists']: array();
        if ($edit_msg['poll']) {
        	for ($i=0; $i<count($edit_msg['poll']); $i++) {
        		$ok = !isset($_POST['question']);
        		for ($j=0; $j<count($has); $j++) {
        			//if ($edit_msg['poll'][$i]['id'] == $has[$j]) {
        			if (!empty($has[ $edit_msg['poll'][$i]['id'] ])) {
        				$ok = true;
        				break;
        			}
        		}
        		if ($ok) $answers[] = array('id'=>$edit_msg['poll'][$i]['id'], 'answer'=>(($has[$edit_msg['poll'][$i]['id']])? str_replace(array('"', "'", "\\"), array('&quot;', '&#039;', '&#92;'), stripslashes($has[$edit_msg['poll'][$i]['id']])): $edit_msg['poll'][$i]['answer']));
        	}
        }
        if ($_POST['answers']) {
        	foreach ($_POST['answers'] as $answer) $answers[] = array('id'=>0, 'answer'=>str_replace(array('"', "'", "\\"), array('&quot;', '&#039;', '&#92;'), stripslashes($answer)));
        }
        if (!$answers) {
        	$answers[] = array('id'=>0, 'answer'=>'');
        }
        return $answers;
    }

	/**
     * Формируем дерево для вывода комментариев
     *
     * @param object $blog    Комментарии тут
     * @param string $pkey    Ключ для назначения родительских узлов
     * @return array   Отсортированные данные
     */
    function createTreeBlog($blog, $pkey = 'reply_to') {
        $ch = $thread = array();
        // Идентифицируем узлы.
        foreach($blog->thread as $b) $thread[$b['id']] = $b;
        // Назначаем дочерние узлы родительским узлам по ключу reply_to (сделать параметром).
        foreach($thread as $i=>$b) {
            if(!$b[$pkey]) continue;
            $bp = &$thread[$b[$pkey]];
            $thread[$i]['level'] = $bp['level']+1;
            $bp['children'][$i] = &$thread[$i];
            $ch[] = $i;
        }
        // Удаляем переназначенные узлы с исходных позиций.
        while($i=array_pop($ch)) unset($thread[$i]);
        return $thread;
    }

    /**
     * Печатаем дочерние узлы ($n -- родительский узел).
     *
     * @param array  $n       Родительсткий узел
     * @param string $otag    Тег начала узла
     * @param string $ctag    Тег конца узла
     */
    function viewTreeNodes(&$n, $otag='<ul class="bg-ul">', $ctag="</ul>") {
        global $blog;
        if(!$n['children']) return;
        echo $otag;
        foreach($n['children'] as $n) self::viewTreeNode($n);
        echo $ctag;
    }

    /**
     * Печатаем один узел.
     *
     * @param array $n Данные узла
     */
    function viewTreeNode(&$n) {
        global $clearQueryStrOpen, $uid, $session, $blog;
        $overlvl = $n['level'] >= 4;
        $rlvl = ($n['level'] >= 4 ? 4 : $n['level'])-1;
        echo '<li class="bg-li">';
        echo '<a name="o'.$n['id'].'"></a>';
        echo pda::pda_info_user(array("login"=>$n['login'], "uname"=>$n['uname'], "usurname"=>$n['usurname'], "photo"=>$n['photo'], "is_team"=>$n['is_team']), ($n['payed'] == 't'), is_emp($n['role']), $session->getActivityByLogin($n['login']), ", ".date("d.m.Y | H:i", strtotimeEx($n['post_time'])));

        echo '<div class="bg-cmt-o" '.($_GET['openlevel']==$n['id']?'style="background-color: #fff7dd;"':'').'>';

        $bb       = $blog->thread;
		$buser_id = array_pop($bb);
	    $buser_id = $buser_id['fromuser_id'];

        if($n['deleted']) {
            if($n['deluser_id'] == $n['fromuser_id']) {
                echo "<p>Комментарий удален автором ".date("[d.m.Y | H:i]", strtotime($n['deleted']))."<p>";
            } elseif ($n['deluser_id'] == $buser_id) {
                echo "<p>Комментарий удален автором темы ".date("[d.m.Y | H:i]",strtotimeEx($n['deleted']))."</p>";
            } else {
                echo "<p>Комментарий удален модератором ".date("[d.m.Y | H:i]", strtotime($n['deleted']))."</p>";
            }
        } elseif($n['is_banned']  && !hasPermissions('blogs')) {
            echo "<p>Ответ от заблокированного пользователя</p>";
        } else {
            echo '<h4>'.reformat($n['title'], round(20-$rlvl*2.3), 0, 1).'</h4>';
            echo '<p>'.reformat($n['msgtext'], round(20-$rlvl*2.3), 0, 0, 1).'</p>';
            if($n['yt_link']) {
              echo '<p>'.reformat($n['yt_link'], round(20-$rlvl*2.3)).'</p>';
            }
        }

        if ($n['attach'] && (!$n['deleted'] && !$n['is_banned'] || hasPermissions('blogs'))) {
            foreach ($n['attach'] as $attach) {
                echo pda::viewattachLeft($n['login'], $attach['fname'], "upload", $file, 200, 250, 307200, !$attach['small'], (($attach['small']==2)?1:0), 1);
            }
            echo "<br/>";
        }

        if(!$n['deleted'] && (!$n['is_banned'] || hasPermissions('blogs'))) {
            $parseURL = parse_url($_SERVER['REQUEST_URI']);
            echo '<div class="bg-cmt-in"> ';

            if($uid == $n['fromuser_id'] || $buser_id == $uid) echo '<a href="/alert.php?alert=comment&id='.$n['id'].'&tr='.intval($_GET['tr']).'">Удалить</a> / ';
            if($uid == $n['fromuser_id']) echo '<a href="'.$parseURL['path'].'?action=edit&id='.$n['id'].'&editcnt">Редактировать</a> / ';
            
            echo '<a href="'.($uid ? $parseURL['path']."?"."openlevel=".$n['id']."&ord=".$_GET["ord"]."&newcnt=1":"/fbd.php").'">Комментировать</a> / <a href="'.$parseURL['path']."?"."openlevel=".$n['id']."&ord=".$_GET["ord"]."#o".$n['id'].'">#</a>';

            echo '</div>';
        }



        echo '</div>';
        if(!$overlvl) self::viewTreeNodes($n);
        echo '</li>';
        if($overlvl) self::viewTreeNodes($n, '', '');
    }

    /**
     * Проверяем подписку пользователя к теме
     *
     * @param integer $theme Ид темы блога
     * @param integer $uid   ИД пользователя
     * @return boolean true - Подписан, false- не подписан
     */
    function isBlogSubscribe($theme, $uid) {
        global $DB;
        $sql = "SELECT is_mail FROM blogs_themes_watch WHERE theme_id = ?i AND user_id = ?i";
        $ret = $DB->row($sql, $theme, $uid);
        return ($ret['is_mail'] == 't');
    }

    /**
     * Рассылка подписавшимся на опредленную тему, берем тех кому надо послать
     *
     * @param string|array $themes_ids    идентификаторы комментариев.
     * @param resource $connect           соединение к БД (необходимо в PgQ) или NULL -- создать новое.
     * @return array
     */
    function getUsersSubscribe($themes_ids, $connect = NULL, $get_moderator_info = false) {
        global $DB;
        if(!$themes_ids) return NULL;
        if(is_array($themes_ids))
        $themes_ids = implode(',', array_unique($themes_ids));
        $join_modified_user = '';
        $modified_user_fields = '';
        if ($get_moderator_info) {
            $modified_user_fields = ',moder.login as m_login, moder.uname as m_uname, moder.usurname as m_usurname';
            $join_modified_user = 'INNER JOIN users moder ON bm.modified_id = moder.uid';
        }
        $sql = "SELECT
                    bm.id, bm.thread_id, bm.title, bm.msgtext, bm.reply_to, bt.title as blog_title,
                    ub.login, ub.uname, ub.usurname, ub.uid,
                    us.login as s_login, us.uname as s_uname, us.usurname as s_usurname, us.subscr as s_subscr, us.email as s_email, us.role as s_role, us.uid as s_uid
                    {$modified_user_fields}
                FROM
                ( SELECT *
                   FROM blogs_msgs
                  WHERE id IN ({$themes_ids})
                  LIMIT ALL -- именно так, чтобы указать планировщику, что сначала нужно выполнить данный подзапрос.
                            -- Иначе он начинает с тяжелейших JOIN (b=bt).
                ) as bm
                INNER JOIN blogs_msgs bt
                  ON bt.thread_id = bm.thread_id
                  AND bt.reply_to IS NULL
                INNER JOIN users ub ON ub.uid = bm.fromuser_id
                INNER JOIN blogs_themes_watch bw ON ( bw.is_mail = 't' AND bw.theme_id = bm.thread_id)
                INNER JOIN users us ON us.uid = bw.user_id AND us.is_banned = '0'
                {$join_modified_user}";

        return $DB->rows($sql);
    }
    
    /**
     * Возвращает в каком разделе и на какой странице находится блог
     * 
     * @param  int $thread_id ID темы
     * @param  int $fid UID текущего юзера
     * @param  string $ord Тип вкладки
     * @return array 
     */
    function getGroupAndPos( $thread_id, $fid = 0, $ord = '' ) {
        switch ($ord) {
            case 'my_all':
                $sql = "SELECT t.id_gr FROM blogs_themes t INNER JOIN blogs_msgs m ON t.thread_id = m.thread_id WHERE m.id = ?i";
                $id_gr = $GLOBALS['DB']->val( $sql, $thread_id );
                $sQuery = "
                    SELECT 
                        COUNT(m.*) 
                    FROM 
                        (
                            SELECT mm.id 
                            FROM blogs_themes as tt
                            INNER JOIN blogs_msgs as mm ON tt.thread_id = mm.thread_id AND tt.id_gr = ?i
                            WHERE mm.fromuser_id = ?i
                            ORDER BY mm.id DESC
                        ) m
                    WHERE m.id > ?i
                 ";
                $qret = $GLOBALS['DB']->val( $sQuery, $id_gr, $fid, $thread_id );
                $ret = array('id_gr'=>$id_gr, 'pos'=>$qret);
                break;
            case 'my_posts':
                $sQuery = "
                    SELECT 
                        t.id_gr, COUNT(tb.thread_id) AS pos
                    FROM 
                        blogs_themes t 
                    LEFT JOIN 
                        blogs_themes tb ON t.id_gr = tb.id_gr 
                    WHERE 
                        t.thread_id = ? AND tb.thread_id >= t.thread_id AND tb.fromuser_id = ?i
                    GROUP BY 
                        t.id_gr
                 ";
                $ret = $GLOBALS['DB']->row( $sQuery, $thread_id, $fid );
                break;
            case 'my_comments':
                $sql = "SELECT t.id_gr FROM blogs_themes t INNER JOIN blogs_msgs m ON t.thread_id = m.thread_id WHERE m.id = ?i";
                $id_gr = $GLOBALS['DB']->val( $sql, $thread_id );
                $sQuery = "
                    SELECT 
                        COUNT(m.*) 
                    FROM 
                        (
                            SELECT mm.id 
                            FROM blogs_themes as tt
                            INNER JOIN blogs_msgs as mm ON tt.thread_id = mm.thread_id AND tt.id_gr = ?i
                            WHERE mm.reply_to IS NOT NULL AND mm.fromuser_id = ?i
                            ORDER BY mm.id DESC
                        ) m
                    WHERE m.id > ?i
                 ";
                $qret = $GLOBALS['DB']->val( $sQuery, $id_gr, $fid, $thread_id );
                $ret = array('id_gr'=>$id_gr, 'pos'=>$qret);
                break;
            default:
                $sQuery = "
                    SELECT 
                        t.id_gr, COUNT(tb.thread_id) AS pos
                    FROM 
                        blogs_themes t 
                    LEFT JOIN 
                        blogs_themes tb ON t.id_gr = tb.id_gr 
                    WHERE 
                        t.thread_id = ? AND tb.thread_id >= t.thread_id
                    GROUP BY 
                        t.id_gr
                 ";
                $ret = $GLOBALS['DB']->row( $sQuery, $thread_id );
                break;
        }
         return $ret;
    }

    /**
     * Получает список приатаченных файлов к сообщению
     *
     * @param   integer $msg_id     ID сообщения
     * @param   string  $login      Логин пользователя
     * @return  array               Информация о файлах
     *
     */
    function getAttachedFiles($msg_id) {
        $fList = array();
        $files = CFile::selectFilesBySrc(self::FILE_TABLE, $msg_id, 'id');
        if($files) {
            foreach($files as $file) {
                $fList[] = $file['id'];
            }
        }
        return $fList;
    }

    /**
     * Связывает файлы загруженные ассинхронно с сообщением
     *
     * @param   array     $files              Список загруженных файлов
     * @param   string    $login              Логин пользователя
     * @param   integer   $msg_id             ID сообщения
     * @param   boolean   $from_draft         Файлы из черновика
     */
    function addAttachedFiles($files, $msg_id, $login=NULL, $from_draft=false) {
        global $DB;

        if(!$login) {
            $login = $_SESSION['login'];
        }
        
        $bModeration = false;
        
        $old_files = $this->getAttachedFiles($msg_id);
        if($from_draft) {
            $notdeleted_files = array();
            if($files) {
                foreach($files as $f) {
                    if($f['status']==3 || in_array($f['id'], $old_files)) { array_push($notdeleted_files, $f['id']); }
                }
            }
            $attaches = CFile::selectFilesBySrc(self::FILE_TABLE, $msg_id);
            if ($attaches) {
                foreach ($attaches as $attach){
                    if(in_array($attach['id'], $notdeleted_files)) { continue; }
                    $cFile = new CFile($attach['id']);
                    $cFile->table = self::FILE_TABLE;
                    if($cFile->id) {
                        $cFile->Delete($cFile->id);
                    }
                }
            }
        }
        $max_image_size = array('width' => blogs::MAX_IMAGE_WIDTH, 'height' => blogs::MAX_IMAGE_HEIGHT, 'less' => 0);
        if($files) {
            foreach($files as $file) {
                switch($file['status']) {
                    case 4:
                        // Удаляем файл
                        $cFile = new CFile($file['id']);
                        $cFile->table = self::FILE_TABLE;
                        if($cFile->id) {
                            $cFile->Delete($cFile->id);
                        }
                        break;
                    case 1:
                        if(in_array($file['id'], $old_files)) {
                            $need_copy = false;
                        } else {
                            $bModeration = true;
                            $need_copy = true;
                        }
                        // Добавляем файл
                        $cFile = new CFile($file['id']);
                        $cFile->proportional = 1;
                        $cFile->table = self::FILE_TABLE;
                        $ext = $cFile->getext();

                        if($need_copy) {
                            $tmp_dir = "users/".substr($login, 0, 2)."/".$login."/upload/";
                            $tmp_name = $cFile->secure_tmpname($tmp_dir, '.'.$ext);
                            $tmp_name = substr_replace($tmp_name,"",0,strlen($tmp_dir));
                            $cFile->_remoteCopy($tmp_dir.$tmp_name, true);
                        }
                        if (in_array($ext, $GLOBALS['graf_array']))
                            $is_image = TRUE;
                        else
                            $is_image = FALSE;
                        if ($is_image && $ext != 'swf' && $ext != 'flv') {
                            if ( ($cFile->image_size['width'] > $max_image_size['width'] || $cFile->image_size['height'] > $max_image_size['height']) ) {
                                if($need_copy) {
                                    if ( $cFile->resizeImage($cFile->path.'sm_'.$cFile->name, $max_image_size['width'], $cFile->image_size['height'], 'landscape')) {
                                        $cFile->small = 2;
                                    }
                                } else {
                                    $cFile->small = 2;
                                }
                            } else {
                                $cFile->small = 1;
                            }
                        } else {
                            $cFile->small = ($ext == 'flv' ? 2 : 0);
                        }

                        $cFile->updateFileParams(array('src_id'=>$msg_id, 'small'=>$cFile->small), false);
                        break;
                }
            }
        }
        
        if ( $bModeration && $login == $_SESSION['login'] && !hasPermissions('blogs') /*&& !is_pro()*/ ) {
            // отравляем сообщение на модерирование сразу по факту создания или редактирования
            // появились новые файлы и это правит автор, и он не админ и не ПРО - отправить на модерирование
            /*require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            $DB->query( 'UPDATE blogs_msgs SET moderator_status = 0 WHERE id = ?i', $msg_id );
            $DB->insert( 'moderation', array('rec_id' => $msg_id, 'rec_type' => user_content::MODER_BLOGS) );*/
        }
    }
	
    /**
     * Возвращает true если пост в блоге удален
     * @param $topicId идентификатор поста в блоге
     * */
    function isTopicDeleted($topicId) {
        $id = (int)$topicId;
        global $DB;
        $cmd = "SELECT deleted FROM blogs_msgs WHERE id = {$topicId}";
        $val = $DB->val($cmd);
        if (trim($val)) {
            return true;
        }
        return false;
    }
    
    /**
     * Берем блоги по их ИД
     * 
     * @global type $DB
     * @param array $ids
     * @return boolean
     */
    public static function getBlogsByIds($ids) {
        global $DB;
        if(!is_array($ids)) return false;
        
        $sql = "SELECT title, thread_id as id, post_time 
                FROM blogs_msgs
                WHERE reply_to IS NULL AND thread_id IN (?l)";
        
        return $DB->rows($sql, $ids);
    }
}
