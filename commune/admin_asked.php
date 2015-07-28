<?
$is_comm_admin = $user_mod & (commune::MOD_COMM_ADMIN | commune::MOD_COMM_MODERATOR);
$is_author = $user_mod & (commune::MOD_COMM_AUTHOR);
?>
<table class="tbl-cau">
								<colgroup>
									<col width="420px">
									<col width="225px">
									<col>
								</colgroup>
								<thead>
									<tr>
										<th>
											<a href="<?= $name_link?>" class="lnk-dot-grey">Пользователь</a> <a href="<?= $name_link?>"><?= $arrow_name;?></a>
										</th>

										<th>
											<a href="<?= $asked_link;?>" class="lnk-dot-grey">Заявка подана</a> <a href="<?= $asked_link;?>"><?= $arrow_asked;?></a>
										</th>
										<th>

										</th>
									</tr>
								</thead>
								<tbody>


<? if(count($members)) foreach($members as $memb) { ?>

									<tr>
										<td>
											<?=__commPrntUsrAvtr($memb)?>
											<div class="cau-user">
												<div class="b-username"><?=__commPrntUsrInfo($memb)?></div>
												<div class="form fs-o cau-note">
													<b class="b1"></b>
													<b class="b2"></b>
													<div class="form-in" id="idNote<?= $memb['user_id'];?>">
														<?= __commPrntMemberNote($memb['user_id'], $comm['id'], $memb['note_txt'], ($is_comm_admin || $is_author)) ?>
													</div>
													<b class="b2"></b>
													<b class="b1"></b>
												</div>
											</div>
										</td>

										<td>
                                                                                    <strong><?= date("d.m.Y H:i", strtotime($memb['asked_time']));?></strong>
										</td>
										<td class="cau-lnks">
											<strong><a href="?id=<?=$id?>&site=Admin.members&mode=Asked&m=<?=$memb['id']?><?php if ($page>1){ ?>&page=<?=$page?><?php } ?>&action=do.Accept.member" class="lnk-dot-green">Добавить в сообщество</a>&nbsp;&nbsp;&nbsp; <a href="?id=<?=$id?>&site=Admin.members&mode=Asked&m=<?=$memb['id']?><?php if ($page>1){ ?>&page=<?=$page?><?php } ?>&action=do.Unaccept.member" class="lnk-dot-red">Отказать</a></strong>&nbsp;&nbsp;&nbsp;
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