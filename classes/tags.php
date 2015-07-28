<?
/**
 * Подключаем файл с основными функциями
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Класс для работы с тегами
 *
 */
class tags
{
	/**
	 * Добавить новые теги
	 * 
	 * 
	 * @param array $tags Теги [тег1,тег2,...]
	 * @return array Записанные результаты
	 */
	function Add( $tags ) {
	    global $DB;
	    
		foreach($tags as $ikey => $value){
			if (!$value) continue;
			$sql      = "SELECT inserttag('".change_q_new(substr(trim($value),0,20))."');";
			$tag[]    = $DB->val( $sql );
			$error[1] = parse_db_error( $DB->error );
		}
		
		return $tag;
	}
}
?>