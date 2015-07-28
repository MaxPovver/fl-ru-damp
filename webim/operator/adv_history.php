<?php
/* 
 * 
 * Данный файл является частью проекта Веб Мессенджер.
 * 
 * Все права защищены. (c) 2005-2009 ООО "ТОП".
 * Данное программное обеспечение и все сопутствующие материалы
 * предоставляются на условиях лицензии, доступной по адресу
 * http://webim.ru/license.html
 * 
 */
?>
<?php 
$TITLE_KEY = 'page_analysis.adv.search.title';

require_once(dirname(__FILE__).'/inc/admin_prolog.php');


require_once('../classes/functions.php');
require_once('../classes/class.thread.php');
require_once('../classes/class.operator.php');
require_once('../classes/class.smartyclass.php');
require_once('../classes/class.pagination.php');


$TML = new SmartyClass($TITLE_KEY);

$tmlPage = array();
$operator = Operator::getInstance()->GetLoggedOperator();



$show_empty = isset($_REQUEST['show_empty']) && $_REQUEST['show_empty'] == 1 ? true : false;

if (isset($_REQUEST['q'])) {
  $q = $_REQUEST['q'];
  $items_per_page = verify_param("items", "/^\d{1,3}$/", DEFAULT_ITEMS_PER_PAGE);
  $op_param = verify_param("operator", "/^\d+$/"); // TODO should be operatorid
  $departmentidParam = verify_param("departmentid", "/^\d+$/");
  $localeParam = verify_param($_REQUEST['locale'], "/^[a-z]{2}$/");
  $rateParam = verify_param("rate", "/^\w+$/");
  $startday = verify_param("startday", "/^\d+$/");
  $startmonth = verify_param("startmonth", "/^\d{2}.\d{2}$/");
  $endday = verify_param("endday", "/^\d+$/");
  $endmonth = verify_param("endmonth", "/^\d{2}.\d{2}$/");
  $start = get_form_date($startday, $startmonth);
  $end = get_form_date($endday, $endmonth) + 24*60*60;
  $offlineParam = verify_param("offline", "/^\d+$/");
  if($offlineParam !== null) {
  	$offlineParam = ($offlineParam == 1) ? 0 : 1;
  }
  
  



  if ($start > $end) {
    $errors[] = Resources::Get("search.wrong.dates");
  } else {
    $nTotal = Thread::getInstance()->GetListThreadsAdvCount( $operator['operatorid'], $q, $start, $end, $op_param, $show_empty, $departmentidParam, $localeParam, $rateParam, $offlineParam );
    
    if ( $nTotal ) {
        $pagination = setup_pagination_cnt( $nTotal, $items_per_page );        
        $nLimit     = $pagination['items'];
        $nOffset    = $pagination['start'];
        
        $threads = Thread::getInstance()->GetListThreadsAdv( $operator['operatorid'], $q, $start, $end, $op_param, $show_empty, $departmentidParam, $localeParam, $rateParam, $offlineParam, $nLimit, $nOffset );
        
      $tmlPage['pagination'] = $pagination;
      $tmlPage['pagination_items'] = $threads;

      if (!empty($tmlPage['pagination_items'])) {
        for ($i=0;$i<count($tmlPage['pagination_items']);$i++) {
          $tmlPage['pagination_items'][$i]['diff'] = webim_date_diff($tmlPage['pagination_items'][$i]['modified']-$tmlPage['pagination_items'][$i]['created']);
        }
        $TML->assign('pagination', generate_pagination($tmlPage['pagination']));
      }
    }

    $tmlPage['formq'] = $q;
    $tmlPage['formoperator'] = $op_param;

    $tmlPage["formstartday"] = date("d", $start);
    $tmlPage["formstartmonth"] = date("m.y", $start);

    $tmlPage["formendday"] = date("d", $end - 24*60*60);
    $tmlPage["formendmonth"] = date("m.y", $end - 24*60*60);
//    $TML->assign('pagination', generate_pagination($tmlPage['pagination']));
  } // no errors and need to find
} else { // no query
  $currTime = getCurrentTime();
  $curr = getdate($currTime);
  if ($curr['mday'] < 7) {
    if ($curr['mon'] == 1) {
      $month = 12;
      $year = $curr['year']-1;
    } else {
      $month = $curr['mon']-1;
      $year = $curr['year'];
    }
    $starttime = mktime(0, 0, 0, $month, 1, $year);

    $tmlPage["formstartday"] = date("d", $starttime);
    $tmlPage["formstartmonth"] = date("m.y", $starttime);

    $tmlPage["formendday"] = date("d", mktime(0, 0, 0, $month, date("t", $starttime), $year));
    $tmlPage["formendmonth"] = date("m.y", mktime(0, 0, 0, $month, date("t", $starttime), $year));

  } else {
    $tmlPage["formstartday"] = date("d", mktime(0, 0, 0, $curr['mon'], 1, $curr['year']));
    $tmlPage["formstartmonth"] = date("m.y", mktime(0, 0, 0, $curr['mon'], 1, $curr['year']));

    $tmlPage["formendday"] = date("d", $currTime);
    $tmlPage["formendmonth"] = date("m.y", $currTime);
  }

}

//
// This function should be defined before it's used otherwise on
// some PHP verions it can fail with Fatal error: Call to undefined function
//
function get_operators_list() {
  $operators = Operator::getInstance()->GetAllAccessedOperators();
  $result = array();
  $result[''] = Resources::Get("search.any.operator");

  if (!empty($operators)) {
    foreach ($operators as $op) {
      $result[$op['operatorid']] = $op['fullname'];
    }
  }
  return $result;
}

$tmlPage['availableDays'] = range(1, 31);
$currTime = getCurrentTime();
$tmlPage['availableMonth'] = get_month_selection($currTime-400*24*60*60, $currTime);
$tmlPage['operatorList']   = get_operators_list();
$TML->assign('departments', Operator::getInstance()->enumAvailableDepartmentsForOperator($operator['operatorid'], Resources::getCurrentLocale()));
$TML->assign('locales', getAvailableLocalesForChat());
$tmlPage['show_empty'] = $show_empty;
$TML->assign('page_settings', $tmlPage);
$TML->assign('advanced', true);

$TML->display('thread_search.tpl');

require_once(dirname(__FILE__).'/inc/admin_epilog.php');
?>