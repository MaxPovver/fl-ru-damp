<?php

/**
 * https://beta.free-lance.ru/mantis/view.php?id=29021
 * 
 * Перемещаем сканы паспортов в другую директорию
 * пачами по 100 за запуск, можно увеличиваь если скрипт справляется
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
$log = new log('dav_move/'.SERVER.'-to_account-%d%m%Y.log');

$log->writeln('------------ BEGIN (start time: ' . date('d.m.Y H:i:s') . ') -----');

$list = $DB->rows("
    SELECT 
	fl.*,
	u.login
    FROM account_attach AS aa
    INNER JOIN account AS a ON a.id = aa.account_id
    INNER JOIN users AS u ON u.uid = a.uid    
    INNER JOIN file AS fl ON fl.id = aa.file_id
    WHERE EXISTS (
        SELECT 1 
        FROM file AS f 
        WHERE 
            f.id = aa.file_id 
            AND (
        	f.path NOT LIKE '%/attach/' 
        	AND f.path NOT LIKE '%/attach/finance_other/'
        	AND f.path NOT LIKE '%/private/account/'
        	AND f.path NOT LIKE '%/private/account/finance_other/'
        	)
    ) 
    
    --AND u.login IN('testuser4','vgavran')
    --AND u.last_time < '2015-03-01'
    
    LIMIT 200;
");

$cnt = count($list);
//print_r("Found {$cnt} files \n");
$log->writeln("Found {$cnt} files");

$users_links_ok = array();
$users_links_fail = array();

if ($list) {
    
    $cnt_succes = 0;
    //$cnt_path_fail = 0;
    $cnt_rename_fail = 0;
    
    foreach ($list as $file) {
        $cfile = new CFile();
        $cfile->id = $file['id'];
        $cfile->name = $file['fname'];
        $cfile->path = $file['path'];
        
        /*
        if (empty($cfile->name) || empty($cfile->path)) {
            $cnt_path_fail++;
            continue;
        }
        */
        
        //$to = preg_replace('/\/attach\//', '/private/account/', $cfile->path);        
        //$to .= $cfile->name;

	$to = "{$cfile->path}private/account/{$cfile->name}";
			
        //print_r("USER https://www.fl.ru/users/{$file['login']}/setup/finance/\n");
        //print_r("FROM https://st.fl.ru/{$cfile->path}{$cfile->name} TO https://st.fl.ru/{$to}\n\n");
        //exit;                                   
        
        if (!$cfile->Rename($to)) {
    	    
            $cnt_rename_fail++;

/*            
            $users_links_fail["https://www.fl.ru/users/{$file['login']}/setup/finance/"][] = array(
        	'from' => "https://st.fl.ru/{$file['path']}{$file['fname']}",
        	'to' => "https://st.fl.ru/{$to}"
            ); 
*/            
            continue;
        }
        
        
        unset($cfile);
        $cnt_succes++;

/*        
        $users_links_ok["https://www.fl.ru/users/{$file['login']}/setup/finance/"][] = array(                                                                                                                                                                                            
                'from' => "https://st.fl.ru/{$file['path']}{$file['fname']}",                                                                                                                                                                                                                     
                'to' => "https://st.fl.ru/{$to}"                                                                                                                                                                                                                                               
        );        
*/        
        //unset($cfile);
        
        //exit;
    }

 $time_end = microtime(true); 
 $time_total = number_format($time_end - $time_start,5);    

$log->writeln("                                                                                                                                                                                       
FAIL RENAME: {$cnt_rename_fail}                                                                                                                                                                     
SUCCESS: {$cnt_succes}                                                                                                                                                                              
TIME: {$time_total}                                                                                                                                                                                 
\n");



/*
    print_r("
FAIL RENAME: {$cnt_rename_fail}
SUCCESS: {$cnt_succes}
TIME: {$time_total}
\n");
*/

/*
if ($users_links_fail) {
    
    print_r("\nFAIL LIST:");
    
    foreach($users_links_fail as $key => $links){    
	
	print_r("\n\nUSER: {$key}\n");
	foreach($links as $link) {
	    print_r("FROM {$link['from']} TO {$link['to']}\n");
	}
	
    }
}


if ($users_links_ok) { 
    print_r("\nSUCCESS LIST:"); 
    foreach($users_links_ok as $key => $links){
	print_r("\n\nUSER: {$key}\n"); 
	foreach($links as $link) {
	    print_r("FROM {$link['from']} TO {$link['to']}\n");
	} 
    }
}
*/





}

$log->writeln('------------ END (total time: ' . $log->getTotalTime() . ') ---------------'); 