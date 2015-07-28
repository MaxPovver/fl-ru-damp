<?
require_once("classes/stdf.php");

 function vardump($v) {
        $html = '';
        $html .= "<pre>";
        $html .= var_export($v,true);
        $html .= "</pre>";
        print $html;
    }

require_once($_SERVER['DOCUMENT_ROOT'] . "/engine/init.php");

?>
