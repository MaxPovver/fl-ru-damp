<?
/**
 * Подключаем файл с основными функциями
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/reqv.php");

/**
 * Класс, обрабатывающий реквизиты оплаты по безналичному расчету, по которым уже произведена оплата (табл. reqv_ordered)
 *
 */
class reqv_ordered extends reqv
{
	
	/**
	 * Пришла ли платежка
	 *
	 * @var boolean
	 */
	public $pcheck;
	
	/**
	 * Пришли ли деньги в оплату
	 *
	 * @var boolean
	 */
	public $payed;
	
	/**
	 * сумма на зачисление
	 *
	 * @var float
	 */
	public $ammount;
	
	/**
	 * Дата выписки счета
	 *
	 * @var string (Postgres timestamp)
	 */
	public $op_date;
	
	/**
	 * Дата редактирования
	 *
	 * @var string (Postgres timestamp)
	 */
	public $edited;
	/**
	 * Дата прихода платежки
	 *
	 * @var string (Postgres timestamp)
	 */
	public $pcheck_time;
	
	/**
	 * Дата прихода денег на счет
	 *
	 * @var string (Postgres timestamp)
	 */
	public $payed_time;
	
	/**
	 * Высланы ли документы
	 *
	 * @var boolean
	 */
	public $docsend;
	
	/**
	 * Дата отправки документов
	 *
	 * @var string (Postgres timestamp)
	 */
	public $docsend_time;
	
	/**
	 * id зачисления средст на счет юзера (табл. account_operations)
	 *
	 * @var integer
	 */
	public $billing_id;
	
	/**
	 * Номер счета по порядку для данного юзера
	 *
	 * @var integer
	 */
	public $bill_no;
	
	/**
	 * Код операции, для которой предназначены эти деньги (op_codes)
	 *
	 * @var integer
	 */
	public $op_code;
	
	/**
	 * Вернулись ли документы?
	 *
	 * @var boolean
	 */
	public $docback;
	
	/**
	 * Дата возврата документов
	 *
	 * @var string (Postgres timestamp)
	 */
	public $docback_time;
	
	/**
	 * id Сделки без Риска (ее деньги резервируются под нее)
	 *
	 * @var integer
	 */
	public $norisk_id;


 public $file_sf;
 public $file_act;
	
	/**
	 * Конструктор класса
	 *
	 * @param object $reqv класс reqv 
	 */
	function reqv_ordered($reqv = 0){
		if ($reqv){
			$class_vars = get_class_vars(get_class($reqv));
			foreach ($class_vars as $name => $value) {
    			if (isset($reqv->$name)){
    				$this->$name = $reqv->$name;
    			}
			}
		}
	}
	
	/**
	 * Делает запись в таблице о выставленном счете по текущим реквизитам
	 *
	 * @return integer
	 */
	function SetOrdered(){
		unset($this->id);
		$error = '';
		$ret = $this->Add($error, 1);
		return $ret;
	}
	
	/**
	 * Возвращает все счета, выписанные за данный период
	 *
	 * @todo Привести код функции в удобочитаемый вид
	 * 
	 * @param string $fdate			    с какого числа получить счета
	 * @param string $tdate			    по какое число
	 * @param string $search            Поисковое слово
	 * @param array  $sort              Тип сортировки [login=> DESC, fio=>ASC, ...]
     * @param string $date_search_type  по каким датам ищем, 1-ищем, 0-нет (X1X2X3, X1 - по дате создания, X2 - по дате оплаты, X3 - по дате отправки документов)
	 * @return array				    инфа по счетам
	 */
	function GetOrders($fdate, $tdate, $search=NULL, $sort = NULL, $date_search_type='111'){
	  if($sort) {
  	  $sort_fld = array_keys($sort);
  	  $sort_fld = $sort_fld[0];
  	  $dir = $sort[$sort_fld];
  	  switch($sort_fld) {
  	    case 'login': $orderby = "lower(u.login) {$dir}, ro.id"; break;
  	    case 'fio': $orderby = "COALESCE(NULLIF(lower(ro.full_name),''), lower(ro.org_name), '') {$dir}, ro.id"; break;
  	    case 'sum': $orderby = "ro.ammount {$dir}, ro.id"; break;
  	    case 'status': $orderby = "COALESCE(ro.payed_time, ro.pcheck_time, ro.docsend_time, ro.docback_time, 'epoch') {$dir}, ro.id"; break;
  	    case 'date': $orderby = "ro.id {$dir}"; break;
  	    default: $orderby = "ro.id"; break;
  	  }
  	}
    $where = '';
    if(substr($date_search_type,0,1)) {
        // дата создания
        $where .= " (op_date >= '$fdate' AND op_date-'1 day'::interval < '$tdate') OR ";
    }
    if(substr($date_search_type,1,1)) {
        // дата оплаты
        $where .= " (payed_time >= '$fdate' AND payed_time-'1 day'::interval < '$tdate') OR ";
    }
    if(substr($date_search_type,2,1)) {
        // дата отправки документов
        $where .= " (docsend_time >= '$fdate' AND docsend_time-'1 day'::interval < '$tdate') OR ";
    }

    if($where)
        $where = '('.preg_replace("/OR $/","",$where) . ')';

    if($search) {
        $where .= $where ? ' AND' : 'WHERE';
        $where .= " (ro.fio ilike '%{$search}%'
                     OR ro.org_name ilike '%{$search}%'
                     OR ro.full_name ilike '%{$search}%'
                     OR 'Б-'||a.id||'-'||(COALESCE(ro.bill_no,0)+1) ilike '%{$search}%'
                     OR 'Б-СБР-'||s.id||'-'||CASE s.scheme_type WHEN 1 THEN 'А' WHEN 2 THEN 'П' ELSE 'Т' END||'/О' ilike '%{$search}%'
                     OR u.login ilike '%{$search}%')";
    }

    if($where) $where = ' WHERE '.$where;
    
        global $DB;
		$sql = 
		"SELECT ro.*, u.*, s.scheme_type, a.id as billing_id, ro.city as city_name, scheme_type
       FROM reqv_ordered ro
     INNER JOIN
       users u
         ON u.uid = ro.user_id
     LEFT JOIN
       sbr s
         ON s.id = ro.sbr_id
     INNER JOIN
       account a
         ON a.uid = u.uid
  		{$where}
			ORDER BY {$orderby}";


		$ret   = $DB->rows( $sql );
		$error = $DB->error;
		
		if ( $error ) {
			$ret = null;
		}
		return $ret;
	}
}
?>
