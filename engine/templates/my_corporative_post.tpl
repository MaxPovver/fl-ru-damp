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
    a.ajax {
        text-decoration: none;
        border-bottom:1px dashed #003399;
    }
    
    a.ajax:hover {
        text-decoration: none;
        border-bottom:1px dashed #6BB24B;
    }
</style>

<script>

</script>
<div class="body clear">
    <div class="main  clear">
        <h2>О проекте</h2>
        <div class="rcol-big">
            <div class="press-center clear">
                {{include "press_center/press_menu.tpl"}}
                <div class="pc-content">
                	<a name="top"></a>
                    <div style="float:right;">[<a href="javascript:void(0);" onclick="history.go(-1);"><strong style="font-weight:bold;">Назад</strong></a>]</div>
                    {{include "my_corporative_post_item.tpl"}}	            
		            <? if($$sortComm): ?><h4>Комментарии: <?#intval($$count_comment)?></h4><? endif; ?>
		            <br/>
		            <? if($$sortComm): ?>
			            <ul class='corp-comm' id="comment_content">
			            <? foreach($$sortComm as $k=>$lvl): $is_emp=is_emp($$comments[$k]['role']);?>
				            <li id="comment<?=$k?>" <?=($lvl>0)?'style="margin-left:'.(25*$lvl).'px;"':'class="top-comm"'?> <? if($lvl==0 && $end>0): ?>style="border-top:1px solid #D7D7D7;<? endif; ?>">
				            <a name="c<?=$k?>"></a>
				            <br/>
					        	<span><?=view_avatar($$comments[$k]['login'], $$comments[$k]['photo'])?> </span>
					            <div class="corp-comm-info">
						            <?/*$$session->view_online_status($$comments[$k]['login'])?><a href="/users/<?=$$comments[$k]['login']?>/"><?=$$comments[$k]['uname'].' '.$$comments[$k]['usurname']?> [<?=$$comments[$k]['login']?>]</a>	*/?>
						            <?/*<p class="userInfo">
						            	<div style="float:left;border:1px black solid;"><?=($$comments[$k]['is_pro']=='t' ? ($is_emp ? view_pro_emp() : view_pro2($$comments[$k]['is_pro_test']=='t')).'&nbsp;' : '')?></div>
						            	<div style="float:left;border:1px red solid;"><?=$$session->view_online_status($$comments[$k]['login'])?></div>
						            </p>*/?>
						            <?=view_user($$comments[$k], '', '', '');?> [<?=date('d.m.Y в H:i', strtotime($$comments[$k]['date_create']))?>]  
						            <? if($$comments[$k]['id_deleted'] && is_moder()): ?>
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
						            		<span <? if(is_moder()): ?>title="<?=$$moders[$$comments[$k]['id_modified']]['login'].": ".$$moders[$$comments[$k]['id_modified']]['usurname']." ".$$moders[$$comments[$k]['id_modified']]['uname']?>" <? endif; ?>>Отредактировано модератором</span> [
						            	<?endif;?> 
						            	<?=date('d.m.Y | H:i', strtotime($$comments[$k]['date_change']))?>]
						            <? endif; ?><br/><br/>
						            
						            <div style="clear:both"></div>
						            <? if($$comments[$k]['id_deleted'] && !is_moder()): ?>
							            <p><? if($$comments[$k]['id_deleted']==$$comments[$k]['id_user']):?> Комментарий удален автором<?else:?>Комментарий удален модератором<?endif;?> <?=date("[d.m.Y | H:i]", strtotime($$comments[$k]['date_deleted']))?></p><br/><br/>
						            <? else: ?>
						            	<? if($$comments[$k]['title']): ?><h2 <?=(is_moder()&&$$comments[$k]['id_deleted'])?"style='color:silver'":""?>><?=$$comments[$k]['title']?></h2><? endif; ?>
							            <p <?=(is_moder()&&$$comments[$k]['id_deleted'])?"style='color:silver'":""?>><?=$$comments[$k]['msg']?></p><br/><br/>
							            
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
							            	<?=show_yt($k, $$comments[$k]['yt_link']); ?>
							            	<br/>
							            <? endif; ?>
							            </div>
							            <div style="clear:both"></div>
							           
							            <? if(!$$comments[$k]['id_deleted']): ?>
							            <p><? if($$comments[$k]['id_user'] == get_uid() || is_admin() || is_moder()): ?><a class="ajax" href="javascript:void(0)" onCLick="admin.loadAndExec('cblogEdit', 'cblogClass.deleteItem', [<?=$k;?>, function() {admin.reload()}]);">Удалить</a> | <a class="ajax" href="javascript:void(0)" onclick="admin.openPopup('cblog', <?=$k;?>);">Редактировать</a> | <? endif; ?><? if(get_uid()): ?><a class="ajax" href="javascript:void(0)" onclick="admin.openPopup('cblog', <?=$k;?>, {comment:true, parent:<?=$$blog["id"];?>});">Комментировать</a> | <? endif; ?><a href="/myblog/corporative/post/<?=$$blog['id']?>/#c<?=$k?>">Ссылка</a></p>
						            	<? elseif(is_moder()):?>
						            		<p><a class="ajax" href="renew/<?=$k?>/">Вернуть</a></p>
							            <? endif; ?>
						            <? endif; ?>
						        </div>
					            <div style="clear:both"></div>
				            <li>
				           <? $end = $lvl; endforeach; ?>
			            	
			            	
			            </ul>
			            
			            
		            <? endif; ?>
		            <a style="float:right;" href="#top">Наверх</a>	
                </div>
                
            </div>
        </div>
    </div>
</div>      

{{include "footer.tpl"}}