<?php


ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 



define('PRIMARY_WMID', '284917267100');
define('URL_UD', '0F2A144A-1BC0-433D-A1AC-A49901459726');

require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/WMXI/WMXILogin.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/payment_keys.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/WMXI/WMXI.php');

$wmxi = new WMXILogin(URL_UD, PRIMARY_WMID, realpath($_SERVER['DOCUMENT_ROOT'] . '/classes/WMXI/WMXI.crt'));
  
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($wmid = $wmxi->AuthorizeWMID()) {
        
        
        $wmxi = new WMXI;
        $key  = array( 'file' => WM_VERIFY_KEYFILE, 'pass' => WM_VERIFY_KEYPASS );
        $wmxi->Classic(WM_VERIFY_WMID, $key);
        $res = $wmxi->X11($wmid, 0, 1, 0);
        $res = $res->toObject();

        $v = $res->certinfo->attestat->row['tid'];
        
        
        
        echo $v;
        exit;
    }
}

?>
<a href="https://login.wmtransfer.com/GateKeeper.aspx?RID=<?=URL_UD?>">
    Верификация WM
</a>
