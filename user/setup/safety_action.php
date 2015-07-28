<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opauth/OpauthModel.php");
$opauthModel = new OpauthModel();

$social_multivel = $opauthModel->getMultilevel($uid);
if (!$social_multivel) {
    $social_bind_error = isset($_SESSION['opauth_error']) ? $_SESSION['opauth_error'] : '';
    unset($_SESSION['opauth_error']);

    $social_links = $opauthModel->getUserLinks($uid);            
}

$js_file[] = 'finance.js';
$js_file[] = 'user/safety.js';