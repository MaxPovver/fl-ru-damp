<?
if (!defined('IS_SITE_ADMIN') || !(hasPermissions('sbr') || hasPermissions('tmppayments')) ) {
    header_location_exit('/404.php');
}
?>

<h2 class="b-layout__title">Статистика по СБР</h2>

<form id="sbr_stat_form" method="get">

    <div class="b-ext-filter">
        <div class="b-ext-filter__inner">
            
            <div class="b-radio b-radio_layout_vertical">

                <div class="b-radio__item b-radio__item_padbot_10">
                    <input id="b-radio__input2" class="b-radio__input" name="period" type="radio" value="today" <?= $period_param === 'today' ? 'checked="checked"' : ''?> />
                    <label class="b-radio__label b-radio__label_fontsize_13" for="b-radio__input2">За сегодня</label>
                </div>

                <div class="b-radio__item b-radio__item_padbot_10">
                    <input id="b-radio__input3" class="b-radio__input" name="period" type="radio" value="week" <?= $period_param === 'week' ? 'checked="checked"' : ''?> />
                    <label class="b-radio__label b-radio__label_fontsize_13" for="b-radio__input3">За прошедшую неделю</label>
                </div>

                <div class="b-radio__item b-radio__item_padbot_10">
                    <input id="b-radio__input4" class="b-radio__input" name="period" type="radio" value="month" <?= $period_param === 'month' ? 'checked="checked"' : ''?> />
                    <label class="b-radio__label b-radio__label_fontsize_13" for="b-radio__input4">За прошедший месяц</label>
                </div>
                
                <div class="b-radio__item b-radio__item_padbot_10">
                    <input id="b-radio__input5" class="b-radio__input" name="period" type="radio" value="year" <?= $period_param === 'year' ? 'checked="checked"' : ''?> />
                    <label class="b-radio__label b-radio__label_fontsize_13" for="b-radio__input5">За прошедший год (статистика по месяцам)</label>
                </div>
                
                <div class="b-radio__item b-radio__item_padbot_10">
                    <input id="b-radio__input6" class="b-radio__input" name="period" type="radio" value="alltime" <?= $period_param === 'alltime' ? 'checked="checked"' : ''?> />
                    <label class="b-radio__label b-radio__label_fontsize_13" for="b-radio__input6">За все время (статистика по годам)</label>
                </div>

                <div class="b-radio__item b-radio__item_inline-block b-radio__item_padtop_6">
                    <input id="b-radio__input7" class="b-radio__input" name="period" type="radio" value="custom" <?= $period_param === 'custom' ? 'checked="checked"' : ''?> />
                    <label class="b-radio__label b-radio__label_fontsize_13" for="b-radio__input7">С</label>
                </div>&nbsp;&nbsp;

                <div class="b-combo b-combo_inline-block">
                    <div class="b-combo__input b-combo__input_width_140 b-combo__input_max-width_140 b-combo__input_arrow_yes b-combo__input_calendar b-combo__input_resize date_format_use_dot use_past_date no_set_date_on_load year_min_limit_1900">
                        <? $custom_period_from = $custom_period_from ? $custom_period_from : date("d.m.Y", time()) ?>
                        <input id="data1" class="b-combo__input-text" name="custom_period_from" type="text" size="80" value="<?= $custom_period_from ? $custom_period_from : (date("d.m.Y", time())) ?>" maxlength="10" />
                        <span class="b-combo__arrow-date"></span>
                    </div>          
                </div>

                <label class="b-form__name b-form__name_fontsize_13 b-form__name_padtop_6 b-form__name_padlr_5" for="b-radio__input7">по</label>

                <div class="b-combo b-combo_inline-block b-combo_margright_10">
                    <div class="b-combo__input b-combo__input_width_140 b-combo__input_max-width_140 b-combo__input_arrow_yes b-combo__input_calendar b-combo__input_resize date_format_use_dot use_past_date no_set_date_on_load year_min_limit_1900">
                        <? $custom_period_to = $custom_period_to ? $custom_period_to : date("d.m.Y", time()) ?>
                        <input id="data2" class="b-combo__input-text" name="custom_period_to" type="text" size="80" value="<?= $custom_period_to ?>" maxlength="10" />
                        <span class="b-combo__arrow-date"></span>
                    </div>          
                </div>
            </div>
            <div class="b-check b-check_padtop_10 b-check_inline-block">
                <input type="checkbox" id="b-check__input1" class="b-check__input" value="1" name="akkr" <?= $akkr_param || $akkr_param === null ? 'checked="checked"' : '' ?> />
                <label class="b-check__label b-check__label_fontsize_13" for="b-check__input1">аккредитив</label>
            </div>
            &nbsp;&nbsp;
            <div class="b-check b-check_padtop_10 b-check_inline-block">
                <input type="checkbox" id="b-check__input2" class="b-check__input" value="1" name="pdrd" <?= $pdrd_param || $pdrd_param === null ? 'checked="checked"' : '' ?> />
                <label class="b-check__label b-check__label_fontsize_13" for="b-check__input2">подряд</label>
            </div>
            

            <div class="b-buttons b-buttons_padtop_10 b-buttons_padbot_20">
                <a href="javascript:$('sbr_stat_form').submit()" class="b-button b-button_flat b-button_flat_grey">Применить</a>
            </div>
            <input type="hidden" name="show_results" value="1" />
            <input type="hidden" id="show_results_tab" name="tab" value="" />

        </div>
    </div>
    
</form>

<? if ($show_results) { ?>

<div class="b-menu b-menu_tabs  b-menu_padtop_20 b-menu_padbot_20">
    <ul class="b-menu__list b-menu__list_padleft_10">
        <li class="b-menu__item b-menu__item_last <?= $tab === 'graph' ? 'b-menu__item_active' : '' ?>">
            <a href="javascript:void(0)" id="tab_graph" class="b-menu__link">
                <span class="b-menu__b1">Графики</span>
            </a>
        </li>
        <li class="b-menu__item <?= $tab === 'table' ? 'b-menu__item_active' : '' ?>">
            <a href="javascript:void(0)" id="tab_table" class="b-menu__link">
                <span class="b-menu__b1">Таблица</span>
            </a>
        </li>
    </ul>
</div>

<?
include("inner_table.php");
include("inner_graph.php");
?>

<script type="text/javascript">
    window.addEvent('domready', function(){
        $('tab_graph').addEvent('click', switchToGraph);
        $('tab_table').addEvent('click', switchToTable);
        
        // переключает на вкладку ГРАФИКИ
        function switchToGraph () {
            $('show_results_tab').set('value', 'graph');
            $('tab_graph').getParent('li').addClass('b-menu__item_active');
            $('tab_table').getParent('li').removeClass('b-menu__item_active');
            $('sbr_stat_graph').setStyle('display', '');
            $('sbr_stat_table').setStyle('display', 'none');
        }
        
        // переключает на вкладку ТАБЛИЦА
        function switchToTable () {
            $('show_results_tab').set('value', 'table');
            $('tab_graph').getParent('li').removeClass('b-menu__item_active');
            $('tab_table').getParent('li').addClass('b-menu__item_active');
            $('sbr_stat_graph').setStyle('display', 'none');
            $('sbr_stat_table').setStyle('display', '');
        }
        
        // корректируем ширину внутренней таблицы (потомучто страница резиновая)
        var div = $('sbr_stat_table_data');
        var width = div.getParent().getSize().x;
        div.setStyle('width', width);
        
        // переключаем на нужную вкладку
        var tab = '<?= $tab ?>';
        tab === 'table' ? switchToTable() : switchToGraph();
    });
</script>

<? } ?>