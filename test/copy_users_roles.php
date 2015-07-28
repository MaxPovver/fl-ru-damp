<?php
/**
 *Скрипт заполняет значения полей
 * users_visits.is_emp, users_visits_daily.is_emp
 * значениями из users.role
**/
set_time_limit(0);

require_once("../classes/config.php");

require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php";

$offset = 0;
$n = 30000;
$select = "SELECT DISTINCT user_id FROM users_visits WHERE is_emp IS NULL OFFSET {$offset} LIMIT {$n}";
$master = new DB("master");
$stat = new DB("stat");

$_update = "UPDATE users_visits SET is_emp = CASE user_id
{WHEN_MACROS}
ELSE is_emp END";

$_update2 = "UPDATE users_visits_daily SET is_emp = CASE user_id
{WHEN_MACROS}
ELSE is_emp END";
/*
WHEN  THEN TRUE
WHEN 238021 THEN FALSE
* */
$count = 0;
while ( true ) {
	$col = $stat->col($select);
	if ( !is_array($col) || !$col) {
		echo "col is not array<br>";
		break; 
	}
    $uids = join(',', $col);
    $rows = $master->rows("SELECT uid, role FROM users WHERE uid IN ($uids) ");
    if ( !is_array($rows) || !$rows ) {
    	$offset += $n;
    	$select = "SELECT DISTINCT user_id FROM users_visits WHERE is_emp IS NULL OFFSET {$offset} LIMIT {$n}";
    	continue;
    }
    $when = array();
    foreach ($rows as $row) {
    	$bool = ($row['role'][0] == 1? 'TRUE' : 'FALSE');
	    $when[] = "WHEN {$row['uid']} THEN {$bool}";
	    $count++;
    }
    if ( count($when) == 0 ) {
    	echo "users with uids ({$uids}) not fount in table users<br>";
    	break;
    }
    $update = str_replace("{WHEN_MACROS}", join("\n", $when), $_update);
    //echo '<b style="color:#0000ff">'.$update.'</b><hr>';
    $stat->query($update);
    $update2 = str_replace("{WHEN_MACROS}", join("\n", $when), $_update2);
    $stat->query($update2);
    Sleep(1);
    
}
echo "Total: {$count}\n";
?>