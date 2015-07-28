<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php' );

$sPrjId    = __paramInit( 'string', 'pid' );
$aReasons  = admin_log::getAdminReasons( 9 );
$js_file[] = 'banned.js';

if ( $sPrjId ) {
    if ( !preg_match('/^[\d]+$/', $sPrjId) ) {
    	header_location_exit( '/404.php' );
    }
    
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
    
	$log             = $admin_log->getLogProjById( $nCount, $sPrjId );
	$obj_project     = new projects();
	$project         = $obj_project->GetPrjCust( $sPrjId );
	$project_attach  = $obj_project->GetAllAttach( $sPrjId );
	$project_history = $obj_project->GetPrjHistory( $sPrjId );
}
else {
    $_SESSION['admin_log_proj'] = $_SERVER['REQUEST_URI'];
    
    $actions      = $admin_log->getAdminActions( admin_log::OBJ_CODE_PROJ );
    $admins       = $admin_log->getAdminsInLog( admin_log::OBJ_CODE_PROJ );
    $all_specs    = professions::GetAllProfessions( '', 0, 1 );
    $categories   = professions::GetAllGroupsLite( true );
    $filter_specs = '';
    $spec_now     = 0;
    
    for ( $i=0; $i < sizeof($all_specs); $i++ ) {
        if ( $all_specs[$i]['groupid'] != $spec_now ) {
            $spec_now      = $all_specs[$i]['groupid'];
            $filter_specs .= "filter_specs[". $all_specs[$i]['groupid'] ."]=[";
        }
        
        $filter_specs .= "[". $all_specs[$i]['id'] .",'". $all_specs[$i]['profname'] ."']";
        
        if ( $all_specs[$i+1]['groupid'] != $spec_now) {
            $filter_specs .= "];";
        }
        else {
            $filter_specs .= ",";
        }
    }
    
    $filter       = array();
    $cmd          = __paramInit( 'string', 'cmd',          null, '' );
    $toD          = __paramInit( 'string', 'to_d',         null, date('d') );
    $toM          = __paramInit( 'string', 'to_m',         null, date('m') );
    $toY          = __paramInit( 'string', 'to_y',         null, date('Y') );
    $act          = __paramInit( 'string', 'act',          null, 0 );
    $adm          = __paramInit( 'string', 'adm',          null, 0 );
    $category     = __paramInit( 'string', 'category',     null, 0 );
    $sub_category = __paramInit( 'string', 'sub_category', null, 0 );
    $search       = __paramInit( 'string', 'search',       null, '' );
    $order        = __paramInit( 'string', 'sort',         null, 'date' );
    $direction    = __paramInit( 'string', 'dir',          null, 'desc' );
    $search       = clearInputText( $search );
    
    if ( $cmd == 'filter' ) {
        $fromD = __paramInit( 'string', 'from_d', null, '' );
        $fromM = __paramInit( 'string', 'from_m', null, '' );
        $fromY = __paramInit( 'string', 'from_y', null, '' );
        
        $filter = admin_log::getDatePeriod( $error, $fromD, $fromM, $fromY, $toD, $toM, $toY );
        
        $filter['act_id']       = $act;
        $filter['admin_id']     = $adm;
        $filter['category']     = $category;
        $filter['sub_category'] = $sub_category;
        $filter['search']       = $search;
    }
    
    if ( !$error ) {
        $log   = $admin_log->getLogProj( $count, $filter, $page, $order, $direction );
        $pages = ceil( $count / $admin_log->getLogPerPage() );
    }
}