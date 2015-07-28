<?
/**
 * Подключаем файл для работы с блогами
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs_proto.php");
/**
 * Класс для работы с сообщениями портфолио
 *
 */
class blogs_portf extends blogs_proto
{
	/**
     * Выборка треда сообщения
     *
     * @todo Функция возвращает пременные которые не существуют в самой функции, стоит проверить где вызывается и убрать.
     * 
     * @param integer $portf_id  ИД темы
     * @param string  $error     Возвращает сообщение об ошибке
     * @return array 
     */
	function GetThread($portf_id, &$error){
        global $DB;
		$curname = get_class($this);
		$sql = "SELECT id, fromuser_id, reply_to, post_time, msgtext, attach, title, uname, usurname, login, photo, role, modified, modified_id, deluser_id, deleted, small, payed
		FROM 
		(SELECT $curname.item_id, $curname.fromuser_id, $curname.id, $curname.reply_to, $curname.post_time, $curname.msgtext, $curname.attach, $curname.title, $curname.modified, $curname.modified_id, $curname.deleted, $curname.deluser_id, $curname.small, 1 as t FROM $curname 
		UNION ALL 
		SELECT id, user_id, 0, NULL, NULL, descr, pict, name, NULL, NULL, NULL, NULL, NULL, 0
		FROM portfolio WHERE id=?i) as blg
		LEFT JOIN users ON fromuser_id=uid 
		LEFT JOIN (SELECT DISTINCT from_id, payed FROM orders 
             WHERE payed=true AND from_date<=now() AND from_date+to_date+COALESCE(freeze_to, '0')::interval >= now() AND orders.active='true'
             AND NOT (freeze_from_time IS NOT NULL AND NOW() >= freeze_from_time::date AND NOW() < freeze_to_time)) as pay
		 ON pay.from_id=uid
		WHERE item_id=?i ORDER BY blg.t, reply_to, post_time";

        $this->thread = $DB->rows($sql, $portf_id, $portf_id);

		$error .= $DB->error;
		if ($error) $error = parse_db_error($error);
		 else {
		 	$this->msg_num = count($this->thread);
		 	if ($this->msg_num > 0) $this->SetVars(0);
		 }
		return array($name, $id_gr, 100);
	}
	/**
	 * Добавить комментарии к портфолио
	 *
	 * @param integer $fid    ID Пользователя
	 * @param integer $reply  Идентификатор сообщения ответом на которое является данное сообщение
	 * @param integer $thread Темы
	 * @param string  $msg    Сообщение
	 * @param string  $name   Название сообщения
	 * @param mixed   $attach Вложения файлов
	 * @param string  $ip     ИП отправителя
	 * @param mixed   $error  Возвращает сообщение об ошибке
	 * @param mixed   $small  Метод показа
	 * @return integer Возвращает ИД добавленного коментария
	 */
	function Add($fid, $reply, $thread, $msg, $name, $attach, $ip, &$error, $small){
        global $DB;
		$curname = get_class($this);
		$sql = "SELECT show_comms FROM portfolio WHERE portfolio.id = ?i";
		$portf_comments = $DB->val($sql, $thread);
        $error = $DB->error;
		if ($portf_comments != 't') {$error = "Пользователь запретил оставлять комментарии"; return 0;}
		return parent::Add($fid, $reply, $thread, $msg, $name, $attach, $ip, $error, $small);
	}
	
}
?>
