{{include "header.tpl"}}
<h1 class="b-page__title">Пресс-центр</h1>
<div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right">
        <? if(hasPermissions('about')) { ?><div style="float:right;">[<a href="javascript:void(0);" onclick="admin.openPopup('staticPages', '<?=$$text["alias"];?>');">Редактировать</a>]</div><? } ?>
        <h2 class="b-layout__title">Реклама</h2>
        <div class="pc-text oldStyles">
            <?=$$text["n_text"];?>
        </div>
</div>
    {{include "press_center/press_menu.tpl"}}
{{include "footer.tpl"}}
