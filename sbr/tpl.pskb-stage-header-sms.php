<?

$pskb_code = '';
if (defined('PSKB_TEST_MODE')) {
    $pskb_code = $pskb->getSmsCode($lc['lc_id']);
    if ($pskb_code) $pskb_code = $pskb_code[0];
}

?>
<div class="b-layout b-layout_padtop_10 b-layout_padbot_15">
    <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_padleft_35 b-layout__left_padright_20">&nbsp;</td>
            <td class="b-layout__right b-layout__right_padright_15 b-layout__right_width_73ps">
                <div class="b-layout__txt b-layout__txt_padbot_20">Ќа номер <span class="b-layout__bold"><?= pskb::phone($lc['numPerf']) ?></span> в течение 5 минут придет код подтверждени€. ¬ведите последний полученный код, чтобы завершить <?= $sbr->status == sbr::STATUS_COMPLETED ? 'сделку' : 'этап' ?>.<br>
                    <span class="b-layout__txt b-layout__txt_color_c7271e" id="alert_sms"></span>
                    <span class="b-layout__txt" id="resend_sms"></span>
                    <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" id="send_sms" href="javascript:void(0)" onclick="xajax_resendCode(<?= $sbr->data['id'] ?>, <?= $stage->data['id']?>)">¬ыслать код повторно.</a>
                </div>
                <div class="b-input b-input_height_60 b-input_width_100 b-input_margbot_20" id="input_sms">
                    <input class="b-input__text b-input__text_fontsize_22 b-input__text_align_center" name="sms_code" type="text" value="<?= $pskb_code ?>" onfocus="$(this).getParent().removeClass('b-input_error');">
                </div>

                
                <div id="finance-err" class="b-layout__txt b-layout__txt_color_c10600 b-layout__txt_padleft_20 b-layout__txt_padbot_10 b-layout_hide"><span class="b-icon b-icon_top_2 b-icon_margleft_-20 b-icon_sbr_rattent"></span><span id="finance-err-txt"></span></div>
                
                <a href="javascript:void(0)" onclick="_send_code(<?= $sbr->data['id'] ?>)" id="send_btn" class="b-button b-button_flat b-button_flat_green">ѕодтвердить</a>
                
                <script>
                    function _send_code(id) {
                        var stage_id = '<?= $stage->data['id']?>';
                        var btn = $('send_btn');
                        if (!btn || !id) return false;
                        
                        var code = document.getElement('input[name=sms_code]');
                        if (!code) return false;
                        
                        code = code.get('value');
                        
                        _loader_show();
                        
                        xajax_subOpen(id, code, stage_id);
                    }
                    
                    function _raise_err(msg) {
                        if (!$('finance-err')) {
                            return false;
                        }
                        $('finance-err').getElement('#finance-err-txt').set('html', msg);
                        $('finance-err').removeClass('b-layout_hide');
                        $('input_sms').addClass('b-input_error');
                        _loader_hide();
                    }
                    
                    function _loader_show() {
                        var btn = $('send_btn');
                        if (!btn) return false;
                        btn.addClass('b-button_disabled');
                        if ($('finance-err')) {
                            $('finance-err').addClass('b-layout_hide');
                            $('input_sms').removeClass('b-input_error');
                        }
                    }
                    
                    function _loader_hide() {
                        var btn = $('send_btn');
                        if (!btn) return false;
                        btn.removeClass('b-button_disabled');
                    }
                </script>
                
</td>
        </tr>
    </table>
</div>