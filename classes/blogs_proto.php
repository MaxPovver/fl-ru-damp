<?
/**
 * Подключаем файл c основными функциями
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
/**
 * Класс для работы с сообщениями
 *
 */
class blogs_proto 
{
	
	/**
     * Темы
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
     * Ид юзера соообщения
     *
     * @var integer
     */
	var $fromuser_id;
	/**
	 * Время опубликования сообщения
	 *
	 * @var date
	 */
	var $post_time;
	/**
	 * Текст сообщения
	 *
	 * @var string
	 */
	var $msgtext;
	/**
	 * Вложения файлов в сообщение
	 *
	 * @var mixed
	 */
	var $attach;
	/**
	 * Заголовок сообщения
	 *
	 * @var string
	 */
	var $title;
	/**
	 * Имя пользователя сообщения
	 *
	 * @var string
	 */
	var $uname;
	/**
	 * Фамилия пользователя сообщения
	 *
	 * @var string
	 */
	var $usurname;
	/**
	 * Логин пользователя сообщения
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
	 * Фотография пользователя сообщения
	 *
	 * @var string
	 */
	var $photo;
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
     * Кто модифицировал сообщение
     *
     * @var integer
     */
    var $modified_id;
	/**
	 * Метод показа
	 *
	 * @var integer
	 */
    var $small;
	/**
	 * Юзер удаливший сообщение
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
	 * Бан сообщения 0 - не забанено, 1 - забанено
	 *
	 * @var boolean
	 */
    var $is_banned;
    
	/**
     * Выборка треда сообщения
     *
     * @todo Возвращает несуществующие переменные, надо посмотреть где вызывается функция и удалить
     * 
     * @param integer $item_id  ИД Темы
     * @param string  $error    Возвращает сообщение об ошибке
     * @return array 
     */
	function GetThread($item_id, &$error){
        global $DB;
		$curname = get_class($this);
		$sql = "SELECT id, fromuser_id, reply_to, post_time, msgtext, attach, title, uname, usurname, users.is_banned, login, is_pro_test, photo, role, modified, modified_id, deluser_id, deleted, small, payed
		FROM 
		(SELECT $curname.portf_id, $curname.fromuser_id, $curname.id, $curname.reply_to, $curname.post_time, $curname.msgtext, $curname.attach, $curname.title, $curname.modified,
		$curname.small, 1 as t FROM $curname WHERE item_id=?i
		UNION ALL 
		SELECT id, user_id, 0, NULL, NULL, descr, pict, name, NULL, NULL, 0
		FROM portfolio WHERE id=?i) as blg
		LEFT JOIN users ON fromuser_id=uid 
		LEFT JOIN (SELECT DISTINCT from_id, payed FROM orders 
             WHERE payed=true AND from_date<=now() AND from_date+to_date+COALESCE(freeze_to, '0')::interval >= now()
             AND orders.active='true'
             AND NOT (freeze_from_time IS NOT NULL AND NOW() >= freeze_from_time::date AND NOW() < freeze_to_time)) as pay
		 ON pay.from_id=uid
		 ORDER BY blg.t, reply_to, post_time";

        $this->thread = $DB->rows($sql, $item_id, $item_id);
		$error .= $DB->error;
		if ($error) $error = parse_db_error($error);
		 else {
		 	$this->msg_num = count($this->thread);
		 	if ($this->msg_num > 0) $this->SetVars(0);
		 }
		return array($name, $id_gr, $base);
	}
	
	/**
     * Возвращает следующее сообщение для вывода (через члены класса)
     *
     * @return integer		идентификатор родительского сообщения
     */
    function GetNext(){
            $ind = $this->SearchFirstChild($this->id);
            $i = 0; // на всякий случай
            while ($ind == -1 && $this->thread[$this->last_inx]['reply_to'] != 0) {
                    $last = $this->thread[$this->last_inx]['reply_to'];
                    $this->thread[$this->last_inx]['reply_to'] = -1;
                    //print_r($this->thread);
                    $ind = $this->SearchFirstChild($last);
                    $this->last_inx = $this->GetInxById($last);
                    $this->level--;
                    if ($i++ > 100) die("Ошибка! сообщите разработчикам!");
            }
            $this->level++;
            $this->SetVars($ind);
            $this->last_inx = $ind;
            $this->reply = $this->thread[$this->last_inx]['reply_to'];
            return $this->thread[$this->last_inx]['reply_to'];
    }
	
	/**
     * Возвращает индекс сообщения в массиве треда по идентификатору сообщения
     *
     * @param integer $id		идентификатор сообщения
     * @return integer			индекс сообщения в массиве треда
     */
    function GetInxById($id){
            $ret = 0;
            foreach ($this->thread as $ikey=>$node){
                    if ($node['id'] == $id) break;
                    else $ret++;
            }
            if ($ret > $this->msg_num-1) $ret = -1;
            return ($ret);
    }
    
	/**
     * Инициализирует члены класса в соответствии с текущим индексом сообщения в массиве треда
     *
     * @param integer $idx		индекс сообщения в массиве треда
     */
	function SetVars($idx){
		$node = $this->thread[$idx];
	 	$this->id = $node['id'];
		$this->fromuser_id = $node['fromuser_id'];
		$this->post_time = $node['post_time'];
		$this->msgtext = $node['msgtext'];
		$this->attach = $node['attach'];
		$this->title = $node['title'];
		$this->uname = $node['uname'];
		$this->is_banned = $node['is_banned'];
		$this->usurname = $node['usurname'];
		$this->login = $node['login'];
		$this->photo = $node['photo'];
		$this->is_pro_test = $node['is_pro_test'];
		$this->modified = $node['modified'];
    	$this->modified_id = $node['modified_id'];
    	$this->deleted = $node['deleted'];
    	$this->deluser_id = $node['deluser_id'];
		$this->small = $node['small'];
		$this->payed = $node['payed'];
		$this->cnt_role = (substr($node['role'], 0, 1)  == '0')? "frl" : "emp";
	}
	
    /**
	 * Добавить сообщение(комментарий)
	 *
	 * @param integer $fid    UID
	 * @param integer $reply  Идентификатор сообщения ответом на которое является данное сообщение
	 * @param integer $thread Тема
	 * @param string  $msg    Сообщение
	 * @param string  $name   Название сообщения
	 * @param mixed   $attach Вложения файлов
	 * @param char    $ip     ИП отправителя
	 * @param mixed   $error  Возвращает сообщение об ошибке
	 * @param mixed   $small  Тип Вида
	 * @return integer  ID нового сообщения
	 */
	function Add($fid, $reply, $thread, $msg, $name, $attach, $ip, &$error, $small){
        global $DB;
        if($attach && (!is_object($attach) || $attach->size))
            list($file, $alert, $error_flag) = $this->UploadFiles($attach, array('width'=>600,'height'=>1000, 'less' => 0));
		if (!$error_flag){
    	    $curname = get_class($this);
    		if ($file)
    			$sql = "INSERT INTO $curname (fromuser_id, reply_to, from_ip, post_time, item_id, msgtext, title, attach, small)
    			        VALUES ('$fid', '$reply', '$ip', NOW(), '$thread', '$msg', '$name', '".$file['f_name']."', '".$file['tn']."') RETURNING id";
    		else
    			$sql = "INSERT INTO $curname (fromuser_id, reply_to, from_ip, post_time, item_id, msgtext, title, attach, small)
    			        VALUES ('$fid', '$reply', '$ip', NOW(), '$thread', '$msg', '$name', NULL, '0') RETURNING id";
            $l_id = $DB->val($sql);
    		$error = $DB->error;
		}
		return $l_id;
	}
	
	/**
	 * Редактировать сообщение(комментарий)
	 *
	 * @param integer $fid      	 UID
	 * @param integer $edit_id  	 ИД Сообщения
	 * @param string  $msg      	 Текст Сообщения 
	 * @param string  $name     	 Название сообщения(Заголовок)
	 * @param string  $attach   	 Вложенные файлы
	 * @param string  $ip       	 ИП пользователя создавшего сообщения 
	 * @param mixed   $error    	 ОШибки
	 * @param boolean $mod      	 Модификации
	 * @param boolean $deleteattach  Удалить вложения
	 * @param boolean $olduserlogin  Старый логин
	 * @return integer $thread_id    Ид темы
 	 */
	function Edit($fid, $edit_id, $msg, $name, $attach, $ip, &$error, $mod = 1, $deleteattach=false, $olduserlogin=false){
        global $DB;
		$curname = get_class($this);
		list($file, $alert, $error_flag) = $this->UploadFiles($attach, array('width'=>600,'height'=>1000, 'less' => 0));
		$sql = "SELECT $curname.fromuser_id, $curname.attach, $curname.item_id, users.login FROM $curname LEFT JOIN users ON users.uid=$curname.fromuser_id  WHERE $curname.id = ?i";
        $res = $DB->query($sql, $edit_id);
        list($from_id, $last_attach, $thread_id, $oldlogin) = pg_fetch_row($res);
        if ($olduserlogin) { $oldlogin=$olduserlogin; }
        if ($from_id != $fid && $mod == 1) return ("Вы не можете править чужие сообщения!");
        $login = get_login($fid);
        if ($last_attach && ($attach || $deleteattach)) {
            $cfile = new CFile();
            $cfile->Delete("/users/".substr($oldlogin, 0, 2)."/".$oldlogin."/upload/",$last_attach); 
        }
        
        if (intval($attach->id) > 0 || $deleteattach) {
                $sql = "UPDATE $curname SET from_ip = '$ip', msgtext = '$msg', modified=NOW(), modified_id=$fid,
                        title = '$name', attach = '".($deleteattach ? 'null' : $file['f_name'])."', small='".($deleteattach ? 'null' : $file['tn'])."' WHERE id = ?i";
        } else {
                $sql = "UPDATE $curname SET from_ip = '$ip', msgtext = '$msg', modified=NOW(), modified_id=$fid,
                        title = '$name' WHERE id = ?i";
        }               
                
        $res = $DB->query($sql, $edit_id);
        
        $error = $DB->error;
		return $thread_id;
	}
	
	/**
	 * Полностью удалить сообщение
	 *
	 * @param integer $fid       ID Пользователя
	 * @param integer $id        ИД блога
	 * @param integer $group     Возвращает раздел для тем
	 * @param integer $base      Возвращает "базу" для темы
	 * @param integer $thread_id Возвращает идентификатор сообщения
	 * @param integer $page      Возвращает страницу
	 * @param string  $msg       Возвращает текст сообщения
	 * @param integer $mod       Имеет ли юзер права на удаление
	 * @return string Возвращает сообщение об ошибке
	 */
	function DeleteMsg($fid, $id, &$group, &$base, &$thread_id, &$page, &$msg, $mod = 1){
        global $DB;
		$curname = get_class($this);
		$sql = "SELECT fromuser_id, item_id, reply_to FROM $curname WHERE id=?i";
		$res = $DB->query($sql, $id);
		list( $from_id, $thread_id, $reply) = pg_fetch_row($res);
		if ($from_id != $fid) $addit = "(id = '$id' AND reply_to = (SELECT id FROM $curname WHERE id = '$reply' AND fromuser_id='$fid')) OR (id = '$id' AND portf_id = (SELECT id FROM portfolio WHERE id = '$thread_id' AND user_id='$fid'))";
		else $addit = "id = '$id' AND fromuser_id = '$fid'";
		if (!$mod) $addit = "id = '$id'";
		$sql = "DELETE FROM $curname WHERE ($addit) RETURNING attach, small";
		$res = $DB->query($sql);
        list($attach, $small) = pg_fetch_row($res);
        $error = $DB->error;
        if ($attach){
            $user = new users();
            $dir = $user->GetField($from_id, $error, 'login');
            $file = new CFile();
            $file->Delete(0,"users/".substr($dir, 0, 2)."/".$dir."/upload/", $attach);
            if ($small == 2) $file->Delete(0,"users/".substr($dir, 0, 2)."/".$dir."/upload/", "sm_".$attach);
        }	
		return $error;
	}
	
	/**
	 * Отметить блог, что он удален (для дальнейшего модерирования)
	 *
	 * @param integer $fid     ID Пользователя
	 * @param integer $edit_id ID Сообщения
	 * @param string  $ip      ИП того кто удаляет
	 * @param sring   $error   Возвращает сообщение об ошибке
	 * @param boolean $mod     Имеет ли юзер права на удаление
	 * @return integer ИД редактируемого сообщения
	 */
  	function MarkDeleteMsg($fid, $edit_id, $ip, &$error, $mod = 1){
        global $DB;
		$curname = get_class($this);
    $sql = "SELECT fromuser_id, item_id FROM $curname WHERE id = ?i";
    $res = $DB->query($sql, $edit_id);
    list($from_id, $item_id) = pg_fetch_row($res);
  	if (!$item_id)
    {
      $error = "Сообщение не найдено!";
      return $item_id;
    }
    $sql = "SELECT fromuser_id from $curname WHERE item_id = ?i AND reply_to ISNULL";
    $buser_id = $DB->val($sql, $item_id);
    if (($fid != $from_id && $mod == 1) && ($fid != $buser_id && $mod == 1))
    {
      $error = "Вы не можете удалять чужие сообщения!";
      return $item_id;
    }
    $sql = "UPDATE $curname SET deleted=NOW(), deluser_id=?i WHERE id = ?i";
    $res = $DB->query($sql, $fid, $edit_id);
    $error = $DB->error;
    return $item_id;
  }
	/**
	 * Возвращает имя группы для текущего класса
 	 *
	 * @return string
	 */
	function GetGroupName() {
		 return "Комментарии к работе:";
	}
	
	    /**
         * Возвращает индекс первого сообщения в массиве треда, являющегося комментарием к данному
         *
         * @param integer $id		идентификатор сообщения
         * @return integer			индекс первого сообщения в массиве треда, являющегося комментарием к данному
         */
        function SearchFirstChild($id){
                $ret = -1;
                $i = 0;
                foreach ($this->thread as $ikey=>$node){
                        if ($node['reply_to'] == $id) { $ret = $i; break;}
                        else $i++;
                }
                //print $id." : ".$ret;
                return ($ret);
        }
        
        /**
         * Возвращает индекс последнего сообщения в массиве треда, являющегося комментарием к данному
         *
         * @param integer $id		идентификатор сообщения
         * @return integer			индекс последнего сообщения в массиве треда, являющегося комментарием к данному
         */
        function SearchLastChildId($id){
                $count = 0;
                $tmpid=$id;
                foreach ($this->thread as $ikey=>$node){
                        if ($node['reply_to'] == $tmpid) { $id = $node['id'] ; $count++; }
                }
                //print $id." : ";
                if ($count) { $id=$this->SearchLastChildId($id); }
                return ($id);
        }
        
    /**
     * Закачка файлов прикрепленных к сообщению
     *
     * @param object $file            Вложенные файлы @link class CFile(); 
     * @param array  $max_image_size  Максимальный размер изображения [height=>1111, width=>1111]
     * @return array [object CFile, предупреждение, флаги ошибок]
     */
    function UploadFiles($file, $max_image_size){
        if ($file) {
            
            if(!($this instanceof  blogs_norisk)) {
                $file->max_size = 2097152;
            }
            
            $file->proportional = 1;
    
            $f_name = $file->MoveUploadedFile($_SESSION['login']."/upload");
            $ext = $file->getext();
            if (in_array($ext, $GLOBALS['graf_array'])) $is_image = TRUE; else $is_image = FALSE;
    
            $p_name = '';
            if (!isNulArray($file->error)) {
                $error_flag = 1;
                $alert[3] = "Файл не удовлетворяет условиям загрузки.";
            } else {
                if ($is_image && $ext != 'swf' && $ext != 'flv') {
                    if (!$file->image_size['width'] || !$file->image_size['height']) {
                        $error_flag = 1;
                        $alert[3] = 'Невозможно уменьшить картинку';
                    }
                    if (!$error_flag && ($file->image_size['width'] > $max_image_size['width'] ||
                         $file->image_size['height'] > $max_image_size['height'])) {
                        if (!$file->img_to_small("sm_".$f_name,$max_image_size)) {
                            $error_flag = 1;
                            $alert[3] = 'Невозможно уменьшить картинку.';
                        } else {
                            $tn = 2;
                            $p_name = "sm_$f_name";
                        }
                    } else {
                        $tn = 1;
                    }
                } else if ($ext == 'flv') {
                    $tn = 2;
                } else {
                    $tn = 0;
                }
            }
            $files['f_name'] = $f_name;
            $files['p_name'] = $p_name;
            $files['tn'] = $tn;
        }
        return array($files, $alert, $error_flag);
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
        $sql = "SELECT fromuser_id, id from blogs_norisk WHERE id = ?i AND deleted IS NOT NULL";
        $res = $DB->query($sql, $edit_id);
        if (! pg_num_rows($res)) {
            $err = "Нечего востанавливать";
            return 0;
        }
        list($from_id, $thread_id) = pg_fetch_row($res);
        if ($mod == 1) {
            $err = "Вы не можете востанавливать сообщения!";
            return $thread_id;
        }
        $sql = "UPDATE blogs_norisk SET deleted=NULL, deluser_id=NULL WHERE id = ?i";
        $res = $DB->query($sql, $edit_id);
        $error = $DB->error;
        return $thread_id;
    }

}
?>
