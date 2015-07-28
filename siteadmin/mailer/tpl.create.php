<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/mailer.common.php");
$xajax->printJavascript('/xajax/'); 

if($mailer->error) {
    $error_name = current(array_keys($mailer->error));
}
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/uploader/uploader.php");
$templates = array(
    uploader::getTemplate('uploader', 'wysiwyg/'),
    uploader::getTemplate('uploader.file', 'wysiwyg/'),
    uploader::getTemplate('uploader.popup'),
);
uploader::init(array(), $templates, 'wysiwyg');
?>
<script type="text/javascript" >
var sregtype = new Array();
   
window.addEvent('domready', 
    function() {
    <?php if($message['message'] && false) {?>
        $('main_message').set('value', '<?= str_replace(array("\r", "\n"), "", $message['message']);?>');
    <?php }//if?>
    <?php if($error_name) {?>
        JSScroll('i_<?= $error_name; ?>');
    <?php }//if?>  
        /*
         * @todo: пока отключаю визуальный редактор
        CKEDITOR.replace( 'main_message', {
            customConfig: '/scripts/ckedit/config_admin.js'
        });*/
    }
);
   
<?php foreach(mailer::$SUB_TYPE_REGULAR as $key=>$val) { ?>
    sregtype[<?=$key?>] = ['<?=implode("', '", $val)?>'];
<?php }//foreach?>
</script>

<div class="b-layout">	
    <h2 class="b-layout__title b-layout__title_padbot_30">
        Новая рассылка&#160;&#160;&#160;<a class="b-layout__link b-layout__link_fontsize_13" href="/siteadmin/mailer/">Все рассылки</a>
        <a class="b-layout_link b-layout__link_fontsize_13 b-layout__link_float_right" href="javascript:void(0);" onclick="preview();">Предпросмотр</a>
    </h2>
     <?php if($is_update_mailer) {?>
     <div class="b-fon b-fon_width_full b-fon_padbot_17">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_35 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf b-fon__body_bordbot_dfedcf">
            <span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span>Рассылка сохранена.
        </div>
     </div>
    <?php }//if?>
    <?php if($is_sending_me) {?>
     <div class="b-fon b-fon_width_full b-fon_padbot_17">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_35 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf b-fon__body_bordbot_dfedcf">
            <span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span>Рассылка была выслана автору.
        </div>
     </div>
    <?php }//if?>
    
    
    
    <form method="post" enctype="multipart/form-data" id="create_form" name="create_mailer_form">
        <input type="hidden" name="preview" id="preview" value="0">
        <input type="hidden" name="action" id="action" value="<?= ($gAction == 'edit'?'edit':'create')?>">
        <input type="hidden" name="in_draft" id="draft" value="0">
        <input type="hidden" name="status_sending" id="status_sending" value="1">
        <input type="hidden" name="status_message" id="status_message" value="0">
        <?php if($gAction == 'edit') { ?>
            <input type="hidden" name="id" value="<?= (int) $message['id']?>">
            <input type="hidden" name="id_filter_frl" value="<?= (int) $message['filter_frl']?>">
            <input type="hidden" name="id_filter_emp" value="<?= (int) $message['filter_emp']?>">
            <input type="hidden" name="file_name" value="<?= (string) $message['filter_file']?>">
        <?php }//else?>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_130">
                    <div class="b-layout__txt">Тема письма</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo">
                        <div class="b-combo__input">
                            <input id="c1" class="b-combo__input-text" name="subject" type="text" size="80" value="<?= stripslashes($message['subject'])?>" />
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_40" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_130">
                    <div class="b-layout__txt">Текст письма</div>
                </td>
                <td class="b-layout__right">
                    <span id="i_message"></span>
                    
                    <?php if(false): ?>
                    <textarea conf="admin" class=" <?= ($mailer->error['message'] ? "wysiwyg-error" : "")?>" name="message" id="main_message" cols="80" rows="5"><?=$message['message']?></textarea>
                    <?php endif; ?>
                    
                    <div class="b-textarea">
                        <textarea rows="5" cols="80" name="message" id="main_message" class="b-textarea__textarea"><?=htmlspecialchars($message['message']);?></textarea>
                    </div>
                    
                    <?php if(mailer::$LINKS_HINT) { ?>
                    <?php foreach(mailer::$LINKS_HINT as $hint=>$descr) { ?>
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11"><a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bold b-layout__link_bordbot_dot_000" href="javascript:void(0)" onclick="setPlaceholderWysiwyg(this); return false;" title="<?=$hint?>"><?= $hint?></a> — <?= $descr?></div>    
                    <?php }//foreach?>
                    <?php }//if?>
                    <div id="attachedfiles" class="b-fon b-fon_width_full"></div>
                    <script type="text/javascript">
                        var attachedfiles_list = new Array();
                        <?
                        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
                        $attachedfiles = new attachedfiles($attachedfiles_session);
                        if(!$attachedfiles_session && $draft_id) {
                            $attachedfiles_tmpdraft_files = drafts::getAttachedFiles($draft_id, 5);
                            if($attachedfiles_tmpdraft_files) {
                                $attachedfiles_prj_files = array();
                                foreach($attachedfiles_tmpdraft_files as $attachedfiles_draft_file) {
                                    $attachedfiles_draft_files[] = $attachedfiles_draft_file;
                                }
                                $attachedfiles->setFiles($attachedfiles_draft_files, 1);
                            }
                        } else {
                            $attachedfiles_tmpprj_files = $mailer->getAttach($message['id']);
                            if($attachedfiles_tmpprj_files) {
                                $attachedfiles_prj_files = array();
                                foreach($attachedfiles_tmpprj_files as $attachedfiles_prj_file) {
                                    $attachedfiles_prj_files[] = $attachedfiles_prj_file['fid'];
                                }
                                $attachedfiles->setFiles($attachedfiles_prj_files);
                            }
                        }
                        $attachedfiles_files = $attachedfiles->getFiles();
                        if($attachedfiles_files) {
                            $n = 0;
                            foreach($attachedfiles_files as $attachedfiles_file) {
                                echo "attachedfiles_list[{$n}] = new Object;\n";
                                echo "attachedfiles_list[{$n}].id = '".md5($attachedfiles_file['id'])."';\n";
                                echo "attachedfiles_list[{$n}].name = '{$attachedfiles_file['orig_name']}';\n";
                                echo "attachedfiles_list[{$n}].path = '".WDCPREFIX."/{$attachedfiles_file['path']}{$attachedfiles_file['name']}';\n";
                                echo "attachedfiles_list[{$n}].size = '".ConvertBtoMB($attachedfiles_file['size'])."';\n";
                                echo "attachedfiles_list[{$n}].type = '{$attachedfiles_file['type']}';\n";
                                $n++;
                            }
                        }
                        ?>
                        attachedFiles.init('attachedfiles', 
                           '<?=$attachedfiles->getSession()?>',
                           attachedfiles_list, 
                           '<?=mailer::MAX_FILE_COUNT?>',
                           '<?=mailer::MAX_FILE_SIZE?>',
                           '<?=implode(', ', $GLOBALS['disallowed_array'])?>',
                           'mailer',
                           '<?=get_uid(false)?>'
                           );
                    </script>
                    
                    <?/* <div class="b-file">
                        <table cellspacing="0" cellpadding="0" border="0" class="b-file_layout">
                            <tbody>
                                <tr>
                                    <td class="b-file__button">            
                                        <div class="b-file__wrap">
                                            <input type="file" class="b-file__input">
                                            <a class="b-button b-button_rectangle_color_transparent" href="#">
                                                <span class="b-button__b1">
                                                    <span class="b-button__b2">
                                                            <span class="b-button__txt">Прикрепить файлы</span>
                                                    </span>
                                                </span>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="b-file__text">
                                        <div class="b-filter" style="z-index: 10;">
                                            <div class="b-filter__body b-filter__body_padtop_10"><a class="b-filter__link b-filter__link_fontsize_11 b-filter__link_dot_41" href="#">Требования к файлам</a></div>
                                            <div class="b-shadow b-filter__toggle b-shadow__margleft_-110 b-shadow__margtop_10 b-filter__toggle_hide">
                                                <div class="b-shadow__right">
                                                    <div class="b-shadow__left">
                                                        <div class="b-shadow__top">
                                                            <div class="b-shadow__bottom">
                                                                <div class="b-shadow__body b-shadow__body_pad_15 b-shadow_width_270 b-shadow__body_bg_fff">
                                                                    <div class="b-shadow__txt b-shadow__txt_fontsize_11 b-shadow__txt_padbot_5">Разрешается добавлять не более <span class="b-shadow__txt b-shadow__txt_bold">10 файлов</span> общим объемом не более 5 МБ.</div>
                                                                    <div class="b-shadow__txt b-shadow__txt_fontsize_11 b-shadow__txt_padbot_5">jpg и gif размером <span class="b-shadow__txt b-shadow__txt_bold">600х1000 пикс.</span> и весом не более 300 КБ будут вставлены в текст поста, остальные файлы будут приложены к нему.</div>
                                                                    <div class="b-shadow__txt b-shadow__txt_fontsize_11">Запрещенные форматы: ade, adp, chm, cmd, com, cpl, exe, hta, ins, isp, jse, lib, mde, msk, msp, mst, pif, scr, sct, shb, sys, vb, vbe, vbs, vxd, wsc, wsf, wsh</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="b-shadow__tl"></div>
                                                <div class="b-shadow__tr"></div>
                                                <div class="b-shadow__bl"></div>
                                                <div class="b-shadow__br"></div>
                                                <div class="b-shadow__icon_nosik"></div>
                                                <div class="b-shadow__icon_close"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>	*/?>										
                </td>
            </tr>
        </table>

        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_5" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_130">&#160;</td>
                <td class="b-layout__right">
                    <?php $sum = $mailer->calcSumRecipientsCount($message, array($rec_emp_count, $rec_frl_count));?>
                    <div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_15">Получатели &mdash; <span id="all_recipients_count"><?= number_format($sum, 0, ",", " ");?> человек</span> &#160;&#160;<a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="calcRecpient();">Пересчитать</a></div>
                </td>
            </tr>
        </table>

        <div class="b-layout__txt b-layout__txt_margleft_130 b-layout__txt_padbot_5 b-username b-check">
            <input id="emp_check1" class="b-check__input" name="filter_emp" type="checkbox" value="1" <?= ($message['filter_emp'] ? "checked" : "")?>/>
            <label class="b-check__label b-check__label_fontsize_13" for="emp_check1">
                <span class="b-username__role b-username__role_emp"></span>
                <span class="b-username__txt b-username__txt_color_6db335">Работодатели</span> &mdash; <span id="emp_recipients_count"><?= number_format(($message['count_rec_emp']>0 ? $message['count_rec_emp'] : $rec_emp_count), 0, ",", " ")?></span>
            </label>
            <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padleft_5 b-layout__txt_top_-1">
                <span class="b-layout__ygol  b-layout__ygol_hide"></span>
                <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_0f71c8 show-filter" href="#">Показать фильтры</a>
            </span>
        </div>
        <? include ("tpl.filter.emp.php"); ?>	

        <div class="b-layout__txt b-layout__txt_margleft_130 b-layout__txt_padbot_5 b-username b-check">
            <input id="frl_check2" class="b-check__input" name="filter_frl" type="checkbox" value="1" <?= ($message['filter_frl'] ? "checked" : "")?>/>
            <label class="b-check__label b-check__label_fontsize_13" for="frl_check2">
                <span class="b-username__role b-username__role_frl"></span>
                <span class="b-username__txt b-username__txt_color_fd6c30">Фрилансеры</span> &mdash; <span id="frl_recipients_count"><?= number_format(($message['count_rec_frl']>0 ? $message['count_rec_frl'] : $rec_frl_count), 0, ",", " ")?></span>
            </label> 
            <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padleft_5 b-layout__txt_top_-1">
                <span class="b-layout__ygol  b-layout__ygol_hide"></span>
                <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_0f71c8 show-filter" href="#">Показать фильтры</a>
            </span>
        </div>
        <? include ("tpl.filter.frl.php"); ?>
			
		<div class="b-layout__txt b-layout__txt_margleft_130 b-layout__txt_padbot_5 b-username b-check">
            <input id="file_check3" class="b-check__input" name="filter_file" type="checkbox" value="1" <?= ($message['filter_file'] ? "checked" : "")?>/>
            <label class="b-check__label b-check__label_fontsize_13" for="file_check3">
                <span class="b-username__role b-username__role_all"></span>
                <span class="b-username__txt">Из файла по списку</span>
            </label> 
            <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padleft_5 b-layout__txt_top_-1">
                <span class="b-layout__ygol  b-layout__ygol_hide"></span>
                <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_0f71c8 show-filter" href="#">Загрузить файл</a>
            </span>
        </div>
        <? include ("tpl.filter.file.php"); ?>

        <table class="b-layout__table b-layout__table_width_full b-layout__table_margtop_30" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_130">
                    <div class="b-layout__txt">Отправить</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-check b-check_padbot_10 b-check_padtop_3">
                        <input id="check3" class="b-check__input" name="type_sending[0]" type="checkbox" value="1" <?= ( (int) $message['type_sending'][0] == 1 ? "checked" : ( isset($message['type_sending']) ? "" : "checked" ) )?>/>
                        <label class="b-check__label b-check__label_fontsize_13" for="check3">Личным сообщением</label>
                    </div>
                    <div class="b-check">
                        <input id="check4" class="b-check__input" name="type_sending[1]" type="checkbox" value="1" <?= ( (int) $message['type_sending'][1] == 1 ? "checked" : ( isset($message['type_sending']) ? "" : "checked" ) )?>/>
                        <label class="b-check__label b-check__label_fontsize_13" for="check4">Письмом на почту</label>
                    </div>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margtop_30" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_130">
                    <div class="b-layout__txt">Регулярность</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-select">
                        <select name="type_regular" class="b-select__select b-select__select_width_220" onchange="selectRegularType(this.value, sregtype);">
                            <?php foreach(mailer::$TYPE_REGULAR as $id_reg=>$name_reg) {?>
                            <option value="<?=$id_reg?>" <?= ($id_reg == $message['type_regular']?'selected':'')?>><?=$name_reg?></option>
                            <?php }//foreach?>
                        </select>
                    </div>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margtop_30 <?= !$is_sub_regular?"b-layout_hide":""?>" id="repeat_type" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_130">
                    <div class="b-layout__txt">Повторять</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-select">
                        <select id="type_send_regular" name="type_send_regular" class="b-select__select b-select__select_width_220">
                            <?php if($is_sub_regular) { ?>
                            <?php foreach(mailer::$SUB_TYPE_REGULAR[$message['type_regular']] as $id_sub_reg=>$sub_regular) { ?>
                            <option value="<?=$id_sub_reg?>" <?= ($id_sub_reg == $message['type_send_regular']?'selected':'')?>><?=$sub_regular?></option>
                            <?php }//foreach?>
                            <?php } else {//if?>
                            <option value="1">Каждое первое число</option>
                            <?php }//else?>
                        </select>
                    </div>
                </td>
            </tr>
        </table>

        <table class="b-layout__table b-layout__table_width_full b-layout__table_margtop_30" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_130">
                    <div class="b-layout__txt b-layout__txt_lineheight_13">Дата и время<br />отправления</div>
                </td>
                <td class="b-layout__right">
                    <span id="i_date_sending"></span>
                    <div class="b-combo <?= ($is_sub_regular?"b-combo_hide":"b-combo_inline-block"); ?>" id="date_sending">
                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes use_past_date no_set_date_on_load">
                            <input id="date_sending" class="b-combo__input-text" name="date_sending" type="text" size="80"  value="<?= (!$is_sub_regular && $message['date_sending'])?date('d.m.Y', strtotime($message['date_sending'])):""?>" />
                            <label class="b-combo__label" for="date_sending"></label>
                            <span class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                    <span class="b-layout__txt b-layout__txt_padtop_3 <?= ($is_sub_regular?"b-layout_hide":"b-layout__txt_inline-block"); ?>" id="str_date_sending">&#160;в&#160;</span>
                    <span id="i_time_sending"></span>
                    <div class="b-select b-select_inline-block">
                        <select id="time_sending" name="time_sending">
                            <?php foreach(range(0,23) as $hour) {?>
                            <option value="<?= ($hour<10?"0".$hour:$hour )?>" <?= ($hour == date('G', strtotime($message['date_sending']))?"selected":"")?>><?= ($hour<10?"0".$hour:$hour )?>:00</option>
                            <?php }//foreach?>
                        </select>
                        <label class="b-combo__label" for="c1"></label>
                    </div>
                    <?if($mailer->error['time_sending'])  print view_error($mailer->error['time_sending']);?>
                    <?if($mailer->error['date_sending'])  print "<br/>" . view_error($mailer->error['date_sending']);?>
                </td>
            </tr>
        </table>

        <div class="b-buttons b-buttons_padtop_40 b-buttons_padleft_132">
            <a class="b-button b-button_flat b-button_flat_green"  href="javascript:void(0)" onClick="$('draft').set('value', '0'); $('create_form').submit();">Поставить в очередь</a>
            &#160;&#160;<a class="b-buttons__link" href="javascript:void(0)" onclick="$('draft').set('value', '1'); $('create_form').submit();">сохранить как черновик</a>
            <span class="b-buttons__txt">,</span>	
            <a class="b-buttons__link" href="javascript:void(0)" onclick="$('draft').set('value', '1'); $('action').set('value', '<?=$is_created?'create_and_sendme':'edit_and_sendme'?>'); $('create_form').submit();">выслать сначала мне</a>	
            <?php if(!$is_created) {?>
            <span class="b-buttons__txt">или</span>	
            <a class="b-buttons__link b-buttons__link_color_c10601" href="javascript:void(0)" onclick="$('action').set('value', 'delete'); $('create_form').submit();">удалить</a>
            <?php }//if?>
        </div>
	</form>
</div>	