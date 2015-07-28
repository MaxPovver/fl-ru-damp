<?php

define('NO_CSRF', 1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Verification.php';

$uid    = get_uid();
$sCode  = __paramInit( 'string', 'code', null, '' );  // временный токен Яндекс.Денег
$sError = __paramInit( 'string', 'error', null, '' ); // код ошибки ответа Яндекс.Денег
$sType  = __paramInit( 'string', 'type', null, '' );  // тип возврата
$sId    = __paramInit( 'string', 'id', null, '' );    // id проекта
$sfName    = __paramInit( 'string', 'fname', null, '' );    // Имя
$slName    = __paramInit( 'string', 'lname', null, '' );    // Фамилия
$error  = '';
$exterr = '';

if ( $uid ) {
    if ( $sError || !$sCode ) {
        $error = 'Произошла ошибка во время верификации.';
    }
    else {
        $verification = new Verification;
        
        if ( $verification->ydCheckUserReqvs($uid) ) {
            $sIsEmp = is_emp() ? 't' : 'f';
            
            if ( !$verification->ydVerification( $uid, $sIsEmp, $sCode, $sfName, $slName ) ) {
                $error = $verification->error;
            }
        }
        else {
            $error = $verification->error;
        }
    }
}
else {
    $error  = Verification::ERROR_NO_AUTH;
    $exterr = 'noagain';
}

switch($sType) {
    case 'promo':
    case 'project':
        $redirect = "/promo/verification/";
        if($error) {
            $redirect .= "?verror=1";
        } else {
            $_SESSION['verifyStatus'] = array( 'status' => 1 );
            $_SESSION['is_verify']    = 't';
            $redirect .= "?vok=1";
        }
        ?>
        <html><body><script>window.close();</script></body></html>
        <?
        exit;
        break;
    default:
        if ( $error ) {
            $_SESSION['verifyStatus'] = array( 
                'status' => 0, 
                'text'   => $error, 
                'exterr' => $exterr 
            );
        } else {
            $_SESSION['verifyStatus'] = array( 'status' => 1 );
            $_SESSION['is_verify']    = 't';
        }
        $redirect = "/promo/verification/?service=yd&done";
        break;
}

header( 'Location: '.$redirect );
exit;