<h2>Управление проектами по &laquo;Безопасной Сделке&raquo;</h2>

<div class="b-menu b-menu_tabs ">
<div class="nr-a-settings">
    <a href="/siteadmin/norisk/?page=exrates">Курсы валют и проценты</a>
    <a href="javascript:window.print()" class="nr-a-lnk-print">Распечатать</a>
</div>
    <ul class="b-menu__list b-menu__list_padleft_10">
        <li class="b-menu__item <?=(!$mode || $mode=='all' ? 'b-menu__item_active' : '')?> "><a class="b-menu__link" href="?site=admin"><span class="b-menu__b1">«Все Сделки» (<?=(int)$sbr_count['all']?>)</span></a></li>
        <li class="b-menu__item <?=($mode=='arbitrage' ? 'b-menu__item_active' : '')?>"><a class="b-menu__link b-menu__link_color_c10600" href="?site=admin&mode=arbitrage"><span class="b-menu__b1"><span style="color:#c10600">С обр. в Арбитраж</span> (<?=(int)$sbr_count['arbitrage']?>)</span></a></li>
        <li class="b-menu__item <?=($mode=='payouts' ? 'b-menu__item_active' : '')?>"><a class="b-menu__link" href="?site=admin&mode=payouts"><span class="b-menu__b1">Выплаты (<?=(int)$sbr_count['payouts']?>)</span></a></li>
        <li class="b-menu__item <?=($mode=='feedbacks' ? 'b-menu__item_active' : '')?>"><a class="b-menu__link" href="?site=admin&mode=feedbacks"><span class="b-menu__b1">Отзывы (<?=(int)$sbr_count['feedbacks']?>)</span></a></li>
        <li class="b-menu__item b-menu__item_last <?=($mode=='reports' ? 'b-menu__item_active' : '')?>"><a class="b-menu__link" href="?site=admin&mode=reports"><span class="b-menu__b1">Выгрузки</span></a></li>
        <li class="b-menu__item b-menu__item_last <?=($mode=='lc' ? 'b-menu__item_active' : '')?>"><a class="b-menu__link" href="?site=admin&mode=lc"><span class="b-menu__b1">Аккредитив</span></a></li>
    </ul>
</div>
