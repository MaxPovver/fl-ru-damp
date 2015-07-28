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




//$DB

/*
print_r($DB);
exit;


$DB->query('');*/
/*
$cfile = new CFile();
$cfile->Delete($id);
 */

$ids = array(
    
  
6569
    /*,
10374,
6569,
6531,
6653,
8675,
9398,
8498,
7646,
7282
 */
    
    
);
 

$cfile = new CFile();

$list = $DB->rows('
    SELECT id 
    FROM tservices_orders 
    WHERE id IN(?l)',
    $ids);

if(count($list))
{
    foreach($list as $el)
    {
        $reserveData = $DB->row('
            SELECT 
                r.*, 
                ra.id as arbitrage_id,
            FROM reserves AS r 
            LEFT JOIN reserves_arbitrage AS ra ON ra.reserve_id = r.id
            WHERE 
            ra.id IS NULL AND 
            r.src_id = ?i AND 
            r.type = 10
         ', $el['id']);
        
        
        if(!$reserveData) continue;
        
        
        $files = $DB->rows('
            SELECT id 
            FROM file_reserves_order 
            WHERE 
                src_id = ?i AND 
                doc_type IN(20,30)
            ', $el['id']);
        
        /*
        foreach($files as $file) 
            $cfile->Delete($file['id']);
        */
        
        
        $DB->update('reserves',array(
            'status' => 10,
            'status_pay' => 10,
            'date_complete' => NULL
        ),'id = ?i', $reserveData['id']);
        
        
        $DB->query('
            DELETE FROM reserves_payout 
            WHERE reserve_id = ?i
        ', $reserveData['id']);
        
        //$DB->delete('reserves_payout');
        
        /*
        $DB->update('reserves',array(
            'status' => 10,
            'status_pay' => 10,
            'date_complete' => NULL
        ),'src_id = ?i', $el['id']);
        */
        
        
        print_r($files);
        exit;
        
    }
}


//print_r($list);
//exit;