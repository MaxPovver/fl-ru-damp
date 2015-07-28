<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/sms_services.php');
?>

<script type="text/javascript">
	<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/sms_gate.php"; ?>
	var LIMIT_EXCEED_LINK_TEXT = '<?=sms_gate::LIMIT_EXCEED_LINK_TEXT ?>';
	var LIMIT_SMS_TO_NUMBER = '<?=sms_gate::SMS_ON_NUMBER_PER_24_HOURS ?>';

	<?php
	$linkText     = ( $_SESSION['send_sms_time'] > time() && !$_SESSION["unbind_phone_action"] ? "Получить код повторно" : "Получить смс с кодом" );
	$smsLinkEnable  = sms_gate_a1::$enable_link_css;
	$smsLinkDisable = sms_gate_a1::$disable_link_css;
	$smsLinkStyle = $_SESSION['send_sms_time'] > time() ? $smsLinkDisable : $smsLinkEnable;

	if (strlen($ureqv['mob_phone']) > 10 ) {
		$sms = new sms_gate_a1($ureqv['mob_phone']);
		$sms->limitSmsOnNumberIsExceed($ureqv['mob_phone'], $recordId, $count, $message);
		?>var sms_message_link_end = '<?=$message ?>';
		<?php
		if ($count < sms_gate::SMS_ON_NUMBER_PER_24_HOURS) {
			$linkText .= " ($message)";
		} else {
			$linkText = $message;
			$smsLinkStyle = $smsLinkDisable;
			?>var smslimit = true;<?
		}
	}
	?>
</script>

<div id="user_phone_popup" class="b-shadow b-shadow_center b-shadow_width_500 b-shadow_pad_20 b-shadow_zindex_3 b-shadow_hide">
	<form method="POST" id='main_phone_form'>
	<input type="hidden" name="type" id="type" value="bind" />
	<input type="hidden" name="action" value="save_phone" />
    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_20">Привязка мобильного телефона к аккаунту &mdash; <span id="safety_status" class="b-layout__txt b-layout__txt_color_c10600 b-layout__txt_fontsize_15">выключена</span></div>
    <div class="b-layout__txt b-layout__txt_inline-block">Телефон</div>
    <div class="b-combo b-combo_inline-block b-combo_valign_mid ">
        <div class="b-combo__input b-combo__input_tel b-combo__input_width_170   b-combo__input_phone_countries_dropdown b-combo__input_visible_items_5 use_scroll show_all_records b-combo__input_init_countryPhoneCodes">
            <input type="text"  value="7" maxlength="15" size="12" id="mob_phone" name="mob_phone" class="b-combo__input-text " onfocus="if($('phone_error')) $('phone_error').addClass('b-layout__txt_hide'); if($('phone_bind')) $('phone_bind').removeClass('b-layout__txt_hide')"><label class="b-combo__label" for="mob_phone"></label>
            <span class="b-combo__tel"><span style="background-position:0 -660px" class="b-combo__flag"></span></span> 
        </div>
    </div>
    <div id="mob_phone_text" class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block">без пробелов и дефиса</div>
    <div id="sms_sent_ok" class="b-layout__txt b-layout__txt_padtop_20 b-layout__txt_hide">На номер <span></span> было отправлено СМС с кодом.<br />Введите его, чтобы отвязать телефон от аккаунта.</div>
	<div class="b-layout__txt b-layout__txt_padtop_20">
        <div id="mob_code_block" class="b-layout__txt b-layout__txt_padbot_15">
            <div class="b-layout__txt b-layout__txt_inline-block">Код подтверждения&#160;</div>
            <div class="b-combo b-combo_inline-block b-combo_valign_mid">
                <div class="b-combo__input b-combo__input_width_45">
                    <input type="text" name="smscode" id="smscode" class="b-combo__input-text" onfocus="if (this.value=='' && !$('getsms').hasClass('sent_by_focus')) {$('getsms').fireEvent('click');$('getsms').addClass('sent_by_focus')}"><label class="b-combo__label" for="smscode"></label>              
                </div>
            </div>
            <div class="b-layout__txt b-layout__txt_inline-block">&#160;<a href="javascript:void(0)" data-field="mob_phone" data-code="smscode" data-form="main_phone_form" id="getsms" class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_bold">Получить смс с кодом</a></div>
			<div id="sms_error" class="b-layout__txt b-layout__txt_color_c10600 b-layout__txt_padtop_10 b-layout__txt_hide">Неправильный код</div>
        </div>
        <div class="b-buttons" id="buttons_step1">
			<a onclick="User_Phone.savePhone(true);" class="b-button b-button_flat b-button_flat_green" href="javascript:void(0)">Привязать</a>
           <span class="b-buttons__txt">&#160; <a class="b-buttons__link" href="#" onClick="this.getParent('.b-shadow').addClass('b-shadow_hide');return false;">пока не привязывать</a></span>
        </div>
		<div class="b-buttons b-layout__txt_hide" id="buttons_step2">
           <a class="b-button b-button_flat b-button_flat_green" href="javascript:void(0)" onClick="this.getParent('.b-shadow').addClass('b-shadow_hide');return false;">Закрыть</a>
        </div>
		<div class="b-buttons b-layout__txt_hide" id="buttons_step3">
			<a onclick="User_Phone.savePhone(false);" class="b-button b-button_flat b-button_flat_green" href="javascript:void(0)">Отвязать</a>
           <span class="b-buttons__txt">&#160; <a class="b-buttons__link" href="#" onClick="this.getParent('.b-shadow').addClass('b-shadow_hide');return false;">пока не отвязывать</a></span>
        </div>
    </div>
	</form>
</div>

<script>
    var sit   = '<?= ( $_SESSION['send_sms_time'] > time() ? $_SESSION['send_sms_time'] - time() : 0) ?>';
    if ((typeof sit != 'undefined') && sit > 0) {
        var smstimeout = Math.round( new Date().valueOf() / 1000 );
        setTimeout(function() {
            $('getsms').removeClass('b-layout__link_bordbot_dot_80');
            $('getsms').addClass('b-layout__link_bordbot_dot_0f71c8');
            if ( $('was_send_sms_text') && $$("div.sms_form").length && $$("div.sms_form")[0].hasClass("b-layout__txt_hide") ) {
                $('was_send_sms_text').setStyle("display", null);
                $('was_send_sms_text2').setStyle("display", null);
            }
        }, sit*1000);
    }
</script>