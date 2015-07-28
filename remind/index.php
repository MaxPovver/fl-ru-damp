<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sms_services.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/captcha.php");

session_start();

$no_banner = false;
$stretch_page = true;
$footer_remind = true;
$hide_banner_top = true;

$header = "header.php";
$footer = "footer.html";
$content = "rem_inner.php";

$css_file = "/css/block/b-captcha/b-captcha.css";
$js_file  = array( 
    'mootools-form-validator.js',
    'remind.js' 
);


include ("../template3.php");