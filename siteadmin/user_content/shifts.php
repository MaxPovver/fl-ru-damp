<?php
/**
 * Модерирование пользовательского контента. Смены. Контроллер.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }

$error   = '';
$cmd     = __paramInit( 'string', null, 'cmd', '' );
$aExId   = array();
$aExFrom = array();
$aExTo   = array();

if ( $cmd == 'go' ) {
    $aDelId   = __paramInit( 'array',  null, 'del_id',   array());
    $aExId    = __paramInit( 'array',  null, 'ex_id',    array());
    $aExFrom  = __paramInit( 'array',  null, 'ex_from',  array());
    $aExTo    = __paramInit( 'array',  null, 'ex_to',    array());
    $aAddFrom = __paramInit( 'array',  null, 'add_from', array());
    $aAddTo   = __paramInit( 'array',  null, 'add_to',   array());
    
    if ( !empty($aExId) || !empty($aExFrom) || !empty($aExTo) ) {
        if ( !$user_content->matchCount($aExId, $aExFrom, $aExTo) ) {
            $error = 'Ошибка сохранения смен';
        }
        elseif ( !$user_content->validTimes($aExFrom) || !$user_content->validTimes($aExTo)  ) {
            $error = 'Не все смены указаны корректно';
        }
    }
    
    if ( empty($error) && !empty($aAddFrom) && !empty($aAddTo) ) {
        if ( !$user_content->matchCount($aAddFrom, $aAddTo) ) {
            $error = 'Ошибка сохранения смен';
        }
        elseif ( !$user_content->validTimes($aAddFrom) || !$user_content->validTimes($aAddTo)  ) {
            $error = 'Не все смены указаны корректно';
        }
    }
    
    if ( empty($error) ) {
        if ( !empty($aDelId) ) {
            $user_content->deleteShifts( $aDelId );
        }

        if ( !empty($aExId) && !empty($aExFrom) && !empty($aExTo) ) {
            $user_content->updateShifts( $aExId, $aExFrom, $aExTo );
        }

        if ( !empty($aAddFrom) && !empty($aAddTo) ) {
            $user_content->insertShifts( $aAddFrom, $aAddTo );
        }
        
        $_SESSION['admin_shifts_success'] = true;
        header( 'Location: /siteadmin/user_content/?site=shifts' );
        exit;
    }
    
    $nShifts = count($aExFrom) + count($aAddFrom);
}
else {
    $aShifts = $user_content->getShifts();
    $nShifts = !empty($aShifts) ? count($aShifts) : 0;
    
    if ( $nShifts ) {
        foreach ( $aShifts as $aOne ) {
            $aExId[]   = $aOne['id'];
            $aExFrom[] = substr( $aOne['time_from'], 0, 5 );
            $aExTo[]   = substr( $aOne['time_to'], 0, 5 );
        }
    }
}