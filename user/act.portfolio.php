<?php
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/kwords.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/uploader/uploader.php");
$templates = array(
    uploader::getTemplate('uploader', 'portfolio/'),
    uploader::getTemplate('uploader.file', 'portfolio/'),
    uploader::getTemplate('uploader.popup', 'portfolio/'),
);
$stop_words = new stop_words( hasPermissions('users') );
setlocale(LC_ALL, 'ru_RU.CP1251');
$portf = new portfolio();
$prjs = $portf->GetPortf($user->uid, 'NULL', true);
$prfs = new professions();
$profs = $prfs->GetAllProfessionsPortfWithoutMirrored($user->uid, "AND t.user_id IS NOT NULL");
$is_not_spec = (sizeof($profs)<=0);
$first_profs = current($profs);
if ($specs_add) {
    $specs_add_array = array();
    for ($si = 0; $si<sizeof($specs_add); $si++) {
        $specs_add_array[$si] = professions::GetProfNameWP($specs_add[$si], ' / ');
	}
	$specs_add_string = join(", ", $specs_add_array);
} else {
    $specs_add_string = "Нет";
}

$html_keyword_js = '<a href="/freelancers/?word=$1" class="inherit">$2</a>';
$html_keyword = preg_replace('/\$\d/', '%s', $html_keyword_js);
$is_owner  = ( $user->login == $_SESSION['login'] );
if($prjs) {
    $result = $portf->prepareDataPortfolio($prjs, $user->uid, $stop_words);
    extract($result);
}
$sSpecText = $user->isChangeOnModeration( $user->uid, 'spec_text' ) && $user->is_pro != 't' ? $stop_words->replace($user->spec_text) : $user->spec_text;