<h1 class="b-page__title">Черновики</h1>
<div class="b-menu b-menu_tabs b-menu_padbot_20">
    <ul class="b-menu__list b-menu__list_padleft_20">
        <? if(is_emp()) { ?>
        <li class="b-menu__item <?=($p=='projects'?'b-menu__item_active':'')?>"><a class="b-menu__link" href="/drafts/?p=projects" title="Проекты"><span class="b-menu__b1">Проекты</span></a></li>
        <? } ?>
        <li class="b-menu__item <?=($p=='contacts'?'b-menu__item_active':'')?>"><a class="b-menu__link" href="/drafts/?p=contacts" title="Сообщения"><span class="b-menu__b1">Сообщения</span></a></li>
        <? if(BLOGS_CLOSED == false) { ?><li class="b-menu__item <?=($p=='blogs'?'b-menu__item_active':'')?>"><a class="b-menu__link" href="/drafts/?p=blogs" title="Блоги"><span class="b-menu__b1">Блоги</span></a></li> <?}//if?>
        <li class="b-menu__item b-menu__item_last <?=($p=='communes'?'b-menu__item_active':'')?>"><a class="b-menu__link" href="/drafts/?p=communes" title="Темы в сообществах"><span class="b-menu__b1">Темы в сообществах</span></a></li>
    </ul>
</div>

