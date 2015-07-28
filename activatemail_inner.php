<div class="b-layout__right b-layout__right_float_right b-layout__right_width_240">
    <!-- Banner 240x400 -->
    <?= printBanner240(false); ?>
    <!-- end of Banner 240x400 -->
</div>
<div class="b-layout__left b-layout__left_margright_270">
<h1 class="b-page__title">Активация</h1>
<div class="b-layout__txt b-layout__txt_padbot_20"><? if ($activated == 1) { ?>E-mail активирован!<? } elseif ($activated == -1) { ?>E-mail не найден!<? } 
elseif (!$code) {?> Не введен код<? } else { ?>Произошла ошибка! Ваш e-mail уже активирован, либо введенный код не найден.<br>
Если у вас не получается сменить e-mail, <a class="b-layout__link" href="/about/feedback/">обратитесь</a> к администрации.<? } ?></div>
</div>