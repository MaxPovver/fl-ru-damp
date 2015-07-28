<?
/**
 * Подключаем файл с основными функциями
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Класс для работы с конференцией 07 года
 *
 */
class confa07 
{
	/**
	 * Зарегать пользователя на конференцию
	 *
	 * @param string   $name    Имя пользователя
	 * @param string   $surname Фамилия пользователя
	 * @param integer  $type    Тип
	 * @param string   $mess    Сообщение
	 * @param integer  $uid     ИД Пользотеля
	 * @return string Ошибка если есть
	 */
    function AddNew($name, $surname, $type, $mess, $uid){
        if (!confa07::CheckUid($uid)) {
            $sql = "INSERT INTO confa07 (uid,name,surname,type,message) VALUES ('$uid', '$name', '$surname', ".intval($type).", '$mess')";
            pg_query(DBConnect(),$sql);
            return true;
        } else $error[1] = "Вы уже зарегистрированы";

        return $error;
    }
    
	/**
	 * Обновить запись о пользователе
	 *
	 * @param string   $name    Имя пользователя
	 * @param string   $surname Фамилия пользователя
	 * @param integer  $type    Тип
	 * @param string   $mess    Сообщение
	 * @param integer  $uid     ИД Пользотеля
	 * @return boolean true - если все прошло успешно, иначе false
	 */
    function Update($name, $surname, $type, $mess, $uid){
        if (confa07::CheckUid($uid)) {
            $sql = "UPDATE confa07 SET name='$name',surname='$surname',type=".intval($type).",message='$mess' where uid='$uid'";
           pg_query(DBConnect(),$sql);
            return true;
        }
        return false;
    }
    
	/**
	 * Информация о пользователе зарегистрированном на конференцию
	 *
	 * @param integer $uid ИД Пользователя
	 * @return array|boolan Информация выборки, либо false если нет информации
	 */
    function GetInfo($uid){
        $sql = "SELECT id,uid,name,surname,type,message FROM confa07 WHERE uid='".intval($uid)."' ";
        $res = @pg_query(DBConnect(),$sql);
        if (@pg_num_rows($res)) {return pg_fetch_assoc($res);  }
        else {  return false; }
    }
    
	/**
	 * Удалить конференцию
	 *
	 * @param integer $id ИД конференции
	 * @return string Сообщение об ошибке
	 */
    function Delete($id) {
        $sql = "DELETE FROM confa07 WHERE id='$id'";
       pg_query(DBConnect(),$sql);
        $error .= pg_errormessage();
        return $error;
    }
    
	/**
	 * Открывает и закрывает конфу
	 *
	 * @param integer $sw   0 - закрыта, 1 - открыта
	 * @return string Сообщение об ошибке
	 */
    function Swch($sw){
        $sql = "UPDATE confa07 SET uid='$sw' WHERE id='0'";
        pg_query(DBConnect(),$sql);
        $error .= pg_errormessage();
        return $error;
    }
    
	/**
	 * Полная информация о пользователе
	 *
	 * @param integer $uid ИД Пользователя
	 * @return array|boolan Информация выборки, либо false если нет информации
	 */
    function GetAllUinfo($uid){

        $sql = "SELECT users.role, users.uname, users.usurname, professions.name  FROM users LEFT JOIN freelancer ON fid=uid LEFT JOIN professions ON professions.id=freelancer.spec   WHERE uid='".$uid."'";
        $res = pg_query(DBConnect(),$sql);

        if (pg_num_rows($res)) {return pg_fetch_row($res);  }
        else {  return false; }
    }
    
	/**
	 * Взять все конференции
	 *
	 * @return array|boolan Информация выборки, либо false если нет информации
	 */
    function GetAll(){

        $sql = "SELECT confa07.id,confa07.uid,confa07.name,confa07.surname,confa07.type,confa07.message, professions.name as prof, users.login,users.email FROM confa07 LEFT JOIN freelancer ON fid=uid LEFT JOIN professions ON professions.id=freelancer.spec LEFT JOIN users ON users.uid=confa07.uid ORDER BY confa07.id ASC ";
        $res = pg_query(DBConnect(),$sql);

        if (pg_num_rows($res)) {return @pg_fetch_all($res);  }
        else {  return false; }
    }
    
	/**
	 * Проверка регистрации юзера в конферецию (зареган уже или нет)
	 * если uid = 0, то проверяет открыта конфа или нет.
	 *
	 * @param integer $uid ИД Польователя
	 * @return integer ID Пользователя если есть, иначе null
	 */
    function Check($uid=0){
        $sql = "SELECT uid FROM confa07 WHERE uid='".$uid."'";
        $res = pg_query(DBConnect(),$sql);
        list($ch) = pg_fetch_row($res);
        return $ch;
    }
    
	/**
	 * Проверка регистрации юзера в конферецию (зареган уже или нет)
	 *
	 * @param integer $uid ИД Польователя
	 * @return integer Количество колонок в выборке
	 */
    function CheckUid($uid){
        $sql = "SELECT uid FROM confa07 WHERE uid='$uid'";
        $res = pg_query(DBConnect(),$sql);
        return pg_numrows($res);
    }
    
	/**
	 * Общее число конференций
	 *
	 * @return integer|boolan Количество значений выборки, либо false если нет информации
	 */
    function GetCount(){
        $sql = "SELECT id from confa07";
        $res = pg_query(DBConnect(),$sql);
        if (pg_num_rows($res)) { return pg_num_rows($res); }
            else {  return false; }
    }
    
	/**
	 * Удалить пользователя с конференции
	 *
	 * @param integer $uid ИД пользователя
	 * @param  $opid -> Лишняя переменная необходимо удалить
 	 * @return string Сообщение об ошибке
	 */
	function DelByOpid($uid, $opid){
		$sql = "DELETE FROM confa07 WHERE uid='$uid'";
        pg_query(DBConnect(),$sql);
        $error .= pg_errormessage();
        return $error;
	}

}
?>
