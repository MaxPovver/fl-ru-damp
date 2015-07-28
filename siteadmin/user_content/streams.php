<?php
/**
 * Модерирование пользовательского контента. Количество потоков в сменах. Контроллер.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }

$error     = '';
$cmd       = __paramInit( 'string', null, 'cmd', '' );
$aShifts   = $user_content->getShifts();
$nShifts   = count( $aShifts );
$aContents = $user_content->getContents();
$aStreams  = array();

if ( $cmd == 'go' ) {
    $aStreams = __paramInit( 'array',  null, 'streams', array());
    
    if ( $user_content->validShiftsContents($aStreams) ) {
        $user_content->updateShiftsContents( $aStreams );
        
        $_SESSION['admin_streams_success'] = true;
        header( 'Location: /siteadmin/user_content/?site=streams' );
        exit;
    }
    else {
        $error = 'Кол-во потоков должно быть целым числом от 1 до 32767';
    }
}
else {
    $aDB = $user_content->getShiftsContents();
    
    if ( is_array($aDB) && count($aDB) ) {
        foreach ( $aDB as $aOne ) {
            $aStreams[$aOne['content_id']][$aOne['shift_id']] = $aOne['streams'];
        }
    }
}