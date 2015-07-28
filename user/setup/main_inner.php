<?
if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}
$_promo_block = (bool)$_COOKIE['nfastpromo_open'];

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/users.common.php");
$xajax->printJavascript('/xajax/');

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
    <?
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
<? if(is_emp ()) { ?>
<form action="." method="post">
<div class="b-layout b-layout_padtop_20">
		  <h2 class="b-layout__title">Основные настройки</h2>
<? if ($info || $error) { ?>
	<? if ($info) { print(view_info($info)); } ?>
	<? if ($error) { print(view_error($error)); } ?>
<? } ?>


<table class="b-layout__table">
   <tr class="b-layout__tr">
      <td class="b-layout__td b-layout__td_width_140 b-layout__td_padbot_20">
          <div class="b-layout__txt b-layout__txt_padtop_5">Имя:</div>
      </td>
      <td class="b-layout__td b-layout__td_padbot_20">
          <div class="b-combo">
              <div class="b-combo__input b-combo__input_width_300">
                  <input class="b-combo__input-text" type="text" name="name" value="<?=($action == "main_change")? stripslashes($name):$user->uname?>" maxlength="21" />
              </div>
          </div>
          <? if ($alert[1]) print(view_error($alert[1])) ?>
      </td>
   </tr>
   <tr class="b-layout__tr">
      <td class="b-layout__td b-layout__td_width_140 b-layout__td_padbot_20">
          <div class="b-layout__txt b-layout__txt_padtop_5">Фамилия:</div>
      </td>
      <td class="b-layout__td b-layout__td_padbot_20">
          <div class="b-combo">
              <div class="b-combo__input b-combo__input_width_300">
                  <input class="b-combo__input-text" type="text" name="surname" value="<?=($action == "main_change")? stripslashes($surname):$user->usurname?>" maxlength="21" />
              </div>
          </div>
        <? if ($alert[2])  print(view_error($alert[2])) ?>
      </td>
   </tr>
   <tr class="b-layout__tr">
      <td class="b-layout__td b-layout__td_width_140 b-layout__td_padbot_20">
          <div class="b-layout__txt b-layout__txt_padtop_5">Электронная почта:</div>
      </td>
      <td class="b-layout__td b-layout__td_padbot_20">
          <div class="b-combo">
              <div class="b-combo__input b-combo__input_width_300">
                  <input class="b-combo__input-text" type="text" name="email" value="<?=($action == "main_change")? stripslashes($email):$user->email?>" maxlength="64" />
              </div>
          </div>
          <? if ($alert[3])  print(view_error($alert[3])) ?>
      </td>
   </tr>
   <tr class="b-layout__tr">
      <td class="b-layout__td b-layout__td_width_140 b-layout__td_padbot_20">
          <div class="b-layout__txt b-layout__txt_padtop_5">Заголовок страницы:</div>
      </td>
      <td class="b-layout__td b-layout__td_padbot_20">
          <div class="b-combo">
              <div class="b-combo__input b-combo__input_width_300">
                  <input class="b-combo__input-text"  type="text" name="pname" value="<?=($action == "main_change")? stripslashes($pname):$user->pname?>" />
              </div>
          </div>
      </td>
   </tr>
   <tr class="b-layout__tr">
      <td class="b-layout__td b-layout__td_width_140">
          <div class="b-layout__txt b-layout__txt_padtop_5">Введите пароль:</div>
      </td>
      <td class="b-layout__td">
          <div class="b-combo">
              <div class="b-combo__input b-combo__input_width_300">
                  <input class="b-combo__input-text"  type="password" name="oldpwd" />
                  <? if ($alert[4])  print(view_error($alert[4])) ?>
              </div>
          </div>
      </td>
   </tr>
   <tr class="b-layout__tr">
       <td class="b-layout__td b-layout__td_width_140 b-layout__td_padbot_20">&nbsp;</td>
       <td class="b-layout__td b-layout__td_padbot_20">
           <div class="b-layout__txt"><a href="/users/<?= $user->login ?>/setup/pwd/" class="b-layout__link">Изменить пароль</a></div>
       </td>
   </tr>
   <tr class="b-layout__tr">
      <td class="b-layout__td b-layout__td_width_140 b-layout__td_padbot_20">&#160;</td>
      <td class="b-layout__td b-layout__td_padbot_20">
          <div class="b-buttons">
              <button class="b-button b-button_flat b-button_flat_green" type="submit" name="btn">Изменить</button>
          </div>
      </td>
   </tr>
</table>
<input type="hidden" name="action" value="main_change" />
</div>
</form>




<? } else{ ?>





    <form action="." method="post">
      <div class="b-layout b-layout_padtop_20">
		  <h2 class="b-layout__title">Основные настройки</h2>
<? if ($info || $error) { ?>
	<? if ($info) { print(view_info($info)); } ?>
	<? if ($error) { print(view_error($error)); } ?>
<? } ?>
              <table class="b-layout__table">
                 <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_width_140 b-layout__td_padbot_20">
                        <div class="b-layout__txt b-layout__txt_padtop_5">Имя:</div>
                    </td>
                    <td class="b-layout__td b-layout__td_padbot_20">
                        <div class="b-combo">
                            <div class="b-combo__input b-combo__input_width_300">
                                <input class="b-combo__input-text" name="name" value="<?=($action == "main_change")? stripslashes($name):$user->uname?>" maxlength="21" type="text" />
                            </div>
                        </div>
						<? if ($alert[1]) print(view_error($alert[1])) ?>
                    </td>
                 </tr>
                 <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_width_140 b-layout__td_padbot_20">
                        <div class="b-layout__txt b-layout__txt_padtop_5">Фамилия:</div>
                    </td>
                    <td class="b-layout__td b-layout__td_padbot_20">
                        <div class="b-combo">
                            <div class="b-combo__input b-combo__input_width_300">
                                <input class="b-combo__input-text" type="text" name="surname" value="<?=($action == "main_change")? stripslashes($surname):$user->usurname?>" maxlength="21" />
                            </div>
                        </div>
					  <? if ($alert[2])  print(view_error($alert[2])) ?>
                    </td>
                 </tr>
                 <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_width_140 b-layout__td_padbot_20">
                        <div class="b-layout__txt b-layout__txt_padtop_5">Электронная почта:</div>
                    </td>
                    <td class="b-layout__td b-layout__td_padbot_20">
                        <div class="b-combo">
                            <div class="b-combo__input b-combo__input_width_300">
                                <input class="b-combo__input-text" type="text" name="email" value="<?=($action == "main_change")? stripslashes($email):$user->email?>" maxlength="64" />
                            </div>
                        </div>
						<? if ($alert[3])  print(view_error($alert[3])) ?>
                    </td>
                 </tr>
                 <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_width_140 b-layout__td_padbot_20">
                        <div class="b-layout__txt b-layout__txt_padtop_5">Заголовок страницы:</div>
                    </td>
                    <td class="b-layout__td b-layout__td_padbot_20">
                        <div class="b-combo">
                            <div class="b-combo__input b-combo__input_width_300">
                                <input class="b-combo__input-text"  type="text" name="pname" value="<?=($action == "main_change")? stripslashes($pname):$user->pname?>" />
                            </div>
                        </div>
                    </td>
                 </tr>
                 <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_width_140">
                        <div class="b-layout__txt b-layout__txt_padtop_5">Введите пароль:</div>
                    </td>
                    <td class="b-layout__td">
                        <div class="b-combo">
                            <div class="b-combo__input b-combo__input_width_300">
                                <input class="b-combo__input-text"  type="password" name="oldpwd" />
								<? if ($alert[4])  print(view_error($alert[4])) ?>
                            </div>
                        </div>
                    </td>
                 </tr>
                 <tr class="b-layout__tr">
                     <td class="b-layout__td b-layout__td_width_140 b-layout__td_padbot_20">&nbsp;</td>
                    <td class="b-layout__td b-layout__td_padbot_20">
                        <div class="b-layout__txt"><a href="/users/<?=$user->login?>/setup/pwd/" class="b-layout__link">Изменить пароль</a></div>
                    </td>
                 </tr>
                 <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_width_140 b-layout__td_padbot_20">&#160;</td>
                    <td class="b-layout__td b-layout__td_padbot_20">
                        <div class="b-buttons">
                            <button class="b-button b-button_flat b-button_flat_green" type="submit" name="btn">Изменить</button>
                        </div>
                    </td>
                 </tr>
              </table>
          <input type="hidden" name="action" value="main_change" />
       </div>
    </form>
<?} ?>

    <h3 class="b-layout__h3 b-layout__h3_padtop_17">Привязка аккаунтов в социальных сетях</h3>
    <div class="b-layout__txt b-layout__txt_padbot_10">
        Привязав ваш профиль к аккаунтам VKontakte, Facebook, Odnoklassniki, вы сможете заходить на сайт, авторизовавшись в одной из социальных сетей.
    </div>
    <div class="b-layout b-layout_margbot_30">
        <?php if ($social_bind_error): ?>
            <div class="b-layout__txt b-layout__txt_color_c4271f">
                <?=$social_bind_error?>
            </div>
        <?php endif; ?>
        <div class="b-layout__txt">
            <a href="<?=isset($social_links[OpauthModel::TYPE_VK]) ? $social_links[OpauthModel::TYPE_VK] : '/auth/?param=vkontakte'?>"
               <?=isset($social_links[OpauthModel::TYPE_VK]) ? 'target="blank"' : ''?>
               class="b-layout__link b-layout__link_lineheight_34 b-layout__link_valign_top">
                <span class="b-auth_btn b-auth_mini b-auth_btn_vk b-auth_margright_5 b-auth_btn_float_left">
                </span><?=isset($social_links[OpauthModel::TYPE_VK]) ? $social_links[OpauthModel::TYPE_VK] : 'Привязать VKontakte-аккаунт к профилю'?>
            </a>
        </div>
        <div class="b-layout__txt">
            <a href="<?=isset($social_links[OpauthModel::TYPE_FACEBOOK]) ? $social_links[OpauthModel::TYPE_FACEBOOK] : '/auth/?param=facebook'?>"
               <?=isset($social_links[OpauthModel::TYPE_FACEBOOK]) ? 'target="blank"' : ''?>
               class="b-layout__link b-layout__link_lineheight_34 b-layout__link_valign_top">
                <span class="b-auth_btn b-auth_mini b-auth_btn_facebook b-auth_margright_5 b-auth_btn_float_left">
                </span><?=isset($social_links[OpauthModel::TYPE_FACEBOOK]) ? $social_links[OpauthModel::TYPE_FACEBOOK] : 'Привязать Facebook-аккаунт к профилю'?>
            </a>
        </div>
        <div class="b-layout__txt">
            <a href="<?=isset($social_links[OpauthModel::TYPE_ODNOKLASSNIKI]) ? $social_links[OpauthModel::TYPE_ODNOKLASSNIKI] : '/auth/?param=odnoklassniki'?>"
               <?=isset($social_links[OpauthModel::TYPE_ODNOKLASSNIKI]) ? 'target="blank"' : ''?>
               class="b-layout__link b-layout__link_lineheight_34 b-layout__link_valign_top">
                <span class="b-auth_btn b-auth_mini b-auth_btn_odnoklassniki b-auth_margright_5 b-auth_btn_float_left">
                </span><?=isset($social_links[OpauthModel::TYPE_ODNOKLASSNIKI]) ? $social_links[OpauthModel::TYPE_ODNOKLASSNIKI] : 'Привязать Odnoklassniki-аккаунт к профилю'?>
            </a>
        </div>
    </div>
    

    <h3 class="b-layout__h3">Привязка мобильного телефона <span class="b-layout__txt <?= ( $reqv['is_activate_mob'] == 't' ? 'b-layout__txt_color_6bb336' : 'b-layout__txt_color_c10600' ); ?> b-layout__txt_fontsize_15" id="safety_status"><?= ( $reqv['is_activate_mob'] == 't' ? 'включена' : 'выключена' ); ?></span></h3>
    <div class="b-layout__txt">Телефон</div>
    <form method="POST" id='main_phone_form'>
        <input type="hidden" name="type" id="type" value="<?= $reqv['is_activate_mob'] == 't' ? 'unbind' : 'bind';?>" />
        <input type="hidden" name="action" value="save_phone" />
        <? if($reqv['is_activate_mob'] == 't' || $_SESSION['is_verify'] == 't') { ?>
        <input type="hidden" name="_mob_phone" value="<?= $ureqv['mob_phone'] ?>" />
        <? }//if?>
        <div class="b-combo b-combo_inline-block b-combo_valign_mid <?=(($reqv['is_activate_mob'] == 't')? 'b-combo__input_disabled' : '')?>">
            <div class="b-combo__input b-combo__input_tel b-combo__input_width_170 <?= $error_phone['phone'] ? "b-combo__input_error" : ""?>  b-combo__input_phone_countries_dropdown b-combo__input_visible_items_5 use_scroll show_all_records b-combo__input_init_countryPhoneCodes" id="user_mob_phone">
                <input <?=(($reqv['is_activate_mob'] == 't')? 'disabled' : '')?> class="b-combo__input-text " name="mob_phone" id="mob_phone" type="text" size="12" maxlength="15" value="<?= ( $ureqv['mob_phone'] == '' ? ($phone != '' ? str_replace("+", "", $phone) : '7' ) : str_replace("+", "", $ureqv['mob_phone']) ); ?>" onfocus="if($('phone_error')) $('phone_error').addClass('b-layout__txt_hide'); if($('phone_bind')) $('phone_bind').removeClass('b-layout__txt_hide')"/>
                <span class="b-combo__tel"><span class="b-combo__flag" style="background-position:0 -660px"></span></span> 
            </div>
        </div>
        <span class="phone_main">
            <?php if ($reqv['is_activate_mob'] == 't') { ?>
            &#160;&#160;
            <div class="b-layout__txt <?= !($error_phone['code'] && !$post_safety_phone) ? "" : "b-layout__txt_hide"?> b-layout__txt_inline-block b-layout__txt_fontsize_11">
                <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_0f71c8 sms_unbind_link" href="javascript:void(0)">Отвязать</a>
            </div>
            <span class="<?= $reqv['is_activate_mob'] == 't' && !($error_phone['code'] && !$post_safety_phone) ? "" : "b-layout__txt_hide"?>" id="safety_phone_block">
                <div class="b-check b-check_padbot_10 b-check_padtop_20 safety_phone_checks">
                    <input type="hidden" name="def_only_phone" value="<?= ($o_only_phone == 't' ? 1 : 0 ); ?>" />
                    <input class="b-check__input" name="only_phone" id="only_phone" value="t" type="checkbox" <?= ( ($o_only_phone == 't' && !$post_safety_phone) || $only_phone == 't' ? 'checked="checked"' : '' ); ?> <?= ( $reqv['is_activate_mob'] == 't' ? '' : 'disabled'); ?>/> <label class="b-check__label b-check__label_fontsize_13" for="only_phone">Восстанавливать пароль <span class="b-page__iphone"><br></span> только на мобильный телефон</label>
                </div>
                <div class="b-check b-check_padbot_10 safety_phone_check safety_phone_checks">
                    <input type="hidden" name="def_finance_safety_phone" value="<?= ($reqv['is_safety_mob'] == 't' ? 1 : 0 ); ?>" />
                    <input class="b-check__input" id="finance_safety_phone" name="finance_safety_phone" value="t" type="checkbox" <?= ( ( $reqv['is_safety_mob'] == 't' && !$post_safety_phone) || $finance_safety_phone == 't' ? 'checked="checked"' : '' ); ?> <?= ( $reqv['is_activate_mob'] == 't' ? '' : 'disabled'); ?>/> <label class="b-check__label b-check__label_fontsize_13" for="finance_safety_phone" >Вход на страницу &laquo;Финансы&raquo; &mdash; <span class="b-page__iphone"><br></span> только через код из смс-сообщения</label>
                </div>

                <div class="b-buttons b-buttons_padtop_20 b-layout__txt_hide button_first_save">
                    <a href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green">Сохранить изменения</a>
                    <span class="b-buttons__txt">&nbsp;&nbsp;&nbsp;или&nbsp;&nbsp;&nbsp;</span><a href="javascript:void(0)" class="b-buttons__link b-buttons__link_dot_0f71c8 first_cancel_btn">отменить</a>
                </div>
            </span>
           
            <div class="sms_form b-layout__txt <?= $error_phone['code'] ? "" : "b-layout__txt_hide"?> b-layout__txt_padtop_10">
                <h3 class="b-layout__h3 title"><?= !$post_safety_phone ? "Отвязать телефон" : "Подтверждение действий"?></h3>
                <div id="was_send_sms_text" class="b-layout__txt b-layout__txt_padbot_5" <?php if ( $_SESSION['send_sms_time'] >= time() ) { ?> style="display:none"<? } ?>>На номер <?= $ureqv['mob_phone']?> было отправлено СМС с <?= sms_gate::LENGTH_CODE?> цифрами.</div>
                <div id="was_send_sms_text2" class="b-layout__txt b-layout__txt_padbot_5" <?php if ( $_SESSION['send_sms_time'] >= time() ) { ?> style="display:none"<? } ?>>Введите их:</div>
                <div class="b-combo b-combo_inline-block b-combo_valign_mid">
                    <div class="b-combo__input b-combo__input_width_45 <?= $error_phone['code'] ? "b-combo__input_error" : ""?>">
                        <input class="b-combo__input-text" type="text" id="smscode" name="smscode">              
                    </div>
                </div>
                <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_fontsize_11">
                    <a class="b-layout__link b-layout__link_fontsize_11 <?=$smsLinkStyle ?>" href="javascript:void(0)" data-field="mob_phone" data-code="smscode" data-form="main_phone_form" id="getsms"><?=$linkText ?></a>
                </div>
                <div class="b-buttons b-buttons_padtop_20">
                    <a href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green sms_valid_send"><?= !$post_safety_phone ? "Отвязать" : "Сохранить изменения"?></a><span class="b-buttons__txt">&nbsp;&nbsp;&nbsp;или&nbsp;&nbsp;&nbsp;</span><a href="javascript:void(0)" class="b-buttons__link b-buttons__link_dot_0f71c8  sms_cancel_change">отменить</a>
                </div>
            </div>
            
            
            
            <?php } else {//if?>
                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block <?= $error_phone['phone'] ? "b-layout__txt_hide" : ""?>" id="phone_bind">Введите номер телефона без пробелов и дефиса</div>
                <? if($error_phone['phone']) { ?>
                <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_fontsize_11 b-layout__txt_inline-block" id="phone_error"><?= $error_phone['phone']?></div>
                <? } ?>
                <div class="b-layout__txt b-layout__txt_padtop_20 sms_form <?= $reqv['is_activate_mob'] == 't' ? "b-layout__txt_hide": ""?>">
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        <div class="b-layout__txt b-layout__txt_inline-block">Введите код</div>
                        <div class="b-combo b-combo_inline-block b-combo_valign_mid">
                            <div class="b-combo__input b-combo__input_width_45 <?= $error_phone['code'] ? "b-combo__input_error" : ""?>">
                                <input class="b-combo__input-text " type="text" id="smscode" name="smscode">              
                            </div>
                        </div>
                        <div class="b-layout__txt b-layout__txt_inline-block"><a class="b-layout__link b-layout__link_fontsize_11 <?=$smsLinkStyle ?>" href="javascript:void(0)" data-field="mob_phone" data-code="smscode" data-form="main_phone_form" id="getsms"><?=$linkText ?></a></div>
                    </div>
                    <a href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green sms_valid_send">Активировать</a>
                </div>
            <?php }//else?>
            
        </span>
    </form>

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
