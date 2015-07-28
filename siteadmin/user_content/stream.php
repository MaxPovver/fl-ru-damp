<?php
/**
 * Модерирование пользовательского контента. Контроллер.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }

$sContentId  = __paramInit( 'string',  'cid',    null, '' );
$sStreamId   = __paramInit( 'string',  'sid',    null, '' );
$nStatus     = __paramInit( 'integer', 'status', null, 0 );
$sMode       = __paramInit( 'string',  'mode',   null, '' );
$aModeAllow  = array( '', 'letters'/*, 'choose'*/ );
$aStream     = array();
$checkStream = $user_content->checkStream( $sContentId, $sStreamId, $uid, $aStream );
$js_file[]   = 'banned.js';
//$js_file[]   = 'adm_edit_content.js';
$js_file[]   = 'highlight.min.js';
$js_file[]   = 'highlight.init.js';
$js_file[]   = '/css/block/b-shadow/b-shadow.js';
$js_file[]   = 'attachedfiles.js';
$js_file[]   = 'polls.js';
$css_file[]  = 'hljs.css';
$page_title  = '';
$sOutput     = '';

if ( !in_array($sMode, $aModeAllow) ) {
    header('Location: /404.php'); exit;
}

switch ($sMode) {
    case 'letters':
        $nFromId = __paramInit( 'integer', 'fid', null, 0 );
        $nToId   = __paramInit( 'integer', 'tid', null, 0 );
        $nMsgId  = __paramInit( 'integer', 'lid', null, 0 );
        $oToUser = $oFromUser = null;

        if ( $nFromId && $nToId ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );

            $oFromUser = new users();
            $oFromUser->GetUserByUID( $nFromId );

            $oToUser = new users();
            $oToUser->GetUserByUID( $nToId );
            
            $page_title = 'Переписка ' . $oFromUser->login . ' с ' . $oToUser->login;
        }

        ob_start();
        include_once( 'stream_inner_letters.php' );
        $sOutput = ob_get_contents();
        ob_end_clean();
        break;

    case 'choose':
        if ( $user_content->hasContentPermissions($sContentId) ) {
            if ( $sStreamId != $user_content->chooseStream($sContentId, $sStreamId, $uid) ) {
                $checkStream = false;
                ob_start();
                include_once( 'stream_inner.php' );
                $sOutput = ob_get_contents();
                ob_end_clean();
            }
            else {
                $sUrl = '/siteadmin/user_content/?site=stream&cid=' . $sContentId . '&sid=' . $sStreamId;
                header( 'Location: '. $sUrl );
                exit;
            }
        }
        break;

    default:
        $sContentName = '';

        foreach ($user_content->contents as $aOne ) {
            if ( $aOne['id'] == $sContentId ) {
                $sContentName = $aOne['name'];
            }
        }
        
        $page_title = $sContentName . ', поток ' . $aStream['title_num'];
        
        ob_start();
        include_once( 'stream_inner.php' );
        $sOutput = ob_get_contents();
        ob_end_clean();
        break;
}

include_once( 'stream_header.php' );

echo $sOutput;

include_once( 'stream_footer.php' );
