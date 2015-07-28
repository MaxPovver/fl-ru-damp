<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/safetyphone.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

/**
 * Привязать телефон к аккаунту.
 */
function SafetyPhoneNow() {
    session_start();
    return; // #0019588
    
    $aRes = array('success' => false);
    
    if ( trim($_POST['phone']) == '' ) {
        $aRes['error'] = iconv( 'CP1251', 'UTF-8', 'Вы должны ввести номер телефона');
    }
    else {
        $users = new users();
        
        $sPhone = change_q( stripslashes($_POST['phone']), true );
        $aPhone = $users->CheckSafetyPhone( $sPhone );
        
        if ( $aPhone['error_flag'] == 1 ) {
            $aRes['error'] = iconv( 'CP1251', 'UTF-8', $aPhone['alert'][2]);
        } elseif ( isset($_SESSION['uid']) ) {
        	$sPhoneOnly = ($_POST['phone_only'] == 't' ) ? 't' : 'f';

            if ( $users->updateSafetyPhone($_SESSION['uid'], $sPhone, $sPhoneOnly) ) {
        		$aRes['success'] = true;
        	}
        }
    }
    
    echo json_encode( $aRes );
}

/**
 * Отложить до следующего логина.
 */
function SafetyPhoneLater() {
    session_start();
    
    $aRes = array('success' => false );
    
    if ( isset($_SESSION['uid']) ) {
        $_SESSION['safety_phone_hide'] = true;
        $aRes['success'] = true;
    }
    
    echo json_encode( $aRes );
}

/**
 * Больше не показывать это сообщение.
 */
function SafetyPhoneNever() {
    session_start();
    
    $aRes = array('success' => false );
    
    if ( isset($_SESSION['uid']) ) {
        $users = new users();
        $aRes['success'] = $users->setSafetyPhoneHide( $_SESSION['uid'] );
    }
    
    echo json_encode( $aRes );
}

$xajax->processRequest();

?>