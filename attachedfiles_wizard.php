<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/wizard.php");

session_start();

$uid = $_SESSION['WUID'];
if(!$uid || !$_POST['attachedfiles_session']) return;

$action = $_POST['attachedfiles_action'];
$type = $_POST['attachedfiles_type'];
$sess = $_POST['attachedfiles_session'];

switch($action) {
    case 'add':
        if(is_array($_FILES['attachedfiles_file']) && !$_FILES['attachedfiles_file']['error']) {
            
            $dir   = wizard::FILE_DIR;
            $cFile = new CFile($_FILES['attachedfiles_file']);
            $cFile->table = 'file';
            switch($type) {
                case 'wizard':
                    $max_files      = wizard::MAX_FILE_COUNT;
                    $max_files_size = wizard::MAX_FILE_SIZE;
                    break;
                default:
                    $file['error'] = 'Ошибка загрузки файла';
                    break;
            }

            $cFile->max_size = $max_files_size;
            $cFile->server_root = 1;
            $cFile->MoveUploadedFile($dir);
            if($cFile->id) {
                $attachedfiles = new attachedfiles($sess);

                $files_info = $attachedfiles->calcFiles();
                $files_count = $files_info['count'];
                $files_size = $files_info['size'];
                
                if(($files_count+1)>$max_files) {
                    $file['error'] = "Максимальное количество файлов: {$max_files}";
                }
                if(($files_size+$cFile->size)>$max_files_size) {
                    $file['error'] = "Максимальный объем файлов: ".ConvertBtoMB($max_files_size);
                }
                if( in_array($cFile->getext(), $GLOBALS['disallowed_array'])) {
                    $file['error'] = "Недопустимый формат файла";
                }
                if($file['error']) {
                    $cFile->Delete($cFile->id);
                } else {
                    $fileinfo = $attachedfiles->add($cFile);
                    $file['id'] = md5($fileinfo['id']);
                    $file['name'] = $fileinfo['name'];
                    $file['path'] = WDCPREFIX."/".$fileinfo['path'];
                    $file['size'] = ConvertBtoMB($fileinfo['size']);
                    $file['type'] = $fileinfo['type'];
                }
            } else {
                if($_FILES['attachedfiles_file']['size']>$max_files_size) {
                    $file['error'] = "Максимальный объем файлов: ".ConvertBtoMB($max_files_size);
                } else {
                    $file['error'] = 'Ошибка загрузки файла';
                }
            }
        }
        break;
    case 'delete':
        $attachedfiles = new attachedfiles($sess);
        $attachedfiles->delete($_POST['attachedfiles_delete']);
        break;
}
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
            message.name = '<?=$file['name']?>';
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