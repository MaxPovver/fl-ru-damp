<?php

/**
 * https://beta.free-lance.ru/mantis/view.php?id=0029052
 * 
 * Перемещаем файлы переписки в заказе в другую директорию
 */

$time_start = microtime(true); 

ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/multi_log.php");
$log = new log('dav_move/'.SERVER.'-to_orders-%d%m%Y.log');

$log->writeln('------------ BEGIN (start time: ' . date('d.m.Y H:i:s') . ') -----');


$list = $DB->rows("
    SELECT 
    
    tm.order_id,
    fl.*

    FROM file_tservice_msg AS fl
    INNER JOIN tservices_msg AS tm ON tm.id = fl.src_id
    WHERE 
        fl.path LIKE '%/attach/'
        
    LIMIT 50;
");

$cnt = count($list);
$log->writeln("Found {$cnt} files");

if ($list) {
    
    $cnt_succes = 0;
    $cnt_rename_fail = 0;
    
    foreach ($list as $file) {
        $cfile = new CFile();
        $cfile->id = $file['id'];
        $cfile->name = $file['fname'];
        $cfile->path = $file['path'];
        $cfile->modified = $file['modified'];
        
        $to = preg_replace('/\/attach\//', "/private/orders/{$file['order_id']}/", $cfile->path);        
        $to .= $cfile->name;
			
        //print_r("USER https://www.fl.ru/tu/order/{$file['order_id']}/\n");
        //print_r("FROM https://st.fl.ru/{$cfile->path}{$cfile->name} TO https://st.fl.ru/{$to}\n\n");

        //print_r("USER http://beta.free-lance.lo/tu/order/{$file['order_id']}/\n");
        //print_r("FROM http://dav.free-lance.lo/{$cfile->path}{$cfile->name} TO http://dav.free-lance.lo/{$to}\n\n");        
        //exit;                                   
        
        //print_r("USER https://beta.fl.ru/tu/order/{$file['order_id']}/\n");
        //print_r("FROM https://dav.beta.fl.ru/{$cfile->path}{$cfile->name} TO http://dav.beta.fl.ru/{$to}\n\n");        
        //exit;          
        
        if (!$cfile->Rename($to)) {
            $cnt_rename_fail++;
            continue;
        }
        
        unset($cfile);
        $cnt_succes++;
        
        //exit;
    }

 $time_end = microtime(true); 
 $time_total = number_format($time_end - $time_start,5);    

$log->writeln("                                                                                                                                                                                       
FAIL RENAME: {$cnt_rename_fail}                                                                                                                                                                     
SUCCESS: {$cnt_succes}                                                                                                                                                                              
TIME: {$time_total}");

}

$log->writeln('------------ END (total time: ' . $log->getTotalTime() . ') ---------------'); 