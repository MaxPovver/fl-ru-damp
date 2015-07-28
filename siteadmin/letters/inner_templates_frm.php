<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/letters.common.php' );
$xajax->printJavascript( '/xajax/' );
?>


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
is_templates_mode = true;
</script>


<div class="b-layout__txt"><a class="b-layout__link" href="/siteadmin/letters/?mode=templates">Все шаблоны</a> &rarr;</div>

<?php if($_GET['mode']=='edit_template') { ?>
<h2 class="b-layout__title">Редактирование шаблона: <?=htmlspecialchars($template['title'])?></h2>
<?php } else { ?>
<h2 class="b-layout__title">Новый шаблон</h2>
<?php } ?>


<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20">
	<tbody>
		<tr class="b-layout__tr">
			<td class="b-layout__left b-layout__left_width_100">
				<div class="b-layout__txt b-layout__txt_padtop_5">Название</div>
			</td>
			<td class="b-layout__right">
				<div class="b-combo b-combo_inline-block">
					<div class="b-combo__input b-combo__input_width_400">
						<input type="text" class="b-combo__input-text" id="frm_template_name" name="frm_template_name" size="80" value="<?=htmlspecialchars($template['title'])?>">
					</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>


<div id="letters_form_start"></div>
<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/siteadmin/letters/tpl.forms.php' );
?>

<div id="l_form_1" class="b-shadow__title">Новый документ <div id="l_form_1_1" class="b-layout__txt b-layout__txt_inline-block"><a class="b-button b-button_poll_plus" href="" onClick="letters.TemplateInsertNewDoc(); return false;"></a> <a class="b-layout__link b-layout__link_fontsize_15 b-layout__link_bordbot_dot_0f71c8" href="" onClick="letters.TemplateInsertNewDoc(); return false;">добавить</a></div></div>

<br><br>


		<div class="b-shadow b-shadow_zindex_11">
			<form id="letters_doc_frm" action="/siteadmin/letters/" method="POST" enctype="multipart/form-data" onKeyPress="if((event.ctrlKey) && ((event.keyCode==10)||(event.keyCode==13))) { return false; }">
			<input id="letters_doc_frm_template_id" name="letters_doc_frm_template_id" type="hidden" value="<?=$template['id']?>">
			<input id="letters_doc_frm_user_query" name="letters_doc_frm_user_query" type="hidden" value="">
			<input id="letters_doc_frm_user1_status_data" name="letters_doc_frm_user1_status_data" type="hidden" value="0">
			<input id="letters_doc_frm_user2_status_data" name="letters_doc_frm_user2_status_data" type="hidden" value="0">
			<input id="letters_doc_frm_user3_status_data" name="letters_doc_frm_user3_status_data" type="hidden" value="0">
			<input id="letters_doc_frm_user1_status_date_data" name="letters_doc_frm_user1_status_date_data" type="hidden" value="">
			<input id="letters_doc_frm_user2_status_date_data" name="letters_doc_frm_user2_status_date_data" type="hidden" value="">
			<input id="letters_doc_frm_user3_status_date_data" name="letters_doc_frm_user3_status_date_data" type="hidden" value="">
			<div class="b-shadow__right">
				<div class="b-shadow__left">
					<div class="b-shadow__top">
						<div class="b-shadow__bottom">
							<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
								<div class="b-form b-form_padbot_20">
									<div class="b-form__name b-form__name_fontsize_13 b-form__name_padtop_5 b-form__name_width_80">Название</div>
									<div class="b-combo b-combo_inline-block">
										<div class="b-combo__input b-combo__input_width_400">
											<input id="letters_doc_frm_title" name="letters_doc_frm_title" type="text" value="" size="80" class="b-combo__input-text" maxlength="250">
										</div>
									</div>
								</div>
								<div class="b-form b-form_padbot_20">
									<div class="b-form__name b-form__name_fontsize_13 b-form__name_padtop_5 b-form__name_width_80">Группа</div>
									<div class="b-combo b-combo_inline-block">
										<div class="b-combo__input b-combo__input_width_135 b-combo__input_arrow_yes b-combo__input_dropdown b_combo__input_request_id_getlettergrouplist b-combo__input_overflow_hidden allow_create_value b-combo__input_quantity_symbols_1">
											<input id="letters_doc_frm_group" name="letters_doc_frm_group" type="text" value="" size="80" class="b-combo__input-text" first_section_text="&nbsp;" second_section_text="&nbsp;" maxlength="250">
											<span class="b-combo__arrow"></span>
										</div>
									</div>
								</div>



								<div class="b-form b-form_padbot_20">
									<div class="b-form__name b-form__name_fontsize_13 b-form__name_padtop_5 b-form__name_width_80">Сторона 1</div>
									<div class="b-combo b-combo_inline-block">
										<div class="b-combo__input b-combo__input_width_125 b-combo__input_arrow-user_yes b-combo__input_dropdown b_combo__input_request_id_getusersandcompanies b-combo__input_overflow_hidden allow_create_value">
											<input id="letters_doc_frm_user_1" name="letters_doc_frm_user_1" type="text" value="" size="80" class="b-combo__input-text">
											<span class="b-combo__arrow-user"></span>
										</div>
									</div>
									<div id="letters_doc_frm_user_1_status_change" class="b-form__txt b-form__txt_padleft_10 b-form__txt_padtop_5 ">
										<a id="letters_doc_frm_user_1_status_change_lnk" href="#" class="b-layout__link b-layout__link_bordbot_dot_0f71c8" onClick="letters.statusesShow(this, 1); return false;">Добавить статус</a>
									</div>
								</div>
								<div class="b-form b-form_padbot_20">
									<div class="b-form__name b-form__name_fontsize_13 b-form__name_padtop_5 b-form__name_width_80">Сторона 2</div>
									<div class="b-combo b-combo_inline-block">
										<div class="b-combo__input b-combo__input_width_125 b-combo__input_arrow-user_yes b-combo__input_dropdown b_combo__input_request_id_getusersandcompanies b-combo__input_overflow_hidden allow_create_value">
											<input id="letters_doc_frm_user_2" name="letters_doc_frm_user_2" type="text" value="" size="80" class="b-combo__input-text">
											<span class="b-combo__arrow-user"></span>
										</div>
									</div>
									<div id="letters_doc_frm_user_2_status_change" class="b-form__txt b-form__txt_padleft_10 b-form__txt_padtop_5 ">
										<a id="letters_doc_frm_user_2_status_change_lnk" href="#" class="b-layout__link b-layout__link_bordbot_dot_0f71c8" onClick="letters.statusesShow(this, 2); return false;">Добавить статус</a>
									</div>
								</div>
								<div id="letters_doc_frm_user_3_div" class="b-form b-form_padbot_20" style="display: none">
									<div class="b-form__name b-form__name_fontsize_13 b-form__name_padtop_5 b-form__name_width_80">Сторона 3</div>
									<div class="b-combo b-combo_inline-block">
										<div class="b-combo__input b-combo__input_width_125 b-combo__input_arrow-user_yes b-combo__input_dropdown b_combo__input_request_id_getusersandcompanies b-combo__input_overflow_hidden allow_create_value">
											<input id="letters_doc_frm_user_3" name="letters_doc_frm_user_3" type="text" value="" size="80" class="b-combo__input-text">
											<span class="b-combo__arrow-user"></span>
										</div>
									</div>
									<div id="letters_doc_frm_user_3_status_change" class="b-form__txt b-form__txt_padleft_10 b-form__txt_padtop_5 ">
										<a id="letters_doc_frm_user_3_status_change_lnk" href="#" class="b-layout__link b-layout__link_bordbot_dot_0f71c8" onClick="letters.statusesShow(this, 3); return false;">Добавить статус</a>
									</div>
								</div>
								<div class="i-button b-layout__txt b-layout__txt_padbot_20 b-layout__txt_padleft_80">
									<span class="b-button b-button_padright_5 b-button_poll_plus" href="#"></span>&nbsp;<a id="letters_doc_frm_user_3_addlnk" href="#" class="b-layout__link b-layout__link_bordbot_dot_0f71c8" onClick="letters.toggleUser3(this); return false;">Добавить третью сторону</a>
								</div>

								<div id="l_form_3" class="b-form b-form_padbot_20">
									<div class="b-form__name b-form__name_lineheight_1 b-form__name_fontsize_13 b-form__name_width_80">Тип<br>доставки</div>
									<div class="b-combo b-combo_inline-block">
										<div class="b-combo__input b-combo__input_width_135 b-combo__input_arrow_yes b-combo__input_init_deliveryList b-combo__input_multi_dropdown b-combo__input_overflow_hidden allow_create_value">
											<input id="letters_doc_frm_delivery" name="letters_doc_frm_delivery" type="text" value="" size="80" class="b-combo__input-text" readonly>
											<span class="b-combo__arrow"></span>
										</div>
									</div>
								</div>
								<div id="l_form_4" class="b-form b-form_padbot_20">
									<div class="b-form__name b-form__name_lineheight_1 b-form__name_fontsize_13 b-form__name_width_80">Стоимость<br>доставки</div>
									<div class="b-combo b-combo_inline-block">
										<div class="b-combo__input b-combo__input_width_80">
											<input id="letters_doc_frm_delivery_cost" name="letters_doc_frm_delivery_cost" type="text" value="" size="80" class="b-combo__input-text" maxlength="10">
										</div>
									</div>
									<div class="b-form__txt b-form__txt_padleft_10 b-form__txt_padtop_5 ">руб.</div>
								</div>
								<div class="b-form b-form_padbot_20">
									<div class="b-form__name b-form__name_padtop_5 b-form__name_fontsize_13 b-form__name_width_80">Подчинен</div>
									<div class="b-combo b-combo_inline-block">
										<div class="b-combo__input b-combo__input_width_135 b-combo__input_arrow_yes b-combo__input_dropdown b_combo__input_request_id_getletterdoclist b-combo__input_quantity_symbols_1 b-combo__input_overflow_hidden allow_create_value b-combo__input_max-width_500">
											<input id="letters_doc_frm_parent" name="letters_doc_frm_parent" type="text" value="" size="80" class="b-combo__input-text" first_section_text="&nbsp;" second_section_text="&nbsp;">
											<span class="b-combo__arrow"></span>
										</div>
									</div>
									<div class="b-form__txt b-form__txt_padtop_3 b-form__txt_padleft_80 b-form__txt_fontsize_11 b-form__txt_block">Чтобы связать этот документ с другим, укажите его ID. Например, 12345</div>
								</div>
								<!--
								<div class="b-form b-form_padbot_20">
									<div class="b-form__name b-form__name_padtop_5 b-form__name_fontsize_13 b-form__name_width_80">Дата<br>добавления</div>
									<div class="b-combo b-combo_inline-block">
										<div class="b-combo__input b-combo__input_calendar b-combo__input_width_125 b-combo__input_arrow-date_yes use_past_date date_format_use_text no_set_date_on_load">
											<input id="letters_doc_frm_dateadd" name="letters_doc_frm_dateadd" class="b-combo__input-text" type="text" size="80" value="">
											<span class="b-combo__arrow-date"></span> 
										</div>
									</div>
								</div>
								-->
								<div id="l_form_5" class="b-form b-form_padbot_15">
									<div class="b-form__name b-form__name_padtop_5 b-form__name_fontsize_13 b-form__name_width_80">Примечание</div>
									<div class="b-textarea b-textarea_inline-block b-textarea_width_402">
										<textarea id="letters_doc_frm_comment" name="letters_doc_frm_comment" class="b-textarea__textarea" cols="" rows=""></textarea>
									</div>
								</div>

								<div class="b-buttons b-buttons_padleft_78" id="l_form_1_2">
									<a class="b-buttons__link b-buttons__link_dot_c10601" href="#" onClick="letters.TemplateDeleteDoc(letters.curDocTemplate); return false;">удалить документ</a>
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
		</form>
		</div>

<br><br>

								<div class="b-buttons b-buttons_padleft_78">
									<a id="f_button_actionwork" class="b-button b-button_flat b-button_flat_green" onclick="<? if($_GET['mode']=='add_template') { ?>letters.TemplateAddDoc();<? } else { ?>letters.TemplateUpdateDoc();<? } ?> return false;" href="javascript:void()"><span id="f_button_actionwork_txt">Сохранить</span></a>
									<span class="b-buttons__txt b-buttons__txt_padleft_10">или</span>
									<a class="b-buttons__link b-buttons__link_dot_c10601" href="/siteadmin/letters/?mode=templates">закрыть не добавляя</a>
								</div>


<?php if($_GET['mode']=='edit_template') { ?>
<script>
window.addEvent('domready', function() {
	<?php for($n=0; $n<count($template['docs'])-1; $n++) { ?>
	letters.TemplateInsertNewDoc();
	<?php } ?>
	<?php $num=0; foreach($template['docs'] as $doc) { ?>
		letters.TemplateData[<?=$num?>] = [];
		letters.TemplateData[<?=$num?>]['letters_doc_frm_title'] = '<?=$doc['title']?>';
		letters.TemplateData[<?=$num?>]['letters_doc_frm_group_db_id'] = '<?=intval($doc['group_id'])?>';
		letters.TemplateData[<?=$num?>]['letters_doc_frm_user_1_db_id'] = '<?=intval($doc['user_1'])?>';
		letters.TemplateData[<?=$num?>]['letters_doc_frm_user_1_section'] = '<?=($doc['is_user_1_company']=='t' ? 1 : 0)?>';
		letters.TemplateData[<?=$num?>]['letters_doc_frm_user_2_db_id'] = '<?=intval($doc['user_2'])?>';
		letters.TemplateData[<?=$num?>]['letters_doc_frm_user_2_section'] = '<?=($doc['is_user_2_company']=='t' ? 1 : 0)?>';
		letters.TemplateData[<?=$num?>]['letters_doc_frm_user_3_db_id'] = '<?=intval($doc['user_3'])?>';
		letters.TemplateData[<?=$num?>]['letters_doc_frm_user_3_section'] = '<?=($doc['is_user_3_company']=='t' ? 1 : 0)?>';
		letters.TemplateData[<?=$num?>]['letters_doc_frm_delivery_db_id'] = '<?=intval($doc['delivery'])?>';
		letters.TemplateData[<?=$num?>]['letters_doc_frm_delivery_cost'] = '<?=$doc['delivery_cost']?>';
		letters.TemplateData[<?=$num?>]['letters_doc_frm_parent_db_id'] = '<?=$doc['parent']?>';
		letters.TemplateData[<?=$num?>]['letters_doc_frm_comment'] = '<?=$doc['comment']?>';

		//letters.TemplateData[<?=$num?>]['letters_doc_frm_withoutourdoc'] = ($('letters_doc_frm_withoutourdoc').get('checked') ? 1 : 0);

		letters.TemplateData[<?=$num?>]['letters_doc_frm_user1_status_data'] = '<?=intval($doc['user_status_1'])?>';
		letters.TemplateData[<?=$num?>]['letters_doc_frm_user2_status_data'] = '<?=intval($doc['user_status_2'])?>';
		letters.TemplateData[<?=$num?>]['letters_doc_frm_user3_status_data'] = '<?=intval($doc['user_status_3'])?>';

		letters.TemplateData[<?=$num?>]['letters_doc_frm_user1_status_date_data'] = '<?=($doc['user_status_date_1'] ? dateFormat('Y-m-d', $doc['user_status_date_1']) : '')?>';
		letters.TemplateData[<?=$num?>]['letters_doc_frm_user2_status_date_data'] = '<?=($doc['user_status_date_2'] ? dateFormat('Y-m-d', $doc['user_status_date_2']) : '')?>';
		letters.TemplateData[<?=$num?>]['letters_doc_frm_user3_status_date_data'] = '<?=($doc['user_status_date_3'] ? dateFormat('Y-m-d', $doc['user_status_date_3']) : '')?>';

	<?php $num++; } ?>
	letters.TemplateShowDoc(1, false);
});
</script>
<?php } ?>