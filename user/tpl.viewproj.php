<?php 

if ( hasPermissions('users') ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/banned.common.php' );
} else {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/quickpro.common.php' );
}

$xajax->printJavascript( '/xajax/' );

?>
          <style type="text/css">.b-icon__ver{ vertical-align:top;}</style>

			<div style="border-bottom: 1px solid #B2B2B2; margin: 0 0 -1px 0; clear:both;">
				<table style="margin: 0 auto -1px; border-bottom: 1px solid #B2B2B2; width:100%;">
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
														<?=$session->view_online_status($proj['login'])?><font class="frlname11"><a href="/users/<?=$proj['login']?>" class="frlname11" title="<?=($proj['uname']." ".$proj['usurname'])?>"><?=($proj['uname']." ".$proj['usurname'])?></a> [<a href="/users/<?=$proj['login']?>" class="frlname11" title="<?=$proj['login']?>"><?=$proj['login']?></a>]</font> 
                                                        <?= view_mark_user(array("login"       => $user_obj->login, 
                                                                                 "is_pro"      => $user_obj->is_pro,
                                                                                 "is_profi"    => $user_obj->is_profi,
														                         "is_pro_test" => $user_obj->is_pro_test,
														                         "is_team"     => $user_obj->is_team,
														                         "role"        => $user_obj->role
														                         ))?><br>
														<?
														  //время последней активности пользователя
                                                          include_once($_SERVER['DOCUMENT_ROOT'].'/classes/users.php');
                                                          $usr = new users();
                                                          $usr->GetUser($proj['login']);
														?>
														
													</td>
												</tr>
											</table>
										</td>
									</tr>
                                    <?php if ( hasPermissions('users') || $proj['is_blocked'] == 't' ) { ?>
                                    <tr class="qpr">
                                        <td style="padding:10px; border-left: 1px solid #C6C6C6; border-right: 1px solid #C6C6C6; border-bottom: 1px solid #C6C6C6;">
                                            <?php if ( hasPermissions('users') ) { ?>
                                            <div id="portfolio-button-<?= $proj['id'] ?>">
                                                <a class="admn" href="javascript:void(0);" onclick="banned.<?=($proj['is_blocked']=='t'? 'unblockedPortfolio': 'blockedPortfolio')?>(<?=$proj['id']?>)"><?= $proj['is_blocked']=='f' ? "Заблокировать" : "Разблокировать"; ?></a>
                                            </div>
                                            <? } ?>
                                            <div id="portfolio-block-<?= $proj['id'] ?>" style="display: <?= ($proj['is_blocked'] ? 'block': 'none') ?>">
                                                <? if ($proj['is_blocked'] == 't') { ?>
                                                <div class='b-fon b-fon_clear_both b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padbot_10'>
                                                    <b class="b-fon__b1"></b>
                                                    <b class="b-fon__b2"></b>
                                                    <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13">
                                                        <span class="b-fon__attent"></span>
                                                        <div class="b-fon__txt b-fon__txt_margleft_20">
                                                                <span class="b-fon__txt_bold">Работа заблокирована</span>. <?= reformat($proj['blocked_reason'], 24, 0, 0, 1, 24) ?> <a class='b-fon__link' href='https://feedback.fl.ru/'>Служба поддержки</a>
                                                                <div class='b-fon__txt'><?php if ( hasPermissions('users') ) { ?><?= ($proj['admin_login'] ? "Заблокировал: <a class='b-fon__link' href='/users/{$proj['admin_login']}'>{$proj['admin_uname']} {$proj['admin_usurname']} [{$proj['admin_login']}]</a><br />": '') ?><?php } ?>
                                                                Дата блокировки: <?= dateFormat('d.m.Y H:i', $proj['blocked_time']) ?></div>
                                                        </div>
                                                    </div>
                                                    <b class="b-fon__b2"></b>
                                                    <b class="b-fon__b1"></b>
                                                </div>
                                                <? } ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
								</table>
								<table width="100%" cellspacing="0" cellpadding="15" border="0">
									<tr class="qpr">
												<td style="white-space:nowrap;padding:10px 24px 10px 10px; border-left: 1px solid #C6C6C6; background:#fafafa;">
												  <? if ( $prj_prev && $prj_prev != $prjid ) { ?><a href="/users/<?=$proj['login']?>/viewproj.php?prjid=<?=$prj_prev?>" title="Используйте Ctrl &lt;&ndash;" class="blue">Предыдущая работа</a><? } else { ?>Предыдущая работа<? } ?>
												</td>
												<td style="text-align:left; padding:10px 10px 10px 20px; background:#fafafa;">
                                                    <?php $sName = /*$proj['moderator_status'] === '0' ? $stop_words->replace($proj['name']) :*/ $proj['name']; ?>
                                                    	
                                                        <div style="padding-left:30px">
                                                        <h1 class="b-layout__txt b-layout__txt_inline b-layout__txt_bold b-layout__txt_fontsize_11"><?=$sName?></h1> 
                                                        &nbsp;&nbsp; <? $txt_cost = view_cost2($proj['cost'], '', '', '', $proj['cost_type']); $txt_time = view_time($proj['time_value'], $proj['time_type']);?>
                                                        <span class="money"><?=$txt_cost?></span>
														<? if ($txt_cost != '' && $txt_time != '') { ?>, <? } ?><?=$txt_time?>
                                                     	</div>
                                                     
												<?php 
												 if ($proj['post_date']) {
												?>
														
                                                	<div style="padding-top:12px; padding-left:30px;">
                                                		Дата добавления: <?= $proj['post_date']?>
                                                		<?php if($proj['edit_date'] && $proj['post_date']!=$proj['edit_date']){?>
                                                		<br>
                                                        Дата обновления: <?= $proj['edit_date']?>
                                                        <?php } ?>
													</div>

												<? } ?>
												<? if ($proj['descr']) {
                                                    $sDescr = /*$proj['moderator_status'] === '0' ? $stop_words->replace($proj['descr']) :*/ $proj['descr'];
                                                    ?>


                                                	<div style="padding-top:12px">
														<?=reformat($sDescr, 60, 0, 0, 1)?>
													</div>

												<? }
												if ($proj['link']) {
                                                                                                    if ( !preg_match("/^[a-z]{3,5}\:\/\//", $proj['link']) ) {
                                                                                                        $proj['link'] = 'http://' . $proj['link'];
                                                                                                    }
                                                                                                ?>
													
                                                	<div style="padding-top:12px">
														<?=reformat($proj['link'],0,0,0,0,80)?>
													</div>
                                                    
												<? } ?>
                                                    </td>
												<td style="text-align:right;white-space:nowrap;padding:10px 10px 10px 24px; border-right: 1px solid #C6C6C6; background:#fafafa;">
												  <? if ( $prj_next && $prj_next != $prjid ) { ?><a href="/users/<?=$proj['login']?>/viewproj.php?prjid=<?=$prj_next?>" title="Используйте Ctrl &ndash;&gt;" class="blue">Следующая работа</a><? } else { ?>Следующая работа<? } ?>
												</td>
											</tr>
											<? if ($proj['pict'] && $file) {?>
											<tr valign="top">
												<td height="30" style="border-left: 1px solid #C6C6C6;">&nbsp;</td>
												<td colspan="2" style="border-right: 1px solid #C6C6C6;"><?=$str?></td>
											</tr>
											<? } ?>
									<? if ($proj['pict']) {?>
									<tr class="qpr">
										<td id="proj_pict" style="padding:10px; border-left: 1px solid #C6C6C6; border-right: 1px solid #C6C6C6; border-bottom: 1px solid #C6C6C6;" colspan="3">
										    <? if (!$file) { echo ( $prj_next && $prj_next != $prjid && $proj['pict_ext'] != 'swf' )? "<a href='/users/{$proj['login']}/viewproj.php?prjid={$prj_next}'>{$str}</a>": $str; } ?>
										</td>
									</tr>
									<? } ?>
									<? if ($proj['is_video']=='t') {?>
									<tr class="qpr">
										<td style="padding:10px; border-left: 1px solid #C6C6C6; border-right: 1px solid #C6C6C6; border-bottom: 1px solid #C6C6C6;" colspan="3">
											<?php echo '<br/>'.show_video($prjid,'http://'.$proj['video_link']).'<br/>'; ?>
										</td>
									</tr>
									<? } ?>
									<tr class="qpr">
									   <td style="padding:10px; border-left: 1px solid #C6C6C6; border-right: 1px solid #C6C6C6; border-bottom: 1px solid #C6C6C6;" colspan="3">
                                            <?
                                            $prevLink = ( $proj['prev_pict']||$proj['pict'] ) ?
                                                WDCPREFIX . '/users/' . $proj['login'] . '/upload/' . ($proj['prev_pict']?$proj['prev_pict']:$proj['pict']) :
                                                $host . "/images/free-lance_logo.jpg";
                                            ?>
											<div class="b-free-share"><?= ViewSocialButtons('viewproj', $proj['name'], true, true, null, null, $prevLink)?></div>
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
<script type="text/javascript">
window.addEvent('domready', function() {
    document.addEvent('keydown', function(e){
        var a={37:<?=$prj_prev?>,39:<?=$prj_next?>};
        if (e.control && a[e.code]) {
            location.replace(location.pathname + '?prjid=' + a[e.code]);
        }

    });
});

</script>			