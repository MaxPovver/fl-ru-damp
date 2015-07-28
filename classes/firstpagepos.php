<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Класс работы с платными иестами на главной странице
 *
 */
class firstpagepos{

	
    /**
     * Изменение позиции пользователя
     * @param integer $user               uid пользователя
     * @param integer $transaction_id     id транзакции
     * @param array $bids                 массив, в котором индекс элементов - id места размещения, значения - сумма для поднятия позиции
     *
     * @return integer                    id операции транзакции
     */
    function BidPlaces($user, $transaction_id, $bids, &$error){
        $bill_id = 0;
        if ($bids)
            foreach($bids as $prof => $sum){
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
            $account = new account();
            $error = $account->Buy($bill_id, $transaction_id, 21, $user, $prof, "", $sum, 0);
            if ($error!==0) return 0;
            global $DB;
            $sql = "UPDATE users_first_page SET psum=psum+? WHERE id IN (SELECT id FROM users_first_page 
                    WHERE user_id = ? AND from_date+to_date >= now() AND payed=true
                    AND ordered = true AND profession=? )";
            $DB->query( $sql, $sum, $user, $prof );
            $id = 1;
        }
        if ($bill_id) {
            //$account->commit_transaction($transaction_id, $user, $bill_id);
            $memBuff = new memBuff();
            $memBuff->flushGroup("firstpg");
        }
        return $id;
    }



	
	
    /**
     * Удаление заказа по id в account_operations
     * @see account::DelByOpid()
     *
     * @param integer $uid               uid пользователя
     * @param integer $opid              id операции в биллинге
     *
     * @return integer                   0
     */
    function DelByOpid($uid, $opid){
        global $DB;
        $sql = "SELECT ammount, descr FROM account_operations WHERE id=? AND billing_id=(SELECT id FROM account WHERE uid = ?)";
        $row = $DB->row( $sql, $opid, $uid );
        if (!$row || !($sum = $row['ammount']) || ($prof_id = $row['descr']) === "" || !$uid) die("+°шсър!");
        
        $sql = "UPDATE users_first_page SET psum=psum$sum WHERE id IN (SELECT id FROM users_first_page 
                WHERE user_id = ? AND from_date+to_date >= now() AND payed=true
                AND ordered = true AND profession=? )";
        $DB->query( $sql, $uid, $prof_id );
        return 0;
    }
    
}

?>