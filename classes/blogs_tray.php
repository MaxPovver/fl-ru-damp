<?
/**
 * Подключаем файл для работы с блогами
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs_proto.php");
/**
 * Класс для работы с сообщениями какого то определенного типа
 * @deprecated Данный класс ушел в архив
 */
class blogs_tray extends blogs_proto
{
	/**
     * Выборка тем сообщений
     *
     * @todo Возвращает несуществующие переменные, проверить и удалить
     * 
     * @param integer $nrsk_id  ИД треда
     * @param mixed   $error    Возвращает сообщение об ошибке
     * @return array
     */
	function GetThread($nrsk_id, &$error){
        global $DB;
		$curname = get_class($this);
		$sql = "SELECT id, fromuser_id, reply_to, post_time, msgtext, attach, title, uname, usurname, login, photo, role, modified, modified_id, deluser_id, deleted, small, is_pro as payed
		FROM  $curname
		LEFT JOIN users ON fromuser_id=uid 
		WHERE item_id=?i ORDER BY reply_to, post_time";

        $this->thread = $DB->rows($sql, $nrsk_id);	
        $error .= $DB->error;

		if ($error) $error = parse_db_error($error);
		 else {
		 	$this->msg_num = count($this->thread);
		 	if ($this->msg_num > 0) $this->SetVars($this->msg_num-1);
		 }
		return array($name, $id_gr, 101);
	}
	/**
	 * Получение дополнительной информации о сообщении
	 *
	 * @param integer $msg_id  ИД сообщения
	 * @param string  $error   Возвращает сообщение об ошибке
	 * @return array Информация по выборке
	 */
	function GetMsgInfo($msg_id, &$error){
        global $DB;
		$curname = get_class($this);
        $sql = "SELECT * FROM $curname LEFT JOIN users ON users.uid=$curname.fromuser_id WHERE id=?i";

        $ret = $DB->row($sql, $msg_id);

        $error = $DB->error;
        if (!$error && $ret){
           //Определить $kind
			$ret['kind'] = $kind;
        }
        return $ret;
     }
}
?>
