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
                case 'letters':
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/letters.php");
                    $max_files = 1;
                    $max_files_size = letters::MAX_FILE_SIZE;
                    break;
                
                case 'tservice_message':
                    
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/tu/models/TServiceMsgModel.php");
                    
                    $max_files = TServiceMsgModel::MAX_FILES;
                    $max_files_size = TServiceMsgModel::MAX_FILE_SIZE;
                    $cFile->table = 'file_tservice_msg';
                    
                    $order_id = __paramInit('uint', NULL, 'orderid');
                    $hash = __paramInit('striptrim', NULL, 'hash');
                    
                    $_dir = TServiceMsgModel::getUploadPath($order_id, $sess, $_SESSION['uid'], $hash);
                    
                    if ($_dir) {
                        $dir = $login . $_dir;
                    } else {
                        $file['error'] = 'Ошибка загрузки файла';
                        $file['errno'] = 1;                          
                    }

                    break;
                default:
                    $file['error'] = 'Ошибка загрузки файла';
                    $file['errno'] = 1;
                    break;
            }


            if (!isset($file['error'])) {
            
                $cFile->max_size = $max_files_size;
                $cFile->MoveUploadedFile($dir);

                if ($cFile->id) {
                    $files_info = $attachedfiles->calcFiles();
                    $files_count = $files_info['count'];
                    $files_size = $files_info['size'];

                    if(($files_count+1)>$max_files) {
                        $file['error'] = "Максимальное количество файлов: {$max_files}";
                        $file['errno'] = 2;
                    }
                    if(($files_size+$cFile->size)>$max_files_size) {
                        $file['error'] = "Максимальный объем файлов: ".ConvertBtoMB($max_files_size);
                        $file['errno'] = 3;
                    }
                    if( in_array($cFile->getext(), $GLOBALS['disallowed_array']) || ($type=='wd' && (!in_array($cFile->image_size['type'], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)) || $cFile->image_size['width']>2000 || $cFile->image_size['height']>2000) ) ) {
                        $file['error'] = "Недопустимый формат файла";
                        $file['errno'] = 4;
                    }                
                    if($file['error']) {
                        $cFile->Delete($cFile->id);
                    } else {
                        $fileinfo = $attachedfiles->add($cFile);
                        $file['id'] = md5($fileinfo['id']);
                        //@todo: автоматически сгенерированное имя файла
                        // пока не используется в интерфейсе
                        $file['name'] = $fileinfo['name'];
                        //@todo: оригинально имя файла выводим в интерфейс
                        $file['orig_name'] = $fileinfo['orig_name'];
                        //@todo: тут теперь полный путь к файлу
                        $file['path'] = WDCPREFIX . '/' . $fileinfo['path'] . $fileinfo['name'];
                        $file['size'] = ConvertBtoMB($fileinfo['size']);
                        $file['type'] = $fileinfo['type'];
                    }
                } else {
                    if($_FILES['attachedfiles_file']['size']>$max_files_size) {
                        $file['error'] = "Максимальный объем файлов: ".ConvertBtoMB($max_files_size);
                        $file['errno'] = 3;
                    } else {
                        $file['error'] = 'Ошибка загрузки файла';
                        $file['errno'] = 1;
                    }
                }
                
            }
        }
        break;
    case 'delete':
        $attachedfiles->delete($_POST['attachedfiles_delete']);
        break;
}

if ( !isset($bSilentMode) || !$bSilentMode ) {
?>

<script type="text/javascript">
    window.parent.attachedFiles.clearFileField();
    <?php
    switch($action) {
        case 'add':
            ?>
            var message = new Object;
            message.error = '<?=$file['error']?>';
            message.id = '<?=$file['id']?>';
            message.name = '<?=$file['orig_name']?>';
            message.path = '<?=$file['path']?>';
            message.size = '<?=$file['size']?>';
            message.type = '<?=$file['type']?>';
            window.parent.attachedFiles.upload_done(message);
            <?php
            break;
        case 'delete':
            ?>
            window.parent.attachedFiles.del_done();
            <?php
            break;
    }
    ?>
</script>
<?php } ?>