<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/mailer.common.php");
$xajax->printJavascript('/xajax/'); 
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/uploader/uploader.php");
$templates = array(
    uploader::getTemplate('uploader', 'wysiwyg/'),
    uploader::getTemplate('uploader.file', 'wysiwyg/'),
    uploader::getTemplate('uploader.popup'),
);
uploader::init(array(), $templates, 'wysiwyg');
?>
<script type="text/javascript">
    
var EMP_CNT = '<?= number_format($rec_emp_count, 0, '', ' ');?>';
var FRL_CNT = '<?= number_format($rec_frl_count, 0, '', ' ');?>';
var ALL_CNT = '<?= number_format($rec_emp_count + $rec_frl_count, 0, '', ' ');?>';
CKEDITOR.config.customConfig = '/scripts/ckedit/config_admin.js';
window.addEvent('domready', 
    function() {
    <? foreach($blocks->getBlocks() as $i => $block) { ?>
            var blc<?= $i;?> = new Digest({
                'is_wysiwyg' : '<?= ($block->isWysiwyg() ? 'true' : 'false');?>',
                'name'       : '<?= $block; ?>',
                'num'        : '<?= $block->getNum();?>',
                'main'       : <?= ($block->isMain() ? 'true' : 'false'); ?>,
                'is_create'  : <?= ($block->isCreated() ? 'true' : 'false'); ?>,
                'is_add_fld' : <?= ($block->isAdditionFields() ? 'true' : 'false'); ?>
            });
    <? } //foreach?>
    
    initNaviButton();
    initCheckSelect();
    checkRecipients();
    setInitPosition();
    if($('digest_post').getElements('.b-combo__input_error')[0] || $('digest_post').getElements('.b-form__error')[0]) {
        var error_element = $('digest_post').getElements('.b-form__error')[0] ? $('digest_post').getElements('.b-form__error')[0] : $('digest_post').getElements('.b-combo__input_error')[0];
        JSScroll(error_element);
    }
});
</script>

<h2 class="b-layout__title b-layout__title_padbot_30">Новый дайджест&#160;&#160;&#160;<a class="b-layout__link b-layout__link_fontsize_13" href="/siteadmin/mailer/">Все рассылки</a></h2>
<div class="b-fon b-fon_padbot_20">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffebbf">
        <span class="b-icon b-icon_sbr_oattent b-icon_margleft_-25"></span>Будте внимательны при работе с массовой рассылкой! Перепроверяйте содержание и настройки рассылки очень внимательно: выполненная рассылка не может быть отменена или изменена. 
    </div>
</div>    

<? if($_SESSION['is_save_digest']) { unset($_SESSION['is_save_digest']);?>
<div class="b-fon b-fon_padbot_20">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
        <span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span>Данные успешно сохранены
    </div>
</div>    
<? }//if?>

<form method="POST" name="digestPost" id="digest_post">
    <input type="hidden" id="draft" name="draft" value="<?= ($digest['in_draft'] ? '1' : '0'); ?>">
    <input type="hidden" id="preview" name="preview" value="0">
    <input type="hidden" name="action" value="<?= $is_edit ? "digest_edit" : "digest";?>">
    <?php if($is_edit) { ?>
    <input type="hidden" name="id" value="<?= $id; ?>">
    <?php }//if?>
<div class="b-fon b-fon_padbot_30" id="main_form">
    <div class="b-fon__body b-fon__body_pad_20">
        <table class="b-layout__table b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0">
            <? if ( $_error['check_recipient'] ) { ?>
            <tr class="b-layout__tr" id="error_check_recipient">
                <td class="b-layout__left b-layout__left_width_55"></td>
                <td colspan="2" class="">
                    <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                        <span class="b-form__error"></span> Необходимо выбрать хотя бы одного получателя
                    </div>
                </td>
            </tr>
            <? } ?>
            
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_150"><div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_lineheight_13">Получатели</div></td>
                <td class="b-layout__right b-layout__right_padbot_20"><div class="b-check b-check_padbot_10">
                        <input onchange="checkRecipients();" id="chk_frl" class="b-check__input" name="freelancers" type="checkbox" value="1" <?= $digest['filter_frl'] !== null ? "checked" : ""?> onclick="if($('error_check_recipient')) $('error_check_recipient').destroy();"/>
                        <label for="chk_frl" class="b-check__label"><span class="b-username b-username__role b-username__role_valign_top b-username__role_frl"></span>Фрилансеры</label>
                    </div>
                    <div class="b-check">
                        <input onchange="checkRecipients();" id="chk_emp" class="b-check__input" name="employers" type="checkbox" value="1" <?= $digest['filter_emp'] !== null ? "checked" : ""?> onclick="if($('error_check_recipient')) $('error_check_recipient').destroy();"/>
                        <label for="chk_emp" class="b-check__label"><span class="b-username b-username__role b-username__role_valign_top b-username__role_emp"></span>Работодатели</label>
                    </div>
                </td>
            </tr>
            
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_padbot_10 b-layout__left_width_150"><div class="b-layout__txt b-layout__txt_padtop_7 b-layout__txt_fontsize_11 b-layout__txt_lineheight_13">Тема письма:</div></td>
                <td class="b-layout__right b-layout__right_padbot_10">
                    <div class="b-combo">
                        <div class="b-combo__input <?= $_error['title_mail'] ? "b-combo__input_error" : ""?>">
                            <input class="b-combo__input-text" type="text" value="<?= $digest['subject']?>" size="80" name="title_mail" autocomplete="off"/>
                        </div>
                    </div>                            
                </td>
            </tr>
            <?/*
            <tr class="b-layout__tr">
                <td class="b-layout__left" colspan="2">
                    <div class="b-textarea">
                        <textarea class="b-textarea__textarea" cols="" rows="" name="message_mail" ><?= $message_mail; ?></textarea>
                    </div>
                </td>
            </tr>
             */ ?>
        </table>
    </div>
</div> 

<? if($_error['block']) { ?>
<div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10" id="error_blocks_select">
    <span class="b-form__error"></span> Необходимо выбрать хотя бы один отображаемый блок
</div>    
<? }//if?>
    
<? foreach($blocks->getBlocks() as $i => $block) { ?>
<span class="BlockList <?= $block;?>" id="<?=$block?><?= $block->getNum();?>">
    <?= $block->displayBlock(); ?>
</span>
<? }//foreach?>

<div class="b-fon b-fon_padbot_30">
    <div class="b-fon__body b-fon__body_pad_20">
        <table class="b-layout__table b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_padbot_10 b-layout__left_width_150"><div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_lineheight_13">Время рассылки:</div></td>
                <td class="b-layout__right b-layout__right_padbot_10">
                    <div class="b-radio b-radio_layout_vertical">
                        <div class="b-radio__item b-radio__item_padbot_10">
                            <input id="send_now" class="b-radio__input" name="send_type" type="radio" value="1" <?= ($send_type == 1 || $send_type == null ? "checked" : ""); ?> />
                            <label class="b-radio__label b-radio__label_fontsize_13" for="send_now">Мгновенно</label> 
                        </div>
                        <div class="b-radio__item b-radio__item_padbot_10">
                            <input id="send_time" class="b-radio__input b-radio__input_top_5 b-radio__input_valign_top" name="send_type" type="radio" value="2" <?= ($send_type == 2 ? "checked" : ""); ?> />
                            <label class="b-radio__label" for="send_time">
                                <div class="b-combo b-combo_inline-block ">
                                    <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes use_past_date">
                                        <input class="b-combo__input-text" type="text" size="80" name="send_date" value="<?= ( $digest['date_sending'] ? date('d.m.Y', strtotime($digest['date_sending'])) : date('d.m.Y') );?>" />
                                        <span class="b-combo__arrow-date"></span>
                                    </div>
                                </div>
                                <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_2">&nbsp;&nbsp;&nbsp;Время&nbsp;</span>
                            </label>
                            <div class="b-select b-select_inline-block ">
                                <select id="time_sending" name="time_sending">
                                    <?php foreach(range(0,23) as $hour) {?>
                                    <option value="<?= ($hour<10?"0".$hour:$hour )?>" <?= ($hour == date('G', strtotime($digest['date_sending'])) ?"selected":"")?>><?= ($hour<10?"0".$hour:$hour )?>:00</option>
                                    <?php }//foreach?>
                                </select>
                                <label class="b-combo__label" for="c1"></label>
                            </div>
                        </div>
                    </div>
                    <?/*
                    <div class="b-check">
                        <input id="regular_week" class="b-check__input" name="regular_week" type="checkbox" value="1" <?= ( $regular ? "checked" : "" ) ;?>  <?= ( $send_type == 2 ? "" : "disabled" );?> />
                        <label for="regular_week" class="b-check__label b-check__label_fontsize_13">Еженедельно</label>
                    </div>
                     */ ?>
                </td>
            </tr>
        </table>
    </div>
</div> 


<div class="b-fon">
    <div class="b-fon__body b-fon__body_pad_10_20">
        <div class="b-layout__txt b-layout__txt_fontsize_15">Итого получателей: <span class="b-layout__bold" id="count_recipient"></span></div>
    </div>
</div>

<div class="b-buttons b-buttons_padtop_40">
    <a class="b-button b-button_flat b-button_flat_green"  href="javascript:void(0)" onClick="if(confirm('Перепроверяйте содержание и настройки рассылки очень внимательно: выполненная рассылка не может быть отменена или изменена. Отправить рассылку?')) { $('draft').set('value', '0'); $('preview').set('value', '0');  $('digest_post').submit(); }">Отправить рассылку</a>
    &#160;&#160;&#160;&#160;<a class="b-buttons__link b-buttons__link_color_c10601" href="javascript:void(0)" onclick="$('draft').set('value', '1'); $('digest_post').submit();"><?= $is_edit ? 'Сохранить в черновики' : 'Поместить в черновики'?></a>
    <span class="b-buttons__txt">&#160;&#160;&#160;&#160;</span>	
    <a class="b-buttons__link" href="javascript:void(0)" onclick="$('draft').set('value', '1'); $('preview').set('value', '1'); $('digest_post').submit();">Предпросмотр рассылки</a>	
</div>
    
</form>