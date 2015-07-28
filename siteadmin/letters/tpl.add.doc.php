		<div class="b-shadow b-shadow_width_560 b-shadow_zindex_11 b-shadow_hide">
			<form id="letters_doc_frm" action="/siteadmin/letters/" method="POST" enctype="multipart/form-data" onKeyPress="if((event.ctrlKey) && ((event.keyCode==10)||(event.keyCode==13))) { return false; }">
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
								<div id="l_form_10" class="b-shadow__title b-shadow__title_padbot_15">
									<select onchange="letters.selectTemplate(this.value);">
										<option value="0">Выберите шаблон</option>
										<?php
										$templates = letters::getTemplatesList();
										if($templates) {
											foreach($templates as $template) {
												?>
												<option value="<?=$template['id']?>"><?=($template['title'] ? htmlspecialchars($template['title']) : '[без названия]')?></option>
												<?php
											}
										}
										?>
									</select>
								</div>
								<div id="l_form_1" class="b-shadow__title b-shadow__title_padbot_15">Новый документ <div id="l_form_1_1" class="b-layout__txt b-layout__txt_inline-block"><a class="b-button b-button_poll_plus" href="" onClick="letters.M_InsertNewDoc(); return false;"></a> <a class="b-layout__link b-layout__link_fontsize_15 b-layout__link_bordbot_dot_0f71c8" href="" onClick="letters.M_InsertNewDoc(); return false;">добавить</a></div></div>
								
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
								<div class="b-form b-form_padbot_20">
									<div class="b-form__name b-form__name_padtop_5 b-form__name_fontsize_13 b-form__name_width_80">Дата<br>добавления</div>
									<div class="b-combo b-combo_inline-block">
										<div class="b-combo__input b-combo__input_calendar b-combo__input_width_125 b-combo__input_arrow-date_yes use_past_date date_format_use_text no_set_date_on_load">
											<input id="letters_doc_frm_dateadd" name="letters_doc_frm_dateadd" class="b-combo__input-text" type="text" size="80" value="">
											<span class="b-combo__arrow-date"></span> 
										</div>
									</div>
								</div>
								<div id="l_form_5" class="b-form b-form_padbot_15">
									<div class="b-form__name b-form__name_padtop_5 b-form__name_fontsize_13 b-form__name_width_80">Примечание</div>
									<div class="b-textarea b-textarea_inline-block b-textarea_width_402">
										<textarea id="letters_doc_frm_comment" name="letters_doc_frm_comment" class="b-textarea__textarea" cols="" rows=""></textarea>
									</div>
								</div>

								<div id="l_form_6" class="b-form b-form_padbot_15">
									<div class="b-form__name b-form__name_padtop_5 b-form__name_fontsize_13 b-form__name_width_80">Документ<br/>без нашего<br/>экземпляра</div>
									<div class="b-combo b-combo_inline-block b-form__name_padtop_5">
										<input type="checkbox" id="letters_doc_frm_withoutourdoc" name="letters_doc_frm_withoutourdoc" class="b-check__check" value="1">
									</div>
								</div>

								<div id="letters_div_attach" class="b-file b-file_padleft_80 b-file_padbot_15">												

									<div id="attachedfiles">
		 					 	   	<?php require_once($_SERVER['DOCUMENT_ROOT'].'/siteadmin/letters/tpl.attachedfiles.php'); ?>
		 					 	    </div>
									<script type="text/javascript">
									    (function () {
									        var attachedfiles_list = new Array();
							    		    <?php
							        		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
										    $attachedfiles = new attachedfiles('', true);
    										$asid = $attachedfiles->createSessionID();
    										$attachedfiles->addNewSession($asid);
							        		?>
							   	    		attachedFiles.initComm( 'attachedfiles', 
							                                		'<?=$asid?>',
							                                		attachedfiles_list, 
							                                		'1',
							                                		'<?=letters::MAX_FILE_SIZE?>',
							                                		'<?=implode(', ', $GLOBALS['disallowed_array'])?>',
							                                		'letters',
							                                		'<?=get_uid(false)?>'
							                                		);
							    		})();
									</script>
									<input type='hidden' id='attachedfiles_uid' name='attachedfiles_uid' value='<?=get_uid(false)?>'>
									<input type='hidden' id='attachedfiles_action' name='attachedfiles_action' value=''>
									<input type='hidden' id='attachedfiles_delete' name='attachedfiles_delete' value=''>
									<input type='hidden' id='attachedfiles_type' name='attachedfiles_type' value='letters'>
									<input type='hidden' id='attachedfiles_session' name='attachedfiles_session' value='<?=$asid?>'>
									<iframe id='attachedfiles_hiddenframe' name='attachedfiles_hiddenframe' style='display:none;'></iframe>

								</div>											



								<div class="b-buttons b-buttons_padleft_78" id="l_form_1_2">
									<a class="b-buttons__link b-buttons__link_dot_c10601" href="#" onClick="letters.M_DeleteDoc(letters.curDocM); return false;">удалить документ</a>
									<br/><br/>
								</div>



								<div class="b-buttons b-buttons_padleft_78">
									<a id="f_button_actionwork" class="b-button b-button_flat b-button_flat_green" onclick="letters.addDocument(); return false;" href="javascript:void()"><span id="f_button_actionwork_txt">Создать</span></a>
									<span class="b-buttons__txt b-buttons__txt_padleft_10">или</span>
									<a class="b-buttons__link b-buttons__link_dot_c10601" href="#" onClick="letters.hideAddForm(); return false;">закрыть не добавляя</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
		</div>
        
 <style type="text/css">
 #letters_doc_frm .b-combo .b-shadow{ top:26px;}
 </style>       
        