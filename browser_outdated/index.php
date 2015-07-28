<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
session_start();
get_uid(false);

$page_content = array(0);
$name = false; // название браузера
$version = false; // версия браузера
// информация о браузерах
$browsers = array(
    'opera' => array (
        'fullname' => 'Opera',
        1 => array(1994, 1996),
        2 => array(1996, 1997),
        3 => array(1997, 2000),
        4 => array(2000, 2000),
        5 => array(2000, 2001),
        6 => array(2001, 2003),
        7 => array(2003, 2005),
        8 => array(2005, 2005),
        9 => array(2005, 2009),
        10 => array(2009, 2010),
        11 => array(2010, 2012),
        12 => array(2012),
    ),
    'chrome' => array (
        'fullname' => 'Google Chrome',
        1 => array(2008, 2009),
        2 => array(2009, 2009),
        3 => array(2009, 2010),
        4 => array(2010, 2010),
        5 => array(2010, 2010),
        6 => array(2010, 2010),
        7 => array(2010, 2010),
        8 => array(2010, 2011),
        9 => array(2011, 2011),
        10 => array(2011, 2011),
        11 => array(2011, 2011),
        12 => array(2011, 2011),
        13 => array(2011, 2011),
        14 => array(2011, 2011),
        15 => array(2011, 2011),
        16 => array(2011, 2012),
        17 => array(2012, 2012),
        18 => array(2012, 2012),
        19 => array(2012, 2012),
        20 => array(2012)
    ),
    'safari' => array (
        'fullname' => 'Safari',
        1 => array(2003, 2005),
        2 => array(2005, 2007),
        3 => array(2007, 2008),
        4 => array(2008, 2010),
        5 => array(2010),
    ),
    'msie' => array (
        'fullname' => 'Internet Explorer',
        1 => array(1995, 1995),
        2 => array(1995, 1996),
        3 => array(1996, 1997),
        4 => array(1997, 1999),
        5 => array(1999, 2001),
        6 => array(2001, 2006),
        7 => array(2006, 2008),
        8 => array(2008, 2011),
        9 => array(2011),
    ),
    'firefox' => array (
        'fullname' => 'Firefox',
        1 => array(2004, 2006),
        2 => array(2006, 2008),
        3 => array(2008, 2011),
        4 => array(2011, 2011),
        5 => array(2011, 2011),
        6 => array(2011, 2011),
        7 => array(2011, 2011),
        8 => array(2011, 2011),
        9 => array(2011, 2011),
        10 => array(2011, 2011),
        11 => array(2011, 2011),
        12 => array(2011, 2012),
        13 => array(2012),
    ),
    'mozilla' => array (
        'fullname' => 'Mozilla'
    )
);

$browser_outdated = browserCompat($name, $version);

$browser = $browsers[$name];
$page_content['name_version'] = $browser['fullname'] . ' ' . $version[1];
// годы жизни
$vers = floor($version[1]);
$page_content['born'] = $browser[$vers][0];
$page_content['die'] = $browser[$vers][1];

if ($browser_outdated) {
    $content = 'tpl.browser_not_outdated.php';
} else {
    $content = 'tpl.browser_outdated.php';
}

$rpath = "../";
$browser_outdated_page = true;
include("../template2.php");
?>