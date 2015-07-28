<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/bill.common.php");
$xajax->printJavascript('/xajax/');
?>

<div class="b-layout b-layout__page">
    <h1 class="b-page__title">Мои услуги</h1>
    <div class="b-page__ipad b-page__iphone"><?php include($_SERVER['DOCUMENT_ROOT'] . "/bill/tpl.head_menu.php"); ?></div>
    <div class="b-layout__one b-layout__one_width_25ps b-layout__one_padbot_30 b-layout__right_float_right b-layout__one_width_full_ipad b-layout_padbot_10_ipad">
       <?php include($_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.score.php"); ?>
    </div>

    <div id="services-list" class="b-layout__one b-layout__one_float_left b-layout__one_width_72ps b-layout__one_width_full_ipad">
        <div class="b-page__desktop"><?php include($_SERVER['DOCUMENT_ROOT'] . "/bill/tpl.head_menu.php"); ?></div>
        
        <?php if($_SESSION['send_success']) { unset($_SESSION['send_success']); $hide_form = true;?>
            <div class="b-fon b-fon_padbot_10" id='send_success'>
                <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
                    <span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span>Перевод денежных средств прошел успешно.<br/>
                    <a href="javascript:void(0)" class="b-buttons__link" onclick="$('send_success').destroy(); $('sended_form').removeClass('b-layout__txt_hide');">Перевести еще раз</a>
                </div>
            </div>
        <?php } ?>
        <table class="b-layout__table b-layout__table_width_full <?= $hide_form ? "b-layout__txt_hide" : ""?>" id='sended_form'>
            <tbody>
                <tr class="b-layout__tr">
                    <td class="b-layout__td">
                        <form action="/bill/send/" method="POST" id="send_money">
                            <input type="hidden" name="action" value="sended"/>
                            <input type="hidden" name="transaction_id" value="<?= $bill->account->start_transaction($bill->user['uid'], $_REQUEST['transaction_id']);?>"/>
                            <table class="b-layout__table b-layout__table_width_full ">
                                <tbody>
                                    <tr class="b-layout__tr">
                                        <td class="b-layout__td b-layout__td_padbot_20"><div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Сумма</div></td>
                                        <td class="b-layout__td b-layout__td_padleft_20 b-layout__td_padbot_20">
                                            <div class="b-combo b-combo_inline-block">
                                                <div class="b-combo__input b-combo__input_width_200 <?= $bill->error['sum'] ? "b-combo__input_error" : ""?>">
                                                    <input type="text" id="sum" name="sum" maxlength="9" class="b-combo__input-text" size="80" style="text-align:right" value="<?= stripcslashes($bill->post['sum'])?>"> 
                                                </div>
                                            </div>
                                            <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_4"> руб.</div>                        
                                        </td>
                                    </tr>
                                    <tr class="b-layout__tr">
                                        <td class="b-layout__td b-layout__td_padbot_20"><div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Логин</div></td>
                                        <td class="b-layout__td b-layout__td_padleft_20 b-layout__td_padbot_20">
                                            <div class="b-combo">
                                                <div class="b-combo__input b-combo__input_resize <?= $bill->error['login'] ? "b-combo__input_error" : ""?> b-combo__input_dropdown b-combo__input_width_200 b-combo__input_max-width_700 b-combo__input_arrow-user_yes b_combo__input_quantity_symbols_3  b_combo__input_request_id_getuserlist search_in_userlist <?= ($bill->post['uid'] > 0 ? "drop_down_default_" . $bill->post['uid'] : ""); ?>">
                                                    <input type="text" value="" size="80" name="login" class="b-combo__input-text" id="login" autocomplete="off">
                                                </div>
                                            </div>                        
                                        </td>
                                    </tr>
                                    <tr class="b-layout__tr">
                                        <td class="b-layout__td b-layout__td_padbot_20"><div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_3">Комментарий</div></td>
                                        <td class="b-layout__td b-layout__td_padleft_20 b-layout__td_padbot_20">
                                            <div class="b-textarea <?= $bill->error['address'] ? "b-textarea_error" : ""?>">
                                                <textarea rows="5" cols="80" id="comment" name="comment" class="b-textarea__textarea"><?=stripcslashes($bill->post['comment'])?></textarea>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </form>
                        <!--<div class="b-fon b-fon_bg_fff9bf b-fon_pad_10 b-fon_padleft_35">
                            <div class="b-layout__txt b-layout__txt_fontsize_11">Введите логин получателя и сумму, которую вы собираетесь перевести.</div>
                            <div class="b-layout__txt b-layout__txt_fontsize_11">Вы также можете добавить небольшой комментарий к переводу.</div>
                            <div class="b-layout__txt b-layout__txt_fontsize_11">Деньги будут переведены мгновенно и без комиссии..</div>
                        </div>  -->

                        <div class="b-buttons b-buttons_padtop_20">
                            <a href="javascript:void(0)" class="b-button  b-button_flat  b-button_flat_green" onclick="if(!$(this).hasClass('b-button_disabled')) { $(this).addClass('b-button_disabled'); $('send_money').submit(); }">Перевести</a>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="b-layout__one b-layout__one_width_25ps b-layout__one_float_left b-layout__one_margleft_3ps b-layout__one_width_full_ipad">
        <?php include($_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.right_column.php"); ?>
    </div>
    
</div>
<input type="hidden" name="tr_id" id="tr_id" value="" />
<script>
    var orders = new Services();
</script>