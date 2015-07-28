<?
$is_comm_admin = $user_mod & (commune::MOD_COMM_ADMIN | commune::MOD_COMM_MODERATOR);
$is_author = $user_mod & (commune::MOD_COMM_AUTHOR);
?>
<table class="tbl-cau">
								<colgroup>
									<col>
									<col width="100px">
									<col width="120px">
									<col width="170">
									<col width="150">
								</colgroup>
								<thead>
									<tr>
										<th>
											<a href="<?= $name_link?>" class="lnk-dot-grey">Пользователь</a> <a href="<?= $name_link?>"><?= $arrow_name;?></a>
										</th>

										<th>
											<a href="<?= $date_link;?>" class="lnk-dot-grey">Вступил</a> <a href="<?= $date_link;?>"><?= $arrow_date;?></a>
										</th>
										<th><b>Модерирование</b></th>
										<th><b>Управлением людьми</b> <span>Бан/Приглашения/Уведомления</span></th>
										<th></th>
									</tr>
								</thead>
								<tbody>
                                <?php if(($user_filter === 0 || $user_filter == 1) && $user_login == "" && $page <= 1) { ?>        
								<?$comm['is_admin'] = 't'; ?>
                                <tr id="user_row_<?=$comm['author_id'];?>">
										<td class="first">
											<?=__commPrntUsrAvtr($comm, 'author_')?>
											<div class="cau-user">
												<div class="b-username"><?=__commPrntUsrInfo($comm, 'author_')?></div>
                                                <?php if($is_comm_admin || $is_author) { ?>
												<div class="form fs-o cau-note">
													<b class="b1"></b>
													<b class="b2"></b>
													<div class="form-in" id="idNote<?= $comm['author_id'];?>">
														<?= __commPrntMemberNote($comm['author_id'], $comm['id'], $comm['note_txt'], ($comm['author_id'] == $_SESSION['uid'])) ?>
													</div>
													<b class="b2"></b>
													<b class="b1"></b>
												</div>
                                                <?php }?>
											</div>
										</td>

										<td><strong><?= date("d.m.Y H:i", strtotime($comm['created_time']));?></strong></td>
										<td><input name="is_mod" type="checkbox" value="1" class="check" checked disabled /></td>
                                        <td><input name="is_men" type="checkbox" value="1" class="check" checked disabled/></td>										
										<td class="cau-lnks"></td>
									</tr>
								<?php }//if?>
<? if(count($members)) foreach($members as $memb) { $is_moderator_check = $memb['is_moderator']=='t'?1:0; $is_manager_check = $memb['is_manager']=='t'?1:0;?>

									<tr<?= $memb['is_banned'] == 't' ? ' class="cau-banned"' : '';?> id="user_row_<?=$memb['id'];?>">
										<td class="first">
											<?=__commPrntUsrAvtr($memb)?>
											<div class="cau-user">
												<div class="b-username"><?=__commPrntUsrInfo($memb)?></div>
                                                <?php if($is_comm_admin || $is_author) { ?>
												<div class="form fs-o cau-note">
													<b class="b1"></b>
													<b class="b2"></b>
													<div class="form-in" id="idNote<?= $memb['user_id'];?>">
														<?= __commPrntMemberNote($memb['user_id'], $comm['id'], $memb['note_txt'], $is_comm_admin || $is_author) ?>
													</div>
													<b class="b2"></b>
													<b class="b1"></b>
												</div>
                                                <?php }//if?>
											</div>
										</td>

										<td><strong><?= date("d.m.Y H:i", strtotime($memb['accepted_time']));?></strong></td>
										<td>
										  <input type="hidden" name="is_mod_value" id="is_mod_value<?=$memb['id']?>" value="<?=$is_moderator_check?>">
										  <input type="hidden" name="is_men_value" id="is_men_value<?=$memb['id']?>" value="<?=$is_manager_check?>">
										  <input name="is_mod" <?=($is_moderator_check?"checked":"")?> type="checkbox" value="1" onClick="if(this.checked==true) { setRoleUser(<?=$memb['commune_id']?>, <?=$memb['id']?>, 1, $('is_men_value<?=$memb['id']?>').get('value')); } else { setRoleUser(<?=$memb['commune_id']?>, <?=$memb['id']?>, 0, $('is_men_value<?=$memb['id']?>').get('value'));}" class="check" <?= ($comm['author_id'] != $_SESSION['uid'])?"disabled":""?> /></td>
                                        <td><input name="is_men" <?=($is_manager_check?"checked":"")?> type="checkbox" value="1" onClick="if(this.checked==true) { setRoleUser(<?=$memb['commune_id']?>, <?=$memb['id']?>, $('is_mod_value<?=$memb['id']?>').get('value'), 1); } else { setRoleUser(<?=$memb['commune_id']?>, <?=$memb['id']?>, $('is_mod_value<?=$memb['id']?>').get('value'), 0);}" class="check" <?= ($comm['author_id'] != $_SESSION['uid'])?"disabled":""?>/></td>
										<td class="cau-lnks">
											<strong><a href="javascript:void(0)" onclick="if(confirm(this.innerHTML + ' пользователя?')) xajax_BanMember('idBan<?=$memb['id']?>', <?=$memb['id']?>)" id="idBan<?=$memb['id']?>" class="<?= ($memb['is_banned']=='t' ? 'lnk-dot-green' : 'lnk-dot-red');?>"><?=($memb['is_banned']=='t' ? 'Разбанить' : 'Забанить')?></a>&nbsp;&nbsp;&nbsp; <a href="?id=<?=$id?>&site=Admin.members&mode=Asked&m=<?=$memb['id']?><?php if ($page>1){ ?>&page=<?=$page?><?php } ?>&action=do.Kill.member" onclick="return confirm('Удалить пользователя?')" class="lnk-dot-red">Удалить</a></strong>
										</td>
									</tr>

<? }else{ ?>
                                                                        <tr><td><? if($user_login!==NULL && empty($members)) { ?>
                      Пользователь не найден
                    <? } else { ?>
                      &nbsp;
                    <? } ?></td></tr>
<? } ?>


                                                                        
								</tbody>
							</table>