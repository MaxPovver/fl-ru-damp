<div class="b-layout b-layout_pad_10 b-layout_bord_e6 b-layout_relative b-layout_margbot_10 b-promo__servis  b-promo__servis_ff service" data-name="verify_ff_<?= $service['id']?>" data-cost="<?= round($service['ammount'])?>">
    <a href="javascript:void(0)" class="b-button b-button_admin_del b-button_float_right service-remove"></a>
    <input type="hidden" name="opcode" value="<?= $service['op_code']; ?>" />
    <h3 class="b-layout__h3 b-layout__h3_padleft_70">
        Верификация через FF.ru
        &nbsp;&nbsp; <span class="b-layout__txt b-layout__txt_fontsize_11 b-layouyt__txt_weight_normal"><a href="http://feedback.free-lance.ru/article/details/id/862" class="b-layout__link">Подробнее об услуге</a></span>
    </h3>
    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_20 b-layout__txt_padleft_70">
        Верификация – это процедура проверки паспортных данных, рекомендованная для фрилансеров и работодателей. Рядом с именем верифицированного пользователя появляется отметка «Личность подтверждена», на вкладке "Финансы" в профиле отображаются реальные имя и фамилия.
    </div>
    <div class="b-layout__txt b-layout__txt_padleft_70 b-layout__txt_fontsize_22 b-layout__txt_color_fd6c30"><span class="upd-cost-sum"><?= to_money($service['ammount'])?></span> руб.</div>
</div>