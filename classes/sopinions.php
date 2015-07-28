<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Класс для работы с отзывами партнеров о нас
 */
class sopinions{
	/**
	 * Возвращает отзывы партнеров о нас
	 * 
	 * @return array
	 */
	function GetMsgs() {
		return $GLOBALS['DB']->rows("SELECT id, msgtext, sign, logo, link FROM sopinions ORDER BY post_time DESC");
	}
	
	/**
	 * Возвращает определенный отзыв 
	 *
	 * @param  int $msg_id ID отзыва
	 * @return array
	 */
	function GetMsgInfo( $msg_id ) {
		return $GLOBALS['DB']->row("SELECT msgtext, sign, link, logo, id FROM sopinions WHERE id = ?", $msg_id);
	}
	
	/**
	 * Добавить отзыв
	 *
	 * @param  string $msg Текст отзыва
	 * @param  string $sign Подпись
	 * @param  object $file CFile файл с логотипом
	 * @param  string $link Ссылка на сайт того кто оставил отзыв
	 * @param  string $from_ip IP адрес того кто писал отзыв
	 * @return array сообщения об ошибках (файл, база)
	 */
	function Add( $msg, $sign, $file, $link, $from_ip ) {
	    if ($file->tmp_name){
    	    $file->max_size = 1048576;
            $file->proportional = 1;
            $file->max_image_size = array('width'=>120, 'height'=>120, 'less'=>1);
            $file->resize = 1;
            $file->proportional = 1;
            $file->topfill = 1;
            $file->server_root = 1;
        
            $f_name = $file->MoveUploadedFile("about/opinions/");
    	    if (!isNulArray($file->error)) { $alert[3] = "Файл не удовлетворяет условиям загрузки"; $error_flag = 1;}
	    }
	    if (!$error_flag){
			$GLOBALS['DB']->insert('sopinions', array(
				'msgtext' => $msg,
				'sign'    => $sign,
				'logo'    => $f_name,
				'link'    => $link,
				'from_id' => $from_ip
			));
	    }
		return array($alert, $DB->error);
	}
	
	/**
	 * Удалить отзыв
	 *
	 * @param  int $msg ID отзыва
	 * @param  int $admin Является ли пользователь администратором 1 - да, 0 - нет (типа is_admin из stdf)
	 * @return array сообщение об ошибке
	 */
	function Del( $msg, $admin = 0 ) {
		if (!$admin) {
			return 0;
	    }
		if ($ret = $GLOBALS['DB']->val("DELETE FROM sopinions WHERE (id = ?) RETURNING logo", $msg)) {
		    $file = new CFile();
		    $file->Delete(0,"about/opinions/", $ret);
		}
		return $DB->error;
	}
	
	/**
	 * Изменить отзыв
	 *
	 * @param  string $msg Текст отзыва
	 * @param  string $sign Подпись
	 * @param  object $file CFile файл с логотипом
	 * @param  string $link Ссылка на сайт того кто оставил отзыв
	 * @param  string $from_ip IP адрес того кто писал отзыв
	 * @param  int $msgid ID отзыва
	 * @return array сообщения об ошибках (файл, база)
	 */
	function Edit( $msg, $sign, $file, $link, $from_ip, $msgid ) {
		if ($file) {
		    $file->max_size = 1048576;
            $file->proportional = 1;
            $file->max_image_size = array('width'=>120, 'height'=>120, 'less'=>1);
            $file->resize = 1;
            $file->proportional = 1;
            $file->topfill = 1;
            $file->server_root = 1;
        
            $f_name = $file->MoveUploadedFile("about/opinions/");
    	    if (!isNulArray($file->error)) { $alert[3] = "Файл не удовлетворяет условиям загрузки"; $error_flag = 1;}
		    if (!$error_flag) {
				$GLOBALS['DB']->query(
					"UPDATE sopinions SET msgtext = ?, sign = ?, logo = ?, link = ?, from_ip = ?, modified = NOW() WHERE id = ?",
					$msg, $sign, $f_name, $link, $from_id, $msgid
				);
			}
		} else {
			$GLOBALS['DB']->query(
				"UPDATE sopinions SET msgtext = ?, sign = ?, link = ?, from_ip = ?, modified = NOW() WHERE id=?",
				$msg, $sign, $link, $from_id, $msgid
			);
		}
		return array($alert, $DB->error);
	}
	
}
?>