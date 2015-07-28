<?php
ini_set("memory_limit","512M");

$_SERVER['DOCUMENT_ROOT'] = realpath( dirname(__FILE__).'/../' );
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");

new_projects::joobleGenerateRss('upload/jooble.xml', '24 hours');
