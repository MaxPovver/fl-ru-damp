<?php
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
if (!$xajax) {
    if ($_GET['p'] == 'opinions') {
        if ($ops_type == 'norisk') {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/sbr.common.php");
        } else {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/opinions.common.php");
        }
    } else {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/commune.common.php");
    }
    $xajax->printJavascript('/xajax/');
}

?>
        <?php /* if($p_user->is_pro == 'f') { ?>
        <div class="profile-advert">
            <?= printBanner240(0, 0, $g_page_id);//, $p_user->is_pro != 't') ?>
        </div>
        <? } */ ?>
        <div class="page-profile">
            <?php include ($fpath . "header.php") ?>
            <?php include ($fpath . "usermenu.php") ?>
            <div class="page-ops">
                <?php include ($_SERVER['DOCUMENT_ROOT'] . '/user/tpl.op_header.php') ?>
                <?php if ($inner) include ($fpath . $inner); else print('&nbsp;') ?>
            </div>
        </div>
