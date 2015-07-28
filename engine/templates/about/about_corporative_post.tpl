{{include "header.tpl"}}

<?/* STYLE будет убран как только будет верстка, сделано тут чтобы не засорят ксс файлы лишним кодом*/?>
<style>
	.corp-comm {
		list-style:none;	
	}
	.corp-comm li {
		padding-bottom:5px;
	}
	.corp-comm li.top-comm {
		border-bottom:1px solid #D7D7D7;	
	}
	.corp-comm li span img {
		float:left;
		margin: 0 5px 0 0;	
	}
	
	.corp-comm li div.corp-comm-info {
		display:table;
		height: 1%;
	}
	.addButton INPUT { width: 28px; }
	
	.userInfo {
	}
	
	.userInfo img {
		vertical-align: left;
		border:1px black solid;
	}
	.userInfo a {
	}

	.link-edit a {
		color:	#ff6b3d;	
		font-size: 10px;
		text-decoration: none;
	}
	.link-edit a:hover {
		text-decoration: underline;
	}
	
	.blog-link {
		color:#666666;
		text-decoration: none;
		font-size:100%;
	}
	
	.blog-link:hover {
		color:#666666; underline;
	}
	.empname11 {
		color: #6ba813;
		font-weight:bold;
	}
	.frlname11 {
		font-size: 11px;
		color: #666666;
		text-decoration:none;
		font-weight: bold;
	}
	.frlname11:hover {
		color: #666666;
	}
	
	.corp-comm-info h2 {
		line-height: 1;
	}
</style>


<?=$$xajax->printJavascript('/xajax/');?>

<script>
banned.addContext( 'all', -1, '', '' );

var Locksubmit = 0;

function toggle_box(el) { if($(el)){var a = $(el).style; if(a.display!='block') a.display='block'; else a.display='none';}};

function getFormComment(idComm, flag) {
	$$('#edit_comment').setStyle("display", "none"); 
	
	if(!flag) flag = "";
	
	if(flag == 'new') {
		$('title').value       = "";
		$('msg').value         = "";
		$('yt_link_val').value = "";
	}
	
	var addComment = $$('#add_comment');
	//$$('#add_comment').destroy();
	$$('#comment'+idComm).adopt(addComment);
	$('parentID').value = idComm;
	addComment.setStyle("display", "block"); 
}

window.onload = function() {
    new mAttach(document.getElementById('attaches'), 10);
    <? if($$error_flag): ?>getFormComment(<?=$$post['parent']?>);<? endif; ?>    
}



</script>
<div class="body clear">
    <div class="main  clear">
        <h2>О проекте</h2>
        <div class="rcol-big">
            <div class="press-center clear">
                {{include "press_center/press_menu.tpl"}}
                <div class="pc-content">
                	<a name="top"></a>
                    <div style="float:right;">[<a href="/<?=$$name_page?>/corporative/" onclick=""><strong style="font-weight:bold;">Назад</strong></a>]</div>
                    <h3><?=reformat2($$blog['title'])?></h3>
                    <div class="pc-blog" id="comment0">
                    	<div class="utxt">
                        <?
                        $msg = $$blog['msg'];
                        $msg = preg_replace("/<ul>[\r\n ]{1,}/i","<ul>",$msg);
                        $msg = preg_replace("/[\r\n ]{1,}<\/ul>/i","</ul>",$msg);
                        $msg = preg_replace("/[\r\n ]{1,}<li>/i","<li>",$msg);
                        $msg = preg_replace("/<\/li>[\r\n ]{1,}/i","</li>",$msg);
                        $msg = reformat2($msg, 60);
                        $tidy = new tidy();
                        $msg = $tidy->repairString(iconv('CP1251','UTF-8',$msg), array('show-body-only' => true, 'wrap' => 0), 'utf8');
                        $msg = iconv('UTF-8', 'CP1251', $msg);
                        $msg = preg_replace("/<br>[\r\n]{1,}/i","<br>",$msg);
                        $msg = preg_replace("/[\r\n]{1,}<br>/i","<br>",$msg);
                        $msg = preg_replace("/<ul>[\r\n ]{1,}/i","<ul>",$msg);
                        $msg = preg_replace("/[\r\n ]{1,}<\/ul>/i","</ul>",$msg);
                        $msg = preg_replace("/[\r\n ]{1,}<li>/i","<li>",$msg);
                        $msg = preg_replace("/<\/li>[\r\n ]{1,}/i","</li>",$msg);
                        echo $msg;
                        ?>
                        </div>
                    	<? if($$attach_blog): ?>
							            	<? foreach($$attach_blog as $attach): ?>
		                                		<? if ($attach['name']): $att_ext = strtolower($attach['name']); ?>
			                                        <?  if ($att_ext == "swf"): ?>
			                                        	<?="<br>".viewattachExternal($$blog['login'], $attach['name'], "upload", "/blogs/view_attach.php?user=".$$blog['login']."&attach=".$attach['name']); ?>
			                                        <? else: ?>
		                                                <?="<br>".viewattachLeft($$blog['login'], $attach['name'], "upload", $file, 1000, 470, 307200, !$attach['small'], (($attach['small']==2)?1:0))."<br>"; ?>
		                                        	<? endif; ?>
		                                        <? endif; ?>
		                                        <div style="clear:both"></div>
	                                        <? endforeach; ?>
	                                        
	                                        <br/>
	                    <? endif; ?> 
	                    
	                    <? if($$blog['yt_link']): ?>
						  <?=show_video(1, $$blog['yt_link']);?>
						  <br/>
						<? endif; ?>
		                <ul class="clear">
		                	<li style="float:left;">Опубликовано <?=view_user2($$blog, '', (is_emp($$blog['role']) ? 'employer':'freelancer').'-name', '');?> <?/*<a href="/users/<?=strtolower($$blog['login'])?>/"><?=$$blog['uname'].' '.$$blog['usurname']?></a>*/?> <?=date('d.m.Y в H:i', strtotime($$blog['date_create']))?> <?if($$blog['id_modified']):?> [внесены изменения <?=date('d.m.Y в H:i', strtotime($$blog['date_change']))?>]<?endif;?></li>
		                	
		                	<? if(get_uid()): ?><li style="float:right;">
		                	<? if(hasPermissions('about')): ?>
		                    	<a href="/<?=$$name_page?>/corporative/deleted/<?=$$blog['id']?>/" onCLick="if(confirm('Удалить?')) return true; else return false;">Удалить</a> | <a href="/<?=$$name_page?>/corporative/post/<?=$$blog['id']?>/adminedit/">Редактировать</a> |
		                    <? endif; ?>
		                	
		                	<a href="javascript:void(0)" class="blog-link" onClick="getFormComment(0, 'new');">Комментировать</a>
		                	</li><? endif; ?>
		                	
		                	<? if($$tags): $ct = count($$tags); ?>
		                            <li class="tags">
		                            <br/>
                                        Теги: 
                                        <? $i=0;foreach($$tags as $k=>$tag):$i++; ?>
                                        	<a href="/<?=$$name_page?>/corporative/tags/<?=$tag['tag_id']?>/"><?=$tag['name']?></a><?=($ct==$i?'':',')?>
                                        <? endforeach; ?>
                                    </li>
                            <? endif; ?>
		                </ul>
		                <?if($$post['parent']==0 && $$error_flag):?><a name="new"></a><?endif;?>
		                <? if($$IDEditAdm): ?>
		                <div id='edit_new' style="float:right;width:640px;">
		                            <? if($$ederror_flag): ?><a name="new"></a><? endif; ?>
					            	<br/>
					            	<br/>
									<h2>Редактирование:</h2>
									<form action="<?=$form_uri?>" method="post" enctype="multipart/form-data" name="frm" id="frm" onkeypress="if((event.ctrlKey) && ((event.keyCode==10)||(event.keyCode==13))) {this.submit();}" onSubmit="if (!Locksubmit) { this.btn.value='Подождите'; this.btn.disabled=true; Locksubmit=1; } else { return false; }">
									<table cellpadding="5" style="cell-padding:10px;">
										<tr>
											<td style='width:150px;'>Заголовок:</td>
										 	<td><input type="text" name="title" id="title" style="width:500px;" value="<?=$$edpost['title']?$edpost['title']:$$blog['title']?>"><br/><br/></td>
										</tr>
										<tr>
											<td>Текст:</td>
											<td><textarea style="width:500px;height:200px;" name="msg" id="msg"><?=$$edpost['msg']?$edpost['msg']:$$blog['msg']?></textarea><br/>
											<? if ($$edalert[2]) print((view_error($$edalert[2]))."<br>"); ?>
											Можно использовать &lt;b&gt;&lt;i&gt;&lt;p&gt;&lt;ul&gt;&lt;li&gt;&lt;cut&gt;&lt;h&gt;
											</td>
										</tr>
										<tr>
											<td>&nbsp;</td>
											<td><br/><a href="javascript:void(0);" class="blue" onClick="toggle_box('attach')">+ Прикрепить файл к сообщению (<?=(blogs::MAX_FILE_SIZE / (1024*1024))?> Мб)</a></td>
										</tr>
										<tr>
											<td>&nbsp;</td>
											<td>
												<div id="attach" style="display:<?=($$edalert[3]?'block':'none')?>;padding-top:4px">
													<input type="hidden" name="MAX_FILE_SIZE" value="<?=blogs::MAX_FILE_SIZE?>">
														<div id="ad_button">
														  <div>	
															<div id="attaches">
														    	<input type="file" name="attach[]" class="input-file" size="50"><span class="addButton" style="font-size: 12px;">&nbsp;</span>
														    </div>
														   </div>
													    </div>
														<? if ($$edalert[2]) print((view_error($$edalert[2]))); ?>
														
												
													С помощью этого поля возможно загрузить:
													<ul style="padding: 0;margin-left:20px;">
														<li>Картинку: gif, jpeg. 600x1000 пикселей. 300 Кб. </li>
													    <li>Файл: <?=(blogs::MAX_FILE_SIZE / (1024*1024))?> Мб.</li>
												    </ul>
												    Файлы следующих форматов запрещены к загрузке: <?=implode(', ', $GLOBALS['disallowed_array'])?>
												    
											    </div>	
											    <? if($$attach_blog): ?>
												     <h4 style="margin: 16px 0 4px 0">Добавленные файлы:</h4>
												     <ul style="margin-left:20px;">
												       <? $l_dir = substr($$blog['login'], 0, 2)."/".$$blog['login']; $dir = "upload"; ?>
												     	
												     	<? foreach($$attach_blog as $attach): $fname = $attach['name']; $cfile = new CFile("users/$l_dir/$dir/".$fname); ?>
					                                		<li><a href="<?=WDCPREFIX."/users/{$$blog['login']}/$dir/$fname"?>" target="_blank">Посмотреть</a> (<?=$cfile->getext();?>, <?=ConvertBtoMB($cfile->size);?>) <input type="checkbox" name="editattach[<?=$attach['id']?>]" value="1"> удалить</li>
					                                        <div style="clear:both"></div>
				                                        <? endforeach; ?>
												     </ul>
												 <? endif; ?>
											</td>
										</tr>
										<tr>
											<td>&nbsp;</td>
											<td>
												<? if(($$edalert[3])) print(view_error($$edalert[3]) . '<br><br>');?>
											</td>
										</tr>
										<tr>
									        <td>&nbsp;</td>
									        <td><a href="javascript:void(null);" class="blue" onClick="toggle_box('yt_link')">+ Добавить ссылку на YouTube видео</a></td>
								        </tr>
								        <tr>
									    	<td>&nbsp;</td>
									    	<td>
									    		<div id="yt_link" style="padding-top:4px;<? if(!$$edalert[4]): ?>display:none<? endif; ?>">
									    			<input type="text" class="wdh100" name="yt_link" id="yt_link_val" value="<?=$$post['yt_link']?>" style="width:500px;" onfocus="isFocus = true;" onblur="isFocus = false;"><br/>
									    		</div>
									            <? if($$edalert[4]) print((view_error($$edalert[4]))); ?>
									    	</td>
								        </tr>
								        <tr>
								        	<td>&nbsp;</td>
								        	<td>
								        		<br/><input type="submit" name="btn" class="btn" value="Сохранить">
								        		<input type="hidden" name="blogID" value="0">
								        		<input type="hidden" name="parent" id="parentID" value="0">
								        		<input type="hidden" name="action" value="editnew">
								        	</td>
								        </tr>	
									</table>
									</form>
							</div>
							<? endif; ?>
		            </div>
		            
		            <? if($$sortComm): ?><h4>Комментарии: <?#intval($$count_comment)?></h4><? endif; ?>
		            <br/>
		            <? if($$sortComm): ?>
			            <ul class='corp-comm' id="comment_content">
			            <? foreach($$sortComm as $k=>$lvl): $is_emp=is_emp($$comments[$k]['role']); if($lvl>7) $lvl = 7;?>
			            
				            <li id="comment<?=$k?>"  <?=($lvl>0)?'style="margin-left:'.(20*$lvl).'px;"':'class="top-comm"'?> <? if($lvl==0 && $end>0): ?>style="border-top:1px solid #D7D7D7;"<? endif; ?>>
				            <?if($$linked==$k):?><div style="background-color:#fff7dd"><a name="new"></a><?endif;?>
				            <a name="c<?=$k?>"></a>
				            <? if(intval($$lastDate) < strtotime($$comments[$k]['date_create']) && !$is_new_link): $is_new_link=true?><a name="cnew"></a><? endif; ?>
				            <br/>
					        	<span><?=view_avatar($$comments[$k]['login'], $$comments[$k]['photo'])?> </span>
					            <div class="corp-comm-info">
						            <?/*$$session->view_online_status($$comments[$k]['login'])?><a href="/users/<?=$$comments[$k]['login']?>/"><?=$$comments[$k]['uname'].' '.$$comments[$k]['usurname']?> [<?=$$comments[$k]['login']?>]</a>	*/?>
						            <?/*<p class="userInfo">
						            	<div style="float:left;border:1px black solid;"><?=($$comments[$k]['is_pro']=='t' ? ($is_emp ? view_pro_emp() : view_pro2($$comments[$k]['is_pro_test']=='t')).'&nbsp;' : '')?></div>
						            	<div style="float:left;border:1px red solid;"><?=$$session->view_online_status($$comments[$k]['login'])?></div>
						            </p>*/?>
						            <?=view_user2($$comments[$k], '', ($is_emp?'employer':'freelancer').'-name', '');?> [<?=date('d.m.Y в H:i', strtotime($$comments[$k]['date_create']))?>]
						            <? if($$comments[$k]['id_deleted'] && hasPermissions('about')): ?>
						            	<?if($$comments[$k]['id_deleted']==$$comments[$k]['id_user']):?>
						            		Комментарий удален автором
						            	<? else: ?>
						            		<span title="<?=$$moders[$$comments[$k]['id_deleted']]['login'].": ".$$moders[$$comments[$k]['id_deleted']]['usurname']." ".$$moders[$$comments[$k]['id_deleted']]['uname']?>">Комментарий удален модератором</span>
						            	<?endif;?> 
						            	<?=date("[d.m.Y | H:i]", strtotime($$comments[$k]['date_deleted']))?>
						            <? endif; ?>
						            <? if($$comments[$k]['date_change']): ?> 
						            	<? if($$comments[$k]['id_modified']==$$comments[$k]['id_user']): ?>
						            		[внесены изменения:
						            	<? else: ?>
						            		<span <? if(hasPermissions('about')): ?>title="<?=$$moders[$$comments[$k]['id_modified']]['login'].": ".$$moders[$$comments[$k]['id_modified']]['usurname']." ".$$moders[$$comments[$k]['id_modified']]['uname']?>" <? endif; ?>>Отредактировано модератором</span> [
						            	<?endif;?> 
						            	<?=date('d.m.Y | H:i', strtotime($$comments[$k]['date_change']))?>]
						            <? endif; ?>
						            <br/><br/>
						            
						            <?/*<div style="clear:both"></div>*/?>
						           
						            <? if(intval($$lastDate) < strtotime($$comments[$k]['date_create'])): ?><img src="/images/ico_new_blog.gif" ><br/><br/><? endif; ?>
						            <? if($$comments[$k]['id_deleted'] && !hasPermissions('about')): ?>
							            <p><? if($$comments[$k]['id_deleted']==$$comments[$k]['id_user']):?> Комментарий удален автором<?else:?>Комментарий удален модератором<?endif;?> <?=date("[d.m.Y | H:i]", strtotime($$comments[$k]['date_deleted']))?></p><br/><br/>
						            <? else: ?>
						            	<? if($$comments[$k]['title']): ?><h2 <?=(hasPermissions('about')&&$$comments[$k]['id_deleted'])?"style='color:silver'":""?>><?=reformat2($$comments[$k]['title'], 40)?></h2><? endif; ?>
							            <p id="message<?=$k?>" <?=(hasPermissions('about')&&$$comments[$k]['id_deleted'])?"style='color:silver'":""?>>
                                        <?
                                        $msg = $$comments[$k]['msg'];
                                        $msg = preg_replace("/<ul>[\r\n ]{1,}/i","<ul>",$msg);
                                        $msg = preg_replace("/[\r\n ]{1,}<\/ul>/i","</ul>",$msg);
                                        $msg = preg_replace("/[\r\n ]{1,}<li>/i","<li>",$msg);
                                        $msg = preg_replace("/<\/li>[\r\n ]{1,}/i","</li>",$msg);
                                        $msg = reformat2($msg, 50);
                                        $tidy = new tidy();
                                        $msg = $tidy->repairString(iconv('CP1251','UTF-8',$msg), array('show-body-only' => true, 'wrap' => 0), 'utf8');
                                        $msg = iconv('UTF-8', 'CP1251', $msg);
                                        $msg = preg_replace("/<br>[\r\n]{1,}/i","<br>",$msg);
                                        $msg = preg_replace("/[\r\n]{1,}<br>/i","<br>",$msg);
                                        $msg = preg_replace("/<ul>[\r\n ]{1,}/i","<ul>",$msg);
                                        $msg = preg_replace("/[\r\n ]{1,}<\/ul>/i","</ul>",$msg);
                                        $msg = preg_replace("/[\r\n ]{1,}<li>/i","<li>",$msg);
                                        $msg = preg_replace("/<\/li>[\r\n ]{1,}/i","</li>",$msg);
                                        echo $msg;
                                        ?>
                                        </p><br/><br/>
							            
							            <div style="clear:both;">
							            <? if($$attach[$k]): ?>
							            	<? foreach($$attach[$k] as $attach): ?>
		                                		<? if ($attach['name']): $att_ext = strtolower($attach['name']); ?>
			                                        <?  if ($att_ext == "swf"): ?>
			                                        	<?="<br>".viewattachExternal($$comments[$k]['login'], $attach['name'], "upload", "/blogs/view_attach.php?user=".$$comments[$k]['login']."&attach=".$attach['name']); ?>
			                                        <? else: ?>
		                                                <?="<br>".viewattachLeft($$comments[$k]['login'], $attach['name'], "upload", $file, 1000, 470, 307200, !$attach['small'], (($attach['small']==2)?1:0))."<br>"; ?>
		                                        	<? endif; ?>
		                                        <? endif; ?>
		                                        <div style="clear:both"></div>
	                                        <? endforeach; ?>
	                                        <br/>
	                                    <? endif; ?> 
	                                        
							            <? if($$comments[$k]['yt_link']): ?>
							            	<?=show_video($k, $$comments[$k]['yt_link']);?>
							            	<br/>
							            <? endif; ?>
							            </div>
							            <div style="clear:both"></div>
							           
							            <? if(!$$comments[$k]['id_deleted']): ?>
							            <p class="link-edit" style="color:#ff6b3d;font-size:12px;">
							            <? if($$comments[$k]['id_user'] == get_uid() || hasPermissions('about')): ?>
							            	<? if($$comments[$k]['warn']<3 && hasPermissions('about')): ?>
							            		<span class="warnlink-<?=$$comments[$k]['id_user']?>"><a style="color: #D75A29; font-size:9px;" href="javascript: void(0);" onclick="banned.warnUser(<?=$$comments[$k]['id_user']?>, 0, 'blogs', 'all', 0); return false;">Сделать предупреждение (<span class="warncount-<?=$$comments[$k]['id_user']?>"><?=($$comments[$k]['warn'] ? $$comments[$k]['warn'] : 0)?></span>)</a></span>
							            	<? elseif(!$$comments[$k]['is_banned'] && hasPermissions('about')):?>
												<a style="color: Red;font-size:9px;" href="javascript:void(0);" onclick="banned.userBan(<?=$$comments[$k]['id_user']?>, 'admin',0)" style="color: #D75A29;" >Забанить!</a> |
											<? endif; ?>
							            	<a href="/<?=$$name_page?>/corporative/delete/<?=$k?>/" onClick="if(confirm('Вы уверены?')) return true; else return false;">Удалить</a> | <a href="/<?=$$name_page?>/corporative/post/<?=$$blog['id']?>/edit/<?=$k?>/#c<?=$k?>" onClick="$$('#add_comment').hide(); $$('#edit_comment').show();">Редактировать</a> | 
							            
							            	
							           	<? endif; ?>
							            <? if(get_uid()): ?><a href="javascript:void(0)" onClick="getFormComment(<?=$k?>, 'new');">Комментировать</a> | <? endif; ?><a href="/<?=$$name_page?>/corporative/post/<?=$$blog['id']?>/link/<?=$k?>/#c<?=$k?>">Ссылка</a></p>
						            	<? elseif(hasPermissions('about')):?>
						            		<p><a href="renew/<?=$k?>/">Вернуть</a></p>
							            <? endif; ?>
						            <? endif; ?>
						        </div>
					            <div style="clear:both"></div>
				            
				            <div id="warnreason-<?=$k?>" style="display:none">&nbsp;</div>
				            
				            <? if($$edit_flag && $$IDEdit == $k): ?>
				            	<div id='edit_comment' style="width:700px;<?=($lvl>0)?'margin-left:-'.(20*$lvl).'px;"':''?>">
					            	<br>
									<h2>Редактировать:</h2>
									<form action="<?=$form_uri?>#new" method="post" enctype="multipart/form-data" name="frm" id="frm" onkeypress="if((event.ctrlKey) && ((event.keyCode==10)||(event.keyCode==13))) {this.submit();}" onSubmit="if (!Locksubmit) { this.btn.value='Подождите'; this.btn.disabled=true; Locksubmit=1; } else { return false; }">
									<table cellpadding="5" style="cell-padding:10px;">
										<tr>
											<td style='width:150px;'>Заголовок:</td>
										 	<td><input type="text" name="title" id="etitle" style="width:500px;" value="<?=$$comments[$k]['title']?>"><br/><br/></td>
										</tr>
										
										<tr>
											<td>Комментарий:</td>
											<td><textarea style="width:500px;height:200px;" name="msg" id="emsg"><?=$$comments[$k]['msg']?></textarea><br/>
											<? if ($$edalert[2]) print((view_error($$edalert[2]))."<br>"); ?>
											Можно использовать &lt;b&gt;&lt;i&gt;&lt;p&gt;&lt;ul&gt;&lt;li&gt;&lt;cut&gt;&lt;h&gt;
											</td>
										</tr>
										<tr>
											<td>&nbsp;</td>
											<td><br/><a href="javascript:void(0);" class="blue" onClick="toggle_box('attach');">+ Прикрепить файл к сообщению (<?=(blogs::MAX_FILE_SIZE / (1024*1024))?> Мб)</a></td>
										</tr>
										<tr>
											<td>&nbsp;</td>
											<td>
												<div id="attach" style="display:<?=($$edalert[3]?'block':'none')?>;padding-top:4px">
														<input type="hidden" name="MAX_FILE_SIZE" value="<?=blogs::MAX_FILE_SIZE?>">
														<div id="ad_button">
														  <div>	
															<div id="attaches">
														    	<input type="file" name="attach[]" class="input-file" size="50"><span class="addButton" style="font-size: 12px;">&nbsp;</span>
														    </div>
														   </div>
													    </div>
														<? if ($$edalert[2]) print((view_error($$edalert[2]))); ?>
														С помощью этого поля возможно загрузить:
														<ul style="padding: 0;margin-left:20px;">
															<li>Картинку: gif, jpeg. 600x1000 пикселей. 300 Кб. </li>
														    <li>Файл: <?=(blogs::MAX_FILE_SIZE / (1024*1024))?> Мб.</li>
													    </ul>
													    Файлы следующих форматов запрещены к загрузке: <?=implode(', ', $GLOBALS['disallowed_array'])?>
											    </div>	
											    
											    <? if($$attach[$k]): ?>
											     <h4 style="margin: 16px 0 4px 0">Добавленные файлы:</h4>
											     <ul style="margin-left:20px;">
											       <? $l_dir = substr($$comments[$k]['login'], 0, 2)."/".$$comments[$k]['login']; $dir = "upload"; ?>
											     	
											     	<? foreach($$attach[$k] as $attach): $fname = $attach['name']; $cfile = new CFile("users/$l_dir/$dir/".$fname); ?>
				                                		<li><a href="<?=WDCPREFIX."/users/{$$comments[$k]['login']}/$dir/$fname"?>" target="_blank">Посмотреть</a> (<?=$cfile->getext();?>, <?=ConvertBtoMB($cfile->size);?>) <input type="checkbox" name="editattach[<?=$attach['id']?>]" value="1"> удалить</li>
				                                        <div style="clear:both"></div>
			                                        <? endforeach; ?>
											     </ul>
											    <? endif; ?>
											</td>
										</tr>
										<tr>
											<td>&nbsp;</td>
											<td>
												<? if(($$edalert[3])) print(view_error($$edalert[3]) . '<br><br>');?>
											</td>
										</tr>
										<tr>
									        <td>&nbsp;</td>
									        <td><a href="javascript:void(null);" class="blue" onClick="toggle_box('yt_link');">+ Добавить ссылку на YouTube видео</a></td>
								        </tr>
								        <tr>
									    	<td>&nbsp;</td>
									    	<td>
									    		<div id="yt_link" style="padding-top:4px;<? if(!$$edalert[4] && $$comments[$k]['yt_link'] == ""): ?>display:none<? endif; ?>">
									    			<input type="text" class="wdh100" name="yt_link" id="eyt_link_val" value="<?=($$edpost['yt_link']==''?$$comments[$k]['yt_link']:$$edpost['yt_link'])?>" style="width:500px;" onfocus="isFocus = true;" onblur="isFocus = false;"><br/>
									    		</div>
									            <? if($$edalert[4]) print((view_error($$edalert[4]))); ?>
									    	</td>
								        </tr>
								        <tr>
								        	<td>&nbsp;</td>
								        	<td>
								        		<br/><input type="submit" name="btn" class="btn" value="Сохранить">
								        		<input type="hidden" name="blogID" value="<?=$$blog['id']?>">
								        		<input type="hidden" name="action" value="editcmt">
								        	</td>
								        </tr>	
									</table>
									</form>
							</div>
				            <? endif; ?>
				            <?if($$linked==$k):?></div><?endif;?>
				             <?if($$post['parent']==$k && $$error_flag):?><a name="new"></a><?endif;?>
				            </li>
				           
			            <? $end = $lvl; endforeach; ?>
			            	
			            	
			            </ul>
			            
			            
		            <? endif; ?>
		            	{{include "about/about_corporative_snap.tpl"}}
		            <a style="float:right;" href="#top" class="blog-link">Наверх</a>	
                </div>
                
            </div>
        </div>
    </div>
</div>      

<?php
if ( hasPermissions('about') ) {
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/ban_overlay.php' );
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/warn_overlay.php' );
}
?>

{{include "footer.tpl"}}
