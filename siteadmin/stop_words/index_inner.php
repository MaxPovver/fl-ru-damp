<?php
/**
 * Стоп-слова. Шаблон.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
?>

<h2 class="b-layout__title b-layout__title_padbot_20">Пользовательский контент / Стоп-слова</h2>

<div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_padbot_5">
<?php /*
// мини меню
echo $site == 'words' ? '<a href="/siteadmin/stop_words/?site=regex" class="lnk-dot-666">' : '';
echo 'Запрещенные выражения';
echo $site == 'words' ? '</a>' : '';
echo '&nbsp;|&nbsp;';
echo $site == 'regex' ? '<a href="/siteadmin/stop_words/?site=words" class="lnk-dot-666">' : '';
echo 'Подозрительные слова';
echo $site == 'regex' ? '</a>' : '';
*/?>
</div>

<?php 
// блоки сообщение об успехе или ошибках при сохранении
if ($_SESSION['admin_stop_words_success']) { 
    unset( $_SESSION['admin_stop_words_success'] );
?>
  <div>
    <img src="/images/ico_ok.gif" alt="" border="0" height="18" width="19"/>&nbsp;&nbsp;Изменения внесены.
  </div>
  <br/><br/>
<?php } if ($error) print(view_error($error).'<br/>'); ?>

  
<form id="form_stop_words" method="post">
    <input type="hidden" name="site" value="<?=$site?>">
    <input type="hidden" name="cmd" value="go">
<?php
if ( $site == 'regex' ) {
    /*
    // Запрещенные выражения
    
?>
    <input type="hidden" name="action" id="action" value="">
    <div class="b-textarea">
        <textarea class="b-textarea__textarea" name="regex" id="regex" cols="80" rows="5"><?=  $sStopRegex?></textarea>
    </div>

    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_20">
        Каждое новое выражение &mdash; с новой строки. При публикации будет заменено на <?=CENSORED?>.<br/>
        Порядок следования выражений влияет на конечный результат замен.
    </div>
    
    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_padbot_5">Тестовый текст</div>
    
    <div class="b-textarea">
        <textarea class="b-textarea__textarea" name="test" id="test" cols="80" rows="5"><?=  $sTestText?></textarea>
    </div>
    
    <div class="b-layout__txt b-layout__txt_fontsize_11 <?php if ( !empty($sUserMode) || !empty($sAdminMode) ) { ?>b-layout__txt_padbot_20<?php } ?>">
        Во избежание ошибок перед сохранением запрещенных выражений рекомендуется проверить их на тестовом тексте и убедиться, что производятся все ожидаемые замены.
        В противном случае пользователи будут видеь запрещенную к показу информацию.
    </div>
    
    <?php if ( !empty($sUserMode) ) { ?>
        <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_padbot_5">Замены для пользователя:</div>
        <div class="b-layout__txt b-layout__txt_fontsize_11 <?php if ( !empty($sAdminMode) ) { ?>b-layout__txt_padbot_20<?php } ?>"><?=reformat( $sUserMode, 100, 0, 1 )?></div>
    <?php } ?>
    
    <?php if ( !empty($sAdminMode) ) { ?>
        <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_padbot_5">Замены для администратора:</div>
        <div class="b-layout__txt b-layout__txt_fontsize_11"><?=reformat( $sAdminMode, 100, 0, 1 )?></div>
    <?php } ?>
    
    <div class="b-buttons b-buttons_padtop_40">
        <a onclick="stop_words.regexTest();" class="b-button b-button_rectangle_color_green" href="javascript:void(0);">
            <span class="b-button__b1">
                <span class="b-button__b2">
                    <span class="b-button__txt">Тестировать</span>
                </span>
            </span>
        </a>

        &nbsp;&nbsp;

        <a href="javascript:void(0);"  onclick="stop_words.regexSubmit();" class="b-button b-button_rectangle_color_green">
            <span class="b-button__b1">
                <span class="b-button__b2">
                    <span class="b-button__txt">Сохранить</span>
                </span>
            </span>
        </a>
        <span class="b-buttons__txt b-buttons__txt_padleft_10">или</span>
        <a href="/siteadmin/stop_words/?site=<?=$site?>" class="b-buttons__link b-buttons__link_color_c10601">отменить изменения</a>
    </div>

    
<?php
*/
}
else {
    
    // Подозрительные слова
    
?>
    <div class="b-textarea">
        <textarea class="b-textarea__textarea" name="words" id="words" cols="80" rows="5"><?=  $sStopWords?></textarea>
    </div>

    <div class="b-layout__txt b-layout__txt_fontsize_11">Через запятую. Эти слова будут выделены жирным при модерировании пользовательского контента.</div>

    <div class="b-buttons b-buttons_padtop_40">
        <a href="javascript:void(0);" onclick="stop_words.wordsSubmit();" class="b-button b-button_flat b-button_flat_green">Сохранить</a>
        <span class="b-buttons__txt b-buttons__txt_padleft_10">или</span>
        <a href="/siteadmin/stop_words/?site=<?=$site?>" class="b-buttons__link b-buttons__link_color_c10601">отменить изменения</a>
    </div>

<?php

}

?>
</form>