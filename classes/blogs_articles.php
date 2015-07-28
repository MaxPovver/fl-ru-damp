<?
/**
 * Подключаем файл для работы с блогами
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs_proto.php");
/**
 * Класс для работы с разделом блогов Статьи и Интервью
 *
 */
class blogs_articles extends blogs_proto {

    const MAX_FILE_SIZE = 2097152;
	/**
	 * Проверка статуса аккаунта (Тест ПРО, или не Тест ПРО)
	 *
	 * @var integer
	 */
	var $is_pro_test;
	
    /**
     * Выборка тем сообщений
     *
     * @param integer $item_id  ИД треда
     * @param string  $error    Возвращает сообщения об ошибке
     */
	function GetThread($item_id, &$error)
    {
        global $DB;
        
		$curname = get_class($this);
        
		$sql = "SELECT id, fromuser_id, reply_to, post_time, msgtext, attach, title, uname, usurname, users.is_banned, login, photo, is_pro_test, role, modified, modified_id, deluser_id, deleted, small, payed, sign
		FROM 
		(SELECT $curname.item_id, $curname.fromuser_id, $curname.id, $curname.reply_to, $curname.post_time, $curname.msgtext, $curname.attach, $curname.title, $curname.modified, 
		$curname.small, 1 as t, modified_id, deluser_id, deleted, NULL as sign FROM $curname WHERE item_id = ?i
		UNION ALL 
		SELECT id, 0, 0, NULL, NULL, short, NULL, title, NULL, NULL, 0, NULL, NULL, NULL, sign
		FROM articles WHERE id = ?i) as blg
		LEFT JOIN users ON fromuser_id=uid 
		LEFT JOIN (SELECT DISTINCT from_id, payed FROM orders 
             WHERE payed=true AND from_date<=now() AND from_date+to_date+COALESCE(freeze_to, '0')::interval >= now() AND orders.active='true'
             AND NOT (freeze_from_time IS NOT NULL AND NOW() >= freeze_from_time::date AND NOW() < freeze_to_time)) as pay
		 ON pay.from_id=uid
		ORDER BY blg.t, reply_to, post_time";
        
        
        $this->thread = $DB->rows($sql, $item_id, $item_id);
		$error .= $DB->error;
		if ($error) {
            $error = parse_db_error($error);
        } else {
		 	$this->msg_num = count($this->thread);
		 	if ($this->msg_num > 0) $this->SetVars(0);
		}
	}
	
	/**
     * Инициализирует члены класса в соответствии с текущим индексом сообщения в массиве треда
     *
     * @param integer $idx	индекс соощения в массиве тем
     */
	function SetVars($idx){
		parent::SetVars($idx);
		if ($idx == 0) {
			$node = $this->thread[$idx];
			$this->login = '';
			$this->uname = $node['sign'];
			$this->usurname = '';
		}
	}
	
	/**
     * Возвращает следующее сообщение для вывода (через члены класса)
     *
     * @return integer		идентификатор родительского сообщения
     */
    function GetNext(){
            $ind = $this->SearchFirstChild($this->id);
            $i = 0; // на всякий случай
            while ($ind == -1) {
                    $last = $this->thread[$this->last_inx]['reply_to'];
                    $this->thread[$this->last_inx]['reply_to'] = -1;
                    //print_r($this->thread);
                    $ind = $this->SearchFirstChild($last);
                    $this->last_inx = $this->GetInxById($last);
                    $this->level--;
                    if ($i++ > 100) die("Ошибка! сообщите разработчикам!");
            }
            $this->level++;
            $this->SetVars($ind);
            $this->last_inx = $ind;
            $this->reply = $this->thread[$this->last_inx]['reply_to'];
            return $this->thread[$this->last_inx]['id'];
    }
}
?>