<?
if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/sbr.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/users.common.php");
$xajax->printJavascript('/xajax/');

require_once($_SERVER['DOCUMENT_ROOT'].'/classes/sms_services.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');

$u = new users;
$o_only_phone = $u->GetField($uid,$ee,'safety_only_phone');
$bind_ip_current = $bind_ip;
if($_POST['action']!='safety_update') {
    $phone = $u->GetField($uid,$ee,'safety_phone');
    $only_phone = $u->GetField($uid,$ee,'safety_only_phone');
    $bind_ip_current = $bind_ip = $u->GetField($uid,$ee,'safety_bind_ip');
    $array_ip_addresses = $u->GetSafetyIP($uid);
    while(list($k,$v)=each($array_ip_addresses)) {
        $ip_addresses .= $v."\r\n";
    }
} else if ( $error_flag ) {
    $bind_ip_current = $u->GetField($uid,$ee,'safety_bind_ip');
}
$reqv = sbr_meta::getUserReqvs($uid);
$ureqv = $reqv[$reqv['form_type']];
if($_SESSION['alert']) {
    $alert = $_SESSION['alert'];
    unset($_SESSION['alert']);
}
if($_SESSION['info_msg']) {
    $info_msg = $_SESSION['info_msg'];
    unset($_SESSION['info_msg']);
}
?>

<div class="b-layout b-layout_padtop_20">
	<h2 class="b-layout__title b-layout__title_padbot_30">Безопасность аккаунта</h2>
	<? if ($info_msg) print(view_info($info_msg)."<br />") ?>
    
    <h3 class="b-layout__h3">Двухэтапная аутентификация через соцсети</h3>
    <?php if ($social_multivel && is_array($social_multivel)): ?>
        <div class="b-layout__txt b-layout__txt_padbot_40">
            Включена двухэтапная аутентификация с помощью <?=$social_multivel['name']?>. 
            <a id="multilevel_switchoff" href="javascript:void(0);">Отключить</a>.
        </div>
    <?php else: ?>
        <div class="b-layout__txt b-layout__txt_padbot_15">
            Настройте двухэтапную аутентификацию, и после ввода логина и пароля вам 
            также нужно будет авторизоваться через выбранную соцсеть на втором шаге. 
            <a href="http://feedback.fl.ru/topic/683170-dvuhetapnaya-autentifikatsiya-cherez-sotsseti/" target="_blank">Подробнее</a>
        </div>
        <?php if (isset($social_links) && !empty($social_links)): ?>
            <form action="." method="post" class="b-form b-form_padbot_40">
                <input type="hidden" name="action" value="safety_social" />
                <table class="b-layout__table">
                    <tr class="b-layout__tr">
                        <td class="b-layout__td b-layout__td_width_140">
                            <div class="b-layout__txt b-layout__txt_padtop_5">Выберите соцсеть:</div>
                        </td>

                        <td class="b-layout__td b-layout__td_padbot_20">
                            <?php foreach (array(1 => 'facebook', 2 => 'vk', 3 => 'odnoklassniki') as $key => $code): ?>
                                <?php if (isset($social_links[$key])): ?>
                                    <div class="b-radio b-radio_inline-block b-radio__item_padright_20">
                                        <input class="b-radio__input b-radio__input_top_5" 
                                               type="radio" 
                                               id="provider_<?=$key?>" 
                                               name="type" 
                                               value="<?=$key?>"
                                               <?=isset($provider_type) && $provider_type == $key ? ' checked="checked"' : ''?> />
                                        <label class="b-radio__label" for="provider_<?=$key?>">
                                            <span class="b-auth_btn b-auth_mini b-auth_btn_<?=$code?> g-float_left g-margtop_0"></span>
                                        </label>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if ($alert[4]): ?>
                                <?=view_error($alert[4])?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr class="b-layout__tr">
                        <td class="b-layout__td b-layout__td_width_140">
                            <div class="b-layout__txt b-layout__txt_padtop_5">Введите пароль:</div>
                        </td>
                        <td class="b-layout__td b-layout__td_padbot_20">
                            <div class="b-combo">
                                <div class="b-combo__input b-combo__input_width_300">
                                    <input class="b-combo__input-text"  type="password" name="oldpwd" />
                                    <?php if ($alert[5]): ?>
                                        <?=view_error($alert[5])?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
                <button class="b-button b-button_flat b-button_flat_green" type="submit">Включить двухэтапную аутентификацию</button>
            </form>
        <?php else: ?>
            <div class="b-layout__txt b-layout__txt_padbot_15">Привяжите ваш аккаунт из соцсети, который вы будете использовать для двухэтапной аутентификации.</div>
            <div class="b-layout b-layout_padbot_30">
                <?php if (isset($social_bind_error) && !empty($social_bind_error)): ?>
                    <div class="b-layout__txt b-layout__txt_color_c4271f">
                        <?=$social_bind_error?>
                    </div>
                <?php endif; ?>
                <div class="b-layout__txt">
                    <a href="/auth/?param=vkontakte&multilevel=1"
                       class="b-layout__link b-layout__link_lineheight_34 b-layout__link_valign_top">
                        <span class="b-auth_btn b-auth_mini b-auth_btn_vk b-auth_margright_5 b-auth_btn_float_left"></span>Привязать VKontakte-аккаунт к профилю
                    </a>
                </div>
                <div class="b-layout__txt">
                    <a href="/auth/?param=facebook&multilevel=1"
                       class="b-layout__link b-layout__link_lineheight_34 b-layout__link_valign_top">
                        <span class="b-auth_btn b-auth_mini b-auth_btn_facebook b-auth_margright_5 b-auth_btn_float_left"></span>Привязать Facebook-аккаунт к профилю
                    </a>
                </div>
                <div class="b-layout__txt">
                    <a href="/auth/?param=odnoklassniki&multilevel=1"
                       class="b-layout__link b-layout__link_lineheight_34 b-layout__link_valign_top">
                        <span class="b-auth_btn b-auth_mini b-auth_btn_odnoklassniki b-auth_margright_5 b-auth_btn_float_left"></span>Привязать Odnoklassniki-аккаунт к профилю
                    </a>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
	<a name="safety_ip"></a>
	<h3 class="b-layout__h3">Привязка аккаунта к IP-адресу</h3>
	<form action='.' method='POST' id="safetyform">
		<div class="b-form">
			<label for="ip_addresses" class="b-form__name b-form__name_fontsize_13 b-form__name_padbot_10">Укажите IP-адреса, для которых будет разрешен вход в аккаунт. Вход с других IP-адресов будет невозможен.</label>
			<div class="b-textarea">
				<textarea id="ip_addresses" class="b-textarea__textarea b-textarea__textarea_width_750" cols="20" rows="5" name="ip_addresses"><?=$ip_addresses?></textarea>
			</div>
			<input type="hidden" name="action" value="safety_update" />			
			<div class="b-form__txt b-form__txt_fontsize_11 b-form__txt_padtop_3 b-form__txt_block b-form__txt_width_full">IP-адреса следует указывать через запятую, при вводе диапазона адресов используйте дефис или слеш.<br/>К примеру,  10.10.10.1, 10.10.10.5-10.10.10.10 или 10.10.10.0/24</div>
		</div><?php if ($alert[1]) { ?>
				<?=view_error($alert[1]);?>
			<?php } ?>
		<div class="b-fon b-fon_width_full b-fon_padbot_30">
			<div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb b-fon__body_bordbot_edddda">
				Обратите внимание: не используйте привязку к IP, если у вас динамический IP-адрес (информацию можно получить у вашего провайдера).
			</div>
		</div>
		<h3 class="b-layout__h3">Привязка авторизации на сайте к IP-адресу <? if ($bind_ip_current=='t') { ?><span class="b-layout__txt b-layout__txt_color_6bb336 b-layout__txt_fontsize_15">включена</span><? } else { ?><span class="b-layout__txt b-layout__txt_color_c10600 b-layout__txt_fontsize_15">выключена</span><? } ?></h3>
		<div class="b-layout__txt b-layout__txt_padbot_15">Активировав эту функцию и установив флажок напротив &laquo;Запомнить меня&raquo;, вы будете авторизованы на сайте до тех пор, пока не изменится ваш IP-адрес. Функция полезна для тех, кто беспокоится о безопасности своего аккаунта: привязка авторизации к IP не позволит злоумышленнику авторизоваться в вашем аккаунте с другого IP-адреса, использовав данные, украденные из вашего браузера (файлы cookies).</div>
		<div class="b-check">
			<input id="bind_ip" class="b-check__input" type="checkbox" name="bind_ip" value="t" <?=($bind_ip=='t'?' checked="checked"':'')?> /> <label class="b-check__label b-check__label_fontsize_13" for="bind_ip">Привязка включена</label>
		</div>
		<div class="b-fon b-fon_width_full b-fon_padbot_30 b-fon_padtop_10">
			<div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb b-fon__body_bordbot_edddda">
				Обратите внимание: функция может причинить определенные неудобства обладателям динамического IP &mdash; в связи со сменой IP-адреса, которая происходит через некоторый промежуток времени, вам будет необходимо повторно вводить логин и пароль от аккаунта.
			</div>
		</div>
        <? /* #0019359
		<h3 class="b-layout__h3">Привязка к телефону</h3>
		<div class="b-layout__txt b-layout__txt_padbot_5">В случае кражи ваших личных данных вы сможете восстановить доступ к аккаунту посредством мобильного телефона &mdash; пароль придет в виде SMS-сообщения на указанный вами номер телефона. <br /><br />Ваш номер:</div>
		<div class="b-form b-form_width_490">
			<div class="b-input b-input_width_160 b-input_inline-block">
				<input id="phone" class="b-input__text" type="text" autocomplete="off"  name="phone" value="<?=$phone?>" maxlength="30" <?=(($o_only_phone=='t')?'disabled="disabled"':'')?> onKeyPress="return submitEnter(this,event)"  /> 
			</div>
			<div class="b-check b-check_inline-block b-check_padleft_10 b-check_padtop_3">
				<input id="only_phone" class="b-check__input" type="checkbox" name="only_phone" value="t" <?=($only_phone=='t'?' checked="checked"':'')?> <?=(($o_only_phone=='t')?'disabled="disabled"':'')?> /> <label class="b-check__label b-check__label_fontsize_13" for="only_phone">Восстанавливать пароль только на телефон</label>
			</div>
			<div class="b-form__txt b-form__txt_fontsize_11 b-form__txt_padtop_3 b-form__txt_width_full">Например, +79266543210</div>
			
		</div>
		<?php if ($alert[2]) { ?>
				<?=view_error($alert[2])?>
			<?php } ?>
		<div class="b-fon b-fon_width_full b-fon_padbot_10 b-fon_padtop_10">
			<div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb b-fon__body_bordbot_edddda">
				Обратите внимание: если вы выберете опцию восстановления пароля только на телефон, единственным способом изменения указанного номера телефона будет обращение в <a class="b-layout__link" href="/help/?all" target="_blank">Службу поддержки</a>.
			</div>
		</div>
		<h4 class="b-layout__h4"><a class="b-layout__link b-layout__toggler b-layout__link_bordbot_dot_0f71c8" href="#">Способ восстановления пароля через SMS и стоимость услуги</a></h4>
		<div class="b-layout__slider">
			<div class="b-layout__txt b-layout__txt_padbot_10">Для восстановления пароля вам необходимо отправить SMS с текстом <span class="b-layout__txt b-layout__txt_color_6bb336">free 2+<?=htmlspecialchars($_SESSION['login'])?></span> на номер <span class="b-layout__txt b-layout__txt_color_6bb336">4446</span>.<br/>Подробная инструкция по восстановлению пароля через SMS-сообщение находится в соответствующем <a class="b-layout__link" href="/help/?q=882">разделе помощи</a>.</div>
			<div class="b-layout__txt b-layout__txt_padbot_10">Услуга доступна для жителей России, Украины и Белоруссии.</div> 
    		<div class="b-layout__txt b-layout__txt_padbot_10"><?=sms_services::$tariffs['4446']['descr']?></div>
		</div>
        
        <h3 class="b-layout__h3 b-layout__h3_padtop_30">Привязка мобильного телефона <span class="b-layout__txt <?= ( $reqv['is_activate_mob'] == 't' ? 'b-layout__txt_color_6bb336' : 'b-layout__txt_color_c10600' );?> b-layout__txt_fontsize_15" id="safety_status"><?= ( $reqv['is_activate_mob'] == 't' ? 'включена' : 'выключена' );?></span></h3>
        <div class="b-combo b-combo_inline-block b-combo_valign_mid">
            <div class="b-combo__input b-combo__input_width_170 <?= ( ($reqv['is_activate_mob'] == 't' || $_SESSION['is_verify'] == 't') ? 'b-combo__input_disabled' : '');?>" id="safety_mob_phone">
                <input class="b-combo__input-text b-combo__input-text_fontsize_18" <?= ( $reqv['is_activate_mob'] == 't' ? 'disabled' : '');?> name="mob_phone" type="text" size="12" maxlength="15" value="<?= $ureqv['mob_phone'];?>" onChange="savePhoneChage(this);" onBlur="savePhoneChage(this);"/>
            </div>
        </div>
        <span class="c_sms_main">
            <?php if($reqv['is_activate_mob'] == 't') { ?>
            &#160;&#160;
            <div class="b-layout__txt b-layout__txt_inline-block">
                <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)">Отвязать</a>
            </div>
            <script>bindLinkUnativateAuth('<?= $_SESSION['uid'];?>');</script>
            <?php } else {//if?>
            <a href="javascript:void(0)" class="b-button b-button_rectangle_color_transparent b-button_margtop_-2" data-send="safety" data-phone="<?= $ureqv['mob_phone'];?>">
                <span class="b-button__b1">
                    <span class="b-button__b2">
                        <span class="b-button__txt">Активировать</span>
                    </span>
                </span>
            </a>
            <?php }//else?>
        </span>
        <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_11">Например +79201234567</div>
        <div class="b-check b-check_padbot_10 safety_phone_checks">
            <input class="b-check__input" name="only_phone" value="t" type="checkbox" <?= ( $o_only_phone == 't' ? 'checked="checked"' : '' );?> <?= ( $reqv['is_activate_mob'] == 't' ? '' : 'disabled');?>/> <label class="b-check__label b-check__label_fontsize_13" >Восстанавливать пароль только на мобильный телефон</label>
        </div>
        <div class="b-check safety_phone_check safety_phone_checks">
            <input class="b-check__input" id="finance_safety_phone" name="finance_safety_phone" value="t" type="checkbox" <?= ( $reqv['is_safety_mob'] == 't' ? 'checked="checked"' : '' );?> <?= ( $reqv['is_activate_mob'] == 't' ? '' : 'disabled');?>/> <label class="b-check__label b-check__label_fontsize_13" >Вход на страницу &laquo;Финансы&raquo; &mdash; только через код из смс-сообщения</label>
        </div>*/ ?>
 
        <a name="safety_password"></a>
		<h3 class="b-layout__h3 b-layout__h3_padtop_30">Подтверждение изменений</h3>
		<div class="b-form b-form_padbot_40 b-form_width_full">
			<label class="b-form__name b-form__name_fontsize_13 b-form__name_padbot_10">Для сохранения внесенных изменений введите ваш текущий пароль:</label>
			<div class="b-input b-input_width_160">
				<input id="password" class="b-input__text b-input__text_width_160" type="password" name="password" onKeyPress="return submitEnter(this,event)" />
			</div>
			<?php if ($alert[3]) { ?>
                    <?=view_error($alert[3])?>
            <?php } ?>
		</div>
		<a class="b-button b-button_flat b-button_flat_green" onclick="safetyForm('f');" href="javascript:void(0)">Сохранить изменения</a>
</form>	
</div>
<div class="b-shadow b-shadow_zindex_11 b-shadow_center b-shadow_width_450" id="auth_popup" style="display:none"></div>
<?php
if($alert[1] || $alert[3]) { 
	?>
	<script type="text/javascript">
	<?php
	if($alert[1]) {
		?>
		window.location = '#safety_ip';
		<?php
	} elseif ($alert[3]) {
		?>
		window.location = '#safety_password';
		<?php
	}
	?>
	</script>
	<?php
} 
?>
