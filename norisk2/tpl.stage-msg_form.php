<div class="cl-form">
    <div class="cl-form-in">
        <form action="?Sending...#c_<?=$form_key?>" method="post" enctype="multipart/form-data" id="msg_form<?=$msg['id']?>" onkeydown="if(event.ctrlKey&&event.keyCode==13){SBR.submitLock(this);return false;}">
        <div>
            <? if($error['msgtext']) { ?><div style="color:red"><?=$error['msgtext']?></div><? } ?>
            <textarea rows="5" cols="100" name="msgtext"><?=$msg['msgtext']?></textarea>
            <ul class="cl-form-o c">
            <?/*
                <li class="cl-form-tags">
                    <a href="javascript:;">&lt;b&gt;</a>
                    <a href="javascript:;">&lt;i&gt;</a>
                    <a href="javascript:;">&lt;p&gt;</a>
                    <a href="javascript:;">&lt;ul&gt;</a>
                    <a href="javascript:;">&lt;li&gt;</a>
                    <a href="javascript:;">&lt;cut&gt;</a>
                    <a href="javascript:;">&lt;h&gt;</a>
                </li>
            */?>
                <li><a href="javascript:;" id="msg_fs_toggler<?=$msg['id']?>">Прикрепить файл к сообщению</a></li>
                <li><a href="javascript:;" id="msg_yt_toggler<?=$msg['id']?>">Добавить ссылку на YouTube/RuTube/Vimeo видео</a></li>
            </ul>
            <div class="cl-form-files" id="msgs_files_box<?=$msg['id']?>">
                
                    <? if ($msg['attach']) { ?>
                    <ul class="form-files-added">
                        <? foreach($msg['attach'] as $id=>$a) { if($msg['delattach'][$id]) continue; ?>
                        <li>
                            <a href="javascript:;" onclick="SBR_STAGE.delMsgAttach(this, <?=$msg['id']?>, <?=$a['id']?>)">
                              <img src="/images/btn-remove2.png" alt="Удалить">
                            </a>
                            <a href="<?=WDCPREFIX.'/'.$a['path'].$a['name']?>" target="_blank"><?=($a['orig_name'] ? $a['orig_name'] : $a['name'])?></a>
                        </li>
                        <? } ?>
                    </ul>
                    <? } ?>
                    <? if($error['attach']) { ?><div style="color:red"><?=$error['attach']?></div><? } ?>
                    <ul class="form-files-list" id="msg_files_list<?=$msg['id']?>"><li class="c"><input type="file" size="23" class="i-file" name="attach[]" /></li></ul>
                    <div class="form-files-inf">
                        <p>
                        Вы можете прикрепить к сообщению:<br />
                        <strong>Файл:</strong> <?=sbr::MAX_FILE_SIZE/1024/1024?> Мб.<br />
                        <strong>Картинку</strong>: 600x1000 пикселей, 300 Кб.<br />
                        Файлы следующих форматов запрещены к загрузке: <?=implode(', ', $GLOBALS['disallowed_array'])?>
                        </p>
                    </div>
                    <div style="clear:both;"></div>
            </div>
            <div class="cl-form-video" id="msgs_yt_box<?=$msg['id']?>" style="height:auto">
                <? if($error['yt_link']) { ?><div style="color:red"><?=$error['yt_link']?></div><? } ?>
                <input type="text" name="yt_link" value="<?=html_attr($msg['yt_link'])?>" />&nbsp;
                <label>Например:&nbsp;http://www.youtube.com/watch?v=bNF_P281Uu4</label>
            </div>
            <div class="cl-form-btns">
                <? if(!$is_main) { ?>
                  <a href="javascript:;" class="lnk-dot-grey cl-form-cancel" onclick="SBR_STAGE.delMsgForm(<?=($msg['id'] ? $msg['id'] : $msg['parent_id'])?>)">Отменить</a>
                <? } ?>
                <a href="javascript:;" class="btnr btnr-grey" onclick="SBR.submitLock(document.getElementById('msg_form<?=$msg['id']?>'))"><span class="btn-lc"><span class="btn-m"><span class="btn-txt"><?=($is_edit ? 'Сохранить' : 'Комментировать')?></span></span></span></a>
                <? if (hasPermissions('sbr')) { ?>
                <div class="b-layout b-layout_inline-block b-layout_pad_10">
                    <div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_inline-block">Срок ожидания ответа</div>&nbsp;
                    <?
                    $dateMaxLimit = "date_max_limit_" . date('Y_m_d', strtotime('+ 30 days'));
                    $dateMinLimit = "date_min_limit_" . date('Y_m_d', strtotime('+ 1 day'));
                    ?>
                    <div class="b-combo b-combo_inline-block">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_170 b-combo__input_arrow-date_yes use_past_date <?= $dateMinLimit; ?> <?= $dateMaxLimit ?>">
                            <input class="b-combo__input-text" name="date_to_answer" type="text" size="80" value="<?= $msg['date_to_answer'] ? date('d.m.Y', strtotime($msg['date_to_answer'])) : date("d.m.Y", strtotime('+1day')); ?>" />
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                </div>
                <? } ?>
								<input type="hidden" name="site" value="<?=$site?>" />
								<input type="hidden" name="id" value="<?=$msg['stage_id']?>" />
								<input type="hidden" name="parent_id" value="<?=$msg['parent_id']?>" />
								<input type="hidden" name="msg_id" value="<?=$msg['id']?>" />
								<input type="hidden" name="action" value="<?=$action?>" />
								<input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>" />
            </div>
            <? if($msg['delattach']) foreach($msg['delattach'] as $id) { ?>
              <input type="hidden" name="delattach[<?=$id?>]" value="<?=$id?>" />
            <? } ?>
        </div>
    	</form>
    </div>
</div>
<? if($static) { ?>
<script type="text/javascript">
  SBR_STAGE.initMsgForm(<?=$form_key?>, <?=(int)$is_edit?>, '<?=$msg['id']?>', <?=count($msg['attach']) - count($msg['delattach'])?>, <?=(int)($msg['attach']||$error['attach'])?>, <?=(int)(!!$msg['yt_link'])?>, <?=(int)(!$error)?>);
</script>
<? } ?>
