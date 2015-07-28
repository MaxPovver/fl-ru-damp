<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
// Ключи - admin_actions.obj_code
$aClass = array(
    admin_log::OBJ_CODE_USER => 'color-666666',
    admin_log::OBJ_CODE_BLOG => 'color-45a300',
    admin_log::OBJ_CODE_PROJ => '',
    admin_log::OBJ_CODE_COMM => 'color-45a300',
    admin_log::OBJ_CODE_ART  => ''
);

$error = '';

if ( $sViweId ) {
    if ( !preg_match('/^[\d]+$/', $sViweId) ) {
    	header_location_exit( '/404.php' );
    }
    
    $aOne = $admin_log->getLogById( $sViweId );
}
else {
    $filter = array();
    $cmd    = __paramInit( 'string', 'cmd',    null, '' );
    $toD    = __paramInit( 'string', 'to_d',   null, date('d') );
    $toM    = __paramInit( 'string', 'to_m',   null, date('m') );
    $toY    = __paramInit( 'string', 'to_y',   null, date('Y') );
    $toH    = __paramInit( 'string', 'to_h',     null, '' );
    $toI    = __paramInit( 'string', 'to_i',     null, '' );
    $act    = __paramInit( 'string', 'act',    null, 0 );
    $adm    = __paramInit( 'string', 'adm',    null, 0 );
    $search = __paramInit( 'string', 'search', null, '' );
    $shiftChecked = '';
    $timeChecked  = '';
    $shiftEnabled = 'disabled="disabled"';
    $timeEnabled  = 'disabled="disabled"';
    $shiftId      = -1;    
    switch (__paramInit( 'string', 'period', null, 'shift')) {
        case "shift":
    	    $shiftChecked = 'checked';
    	    $shiftEnabled = '';
    	    $shiftId = __paramInit( 'int', 'shifts_list', null, -1);    	    
            break;
        case "time":
    	    $timeChecked = 'checked';
    	    $timeEnabled = '';
            break;    	
    }
    $search = clearInputText( $search );
    
    if ( $cmd == 'filter' ) {
        $fromD = __paramInit( 'string', 'from_d', null, '' );
        $fromM = __paramInit( 'string', 'from_m', null, '' );
        $fromY = __paramInit( 'string', 'from_y', null, '' );
        $fromH = __paramInit( 'string', 'from_h',   null, '' );
        $fromI = __paramInit( 'string', 'from_i',   null, '' );
        
        $filter = admin_log::getDatePeriod( $error, $fromD, $fromM, $fromY, $toD, $toM, $toY );
        
        if ( !$error ) {
            $error = admin_log::checkTimePeriod( $bIsNull, $fromH, $fromI, $toH, $toI );
            
            if ( !$error ) {
                if ( !$bIsNull ) {
                    $filter['time'] = array( $fromH . ':' . $fromI . ' - ' . $toH . ':' . $toI );
                }
                else {
                    $filter['time'] = array( '00:00 - 23:59' );
                }
                
                $filter['act_id']   = $act;
                $filter['admin_id'] = $adm;
                $filter['search']   = $search;
            }
        }
    }
    
    if ( !$error ) {
        $log   = $admin_log->getLogAll( $count, $filter, $page );
        $pages = ceil( $count / $admin_log->getLogPerPage() );
    }
    
    $actions = $admin_log->getAdminActions();
    $admins  = $admin_log->getAdminsInLog();
}