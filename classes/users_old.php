<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

/**
 * Класс для обработки смены логина пользователей
 *
 */
class users_old extends users {
	/**
	 * Добавить логин в таблицу "старых" логинов.
	 * Вызывается при платной смене логина юзера чтобы зарезервировать его старый логин
	 *
	 * @param string $login    Логин пользователя
	 * @return string Ошибка если есть
	 */
	function Add( $login ) {
	    global $DB;
	    
		$sql = "INSERT INTO users_old (login, is_active, subscr, active) VALUES ( ?, false, '0'::bit(".$GLOBALS['subscrsize']."), true)";
		$DB->query( $sql, $login );
		return $DB->error;
	}
}
?>