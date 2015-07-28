<? return; // #0019588 ?>
<div class="b-fon b-fon_width_full b-fon_padbot_10" id="safety-phone-block">
	<span class="b-fon__bord-attent"></span>
 <div class="b-fon__body b-fon__body_pad_15 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffe679">
				<div class="b-fon__txt b-fon__txt_bold b-fon__txt_padleft_100 b-fon__txt_padbot_5">Восстановление пароля на телефон</div>
				<div class="b-fon__txt b-fon__txt_fontsize_11 b-fon__txt_padleft_100 b-fon__txt_padbot_20">Установка этого параметра позволит сохранить доступ к вашему аккаунту, даже если произойдет утрата электронного почтового ящика.<br>Пожалуйста, ознакомьтесь сначала с <a class="b-fon__link" href="https://feedback.fl.ru/article/details/id/209">инструкцией по привязке телефона</a>.</div>
				<div class="b-fon__txt b-fon__txt_padleft_100 b-fon__txt_inline-block b-fon__txt_width_160">Номер вашего телефона:</div><div class="b-combo b-combo_margtop_-5 b-combo_inline-block b-combo_padbot_15">
					<div class="b-combo__input b-combo__input_width_100 ">
						<input class="b-combo__input-text" id="safety_phone" name="safety_phone" type="text" size="80" value=""><label class="b-combo__label" for=""></label>
					</div>
				</div>
				<span id="safety_phone_example" class="b-fon__txt b-fon__txt_fontsize_11">&nbsp;&nbsp;&nbsp;Формат: +7xxxxxxxxxx</span>
                <span id="safety_phone_error" class="b-fon__txt b-fon__txt_fontsize_16 b-fon__txt_color_c10601 b-fon_hide"></span>
				<div class="b-check b-check_padleft_260">
						<input id="safety_phone_only" class="b-check__input" name="safety_phone_only" type="checkbox" value="">
						<label class="b-check__label b-check__label_fontsize_13">Восстанавливать пароль только с помощью телефона</label>
				</div>
				<div class="b-buttons b-buttons_padtop_15 b-buttons_padleft_260">
					<a id="safety_phone_now" class="b-button b-button_rectangle_color_green b-button_rectangle_color_disable" href="javascript:void(0)" onclick="SafetyPhoneNow();">
						<span class="b-button__b1">
							<span class="b-button__b2 b-button__b2_padlr_5">
								<span class="b-button__txt">Привязать телефон</span>
							</span>
						</span>
					</a>
					&nbsp;&nbsp;&nbsp;<a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript:void(0)" onclick="SafetyPhoneLater();">напомнить позже</a> <span class="b-buttons__txt">или</span>		<a class="b-buttons__link b-buttons__link_dot_c10601" href="javascript:void(0)" onclick="SafetyPhoneNever();">больше не показывать это сообщение</a>
				</div>


				
	</div>
	<span class="b-fon__bord-attent"></span>
</div>

<script type="text/javascript">
function SafetyPhoneNow() {
    var button = $('safety_phone_now');
    if (button && button.hasClass('b-button_rectangle_color_disable')) {
        return;
    }
    var phone_only = $('safety_phone_only').get('checked') ? 't' : 'f';
    new Request.JSON({
        url: '/xajax/safetyphone.server.php',
        onSuccess: function(resp) {
            if(resp && resp.success) {
                hideSafetyPhoneBlock();
                window.location='/users/<?=$_SESSION['login']?>/setup/safety/';
            } else if( resp && resp.error ) {
                $('safety_phone_error').set('html', "&nbsp;&nbsp;&nbsp;" + resp.error);
                $('safety_phone_error').removeClass('b-fon_hide');
                $('safety_phone_example').addClass('b-fon_hide');
            }
        }
    }).post({
       "xjxfun": "SafetyPhoneNow",
       "u_token_key": _TOKEN_KEY,
       "phone": $("safety_phone").get("value"),
       "phone_only": phone_only
    });
}

function SafetyPhoneLater() {
    new Request.JSON({
        url: '/xajax/safetyphone.server.php',
        onSuccess: function(resp){
            if(resp && resp.success) {
                hideSafetyPhoneBlock();
            }
        }
    }).post({'xjxfun': 'SafetyPhoneLater', 'u_token_key': _TOKEN_KEY});
}

function SafetyPhoneNever() {
    new Request.JSON({
        url: '/xajax/safetyphone.server.php',
        onSuccess: function(resp){
            if(resp && resp.success) {
                hideSafetyPhoneBlock();
            }
        }
    }).post({'xjxfun': 'SafetyPhoneNever', 'u_token_key': _TOKEN_KEY});
}

// скрывает блок привязки телефона к аккаунту
// а также корректирует положение промоблока, карусели и основного контентного блока
function hideSafetyPhoneBlock () {
    var safetyBlock = $$("div#safety-phone-block")[0];
    safetyBlock.dispose();
    
    shiftPromo(210);
        
}
function safetyPhoneEnter () {
    var input = $('safety_phone');
    var button = $('safety_phone_now');
    if (!input || !button) return;
    var phone = input.get('value');
    // проверяем введен ли телефон
    var valid = true;
    if (phone.length === 0 || !/^\+7\d{10}$/.test(phone)) {
        valid = false;
    }
    
    if (valid) {
        button.removeClass('b-button_rectangle_color_disable');
    } else {
        button.addClass('b-button_rectangle_color_disable');
    }
}
window.addEvent('domready', function () {
    var lastPhone = '';
    $('safety_phone').addEvent('keyup', function (e) {
        var input = $('safety_phone');
        var button = $('safety_phone_now');
        if (!input || !button) return;
        var phone = input.get('value');
        
        // фильтрация недопустимых символов
        if (!/^[\+0-9]*$/.test(phone)) {
            input.set('value', lastPhone);
            return false;
        } else {
            lastPhone = phone;
        }
        
        // проверяем введен ли телефон
        var valid = true;
        if (phone.length === 0 || !/^\+\d{1,}$/.test(phone)) {
            valid = false;
        }

        if (valid) {
            button.removeClass('b-button_rectangle_color_disable');
        } else {
            button.addClass('b-button_rectangle_color_disable');
            
        }
    });
    
    $('safety_phone').addEvent('focus',function(){
        $('safety_phone_error').addClass('b-fon_hide');
        $('safety_phone_example').removeClass('b-fon_hide');
    })
})

//window.addEvent('domready',function(){;});
</script>