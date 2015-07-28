<?php
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/search_parser.php");
session_start();
get_uid();


if (!(hasPermissions('adm') && hasPermissions('seo'))) {
    header("Location: /404.php");
    exit;
}

$tab = __paramInit('string', 'tab');
$action = __paramInit('string', 'action', 'action');

$parser = search_parser::factory();

if ($action == 'save_settings') {
    $min_cnt = __paramInit('int', null, 'min_cnt', 0);
    $data = array(
        'min_cnt' => intval($min_cnt)
    );
    $parser->setSettings($data);
}

$settings = $parser->getSettings();

switch ($tab) {
    case 'filters':
        include_once('tab.filters.php');
        break;
    case 'rules':
        include_once('tab.rules.php');
        break;
    case 'top':
        include_once('tab.top.php');
        break;
    default:
        include_once('tab.queries.php');
}

$css_file = array( 
    'moderation.css', 
    'new-admin.css',
    'nav.css'
);
$js_file  = array( 'adm.search_parser.js' );

include ($rpath . "template2.php");
