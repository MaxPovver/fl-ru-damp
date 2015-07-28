               
               
			<? seo_start($is_ajax)?>
			<? if ($row['cost']) {
					$priceby_str = getPricebyProject($row['priceby']);
					if($row['cost']=='' || $row['cost']==0) { $priceby_str = ""; }
			?>
			<div class="b-post__price b-post__price_padleft_10 b-post__price_padbot_5 b-post__price_fontsize_15 b-post__price_bold b-post__price_float_right">
					<?php
         
					 if($can_change_prj) { ?><a 
							id="prj_budget_lnk_<?=$row['id']?>" class="b-post__link  b-post__link_dot_6db335" href="#" onClick="popupShowChangeBudget(<?=$row['id']?>, '<?=$row['cost']?>', '<?=$row['currency']?>', '<?=$row['priceby']?>', false, <?=$row['id']?>, 1); return false;"><?= CurToChar($row['cost'], $row['currency']) ?><?=$priceby_str?></a>
					<? } else { ?><?= CurToChar($row['cost'], $row['currency']) ?><?=$priceby_str?>
					<? } ?>
			</div>
			<? } else { ?>
			<div class="b-post__price b-post__price_padleft_10 b-post__price_padbot_5 b-post__price_fontsize_13 b-post__price_float_right">
					<?php
					 if($can_change_prj) { ?><a id="prj_budget_lnk_<?=$row['id']?>" class="b-post__link b-post__link_dot_6db335" href="#" onClick="popupShowChangeBudget(<?=$row['id']?>, '', 2, 1, true, <?=$row['id']?>, 1); return false;">По договоренности</a>
					<? } else { ?>По договоренности
					<? } ?>
					</div>
			<? } ?>
			<?= seo_end(false, $is_ajax)?>
			
			
			
			<h2 class="b-post__title b-post__title_inline <?php if ($row['t_is_ontop'] || $row['strong_top'] == 1) { ?>b-post__pin<?php }?>" >
            <? if ($row['urgent'] == 't') { ?><img class="b-pic b-pic_margtop_1" src="<?=WDCPREFIX?>/images/urgently.png" width="80" height="22"><?php }?>
                    <?php $sTitle = $row['moderator_status'] === '0' && $row['kind'] != 4 && $row['is_pro'] != 't' ? $stop_words->replace($row['name']) : $row['name']; ?>
					<a class="b-post__link" id="prj_name_<?=$row['id']?>" name="prj<?= $row['id'] ?>" href="<?= $row['friendly_url'] ?>"><?= reformat2(strip_tags($sTitle),30,0,1) ?></a>
			</h2>
			<? /*if (get_uid(false) && $row['t_is_ontop'] && !is_emp()) { ?>
			<a href="#" title="Скрыть" onclick="xajax_HideProject(<?= $row['id'] ?>, 'hide', '<?= $this->kind ?>', '<?= $this->page ?>', '<?= $this->filter ?>'); return false;" class="b-post__link b-post__link_dot_c10601">скрыть</a>
			<? } */?>

			<? seo_start($is_ajax)?>
			

    <div class="b-post__body b-post__body_padtop_15 b-post__body_overflow_hidden b-layuot_width_full">
			<?php if ($row['logo_name']) { ?>
					<?php 
                                        if ($row['link'] != "") { 
                                            if (preg_match("/^https?\:\/\//", $row['link'])) {
                                                $link = $row['link'];
                                            } else {
                                                $link = "http://{$row['link']}";
                                            }
                                        ?>
					<a class="b-post__link project_logo_wrap" href="<?=$link?>" rel="nofollow" target="_blank">
							<img class="b-post__pic b-post__pic_float_right b-post__pic_clear_right b-post__pic_margleft_10" src="<?= WDCPREFIX.'/'.$row['logo_path'].$row['logo_name'] ?>" alt="" />
					</a>
					<?php } else {//if?>
					<img class="b-post__pic b-post__pic_float_right b-post__pic_clear_right b-post__pic_margleft_10" src="<?= WDCPREFIX.'/'.$row['logo_path'].$row['logo_name'] ?>" alt="" />
					<?php }//else?>
			<?php }//if?>
        <div class="b-post__txt <?= $row['is_bold'] == 't' ? 'b-post__txt_bold': '' ?>">
            <?= strip_tags($row['descr']) ?>
        </div>
        <div id="project-reason-<?= $row['id'] ?>" style="display: <?= ($row['is_blocked'] ? 'block': 'none') ?>">
            <? if ($row['is_blocked']) { ?>
            <div class='b-fon b-fon_clear_both b-fon_bg_ff6d2d b-fon_padtop_10'>
							<b class="b-fon__b1"></b>
							<b class="b-fon__b2"></b>
							<div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13">
								<span class="b-fon__attent"></span>
								<div class="b-fon__txt b-fon__txt_margleft_20">
										<span class="b-fon__txt_bold">Проект заблокирован</span>. <?= reformat($row['blocked_reason'], 24, 0, 0, 1, 24) ?> <a class='b-fon__link' href='https://feedback.fl.ru/'>Служба поддержки</a>
										<div class='b-fon__txt'><?= ($row['admin_login'] ? "Заблокировал: <a class='b-fon__link' href='/users/{$row['admin_login']}'>{$row['admin_uname']} {$row['admin_usurname']} [{$row['admin_login']}]</a><br />": '') ?>
										Дата блокировки: <?= dateFormat('d.m.Y H:i', $row['blocked_time']) ?></div>
								</div>
							</div>
							<b class="b-fon__b2"></b>
							<b class="b-fon__b1"></b>
            </div>
            <? } ?>
        </div>
    </div>
    <?= seo_end(false, $is_ajax)?>
    <div class="b-post__foot b-post__foot_padtop_15">
        <? seo_start($is_ajax)?>
        <div class="b-post__txt b-post__txt_fontsize_11 b-post__txt_overflow_hidden">
           <div class="b-post__txt b-post__txt_fontsize_11 b-post__txt_bold b-post__txt_float_right b-page__desktop <? if (($row['t_pro_only'] == 't')||($row['t_verify_only'] == 't')||($row['urgent'] == 't')||($row['hide'] == 't')){?>b-post__link_margtop_7<?php } ?>">
               <?php if($row['exec_id'] > 0 && !$row['exec_is_banned']) {?>
               <a class="b-post__link b-post__link_fontsize_11" href="<?= $row['friendly_url'] ?>"><?= ($row['kind'] == 7 || $row['kind'] == 2 ?"Победитель":"Исполнитель")?> определён</a>
               <?php } ?>
               
               <?php if($row['refused'] == 't') {?>
               &nbsp;&nbsp;Работодатель <a class="b-post__link b-post__link_fontsize_11" href="<?= $row['friendly_url'] ?>#freelancer_<?=$_SESSION['uid']?>">отказал</a> вам
               <?php } else if($row['selected'] == 't') {?>
               &nbsp;&nbsp;Работодатель определил вас <a class="b-post__link b-post__link_fontsize_11" href="<?= $row['friendly_url'] ?>#freelancer_<?=$_SESSION['uid']?>">кандидатом</a>
               <?php } else if($_SESSION['uid'] && $row['exec_id'] == $_SESSION['uid']) {?>
               &nbsp;&nbsp;Работодатель определил вас <a class="b-post__link b-post__link_fontsize_11" href="<?= $row['friendly_url'] ?>#freelancer_<?=$_SESSION['uid']?>">исполнителем</a>
               <?php }//elseif?>
           </div>
            <?php if(!($row['exec_id'] > 0 && !$row['exec_is_banned'])) {?>
               <a class="b-post__link b-post__txt_float_right b-post__link_bold b-post__link_fontsize_11 b-post__link_color_4e b-post__link_color_0f71c8_hover <? if (($row['t_pro_only'] == 't')||($row['t_verify_only'] == 't')||($row['urgent'] == 't')||($row['hide'] == 't')){?>b-post__link_margtop_7<?php } ?> b-page__desktop" href="<?= $row['friendly_url'] ?>"><span class="b-icon b-icon__com b-icon_top_3 b-icon_valign_bas"></span><?=project_status_link($row['kind'], $row['offers_count'])?></a>
               <?php if($row['offer_id']) { ?>
               <a class="b-post__link b-post__txt_float_right b-post__link_bold b-post__link_fontsize_11 b-post__link_color_0f71c8_hover <? if (($row['t_pro_only'] == 't')||($row['t_verify_only'] == 't')||($row['urgent'] == 't')||($row['hide'] == 't')){?>b-post__link_margtop_7<?php } ?> b-page__desktop" href="<?= $row['friendly_url'] ?>#freelancer_<?=$_SESSION['uid']?>">Ваш ответ&nbsp;&nbsp;</a>
               <?php }//if?>
            <?php }//if?>
            
            <?php if($row['view_cnt'] > 0): ?>
            <span class="b-post__txt b-post__txt_float_right b-post__txt_fontsize_11 b-post__txt_bold <?php if(($row['t_pro_only'] == 't')||($row['t_verify_only'] == 't')||($row['urgent'] == 't')||($row['hide'] == 't')): ?>b-post__link_margtop_7<?php endif; ?>">
                <span class="b-icon b-icon__counter b-icon_valign_bas" title="Количество просмотров"></span>
                <?=(int)$row['view_cnt'] ?>&#160;&#160;&#160;&#160;
            </span>
            <?php endif; ?>
           
           
           
            <? if ($row['t_is_payed']  && $row['kind'] != 2 && $row['kind'] != 7) { ?>
                <span class="b-post__bold b-layout__txt_inline-block">Платный проект</span>&nbsp;&nbsp;
            <? } else if ($row['kind'] == 2 || $row['kind'] == 7) { ?>
                <span class="b-post__bold b-layout__txt_inline-block">Конкурс</span>&nbsp;&nbsp;
            <? } else if ($row['kind'] == 4) { ?>
                <span class="b-post__bold b-layout__txt_inline-block">
                    Вакансия&nbsp;<?= (($row['country']) ? "(".$row['country_name'] . (($row['city']) ? ", " . $row['city_name'] : "" ) . ")" : "") ?>
                </span>&nbsp;&nbsp;
            <? } else { ?>
               <span class="b-post__bold b-layout__txt_inline-block">Проект</span>&nbsp;&nbsp;
            <?php } ?>
            <?php if($row['kind'] == 2 || $row['kind'] == 7) { ?>
                <?if(strtotime($row['end_date']) > time()) { ?>
                   завершится через <span class="b-page__iphone"><br></span><?= ago_pub_x(strtotime($row['end_date']), "ynjGx") ?>&nbsp;&nbsp; 
                <? } else {?>
                   завершен&nbsp;&nbsp;
                <? }?>
            <?php } else { //if?>
                <?= ago_project_created(strtotime($row['post_date'])) ?>&nbsp;&nbsp;
            <?php }//else?> 
            <? if ($row['t_pro_only'] == 't'){?><span class="b-post__only">Только для <?= view_pro2(false, false, false, 'пользователей с платным аккаунтом') ?></span><? } ?>
                 <? if ($row['t_verify_only'] == 't'){?><span class="b-post__only">Только для <?= view_verify('верифицированных пользователей') ?></span><? } ?>
            <? if ($row['hide'] == 't') { ?><span class="b-post__only">Скрытый <span class="b-icon b-icon__eye b-icon_top_1" title="от поисковых систем и неавторизированных пользователей"></span></span><? } ?>
            <?php if ($row['t_prefer_sbr']) { ?><span class="b-post__only">Оплата через <a class="b-layout__link" href="/promo/bezopasnaya-sdelka/" target="_blank">Безопасную сделку</a> <a class="b-layout__link" href="/promo/bezopasnaya-sdelka/" target="_blank"><span class="b-icon b-icon__shield" title="Оплата через Безопасную сделку"></span></a></span><? } ?>
        </div>
			  <?php if(!($row['exec_id'] > 0 && !$row['exec_is_banned'])) {?>
                   <?php if($row['offer_id']) { ?>
                   <a class="b-post__link b-post__link_bold b-post__link_fontsize_11 b-post__link_color_0f71c8_hover b-page__ipad b-page__iphone" href="<?= $row['friendly_url'] ?>#freelancer_<?=$_SESSION['uid']?>">Ваш ответ&nbsp;&nbsp;</a>
                   <?php }//if?>
                   <a class="b-post__link b-post__link_bold b-post__link_fontsize_11 b-post__link_color_4e b-post__link_color_0f71c8_hover b-page__ipad b-page__iphone" href="<?= $row['friendly_url'] ?>"><span class="b-icon b-icon__com b-icon_top_3"></span><?=project_status_link($row['kind'], $row['offers_count'])?></a>
           <?php }//if?>
        <?= seo_end(false, $is_ajax)?>	
           <div class="b-post__txt b-post__txt_fontsize_11 b-post__txt_bold b-page__ipad b-page__iphone">
               <?php if($row['exec_id'] > 0 && !$row['exec_is_banned']) {?>
               <a class="b-post__link b-post__link_fontsize_11" href="<?= $row['friendly_url'] ?>"><?= ($row['kind'] == 7 || $row['kind'] == 2 ?"Победитель":"Исполнитель")?> определён</a>
               <?php } ?>
               
               <?php if($row['refused'] == 't') {?>
               &nbsp;&nbsp;Работодатель <a class="b-post__link b-post__link_fontsize_11" href="<?= $row['friendly_url'] ?>#freelancer_<?=$_SESSION['uid']?>">отказал</a> вам
               <?php } else if($row['selected'] == 't') {?>
               &nbsp;&nbsp;Работодатель определил вас <a class="b-post__link b-post__link_fontsize_11" href="<?= $row['friendly_url'] ?>#freelancer_<?=$_SESSION['uid']?>">кандидатом</a>
               <?php } else if($_SESSION['uid'] && $row['exec_id'] == $_SESSION['uid']) {?>
               &nbsp;&nbsp;Работодатель определил вас <a class="b-post__link b-post__link_fontsize_11" href="<?= $row['friendly_url'] ?>#freelancer_<?=$_SESSION['uid']?>">исполнителем</a>
               <?php }//elseif?>
           </div>
						
								<? if($this_edit_mode || ($this_uid == $row['user_id'] && $this_uid && $row['is_blocked'] != 't')) { ?>
								<?php if((!$this_edit_mode && ( ($row['kind'] != 2 && $row['kind'] != 7) || (($row['kind'] == 2 || $row['kind'] == 7) && strtotime($row['end_date']) > time()) )) || $this_edit_mode) { ?>
								<div class="b-fon b-fon_padtop_10 b-fon_bg_fcc">
										<b class="b-fon__b1"></b>
										<b class="b-fon__b2"></b>
										<div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_overflow_hidden">
                                                <?php if($this_edit_mode && $project->isNotPayedVacancy()): ?>
                                                <span class="b-txt b-txt_bold b-txt_color_de2c2c">Вакансия еще не оплачена</span>
                                                <?php endif; ?>
												<ul class="b-post__links b-post__links_float_right">
																<? // Для админов ?>
																<? if ($this_edit_mode) { ?>
																	<?php if ($project->isAllowMovedToVacancy()): ?>
																		<li class="b-post__links-item b-post__links-item_padleft_10">
																			<a onclick="return confirm('Сделать вакансией?');" class="b-post__link b-post__link_dot_c10601" href="/projects/makevacancy/?id=<?=$row['id']?>">Сделать вакансией</a>
																		</li>
																	<?php endif; ?>

																	<li class="b-post__links-item b-post__links-item_padleft_10">
																			<a class="b-post__link b-post__link_dot_c10601" href="/public/?step=1&public=<?= $row['id'] ?>&red=<?= rawurlencode($_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']) ?>" onClick="popupQEditPrjShow(<?=$row['id']?>, event, true); return false;" >Редактировать</a>
																	</li>
																	<li class="b-post__links-item b-post__links-item_padleft_10">
																			<span id='project-button-<?= $row['id'] ?>'>
																					<a class="b-post__link b-post__link_dot_c10601" href="javascript:void(0);" onclick="banned.<?= ($row['is_blocked']? 'unblockedProject': 'blockedProject') ?>(<?= $row['id'] ?>)"><?= ($row['is_blocked'] ? 'Разблокировать' : 'Заблокировать') ?></a>
																			</span>
																	</li>
																<? // Для автора проекта ?>
																<? } elseif ($this_uid == $row['user_id'] && $this_uid && $row['is_blocked'] != 't') { ?>
																	<? if(!projects::isProjectOfficePostedAfterNewSBR($row)) { ?>
										            					<?php if($row['kind'] == 2 || $row['kind'] == 7) { ?>
                															<?php if(strtotime($row['end_date']) > time()) { ?>
																				<li class="b-post__links-item b-post__links-item_padleft_10"><a href="/public/?step=1&public=<?= $row['id'] ?>&red=<?= rawurlencode($_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']) ?>" class="b-post__link b-post__link_dot_c10601">Редактировать</a></li>
            																<?php } ?>
            															<?php } else { ?>
																			<li class="b-post__links-item b-post__links-item_padleft_10"><a href="/public/?step=1&public=<?= $row['id'] ?>&red=<?= rawurlencode($_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']) ?>" class="b-post__link b-post__link_dot_c10601">Редактировать</a></li>
																		<?php } ?>
																	<? } ?>
																	<? if($row['kind'] != 2 && $row['kind'] != 7) { ?>
																	<li class="b-post__links-item b-post__links-item_padleft_10"><a href="/projects/index.php?action=prj_close&pid=<?= $row['id'] ?>&kind=<?= $row['kind'] ?>" onclick="return warning(2)" class="b-post__link b-post__link_dot_c10601">Снять с публикации</a></li>
																	<? } ?>
																<? } ?>
												</ul>
										</div>
										<b class="b-fon__b2"></b>
										<b class="b-fon__b1"></b>				
								</div>
								<?php } ?>
								<? } ?>    
    </div>
        

