<?php

/**
 * Старица покупки ПРО для новичков
 */


if($uid) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/professions.common.php");
    $xajax->printJavascript('/xajax/');
?>
<script type="text/javascript">
var account_sum = <?= round($account->sum, 2)?>;
var role = 'FRL';
</script>
<? } else { //if?>
<script type="text/javascript">
var alowLogin = function(){
    if($('login_inp').get('value') != '' && $('pass_inp').get('value') != ''){
        $('auth_form').submit();
    };
}
</script>
<? } //else?>
    <h1 id="header_payed_pro" class="b-page__title b-page__title_padbot_30 b-page__title_center b-page__title_padbot_10_ipad">
        Профессиональный аккаунт фрилансера
        <? if ($pro_last) { ?>
            <div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_center b-layout__txt_fontsize_20">
                Действует до <?= date('d.m.Y', strtotime($pro_last)) ?>
            </div>
        <? } ?>
    </h1>

    <? include_once("tpl.setting.pro.php"); ?>

    <table class="b-layout__table b-layout__table_center b-layout__table_margbot_60">
        <tr class="b-layout__tr">
            <td class="b-layout__td">
                <table class="b-layout__table b-layout__table_width_full">
                        <?php 
                            foreach(payed::getPayedPROList() as $pay) {
                            if($pay['is_test'] && $is_user_was_pro) continue;
                            if(get_uid(false)) {
                                $dcost = $pay['cost'] - round($account->sum,2);
                            }
                        ?>
                        <td class="b-layout__td b-layout__td_width_200 b-layout__td_padright_70" id="pro_payed_<?=$pay['opcode']?>">
                            <form action="/payed/buy.php" method="post" id="post">
                            <input type="hidden" name="mnth" value="1" />
                            <?/* <input type="hidden" name="transaction_id" value="<?= get_uid(false) ? $account->start_transaction($uid, $tr_id) : 0;?>" /> */?>
                            <input type="hidden" name="oppro" value="<?= $pay['opcode'] ?>" />
                            <input type="hidden" name="action" value="buy" />
                            
                            <?php if(false): ?>
                            <div class="b-promo__buy b-layout__txt_hide payed_success" id="payed_success_<?=$pay['opcode']?>">
                                <div class="b-promo__buy-head">Поздравляем</div>
                                <div class="b-promo__buy-body">
                                    <div class="b-layout__txt b-layout__txt_padtop_20">Вы купили аккаунт <span class="b-icon b-icon__pro b-icon__pro_f" title="PRO"></span></div>
                                    <div class="b-layout__txt b-layout__txt_padbot_20">на <?= $pay['week'] > 0 ? $pay['week'] . " " . ending($pay['week'], 'неделю', 'недели', 'недель') : ($pay['month'] == 12 ? '1 год' : $pay['month'] . " " . ending($pay['month'], 'месяц', 'месяца', 'месяцев') )?> (до <span class="payed_pro_last">20.05.2013</span>)</div>
                                    <div class="b-check b-check_center">
                                        <input id="autolong" class="b-check__input b-check__input_top_-3 auto_prolong" type="checkbox" value="1" name="prolong" <?= $u_is_pro_auto_prolong  == 't' ? 'checked="checked"' : ''?>><br />
                                        <label class="b-check__label b-check__label_color_41" for="autolong">Автоматически продлевать<br />действие аккаунта PRO<br />каждый месяц</label>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="b-promo__buy payed_form" id="payed_form_<?=$pay['opcode']?>">
                                <div class="b-promo__buy-head <?= $pay['is_test'] ? "b-promo__buy-head_test" : ""?>">
                                    PRO на 
                                    <? if($pay['day']) { ?>
                                        <?= $pay['day']?> <?= ending($pay['day'], 'день', 'дня', 'дня')?>
                                    <? } elseif ($pay['week']) { ?>
                                        <?= $pay['week']?> <?= ending($pay['week'], 'неделя', 'недели', 'недель')?>
                                    <? } else { ?>
                                        <? if($pay['month']==12) { ?>
                                            1 год
                                        <? } else { ?>
                                            <?= $pay['month']?> <?= ending($pay['month'], 'месяц', 'месяца', 'месяцев')?>
                                        <? } ?>
                                    <? } ?>
                                </div>
                                <div class="b-promo__buy-body b-promo__buy-body_height_160">
                                    <div class="b-layout b-layout_inline-block b-layout__txt_top_2"><img class="b-layout__pic" src="/images/62.png" width="73" height="50"></div> &#160;&#160;
                                    <div class="b-layout b-layout_inline-block">
                                        <div class="b-layout__txt b-layout__txt_color_fd6c30 b-layout__txt_fontsize_34 b-layout__txt_lineheight_1 b-layout__txt_padtop_30"><?= $pay['cost']?></div>
                                        <div class="b-layout__txt b-layout__txt_padbot_<?= $eco > 0 ? '10': '30';?>">рублей</div>
                                    </div>
                                    <div class="b-buttons">
                                        <a id="is_enough_<?= $pay['opcode']?>" class="b-button b-button_flat b-button_flat_green b-button_block<?php if (get_uid(false)): ?> __ga__pro__frl_buy<?php endif; ?>" href="javascript:void(0)" onclick="<?=  get_uid(false) ? "quickPRO_show(); $('quick_pro_f_item_".$pay['opcode']."').set('checked', 'true'); quickPRO_select($('quick_pro_f_item_".$pay['opcode']."'));" : "window.location = '/registration/?user_action=buypro';"?>">Купить</a>
                                    </div>
                                </div>
                            </div>
                            </form>
                        </td>
                       <?php }//foreach?>
                    </tr>
                </table>
            </td>
            <td class="b-layout__td b-layout__td_padtop_20">
                <div class="b-layout__txt  b-layout__txt_padbot_10"><span class="b-icon b-icon_sbr_gok b-icon_top_1"></span>Увеличение рейтинга на 20%.</div>
                <div class="b-layout__txt  b-layout__txt_padbot_10"><span class="b-icon b-icon_sbr_gok b-icon_top_1"></span>Возможность размещения в каталоге фрилансеров по 5 специализациям.</div>
                <div class="b-layout__txt  b-layout__txt_padbot_10"><span class="b-icon b-icon_sbr_gok b-icon_top_1"></span>Вы в каталоге &mdash; выше остальных &mdash; в отдельной зоне PRO.</div>
                <div class="b-layout__txt  b-layout__txt_padbot_10"><span class="b-icon b-icon_sbr_gok b-icon_top_1"></span>Ваши ответы на проекты &mdash; выше остальных &mdash; в зоне ответов PRO.</div>
                <div class="b-layout__txt  b-layout__txt_padbot_10"><span class="b-icon b-icon_sbr_gok b-icon_top_1"></span>Можете загружать работы в ответах на проекты.</div>
                <div class="b-layout__txt  b-layout__txt_padbot_10"><span class="b-icon b-icon_sbr_gok b-icon_top_1"></span>Превью работ в портфолио.</div>
                
               <div class="b-fon b-fon_pad_10 b-fon_bg_d3f2c0 b-fon__nosik_left">
                  Теперь можно приобрести аккаунт, оплатив его потом (через сервис <a class="b-layout__link" href="http://PlatiPotom.ru" target="_blank">PlatiPotom.ru</a>).<br>Вы станете PRO сразу, а оплатите его с отсрочкой до 30 дней.
               </div>
            </td>            
        </tr>
    </table>

<style type="text/css">
@media screen and (max-width: 1000px){
.b-layout__page .body .b-button.b-button_flat-size_medium { width:auto !important; padding: 0 14px !important;}
.b-layout__page .body .b-button.b-button_flat-size_medium.b-button_flat_green { display:block; width:100px !important;}
}
@media screen and (max-width: 700px){
.b-layout__page .body .b-button.b-button_flat-size_medium { width:200px !important;  float:none !important; margin:0 auto !important; display:block}
}
</style>

<? require_once($_SERVER['DOCUMENT_ROOT'] . "/templates/quick_buy_pro.php"); ?>