<?php
/**
 * Модерирование пользовательского контента. Фреймы. Контроллер.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }

$sMode       = __paramInit( 'string',  'mode',   null, '' );
$aModeAllow  = array( '', 'choose' );
$bChooseErr  = false;

if ( !in_array($sMode, $aModeAllow) ) {
    header('Location: /404.php'); exit;
}

if ( $sMode == 'choose' ) {
    $sContentId  = __paramInit( 'string',  'cid',    null, '' );
    $sStreamId   = __paramInit( 'string',  'sid',    null, '' );

    if ( $user_content->hasContentPermissions($sContentId) ) {
        if ( $sStreamId != $user_content->chooseStream($sContentId, $sStreamId, $uid) ) {
            $bChooseErr = true;
        }
        else {
            header('Location: /siteadmin/user_content/?site=frames'); exit;
        }
    }
}

$aStreams = $user_content->getStreamsForUser( $uid );

// разделы проектов
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php' );

$all_specs = professions::GetAllProfessions("", 0, 1);
$spec_now  = 0;
$sSpecs    = '';

for ( $i = 0; $i < sizeof($all_specs); $i++ ) {
    if ( $all_specs[$i]['groupid'] != $spec_now ) {
        $spec_now = $all_specs[$i]['groupid'];
        $sSpecs .= "adm_edit_content.prj_specs[" . $all_specs[$i]['groupid'] . "]=[";
    }

    $sSpecs .=  "[" . $all_specs[$i]['id'] . ",'" . $all_specs[$i]['profname'] . "']";

    if ( $all_specs[$i + 1]['groupid'] != $spec_now ) {
        $sSpecs .=  "];";
    } 
    else {
        $sSpecs .=  ",";
    }
}

include_once( 'frames_inner.php' );
