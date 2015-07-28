<?
/**
 * Подключаем файл с основными функциями
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
/**
 * Класс для создания и обработки кода активации новых пользователей.
 *
 */
class activate_code 
{	
	/**
	 * Создать код активации для нового юзера и записать его в Базу Данных
	 *
	 * @param  integer $uid ID Пользователя (users.uid)
	 * @param  string  $login Логин пользователя
	 * @param  string  $sSuspectPwd опционльно. открытый пароль, если пользователь считается подозрительным
	 * @param  string  $error Возвращает ошибку, если она есть.
	 * @return string|integer $code Сгенерированный код активации, возвращает ноль если код активации не был сгенерирован
	 */
	function Create( $uid, $login, $sSuspectPwd = '', &$error ) {
		if( $login && $uid ) {
		    global $DB;
			$code = md5( crypt($login) ); // Создание нового кода
			$data = array( 'user_id' => $uid, 'code' => $code );
			
			if ( $sSuspectPwd ) { // кладем только если нужно. иначе пусть null будет
				$data['suspect_plain_pwd'] = $sSuspectPwd;
			}
			
			$DB->insert( 'activate_code', $data );
			$error .= pg_errormessage();
		} 
		else $code = 0;
		
		return ($code);
	}
	
	/**
	 * Активирует аккаунт юзера по $code, возвращает логин и пароль пользователя
	 *
	 * @param string $code		Код активации
	 * @param string $login		Возвращает логин пользователя
	 * @param string $pass		Возвращает пароль пользователя
	 * @return integer			1 - активация прошла успешно, 0 - активация не прошла
	 */
	function Activate ( $code, &$login, &$pass ) {
        define('IS_USER_ACTION', 1);
		/**
		 * Подлючаем файл для работы с пользователем
		 */
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/wizard_registration.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/step_employer.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/step_freelancer.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
        
		global $DB;
		$sql = "SELECT user_id, login, passwd FROM activate_code LEFT JOIN users ON user_id=uid WHERE code = ?";
		$res = $DB->query( $sql, $code );
		list($fid, $login, $pass) = pg_fetch_row($res);
		if ($fid) {
			$usr = new users();
			$usr->active = 1;
			$usr->Update($fid, $res);
            $usr->GetUserByUID($fid);
            // #0017513
            if($usr->role[0] == 1) {
                $wiz_user = wizard::isUserWizard($fid, step_employer::STEP_REGISTRATION_CONFIRM, wizard_registration::REG_EMP_ID);
            } else {
                $wiz_user = wizard::isUserWizard($fid, step_freelancer::STEP_REGISTRATION_CONFIRM, wizard_registration::REG_FRL_ID);
            }
			$out = 1;
			$this->Delete($fid);
            if($wiz_user['id'] > 0) {
                $mail = new smail(); 
                if ($usr->role[0] == 1) {                
                    $mail->employerQuickStartGuide($fid);
                } else {
                    $mail->freelancerQuickStartGuide($fid);
                }
                step_wizard::setStatusStepAdmin(step_wizard::STATUS_COMPLITED, $fid, $wiz_user['id']);
                $role = ($usr->role[0] == 1) ? wizard_registration::REG_EMP_ID : wizard_registration::REG_FRL_ID ;
                login($login, $pass, 0, true);
                header("Location: /registration/activated.php?role=".$role);
                exit;
            }
		} else $out = 0;
		return $out;
	}
	
	/**
	 * Удаляем код активации который был активирован.
	 *
	 * @param integer $fid ID Пользователя
	 * @return mixed	Сообщение об ошибке
	 */
	function Delete( $fid ) {
	    global $DB;
		$sql = "DELETE FROM activate_code WHERE user_id = ?";
		$DB->query( $sql, $fid );
		return pg_errormessage();
	}
    
    /**
     * Берем код активации для повторного отправления пользователю на почту
     * 
     * @global type $DB
     * @param integer $uid ИД пользователя
     * @return string 
     */
    function getActivateCodeByUID($uid) {
        global $DB;
        return $DB->val("SELECT code FROM activate_code WHERE user_id = ?", $uid);
    }
    
    function isActivateCode($code) {
        global $DB;
		return $DB->val("SELECT user_id FROM activate_code WHERE code = ?", $code );
    }
}
?>