<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/sbr.common.php");
$xajax->printJavascript('/xajax/');
?>
<script type="text/javascript">
var SBR;
window.addEvent('domready', function() { SBR = new Sbr('adminFrm'); } );
</script>
<? include($fpath.'tpl.admin-header.php'); ?>
<? include($inner); ?>
