<? 
/** 
 * для фрейма привязки карт (ПСКБ)
 * https://beta.free-lance.ru/mantis/view.php?id=23027 
 */
?><html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
        <title>card</title>
        <link href="/css/pskb.css" rel="stylesheet" type="text/css">
    </head>
    <body class="b-page" style=" background:#f0ffdf;">
        <div class="b-layout" style="text-align:center; padding-top:150px;">
            <? if ($src === 1) { ?>
                <div class="b-layout__txt b-layout__txt_padbot_30"><span class="b-icon b-icon_sbr_gok b-icon_margleft_-20"></span>Средства успешно списаны</div>
                <a href="javascript:void(0)" onclick="top.pskb_frame_close(1)" class="b-button b-button_flat b-button_flat_green">Закрыть</a>
            <? } ?>

            <? if ($src == 2) { ?>
                <div class="b-layout__txt b-layout__txt_padbot_30 b-layout__txt_color_c10600"><span class="b-icon b-icon_sbr_rattent b-icon_margleft_-20"></span><?= $err_msg?></div>
                <a href="javascript:void(0)" onclick="top.pskb_frame_close(0)" class="b-button b-button_flat b-button_flat_red">Закрыть</a>
            <? } ?>
        </div>
    </body>
</html>