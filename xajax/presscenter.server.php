<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/presscenter.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
* Реорганизация членов комманды в категориях
*
* @param    string  $order  Порядок членов комманды в категориях
*
*/
function ReorderTeam($order) {
    session_start();
    $objResponse = new xajaxResponse();
    if (hasPermissions('users')) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/team.php");
        team::ReorderTeam($order);
    }
    return $objResponse;
}

/**
* Получение информации о пользователе
*
* @param    integer $id     ID пользователя
*
*/
function GetPeopleTeamInfo($id) {
    session_start();
    $objResponse = new xajaxResponse();
    if (hasPermissions('users')) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/team.php");
        $user = team::GetUser($id);
        $user['name'] = preg_replace(array("/&quot;/","/&#039;/"),array('"',"'"),$user['name']);
        $user['occupation'] = preg_replace(array("/&quot;/","/&#039;/"),array('"',"'"),$user['occupation']);
        $user['info'] = preg_replace(array("/&quot;/","/&#039;/"),array('"',"'"),$user['info']);
        $objResponse->assign("pt_id", "value", $user['id']);
        $objResponse->assign("pt_name", "value", $user['name']);
        $objResponse->assign("pt_login", "value", $user['login']);
        $objResponse->assign("pt_occupation", "value", $user['occupation']);
        $objResponse->assign("pt_position", "value", $user['position']);
        $objResponse->assign("pt_info", "value", $user['info']);
        $objResponse->assign("pt_group", "value", $user['groupid']);
        if($user['userpic']) {
            $objResponse->assign("pt_photo_file", "src", WDCPREFIX.'/team/'.$user['userpic']);
            $objResponse->assign("pt_photo_file", "style.display", 'inline');
        }
    }
    return $objResponse;
}

/**
* Удаление фотографии
*
* @param    integer $id     ID пользователя
*
*/
function DeletePhoto($id) {
    session_start();
    $objResponse = new xajaxResponse();
    if (hasPermissions('users')) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/team.php");
        team::DeletePhoto($id);
        $objResponse->assign("peoplephoto_".$id, "src", '/images/team_no_foto.gif');
        $objResponse->assign("pt_photo_file", "style.display", 'none');
    }
    return $objResponse;
}


$GLOBALS['xajax']->processRequest();
?>
