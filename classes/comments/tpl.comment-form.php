    <div class="cl-form" style="padding-top:10px; padding-bottom:10px;">
        <?
        $_url = array();
        if($ord) $_url['id'] = $article['id'];
        if($ord) $_url['ord'] = $ord;
        if($page) $_url['p'] = $page;
        ?>
        <a name="comment-form"></a>
        <form action="#comment-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="resource_id" value="<?= $this->_resource_id ?>"/>
            <input type="hidden" name="parent_id" value="<?= $reply ?>"/>
            <input type="hidden" name="action" value="<?= $this->_task == 'edit' ? 'edit_comment' : 'add_comment' ?>"/>
            <input type="hidden" name="cmtask" value="<?= $this->_task == 'edit' ? 'edit' : 'add' ?>"/>
            <div class="cl-form-in">
                <textarea <?= ( $this->enableNewWysiwyg ? 'id="textarea_comments"' : '' );?> class="<?= $this->enableWysiwyg ? ($this->enableNewWysiwyg ? 'ckeditor' : 'wysiwyg wysiwyg-comments' ) : '' ?>" name="cmsgtext" rows="5" cols="100" style="height:100px;"><?= $alert && $msg ? $msg : ''?></textarea>
                <ul class="cl-form-o c">
<!--                    <li class="cl-form-tags">
                        <a href="" class="b_tag">&lt;b&gt;</a>
                        <a href="" class="i_tag">&lt;i&gt;</a>
                        <a href="" class="p_tag">&lt;p&gt;</a>
                        <a href="" class="ul_tag">&lt;ul&gt;</a>
                        <a href="" class="li_tag">&lt;li&gt;</a>
                        <a href="" class="s_tag">&lt;s&gt;</a>
                        <a href="" class="h_tag">&lt;h&gt;</a>
                    </li>-->
                    <li><a href="javascript:void(0)" onclick="toggleFiles()">Прикрепить файл к сообщению</a></li>
                    <li><a href="javascript:void(0)" onclick="toggleYoutube()">Добавить видео</a></li>
                </ul>
                <div class="cl-form-files c" style="<?if (!isset($alert['attach']) && !$_POST['is_attached']) echo 'display: none;'; ?>">
                    <?php 
                    $aRmAttaches = array();
                    if ( $this->_post_msg['rmattaches'] ) { 
                        $aRmAttaches = $this->_post_msg['rmattaches'];
                        
                        foreach ( $aRmAttaches as $sOne ) {
                        	?><input type="hidden" name="rmattaches[]" value="<?=$sOne?>"/><?php
                        }
                    }
                    
                    if ( $this->_post_msg['attaches'] ) { 
                        $sAttaches = '';
                        
                        foreach ( $this->_post_msg['attaches'] as $attach ) { 
                            $bRm = in_array( $attach['id'], $aRmAttaches );
                            if ( !$bRm ) { 
                                $sAttaches .= '<li><input type="hidden" name="attaches[]" value="'.$attach['id'].'"/>
                                    <a href="javascript:void(0)" title="Удалить" onclick="deleteAttach(this)"><img src="/images/btn-remove2.png" alt="Удалить" /></a>
                                    <a href="javascript:void(0)">'.$attach['fname'].'</a></li>';
                            }
                        } 
                        
                        if ( $sAttaches ) { ?>
                    <ul class="form-files-added tpl"><?=$sAttaches?></ul>
                    <?php 
                        }
                    } 
                    ?>
                    <ul class="form-files-list" onclick="if($('error_file') != undefined) $('error_file').destroy();">
                        <li class="c"><input name="attach[]" type="file" size="23" class="i-file" /> <input src="/images/btn-add.png" type="image" value="+" /></li>
                    </ul>
                    <div class="form-files-inf">
                        <strong class="form-files-max">Максимальный размер файла: 2 Мб.</strong>
                        <p>Размеры картинки до 470x1000 пикселей, 300 Кб.<br />
                        Файлы следующих форматов запрещены к загрузке: <?=implode(', ', $GLOBALS['disallowed_array'])?>.</p>
                    </div>
                    <? if(!empty($alert['attach'])){ ?>
                        <div id="error_file" onclick="this.style.display = 'none'" class="tip tip-t2" style="top: <?=(40+(count($this->_post_msg['attaches'])*27))?>px; left: 10px;"><div class="tip-in"><div class="tip-txt"><div class="tip-txt-in"><span class="middled"><strong><?= $alert['attach'];?></strong></span></div></div></div></div>
                    <? } //if ?>  
                    <? /*if (isset($alert['attach'])) echo view_error($alert['attach']); */?>
                </div>
                <div class="cl-form-video" style="<?if (!isset($alert['yt_link']) && !$_POST['yt_link']) echo 'display: none;'; ?>">
                    <input type="text" id="yt_link" name="yt_link" onclick="if($('yt_error') != undefined) $('yt_error').destroy();"/>&nbsp;
                    <label>Например:&nbsp;http://www.youtube.com/watch?v=jWxnI8-5LEg</label>
                    <? if(!empty($alert['yt_link'])){?>
                        <div id="yt_error" class="tip tip-t2" style="top: 20px; left: 10px;"><div class="tip-in"><div class="tip-txt"><div class="tip-txt-in"><span class="middled"><strong><?= $alert['yt_link'];?></strong></span></div></div></div></div>
                    <? } ?>
                    <? /*if (isset($alert['yt_link'])) echo view_error($alert['yt_link']); */?>
                </div>
                <div class="cl-form-btns">
	                <? if(!empty($alert['msgtext'])){ ?>
	                <div id="msgtext_error" class="b-layout__txt b-layout__txt_color_c10600 b-layout__txt_padbot_10"><span class="b-icon b-icon_sbr_rattent"></span> <?= $alert['msgtext']?></div>
	                <? } ?>
                   <div class="b-buttons">
                    <a href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green cl-form-submit add-comment" onclick="$(this).addClass('b-button_disabled'); if ($$('textarea[name=cmsgtext]')[0].value == '') { $$('textarea[name=cmsgtext]')[0].value = <?= $this->enableNewWysiwyg ? "CKEDITOR.instances.textarea_comments.getData();" : "$($$('.mooeditable-iframe')[0].contentWindow.document.body).innerHTML;";?> }" style="display: <?= $this->_task == 'edit' ? 'none' : ''?>;">Публиковать сообщение</a>
                    <a href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green cl-form-submit edit-comment" onclick="$(this).addClass('b-button_disabled')" style="display: <?= $this->_task == 'edit' ? '' : 'none'?>;">Изменить сообщение</a>
                    &#160;&#160;<a href="javascript:void(0)" class="b-buttons__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_fontsize_13 cl-form-cancel" style="display: <?= $this->_task == 'edit' ? '' : 'none'?>;">Отменить</a>
                    </div>
                </div>
            </div>
        </form>
        <ul class="form-files-added tpl" style="display:none;">
            <li>
                <input type="hidden" name="attaches[]" value=""/>
                <a href="javascript:void(0)" title="Удалить" onclick="deleteAttach(this)"><img src="/images/btn-remove2.png" alt="Удалить" /></a>
                <a href="javascript:void(0)">1234.rar</a>
            </li>
        </ul>
    </div>