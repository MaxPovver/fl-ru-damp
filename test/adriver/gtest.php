<?php

exit;

ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/adriver.php");

?>
<html>
    <head>
        <script type="text/javascript" src="/scripts/adriver.core.2.js"></script>
        <script type="text/javascript" src="/scripts/mootools-new.js" charset="windows-1251"></script>
        <script type="text/javascript" src="/scripts/mootools-more.js" charset="windows-1251"></script>
        <script type="text/javascript" src="/scripts/mootools-Assets.js" charset="windows-1251"></script>        
        <script type="text/javascript" src="/scripts/banners.js" charset="windows-1251"></script>
        
        <script type="text/javascript">
            <?= adriver::target(); ?>
        </script>        
        
    </head>
    <body>
        
        
	<!-- Banner 240x400 -->
        <?= printBanner240(false); ?>
	<!-- end of Banner 240x400 -->           
        
    </body>
</html>