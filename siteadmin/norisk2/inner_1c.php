<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
if(!$sbr) exit;
?>

<h2 class="b-layout__title b-layout__title_padbot_20">Данные СБР для 1С</h3>


<div class="b-form b-form_padbot_20">
    <div class="b-form__name b-form__name_width_70 b-form__name_padtop_7">Период</div><div
     class="b-combo b-combo_inline-block">
        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_125 b-combo__input_arrow-date_yes use_past_date <?= ($_GET['date_s']!=''?'':'no_set_date_on_load')?>">
            <input id="date_s" name="date_s" class="b-combo__input-text" type="text" size="80" value="<?=htmlspecialchars($_GET['date_s'])?>">
            <span class="b-combo__arrow-date"></span> 
        </div>
    </div><div
     class="b-layout__txt b-layout__txt_padtop_3 b-layout__txt_inline-block b-layout__txt_valign_top">&#160;—&#160;</div><div
     class="b-combo b-combo_inline-block">
        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_125 b-combo__input_arrow-date_yes use_past_date <?= ($_GET['date_e']!=''?'':'no_set_date_on_load')?>">
            <input id="date_e" name="date_e" class="b-combo__input-text" type="text" size="80" value="<?=htmlspecialchars($_GET['date_e'])?>">
            <span class="b-combo__arrow-date"></span> 
        </div>
    </div>&#160;<a
     class="b-button b-button_flat b-button_flat_grey" href="" onClick="window.location='?site=1c&action=export&date_s='+$('date_s_eng_format').get('value')+'&date_e='+$('date_e_eng_format').get('value'); return false;">Экспорт</a>
    
</div>

<? if($not_result_1c) { ?>
За данный период сделок не найдено!
<? } ?>

