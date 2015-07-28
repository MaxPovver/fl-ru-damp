					<?php // С Сашей CSS для старого календаря временно решили поставить здесь, до переделки календаря на новый вид ?>

					<?php
					require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");

					$filter_categories = professions::GetAllGroupsLite(TRUE);
					$filter_subcategories = professions::GetAllProfessions(1);

					$all_mirrored_specs = professions::GetAllMirroredProfsId();
					$mirrored_specs = array();
					for ($is = 0; $is < sizeof($all_mirrored_specs); $is++) {
    					$mirrored_specs[$all_mirrored_specs[$is]['main_prof']] = $all_mirrored_specs[$is]['mirror_prof'];
    					$mirrored_specs[$all_mirrored_specs[$is]['mirror_prof']] = $all_mirrored_specs[$is]['main_prof'];
					}
    				$all_specs = professions::GetAllProfessions("", 0, 1);
					?>

					<script type="text/javascript">
    					var qprj_filter_specs = new Array();
    					var qprj_filter_specs_ids = new Array();
						<?
						$spec_now = 0;
						for ($i = 0; $i < sizeof($all_specs); $i++) {
    						if ($all_specs[$i]['groupid'] != $spec_now) {
        						$spec_now = $all_specs[$i]['groupid'];
        						echo "qprj_filter_specs[" . $all_specs[$i]['groupid'] . "]=[";
    						}
    						echo "[" . $all_specs[$i]['id'] . ",'" . $all_specs[$i]['profname'] . "']";
    						if ($all_specs[$i + 1]['groupid'] != $spec_now) {
	        					echo "];";
    						} else {
	        					echo ",";
    						}
						}
						$spec_now = 0;
						for ($i = 0; $i < sizeof($all_specs); $i++) {
    						if ($all_specs[$i]['groupid'] != $spec_now) {
        						$spec_now = $all_specs[$i]['groupid'];
        						echo "qprj_filter_specs_ids[" . $all_specs[$i]['groupid'] . "]={";
    						}
    						echo "" . $all_specs[$i]['id'] . ":1";
    						if ($all_specs[$i + 1]['groupid'] != $spec_now) {
        						echo "};";
    						} else {
        						echo ",";
    						}
						}
						?>
    					var qprj_filter_mirror_specs = {<?
						for ($i = 0; $i < count($all_mirrored_specs), $ms = $all_mirrored_specs[$i]; $i++)
    						print(($i ? ',' : '') . $ms['mirror_prof'] . ':' . $ms['main_prof'] . ',' . $ms['main_prof'] . ':' . $ms['mirror_prof']);
						?>};
        				var qprj_filter_bullets = [[],[]];
                        var PROJECTS_MAX_FILE_COUNT = <?=tmp_project::MAX_FILE_COUNT?>;
                        var PROJECTS_MAX_FILE_SIZE = <?=tmp_project::MAX_FILE_SIZE?>;
                        var PROJECTS_FILE_DISALLOWED = '<?=implode(', ', $GLOBALS['disallowed_array'])?>';
        			</script>

<div id="popup_qedit_prj_div" class="i-shadow">																						
<div id="popup_qedit_prj" class="b-shadow b-shadow_width_600 b-shadow_hide b-shadow_zindex_11" >
					<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
																						
																						
			                    <form id="popup_qedit_prj_frm" name="popup_qedit_prj_frm" action="" enctype="multipart/form-data" method="post">
				                    <div class="b-menu b-menu_rubric b-menu_padbot_10">
					                    <ul class="b-menu__list">
						                    <li id="popup_qedit_prj_tab_i1" class="b-menu__item b-menu__item_active"><span class="b-menu__b1"><span class="b-menu__b2">Основное</span></span></li>
						                    <li id="popup_qedit_prj_tab_i2" class="b-menu__item"><a class="b-menu__link" href="#" onClick="popupQEditPrjMenu(2); return false;">Платные услуги</a></li>
						                    <li id="popup_qedit_prj_tab_i3" class="b-menu__item"><span class="b-menu__info"></span></li>
					                    </ul>
				                    </div>

                                    <div id="popup_qedit_prj_tab_payed" class="b-layout" style="display: none;">
                                        <input id="popup_qedit_prj_fld_id" type="hidden" name="id" value="" />
                                        <input id="popup_qedit_prj_fld_tmpid" type="hidden" name="tmpid" value="" />
                                        <input id="popup_qedit_prj_fld_tmpaction" type="hidden" name="tmpaction" value="" />
					                    <table class="b-layout__table" cellpadding="0" cellspacing="0" border="0">
						                    <tbody><tr class="b-layout__tr">
							                    <td class="b-layout__left b-layout__left_width_240 b-layout__left_padleft_15">
								                    <div class="b-check b-check_padbot_10">
									                    <input id="popup_qedit_prj_top_ok" class="b-check__input" name="top_ok" type="checkbox" value="1" onClick='popupQEditPrjToggleIsOntop();' />
									                    <label for="popup_qedit_prj_top_ok" class="b-check__label b-check__label_margleft_5">Закрепить наверху страницы<span id="popup_qedit_prj_top_ok_icon" class="b-check__pin" style="display:none"></span></label>
								                    </div>
                                                    <div id="popup_qedit_prj_top_ok_tab1" class="b-check b-check_margleft_25 b-check_padbot_15" style="display:none;">
                                                        <label class="b-check__label">На </label>
                                                        <div class="b-form b-form_inline-block b-check__form">
                                                            <div class="b-input b-input_inline-block"><input id="popup_qedit_prj_top_ok_tab1_days" class="b-input__text b-input__text_width_25 b-input__text_fontsize_11" name="top_days" type="text" value="1" /></div> 
                                                            <label class="b-form__name b-form__name_padtop_2">&nbsp;дней</label>
                                                        </div>
                                                    </div>
								                    <div id="popup_qedit_prj_top_ok_tab2" class="b-check b-check_margleft_25 b-check_padbot_15" style="display:none">
									                    <p id="popup_qedit_prj_top_ok_tab2_left" class="b-check__over"></p>
									                    <input id="popup_qedit_prj_top_ok_tab2_c" class="b-check__input" name="top_ok" type="checkbox" value="1" disabled="disabled" />
									                    <label for="r24" class="b-check__label">Продлить на </label>
									                    <div class="b-form b-form_inline-block b-check__form">
										                    <div class="b-input b-input_inline-block"><input id="popup_qedit_prj_top_ok_tab2_days" class="b-input__text b-input__text_width_25 b-input__text_fontsize_11" name="top_days" type="text" value="10" disabled="disabled" /></div> 
										                    <label class="b-form__name b-form__name_padtop_2" for="r26">&nbsp;дней</label>
									                    </div>
								                    </div>
                                            <?php /*
								                    <div class="b-check b-check_padbot_10">
									                    <input id="popup_qedit_prj_is_color" class="b-check__input" name="is_color" type="checkbox" value="1" onClick="popupQEditPrjToggleIsColor();" />
									                    <label for="popup_qedit_prj_is_color" class="b-check__label b-check__label_margleft_5 ">Выделить цветом</label>
								                    </div>
								                    <div class="b-check b-check_padbot_10">
									                    <input id="popup_qedit_prj_is_bold" class="b-check__input" name="is_bold" type="checkbox" value="1" onClick="popupQEditPrjToggleIsBold();" />
									                    <label for="popup_qedit_prj_is_bold" class="b-check__label b-check__label_margleft_5">Выделить <span class="">жирным</span></label>
								                    </div>
														  */ ?>
								                    <div class="b-check b-check_padbot_10">
									                    <input id="popup_qedit_prj_is_hide" class="b-check__input" name="is_hide" type="checkbox" value="1"  />
									                    <label for="popup_qedit_prj_is_hide" class="b-check__label b-check__label_margleft_5 ">Скрытый проект</label>
								                    </div>
								                    <div class="b-check b-check_padbot_10">
									                    <input id="popup_qedit_prj_is_urgent" class="b-check__input" name="is_urgent" type="checkbox" value="1"  />
									                    <label for="popup_qedit_prj_is_urgent" class="b-check__label b-check__label_margleft_5 ">Срочный проект</label>
								                    </div>
							                    </td>
							                    <td class="b-layout__right b-layout__right_width_240 b-layout__right_padleft_15">
								                    <div class="b-check b-check_padbot_5">
									                    <input id="popup_qedit_prj_use_logo" class="b-check__input" name="use_logo" type="checkbox" value="1" onClick="popupQEditPrjToggleUseLogo();" />
									                    <label for="popup_qedit_prj_use_logo" class="b-check__label b-check__label_margleft_5">Разместить логотип со ссылкой</label>
								                    </div>

								                    <div id="popup_qedit_prj_use_logo_tab" class="b-form b-form_margleft_25" style="display:none">
													    <label class="b-form__name b-form_padbot_5" for="r27">Загруженный логотип:</label>
									                    <div class="b-menu b-menu_separator b-menu_padbot_10">
										                    <ul class="b-menu__list">
											                    <li class="b-menu__item"><a id="popup_qedit_prj_use_logo_src" class="b-menu__link" href="#" target="_blank">Посмотреть</a>&nbsp;•&nbsp;</li>
											                    <li class="b-menu__item"><a class="b-menu__link b-menu__link_color_c10601" href="#" onclick="popupQEditPrjDelLogo(); return false;">Удалить</a></li>
										                    </ul>
									                    </div>
									                    <div class="b-input b-input_padbot_10">
										                    <input id="popup_qedit_prj_logolink" class="b-input__text b-input__text_width_180 b-input__text_fontsize_11" name="link" type="text" size="80" value="" />
									                    </div>
                                                    </div>

                                                    <div id="popup_qedit_prj_use_logo_tab2" class="b-form b-form_margleft_25" style="display:none">
														<div class="b-file">
															<label class="b-form__name b-form_padbot_5" for="r27">Загрузить логотип:</label>
															<table class="b-file__layout">
						      									<tr>
																	<td class="b-file__button">            
							     										<div class="b-file__wrap">
																			<input class="b-file__input" type="file" name="logo" />
																			<a class="b-button b-button_flat b-button_flat_grey" onclick="return false" href="#">Выбрать файл</a>
																		</div>
												    				</td>
																	<td class="b-file__button">
																		<a class="b-button b-button_flat b-button_flat_grey" onclick="popupQEditPrjUploadLogo(); return false;" href="#">Загрузить</a>
																	</td>
																</tr>
															</table>
															<p class="b-file__descript">Не более 50 Кб., 150 пикселей в ширину, до 150 в высоту (gif, jpeg, png).</p>
														</div>

								                    </div>

							                    </td>
						                    </tr>
					                    </tbody></table>
															
															
															<div id="popup_qedit_prj_fld_err_pay" class="b-fon b-fon_bg_ff6d2d b-fon_width_full b-fon_padbot_10" style="display:none;">
																	<b class="b-fon__b1"></b>
																	<b class="b-fon__b2"></b>
																	<div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
																	<span class="b-fon__attent"></span><div id="popup_qedit_prj_fld_err_pay_txt" class="b-fon__txt b-fon__txt_margleft_20">Ошибка</div>
																</div>
																	<b class="b-fon__b2"></b>
																	<b class="b-fon__b1"></b>
															</div>
															
															
				                    </div>

                                    <div id="popup_qedit_prj_tab_main">
    				                    <div class="b-form b-form_padleft_15">
	    				                    <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_90 b-form__name_padtop_3" for="popup_qedit_prj_fld_name">Заголовок</label>
    					                    <div class="b-input b-input_inline-block b-input_width_400">
    						                    <input id="popup_qedit_prj_fld_name" class="b-input__text" name="name" type="text" size="80" value="" />
    					                    </div>
																	
											<div id="popup_qedit_prj_fld_err_name" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_90" style="display: none">
												<b class="b-fon__b1"></b>
												<b class="b-fon__b2"></b>
												<div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
												    <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20">Поле не заполнено</div>
												</div>
												<b class="b-fon__b2"></b>
												<b class="b-fon__b1"></b>
											</div>
																	
    				                    </div>
    				                    <div class="b-form b-form_padleft_15">
    					                    <label class="b-form__name b-form__name_bold b-form__name_width_90 b-form__name_padtop_3" for="popup_qedit_prj_fld_descr">Текст</label>
    					                    <div class="b-textarea b-textarea_inline-block b-textarea_width_402">
    						                    <textarea id="popup_qedit_prj_fld_descr" class="b-textarea__textarea b-textarea__textarea__height_140" name="descr" cols="77" rows="5"></textarea>
    					                    </div>
																	
											<div id="popup_qedit_prj_fld_err_descr" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_90" style="display: none">
												<b class="b-fon__b1"></b>
												<b class="b-fon__b2"></b>
												<div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
													<span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20">Поле не заполнено</div>
												</div>
												<b class="b-fon__b2"></b>
												<b class="b-fon__b1"></b>
											</div>
																	
    				                    </div>

                                        <div id="popup_qedit_prj_attachedfiles" class="b-form b-form_relative b-form_bordbot_d7d7d7 b-form_padleft_90 b-form_padright_5 b-form_margleft_15"></div>

    				                    <div class="b-form b-form_padleft_15 b-form_padtop_10 b-form_width_500 b-form_float_left">
    					                    <label class="b-form__name b-form__name_bold b-form__name_width_90 b-form__name_padtop_3" >Раздел</label>
                                            <div id="popup_qedit_prj_fld_categories"></div>
<style type="text/css">
#popup_qedit_prj_fld_categories {display: inline-block;margin-bottom: -20px;width: 400px;}
#popup_qedit_prj_fld_categories select {display: inline;float: left;margin-right: 2px !important;vertical-align: top;width: 180px;}
#popup_qedit_prj_fld_categories #category_line a img {margin: -5px 0;border:0;}
#category_line {height: 20px;padding-bottom: 10px;}
#popup_qedit_prj_fld_location {padding-top: 10px;}
#popup_qedit_prj_fld_location select {display: inline;vertical-align: top;width: 180px;}
</style>
																	
											<div id="popup_qedit_prj_fld_err_categories" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_90" style="display: none">
												<b class="b-fon__b1"></b>
												<b class="b-fon__b2"></b>
												<div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
													<span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20">Поле не заполнено</div>
												</div>
												<b class="b-fon__b2"></b>
												<b class="b-fon__b1"></b>
											</div>
																	
																						
    				                    </div>
<div style=" position:relative; clear:both;">
										<div id="popup_qedit_prj_cal1" class="b-form b-form_padleft_15 b-form_padtop_10 b-form_width_490">
											<label class="b-form__name b-form__name_bold b-form__name_width_90" for="r8">Окончание конкурса</label>
											<div class="b-input b-input_width_180 b-input_inline-block">
												<input id="popup_qedit_prj_fld_end_date" class="b-input__text" name="end_date" type="text" size="10" maxlength="10" value="" />
												<div id="end_date_btn" class="b-input__cal"></div>
											</div>

											<div id="popup_qedit_prj_fld_err_cal1" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_90" style="display: none">
												<b class="b-fon__b1"></b>
												<b class="b-fon__b2"></b>
												<div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
													<span class="b-fon__attent"></span><div id="popup_qedit_prj_fld_err_txt_cal1" class="b-fon__txt b-fon__txt_margleft_20">Поле не заполнено</div>
												</div>
												<b class="b-fon__b2"></b>
												<b class="b-fon__b1"></b>
											</div>

										</div>
										<div id="popup_qedit_prj_cal2" class="b-form b-form_padleft_15 b-form_bordbot_d7d7d7 b-form_padtop_10 b-form_width_490">
											<label class="b-form__name b-form__name_bold b-form__name_width_90" for="r10">Объявление победителей</label>
											<div class="b-input b-input_width_180 b-input_inline-block">
												<input id="popup_qedit_prj_fld_win_date" class="b-input__text" name="win_date" type="text" size="10" maxlength="10" value="" />
												<div id="win_date_btn" class="b-input__cal"></div>
											</div>

											<div id="popup_qedit_prj_fld_err_cal2" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_90" style="display: none">
												<b class="b-fon__b1"></b>
												<b class="b-fon__b2"></b>
												<div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
													<span class="b-fon__attent"></span><div id="popup_qedit_prj_fld_err_txt_cal2" class="b-fon__txt b-fon__txt_margleft_20">Поле не заполнено</div>
												</div>
												<b class="b-fon__b2"></b>
												<b class="b-fon__b1"></b>
											</div>
										</div>
</div>

    				                    <div id="popup_qedit_prj_kind" class="b-form b-form_margleft_15 b-form_bordbot_d7d7d7 b-form_padtop_10 b-form_width_500 b-form_padright_5 b-form_relative ">
    					                    <label class="b-form__name b-form__name_bold b-form__name_width_90">Закладка</label>
    					                    <div class="b-radio b-radio_layout_vertical b-radio_inline-block">
    						                    <div class="b-radio__item b-radio__item_padbot_10">
    							                    <input id="popup_qedit_prj_fld_kind_1" class="b-radio__input" name="kind" type="radio" value="1" onClick="popupQEditPrjChangeKind();" />
    							                    <label class="b-radio__label" for="popup_qedit_prj_fld_kind_1"><span class="b-radio__bold">Проекты</span> — Разовые проекты с фиксированной оплатой</label>
    						                    </div>
    						                    <div class="b-radio__item">
    							                    <input id="popup_qedit_prj_fld_kind_2" class="b-radio__input" name="kind" type="radio" value="4" onClick="popupQEditPrjChangeKind();" />
    							                    <label class="b-radio__label" for="popup_qedit_prj_fld_kind_2"><span class="b-radio__bold">Вакансии</span> — Вакансии на постоянную или попроектную работу в офисе</label>
    						                    </div>
                                             
                                                <div id="popup_qedit_prj_fld_location" style="display: none;"></div>
    					                    </div>
																	
											<div id="popup_qedit_prj_fld_err_location" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_90" style="display: none">
												<b class="b-fon__b1"></b>
												<b class="b-fon__b2"></b>
												<div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
													<span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20">Поле не заполнено</div>
												</div>
												<b class="b-fon__b2"></b>
												<b class="b-fon__b1"></b>
											</div>
																	
																								
    				                    </div>
    				                    <div class="b-form b-form_relative b-form_margleft_15 b-form_padleft_90 b-form_padtop_20">
    					                    <div class="b-check b-check_bg_fff9e5 b-check_width_410">
    						                    <input id="popup_qedit_prj_fld_pro_only" class="b-check__input" name="pro_only" type="checkbox" value="1" />
    						                    <label for="popup_qedit_prj_fld_pro_only" class="b-check__label">Отвечать на проект могут только пользователи с аккаунтом <span class="b-icon b-icon__pro b-icon__pro_f" title="PRO"></span><br />Фрилансеры с аккаунтом <span class="b-icon b-icon__pro b-icon__pro_f" title="PRO"></span> - наиболее серьезная и активная<br />часть аудитории сайта</label>
    					                    </div>
    					                    <div class="b-check b-check_bg_fff9e5 b-check_width_410">
    						                    <input id="popup_qedit_prj_fld_verify_only" class="b-check__input" name="verify_only" type="checkbox" value="1" />
    						                    <label for="popup_qedit_prj_fld_verify_only" class="b-check__label">Отвечать на проект могут только верифицированные пользователи <span class="b-icon b-icon__ver b-icon_valign_middle"></span></label>
    					                    </div>
    					                    <div class="b-check b-check_bg_fff9e5 b-check_width_410">
    						                    <input id="popup_qedit_prj_fld_prefer_sbr" class="b-check__input" name="prefer_sbr" type="checkbox" value="1" />
    						                    <label for="popup_qedit_prj_fld_prefer_sbr" class="b-check__label">Предпочитаю Безопасную Сделку <?= view_sbr_shield('', 'b-icon_valign_middle') ?></label>
    					                    </div>
    				                    </div>
                                                    <div class="b-form b-form_relative b-form_margleft_15 b-form_padleft_90 b-form_padtop_20">
                                                        <div class="b-check b-check_width_400">
                                                            <input id="popup_qedit_prj_fld_strong_top" class="b-check__input" name="strong_top" type="checkbox" value="1" />
                                                            <label for="popup_qedit_prj_fld_strong_top" class="b-check__label">Закрепить железно наверху ленты</label>
                                                        </div>
    				                    </div> 
                                        <?/* #0019741
    				                    <div id="sbr_text_block" class="b-form b-form_relative b-form_margleft_15 b-form_padleft_90 b-form_padtop_20">
    					                    <div class="b-check b-check_width_400 b-check_bg_fff9e5" style="background-color: #d9efff">
    						                    <input id="popup_qedit_prj_fld_prefer_sbr" class="b-check__input" name="prefer_sbr" type="checkbox" value="1" />
    						                    <label for="popup_qedit_prj_fld_prefer_sbr" class="b-check__label">Чтобы обезопасить себя и сократить риски при работе <br />с фрилансерами, воспользуйтесь <a class="sbr-ic" href="/sbr/" target="_blank">Сделкой Без Риска</a></label>
    					                    </div>
    				                    </div>
                                         */ ?>
                                    </div>
					                    <div class="b-buttons b-buttons_padleft_102">
						                    <a class="b-button b-button_flat b-button_flat_green" href="#" onClick="popupQEditPrjSave(<?=$quickEditPoputType?>); return false;">Сохранить изменения</a>
						                    <a class="b-buttons__link b-buttons__link_dot_039" href="#" onClick="popupQEditPrjHide(); return false;">Закрыть без изменений</a>
					                    </div>
			                    </form>
                                <iframe id="popup_qedit_prj_upload_logo" name="popup_qedit_prj_upload_logo" style="display:none;"></iframe>
					</div>
</div>
</div>