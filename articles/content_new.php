<?php
/**
 * Форма добавления статьи
 */
?>
<?php
if ( hasPermissions('articles') ) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/uploader/uploader.php");
    $templates = array(
        uploader::getTemplate('uploader', 'wysiwyg/'),
        uploader::getTemplate('uploader.file', 'wysiwyg/'),
        uploader::getTemplate('uploader.popup'),
    );
    uploader::init(array(), $templates, 'wysiwyg');
}
?>
<? if(count($alert)): ?>
<script>
window.addEvent('domready', function() {
   $('frm').scrollIntoView(true);
});
</script>
<? endif; ?>
<script>
    window.addEvent('domready', function() {

        $$('.js-form input[type=file]').each( function (el) {
            articlesFileInput(el);
        });
        <?php
        if ( hasPermissions('articles') ) {
        ?>CKEDITOR.config.customConfig = '/scripts/ckedit/config_admin.js';<?php 
        }
        ?>
        CKEDITOR.replace('msgtext', {
            toolbar : 'Articles',
//            enterMode : CKEDITOR.ENTER_BR,
//            shiftEnterMode : CKEDITOR.ENTER_P,
            format_tags : 'p;h1;h2;h3;h4;h5;h6',
            linkShowAdvancedTab: false,
            linkShowTargetTab: false
        });
//        CKEDITOR.replace('short', {
//            toolbar : 'Articles',
//            enterMode : CKEDITOR.ENTER_BR,
//            shiftEnterMode : CKEDITOR.ENTER_P,
//            format_tags : 'p;h1;h2;h3;h4;h5;h6'
//        });

        $('frm').addEvent('submit', function() {
            saveArticle($('frm'));
            return false;
        });
    });
</script>
<h2>Статьи и интервью</h2>
<? include($mpath . '/tabs.php'); ?>
<div class="page-interview">
    <div class="tnav-interview">
        <a class="b-layout__link" href="./">Вернуться к списку статей</a>
    </div>
    <div class="p-interview-in">
        <div class="p-article-add-i">
            <div class="b-txt b-txt_padbot_10">Если вам есть о чем рассказать, вы умеете писать ярко и увлекательно, или вам просто хочется поделиться опытом – присылайте ваши статьи.</div>
            <h3 class="b-txt__h3">Требования к присылаемым статьям:</h3>
            <div class="b-txt">&mdash; Присылаемый материал должен принадлежать вам. Мы не приветствуем статьи, скопированные из других источников.</div> 
            <div class="b-txt">&mdash; Желательно, чтобы отправляемый на модерацию контент не был опубликован ранее на других сайтах.</div> 
            <div class="b-txt">&mdash; Для нас важен хорошо структурированный материал, который будет идти не одним большим сплошным куском, а разбит на небольшие абзацы и отформатированный.</div> 
            <div class="b-txt">&mdash; Соблюдайте правила орфографии и пунктуации.</div> 
            <div class="b-txt">&mdash; Ваша статья должна быть полезной и интересной для наших читателей.</div>
            <div class="b-txt b-txt_padbot_10">В течение недели после того, как вы прислали статью на модерацию, мы известим вас о её судьбе по почте или личным сообщением на сайте.</div> 
            <div class="b-txt b-txt_padbot_10">По всем вопросам и предложениям пишите на почту: <a class="b-layout__link" href="mailto:editor@free-lance.ru">editor@fl.ru</a></div>
        </div>
        
        <div class="cl-form form-article-add js-form">
            <div class="cl-form-in">
                <form action="" method="post" enctype="multipart/form-data" id="frm" name="interviewForm">
                <div>
                    <input type="hidden" name="action" value="add-article" />
                    <input type="hidden" name="task" value="add-article" />
										<div class="b-form">
                    	<label class="b-form__name b-form__name_fontsize_13">Заголовок (до 100 символов):</label>
											<div class="b-input">
												<input class="b-input__text " type="text" name="title" value="<?=$title?>"  />
											</div>
										</div>
                    <? if ($alert[0])  print(view_error($alert[0])) . '<br />' ?>
										<div class="b-form">
                    	<label class="b-form__name b-form__name_fontsize_13">Анонс:</label>
                    	<div class="b-textarea">
                        <textarea class="b-textarea__textarea b-textarea__textarea__height_140 editor" cols="100" name="short"><?=$short?></textarea>
											</div>
                    </div>

                    <? if ($alert[1])  print(view_error($alert[1])) . '<br />' ?>
										<div class="b-form">
											<label class="b-form__name b-form__name_fontsize_13">Текст статьи:</label>
											<div >
													<textarea class="b-textarea__textarea b-textarea__textarea__height_140 editor" rows="5" cols="100" name="msgtext"><?=$msgtext?></textarea>
											</div>
										</div>
										<div class="b-form">
                    	<label class="b-form__name b-form__name_fontsize_13">Темы:</label>
											<div class="b-input-hint b-input-hint_height_20">
                        <div id="body_1" class="b-input">
                        	<input id="kword_se" class="b-input__text " type="text" name="kword"  />
												</div>
											</div>
											<div class="b-form__txt b-form__txt_fontsize_11">Ключевые слова вводятся через запятую.</div>
										</div>
                    <? if ($alert[2])  print(view_error($alert[2])) . '<br />' ?>
                    <div class="cl-form-files c">
                        <ul class="form-files-list">
                            <li class="c"><input type="file" size="23" class="i-file b-fon" name="attach" onchange="if($(this).getParent().getParent().getParent().getElement('.errorBox') != undefined) { $(this).getParent().getParent().getParent().getElement('.errorBox').set('html', ''); }"/> </li>
                        </ul>
                        <div class="form-files-inf">
                            <?php $aAllowedExt = array_diff( $GLOBALS['graf_array'], array('swf') ) ?>
                            <strong class="form-files-max">Максимальный размер файла: <?=articles::ARTICLE_MAX_LOGOSIZE/(1024*1024)?> Мб.</strong>
                             Картинка: 100х100px, <?=implode(', ', $aAllowedExt )?><br />
                        </div>
                        <? if ($alert[3])  print(view_error($alert[3])) ?>
                    </div>
                    <div class="cl-form-btns">
                        <a href="javascript:void(0)" id="btn-send-articles" onclick="if(!$(this).hasClass('btnr-disabled')) {  saveArticle($('frm')); $(this).addClass('btnr-disabled'); }" class="btnr btnr-grey "><span class="btn-lc"><span class="btn-m"><span class="btn-txt" id="btn_name">Отправить на модерацию</span></span></span></a>
                        &nbsp;&nbsp;&nbsp;Ваша статья должна быть проверена перед публикацией. Срок прохождения модерации — 1 неделя.
                    </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>