<?php
require_once ($_SERVER['DOCUMENT_ROOT'] . "/xajax/countrys.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
$xajax->printJavascript('/xajax/');

$is_show_adv = (isset($action) && $action == 'search_advanced')?true:false;

if(!$filter) {
    $filter = $_SESSION['search_advanced'][$type];
}

?>
<div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_20 b-layout__txt_padtop_10">
    <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" id="search-advanced-button" href="javascript:void(0);">
        Расширенный поиск
    </a>
    <span id="search_reset_btn" class="b-layout <?php if(!$is_show_adv): ?>b-layout_hide<?php endif; ?>">
        (<a class="b-layout__link b-layout__link_dot_c10600 b-layout__link_fontsize_13"  href="javascript:void(0);">сбросить настройки</a>)
    </span>
</div>
<div id="advanced-search" class="b-fon b-fon_bg_fa b-fon_pad_15 b-fon__as b-fon_margbot_20" style="display:<?= $is_show_adv?"block":"none";?>;">
   
   <table class="b-layout__table b-layout__table_width_full">
      <tr class="b-layout__tr">
         <td class="b-layout__td b-layout__td_padbot_30_ipad">
            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10">Специализация исполнителя</div>
            <div class="b-combo b-combo_padbot_30">
              <div class="
                   b-combo__input 
                   b-combo__input_height_35 
                   b-combo__input_width_370 
                   b-combo__input_width_280_r1130 
                   b-combo__input_width_350_ipad 
                   b-combo__input_multi_dropdown 
                   b-combo__input_arrow_yes 
                   b-combo__input_init_professionsList 
                   show_all_records 
                   sort_cnt 
                   exclude_value_0_0
                   <?php if(@$prof_id > 0): ?>
                   drop_down_default_<?=$prof_id?>
                   <?php elseif(@$prof_group_id > 0): ?>
                   drop_down_default_<?=$prof_group_id?>
                   <?php endif; ?>
                   ">
                 <input type="hidden" name="profession_columns[0]" value="<?=@$prof_group_id?>" />
                 <input type="hidden" name="profession_columns[1]" value="<?=@$prof_id ?>" />
                 <input id="profession" 
                        type="text" 
                        placeholder="Выберите специализацию" 
                        value="<?=(@$cur_prof['groupname'])?$cur_prof['groupname'].((@$cur_prof['profname'])?': ' . $cur_prof['profname']:''):@$prof_name?>" 
                        name="profession" 
                        class="b-combo__input-text" />
                 <span class="b-combo__arrow"></span>
              </div>
            </div>
            

            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10">Место жительства</div>
            
            <div class="b-combo b-combo_inline-block">
               <div class="
                    b-combo__input 
                    b-combo__input_height_35 
                    b-combo__input_arrow_yes  
                    b-combo__input_width_370 
                    b-combo__input_width_280_r1130 
                    b-combo__input_width_350_ipad 
                    b-combo__input_multi_dropdown 
                    b-combo__input_init_citiesList 
                    b-combo__input_on_click_request_id_getcities 
                    override_value_id_0_0_Все+страны 
                    override_value_id_1_0_Все+города 
                    <?php if(@$filter['city'] > 0): ?>
                    drop_down_default_<?=$filter['city']?>
                    <?php elseif(@$filter['country'] > 0): ?>
                    drop_down_default_<?=$filter['country']?>
                    <?php endif; ?>
                    ">
                  <input type="hidden" name="location_columns[0]" value="<?=@$filter['country']?>">
                  <input type="hidden" name="location_columns[1]" value="<?=@$filter['city']?>">                   
                  <input id="location"
                         class="b-combo__input-text" 
                         name="location" 
                         type="text" 
                         size="80" 
                         value="<?=@$countryCityName?>" 
                         placeholder="Любое место жительства"
                         autocomplete="off"/>
                  <span class="b-combo__arrow"></span> 
               </div>
            </div>
               
         </td>
         <td class="b-layout__td b-layout__td_width_355 b-layout__td_padright_40 b-layout__td_pad_null_r1200">
            <div class="b-layout b-layout_float_right">
               <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10">Возраст (в годах)</div>
               <div class="b-combo b-combo_padbot_30 b-combo_inline-block b-combo_valign_bas">
                  <div class="b-combo__input b-combo__input_height_35 b-combo__input_width_70">
                     <input class="b-combo__input-text" type="text" value="<?= $filter['age_from'] == 0 ? '' : $filter['age_from']?>" name="age[]" maxlength="3" size="10" placeholder="от" />
                  </div>
               </div>
               &mdash;
               <div class="b-combo b-combo_padbot_30 b-combo_inline-block b-combo_valign_bas">
                  <div class="b-combo__input b-combo__input_height_35 b-combo__input_width_70">
                     <input class="b-combo__input-text" type="text" value="<?= $filter['age_to']   == 0 ? '' : $filter['age_to']?>" name="age[]" maxlength="3" size="10" placeholder="до" />
                  </div>
               </div>
               &#8201;
            </div>
            <div class="b-layout">
               <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10">Опыт работы (в годах)</div>
               <div class="b-combo b-combo_padbot_30 b-combo_inline-block b-combo_valign_bas">
                  <div class="b-combo__input b-combo__input_height_35 b-combo__input_width_70">
                     <input class="b-combo__input-text"  type="text" maxlength="3" value="<?= $filter['exp_from'] == 0 ? '' : $filter['exp_from']?>" name="exp[]" size="10" placeholder="от" />
                  </div>
               </div>
               &mdash;
               <div class="b-combo b-combo_padbot_30 b-combo_inline-block b-combo_valign_bas">
                  <div class="b-combo__input b-combo__input_height_35 b-combo__input_width_70">
                     <input class="b-combo__input-text"  type="text" maxlength="3" value="<?= $filter['exp_to']   == 0 ? '' : $filter['exp_to']?>" name="exp[]" size="10" placeholder="до" />
                  </div>
               </div>
            </div>
            
            <div class="b-layout">
               <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10">Стоимость работы</div>
               <div class="b-combo b-combo_inline-block b-combo_valign_bas">
                  <div class="b-combo__input b-combo__input_height_35 b-combo__input_width_70">
                     <input class="b-combo__input-text" name="from_cost" type="text" size="80" value="<?=(@$filter['from_cost']>0)?$filter['from_cost']:''?>" placeholder="от" />
                  </div>
               </div>
               &mdash;
               <div class="b-combo b-combo_inline-block b-combo_valign_bas">
                  <div class="b-combo__input b-combo__input_height_35 b-combo__input_width_70">
                     <input class="b-combo__input-text" name="to_cost" type="text" size="80" value="<?=(@$filter['to_cost']>0)?$filter['to_cost']:''?>" placeholder="до" />
                  </div>
               </div>
               <script type="text/javascript">var currencyList = {0:"USD", 1:"Евро", 2:"Руб"};</script>
               <div class="b-combo b-combo_inline-block">
                  <div class="
                       b-combo__input 
                       b-combo__input_height_35 
                       b-combo__input_arrow_yes 
                       b-combo__input_width_80
                       b-combo__input_multi_dropdown
                       b-combo__input_init_currencyList
                       reverse_list
                       drop_down_default_<?=((isset($filter['curr_type']) && @$filter['curr_type'] >= 0)?$filter['curr_type']:2)?>
                       ">
                     <input id="curr_type" class="b-combo__input-text" readonly="readonly" name="curr_type" type="text" size="80" value="" />
                     <span class="b-combo__arrow"></span> 
                  </div>
               </div>
               <script type="text/javascript">var pricebyList = {1:"За месяц", 2:"За 1000 знаков", 3:"За проект", 4:"За час"};</script>
               <div class="b-combo b-combo_inline-block">
                  <div class="
                       b-combo__input 
                       b-combo__input_height_35 
                       b-combo__input_arrow_yes 
                       b-combo__input_width_100 
                       b-combo__input_multi_dropdown 
                       b-combo__input_init_pricebyList
                       drop_down_default_<?=((@$filter['cost_type'] > 0)?$filter['cost_type']:1)?>
                       ">
                     <input id="cost_type" class="b-combo__input-text" readonly="readonly" name="cost_type" type="text" size="80" value="" />
                     <span class="b-combo__arrow"></span> 
                  </div>
               </div>
            </div>
         </td>
      </tr>
      <tr class="b-layout__tr">
         <td class="b-layout__td">
            <div class="b-check b-check_padbot_20 b-check_padtop_10 b-check_padbot_10_ipad" >
               <table class="b-layout__table b-layout__table_width_full b-layout__table_ipad">
                  <tr class="b-layout__tr">
                     <td class="b-layout__td b-layout__td_width_20 b-layout__td_ipad b-layout__td_width_null_ipad b-layout__td_padright_5_ipad">
                        <input id="in_office" type="checkbox" value="1" name="in_office" class="b-check__input" <?= ($filter['in_office'] ? 'checked="checked"' : '');?> />
                     </td>
                     <td class="b-layout__td b-layout__td_padright_20 b-layout__td_ipad"><label for="in_office" class="b-check__label b-check__label_fontsize_13">Готов на регулярную работу или работу в офисе</label></td>
                  </tr>
               </table>
            </div>
            <div class="b-check b-check_padbot_10">
               <input id="is_pro" class="b-check__input" type="checkbox" name="is_pro" value="1" <?=($filter['is_pro']=="t"?'checked="checked"':'')?>/> 
               <label for="is_pro" class="b-check__label b-check__label_fontsize_13">С аккаунтом <a href="/profi/" target="_blank" class="b-layout__link"><span class="b-icon b-icon__lprofi b-icon_top_1" data-profi-txt="Лучшие фрилансеры сайта FL.ru. Работают на сайте более 2-х лет, прошли верификацию личности и имеют не менее 98% положительных отзывов."></span></a> или <?= view_pro('', false, true, 'платным аккаунтом')?> </label>
            </div>
            <div class="b-check b-check_padbot_10">
                <input id="is_preview" class="b-check__input" type="checkbox" name="is_preview" value="1" <?=   ($filter['is_preview']   ? 'checked="checked"' : '')?> /> 
                <label for="is_preview" class="b-check__label b-check__label_fontsize_13">С примерами работ в портфолио</label>
            </div>
            <?php if(@$_SESSION['uid']): ?>
                 <div class="b-check">
                     <input id="in_fav" class="b-check__input" type="checkbox" name="in_fav" value="1" <?= ($filter['in_fav'] ? 'checked="checked"' : '') ?> /> 
                     <label for="in_fav" class="b-check__label b-check__label_fontsize_13">У меня в избранных</label>
                 </div>
		     <?php endif; ?>
         </td>
         <td class="b-layout__td b-layout__td_width_355 b-layout__td_padright_40 b-layout__td_pad_null_r1200">
             <div class="b-check b-check_padbot_20 b-check_padtop_10 b-check_padbot_10_ipad">
                 <input id="only_free" class="b-check__input" type="checkbox" name="only_free" value="1" <?= ($filter['only_free'] ? 'checked="checked"' : '')?> /> 
                 <label for="only_free" class="b-check__label b-check__label_fontsize_13">Свободен в данный момент</label>
             </div>
             <div class="b-check b-check_padbot_10">
                <input id="is_verify" class="b-check__input" type="checkbox" name="is_verify" value="1" <?= ($filter['is_verify'] ?'checked="checked"':'')?> /> 
                <label for="is_verify" class="b-check__label b-check__label_fontsize_13">С верификацией <span class="b-icon b-icon__ver" title="верифицированым" alt="верифицированым"></span></label>
             </div>
             <div class="b-check b-check_padbot_10">
                 <input id="sbr_is_positive" class="b-check__input" type="checkbox" name="sbr_is_positive" value="1" <?=($filter['sbr_is_positive']?'checked="checked"':'')?>/> 
                 <label for="sbr_is_positive" class="b-check__label b-check__label_fontsize_13">С отзывами</label>
             </div>
             <div class="b-check">
                 <input id="sbr_not_negative" class="b-check__input" type="checkbox" name="sbr_not_negative" value="1" <?=($filter['sbr_not_negative']?'checked="checked"':'')?>/> 
                 <label for="sbr_not_negative" class="b-check__label b-check__label_fontsize_13">Без отрицательных отзывов</label>
             </div>
         </td>
      </tr>
   </table>
</div>  