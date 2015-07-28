<?
/**
 * подключаем файл с основными функциями
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Класс для работы со статьями
 *
 */
class articles{

    const ARTICLE_MAX_FILESIZE = 5242880;
    const ARTICLE_MAX_LOGOSIZE = 1048576;
    const ARTICLE_MAX_TITLELENGTH = 100;
    const MAX_IMAGE_WIDTH  = 120;
    const MAX_IMAGE_HEIGHT = 120;

    /**
     * Возвращает данные необходимые для построения ЧПУ ссылки
     *
     * @param     integer    $id    ID статьи
     * @return    array             Данные для ЧПУ
     */
    public function getInfoForFriendlyURL($id) {
        global $DB;
        $sql = "SELECT title FROM articles_new WHERE id=?i";
        return $DB->cache(1800)->row($sql, $id);
    }
	
	/**
	 * Получает массив с превьюхами статей
	 *
	 * @param х3 $msg_cntr 				- ваще не ясно нафига
	 * @param integer $page			    - номер страницы
	 * @param integer $num_msgs			- количество статей на одной странице
	 * @param х3 $error					- ваще не ясно нафига
	 * @return Array					[[идентификатор статьи, превью статьи, подпись, логотип, ссылка, название, кол-во комментов]]
	 */
	function GetMsgs($msg_cntr, $page, $num_msgs, &$error){
        global $DB;
        $sql = "SELECT id, short, sign, logo, link, title, comms FROM articles LEFT JOIN (SELECT item_id, COUNT(*) as comms FROM blogs_articles GROUP BY item_id) as com
			 ON com.item_id=articles.id ORDER BY post_time DESC";
        if ($page && $num_msgs) {
            $sql .= " LIMIT $num_msgs OFFSET " . (($page - 1) * $num_msgs);
        }
		$ret = $DB->rows($sql);
		$error = $DB->error;
		if ($error) $error = parse_db_error($error);
		foreach ($ret as $row) {
			validate_code_style($row["msgtext"]);
		}
		return $ret;
	}

	/**
	 * Возвращает кол-во статей
	 *
	 * @return integer				количество статей
	 */
    function MsgsCount() {
        global $DB;
        $sql = "SELECT COUNT(*) FROM articles";
        return $DB->val($sql);
    }
  	
	/**
	 * Возвращает массив с данными о статье
	 *
	 * @param integer $msg_id		идентификатор статьи
	 * @param char $error			сообщение об ошибке
	 * @return array				[название, превью, текст статьи, подпись, ссылка, лого, идентификатор]
	 */
	function GetMsgInfo($msg_id, $error){
        global $DB;
		$sql = "SELECT title, short, msgtext, sign, link, logo, id FROM articles WHERE id = ?i";
		$ret = $DB->row($sql, $msg_id);
		$error = $DB->error;
		validate_code_style($ret["msgtext"]);
		return $ret;
	}
	
	/**
	 * Добавить статью
	 *
	 * @param char $title		название статьи
	 * @param char $short		превью статьи
	 * @param char $msg			текст статьи
	 * @param char $sign		подпись
	 * @param char $f_name		имя файла с логотипом
	 * @param char $link		ссылка на ресурс
	 * @return char				сообщение об ошибке
	 */
	function Add($title, $short, $msg, $sign, $file, $link){
        global $DB;
	    if ($file->tmp_name){
    	    $file->max_size = 1048576;
            $file->proportional = 1;
            $file->max_image_size = array('width'=>articles::MAX_IMAGE_WIDTH, 'height'=>articles::MAX_IMAGE_HEIGHT, 'less'=>1);
            $file->resize = 1;
            $file->proportional = 1;
            $file->topfill = 1;
            $file->server_root = 1;
        
            $f_name = $file->MoveUploadedFile("about/articles/");
    	    if (!isNulArray($file->error)) { $alert[3] = "Файл не удовлетворяет условиям загрузки"; $error_flag = 1;}
	    }
	    if (!$error_flag){
	    	validate_code_style($msg);
		    $sql = "INSERT INTO articles (title, short, msgtext, sign, logo, link) VALUES (?, ?, ?, ?, ?, ?)";
		    $DB->query($sql, $title, $short, $msg, $sign, $f_name, $link);
	    }
		return array($alert, $DB->error);
	}

	/**
	 * Удаляет статью
	 *
	 * @param integer $msg		идентификатор стаьи
	 * @param integer $admin	можно ли удалять статью
	 * @return char				сообщение об ошибке
	 */
	function Del($msg, $admin = 0){
        global $DB;
		if ($admin) $sql = "DELETE FROM articles WHERE (id=?i) RETURNING logo"; 
		else return 0;
		$ret = $DB->val($sql, $msg);
		if ($ret){
		    $file = new CFile();
		    $file->Delete(0,"about/articles/",$ret);
		}
		return $DB->error;
	}
	
	/**
	 * Редактировать статью
	 *
	 * @param char $title		название статьи
	 * @param char $short		превью статьи
	 * @param char $msg			текст статьи
	 * @param char $sign		подпись
	 * @param char $f_name		имя файла с логотипом
	 * @param char $link		ссылка на ресурс
	 * @param integer $msgid	идентификатор стаьи
	 * @return char				сообщение об ошибке
	 */
	function Edit($title, $short, $msg, $sign, $file, $link, $msgid){
        global $DB;
        validate_code_style($msg);
		if ($file->tmp_name) {
    		$file->max_size = 1048576;
            $file->proportional = 1;
            $file->max_image_size = array('width'=>articles::MAX_IMAGE_WIDTH, 'height'=>articles::MAX_IMAGE_HEIGHT, 'less'=>1);
            $file->resize = 1;
            $file->proportional = 1;
            $file->topfill = 1;
            $file->server_root = 1;
        
            $f_name = $file->MoveUploadedFile("about/articles/");
    	    if (!isNulArray($file->error)) { $alert[3] = "Файл не удовлетворяет условиям загрузки"; $error_flag = 1;}
		    if (!$error_flag) $sql = "UPDATE articles SET title = '$title', short= '$short', msgtext='$msg', sign='$sign', logo='$f_name', link='$link', modified=now() WHERE (id=?i)";
		}
		else $sql = "UPDATE articles SET title = '$title', short = '$short', msgtext='$msg', sign='$sign', link='$link', modified=now() WHERE (id=?i)";
		$DB->query($sql, $msgid);
		return array($alert, $DB->error);
	}


    /**
     * Добавляет статью для модерации
     *
     * @param int $user ID пользователя, добавившего статью
     * @param string $short Текст анонса
     * @param string $msg Текст статьи
     * @param CFile $file
     */
    function AddArticle($user, $title, $short, $msg, $fileid) {
        global $DB;
        validate_code_style($msg);
        $sql = "INSERT INTO articles_new (title, short, msgtext, user_id, logo)
                    VALUES (?, ?, ?, ?, ?) RETURNING id";

        $article_id = $DB->val($sql, $title, $short, $msg, $user, $fileid);

        return array($article_id, $DB->error);
    }
    
    /**
     * Изменяет статью
     * 
     * @param  mixed $user не используется
     * @param  int $article_id идентификатор статьи
     * @param  string $title новый заголовок
     * @param  string $short новое краткое содержание
     * @param  string $msg новый текст
     * @param  int $fileid новый id файла логотипа
     * @return resource результат запроса
     */
    function updateArticle($user, $article_id, $title, $short, $msg, $fileid) {
        global $DB;
        validate_code_style($msg);
        $sql = "UPDATE articles_new SET 
                    title = ?,
                    short = ?,
                    msgtext = ?,
                    logo = ?
                WHERE id = ?i";
        $res = $DB->query($sql, $title, $short, $msg, $fileid, $article_id);

        return $res;
    }

    /**
     * Удаляет статью
     *
     * @param integer $msg		идентификатор стаьи
     * @param integer $admin	можно ли удалять статью
     * @return char				сообщение об ошибке
     */
    function delArticle($id, $send_warn = "") {
        global $DB;
        $sql = "DELETE FROM articles_new WHERE id=? RETURNING logo, user_id, title";

        $ret = $DB->row($sql, $id);
        if ($ret) {
            $file = new CFile();
            $file->Delete($ret['logo']);
            require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php";
            $smail = new smail;
            $smail->delArticleSendReason($ret['user_id'], $ret['title'], $send_warn);
        }

        return $DB->error;
    }

    /**
     * Получает список статей
     *
     * @param integer $page Номер текущей страницы
     * @param integer $num_msgs Кол-во статей на странице
     * @param integer $user ид текущего пользователя
     * @param boolean $approved Если TRUE - выводить только подвержденные статьи,
     *                               FALSE - только неподтвержденные
     * @param integer $author ID пользователя-автора статьи
     * @return <type>
     */
    function getArticles($page, $num_msgs, $user_id, $tag, $approved = true, $author = null, $order = null, $declined = false) {
        global $DB;
        
        $inner_tags = "";
        if(intval($tag) > 0) {
            $tag_id = intval($tag);
            $inner_tags = "JOIN articles_word aw ON aw.word_id = {$tag_id} AND aw.article_id = an.id";
        } 
        $sql = "SELECT an.*,
                    file.fname, file.path, file.ftype, file.width, file.height,
                    u.uname, u.usurname, u.login,
                    au.rated, au.rate_value, au.bookmark, au.lastviewtime,
                    (an.comments_cnt-au.comments_cnt) as comments_unread
                FROM articles_new as an
                {$inner_tags}
                LEFT JOIN file ON file.id = an.logo
                LEFT JOIN articles_users au ON au.article_id = an.id AND au.user_id = {$user_id}
                INNER JOIN users as u ON u.uid = an.user_id 
                WHERE an.approved = " . ($approved ? 'TRUE ' : 'FALSE ')." AND an.declined = ". ($declined ? 'TRUE ' : 'FALSE ');
        if ($author) {
            $sql .= " AND u.uid = {$author}";
        }

        switch($order) {
            case 'comm':
                $sql .= " ORDER BY an.comments_cnt DESC, an.post_time DESC";
                break;
            case 'views':
                $sql .= " ORDER BY an.view_cnt DESC, an.post_time DESC";
                break;
            case 'rating':
                $sql .= " ORDER BY an.rating DESC, an.post_time DESC";
                break;
            case 'unpublic':
                $sql .= " ORDER BY an.moderation_time DESC";
                break;
            default:
                if(!$approved) {
                    $sql .= " ORDER BY an.post_time DESC";
                } else {
                    $sql .= " ORDER BY an.approve_date DESC, an.post_time DESC";
                }
        }

        if ($page && $num_msgs) {
            $sql .= " LIMIT $num_msgs OFFSET " . (($page - 1) * $num_msgs);
        }
        $ret = $DB->rows($sql);
        $error = $DB->error;
        if ($error) $error = parse_db_error($error);
        foreach ($ret as $row) {
        	validate_code_style($row["msgtext"]);
        }
        return $ret;
    }

    /**
     * Возвращает кол-во статей
     *
     * @param boolean $approved Если TRUE - учитывать только подвержденные статьи,
     *                               FALSE - только неподтвержденные
     * @return integer				количество статей
     */
    function ArticlesCount($approved = true, $user = null, $tag=0, $declined = false) {
        global $DB;
        $inner_tags = "";
        if(intval($tag) > 0) {
            $tag_id = intval($tag);
            $inner_tags = "JOIN articles_word aw ON aw.word_id = {$tag_id} AND aw.article_id = an.id";
        } 
        $sql = "SELECT COUNT(*) FROM articles_new an {$inner_tags} WHERE approved = "
             . ($approved ? 'TRUE ' : 'FALSE '). "AND an.declined = ". ($declined ? 'TRUE ' : 'FALSE ');
        if ($user) {
            $sql .= " AND user_id = {$user}";
        }
        return $DB->val($sql);
    }


    /**
     * Получить одну статью по ее ID
     *
     * @param integer $id ид статьи
     * @param integer $user ид текущего пользователя
     * @return <type>
     */
    function getArticle($id, $user_id, $force = false) {
        global $DB;
        $sql = "SELECT an.*,
                    file.fname, file.path, file.ftype, file.width, file.height,
                    u.uname, u.usurname, u.login,
                    au.rated, au.rate_value, au.bookmark, au.lastviewtime, au.hidden_threads
                FROM articles_new as an
                LEFT JOIN file ON file.id = an.logo
                LEFT JOIN articles_users au ON au.article_id = an.id ". (!$force?"AND au.user_id = ?":"") ."
                INNER JOIN users as u ON u.uid = an.user_id
                WHERE an.id = ?";
        $tsql = "SELECT name, word_id FROM articles_word as aw INNER JOIN words w ON w.id = aw.word_id WHERE article_id = ?i";
        $tags = $DB->rows($tsql, $id);
        if($force) { 
            $res = $DB->query($sql, $id);
        } else {
            $res = $DB->query($sql, $user_id, $id);
        }
        $error = $DB->error;
        if ($error) {
            $error = parse_db_error($error);
        } else {
            $ret = pg_fetch_row($res, null, PGSQL_ASSOC);
            if (!$ret) {
                return false;
            }
            $ret['kwords'] = $tags;
        }
        validate_code_style($ret["msgtext"]);
        return $ret;
    }

    /**
     * Изменяет рейтинг статьи
     * 
     * @param  int $user_id UID подьзователя
     * @param  int $article_id ID статьи
     * @param  int $rtype 1 или -1
     * @return mixed новый рейтинг - успех, false - провал 
     */
    function setRating($user_id, $article_id, $rtype) {
        global $DB;
        $article = articles::getArticle($article_id, $user_id);

        if(($article['rate_value'] > 0 && $rtype > 0) || ($article['rate_value'] < 0 && $rtype < 0) || $user_id == $article['user_id']) {
            return false;
        }

        $sql = "UPDATE articles_new SET rating = rating+{$rtype}
                WHERE id = ?";
        if(!$DB->query($sql, $article_id) ) {
            return false;
        }

        if($article['rate_value'] === NULL) {
            $sql = "INSERT INTO articles_users (user_id, article_id, rate_value)
                                VALUES (?, ?, ?i)";
            if(!$DB->query($sql, $user_id, $article_id, $rtype) ) {
                return false;
            }
        } else {
            $sql = "UPDATE articles_users SET rate_value = rate_value+?i
                           WHERE user_id = ?i AND article_id = ?i";
            if(!$DB->query($sql, $rtype, $user_id, $article_id) ) {
                return false;
            }
        }

        $ret_val = intval($article['rate_value'])+$rtype;
        return $ret_val;
    }

    /**
     * Устанавливает/обновляет закладку
     *
     * @param  integer $user_id ид пользователя
     * @param  integer $article_id ид сатьи
     * @param  integer $star тип звезды - число от 0 дл 4
     * @return bool
     */
    function bookmarkArticle($user_id, $article_id, $star) {
        global $DB;
        $article = articles::getArticle($article_id, $user_id);

        if($article['bookmark'] === NULL) {
            $sql = "INSERT INTO articles_users (user_id, article_id, bookmark, bookmark_time)
                                VALUES (?i, ?i, ?i, NOW())";
            if(!$DB->query($sql, $user_id, $article_id, $star) ) {
                return false;
            }
        } else {
            $sql = "UPDATE articles_users SET bookmark = ?i, bookmark_time = NOW()
                           WHERE user_id = ?i AND article_id = ?i";
            if(!$DB->query($sql, $star, $user_id, $article_id) ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Получить закладки пользователя
     * 
     * @param  integer $user_id ид пользователя
     * @param  srting $order порядок сортироки
     * @return array
     */
    function getBookmarks($user_id, $order = 'time') {
        global $DB;
        switch($order) {
            case 'time':
                $order = 'au.bookmark_time DESC';
                break;
            case 'priority':
                $order = 'au.bookmark DESC';
                break;
            case 'title':
                $order = 'btitle ASC';
                break;
        }

        $sql = "SELECT au.*,
                    a.title,
                    CASE WHEN au.bookmark_title IS NULL THEN a.title ELSE au.bookmark_title END as btitle
                FROM articles_users au
                INNER JOIN articles_new as a ON a.id = au.article_id AND a.approved = TRUE
                WHERE au.user_id = ?i AND au.bookmark > 0
                ORDER by $order";
                
        $ret = $DB->rows($sql, $user_id);
        $error = $DB->error;
        if ($error) $error = parse_db_error($error);
        return $ret;
    }


    /**
     * Устанавливает время последнего просмотра статьи,
     * текущее кол-во комментариев к статье,
     * добавляет просмотр в счетчике, если статья просматривается впервые
     *
     * @param integer $user_id ид пользователя
     * @param array $article Результат articles::getArticle()
     * @return boolean
     */
    function setArticleLVT($user_id, $article, $hidden = null) {
        global $DB;
        $article_id = $article['id'];
        $comments_cnt = $article['comments_cnt'];

        $h = "";
        if($hidden) $h = ", hidden_threads = '$hidden' ";
        
        if($article['bookmark'] !== NULL) {
            $sql = "UPDATE articles_users SET lastviewtime = NOW(), comments_cnt = ?i {$h}
                           WHERE user_id = ?i AND article_id = ?i";
            if(!$DB->query($sql, $comments_cnt, $user_id, $article_id) ) {
                return false;
            }
        } else {
            $sql = "INSERT INTO articles_users (user_id, article_id, lastviewtime, comments_cnt)
                                VALUES (?, ?, NOW(), ?i)";
            if(!$DB->query($sql, $user_id, $article_id, $comments_cnt) ) {
                return false;
            }
        }

        if($article['lastviewtime'] === NULL) {
            $sql = "UPDATE articles_new SET view_cnt = view_cnt+1
                        WHERE id = ?i AND approved = TRUE";
            if(!$DB->query($sql, $article_id) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Популярные авторы
     *
     * @param integer $limit 
     * @return <type>
     */
    function getTopAuthors($limit = 12) {
        global $DB;
        $sql = "SELECT u.login, u.photo, SUM(a.view_cnt) as viewcnt FROM
                articles_new a
                INNER JOIN users as u ON u.uid = a.user_id
                WHERE a.approved = TRUE
                GROUP BY a.user_id, u.login, u.photo ORDER BY viewcnt DESC LIMIT ?i";

        $ret = $DB->rows($sql, $limit);
        $error = $DB->error;
        if ($error) $error = parse_db_error($error);
        return $ret;
    }
    
    /**
     * Удалить закладку пользователя
     * 
     * @param  integer $user_id ид пользователя
     * @param  integer $article_id идентификатор статьи
     * @return bool true - успех, false - провал
     */
    function bookmarkDel($user_id, $article_id) {
        global $DB;
        $sql = "UPDATE articles_users SET bookmark = '0', bookmark_title = NULL, bookmark_time = NULL 
                    WHERE article_id = ?i AND user_id = ?i";
        if(!$DB->query($sql, $article_id, $user_id) ) {
            return false;
        }
        return true;
    }
    
    /**
     * Изменить закладку пользователя
     * 
     * @param  integer $user_id ид пользователя
     * @param  integer $article_id идентификатор статьи
     * @param  string $title новый заголовок
     * @param  integer $type 0 - не в закладках. 1-3 - в закладках, важность (тип звезд)
     * @return bool true - успех, false - провал
     */
    function bookmarkEdit($user_id, $article_id, $title, $type) {
        global $DB;
        $titleq = '';
        $titleq = $title ? "'$title'" : "NULL";

        $sql = "UPDATE articles_users SET bookmark = ?, bookmark_title = $titleq
                    WHERE article_id = ?i AND user_id = ?i";
        
        if(!$DB->query($sql, $type, $article_id, $user_id) ) {
            return false;
        }
        return true;
    }
    
    /**
     * Утвердить статью
     * 
     * @param  int $article_id идентификатор статьи
     * @param  int $uid uid пользователя
     * @return bool true - успех, false - провал
     */
    function setApproved($article_id, $uid) {
        global $DB;
        $sql = "UPDATE articles_new SET approved = TRUE,
                       approved_by = ?i,
                       approve_date = NOW()
                    WHERE id = ?i";
        if(!$res = $DB->query($sql, $uid, $article_id) ) {
            return false;
        }
        return true;
    }
    
    /**
     * Отклонить статью
     * 
     * @global type $DB
     * @param  int $article_id идентификатор статьи
     * @param  int $uid uid пользователя
     * @return bool true - успех, false - провал
     */
    function setDecline($article_id, $uid) {
        global $DB;
        $sql = "UPDATE articles_new SET declined = TRUE,
                       declined_by = ?i,
                       declined_date = NOW()
                    WHERE id = ?i";
        if(!$res = $DB->query($sql, $uid, $article_id) ) {
            return false;
        }
        return true;
    }
    
    /**
     * Возвратить на модерацию
     * 
     * @global type $DB
     * @param  int $article_id идентификатор статьи
     * @param  int $uid uid пользователя
     * @return bool true - успех, false - провал
     */
    function setUnDecline($article_id, $uid) {
        global $DB;
        $sql = "UPDATE articles_new SET declined = FALSE,
                       approved = FALSE,
                       declined_by = ?i,
                       declined_date = NOW(),
                       moderation_time = NOW()
                    WHERE id = ?i";
        if(!$res = $DB->query($sql, $uid, $article_id) ) {
            return false;
        }
        return true;
    }
    
    /**
     * Навигация предыдущая - следующая
     * 
     * @param  string $post_time время публикации текущей статьи
     * @param  bool $approved true - искать среди утвержденных, false - в очереди
     * @return array
     */
    function getNavigation($post_time, $approved = true) {
        global $DB;
        $field = "post_time";

        $fld = $approved ? 'approve_date' : 'post_time';
        $approved = $approved ? " TRUE " : " FALSE ";

        $sql = "SELECT NULL as id, NULL as pos, NULL as title, NULL as {$fld}
                UNION
                (SELECT id, 1 as pos, title, {$fld} FROM articles_new
                WHERE {$fld} > '$post_time' AND approved = $approved ORDER by {$fld} ASC LIMIT 1)
                UNION
                (SELECT id, 2 as pos, title, {$fld} FROM articles_new
                WHERE {$fld} < '$post_time' AND approved = $approved ORDER by {$fld} DESC LIMIT 1)
                ORDER by {$fld} DESC";
                
        $ret = $DB->rows($sql);

        return $ret;
    }
    
    /**
     * Добавляем ключевые слова к статье
     *
     * @param integer $article_id  ИД Статьи
     * @param array   $tags        Ключевые слова (теги)
     * @return boolean
     */
    function addArticleTags($article_id, $tags) {
        global $DB;
        
        require_once($_SERVER['DOCUMENT_ROOT']."/classes/kwords.php");
        
        $kwords = new kwords();
        $ids  = $kwords->add($tags, true);
        
        self::clearArticleTags($article_id);
        
        $sql = "INSERT INTO articles_word (article_id, word_id, pos) VALUES ";
        if($ids)
            foreach($ids as $position=>$word_id) {
                $data[] = "({$article_id}, {$word_id}, {$position})";
            }
        if(count($data) > 0) {
            $sql .= implode(", ", $data); 
            $res = $DB->squery($sql);
            
            $tags = implode(', ', $tags);
            $sql = "UPDATE articles_new SET keywords = ? WHERE id = ?";
            $DB->query($sql, $tags, $article_id);
            
            return $res;
        } 
        
        return false;
    }
    
    /**
     * Чистим статью от тегов
     *
     * @param integer $article_id  ИД статьи
     * @return boolean
     */
    function clearArticleTags($article_id) {
        global $DB;
        
        $sql = "DELETE FROM articles_word WHERE article_id = ?i";
        return $DB->query($sql, $article_id);    
    }
    
    /**
     * 10 Популярных тегов
     *
     * @return array
     */
    function getPopularTags() {
        global $DB;
        
        $sql = "SELECT COUNT(aw.article_id) as cnt, aw.word_id, w.name FROM articles_word aw
                INNER JOIN articles_new an ON an.id = aw.article_id AND an.approved = true 
                INNER JOIN words w ON w.id = aw.word_id GROUP BY aw.word_id, w.name ORDER BY cnt DESC, name ASC LIMIT 10";
        return $DB->rows($sql);
    }
    
    /**
     * Проверяем есть ли у пользователя статьи на модерации
     *
     * @param integer $uid ИД Пользователя
     * @return integer
     */
    function isApprovedArticles($uid) {
        global $DB;
        
        $sql = "SELECT 1 FROM articles_new WHERE user_id = ?i AND approved = false AND declined = false GROUP BY approved;";
        
        return $DB->val($sql, $uid);
    }
    
    /**
     * Добавляет в commune_attach запись о временном файле, загруженном во время наботра текста в визивиге 
     * */
    function addWysiwygFile($cfile) {
        global $DB;//articles_comments
        //временно прикрепляю файл к самому старому удаленному сообщению  
        $messageId = $DB->val("SELECT id FROM articles_comments WHERE deleted_id IS NOT NULL ORDER BY id ASC LIMIT 1");
        //articles_comments_files
        $DB->query("INSERT INTO articles_comments_files (comment_id, file_id, small, inline, temp) 
                    VALUES ({$messageId}, {$cfile->id}, 0, TRUE, TRUE)");
    }
    
    /**
     * Удаляет файлы и записи у которых флаги commune_attach.inline и commune_attach.temp равны TRUE
     *  и с момента создания которых прошло больше суток
     *  вызывается из hourly.php раз в сутки
     * */
    function removeWysiwygTrash() {
        global $DB;
        $rows = $DB->rows("SELECT ca.id, file_id
        FROM articles_comments_files AS ca
        LEFT JOIN file AS f
         ON f.id = ca.file_id
        WHERE ca.inline = TRUE AND ca.temp = TRUE AND f.modified + '1 day'::interval < NOW()");
        $files   = array();
        $records = array();
        $doDelete = 0;
        foreach ($rows as $row) {
            $files   [] = $row['file_id'];
            $records [] = $row['id'];
            $doDelete++;
        }
        if ($doDelete) {
            $_files   = join(", ", $files);
            $_records = join(", ", $records);
            $DB->query("DELETE FROM articles_comments_files WHERE id IN ({$_records})");
            $DB->query("DELETE FROM file WHERE id IN ({$_files})");
        }
    }
    
    /**
     * Берем статьи по их ИД
     * 
     * @global type $DB
     * @param array $ids
     * @return boolean
     */
    public function getArticleByIds($ids) {
        global $DB;
        
        if(!is_array($ids)) return false;
        
        $sql = "SELECT * FROM articles_new WHERE id IN (?l)";
        
        return $DB->rows($sql, $ids);
    }
}
?>
