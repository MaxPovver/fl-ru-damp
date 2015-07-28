<?php
/**
 * Подключаем файл с основными функциями
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Класс для работы c ИП пользотваеля
 */
class annoy {
	/**
	 * ИД пользователя
	 *
	 * @var integer
	 */
	public $id;
	/**
	 * IP Пользователя
	 *
	 * @var char
	 */
	public $ip;
	/**
	 * Последняя проверка
	 *
	 * @var date
	 */
	public $last_req;
	/**
	 * Добавить ИП в БД
	 *
	 * @param char $ip
	 */
	function Add( $ip ) {
	    global $DB;
		$DB->query( 'INSERT INTO annoy(ip,last_req) VALUES ( ?, NOW())', $ip );
	}
	/**
	 * Проверить ИП по базе
	 *
	 * @param char $ip
	 * @return число заходов
	 */
	function Check( $ip ) {
	    global $DB;
		$icount = 0;
        $sql = "SELECT COUNT(*) FROM annoy WHERE ip = ? AND last_req+'?i minutes'::interval > NOW()";
		$icount = $DB->val( $sql, $ip, $GLOBALS['login_wait_time'] );
		
		$this->Clear($ip);
				
		return $icount;
	}
	
	/**
	 * Очитить лог для IP
	 *
	 * @param char $ip
	 */
	function Clear( $ip ) {
		global $DB;

        $sql = "DELETE FROM annoy WHERE last_req+'?i minutes'::interval < NOW()";
		$DB->query( $sql, $GLOBALS['login_wait_time'] );

	}

    
    /**
     * Проверка есть ли у пользователя еще попытки на повтор ввода пароля
     * @todo: может перенести все это в мемкеш?
     * 
     * @global type $DB
     * @param type $uid
     * @return type
     */
    public function allowRepeatPass($uid)
    {
        global $DB;
        
        $cnt = $DB->val("
            SELECT cnt 
            FROM users_login_attempt
            WHERE user_id = ?i
        ", $uid);
        
        //Записи нет тогда добавляем
        if ($cnt === null) {
            $cnt = 0;
            
            //Если первый раз ошибся то начинаем отсчет
            $DB->query("INSERT INTO users_login_attempt (user_id, cnt) 
                        SELECT ?i, ?i
                        WHERE NOT EXISTS(
                            SELECT 1 FROM users_login_attempt 
                            WHERE user_id = ?i 
                            LIMIT 1)", 
                $uid, 
                $cnt, 
                $uid              
            );
        }
        
        return $cnt < 7;
    }

    
    /**
     * Не корректный пароль - увечиливаем счетчик
     * 
     * @global type $DB
     * @param type $uid
     */
    public function wrongRepeatPass($uid)
    {
        global $DB;
        
        $DB->query("
            UPDATE users_login_attempt 
            SET cnt = cnt + 1
            WHERE user_id = ?i
        ", $uid);
    }
    
    
    /**
     * Очистка заблокированных пользователей
     * 
     * @global type $DB
     */
    static function clearRepeatPassByCnt()
    {
        global $DB;
        $DB->query("DELETE FROM users_login_attempt WHERE cnt > 6");
    }

    /**
     * Очистка счетчика указанного пользователя
     * 
     * @global type $DB
     * @param type $uid
     */
    public function clearRepeatPass($uid)
    {
         global $DB;
         $DB->query("DELETE FROM users_login_attempt WHERE user_id = ?i", $uid);
    }
    
    
}