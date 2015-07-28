<?php
define("XAJAX_DEFAULT_CHAR_ENCODING", "windows-1251");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/xajax_core/xajax.inc.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
global $xajax;
if (!$xajax) {
	$xajax = new xajax("/xajax/seo.server.php");
	$xajax->configure('decodeUTF8Input',true);
    //$xajax->configure("debug", true);
	$xajax->configure('scriptLoadTimeout', XAJAX_LOAD_TIMEOUT);
    $xajax->register(XAJAX_FUNCTION, "loadForm");
    $xajax->register(XAJAX_FUNCTION, "createSection");
    $xajax->register(XAJAX_FUNCTION, "deleteSection");
    $xajax->register(XAJAX_FUNCTION, "loadFormEdit");
    $xajax->register(XAJAX_FUNCTION, "updateContentSubdomain"); 
    $xajax->register(XAJAX_FUNCTION, "loadMainForm");
    $xajax->register(XAJAX_FUNCTION, "setTranslit"); 
    $xajax->register(XAJAX_FUNCTION, "loadDirectForm");  
    $xajax->register(XAJAX_FUNCTION, "saveDirectForm");
    $xajax->register(XAJAX_FUNCTION, "deleteDirection");
    $xajax->register(XAJAX_FUNCTION, "getPositions"); 
}
?>