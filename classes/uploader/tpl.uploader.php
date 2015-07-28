<div class="<?= $cssName ?>-tpl">
    <input type="hidden" name="IDResource[]" value="{IDResource}"/>
    
    <span class="qq-upload-list"></span>
    
    <div class="b-layout__txt b-layout__txt_color_c10600 b-layout__txt_padtop_5 b-layout__txt_padbot_5 qq-upload-error" style="display: none;">
        <span class="b-icon b-icon_sbr_rattent"></span>
        <span class="qq-upload-error-text"></span>
        <a class="b-icon-layout__link b-icon-layout__link_dot_666 qq-upload-error-close" href="javascript:void(0)">Скрыть</a>
    </div>
    
    <table class="b-file_layout" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td class="b-file__button">            
                <div class="b-file__wrap attachedfiles_file_div">
                    <div class="b-button b-button_flat b-button_flat_grey qq-upload-button" >{uploadButtonText}</div>
                </div>
            </td>
            <td class="b-file__text">
                <div style="z-index: 10;" class="b-filter">
                    <div class="b-filter__body b-filter__body_padtop_5">
                        <div class="b-file__txt b-file__txt_relative b-file__txt_padleft_15 qq-popup-info">
                            <a href="javascript:void(0)" class="b-filter__link b-filter__link_fontsize_11 b-filter__link_dot_41 b-fileinfo">{infoButton}</a>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>
    
</div>