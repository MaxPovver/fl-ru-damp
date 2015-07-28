{{include "header.tpl"}}
<div class="body clear">
    <div class="main  clear">
        <h2>Пресс-центр</h2>
        <div class="rcol-big">
            <div class="press-center clear">            
                {{include "press_center/press_menu.tpl"}}
                <div class="pc-content">
                    <? if(hasPermissions('about')) { ?><div style="float:right;">[<a href="javascript:void(0);" onclick="admin.openPopup('opinions', 0); return false;">Добавить отзыв</a>]</div><? } ?>
                    <h3>Отзывы</h3>
                    <div class="pc-content">
                        <? foreach($$msgs as $msg) : ?>
                        <div class="smi-block-one">
                            <div>
                                <? if(hasPermissions('about')) { ?>
                                    <a href="javascript:void(0);" onclick="admin.openPopup('opinions', <?=$msg["id"];?>);"><img height="19" width="20" border="0" align="absmiddle" src="/images/ico_edit_news.gif" alt="Редактировать"/></a>
                                    <a href="javascript:void(0);" onclick="admin.loadAndExec('opinionsEdit', 'opinionsClass.deleteItem', [<?=$msg["id"];?>, function() {admin.reload()}]);"><img height="19" width="20" border="0" align="absmiddle" src="/images/ico_delete_news.gif" alt="Удалить новость"/></a>
                                <? } ?>
                                <p><?=reformat($msg["msgtext"]);?></p>
                                <p><?=$msg["short"];?></p>
                                <p class="smi-from"><?=reformat($msg["sign"]);?><? if($msg['link'] != "") { ?><br><?=reformat($msg["link"]);?><? } ?></p>
                            </div>
                            <div class="smi-block-logo"><? if ($msg['link'] && $msg['logo']) { ?><img border="0" alt="" src="<?=WDCPREFIX;?>/about/opinions/<?=$msg['logo']?>"/><? } ?></div>
                        </div>
                        <? endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{include "footer.tpl"}}