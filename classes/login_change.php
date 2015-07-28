<?
/**
 * Класс для хранения и обработки изменения логина пользователя
 */
class login_change {
	/**
	 * id изменения
	 *
	 * @var integer
	 */
	public $id;
	/**
	 * UID
	 *
	 * @var integer
	 */
	public $user_id;
	/**
	 * Новый логин
	 *
	 * @var char
	 */
	public $new_login;
	/**
	 * Старый логин
	 *
	 * @var char
	 */
	public $old_login;
	
	/**
	 * Хранить ли старый логин
	 *
	 * @var boolean
	 */
	public $save_old;
	
	/**
	 * ID операции в таблице account_operations
	 *
	 * @var boolean
	 */
	public $operation_id;
	
	/**
	 * Дата создания
	 *
	 * @var string
	 */
	public $cdate;
	
	public $pr_key = "id";
	
	const OP_CODE = 70;
	
	/**
	 * Изменение логина юзера. Перед вызовом необходимо проинициализировать члены класса
	 * old_login, new_login, save_old
	 * 
	 * @param string $error	возвращает сообщение об ошибке	
	 * @return 0
	 * @see classes/db_access#Add($error, $return_id)
	 */
	function Add(&$error){
	    global $DB;
	    
		require_once ABS_PATH.'/classes/users.php';
		$user = new users();
		$this->user_id = $user->GetUid($error, $this->old_login);
		if (!$this->user_id) {$error = "Пользователь не найден!"; return 0;}
		$new_user = $user->GetUid($error, $this->new_login);
		if ($new_user) {$error = "Логин занят!"; return 0;}
		if ($this->save_old){
			require_once ABS_PATH.'/classes/users_old.php';
			require_once ABS_PATH.'/classes/account.php';
			$account = new account();
			$tr_id = $account->start_transaction($this->user_id);
			$id = 0;
			$error = $account->Buy($id, $tr_id, login_change::OP_CODE, $this->user_id, "Изменеие логина", "Изменение логина");
			if ($error) return 0;
			$this->operation_id = $id;
			$users_old = new users_old();
			$users_old->Add($this->old_login);
		}
		
        if (!$error) {
            $aData = array(
                'user_id'      => '',
                'old_login'    => '',
                'new_login'    => '',
                'save_old'     => '',
                'operation_id' => ''
            );
            
            foreach ( $aData as $key => $val ) {
            	$aData[$key] = $this->$key;
            }
            
    		$CFile = new CFile();
    		if (!$CFile->MoveDir($this->new_login, $this->old_login)) {
    			$error = "Директория не создана! $this->new_login, $this->old_login";
    			if ($this->operation_id){
    				$account->Del($this->user_id,$this->operation_id);
    			}
    		} else {
                $DB->insert('login_change', $aData);
    			$user->login = $this->new_login;
    			$user->Update($this->user_id, $res);
    		}
        }
        return 0;
	}
	
	/**
	 * Выбирает запись из login_change по полю old_login и устанавливает переменные класса.
	 * 
	 * @param  string $sOldLogin old_login
	 * @return bool true - успех, false - провал
	 */
	function GetRowByOldLogin( $sOldLogin = '' ) {
	    global $DB;
	    
	    $bRet = true;
	    $aRow = $DB->row('SELECT * FROM login_change WHERE lower(old_login)=lower(?) ORDER BY id DESC', $sOldLogin);
	    
	    if ( is_array($aRow) && count($aRow) ) {
            foreach ( $aRow as $key => $val ) {
                $this->$key = $val;
            }
	    }
	    else {
	        $bRet = false;
	    }
	    
	    return $bRet;
	}
	
	/**
	 * Поиск по смене логина:
	 * 1. по конкретному логину.
	 * 2. по конкретному логину в конкретную дату.
	 * 3. все смены логина за указанный период.
	 * 
	 * @param  string $login опционально. старый логин при поиске по логину.
	 * @param  string $date опционально. дата смены логина по логину.
	 * @param  string $ds опционально. начальная дата при поиске за период.
	 * @param  string $de опционально. конечная дата при поиске за период.
	 * @return array массив записей.
	 */
	function getAllForAdmin( $login = '', $date = '', $ds = '', $de = '' ) {
	    global $DB;
	    
	    if ( $login ) {
        	$sWhere  = "old_login = '$login'";
        	$sWhere .= ( $date ) ? " AND cdate > '$date'" : '';
        } else {
            $ds  = ( $ds ) ? $ds : date('Y-m-d');
            $de  = ( $de ) ? $de : date('Y-m-d');
        	$sWhere = "cdate >= '$ds 00:00:01' AND cdate < '$de 23:59:59'";
        }
        
        return $DB->rows( "SELECT * FROM login_change WHERE $sWhere ORDER BY id" );
	}
	
	/**
	 * Заглушка для вызова в account::Del();
	 * 
	 * @param integer $uid	UID	
	 * @param integer $opid идентификатор операции
	 * @return 0
	 */
	function DelByOpid($uid, $opid){
		return 0;
	}
	
	/**
	 * Заглушка для вызова в account::GetHistoryInfo();
	 * 
	 * @param  integer $bill_id идентификатор операции
	 * @param  integer $uid ID Пользователя
	 * @param  integer $mode 1:история юзера; 2:история юзера для админа; 3:подарок
	 * @return string текстовое описание операции
	 */
	function GetOrderInfo( $bill_id, $uid, $mode ) {
	    return '';
	}
	
	/**
	 * Заглушка для вызова в account::Blocked();
	 * 
	 * @param  integer $uid ID Пользователя Который производит блокировку
	 * @param  integer $opid идентификатор типа операции
	 * @return string сообщение об ошибке
	 */
	function BlockedByOpid( $uid, $opid ) {
	    return '';
	}
	
    /**
     * Заглушка для вызова в account::unBlocked();
     * 
     * @param integer $uid ИД заблокированной записи 
	 * @param integer $opid ИД заблокированной операции
     */
    function unBlockedByOpid( $uid, $opid ) {
        return true;
    }
}
?>