<?
/**
 * подключаем файл с основными функциями работы с БД
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

/**
 * Класс обрабатывающий обращения к личному менеджеру
 *
 */
class lm {

    /**
     * Возвращает информацию о текущем состоянии документов
     *
     * @param   integer $id Идентификатор обращения к ЛМ
     * @return  array       Информация о состоянии документов
     */
    function GetDocumentsInfo($id) {
        global $DB; 
        $sql = "SELECT * FROM lm_docs WHERE id=?i";
        return $DB->row($sql, $id);
    }

    /**
    * Обновляет информацию о текущем состоянии отосланных документов
    *
    * @param    integer $id     ID обращения к ЛМ
    * @param    boolean $status Статус документов(true - отослали, false - нет)
    * @param    string  $date   Дата отсылки документов
    */
    function UpdateDocumentsSend($id, $status, $date) {
        global $DB;
        $sql = "UPDATE lm_docs SET docsend = ?, docsend_time = ? WHERE id = ?i";
        $DB->query($sql, $status, ($status?$date:NULL), $id);
    }

    /**
    * Обновляет информацию о текущем состоянии вернувшихся документов
    *
    * @param    integer $id     ID обращения к ЛМ
    * @param    boolean $status Статус документов(true - вернулись, false - нет)
    * @param    string  $date   Дата получения документов
    */
    function UpdateDocumentsBack($id, $status, $date) {
        global $DB;
        $sql = "UPDATE lm_docs SET docback = ?, docback_time = ? WHERE id = ?i";
        $DB->query($sql, $status, ($status?$date:NULL), $id);
    }

    /**
    * Добавляет запись в базу документов ЛМ
    *
    * @param    integer $user_id    ID пользователя
    * @param    integer $op_id      ID операции
    * @return   integer             ID добвленной записи
    */
    function AddDocumentRecord($user_id, $op_id) {
        global $DB;
        $sql = "INSERT INTO lm_docs(opid, user_id, docsend_time, docsend, docback_time, docback) VALUES(?i, ?i, NULL, false, NULL, false) RETURNING id";
        $id = $DB->val($sql, $op_id, $user_id);
        return $id;
    }

    /**
    * Добавление файлов акта и счета-фактуры
    *
    * @param    integer $id         Идентификатор обращения к ЛМ
    * @param    string  $file_sf    Имя файла счета-фактуры
    * @param    string  $file_act   Имя файла акта
    */
    function UpdateFiles($id, $file_sf, $file_act) {
        global $DB;
        $sql = "UPDATE lm_docs SET file_sf=?, file_act=? WHERE id=?i";
        $DB->query($sql, $file_sf, $file_act, $id);
    }

	/**
	 * Возвращает все обращения к ЛМ за данный период
	 *
	 * @param string $fdate			    с какого числа
	 * @param string $tdate			    по какое число
	 * @param string $search            Поисковое слово
	 * @param array  $sort              Тип сортировки [login=> DESC ...]
     * @param string $date_search_type  по каким датам ищем, 1-ищем, 0-нет (X1X2, X1 - по дате создания, X2 - по дате отправки документов)
	 * @return array				    информация по обращениям
	 */
    function GetLMRequests($fdate, $tdate, $search=NULL, $sort = NULL, $date_search_type='11') {
        global $DB;
        if($sort) {
            $sort_fld = array_keys($sort);
            $sort_fld = $sort_fld[0];
            $dir = $sort[$sort_fld];
            switch($sort_fld) {
                case 'login':
                    $orderby = "lower(u.login) {$dir}, ao.id";
                    break;
                case 'status':
                    $orderby = "COALESCE(ao.op_date, lmd.docsend_time, lmd.docback_time, 'epoch') {$dir}, ao.id";
                    break;
                case 'date':
                    $orderby = "ao.id {$dir}";
                    break;
                default:
                    $orderby = "ao.id";
                    break;
            }
        }

        $where = '';
        if(substr($date_search_type,0,1)) {
            // дата создания
            $where .= " (ao.op_date >= '$fdate' AND ao.op_date-'1 day'::interval < '$tdate') OR ";
        }
        if(substr($date_search_type,1,1)) {
            // дата отправки документов
            $where .= " (lmd.docsend_time >= '$fdate' AND lmd.docsend_time-'1 day'::interval < '$tdate') OR ";
        }
        if($where)
            $where = '('.preg_replace("/OR $/","",$where) . ')';

        if($search) {
            $where .= $where ? ' AND' : 'WHERE';
            $where .= " (u.uname ilike '%{$search}%'
                         OR u.usurname ilike '%{$search}%' 
                         OR u.login ilike '%{$search}%')";
        }

        $sql = "SELECT lmd.*,
                       u.*,
                       ao.op_date,
                       ao.id as ao_id,
                       (-1*ao.ammount) as ao_ammount
                FROM users as u
                LEFT JOIN 
                    account a 
                    ON a.uid=u.uid 
                LEFT JOIN 
                    account_operations ao 
                    ON ao.billing_id=a.id 
                LEFT JOIN 
                    op_codes oc 
                    ON oc.id = ao.op_code
                LEFT JOIN
                    lm_docs lmd
                    ON lmd.opid = ao.id
                WHERE 
                    ao.op_code = 82 AND 
                    {$where}
                ORDER BY {$orderby}";

		return $DB->rows($sql);
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

}
?>
