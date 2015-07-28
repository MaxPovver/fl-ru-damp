<div id="ov-notice" class="b-popup b-popup_center b-popup_width_500" style="z-index:1050"> 
	<b class="b-popup__c1"></b> <b class="b-popup__c2"></b> <b class="b-popup__t"></b>
	<div class="b-popup__r">
		<div class="b-popup__l">
			<div class="b-popup__body"> 
				<a class="close" style="float: right;" href="javascript:void(0);" onclick="banned.commit(banned.banUid,(banned.buffer[banned.banUid].action='close'));return false;"><img height="21" width="21" alt="" src="/images/btn-close.png"></a>
				<h4 class="b-popup__h4">Предупреждение для <a id="warn_name" class="b-popup__link b-popup__link_color_000" target="_blank" href="#"></a></h4>
				<div class="b-form b-form_padbot_10">
					<label class="b-form__name b-form__name_width_70 b-form__name_fontsize_13">Действие:</label>
					<div id="warn_label" class="b-form__txt b-form__txt_inline-block">Снять</div>
				</div>
				<div class="b-form b-form_padbot_10">
					<label class="b-form__name b-form__name_width_70 b-form__name_fontsize_13">Причина:</label>
					<div id="warn_div" class="b-select b-select_inline-block">
						<select class="b-select__select b-select__select_width_full" disabled="disabled">
							<option>Подождите...</option>
						</select>
					</div>
				</div>
				<div class="b-form b-form_padbot_10 b-form_padleft_70">
					<div class="b-textarea" id="warn_texarea">
						<textarea class="b-textarea__textarea" name="" cols="" rows=""></textarea>
					</div>
				</div>
				<div class="b-popup__foot">
						<div class="b-buttons">
							<input type="button" id="warn_btn" onclick="banned.commit(banned.banUid,$('bfrm_'+banned.banUid).get('value'));" class="i-btn i-bold" value="Сохранить" />
							<a id="warn_close" href="javascript:void(0);" onclick="banned.commit(banned.banUid,(banned.buffer[banned.banUid].action='close'));return false;" class="b-buttons__link b-buttons__link_dot_666">Отмена</a> 
						</div>
				</div>
			</div>
		</div>
	</div>
	<b class="b-popup__b"></b> <b class="b-popup__c3"></b> <b class="b-popup__c4"></b> 
</div>
