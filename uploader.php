<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/uploader/uploader.php");
session_start();

$resource = __paramInit('string', null, 'resource');
$action   = __paramInit('string', null, 'action');

if($resource) {
    
    switch($action) {
        case 'create':
            if(is_array($_FILES['qqfile']) && !$_FILES['qqfile']['error']) {
                $result = uploader::listener($resource);
            } else {
                $result['success'] = false;
                switch ($_FILES['attachedfiles_file']['error']) {
                    case UPLOAD_ERR_FORM_SIZE:
                    case UPLOAD_ERR_INI_SIZE:
                        $result['error'] = "Максимальный объем файлов: " . ConvertBtoMB($max_files_size);
                        break;
                    default:
                        $result['error'] = "Ошибка загрузки файла.";
                        break;
                }
            }
            break; 
        case 'remove':
            $files = __paramInit('array_int', null, 'files');
            uploader::sremoveFiles($resource, $files);
            $result['onComplete'] = uploader::getRemoveCallback(uploader::sgetTypeUpload($resource));
            $result['success'] = true;
            break;
        default:
            $result['success'] = false;
            $result['error']   = 'Ошибка загрузки файла';
            break;
    }
    
} else {
    $result['success'] = false;
    $result['error']   = 'Ошибка загрузки файла';
}

//@todo: где встречаются русские символы мы из преобразуем 
//в unicode последовательности чтобы json_encode не ругался
foreach (array('onComplete', 'error') as $key) {
    if (isset($result[$key])) {
        $result[$key] = htmlentities($result[$key], NULL, 'CP1251');
    }
}

header("Content-Type: text/html");
echo json_encode($result);