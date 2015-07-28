		

		<div class="b-layout__txt"><a href="/siteadmin/letters/?page=tab&tab=1" class="b-layout__link">Документы</a> &rarr; <?php if($doc['group_title']) { ?><a href="/siteadmin/letters/?page=group&group=<?=$doc['group_id']?>" class="b-layout__link"><?=htmlspecialchars($doc['group_title'])?></a> &rarr;<?php } ?></div>
		<div class="b-layout__txt b-layout__txt_float_right">
			<?php
			require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");
			if($doc['file_id']) {
			$cFile = new CFile($doc['file_id']);
			?>
			<a href="<?=WDCPREFIX."/".$cFile->path.$cFile->name?>" class="b-layout__link">Электронная версия</a>
			<?php } ?>
		</div>
		<h2 class="b-layout__title b-layout__title_padbot_20 b-layout__title_margright_140">
			<?=reformat(htmlspecialchars($doc['title']),20)?>&nbsp;
			<a class="b-icon b-icon_margtop_6 b-icon_sbr_edit2" href="#" onClick="letters.editDoc(<?=$doc['id']?>); return false;"></a>
		</h2>

		<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-layout__table_margtop_15 b-layout__table_margbot_10">
			<tbody>
				<tr class="b-layout__tr">
					<td class="b-layout__one b-layout__one_width_80 b-layout__one_bordbot_double_ccc">
						<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_lineheight_20">ID</div>
					</td>
					<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_150 b-layout__one_bordbot_double_ccc">
						<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_lineheight_20">Стороны</div>
					</td>
					<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_100 b-layout__one_bordbot_double_ccc">
						<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_lineheight_20">Отправление</div>
					</td>
					<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_60 b-layout__one_bordbot_double_ccc">
						<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_lineheight_20">Стоимость</div>
					</td>
					<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_100 b-layout__one_bordbot_double_ccc">
						<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__one_padright_10 b-layout__txt_fontsize_11 b-layout__txt_lineheight_20">Статус</div>
					</td>
					<td class="b-layout__one b-layout__one_width_100 b-layout__one_padright_10 b-layout__one_bordbot_double_ccc">
						<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_lineheight_20">Изменение статуса</div>
					</td>
					<td class="b-layout__one b-layout__one_width_100 b-layout__one_bordbot_double_ccc">
						<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_lineheight_20">Создан</div>
					</td>
				</tr>
			</tbody>
		</table>



		<div class="b-fon b-fon_marglr_-10 b-fon_padbot_10">
			<div class="b-fon__body b-fon__body_pad_2_10 b-fon__body_fontsize_13 b-fon__body_bg_fff">
				<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-layout__table_margtop_15 b-layout__table_margbot_10">
					<tbody>
						<tr class="b-layout__tr">
							<td rowspan="<?=($doc['user_3'] ? '3' : '2')?>" class="b-layout__one b-layout__one_width_80">
								<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 "><?=$doc['id']?></div>
							</td>
							<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_150">
								<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11">
									<?php if($doc['is_user_1_company']=='t') { ?>
									<span class="b-icon b-icon_<?=letters::$status_icons[intval($doc['user_status_1'])]?> b-icon_margleft_-20 b-icon_top_2"></span><a href="/siteadmin/letters/?mode=edit&id=<?=$doc['user_1']?>" target="_blank" class="b-layout__link b-layout__link_fontsize_11"><?=$doc['company1']['name']?></a> <a href="#" class="b-layout__link" onClick="letters.showByUser(<?=$doc['user_1']?>, '<?=$doc['is_user_1_company']?>'); return false;"><span class="b-icon b-icon_sbr_folder b-icon_valign_middle"></span></a>
									<br>
                                    <?=$doc['company1']['index']? ($doc['company1']['index'] . ','): ''?>
                                    <?=$doc['company1']['country_title']? ($doc['company1']['country_title'] . ','): ''?>
                                    <?=$doc['company1']['city_title']? ($doc['company1']['city_title'] . ','): ''?>
                                    <?=$doc['company1']['address']?>
									<?php } else { ?>
									<span class="b-icon b-icon_<?=letters::$status_icons[intval($doc['user_status_1'])]?> b-icon_margleft_-20 b-icon_top_2"></span><a href="/users/<?=$doc['user1_login']?>" target="_blank" class="b-layout__link b-layout__link_fontsize_11"><?=($doc['user1_i']['form_type']==1 ? $doc['user1_i'][1]['fio'] : $doc['user1_i'][2]['full_name'])?> [<?=$doc['user1_login']?>]</a> <a href="#" class="b-layout__link" onClick="letters.showByUser(<?=$doc['user_1']?>, '<?=$doc['is_user_1_company']?>'); return false;"><span class="b-icon b-icon_sbr_folder b-icon_valign_middle"></span></a>
									<br>
                                    <?/*=($doc['user1_i']['form_type']==1 ? ($doc['user1_i'][1]['index']? "{$doc['user1_i'][1]['index']},": "") : ($doc['user1_i'][2]['index']? "{$doc['user1_i'][2]['index']},": ""))?>
                                    <?=($doc['user1_i']['form_type']==1 ? ($doc['user1_i'][1]['country']? "{$doc['user1_i'][1]['country']},": "") : ($doc['user1_i'][2]['country']? "{$doc['user1_i'][2]['country']},": ""))?>
                                    <?=($doc['user1_i']['form_type']==1 ? ($doc['user1_i'][1]['city']? "{$doc['user1_i'][1]['city']},": "") : ($doc['user1_i'][2]['city']? "{$doc['user1_i'][2]['city']},": ""))*/?>
                                    <?=($doc['user1_i']['form_type']==1 ? $doc['user1_i'][1]['address'] : $doc['user1_i'][2]['address'])?>
									<?php } ?>
								</div>
							</td>
							<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_100">
								<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 " id="letters_item_delivery_<?=$doc['id']?>">
									<a href="#" onClick="letters.formDeliveryShow(<?=$doc['id']?>); return false;" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_41">
										<?php if($doc['delivery_title']) { ?>
											<?=$doc['delivery_title']?>
										<?php } else { ?>
											Нет
										<?php } ?>
									</a>
								</div>
							</td>
							<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_60">
								<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 " id="letters_item_deliverycost_<?=$doc['id']?>">
									<a href="#" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_41" onClick="letters.formDeliveryCostShow(<?=$doc['id']?>, 'view'); return false;">
										<?php if($doc['delivery_cost']) { ?>
											<?=sprintf("%01.2f", $doc['delivery_cost'])?> руб.
										<?php } else { ?>
											Нет
										<?php } ?>
									</a>
								</div>
							</td>
							<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_100">
								<div id="letters_item_status_1_<?=$doc['id']?>" class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11">
									<!--
									<a href="#" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_<?=letters::$status_colors[intval($doc['user_status_1'])]?>" onClick="letters.formStatusShow(<?=$doc['id']?>,1); return false;">
										<?=$statuses[intval($doc['user_status_1'])]?> 
										<?php if($doc['user_status_1']==2 || $doc['user_status_1']==3) { ?>
											<?=dateFormat("d.m.Y", $doc['user_status_date_1'])?>
										<?php } ?>
									</a>
									-->
								</div>
							</td>
							<td class="b-layout__one b-layout__one_width_100 b-layout__one_padright_10">
								<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 " id="letters_item_datechange_<?=$doc['id']?>">
									<a href="#" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_41" onClick="letters.formDateChangeShow(<?=$doc['id']?>, 'view'); return false;">
										<?=dateFormat("d.m.Y, H:i", $doc['date_change_status'])?>
									</a>
								</div>
							</td>
							<td class="b-layout__one b-layout__one_width_100">
								<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 " id="letters_item_dateadd_<?=$doc['id']?>">
									<a href="#" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_41" onClick="letters.formDateAddShow(<?=$doc['id']?>, 'view'); return false;">
										<?=dateFormat("d.m.Y, H:i", $doc['date_add'])?>
									</a>
									<br><a href="/users/<?=$doc['useradd_login']?>/" target="_blank" class="b-layout__link b-layout__link_fontsize_11">[<?=$doc['useradd_login']?>]</a>
								</div>
							</td>
						</tr>
					    <tr class="b-layout__tr">
							<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_150">
								<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11">
									<?php if($doc['is_user_2_company']=='t') { ?>
									<span class="b-icon b-icon_<?=letters::$status_icons[intval($doc['user_status_2'])]?> b-icon_margleft_-20 b-icon_top_2"></span><a href="/siteadmin/letters/?mode=edit&id=<?=$doc['user_2']?>" target="_blank" class="b-layout__link b-layout__link_fontsize_11"><?=$doc['company2']['name']?></a> <a href="#" class="b-layout__link" onClick="letters.showByUser(<?=$doc['user_2']?>, '<?=$doc['is_user_2_company']?>'); return false;"><span class="b-icon b-icon_sbr_folder b-icon_valign_middle"></span></a>
									<br>
                                    <?=$doc['company2']['index']? ($doc['company2']['index'] . ','): ''?>
                                    <?=$doc['company2']['country_title']? ($doc['company2']['country_title'] . ','): ''?>
                                    <?=$doc['company2']['city_title']? ($doc['company2']['city_title'] . ','): ''?>
                                    <?=$doc['company2']['address']?>
									<?php } else { ?>
									<span class="b-icon b-icon_<?=letters::$status_icons[intval($doc['user_status_2'])]?> b-icon_margleft_-20 b-icon_top_2"></span><a href="/users/<?=$doc['user2_login']?>" target="_blank" class="b-layout__link b-layout__link_fontsize_11"><?=($doc['user2_i']['form_type']==1 ? $doc['user2_i'][1]['fio'] : $doc['user2_i'][2]['full_name'])?> [<?=$doc['user2_login']?>]</a> <a href="#" class="b-layout__link" onClick="letters.showByUser(<?=$doc['user_2']?>, '<?=$doc['is_user_2_company']?>'); return false;"><span class="b-icon b-icon_sbr_folder b-icon_valign_middle"></span></a>
									<br>
                                    <?/*=($doc['user2_i']['form_type']==1 ? ($doc['user2_i'][1]['index']? "{$doc['user2_i'][1]['index']},": "") : ($doc['user2_i'][2]['index']? "{$doc['user2_i'][2]['index']},": ""))?>
                                    <?=($doc['user2_i']['form_type']==1 ? ($doc['user2_i'][1]['country']? "{$doc['user2_i'][1]['country']},": "") : ($doc['user2_i'][2]['country']? "{$doc['user2_i'][2]['country']},": ""))?>
                                    <?=($doc['user2_i']['form_type']==1 ? ($doc['user2_i'][1]['city']? "{$doc['user2_i'][1]['city']},": "") : ($doc['user2_i'][2]['city']? "{$doc['user2_i'][2]['city']},": ""))*/?>
                                    <?=($doc['user2_i']['form_type']==1 ? $doc['user2_i'][1]['address'] : $doc['user2_i'][2]['address'])?>
									<?php } ?>
								</div>
                            </td>
							<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_100">
								<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 ">&nbsp;</div>
							</td>
							<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_60">
								<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 ">&nbsp;</div>
							</td>
							<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_100">
								<div id="letters_item_status_2_<?=$doc['id']?>" class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11">
									<a href="#" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_<?=letters::$status_colors[intval($doc['user_status_2'])]?>" onClick="letters.formStatusShow(<?=$doc['id']?>,2); return false;">
										<?=$statuses[intval($doc['user_status_2'])]?> 
										<?php if($doc['user_status_2']==2 || $doc['user_status_2']==3) { ?>
											<?=dateFormat("d.m.Y", $doc['user_status_date_2'])?>
										<?php } ?>
									</a>
								</div>
							</td>
							<td class="b-layout__one b-layout__one_width_100 b-layout__one_padright_10">
								<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 ">&nbsp;</div>
							</td>
							<td class="b-layout__one b-layout__one_width_100">
								<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 ">&nbsp;</div>
							</td>
				    	</tr>
				    	<?php if($doc['user_3']) { ?>
					    <tr class="b-layout__tr">
							<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_150">
								<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11">
									<?php if($doc['is_user_3_company']=='t') { ?>
									<span class="b-icon b-icon_<?=letters::$status_icons[intval($doc['user_status_3'])]?> b-icon_margleft_-20 b-icon_top_2"></span><a href="/siteadmin/letters/?mode=edit&id=<?=$doc['user_3']?>" target="_blank" class="b-layout__link b-layout__link_fontsize_11"><?=$doc['company3']['name']?></a> <a href="#" class="b-layout__link" onClick="letters.showByUser(<?=$doc['user_3']?>, '<?=$doc['is_user_3_company']?>'); return false;"><span class="b-icon b-icon_sbr_folder b-icon_valign_middle"></span></a>
									<br>
                                    <?=$doc['company3']['index']? ($doc['company3']['index'] . ','): ''?>
                                    <?=$doc['company3']['country_title']? ($doc['company3']['country_title'] . ','): ''?>
                                    <?=$doc['company3']['city_title']? ($doc['company3']['city_title'] . ','): ''?>
                                    <?=$doc['company3']['address']?>
									<?php } else { ?>
									<span class="b-icon b-icon_<?=letters::$status_icons[intval($doc['user_status_3'])]?> b-icon_margleft_-20 b-icon_top_2"></span><a href="/users/<?=$doc['user3_login']?>" target="_blank" class="b-layout__link b-layout__link_fontsize_11"><?=($doc['user3_i']['form_type']==1 ? $doc['user3_i'][1]['fio'] : $doc['user3_i'][2]['full_name'])?> [<?=$doc['user3_login']?>]</a> <a href="#" class="b-layout__link" onClick="letters.showByUser(<?=$doc['user_1']?>, '<?=$doc['is_user_3_company']?>'); return false;"><span class="b-icon b-icon_sbr_folder b-icon_valign_middle"></span></a>
									<br>
                                    <?/*=($doc['user3_i']['form_type']==1 ? ($doc['user3_i'][1]['index']? "{$doc['user3_i'][1]['index']},": "") : ($doc['user3_i'][2]['index']? "{$doc['user3_i'][2]['index']},": ""))?>
                                    <?=($doc['user3_i']['form_type']==1 ? ($doc['user3_i'][1]['country']? "{$doc['user3_i'][1]['country']},": "") : ($doc['user3_i'][2]['country']? "{$doc['user3_i'][2]['country']},": ""))?>
                                    <?=($doc['user3_i']['form_type']==1 ? ($doc['user3_i'][1]['city']? "{$doc['user3_i'][1]['city']},": "") : ($doc['user3_i'][2]['city']? "{$doc['user3_i'][2]['city']},": ""))*/?>
                                    <?=($doc['user3_i']['form_type']==1 ? $doc['user3_i'][1]['address'] : $doc['user3_i'][2]['address'])?>
									<?php } ?>
								</div>
                            </td>
							<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_100">
								<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 ">&nbsp;</div>
							</td>
							<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_60">
								<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 ">&nbsp;</div>
							</td>
							<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_100">
								<div id="letters_item_status_3_<?=$doc['id']?>" class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11">
									<a href="#" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_<?=letters::$status_colors[intval($doc['user_status_3'])]?>" onClick="letters.formStatusShow(<?=$doc['id']?>,3); return false;">
										<?=$statuses[intval($doc['user_status_3'])]?> 
										<?php if($doc['user_status_3']==2 || $doc['user_status_3']==3) { ?>
											<?=dateFormat("d.m.Y", $doc['user_status_date_3'])?>
										<?php } ?>
									</a>
								</div>
							</td>
							<td class="b-layout__one b-layout__one_width_100 b-layout__one_padright_10">
								<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 ">&nbsp;</div>
							</td>
							<td class="b-layout__one b-layout__one_width_100">
								<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 ">&nbsp;</div>
							</td>
				    	</tr>
				    	<?php } ?>
					</tbody>
				</table>	
				<div class="b-layout__txt b-layout__txt_padbot_15 b-layout__txt_padleft_80">
					<?php if($doc['parent'] && $doc['parent_title']) { ?>
					Документ связан с <a href="/siteadmin/letters/?page=doc&doc=<?=$doc['parent']?>" class="b-layout__link b-layout__link_color_000">ID<?=$doc['parent']?> <?=reformat(htmlspecialchars($doc['parent_title']),20)?></a><br><br>
					<?php } ?>

					<span id="letters_item_comment_<?=$doc['id']?>">
					<?php if($doc['comment']) {?>
						<?=reformat(htmlspecialchars($doc['comment']),20)?>&nbsp;&nbsp;<a class="b-icon b-icon_margtop_4 b-icon_sbr_edit2" href="#" onClick="letters.formCommentShow(<?=$doc['id']?>); return false;"></a>
					<?php } else { ?>
						<a href="#" class="b-layout__link b-layout__link_bordbot_dot_000" onClick="letters.formCommentShow(<?=$doc['id']?>); return false;">Добавить примечание</a>
					<?php } ?>
					</span>
				</div>
			</div>
		</div>	
	</div>


	<?php 
	if($history) { 
	?>
	<div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_5 b-layout__txt_bold">История документа</div>
	<?php
		$n = 0;
		foreach($history as $ihistory) {
			$old_value = '';
			$new_value = '';
	?>
	<div class="b-fon b-fon_marglr_-10 b-fon_padbot_10">
		<div class="b-fon__body b-fon__body_pad_2_10 b-fon__body_fontsize_13 <?=($n==0 ? 'b-fon__body_bg_f0ffdf' : '')?>">
			<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11"><?=dateFormat("d.m.Y, H:i", $ihistory['change_date'])?> <a href="/users/<?=$ihistory['user_login']?>/" target="_blank" class="b-layout__link b-layout__link_fontsize_11">[<?=$ihistory['user_login']?>]</a> изменил <?=letters::$history_fields[$ihistory['type_field']]?>:</div>
			<div class="b-layout__txt b-layout__txt_padbot_5">
				<?php
				switch($ihistory['type_field']) {
					case '9':
						if($ihistory['val_old']) {
							$old_value = reformat(htmlspecialchars($ihistory['val_old']),20);
						} else { 
							$old_value = 'Нет';
						}
						if($ihistory['val_new']) {
							$new_value = reformat(htmlspecialchars($ihistory['val_new']),20);
						} else { 
							$new_value = 'Нет';
						}
						break;
					case '2':
						if($ihistory['val_old']) {
							$old_value = reformat(htmlspecialchars($ihistory['val_old']),20);
						} else { 
							$old_value = 'Нет';
						}
						if($ihistory['val_new']) {
							$new_value = reformat(htmlspecialchars($ihistory['val_new']),20);
						} else { 
							$new_value = 'Нет';
						}
						break;
					case '10':
						if($ihistory['val_old']) {
							$old_value = sprintf("%01.2f", $ihistory['val_old']).' руб.';
						} else { 
							$old_value = 'Нет';
						}
						if($ihistory['val_new']) {
							$new_value = sprintf("%01.2f", $ihistory['val_new']).' руб.';
						} else { 
							$new_value = 'Нет';
						}
						break;
					case '6':
					case '7':
					case '8':
						if($ihistory['val_old']) {
							$old_value = $ihistory['val_old'];
						} else { 
							$old_value = 'Не выбрано';
						}
						if($ihistory['val_new']) {
							$new_value = $ihistory['val_new'];
						} else { 
							$new_value = 'Не выбрано';
						}
						break;
					case '3':
					case '4':
					case '5':
						if($ihistory['val_old']) {
							list($v1, $v2) = preg_split("/-/",$ihistory['val_old']);
							if(intval($v2)==0) {
								$huser = new users();
	    						$huser->GetUserByUID(intval($v1));
	    						$old_value = $huser->uname.' '.$huser->usurname.' ['.$huser->login.']';
	    					} else {
	    						$company = letters::getCompany(intval($v1));
	    						$old_value = $company['name'];
	    					}
						} else {
							$old_value = "Нет";
						}
						if($ihistory['val_new']) {
							list($v1, $v2) = preg_split("/-/",$ihistory['val_new']);
							if(intval($v2)==0) {
								$huser = new users();
	    						$huser->GetUserByUID(intval($v1));
	    						$new_value = $huser->uname.' '.$huser->usurname.' ['.$huser->login.']';
	    					} else {
	    						$company = letters::getCompany(intval($v1));
	    						$new_value = $company['name'];
	    					}
						} else {
							$new_value = "Нет";
						}
						break;
					default:
						$old_value = reformat(htmlspecialchars($ihistory['val_old']),20);
						$new_value = reformat(htmlspecialchars($ihistory['val_new']),20);
						break;
				}
				?>
				<?=$old_value?> &rarr; <?=$new_value?>
			</div>
		</div>	
	</div>
	<?php
		$n = ($n==0 ? 1 : 0);
		}
	}
	?>



	<div class="b-buttons b-buttons_padtop_30">
		<a href="#" class="b-button b-button_flat b-button_flat_red" onClick="if(confirm('Вы действительно хотите удалить документ?')) { letters.delDocument(<?=$doc['id']?>) } return false;">Удалить документ</a>	
	</div>