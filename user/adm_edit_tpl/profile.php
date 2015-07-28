<?php
/**
 * Шаблон попап формы быстрого редактирования профиля юзера
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IN_STDF') ) { 
    header("HTTP/1.0 404 Not Found"); // ибо нефиг
    exit();
}
?>

<input type="hidden" name="login" value="<?=$user->login?>">

<?=_parseHiddenParams($aParams)?>

<div id="adm_edit_tab_div1">
    <?php if ( in_array($ucolumn, $aText) || in_array($ucolumn, $aTextArea) ) { ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_relative b-form__name_bold b-form__name_width_80 b-form__name_padtop_3" for="adm_edit_pname">Поле</label>
        
        <?php if ( in_array($ucolumn, $aText) ) { ?>
        <div class="b-input b-input_inline-block b-input_width_545">
            <input type="text" id="adm_edit_pname" name="new_val" value="<?=input_ref($user->$ucolumn)?>" onfocus="adm_edit_content.hideError('new_val');" class="b-input__text" size="80">
        </div>
        <?php } else { ?>
        <div class="b-textarea_inline-block b-textarea_width_550">
            <textarea id="adm_edit_msg_source" style="display:none" cols="50" rows="20"><?=input_ref($user->$ucolumn)?></textarea>
            <textarea id="adm_edit_msg" name="new_val" onfocus="adm_edit_content.hideError('new_val')" class="b-textarea__textarea_width_full b-textarea__textarea_height_70" cols="77" rows="5"></textarea>
        </div>
        <?php } ?>
        
        <div id="div_adm_edit_err_new_val" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padleft_80">
            <b class="b-fon__b1"></b>
            <b class="b-fon__b2"></b>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20" id="adm_edit_err_new_val">Поле заполнено некорректно</div>
            </div>
            <b class="b-fon__b2"></b>
            <b class="b-fon__b1"></b>
        </div>
    </div>
    <?php } ?>
    <?php if ( in_array($ucolumn, $aFile) ) { ?>
    <div class="b-form">
        <label class="b-form__name b-form__name_bold b-form__name_width_80 b-form__name_padtop_3">Файл</label>
        <iframe style="width:550px;height:45px;" scrolling="no" id="fupload" name="fupload" src="/upload.php?type=<?=$ucolumn?>&uid=<?=$user->uid?>" frameborder="0"></iframe>
        <input type="hidden" id="new_val" name="new_val" value="">
        <input type="hidden" id="old_val" name="old_val" value="<?=$user->$ucolumn?>">
        
        <br />
        <?php
        if ( $ucolumn == 'resume_file' ) {
            $sHref = WDCPREFIX.'/users/'.$user->login.'/resume/'.$user->resume_file;
        }
        if ( $ucolumn == 'photo' ) {
            $sHref = WDCPREFIX.'/users/'.$user->login.'/foto/'.$user->photo;
        }
        if ( $ucolumn == 'logo' ) {
            $sHref = WDCPREFIX.'/users/'.$user->login.'/logo/'.$user->logo;
        }
        ?>
        <span id="span_new_val"><a href="<?=$sHref?>" class="blue" target="_blank">Посмотреть загруженный файл</a>&nbsp;&nbsp;<input type="checkbox" class="b-check__input" id="adm_edit_del_prev" name="del_file" value="1"><label class="b-check__label" for="adm_edit_del_prev">Удалить файл</label></span>
        <br />Файлы следующих форматов запрещены к загрузке: <?=implode(', ', $GLOBALS['disallowed_array'])?>
    </div>
    <?php } ?>
</div>