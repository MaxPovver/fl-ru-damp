<?php
/**
 * Действия админов
 *     - Лента всех действий
 *     - Нарушители (бан и предупреждения)
 *     - Проекты и конкурсы
 *     - Предложения
 *     - Все модераторы
 *     - Уведомления
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
define( 'IS_SITE_ADMIN', 1 );
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/admin_log.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/user_content.php");
session_start();

$uid  = get_uid();
$site = __paramInit( 'string', 'site', 'site', 'log' );

if ( !in_array($site, array('log', 'proj', 'user')) ) {
    header_location_exit( '/404.php' );
    exit;
}

if ( !isset($aPermissions) ) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/permissions.php");
    $aPermissions = permissions::getUserPermissions( $uid );
}

if ( 
    !admin_log::isAllowed( 'adm', $aPermissions ) // право "Доступ в админку"
    || !in_array( $site, admin_log::$mode_allow ) // существующий раздел
    || !admin_log::isAllowed( admin_log::$mode_permissions[$site], $aPermissions )  // право на конкретный раздел
) {
    header_location_exit( '/404.php' );
}

$menu_item  = array_search( $site, admin_log::$mode_allow ) + 1;
$rpath      = '../../';
$css_file   = array( 'moderation.css', 'new-admin.css', 'nav.css' );
$js_file    = array( 'admin_log.js' );
$header     = $rpath . 'header.php';
$inner_page = "{$site}_inner.php";
$content    = '../content22.php';
$footer     = $rpath . 'footer.html';
$template   = 'template2.php';
$admin_log  = new admin_log( $site, $uid, $aPermissions );
$sLogId     = __paramInit( 'string', 'lid', 'lid' );
$sViweId    = __paramInit( 'string', 'view' );
$page       = __paramInit( 'int', 'page', 'page', 1 );
$log_pp     = __paramInit( 'int', 'log_pp', 'log_pp', 20 );

$admin_log->setLogPerPage( $log_pp );

if ( !$page ) {
	$page = 1;
}
elseif ( $page < 0 ) {
    header_location_exit( '/404.php' );
    exit;
}

if ( $sViweId ) {
    $sLogId    = $sViweId;
    $menu_item = 0;
}

if ( $sLogId ) {
    if ( !preg_match('/^[\d]+$/', $sLogId) || !$admin_log->getLogById($sLogId) ) {
    	header_location_exit( '/404.php' );
    }
    
    $aUTL = admin_log::getUserToLogInfo( $uid, $sLogId );
    admin_log::setLogCLV( $uid, $sLogId );
    
	require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/comments/CommentsAdminLog.php' );
	$maxlevel = 10;
    $maxwidth = 460;
    $minus_level_width = 2;
    $comments = new CommentsAdminLog( $sLogId, $aUTL['last_comment_view'], 
        array(
            "maxlevel"          => $maxlevel, 
            "maxwidth"          => $maxwidth, 
            "minus_level_width" => $minus_level_width,
            "hidden_threads"    => $aUTL['hidden_threads']
        ) 
    );
    $comments->tpl_path = $_SERVER['DOCUMENT_ROOT'] . '/classes/comments/';
    $comments_html = $comments->render();
    $css_file[] = 'wysiwyg.css';
    $css_file[] = 'hljs.css';
    $js_file    = array_merge( $js_file, array('highlight.min.js', 'highlight.init.js', 'mooeditable.new/MooEditable.ru-RU.js', 
        'mooeditable.new/rangy-core.js', 'mooeditable.new/MooEditable.js', 'mooeditable.new/MooEditable.Pagebreak.js', 
        'mooeditable.new/MooEditable.UI.MenuList.js', 'mooeditable.new/MooEditable.Extras.js', 'mooeditable.new/init.js', 
        'comments.all.js', 'banned.js') );
}

$aDays   = range( 1, 31 );
$aYears  = array_reverse( range(2007, date('Y')) );
$aMounth = array(
    '01' => "Января", 
    '02' => "Февраля", 
    '03' => "Марта", 
    '04' => "Апреля", 
    '05' => "Мая", 
    '06' => "Июня", 
    '07' => "Июля", 
    '08' => "Августа", 
    '09' => "Сентября", 
    '10' => "Октября", 
    '11' => "Ноября", 
    '12' => "Декабря"
);
$shifts = user_content::getShifts();
// admin_actions.id
$aRed    = array( 3, 5, 7, 9, 11, 13, 15, 18, 22, 24, 27, 29, 31 );
$aYellow = array( 1 );
$aGreen  = array( 2, 4, 6, 8, 10, 12, 14, 16, 17, 19, 23, 25, 28, 30, 32 );

// Ключи - admin_actions.obj_code
$aClass = array(
    admin_log::OBJ_CODE_USER => 'color-666666',
    admin_log::OBJ_CODE_BLOG => 'color-45a300',
    admin_log::OBJ_CODE_PROJ => '',
    admin_log::OBJ_CODE_COMM => 'color-45a300',
    admin_log::OBJ_CODE_ART  => ''
);

// причина действия содержит другие данные
$aReasonData = array(
    admin_log::ACT_ID_BLOG_CH_GR, 
    admin_log::ACT_ID_PRJ_CH_SPEC, 
    admin_log::ACT_ID_PRJ_DEL_OFFER, 
    admin_log::ACT_ID_PRJ_RST_OFFER, 
    admin_log::ACT_ID_BLOG_DEL_COMM, 
    admin_log::ACT_ID_BLOG_RST_COMM, 
    admin_log::ACT_ID_USR_CH_RATING 
);

// в ленте показывать атора объекта для следующих действий admin_actions.id 
$aLogShowAuthor = array(
    admin_log::ACT_ID_PRJ_BLOCK_OFFER, 
    admin_log::ACT_ID_PRJ_UNBLOCK_OFFER, 
    admin_log::ACT_ID_PORTFOLIO_BLOCK, 
    admin_log::ACT_ID_PORTFOLIO_UNBLOCK, 
    admin_log::ACT_ID_PRJ_DIALOG_BLOCK, 
    admin_log::ACT_ID_PRJ_DIALOG_UNBLOCK 
);

include( "{$site}.php" );
include( $rpath . $template );