<?php if(hasPermissions('payments')) { ?>
<div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_padbot_30 b-layout__txt_clear_left">
    <? /*= access_view('Все услуги', '<a href="/bill/" class="b-layout__link b-layout__link_bold">%s</a>', ($bill->name_page != 'index'))?> &#160;&#160;&#160;&#160;
    
    <? /*
    if($bill->count > 0) {
        echo access_view('Список заказов', '<a href="/bill/orders/" class="b-layout__link b-layout__link_bold">%s</a>', ($bill->count > 0 && $bill->name_page != 'orders'))?>
        <span class="b-layout__txt b-layouyt__txt_weight_normal b-layout__txt_color_808080" id="count_orders"><?= $bill->count;?></span>&#160;&#160;&#160;&#160;
        <?
    }*/
    
    echo access_view('Перевод средств', '<a href="/bill/send/" class="b-layout__link b-layout__link_color_c7271e b-layout__link_bold">%s</a>', ($bill->name_page != 'send'));
    ?>
</div>
<?php } ?>
