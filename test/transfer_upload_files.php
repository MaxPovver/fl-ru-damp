<?php
error_reporting(E_ALL & ~E_NOTICE);

/**
 * Скрипт для перегонки старых файлов по подпапкам, работает из консоли
 * Свои результаты работы записывает в лог к webdav с пометкой transfer
 * 
 * @example
 * php transfer_upload_files.php 
 */

$st = microtime(true);
require_once "../classes/stdf.php";
require_once "../classes/log.php";

// Данные настройки перегонки файлов, все надо поменять под бету
define("UPLOAD_PROJECTS", "/var/www/webdav/projects/upload/"); // Заменить на путь на бете
define("DIR_SEPARATOR", "/");
define("MAX_READ_FILES", 5000); // Количество файлов для обработки
define("CHMOD_DIR", 0755); // Права доступа к папке 

$log = new log('webdav/transfer-'.SERVER.'-%d.log', 'a', '%d.%m.%Y %H:%M:%S : ');
if ($handle = opendir(UPLOAD_PROJECTS)) {
    $i = $error_transfer = 0;
    while (false !== ($file = readdir($handle))) {
        if (strlen($file) > 6) {
            $i++;
            
            if(substr($file, 0, 2) == "na") {
                continue; // такие файлы будут перемещатся вместе с родителями их нет в БД
            }
                    
            $sql = "SELECT * FROM file_projects WHERE fname = ?";
            $db_file = $DB->row($sql, $file);
            if( (int) $db_file['id'] ) {
                $month = date("Ym", strtotime($db_file['modified']));
                $month_dir = UPLOAD_PROJECTS . $month . DIR_SEPARATOR;
                if(!is_dir($month_dir)) {
                    mkdir($month_dir, CHMOD_DIR);
                }
                
                $ext = explode(".", $file);
                $ext = strtolower($ext[count($ext)-1]);
                if($ext == "gif") {
                    if(file_exists(UPLOAD_PROJECTS . "na_".$file)) {
                        $file_added_copy = "na_".$file;
                    } 
                }
                if(copy(UPLOAD_PROJECTS . $file, $month_dir . $file)) {
                    if($file_added_copy) {
                        copy(UPLOAD_PROJECTS . $file_added_copy, $month_dir . $file_added_copy);
                        unlink(UPLOAD_PROJECTS . $file_added_copy);
                        unset($file_added_copy);
                    }
                    
                    unlink(UPLOAD_PROJECTS . $file);
                    $sql = "UPDATE file_projects SET path = path || ? WHERE id = ?i";
                    $DB->query($sql, $month . DIR_SEPARATOR, $db_file['id']);
                } else {
                    $error_transfer++;
                    $log->writeln("Error transfer file - {$file}");
                }
            } else {
                $error_transfer++;
                $log->writeln("File not found in DB ({$file})");
            }
        }
        if(MAX_READ_FILES <= $i) {
            break;
        }
    }
    closedir($handle);
    
    $sf = microtime(true);
    $f = round($sf-$st, 6);
    $log->writeln("\n-------");
    $log->writeln("Transfer files\t\t\t".($i - $error_transfer));
    $log->writeln("Error transfer\t\t\t{$error_transfer}");
    $log->writeln("Runtime\t\t\t\t\t{$f} sec.");
} else {
    $log->writeln("Error: dir not found");
}

?>