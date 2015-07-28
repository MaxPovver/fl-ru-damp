{{include "header.tpl"}}
<link rel="alternate" type="application/rss+xml" title="Корпоративный блог на Free-lance.ru" href="/rss/corporative.php"/>
<?/* STYLE будет убран как только будет верстка, сделано тут чтобы не засорят ксс файлы лишним кодом*/?>
<style>
	.ico {
		float:left;
	}
	.text {
		float:left;
		padding-left:5px;
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
	
	a.ajax_gray {
		border-bottom: 1px gray dashed;
		text-decoration:none;
		color:gray;	
	}
	a.ajax_gray:hover {
		border-bottom:0px;
	}
	.link-box {
	   float:left;
	   padding-left:50px;
	   padding-top:10px
	}
	.tag-cnt {
		background: whitesmoke;
		display:none;
		padding:10px;
	}
	
	.tag-cnt a {
		color:gray;
		font-size:90%;
	}
	
	.tag-search {
		font-size:200%;
		color:gray;
	}
	
	.tag-content input {
		width:475px;
		font-size: 150%;
	}
	
	.tag-list div {
		padding-bottom:5px;
	}
	.tag-list div img {
		cursor:pointer;
	}
	
	.search-tag-list {
		display:block;
		position:absolute;
		z-index:100;
		width: 480px;
		border-left:1px silver solid;
		border-right:1px silver solid;
	}
	
	.search-tag-list li {
		padding:5px;
		background: whitesmoke;
		border-bottom:1px silver solid;;
	}
</style>

<!--[if lte IE 6]>
<style>
	.search-tag-list {
		display:block;
		margin-top: 29px;
		margin-left:-506px;
		position:absolute;
		z-index:100;
		width: 480px;
		border-left:1px silver solid;
		border-right:1px silver solid;
	}	
</style>
<![endif]-->

<!--[if lte IE 7]>
<style>
	.search-tag-list {
		display:block;
		margin-top: 29px;
		margin-left:-506px;
		position:absolute;
		z-index:100;
		width: 480px;
		border-left:1px silver solid;
		border-right:1px silver solid;
	}	
</style>
<![endif]-->

<? if(hasPermissions('about')): ?>

<script>
banned.addContext( 'all', -1, '', '' );
    
    
	var Locksubmit = 0;
	
	
	window.onload = function() {
	   new mAttach(document.getElementById('attaches'), 10);

	   $(document.body).addEvent("click", function(){
	       //$("search_content").destroy();    
	   });  
	}
	
	<? if($$post['tags']): ?>
	var itag = <?=(count($$post['tags'])+1);?>;
	<? else: ?>
	var itag = 1;
	<? endif; ?>
	function addTagInput(cont) {
		if(cont == undefined) cont = 'tag_input';
		var html = "<div id='tag_"+itag+"'><input type='text' name='tags[]' id='input_"+itag+"' autocomplete='off' onKeyUp='get_search_tag(this.value, "+itag+", event);' >&nbsp;&nbsp;<img src='/images/minus.gif' width='15' height='4' vspace='4' onClick='delTagInput("+itag+")'></div>";
		
		var idDiv = 'tag_'+itag;
		var idTag = 'input_'+itag;
		var div = new Element('div', {'id':idDiv});
		var inp = new Element('input', {
		    'type' : 'text',
		    'name' : 'tags[]',
		    'id'   : idTag,
		    'autocomplete' : 'off',
		    'events': {
		        'onkeyup' : function() {
		           get_search_tag(this.value, "+itag+", event);    
		        }
		    }
		     
		});
		var spn = new Element('span', {'html':'&nbsp;&nbsp;'});
		var img = new Element('img', {
		    'src' : '/images/minus.gif',
		    'width': '15',
		    'height':'4',
		    'vspace':'4',
		    'styles': {
		        'cursor' : 'pointer'
		    },
		    'onClick': 'delTagInput('+itag+')'
		});
		
		div.adopt(inp, spn, img);
		$$('#'+cont).adopt(div);
		itag += itag;	
	}
	
	function delTagInput(tag) {
		$('tag_'+tag).destroy();
	}
	
	function searchTag(v, id) {
		xajax_searchCorporativeTag(v, id);
	}
	var iTimeoutId = null;
	
	function get_search_tag(val, id, event) {
		/*if(event.keyCode == 27) {
			$('search_content').destroy();	
			return false;
		}
		
		if(val.length >= 3) {
			if(iTimeoutId != null) {
				clearTimeout(iTimeoutId);
				iTimeoutId = null;
			}
			
			iTimeoutId = setTimeout(function(){
				searchTag(val, id);	
			}, 800);
		} else {
			$('search_content').destory();	
		}
		
		$('search_content').addEvent('mouseover', function() {
			$(this).destroy();
		});*/
	}
	
	
	
</script>

<? endif; ?>

<script>
var toggle_box = function(el) { if($(el)){var a = $(el).style; if(a.display!='block') a.display='block'; else a.display='none';}};
</script>

<?=$$xajax->printJavascript('/xajax/');?>

<div class="body clear">
    <div class="main  clear">
        <h2>О проекте</h2>
        <div class="rcol-big">
            <div class="press-center clear">
                {{include "press_center/press_menu.tpl"}}
                <div class="pc-content">
                    <? /*if(is_moder() || is_admin()) { ?><div style="float:right;">[<a href="javascript:void(0);" onclick="admin.openPopup('cblog', 0); return false;">Добавить блог</a>]</div><? } */?>
                    <? if(hasPermissions('about')): ?><div style="float:right"><a href="#bottom"><img src="/images/btn.gif"></a></div><? endif; ?>
                    <div>
                        <span style="float:left"><h3>Корпоративный блог</h3></span> 
                        
                        <span class="link-box"><a href="javascript:void(0)" class="ajax_gray" onClick="if($('tags_content').get('text') == '') { xajax_CorporativeTags('<?=$$tag_id?$$tag_id:0?>'); $('tags_content').setStyle('display', 'block'); } else { toggle_box('tags_content') }">Cписок тегов</a></span>
                      
                    </div>
                    <div style="clear:both"></div>
                    <div id="tags_content" class="tag-cnt" <?=($$oblako?"style='display:block'":"");?>><? if($$oblako): $tags = $$oblako; $tag_id = $$tag_id?>{{include "about/corporative_tags.tpl"}}<? endif; ?></div><br/>
                    <? if($$tag_name): ?>
                    	<div class="tag-search">Вы искали записи по тегу &laquo;<?=$$tag_name?>&raquo;</div>
                    <? endif; ?>
                    <? if($$blogs): ?>
                    	<? foreach($$blogs as $k=>$val): $is_emp=is_emp($$usbank[$val['id_user']]['role']); ?>
                    	
                    		 <div class="pc-blog">
		                        <h4><a href="/<?=$$name_page?>/corporative/post/<?=$val['id']?>/"><?=reformat($val['title'])?></a>
                                <?/* if(is_moder() || is_admin()) { ?>
                                    <a href="javascript:void(0);" onclick="admin.openPopup('cblog', <?=$val["id"];?>);"><img height="19" width="20" border="0" align="absmiddle" src="/team/images/ico_edit.gif" alt="Редактировать блог"/></a>
                                <? } */?>
                                </h4>
		                        <div class="utxt">
                                <?
                                $msg = $val['msg'];
                                $msg = preg_replace("/<ul>[\r\n ]{1,}/i","<ul>",$msg);
                                $msg = preg_replace("/[\r\n ]{1,}<\/ul>/i","</ul>",$msg);
                                $msg = preg_replace("/[\r\n ]{1,}<li>/i","<li>",$msg);
                                $msg = preg_replace("/<\/li>[\r\n ]{1,}/i","</li>",$msg);
                                $msg = reformat($msg, 60, 1);
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
		                        <? if($$attach[$val['id']]): ?>
							            	<? foreach($$attach[$val['id']] as $attach): ?>
		                                		<? if ($attach['name']): $att_ext = strtolower($attach['name']); ?>
			                                        <?  if ($att_ext == "swf"): ?>
			                                        	<?="<br>".viewattachExternal($$usbank[$val['id_user']]['login'], $attach['name'], "upload", "/blogs/view_attach.php?user=".$$usbank[$val['id_user']]['login']."&attach=".$attach['name']); ?>
			                                        <? else: ?>
		                                                <?="<br>".viewattachLeft($$usbank[$val['id_user']]['login'], $attach['name'], "upload", $file, 1000, 470, 307200, !$attach['small'], (($attach['small']==2)?1:0))."<br>"; ?>
		                                        	<? endif; ?>
		                                        <? endif; ?>
		                                        <div style="clear:both"></div>
	                                        <? endforeach; ?>
	                                        <br/>
	                                    <? endif; ?> 
	                                    
		                        <ul class="clear">
		                            
		                        	<li class="pcb-comment">
		                        		<? if(hasPermissions('about')): ?>
		                        			<a href="/<?=$$name_page?>/corporative/deleted/<?=$val['id']?>/" class="blog-link" onCLick="if(confirm('Удалить?')) return true; else return false;">Удалить</a> | <a href="/<?=$$name_page?>/corporative/edit/<?=$val['id']?>/#edit" class="blog-link">Редактировать</a> |
		                        			<? if($$usbank[$val['id_user']]['warn']<3 && $false): ?>
		                        				<span class="warnlink-<?=$val['id_user']?>"><a style="color: #D75A29; font-size:9px;" href="javascript: void(0);" onclick="banned.warnUser(<?=$val['id_user']?>, 0, 'blogs', 'all', 0); return false;">Сделать предупреждение (<span class="warncount-<?=$val['id_user']?>"><?=($$usbank[$val['id_user']]['warn'] ? $$usbank[$val['id_user']]['warn'] : 0)?></span>)</a></span>
		                        			<? endif; ?>
		                        		<? endif; ?>
		                        		
		                        		<a href="/<?=$$name_page?>/corporative/post/<?=$val['id']?>/" class="blog-link" <?if(get_uid() && (!$$lastDate[$val['id']] && $val['m_count']>0)):?>style="font-weight:bold;"<? endif; ?>>Комментарии (<span><?=intval($val['m_count'])?></span>)</a> 
		                        		<? if(get_uid() && is_array($$lastDate[$val['id']]) && ($c=$val['m_count']-$$lastDate[$val['id']]['count']) > 0): ?><a style="color:#6ba813;font-weight:bold" href="/<?=$$name_page?>/corporative/post/<?=$val['id']?>/#cnew">(<?=$c?> новы<?=ending($c, "й", "х", "х")?>)</a><? endif; ?>
		                        	</li>
		                        	
		                            <li>Опубликовано  <?=view_user($$usbank[$val['id_user']], '', ($is_emp?'employer':'freelancer').'-name', '');?><?/*<a href="/users/<?=strtolower($$usbank[$val['id_user']]['login'])?>/"><?=$$usbank[$val['id_user']]['uname'].' '.$$usbank[$val['id_user']]['usurname'];?></a>*/?> <?=date('d.m.Y в H:i', strtotime($val['date_create']))?> <?if($val['id_modified']):?> [внесены изменения <?=date('d.m.Y в H:i', strtotime($val['date_change']))?>]<?endif;?></li>
		                        	
		                            <? if($$tags[$val['id']]): $ct = count($$tags[$val['id']]); ?>
		                            <li class="tags">
                                        Теги: 
                                        <? $i=0;foreach($$tags[$val['id']] as $k=>$name):$i++; ?>
                                        	<a href="/<?=$$name_page?>/corporative/tags/<?=$k?>/"><?=$name?></a><?=($ct==$i?'':',')?> 
                                        <? endforeach; ?>
                                    </li>
                                    <? endif; ?>
		                        </ul>
		                    </div>
		                    <div id="warnreason-<?=$val['id']?>" style="display:none">&nbsp;</div>
		                    
		                    <? if(($val['id'] == $$IDEdit) && hasPermissions('about')): ?>
		                        
		                    	<div id='edit_new' style="float:right;width:650px;">
		                    	     <a name="edit"></a>
					            	<br>
									<h2>Редактирование:</h2>
									<form action="<?=$form_uri?>#edit" method="post" enctype="multipart/form-data" name="frm" id="frm" onkeypress="if((event.ctrlKey) && ((event.keyCode==10)||(event.keyCode==13))) {this.submit();}" onSubmit="if (!Locksubmit) { this.btn.value='Подождите'; this.btn.disabled=true; Locksubmit=1; } else { return false; }">
									<table cellpadding="5" style="cell-padding:10px;">
										<tr>
											<td style='width:150px;'>Заголовок:</td>
										 	<td><input type="text" name="title" id="title" style="width:500px;" value="<?=$$edpost['title']?$$edpost['title']:$val['title']?>"><br/><br/></td>
										</tr>
										
										<tr>
											<td>Текст:</td>
											<td><textarea style="width:500px;height:200px;" name="msg" id="msg"><?=$$edpost['msg']?$$edpost['msg']:$val['msg']?></textarea><br/>
											<? if ($$edalert[2]) print((view_error($$edalert[2]))."<br>"); ?>
											Можно использовать &lt;b&gt;&lt;i&gt;&lt;p&gt;&lt;ul&gt;&lt;li&gt;&lt;cut&gt;&lt;h&gt;
											</td>
										</tr>
										<tr>
											<td>Теги:</td>
											<td class="tag-content">
												<div id="tag_input" class="tag-list">
													<? if($$tags[$val['id']]): ?>
													<? foreach($$tags[$val['id']] as $k=>$tag):$j++; ?>
														<? if($j==1): ?>
															<div id="tag_<?=$k?>">
																<input type="text" name="tags[]" id="input_<?=$k?>" onblur="" autocomplete="off" onKeyUp="get_search_tag(this.value, <?=$k?>, event);" value="<?=$tag?>" >&nbsp;&nbsp;<img src="/images/add.gif" width="15" height="15" onClick="addTagInput();"/>
															</div>
														<? continue; endif; ?>
															<div id='tag_<?=$k?>'>
																<input type='text' name='tags[]' id="input_<?=$k?>" onblur="; "autocomplete="off" onKeyUp="get_search_tag(this.value, <?=$k?>, event);" value="<?=$tag?>">&nbsp;&nbsp;<img src='/images/minus.gif' width='15' height='4' vspace='4' onClick='delTagInput(<?=$k?>)'>
															</div>
													<? endforeach; ?>
													<? else: ?>
													<div id="tag_0">
														<input type="text" name="tags[]" id="input_0" onblur="" autocomplete="off" onKeyUp="get_search_tag(this.value, 0, event);" value="" >&nbsp;&nbsp;<img src="/images/add.gif" width="15" height="15" onClick="addTagInput();"/>
													</div>
													<? endif; ?>
												</div>
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
														<li>Картинку: 600x1000 пикселей. 300 Кб. </li>
													    <li>Файл: <?=(blogs::MAX_FILE_SIZE / (1024*1024))?> Мб.</li>
												    </ul>
												    Файлы следующих форматов запрещены к загрузке: <?=implode(', ', $GLOBALS['disallowed_array'])?>
												    
											    </div>	
											    <? if($$attach[$val['id']]): ?>
												     <h4 style="margin: 16px 0 4px 0">Добавленные файлы:</h4>
												     <ul style="margin-left:20px;">
												       <? $l_dir = substr($$usbank[$val['id_user']]['login'], 0, 2)."/".$$usbank[$val['id_user']]['login']; $dir = "upload"; ?>
												     	
												     	<? foreach($$attach[$val['id']] as $attach): $fname = $attach['name']; $cfile = new CFile("users/$l_dir/$dir/".$fname); ?>
					                                		<li><a href="<?=WDCPREFIX."/users/{$$usbank[$val['id_user']]['login']}/$dir/$fname"?>" target="_blank">Посмотреть</a> (<?=$cfile->getext();?>, <?=ConvertBtoMB($cfile->size);?>) <input type="checkbox" name="editattach[<?=$attach['id']?>]" value="1"> удалить</li>
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
									    		<div id="yt_link" style="padding-top:4px;<? if(!$$edalert[4]): ?>display:none<? endif; ?>">
									    			<input type="text" class="wdh100" name="yt_link" id="yt_link_val" value="<?=$$edpost['yt_link']?$$edpost['yt_link']:$val['yt_link']?>" style="width:500px;" onfocus="isFocus = true;" onblur="isFocus = false;"><br/>
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
                    	<? endforeach;?>
                    <? else: ?>
                    	<div class="pc-blog">
                        <h4><a href="">Корпоративные блоги</a></h4>
                        <p>Это корпоративный блог, здесь пока ничего нет поэтому здесь есть этот пост</p>
                        <ul class="clear">
                            <li class="pcb-comment"><a href="">Комментарии</a> <span>0</span></li>
                            <li>Опубликовано 20.02.2009 в 21:30</li>
                        </ul>
                    </div>
                    <? endif; ?>
                    
            <?=paginator($$page_corp, $$pages_corp, PAGINATOR_PAGES_COUNT, "%s/about/corporative/page/%d/%s");?>
           <? if(0) {?> <div class="rss">
                <a href="" class="ico_rss"><img src="/images/ico_rss.gif" alt="RSS" width="36" height="14" /></a>
            </div>
            <? } ?>
                </div>
                
                
                <? if(hasPermissions('about')): ?>
                
                <div id='add_new' style="float:right;width:700px;">
                            <a name="foo"></a>
			            	<br>
							<h2>Создать новое сообщение:</h2>
							<form action="<?=$form_uri?>#top" method="post" enctype="multipart/form-data" name="frm" id="frm" onkeypress="if((event.ctrlKey) && ((event.keyCode==10)||(event.keyCode==13))) {this.submit();}" onSubmit="if (!Locksubmit) { this.btn.value='Подождите'; this.btn.disabled=true; Locksubmit=1; } else { return false; }">
							<table cellpadding="5" style="cell-padding:10px;">
								<tr>
									<td style='width:150px;'>Заголовок:</td>
								 	<td><input type="text" name="title" id="title" style="width:500px;" value="<?=$$post['title']?>"><br/><br/></td>
								</tr>
								
								<tr>
									<td>Текст:</td>
									<td><textarea style="width:500px;height:200px;" name="msg" id="msg"><?=$$post['msg']?></textarea><br/>
									<? if ($$alert[2]) print((view_error($$alert[2]))."<br>"); ?>
									Можно использовать &lt;b&gt;&lt;i&gt;&lt;p&gt;&lt;ul&gt;&lt;li&gt;&lt;cut&gt;&lt;h&gt;<br/><br/>
									</td>
								</tr>
								<tr>
									<td>Теги:</td>
									<td class="tag-content">
											<div id="tag_input_add" class="tag-list">
												<? if($$post['tags']): ?>
												<? foreach($$post['tags'] as $k=>$tag):$t++; ?>
													<? if($t==1): ?>
														<div id="tag_0">
															<input type="text" name="tags[]" id="input_0" autocomplete="off" onKeyUp="get_search_tag(this.value, 0, event);" value="<?=$tag?>" >&nbsp;&nbsp;<img src="/images/add.gif" width="15" height="15" onClick="addTagInput('tag_input_add');"/>
														</div>
													<? continue; endif; ?>
														<div id='tag_<?=$k?>'>
															<input type='text' name='tags[]' id="input_<?=$k?>" autocomplete="off" onKeyUp="get_search_tag(this.value, <?=$k?>, event);" value="<?=$tag?>">&nbsp;&nbsp;<img src='/images/minus.gif' width='15' height='4' vspace='4' onClick='delTagInput(<?=$k?>)'>
														</div>
												<? endforeach; ?>
												<? else: ?>
												<div id="tag_0">
													<input type="text" name="tags[]" id="input_0" autocomplete="off" onKeyUp="get_search_tag(this.value, 0, event);" value="" >&nbsp;&nbsp;<img src="/images/add.gif" width="15" height="15" onClick="addTagInput('tag_input_add');"/>
												</div>
												<? endif; ?>
											</div>
										</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><br/><a href="javascript:void(0);" class="blue" onClick="toggle_box('attach');">+ Прикрепить файл к сообщению (<?=(blogs::MAX_FILE_SIZE / (1024*1024))?> Мб)</a></td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td>
										<div id="attach" style="display:<?=($$alert[3]?'block':'none')?>;padding-top:4px">
											<input type="hidden" name="MAX_FILE_SIZE" value="<?=blogs::MAX_FILE_SIZE?>">
												<div id="ad_button">
												  <div>	
													<div id="attaches">
												    	<input type="file" name="attach[]" class="input-file" size="50"><span class="addButton" style="font-size: 12px;">&nbsp;</span>
												    </div>
												   </div>
											    </div>
												<? if ($$alert[2]) print((view_error($$alert[2]))); ?>
												
										
											С помощью этого поля возможно загрузить:
											<ul style="padding: 0;margin-left:20px;">
												<li>Картинку: gif, jpeg. 600x1000 пикселей. 300 Кб. </li>
											    <li>Файл: <?=(blogs::MAX_FILE_SIZE / (1024*1024))?> Мб.</li>
										    </ul>
										    Файлы следующих форматов запрещены к загрузке: <?=implode(', ', $GLOBALS['disallowed_array'])?>
									    </div>	
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td>
										<? if(($$alert[3])) print(view_error($$alert[3]) . '<br><br>');?>
									</td>
								</tr>
								<tr>
							        <td>&nbsp;</td>
							        <td><a href="javascript:void(null);" class="blue" onClick="toggle_box('yt_link');">+ Добавить ссылку на YouTube видео</a></td>
						        </tr>
						        <tr>
							    	<td>&nbsp;</td>
							    	<td>
							    		<div id="yt_link" style="padding-top:4px;<? if(!$$alert[4]): ?>display:none<? endif; ?>">
							    			<input type="text" class="wdh100" name="yt_link" id="yt_link_val" value="<?=$$post['yt_link']?>" style="width:500px;" onfocus="isFocus = true;" onblur="isFocus = false;"><br/>
							    		</div>
							            <? if($$alert[4]) print((view_error($$alert[4]))); ?>
							    	</td>
						        </tr>
						        <tr>
						        	<td>&nbsp;</td>
						        	<td>
						        		<br/><input type="submit" name="btn" class="btn" value="Создать">
						        		<input type="hidden" name="blogID" value="0">
						        		<input type="hidden" name="parent" id="parentID" value="0">
						        		<input type="hidden" name="action" value="addnew">
						        	</td>
						        </tr>	
							</table>
							</form>
							
					</div>
				
               <? endif; ?> 
              
            </div>
             <div style="text-align:right;padding:10px;"><a href="/rss/corporative.php"><img src="http://www.free-lance.ru/images/ico_rss.gif"></a> <a href="/rss/corporative.php">Фри-ланс</a></div>
        </div>
    </div>	
</div>
 <a name="bottom" ></a> 
{{include "footer.tpl"}}
