
			<div style="border-bottom: 1px solid #B2B2B2; margin: 0 0 -1px 0; clear:both;">
				<table style="margin: 0 auto -1px; border-bottom: 1px solid #B2B2B2;">
					<tr>
						<td align="left" valign="top" style="padding-top: 10px;">
							<div style="margin: 0 auto; background-color: #fff;">
								<table width="100%" cellspacing="0" cellpadding="15" border="0">
									<tr class="qpr">
										<td bgcolor="#FAFAFA" style="border-top: 1px solid #C6C6C6; border-left: 1px solid #C6C6C6; border-right: 1px solid #C6C6C6; padding:10px">
											<table width="100%" cellspacing="0" cellpadding="0" border="0">
												<tr valign="top" class="n_qpr">
													<td width="70" align="center">
														<a href="/users/<?=$proj['login']?>" class="frlname11"><?=view_avatar($proj['login'], $proj['photo'])?></a>
													</td>
													<td class="prj-user">
                                                                                                                <? $user_obj->getUserByUID($uid);?>
														<font class="frlname11"><a href="/users/<?=$proj['login']?>" class="frlname11" title="<?=($proj['uname']." ".$proj['usurname'])?>"><?=($proj['uname']." ".$proj['usurname'])?></a> [<a href="/users/<?=$proj['login']?>" class="frlname11" title="<?=$proj['login']?>"><?=$proj['login']?></a>]</font> <?= view_mark_user(array("login"=>$user_obj->login,
                                       "is_pro"      => $user_obj->is_pro,
														                         "is_pro_test" => $user_obj->is_pro_test,
														                         "is_team"     => $user_obj->is_team,
														                         "role"        => $user_obj->role
														                         ))?> <?=$session->view_online_status($proj['login'])?><br>
														<?
														  //время последней активности пользователя
														  $last_ref = $session->getActivityByLogin($proj['login']);
														  if($last_ref) {
															$ago = ago_pub(strtotime($last_ref));
															if(!intval($ago))
															  $ago = "менее минуты";
														  }
														  if(!$ago && $proj['last_time']) {
															$fmt = 'ynjGi';
															if(time() - ($lt = strtotime($proj['last_time'])) > 24*3600) {
															  $fmt = 'ynjG';
															  if(time() - $lt > 30*24*3600)
																$fmt = 'ynj';
															}

															$ago = ago_pub($lt,$fmt);
														  }
                                                                                                                  include_once($_SERVER['DOCUMENT_ROOT'].'/classes/users.php');
                                                                                                                  $usr = new users();
                                                                                                                  $usr->GetUser($proj['login']);
														?>
														Заходил<?= $usr->sex == 'f' ? 'а' : '';?>: <?=$ago?> назад
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr class="qpr">
										<td valign="top" bgcolor="#FAFAFA" style="border-left: 1px solid #C6C6C6; border-right: 1px solid #C6C6C6; padding:10px;">
											<table style="width:922px;" cellspacing="0" cellpadding="0" border="0" class="noborder">
											<tr valign="top">
												<td style="white-space:nowrap;padding-right:24px;"></td>
												<td>
												<table width="100%" cellspacing="0" cellpadding="0" border="0" class="noborder">
												<tr valign="top">
													<td style="text-align:left; padding-left: 20px;"><strong><?=$proj['name']?></strong> &nbsp;&nbsp; <? $txt_cost = view_cost2($proj['cost'], '', '', '', $proj['cost_type']); $txt_time = view_time($proj['time_value'], $proj['time_type']);?><span class="money"><?=$txt_cost?></span><? if ($txt_cost != '' && $txt_time != '') { ?>, <? } ?><?=$txt_time?></td>
												</tr>
												<?php 
												 if ($proj['post_date']) {
												?>
												<tr>
													<td style="text-align:left; padding-left: 20px;">Дата публикации: <?=$proj['post_date']?> 
													<?php if($proj['edit_date']){?>
													<br/>
													Дата обновления: <?= $proj['edit_date']?>
													<?php }?>
													</td>
												</tr>
												<? } ?>
												<? if ($proj['descr']) {?>
												<tr valign="top">
													<td style="padding-top:12px;">
														<?=reformat($proj['descr'], 60, 0, 0, 1)?>
													</td>
												</tr>
												<? }
												if ($proj['link']) {
                                                                                                    if ( !preg_match("/^[a-z]{3,5}\:\/\//", $proj['link']) ) {
                                                                                                        $proj['link'] = 'http://' . $proj['link'];
                                                                                                    }
                                                                                                ?>
												<tr valign="top">
													<td style="padding-top:12px;"><?=reformat($proj['link'],0,0,0,0,80)?></td>
												</tr>
												<? } ?>
												</table>
												</td>
												<td style="text-align:right;padding-left:24px;white-space:nowrap;"></td>
											</tr>
											<? if ($proj['pict'] && $file) {?>
											<tr valign="top">
												<td height="30">&nbsp;</td>
												<td><?=$str?></td>
											</tr>
											<? } ?>
											</table>
										</td>
									</tr>
									<? if ($proj['pict']) {?>
									<tr class="qpr">
										<td id="proj_pict" style="padding:10px; border-left: 1px solid #C6C6C6; border-right: 1px solid #C6C6C6; border-bottom: 1px solid #C6C6C6;">
										<? if (!$file) {
										    print $str;
										    ?>
										    <?php
										    if ( $prj_next && $prj_next != $prjid ) {
										    ?>
										    <script type="text/javascript">
										    viewprojNextLink('/users/<?=$proj['login']?>/viewproj.php?prjid=<?=$prj_next?>');
										    </script>
										    <?php
                                            } 
										}
										?>
										</td>
									</tr>
									<? } ?>
									<? if ($proj['is_video']=='t') {?>
									<tr class="qpr">
										<td style="padding:10px; border-left: 1px solid #C6C6C6; border-right: 1px solid #C6C6C6; border-bottom: 1px solid #C6C6C6;">
											<?php echo '<br/>'.show_video($prjid,'http://'.$proj['video_link']).'<br/>'; ?>
										</td>
									</tr>
									<? } ?>
									<tr class="qpr">
									   <td style="padding:10px; border-left: 1px solid #C6C6C6; border-right: 1px solid #C6C6C6; border-bottom: 1px solid #C6C6C6;">
									       <ul class="portfolio-share">
									           <li class="yashare">
																				
<?= ViewSocialButtons('viewproj', $proj['name'])?>
																				
																				
																				
																				<? /*
                    <?=SocialButtons( $prjid, $proj['name'], 'viewproj', $proj['pict'], $name, $proj['uname'].' '.$proj['usurname'] )?>
																				*/ ?>
																				
																				
												</li>
									           <li class="ds-free-lance"> 
													<a href="/blogs/viewgroup.php?l=<?=HTTP_PREFIX.$_SERVER['HTTP_HOST']?>/users/<?=$name?>/viewproj.php?prjid=<?=$prjid?>#bottom" target="_blank"><img src="/images/btn-toblog.png" width="67" height="23" alt="В блог" title="В блог"></a> 
												</li>
                                            	<li class="ds-facebook"> 
                                                    <?=SocialFBLikeButton("portfolio", HTTP_PREFIX."{$_SERVER['HTTP_HOST']}/users/{$name}/viewproj.php?prjid={$prjid}")?>
                                                    <? /*
                                                    <script src="http://connect.facebook.net/ru_RU/all.js#xfbml=1"></script>
                                                    <fb:like show_faces=false href="<?=HTTP_PREFIX.$_SERVER['HTTP_HOST']?>/users/<?=$name?>/viewproj.php?prjid=<?=$prjid?>/"></fb:like>
                                                     */ ?>
                                                </li>
									       </ul>


									   </td>
									</tr>
								</table>
								<? if (hasPermissions('users')) { ?>
									<div align="right" style="margin: 10px 10px">
										<a href="./viewproj.php?prjid=<?=$prjid?>&action=delete" class="blue" onclick="return confirm('Вы действительно хотите удалить работу?')">Удалить</a>
									</div>
								<? } ?>
							</div>
							<br />
						</td>
					</tr>
				</table>
			</div>
			