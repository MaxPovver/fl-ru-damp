<?php
/**
 * Загрузка баннеров для ежедневной рассылки о новых проектах
 */

define('IS_SITE_ADMIN', 1);
//$no_banner = 1;
$rpath = "../../";

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/settings.php");

get_uid();

$css_file  = array( 'moderation.css', 'new-admin.css', 'nav.css' );


if (!(hasPermissions('newsletter') && hasPermissions('adm'))) 
{
    header_location_exit('/404.php');
}


define('BANNER_PATH','banners/newsletter/');
define('MAX_SIZE',5 * 1024 * 1024);//5Mb


$action = __paramInit('string', 'action', 'action');
$type = __paramInit('bool', 'type', 'type');

$type_prefix = ($type == 1)?'emp_':'';

$settings = new settings();


switch($action)
{
    case 'save':
        
        $uploaded_file = new CFile($_FILES['file']);
        $uploaded_file->server_root = 1;
        $uploaded_file->max_size = MAX_SIZE;
        $uploaded_file->allowed_ext = array('jpg', 'jpeg', 'gif', 'png');
        $filename = $uploaded_file->MoveUploadedFile(BANNER_PATH); 
        
        if(!count($uploaded_file->error) && $filename)
        {
            $settings->AddVariable('newsletter', $type_prefix . 'banner_file', WDCPREFIX . '/' . $uploaded_file->path . $uploaded_file->name);
            $settings->AddVariable('newsletter', $type_prefix . 'banner_link', __paramInit('string', null, 'link'));
        }
        
        header_location_exit('./#'.(($type == 1)?'emp':'frl'));
        
        break;
    
        
        
    case 'delete':
        
        $settings->SetVariable('newsletter', $type_prefix . 'banner_file', NULL);
        $settings->SetVariable('newsletter', $type_prefix . 'banner_link', NULL);
        
        break;
}


$newsletter_banner_file = $settings->GetVariable('newsletter', 'banner_file');
$newsletter_banner_link = $settings->GetVariable('newsletter', 'banner_link');

$newsletter_emp_banner_file = $settings->GetVariable('newsletter', 'emp_banner_file');
$newsletter_emp_banner_link = $settings->GetVariable('newsletter', 'emp_banner_link');


$content = "../content.php";
$inner_page = "inner_index.php";

include ($rpath . "template2.php");