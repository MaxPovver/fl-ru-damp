{{include "header.tpl"}}
<div class="body clear">
    <div class="main  clear">
        <h2>Пресс-центр</h2>
        <div class="rcol-big">
            <div class="press-center clear">            
                {{include "press_center/press_menu.tpl"}}
                <div class="pc-content">
                    <? if(hasPermissions('about')) { ?><div style="float:right;">[<a href="javascript:void(0);" onclick="admin.openPopup('partners', 0); return false;">Добавить партнера</a>]</div><? } ?>
                    <h3>Партнеры</h3>
                    <div class="pc-content">
                        <? if($$msgs) foreach($$msgs as $msg) : ?>
                        <div class="smi-block-one">
                            <div>
                                <? if(hasPermissions('about')) { ?>
                                    <a href="javascript:void(0);" onclick="admin.openPopup('partners', <?=$msg["id"];?>);"><img style="height:19px; width:20px; border:0; vertical-align:middle" src="/images/ico_edit_news.gif" alt="Редактировать"/></a>
                                    <a href="javascript:void(0);" onclick="admin.loadAndExec('partnersEdit', 'partnersClass.deleteItem', [<?=$msg["id"];?>, function() {admin.reload()}]);"><img style="height:19px; width:20px; border:0; vertical-align:middle"  src="/images/ico_delete_news.gif" alt="Удалить новость" /></a>
                                <? } ?>
                                <p><b><?=reformat($msg["sign"]);?></b><? if($msg['link'] != "") { ?><br /><?=reformat($msg["link"]);?><? } ?></p>
                                <br />
                                <p><?=$msg["msgtext"];?></p>
                            </div>
                            <div class="smi-block-logo"><? if ($msg['link'] && $msg['logo']) { ?><img border="0" alt="" src="<?=WDCPREFIX;?>/about/press/<?=$msg['logo']?>" /><? } ?></div>
                        </div>
                        <? endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{include "footer.tpl"}}