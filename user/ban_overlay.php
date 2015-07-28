<?php
$aDays   = range( 1, 31 );
$nYear   = intval( date('Y') );
$aYears  = range( $nYear, $nYear + 10 );
$aMounth = array(
    '01' => "Января", 
    '02' => "Февраля", 
    '03' => "Марта", 
    '04' => "Апреля", 
    '05' => "Мая", 
    '06' => "Июня", 
    '07' => "Июля", 
    '08' => "Августа", 
    '09' => "Сентября", 
    '10' => "Октября", 
    '11' => "Ноября", 
    '12' => "Декабря"
);
//$uncompletedDeals = $sbr_info['all_cnt'] - $sbr_info['completed_cnt'];
?>



<div id="ov-notice22" class="b-shadow b-shadow_center_top b-shadow_width_540 b-shadow_zindex_11 b-shadow_hide">
					<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_10">
                		<h4 id="ban_title" class="b-shadow__h4 b-shadow__h4_padbot_10">Блокировка <a class="b-shadow__link b-shadow__link_color_000" href="#"></a></h4>
                        
                        <div id="ban_user_sbrs" class="b-fon b-fon_padbot_10 b-fon_hide">
                            <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
                                <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-25"></span><span id="uncompleted_deals_count"></span>
                                    <div class="b-check b-check_padtop_10">
                                    	<input id="notice_sbr_partners" class="b-check__input" name="notice_sbr_partners" type="checkbox" value="1" />
                                        <label class="b-check__label b-check__label_fontsize_13" for="notice_sbr_partners">Оповестить заказчиков</label>
                                    </div>
                            </div>
                        </div>                        
                        
							<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full">
								<tbody><tr class="b-layout__tr">
										<td class="b-layout__left b-layout__left_padbot_10 b-layout__left_width_90">
												<div class="b-layout__txt">Действие:</div>
										</td>
										<td class="b-layout__right b-layout__right_padbot_10">
												<div class="b-radio b-radio_layout_vertical">
													<div class="b-radio__item ">
														<input id="ban_none" class="b-radio__input" type="radio" value="1" name="action" onchange="banned.userBanToggle();" onclick="banned.userBanNone(banned.banUid);" />
														<label class="b-radio__label b-radio__label_fontsize_13" for="ban_none">Разблокировать</label>
													</div>
													<div class="b-radio__item ">
														<input id="ban_site" class="b-radio__input" type="radio" value="2" name="action" onchange="banned.userBanToggle();" onclick="banned.userBanSite(banned.banUid);" />
														<label for="ban_site" class="b-radio__label b-radio__label_fontsize_13">Заблокировать на всем сайте</label>
													</div>
												</div>
										</td>
								</tr>
								<tr class="b-layout__tr">
										<td class="b-layout__left b-layout__left_padbot_10 b-layout__left_width_90">
												<div class="b-layout__txt">Срок:</div>
										</td>
										<td class="b-layout__right b-layout__right_padbot_10">
												<div class="b-radio b-radio_layout_horizontal">
													<div class="b-radio__item ">
														<input id="ban_to_date" class="b-radio__input" type="radio" value="1" name="ban_to" onclick="banned.userBanToToggle();" />
														<label for="ban_to_date" class="b-radio__label b-radio__label_fontsize_13">До</label>
													</div>
													&nbsp;&nbsp;
												<div class="b-select b-select_inline-block">
														<select id="ban_day" class="b-select__select b-select__select_width_50" name="ban_day" maxlength="10">
                                                        <option value=""></option>
														  <?php foreach ( $aDays as $nDay ) { ?>
                                                          <option value="<?=$nDay?>"><?=$nDay?></option>
                                                          <?php } ?>
                                                        </select>
												</div>&nbsp;<div class="b-select b-select_inline-block">
														<select id="ban_month" class="b-select__select b-select__select_width_80" onchange="banned.updateDays('ban_day','ban_month','ban_year')" name="ban_month">
                                                          <option value=""></option>
                                                          <?php foreach ( $aMounth as $key => $name ) { ?>
                                                          <option value="<?=$key?>"><?=$name?></option>
                                                          <?php } ?>
                                                        </select>
												</div>&nbsp;<div class="b-select b-select_inline-block">
														<select id="ban_year" class="b-select__select b-select__select_width_50" onchange="banned.updateDays('ban_day','ban_month','ban_year')" name="ban_year">
                                                          <option value=""></option>
                                                          <?php foreach ( $aYears as $nYear ) { ?>
                                                          <option value="<?=$nYear?>"><?=$nYear?></option>
                                                          <?php } ?>
                                                        </select>
												</div>&nbsp;&nbsp;
													<div class="b-radio__item ">
														<label class="b-radio__label b-radio__label_fontsize_13">или</label>&nbsp;
														<input id="ban_forever" class="b-radio__input" type="radio" value="2" name="ban_to" onclick="banned.userBanToToggle();" />
														<label class="b-radio__label b-radio__label_fontsize_13" for="ban_forever">навсегда</label>
													</div>
												</div>
										</td>
								</tr>
                                <tr class="b-layout__tr">
										<td class="b-layout__left b-layout__left_padbot_10 b-layout__left_width_90">
												<div class="b-layout__txt b-layout__txt_padtop_4">Причина:</div>
										</td>
										<td class="b-layout__right b-layout__right_padbot_10">
                                            <div id="ban_div_select" class="b-select">
                                                <select class="b-select__select" disabled="disabled"><option>Подождите...</option></select>
                                            </div>
										</td>
								</tr>
								<tr class="b-layout__tr">
										<td class="b-layout__left b-layout__left_padbot_10 b-layout__left_width_90">&nbsp;</td>
										<td class="b-layout__right b-layout__right_padbot_15">
												<div id="ban_div_textarea" class="b-textarea">
														<textarea class="b-textarea__textarea b-textarea__textarea_height_50" rows="" cols=""></textarea>
												</div>
										</td>
								</tr>

							</tbody></table>

							<h4 id="ban_delreason_title" class="b-shadow__h4 b-shadow__h4_padbot_10" style='display: none;'>Причина удаления</h4>
							<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full">
                                <tr class="b-layout__tr" style="display: none;">
										<td class="b-layout__left b-layout__left_padbot_10 b-layout__left_width_90">
												<div class="b-layout__txt b-layout__txt_padtop_4">Причина:</div>
										</td>
										<td class="b-layout__right b-layout__right_padbot_10">
                                            <div id="ban_div_select_stream" class="b-select">
                                                <select class="b-select__select" disabled="disabled"><option>Подождите...</option></select>
                                            </div>
										</td>
								</tr>
								<tr class="b-layout__tr" style="display: none;">
										<td class="b-layout__left b-layout__left_padbot_10 b-layout__left_width_90">&nbsp;</td>
										<td class="b-layout__right b-layout__right_padbot_10">
												<div id="ban_div_textarea_stream" class="b-textarea">
														<textarea class="b-textarea__textarea b-textarea__textarea_height_50" rows="" cols=""></textarea>
												</div>
										</td>
								</tr>
							</table>
							<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full">
								<tr class="b-layout__tr">
										<td class="b-layout__left b-layout__left_width_90">&nbsp;</td>
										<td class="b-layout__right">
                                            <div id="div_ban_btn" class="b-buttons">
                                                <a id="ban_btn" href="javascript:void(0);" class="b-button b-button_flat b-button_flat_green" onclick="banned.commit(banned.banUid,$('bfrm_'+banned.banUid).get('value') )">Сохранить</a>
                                                <span class="b-buttons__txt b-buttons__txt_padleft_10">или</span>
                                                <a href="javascript:void(0);" class="b-buttons__link b-buttons__link_dot_c10601" onclick="banned.commit(banned.banUid,(banned.buffer[banned.banUid].action='close'));return false;">закрыть, не сохраняя</a>
                                            </div>
										</td>
								</tr>
						</tbody></table>                
                			<input type="hidden" id="ban_uid" name="ban_uid" value="">
            		</div>

	<span class="b-shadow__icon b-shadow__icon_close" onclick="banned.commit(banned.banUid,(banned.buffer[banned.banUid].action='close'));$('ov-notice22').toggleClass('b-shadow_hide');return false;"></span>
</div>


