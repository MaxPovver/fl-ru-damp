<div class="i-shadow i-shadow_zindex_110 ">
    <div class="b-shadow b-shadow_width_950 b-shadow_vertical-center" >
        <div class="b-shadow__right">
            <div class="b-shadow__left">
                <div class="b-shadow__top">
                    <div class="b-shadow__bottom">
                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20 b-layout">


<h2 class="b-shadow__title b-shadow__title_padbot_30 b-shadow__title_fontsize_22">Правила обмена сообщениями</h2>

<table class="b-layout__table b-layout__table_margbot_100 b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0">
    <tr class="b-layout__tr">
        <td class="b-layout__one">
            <img class="b-layout__pic b-layout__pic_margright_20" src="/images/promo-icons/big/13.png" alt=""  />
        </td>
        <td class="b-layout__right">
            <h3 class="b-layout__h3 b-layout__h3_padbot_5">Обмен контактами без аккаунта PRO<br />запрещен</h3>
            <div class="b-layout__txt">Пользователям без профессионального аккаунта<br />запрещен обмен контактными данными<br />(номерами телефонов, адресами электронной<br />почты, сайтов, логинами Skype, ICQ, аккаунтами<br />в социальных сетях) вне сервиса «Безопасная<br />Сделка». Для того чтобы ваша контактная<br />информация была в открытом доступе,<br />приобретайте <a href="/payed" class="b-layout__link">аккаунт PRO</a>.</div>
        </td>
        <td class="b-layout__one b-layout__one_padleft_20">
            <img class="b-layout__pic b-layout__pic_margright_20" src="/images/promo-icons/big/14.png" alt=""  />
        </td>
        <td class="b-layout__left b-layout__left_padright_20">
            <h3 class="b-layout__h3 b-layout__h3_padbot_5">Все сообщения модерируются</h3>
            <div class="b-layout__txt">Любое сообщение может быть заблокировано,<br />если в нём будет содержаться запрещённая<br />информация.</div>
        </td>
    </tr>
</table>

<div class="b-buttons b-buttons_padleft_122 b-buttons_padbot_30">
    <div class="b-check b-check_float_left b-check_padtop_10 b-check_padright_30">
        <input id="b-check1" class="b-check__input" name="" type="checkbox" value="" onclick="if (this.get('checked')) {$('close_mess_splash').removeClass('b-button_round_color_disable')} else {$('close_mess_splash').addClass('b-button_round_color_disable')}" />
        <label class="b-check__label b-check__label_fontsize_13" for="b-check1">Я согласен с <a class="b-layout__link" href="<?=WDCPREFIX?>/about/documents/appendix_2_regulations.pdf">новыми условиями</a></label>
    </div>
    <a id="close_mess_splash" href="javascript:void(0)" onclick="if ($('b-check1').get('checked')) {$('b-shadow__overlay').dispose(); $$('.b-shadow_main_content').addClass('b-shadow_hide'); window.location='/contacts/?action=accept_new_rules&url=<?=$_SERVER['REQUEST_URI']?>';} return false;" class="b-button b-button_round_green b-button_round_color_disable">
        <span class="b-button__b1">
            <span class="b-button__b2"><span class="b-button__txt">Продолжить работу</span></span>
        </span>
    </a>
</div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-shadow__tl"></div>
        <div class="b-shadow__tr"></div>
        <div class="b-shadow__bl"></div>
        <div class="b-shadow__br"></div>
    </div>
</div>

<div id="b-shadow__overlay" class="b-shadow__overlay b-shadow__overlay_bg_black"></div>
