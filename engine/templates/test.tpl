{{include "header.tpl"}}

<link rel="stylesheet" type="text/css" href="/engine/js/back/ext-2.2/resources/css/xtheme-fl.css" ></link>
<script>
Ext.onReady(function(){
//admin.openPopup('team', 0);
admin.openPopup('cblog', 1); 
//admin.global_wait.show();
//    Ext.MessageBox.confirm("d");
});
</script>
<div class="">

</div>
<div class="body clear">
    <div class="main  clear">
        <h2>О проекте</h2>
        <div class="rcol-big">
            <div class="press-center clear">
                {{include "press_center/press_menu.tpl"}}
                <div class="pc-content">
                    <? if(is_moder() || is_admin()) { ?><div style="float:right;">[<a href="javascript:void(0);" onclick="admin.openPopup('staticPages', '<?=$$text["alias"];?>');">Редактировать</a>]</div><? } ?>
                    <h3>История</h3>
                    <div class="pc-text">
                        <?=$$text["n_text"];?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{include "footer.tpl"}}