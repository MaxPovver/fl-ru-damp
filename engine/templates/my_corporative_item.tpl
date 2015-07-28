<? 
    if(!$blog && $$blog) $blog = $$blog;
    if(!$comment && $$comment) $comment = $$comment;
    if(!$blog && $$blog) $blog = $$blog;
    if(!$attach && $$attach) $attach = $$attach;
?>
<div class="pc-blog" id="corp_blog_<?=$blog['id']?>">
    <h4><a href="/myblog/corporative/post/<?=$blog['id']?>/"><?=$blog['title']?></a>
    <?/* if(is_moder() || is_admin()) { ?>
        <a href="javascript:void(0);" onclick="admin.openPopup('cblog', <?=$blog["id"];?>);"><img height="19" width="20" border="0" align="absmiddle" src="/team/images/ico_edit.gif" alt="Редактировать блог"/></a>
    <? } */?>
    </h4>
    <p><?=$blog['msg']?></p>
    
    <? if($$attach[$blog['id']]): ?>
                <? foreach($$attach[$blog['id']] as $attach): ?>
                    <? if ($attach['name']): $att_ext = strtolower($attach['name']); ?>
                        <?  if ($att_ext == "swf"): ?>
                            <?="<br>".viewattachExternal($$usbank[$blog['id_user']]['login'], $attach['name'], "upload", "/blogs/view_attach.php?user=".$$usbank[$blog['id_user']]['login']."&attach=".$attach['name']); ?>
                        <? else: ?>
                            <?="<br>".viewattachLeft($$usbank[$blog['id_user']]['login'], $attach['name'], "upload", $file, 1000, 470, 307200, !$attach['small'], (($attach['small']==2)?1:0))."<br>"; ?>
                        <? endif; ?>
                    <? endif; ?>
                    <div style="clear:both"></div>
                <? endforeach; ?>
                <br/>
            <? endif; ?> 
            
    <ul class="clear">
        <li class="pcb-comment">
            <? if(is_moder() || is_admin()): ?>
                <a class="ajax" href="javascript:void(0)" onCLick="admin.loadAndExec('cblogEdit', 'cblogClass.deleteItem', [<?=$blog["id"];?>, function() {Ext.get('corp_blog_<?=$blog["id"];?>').remove()}]);">Удалить</a> | <a  class="ajax" href="javascript:void(0)" onclick="admin.openPopup('cblog', <?=$blog['id']?>, {afterOk:function(data) {var el = Ext.get('corp_blog_<?=$blog["id"];?>');el.insertHtml('afterEnd',data.html);el.remove()}}); return false;">Редатировать</a> |
            <? endif; ?>
            <a href="/myblog/corporative/post/<?=$blog['id']?>/">Комментарии (<span><?=intval($$comment[$blog['id']])?></span>)</a> 
        </li>
        <li>Опубликовано  <?=view_user($$usbank[$blog['id_user']], '', '', '');?><?/*<a href="/users/<?=strtolower($$usbank[$blog['id_user']]['login'])?>/"><?=$$usbank[$blog['id_user']]['uname'].' '.$$usbank[$blog['id_user']]['usurname'];?></a>*/?> <?=date('d.m.Y в H:i', strtotime($blog['date_create']))?> <?if($blog['id_modified']):?><br/>[внесены изменения <?=date('d.m.Y в H:i', strtotime($blog['date_change']))?>]<?endif;?></li>
    </ul>
</div>