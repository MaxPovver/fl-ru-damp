<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");

session_start();
$uid = get_uid(false);

if(!$uid) return;
if(is_array($_POST['attachedfiles_session'])) {
    reset($_POST['attachedfiles_session']);
    $_POST['attachedfiles_session'] = current($_POST['attachedfiles_session']);
}
if(!$_POST['attachedfiles_session']) {
    $generate_session = attachedfiles::createSessionID();
    $_POST['attachedfiles_session'] = $generate_session;
}

$action = $_POST['attachedfiles_action'];
$type = $_POST['attachedfiles_type'];
$sess = $_POST['attachedfiles_session'];
$formid = change_q($_POST['attachedfiles_formid'], true);
$del_id = change_q(__paramInit('string', null, 'attachedfiles_delete'));

$attachedfiles = new attachedfiles($sess);

switch($action) {
    case 'add':
        if(is_array($_FILES['attachedfiles_file']) && !$_FILES['attachedfiles_file']['error']) {
            $login = $_SESSION['login'];
            $dir = $login."/attach";
            $cFile = new CFile($_FILES['attachedfiles_file']);
            $cFile->table = 'file';
            switch($type) {
                case 'contacts':
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
                    $max_files = messages::MAX_FILES;
                    $max_files_size = messages::MAX_FILE_SIZE;
                    break;
                case 'blog':
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
                    $max_files = blogs::MAX_FILES;
                    $max_files_size = blogs::MAX_FILE_SIZE;
                    break;
                case 'project':
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
                    $max_files = tmp_project::MAX_FILE_COUNT;
                    $max_files_size = tmp_project::MAX_FILE_SIZE;
                    break;
                case 'mailer':
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/mailer.php");
                    $max_files = mailer::MAX_FILE_COUNT;
                    $max_files_size = mailer::MAX_FILE_SIZE;
                    break;
                case 'commune':
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
                    $max_files = commune::MAX_FILES;
                    $max_files_size = commune::MAX_FILE_SIZE;
                    break;
                case 'sbr_arb':
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
                    $max_files = 1;
                    $max_files_size = sbr_stages::ARB_FILE_MAX_SIZE;
                    break;
                case 'sbr':
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
                    $max_files = sbr::MAX_FILES;
                    $max_files_size = sbr::MAX_FILE_SIZE;
                    break;
                
                //Загрузка сканов во вкладке финансовой информации
                case 'finance_doc':
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
                    $max_files = account::MAX_FILE_COUNT;
                    $max_files_size = account::MAX_FILE_SIZE;
                    $imageTypes = array(2, 3);//jpg,png
                    $maxImageHeight = 3000;
                    $maxImageWidth = 3000;
                    $default_error = 'Файл не соответствует требованиям.';
                    
                    //Админ может загрузить сканы в директорию пользователя
                    if (hasPermissions('users')) {
                        $user_uid = __paramInit('striptrim', NULL, 'attachedfiles_uid');
                        $hash = __paramInit('striptrim', NULL, 'attachedfiles_hash');
                        if ($hash === paramsHash(array($user_uid)) && ($uid != $user_uid)) {
                            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
                            $user = new users();
                            $user->GetUserByUID($user_uid);
                            if ($user->uid > 0) {
                                $login = $user->login;
                            }
                        }
                    }
                    
                    $dir = sprintf(account::DOC_UPLOAD_PATH, $login);
                    break;
                
                //@todo: данный вид загрузки сейчас вроде не используется, но был в старой СБР
                case 'finance_other':
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
                    $max_files = account::MAX_FILE_COUNT;
                    $max_files_size = account::MAX_FILE_SIZE;
                    $dir = sprintf(account::OTHER_UPLOAD_PATH, $login);
                    break;
                
                case 'carusellogo':
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php");
                    $max_files_size = 1024 * 1024; // 1 мб
                    $max_files = 1;
                    $imageTypes = array(2, 3);
                    $cFile->max_image_size = array('width' => 50, 'height' => 50, 'less' => 0);
                    $cFile->resize = 1;
                    $cFile->proportional = 1;
                    $cFile->crop = 1;
                    $dir = $login . '/foto';
                    break;
                case 'userpic':
                    // так как для юзерпика нужен только один файл, то удаляем все файлы текущей сессии кроме последнего, на случай если файл по какой-то причине не подойдет или не загрузится
                    $aFiles = new attachedfiles($sess);
                    $userpics = $aFiles->getFiles(array(1));
                    if (is_array($userpics)) {
                        while(count($userpics) > 1) {
                            $userpic = array_splice($userpics, 0, 1);
                            $aFiles->delete($userpic['id']);
                        }
                    }                    
                    $max_files_size = 1024 * 1024; // 1 мб
                    $max_files = 10;
                    $imageTypes = array(2, 3);
                    $maxImageHeight = 100;
                    $maxImageWidth = 100;
                    $dir = $login . '/foto';
                    break;
                case 'project_logo':
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
                    $cFile->server_root = true;
                    $dir = 'users/' . substr($login, 0, 2) . '/' . $login . "/tmpproj/";
                    $cFile->table = 'file_projects';
                    $cFile->disable_animate = true;
                    // так как для логотипа нужен только один файл, то удаляем все файлы текущей сессии кроме последнего, на случай если файл по какой-то причине не подойдет или не загрузится
                    $aFiles = new attachedfiles($sess);
                    $logos = $aFiles->getFiles(array(1));
                    if (is_array($logos)) {
                        while(count($logos) > 1) {
                            $logos = array_splice($logos, 0, 1);
                            $aFiles->delete($logos['id']);
                        }
                    }  
                    $max_files_size = new_projects::LOGO_SIZE;
                    $max_files = 10;
                    $imageTypes = array(1, 2, 3);
                    $cFile->max_image_size = array('width' => 150, 'height' => 150, 'less' => 0);
                    $cFile->resize = 1;
                    $cFile->proportional = 1;
                    $cFile->crop = 1;

                    break;
                default:
                    $file['error'] = 'Ошибка загрузки файла';
                    break;
            }

            $cFile->max_size = $max_files_size;
            $cFile->_getImageSize($cFile->tmp_name);
            
            

            $files_info = $attachedfiles->calcFiles();
            $files_count = $files_info['count'];
            $files_size = $files_info['size'];

            if(($files_count+1)>$max_files) {
                $file['error'] = "Максимальное количество файлов: {$max_files}";
            }
            if(($files_size+$cFile->size)>$max_files_size) {
                $file['error'] = "Максимальный объем файлов: ".ConvertBtoMB($max_files_size);
            }
            if( in_array($cFile->getext(), $GLOBALS['disallowed_array']) || 
                ($type=='wd' && (!in_array($cFile->image_size['type'], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)) || $cFile->image_size['width']>2000 || $cFile->image_size['height']>2000) ) || 
                ($type=='help_video' && $cFile->getext()!='flv')
              ) {
                $file['error'] = "Недопустимый формат файла";
            }
            // если заданы типы графических файлов
            if ($imageTypes) {
                // то файл должен быть графическим
                if (!$cFile->image_size['type'] || !in_array($cFile->image_size['type'], $imageTypes)) {
                    $file['error'] = "Недопустимый формат файла";
                }                    
            }
            // если задана максимальная высота
            if ($maxImageHeight && $cFile->image_size['height'] > $maxImageHeight) {
                $file['error'] = "Превышена максимальная высота изображения";
            }
            // если задана максимальная ширина
            if ($maxImageWidth && $cFile->image_size['width'] > $maxImageWidth) {
                $file['error'] = "Превышена максимальная ширина изображения";
            }
            // если задана определенная ширина изображения
            if ($sharpImageWidth && $cFile->image_size['width'] != $sharpImageWidth) {
                $file['error'] = "Ширина изображения не соответствует требуемой";
            }
            if ( $cFile->size == 0) {
                $file['error'] = "Пустой файл";
            }
            if(!$file['error']) {
                $cFile->MoveUploadedFile($dir);
                if($cFile->id) {
                    if($file['error']) {
                        $cFile->Delete($cFile->id);
                    } else {
                        $fileinfo = $attachedfiles->add($cFile);
                        $file['orig_name'] = __paramValue('string', $fileinfo['orig_name']);
                        $file['id'] = md5($fileinfo['id']);
                        $file['name']  = $fileinfo['name'];
                        $file['path']  = $fileinfo['path'];
                        $file['size']  = $fileinfo['size'];
                        $file['tsize'] = ConvertBtoMB($fileinfo['size']);
                        $file['type']  = $fileinfo['type'];
                        $file['session'] = $sess;
                    }
                } else {
                    if($_FILES['attachedfiles_file']['size']>$max_files_size) {
                        $file['error'] = "Максимальный объем файлов: ".ConvertBtoMB($max_files_size);
                    } else {
                        $file['error'] = $cFile->error;
                    }
                }
            }
            
        } else {
            switch($_FILES['attachedfiles_file']['error']) {
                case UPLOAD_ERR_FORM_SIZE:
                case UPLOAD_ERR_INI_SIZE:
                    $file['error'] = "Максимальный объем файлов: ".ConvertBtoMB($max_files_size);
                    break;
                default:
                    $file['error'] = (isset($default_error))?$default_error:"Ошибка загрузки файла.";
                    break;
            }
        }
        break;
    case 'delete':
        $attachedfiles->delete($_POST['attachedfiles_delete']);
        break;
    case 'real_delete': // Полностью удаляет файл
        $attachedfiles->delete($_POST['attachedfiles_delete']);
        $delete_files = $attachedfiles->getFiles(array(2,4));
        $cfile = new CFile();
        foreach($delete_files as $delete) {
            $cfile->Delete($delete['id']);
        }
        break;
    case 'delete_file_stage': // Метод для удаления файлов из этапа сделки СБР что бы не мудрить с системой файлов - тут нельзя удалять файлы, не запишется история изменений ТЗ
        //require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr_stages.php");
        $attachedfiles->delete($_POST['attachedfiles_delete']);
        //$delete_files = $attachedfiles->getFiles(array(2,4));
        //sbr_stages::_new_delAttach($delete_files);
        break;
}

?>

<script type="text/javascript">
//    window.parent.attachedFiles2.clearFileField(<?= $formid ?>);
    <?php
    switch($action) {
        case 'add':
            ?>
            var message = new Object;
            message.error = '<?=$file['error']?>';
            message.id = '<?=$file['id']?>';
            message.name = '<?=$file['name']?>';
            message.orig_name = '<?=$file['orig_name']?>';
            message.path = '<?=$file['path']?>';
            message.size = '<?=$file['size']?>';
            message.tsize = '<?=$file['tsize']?>';
            message.type = '<?=$file['type']?>';
            message.session = '<?=$file['session']?>';
            <?php if($generate_session) { ?>
            message.attach_session = '<?=$generate_session?>';
            <?php } //if?>
            window.parent.attachedFiles2.upload_done('<?= $formid ?>', message);
            <?php
            break;
        case 'real_delete':
        case 'delete':
        case 'delete_file_stage':
            ?>
            window.parent.attachedFiles2.del_done('<?= $formid ?>', '<?= $del_id ?>');
            <?php
            break;
    }
    ?>
</script>