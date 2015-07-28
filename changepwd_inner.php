<div class="b-layout__right b-layout__right_float_right b-layout__right_width_240">
    <!-- Banner 240x400 -->
    <?= printBanner240(false); ?>
    <!-- end of Banner 240x400 -->
</div>
<div class="b-layout__left b-layout__left_margright_270">
<h1 class="b-page__title">Изменение пароля</h1>
<?php 
    
    if (isset($master_error)) {
?>
<div class="b-layout__txt">
        <?=$master_error?>
</div> 
<?php
    } elseif ($action == "change" && $info) { 
?> 
<div class="b-fon">
		<div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
				<span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span>Пароль успешно изменен!
		</div>
</div>    
<? } elseif ($uuid) { ?>
<div class="b-layout__txt b-layout__txt_padbot_20">
			Для завершения процедуры и восстановления доступа, пожалуйста, укажите пароль к аккаунту:</div>
			<form action="/changepwd.php" method="post" id="cpwd">
			<input type="hidden" name="action" value="change">
			<input type="hidden" name="c" value="<?=$uuid?>">


			<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table">
                            <tbody>
                                
                                
                                <tr class="b-layout__tr">
                                    <td class="b-layout__td b-layout__td_width_100">
                                        <div class="b-layout__txt b-layout__txt_padtop_4">
                                            <label for="pswd">Новый пароль</label>
                                        </div>
                                    </td>
                                    <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_width_280">
                                        <div class="b-combo">
                                            <div class="b-combo__input b-combo__input_width_280 b-eye <?= $error ? 'b-combo__input_error' : ''?>">
                                                <a onclick="show_password('pwd'); return false;" tabindex="10000" class="b-eye__link b-eye__link_right_null " href="javascript:void(0)"><span class="b-eye__icon b-eye__icon_close"></span></a>
                                                <input type="password" class="b-combo__input-text" id="pwd" name="pwd" size="80" value="">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="b-layout__td b-layout__td_padleft_10">
                                        <div class="i-shadow">
                                            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_lineheight_1">От 6 до 24 символов. Допустимы латинские буквы, <br>цифры и следующие спецсимволы: !@#$%^&*()_+-=;,./?[]{}</div>
											<div id="error_email" class="b-shadow b-shadow_m b-shadow_top_0 b-shadow_zindex_3 <?=$error ? '' : 'b-shadow_hide'?>">
	                                            <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_10">
	                                                <div id="error_txt_email" class="b-layout__txt b-layout__txt_nowrap b-layout__txt_padright_15 b-layout__txt_color_c4271f"><span class="b-form__error"></span><?=$error?></div>
	                                            </div>
	                                            <span class="b-shadow__icon b-shadow__icon_close b-shadow__icon_right_12 b-shadow__icon_top_12"></span>
	                                            <span class="b-shadow__icon b-shadow__icon_nosik-left b-shadow__icon_top_10 b-shadow__icon_left_-4"></span>
	                                        </div>
                                        </div>
                                    </td>
                                    <td class="b-layout__td">&nbsp;</td>
                                </tr>
                      
                                
                                
                                <tr class="b-layout__tr">
                                    <td class="b-layout__td b-layout__td_width_100">
                                        <div class="b-layout__txt b-layout__txt_padtop_4">
                                            <label for="pswd_yet">Еще раз</label>
                                        </div>
                                    </td>
                                    <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_width_280">
                                        <div class="b-combo">
                                            <div class="b-combo__input b-combo__input_width_280 b-eye">
                                                <a onclick="show_password('pwd2'); return false;" tabindex="10000" class="b-eye__link b-eye__link_right_null " href="javascript:void(0)"><span class="b-eye__icon b-eye__icon_close"></span></a>
                                                <input type="password" class="b-combo__input-text" id="pwd2" name="pwd2" size="80" value="">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="b-layout__td b-layout__td_padleft_10">
                                    </td>
                                    <td class="b-layout__td">&nbsp;</td>
                                </tr>
                      
                                
                      
                                <tr class="b-layout__tr">
                                    <td class="b-layout__td b-layout__td_width_100"></td>
                                    <td colspan="3" class="b-layout__td b-layout__td_padbot_20">
                                        <div class="b-buttons"><button id="send_btn" onclick="yaCounter6051055.reachGoal('change_psw'); $('cpwd').submit();" class="b-button b-button_flat b-button_flat_green">Изменить и авторизоваться</button></div>
                                        
                                    </td>
                                </tr>
                            </tbody>
                        </table>

			</form>

</div>

<script type="text/javascript">
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
                                                'id'     : 'reg_password'});
            var parent = v.getParent();
            v.dispose();
            parent.adopt(inputText);
        } else {
            var inputText = new Element('input', {'class'  :'b-combo__input-text', 
                                                'value'  : v.value, 
                                                'name'   : 'password',
                                                'size'   : '80',
                                                'type'   : 'password',
                                                'id'     : 'reg_password'});
            var parent = v.getParent();
            v.dispose();
            parent.adopt(inputText);
        }
        inputText.addEvent('blur', function(){
            registration_value_check('password');
        });
        inputText.addEvent('keyup', function(){
            registration_value_check('password', 0);
        });
    } else {
        if(v.getProperty('type') == 'password') {
            v.setProperty('type', 'text');
        } else {
            v.setProperty('type', 'password');
        }
    }
}

</script>
<? } ?>