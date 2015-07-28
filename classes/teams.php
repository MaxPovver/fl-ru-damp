<?
/**
 * Подключаем файл с основными функциями
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

/**
 * Класс для работы с группам пользователей (избарнное, я рекомендую, меня рекомендуют)
 *
 */
class teams {
	/**
	 * ИД Пользователя
	 *
	 * @var integer
	 */
	public $user_id;
	/**
	 * ИД
	 *
	 * @var integer
	 */
	public $target_id;
	
	/**
	 * Добавить пользователя в избарнное
	 *
	 * @param integer $user_id       ИД пользователя, к которому добавляем
	 * @param string  $target_login  Логин или uid добавляемого пользователя
	 * @return string Сообщение об ошибке
	 */
	function teamsAddFavorites($user_id, $target, $by_login = true) {
		$DB = new DB;
		$error = '';

		$user = new users;
		if($by_login) {
			$user->GetUser($target);
			$target = $user->uid;
		} else {
			$user->GetUserByUID($target);
		}

		if ($user_id && $target && $user_id != $target) {
			if ($DB->val("SELECT teams_check(?i, ?i)", $user_id, $target)) {
				$error = 'Пользователь уже добавлен';
			} else {
                $DB->val("SELECT teams_add(?i, ?i)", $user_id, $target);
                if($user->subscr[9]) {
					require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/pmail.php";
                    $mail = new pmail;
                    $mail->addTeamPeople($user_id, $target);
                }
            }
		} else {
			$error = "Пользователь не определен";
		}
		return $error;
	}
	
	/**
	 * Получить фрилансеров у которых юзер в избарнном
	 *
	 * @param string $for_whom логин
	 * @param string $error Возвращает сообщение об ошибке
	 * @return array 
	 */
	function teamsInFrlFavorites($for_whom, &$error) {
		$users = new users;
		$uid = $users->GetUid($err, $for_whom);
		$DB = new DB;
		$rows = $DB->rows("SELECT * FROM teams_recom_freelancers(?i)", $uid);
		return $rows;
	}


	/**
	 * Получить работодателей у которых юзер в избарнном
	 *
	 * @param string $whom Логин пользователя
	 * @param string $error Возвращает сообщение об ошибке
	 * @return array
	 */
	function teamsInEmpFavorites($whom, &$error) {
		$users = new users;
		$uid = $users->GetUid($err, $whom);
		$DB = new DB;
		$rows = $DB->rows("SELECT * FROM teams_recom_employers(?i)", $uid);
		return $rows;
	}

	
	/**
	 * Получить всех кто у юзера в избарнном
	 *
	 * @param string $whom  логин или uid
	 * @param string $error Возвращает сообщение об ошибке
	 * @param bool $bIsLogin является ли $whom логином
	 * @return array
	 */
	function teamsFavorites( $whom, &$error, $bIsLogin = false ) { // Я рекомендую.
		$DB = new DB;
		
        if ( $whom === NULL ) {
            $whom = get_uid(false);
        }
        
        if ( $bIsLogin ) {
            $users = new users;
            $whom = $users->GetUid($err, $whom);
        }
        
		$users = $DB->rows("SELECT * FROM teams_get_users(?i)", $whom);
		return $users;
	}


	/**
	 * Получить количество всех кто у юзера в избарнном
	 *
	 * @param string $whom  логин или uid
	 * @param string $error Возвращает сообщение об ошибке
	 * @param bool $bIsLogin является ли $whom логином
	 * @return array
	 */
	function teamsFavoritesCount( $whom, &$error, $bIsLogin = false ) { // Я рекомендую.
		$DB = new DB;
		
		if ( $bIsLogin ) {
			$users = new users;
			$whom = $users->GetUid($err, $whom);
		}
		
		return $DB->val("SELECT COUNT(*) FROM teams(?i)", $whom);
	}

	/**
	 * Удалить юзеров из избарнного за иключением перечисленных
	 *
	 * @param integer $user_id  ид юзера, у которого редактируем избранное.
	 * @param array   $selected ид юзеров, которых НЕ нужно удалять (пустой массив -- удаляем всех).
	 * @return string Сообщение об ошибке
	 */
	function teamsDelFavoritesExcept($user_id, $selected) {
		$DB = new DB;
        if ( empty($user_id) ) {
            return 'Ошибка при удалении';
        }
		if (!$selected) $selected = array();
		$DB->val("SELECT teams_leave(?i, ?ai)", $user_id, $selected);
		return '';
	}
	
	/**
	 * Добавить юзера в избранное, если его там не было, или удалить, если был
	 *
	 * @param string $login Логин юзера, которого нужно удалить/добавить.
	 * @return integer Количество выбранных для изменения данных
	 */
	function teamsInverseFavorites($login) {
		$r = 1;
		$DB = new DB;
		$this->target_id = users::GetUid($err, $login);
		if ($this->user_id && $this->target_id) {
			$r = $DB->val("SELECT teams_check(?i, ?i)", $this->user_id, $this->target_id);
			if ($r) {
				$this->teamsDelFavorites();
			} else {
				$this->teamsAddFavorites($this->user_id, $login);
			}
		}
		return $r;
	}
	
	
	/**
	 * Удалить юзера из избарнного
	 *
	 * @return string Сообщения об ошибке
	 */
	function teamsDelFavorites() {
		$DB = new DB;
        if ( empty($this->user_id) || empty($this->target_id) ) {
            return 'Ошибка при удалении пользователя';
        }
		$DB->query("SELECT teams_del(?i, ?i)", $this->user_id, $this->target_id);

		require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/pmail.php";
        $mail = new pmail;
        $mail->delTeamPeople($this->user_id, $this->target_id);

		return '';
	}

    /**
     * Удаление пользователя из избранного по его логину
     *
     * @param integer $user_id     id пользователя в избранном которого нужно удалить пользователя
     * @param string  $target_login   login пользователя, которого нужно удалить
     * @return string  текст ошибки операции или пустая строка
     */    
	function teamsDelFavoritesByLogin($user_id, $target_login) {
		$DB = new DB;
		$error = '';
		if ($user_id && ($target_id = users::GetUid($error, $target_login))) {
			$DB->query("SELECT teams_del(?i, ?i)", $user_id, $target_id);

			require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/pmail.php";
        	$mail = new pmail;
	        $mail->delTeamPeople($user_id, $target_id);

		} else {
			$error = "Юзер не определен";
		}
		return $error;
	}    
    
    /**
     * Проверяет, находится ли один пользователь в избранных у другого
     *
     * @param integer $user_id     id пользователя в избранном которого нужно проверить
     * @param integer $target_id   id пользователя, которого проверяем
     * @return integer  1 - если есть в избранном, 0 - если нет
     */
    function teamsIsInFavorites($user_id, $target_id) {
        $DB = new DB;
		return $DB->val("SELECT teams_check(?i, ?i)", $user_id, $target_id);
    }
    
}

?>
