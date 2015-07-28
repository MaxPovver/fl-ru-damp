<?php
define( 'IS_SITE_ADMIN', 1 );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/account.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/exrates.php' );

session_start();
get_uid();

if ( !(hasPermissions('bankalpha') && hasPermissions('adm')) ) {
    header("Location: /404.php");
    exit;
}

$no_banner  = 1;
$rpath      = '../../';
$content    = '../content2.php';
$header     = $rpath . 'header.php';
$footer     = $rpath . 'footer.html';
$inner_page = 'index_inner.php';
$css_file = array( 'calendar.css','moderation.css','nav.css','new-admin.css' );
$js_file    = array( 'calendar.js' );
$sError     = '';

$action  = $_REQUEST['action'];
$account = new account();
$exrates = new exrates();

if ( $action == 'add' ) {
    $sLogin = $_POST['login'];
    $nSummR = floatval( $_POST['summ'] );
    $nStamp = strtotime($_POST['date'] . ' ' . $_POST['time']);
    $oUser  = new users();
    
    $oUser->GetUser( $sLogin );
    
    if ( $oUser->uid ) {
        if( $nSummR > 0 ) {
            if ( intval($nStamp) ) {
                $nRate   = $exrates->GetField( 51, $err, 'val' );
                $nSummFM = round($nSummR, 2 );
                $sAdmin  = 'Запись добавил: ' . $_SESSION['login'];
                $comments  = "Безналичный перевод для физ.лиц, логин {$oUser->login}, Альфа-банк";
                $sDate   = date( 'c', $nStamp );
                
                if ( 
                    $account->GetInfo($oUser->uid, true) 
                    && !$err = $account->depositEx($account->id, $nSummFM, $sAdmin, $comments, 12, $nSummR, 11, $sDate) 
                ) {
                    // ВРЕМЕННАЯ АКЦИЯ! -------------
                    // ВРЕМЕННАЯ ЗАКОМЕНТЕНО! :) -------------
                    // $account->alphaBankGift( $nSummR, $sDate, $oUser->uid, $oUser->login );
                    //-------------------------------
                    
                    // обновляем сессию юзера
                    $session = new session();
                    $session->UpdateProEndingDate( $oUser->login );
                    
                	$_SESSION['success'] = 'ok';
                    $sReferer = $_SERVER['HTTP_REFERER'];
                    $sReferer = ( preg_match('~siteadmin/alpha~i', $sReferer) ) ? $sReferer : '/siteadmin/alpha/';
                	header( 'Location: ' . $sReferer );
                	exit;
                }
            }
            else {
                $sError = 'Дата указана не верно';
            }
        }
        else {
            $sError = 'Сумма должна быть числом больше 0';
        }
    }
    else {
        $sError = 'Пользователь не найден';
    }
}
elseif ( $action == 'del' ) {
	if ($_SESSION["rand"] != $_POST["u_token_key"]) {
        header ("Location: /404.php");
        exit;
	}
	
    $sId    = intval($_GET['id']);
    $sUid   = intval($_GET['uid']);
    $bForce = isset($_GET['force']);
    
    if ( $sId && $sUid ) {
        $aInfo = account::getOperationInfo( $sId );
        
    	if ( $account->GetInfo($sUid) && ($bForce || $account->sum >= $aInfo['ammount']) ) {
    	    $account->Del( $sUid, $sId );
    	    
    	    // уведомление о том, что ошибочно зачисленное списано
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/smail.php' );
			$sm = new smail();
			$sm->alphaBankMistakeSorry( $sUid, $aInfo['op_date'] );
    	    
    	    $_SESSION['success'] = 'ok';
        	header( 'Location: /siteadmin/alpha/?ds=' . $_GET['ds'] . '&de=' . $_GET['de'] );
        	exit;
    	}
    	else {
    	    $bAskForce = true;
    	}
    }
    else {
        $sError = 'Данные указаны не верно';
    }
}

if ($_GET['ds']) $ds = date("Y-m-d",strtotime($_GET['ds']));
if ($_GET['de']) $de = date("Y-m-d",strtotime($_GET['de']));
if (!$ds) $ds = date("Y-m-d",mktime(0, 0, 1, date('m'), date('d'), date('Y')));
if (!$de) $de = date("Y-m-d",mktime(23, 59, 59, date('m'), date('d'), date('Y')));

$aData = $account->getPayUsers( array('alpha' => 1), $ds, $de );

include( $rpath . 'template2.php' );
