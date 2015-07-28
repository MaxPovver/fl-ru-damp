{{include "header.tpl"}}
<div class="b-layout b-layout__page">
    <div class="b-layout__txt"><a class="b-layout__link" href="/about/">О проекте</a> &rarr;</div>
    <h1 class="b-page__title b-page__title_padbot_30">Реклама</h1>
    <div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right">
        <? if(hasPermissions('about')) { ?><div style="float:right;">[<a href="javascript:void(0);" onclick="admin.openPopup('staticPages', '<?=$$text["alias"];?>');">Редактировать</a>]</div><? } ?>
        <div class="pc-text oldStyles">
            <?=$$text["n_text"];?>
        </div>
    </div>
</div>
{{include "press_center/press_menu.tpl"}}
{{include "footer.tpl"}}
