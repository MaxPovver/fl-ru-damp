<?php

/**
 * Класс для работы с менеджером
 *
 */
class manager
{
	/**
  	 * Максимальное количество вложений в сообщение
  	 *
  	 */
    const MAX_FILES = 1;
    
    /**
	 * Максимально допустимый размер вложенных в сообщение файлов
	 *
	 */
    const MAX_FILE_SIZE = 5242880;
    
    /**
	 * Добавление новой заявки, при добавлении заявки текст заявки отправляется на email 
	 * @see smail::SendManagerWork()
	 *
	 */
	function addMsg($text, $phone, $email, $fio, $uid, $files=false) 
    {
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
		
		$smail = new smail();
		
		$error = $smail->SendManagerWork($uid, $text, $phone, $email, $fio, $files);

        if ($error) { 
            return false;	
        }
        
        return true;
	}
	
	/**
	 * Отправляем заказ на звонок
	 *
	 * @param integer $uid             ИД Пользователя
	 * @param string  $fio             ФИО Пользователя
	 * @param string  $phone           Телефон для звонка
	 * @param string  $time_to_call    Удобное время звонка
	 * @return boolean
	 */
	function OrderCall($uid, $fio, $phone, $time_to_call, $email) {
	   require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
		
		$smail = new smail();
		
		$error = $smail->SendManagerOrderCall($uid, $fio, $phone, $time_to_call, $email);
		if ($error) return false;	
        
        return true;
	}
	
    /**
     * Возвращает информацию о менеджере
     *
     * @return array
     */
    function getManager() {
        return array(
            /*array(
                "name" => "Татьяна Ефремова",
//                "icq" => "553045731",
                "skype" => "manager.free-lance.ru",
                "phone" => "+7 (495) 646-81-29 (доб. *1)",
                "email" => "manager@free-lance.ru",
                "login" => "TatianaEfremova",
                "photo" => "/images/managers/tatyanae.jpg"
            ),*/
            array(
                "name" => "",
//                "icq" => "553045731",
                "skype" => "manager.free-lance.ru",
                "phone" => "+7 (495) 646-81-29 (доб. *1)",
                "email" => $GLOBALS['sManagerEmail'],
                "login" => "fmanager",
                "photo" => "/images/managers/photo.png"//"/images/managers/tatyana.jpg"
            )
        );
    }
	
	/**
	 * Возвращает запросы заявок
	 *
	 * @param integer $type  Тип заявки (1- отвеченные, 2 - не отвеченные, 3 - оконченные, 4 - неоконченные)
	 * @param string  $field Поле для сортировки
 	 * @param string  $d     Тип сортировки (ASC, DESC)
	 * @return array  Данные по выборке
	 */
	function getMsg($type = 0, $field="post_date", $d="DESC") {
	    global $DB;
		$sort = $field." ".$d; //"post_date DESC";
		switch($type) {
			case 1: $sql = "SELECT m.*, u.uname, u.login, u.usurname FROM my_manager as m,users as u WHERE m.status = 1 AND u.uid = m.uid ORDER BY $sort"; break;
			case 2: $sql = "SELECT m.*, u.uname, u.login, u.usurname FROM my_manager as m,users as u WHERE m.status = 0 AND u.uid = m.uid ORDER BY $sort"; break;
			case 3: $sql = "SELECT m.*, u.uname, u.login, u.usurname FROM my_manager as m,users as u WHERE m.completed = 1 AND u.uid = m.uid ORDER BY $sort"; break;
			case 4: $sql = "SELECT m.*, u.uname, u.login, u.usurname FROM my_manager as m,users as u WHERE m.completed = -1 AND u.uid = m.uid ORDER BY $sort"; break;
			default: $sql = "SELECT m.*, u.uname, u.login, u.usurname FROM my_manager as m,users as u WHERE u.uid = m.uid ORDER BY $sort"; break;
		}
		
		$ret = $DB->rows( $sql );
		
		if ( $DB->error ) {
        	$error = parse_db_error( $DB->error );
        	$ret   = null;
        }
        	
        return $ret;
	}
	
	/**
	 * Возвращает количества для верхнего меню
	 * 
	 * @return array [все заявки, отвеченные, не отвеченные, оконченные, отказанные]
	 */
	function getTotalTopMenu() {
		global $DB;
		
		$full = $DB->val( 'SELECT COUNT(*) FROM my_manager LIMIT 1 OFFSET 0' );
		
        if ( $DB->error ) $error = parse_db_error( $DB->error );
        
		$answ = $DB->val( 'SELECT COUNT(*) FROM my_manager WHERE status != 0 LIMIT 1 OFFSET 0' );
		
        if ( $DB->error ) $error = parse_db_error( $DB->error );
        
        $notansw = $DB->val( 'SELECT COUNT(*) FROM my_manager WHERE status = 0 LIMIT 1 OFFSET 0' );
        
        if ( $DB->error ) $error = parse_db_error( $DB->error );
        
        $compl = $DB->val( 'SELECT COUNT(*) FROM my_manager WHERE status = 1 LIMIT 1 OFFSET 0' );
        
        if ( $DB->error ) $error = parse_db_error( $DB->error );
     	
		$notcompl = $DB->val( 'SELECT COUNT(*) FROM my_manager WHERE status = -1 LIMIT 1 OFFSET 0' );
		
        if ( $DB->error ) $error = parse_db_error( $DB->error );
        
        return array((int)$full, (int)$answ, (int)$notansw, (int)$compl, (int)$notcompl); 
	}
	
	/**
	 * Возвращает поле сортировки
	 *
	 * @param string $sort Название сортировки
	 * @return string Поле для сортировки
	 */
	function getFieldSort($sort) {
		switch($sort) {
			case "name": $field = "uid"; break;
			case "status": $field = "status"; break;
			default: $field = "post_date"; break;
		}
		
		return $field;
	}
	
	/**
	 * Добавление ответа менеджера, по заявке
	 *
	 * @param string  $msg Сообщение
	 * @param integer $id ИД заявки
	 * @param integer $status Статус (принять(1)/отказать(0))
	 * @return boolean
	 */
	function addAnswerManager($msg, $id, $status=1) {
	    global $DB;
		$type = $status==1?'msg_green':'msg_red';
		        
        $DB->update( 'my_manager', array($type => $msg, 'status' => $status), 'id = ?i', $id );
        
        $error = $DB->error;
        
        $sql = "SELECT uid FROM my_manager as m WHERE m.id = ?i LIMIT 1 OFFSET 0";
		$uid = $DB->val( $sql, $id );
        
        if ( $DB->error ) $error = parse_db_error( $DB->error );
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
        
        $smail = new smail();
        
        $smail->sendManagerAnswer($uid, $msg);
        
        return true;	
	}
	
	/**
     * Закачать файл
     *
     * @param mixed $attach Массив закаченных файлов (см. класс CFile)
     * @param array $max_image_size Максимально разрешенные размеры файла [width=длинна,height=ширина]
     * @param string $login Логин того кто закачивает файл
     * @return array [файлы, ошибки(если есть), коды ошибок(если есть)]
     */
    function uploadFile($attach, $max_image_size, $login = 'test') {
    	if ($login == '') $login = $_SESSION['login'];
    	
        if ($attach) {
            foreach ($attach as $file) {
                $file->real_name = $file->name;
                $file->max_size = MAX_FILE_SIZE_MANAGER;
                $file->proportional = 1;
                $file->allowed_ext = array("gif", "jpeg", "png", "swf", "zip", "rar", "xls", "doc", "rtf", "pdf", "psd", "mp3", "txt", "jpg", "docx", "xlsx", "ppt", "pptx", "pub", "tiff", "eps");
                $f_name = $file->MoveUploadedFile($login . "/upload");
                
                $p_name = '';
                if (! isNulArray($file->error)) {
                    $error_flag = 1;
                    $alert[3] = "Файл не удовлетворяет условиям загрузки.";
                    break;
                } 
                $files[] = $file;
            }
        }    
        return array($files, $alert, $error_flag);
    }
}

?>