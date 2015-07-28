<?php
/**
 * Модерирование пользовательского контента. Заблокированные сущности. Шаблон.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
?>
<div id="my_div_all">

<?php
if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/user_content.common.php' );
$xajax->printJavascript( '/xajax/' );

$sTitle = '';

foreach ( $aContents as $key => $aOne ) {
    if ( $nCid == $key ) {
        $sTitle = ' / ' . $aOne['name'];
    }
}
?>

<h2 class="b-layout__title b-layout__title_padbot_15">Заблокированные <?=$sTitle?></h2>
	
<!-- Фильтр старт -->
<div class="b-ext-filter">
    <div class="b-ext-filter__inner">
        <div class="b-ext-filter__body">

            <div class="b-form b-layout">
                <table class="b-layout__table" border="0" cellpadding="0" cellspacing="0">
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_80">
                        <label class="b-form__name b-form__name_padtop_8">Логин</label>
                    </td>
                    <td class="b-layout__right b-layout__right_width_395">
                        <div class="b-input b-input_visibility_yes b-input_height_24">
                            <input id="login" class="b-input__text" type="text" name="login" value=""  />
                        </div>
                    </td>
                </tr>
                </table>
            </div>
            
            <div class="b-form b-layout b-form_padbot_20">
                <table class="b-layout__table" border="0" cellpadding="0" cellspacing="0">
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_80">&nbsp;
                		
                    </td>
                    <td class="b-layout__right b-layout__right_width_395">
                    	<div class="b-check">
                            <input id="login_ex" class="b-check__input" type="checkbox" value="1" name="login_ex" />
                            <label class="b-check__label b-check__label_fontsize_13" for="login_ex">точное совпадение</label>
                        </div>
                    </td>
                </tr>
                </table>
            </div>

            <div class="b-form b-layout b-form_padbot_20">
                <table class="b-layout__table" border="0" cellpadding="0" cellspacing="0">
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_80">
                		<label class="b-form__name b-form__name_padtop_8">Дата записи</label>
                    </td>
                    <td class="b-layout__right">
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_calendar b-combo__input_width_150 b-combo__input_max-width_180 b-combo__input_arrow-date_yes date_format_use_text use_past_date set_current_date_on_null year_min_limit_2008">
                                <input id="date_from" class="b-combo__input-text" name="date_from" type="text" size="80" value="" />
                                <label class="b-combo__label" for="date_from"></label>
                                <span class="b-combo__arrow-date"></span>
                            </div>
                        </div>
                        
                        <label class="b-form__name b-form__name_fontsize_13 b-form__name_padtop_4">&#160;&mdash;&#160;</label>
                        <div class="b-combo b-combo_inline-block ">
                            <div class="b-combo__input b-combo__input_calendar b-combo__input_width_150 b-combo__input_max-width_180 b-combo__input_arrow-date_yes date_format_use_text use_past_date set_current_date_on_null date_max_limit_<?=date('Y_m_d')?>">
                                <input id="date_to" class="b-combo__input-text" name="date_to" type="text" size="80" value="" />
                                <label class="b-combo__label" for="date_to"></label>
                                <span class="b-combo__arrow-date"></span>
                            </div>
                        </div>
                    </td>
                </tr>
                </table>
            </div>

            <div class="b-buttons b-buttons_padleft_78 b-buttons_padbot_15">
                <a id="my_filter" class="b-button b-button_flat b-button_flat_green"  href="javascript:void(0);">Найти документы</a>
                <span class="b-buttons__txt">&nbsp;</span>
                <a id="my_reset" class="b-buttons__link" href="javascript:void(0);">Очистить</a>
            </div>
            
            
            
        </div>
    </div>
</div>
<!-- Фильтр стоп -->

<div id="my_div_contents_wnd" style="height: 1800px; overflow: auto;">
    <div id="my_div_contents"></div>
</div>

<script type="text/javascript">
window.addEvent('domready', function() {
    user_content.currUid        = <?=$uid?>;
    user_content.contentID      = <?=$nCid?>;
    user_content.lastID         = '2147483647'; // pg max int as string!
    user_content.spinner        = new Spinner('my_div_all');
    user_content.scrollWindow   = 'my_div_contents_wnd';
    user_content.scrollContent  = 'my_div_contents';
    user_content.scrollFunction = 'getBlocked';
    $('my_filter').addEvent('click', function(){user_content.setBlockedFilter();});
    $('my_reset').addEvent('click', function(){user_content.clearBlockedFilter();});
});
</script>

</div>

<?php include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/adm_edit_overlay.php' ); ?>