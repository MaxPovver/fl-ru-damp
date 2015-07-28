<div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right">
        <h1 class="b-page__title b-page__title_padbot_30">Добро пожаловать на Free-lance.ru</h1>
</div>
<div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right">
    <div class="b-fon b-fon_inline-block b-fon_padbot_20">
            <div class="b-fon__body b-fon__body_pad_15  b-fon__body_padleft_30 b-fon__body_lineheight_18 b-fon__body_padright_40 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
            <span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span>Вы успешно зарегистрированы, и теперь вам доступны все бесплатные функции сайта.
        </div>
    </div>

    <div class="b-layout__txt b-layout__txt_fontsize_22">&mdash; Перейти на <a class="b-layout__link" href="/">главную страницу</a></div>
    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_30 b-layout__txt_padbot_40">и начать самостоятельный поиск работы.</div>
    <div class="b-layout__txt b-layout__txt_fontsize_22">&mdash; Перейти в <a class="b-layout__link" href="/users/<?= get_login(get_uid(0)) ?>/">личный кабинет</a></div>
    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_30 b-layout__txt_padbot_40">указать профессиональную информацию и заполнить портфолио.</div>
</div>

<?
if ( !empty($_SESSION['is_new_user']) ) {
    unset($_SESSION['is_new_user']);
?>
<script language="javascript" src="http://www.everestjs.net/static/st.v2.js"></script>
<script language="javascript">
var ef_event_type="transaction";
var ef_transaction_properties = "ev_reg_worker=1&ev_reg_employer=0&ev_reg_worker_master=0&ev_reg_employer_master=0&ev_transid=<?=md5($_SESSION['uid'])?>";
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
<noscript><img src='http://pixel.everesttech.net/3208/t?ev_reg_worker=1&ev_reg_employer=0&ev_reg_worker_master=0&ev_reg_employer_master=0&ev_transid=<?=md5($_SESSION['uid'])?>' width='1' height='1'/></noscript>
<?
}
?>