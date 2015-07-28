<?php

ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/log.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/multi_log.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/contacts.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sitemap.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stats.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/hh.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stat_collector.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/banners.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/maintenance.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/search_parser.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/spam.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/articles.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users_suspicious_contacts.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/firstpage.php");


/*
sitemap::create('blogs', false);
sitemap::create('projects', false);
sitemap::create('commune', false);
sitemap::create('articles', false);
sitemap::create('interview', false);
sitemap::create('portfolio', false);
sitemap::create('users', false);
sitemap::create('catalog', false);
sitemap::create('userpages', false);
*/

sitemap::create('catalog', false);
//sitemap::update('catalog', false);


//sitemap::create('projects', false);
//sitemap::update('projects', false);

//sitemap::generateMainSitemap();