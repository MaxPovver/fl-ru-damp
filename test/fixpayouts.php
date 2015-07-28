<?php
ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once("../classes/config.php");
require_once("../classes/pskb.php");

pskb::fixStagePayoutsCompleted();

?>