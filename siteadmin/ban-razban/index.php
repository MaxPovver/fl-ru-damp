<?
$no_banner = 1;
$rpath = "../../";

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/blogs.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects_complains.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/commune.php';
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer_offers.php' );

session_start();
get_uid();
setlocale(LC_ALL, "ru_RU.CP1251");

if (!hasPermissions('users')) {
    header ("Location: /404.php"); 
    exit;
}

$mode   = __paramInit( 'string', 'mode',   null,     '' ); // раздел
$sort   = __paramInit( 'string', 'sort',   null,     '' ); // сортировка
$page   = __paramInit( 'int',    'p',      null,     1 );  // номер страницы
$ft     = __paramInit( 'int',    'ft',     null,     0 );  // тип отображения
$admin  = __paramInit( 'int',    'admin',  null,     0 );  // uid админа, данные по которому смотрим
$action = __paramInit( 'string', 'action', 'action', '' ); // текущее действие
$search = __paramInit( 'string', 'search', null,     '' ); // поиск
$group  = __paramInit( 'string', 'group', null,     'new' ); // группа
$sort   = in_array( $sort, array('btime', 'utime',   'login') ) ? $sort : '';
$search = clearInputText( $search );

// где находимся
if ( !in_array($mode, array('users', 'commune', 'opinions', 'complain', 'complain_types')) ) {
    header ("Location: /404.php"); 
    exit;
}

if ( !$page ) {
	$page = 1;
}
elseif ( $page < 0 ) {
    header_location_exit( '/404.php' );
    exit;
}

$content    = '../content.php';
$css_file   = array( 'moderation.css', 'new-admin.css', 'nav.css','/css/block/b-menu/_tabs/b-menu_tabs.css' );
$js_file    = array( 'banned.js' );
$inner_page = $mode.'/content.php';
$header     = $rpath."header.php";
$footer     = $rpath."footer.html";
$log_pp     = __paramInit( 'int', 'log_pp', 'log_pp', 20 );

define( 'IS_SITE_ADMIN', 1 );
include "$mode/index.php";

function YellowLine($text, $search=FALSE) {
    if ($search === FALSE) $search = clearInputText(__paramInit( 'string', 'search', null, '') );
    $s = preg_split("/[\\s]+/", $search);
    for ($i=0; $i<count($s); $i++) {
		if ($s[$i]) $text = preg_replace('/('.preg_quote($s[$i]).')/i', "<span style='background-color: yellow; margin: 0;'>\$1</span>", $text);
    }
    return $text;
}

?>
