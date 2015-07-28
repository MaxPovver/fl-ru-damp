<?
/**
 * Подключаем файл с основными функциями
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Класс для работы с избранным в блогами
 *
 */
class blogs_fav 
{	
	/**
	 * Изменить избранное в блогах
	 *
	 * @param  integer $thread_id ИД Темы
	 * @param  integer $uid       ID Пользователя
	 * @return integer Результат изменения: 0 - добавлен в избранное, 1 - удален из избранного.
	 */
	function ChangeFav( $thread_id, $uid ) {
	    global $DB;
		$sql = "SELECT * FROM blogs_fav WHERE (thread_id = ? AND user_id = ?)";
		$res = $DB->query( $sql, $thread_id, $uid );
		
		if ( pg_numrows($res) == 0 ) {
			$sql = "INSERT INTO blogs_fav (thread_id, user_id) VALUES (?, ?)";
			$ret = 1;
		}
		else {
			$sql = "DELETE FROM blogs_fav WHERE (thread_id = ? AND user_id = ?)";
			$ret = 0;
		}
		
		$res = $DB->query( $sql, $thread_id, $uid );
		
		return $ret;
	}
}
?>