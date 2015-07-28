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
$TITLE_KEY = 'statistics.title';

require_once(dirname(__FILE__).'/inc/admin_prolog.php');


require_once('../classes/functions.php');
require_once('../classes/class.thread.php');
require_once('../classes/class.operator.php');
require_once('../classes/class.smartyclass.php');


Operator::getInstance()->IsCurrentUserAdminOrRedirect();

$tmlPage = null;
$TML = new SmartyClass($TITLE_KEY);

$tmlPage['availableDays'] = range(1, 31);
$currTime = getCurrentTime();
$tmlPage['availableMonth'] = get_month_selection($currTime-400*24*60*60, $currTime+50*24*60*60);
$tmlPage['showresults'] = false;
$tmlPage['departments'] = MapperFactory::getMapper("Department")->enumDepartments(Resources::getCurrentLocale());
$tmlPage['locales'] = getAvailableLocalesForChat();

Operator::getInstance()->ensureOperatorsAreInLastAccess();

$errors = array();
if (isset($_GET['startday'])) {
  $startday = verify_param("startday", "/^\d+$/");
  $startmonth = verify_param("startmonth", "/^\d{2}.\d{2}$/");
  $endday = verify_param("endday", "/^\d+$/");
  $endmonth = verify_param("endmonth", "/^\d{2}.\d{2}$/");
  $start = get_form_date($startday, $startmonth);
  $end = get_form_date($endday, $endmonth)+24*60*60;
  $locale = verify_param("locale", "/^(en|ru)$/");
  $departmentid = verify_param("departmentid", "/^\d+$/");
  
  if ($start > $end) {
    $errors[] = Resources::Get("statistics.wrong.dates");
  }
  
  Operator::getInstance()->loadOnlineStatsIntoDB();
  $tmlPage['reportByDate'] = MapperFactory::getMapper("Thread")->getReportByDate($start, $end, $departmentid, $locale);

  $tmlPage['reportByDateTotal'] = MapperFactory::getMapper("Thread")->getReportTotalByDate($start, $end, $departmentid, $locale);

  $tmlPage['reportByAgent'] = Thread::getInstance()->GetReportByAgent($start, $end, $departmentid, $locale);
  
  $tmlPage['reportLostVisitors'] = MapperFactory::getMapper("LostVisitor")->getReportByOperator($start, $end, $departmentid, $locale);
  $tmlPage['reportInterceptedVisitors'] = MapperFactory::getMapper("LostVisitor")->getReportInterceptedByOperator($start, $end, $departmentid, $locale);
  
  $tmlPage['reportByAgentByDate'] = MapperFactory::getMapper("Operator")->getAdvancedReportByDate($start, $end, $departmentid, $locale);
  
  
  $tmlPage['locale'] = $locale;
  $tmlPage['departmentid'] = $departmentid;
  
  $tmlPage['showresults'] = count($errors) ? 0 : 1;

  $tmlPage["formstartday"] = date("d", $start);
  $tmlPage["formstartmonth"] = date("m.y", $start);

  $tmlPage["formendday"] = date("d", $end-24*60*60);
  $tmlPage["formendmonth"] = date("m.y", $end-24*60*60);
} else {
  $curr = getdate($currTime);
  if ($curr['mday'] < 7) {
    // previous month
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

if (!empty($errors)) $TML->assign('errors', $errors);
$TML->assign('page_settings', $tmlPage);

$TML->display('statistics.tpl');

require_once(dirname(__FILE__).'/inc/admin_epilog.php');
?>