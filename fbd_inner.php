
<? /*!--
<script type="text/javascript">
var alowLogin = function(){
    if($('login_fbd').get('value') != '' && $('pass_fbd').get('value') != ''){
        $('auth_form').submit();
    } else {
        alert('Неправильный логин или пароль');
    }
}
</script>
							<form id="auth_form" class="b-fon b-fon_bg_ffeda9 b-fon_width_400 b-fon_padtop_20" method="post" action="/" >
								<div class="b-fon__b1"></div>
								<div class="b-fon__b2"></div>
								<div class="b-fon__body b-fon__body_pad_10">
										<div class="b-form">
											<label class="b-form__name b-form__name_padtop_3 b-form__name_width_70" for="login_fbd">Логин</label><div 
											class="b-input b-input_inline-block b-input_width_180">
												<input id="login_fbd" class="b-input__text" name="login" tabindex="0" type="text" />
											</div>
										</div>
										<div class="b-form">
											<label class="b-form__name b-form__name_padtop_3 b-form__name_width_70" for="pass_fbd">Пароль</label><div
											 class="b-input b-input_inline-block b-input_width_180">
												<input id="pass_fbd" class="b-input__text" type="password" name="passwd" />
											</div><div
											 class="b-form__txt b-form__txt_padleft_10 b-form__txt_padtop_2"><a class="b-form__link b-form__link_color_666" href="/remind/">Напомнить пароль</a></div>
										</div>
										<div class="b-form b-form_padleft_70">
											<div class="b-check">
												<input id="b-check3" class="b-check__input" type="checkbox" value="1" name="autologin" />
												<label for="b-check3" class="b-check__label b-check__label_fontsize_13">Запомнить меня</label>
											</div>
										</div>
										<div class="b-form b-form_padbot_null b-form_padleft_70">
											<div class="b-buttons">
												<a class="b-button b-button_rectangle_transparent" onclick="alowLogin(); return false;" href="javascript:void()">
													<span class="b-button__b1">
														<span class="b-button__b2 b-button__b2_padlr_5">
															<span class="b-button__txt">Войти</span>
														</span>
													</span>
												</a>
												<span class="b-buttons__txt b-buttons__txt_fontsize_13 b-buttons__txt_padleft_5">или</span>
												<a class="b-buttons__link b-buttons__link_fontsize_13 b-buttons__link_color_ff6d3d b-buttons__link_margleft_10" href="/registration/">Зарегистрироваться</a>
											</div>
										</div>
										<input type="hidden" name="action" value="login"/>
										<input type="hidden" name="redirect" value="<?=$_SESSION['ref_uri']?>"/>
										<input type="submit" value="" style="position:absolute; left:-9999px;" />
								</div>
								<div class="b-fon__b2"></div>
								<div class="b-fon__b1"></div>
							</form>    

--*/?>

<script type="text/javascript">
var alowLogin = function(){
    if($('login_fbd').get('value') != '' && $('pass_fbd').get('value') != ''){
        $('auth_form').submit();
    } else {
        alert('Неправильный логин или пароль');
    }
}

// Добавил временно функцию сюда, она описана в wizard.js а тащить весть wizard - смысла нет
/**
 * меняет type для поля пароль (text/password)
 * @param string id - id элемента input для ввода пароля
 */
function show_password(id) {
    // добавил возможность задавать свой id (на случай если на странице несколько паролей)
    var v = id ? $(id) : $('reg_password');
    if (!v) return;
    
    if (Browser.ie) {
        if(v.type == 'password') {
            var inputText = new Element('input', {'class'  :'b-combo__input-text', 
                                                'value'  : v.value, 
                                                'name'   : 'password',
                                                'size'   : '80',
                                                'type'   : 'text',
                                                'id'     : id});
            var parent = v.getParent();
            v.dispose();
            parent.adopt(inputText);
        } else {
            var inputText = new Element('input', {'class'  :'b-combo__input-text', 
                                                'value'  : v.value, 
                                                'name'   : 'password',
                                                'size'   : '80',
                                                'type'   : 'password',
                                                'id'     : id});
            var parent = v.getParent();
            v.dispose();
            parent.adopt(inputText);
        }
        /*inputText.addEvent('blur', function(){
            registration_value_check('password');
        });
        inputText.addEvent('keyup', function(){
            registration_value_check('password', 0);
        });*/
    } else {
        if(v.getProperty('type') == 'password') {
            v.setProperty('type', 'text');
        } else {
            v.setProperty('type', 'password');
        }
    }
}
</script>
						  
<h1 class="b-page__title">Запрашиваемая страница доступна только зарегистрированным пользователям</h1>
<div class="b-layout__txt b-layout__txt_padbot_20">Авторизуйтесь, чтобы получить возможность пользоваться дополнительными услугами.</div>
            <form id="auth_form" method="post" action="/" >
            <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table">
                    <tr class="b-layout__tr">
                        <td class="b-layout__td b-layout__td_width_120 b-layout__td_width_null_iphone"><label class="b-layout__txt b-layout__txt_block  b-layout__txt_lineheight_1 b-page__desktop b-page__ipad" for="login_fbd">Логин, e-mail,<br/>телефон</label></td>
                        <td class="b-layout__td b-layout__td_width_240 b-layout__td_padbot_20 b-layout__td_width_full_iphone">
                            <label class="b-layout__txt b-layout__txt_block b-layout__txt_padbot_5 b-page__iphone" for="login_fbd">Логин, e-mail, телефон</label>
                            <div class="b-combo">
                                <div class="b-combo__input">
                                    <input id="login_fbd" class="b-combo__input-text" type="text" value="" size="80" name="login" tabindex="100" />
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="b-layout__tr">
                        <td class="b-layout__td b-layout__td_width_120 b-layout__td_width_null_iphone"><label class="b-layout__txt b-layout__txt_block b-layout__txt_padtop_5 b-page__desktop b-page__ipad" for="pass_fbd">Пароль</label></td>
                        <td class="b-layout__td b-layout__td_width_240 b-layout__td_padbot_20 b-layout__td_width_full_iphone">
                            <label class="b-layout__txt b-layout__txt_block b-layout__txt_padbot_5 b-page__iphone" for="pass_fbd">Пароль</label>
                            <div class="b-combo">
                                <div class="b-combo__input b-eye">
                                    <a class="b-eye__link b-eye__link_right_null" href="javascript:void(0)" onclick="show_password('pass_fbd')"><span class="b-eye__icon b-eye__icon_close"></span></a>
                                    <input id="pass_fbd" class="b-combo__input-text" type="password" value="" size="80" name="passwd" tabindex="101" />
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="b-layout__tr">
                        <td class="b-layout__td b-layout__td_width_120 b-layout__td_width_null_iphone">&nbsp;</td>
                        <td class="b-layout__td b-layout__td_width_240 b-layout__td_padbot_20 b-layout__td_width_full_iphone">
                            <div class="b-check">
                                <input id="remember" class="b-check__input" type="checkbox" value="1" name="autologin" tabindex="102" />
                                <label for="remember" class="b-check__label b-check__label_fontsize_13">Запомнить меня</label>
                            </div>
                        </td>
                    </tr>
            </table>
            <div class="b-buttons b-buttons_padleft_122 b-buttons_padbot_30 b-page__desktop b-page__ipad">
                <a class="b-button b-button_flat b-button_flat_green" onclick="alowLogin(); return false;" href="javascript:void()" tabindex="103">Войти</a>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <a class="b-buttons__link" href="/remind/">восстановить пароль</a> <span class="b-buttons__txt">или</span> <a class="b-buttons__link b-buttons__link_color_fd6c30" href="/registration/">зарегистрироваться</a>
            </div>
            <div class="b-page__iphone">
                <a class="b-button b-button_flat b-button_flat_green b-button_block" onclick="alowLogin(); return false;" href="javascript:void()" tabindex="103">Войти</a>
                <div class=" b-layout__txt b-layout__txt_center b-layout__txt_padtop_20">
                   <a class="b-layout__link" href="/remind/">восстановить пароль</a> <span class="b-layout__txt">или</span> <a class="b-layout__link b-layout__link_color_fd6c30" href="/registration/">зарегистрироваться</a>
                </div>
            </div>
            <input type="hidden" name="action" value="login" />
            <input type="hidden" name="redirect" value="<?=$_SESSION['ref_uri']?>" />										
            </form>

<div class="b-layout__txt b-layout__txt_padtop_20">Номер телефона указывается вместе с кодом страны (в формате +7..., +380... и т.д.)<br/><br/>Если у вас возникли вопросы - обращайтесь в <a class="b-layout__link" href="https://feedback.fl.ru">службу поддержки</a>. С удовольствием ответим.</div>
