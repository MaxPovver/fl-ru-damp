<?
/**
 * Класс для работы с пластиковыми картами пользователей.
 *
 */
class card_verified
{
	/**
	 * ИД
	 *
	 * @var integer
	 */
	var $id;
	/**
	 * ИД аккаунта пользователя (accounts.id)
	 *
	 * @var integer
	 */
	var $account_id;
	/**
	 * Дата проверки
	 *
	 * @var data
	 */
	var $v_date;
	/**
	 * Номер карты
	 *
	 * @var string
	 */
	var $card_num;
	/**
	 * Статус карты
	 *
	 * @var string
	 */
	var $verified;
	/**
	 * Первичный ключ в таблице
	 *
	 * @var string
	 */
	var $pr_key="id";
	
	/**
	 * Проверка карты (были ли раньше переводы)
	 *
	 * @param integer $id ИД карты
	 * @param integer $account_id номер счета юзера
	 * @return boolean true если проверка успешна, 0 - если нет 
	 */
	function checkCard( $id, $account_id ) {
	    global $DB;
        
		$aRow = $DB->row( 'SELECT * FROM card_verified WHERE card_num = ?', $id );
		
		if ( $aRow ) {
			foreach ( $aRow as $key => $val ) {
				$this->$key = $val;
			}
		}
		
		if ($this->verified == 't') return true;
		elseif (!$this->id) {
			$this->account_id = $account_id;
			$this->card_num = $id;
			
			$DB->insert( 'card_verified', array('account_id' => $account_id, 'card_num' => $id) );
		}
		return false;
	}
	
	/**
	 * Установка статуса карты в "проверен"
	 *
	 */
	function verifyCards() {
	    global $DB;
		$sql = "UPDATE card_verified SET verified = true WHERE verified = 'f' AND v_date + '1 day'::interval < now()";
		
		$DB->squery( $sql );
	}
}
?>