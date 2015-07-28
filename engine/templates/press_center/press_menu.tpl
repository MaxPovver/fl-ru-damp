<style type="text/css">
a.active_menu {
	color:#b2b2b2;
	text-decoration:none	
}

a.active_menu:hover {
	text-decoration:underline;
}
</style>
<div class="b-layout__left b-layout__left_width_25ps">
    <div class="pc-menu">
        <h3 style="margin-left:0">Пресс-центр</h3>
        <ul>
            <li><?=($$page == "press" && ($$action == "advAction") ? '<a href="/promo/adv/" class="active_menu">Реклама</a>' : '<a href="/promo/adv/">Реклама</a>');?></li>
            <li><?=($$page == "press" && ($$action == "contactsAction") ? '<a href="/press/contacts/" class="active_menu">Контакты</a>' : '<a href="/press/contacts/">Контакты</a>');?></li>
            <li><a href="/about/politika_po_obrabotke_pdn.pdf" target="_blank">Политика по обработке персональных данных</a></li>
            <li><a href="/about/polozhenie_po_obespecheniu_bezopasnosti_pdn.pdf" target="_blank">Положение по обеспечению безопасности персональных данных</a></li>
        </ul>
    </div>
    <div class="pc-menu">
        <h3 style="margin-left:0">О проекте</h3>
        <ul>
            <li><?=($$page == "about" && ($$action == "teamAction") ? '<span>Команда</span>' : '<a href="/about/team/">Команда</a>');?></li>
            <li><?=($$page == "about" && ($$action == "servicesAction") ? '<span>Услуги</span>' : '<a href="/service/">Услуги</a>');?></li>
            <? if(0) {?> <li><?=sprintf($$page == "about" && ($$action == "faqAction") ? '<a href="%s" class="active_menu">%s</a>' : '<a href="%s">%s</a>', "/about/faq/", "Помощь");?></li><? } ?>
            <li><?=($$page == "about" && ($$action == "rulesAction") ? '<span>Правила сайта</span>' : '<a href="/about/appendix_2_regulations.pdf">Правила сайта</a>');?></li>
            <li><?=($$page == "about" && ($$action == "offerAction") ? '<span>Пользовательское соглашение</span>' : '<a href="/about/agreement_site.pdf">Пользовательское соглашение</a>');?></li>
            <li><?=sprintf('<a href="%s">%s</a>', "/about/appendix_1_price.pdf", "Перечень платных услуг");?></li>
            <li><?=sprintf('<a href="%s">%s</a>', "/about/appendix_4_service.pdf", "Перечень бесплатных услуг");?></li>
            <li><?=($$page == "about" && ($$action == "tpoAction") ? '<span>Требования к ПО</span>' : '<a href="/about/appendix_3_software_requirements.pdf">Требования к ПО</a>');?></li>
            <li><?=($$page == "about" && ($$action == "feedbackAction") ? '<span>Форма обратной связи</span>' : '<a href="/about/feedback/">Форма обратной связи</a>');?></li>
        </ul>
    </div>

    <?= printBanner240(false, false, false); ?>

</div>
