<?php

exit;

//Отключаем запуск основного приложения
//и инклудим библиотеки сами ниже чтобы создать минимальное окружение
define('IN_STDF', 1);


require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/config.php');                                                                                                                                                                              
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/globals.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/memBuff' . (defined('USE_MEMCACHED') ? 2 : 1) . '.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/log.php');   
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DB.php'); 


//Пока такой хак чтобы отдельно 
//конфиг не делать для мини окружения
//так в DAV там хост соответствующий
if (is_release()) {
    $host = HTTP_PREFIX . 'www.fl.ru';
} elseif (is_beta()) {
    $host = 'beta.fl.ru';
} elseif (!is_local()) {
    $host = 'alpha.fl.ru';
}

session_start();
$uid = isset($_SESSION['uid'])? $_SESSION['uid'] : 0;
$filename = isset($_GET['path'])? ltrim(parse_url($_GET['path'], PHP_URL_PATH) ,'/') : null;

if ($uid <= 0 || !$filename) {
    header("Location: {$host}/404.php");
    exit;
}

//Исправляем не корректный путь у папки юзера
$components = explode('/', $filename);
$components_cnt = count($components);
if ($components[0] === 'users' && $components_cnt > 2) {
    if (strlen($components[1]) > 2) {
        array_splice($components, 1, 0, array(substr($components[1], 0, 2)));
        $filename = implode('/',  $components);
    }
}



$DB = new DB('master');
$file = new CFile($filename);

//Если ли файл у нас в базе
if ($file->id > 0) {

    $tableName = $file->getTableName();
    $permission = 'adm';
    $allow_download = true; 
    
    switch ($tableName) {
        //файлы БС документы
        case 'file_reserves_order':

            require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
            
            $permission = 'tservices';
            $order_id = intval($file->src_id);
            $orderModel = TServiceOrderModel::model();
            $allow_download = $orderModel->isOrderMember($order_id, $uid);

            break;
        
        //файлы БС в сообщениях 
        case 'file_tservice_msg':
            
            require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceMsgModel.php');
            
            $permission = 'tservices';
            $msg_id = intval($file->src_id);
            $msgModel = TServiceMsgModel::model();
            $allow_download = $msgModel->isMsgMember($msg_id, $uid);
            
            break;
        
        
        //Другие файлы
        default:
        
            $components = explode('/', $filename);
            $components_cnt = count($components);
            
            //Работает с файлами в диретории пользователя
            if ($components[0] === 'users' && $components_cnt > 2) {
    
                $splice_idx = strlen($components[1]) == 2? 2:1;
                $components = array_splice($components, $splice_idx, $components_cnt);
                $login = $components[0];
                if ($login !== $_SESSION['login']) {
                    $allow_download = false;
                }
            }
            
    }
    
    //Если полный админ или соспец правами
    $is_adm = currentUserHasPermissions($permission);

    if ($allow_download || $is_adm) {
        header('X-Accel-Redirect: /bzqvzvyw/' . $filename);
        
        if (is_local()) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($filename));
        }
        
        exit;         
    }
    
}


header("Location: {$host}/404.php");
exit;