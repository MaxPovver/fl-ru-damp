<?php

define('IS_EXTERNAL', 1);
require_once '../classes/stdf.php';

$check = 0;
$skip  = 0;
$clear = 0;
$error = 0;
$virus = 0;

if ( !empty($arvg[1]) && $argv[1] == 'full' ) {
    $res = $DB->query("SELECT * FROM file_template WHERE virus IS NULL OR virus::integer > 1 ORDER BY id DESC");
} else {
    $res = $DB->query("SELECT * FROM file_template WHERE virus IS NULL ORDER BY id DESC");
}

while ( $row = pg_fetch_assoc($res) ) {
    
    $load = sys_getloadavg();
    
    while ( $load[0] > 3 ) {
        echo "Waiting 10 sec. (AVG: {$load[0]})\n";
        sleep(10);
        $load = sys_getloadavg();
    }

    $time = microtime(1);
    $file = new CFile($row['id']);

    
    echo $file->path .$file->name . "\n";
    
    if ( !is_file(DRWEB_STORE . '/' . $file->path . $file->name) ) {
        $skip++;
        echo "Result: No File!\n\n";
        continue;
    }
        

    $code = $file->antivirus();

    if ($file->virus == 0) {
        $status = 'Clear.';
        $clear++;
    } else if ($file->virus == 1) {
        $status = 'Infected. ' . $file->virusName . '.';
        $virus++;
    } else if ($file->virus == 2) {
        $status = 'Skipped by archive restrictions.';
        $error++;
    } else if ($file->virus == 16) {
        $error++;
        $status = 'No need check. Skipped.';
    } else {
        $error++;
        $status = 'Error.';
    }
    
    $check++;
    $time = microtime(1) - $time;

    echo "Result: {$status} (AVG: {$load[0]}, Time: {$time}, Checked: {$check})\n\n";
    
}

echo "-------------------------------------------------------------------------------------- \n";
echo "Checked: {$check}; Skipped: {$skip}; Clear: {$clear}; Infected: {$virus}; Errors: {$error} \n";