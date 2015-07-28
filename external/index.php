<?php
if(!$EXTERNAL_REQ) { // зашли напрямую.
   include('content.php');
   exit;
}

define('IS_EXTERNAL', TRUE);
define('EXTERNAL_DEBUG', 1);
//error_reporting(0);

require_once('../../classes/stdf.php');
require_once(ABS_PATH.'/classes/external/base.php');
externalBase::run($EXTERNAL_REQ);
