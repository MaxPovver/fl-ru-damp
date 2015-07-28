<?
/* @deprecated Не используется в системе, и файла /nycompetition07/xajax/xajaxExtend.php уже не существует*/
exit;
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/nycomp07.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/nycompetition07/xajax/xajaxExtend.php");

function UploadPic ($aFormValues){
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
    global $session;
    session_start();
    get_uid(false);
    print_r($aFormValues);
    $objResponse = new xajaxResponse();

}
$xajax->processRequest();
?>
