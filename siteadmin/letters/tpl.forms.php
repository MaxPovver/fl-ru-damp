<?php
$statuses = letters::getStatuses();
?>
<script type="text/javascript">
	var statuses_list = new Array();
	statuses_list[0] = 'ƒобавить статус';
	<?php foreach($statuses as $status) { ?>
		statuses_list[<?=$status['id']?>] = '<?=$status['title']?>';
	<?php } ?>

</script>

<div id="letters_form_comment" class="i-shadow" style="display: none;">
	<div class="b-shadow b-shadow_width_450 b-shadow_top_0 b-shadow_left_-70">
		<div class="b-shadow__right">
			<div class="b-shadow__left">
				<div class="b-shadow__top">
					<div class="b-shadow__bottom">
						<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
							<div class="b-textarea b-textarea_noresize">
								<textarea id="letters_form_comment_field_data" class="b-textarea__textarea" cols="" rows=""></textarea>
							</div>
							<div class="b-buttons b-buttons_padtop_15">
								<a class="b-button b-button_flat b-button_flat_green" href="javascript:void()" onClick="letters.formCommentUpdate(); return false;">—охранить</a>
								<span class="b-buttons__txt b-buttons__txt_padleft_10">или&nbsp;</span>
								<a class="b-buttons__link b-buttons__link_dot_c10601" href="#" onClick="letters.formCommentHide(); return false;">закрыть, не сохран€€</a>
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




<div id="letters_form_delivery" class="i-shadow" style="display: none; position: absolute;">									
	<div class="b-shadow b-shadow_width_380 b-shadow_top_-5 b-shadow_left_-160">
		<div class="b-shadow__right">
			<div class="b-shadow__left">
				<div class="b-shadow__top">
					<div class="b-shadow__bottom">
						<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
							<div class="b-radio b-radio_layout_vertical b-radio_inline-block">
								<form id="letters_form_delivery_form">
									<div class="b-radio__item b-radio__item_padbot_10">
										<input id="letters_form_delivery_field_data_0" name="letters_form_delivery_field_data" type="radio" class="b-radio__input" value="0">
										<label class="b-radio__label  b-radio__label_fontsize_13" for="letters_form_delivery_field_data_0">Ќе выбрано</label>
									</div>
								<?php
								$f_deliveries = letters::getDeliveries();
								foreach($f_deliveries as $f_delivery) {
									?>
									<div class="b-radio__item b-radio__item_padbot_10">
										<input id="letters_form_delivery_field_data_<?=$f_delivery['id']?>" name="letters_form_delivery_field_data" type="radio" class="b-radio__input" value="<?=$f_delivery['id']?>">
										<label class="b-radio__label  b-radio__label_fontsize_13" for="letters_form_delivery_field_data_<?=$f_delivery['id']?>"><?=$f_delivery['title']?></label>
									</div>
									<?php
								}
								?>
								</form>
							</div>
							<div class="b-buttons b-buttons_padleft_20">
								<a class="b-button b-button_flat b-button_flat_green" href="javascript:void()" onClick="letters.formDeliveryUpdate(); return false;">—охранить</a>
								<span class="b-buttons__txt b-buttons__txt_padleft_10">или&nbsp;</span>
								<a class="b-buttons__link b-buttons__link_dot_c10601" href="#" onClick="letters.formDeliveryHide(); return false;">закрыть, не сохран€€</a>
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




<div id="letters_form_deliverycost" class="i-shadow" style="display: none; position: absolute;">									
	<div class="b-shadow b-shadow_width_335 b-shadow_top_-5 b-shadow_left_-140">
		<div class="b-shadow__right">
			<div class="b-shadow__left">
				<div class="b-shadow__top">
					<div class="b-shadow__bottom">
						<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
							<div class="b-combo">
								<div class="b-combo__input">
									<input id="letters_form_deliverycost_field_data" name="letters_form_deliverycost_field_data" class="b-combo__input-text" type="text" size="80" value="" maxlength="10">
								</div>
							</div>
							<div class="b-buttons b-buttons_padtop_15">
								<a class="b-button b-button_flat b-button_flat_green" href="javascript:void()" onClick="letters.formDeliveryCostUpdate(); return false;">—охранить</a>
								<span class="b-buttons__txt b-buttons__txt_padleft_10">или&nbsp;</span>
								<a class="b-buttons__link b-buttons__link_dot_c10601" href="#" onClick="letters.formDeliveryCostHide(); return false;">закрыть, не сохран€€</a>
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



<div id="letters_form_dateadd" class="i-shadow" style="display: none; position: absolute;">									
	<div class="b-shadow b-shadow_width_335 b-shadow_top_-5 b-shadow_left_-140">
		<div class="b-shadow__right">
			<div class="b-shadow__left">
				<div class="b-shadow__top">
					<div class="b-shadow__bottom">
						<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">

									<div class="b-combo">
										<div class="b-combo__input b-combo__input_calendar b-combo__input_width_125 b-combo__input_arrow-date_yes use_past_date date_format_use_text no_set_date_on_load">
											<input id="letters_form_dateadd_field_data" name="letters_form_dateadd_field_data" class="b-combo__input-text" type="text" size="80" value="">
											<span class="b-combo__arrow-date"></span> 
										</div>
									</div>

							<div class="b-buttons b-buttons_padtop_15">
								<a class="b-button b-button_flat b-button_flat_green" href="javascript:void()" onClick="letters.formDateAddUpdate(); return false;">—охранить</a>
								<span class="b-buttons__txt b-buttons__txt_padleft_10">или&nbsp;</span>
								<a class="b-buttons__link b-buttons__link_dot_c10601" href="#" onClick="letters.formDateAddHide(); return false;">закрыть, не сохран€€</a>
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



<div id="letters_form_datechange" class="" style="display: none; position: absolute;">									
	<div class="b-shadow b-shadow_zindex_2 b-shadow_width_335 b-shadow_top_-5 b-shadow_left_-140">
		<div class="b-shadow__right">
			<div class="b-shadow__left">
				<div class="b-shadow__top">
					<div class="b-shadow__bottom">
						<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">

									<div class="b-combo">
										<div class="b-combo__input b-combo__input_calendar b-combo__input_width_125 b-combo__input_arrow-date_yes use_past_date date_format_use_text no_set_date_on_load">
											<input id="letters_form_datechange_field_data" name="letters_form_datechange_field_data" class="b-combo__input-text" type="text" size="80" value="">
											<span class="b-combo__arrow-date"></span> 
										</div>
									</div>

							<div class="b-buttons b-buttons_padtop_15">
								<a class="b-button b-button_flat b-button_flat_green" href="javascript:void()" onClick="letters.formDateChangeUpdate(); return false;">—охранить</a>
								<span class="b-buttons__txt b-buttons__txt_padleft_10">или&nbsp;</span>
								<a class="b-buttons__link b-buttons__link_dot_c10601" href="#" onClick="letters.formDateChangeHide(); return false;">закрыть, не сохран€€</a>
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



<div id="letters_form_status" class="i-shadow" style="display: none;">								
	<div class="b-shadow b-shadow_width_380 b-shadow_top_-5 b-shadow_left_-130">
		<div class="b-shadow__right">
			<div class="b-shadow__left">
				<div class="b-shadow__top">
					<div class="b-shadow__bottom">
						<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
							<div class="b-radio b-radio_layout_vertical b-radio_inline-block">
								<form id="letters_form_status_form">
								<?php
								$n = 0;
								foreach($statuses as $status) {
									?>
									<div class="b-radio__item b-radio__item_padbot_10">
										<input id="letters_form_status_<?=$status['id']?>" type="radio" name="letters_form_field_status" value="<?=$status['id']?>" class="b-radio__input" <?=($n==0 ? 'checked' : '')?> onClick="letters.changeStatus('popup', <?=$status['id']?>);">
										<label for="" class="b-radio__label  b-radio__label_fontsize_13">
											<?=$status['title']?>
										</label>
										<?php if($status['id']==2 || $status['id']==3) { ?>
										<div id="letters_form_status_date_div_<?=$status['id']?>" class="b-combo b-combo_inline-block b-combo_absolute b-combo_margtop_-6 b-combo_margleft_5" style="visibility: hidden;">
											<div class="b-combo__input b-combo__input_width_125 b-combo__input_arrow-date_yes use_past_date date_format_use_text no_set_date_on_load use_past_date date_format_use_text b-combo__input_calendar">
												<input id="letters_form_status_date_<?=$status['id']?>" class="b-combo__input-text" type="text" size="80" value="">
												<span class="b-combo__arrow-date"></span> </div>
										</div>
										<?php } ?>
									</div>
									<?php
									$n = 1;
								}
								?>
								</form>
							</div>
							<div class="b-buttons b-buttons_padleft_20">
								<a class="b-button b-button_flat b-button_flat_green" href="javascript:void()" onClick="letters.formStatusUpdate(); return false;">—охранить</a>
								<span class="b-buttons__txt b-buttons__txt_padleft_10">или&nbsp;</span>
								<a class="b-buttons__link b-buttons__link_dot_c10601" href="#" onClick="letters.formStatusHide(); return false;">закрыть, не сохран€€</a>
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



<div id="letters_doc_frm_div_statuses" class="i-shadow" style="display: none; z-index: 50;">								
	<div class="b-shadow b-shadow_top_-10 b-shadow_left_-130" style="width:350px;">
		<div class="b-shadow__right">
			<div class="b-shadow__left">
				<div class="b-shadow__top">
					<div class="b-shadow__bottom">
						<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
							<div class="b-radio b-radio_layout_vertical b-radio_inline-block">
								<div class="b-radio__item b-radio__item_padbot_10">
									<input id="letters_doc_frm_div_statuses_st_0" type="radio" name="letters_doc_frm_div_statuses_st" value="0" class="b-radio__input" checked onClick="letters.changeStatus('newpopup', 0);">
									<label for="letters_doc_frm_div_statuses_st_0" class="b-radio__label  b-radio__label_fontsize_13">Ќе выбрано</label>
								</div>
								<?php
								foreach($statuses as $status) {
									?>
									<div class="b-radio__item b-radio__item_padbot_10">
										<input id="letters_doc_frm_div_statuses_st_<?=$status['id']?>" type="radio" name="letters_doc_frm_div_statuses_st" value="<?=$status['id']?>" class="b-radio__input" onClick="letters.changeStatus('newpopup', <?=$status['id']?>);">
										<label for="letters_doc_frm_div_statuses_st_<?=$status['id']?>" class="b-radio__label  b-radio__label_fontsize_13"><?=$status['title']?></label>
										<?php if($status['id']==2 || $status['id']==3) { ?>
										<div id="letters_doc_frm_div_statuses_st_date_div_<?=$status['id']?>" class="b-combo b-combo_inline-block b-combo_absolute b-combo_margtop_-6 b-combo_margleft_5" style="visibility: hidden;">
											<div class="b-combo__input b-combo__input_width_125 b-combo__input_arrow-date_yes use_past_date date_format_use_text no_set_date_on_load use_past_date date_format_use_text b-combo__input_calendar">
												<input id="letters_doc_frm_div_statuses_st_date_<?=$status['id']?>" class="b-combo__input-text" type="text" size="80" value="">
												<span class="b-combo__arrow-date"></span> </div>
										</div>
										<?php } ?>
									</div>
									<?php
								}
								?>
							</div>
							<div class="b-buttons b-buttons_padleft_20">
								<a id="letters_doc_frm_div_statuses_btn_submit" class="b-button b-button_flat b-button_flat_green" href="javascript:void()" onClick="letters.statusesSet(); return false;">¬ыбрать</a>
								<span class="b-buttons__txt b-buttons__txt_padleft_10">или&nbsp;</span>
								<a class="b-buttons__link b-buttons__link_dot_c10601" href="#" onClick="letters.statusesHide(); return false;">закрыть, не выбира€</a>
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

