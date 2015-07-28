<?php
define('NO_CSRF', true);
header('Content-Type: text/plain; charset=windows-1251');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/yd_payments.php');
if(!$_POST['ACT_CD']) {
    header('HTTP/1.1 404 Not Found');
    exit;
}

define('YD_MAX_AMT', 500);
$RES_CD=0;
$ERR_CD=0;
$PERFORMED_DT=date('c');
if($_POST['ACT_CD']==1 || $_POST['ACT_CD']==1003)
    $BALANCE=3000;
    
if(mt_rand(1,150) > 149) {
    $RES_CD=30;
    $ERR_CD=30;
}
else if(mt_rand(1,100) < 2) {
    $RES_CD=1;
    $ERR_CD=0;
}
else if(substr($_POST['DSTACNT_NR'], -1, 1) == '3') {
    $RES_CD=3;
    $ERR_CD=41;
}
else if($_POST['TR_AMT'] > YD_MAX_AMT) {
    $RES_CD=3;
    $ERR_CD=43;
}

if($RES_CD + $ERR_CD > 0) {
    $PERFORMED_DT=NULL;
    $BALANCE=NULL;
}

echo "
RES_CD=$RES_CD
ERR_CD=$ERR_CD
PERFORMED_DT=$PERFORMED_DT
BALANCE=$BALANCE
";
