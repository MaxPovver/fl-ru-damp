<?php 
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
if(get_uid(false) && $_SESSION['sex'] === null){ ?>
<script type="text/javascript">
window.addEvent('domready', function() {
   $('ov-sex-list').getElements('.b-radio__label').addEvent('click', function(){
      $('ov-sex-list').getNext('.b-button').addClass('b-button_rectangle_color_green').removeClass('b-button_rectangle_color_disable');
   });
   $('ov-sex-list').getElements('.b-radio__input').addEvent('click', function(){
      $('ov-sex-list').getNext('.b-button').addClass('b-button_rectangle_color_green').removeClass('b-button_rectangle_color_disable');
   });
});

function getSex(){
var sex = null;
if($('m_sex').checked) sex = 1;
if($('f_sex').checked) sex = 0;
return sex;
}

function setSex() {
    var sex = getSex();
    if(sex === null) return false;
    new Request.JSON({
        url: "/xajax/users.server.php",
        onSuccess: function(resp){
            if(resp.status != 'ok'){
                alert(resp.alert);
            }else{
                var myEffect = new Fx.Morph('ov-sex', {duration: '1500', transition: Fx.Transitions.linear.easeOut});
                var myEffect2 = new Fx.Morph('need_sex', {duration: '1500', transition: Fx.Transitions.linear.easeOut});
                $('ov-sex-q').setStyle('display', 'none');
                $('ov-sex-a').setStyle('display', 'block');
                myEffect.start({
                    'opacity': 0
                });
                myEffect2.start({
                    'opacity': 0
                });
            }
        }
    }).post({
       xjxfun: 'SetSex',
       'u_token_key': _TOKEN_KEY,
       sex: sex
    });
}
</script>
<div id="ov-sex" class="b-shadow b-shadow_width_300 b-shadow_center" style="z-index:5">
	<div class="b-shadow__right">
		<div class="b-shadow__left">
			<div class="b-shadow__top">
				<div class="b-shadow__bottom">
					<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                <div id="ov-sex-q">
                    <form method="post" action="/" id="form_sex_choice" onsubmit="return false">
                    <h4 class="b-shadow__title b-shadow__title_padbot_15">Определились?</h4>
                    <div class="b-shadow__txt b-shadow__txt_padbot_10">Ой, мы случайно нажали кнопку, которая делает обязательным заполнение поля "пол", простите нас, пожалуйста, но мы оставили вам выбор:</div>
										<div id="ov-sex-list" class="b-radio b-radio_layout_vertical">
											<div class="b-radio__item b-radio__item_padbot_10">
												<input id="m_sex" class="b-radio__input" name="ov-sex" type="radio" value="1" />
												<label class="b-radio__label" for="m_sex">Мужской</label>
											</div>
											<div class="b-radio__item b-radio__item_padbot_10">
												<input id="f_sex" class="b-radio__input" name="ov-sex" type="radio" value="1" />
												<label class="b-radio__label" for="f_sex">Женский</label>
											</div>
										</div>
										<a class="b-button b-button_rectangle_color_disable" onclick="setSex(); return false;" href="javascript:void(0);">
												<span class="b-button__b1">
														<span class="b-button__b2">
																<span class="b-button__txt">Продолжить</span>
														</span>
												</span>
										</a>
                    </form>
                </div>
                <div id="ov-sex-a" style="display:none">
                    <h4 class="b-shadow__title b-shadow__title_padbot_15">Спасибо!</h4>
                    <div class="b-shadow__txt">Вы можете продолжить работу на сайте.</div>
                </div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="b-shadow__tl"></div>
	<div class="b-shadow__tr"></div>
	<div class="b-shadow__bl"></div>
	<div class="b-shadow__br"></div>
</div>
<div id="need_sex" class="b-shadow__overlay"></div>
<?php } ?>
