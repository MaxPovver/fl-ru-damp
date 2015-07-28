<?php 
include_once("act.portfolio.php"); // Файл для обработки логики

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/users.common.php");
$xajax->printJavascript('/xajax/');
?>

<form action="." method="post" name="frm_serv" id="frm_serv" onSubmit="if(tawlFormValidation(this)){this.btn.value='Подождите'; this.btn.disabled=true;}else{return false;}">
  <div class="b-layout b-layout_padtop_20">
    <input type="hidden" name="action" value="serv_change" />
    <input type="hidden" name="prjid" value="" />
    
    <?php if ($error_serv) { ?>
    <div class="b-fon b-fon_width_full b-fon_padbot_17">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_35 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
            <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-25"></span><?= $error_serv; ?>
        </div>
    </div>
    <?php } ?>
    <?php if ($info_serv) { ?>
    <div class="b-fon b-fon_width_full b-fon_padbot_17">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_35 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf b-fon__body_bordbot_dfedcf">
            <span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span><?= $info_serv; ?>
        </div>
    </div>
    <?php } ?>
    <div class="b-layout__txt b-layout__txt_float_right b-page__desktop"><img src="/images/ico_setup.gif" alt="" width="6" height="9" />&nbsp;&nbsp;<a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" href="/users/<?= $user->login ?>/setup/portfsetup/">Изменить разделы</a></div>
    
    <div class="b-select b-select_inline-block b-select_padbot_20">
        <label class="b-select__label" for="b-select__select">Выберите название закладки:</label>
        <select id="tab_name_id" class="b-select__select  b-select__select_inline-block b-select__select_width_110" name="tab_name_id">
          <option value="0"<? if ($frm_serv_val['tab_name_id'] == 0) { ?> selected='selected'<? } ?>>Портфолио</option>
          <option value="1"<? if ($frm_serv_val['tab_name_id'] == 1) { ?> selected='selected'<? } ?>>Услуги</option>
        </select>
    </div>
    <div class="b-layout__txt b-layout__txt_padbot_20 b-page__ipad b-page__iphone"><img src="/images/ico_setup.gif" alt="" width="6" height="9" />&nbsp;&nbsp;<a class="b-layout__link b-layout__link_fontsize_11" href="/users/<?= $user->login ?>/setup/portfsetup/">Изменить разделы</a></div>
    <div class="b-layout__txt b-layout__txt_padbot_15 b-layout__txt_fontsize_11"><a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" href="/users/<?= $user->login ?>/setup/specsetup/" id="ap11">Специализация</a>:&nbsp;&nbsp;<?= professions::GetProfNameWP($user->spec, ' / ', 'Нет специализации') ?></div>
    <? if(!is_pro()) { ?>
    <div class="b-layout__txt b-layout__txt_padbot_15 b-layout__txt_fontsize_11">Чтобы увеличить количество специализаций и получить дополнительные возможности, рекомендуем приобрести аккаунт <?= view_pro(false, false, true, 'владельцев платного аккаунта')?></div>
    <? } else { ?>
    <div class="b-layout__txt b-layout__txt_padbot_15 b-layout__txt_fontsize_11"><a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" href="/users/<?= $user->login ?>/setup/specaddsetup/" id="ap11">Дополнительные специализации</a>:&nbsp;&nbsp;<?= $specs_add_string ?></div>
    <? } ?>
  <div class="b-check b-check_padbot_20">
     <table class="b-layout__table b-layout__table_width_full">
        <tr class="b-layout__tr">
           <td class="b-layout__td b-layout__td_width_null_ipad b-layout__td_ipad"><input name="cat_show" class="b-check__input" type="checkbox" value="1" <?= !is_pro() ? 'disabled="disabled"' : '' ?> <?= $user->cat_show == 't' || !is_pro() ? 'checked="checked"' : '' ?> id="cat_showl" /></td>
           <td class="b-layout__td b-layout__td_ipad b-layout__td_width_full b-layout__td_width_full_ipad">
              <label class="b-check__label b-check__label_color_71" for="cat_showl">&#160;<strong class="b-layout__txt_bold">Разрешить размещение в каталоге</strong><span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_71 b-layout__txt_lineheight_1 b-layout_block_iphone">&#160;(только для <span title="владельцев платного аккаунта" class="b-icon b-icon__pro b-icon__pro_f b-icon_valign_bas"></span>)</span></label>
           </td>
        </tr>
     </table>         
  </div>
   
   
  <table class="b-layout__table b-layout__table_width_full">
     <tr class="b-layout__tr">
        <td class="b-layout__td b-layout__td_width_240 b-layout__td_padbot_10">
           <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_2">Опыт работы (в годах)</div>
        </td>
        <td class="b-layout__td b-layout__td_width_140 b-layout__td_padbot_10 b-layout__td_right b-layout__td_left_ipad b-layout__td_padright_35">
           <div class="b-input b-input_width_60 b-input_inline-block">
             <input class="b-input__text" type="text" name="exp" value="<?= $frm_serv_val['exp'] ?>" maxlength="2" />
           </div>
        </td>
        <td class="b-layout__td b-layout__td_padbot_10"></td>
     </tr>
     <tr class="b-layout__tr">
        <td class="b-layout__td b-layout__td_width_240 b-layout__td_padbot_10">
           <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_2">Укажите стоимость часа вашей работы</div>
        </td>
        <td class="b-layout__td b-layout__td_width_140 b-layout__td_padbot_10 b-layout__td_right b-layout__td_left_ipad b-layout__td_padright_35">
           <div class="b-select  b-select_margright_5 b-select_inline-block">
              <select  class="b-select__select b-select__select_width_50" name="cost_type_hour" id="cost_type_hour">
                  <option value="0" <?= ($frm_serv_val['cost_type_hour'] == 0 ? "selected='selected'" : "") ?> >USD</option>
                  <option value="1" <?= ($frm_serv_val['cost_type_hour'] == 1 ? "selected='selected'" : "") ?>>Euro</option>
                  <option value="2" <?= ($frm_serv_val['cost_type_hour'] == 2 ? "selected='selected'" : "") ?>>Руб</option>
              </select>
           </div>
           <div class="b-input b-input_width_60 b-input_inline-block">
            <input class="b-input__text" type="text" id="cost_hour" name="cost_hour" value="<?= floatval($frm_serv_val['cost_hour']) ?>" maxlength="6" />
           </div>
        </td>
        <td class="b-layout__td b-layout__td_padbot_10">
           <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_2"> &mdash; данные цены будут выводиться на вашей странице и в общем каталоге</div>
        </td>
     </tr>
     <tr class="b-layout__tr">
        <td class="b-layout__td b-layout__td_width_240 b-layout__td_padbot_10">
           <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_2">Укажите стоимость месяца вашей работы</div>
        </td>
        <td class="b-layout__td b-layout__td_width_140 b-layout__td_padbot_10 b-layout__td_right b-layout__td_left_ipad b-layout__td_padright_35">
           <div class="b-select  b-select_margright_5 b-select_inline-block">
              <select class="b-select__select b-select__select_width_50" name="cost_type_month" id="cost_type_month">
                  <option value="0" <?= ($frm_serv_val['cost_type_month'] == 0 ? "selected='selected'" : "") ?> >USD</option>
                  <option value="1" <?= ($frm_serv_val['cost_type_month'] == 1 ? "selected='selected'" : "") ?>>Euro</option>
                  <option value="2" <?= ($frm_serv_val['cost_type_month'] == 2 ? "selected='selected'" : "") ?>>Руб</option>
              </select>
           </div>
           <div class="b-input b-input_width_60 b-input_inline-block">
              <input class="b-input__text" type="text" id="cost_month" name="cost_month" value="<?= floatval($frm_serv_val['cost_month']); ?>" maxlength="6" />
           </div>
        </td>
        <td class="b-layout__td b-layout__td_padbot_10"></td>
     </tr>
  </table>         
    <div class="b-check b-check_padbot_30">
        <input type="checkbox" id="in_officel" name="in_office" value="1" <?= $frm_serv_val['in_office'] == "t" ? " checked='checked'" : "" ?>  class="b-check__input" />
        <label class="b-check__label b-check__label_bold b-check__label_color_71" for="in_officel">
            Ищу долгосрочную работу <span style="display:inline-block; vertical-align: baseline; line-height:1; padding: 0 0 0 15px; background: url(/images/icons-sprite.png) no-repeat -100px -337px;">в офисе</span>
        </label>
    </div>
    
    
    <div class="b-layout__txt b-layout__txt_padbot_5">Уточнения к услугам в портфолио:</div>
    <div class="b-textarea">
        <textarea class="b-textarea__textarea" rel="<?= $ab_text_max_length ?>" cols="60" rows="7" id="ab_text" name="ab_text"><?= input_ref($frm_serv_val['text']) ?></textarea>
    </div>
    <div class="b-buttons b-buttons_padtop_10">
       <button id="btn" class="b-button b-button_flat b-button_flat_green" name="btn" type="submit">Изменить</button>
    </div>
  </div>
</form>
