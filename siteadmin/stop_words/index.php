<?php
/**
 * Стоп-слова. Контроллер.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
define( 'IS_SITE_ADMIN', 1 );
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stop_words.php");

session_start();

$uid  = get_uid();
$site = __paramInit( 'string', 'site', 'site', 'words' );

if ( !in_array($site, stop_words::$site_allow) ) {
    header_location_exit( '/404.php' );
    exit;
}

if ( !hasPermissions('all') ) { // TODO: сделать разграничение прав
    header_location_exit( '/404.php' );
    exit;
}

$error = '';
$cmd   = __paramInit( 'string', null, 'cmd',   '' );

$stop_words = new stop_words( true );

if ( $site == 'words' ) {
    // Подозрительные слова
    if ( $cmd == 'go' ) {
        $sStopWords = clearInputText( __paramInit('array', null, 'words', '') );
        
        if ( stop_words::updateAdminStopWords($sStopWords) ) {
            $_SESSION['admin_stop_words_success'] = TRUE;
            header( 'Location: /siteadmin/stop_words/?site=words' );
            exit;
        }
        else {
            $error = 'Ошибка при сохранеии стоп-слов';
        }
    }
    else {
        $sStopWords = implode( ', ', stop_words::getAdminStopWords(false) );
    }
}
else {
    // Запрещенные выражения
    if ( $cmd == 'go' ) {
        $sStopRegex = clearInputText( __paramInit('array', null, 'regex', '') );
        $sTestText  = clearInputText( __paramInit('array', null, 'test', '') );
        $sBadRegex  = stop_words::validateAdminStopRegex( $sStopRegex );
        
        if ( !$sBadRegex ) {
            $action = __paramInit( 'string', null, 'action',   '' );
            
            if ( $action == 'update' ) {
                if ( stop_words::updateAdminStopRegex($sStopRegex) ) {
                    $_SESSION['admin_stop_words_success'] = TRUE;
                    header( 'Location: /siteadmin/stop_words/' );
                    exit;
                }
                else {
                    $error = 'Ошибка при сохранеии стоп-слов';
                }
            }
            else {
                $stop_words->setStopRegex( $sStopRegex );
                
                $sUserMode  = $stop_words->replace( $sTestText, 'html', false );
                $sAdminMode = $stop_words->replace( $sTestText, 'html', true );
            }
        }
        else {
            $error = 'Выражение "' . $sBadRegex . '" не корректно';
        }
    }
    else {
        $sStopRegex = implode( "\n", stop_words::getAdminStopRegex(false) );
    }
}

$menu_item  = 14;
$rpath      = '../../';
$js_file    = array( 'stop_words_admin.js' );
$css_file    = array('moderation.css','nav.css' );
$header     = $rpath . 'header.php';
$inner_page = "index_inner.php";
$content    = '../content22.php';
$footer     = $rpath . 'footer.html';
$template   = 'template2.php';

include( $rpath . $template );