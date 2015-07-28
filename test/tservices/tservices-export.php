<?php
ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' ); //???
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

/*
$user_obj = new users();

var_dump($user_obj->CountAll());
*/

$db = $GLOBALS['DB'];


//print_r($db);

$res = $db->rows('
    SELECT id,description
    FROM __tservices
');


/*
$sql = <<<SQL
        
        UPDATE __tservices SET 
        
SQL;        
*/

$pattern = "UPDATE __tservices SET description = '%s' WHERE id = %d;" . PHP_EOL;
   
if(count($res))
{
    $filename = ABS_PATH  . '/temp/__tservices_update.sql';
    file_put_contents($filename, '');
    chmod($filename, 0666);
    
    foreach($res as $el)
    {
        file_put_contents($filename, sprintf($pattern,$el['description'],$el['id']), FILE_APPEND);
    }
}


//print_r(count($res));

exit;