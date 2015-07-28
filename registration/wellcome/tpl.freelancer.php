<div class="b-layout">
    <div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right">
        <h1 class="b-page__title b-page__title_padbot_30">Добро пожаловать на FL.ru</h1>
    </div>
    <div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right">
        <? if (!$_COOKIE['master_auth']) { ?>
        <div class="b-fon b-fon_inline-block b-fon_padbot_50">
            <div class="b-fon__body b-fon__body_pad_15  b-fon__body_padleft_30 b-fon__body_padright_40 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
                <span class="b-icon b-icon_sbr_gattent b-icon_margleft_-20 b-icon_top_8"></span>Поздравляем вас с успешной регистрацией на сайте FL.ru!<br>На e-mail, указанный при регистрации, отправлено письмо с логином, паролем и ссылкой для активации аккаунта. Пожалуйста, сохраните эти учетные данные и активируйте аккаунт.
            </div>
        </div>
        <? } ?>
        <? if($from_blocked_prj==1) { ?>
        <div class="b-layout__txt b-layout__txt_fontsize_22 b-layout__txt_padbot_40">Найдите интересные <a href="/projects/?kind=1" class="b-layout__link">проекты</a>, <a href="/konkurs/" class="b-layout__link">конкурсы</a> и <a href="/projects/?kind=4" class="b-layout__link">вакансии</a></div>
        <div class="b-layout__txt b-layout__txt_fontsize_22">Приобретите  <a href="/payed/" class="b-layout__link">аккаунт</a> <a href="/payed/" class="b-layout__link"><span title="PRO" class="b-icon b-icon__spro b-icon__spro_f"></span></a> за <span class="b-layout__txt_through b-layout__txt_color_d7d7d7"><?=payed::getPriceByOpCode(48)?></span> <?=payed::getPriceByOpCode(163)?> рублей</div>
        <div class="b-layout__txt b-layout__txt_padbot_40">и отвечайте на проекты, конкурсы и вакансии без ограничений</div>
        <div class="b-layout__txt b-layout__txt_fontsize_22">Заполните <a href="/users/<?= $_SESSION['login']?>/setup/info/" class="b-layout__link">профиль</a>,</div>
        <div class="b-layout__txt b-layout__txt_padbot_40">чтобы работодатели смогли найти вас в каталоге фрилансеров</div>
        <div class="b-layout__txt b-layout__txt_fontsize_22">Подтвердите свою   <a href="/promo/verification/" class="b-layout__link">личность</a>,</div>
        <div class="b-layout__txt b-layout__txt_padbot_40">чтобы вам доверяли Заказчики</div>
        <? } else { ?>
        <div class="b-layout__txt b-layout__txt_fontsize_22">Приобретите  <a href="/payed/" class="b-layout__link">аккаунт</a> <a href="/payed/" class="b-layout__link"><span title="PRO" class="b-icon b-icon__spro b-icon__spro_f"></span></a> за <span class="b-layout__txt_through b-layout__txt_color_d7d7d7"><?=payed::getPriceByOpCode(48)?></span> <?=payed::getPriceByOpCode(163)?> рублей</div>
        <div class="b-layout__txt b-layout__txt_padbot_40">и отвечайте на проекты, конкурсы и вакансии без ограничений</div>
        <div class="b-layout__txt b-layout__txt_fontsize_22">Заполните <a href="/users/<?= $_SESSION['login']?>/setup/info/" class="b-layout__link">профиль</a>,</div>
        <div class="b-layout__txt b-layout__txt_padbot_40">чтобы работодатели смогли найти вас в каталоге фрилансеров</div>
        <div class="b-layout__txt b-layout__txt_fontsize_22 b-layout__txt_padbot_40">Найдите интересные <a href="/projects/?kind=1" class="b-layout__link">проекты</a>, <a href="/konkurs/" class="b-layout__link">конкурсы</a> и <a href="/projects/?kind=4" class="b-layout__link">вакансии</a></div>
        <div class="b-layout__txt b-layout__txt_fontsize_22">Подтвердите свою   <a href="/promo/verification/" class="b-layout__link">личность</a>,</div>
        <div class="b-layout__txt b-layout__txt_padbot_40">чтобы вам доверяли Заказчики</div>
        <? } ?>
    </div>
</div>

<?
if ( !empty($_SESSION['is_new_user']) ) {
    unset($_SESSION['is_new_user']);
?>
<script language="javascript" src="http://www.everestjs.net/static/st.v2.js"></script>
<script language="javascript">
var ef_event_type="transaction";
var ef_transaction_properties = "ev_reg_worker=0&ev_reg_employer=0&ev_reg_worker_master=1&ev_reg_employer_master=0&ev_transid=<?=md5($_SESSION['uid'])?>";
/*
 * Do not modify below this line
 */
var ef_segment = "";
var ef_search_segment = "";
var ef_userid="3208";
var ef_pixel_host="pixel.everesttech.net";
var ef_fb_is_app = 0;
effp();
</script>
<noscript><img src='http://pixel.everesttech.net/3208/t?ev_reg_worker=0&ev_reg_employer=0&ev_reg_worker_master=1&ev_reg_employer_master=0&ev_transid=<?=md5($_SESSION['uid'])?>' width='1' height='1'/></noscript>
<?
}
?>
