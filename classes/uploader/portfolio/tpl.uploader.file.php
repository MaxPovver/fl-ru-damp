<div class="qq-progress-bar b-layout__txt" id="uploader.progress{idFile}" style="display:none">
    <div class="quiz-progress-num qq-progress-bar-percent" style="width:0%">0%</div>
    <div class="quiz-progress-bar">
        <div class="quiz-progress-bar qq-progress-bar-line" style="width:0%;background-color:#74bb54;"></div>
    </div>
</div>

<div class="qq-upload-spinner b-layout__txt" id="uploader.spinner{idFile}" style="display:none;">
    <table class="b-layout__table b-layout__table_width_full">
        <tr>
            <td class="b-icon-layout__icon"><img class="b-fon__loader load-spinner" src="/images/load_fav_btn.gif" alt="" width="24" height="24" /></td>
            <td class="b-icon-layout__files qq-upload-spinner-text"><span class="b-layout__txt b-layout__txt_pad_1_3 b-layout__txt_bg_f2">Идет загрузка файла…</span></td>
        </tr>
    </table>
</div>

<div class="b-layout b-layout_padtop_20 b-layout_padlr_10 qq-uploader-fileID" file="" style="display:none;" id="uploader.file{idFile}">
	<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full qq-upload-file-table">
   <tr class="b-layout__tr">
				<td class="b-icon-layout__icon"><i class="b-icon qq-upload-ico"></i></td>
				<td class="b-icon-layout__files"><div class="b-layout__txt b-layout__txt_padtop_5"><a href="javascript:void(0)" class="b-icon-layout__link b-icon-layout__link_fontsize_13 qq-upload-file">{fileName}</a></div></td>
				<td class="b-icon-layout__size" style="padding-right:0;"><div class="b-layout__txt b-layout__txt_padtop_5 qq-upload-size"></div></td>
			</tr>
 </table>
    
  <div id="swf_params" class="b-select b-select_padtop_10 b-select_center" style="display:none">
      <label for="wmode" class="b-select__label b-select__label_inline-block b-select__label_fontsize_11">wmode: </label>
      <select id="wmode" class="b-select__select b-select__select_width_70" name="wmode">
          <option>window</option>
          <option>direct</option>
          <option>gpu</option>
      </select>
  </div>
                
    <a class="b-button b-button_admin_gdel b-button_absolute b-button_top_-8 b-button_right_-8 qq-upload-delete b-button_z-index_3" href="javascript:void(0)"></a>
</div>
