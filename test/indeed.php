<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");

new_projects::indeedGenerateRss('upload/indeed.xml');

?>