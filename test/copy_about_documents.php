<?php
/**
 * Скрипт копирует все файлы из папки /about в папку на dav сервере about/documents
**/
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/CFile.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/dav_file_upload.php';
if ( !( hasPermissions('admin') ) ) {
    header ("Location: /404.php"); 
    exit;
}
$srcdir = $_SERVER['DOCUMENT_ROOT'] . '/about';
$ls = scandir( $srcdir );
foreach ( $ls as $item ) {
    if ( $item != '.'  && $item !== '..' && !is_dir( "$srcdir/$item" ) ) {
        $filedata = array (
            "name"     => $item,
            "tmp_name" => "$srcdir/$item",
            "size"     =>  filesize("$srcdir/$item")
        );
        $srcfile  = new CFile($filedata);
        $srcfile->unlinkOff = true;
        $path = "about/documents";
        $destfile = new CFile("$path/$item", dav_file_upload::FILE_TABLE);
        $rename_name = '';
        if ($destfile->id) {
            $ext = $destfile->getext($destfile->name);
            $tmp = $destfile->secure_tmpname($path . '/', '.' . $ext );
            $rename_name = substr_replace($tmp, "", 0, strlen($path) + 1);
            $destfile->Rename("{$path}/{$rename_name}");
        }
        $srcfile->server_root = 1;
        $srcfile->max_size = dav_file_upload::MAX_FILE_SIZE;
        $r = $srcfile->MoveUploadedFile($path . '/', true, $item);
        dav_file_upload::addRecord($srcfile->id, $srcfile->name, $rename_name);
        echo "Copy $srcdir/$item " . WDCPREFIX . '/' . $path . '/' . $item . '<br><br>';
    }
}