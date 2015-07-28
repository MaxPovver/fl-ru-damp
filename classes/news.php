<?
/**
 * Подключаем файл с основными функциями системы
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");

/**
 * Класс для работы с новостями
 *
 */
class news 
{
	/**
	 * Взять новости по определенной дате
	 *
	 * @param string $date   Дата
	 * @param string $error  Возвращает сообщение об ошибке
	 * @return array Новости
	 */
	function GetNews($date, &$error){
	    global $DB;
		$d = intval(substr($date,0,2));
		$m = intval(substr($date,2,2));
		$y = intval(substr($date,4,2));
		$num = intval(substr($date,6));
		$date = sprintf("20%02d-%02d-%02d", $y, $m, $d);
		$sql = "SELECT post_date, header, n_text FROM news WHERE post_date=? ORDER BY id DESC LIMIT 1 OFFSET ?i";
		
		$ret = $DB->row( $sql, $date, $num );
		
		if ($DB->error) $error = $DB->error;
        
		return (!$DB->error ? $ret : null);
	}
	
	/**
	 * Взять новость по его ИД
	 *
	 * @param integer $id    ИД новости
	 * @param string  $error Возвращает сообщение об ошибке
	 * @return array Новость
	 */
	function GetNewsById($id, &$error){
	    global $DB;
		$sql = "SELECT post_date, header, n_text FROM news WHERE id = '$id'";
		$ret = $DB->row( $sql, $date, $num );
		
		if ($DB->error) $error = $DB->error;
        
		return (!$DB->error ? $ret : null);
	}
	
	/**
	 * Взять последнюю новость
	 *
	 * @return array Новость
	 */
	function GetLastNews(){
		$sql = "SELECT post_date, header FROM news ORDER BY post_date DESC, id DESC LIMIT 1";
		$memBuff = new memBuff();
	  	$headers = $memBuff->getSql($error, $sql, 1800);
		if ($error) $error = parse_db_error($error);
			else $ret = $headers[0];
		return ($ret);
	}
	
	/**
	 * Взять все новости
	 *
	 * @param string $error Возвращает сообщение об ошибке
	 * @return array Все новости
	 */
	function GetAllNews(&$error){
	    global $DB;
		$sql = "SELECT post_date, header, id FROM news ORDER BY post_date DESC, id DESC";
		$ret = $DB->rows( $sql );
		
		if ($DB->error) $error = $DB->error;
        
		return (!$DB->error ? $ret : null);
	}
	
	/**
	 * Добавить новость
	 *
	 * @param string $post_date Дата новости
	 * @param string $header Заголовок новости
	 * @param string $n_text Текст новости
	 * @return string Сообщение об ошибке
	 */
	function Add( $post_date, $header, $n_text ) {
	    global $DB;
	    $data = compact( 'post_date', 'header', 'n_text' );
	    
		$DB->insert( 'news', $data );
		
		if ($DB->error) $error = parse_db_error( $DB->error );
		
		return ($error);
	}
	
	/**
	 * Редактирование новости
	 *
	 * @param string $date Дата новости
	 * @param string $name заголовок
	 * @param string $text Текст
	 * @param integer $id  Ид новости
	 * @return string Сообщение об ошибке
	 */
	function Edit( $post_date, $header, $n_text, $id ) {
	    global $DB;
	    $data = compact( 'post_date', 'header', 'n_text' );
	    
		$DB->update('news', $data, 'id = ?i', $id);
		
		if ($DB->error) $error = parse_db_error( $DB->error );
		
		return ($error);
	}
	
	/**
	 * Удалить новость
	 *
	 * @param integer $id Ид новости
	 * @return string Сообщение об ошибке
	 */
	function Delete($id){
	    global $DB;
	    
		$DB->query('DELETE FROM news WHERE id = ?', $id);
		
		if ($DB->error) $error = parse_db_error( $DB->error );
		
		return ($error);
	}
}

?>