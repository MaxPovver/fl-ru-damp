<?php

//https://beta.free-lance.ru/mantis/view.php?id=28835

//exit;

ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

//------------------------------------------------------------------------------

$file = '/tmp/failmail/55x-emails.log';

if (!file_exists($file)) {
    echo "File {$file} not found!\n";
    exit;
}

$list = file($file);
$list = array_unique($list);

$total = 0;
$cnt = 0;
$sleep_cnt = 0;

if (!empty($list)) {
    $total = count($list);
    foreach ($list as $el) {
        $email = trim($el);
        
        $res = $DB->query("
            UPDATE users SET subscr = B'0'::bit(16) 
            WHERE email = ? AND subscr <> B'0'::bit(16)
            RETURNING uid
          ", $email);

        $res_frl = $DB->query("
            UPDATE freelancer 
            SET mailer = 0, mailer_str = '' 
            WHERE email = ? AND mailer > 0         
        ", $email);
        
        if (pg_num_rows($res) || 
            pg_num_rows($res_frl)) { 
            
            $cnt++;                                                                                                                                                                               
        }        
        
        $sleep_cnt ++;
        
        if ($sleep_cnt >= 2000) {
            print_r("
                Done: {$sleep_cnt}
                Current updated: {$cnt}
            ");
            flush();
            sleep(20);
            $sleep_cnt = 0;
        } 
    }
}


print_r("
    Total: {$total}
    Updated: {$cnt}\n
");

    
//------------------------------------------------------------------------------

exit;