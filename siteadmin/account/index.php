<?
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
	$rpath = "../../";
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pf.php");
    
	session_start();
	get_uid();
	
	if (!(hasPermissions('statsaccounts') || hasPermissions('tmppayments')))
		{header ("Location: /404.php"); exit;}
                
$account = new account();

if (
        $_GET['action']=='getsmsinfoincsv' 
        && preg_match("/^[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}$/", $_GET['fdate']) 
        && preg_match("/^[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}$/", $_GET['tdate'])
   ) {
    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"SMSInfo.csv\"");
    $smsinfo = $account->getSmsInfoInCSV($_GET['fdate'], $_GET['tdate']);
    $csv = "evtId;MSISDN;SmsText;Time\n";
    if($smsinfo) {
        foreach($smsinfo as $sms) {
            $csv .= "\"{$sms['evtid']}\";\"{$sms['msisdn']}\";\"{$sms['smstext']}\";\"{$sms['time']}\"\n";
        }
    }
    echo $csv;
    exit;
}
	
$content = "../content.php";
$css_file = array('moderation.css','nav.css' );

$inner_page = "inner_index.php";

$header = $rpath."header.php";
$footer = $rpath."footer.html";

include ($rpath."template.php");

?>
