<?
define("XAJAX_DEFAULT_CHAR_ENCODING","windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
$xajax = new xajax("/xajax/ourcontacts.server.php");
//$xajax->setFlag('debug',true);
$xajax->configure('decodeUTF8Input',true);
$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);

$xajax->register(XAJAX_FUNCTION, "GetGroups");
$xajax->register(XAJAX_FUNCTION, "GetGroupsForSelect");
$xajax->register(XAJAX_FUNCTION, "DeleteGroup");
$xajax->register(XAJAX_FUNCTION, "AddGroup");
$xajax->register(XAJAX_FUNCTION, "GetGroupTitle");
$xajax->register(XAJAX_FUNCTION, "UpdateGroup");
$xajax->register(XAJAX_FUNCTION, "AddContact");
$xajax->register(XAJAX_FUNCTION, "DeleteContact");
$xajax->register(XAJAX_FUNCTION, "DeleteContacts");
$xajax->register(XAJAX_FUNCTION, "GetContactInfo");
$xajax->register(XAJAX_FUNCTION, "EditContact");
$xajax->register(XAJAX_FUNCTION, "AddContactsForMail");
$xajax->register(XAJAX_FUNCTION, "AddContactsByGroupsForMail");
$xajax->register(XAJAX_FUNCTION, "GetGroupsForMailerDialog");
$xajax->register(XAJAX_FUNCTION, "MailerToggleContacts");
$xajax->register(XAJAX_FUNCTION, "SaveContactsMailer");
?>
