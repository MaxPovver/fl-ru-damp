<?php

if (!defined('IN_STDF')) { 
    header ("Location: /404.php");
    exit; 
}

echo $controller->renderOutput;