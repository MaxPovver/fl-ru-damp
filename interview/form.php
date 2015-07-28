<script>
    window.addEvent('domready', function() {
        $$('.form-files-list:not(.main-f) li input[type=image]').removeEvents('click');
        $$('.form-files-list:not(.main-f) li input[type=image]').addEvent('click', function() {
            if(this.value == '+') {
                if($$('.form-files-list:not(.main-f) input[type=file]').length >= MAX_FILE_COUNT) {
                    return false;
                }

                l = this.getParent('li').clone();
                l.inject(this.getParent('ul.form-files-list'));
                _tmp = l.getElement('input[type=file]');
                im = new Element('input', {
                    'type' : 'file',
                    'name' : _tmp.get('name'),
                    'class' : _tmp.get('class'),
                    'size' : _tmp.get('size')
                });
                im.cloneEvents(_tmp);
                l.getElement('input[type=file]').dispose();
                im.inject(l.getElement('input[type=image]'), 'before');
                l.getElement('input[type=image]').cloneEvents(this);

                if($$('.form-files-list:not(.main-f) input[type=file]').length >= MAX_FILE_COUNT-1)
                    l.getElement('input[type=image]').setStyle('display', 'none');

                l.getElement('.ap-id').cloneEvents(this.getParent().getElement('.ap-id'));
                l.getElement('.ap-id').set('html', l.getElement('.ap-id').get('html').replace(/(\d+)/g, 0));

                if(l.getElement('.ap-id').getParent().tagName.toLowerCase() == 'a')
                    l.getElement('.ap-id').replaces(l.getElement('.ap-id').getParent());


                initFileInput(l.getElement('input[type=file]'));

                this.set('src', btn_cancel);
                this.set('value', '-');
            } else {
                if($$('.form-files-list:not(.main-f) input[type=file]').length < 10) {
                    $$('.form-files-list:not(.main-f) li input[type=image]').setStyle('display', 'inline-block');
                }

                tx = this.getParent('div.form-edit').getElement('textarea');
                l = this.getParent('li').getAllNext('li');
                tx.set('value', tx.get('value').replace(this.getParent().getElement('.ap-id').firstChild.nodeValue, ''));

                if(this.getParent().getElement('input[name^=attached]')) {
                    r = new RegExp('(<img id="'+this.getParent().getElement('input[name^=attached]').get('value')+'".*?>)', 'g');
                    txt = CKEDITOR.instances.txt.getData();

                    if(r.test(txt)) {
                        CKEDITOR.instances.txt.setData( txt.replace(r, "") );
                    }
                }
                
                this.getParent('li').dispose();
            }
            return false;
        });

        $$('li a.img_tag').addEvent('click', function() {
            el = this.getParent('div.form-edit').getElement('textarea');
            el.insertAtCursor(this.getElement('span').firstChild.nodeValue);

            return false;
        });

        $$('.form.ai-form input[name=login]').addEvent('change', function() {
            checkLogin(this.get('value'));
        });
        $$('.form.ai-form input[name=login]').addEvent('blur', function() {
            checkLogin(this.get('value'));
        });

        $$('input[type=file]').each( function (el) {
            initFileInput(el);
        });

        CKEDITOR.WDCPREFIX = '<?=WDCPREFIX?>/';
  <?php
        if ( hasPermissions('interviews') ) {
        ?>CKEDITOR.config.customConfig = '/scripts/ckedit/config_admin.js';<?php 
        }
        ?>
        CKEDITOR.replace('txt', {
            toolbar : 'Interview',
            format_tags : 'p;h1;h2;h3;h4;h5;h6',
            //extraPlugins : 'interview',
            bodyClass: 'interview-one-ck utxt',
            width: '',
            height: '300px',
            linkShowAdvancedTab: false,
            linkShowTargetTab: false
        });
        
        
    });

    function insertImage(el) {
       CKEDITOR.instances.txt.execCommand('insertImage', el.getElement('.ap-id'));
    }
</script>
<div class="form ai-form interview-<?=isset($id) ? 'one-' : ''?>edit" style="display:none;">
    <h3><?=!isset($id) ? 'Новое' : 'Редактировать'?> интервью</h3>
    <form action="" method="post" name="interviewForm" onsubmit="return false;">
        <fieldset>
        <input type="hidden" name="task" value="add"/>
        <input type="hidden" name="id" value=""/>
        <input type="hidden" name="page_view" value="<?=$_page?>"/>
            <div class="form-el">
                <label class="form-label3">Логин героя:</label>
                <span class="login-input" style="<?=isset($form_data['login']) ? 'display:none;' : ''?>">
                    <input type="text" size="40" name="login" value="<?=isset($form_data['login']) ? $form_data['login'] : ''?>" />
                </span>
                <span class="login-view" style="<?=!isset($form_data['login']) ? 'display:none;' : ''?>">
                    <a href="#"><?=isset($form_data['username']) ? $form_data['username'] : ''?></a>&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="changeLogin()">изменить</a>
                </span>
                <span class="login-error" style="display:none;">
                    Пользователь не найден
                </span>
            </div>
            <div class="form-el">
                <label class="form-label3">Текст интервью:</label>
                <div class="form-edit form-fck2">
                    <textarea rows="20" cols="100" name="txt"><?=isset($form_data['txt']) ? $form_data['txt'] : ''?></textarea>
<!--                    <ul class="cl-form-o c">
                        <li class="cl-form-tags">
                            <a href="" class="question_tag">&lt;вопрос&gt;</a>
                            &nbsp;
                            <a href="" class="answer_tag">&lt;ответ&gt;</a>
                            &nbsp;&nbsp;&nbsp;
                            <a href="" class="b_tag">&lt;b&gt;</a>
                            <a href="" class="i_tag">&lt;i&gt;</a>
                            <a href="" class="p_tag">&lt;p&gt;</a>
                            <a href="" class="ul_tag">&lt;ul&gt;</a>
                            <a href="" class="li_tag">&lt;li&gt;</a>
                            <a href="" class="cut_tag">&lt;cut&gt;</a>
                            <a href="" class="h_tag">&lt;h&gt;</a>
                        </li>
                        <li>&nbsp;</li>
                    </ul>-->
                    <div>
<!--                        <ul class="form-edit-hint">
                            <li>
                                Вставка изображения: <strong>&lt;img id="n"&gt;</strong>&nbsp;&nbsp;n — номер изображения
                            </li>
                            <li>
                                <strong>&lt;p class="q"&gt;</strong> Вопрос <strong>&lt;/p&gt;</strong>&nbsp;&nbsp;
                                <strong>&lt;p class="a"&gt;</strong> Ответ <strong>&lt;/p&gt;</strong>
                            </li>
                            <li>
                                Можно использовать
                                <strong>&lt;b&gt;</strong>&nbsp;
                                <strong>&lt;i&gt;</strong>&nbsp;
                                <strong>&lt;p&gt;</strong>&nbsp;
                                <strong>&lt;ul&gt;</strong>&nbsp;
                                <strong>&lt;li&gt;</strong>&nbsp;
                                <strong>&lt;cut&gt;</strong>&nbsp;
                                <strong>&lt;h&gt;</strong>
                            </li>
                        </ul>-->
                        <div class="add-photos">
                            <div class="add-photos-g c">
                                <label class="add-photos-label">Основная фотография:</label>
                                <div class="add-photos-in main-f">
																
                                    <ul class="form-files-added main-f"></ul>
																
                                    <div class="add-photos-up">180х180 px, JPG, GIF, PNG</div>
                                    <ul class="form-files-list main-f ">
                                        <li>
                                            <span class="ap-id">&lt;img id=""&gt;</span>
                                            <input type="file" size="23" class="i-file" name="main_foto" />
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="add-photos-g c">
                                <label class="add-photos-label">Примеры работ:</label>
                                <div class="add-photos-in">
																
                                    <ul class="form-files-added add-f"></ul>
																
                                    <div class="add-photos-up">JPG, GIF, PNG</div>
                                    <ul class="form-files-list c">
                                        <li class="c">
                                            <span class="ap-id">&lt;img id=""&gt;</span>
                                            <input type="file" size="23" class="i-file" name="attach" />
                                            <input src="/images/btn-add.png" type="image" value="+" />
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="form-btns">
                            <input type="submit" class="i-btn i-bold" value="Сохранить" onclick="saveInterview(this.form)" />
                            <input type="button" class="i-btn" value="Отменить" onclick="toggleAddForm(true)" />
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </form>
    <ul class="form-files-added tpl" style="display:none;">
        <li>
            <input type="hidden" name="attaches[]" value="" />
            <span class="ap-id">&lt;img id="2"&gt;</span>
            <a href="javascript:void(0)" title="Удалить" onclick="deleteAttach(this)"><img src="/images/btn-remove2.png" alt="Удалить" /></a>
            <a href="javascript:void(0)"></a>
        </li>
    </ul>
</div>