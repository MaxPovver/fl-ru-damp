<!--{include file='control/header_admin.tpl'}-->
<div class="page">
	<h2 class="logotype"><a href="#">Веб Мессенджер</a></h2>
	<div class="loin-box">
		<h1><!--{get_res code="page_login.title"}--></h1>
		<!--{if $errors}-->
			<!--{if $errors}-->
			<!--{get_res code="errors.header"}-->
				<!--{foreach from=$errors item=error}-->
					<!--{get_res code="errors.prefix"}--><!--{$error}--><!--{get_res code="errors.suffix"}-->
				<!--{/foreach}-->
			<!--{get_res code="errors.footer"}-->
			<!--{/if}-->
		<!--{/if}-->
		<form class="login" name="loginForm" method="post" action="<!--{$webim_root}-->/operator/login.php">
			<fieldset>
				<dl>
					<dt><label for="login-name"><!--{get_res code="page_login.login"}--></label></dt>
					<dd><input type="text" name="login" id="login-name"	size="20" value="<!--{$smarty.post.login|htmlspecialchars}-->" class="formauth"/></dd>
					<dt><label for="password"><!--{get_res code="page_login.password"}--></label></dt>
					<dd><input type="password" name="password" id="password" size="20" value="" class="formauth"/></dd>
				</dl>
				<p class="remember"><a href="<!--{$webim_root}-->/operator/recover_password.php?act=send"><!--{get_res code="page_login.restore_password"}--></a></p>
				<p class="remember">
					<input type="checkbox" name="isRemember" id="isRemember" value="on"<!--{if $isRemember}--> checked="checked"<!--{/if}--> />
					<label for="isRemember"><!--{get_res code="page_login.remember"}--></label>
				</p>
				<div class="submit">
					<input type="hidden" name="redir" value="<!--{$redir}-->"/> <input type="submit" class="btn-login" id="login" value="<!--{get_res code="button.enter"}-->" />
				</div>
			</fieldset>
		</form>
	</div>
</div>
<!--{include file='control/footer.tpl'}-->
