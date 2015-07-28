{{include "header.tpl"}}
<div class="body clear">
    <div class="main  clear">
        <h2>О проекте</h2>
        <div class="rcol-big">
            <div class="press-center clear">
                {{include "press_center/press_menu.tpl"}}
                <div class="pc-content">
                    <? if(hasPermissions('about')) { ?><div style="float:right;">[<a href="javascript:void(0);" onclick="admin.openPopup('staticPages', '<?=$$text["alias"];?>');">Редактировать</a>]</div><? } ?>
                    <h3>Сервисы</h3>
                    <?=$$text["n_text"];?>
                </div>
            </div>
        </div>
    </div>
</div>
{{include "footer.tpl"}}