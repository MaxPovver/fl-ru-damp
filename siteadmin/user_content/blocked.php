<?php
/**
 * Модерирование пользовательского контента. Заблокированные сущности. Контроллер.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }

$sMode      = __paramInit( 'string',  'mode',   null, '' );
$aModeAllow = array( '', 'letters' );
$js_file[]  = 'banned.js';
$js_file[]  = 'adm_edit_content.js';
$js_file[]  = 'highlight.min.js';
$js_file[]  = 'highlight.init.js';
$js_file[]  = '/css/block/b-shadow/b-shadow.js';
$js_file[]  = 'attachedfiles.js';
$js_file[]  = 'polls.js';

if ( in_array($sMode, $aModeAllow) ) {
    switch ( $sMode ) {
        case 'letters': // просмотр переписки из заблокированой лички
            include_once( 'stream_header.php' );
            
            $nFromId    = __paramInit( 'integer', 'fid', null, 0 );
            $nToId      = __paramInit( 'integer', 'tid', null, 0 );
            $nMsgId     = __paramInit( 'integer', 'lid', null, 0 );
            $oToUser    = $oFromUser = null;
            $sStreamId  = '';
            $sContentId = 1;
            
            if ( $nFromId && $nToId ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );

                $oFromUser = new users();
                $oFromUser->GetUserByUID( $nFromId );

                $oToUser = new users();
                $oToUser->GetUserByUID( $nToId );
            }
            
            include_once( 'stream_inner_letters.php' );
            include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/ban_overlay.php' );
            include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/warn_overlay.php' );
            include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/del_overlay.php' );
            include_once( 'stream_footer.php' );
            break;

        default: // заблокированные сущности
            $nCid      = __paramInit( 'int',  'cid', null, 0 );
            $aContents = $user_content->getBlockedContentsForUser();
            
            if ( !$nCid ) { // Выбор типа заблокированных сущностей
                $inner_page = "blocked_choose_inner.php";
                
                if ( !$aContents ) {
                    header_location_exit( '/404.php' );
                    exit;
                }
            }
            elseif ( !$user_content->hasContentPermissions($nCid) ) {
                header_location_exit( '/404.php' );
                exit;
            }
            
            if ( $nCid && !in_array($nCid, array_keys($aContents)) ) {
                header_location_exit( '/404.php' );
                exit;
            }
            
            if ( $nCid == user_content::MODER_PRJ_OFFERS ) {
                $css_file[] = 'contest.css';
                $js_file[] = 'ibox.js';
                $js_file[] = 'contest.js';
            }
            
            include( $rpath . $template );
            break;
    }
}
else {
    header('Location: /404.php'); exit;
}