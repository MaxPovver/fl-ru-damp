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
<script>
    window.addEvent('domready', function() {

        $$('.ai-form input[type=file]').each( function (el) {
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
            width: '',
            linkShowAdvancedTab: false,
            linkShowTargetTab: false
        });
//        CKEDITOR.replace('short', {
//            toolbar : 'Articles',
//            enterMode : CKEDITOR.ENTER_BR,
//            shiftEnterMode : CKEDITOR.ENTER_P,
//            format_tags : 'p;h1;h2;h3;h4;h5;h6',
//            width: ''
//        });
    });
</script>
<div class="form js-form ai-form <?=isset($id) ? 'article-one-' : 'articles-'?>edit" style="display:none;">
    <div class="<?=isset($id) ? 'ai-form-in' : ''?>">
    <h3><?=!isset($id) ? 'Добавить' : 'Редактировать'?> статью</h3>
    <form action="" method="post" name="interviewForm" onsubmit="return false;" id="frm">
        <fieldset>
            <input type="hidden" name="task" value="add"/>
            <input type="hidden" name="id" value=""/>
            <input type="hidden" name="page_view" value="<?=$_page?>"/>
            <div class="form-el">
                <label class="form-label3">Название:</label>
                <span><input type="text" size="40" class="i-a-title" name="title" /></span>
            </div>
            <div class="form-el">
                <label class="form-label3">Анонс:</label>
                <div class="form-edit form-fck<?=!isset($id) ? 1 : 2?>">
                    <textarea rows="10" cols="100" name="short"></textarea>
                </div>
            </div>
            <div class="form-el">
                <label class="form-label3">Текст статьи:</label>
                <div class="form-edit form-fck<?=!isset($id) ? 1 : 2?>">
                    <textarea rows="10" cols="100" name="msgtext"></textarea>
                </div>
            </div>
            
            <div class="form-el" style="position:relative; z-index:1;">
                <label class="form-label3">Темы:</label>
                <div class="form-edit form-fck<?=!isset($id) ? 1 : 2?>">
                    <div class="b-input-hint b-input-hint_height_20">
                        <div id="body_1" class="b-input">
                            <input id="kword_se" class="b-input__text " type="text" name="kword"  />
                        </div>
                    </div>
                    <div class="b-form__txt b-form__txt_fontsize_11">Ключевые слова вводятся через запятую.</div>
                </div>
            </div>
						
            <div class="form-el">
                <label class="form-label3">Изображение:</label>
                <div class="form-el-in">
                    <ul class="form-files-added add-f" style="display:none;">
                        <li>
                            <input type="hidden" name="logo" />
                            <input type="hidden" name="rmlogo" />
                            <a href="javascript:void(0)" title="Удалить" onclick="delArticleAttach(this)"><img src="/images/btn-remove2.png" alt="" /></a>
                            <a href=""></a>
                        </li>
                    </ul>
                    <div class="form-file-add c">
                        <span>100х100px, JPG, GIF, PNG</span>
                        <input type="file" size="25" name="attach" onchange="if($(this).getParent().getParent().getParent().getElement('.errorBox') != undefined) $(this).getParent().getParent().getParent().getElement('.errorBox').set('html', '');"/>
                    </div>
                </div>
						<!--
								<div class="b-fon b-file b-fon_inline-block b-fon_width_505">
										<b class="b-fon__b1"></b>
										<b class="b-fon__b2"></b>
										<div class="b-fon__body b-fon__body_pad_2_10">
												<table class="b-file_layout">
													<tr>
															<td class="b-file__button">            
																	<div class="b-file__wrap">
																		<input class="b-file__input" type="file" />
																		<a class="b-button b-button_rectangle_transparent_small" onclick="return false" href="#">
																			<span class="b-button__b1">
																				<span class="b-button__b2">
																					Выбрать файл
																				</span>
																			</span>
																		</a>
																	</div>
															</td>
															<td class="b-file__text">
																		<p class="b-file__descript b-file__descript_bold">Максимальный размер файла 1 Мб.</p>
																		<p class="b-file__descript">Картинка 100х100рх, gif, jpg, jpeg, png</p>
																</td>
														</tr>
												</table>
										</div>
										<b class="b-fon__b2"></b>
										<b class="b-fon__b1"></b>
								</div>
						-->
            </div>
						
						
						
						
						
						
						
						<div class="b-buttons b-buttons_padleft_112">
								<a href="javascript:void(0)" onclick="saveArticle($('frm'))" class="b-button b-button_rectangle_color_transparent" id="save_article">
										<span class="b-button__b1">
												<span class="b-button__b2">
														<span class="b-button__txt" id="btn_name">Отправить на модерацию</span>
												</span>
										</span>
								</a>
							<div class="b-buttons__txt b-buttons__txt_padleft_10">Ваша статья должна быть проверена перед публикацией.<br />Срок прохождения модерации &mdash; 1 неделя.</div>
						</div>
						
        </fieldset>
    </form>
    </div>
</div>