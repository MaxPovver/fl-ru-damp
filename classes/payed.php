<?
/**
 * Подключаем файл с основными функциями
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Класс для работы с оплатой услуг
 *
 */
class payed
{
        static public $date_action_test_pro = array(20130210, 20130221);
        
        const PRICE_EMP_PRO = 899; // В рублях
        const PRICE_FRL_PRO = 899;
    
	/**
	 * Рассчет стоимости ПРО 
	 *
	 * @param boolean $get_all Взять все или не все
	 * @return integer стоимость
	 */
	 function GetProPrice($get_all=FALSE, $op_code = 48) {
	 	$base = 10;
			require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/op_codes.php");
			$op_codes = new op_codes();
			if($get_all) {
			  $prices = NULL;
	      if($rows = $op_codes->getCodes('132,131,47,48,49,50,51,76,114')) {
	        foreach($rows as $r)
	          $prices[$r['id']] = $r['sum'] * $base;
	      }
	      return $prices;
	    }
	    return $op_codes->GetField($op_code, $error, 'sum') * $base;
	}
        
        static function get_opcode_action_test_pro() {
            return ( (int) date('Ymd') >= self::$date_action_test_pro[0] && (int) date('Ymd') <= self::$date_action_test_pro[1] ) ? 114 : 47;
        }
	
	/**
	 * Оплата определенной услуги (Аккаунт ПРО)
	 *
	 * @param integer $fid              ИД Пользователя
	 * @param integer $transaction_id   ИД транзакции
	 * @param string  $time             Время 
	 * @param string  $comments         Комент заказа
	 * @param integer $tarif            Тариф заказа
	 * @return integer|array 0 - если ничего не вышло, иначе данные по заказу
	 */
	function SetOrderedTarif($fid, $transaction_id, $time, $comments="Все разделы", $tarif = 48, $promo_code = 0, &$error = null){
        global $DB;
	    //не позволим купить пробный PRO пользователям, которые уже когда-либо им пользовались
	    if ($this->IsUserWasPro($fid) && ($tarif == 47 || $tarif == 114) )
	    	return 0;
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
		$account = new account();
		$cost = $time * 10;
        if($tarif == 15) {
            $cost = $time * ( payed::PRICE_EMP_PRO );
        }
		$is_pro_test = 'false';
                if($tarif == 114 && self::get_opcode_action_test_pro() != 114) // проверяем не вышло ли время акции
                    return 0;
		if ($tarif == 47 || $tarif == 114) {
			$time = "7 days";
			$is_pro_test = 'true';
		}
		elseif ($tarif == 131)	{$time = "1 week";}
		elseif ($tarif == 132)	{$time = "1 day";}
		elseif (in_array($tarif, array(48, 163, 164))) {$time = "1 month";}
		elseif ($tarif == 49)	{$time = "3 month";}
		elseif ($tarif == 50)	{$time = "6 month";}
		elseif ($tarif == 51)	{$time = "12 month";}
	    elseif ($tarif == 15)	{$time = $time." month";}
        elseif ($tarif == 118)	{$time = "3 month";}
		elseif ($tarif == 119)	{$time = "6 month";}
		elseif ($tarif == 120)	{$time = "12 month";}
	    elseif ($tarif == 76)   {$time = $time." week";}
        
        
        //Отменяем заморозку и пересчитываем ПРО
        if ($tarif == 164) {
            $data = $this->ProLastById($fid, array($tarif));
            if ($data) {
                //Если есть или будет заморозка то отменяем
                if (!empty($data['freeze_from'])) {
                    $this->freezeProDeactivate($fid);
                }

                //Пересчитываем оставшееся PRO в PROFI
                $_interval = $this->getProfiDaysFromPro($fid);

                //Обновляем комментарий добавляя дополнительное время
                if ($_interval) {
                    $diff = abs(strtotime('now') - strtotime("+ {$_interval}"));
                    $days = floor($diff / (60*60*24));

                    if ($days > 0) {
                        $comments .= sprintf(" + %s %s компенсация за пересчет ПРО", $days, ending($days, 'день', 'дня', 'дней'));                       
                    }
                }
            }
        }
        
        $DB->start();
		$error = $account->Buy($bill_id, $transaction_id, $tarif, $fid, "Все разделы", $comments, $cost, 0, $promo_code);
        
        if (!$error) {
            
            $this->account_operation_id = $bill_id;
            
            //Если есть ПРО то конвертируем в PROFI и добавялем
            if ($tarif == 164) {
                if ($data) {
                    if ($_interval) {
                        $this->disableActivePro($fid, array($tarif));
                        $time = sprintf("%s + %s", $time, $_interval);
                    }
                }
            }

            $sql = "INSERT INTO orders (from_id, to_date, tarif, ordered, billing_id, payed) VALUES (?, ?, ?, true, ?, true);";
            if ($DB->query($sql, $fid, $time, $tarif, $bill_id)) {
                
                //@todo: Это не будет работать при внешних запросах - а сейчас это постоянно!
                if ($fid == $_SESSION['uid'] && !is_pro())
                    $_SESSION['is_pro_new'] = 't';
                
                $DB->commit();
                return true;
            }else {
                $DB->rollback();
            }
        } else {
            $DB->rollback();
        }
    return false;
	}
	
	/**
	 * Взять заявки по определенному тарифу
	 *
	 * @param integer $bill_id  	   ИД оплаты
	 * @param integer $gift_id  	   ИД подарка
	 * @param integer $gid      	   ИД подарка
	 * @param integer $fid      	   ИД пользователя
	 * @param integer $transaction_id  ИД транзакции
	 * @param integer $time            Время
	 * @param integer $comments        Комментарий оплаты
	 * @param integer $tarif           ИД Тарифа (см. таблицу op_codes)
	 * @return array Данные выборки
	 */
	function GiftOrderedTarif(&$bill_id, &$gift_id, $gid, $fid, $transaction_id, $time, $comments="Аккаунт PRO  в подарок", $tarif = 52){
        global $DB;
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
		$account = new account();
		$error = $account->Gift($bill_id, $gift_id, $transaction_id, $tarif, $fid, $gid, "Все разделы", $comments, 10*($tarif==52||$tarif==16 ? $time : 1));
		if(!$error) {
            $sql = "INSERT INTO orders (from_id, to_date, tarif, ordered, billing_id, payed) VALUES (?, ?, ?, true, ?, true)";
            if($DB->query($sql, $gid, (is_numeric($time)? "{$time} month": $time) , $tarif, $bill_id)) {
                $login = get_login($gid);
                
                if($gid==$_SESSION['uid'] && !is_pro()) 
                    $_SESSION['is_pro_new'] = 't';
                
                if ($gid == $_SESSION['uid']) {
                    $pro_last = payed::ProLast($login);
                    $_SESSION['pro_last'] = $pro_last['freeze_to'] ? false : $pro_last['cnt'];
                } else {
                    $session = new session();
                    $session->UpdateProEndingDate($login);
                }
                
                return true;
            }
        }
        return false;
	}
	
	
    /**
     * Возвращает данные по пользователям которые используют тестовые ПРО Аккаунты
     *
     * @return array Данные выборки
     */
    function GetProTestUsers() {
        global $DB;
        $sql = "
            SELECT users.*, orders.from_date, from_date+to_date+COALESCE(freeze_to, '0')::interval as to_date
            FROM orders 
            INNER JOIN users ON users.uid = orders.from_id
            WHERE ( -- payed=true AND orders.active=true AND -- deprecated #0021704
            ordered=true AND from_date < now() AND from_date+to_date+COALESCE(freeze_to, '0')::interval > now() AND is_pro_test = 't')
        ";
		return $DB->rows($sql);
    }
    
	/**
	 * Прием заявки и подтверждение оплаты (из админки)
	 *
	 * @deprecated 
	 * 
	 * @param integere $id      ИД Оплаты
	 * @param integer  $ammount Сумма оплаты
	 * @param string   $date    Дата оплаты
	 * @param string   $to_date Дата (по какое число действует услуга)
	 * @return string Сообщение об ошибке
	 */
	function SetPayed($id, $ammount, $date, $to_date){
        global $DB;
		$sql = "UPDATE orders SET payed=true, from_date= ? , to_date=(timestamp ? -timestamp ? ) WHERE id=?i; SELECT from_id, tarif FROM orders WHERE id=?i;";
		$res = $DB->query($sql, $date, $to_date, $date, $id, $id);
		list($uid, $tarif) = @pg_fetch_row($res);
		$sql = "SELECT billing_id FROM orders WHERE id=?i;";
		$billing_id = $DB->val($sql, $id);
		if ($billing_id){
			$sql = "DELETE FROM billing WHERE id=?i";
			$DB->query($sql, $billing_id);
		}
		$sql = "INSERT INTO billing (uid, ammount, op_code) VALUES (?i,?,?);
		SELECT id FROM billing ORDER BY id DESC LIMIT 1";
		$billing_id = $DB->val($sql, $uid, $ammount, $tarif);
        $error = $DB->error;
		$sql = "UPDATE orders SET billing_id=?i WHERE id = ?i";
		$DB->query($sql, $billing_id, $id);
		return $error;
	}
	
	/**
	 * Удалить заявку по ее ид.
	 *
	 * @param integer $id Ид заказа
	 * @return string Сообщение об ошибке
	 */
	function DeleteOrder($id){
        global $DB;
		$sql = "DELETE FROM orders WHERE id=?i";
		$DB->query($sql, $id);
		$error = $DB->error;
		return $error;
	}
	
	/**
     * Проверка, является ли пользователь ПРО по логину
     *
     * @param string  $login    Логин пользователя
     * @return integer ИД пользователя если он существует в таблице, иначе 0
     */
	function CheckPro($login){
        global $DB;
		$sql = "SELECT from_id FROM orders
          LEFT JOIN users ON from_id=uid
          WHERE login=? -- AND payed=true AND orders.active='true' -- depracated #0021704
          AND from_date<=now() AND from_date+to_date+COALESCE(freeze_to, '0')::interval >= now() 
            AND NOT (freeze_from_time IS NOT NULL AND NOW() >= freeze_from_time::date AND NOW() < freeze_to_time)";
		$id = $DB->val($sql,$login);
        $error = $DB->error;
        return ($id?$id:0);
    }

    /**
     * Проверка, явялется ли пользователь ПРО по ИД
     *
     * @param integer $uid ИД пользователя
     * @return boolean true - если является, иначе false
     */
    function checkProByUid($uid)
    {
        global $DB;
        $sql = "SELECT 1 FROM orders 
                WHERE from_id = ?i -- AND payed=true AND active='true' -- deprecated #0021704
                AND from_date<=now() AND from_date+to_date+COALESCE(freeze_to, '0')::interval >= now() 
                AND NOT (freeze_from_time IS NOT NULL AND NOW() >= freeze_from_time::date AND NOW() < freeze_to_time)";
        return (bool) $DB->val($sql, $uid);
    }

    /**
     * Пользователи ПРО у которых закончилась дата ПРО, проверка по логину
     *
     * @param string $login  Логин
     * @return integer
     */
    function ProLast($login) {
        global $DB;
        $sql = "SELECT from_date+to_date+COALESCE(freeze_to, '0')::interval as cnt,
                    fr.from_time as freeze_from,
                    fr.to_time-'1 day'::interval as freeze_to,
                    CASE WHEN NOW() >= fr.from_time AND NOW() < fr.to_time THEN 1 ELSE 0 END as is_freezed
                FROM orders
                LEFT JOIN users ON from_id=uid
                LEFT JOIN (
                    SELECT from_time, to_time, user_id, (to_time-from_time)::interval-'1 day'::interval _freeze_to
                    FROM orders_freezing_pro
                    WHERE to_time > NOW() 
                ) fr ON fr.user_id = uid
                WHERE login=? -- AND payed=true AND orders.active='true' -- deprecated #0021704
                    AND from_date+to_date+COALESCE(COALESCE(_freeze_to, freeze_to), '0')::interval > now()
                ORDER BY id DESC LIMIT 1";
        $res = $DB->query($sql, $login);
        $error = $DB->error;
        $result = null;
        if (!$error && pg_numrows($res)) {
            $result = pg_fetch_row($res, null, PGSQL_ASSOC);
        }
        return $result;
    }
	
	
    /**
     * Информация о наличии ПРО и его состоянии по ID пользователя
     * Аналог ProLast
     * 
     * @global DB $DB
     * @param type $uid - ID пользователя
     * @param type $_notIn - ID op_codes которые нужно исключить из рассмотрения
     * @return type
     */
    function ProLastById($uid, $_notIn = array()) 
    {
        global $DB;
        $sql = "SELECT 
                    from_date + to_date + COALESCE(freeze_to, '0')::interval as cnt,
                    fr.from_time as freeze_from,
                    fr.to_time-'1 day'::interval as freeze_to,
                    CASE WHEN NOW() >= fr.from_time AND NOW() < fr.to_time THEN 1 ELSE 0 END as is_freezed
                FROM orders
                LEFT JOIN (
                    SELECT 
                        from_time, 
                        to_time, 
                        user_id, 
                        (to_time-from_time)::interval-'1 day'::interval _freeze_to
                    FROM orders_freezing_pro
                    WHERE to_time > NOW() 
                ) fr ON fr.user_id = from_id
                WHERE 
                    from_id=?i 
                    ".(!empty($_notIn)?" AND tarif NOT IN(?l)":"")."
                    AND from_date + to_date + COALESCE(COALESCE(_freeze_to, freeze_to), '0')::interval > now()
                ORDER BY id DESC LIMIT 1";
        
        $res = $DB->query($sql, $uid, $_notIn);
        $error = $DB->error;
        $result = null;

        if (!$error && pg_numrows($res)) {
            $result = pg_fetch_row($res, null, PGSQL_ASSOC);
        }
        
        return $result;
    }    
    
    
    
    
    
    
    
    
    /**
     * Конвертация существующего срока ПРО в PROFI
     * 
     * @global DB $DB
     * @param type $uid
     */
    function getProfiDaysFromPro($uid)
    {
        global $DB;
        
        return $DB->val("
        SELECT 
            ((from_date + to_date - COALESCE(COALESCE(_freeze_to, freeze_to), '0')::interval) - NOW()) / 13
        FROM orders
        LEFT JOIN (
            SELECT 
                user_id, 
                (to_time - from_time)::interval _freeze_to
            FROM orders_freezing_pro
            WHERE to_time > NOW() 
        ) fr ON fr.user_id = from_id
        WHERE 
            from_id = ?i 
            AND tarif <> 164
            AND from_date + to_date + COALESCE(COALESCE(_freeze_to, freeze_to), '0')::interval > NOW()
        ORDER BY id DESC LIMIT 1    
        ", $uid);
    }








    /**
	 * Выборка пользователей которых необходимо предупредить об окончании ПРО услуги
	 *
	 * @see smail::SendWarnings();
	 * 
	 * @return array
	 */
	function GetWarnings(){
        global $DB;
        $sql = "
          SELECT u.uname, u.usurname, u.login, u.email, u.role, a.from_date+a.to_date+COALESCE(a.freeze_to, '0')::interval as to_date 
            FROM (
              SELECT *
                FROM orders
               WHERE (from_date, from_id) IN (SELECT MAX(from_date), from_id FROM orders GROUP BY from_id)
                 AND from_date+to_date+COALESCE(freeze_to, '0')::interval < now()+INTERVAL '3 DAY' 
                 AND from_date+to_date+COALESCE(freeze_to, '0')::interval > now()+INTERVAL '2 DAY' 
                 -- AND payed = true AND active = true -- deprecated
            ) a 
          INNER JOIN users u
              ON u.uid = a.from_id 
           WHERE u.is_banned = '0'
             AND u.is_pro = true
             AND u.is_pro_auto_prolong IS NOT TRUE
        ";
        $ret = $DB->rows($sql);
        return $ret;
	}
	
	/**
	 * Количество ПРО аккаунтов
	 *
	 * @return array [количество]
	 */
	function CountPro(){
        global $DB;
		$sql = "SELECT COUNT(*) FROM orders WHERE (payed=true AND orders.active=true AND ordered=true AND from_date < now() AND from_date+to_date+COALESCE(freeze_to, '0')::interval > now())";
		$ret['cur'] = $DB->val($sql);
		$sql = "SELECT COUNT(*) FROM (SELECT DISTINCT from_id FROM orders WHERE (payed=true AND ordered=true)) as t";
		$ret['all'] = $DB->val($sql);
		return $ret;
	}
	
	/**
	 * Информация о заявке по данным оплаты и ид. пользователя
	 *
	 * @param integer $bill_id ИД оплаты
	 * @param integer $uid     Ид пользователя
	 * @return string
	 */
	function GetOrderInfo($bill_id, $uid){
        global $DB;
        $out = '';
        $sql = "
          SELECT u.uname, u.usurname, u.login, ao.ammount, ao.op_code
            FROM account_operations ao
          LEFT JOIN
            present p
          INNER JOIN
            users u
              ON u.uid = CASE WHEN p.to_uid = ?i THEN p.from_uid ELSE p.to_uid END
              ON (p.billing_to_id = ao.id AND p.to_uid = ?i OR p.billing_from_id = ao.id AND p.from_uid = ?i)
           WHERE ao.id = ?i
        ";
		$res = $DB->row($sql, $uid, $uid, $uid, $bill_id);
        $uname = $res['uname'];
        $usurname = $res['usurname'];
        $login = $res['login'];
        $ammount = $res['ammount'];
        $op_code = $res['op_code'];
		if (in_array($op_code,array(16,52,66,67,68)) && $ammount < 0) {
			$out = "Аккаунт PRO для <a href=\"/users/".$login."\" class=\"blue\">".$uname." ".$usurname." [".$login."]</a>";
		} else {
			
			if (in_array($op_code,array(16,52,66,67,68))) 
				$out = "Аккаунт PRO от <a href=\"/users/".$login."\" class=\"blue\">".$uname." ".$usurname." [".$login."]</a><br>";
			$sql = "
                SELECT 
                    from_date, 
                    (from_date+to_date+COALESCE(freeze_to, '0')::interval) as to_date 
                FROM orders 
                WHERE 
                    billing_id = ?i 
                    AND from_id = ?i 
                    AND to_date > '0'::interval
                ";
            $row = $DB->row($sql, $bill_id, $uid);
			if ($row) {
    		    $out .= "С ".date("d.m.Y | H:i", strtotime($row['from_date']))." по ".date("d.m.Y | H:i", strtotime($row['to_date']));
    		} else {
                $out .= 'Пересчет срока действия аккаунта PRO/PROFI';
            }
		}
		return $out;
	}
	
	/**
	 * Удалить заявку по ид. пользователя и ид. биллинга 
	 *
	 * @param integer $uid ИД пользователя
	 * @param integer $opid ИД биллинга
	 * @return integer
	 */
	function DelByOpid($uid, $opid){
        global $DB;
        $sql = "DELETE FROM orders WHERE billing_id=?i AND from_id=?i";
        $DB->query($sql, $opid, $uid);

        self::UpdateProUsers();

        return 0;
	}
	
    
    /**
     * Занулить периоды для активного ПРО
     * 
     * @global DB $DB
     * @param type $uid
     * @param type $_notIn
     * @return type
     */
    function disableActivePro($uid, $_tarifNotIn = array()) 
    {
        global $DB;
        
        $DB->start();
        
        //Зануляем будущие ПРО
        $ok1 = $DB->query("
            UPDATE orders SET
                from_date = NOW(),
                to_date = '0'::interval
            WHERE
                from_id = ?i 
                ".(!empty($_tarifNotIn)?" AND tarif NOT IN(?l) ":"")." 
                AND from_date > NOW()                
        ", $uid, $_tarifNotIn);
        
        //Прерываем текущее ПРО
        $ok2 = $DB->query("
            UPDATE orders SET
                to_date = (NOW() - from_date)::interval
            WHERE 
                from_id = ?i 
                ".(!empty($_tarifNotIn)?" AND tarif NOT IN(?l) ":"")." 
                AND NOW() BETWEEN from_date AND from_date + to_date
        ", $uid, $_tarifNotIn);
        
        if(!$ok1 || !$ok2) {
            $DB->rollback();
        }
        
        $DB->commit();
        
        return true;
    }


    
    /**
	 * Обновить ПРО пользователей у которых вышло время пользования услугой
	 *
	 * @return integer
	 */
	function UpdateProUsers() 
    {
        global $DB;

		$sql = "UPDATE users SET 
                    is_pro = false, 
                    is_pro_test = false, 
                    is_profi = false
		        WHERE 
                   is_pro = true 
                   AND (
                     uid NOT IN (SELECT from_id FROM pro_orders_id_uid)
                     OR uid IN (SELECT user_id FROM orders_freezing_pro WHERE now() >= from_time AND now() < to_time)
                   );";
        
		$DB->squery($sql);

		return 0;
	}
	
	
	/**
	 * Проверяет, нужно ли установить флаг is_new_pro, действующий с 25.11.2010:
	 * 1. Находим покупку о ПРО, используемом в данный момент.
	 * 2. Смотрим, если дата покупки позже или равна 25-му, то устанавливаем флаг.
	 *
	 * @param integer $uid   ид. юзера.
	 * @return boolean
	 */
	function checkNewPro($uid) {
        global $DB;
	    $sql = "
	      UPDATE freelancer f
	         SET is_pro_new = true
	        FROM orders o
	       WHERE f.uid = o.from_id
	         AND f.is_pro_new = false
	         AND o.from_date <= now()
	         AND o.from_date + o.to_date + COALESCE(freeze_to, '0')::interval >= now()
	         AND o.posted >= '2010-11-25'::date
	         AND o.from_id = {$uid}
	    ";
	    return !!$DB->query($sql, $uid);
	}
	
	
	/**
	 * Рассылка уведомление о том что скоро ПРО закончится
	 *
	 * @return integer
	 */
    function AlertPROEnding() {
        global $DB;
    	/**
    	 * Файл для работы с почтой и рассылкой
    	 */
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
        $mail = new smail();
        $sql = "SELECT pro_users.uid, pro_users.date_end FROM (
                    SELECT uid, MAX(from_date+to_date+COALESCE(freeze_to, '0')::interval) AS date_end
                        FROM orders 
                        LEFT JOIN users ON from_id=uid 
                        WHERE users.is_banned = '0' AND users.is_pro='true' AND users.is_pro_auto_prolong='t' -- AND orders.payed='true' AND orders.active='true' -- deprecated #0021704
                        AND from_date+to_date+COALESCE(freeze_to, '0')::interval > NOW() GROUP BY uid
                    ) pro_users
                WHERE pro_users.date_end>(NOW()+'1 day') AND pro_users.date_end<=(NOW()+'1 day 1 hour');
                ";
        $qusers = $DB->rows($sql);
        if($qusers) {
            foreach($qusers as $user) {
                $mail->PROEnding( $user['uid'], $user['date_end'] );
            }
        }
        return 0;
    }

    /**
     * Уведомления за 3 дня до кончания ПРО у тех у кого не включено автопродление
     *
     * @return bool
     */
    public function getPROEnding($auto = true, $days = 3) {
        global $DB;
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");

        foreach( array('freelancer', 'employer') as $tbl ) {
            $sql = "
                SELECT
                  pro_users.uid, pro_users.date_end, u.email, u.login, a.id as acc_id, substr(u.subscr::text,16,1) = '1' as bill_subscribe,
                  u.uname, u.usurname, u.subscr, date_part('days', date_end - NOW() ) as days_left
                FROM (
                    SELECT uid, MAX(from_date+to_date+COALESCE(freeze_to, '0')::interval) AS date_end
                        FROM orders
                        INNER JOIN {$tbl} u ON from_id=uid
                    WHERE
                        u.is_banned = '0'
                        AND u.is_pro='true'
                        AND u.is_pro_auto_prolong=?
                        -- AND orders.payed='true' AND orders.active='true' -- deprecated #0021704
                        AND from_date+to_date+COALESCE(freeze_to, '0')::interval > NOW()
                    GROUP BY uid
                ) as pro_users
                INNER JOIN {$tbl} u ON u.uid = pro_users.uid
                INNER JOIN account a ON a.uid = u.uid
                WHERE (pro_users.date_end>(NOW()+'{$days} day') AND pro_users.date_end<=(NOW()+'{$days} day 1 hour'));";
            $result[$tbl] = $DB->rows($sql, $auto);
            if(!$result[$tbl]) unset($result[$tbl]); // Если пустой результат
        }
        
        if($result) {
            foreach($result as $role => $users) {
                $mail = new smail();
                $mail->remindTimeleftPRO($users, $days);
                
                /*
                 @todo: автопродления пока нет, все уведомляем обычно
                if(!$auto) {
                    $mail->remindTimeleftPRO($users, $days);
                } else {
                    $mail->remindAutoprolongPRO($users, $role, $days);
                }
                */
                
//                $mail = new smail2();
//                $mail->sendPROEnding(( $role == 'freelancer' ? 'FRL' : 'EMP' ), $users);
            }
            return true;
        }
        return false;
    }

    /**
     * ищет пользователей у которых до окончания PRO остаются одни сутки и включено автопродление
     * найденным отправляет соответствующее письмо
     */
    function checkAutoPro() {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/bar_notify.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
        global $DB;
        foreach( array('freelancer', 'employer') as $tbl ) {
            // Если в первый раз услугу не получилось купить, пытаемся купить еще раз за час до завершения услуги
            $sql = "
                SELECT pro_users.uid, pro_users.date_end, a.id as acc_id, u.email, u.login, u.uname, u.usurname, u.subscr, substr(u.subscr::text,16,1) = '1' as bill_subscribe,
                (CASE WHEN (pro_users.date_end > (NOW() + '1 hour') AND pro_users.date_end <= (NOW() + '2 hour')) = true
                    THEN 2 -- вторая попытка продления
                    ELSE 1 -- первая попытка продления
                END) as attempt
                FROM (
                    SELECT uid, MAX(from_date+to_date+COALESCE(freeze_to, '0')::interval) AS date_end
                    FROM orders
                    INNER JOIN {$tbl} u ON from_id=uid
                    WHERE u.is_banned = '0'
                        AND u.is_pro = TRUE
                        AND u.is_pro_auto_prolong = TRUE
                        AND from_date + to_date + COALESCE(freeze_to, '0')::interval > NOW()
                    GROUP BY uid
                ) as pro_users
                INNER JOIN {$tbl} u
                    ON u.uid = pro_users.uid AND u.is_banned = B'0'
                INNER JOIN account a ON a.uid = u.uid
                WHERE
                  ( pro_users.date_end > (NOW() + '1 day') AND pro_users.date_end <= (NOW() + '1 day 1 hour') )
                    OR
                  ( pro_users.date_end > (NOW() + '1 hour') AND pro_users.date_end <= (NOW() + '2 hour') )
                ;";

            $result[$tbl] = $DB->rows($sql);
            if(!$result[$tbl]) unset($result[$tbl]); // Если пустой результат
        }

        if($result) {
            foreach($result as $role => $users) {
                $op_code = $role === 'freelancer' ? 48 : 15;
                $price   = $role === 'freelancer' ? self::PRICE_FRL_PRO : self::PRICE_EMP_PRO;
                $mail    = new smail();
                foreach($users as $user) {
                    $billing = new billing($user['uid']);
                    $queueID = $billing->create($op_code, 1);
                    if ($queueID) {
                        //Проталкиваем дальше автопродление для оплаты
                        $billing->preparePayments($price, false, array($queueID));
                        $complete = billing::autoPayed($billing, $price);

                        $barNotify = new bar_notify($user['uid']);
                        if($complete) {
                            $barNotify->addNotify('bill', '', 'Услуга успешно продлена.');
                            $mail->successAutoprolong(array('user' => $user, 'sum_cost' => $price), 'pro');
                        } else if($user['attempt'] == 1) { // Первая попытка
                            $barNotify->addNotify('bill', '', 'Ошибка списания, услуга не продлена.');
                            $mail->attemptAutoprolong(array('user' => $user, 'sum_cost' => $price), 'pro');
                        } else { // Вторая попытка не удачная
                            $barNotify->addNotify('bill', '', 'Ошибка списания, автопродление отключено.');
                            $mail->failAutoprolong(array('user' => $user, 'sum_cost' => $price), 'pro');
                        }
                    };
                }

//                $mail = new smail();
//                $mail->sendAutoPROEnding(( $role == 'freelancer' ? 'FRL' : 'EMP' ), $users);
            }
            return true;
        }
    }

    /**
     * для имитация окончания PRO у пользователя
     *
     * @param $attempt текущая попытка продления
     */
    function checkAutoProTest($login, $attempt = 1) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/bar_notify.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wallet/wallet.php");
        global $DB;

        if($attempt <= 0) {
            $attempt = 1;
        }

        $sql = "
            SELECT users.uid, a.id as acc_id, email, login, uname, usurname, subscr, role, substr(subscr::text,16,1) = '1' as bill_subscribe
            FROM users
            INNER JOIN account a ON a.uid = users.uid
            WHERE users.login = ?";
        $user = $DB->row($sql, $login);
        if (!$user) {
            return;
        }
        $user['date_end'] = date('Y-m-d H:i:s', time());
        $op_code = !is_emp($user['role']) ? 48 : 15;
        $price   = !is_emp($user['role']) ? self::PRICE_FRL_PRO : self::PRICE_EMP_PRO;

        $billing = new billing($user['uid']);
        $queueID = $billing->create($op_code, 1);
        if (!$queueID) {
            return;
        }
        //Проталкиваем дальше автопродление для оплаты
        $billing->preparePayments($price, false, array($queueID));
        $complete = billing::autoPayed($billing, $price);

        // @todo отключать ли автопродление или нет при второй попытке ( по идее на систему никак влиять не будет )
        // Автопродление не будет куплено уведомляем об этом пользователя
        $barNotify = new bar_notify($user['uid']);
        $mail = new smail();
        if($complete) {
            $barNotify->addNotify('bill', '', 'Услуга успешно продлена.');
            $mail->successAutoprolong(array('user' => $user, 'sum_cost' => $price), 'pro');
            //$mail->sendAutoPROEnding(( $user['role'] == 'freelancer' ? 'FRL' : 'EMP' ), array($user));
        } else if($attempt == 1) {
            $barNotify->addNotify('bill', '', 'Ошибка списания, услуга не продлена.');
            $mail->attemptAutoprolong(array('user' => $user, 'sum_cost' => $price), 'pro');
        } else {
            $barNotify->addNotify('bill', '', 'Ошибка списания, автопродление отключено.');
            $mail->failAutoprolong(array('user' => $user, 'sum_cost' => $price), 'pro');
        }
    }


	
	/**
	* Проверяет пользовался ли пользователь платными услугами (ПРО, Первая страница) (см. таблицу op_codes) и если пользовался то возвращаем ид заявки
	* 
	* @param integer $uid id юзера
	* @return integer ИД оплаты, иначе 0
	*/
	function IsUserWasPro($uid)
	{
        global $DB;
		$uid = intval($uid);

		$sql = "SELECT id FROM orders WHERE from_id = ?i AND ordered = '1' -- AND payed = 't' -- deprecated #0021704
                    AND tarif IN (1,2,3,4,5,6,15,16,28,35,42,47,114,48,49,50,51,52,76) LIMIT 1";
        $id = $DB->val($sql, $uid);
        return ($id?$id:0);
	}
	
    
    /**
     * Покупал ли пользователь ПРО за последнии 90 дней
     * 
     * @global DB $DB
     * @param type $uid
     * @return type
     */
    static public function isWasPro($uid)
    {
        global $DB;
        
        $op_codes = array();//array(163, 48, 49, 50, 51);
        
        $sql = "
            SELECT 1 
            FROM orders 
            WHERE 
                from_id = ?i 
                AND ordered = true 
                AND from_date > NOW() - '90 days'::interval 
                " . (!empty($op_codes)?" AND tarif IN(?l)":"") . "
            LIMIT 1
        ";
        
        return (bool)$DB->val($sql, $uid, $op_codes);
    }


    /**
	 * Админская функция для раздачи ПРО аккаунта
	 *
	 * @param integer $fid            Кому (ИД Пользователя)
	 * @param integer $transaction_id Ид транзакции
	 * @param string  $time           Время
	 * @param string  $comments       Комент
	 * @param integer $tarif          Тариф (op_codes)
	 * @return integer 0 - если не получилось, 1 - если все прошло успешно
	 */
	function AdminAddPRO($fid, $transaction_id, $time, $comments="Аккаунт PRO. Возмещение платного сервиса", $tarif = 63){
        global $DB;
		require_once(ABS_PATH . "/classes/account.php");
		$account = new account();
		$error = $account->Buy($bill_id, $transaction_id, $tarif, $fid, $comments, $comments);
		if(!$error) {
            $sql = "INSERT INTO orders (from_id, to_date, tarif, ordered, billing_id, payed) VALUES (?, ?, ?, true, ?, true);
                    UPDATE users SET is_pro = true, is_pro_test = false WHERE uid=?;";
            $res = $DB->query($sql, $fid, $time, $tarif, $bill_id, $fid);
        }
        return !!$res;
	}
	
	/**
	 * Информация о успешно прошедшей операции
	 * 
	 * @param array $data - Информация об операции
	 * @return array информация
	 */
	function getSuccessInfo($data) {
        global $DB;
	    if(in_array($data['op_code'], array(52,66,67,68))) {
    		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/present.php");
    		$present = new present();
    		return $present->getSuccessInfo($data);
	    }
	    $uid = get_uid(false);
        $sql = "SELECT (o.from_date + o.to_date)::date as to_date FROM users u, orders o WHERE u.uid = ?i AND o.from_id = u.uid AND o.billing_id = ?i";
        $pro = $DB->row($sql, $uid, $data['id']); 
	    $date = date('d.m.Y', strtotime($pro['to_date']));
	           
	    $data['ammount'] = abs($data['ammount']);
	    $suc = array("date"  => $data['op_date'],
	                 "name"  => "Аккаунт \"ПРО\" (Срок действия — {$date})",
	                 "descr" => "",
	                 "sum"   => "{$data['ammount']} руб."); 
	    return $suc;                        
	}
	
	/**
	 * Поднимаем рейтиинг
	 *
	 * @param integer $fid                ИД Кому поднимаем
	 * @param integer $transaction_id     ИД Транзакции     
	 * @param integer $time               Время
	 * @param string  $comments           Дополнительный комментарий
	 * @param integer $tarif              Тариф
	 * @param integer $ammount            Kоличество товара
	 * @param integer $commit             Завершать ли транзакцию после этой операции.
	 * @return unknown
	 */
	function getUpRating($fid, $sum, $transaction_id, $time, $comments="Поднятие рейтинга за FM", $tarif = 75, $ammount=1, $commit = 1) {
	    require_once(ABS_PATH . "/classes/account.php");
		$account = new account();
		$error = $account->Buy($bill_id, $transaction_id, $tarif, $fid, $comments, $comments, $ammount, $commit);
		if ($error!==0) return 1;
		return 0;
	}

    /**
     * Возвращает последнюю запись заморозки ПРО
     *
     * @param integer $uid ИД пользователя
     */
    function getLastFreeze($uid) {
        global $DB;
        $uid = intval($uid);

        $sql = "SELECT *, from_time::date AS from_time_date, 
                       (to_time - '1 day'::interval)::date AS to_time_date
                FROM orders_freezing_pro WHERE user_id = ?i
                ORDER BY id DESC LIMIT 1";
        
        $res = $DB->row($sql, $uid);
        $error = $DB->error;

        if (!$error && $res) {
            $fz = self::getFreezedDaysCnt($uid);
            $res['freezed_days'] = 0;
            $res['freezed_cnt'] = 0;
            if ($fz) {
                $res['freezed_days'] = $fz['days'];
                $res['freezed_cnt'] = $fz['cnt'];
            }
            return $res;
        } else {
            return FALSE;
        }
    }

    /**
     * Добавляет новую запись с данными по заморозке
     *
     * @param integer $uid ИД пользователя
     * @param string $from_date Дата начала заморозки
     * @param string $to_date Дата окончания заморозки
     * @return boolean
     */
    function freezePro($uid, $from_date, $to_date) {
        global $DB;
        $uid = intval($uid);
        
        $sql = "INSERT INTO orders_freezing_pro (user_id, from_time, to_time, order_id)
                VALUES (?, ? ::timestamp, (? ::timestamp), 1)";

        $res = $DB->query($sql, $uid, $from_date, $to_date);

        if(!$res) return false;

        return true;
    }

    /**
     * Отмена заморозки
     *
     * @param integer $uid ИД пользователя
     * @param integer $freeze_id ИД заморозки
     * @return boolean
     */
    function freezeProCancel($uid, $freeze_id) {
        global $DB;
        $uid = intval($uid);
        $freeze_id = intval($freeze_id);

        $sql = "DELETE FROM orders_freezing_pro
                WHERE id = $freeze_id AND user_id = $uid";

        $res = $DB->query($sql, $freeze_id, $uid);

        if(!$res) return false;

        return true;
    }

    /**
     * Досрочная разморозка
     *
     * @param integer $uid ИД пользователя
     * @param integer $freeze_id ИД заморозки
     * @return boolean
     */
    function freezeProStop($uid, $freeze_id) {
        global $DB;
        $uid = intval($uid);
        $freeze_id = intval($freeze_id);

        $sql = "UPDATE orders_freezing_pro SET to_time = NOW(), stop_time = NOW()
                WHERE id = $freeze_id AND user_id = $uid";

        $res = $DB->query($sql, $freeze_id, $uid);

        if(!$res) return false;

        return true;
    }

    
    /**
     * Деактивация текущей или будущей заморозки ПРО
     * 
     * @global DB $DB
     * @param type $uid
     * @return boolean
     */
    function freezeProDeactivate($uid)
    {
        global $DB;
        $uid = intval($uid);
        
        $data = $DB->row("
            SELECT 
                id,
                (from_time <= NOW() AND to_time > NOW()) AS is_now
            FROM orders_freezing_pro 
            WHERE user_id = ?i
            ORDER BY id DESC LIMIT 1            
        ", $uid);
        
        if($data) {
            return $data['is_now'] == 't'?
                   $this->freezeProStop($uid, $data['id']):
                   $this->freezeProCancel($uid, $data['id']);
        } 
        
        return false;
    }




    /**
     * Обновляет ПРО пользователей, у которых закончился период заморозки.
     * @global DB $DB
     * @return boolean
     */
    function freezeUpdateProUsers() {
        global $DB;
        $sql = "SELECT id FROM settings WHERE module = 'unfreeze' AND variable = 'lastdate' AND value::date = NOW()::date";

        $res = $DB->row($sql);
        if($res) return false;

        $sql = "UPDATE orders_freezing_pro SET stop_time = to_time
                              WHERE to_time <= NOW()::date AND stop_time IS NULL;";
        $sql .= "UPDATE settings SET value = NOW() WHERE module = 'unfreeze' AND variable = 'lastdate';";
        
        $res = $DB->squery($sql);
        if(!$res) return false;

        return true;
    }


    /**
     * Проверяет статус заморозки ПРО.
     *
     * @return boolean
     */
    function isProFreezed($uid) {
        global $DB;
        $uid = intval($uid);

        $sql = "SELECT *, from_time::date AS from_time_date,
                       (to_time - '1 day'::interval)::date AS to_time_date
                FROM orders_freezing_pro WHERE user_id = ?i
                AND from_time <= NOW() AND to_time > NOW()
                ORDER BY id DESC LIMIT 1";
        $res = $DB->query($sql, $uid);
        $error = $DB->error;

        if (!$error && pg_numrows($res)) {
            return pg_fetch_row($res, null, PGSQL_ASSOC);
        } else {
            return FALSE;
        }
    }

    /**
     * Возвращает кол-во дней, 
     * в которые аккаунт был заморожен в текущем году
     *
     * @param integer $uid ИД пользователя
     */
    function getFreezedDaysCnt($uid) {
        global $DB;
        $uid = intval($uid);
        
        $sql = "SELECT COUNT(*) cnt, CASE WHEN extract('days' from SUM((to_time-'1 sec'::interval) - from_time)) = 0 
            AND SUM(to_time - from_time) != '0'::interval 
            THEN 1 ELSE extract('days' from SUM((to_time-'1 sec'::interval) - from_time)) END days
        FROM orders_freezing_pro WHERE user_id = ?i
        AND date_part('year', from_time) = ?i";
        
        $res = $DB->row($sql, $uid, date('Y'));
        $error = $DB->error;

        if ($res) {
            return $res;
        } else {
            return FALSE;
        }
    }
    
    
    
    /**
     * Поучить список доступный услуг по ПРО и/или ПРОФИ
     * 
     * @param type $is_emp
     * @return type
     */
    static function getAvailablePayedList($is_emp = false)
    {
        $payed = self::getPayedPROList($is_emp?'emp':'frl');
        
        if(!$is_emp && isAllowProfi()) {
            $payed = isProfi()?self::getPayedPROFIList():array_merge($payed, self::getPayedPROFIList());
        }
        
        return $payed;
    }


    /**
     * Список услуг по ПРОФИ
     * 
     * @return int
     */
    static function getPayedPROFIList()
    {
        $payed = array();
        
        $payed[] = array(
            'week'  => 0,
            'month'  => 1,
            'cost'   => 5990,
            'opcode' => 164            
        );
        
        return $payed;
    }



    /**
     * Информация по тому что можно купить ( на 1,3,6,12 месяцев) 
     * 
     * @return array
     */
    static function getPayedPROList($role = 'frl') {
        
        if($role == 'frl') {
            $payed = array(
                /*
                 * @todo: вырубаем по https://beta.free-lance.ru/mantis/view.php?id=28753
                0 => array(
                    'day'  => 1,
                    'month'  => 1,
                    'cost'   => 99,
                    'opcode' => 132
                ),
                1 => array(
                    'week'  => 1,
                    'month'  => 1,
                    'cost'   => 299,
                    'opcode' => 131
                ),*/
                2 => array(
                    'month'  => 1,
                    'cost'   => 899,
                    'opcode' => 48,
                    'sale_txt' => 'Легкий старт'
                ),
                3 => array(
                    'month'  => 3,
                    'cost'   => 2499,
                    'opcode' => 49,
                    'sale' => '7%', //экономия в %
                ),
                /*
                4 => array(
                    'month'  => 6,
                    'cost'   => 4599,
                    'opcode' => 50
                ),*/
                5 => array(
                    'month'  => 12,
                    'cost'   => 8199,
                    'opcode' => 51,
                    'sale' => '24%' //экономия в %
                )
            );            
            
            //nолько тестовый ПРО на месяц    
            if (isAllowTestPro()) {
                $payed[2] = array(
                        'week'  => 0,
                        'month'  => 1,
                        'cost'   => self::getPriceByOpCode(163),
                        'opcode' => 163,
                        'badge' => 'Акция',
                        'old_cost' => self::getPriceByOpCode(48),
                        'sale_txt' => 'Легкий старт',
                        'class' => 'b-promo__buy_test_pro'
                    );
            }
            
        } else {
            $payed = array(
                array(
                    'month'  => 1,
                    'cost'   => 899,
                    'opcode' => 15,
                    'sale_txt' => 'Легкий старт',
                    //'class' => 'b-promo__buy_min-width_250'
                ),
                array(
                    'month'  => 3,
                    'cost'   => 2499,
                    'opcode' => 118,
                    'sale' => '7%', //экономия в %
                    //'class' => 'b-promo__buy_min-width_250'
                ),/*
                array(
                    'month'  => 6,
                    'cost'   => 4599,
                    'opcode' => 119,
                    'sale' => '15%', //экономия в %
                    //'class' => 'b-promo__buy_min-width_250'
                ),*/
                array(
                    'month'  => 12,
                    'cost'   => 8199,
                    'opcode' => 120,
                    'sale' => '24%', //экономия в %
                    //'class' => 'b-promo__buy_min-width_250'
                )
            );
        }
        
        return $payed;
    }
    
    static function getPriceByOpCode($opCode)
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/op_codes.php';
        $price = op_codes::getPriceByOpCode($opCode);
        return ceil($price * 10);
    }
    
    
    /**
     * Обновляет данные о ПРО в сессии пользователя
     * @return boolean
     */
    public static function updateUserSession()
    {
        if (!$_SESSION['login']) {
            return false;
        }
        
        $pro_last = payed::ProLast($_SESSION['login']);
        
        $_SESSION['pro_last'] = $pro_last['is_freezed'] ? false : $pro_last['cnt'];
        
        if ($pro_last['freeze_to']) {
            $_SESSION['freeze_from'] = $pro_last['freeze_from'];
            $_SESSION['freeze_to'] = $pro_last['freeze_to'];
            $_SESSION['is_freezed'] = $pro_last['is_freezed'];
            $_SESSION['payed_to'] = $pro_last['cnt'];
        }
        
        return true;
    }
}