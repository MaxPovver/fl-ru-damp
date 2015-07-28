<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Verification.php");

switch($quick_verification_type) {
  case 'promo':
    $qver_title = 'Верификация';
    $qver_btn = 'Закрыть';
    break;
  case 'project':
    $qver_title = 'Верификация для ответа на проект';
    $qver_btn = 'Закрыть и ответить на проект';
    break;
}
$quser = new users();
$quser->GetUserByUID(get_uid(false));

$_yd_uri_auth = Verification::getYDUriAuth($quick_verification_type=='project'?$project['id']:null);

?>
<script type="text/javascript">
    var _YD_URI_AUTH = "<?=$_yd_uri_auth?>";
</script>

<div id="quick_ver_window" class=" b-shadow b-shadow_width_540 b-shadow_center b-shadow_pad_20 b-shadow_zindex_110 b-shadow_hide">
   <table class="b-layout__table b-layout__table_width_full">
      <tbody><tr class="b-layout__tr">
         <td class="b-layout__td b-layout__td_width_70 b-layout__td_padright_10">
             <span class="b-icon b-icon__ver-big b-icon__ver-big_empty"></span>
         </td>
         <td class="b-layout__td">
            <h2 class="b-shadow__title b-shadow__title_padbot_10"><?=$qver_title?></h2>

            <div id="quick_ver_big_1">

                <div class="b-layout__txt b-layout__txt_padbot_30">
                    Верификация — это подтверждение личности без передачи персональных данных. 
                    Укажите свои имя и фамилию и привяжите свой аккаунт в одной из платёжных систем.
                </div>

                <div id="quick_ver_main">

                    <div id="quick_ver_waiting_1" class="b-layout__wait b-layout_hide">
                        Ожидается завершение верификации через 
                        <span id="quick_ver_waiting_1_txt"></span><br/>
                        <img src="/images/load.gif" width="26" height="6">
                    </div>

                    <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padbot_30">
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_200">
                                <input id="quick_ver_f_fname" type="text"  size="21" value="<?= $_SESSION['quick_ver_fname'] ? $_SESSION['quick_ver_fname'] : $quser->uname ?>" maxlength="21" class="b-combo__input-text" placeholder="Введите имя" onkeydown="quickVerCheckFIO();" onkeyup="quickVerCheckFIO();" onkeypress="quickVerCheckLetterOnly(event);">
                            </div>
                        </div>
                        <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_5">Не более 21 символа
                            <div class="i-shadow i-shadow_inline-block">
                                <span class="b-shadow__icon b-shadow__icon_quest b-shadow__icon_margbot_-2" onclick="this.getNext().toggleClass('b-layout_hide')"></span>
                                <div class="b-shadow b-shadow_m b-shadow_width_200 b-shadow_pad_10 b-shadow_left_-110 b-shadow_top_15 b-layout_hide">
                                    <div class="b-layout__txt b-layout__txt_fontsize_11">Если ваше имя больше чем 21 символ попробуйте его сократить.</div>
                                    <span class="b-shadow__icon b-shadow__icon_nosik"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padbot_30">
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_200">
                                <input id="quick_ver_f_lname" type="text"  size="21" maxlength="21" value="<?= $_SESSION['quick_ver_lname'] ? $_SESSION['quick_ver_lname'] : $quser->usurname ?>" class="b-combo__input-text" placeholder="Введите фамилию" onkeydown="quickVerCheckFIO();" onkeyup="quickVerCheckFIO();" onkeypress="quickVerCheckLetterOnly(event);">
                            </div>
                        </div>
                        <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_5">Не более 21 символа
                            <div class="i-shadow i-shadow_inline-block">
                                <span class="b-shadow__icon b-shadow__icon_quest b-shadow__icon_margbot_-2" onclick="this.getNext().toggleClass('b-layout_hide')"></span>
                                <div class="b-shadow b-shadow_m b-shadow_width_200 b-shadow_pad_10 b-shadow_left_-110 b-shadow_top_15 b-layout_hide">
                                    <div class="b-layout__txt b-layout__txt_fontsize_11">Если ваша фамилия больше чем 21 символ попробуйте ее сократить.</div>
                                    <span class="b-shadow__icon b-shadow__icon_nosik"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="b-layout__txt b-layout__txt_padbot_20">
                        Выберите один из вариантов верификации:
                    </div>

                    <div id="quick_ver_error_1" class="b-fon b-fon_padbot_20 b-layout_hide">
                        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
                            <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-25"></span>
                            <span id="quick_ver_error_txt_1">
                                Произошла ошибка при верификации. Попробуйте ещё раз.
                            </span>
                        </div>
                    </div>

                    <div id="quick_ver_block_1">
                        <a class="b-button b-button__pay b-button__pay_wm b-button_width_126 b-button_disabled" href="#" onClick="quickVerStartWebmoney(1, this); return false;">Требуется WM-аттестат<br>не ниже начального</a>
                        <a class="b-button b-button__pay b-button__pay_card b-button_width_126 b-button_disabled" href="#" onClick="quickVerStartYandexKassaAC(this); return false;">Банковской картой<br>Visa или Mastercard<br>Баланс должен быть не менее 10 р.</a>
                        <a class="b-button b-button__pay b-button__pay_yd b-button_width_126 b-button_disabled" href="#" onClick="quickVerStartYandex(1, this); return false;">Требуется идентифици-<br>рованный кошелек</a>
                    </div>

                    <div id="quick_ver_block_2" style="display: none;">
                        <span class="b-button b-button__pay b-button__pay_padtop_15 b-button_width_196 b-button_disabled">
                            <div class="b-combo b-combo_padbot_15">
                                <div class="b-combo__input b-combo__wm">
                                    <input id="quick_ver_f_wmid" type="text" value="" size="12" name="" class="b-combo__input-text b-combo__input-text_color_67" maxlength="12" placeholder="Введите ваш WMID" onkeypress="quickVerCheckNumOnly(event);">
                                </div>
                            </div>
                            <a href="#" class="b-button b-button_flat b-button_flat_green underline" onClick="quickVerStartWebmoney(2, this); return false;">Проверить аттестат</a>
                        </span>
                        <a class="b-button b-button__pay b-button__pay_yd b-button_width_196 underline" href="#" onClick="quickVerStartYandex(1); return false;">Требуется идентифици-<br>рованный кошелек</a>
                    </div>

                </div> 

            </div>

            <?php if ($quick_verification_type == 'project'): ?>
            <div class="b-layout__txt b-layout__txt_padtop_20"><a class="b-layout__link underline" href="/">Посмотреть другие проекты</a>, отложив верификацию</div>
            <?php endif; ?>

         </td>
      </tr>
   </tbody></table>
   <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>


<div id="quick_ver_window_ok" class=" b-shadow b-shadow_width_540 b-shadow_center b-shadow_pad_20 b-shadow_zindex_110 <?= $_GET['vok'] ? '' : 'b-layout_hide' ?>">
   <table class="b-layout__table b-layout__table_width_full">
      <tbody><tr class="b-layout__tr">
         <td class="b-layout__td b-layout__td_width_70 b-layout__td_padright_10">
             <span class="b-icon b-icon__ver-big"></span>
         </td>
         <td class="b-layout__td">
             <h2 class="b-shadow__title b-shadow__title_padbot_10">Вы успешно верифицированы</h2>
              <div class="b-layout__txt b-layout__txt_padbot_20">
                <?php if (!is_emp()): ?>
                Теперь вы можете отвечать на все проекты, помеченные как «Только для верифицированных».
                <?php endif; ?>
                <?php if($_GET['vuse'] == 'card'): ?>
                <br/>
                Обратите внимание: для верификации с вашей карты было списано 10 рублей. 
                Эта сумма будет возвращена вам на карту в течение суток.
                <?php endif; ?>
              </div>
             <div class="b-buttons b-buttons_padtop_10">
             <a id="quick_ver_window_ok_btn" href="#new_offer" <?= $_GET['vok'] ? "onclick=\"$('quick_ver_window_ok').addClass('b-shadow_hide'); ".($quick_verification_type=="project" ? "window.location.hash = '#new_offer';" : '')."return false;\"" : ""?> class="b-button b-button_flat b-button_flat_green underline"><?=$qver_btn?></a>
             </div>
         </td>
      </tr>
   </tbody></table>
   <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>