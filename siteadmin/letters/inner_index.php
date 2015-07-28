<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/letters.common.php' );
$xajax->printJavascript( '/xajax/' );
?>

<style>
.spinner {
	position: absolute;
	opacity: 0.9;
	filter: alpha(opacity=90);
	-ms-filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=90);
	z-index: 999;
}
.spinner-msg {
	text-align: center;
	font-weight: bold;
}

.spinner-img {
	background: url(/images/load-line.gif) no-repeat;
	width: 128px;
	height: 15px;
	margin: 0 auto;
}
</style>


<script type="text/javascript">
var deliveryList = {
					<?php
					$deliveries = letters::getDeliveries();
					$html = '';
					$html .= "0:'Не выбрано',";
					foreach($deliveries as $delivery) {
						$html .= "{$delivery['id']}:'{$delivery['title']}',";
					}
					$html = preg_replace("/,$/", "", $html);
					echo $html;
					?>
					};
</script>

<div id="letters_form_start"></div>
<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/siteadmin/letters/tpl.forms.php' );
?>


	<div id="letters_add_div" class="i-shadow">
	<?php require_once($_SERVER['DOCUMENT_ROOT'].'/siteadmin/letters/tpl.add.doc.php'); ?>
	</div>

<a name="letters_mass_action_status_div_a"></a>
<div id="letters_mass_action_status_div" class="i-shadow" style="display: none; z-index: 50;">								
	<div class="b-shadow" style="left: 200px; top: 100px;">
		<div class="b-shadow__right">
			<div class="b-shadow__left">
				<div class="b-shadow__top">
					<div class="b-shadow__bottom">
						<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
							<form id="letters_mass_action_status_div_frm">
							<input id="letters_mass_action_status_div_fld_ids" name="letters_mass_action_status_div_fld_ids" type="hidden" value="">
							<div id="letters_mass_action_status_div_data">
								
							</div>
							</form>
							<div class="b-buttons b-buttons_padtop_10">
								<a class="b-button b-button_flat b-button_flat_green" href="javascript:void()" onClick="letters.massUpdateStatus(); return false;">Выбрать</a>
								<span class="b-buttons__txt b-buttons__txt_padleft_10">или&nbsp;</span>
								<a class="b-buttons__link b-buttons__link_dot_c10601" href="#" onClick="letters.massDivHideStatus(); return false;">закрыть, не выбирая</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="b-shadow__tl"></div>
		<div class="b-shadow__tr"></div>
		<div class="b-shadow__bl"></div>
		<div class="b-shadow__br"></div>
	</div>
</div>

<a name="letters_form_mass_deliverycost_a"></a>
<div id="letters_form_mass_deliverycost" class="i-shadow" style="display: none; z-index: 50;">									
	<div class="b-shadow b-shadow_width_335 b-shadow_top_-5 b-shadow_left_-140" style="left: 200px; top: 100px;">
		<div class="b-shadow__right">
			<div class="b-shadow__left">
				<div class="b-shadow__top">
					<div class="b-shadow__bottom">
						<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
							<input id="letters_mass_action_cost_div_fld_ids" name="letters_mass_action_cost_div_fld_ids" type="hidden" value="">
							<div class="b-combo">
								<div class="b-combo__input">
									<input id="letters_form_mass_deliverycost_field_data" name="letters_form_mass_deliverycost_field_data" class="b-combo__input-text" type="text" size="80" value="" maxlength="10">
								</div>
							</div>
							<div class="b-buttons b-buttons_padtop_15">
								<a class="b-button b-button_flat b-button_flat_green" href="javascript:void()" onClick="letters.massUpdateDeliveryCost(); return false;">Сохранить</a>
								<span class="b-buttons__txt b-buttons__txt_padleft_10">или&nbsp;</span>
								<a class="b-buttons__link b-buttons__link_dot_c10601" href="#" onClick="letters.formMassDeliveryCostHide(); return false;">закрыть, не сохраняя</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="b-shadow__tl"></div>
		<div class="b-shadow__tr"></div>
		<div class="b-shadow__bl"></div>
		<div class="b-shadow__br"></div>
	</div>
</div>


<a name="letters_form_mass_date_a"></a>
<div id="letters_form_mass_date" class="i-shadow" style="display: none; z-index: 50;">									
	<div class="b-shadow b-shadow_width_335 b-shadow_top_-5 b-shadow_left_-140" style="left: 200px; top: 100px;">
		<div class="b-shadow__right">
			<div class="b-shadow__left">
				<div class="b-shadow__top">
					<div class="b-shadow__bottom">
						<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
							<input id="letters_mass_action_date_div_fld_ids" name="letters_mass_action_date_div_fld_ids" type="hidden" value="">

									<div class="b-combo">
										<div class="b-combo__input b-combo__input_calendar b-combo__input_width_125 b-combo__input_arrow-date_yes use_past_date date_format_use_text no_set_date_on_load">
											<input id="letters_form_mass_date_field_data" name="letters_form_mass_date_field_data" class="b-combo__input-text" type="text" size="80" value="">
											<span class="b-combo__arrow-date"></span> 
										</div>
									</div>

							<div class="b-buttons b-buttons_padtop_15">
								<a class="b-button b-button_flat b-button_flat_green" href="javascript:void()" onClick="letters.massUpdateDate(); return false;">Сохранить</a>
								<span class="b-buttons__txt b-buttons__txt_padleft_10">или&nbsp;</span>
								<a class="b-buttons__link b-buttons__link_dot_c10601" href="#" onClick="letters.formMassDateHide(); return false;">закрыть, не сохраняя</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="b-shadow__tl"></div>
		<div class="b-shadow__tr"></div>
		<div class="b-shadow__bl"></div>
		<div class="b-shadow__br"></div>
	</div>
</div>

<div id="letters_wrapper_view" class="b-layout" style="display: none;"></div>


<div id="letters_wrapper" class="b-layout">

	<a href="#" class="b-button b-button_flat b-button_flat_green b-button_float_right" onClick="letters.showAddForm(); return false;">Добавить документ</a>
	
	<h2 id="letters_h_list" class="b-layout__title b-layout__title_padbot_20">Документы</h2>

	<div id="letters_h_list_group" class="b-layout__txt"><a href="/siteadmin/letters/?page=tab&tab=1" class="b-layout__link">Документы</a> &rarr;</div>	
	<div id="letters_h_list_user" class="b-layout__txt"><a href="/siteadmin/letters/?page=tab&tab=1" class="b-layout__link">Документы</a> &rarr;</div>	
	<h2 id="letters_h_list_title1" class="b-layout__title"></h2>
	<div id="letters_h_list_title2" class="b-layout__txt b-layout__txt_padbot_20"></div>


	<div class="b-menu b-menu_tabs b-menu_padbot_1">
		<ul id="tabs" class="b-menu__list b-menu__list_padleft_10">
			<li class="b-menu__item b-menu__item_active"><span class="b-menu__b2"><span class="b-menu__b1">Все</span></span></li>
			<li class="b-menu__item" style="display: none;"><a href="/siteadmin/letters/?page=tab&tab=1" class="b-menu__link"><span class="b-menu__b1">Все</span></a></li>

			<li class="b-menu__item b-menu__item_active" style="display: none;"><span class="b-menu__b2"><span class="b-menu__b1">Исходящие</span></span></li>
			<li class="b-menu__item"><a href="/siteadmin/letters/?page=tab&tab=2" class="b-menu__link"><span class="b-menu__b1">Исходящие</span></a></li>

			<li class="b-menu__item b-menu__item_active" style="display: none;"><span class="b-menu__b2"><span class="b-menu__b1">Входящие</span></span></li>
			<li class="b-menu__item"><a href="/siteadmin/letters/?page=tab&tab=3" class="b-menu__link"><span class="b-menu__b1">Входящие</span></a></li>

			<li class="b-menu__item b-menu__item_active" style="display: none;"><span class="b-menu__b2"><span class="b-menu__b1">В обработке</span></span></li>
			<li class="b-menu__item"><a href="/siteadmin/letters/?page=tab&tab=4" class="b-menu__link"><span class="b-menu__b1">В обработке</span></a></li>

			<li class="b-menu__item b-menu__item_active" style="display: none;"><span class="b-menu__b2"><span class="b-menu__b1">Архив</span></span></li>
			<li class="b-menu__item b-menu__item_last"><a href="/siteadmin/letters/?page=tab&tab=5" class="b-menu__link"><span class="b-menu__b1">Архив</span></a></li>

			<li class="b-menu__item b-menu__item_active" style="display: none;"><span class="b-menu__b2"><span class="b-menu__b1">Печать</span></span></li>
			<li class="b-menu__item b-menu__item_last"><a href="/siteadmin/letters/?page=tab&tab=6" class="b-menu__link"><span class="b-menu__b1">Печать</span></a></li>
		</ul>
	</div>

    <a name="is_top"></a>
    <div id="b-ext-filter" class="b-ext-filter">
		<div class="b-ext-filter__inner b-ext-filter__inner_padtb_3">
			<div class="b-search b-search_padbot_3">
				<form id="letters_search_frm" onsubmit="letters.search(); return false;">
				<table class="b-search__table b-search__table_width_full" cellpadding="0" cellspacing="0" border="0">
					<tbody>
						<tr class="b-search__tr">
							<td class="b-search__one b-search__one_width_60 b-search__one_padright_10" style="vertical-align: middle;">
								<label class="b-search__label b-search__label_nowrap">Поиск док.</label>
							</td>
							<td class="b-search__input b-search__input_width_350">


									<div class="b-combo b-combo_inline-block">
										<div class="b-combo__input b-combo__input_width_400 b-combo__input_max-width_440 b-input_height_24 b-combo__input_dropdown b_combo__input_request_id_getletterdocsearch b-combo__input_quantity_symbols_1 b-combo__input_overflow_hidden allow_create_value">
											<input graytext="Название группы, документа или ID" id="letters_search_frm_field" name="letters_search_frm_field" type="text" value="" size="80" class="b-combo__input-text" first_section_text="&nbsp;" second_section_text="&nbsp;" onChange="window.location = '/siteadmin/letters/?page=doc&doc='+$('letters_search_frm_field_db_id').get('value');">
											<span class="b-combo__arrow"></span>
										</div>
									</div>
							</td>
							<td class="b-search__button b-search__button_width_70 b-search__button_padleft_5">
								<a class="b-button b-button_flat b-button_flat_grey" href="" onClick="letters.search(); return false;">Найти</a>
							</td>
							<td class="b-search__one b-search__one_padleft_15" style="vertical-align: middle;">
								<a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-search__ext-link" href="javascript:void(0)">Расширенный поиск</a><span class="b-ext-filter__toggler b-ext-filter__toggler_down"></span>
							</td>
							<td class="b-search__one b-search__one_padleft_15" style="vertical-align: middle;">
                                <select name="dcount" onchange="letters.changeNums(<?=intval($_GET['tab'])?>, this.value); return false;"><option value="50">50</option><option value="100">100</option><? if ( intval($_GET['tab']) > 1 ) { ?><option value="0">Все</option><? } ?></select>
							</td>
						</tr>
					</tbody>
				</table>
				</form>
			</div>
						
			<div class="b-ext-filter__body b-ext-filter__body_bordtop_fff b-ext-filter__body_pad_10_20 b-ext-filter_marglr_-20 b-ext-filter__body_hide">
				<form id="letters_filter" onsubmit="return false;">
				<input id="letters_filter_search_fld" name="letters_filter_search_fld" type="hidden" value="">
				<input id="letters_filter_search_fld2" name="letters_filter_search_fld2" type="hidden" value="">
				<div class="b-form b-form_padbot_20">
					<div class="b-form__name b-form__name_width_70">Дата<br>обновления</div>
					<div class="b-combo b-combo_inline-block">
						<div class="b-combo__input b-combo__input_calendar b-combo__input_width_125 b-combo__input_arrow-date_yes use_past_date date_format_use_text no_set_date_on_load">
							<input id="letters_filter_change_date_s" name="letters_filter_change_date_s" class="b-combo__input-text" type="text" size="80" value="">
							<span class="b-combo__arrow-date"></span> 
						</div>
					</div>
					<div class="b-layout__txt b-layout__txt_padtop_3 b-layout__txt_inline-block">&nbsp;—&nbsp;</div>
					<div class="b-combo b-combo_inline-block">
						<div class="b-combo__input b-combo__input_calendar b-combo__input_width_125 b-combo__input_arrow-date_yes use_past_date date_format_use_text no_set_date_on_load">
							<input id="letters_filter_change_date_e" name="letters_filter_change_date_e" class="b-combo__input-text" type="text" size="80" value="">
							<span class="b-combo__arrow-date"></span> 
						</div>
					</div>
				</div>
				<div class="b-form b-form_padbot_20">
					<div class="b-form__name b-form__name_width_70">Дата<br>добавления</div>
					<div class="b-combo b-combo_inline-block">
						<div class="b-combo__input b-combo__input_calendar b-combo__input_width_125 b-combo__input_arrow-date_yes use_past_date date_format_use_text no_set_date_on_load">
							<input id="letters_filter_add_date_s" name="letters_filter_add_date_s" class="b-combo__input-text" type="text" size="80" value="">
							<span class="b-combo__arrow-date"></span> 
						</div>
					</div>
					<div class="b-layout__txt b-layout__txt_padtop_3 b-layout__txt_inline-block">&nbsp;—&nbsp;</div>
					<div class="b-combo b-combo_inline-block">
						<div class="b-combo__input b-combo__input_calendar b-combo__input_width_125 b-combo__input_arrow-date_yes use_past_date date_format_use_text no_set_date_on_load">
							<input id="letters_filter_add_date_e" name="letters_filter_add_date_e" class="b-combo__input-text" type="text" size="80" value="">
							<span class="b-combo__arrow-date"></span> 
						</div>
					</div>
				</div>
				<div class="b-form b-form_padbot_20">
					<div class="b-form__name b-form__name_width_70">Кто<br>добавил</div>
					<div class="b-combo b-combo_inline-block">
						<div class="b-combo__input b-combo__input_width_125 b-combo__input_dropdown b-combo__input_arrow-user_yes b_combo__input_request_id_getuserlistold b-combo__input_overflow_hidden allow_create_value">
							<input id="letters_filter_add_user" name="letters_filter_add_user" class="b-combo__input-text" name="" type="text" size="80" value="">
							<span class="b-combo__arrow-user"></span> 
						</div>
					</div>
				</div>
				<div class="b-form b-form_padbot_20">
					<div class="b-form__name b-form__name_width_70">Статусы</div>
					<div class="b-radio b-radio_layout_vertical b-radio_inline-block">
						<div class="b-radio__item b-radio__item_padbot_10">
							<input id="letters_filter_status_0" name="letters_filter_status" type="radio" value="" class="b-radio__input" onClick="letters.changeStatus('filter', 0);">
							<label for="letters_filter_status_0" class="b-radio__label  b-radio__label_fontsize_13">
								Любой
							</label>
						</div>
						<?php
						$statuses = letters::getStatuses();
						foreach($statuses as $status) {
							?>
							<div class="b-radio__item b-radio__item_padbot_10">
								<input id="letters_filter_status_<?=$status['id']?>" name="letters_filter_status" type="radio" value="<?=$status['id']?>" class="b-radio__input" onClick="letters.changeStatus('filter', <?=$status['id']?>);">
								<label for="letters_filter_status_<?=$status['id']?>" class="b-radio__label  b-radio__label_fontsize_13">
								<?=$status['title']?>
								</label>
								<?php if($status['id']==2 || $status['id']==3) { ?>
								<div id="letters_filter_status_date_div_<?=$status['id']?>" class="b-combo b-combo_inline-block b-combo_absolute b-combo_margtop_-6 b-combo_margleft_5" style="visibility: hidden;">
									<div class="b-combo__input b-combo__input_width_125 b-combo__input_arrow-date_yes use_past_date date_format_use_text no_set_date_on_load use_past_date date_format_use_text b-combo__input_calendar">
										<input id="letters_filter_status_date_<?=$status['id']?>" name="letters_filter_status_date_<?=$status['id']?>" class="b-combo__input-text" type="text" size="80" value="">
										<span class="b-combo__arrow-date"></span> </div>
								</div>
								<?php } ?>
							</div>
							<?php
							$n = 1;
						}
						?>
					</div>
				</div>
				<div class="b-form b-form_padbot_20">
					<div class="b-form__name b-form__name_width_70">Документ<br/>без нашего<br/>экземпляра</div>
					<div class="b-combo b-combo_inline-block">
						<div class="b-combo__check">
							<input type="checkbox" id="letters_filter_withoutourdoc" name="letters_filter_withoutourdoc" class="b-check__check" value="1">
						</div>
					</div>
				</div>
				<div class="b-form b-form_padbot_20">
					<div class="b-form__name b-form__name_width_70">Тип<br>отправления</div>
					<div class="b-combo b-combo_inline-block">
						<div class="b-combo__input b-combo__input_width_135 b-combo__input_arrow_yes b-combo__input_init_deliveryList b-combo__input_multi_dropdown disallow_null b-combo__input_overflow_hidden allow_create_value">
							<input id="letters_filter_delivery" name="letters_filter_delivery" class="b-combo__input-text" type="text" size="80" value="">
							<span class="b-combo__arrow"></span> 
						</div>
					</div>
				</div>
				<div class="b-form b-form_padbot_20">
					<div class="b-form__name b-form__name_padtop_5 b-form__name_width_70">ID</div>
					<div class="b-combo b-combo_inline-block">
						<div class="b-combo__input b-combo__input_width_150">
							<input id="letters_filter_id" name="letters_filter_id" class="b-combo__input-text" type="text" size="80" value="">
						</div>
					</div>
					<div class="b-layout__txt b-layout__txt_padlr_20 b-layout__txt_inline-block b-layout__txt_fontsize_11 b-layout__txt_lineheight_1">Название<br> группы</div>
					<div class="b-combo b-combo_inline-block">
						<div class="b-combo__input b-combo__input_width_150 b-combo__input_dropdown b_combo__input_request_id_getlettergrouplist b-combo__input_arrow_yes b-combo__input_overflow_hidden allow_create_value">
							<input id="letters_filter_group" name="letters_filter_group" class="b-combo__input-text" type="text" size="80" value="" first_section_text="&nbsp;" second_section_text="&nbsp;">
							<span class="b-combo__arrow"></span> 
						</div>
					</div>
				</div>
				<div class="b-form b-form_padbot_20">
					<div class="b-form__name b-form__name_padtop_5 b-form__name_width_70">Адресат</div>
					<div class="b-combo b-combo_inline-block">
						<div class="b-combo__input b-combo__input_width_125 b-combo__input_dropdown b-combo__input_arrow-user_yes b_combo__input_request_id_getusersandcompanies b-combo__input_overflow_hidden allow_create_value">
							<input id="letters_filter_get_user" name="letters_filter_get_user" class="b-combo__input-text" type="text" size="80" value="">
							<span class="b-combo__arrow-user"></span>
						</div>
					</div>
				</div>
				<div class="b-buttons b-buttons_padleft_67">		
					<a href="#" class="b-button b-button_flat b-button_flat_grey" onClick="letters.searchDocs(); return false;">Найти документы</a>&#160;&#160;<a class="b-buttons__link" href="#" onClick="letters.clearSearch(); return false;">очистить</a>	
				</div>															
				</form>
			</div>
		</div>
	</div>

	<div id="letters_data"></div>
	<div id="letters_notfound" style="text-align: center; display: none;"><h2><br/><br/>Документов не найдено</h2></div>

	<div id="letters_selected_delivery_cost" class="b-layout__txt b-layout__txt_bold" style="display: none">Стоимость доставки всех выбранных документов — <span id="letters_selected_delivery_cost_data"></span> руб.</div>

	<form id="letters_frm_mass_data" style="display: none;"><input type="hidden" id="letters_frm_mass_data_ids" name="letters_frm_mass_data_ids" value=""></form>



	<div id="letters_mass_action_div" class="b-shadow b-shadow_width_760 b-shadow_zindex_3 b-shadow_fixed b-shadow_bottom_-10">
		<div class="b-shadow__right">
			<div class="b-shadow__left">
				<div class="b-shadow__top">
					<div class="b-shadow__bottom">
						<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
							<div class="b-shadow__txt">Выбрано <span id="letters_mass_action_div_selected_docs">0</span>: &nbsp; <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_fontsize_11" href="#" onClick="letters.uncheckAll(); return false;">Снять выделение</a> &nbsp; <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_fontsize_11" href="#" onClick="letters.massEditStatus(); return false;">Изменить статус</a> &nbsp; <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_fontsize_11" href="#" onClick="letters.massEditCost(); return false;">Изменить стоимость</a> &nbsp; <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_fontsize_11" href="#" onClick="letters.massEditDate(); return false;">Изменить дату</a> &nbsp; <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_fontsize_11" href="#" onClick="letters.massCalcDelivery(); return false;">Рассчитать стоимость</a> &nbsp; <a id="letters_mass_action_div_menu_send" class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_fontsize_11" onClick="letters.processSendDocs(); return false;" href="#" >Печать</a></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="b-shadow__tl"></div>
		<div class="b-shadow__tr"></div>
		<div class="b-shadow__bl"></div>
		<div class="b-shadow__br"></div>
	</div>

</div>

<?php if($js_cmd) { ?>
<script type="text/javascript">
var is_js_cmd = true;
window.addEvent('domready', function() {
	<?php
	switch($js_cmd) {
		case 'tab':
			?>
			letters.changeTabs(<?=$js_cmd_var1?>);
			<?php
			break;
		case 'group':
			?>
			letters.showGroup(<?=$js_cmd_var1?>);
			<?php
			break;
		case 'doc':
			?>
			letters.showDoc(<?=$js_cmd_var1?>);
			<?php
			break;
	}
	?>
});
</script>
<?php } ?>