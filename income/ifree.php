<?

define('NO_CSRF', 1);
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/ifreepay.php");

$ifree = new ifreepay($_REQUEST, true, true);

?>
