<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/mailer.common.php");
$xajax->printJavascript('/xajax/'); 
?>

<div class="b-layout">	
    <?/*
    <a class="b-button b-button_round_green b-button_float_right close-block "  href="/siteadmin/mailer/?action=digest_edit&id=<?=$message['id']?>">
        <span class="b-button__b1">
            <span class="b-button__b2">
                <span class="b-button__txt">Повторить рассылку</span>
            </span>
        </span>
    </a>*/?>
    <h2 class="b-layout__title b-layout__title_padbot_30">Отчёт по рассылке  &#160;&#160;&#160;<a class="b-layout__link b-layout__link_fontsize_13" href="/siteadmin/mailer/">Все рассылки</a></h2>
    <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_5" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130">
                <div class="b-layout__txt">Тема письма</div>
            </td>
            <td class="b-layout__right">
                <div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_15"><?= reformat($message['subject'], 30)?></div>
            </td>
        </tr>
    </table>
    <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130">
                <div class="b-layout__txt">Текст письма</div>
            </td>
            <td class="b-layout__right">
                <div class="b-layout__txt b-layout__txt_padbot_10">
                    <a href="/siteadmin/mailer/?action=digest_edit&id=<?=$message['id']?>&preview=2">Просмотр</a>
                </div>
            </td>
        </tr>
    </table>
    <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_5" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130">
                <div class="b-layout__txt">Получатели</div>
            </td>
            <td class="b-layout__right">
                <div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_15"><?= (int) ($sum_rec)?> <?=ending((int) ($sum_rec), "человек", "человека", "человек")?></div>
            </td>
        </tr>
    </table>

    <table class="b-layout__table b-layout__table_width_full b-layout__table_margtop_30" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130">
                <div class="b-layout__txt">Отправлено</div>
            </td>
            <td class="b-layout__right">
                <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10 b-layout__txt_fontsize_15"><?= date('d.m.Y в H:i', strtotime($message['real_date_sending']))?></div>
                <div class="b-layout__txt b-layout__txt_padbot_5">
                    <?= ($message['type_regular'] > 1)?"Рассылается регулярно.": ""?>

                    <?php if($message['type_regular'] > 1) {?>
                    <?=  mailer::$TYPE_REGULAR[$message['type_regular']];?>
                    <?= !empty(mailer::$SUB_TYPE_REGULAR[$message['type_regular']]) ? strtolower(mailer::$SUB_TYPE_REGULAR[$message['type_regular']][$message['type_send_regular']]) : ""; ?>
                    <?php }//if?>
                </div>
                <?php if($message['type_regular'] > 1) {?>
                <div class="b-layout__txt b-layout__txt_padbot_5">
                    <input type="hidden" id="status_sending" value="<?=$message['status_sending']?>">
                    <span class="b-layout__mail-icon <?= $message['status_sending'] == 1?"b-layout__mail-icon_black":"b-layout__mail-icon_pause"?> b-layout__mail-icon_top_4 b-layout__mail-icon_margleft_-15 b-layout__mail-icon_margright_4"></span>
                    Следующая рассылка <?=date('d.m.Y в H:i', strtotime($message['date_sending']))?>.&#160;&#160;
                    <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_0f71c8 mail-pause" href="javascript:void(0)" onclick="xajax_setStatusSending(<?=(int)$message['id']?>, $('status_sending').get('value'));"><?= $message['status_sending'] == 1?"Поставить на паузу":"Снять с паузы"?></a>
                </div>
                <?php } //if?>
            </td>
        </tr>
    </table>
</div>