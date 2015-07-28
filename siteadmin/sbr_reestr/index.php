<?php

define( 'IS_SITE_ADMIN', 1 );

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/template.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/reserves/ReservesAdmin.php");
session_start();

$no_banner = 1;
$rpath = "../../";

$uid = get_uid();
if(!hasPermissions('sbr') && !hasPermissions('sbr_finance'))
    header_location_exit('/404.php');	
    
$reservesAdmin = new ReservesAdmin();

$action = __paramInit('string', 'action', NULL, 'export');

switch ($action) {
    case 'import' :
         
        $inner_page = "content_import.php";
        
        $file = $reservesAdmin->saveUploadedFile();
        
        if ($file) {
            $reservesAdmin->parseFile($file);
        }
        
        $reestrArray = $reservesAdmin->getReestrs(true);
        $list = Template::render('list_uploaded.php', array(
            'dir' => $reservesAdmin->path,
            'data' => $reestrArray
        ));
        
        break;
    case 'export':
    default:
        $inner_page = "content.php";

        $date_s = __paramInit('string', NULL, 'date_s_eng_format');
        $date_e = __paramInit('string', NULL, 'date_e_eng_format');
        $time_s = __paramInit('string', NULL, 'time_s', '00:00');
        $time_e = __paramInit('string', NULL, 'time_e', '23:59');

        if($date_s || $date_e) {
            $res = $reservesAdmin->exportReservesToCSV($date_s, $time_s, $date_e, $time_e);
        }

        $reestrArray = $reservesAdmin->getReestrs();
        $list = Template::render('list.php', array(
            'dir' => $reservesAdmin->path,
            'data' => $reestrArray
        ));
        break;
}

$css_file = array( 
    'moderation.css', 
    'new-admin.css',
    'nav.css'
);

$content = "../content2.php";
$template = "template3.php";

include ($rpath.$template);