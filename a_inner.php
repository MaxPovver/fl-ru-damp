<?php

if ( $uid ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/users.common.php' );
    $xajax->printJavascript( '/xajax/' );
}

$text_link = str_replace(array('<', '>', '"', '\''), array('&lt;', '&gt;', '&quot;', '&#039;'), $href);
if (strlen($text_link) > 35) {$text_link = substr($text_link, 0, 35)."...";}

$sClick = $uid ? 'setDirectExternalLinks('.$uid.',this.checked);' : "document.cookie='no_a_php=1; expires='+(new Date(".date('Y')."+(this.checked?2:-2),1,1)).toGMTString()";
$sCheck = $uid ? ($_SESSION['direct_external_links'] ? ' checked="checked"' : '') : ($_COOKIE['no_a_php'] ? ' checked="checked"' : '');
?>

<div class="b-layout__right b-layout__right_float_right b-layout__right_width_240">
  <!-- Banner 240x400 -->
  <?= printBanner240(false); ?>
  <!-- end of Banner 240x400 -->  
</div>
<div class="b-layout__left b-layout__left_margright_270">
  <h1 class="b-page__title">Переход по внешней ссылке</h1>
  <? if($href) { ?>
  <div class="b-layout__txt b-layout__txt_padbot_20">Вы покидаете сайт FL.ru и переходите на сторонний сайт.</div>
  <div class="b-fon">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb"> <span class="b-fon__attent_red"></span>Данный сайт может содержать вирусы и различные вредоносные программы. Администрация сайта FL.ru не несёт ответственности за контент данного сайта.<br />
      Кроме того мы рекомендуем <strong class="b-layout__bold">не указывать ваши данные</strong>, имеющие отношение к FL.ru (имя пользователя, пароль), на сторонних сайтах. </div>
  </div>
  <div class="b-layout__txt b-layout__txt_padtop_20 b-layout__txt_padbot_20 b-fon__body_padleft_30">Для того чтобы перейти на этот сайт, нажмите на
    <noindex><a class="b-layout__link" href="<?=str_replace(array('<', '>', '"', '\''), array('&lt;', '&gt;', '&quot;', '&#039;'), $scheme.$url)?>" rel="nofollow">
      <?=$text_link?>
      </a></noindex>
    <br />
    Для того чтобы вернуться на FL.ru, нажмите <a class="b-layout__link" href="/">отмена</a>.</div>
  <? } ?>
  <div class="b-fon b-fon_inline-block">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_bg_f0ffdf">
      <?php /* onclick="document.cookie='direct_external_links=1; expires='+(new Date(<?=date('Y')?>+(this.checked?2:-2),1,1)).toGMTString()" */ ?>
      <div class="b-check">
        <input class="b-check__input" onclick="<?=$sClick?>" type="checkbox" value="" id="a-rem"<?=$sCheck?>/>
        <label class="b-check__label" for="a-rem">Больше не показывать страницу &laquo;Переход по ссылке&raquo;</label>
      </div>
    </div>
  </div>
</div>
