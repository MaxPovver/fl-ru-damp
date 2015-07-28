<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 *  ласс дл€ работы с игнорированием пользователей друг друга (Ћичных сообщений)
 *
 */
class ignor{
    
    protected $DB;
	
	/**
     * ƒобавл€ет пользовател€ в список игнорировани€
     *
     * @param integer $user_id           id пользовател€, добавл€ющего другого в игнор-лист
     * @param string $target_login       логин пользовател€, добал€емого в игнор-лист
     *
     * @return string                    пуста€ строка или сообщение об ошибке в случае неуспеха
     */    
    function Add($user_id, $target_login) {
        global $usersNotBeIgnored;
        if ( empty($user_id) || empty($target_login) || in_array($target_login, $usersNotBeIgnored) ) {
            return false;
        }
        $user = new users();
        $user->login = $target_login;
        $target_id = $user->GetUid($error);
        $DB = new DB;
		$r = $DB->val("SELECT ignor_add(?i, ?i)", $user_id, $target_id);
        return '';
    }


    
    /**
     * ƒобавл€ет пользователей в список игнорировани€
     *
     * @param integer $user_id           id пользовател€, добавл€ющего других в игнор-лист
     * @param array $selected            id пользователей, добавл€емых в игнор-лист
     *
     * @return string                    пуста€ строка или сообщение об ошибке в случае неуспеха
     */    
    function AddEx($user_id, $selected){
        $DB = new DB;
		if (!empty($user_id) && is_array($selected) && count($selected)) {
			$DB->query("SELECT ignor_add(?i, ?a)", $user_id, $selected);
			$error = '';
		} else {
			$error = "Ќеобходимо выбрать хот€ бы один контакт";
		}
		return $errors;
    }


    /**
     * ”дал€ет пользовател€ из списка игнорировани€
     *
     * @param integer $user_id           id пользовател€, удал€ющего другого из игнор-листа
     * @param array $selected            id пользователей, удал€емого из игнор-листа
     *
     * @return string                    пуста€ строка или сообщение об ошибке в случае неуспеха
     */    
    function Del(){
        $DB = new DB;
        if ( empty($this->user_id) || empty($this->target_id) ) {
            return '¬ы ну указали контакт';
        }
		$DB->query("SELECT ignor_del(?i, ?)", $this->user_id, $this->target_id);
        return '';
    }
	
    
    /**
     * ”дал€ет пользователей из списка игнорировани€
     *
     * @param integer $user_id           id пользовател€, удал€ющего других из игнор-листа
     * @param array $selected            id пользователей, удал€емых из игнор-листа
     *
     * @return string                    пуста€ строка или сообщение об ошибке в случае неуспеха
     */    
    function DeleteEx($user_id, $selected){
        if (is_numeric($selected)) $selected = array($selected);
		$DB = new DB;
		if ( !empty($user_id) && is_array($selected) && count($selected) ) {
			$DB->query("SELECT ignor_del(?i, ?a)", $user_id, $selected);
			$error = '';
		} else {
			$error = "Ќеобходимо выбрать хот€ бы один контакт";
		}
        return $error;
    }


    
    /**
     * ѕроверка польовател€ на нахождениии в игнор-листе
     *
     * @param integer $from_id           id пользовател€, владельца игнор-листа
     * @param array $tar_id              id пользователей, которого провер€ем
     *
     * @return integer                   0 - нет, 1 - есть
     */    
    function CheckIgnored($from_id, $tar_id){
        $DB = new DB;
		$r = $DB->val("SELECT ignor_check(?i, ?i)", $from_id, $tar_id);
		return $r;
    }


    
    /**
     * ѕроверка польовател€ на нахождениии в игнор-листе.
     * ¬ случае нахождени€ удал€ет пользовател€ из списка, иначе добавл€ет
     *
     * @param integer $login             логин пошльзовател€, которого провер€ем
     *
     * @return string                    пуста€ строка или сообщение об ошибке в случае неуспеха
     */    
    function Change($login){
        $DB = new DB;
		$r = $DB->val("SELECT ignor_check(?, ?)", $this->user_id, $login);
		if ($r) {
			$this->target_id = $login;
			$this->Del();
		} else {
			$this->Add($this->user_id, $login);
		}
		return $r;
    }


    
}

?>