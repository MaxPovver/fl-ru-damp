<div class="qq-progress-bar" id="uploader.progress{idFile}" style="display:none">
    <div class="quiz-progress-num qq-progress-bar-percent" style="width:0%">0%</div>
    <div class="quiz-progress-bar">
        <div class="quiz-progress-bar qq-progress-bar-line" style="width:0%;background-color:#74bb54;"></div>
    </div>
</div>

<div class="qq-upload-spinner" id="uploader.spinner{idFile}" style="display:none">
    <table class="b-icon-layout wdh100">
        <tr>
            <td class="b-icon-layout__icon"><img class="b-fon__loader load-spinner" src="/images/load_fav_btn.gif" alt="" width="24" height="24" /></td>
            <td class="b-icon-layout__files qq-upload-spinner-text">Идет загрузка файла…</td>
            <td class="b-icon-layout__size">&nbsp;</td>
            <td class="b-icon-layout__operate">&nbsp;</td>
        </tr>
    </table>
</div>

<table class="b-icon-layout__table" id="uploader.file{idFile}" file="" cellpadding="0" cellspacing="0" border="0" style="display:none">
    <tr class="b-icon-layout__tr attachedfiles_template">
        <td class="b-icon-layout__icon b-icon-layout__icon_height_25">
            <i class="b-icon qq-upload-ico"></i>
        </td>
        <td class="b-icon-layout__files b-icon-layout__files_fontsize_13">
            <a href="javascript:void(0)" class="b-icon-layout__link b-icon-layout__link_fontsize_13 qq-upload-file">{fileName}</a><span class="qq-upload-size"></span>
        </td>
        <td class="b-icon-layout__operate b-icon-layout__operate_valign_top b-icon-layout__operate_padleft_10">
            <a href="javascript:void(0)" class="b-button b-button_admin_del b-button_margtop_4 qq-upload-delete"></a>
        </td>
    </tr>
</table>