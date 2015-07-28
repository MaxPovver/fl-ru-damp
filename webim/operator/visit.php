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

$TITLE_KEY = 'page.visit.title';

 


require_once('../classes/functions.php');
require_once('../classes/class.visitsession.php');
require_once('../classes/class.visitedpage.php');
require_once('../classes/class.pagination.php');
require_once('../classes/class.smartyclass.php');



require_once '../classes/class.geoiplookup.php';


$operator = Operator::getInstance()->GetLoggedOperator();

$visitSession = null;
if (isset($_GET['visitsessionid'])) {
  $visitSession = VisitSession::GetInstance()->GetVisitSessionById($_GET['visitsessionid']);
} elseif (isset($_GET['pageid'])) {
  $visitdpageid = verify_param("pageid", "/^[a-z0-9]{32}$/"); 
  $vistedpage = VisitedPage::GetInstance()->GetVisitedPageById($_GET['pageid']);
  $visitSession = VisitSession::GetInstance()->GetVisitSessionById($vistedpage['visitsessionid']);
}

if (empty($visitSession)) {
  die("Invalid or no visitsessionid or pageid");
}

$visitedPages = VisitedPage::GetInstance()->enumVisitedPagesByVisitSessionId($visitSession['visitsessionid']);
$landingPage = end($visitedPages);
$exitPage = reset($visitedPages);

$timeend = 0;
$timestart = 0;
foreach ($visitedPages as $k => $vp) {
  $timeend = $timeend == 0 ? $vp['updated'] : max($timeend, $vp['updated']);
  $timestart = $timestart == 0 ? $vp['opened'] : min($timestart, $vp['opened']);
  $visitedPages[$k]['sessionduration'] = $vp['updated'] - $vp['opened'];
}


$geodata = GeoIPLookup::getGeoDataByIP($visitSession['ip']);
//for testing purpose
//$geodata = GeoIPLookup::getGeoDataByIP('89.113.218.99');
if($geodata == NULL) {
  $geodata = array('city' => null, 'country' => null, 'lat' => null, 'lng' => null);
}


$tmlPage = array(
  'visitsessionid'=> $visitSession['visitsessionid'],
  'visitedpages' => $visitedPages,
  'landingpage' => $landingPage['uri'],
  'exitpage' => $exitPage['uri'],
  'timestart' => $timestart,
  'timeend' => $timeend,
  'timediff' => webim_date_diff($timeend - $timestart),
  'active' => time() - $timeend < VISITED_PAGE_TIMEOUT,
  'ip' => $visitSession['ip'],

  'city' => $geodata['city'],
  'country' => $geodata['country'],
  'lat' => $geodata['lat'],
  'lng' => $geodata['lng'],

  'browser' => get_user_agent($visitSession['useragent']),
  'enterref' => $landingPage['referrer'],
  'leaveref' => $exitPage['referrer'],
  'historyParams' => array("q" => "".$visitSession['visitorid'])
);

$TML = new SmartyClass($TITLE_KEY);
 

$TML->assign('page_settings', $tmlPage);
$TML->display('visit_info.tpl');
?>