<?
define( 'IS_SITE_ADMIN', 1 );
ob_start();
$inner_page = trim($_GET['page']);
$no_banner = 1;
$g_page_id  = "0|21";
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");

session_start();
get_uid();
	
if (!hasPermissions('payments'))
  {header ("Location: /404.php"); exit;}

if(!$inner_page) {
    {header ("Location: /404.php"); exit;}    
}
  
$exch = array(1=>'FM', 'USD','EUR','Руб');  // не менять аббревиатуры, служат кодом.
$ex_cnt = count($exch);
$ri = 4; // индекс рублевой эталонной валюты в $exch.
$error = NULL;
$action = __paramInit('string', NULL, 'action');
  
switch ($action)
{
  case "setRates":
        
    $pex  = project_exrates::GetAll(false);
    $arr   = NULL;
    $exs   = __paramInit('array', NULL, 'ex');

    setlocale(LC_ALL, "en_US.UTF-8");

    foreach($exs as $k=>$v) {
      if((float)$v<=0) {
        $error = 'Пересчет не выполнен. Был указан не правильный курс.';
        break;
      }
      $pex[$k] = $v;
    }
      
    if(!$error) {
      for($i=1;$i<=$ex_cnt;$i++) {
        @$pex[$i.'1'] = 1 / $pex['1'.$i];
        $pex[$i.$i] = 1;
      }

      for($i=1;$i<=$ex_cnt;$i++) {
        for($j=1;$j<=$ex_cnt;$j++) {
          $pex[$i.$j] = $pex[$i.$ri] / $pex[$j.$ri];
        }
      }

      if(!project_exrates::BatchUpdate($pex))
        $error = 'Пересчет не выполнен. Неизвестная ошибка.';
    }


    if(!$error) {
      header ("Location: /siteadmin/projects/?page=exrates&result=success");
      exit;
    }

  
  break;
}

$pex = project_exrates::GetAll(false);

	
$content = "../content.php";

$js_file = array( 'calendar.js' );
$css_file    = array( 'calendar.css','nav.css' );

//$inner_page = trim($_GET['page']);
$inner_page = "index";

$inner_page = "inner_".$inner_page.".php";

$header = $rpath."header.php";
$footer = $rpath."footer.html";

include ($rpath."template.php");

?>
