<?
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
$rpath = "../../";

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php');

session_start();
get_uid();

if (!(hasPermissions('adm') && hasPermissions('suspicioususers'))) {
    header ("Location: /404.php"); 
    exit;
}

$sUid = intval($_GET['uid']);

$action = $_GET['action'];
if(!$action) $action = $_POST['action'];


switch($action) {
    case "hide":
        if($sUid > 0) {
            users::approveSuspiciousUser( $sUid );
            $tail = '';
            if ((int)$_GET['page'] > 0) {
                $tail = '?page='.intval($_GET['page']);
            }
            header('Location: index.php'.$tail);
            exit;   
        }
        break;
    case "activate":
        if($sUid > 0) {
            users::approveSuspiciousUser( $sUid );
            // отпавляем юзеру письмо с кодом активации. он и не узнает что модерация была
            $aData = users::getSuspectActivationData( $sUid );
            
            if ( $aData ) {
            	require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/smail.php' );
        		$smail = new smail();
        		$smail->NewUser( $aData['login'], false, $aData['code'] );
            }
            $tail = '';
            if ((int)$_GET['page'] > 0) {
                $tail = '?page='.intval($_GET['page']);
            }
            header('Location: index.php'.$tail);
            exit;    
        }
        break;
    case "userban":
        if(!$sUid) break;
        // юзер не забанен на всем сайте
        $objUser = new users();
        $objUser->GetUserByUID( $sUid );
        
        if ( $objUser->uid ) {
            $sReason = 'Подозрительный пользователь: логин, имя или фамилия содержат подозрительные слова.';
            
            if ( $objUser->ban_where ) {
                // меняем бан в блогах на бан на всем сайте
                $objUser->updateUserBan( $sUid, 1, 0, $sReason, null, '' );
            }
            elseif ( !$objUser->is_banned ) {
                // баним на всем сайте
                $sBanId   = $objUser->setUserBan( $sUid, 0, $sReason, null, '', 1 );
                $sObjName = $objUser->uname. ' ' . $objUser->usurname . '[' . $objUser->login . ']';
                $sObjLink = '/users/' . $objUser->login;
                
                // пишем лог админских действий
                admin_log::addLog( admin_log::OBJ_CODE_USER, 3, $sUid, $sUid, $sObjName, $sObjLink, -1, '', null, $sReason, $sBanId );
            }
            
            users::banSuspiciousUser( $sUid );
            $tail = '';
            if ((int)$_GET['page'] > 0) {
                $tail = '?page='.intval($_GET['page']);
            }
            header('Location: index.php'.$tail);
            exit;
        }
        break;
    case "ban":
        if(!$sUid) break;
        // юзер уже забанен на всем сайте - нужно только убрать его из списка подозрительных
        users::banSuspiciousUser( $sUid );
        $tail = '';
        if ((int)$_GET['page'] > 0) {
            $tail = '?page='.intval($_GET['page']);
        }
        header('Location: index.php'.$tail);
        exit;
        break;
    case "reset":
        //users::resetAllSuspiciousUsers();
        header('Location: index.php');
        exit;
        break;
    case "clear":
        users::approveAllSuspiciousUsers();
        header('Location: index.php');
        exit;
        break;
    case "save_words":
        $error = users::setSuspeciousWordsName($_POST['suspicious_words'], $_POST['type']);
        if($error !== false) {
            header('Location: index.php');
            exit;
        } else {
            $error_string = "Неизвестная ошибка записи.";
        }
        break;
        
}

$page = __paramInit('int', "page");
if (!$page) {
    $page = 1;
}
$itemsPerPaging = 10; //количетво ссылок в строке навигации
$recordsPerPage = 20; //количество запиисей на странице
$itemBack = false;
$itemNext = false;
$totalSuspiciousUsers = users::GetCountSuspiciousUsers();
$totalPages = ceil($totalSuspiciousUsers / $recordsPerPage);
$currentPaging = floor( ($page - 1) / $itemsPerPaging);  //текущая страница навигации

if ($currentPaging > 0) {
    $itemBack = true;
}
if ($currentPaging*$itemsPerPaging + $itemsPerPaging < $totalPages) {
    $itemNext = true;
}
$pagingStart = $currentPaging*$itemsPerPaging + 1;
$pagingLimit = $currentPaging*$itemsPerPaging + $itemsPerPaging + 1;
if ($pagingLimit > $totalPages) {
    $pagingLimit = $totalPages;
}
$mRid = users::GetSuspiciousUsers(($page - 1)*$recordsPerPage, $recordsPerPage);

$content = "../content2.php";
$header = $rpath."header.php";
$footer = $rpath."footer.html";
$inner_page = "inner_index.php";
$css_file   = array( 'moderation.css', 'new-admin.css', 'nav.css' );

include ($rpath."template2.php");

?>
