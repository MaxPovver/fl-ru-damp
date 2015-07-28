<?
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/masssending.php");
session_start();
get_uid();
  
if(!(hasPermissions('adm') && hasPermissions('masssending'))) {
  header ("Location: /404.php");
  exit;
}

$masssending = new masssending;

$error  ='';
$alert  = NULL;
$action = __paramInit('string', 'action', 'action');
$om     = __paramInit('string', 'om', 'om', masssending::OM_NEW);
$page   = __paramInit( 'int', 'page', 'page', 1 );
$per_page = 10;
$denied_reason = '';
$tariff = masssending::GetTariff();

switch($action)
{
  case 'Change.tariff' :
    
    $pro    = __paramInit('float', NULL, 'pro', 0);
    $no_pro = __paramInit('float', NULL, 'no_pro', 0);
    //break;
    if(masssending::SetTariff($pro, $no_pro)) {
      header ("Location: /siteadmin/masssending/?om={$om}&result=success");
      exit;
    }
    break;

  case 'Decide' :
  
    $id    = __paramInit('int', NULL, 'id', 0);
    //$is_accepted = __paramInit('int', NULL, 'Accept_x');
	$is_accepted = (!empty($_POST['status']) && $_POST['status'] == 'Accept');
    $denied_reason = NULL;

    if(!$is_accepted) {
      $denied_reason = __paramInit('string', NULL, 'denied_reason');
      if(is_empty_html($denied_reason))
        $alert[$id]['denied_reason'] = '¬ведите причину отказа';
      else
		$masssending->Deny($id, $denied_reason);
    }
    else
	  $masssending->acceptByAdmin($id);

    if(!$error && !$alert) {
      header ("Location: /siteadmin/masssending/?om={$om}");
      exit;
    }
    
    break;
}

$content = "../content.php";
$js_file = array( '/css/block/b-popup/b-popup.js' );
$css_file = array('moderation.css','nav.css' );
$inner_page = "inner_index.php";

$header = $rpath."header.php";
$footer = $rpath."footer.html";

include ($rpath."template.php");

?>
