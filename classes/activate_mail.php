<?
/**
 * Подключаем файл с основными функциями
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Класс для активации нового e-mail пользователя
 *
 */
class activate_mail
{
	/**
	 * Создать код активации для нового e-mail пользователя
	 *
	 * @param integer $uid		ID Пользователя (users.uid)
	 * @param string  $email	Новый e-mail юзера
	 * @param string  $error	Возвращает ошибку
	 * @return string|integer $code	Возвращает сгенерированный код активации, или 0 если не сгенерировалось
	 */
	function Create( $uid, $email, &$error ) {
		if ( $uid && $email ) {
			// Проверяем есть ли в базе емайл
			global $DB;
		    $sql = "SELECT uid FROM users WHERE lower(email) = ?";
            $res = $DB->query( $sql, strtolower($email) );
            if ( pg_num_rows($res) != 0 ) {
                $error = "Пользователь с таким e-mail уже существует!";
            }
            else {
    			$code = md5(crypt($email));
    			$DB->insert( 'activate_mail', array('user_id'=>$uid, 'code'=>$code, 'email'=>$email) );
    			$error .= pg_errormessage();
            }
		} 
		else {
		    $code = 0;
		}
		
		return ($code);
	}
	
	/**
	 * Активирует новый e-mail юзера по коду активации
	 *
	 * @param string $code	Код активации
	 * @return integer		1 - активировало, 0 - не активировало
	 */
	function Activate ($code) {
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
		global $DB;
		$sql = "SELECT user_id, email FROM activate_mail WHERE code = ?";
		$res = $DB->query( $sql, $code );
		list($fid, $email) = pg_fetch_row($res);
		if ($fid) {
			$usr = new users();
			$usr->email = $email;
			$usr->Update($fid, $res);
            $usr->SaveChangeEmailLog($fid,$email);
			$out = 1;
			$this->Delete($fid);
		} else $out = 0;
		return $out;
	}
	
	/**
	 * Удалить код активации
	 *
	 * @param integer $fid	ID Пользователя, у которого удаляем код активации
	 * @return string	Сообщение об ошибке
	 */
	function Delete( $fid ) {
	    global $DB;
		$sql = "DELETE FROM activate_mail WHERE user_id = ?";
		$DB->query( $sql, $fid );
		return pg_errormessage();
	}
}
?>
