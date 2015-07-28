
	<div class="b-fon__body b-fon__body_pad_2_10 b-fon__body_fontsize_13 <?=(($letter['user_status_1']==1 || $letter['user_status_2']==1 || $letter['user_status_3']==1 || $letter['user_status_1']==2 || $letter['user_status_2']==2 || $letter['user_status_3']==2 || $letter['user_status_1']==3 || $letter['user_status_2']==3 || $letter['user_status_3']==3) ? 'b-fon__body_bg_f0ffdf' : 'b-fon__body_bg_fff b-fon__body_bordbot_edddda')?>">
		<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full">
			<tbody>
				<tr class="b-layout__tr">
					<td rowspan="<?=($letter['user_3'] ? '3' : '2')?>" class="b-layout__one b-layout__one_width_30">
						<div class="b-check b-check_margtop_5">
							<input id="letters_check_<?=$letter['id']?>" numcover="<?=$letter['number']?>" class="b-check__input" <?=($type==2 || $type==6 ? 'u_res="'.$ukey[0].'"' : '')?> <?=($type==2 || $type==6 ? 'u_delivery="'.$oletter[0]['delivery'].'"' : '')?> type="checkbox" value="<?=$letter['id']?>" onClick="letters.checkUncheck(this);">
						</div>
					</td>
					<td rowspan="<?=($letter['user_3'] ? '3' : '2')?>" class="b-layout__one b-layout__one_width_50">
						<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_lineheight_20"><?=$letter['id']?></div>
					</td>
					<td rowspan="<?=($letter['user_3'] ? '3' : '2')?>" class="b-layout__one b-layout__one_padright_30">
						<div class="b-layout__txt b-layout__txt_padbot_5"><?php if($letter['group_title']) { ?><a href="/siteadmin/letters/?page=group&group=<?=$letter['group_id']?>" class="b-layout__link b-layout__link_color_000 b-layout__link_bold"><?=reformat(htmlspecialchars($letter['group_title']),20)?></a> &rarr; <?php } ?><a href="/siteadmin/letters/?page=doc&doc=<?=$letter['id']?>" class="b-layout__link b-layout__link_bold"><?=reformat(htmlspecialchars($letter['title']),20)?></a></div>
					</td>
					<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_150">
						<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_padtop_2">
							<?php if($letter['is_user_1_company']=='t') { ?>
							<span class="b-icon b-icon_<?=letters::$status_icons[intval($letter['user_status_1'])]?> b-icon_margleft_-20 b-icon_top_2"></span><a href="/siteadmin/letters/?mode=edit&id=<?=$letter['user_1']?>" target="_blank" class="b-layout__link b-layout__link_fontsize_11"><?=$letter['company1_name']?></a> <a href="#" class="b-layout__link" onClick="letters.showByUser(<?=$letter['user_1']?>, '<?=$letter['is_user_1_company']?>'); return false;"><span class="b-icon b-icon_sbr_folder b-icon_valign_middle"></span></a>
							<br>
							<?=$letter['company1']['index']? ($letter['company1']['index'] . ','): ''?>
                            <?=$letter['company1']['country_title']? ($letter['company1']['country_title'] . ','): ''?>
                            <?=$letter['company1']['city_title']? ($letter['company1']['city_title'] . ','): ''?>
							<?=$letter['company1']['address']?>
							<?php } else { ?>
							<span class="b-icon b-icon_<?=letters::$status_icons[intval($letter['user_status_1'])]?> b-icon_margleft_-20 b-icon_top_2"></span><a href="/users/<?=$letter['user1_login']?>" target="_blank" class="b-layout__link b-layout__link_fontsize_11"><?=($letter['user1_i']['form_type']==1 ? $letter['user1_i'][1]['fio'] : $letter['user1_i'][2]['full_name'])?> [<?=$letter['user1_login']?>]</a> <a href="#" class="b-layout__link" onClick="letters.showByUser(<?=$letter['user_1']?>, '<?=$letter['is_user_1_company']?>'); return false;"><span class="b-icon b-icon_sbr_folder b-icon_valign_middle"></span></a>
							<br>
							<?/*=($letter['user1_i']['form_type']==1 ? ($letter['user1_i'][1]['index']? "{$letter['user1_i'][1]['index']},": "") : ($letter['user1_i'][2]['index']? "{$letter['user1_i'][2]['index']},": ""))?>
                            <?=($letter['user1_i']['form_type']==1 ? ($letter['user1_i'][1]['country']? "{$letter['user1_i'][1]['country']},": "") : ($letter['user1_i'][2]['country']? "{$letter['user1_i'][2]['country']},": ""))?>
                            <?=($letter['user1_i']['form_type']==1 ? ($letter['user1_i'][1]['city']? "{$letter['user1_i'][1]['city']},": "") : ($letter['user1_i'][2]['city']? "{$letter['user1_i'][2]['city']},": ""))*/?>
							<?=($letter['user1_i']['form_type']==1 ? $letter['user1_i'][1]['address'] : $letter['user1_i'][2]['address'])?>
							<?php } ?>
						</div>
					</td>
					<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_100">
						<div id="letters_item_status_1_<?=$letter['id']?><?=($type==2 || $type==6 ? "_{$nn}" : "")?>" class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_padtop_2 b-layout__txt_fontsize_11" style="visibility: hidden">
							<a href="#" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_<?=letters::$status_colors[intval($letter['user_status_1'])]?>" onClick="letters.nn=<?=$nn?>; letters.formStatusShow(<?=$letter['id']?>,1); return false;">
								<?=$statuses[intval($letter['user_status_1'])]?> 
								<?php if($letter['user_status_1']==2 || $letter['user_status_1']==3) { ?>
									<?=dateFormat("d.m.Y", $letter['user_status_date_1'])?>
								<?php } ?>
							</a>
						</div>
					</td>
					<td class="b-layout__one b-layout__one_width_110 ">
						<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_padtop_2" id="letters_item_datechange_<?=$letter['id']?><?=($type==2 || $type==6 ? "_{$nn}" : "")?>">
							<a href="#" class="b-layout__link b-layout__link_bordbot_dot_000" onClick="letters.nn=<?=$nn?>; letters.formDateChangeShow(<?=$letter['id']?>, 'list'); return false;">
								<?=dateFormat("d.m.Y, H:i", $letter['date_change_status'])?>
							</a>
						</div>
					</td>
				</tr>
 			    <tr class="b-layout__tr">
				    <td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_150">
						<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_padtop_2">
							<?php if($letter['is_user_2_company']=='t') { ?>
							<span class="b-icon b-icon_<?=letters::$status_icons[intval($letter['user_status_2'])]?> b-icon_margleft_-20 b-icon_top_2"></span><a href="/siteadmin/letters/?mode=edit&id=<?=$letter['user_2']?>" target="_blank" class="b-layout__link b-layout__link_fontsize_11"><?=$letter['company2_name']?></a> <a href="#" class="b-layout__link" onClick="letters.showByUser(<?=$letter['user_2']?>, '<?=$letter['is_user_2_company']?>'); return false;"><span class="b-icon b-icon_sbr_folder b-icon_valign_middle"></span></a>
							<br>
							<?=$letter['company2']['index']? ($letter['company2']['index'] . ','): ''?>
                            <?=$letter['company2']['country_title']? ($letter['company2']['country_title'] . ','): ''?>
                            <?=$letter['company2']['city_title']? ($letter['company2']['city_title'] . ','): ''?>
							<?=$letter['company2']['address']?>
							<?php } else { ?>
							<span class="b-icon b-icon_<?=letters::$status_icons[intval($letter['user_status_2'])]?> b-icon_margleft_-20 b-icon_top_2"></span><a href="/users/<?=$letter['user2_login']?>" target="_blank" class="b-layout__link b-layout__link_fontsize_11"><?=($letter['user2_i']['form_type']==1 ? $letter['user2_i'][1]['fio'] : $letter['user2_i'][2]['full_name'])?> [<?=$letter['user2_login']?>]</a> <a href="#" class="b-layout__link" onClick="letters.showByUser(<?=$letter['user_2']?>, '<?=$letter['is_user_2_company']?>'); return false;"><span class="b-icon b-icon_sbr_folder b-icon_valign_middle"></span></a>
							<br>
							<?/*=($letter['user2_i']['form_type']==1 ? ($letter['user2_i'][1]['index']? "{$letter['user2_i'][1]['index']},": "") : ($letter['user2_i'][2]['index']? "{$letter['user2_i'][2]['index']},": ""))?>
                            <?=($letter['user2_i']['form_type']==1 ? ($letter['user2_i'][1]['country']? "{$letter['user2_i'][1]['country']},": "") : ($letter['user2_i'][2]['country']? "{$letter['user2_i'][2]['country']},": ""))?>
                            <?=($letter['user2_i']['form_type']==1 ? ($letter['user2_i'][1]['city']? "{$letter['user2_i'][1]['city']},": "") : ($letter['user2_i'][2]['city']? "{$letter['user2_i'][2]['city']},": ""))*/?>
							<?=($letter['user2_i']['form_type']==1 ? $letter['user2_i'][1]['address'] : $letter['user2_i'][2]['address'])?>
							<?php } ?>
						</div>
                    </td>
					<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_100">
						<div id="letters_item_status_2_<?=$letter['id']?><?=($type==2 || $type==6 ? "_{$nn}" : "")?>" class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_padtop_2 b-layout__txt_fontsize_11">
							<a href="#" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_<?=letters::$status_colors[intval($letter['user_status_2'])]?>" onClick="letters.nn=<?=$nn?>; letters.formStatusShow(<?=$letter['id']?>,2); return false;">
								<?=$statuses[intval($letter['user_status_2'])]?> 
								<?php if($letter['user_status_2']==2 || $letter['user_status_2']==3) { ?>
									<?=dateFormat("d.m.Y", $letter['user_status_date_2'])?>
								<?php } ?>
							</a>
						</div>
					</td>
					<td class="b-layout__one b-layout__one_width_110 ">
						<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_padtop_2">&nbsp;</div>
					</td>
		        </tr>
		        <?php if($letter['user_3']) { ?>
				<tr class="b-layout__tr">
				    <td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_150">
						<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_padtop_2">
							<?php if($letter['is_user_3_company']=='t') { ?>
							<span class="b-icon b-icon_<?=letters::$status_icons[intval($letter['user_status_3'])]?> b-icon_margleft_-20 b-icon_top_2"></span><a href="/siteadmin/letters/?mode=edit&id=<?=$letter['user_3']?>" target="_blank" class="b-layout__link b-layout__link_fontsize_11"><?=$letter['company3_name']?></a> <a href="#" class="b-layout__link" onClick="letters.showByUser(<?=$letter['user_3']?>, '<?=$letter['is_user_3_company']?>'); return false;"><span class="b-icon b-icon_sbr_folder b-icon_valign_middle"></span></a>
							<br>
							<?=$letter['company3']['index']? ($letter['company3']['index'] . ','): ''?>
                            <?=$letter['company3']['country_title']? ($letter['company3']['country_title'] . ','): ''?>
                            <?=$letter['company3']['city_title']? ($letter['company3']['city_title'] . ','): ''?>
							<?=$letter['company3']['address']?>
							<?php } else { ?>
							<span class="b-icon b-icon_<?=letters::$status_icons[intval($letter['user_status_3'])]?> b-icon_margleft_-20 b-icon_top_2"></span><a href="/users/<?=$letter['user3_login']?>" target="_blank" class="b-layout__link b-layout__link_fontsize_11"><?=($letter['user3_i']['form_type']==1 ? $letter['user3_i'][1]['fio'] : $letter['user3_i'][2]['full_name'])?> [<?=$letter['user3_login']?>]</a> <a href="#" class="b-layout__link" onClick="letters.showByUser(<?=$letter['user_3']?>, '<?=$letter['is_user_3_company']?>'); return false;"><span class="b-icon b-icon_sbr_folder b-icon_valign_middle"></span></a>
							<br>
							<?/*=($letter['user3_i']['form_type']==1 ? ($letter['user3_i'][1]['index']? "{$letter['user3_i'][1]['index']},": "") : ($letter['user3_i'][2]['index']? "{$letter['user3_i'][2]['index']},": ""))?>
                            <?=($letter['user3_i']['form_type']==1 ? ($letter['user3_i'][1]['country']? "{$letter['user3_i'][1]['country']},": "") : ($letter['user3_i'][2]['country']? "{$letter['user3_i'][2]['country']},": ""))?>
                            <?=($letter['user3_i']['form_type']==1 ? ($letter['user3_i'][1]['city']? "{$letter['user3_i'][1]['city']},": "") : ($letter['user3_i'][2]['city']? "{$letter['user3_i'][2]['city']},": ""))*/?>
							<?=($letter['user3_i']['form_type']==1 ? $letter['user3_i'][1]['address'] : $letter['user3_i'][2]['address'])?>
							<?php } ?>
						</div>
                    </td>
					<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_100">
						<div id="letters_item_status_3_<?=$letter['id']?><?=($type==2 || $type==6 ? "_{$nn}" : "")?>" class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_padtop_2 b-layout__txt_fontsize_11">
							<a href="#" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_<?=letters::$status_colors[intval($letter['user_status_3'])]?>" onClick="letters.nn=<?=$nn?>; letters.formStatusShow(<?=$letter['id']?>,3); return false;">
								<?=$statuses[intval($letter['user_status_3'])]?> 
								<?php if($letter['user_status_3']==2 || $letter['user_status_3']==3) { ?>
									<?=dateFormat("d.m.Y", $letter['user_status_date_3'])?>
								<?php } ?>
							</a>
						</div>
					</td>
					<td class="b-layout__one b-layout__one_width_110 ">
						<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_padtop_2">&nbsp;</div>
					</td>
 		     	</tr>
 		     	<?php } ?>
			</tbody>
		</table>
		<div class="b-layout__txt b-layout__txt_padbot_15 b-layout__txt_padleft_80">
			<span id="letters_item_delivery_<?=$letter['id']?><?=($type==2 || $type==6 ? "_{$nn}" : "")?>">
				<?php if($letter['delivery_title']) { ?>
					<a href="#" class="b-layout__link b-layout__link_bordbot_dot_000" onClick="letters.nn=<?=$nn?>; letters.formDeliveryShow(<?=$letter['id']?>); return false;"><?=$letter['delivery_title']?></a>. 
				<?php } else { ?>
					<a href="#" class="b-layout__link b-layout__link_bordbot_dot_000" onClick="letters.nn=<?=$nn?>; letters.formDeliveryShow(<?=$letter['id']?>); return false;">Добавить доставку</a>. 
				<?php } ?>
			</span>
			<span id="letters_item_deliverycost_<?=$letter['id']?><?=($type==2 || $type==6 ? "_{$nn}" : "")?>">
			<?php if($letter['delivery_cost']) { ?>
				<a href="#" class="b-layout__link b-layout__link_bordbot_dot_000" onClick="letters.nn=<?=$nn?>; letters.formDeliveryCostShow(<?=$letter['id']?>, 'list'); return false;">Стоимость <?=sprintf("%01.2f", $letter['delivery_cost'])?> рублей</a>. 
			<?php } else { ?>
				<a href="#" class="b-layout__link b-layout__link_bordbot_dot_000" onClick="letters.nn=<?=$nn?>; letters.formDeliveryCostShow(<?=$letter['id']?>, 'list'); return false;">Добавить стоимость</a>.
			<?php } ?>
			</span>
			<br/>
			<?php if($letter['parent'] && $letter['parent_title']) { ?>
			Документ связан с <a href="/siteadmin/letters/?page=doc&doc=<?=$letter['parent']?>" class="b-layout__link b-layout__link_color_000">ID<?=$letter['parent']?> <?=reformat(htmlspecialchars($letter['parent_title']),20)?></a><br/><br/>
			<?php } else { ?>
			<br/>
			<?php } ?>

			<span id="letters_item_comment_<?=$letter['id']?><?=($type==2 || $type==6 ? "_{$nn}" : "")?>">
			<?php if($letter['comment']) {?>
				<?=reformat(htmlspecialchars($letter['comment']),20)?>&nbsp;&nbsp;<a class="b-icon b-icon_margtop_4 b-icon_sbr_edit2" href="#" onClick="letters.nn=<?=$nn?>; letters.formCommentShow(<?=$letter['id']?>); return false;"></a>
			<?php } else { ?>
				<a href="#" class="b-layout__link b-layout__link_bordbot_dot_000" onClick="letters.nn=<?=$nn?>; letters.formCommentShow(<?=$letter['id']?>); return false;">Добавить примечание</a>
			<?php } ?>
			</span>

			<?php if($letter['file_id']) { ?>
			<br/>
			<span>
				<?php
				require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");
				$cFile = new CFile($letter['file_id']);
				?>
				<a href="<?=WDCPREFIX."/".$cFile->path.$cFile->name?>" class="b-layout__link b-layout__link_bordbot_dot_000">Электронная версия</a>
			</span>
			<?php } ?>
		</div>
	</div>