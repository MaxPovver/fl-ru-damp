<?
/**
 * Подключаем файл с основными функциями системы
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Класс для работы с подарками (переводами) 
 *
 */
class present 
{
	/**
	 * Получить информацию о подарке по id подарка (используется при переходе по ссылке "Вам подарок")
	 *
	 * @param integer $id
	 * @return array
	 */
	function GetGiftInfo($id){
	    global $DB;
		$sql = "SELECT op_date, op_name, ammount, comments, op_code, account_operations.id, users.uid, login, comments, descr, billing_id, present.to_uid FROM account_operations 
			LEFT JOIN account ON account.id=account_operations.billing_id LEFT JOIN present ON billing_to_id = account_operations.id 
			LEFT JOIN users ON users.uid=from_uid
			LEFT JOIN op_codes on op_code=op_codes.id WHERE present.id=?";
		
		return $DB->row( $sql, $id );
	}
	
	/**
	 * Получить id последнего подарка, подаренного юзеру по его UID и еще не просмотренного этим юзером (используется при переходе по ссылке "Вам подарок")
	 * заодно возвращает op_code подарка (идентификатор типа подарка)
	 *
	 * @param integer $op_code
	 * @param integer $uid
	 * @return integer
	 */
    function GetLastGift(&$op_code, $uid) {
        global $DB;
        $sql = "SELECT present.id, op_code
                FROM present, account_operations as acop
                WHERE recieved = false AND to_uid = ? AND billing_to_id = acop.id
                ORDER BY id DESC LIMIT 1";
        $res = $DB->row($sql, $uid);
        $out = $res['id'];
        $op_code = $res['op_code'];

        return $out;
    }

   /**
    * Получить информацию о последнем не просмотренном подарке по UID получателя
    *
    * @param integer $uid
    * @return integer
    */
    function GetLastGiftByUid($uid) {
        global $DB;
        // в ammount_from выводится сумма которую потратил даритель
        $sql = "SELECT present.id, acop.op_code, oc.op_name,
                    uname, usurname, login, role, sex, acop.ammount, acop.trs_sum, acop_from.ammount as ammount_from
                FROM present
                INNER JOIN account_operations as acop ON billing_to_id = acop.id
                LEFT JOIN account_operations as acop_from ON billing_from_id = acop_from.id
                LEFT JOIN users ON users.uid=present.from_uid
                LEFT JOIN op_codes oc ON acop.op_code=oc.id
                WHERE recieved = false AND to_uid = ?  
                ORDER BY present.id DESC";
        $res = $DB->rows($sql, $uid);
        if (!$res) {
            $res = array();
        }
        return $res;
    }
	
	/**
	 * Делает отметку о получении подарка(просмотре подарка) по id подарка и UID
	 *
	 * @param integer $gid
	 * @param integer $uid
	 * @return integer		возвращает всегда 0
	 */
	function SetGiftResv($gid, $uid){
        global $DB;
		$sql = "UPDATE present SET recieved = true WHERE id=? AND to_uid = ?";
		$DB->query( $sql, $gid, $uid );
		return 0;
	}
	
	/**
	 * Взять информацию об операциях о подарке (От кого или для кого был данный подарок)
	 * 
	 * @param integer $bill_id ИД биллинга
	 * @param integer $uid     ИД Юзера
	 * @return string
	 */
	function GetOrderInfo($bill_id, $uid){
	    global $DB;
		$sql = "SELECT uname, usurname, login, ammount FROM (SELECT ammount, CASE WHEN ammount < 0 THEN to_uid ELSE from_uid END as acc
				FROM account_operations, present 
				WHERE account_operations.id=? AND (billing_to_id = account_operations.id AND to_uid = ? OR
				billing_from_id = account_operations.id AND from_uid = ?)) as a LEFT JOIN users ON a.acc = uid";
		
		extract( $DB->row($sql, $bill_id, $uid, $uid) );
		
		if ($ammount < 0) $out = "Перевод для <a href=\"/users/".$login."\" class=\"blue\">".$uname." ".$usurname." [".$login."]</a>";
		else  $out = "Перевод от <a href=\"/users/".$login."\" class=\"blue\">".$uname." ".$usurname." [".$login."]</a>";
		return $out;
	}
	
	/**
	 * Удалить операцию о подарке
	 *
	 * @param integer $uid   ИД пользователя
	 * @param integer $opid  ИД операции
	 */
	function DelByOpId($uid, $opid){
	    global $DB;
		$sql = "SELECT id AS present_id, billing_from_id, billing_to_id, from_uid, to_uid FROM present WHERE (billing_from_id = ? AND from_uid = ?) 
			OR (billing_to_id = ? AND to_uid = ?)";
		
		extract( $DB->row($sql, $opid, $uid, $opid, $uid) );
		
		if ($billing_from_id == $opid){
			$sql = "DELETE FROM present WHERE id = ?;
			DELETE FROM account_operations WHERE id=? AND billing_id=(SELECT id FROM account WHERE uid = ?)";
			
			$DB->query( $sql, $present_id, $billing_to_id, $to_uid );
		}
		if ($billing_to_id == $opid){
			$sql = "DELETE FROM present WHERE id = ?;
			DELETE FROM account_operations WHERE id=? AND billing_id=(SELECT id FROM account WHERE uid = ?)";
			
			$DB->query( $sql, $present_id, $billing_from_id, $from_uid );
		}
		
		return $DB->error;
	}
	
	/**
	 * Заблокировать операцию
	 *
	 * @param integer $uid   ИД пользователя который производит блокировку
	 * @param integer $opid  ИД операции
	 * @return string Сообщение об ошибке
	 */
	function BlockedByOpId($uid, $opid) {
		// Проверяем было ли ранее блокировано данная операция
		global $DB;
		$sql  = "SELECT user_blocked FROM account_operations_blocked WHERE operation_id = ?";
		$usid = $DB->val( $sql, $opid );
		
		if($usid>0) return false;
		
		// Выясняем обе операции которые необходимо заблокировать
		$sql = "SELECT billing_from_id AS bill_fid, billing_to_id AS bill_tid, from_uid AS fuid, to_uid AS tuid 
            FROM present WHERE billing_from_id = ? OR billing_to_id = ?";
		
		extract( $DB->row($sql, $opid, $opid) );
		
		// Блокируем обе операции
		$data = array( 
    		array('operation_id' => $bill_fid, 'user_blocked'=> $uid),  
    		array('operation_id' => $bill_tid, 'user_blocked'=> $uid) 
		);
		
		return !!$DB->insert( 'account_operations_blocked', $data );
	}
	
	/**
	 * Разблокировать заблокированную операцию
	 *
	 * @param integer $uid   ИД пользователя который производит блокировку
	 * @param integer $opid  ИД операции
	 * @return string Сообщение об ошибке
	 */
	function unBlockedByOpId($uid, $opid) {
		// Выясняем обе операции которые необходимо разблокировать
		global $DB;
		$sql = "SELECT billing_from_id, billing_to_id, from_uid, to_uid FROM present WHERE billing_from_id = ?i OR billing_to_id = ?i";
		
		extract( $DB->row($sql, $opid, $opid) );
		
		if ( !$DB->error ) {
		    // Разблокируем обе операции
		    $sql = "DELETE FROM account_operations_blocked WHERE operation_id IN (?i, ?i)";
		    return !!$DB->query( $sql, $billing_from_id, $billing_to_id );
		}
		return false;;	
	}
	
	/**
	 * Информация о успешно прошедшей операции
	 * 
	 * @param array $data - Информация об операции
	 * @return array информация
	 */
	function getSuccessInfo($data) {
        $bill_col = 'billing_from_id';
        $user_col = 'to_uid';
        $pfx = 'пользователю';
	    if($data['ammount'] > 0) {
	        $bill_col = 'billing_to_id';
	        $user_col = 'from_uid';
            $pfx = 'от пользователя';
	    }
	    global $DB;
        $sql = "SELECT u.login, u.uname, u.usurname FROM present p JOIN users u ON u.uid = p.{$user_col} WHERE p.{$bill_col} = ?i";
        $user = $DB->row($sql, $data['id']);
        
        if( !$DB->error ) {
    	    $suc = array("date"  => $data['op_date'],
    	                 "name"  => $data['op_name'],
    	                 "descr" => $pfx.' <a href="/users/'.$user['login'].'/">'.$user['login'].'</a>',
    	                 "sum"   => abs($data['trs_sum']).' руб.'); 
    	}
	    return $suc;                        
	}
    
    function insertGift($insert) {
        global $DB;
        return $DB->insert('present', $insert);
    }
}
 ?>