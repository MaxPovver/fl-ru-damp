<?php
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}?>
<div class="b-header">
		<div class="b-header__tel">
			<div class="b-header__tel-ic <?= (NY2012TIME?"b-header__tel-ic_red":"b-header__tel-ic_green")?>"></div>
			<big class="b-header__tel-number">8-800-555-33-14</big>
			<div class="b-header__txt"><?= (NY2012TIME?"31.12—09.01 — каникулы":"Служба поддержки")?></div> 
		</div>
		<!--
		<div class="b-header__tel">
			<div class="b-header__tel-ic b-header__tel-ic_green"></div>
			<big class="b-header__tel-number">8-800-555-33-14</big>
			<div class="b-header__txt">бесплатно для России</div> 
		</div>
		-->
    <?php if (get_uid(false) <= 0) { ?>
        <? seo_start(); ?>
					<div class="b-header__links">
            <?php if(isJSPromlebBrowser()) {?>
            <a href="/login/" class="b-header__link b-header__link_bordbot_dot_333 b-header__link_margright_16">Вход</a><span id="floginToggle"></span>	 
            <?php } else {?>	 
            <a  id="floginToggle" class="b-header__link b-header__link_bordbot_dot_333 b-header__link_margright_16" href="javascript:void(0)">Вход</a>	 
            <?php }?>	 
            <?php $sHideA = preg_match('~/registration/~i', $_SERVER['REQUEST_URI']); ?>	 
            <? if ( !$sHideA ) { ?><a class="b-header__link b-header__link_color_ff4800 b-header__link_bold" href="/registration/">Регистрация</a><? } ?>	 
            <? if ( $sHideA ) { ?><span class="b-header__txt b-header__txt_fontsize_12 b-header__txt_color_ff4800 b-header__txt_bold b-header__float_left">Регистрация</span><? } ?>	 
        </div>
        <?= seo_end(); ?>
		
		
		
		
		
		
<div id="b-login" class="b-login b-login_toggle">
	<div class="b-login__top">
		<div class="b-login__bot">
			<ul class="b-login__list">
				<li class="b-login__item"><a class="b-login__entry" onclick="return false" href="#">Вход</a></li>
				<?php $sHideA = preg_match('~/registration/~i', $_SERVER['REQUEST_URI']); ?>	 
				<? if ( !$sHideA )  print '<li class="b-login__item"><a class="b-login__reg" href="/registration/">Регистрация</a></li>'; ?>
				<? if ( $sHideA )  print '<li class="b-login__item"><span class="b-login__reg">Регистрация</span></li>'; ?>
			</ul>
			<form id="lfrm" class="b-login__body" method="post" action="/">
				<div class="b-form">
                	<input type="hidden" name="action" value="login" />
                    <? if ( !empty($_GET['subdomain']) ) { ?><input type="hidden" name="subdomain" value="<?=htmlspecialchars($_GET['subdomain'])?>" /><? } ?>
					<label class="b-form__name" for="b-login__text">Логин</label>
					<div class="b-input b-input_inline-block b-input_width_195">
						<input id="b-login__text" class="b-input__text" name="login" type="text" />
					</div>
				</div>
				<div class="b-form">
					<label class="b-form__name" for="b-login__password">Пароль</label>
					<div class="b-input b-input_inline-block b-input_width_195">
						<input id="b-login__password" class="b-input__text" name="passwd" type="password" />
					</div>
				</div>
				<div class="b-form b-form_padbot_5">
					<div class="b-check">
						<input id="b-check2" class="b-check__input" type="checkbox" name="autologin" value="1" />
						<label for="b-check2" class="b-check__label b-check__label_fontsize_13">Запомнить меня</label>
					</div>
				</div>
				<div class="b-form b-form_padbot_null">
					<div class="b-buttons">
						<a class="b-button b-button_rectangle_transparent" onclick="document.getElementById('lfrm').submit(); return false;" href="#">
							<span class="b-button__b1">
								<span class="b-button__b2 b-button__b2_padlr_5">
									<span class="b-button__txt">Войти</span>
								</span>
							</span>
						</a>
						<?php if ( strpos( $_SERVER['REQUEST_URI'], 'remind' ) === false ){ ?>
						<a class="b-buttons__link  b-buttons__link_margleft_5" href="/remind/">Напомнить пароль</a>
						<?php } else { ?>
						<span class="b-buttons__link">Напомнить пароль</span>
						<?php } ?>
					</div>
                	<input class="b-login_submit" type="submit" value="" />
				</div>
			</form>
		</div>
	</div>
</div>
    <? } ?>
		
		<a class="b-header__link b-header__link_logo" href="/"><img class="b-header__logo" src="/images/logo<?= $GLOBALS['logoAddition'] ?>.png" alt="Free-lance.ru" /></a>
		
		
    
</div>

<?php
$gifts = array();
if ($_SESSION['uid'] && !$no_personal) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/present.php");
    $gifts = present::GetLastGiftByUid($_SESSION['uid']);
}
?>

<? foreach ($gifts as $i => $last_gift) { ?>






<div id="last_gift<?= $last_gift['id'] ?>" class="b-last-gift b-fon b-fon_bg_ffeda9" <?php if($i>0) echo 'style="display:none;"'; ?>>
	<b class="b-fon__b1"></b>
	<b class="b-fon__b2"></b>
	<div class="b-fon__body">
		<a class="b-last-gift__link" href="/present/?id=<?= $last_gift['id'] ?>">
			<?= ($last_gift['op_code'] == 23) ? "Перевод" : "Подарок" ?><span class="b-last-gift__<?= $last_gift['op_code'] == 23 ? 'fm': 'gift' ?>"></span>
		</a>
		<a class="b-last-gift__close" onclick="SetGiftResv(<?= $last_gift['id'] ?>)" href="javascript://"></a>
		<a class="b-last-gift__name b-last-gift__name_color_<?= is_emp($last_gift['role']) ? '6BB24B' : '666' ?>" href="/users/<?= $last_gift['login'] ?>"><?= $last_gift['uname'] ?> <?= $last_gift['usurname'] ?> [<?= $last_gift['login'] ?>]</a> 
		<? if($last_gift['op_code'] == 23) { ?>
		 перевел<?= $last_gift['sex'] == 'f' ? 'а' : '' ?> на ваш счет <a class="b-last-gift__present" href="/present/?id=<?= $last_gift['id'] ?>">денежные средства</a>.
		<? } elseif ($last_gift['op_code'] == 52 || $last_gift['op_code'] == 16) { ?>
		<a class="b-last-gift__present" href="/present/?id=<?= $last_gift['id'] ?>">подарил<?= $last_gift['sex'] == 'f' ? 'а' : '' ?> вам</a> 
		<a class="b-last-gift__linkpro" href="/payed<?= is_emp() ? '-emp' : '' ?>/"><img class="b-last-gift__pro" src="/images/icons/<?= is_emp() ? 'e' : 'f' ?>-pro.png" alt="PRO" /></a> аккаунт.
		<? } else { ?>
		 сделал<?= $last_gift['sex'] == 'f' ? 'а' : '' ?> вам <a href="/present/?id=<?= $last_gift['id'] ?>"  class="b-last-gift__present">подарок</a>
		<? } ?>
	</div>
	<b class="b-fon__b2"></b>
	<b class="b-fon__b1"></b>
</div>




<? } ?>
