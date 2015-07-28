<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pskb.php");
// для беты и альфы
if (is_release()) {
    header_location_exit('/404.php');
}

$lc_id = __paramInit('int', 'lc');

$defaults = $defaults2 = array(
    'service' => 'test',
    'account' => $lc_id,
    'amount' => 100.22,
    'state' => 0,
);
$defaults['sign'] = pskb::signCardRequest($defaults);

$defaults2['state'] = -1;
$defaults2['sign'] = pskb::signCardRequest($defaults2);

?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
        <title>card</title>
        <link href="/css/pskb.css" rel="stylesheet" type="text/css">
    </head>
    <body class="b-page" style=" background:#f0ffdf;">
        <form id="res_frm_1" method="post" action="/income/pscb.php?res=1">
            <? foreach ($defaults as $k => $v) { ?>
            <input type="hidden" name="<?= $k ?>" value="<?= $v ?>"/>
            <? } ?>
            <input type="hidden" name="u_token_key" value="<?= $_SESSION['rand'] ?>"/>
        </form>
        <form id="res_frm_2" method="post" action="/income/pscb.php?res=2">
            <? foreach ($defaults2 as $k => $v) { ?>
            <input type="hidden" name="<?= $k ?>" value="<?= $v ?>"/>
            <? } ?>
            <input type="hidden" name="u_token_key" value="<?= $_SESSION['rand'] ?>"/>
        </form>
        <div class="b-layout" style="text-align:center; padding-top:150px;">
            <div class="b-layout__txt b-layout__txt_padbot_30">
                <a href="javascript:void(0)" onclick="document.getElementById('res_frm_1').submit();" class="b-button b-button_fla b-button_flat_green">Оплатил</a>
                или
                <a href="javascript:void(0)" onclick="document.getElementById('res_frm_2').submit();" class="b-button b-button_flat b-button_flat_red">Не оплатил или ошибка</a>
            </div>
        </div>
    </body>
</html>