<?
/**
 * Подключем файл с основными функциями
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Полный путь до папки с шаблонами для Заметок
 *
 */
define(TPL_DIR_NOTES, $_SERVER['DOCUMENT_ROOT']."/user");

/**
 * Класс для работы с заметками на странице пользователя
 *
 */
class notes
{
    /**
	 * Добавление новой заметки
	 *
	 * @param integer $user_id       ИД пользователя (чья заметка)
	 * @param string  $target_login  Кому заметка (логин)
	 * @param string  $text          Текст заметки 
	 * @return string Сообщение об ошибке
	 */
	function Add($user_id, $target_login, $text, $rating = 0, $old="?i"){
		$DB = new DB;
		if ( empty($user_id) || empty($target_login) || empty($text) ) {
            return 'Ошибка добавления заметки';
        }
		$id = $DB->val("SELECT notes_add(?i, {$old}, ?, ?i)", $user_id, $target_login, $text, $rating);
		return '';
	}
	
	/**
	 * Обновить заметку
	 *
	 * @param integer $user_id      ИД пользователя 
	 * @param string  $target_login Кому заметка
	 * @param string  $text         текст заметки
	 * @return string Сообщение об ошибке
	 */
	function Update($user_id, $target_login, $text, $rating = 0, $old="?i"){
		$DB = new DB;
		if ( empty($user_id) || empty($target_login) || empty($text) ) {
            return 'Ошибка обновления заметки';
        }
		$res = $DB->val("SELECT notes_update(?i, {$old}, ?, ?i)", $user_id, $target_login, $text, (int)$rating);
		return '';
	}
	
	/**
	 * Взять все заметки
	 *
	 * @param integer $from_id   ИД пользователя чьи заметки
	 * @param array   $to_login  Кому заметка (массив с логинами) 
	 * @param string  $error  Возвращает сообщение об ошибке
	 * @return array данные выборки
	 */
	function GetNotes($from_id, $to_login=false, &$error){
		$DB = new DB;
		if(!$from_id) return false;
		if (empty($to_login)) {
			$rows = $DB->rows("SELECT * FROM notes(?)", $from_id);
		} elseif (is_array($to_login)) {
			$rows = $DB->rows("SELECT * FROM notes_get(?, ?a)", $from_id, $to_login);
		} else {
			$rows = $DB->row("SELECT * FROM notes_get(?i, ?i)", $from_id, $to_login);
        }
		return $rows;
	}
	
	/**
	 * Выборка заметки
	 *
	 * @param integer $from_id   ИД пользователя чья заметка
	 * @param string  $to_login  Кому заметка (логин)
	 * @param string  $error     Возвращает сообщение об ошибке
	 * @return array данные выборки
	 */
	function GetNote($from_id, $to_id, &$error=false){
		$DB = new DB;
		$rows = $DB->row("SELECT * FROM notes_get(?i, ?)", $from_id, $to_id);
		return $rows;
	}
	
	/**
	 * Выборка заметки
	 *
	 * @param integer $from_id   ИД пользователя чья заметка
	 * @param string  $to_login  Кому заметка (логин)
	 * @param string  $error     Возвращает сообщение об ошибке
	 * @return array данные выборки
	 */
	function GetNoteInt($from_id, $to_id, &$error=false){
		$DB = new DB;
		$rows = $DB->row("SELECT * FROM notes_get(?i, ?i)", $from_id, $to_id);
		return $rows;
	}
	
	/**
	 * Удаление заметки
	 *
	 * @param inetger  $user_id    Ид пользотваеля
	 * @param integer  $to_uid     Кому заметка (Логин)
	 * @return string  $error      Возвращает сообщение об ошибке
	 */
	function DeleteNote($user_id, $to_uid, $old="?i"){
		$DB = new DB;
		if ( empty($user_id) || empty($to_uid)) {
            return 'Ошибка удаления заметки';
        }
		$DB->query("SELECT notes_del(?i, {$old})", $user_id, $to_uid);
		return '';
	}
    
	/**
	 * Выводит форм редактирования и добавления Заметки
	 *
	 * @param array $req Данные для формы
	 * @return string $html Шаблон в формате HTML
	 */
	public function getNotesForm($req, $type = 1) {
	    ob_start();
        include_once($_SERVER['DOCUMENT_ROOT'].'/user/tpl.notes_form.php');
        $html = ob_get_clean();
        return $html;
	}
	
	/**
	 * Выводим заметки пользователей которые находятся в избранном
	 *
	 * @param array   $recs  Пользователи находящиеся в избранном
	 * @param array   $notes Заметки пользователя
	 * @param integer $start Начало позиции вывода колонки пользователей (с какого пользователя в массиве recs)
	 * @param integer $stop  Конец позиции вывода колонки пользователей (до какого пользователя в массиве recs)
 	 */
	public function getNotesUsers($recs, $notes, $start, $stop, $type=1) {
	    global $session, $recsProfi;
	    
    	for($i=$start;$i<$stop;$i++) { 
            $rec = $recs[$i];
            
            if(isset($recsProfi[$rec['uid']])) {
                $rec['is_profi'] = $recsProfi[$rec['uid']];
            } 
            
            $clsNote = "";
            if(count($notes[$rec['uid']]) > 0) {
                $note = $notes[$rec['uid']];
                switch($note['rating']) {
                    /*case 1:
                        $clsNote = "fs-g";
                        break;        
                    case 0:
                        $clsNote = "";
                        break;
                    case -1:
                        $clsNote ="fs-p";
                        break;*/
                    default:
                        $clsNote ="fs-o";
                        break;
                }
            } else {
                $note = false;
            }
            include (TPL_DIR_NOTES."/tpl.notes_item.php");
        } 
	}
}

?>
