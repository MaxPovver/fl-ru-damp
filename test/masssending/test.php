<?php


ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
}


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/masssending.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php";


//------------------------------------------------------------------------------


//------------------------------------------------------------------------------

if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);

//------------------------------------------------------------------------------


//$string_query = @$_GET['find'];
//$string_query = iconv('UTF-8','CP1251', $string_query);

//------------------------------------------------------------------------------


function Masssending_test($user_id, $masssending_id, $text, $posted_time, $skip_mail=false) 
{
		$master  = new DB('master');
		$plproxy = new DB('plproxy');
		$error = '';
		
		$files = $master->col("SELECT file.id FROM mass_sending_files m INNER JOIN file ON m.fid = file.id WHERE mass_sending_id = ? ORDER BY m.pos", $masssending_id);

        $ignors = $plproxy->col("SELECT user_id FROM ignor_me(?)", $user_id);
        array_push($ignors, $user_id);
        
        //print_r($ignors);exit;
        
        $sql = $master->parse("
            SELECT 
                m.uid 
            FROM 
                mass_sending_users m 
            INNER JOIN 
                users u ON m.uid = u.uid AND u.is_banned = B'0' 
            WHERE 
                mid = ?i AND m.uid NOT IN (?l)
        ", $masssending_id, $ignors);
        /*$msgid = $plproxy->val("SELECT masssend(?, ?, ?a, ?)", $user_id, $text, $files, ($skip_mail? '': 'SpamFromMasssending'));
        if ( $msgid ) {
            $plproxy->query("SELECT masssend_sql(?, ?, ?)", $msgid, $user_id, $sql);
        }*/
        
        $msgid = $plproxy->val( 'SELECT masssend(?, ?, ?a, ?, ?, ?, ?)', $user_id, $text, $files, $masssending_id, $posted_time, ($skip_mail? '': 'SpamFromMasssending'), $sql );
		
        //print_r($msgid);exit;
        
        // TODO: отдельным тикетом
		//$master->query("DELETE FROM mass_sending_users WHERE mid = ?", $masssending_id);
        return empty( $plproxy->error );
}


$masssending = masssending::Get(7485);
$masssending = $masssending[0];
//$success = (bool)messages::Masssending($masssending['user_id'], $masssending['id'], $masssending['msgtext'], $masssending['posted_time']);

$success = (bool)Masssending_test($masssending['user_id'], $masssending['id'], $masssending['msgtext'], $masssending['posted_time']);


print_r($success);
exit;