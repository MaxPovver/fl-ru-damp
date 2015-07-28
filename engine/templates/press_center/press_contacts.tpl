{{include "header.tpl"}}
<h1 class="b-page__title">Пресс-центр</h1>
<div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right">
    <h2 class="b-layout__title">Наши контакты</h2>
    <div class="b-layout__txt">Наш почтовый адрес:<br>129223, Москва, а/я 33;<br>Обязательно укажите наименование организации-получателя &mdash; ООО «ВААН».<br><br>
    <a class="b-layout__link" target="_blank" href="/promo/adv/">Размещение рекламы на сайте</a><br>
    <a class="b-layout__link" href="mailto:adv@fl.ru">adv@fl.ru</a> &mdash; по вопросам сотрудничества со СМИ и организации спецпроектов<br>
    </div>                            

    <? /* if(hasPermissions('about')) { ?><div style="float:right;">[<a href="javascript:void(0);" onclick="admin.openPopup('staticPages', '<?=$$text["alias"];?>');">Редактировать</a>]</div><? } */ ?>
    <?php /* =$$text["n_text"]; */ ?>
</div>
<style type="text/css">
@media screen and (max-width: 960px){
.b-layout__page .b-layout__left, .b-layout__right {
    display: block;
    width: 100% !important;
}
}
@media screen and (max-width: 640px){
.b-layout__right .b-layout__txt img{ width:100%;}
}
</style>
{{include "press_center/press_menu.tpl"}}
{{include "footer.tpl"}}