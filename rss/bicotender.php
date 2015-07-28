<?php
define('IS_EXTERNAL', 1);
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/projects.php";
header('Content-type: text/xml; charset=utf-8');
print projects::bicotenderGenerateRss($_GET["date"]);