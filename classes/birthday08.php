<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Класс для работы разделом День Рождения Фриланса.
 * @deprecated
 * Устаревший. Новый birthday.php
 *
 */
class birthday08{

    function AddNew($name, $surname, $type, $mess, $uid){
        if (!birthday08::CheckUid($uid)){

            $sql = "INSERT INTO birthday08 (uid,name,surname,type,message) VALUES ('$uid', '$name', '$surname', ".intval($type).", '$mess')";
            pg_query(DBConnect(),$sql);
            return true;
        } else $error[1] = "Вы уже зарегистрированы";

        return $error;
    }

    function Update($name, $surname, $type, $mess, $uid){
        if (birthday08::CheckUid($uid)){

            $sql = "UPDATE birthday08 SET name='$name',surname='$surname',type=".intval($type).",message='$mess' where uid='$uid'";
           pg_query(DBConnect(),$sql);
            return true;
        }
        return false;
    }

    function GetInfo($uid){
        $sql = "SELECT id,uid,name,surname,type,message FROM birthday08 WHERE uid='".intval($uid)."' ";
        $res = @pg_query(DBConnect(),$sql);
        if (@pg_num_rows($res)) {return pg_fetch_assoc($res);  }
        else {  return false; }
    }

    function Delete($id) {
        $sql = "DELETE FROM birthday08 WHERE id='$id'";
       pg_query(DBConnect(),$sql);
        $error .= pg_errormessage();
        return $error;
    }

    function Swch($sw){
        $sql = "UPDATE birthday08 SET uid='$sw' WHERE id='0'";
        pg_query(DBConnect(),$sql);
        $error .= pg_errormessage();
        return $error;
    }

    function GetAllUinfo($uid){

        $sql = "SELECT users.role, users.uname, users.usurname, professions.name  FROM users LEFT JOIN freelancer ON freelancer.uid=users.uid LEFT JOIN professions ON professions.id=freelancer.spec WHERE users.uid='".$uid."'";
        $res = pg_query(DBConnect(),$sql);

        if (pg_num_rows($res)) {return pg_fetch_row($res);  }
        else {  return false; }
    }

    function GetAll(){

        $sql = "SELECT birthday08.id,birthday08.uid,birthday08.name,birthday08.surname,birthday08.type,birthday08.message, professions.name as prof, users.login,users.email FROM birthday08 LEFT JOIN freelancer ON freelancer.uid=birthday08.uid LEFT JOIN professions ON professions.id=freelancer.spec LEFT JOIN users ON users.uid=birthday08.uid ORDER BY birthday08.id ASC ";
        $res = pg_query(DBConnect(),$sql);

        if (pg_num_rows($res)) {return @pg_fetch_all($res);  }
        else {  return false; }
    }

    function Check($uid=0){
        $sql = "SELECT uid FROM birthday08 WHERE uid='".$uid."'";
        $res = pg_query(DBConnect(),$sql);
        list($ch) = pg_fetch_row($res);
        return $ch;
    }

    function CheckUid($uid){
        $sql = "SELECT uid FROM birthday08 WHERE uid='$uid'";
        $res = pg_query(DBConnect(),$sql);
        return pg_numrows($res);
    }

    function GetCount(){
        $sql = "SELECT id from birthday08";
        $res = pg_query(DBConnect(),$sql);
        if (pg_num_rows($res)) { return pg_num_rows($res); }
            else {  return false; }
    }
	
	function DelByOpid($uid, $opid){
		$sql = "DELETE FROM birthday08 WHERE uid='$uid'";
        pg_query(DBConnect(),$sql);
        $error .= pg_errormessage();
        return $error;
	}

}
?>
