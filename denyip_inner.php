<div class="b-layout__right b-layout__right_float_right b-layout__right_width_240">
    <!-- Banner 240x400 -->
    <?= printBanner240(false); ?>
    <!-- end of Banner 240x400 -->
</div>
<div class="b-layout__left b-layout__left_margright_270">
<h1 class="b-page__title b-page__title_color_c10600">В авторизации отказано!</h1>
<div class="b-layout__txt">Ваш текущий <strong class="b-layout__txt b-layout__txt_bold b-layout__txt_color_c10600">IP адрес не соответствует</strong> установленному в настройках безопасности для логина <strong class="b-layout__txt b-layout__txt_bold"><?=strip_tags(trim(stripcslashes($_GET['login'])))?></strong>.<br />Если вы считаете это ошибкой, пожалуйста, обратитесь в <a class="b-layout__link" href="/about/feedback/">службу поддержки</a>.</div>
</div>
