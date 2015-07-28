<div id="ov-notice22-r" class="b-shadow b-shadow_center b-shadow_width_540 b-shadow_zindex_11 b-shadow_hide b-shadow_vertical-center">
    <div class="b-shadow__right">
        <div class="b-shadow__left">
            <div class="b-shadow__top">
                <div class="b-shadow__bottom">
                    <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">
                        
                        <h4 class="b-shadow__h4 b-shadow__h4_padbot_10" id="delreason_d4">Причина удаления</h4>

                        <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full">
                            <tr class="b-layout__tr">
                                <td class="b-layout__left b-layout__left_padbot_15 b-layout__left_width_90">
                                        <div class="b-layout__txt b-layout__txt_padtop_4">Причина:</div>
                                </td>
                                <td class="b-layout__right b-layout__right_padbot_15">
                                    <div id="delreason_div_select" class="b-select">
                                        <select class="b-select__select" disabled="disabled"><option>Подождите...</option></select>
                                    </div>
                                </td>
                            </tr>
                            <tr class="b-layout__tr">
                                <td class="b-layout__left b-layout__left_padbot_15 b-layout__left_width_90">&nbsp;</td>
                                <td class="b-layout__right b-layout__right_padbot_15">
                                    <div id="delreason_div_textarea" class="b-textarea">
                                        <textarea class="b-textarea__textarea" rows="" cols=""></textarea>
                                    </div>
                                </td>
                            </tr>

                            <tr class="b-layout__tr">
                                <td class="b-layout__left b-layout__left_width_90">&nbsp;</td>
                                <td class="b-layout__right">
                                    <div id="delreason_ban_btn" class="b-buttons">
                                        <a id="delreason_btn" href="javascript:void(0);" class="b-button b-button_flat b-button_flat_green" onclick="banned.commit(banned.banUid,$('bfrm_'+banned.banUid).get('value') )">Сохранить</a>
                                        <span class="b-buttons__txt b-buttons__txt_padleft_10">или</span>
                                        <a href="javascript:void(0);" class="b-buttons__link b-buttons__link_dot_c10601" onclick="banned.commit(banned.banUid,(banned.buffer[banned.banUid].action='close'));return false;">закрыть, не сохраняя</a>
                                    </div>
                                </td>
                            </tr>
                        </table>                
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="b-shadow__tl"></div>
    <div class="b-shadow__tr"></div>
    <div class="b-shadow__bl"></div>
    <div class="b-shadow__br"></div>
    <span class="b-shadow__icon b-shadow__icon_close" onclick="banned.commit(banned.banUid,(banned.buffer[banned.banUid].action='close'));$('ov-notice22-r').toggleClass('b-shadow_hide');return false;"></span>
</div>
