<?php
$up_price = array ( 'prj'    => new_projects::getPriceByCode(( is_pro() ? new_projects::OPCODE_UP : new_projects::OPCODE_UP_NOPRO )),
                    'prjtop' => new_projects::getPriceByCode(( is_pro() ? new_projects::OPCODE_TOP : new_projects::OPCODE_TOP_NOPRO )));
?>


 
 
                        
<table class="b-layout__table b-layout__table_width_full b-layout__table_bordbot_df b-layout__table_2bordtop_df b-layout__table_margbot_20">
   <tr class="b-layout__tr">
      <td class="b-layout__td b-layout__td_width_60 b-layout__td_padtb_10 b-layout__td_ipad">
          <?php if($show_info) {?>
              <?= view_avatar($project['login'], $project['photo']);?>
          <?php } else { //if?>
              <img src="/images/user-default-small.png" alt="" width="50" height="50" class="lpl-avatar">
          <?php } //else?>
      </td>
      <td class="b-layout__td b-layout__td_padtb_10 b-layout__td_ipad">
          <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold b-layout__txt_padbot_5">
                  <?php if($show_info) {?>
                      <?= $session->view_online_status($project['login']) ?><a class="b-layout__link b-layout__link_bold b-layout__link_color_6db335" href="/users/<?=$project['login']?>" title="<?=($project['uname']." ".$project['usurname'])?>"><?=($project['uname']." ".$project['usurname'])?></a> <a class="b-layout__link b-layout__link_bold b-layout__link_color_6db335" href="/users/<?=$project['login']?>" title="<?=$project['login']?>">[<?=$project['login']?>]</a> <?=view_mark_user($project); /*!!!is_team!!!*/ ?> <?=($project['completed_cnt'] > 0 ?' <a class="b-layout__link" href="/promo/bezopasnaya-sdelka/" title="Пользователь работал через Безопасную Сделку" target="_blank"><span class="b-icon b-icon__shield"></span></a>':'') ?>
                  <?php } else { //if?>
                      <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_6db335">Заказчик</span> 
                      <?php if ($project['is_pro'] == 't') { ?><?=(is_emp($project['role'])?view_pro_emp():view_pro()); }?> 
                      <?php if (isset($user_offer_exist) && $user_offer_exist && !is_pro()): ?>
                            <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_weight_normal">(контакты заказчика видны только пользователям с аккаунтом <?= view_pro() ?>)</span>
                      <?php endif; ?>
                  <?php } //else?>    
              
              <?
          $user = new users();
          $user->GetUser($user->GetField($project['user_id'],$ee,'login'));
          setlocale(LC_ALL, 'ru_RU.CP1251');
          $registered    = strtolower(ElapsedMnths(strtotime($project['reg_date'])));
          setlocale(LC_ALL, 'en_US.UTF-8');
          ?>
          <? /*<?=$user->getOnlineStatus4Profile()?> */ ?>
              <?php if(hasPermissions('projects') && ($project['ico_payed']=='t' || $project['is_upped'] == 't')) { ?>
              <b class="pay-prj">Внимание! Это платный проект!</b>
              <?php } //if ?>
          </div>

          <? if ($show_info && $project['user_id'] != get_uid(0)) {
              include_once($_SERVER['DOCUMENT_ROOT'].'/projects/tpl.note.php');
              ?><?
          } ?>

        <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_5">
            <span class="b-layout_block_iphone">Отзывы фрилансеров:</span>
            <?php if (get_uid(false) && $show_info): ?>
                <a class="b-layout__link b-layout__link_color_6db335 b-layout__link_bold" href="/users/<?=$project['login']?>/opinions/?sort=1#op_head">+&nbsp;<?= (int)($op_data['frl_total']['p'])?></a>
                <a class="b-layout__link b-layout__link_color_80 b-layout__link_bold" href="/users/<?=$project['login']?>/opinions/?sort=2#op_head"><?= (int)($op_data['frl_total']['n'])?></a>
                <a class="b-layout__link b-layout__link_color_c10600 b-layout__link_bold" href="/users/<?=$project['login']?>/opinions/?sort=3#op_head">-&nbsp;<?= (int)($op_data['frl_total']['m'])?></a>
            <?php else: ?>
                <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_6db335">+&nbsp;<?= (int)($op_data['frl_total']['p'])?></span>
                <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_808080"><?= (int)($op_data['frl_total']['n'])?></span>
                <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_c10600">-&nbsp;<?= (int)($op_data['frl_total']['m'])?></span>
            <?php endif; ?>
        </div>
          
        <div class="b-layout__txt b-layout__txt_fontsize_11">Зарегистрирован<?= $user->sex === 'f' ? 'а' : '' ?> на сайте <span class="b-layout_block_iphone"><?=$registered?></span></div>
      </td>
      <td class="b-layout__td b-layout__td_padtb_10 b-layout__td_padleft_10 b-layout__td_ipad">
		  <?php /* if( get_uid(false) && ( $obj_offer->IsPrjOfferExists($project['id'], get_uid(false)) || is_pro() || $project['user_id'] == get_uid(false) || $project['is_pro'] == 't' || $isPreview || $project['kind'] == 4) && trim($project['contacts']) != '' && !$is_contacts_employer_empty) {?>
              <table class="b-layout__table b-project-contacts-collection">
                 <?php foreach($contacts_employer as $name=>$contact) { if(trim($contact['value']) == '') continue;?>
                 <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_padbot_5"><div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_nowrap"><?= $contact['name']?>:&#160;&#160;</div></td>
                    <td class="b-layout__td b-layout__td_padbot_5">
                        <div class="b-layout__txt  b-layout__txt_fontsize_11 b-layout__txt_nowrap">
                            <?php if($name == 'site') { ?>
                            <a class="b-layout__link" target="_blank" href="<?= $contact['value']?>"><?= reformat($contact['value'],50)?></a>
                            <?php } elseif($name == 'email') { ?>
                            <a class="b-layout__link" target="_blank" href="mailto:<?= $contact['value']?>"><?= reformat($contact['value'], 50)?></a>
                            <?php } else { //if?>
                               <?= reformat($contact['value'], 50)?>
                            <?php }//else?>
                        </div>
                    </td>
                 </tr>
                 <?php }//foreach?>
              </table>
          <?php } */?>
      </td>
      <td class="b-layout__td b-layout__td_padtb_10 b-layout__td_right b-layout__td_ipad">
        <? if ($project['cost'] != 0) { ?>
               <div class="b-layout__txt b-layout__txt_fontsize_18 b-layout__txt_fontsize_13_iphone">
                   <?php if($project['prefer_sbr'] == 't'): ?><a class="b-layout__link " href="/promo/bezopasnaya-sdelka/" target="_blank" title="Оплата через Безопасную сделку"><span class="b-icon b-icon__shield b-icon_top_6 b-icon_top_2_iphone"></span></a><?php endif; ?>Бюджет:
                  <span class="b-layout__bold">
                      <? $can_change_prj = hasPermissions("projects");
                      if($can_change_prj) { ?>
                      <a class="b-layout__link b-layout__link_bordbot_dot_000" href="#" id="prj_budget_lnk_<?=$project['id']?>" onClick="popupShowChangeBudget(<?=$project['id']?>, '<?=$project['cost']?>', <?=$project['currency']?>, <?=$project['priceby']?>, false, <?=$project['id']?>, 2); return false;"> <?= $project['price_display'] ?></a>
                      <? } else { ?>
                       <?= $project['price_display'] ?>
                      <? } ?>
                  </span>
                </div>
              <? } ?>
		<? 
         if ($project['cost'] > 0) {
             $price_other_cur = '';
             if ($project['currency'] != 0) {
                 $price_other_cur .= CurToChar(preg_replace('/.00$/', '', sprintf("%.2f", round($project['cost'] * $project_exRates[trim($translate_exRates[$project['currency']]) . '2'], 2))), 0) . "AA";
             }
             if ($project['currency'] != 1) {
                 $price_other_cur .= CurToChar(preg_replace('/.00$/', '', sprintf("%.2f", round($project['cost'] * $project_exRates[trim($translate_exRates[$project['currency']]) . '3'], 2))), 1) . "AA";
             }
             if ($project['currency'] != 2) {
                 $price_other_cur .= CurToChar(preg_replace('/.00$/', '', sprintf("%.2f", round($project['cost'] * $project_exRates[trim($translate_exRates[$project['currency']]) . '4'], 2))), 2) . "AA";
             }
             //if ($project['currency'] != 3) {
             //    $price_other_cur .= CurToChar(preg_replace('/.00$/', '', sprintf("%.2f", round($project['cost'] * $project_exRates[trim($translate_exRates[$project['currency']]) . '1'], 2))), 3) . "AA";
             //}
             $price_other_cur = preg_replace("/AA$/", "", $price_other_cur);
             $price_other_cur = preg_replace("/AA/", "&nbsp;—&nbsp;", $price_other_cur);
             ?>
             <div class="b-layout__txt b-layout__txt_fontsize_11"><?= $price_other_cur ?></div>
             <? } else { ?>
                 <div class="b-layout__txt b-layout__txt_fontsize_18 b-layout__txt_fontsize_13_iphone">
                      <?php if($project['prefer_sbr'] == 't'): ?><a class="b-layout__link " href="/promo/bezopasnaya-sdelka/" target="_blank" title="Оплата через Безопасную сделку"><span class="b-icon b-icon__shield b-icon_top_6 b-icon_top_2_iphone"></span></a><?php endif; ?>Бюджет:
                      <? $can_change_prj = hasPermissions("projects");
                      if($can_change_prj) { ?>
                      <a class="b-layout__link b-layout__link_bordbot_dot_000 b-layout__link_bold" href="#" id="prj_budget_lnk_<?=$project['id']?>" onClick="popupShowChangeBudget(<?=$project['id']?>, '', 0, 1, true, <?=$project['id']?>, 2); return false;">по договоренности</a>
                      <? } else { ?>
                      <span class="b-layout__bold">по договоренности</span>
                      <? } ?>
                 </div>                                                          
             <? } ?>
      </td>
   </tr>
</table>
                        
                        
                        
                        


                <?php
                 $can_change_prj = hasPermissions("projects");
                 if($can_change_prj) {
                    include_once($_SERVER['DOCUMENT_ROOT'].'/filter_specs.php');
                 ?>
                    <?php 
                    $quickEditPoputType = 3;
                    require_once($_SERVER['DOCUMENT_ROOT'].'/projects/tpl.prj-quickedit.php'); 
                    ?>


                   
                    <div id="popup_budget" class="b-shadow b-shadow_inline-block b-shadow_width_335 b-shadow_center b-shadow_zindex_11 b-shadow_hide">
                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">
                            <div class="b-shadow__title b-shadow__title_padbot_15">Редактирование бюджета</div>
                            <div id="popup_budget_prj_name" class="b-layout__txt b-layout__txt_padbot_15"></div>

                            <div class="b-form b-form_padbot_20">
                                <div class="b-combo b-combo_inline-block b-combo_margright_10">
                                    <div class="b-combo__input b-combo__input_width_60">
                                        <input id="popup_budget_prj_price" class="b-combo__input-text b-combo__input-text_fontsize_15" name="cost" type="text" size="80" maxlength="6" value="" />
                                    </div>
                                </div><div
                                 class="b-combo b-combo_inline-block b-combo_margright_10" >
                                    <div class="b-combo__input b-combo__input_multi_dropdown drop_down_default_0 b-combo__input_init_projQuickEditCurrency b-combo__input_width_60 b-combo__input_min-width_40 b-combo__input_arrow_yes">
                                        <input id="popup_budget_prj_currency" class="b-combo__input-text b-combo__input-text_fontsize_15" type="text" size="80" readonly="readonly" />
                                        <span class="b-combo__arrow"></span>
                                    </div>
                                </div><div
                                 class="b-combo b-combo_inline-block b-combo_margright_10" >
                                    <div class="b-combo__input b-combo__input_multi_dropdown drop_down_default_1 b-combo__input_init_projQuickEditCostby b-combo__input_width_100 b-combo__input_min-width_40 b-combo__input_arrow_yes">
                                        <input id="popup_budget_prj_costby" class="b-combo__input-text b-combo__input-text_fontsize_15" type="text" size="80" readonly="readonly"/>        
                                        <span class="b-combo__arrow"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="b-check b-check_padbot_10 b-check_clear_both">
                                <input id="popup_budget_prj_agreement" class="b-check__input" name="agreement" type="checkbox" value="1">
                                <label class="b-check__label b-check__label_fontsize_13" for="popup_budget_prj_agreement">по договорённости</label>
                            </div>

                            <div id="popup_budget_prj_price_error" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10" style="display: none; ">
                                <b class="b-fon__b1"></b>
                                <b class="b-fon__b2"></b>
                                <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                                    <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20">Бюджет заполнен не верно</div>
                                </div>
                                <b class="b-fon__b2"></b>
                                <b class="b-fon__b1"></b>
                            </div>

                            <div class="b-buttons b-buttons_padtop_15">
                                <a id="popupBtnSaveBudget" href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green">Сохранить</a>                            
                                <span class="b-buttons__txt">&nbsp;или&nbsp;</span>
                                <a class="b-buttons__link b-buttons__link_dot_c10601 b-shadow__close" href="javascript:void(0)">закрыть без изменений</a>
                            </div>
                        </div>
                    </div>
                 <?
                 }
                 ?>
                <div id="project_info_<?=$project['id']?>" class="b-layout b-layout_padleft_60 b-layout_2bordbot_dfdfdf0 b-layout_margbot_30">
                    
                    <div class="b-layout__txt b-layout__txt_padbot_30">
                    <?php if($project['prefer_sbr'] == 't'): ?>
                        <b>Способ оплаты — через <a target="_blank" href="/promo/bezopasnaya-sdelka/">Безопасную сделку</a></b>
                        <a title="Оплата через Безопасную сделку" target="_blank" href="/promo/bezopasnaya-sdelka/" class="b-layout__link "><span class="b-icon b-icon__shield b-icon_top_2 b-icon_top_2_iphone"></span></a>
                    <?php else: ?>
                        <b>Способ оплаты — прямая оплата Исполнителю на его кошелек или счет</b>
                    <?php endif; ?>
                    </div>
                    
                    <?php if($status_content): ?>
                    <div id="project_status_<?=$project['id']?>" class="b-fon b-fon_bg_f5 b-fon_pad_10 b-fon_margbot_20">
                            <?=$status_content?>
                    </div>
                    <?php echo $feedback_form ?>
                    <?php endif; ?>

                    <?php
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/projects/tpl.prj-main-info.php");
                    ?> 
													
                    <div id="warnreason-p<?=$project['id']?>" style="display:none; margin-bottom: 5px;">&nbsp;</div>
                    <div id="project-reason-<?=$project['id']?>" style="margin-top: 10px;<?=($project['is_blocked']? 'display: block': 'display: none')?>">
                        <?php if ($project['is_blocked']) {
                            $moder_login = (hasPermissions('projects'))? $project['admin_login']: '';
                            print '<br clear=all>'.HTMLProjects::BlockedProject($project['blocked_reason'], $project['blocked_time'], $moder_login, "{$project['admin_name']} {$project['admin_uname']}");
                        } else {
                            print '&nbsp;';
                        } ?>
                    </div>
                    <? if(hasPermissions('projects') && $project_history && $project['edit_date']) { ?>
                            <div class="b-fon b-fon_pad_10 b-fon_bg_f2 b-fon_margbot_20">
                                <a href="#" class="b-layout__link b-layout__link_bordbot_dot_41 toggle-history" onClick="$('prjh_content').toggleClass('prjh_visible'); return false;">Сохраненная первоначальная версия проекта (<?=dateFormat("d.m.Y H:i", $project['create_date'])?>)</a>
                                <div id="prjh_content" class="prjh_content ">
                                    <div class="clear"></div>
                                        <? if ($project_history['cost'] != 0) { ?>
                                            <?
                                            switch ($project_history['priceby']) {
                                                case '1':
                                                    $priceby_str = "/час";
                                                    break;
                                                case '2':
                                                    $priceby_str = "/день";
                                                    break;
                                                case '3':
                                                    $priceby_str = "/месяц";
                                                    break;
                                                case '4':
                                                    $priceby_str = "/проект";
                                                    break;
                                                default:
                                                    $priceby_str = "";
                                                    break;
                                            }
                                            ?>
                                            <div class="prj_cost">Бюджет: <?= CurToChar($project_history['cost'], $project_history['currency']) ?><?= $priceby_str ?></div>
                                        <? } else { ?>
                                            <div class="prj_cost prj-dogovor" style="margin-top:-14px">
                                                <table cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td>
                                                            <div class="form">
                                                                <b class="b1"></b>
                                                                <b class="b2"></b>
                                                                <div class="form-in">Бюджет по договоренности</div>
                                                                <b class="b2"></b>
                                                                <b class="b1"></b>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>                            
                                            </div>
                                        <? } ?>
                                    <h1 class="b-page__title"><?=reformat($project_history['name'], 30, 0, 1);?></h1>
                                    <div class="prj_text">
                                        <?=reformat($project_history['descr'], 70, 0, 0, 1);?>
                                    </div>
                                    <?
                                    if (isset($project_history['attach']) && is_array($project_history['attach'])) {
                                        ?><br/><div class="attachments attachments-p"><?
                                        $nn = 1;
                                        foreach ($project_history['attach'] as $attach) {
                                            $str = viewattachLeft( NULL, $attach["name"], $attach['path'], $file, 0, 0, 0, 0, 0, 0, $nn );
                                            echo '<div class = "b-layout__txt b-layout__txt_padbot_5">', $str, '</div>';
                                            $nn++;
                                        }
                                        ?></div><?
                                    }
                                    ?>  
                                    <div class="std prjh_section">
                                        <table border="0" width="100%">
                                        <tr valign="top">
                                        <td align="left">
                                        <? if($project_history['pro_only']=='t') { ?><div class="prj-pro">Только для <img src="/images/icons/f-pro.png"></div><br clear="all"/><? } ?>
                                        <? if($project_history['prefer_sbr']=='t') { ?>Предпочитаю сервис <a href="/<?= sbr::NEW_TEMPLATE_SBR?>/" class="sbr-ic">«Безопасная Сделка»</a><? } ?>
                                        </td>
                                        <td align="right">
                                        Разделы: <?=$project_history['spec_txt']?>
                                        <?
                                        if ($project_history['kind'] == 4) {
                                            $city_str = (($project_history['country'])?" (".$project_history['country_name'].(($project_history['city'])?", ".$project_history['city_name']:"").")":"");
                                            echo "<br><span style=\"color:#5DA534\">В офис".$city_str."</span>";
                                        }
                                        ?>
                                        </td>
                                        </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                    <? } ?> 
                </div>


		
	<div id="note_user" class="b-shadow b-shadow_center b-shadow_width_450 b-shadow_hide b-shadow_zindex_11">
					<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">

			 <form class="b-popup__body " action="">
			    <input type="hidden" name="rating" id="note_rating" value="0<?//(int)$req['rating']?>">
                <input type="hidden" name="userid" id="note_userid" value="">
                <input type="hidden" name="userid" id="note_action" value=""> 
				<div class="b-textarea">
					<textarea class="b-textarea__textarea b-textarea__textarea__height_140" id="notesTxt" name="" cols="80" onkeyup="checknote(this)" rows="5"></textarea>
				</div>
				 <div class="">
					<div class="b-buttons">
						<a class="b-buttons__link  b-popup__delete" href="javascript:void(0)" onclick="$(this).getParent('div.b-shadow').toggleClass('b-shadow_hide'); return false;">Отменить</a>
						<a class="b-button b-button_rectangle_transparent" onclick="xajax_addNotes($('note_userid').get('value'), $('notesTxt').get('value'), $('note_rating').get('value'), $('note_action').get('value'), 101)" href="javascript:void(0)">
							<span class="b-button__b1">
								<span class="b-button__b2 b-button__b2_padlr_5">
									<span class="b-button__txt">Сохранить</span>
								</span>
							</span>
						</a>
					</div>
				 </div>
			 </form>



					</div>
    </div>


	
		
<?php
if ( hasPermissions('projects') ) {
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/ban_overlay.php' );
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/warn_overlay.php' );
}
?>