<?
/**
 * Класс управления пользовательскими, которые пользователь создал самостоятельно, папками в личных сообщениях
 * Используется таблица mess_folders
 */
class mess_folders
{
    /**
     * id папки
     *
     * @var integer
     */
    var $id;

    /**
     * id пользователя, создавшего папку
     *
     * @var integer
     */
    var $from_id;

    /**
     * Имя папки
     *
     * @var string
     */
    var $fname;

    /**
     * Количество пользователей в папке
     *
     * @var integer
     */
    var $users_cont;

    /**
     * Имя поля, таблицы mess_folders, содержащего id serial
     *
     * @var string
     */
    var $pr_key="id";
	
    /**
     * Возвращает список папок.
     * 
     * @return array
     */
	function GetAll() {
		$DB = new DB;
		return $DB->rows("SELECT * FROM messages_folders(?i)", $this->from_id);
	}
	
	/**
	 * Создать папку
	 * 
	 * @return string пустая строка - успех, или сообщение об ошибке
	 */
	public function Add() {
		$DB = new DB;
		if ($DB->val("SELECT COUNT(*) FROM messages_folders(?i) WHERE fname = ?", $this->from_id, $this->fname)) {
			return 'Папка с таким именем уже существует';
		} else {
			$id = $DB->val("SELECT messages_folders_add(?i, ?)", $this->from_id, $this->fname);
		}
		return '';
	}
	
	/**
	 * Удалить папку
	 */
	public function Del() {
		$DB = new DB;
		$DB->val("SELECT messages_folders_del(?i, ?i)", $this->id, $this->from_id);
	}
	
	/**
	 * Переименовать папку
	 * 
	 * @return string пустая строка - успех, или сообщение об ошибке
	 */
	public function Rename() {
		$DB = new DB;
		if (!($r = $DB->row("SELECT * FROM messages_folders(?i) WHERE id = ?", $this->from_id, $this->id))) {
			return 'Указанной папки не существует';
		}
		if ($DB->val("SELECT COUNT(*) FROM messages_folders(?i) WHERE fname = ? AND id <> ?", $this->from_id, $this->fname, $this->id)) {
			return 'Папка с таким именем уже существует';
		}
		$DB->query("SELECT messages_folders_rename(?, ?, ?)", $this->id, $this->from_id, $this->fname);
		return '';

	}
	
}

?>
