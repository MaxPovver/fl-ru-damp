<?
/**
 * подключаем файл с основными функциями
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Класс работы с анонимными юзерами [Рудименты]
 *
 */
class anonymous
{
	/**
	 * Идентификатор анонима
	 *
	 * @var integer
	 */
	public $id;
	/**
	 * icq
	 *
	 * @var char
	 */
	public $icq;
	/**
	 * e-mail
	 *
	 * @var char
	 */
	public $mail;
	/**
	 * Телефон
	 *
	 * @var char
	 */
	public $phone;
	/**
	 * ip данного анонима
	 *
	 * @var char
	 */
	public $ip;
  	/**
  	 * Когда создан аноним
  	 *
  	 * @var datetime
  	 */
  	public $createtime;
	/**
	 * показывать ли данного анонима
	 *
	 * @var bool
	 */
	public $visible;
	
	/**
	 * Создать анонима
	 *
	 * @param char $error
	 * @return integer		идентификатор анонима
	 */
	function Create( &$error ) {
	    global $DB;
		$id = 0;
		if(!$error){
		    $data = array( 'icq' => $this->icq, 'mail' => $this->mail, 'phone' => $this->phone, 'ip' => getRemoteIP() );
		    $DB->insert( 'anonymous', $data );
			$id    = $DB->val( "SELECT currval('anonymous_id_seq');" );
			$error = $DB->error;
		}
		return ($id);
	}
	
	/**
	 * Изменить параметр "видимости" анонима на противоположный
	 *
	 * @param integer $aid		идентификатор анонима
	 * @return char				сообщение об ошибке
	 */
	function ChVisible( $aid ) {
	    global $DB;
		$sql = "UPDATE anonymous SET visible = NOT visible::bool WHERE id = ?";
		$DB->squery( $sql, $aid );
		$error = pg_errormessage();
		return ($error);
	}
	
	/**
	 * Обновить инфу об анониме
	 *
	 * @param integer $aid		идентификатор анонима
	 * @return char				сообщение об ошибке
	 */
	function Update( $aid ) {
	    global $DB;
	    $data = array( 'icq' => $this->icq, 'mail' => $this->mail, 'phone' => $this->phone );
	    $DB->update( 'anonymous', $data, 'id = ?', $aid );
		return $DB->error;
	}
}
?>
