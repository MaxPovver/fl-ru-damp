<?php


/*
 * https://beta.free-lance.ru/mantis/view.php?id=29159
 */

ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php');

//------------------------------------------------------------------------------



//успешные сделки
$uids = $DB->col("
    SELECT u.uid
    FROM account AS a
    INNER JOIN freelancer AS u ON u.uid = a.uid
    WHERE a.sum < -30 AND u.is_banned = B'0'
    LIMIT 1
");

$logins = array();

if ($uids) {
    
    $objUser = new users();
    
    foreach ($uids as $uid) {
        
        $objUser->GetUserByUID( $uid );
        
        if ( !$objUser->uid ) {
            continue;
        }
        
        $sReason = "Приостановка аккаунта до погашения задолженности. Обратитесь в поддержку <a href=\"mailto:support@fl.ru\">support@fl.ru</a> когда будете готовы погасить задолженность за услуги сайта.";
        $sBanId = $objUser->setUserBan( $uid, 0, $sReason, null);
        
        // пишем лог админских действий
        $sObjName = $objUser->uname. ' ' . $objUser->usurname . '[' . $objUser->login . ']';
        $sObjLink = '/users/' . $objUser->login;
        admin_log::addLog( 
                admin_log::OBJ_CODE_USER, 
                3, 
                $uid, 
                $uid, 
                $sObjName, 
                $sObjLink, 
                0, 
                '', 
                null, 
                $sReason, 
                $sBanId, 
                '', 
                103);//admin
        
        $logins[] = $objUser->login;
        
        //sleep(20);
    }
}

print_r($logins);
exit;