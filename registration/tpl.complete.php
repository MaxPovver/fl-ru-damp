<div class="b-layout">
    <div class="b-layout__right b-layout__right_width_73ps b-layout__right_float_right">
        <h1 class="b-page__title b-page__title_padbot_30">Активация аккаунта</h1>
    </div>
    <div class="b-layout__right b-layout__right_width_73ps b-layout__right_float_right">
        <div class="b-fon b-fon_inline-block b-fon_padbot_10">
            <div class="b-fon__body b-fon__body_pad_15  b-fon__body_padleft_30 b-fon__body_lineheight_18 b-fon__body_padright_40 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
                <span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span>Вы успешно зарегистрировались на сайте FL.ru. 
                На ваш email-адрес отправлено письмо для подтверждения регистрации.
            </div>
        </div>	
        <?php if ($allow_resend_mail): ?>
            <form name="form_mail_send" id="form_mail_send" method="POST">
                <input type="hidden" name="action" value="<?= registration::ACTION_SEND_MAIL; ?>">
            </form>
            <div class="b-layout__txt b-layout__txt_padbot_10">
                Я не получил письмо,
                <a class="b-layout__link b-layout__link_color_a7a7a6 b-layout__link_no-decorat disabled" id="resend_activate_link">отправить еще раз</a> 
                <span id="resend_activate_counter"></span></div>
        <?php endif; ?>
    </div>
</div>
