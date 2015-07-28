

	<? if(($value['user_id']==get_uid())&&($real_offers_count > 0)&&$user_offer_exist) { ?><h2  class="b-layout__title">Ваш ответ по проекту</h2><? } ?>


                <div class="b-layout <?=$is_end?'':'b-layout_bordbot_dedfe0'?> <?= $value['user_id']==get_uid()?"b-layout_2bordbot_dfdfdf0":""?> b-layout_margbot_20" style=" <?=$value['user_id']==get_uid(false) &&  !( !(isset($project) && isset($project['payed']) && $project['payed'] > 0) || ($project['is_pro'] === 't') )?'margin-right:250px;':''?>" >
                    
                    <div class="b-fon b-fon_bg_f5 b-fon_margbot_20 b-fon_pad_10">
                    <table id="po_<?=$value['id']?>" class="b-layout__table b-layout__table_width_full">
                     <tr class="b-layout__tr">
                       <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad">
                           <a class="b-layout__link" name="freelancer_<?=$value['user_id'] ?>" href="/users/<?=$value['login']?>"><?= view_avatar($value['login'], $value['photo'])?></a>
                       </td>
                       <td class="b-layout__td b-layout__td_padright_20 b-layout__td_ipad">
                           <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_5">
                              <?=$session->view_online_status($value['login'])?>
                              <a href="/users/<?=$value['login']?>" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bold" title="<?=($value['uname'] . " " . $value['usurname'])?>"><?=($value['uname'] . " " . $value['usurname'])?></a>
                              [<a href="/users/<?=$value['login']?>" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bold" title="<?=$value['login']?>"><?=$value['login']?></a>]
                              <span style="line-height:1; vertical-align:top;"><?= (view_mark_user($value)); /*!!!is_team!!!*/?> <?=($value['completed_cnt'] > 0?'<a class="b-layout__link" href="/promo/bezopasnaya-sdelka/" title="Пользователь работал через Безопасную Сделку" target="_blank"><span class="b-icon b-icon__shield "></span></a>':'') ?></span> &#160; <?php if ( $value['is_banned'] ) { ?><span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_c10600 b-layout__txt_bold" >Пользователь&nbsp;забанен.</span><?php } ?>
                           </div>
                           <? if ($value['spec_name'] != '') { ?><div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_5 b-layout__txt_bold">Специализация: <?=$value['spec_name']?></div><? }?>
                            <?php if ( $value['frl_refused'] == 't' ) { ?>
                                <div class="b-layout__txt b-layout__txt_color_c10600">
									<?php if( $_SESSION['uid'] == $value['user_id'] ) { ?>
                                    Вы отказались от проекта
                                    <?php } else { ?>
                                    Пользователь отказался от проекта
                                    <?php }?>
                                </div>
                            <?php } else { //if?>
                                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_5">
                                    <span class="b-layout__txt b-layout__txt_fontsize_11">Отзывы работодателей:</span>
                                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_6db335"><a class="b-layout__link b-layout__link_color_6db335" href="/users/<?=$value['login']?>/opinions/?sort=1#op_head" target="_blank">+&nbsp;<?= (int)$value['opinions_plus'] ?></a></span>
                                    <?php /*<span class="r_neutral"><a href="/users/<?=$value['login']?>/opinions/?sort=2#op_head" target="_blank"><?= (int)($value['sbr_opi_null'] + $value['ops_emp_null'])?></a></span>*/ ?>
                                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_c10600"><a class="b-layout__link b-layout__link_color_c10600" href="/users/<?=$value['login']?>/opinions/?sort=3#op_head" target="_blank">-&nbsp;<?= (int)$value['opinions_minus']?></a></span>
                                </div>
                                <div class="b-layout__txt b-layout__txt_fontsize_11">Рейтинг: <?=rating::round($value['rating'])?></div>
                              <?php } ?>
                       </td>
                       <td class="b-layout__td b-layout__td_padright_20 b-layout__td_ipad">
                            <?php  $contacts = unserialize( $value['offer_contacts'] ) ? unserialize( $value['offer_contacts'] ) : '';
                                    if(is_array($contacts)) {
                                        $empty_contacts_freelancer = 0;
                                        foreach($contacts as $name=>$contact) { 
                                            if(trim($contact['value']) == '') {
                                                $empty_contacts_freelancer++;
                                            }
                                        }
                                        $is_contacts_freelancer_empty = ( count($contacts) == $empty_contacts_freelancer );
                                    }
                              ?>
                              <?php /* if (!$is_contacts_freelancer_empty && $contacts != '' && get_uid(false) && ( $value['user_id'] == get_uid(false) || is_pro() || $value['is_pro'] == 't' || $project['kind'] == 4) ) { ?>
                                      <table class="b-layout__table b-layout__table_width_full">
                                          <?php foreach($contacts as $name=>$contact) { if(trim($contact['value']) == '') continue;?>
                                          <tr class="b-layout__tr">
                                             <td class="b-layout__td b-layout__td_padbot_5 b-layout__td_padright_10"><div class="b-layout__txt b-layout__txt_fontsize_11"><?= $contact['name']?>:&#160;&#160;</div></td>
                                             <td class="b-layout__td b-layout__td_padbot_5 b-layout__td_width_full">
                                                 <div class="b-layout__txt b-layout__txt_fontsize_11">
                                                     <?php if($name == 'site') { ?>
                                                     <a class="b-layout__link" target="_blank" href="<?= $contact['value']?>"><?= reformat($contact['value'],50)?></a>
                                                     <?php } elseif($name == 'email') { ?>
                                                     <a class="b-layout__link" target="_blank" href="mailto:<?= $contact['value']?>"><?= reformat($contact['value'],50)?></a>
                                                     <?php } else { //if?>
                                                        <?= reformat($contact['value'],50)?>
                                                     <?php }//else?>
                                                 </div>
                                             </td>
                                          </tr>
                                          <?php }//foreach?>
                                          <? if ($value['country_name'] != 'Не определено') { ?>
                                          <tr class="b-layout__tr">
                                             <td class="b-layout__td b-layout__td_padbot_5 b-layout__td_padright_10"><div class="b-layout__txt b-layout__txt_fontsize_11">Город:</div></td>
                                             <td class="b-layout__td b-layout__td_padbot_5 b-layout__td_width_full">
                                                 <div class="b-layout__txt b-layout__txt_fontsize_11">                         
                                                     <?=$value['country_name']?><?  if ($value['city_name'] != 'Не определено') { ?>, 
                                                        <?=$value['city_name']?>
                                                     <? } ?>
                                                 </div>
                                             </td>
                                          </tr>
                                          <? }  ?>
                                      </table>
                              <?php } */ ?>
                              
                                
                       </td>
                       <?php /*
                       <td class="b-layout__td b-layout__td_pad_10">
                              
                              <? if($uid == $project['user_id']) { ?>
                              <div class="uprj-bar<?=($value['in_team'] ? '-act' : '')?>" id="team_<?=$value['login']?>">
                                  <? if(!$value['in_team']) { ?>
                                  <div class="uprj-st1"><a href="javascript:void(0)" onclick="addToFav('<?=$value['login']?>')" class="lnk-dot-grey">Добавить в Избранные</a></div>
                                  <? } else { ?>
                                  <div class="uprj-st2">Этот исполнитель у вас в избранных (<a href="javascript:void(0)" onclick="delFromFav('<?=$value['login']?>')" class="lnk-dot-grey">убрать</a>)</div>
                                  <? } ?>
                                  <div class="uprj-st3" style="<?=$value['n_text']?'display:none;':'' ?>"><a href="javascript:void(0)" onclick="addNoteForm(this, '<?=$value['login']?>')" class="lnk-dot-grey">Добавить заметку</a></div>
                              </div>
                              
                              <div class="uprj-note " id="note_<?=$value['login']?>" style="<?=!$value['n_text'] ? 'display:none;' : ''?>">
                                  <strong class="b-layout__txt b-layout__txt_bold b-layout__txt_color_22b14c" style="margin-left:-1px;">Ваша заметка:</strong>
                                  <div class="b-layout__txt b-layout__txt_inline b-layout__txt_color_22b14c b-layout__txt_fontsize_11 uprj-note-cnt">
                                      <p><?=reformat($value['n_text'], 54, 0, 0, 1, 54)?></p>
                                      &#160;&#160;<a href="javascript:void(0)" onclick="editNoteForm(this, '<?=$value['login']?>')" class="b-layout__link b-layout__link_dot_c10600">Изменить</a>
                                      <div style="display:none;"><?=$value['n_text']?></div>
                                  </div>
                              </div>
                              
                              <? } ?>
                       </td>
                       */ ?>
                     </tr>
                    </table>
                    </div>
					<div class="b-layout b-layout_padleft_60 b-layout_padbot_20">
                    	<?php if($project['user_id'] == $_SESSION['uid'] || $_SESSION['uid'] == $value['user_id'] || hasPermissions('projects')): ?>
                            <div class="b-layout b-layout__txt_padbot_15">
                                <?php if ($txt_cost = view_one_cost($value['cost_from'], $value['cost_to'], $value['cost_type'])): ?>
                                    <div class="b-layout__txt"><span class="b-layout__bold">Стоимость:</span> от&nbsp;<?=$txt_cost?></div>
                                <?php endif; ?>
                                <?php if ($txt_time = view_one_time($value['time_from'], $value['time_to'], $value['time_type'])): ?>
                                    <div class="b-layout__txt"><span class="b-layout__bold">Срок:</span> от&nbsp;<?=$txt_time?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <? if ($value['prefer_sbr'] === 't') { ?>
                            <div class="b-layout__txt b-layout__txt_padbot_15">Предпочитаю оплату работы через <a href="/promo/bezopasnaya-sdelka/" target="_blank" class="b-layout__link">Безопасную Сделку</a> <?= view_sbr_shield('', 'relative top_2 margleft_5') ?></div>
                        <? } ?>
                
            		    <?php if($is_user_offer) { ?>
                                        <?php if ($project['exec_id'] == $value['user_id']) { ?>
                                        <div class="po_exec">
                                            <strong>Вы исполнитель</strong><br />
                                            Работодатель вас определил как исполнителя по этому проекту.
                                        </div>
                                        <?php  } else if ($value['selected'] == 't') { //if ?>
                                        <div class="po_selected">
                                            <strong>Вы кандидат</strong><br />
                                            Работодатель вас определил как кандидата по этому проекту.<br />
                                            Это значит, что вы прошли предварительный отбор. Может быть, вы будете выбраны исполнителем проекта.
                              			</div>
                                        <?php } else if ($value['refused'] == 't' && $value['status'] == projects_status::STATUS_CANCEL) { ?>
                              			<div class="po_refused">
                                            <strong>Вы получили отказ</strong><br />
                                            Ваше предложение не подошло работодателю.
                              			</div>
                          			    <?php } else if ($value['status'] == projects_status::STATUS_DECLINE) { ?>
                              			<div class="po_refused">
                                            <strong>Вы отказались от проекта</strong><br />
                                            Вы отклонили предложение заказчика стать исполнителем проекта.
                              			</div>
                          			    <?php } // elseif?>
                      			   <?php }//if?>
                        
                        
                        <?php if ((hasPermissions('projects') || $_SESSION['uid'] == $project['exec_id']) && $project['exec_id'] == $value['user_id']) { ?>
                            <?php if (isset($order_url) && !empty($order_url)): ?>
                                <div class="b-buttons b-buttons_padbot_20">
                                    <a href="<?= $order_url ?>" class="b-button b-button_flat b-button_flat_green">
                                        Перейти в заказ
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php } ?>
                            
                        
                        <?php if($is_user_offer) {?>
                            <?php if (isset($value['dialogue']) && is_array($value['dialogue']) && (count($value['dialogue']) > 0) && $value['user_id'] == $_SESSION['uid'] && $value['is_blocked'] != 't') { $i = 0; $dc = count($value['dialogue']);?>
                                <?php if (count($value['dialogue']) > 1) { ?>
                                    <span id="count_<?=$value['id']?>" need_change="1" style="float: right;"></span>
                                <?php }//if?>
                                <div class="po_comments_<? if ($value['frl_new_msg_count'] > 0) { ?>new_<? } ?>hide" id="po_comments_<?=$value['id']?>">
                                
                                
                  			    <?php if ($value['frl_new_msg_count'] > 0) {?>
                  			    <span id="new_msgs_<?=$value['id']?>" style="float: right;"><img src="/images/ico_mail_2.gif" alt="" width="10" height="8" border="0"> <a href="javascript:void(0)" onclick="dialogue_toggle(<?=$value['id']?>); markRead('<?=$value['id']?>');"><?=$value['frl_new_msg_count']?> <?=ending($value['frl_new_msg_count'], 'новое сообщение', 'новых сообщения', 'новых сообщений')?></a></span>
                  			    <?php } //if?>
                  			    
                  			    <?php if ($dc == 1) { ?>
                  			        <div style="margin-bottom:8px;font-size:12px;">
                  			            <span class="<?=is_emp($value['dialogue'][0]['role'])?'emp':'frl'?>name11"><a href="/users/<?=$value['dialogue'][0]['login']?>" class="<?=is_emp($value['dialogue'][0]['role'])?'emp':'frl'?>name11" title="<?=($value['dialogue'][0]['uname']." ".$value['dialogue'][0]['usurname'])?>"><?=($value['dialogue'][0]['uname']." ".$value['dialogue'][0]['usurname'])?></a> [<a href="/users/<?=$value['dialogue'][0]['login']?>" class="<?=is_emp($value['dialogue'][0]['role'])?'emp':'frl'?>name11" title="<?=$value['dialogue'][0]['login']?>"><?=$value['dialogue'][0]['login']?></a>]</span> <?=dateFormat("[d.m.Y | H:i]", $value['dialogue'][0]['post_date'])?><br />
                                        <?php $sPostText = ($project['kind'] != 4 && ($value['dialogue'][0]['moderator_status'] === '0' || $value['moderator_status'] === '0')) ? $stop_words->replace($value['dialogue'][0]['post_text']) : $value['dialogue'][0]['post_text']; ?>
                          			    <?=reformat(strip_tags($sPostText), 50, 0, 0, 1)?>
                          		    </div>
                    			    <div id="po_dialogue_talk_<?=$value['id']?>" style="font-size:12px;visibility:visible;height:auto;overflow:visible;display:none;"></div>
                                    <div id="po_dialogue_answer_<?=$value['id']?>" style="font-size:100%;margin:16px 0px 6px 0px;">
                                    <?php if ($value['refused'] != 't' && $value['frl_refused'] != 't') { ?>
                                        <?php if (count($value['dialogue']) > 1) { ?>
                                        <span style="float: right;"><a href="javascript:void(null)" onclick="dialogue_toggle(<?=$value['id']?>);markRead('<?=$value['id']?>');" class="internal" id="toggle_dialogue_<?=$value['id']?>">Развернуть всю переписку</a> <?=count($value['dialogue'])?></span>
                                        <?php } //if?>
                                        <span id="add_dialog_<?=$value['user_id']?>" class="add_dialog_user"><a href="javascript:void(0);" onclick="answer(<?=$value['id']?>);markRead('<?=$value['id']?>');" class="internal">Написать ответ</a></span>
                                    <?php } else if (count($value['dialogue']) > 1) { //if?>
                                     	<span style="float: right;"><a href="javascript:void(null)" onclick="dialogue_toggle(<?=$value['id']?>);markRead('<?=$value['id']?>');" class="internal" id="toggle_dialogue_<?=$value['id']?>">Развернуть всю переписку</a> <?=count($value['dialogue'])?></span>
                                    <?php } ?>
                                    </div>
                                <?php } else { //if
                                    $nBlockedCnt = 0;
                                    $i = 0;
                                    $dialog_cnt = 0;
                                    ?>
                                    <?php foreach ($value['dialogue'] as $key => $comment) { 
                                        if($comment['is_blocked'] == 'f') {
                                            $dialog_cnt++;
                                            $edit_comment = $comment;
                                        }
                                        ?>
                                        <?php if ($i == 1){ ?>
                                        <div id="po_dialogue_talk_<?=$value['id']?>" style="font-size:12px;visibility:visible;height:auto;overflow:visible;display:none;">
                                		<?php } $i++; 
                                        
                                        if ( $comment['is_blocked'] != 't' || $comment['login'] == $_SESSION["login"] || hasPermissions('projects') ) {
                                        ?>
                                		<div style="margin-bottom:8px;font-size:100%;">
                                            <? if (!is_emp($comment['role']) || $show_info) { ?>
                                                <span class="<?=is_emp($comment['role'])?'emp':'frl'?>name11"><a href="/users/<?=$comment['login']?>" class="<?=is_emp($comment['role'])?'emp':'frl'?>name11" title="<?=($comment['uname']." ".$comment['usurname'])?>"><?=($comment['uname']." ".$comment['usurname'])?></a> [<a href="/users/<?=$comment['login']?>" class="<?=is_emp($comment['role'])?'emp':'frl'?>name11" title="<?=$comment['login']?>"><?=$comment['login']?></a>]</span> <span id="po_date_<?=$comment['id']?>"><?=dateFormat("[d.m.Y | H:i]", $comment['post_date'])?></span>
                                            <? } else { ?>
                                                <span class="empname11">Заказчик</span> <span id="po_date_<?=$comment['id']?>"><?=dateFormat("[d.m.Y | H:i]", $comment['post_date'])?></span>
                                            <? } ?>
                                                
                                            <?php if ( $i != 1 && hasPermissions('projects') && $comment['login'] != $_SESSION["login"] ) { ?>
                                            <span style="float: right;" id="dialogue-button-<?= $comment['id'] ?>">
                                                <a class="admn" href="javascript:void(0);" onclick="banned.<?=($comment['is_blocked']=='t'? 'unblockedDialogue': 'blockedDialogue')?>(<?=$comment['id']?>)"><?= $comment['is_blocked']=='f'?"Заблокировать":"Разблокировать"; ?></a>
                                            </span>
                                            <?php } ?>
                                                
                                            <br />
                                            <?php $sPostText = ($project['kind'] != 4 && ($comment['moderator_status'] === '0' || $i == 1 && $value['moderator_status'] === '0')) ? $stop_words->replace($comment['post_text']) : $comment['post_text']; ?>
                                            <a name="comment_<?=$comment['id']?>"></a>
                                            <div id="po_comment_<?=$comment['id']?>"><?=reformat(rtrim(strip_tags($sPostText)), 50, 0, 0, 1)?></div>
                                          	<div id="po_comment_original_<?=$comment['id']?>" style="display:none;"><?=str_replace(' ', '&nbsp;', reformat(trim(strip_tags($comment['post_text'])), 1000, 0, 1))?></div>
                                            
                                            <?php if ( $i != 1 ) { ?>
                                            <div id="dialogue-block-<?= $comment['id'] ?>" style="display: <?= ($comment['is_blocked'] ? 'block': 'none') ?>">
                                                <? if ($comment['is_blocked'] == 't') { ?>
                                                <div class='b-fon b-fon_clear_both b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padbot_10'>
                                                    <b class="b-fon__b1"></b>
                                                    <b class="b-fon__b2"></b>
                                                    <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13">
                                                        <span class="b-fon__attent"></span>
                                                        <div class="b-fon__txt b-fon__txt_margleft_20">
                                                                <span class="b-fon__txt_bold">Комментарий заблокирован</span>. <?= reformat($comment['blocked_reason'], 24, 0, 0, 1, 24) ?> <a class='b-fon__link' href='https://feedback.fl.ru/'>Служба поддержки</a>
                                                                <div class='b-fon__txt'><?php if ( hasPermissions('projects') ) { ?><?= ($comment['admin_login'] ? "Заблокировал: <a class='b-fon__link' href='/users/{$comment['admin_login']}'>{$comment['admin_uname']} {$comment['admin_usurname']} [{$comment['admin_login']}]</a><br />": '') ?><?php } ?>
                                                                Дата блокировки: <?= dateFormat('d.m.Y H:i', $comment['blocked_time']) ?></div>
                                                        </div>
                                                    </div>
                                                    <b class="b-fon__b2"></b>
                                                    <b class="b-fon__b1"></b>
                                                </div>
                                                <? } ?>
                                            </div>
                                            <?php } ?>
                                            
                                    	</div>
                                        <?php 
                                        }
                                        else {
                                            $nBlockedCnt++;
                                        }
                                        
                                        if ($i == $dc) { ?>
                                        </div>
                                        <?php } //if ?>
                          			<?php } //foreach?>
                          			<div id="po_dialogue_answer_<?=$value['id']?>" style="font-size:100%;margin:16px 0px 6px 0px;">
                          			<?php if ($value['refused'] != 't' && $value['frl_refused'] != 't') { ?>
                          			    <?php if (count($value['dialogue']) > 1) { ?>
                                        <span style="float: right;"><a href="javascript:void(null)" onclick="dialogue_toggle(<?=$value['id']?>);markRead('<?=$value['id']?>');" class="internal" id="toggle_dialogue_<?=$value['id']?>">Развернуть всю переписку</a> <?=(count($value['dialogue']) - $nBlockedCnt)?></span>
                                        <?php } //if ?>
                                        <span id="add_dialog_<?= $value['user_id']?>" class="add_dialog_user">
                                        <span><a href="javascript:void(0);" onclick="answer(<?=$value['id']?>);markRead('<?=$value['id']?>');" class="internal">Написать ответ</a></span>
                                        <?php if ($comment['user_id'] == $uid && $dialog_cnt > 1) { ?>
                                        <span><a href="javascript:void(null)" onclick="answer(<?=$value['id']?>, <?=$edit_comment['id']?>);markRead('<?=$value['id']?>');" class="internal">Редактировать</a></span>
                                      	<script language="javascript">
                                      	last_commentid = <?=$edit_comment['id']?>;
                                      	edit_block[<?=$value['id']?>] = '&nbsp;&nbsp;<span><a href="javascript:void(null)" onclick="answer(<?=$value['id']?>, last_commentid);markRead(\'<?=$value['id']?>\');" class="internal">Редактировать</a></span>';
                                      	</script>
                                      	<?php } //if?>
                                      	</span>
                                    <?php } else { //if?>
                                        <?php if (count($value['dialogue']) > 1) { ?>
                                      	<span style="float: right;"><a href="javascript:void(null)" onclick="dialogue_toggle(<?=$value['id']?>);markRead('<?=$value['id']?>');" class="internal" id="toggle_dialogue_<?=$value['id']?>">Развернуть всю переписку</a> <?=(count($value['dialogue']) - $nBlockedCnt)?></span>
                                        <?php } //if ?>
                                        <span>&nbsp;</span>
                                    <?php } //else ?>
                                    </div>
                                <?php } //else ?>
                      	    </div>
                            
																												<?php } //if -- end dialog?>
                        <?php } else { //if - is_user_offer?>
                            <div class="po_comments" style="color:#000;<?= ($value['mod_new_msg_count']>0 && hasPermissions('projects')) ? "background-color:#F0FFEC;" : ""?>" id="po_comments_<?=$value['id']?>">
                            <?php if($value['mod_new_msg_count'] && hasPermissions('projects')) { ?>
                                <?php if(count($value['dialogue'])>1) { ?>
                                <span id="new_msgs_<?=$value['id']?>" need_change="1" style="float: right;"><a href="javascript:markRead('<?=$value['id']?>');"><img src="/images/ico_mail_2.gif" alt="" width="10" height="8" border="0"> <?=$value['mod_new_msg_count']?> <?=ending($value['mod_new_msg_count'], 'новое сообщение', 'новых сообщения', 'новых сообщений')?></a></span>
                                <?php } else { //if?>
                                <span id="new_msgs_<?=$value['id']?>" need_change="0" style="display:none; float: right;"><a href="javascript:markRead('<?=$value['id']?>');"><img src="/images/ico_mail_2.gif" alt="" width="10" height="8" border="0"> <?=$value['mod_new_msg_count']?> <?=ending($value['mod_new_msg_count'], 'новое сообщение', 'новых сообщения', 'новых сообщений')?></a></span>
                                <? } //else ?>
                            <?php } $dc = count($value['dialogue']);//if ?>
                            
                            <?php if (hasPermissions('projects') && ($dc > 1)) { 
                                $i = 0; 
                                $nBlockedCnt = 0;
                                ?>
                                <?php foreach ($value['dialogue'] as $key => $comment) { ?>
                                    <?php if ($i == 1) {?>
                                    <div id="po_dialogue_talk_<?=$value['id']?>" style="font-size: 12px; visibility: visible; height: auto; overflow: visible; display: none;">
                                    <?php } $i++; //if 
                                    
                                    
                                    if ( $comment['is_blocked'] != 't' || $comment['login'] == $_SESSION["login"] || hasPermissions('projects') ) {
                                    ?>
                                    <div style="margin-bottom: 8px; font-size: 12px;"><a name="comment_<?=$comment['id']?>" id="comment_<?=$comment['id']?>"></a>
                                        <span class="<?=is_emp($comment['role']) ? 'emp' : 'frl'?>name11">
                                        <a href="/users/<?=$comment['login']?>" class="<?=is_emp($comment['role']) ? 'emp' : 'frl'?>name11" title="<?=($comment['uname'] . " " . $comment['usurname'])?>"><?=($comment['uname'] . " " . $comment['usurname'])?></a>
                                        [<a href="/users/<?=$comment['login']?>" class="<?=is_emp($comment['role']) ? 'emp' : 'frl'?>name11" title="<?=$comment['login']?>"><?=$comment['login']?></a>]</span> <?=dateFormat("[d.m.Y | H:i]", $comment['post_date'])?>
                                        
                                        <?php if ( $i != 1 && hasPermissions('projects') && $comment['login'] != $_SESSION["login"] ) { ?>
                                        <span style="float: right;" id="dialogue-button-<?= $comment['id'] ?>">
                                            <a class="admn" href="javascript:void(0);" onclick="banned.<?=($comment['is_blocked']=='t'? 'unblockedDialogue': 'blockedDialogue')?>(<?=$comment['id']?>)"><?= $comment['is_blocked']=='f'?"Заблокировать":"Разблокировать"; ?></a>
                                        </span>
                                        <?php } ?>
                                        
                                        <br />
                                        <?php $sPostText = ($project['kind'] != 4 && ($comment['moderator_status'] === '0' || $i == 1 && $value['moderator_status'] === '0')) ? $stop_words->replace($comment['post_text']) : $comment['post_text']; ?>
                                        <?=reformat(strip_tags($sPostText), 50, 0, 0, 1)?>
                                        
                                        <?php if ( $i != 1 ) { ?>
                                        <div id="dialogue-block-<?= $comment['id'] ?>" style="display: <?= ($comment['is_blocked'] ? 'block': 'none') ?>">
                                            <? if ($comment['is_blocked'] == 't') { ?>
                                            <div class='b-fon b-fon_clear_both b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padbot_10'>
                                                <b class="b-fon__b1"></b>
                                                <b class="b-fon__b2"></b>
                                                <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13">
                                                    <span class="b-fon__attent"></span>
                                                    <div class="b-fon__txt b-fon__txt_margleft_20">
                                                            <span class="b-fon__txt_bold">Комментарий заблокирован</span>. <?= reformat($comment['blocked_reason'], 24, 0, 0, 1, 24) ?> <a class='b-fon__link' href='https://feedback.fl.ru/'>Служба поддержки</a>
                                                            <div class='b-fon__txt'><?php if ( hasPermissions('projects') ) { ?><?= ($comment['admin_login'] ? "Заблокировал: <a class='b-fon__link' href='/users/{$comment['admin_login']}'>{$comment['admin_uname']} {$comment['admin_usurname']} [{$comment['admin_login']}]</a><br />": '') ?><?php } ?>
                                                            Дата блокировки: <?= dateFormat('d.m.Y H:i', $comment['blocked_time']) ?></div>
                                                    </div>
                                                </div>
                                                <b class="b-fon__b2"></b>
                                                <b class="b-fon__b1"></b>
                                            </div>
                                            <? } ?>
                                        </div>
                                        <?php } ?>
                                        
                                    </div>
                                    <?php 
                                    }
                                    else {
                                        $nBlockedCnt++;
                                    }
                                    
                                    
                                    if ($i == $dc) {?>
                                    </div>
                                    <?php } //if?>
                                <?php } //foreach?>
                                <div class="c"><span style="float: right;"><a href="javascript:void(null)" onclick="if($('new_msgs_<?=$value['id']?>')) { $('new_msgs_<?=$value['id']?>').set('need_change', 0); } dialogue_toggle(<?=$value['id']?>);markRead('<?=$value['id']?>');"
                                class="internal" id="toggle_dialogue_<?=$value['id']?>">Развернуть всю переписку</a> <?=(count($value['dialogue']) - $nBlockedCnt)?></span></div>
                            <?php } else { //if?>
                                <?php $sPostText = ($project['kind'] != 4 && ($value['dialogue'][0]['moderator_status'] === '0' || $value['moderator_status'] === '0')) ? $stop_words->replace($value['dialogue'][0]['post_text']) : $value['dialogue'][0]['post_text']; ?>
                                <?=reformat(rtrim(strip_tags($sPostText)), 50, 0, 0, 1)?>
                            <?php } //else?>
                            </div>
                        <?php } //else - is_user_offer?>
                
            		    <?php if ($value['is_pro'] == 't' || $value['is_pro_test'] == 't') {?>
              			<table width="100%" border="0" cellspacing="0" cellpadding="2" class="n_qpr">
                    		<col width="33%" align="left">
                            <col width="33%" align="left">
                            <col width="33%" align="left">
                            <tr valign="top" class="qpr">
                                <?php for ($i=1; $i<=3; $i++) { ?>
                                <td align="center" style="vertical-align:top;padding:12px 12px 0px 0px;">
                                    <div style="width:200px;">
                                    <?php if ($value['pict'.$i] != '') { ?>
                                    <?php $aData = getAttachDisplayData( $value['login'], $value['pict'.$i], "upload", 200, 200, 307200, 0 );?>
                                    <div class="sp-in">
                                        <table cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="height: 200px;">
                                                 <?php if ( $aData['virus_flag'] ) { ?>
                                                 <div align="center" class="filesize">
                                                    <a <?=$aData['link']?> target="_blank"><img src="/images/<?=$aData['file_ico']?>" alt="<?=$aData['file_name']?>" title="<?=$aData['file_name']?>" /></a>
                                                    <div><?=$aData['file_ext']?> <?=$aData['file_size_str']?>&nbsp;&nbsp;</div>
                                                 </div>
                                                 <?php } else { //if?>
                                                 <div align="center">
                                                     <?php if ($value['portf_id'.$i] == 0) { ?>
                                                         <?php if (in_array(CFile::getext($value['pict'.$i]), $GLOBALS['graf_array']) || strtolower(CFile::getext($value['pict'.$i])) == "mp3") { ?>
                                                            <?php if ($value['prev_pict'.$i] != '') { ?>
                                                            <div style="text-align:left"><a href="/projects/viewwork.php?pid=<?=$value['project_id']?>&user=<?=$value['login']?>&wid=<?=$i?>" target="_blank" class="blue" title="<?="{$foto_alt} фото ".$value['prev_pict'.$i]?>" style="text-decoration:none"><?=view_preview($value['login'], $value['prev_pict'.$i], "upload", $align, false, true, "{$foto_alt} фото ".$value['prev_pict'.$i])?></a></div>
                                                            <?php } else { //if ?>
                                                            <div style="text-align:left;font-size:11px;">
                                                                <a href="/projects/viewwork.php?pid=<?=$value['project_id']?>&user=<?=$value['login']?>&wid=<?=$i?>" target="_blank" class="blue" title=""><img src="/images/<?=$aData['file_ico']?>" alt="<?=$aData['file_name']?>" title="<?=$aData['file_name']?>" /></a>
                                                            </div>
                                                            <div><?=$aData['file_ext']?> <?=$aData['file_size_str']?>&nbsp;&nbsp;</div>
                                                            <?php } //else ?>
                                                         <?php } else { //if?>
                                                            <?php if ($value['prev_pict'.$i] != '') { ?>
                                                            <div style="text-align:left"><a href="<?=WDCPREFIX?>/users/<?=$value['login']?>/upload/<?=$value['pict'.$i]?>" target="_blank" class="blue" title="<?="{$foto_alt} фото ".$value['pict'.$i]?>" style="text-decoration:none"><?=view_preview($value['login'], $value['prev_pict'.$i], "upload", $align, false, true, "{$foto_alt} фото ".$value['pict'.$i])?></a></div>
                                                            <?php } else { //if?>
                                                            <div style="text-align:left;font-size:11px;"><a href="<?=WDCPREFIX?>/users/<?=$value['login']?>/upload/<?=$value['pict'.$i]?>" target="_blank" class="blue" title=""><img src="/images/<?=$aData['file_ico']?>" alt="<?=$aData['file_name']?>" title="<?=$aData['file_name']?>" /></a></div>
                                                            <div><?=$aData['file_ext']?> <?=$aData['file_size_str']?>&nbsp;&nbsp;</div>
                                                            <?php } //else ?>
                                                         <?php } //else ?>
                                                     <?php } else { ?>
                                                         <?php if ($value['prev_pict'.$i] != '') { ?>
                                                         <div style="text-align:left"><a href="/users/<?=$value['login']?>/viewproj.php?prjid=<?=$value['portf_id'.$i]?>" target="_blank" class="blue" title="<?="{$foto_alt} фото ".$value['prev_pict'.$i]?>" style="text-decoration:none"><?=view_preview($value['login'], $value['prev_pict'.$i], "upload", $align, false, true, "{$foto_alt} фото ".$value['prev_pict'.$i])?></a></div>
                                                         <?php } else { //if?>
                                                         <div style="text-align:left;font-size:11px;"><a href="/users/<?=$value['login']?>/viewproj.php?prjid=<?=$value['portf_id'.$i]?>" target="_blank" class="blue" title=""><img src="/images/<?=$aData['file_ico']?>" alt="<?=$aData['file_name']?>" title="<?=$aData['file_name']?>" /></a></div>
                                                         <div><?=$aData['file_ext']?> <?=$aData['file_size_str']?>&nbsp;&nbsp;</div>
                                                         <?php } //else ?>
                                                     <?php } //else ?>  
                                                 </div>
                                                 <?php } //else?>
                                                 </td>
										     </tr>
    									</table>
    								</div>
    								<div class="avs-contest"><span class="<?=$aData['virus_class']?>" <?=($aData['virus_class'] == 'avs-nocheck' ? 'title="Антивирусом проверяются файлы, загруженные после 1&nbsp;июня&nbsp;2011&nbsp;года"' : '')?>><nobr><?=$aData['virus_msg']?></nobr></span></div>
                                    <?php } //if ?>
                                    </div>
                                </td>
                                <?php  } //for ?>
                            </tr>
                        </table>
                        <?php } //if ?>
              	        <?php if (hasPermissions('projects') && $value['user_id'] != $_SESSION['uid'] ) { ?>
              	        <div class="prj-admin-btn c" style=" padding-bottom:0; padding-top:0; float:right">
                            <ul>
                                <li id="project-button-<?=$value['id']?>"><a class="admn" href="javascript:void(0);" onclick="banned.<?=($value['is_blocked']=='t'? 'unblockedProjectOffer': 'blockedProjectOffer')?>(<?=$value['id']?>,<?=$value['user_id']?>,<?= $project['id']?>)"><?= $value['is_blocked']=='f'?"Заблокировать":"Разблокировать"; ?></a></li>
                                <li><?php if ($value['warn']<3 && !$value['is_banned'] && !$value['ban_where'] ) { ?>
                            <span class='warnlink-<?= $value['user_id']?>'><a style='color: red;' href='javascript: void(0);' onclick='banned.warnUser(<?= $value['user_id']?>, 0, "projects", "p<?= $project['id']?>", 0); return false;'>Сделать предупреждение (<span class='warncount-<?= $value['user_id']?>'><?= ($value['warn'] ? $value['warn'] : 0);?></span>)</a></span> | 
                            <?php } else { 
                                $sBanTitle = (!$value['is_banned'] && !$value['ban_where']) ? 'Забанить!' : 'Разбанить';
                                ?>
                            <span class='warnlink-<?= $value['user_id']?>'><a class="admn" href="javascript:void(0);" onclick="banned.userBan(<?=$value['user_id']?>, 'p<?= $project['id']?>',0)"><?=$sBanTitle?></a></span> | 
                            <?php } //else ?></li>
                            </ul>
                        </div>
                        <?php } //if?>
                        <div id="project-offer-block-<?= $value['id'] ?>" style="display: <?= ($value['is_blocked'] ? 'block': 'none') ?>">
                            <? if ($value['is_blocked'] == 't') { ?>
                            <div class='b-fon b-fon_clear_both b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padbot_10'>
                                <b class="b-fon__b1"></b>
                                <b class="b-fon__b2"></b>
                                <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13">
                                    <span class="b-fon__attent"></span>
                                    <div class="b-fon__txt b-fon__txt_margleft_20">
                                            <span class="b-fon__txt_bold">Предложение заблокировано</span>. <?= reformat($value['blocked_reason'], 24, 0, 0, 1, 24) ?> <a class='b-fon__link' href='https://feedback.fl.ru/'>Служба поддержки</a>
                                            <div class='b-fon__txt'><?php if ( hasPermissions('projects') ) { ?><?= ($value['admin_login'] ? "Заблокировал: <a class='b-fon__link' href='/users/{$value['admin_login']}'>{$value['admin_uname']} {$value['admin_usurname']} [{$value['admin_login']}]</a><br />": '') ?><?php } ?>
                                            Дата блокировки: <?= dateFormat('d.m.Y H:i', $value['blocked_time']) ?></div>
                                    </div>
                                </div>
                                <b class="b-fon__b2"></b>
                                <b class="b-fon__b1"></b>
                            </div>
                            <? } ?>
                        </div>
                        
                        
            		   <?php if ($value['is_blocked'] != 't') {?>
                           <?php if (!($uid > 0 && $value['user_id'] == $uid && $value['refused']!='t' && $project['closed'] != 't' && $value['frl_refused'] != 't' && $project['exec_id'] != $uid && $value['is_blocked'] != 't')) { ?>
                                <?php if ( !($project['kind'] == 9 && !is_emp()) ): ?>
                                <?php if (!$uid) { ?>
                               <div class="b-buttons b-buttons_padtop_15">
                                    <a href="/new-personal-order/<?=$value['login']?>/" class="b-button b-button_flat b-button_flat_green ">Предложить заказ</a>
                                    <span class="b-layout__txt b-layout__txt_fontsize_11"> &#160; <a class="b-layout__link b-layout__link_bold" href="/users/<?=$value['login']?>/tu/">Посмотреть все услуги фрилансера</a>  или <a class="b-layout__link b-layout__link_bold" href="/users/<?=$value['login']?>/">его портфолио</a></span>
                               </div>
                               <?php } else { ?>
                           <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_10"><a class="b-layout__link b-layout__link_bold b-layout__link_fontsize_11 b-layout__link_color_0f71c8" href="/users/<?=$value['login']?>/tu/">Посмотреть все услуги фрилансера</a>  или <a class="b-layout__link b-layout__link_bold b-layout__link_fontsize_11 b-layout__link_color_0f71c8" href="/users/<?=$value['login']?>/">его портфолио</a></div>
                           <?php } ?>
                                <?php endif; ?>
                           <div id="warnreason-o<?=$value['id']?>" style="display:none; margin-bottom: 5px;">&nbsp;</div>
                           <?php }?>
            		    <?php }?>
            		    <?php if ($uid && $value['user_id'] == $uid && $value['refused']!='t' && $project['closed'] != 't' && $value['frl_refused'] != 't' && $project['exec_id'] != $uid && $value['is_blocked'] != 't') { $ref_view=true;?>
            		    <div id="frl_edit_bar" class="b-layout__txt b-layout__txt_padtop_10">
            		        <? if ($value['is_blocked'] != 't') { ?>
                                <a href="/projects/index.php?pid=<?=$value['project_id']?>&edit=<?=$value['dialogue'][0]['id']?><?=$from_prm_s?>" class="b-layout__link b-layout__link_color_6db335 b-layout__link_bold ">Редактировать предложение</a>&nbsp;&nbsp;
                                <a class="b-layout__link b-layout__link_dot_c10600 b-layout__link_bold" href="javascript:void(0);" onclick="if(confirm('Вы действительно хотите отказаться от этого проекта?')) { if($('resetbtn')!= undefined) {$('resetbtn').click();} xajax_FrlRefuse(<?=$value['project_id']?>)}">Отказаться от проекта</a>
                            <?php }?>
                        </div>
                        <? if($error_is_color) print("<br/><br/>".view_error($error_is_color));?> 
                        <?php } //if?>
                        <?php if ($project['kind'] != 9 && $ref_view == false && $uid && $value['user_id'] == $uid && $value['refused']!='t' && $value['frl_refused'] != 't' && $project['exec_id'] != $uid && $value['is_blocked'] != 't') {?>
                        <div id="frl_edit_bar" class="b-layout__txt b-layout__txt_padtop_10">
                            <a class="b-layout__link b-layout__link_dot_c10600 b-layout__link_bold" href="javascript:void(0)" onclick="if(confirm('Вы действительно хотите отказаться от этого проекта?')) { if($('resetbtn')!= undefined) {$('resetbtn').click();} xajax_FrlRefuse(<?=$value['project_id']?>) }">Отказаться от проекта</a>
                        </div>
                        <?php } //if?>
                    </div>
                </div>
        <?php if ( $project['kind'] != 9 && $projectObject->isAllowShowOffers()): ?>        
	<? if(($value['user_id']==get_uid())&&($real_offers_count > 0)) { ?>
		<h2  class="b-layout__title">Ответов от фрилансеров - <?= $real_offers_count ?></h2>
		<? if ($notHiddenOffersCount != $real_offers_count) {
            // сколько ртветов скрыто
            $count_hidden_offers = $real_offers_count - $notHiddenOffersCount;
        } ?>
	<? } elseif($real_offers_count == 0) { ?>
        <h2 class="b-layout__title">Никто из фрилансеров пока не ответил<?= (get_uid())? ', вы первый':'' ?></h2>
	<? /*} elseif ($user_offer_exist) { //else?>
        <h2  class="b-layout__title">Ваше предложение</h2>
    <? */ } elseif ($notHiddenOffersCount == 0 && $real_offers_count != 0) { ?>
    	<h2  class="b-layout__title"><?=$real_offers_count.' '.getSymbolicName($real_offers_count, 'hidden_offers')?></h2>
    	<? $count_hidden_offers = $real_offers_count; ?>
    <? } ?>
    <?php endif; ?>