<div class="b-layout b-layout_pad_10 b-layout_bord_e6 b-layout_relative b-layout_margbot_10 b-promo__servis b-promo__servis_pl-car service" data-name="pay_place">
    <input type="hidden" name="opcode" value="0" />
    <input type="hidden" name="duration" value="0" />
    <input type="hidden" name="auto" value="0" />
    
    <span class="b-layout__txt b-layout__txt_float_right b-layout__txt_fontsize_11 b-page__desktop b-page__ipad"><a href="/pay_place/top_payed.php" class="b-layout__link promo-link">Подробнее об услуге</a></span>
    <h3 class="b-layout__h3 b-layout__h3_padleft_70 b-layout__txt_padleft_null_iphone">
        Карусель  &#160;&#160;
        <? if($service['type'] == 'lately') { //if?>
            <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_808080 b-layouyt__txt_weight_normal">Срок действия истек <?= date('d.m.Y', strtotime($service['d']))?></span>
        <? } //if?>
    </h3>
    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_20 b-layout__txt_padleft_70 b-layout__txt_padleft_null_iphone">Что может быть заметнее рекламного объявления на главной странице или наверху каталога фрилансеров? Это очень эффективный способ продвижения вашего аккаунта на сайте. Прокатитесь на Карусели и «накатайте» выгодный проект.</div>
    <div class="b-buttons b-buttons_padleft_70 b-buttons_padbot_10 b-layout__txt_padleft_null_iphone">
        <a href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green btn-pay"><?= billing::$btn_name_for_type[$service['type']] ?></a>
    </div>
    <span class="b-layout__txt b-layout__txt_padtop_10 b-layout__txt_fontsize_11 b-page__iphone"><a href="/pay_place/top_payed.php" class="b-layout__link promo-link">Подробнее об услуге</a></span>
</div>