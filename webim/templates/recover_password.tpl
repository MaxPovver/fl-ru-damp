<!--{include file='control/header_admin.tpl' title_key='page_recover_password.title'}-->
<div class="page">
	<h2 class="logotype"><a href="http://webim.ru">Веб Мессенджер</a></h2>
	<div class="loin-box">
		<h1><!--{get_res code="page_recover_password.title"}--></h1>
		<!--{include file='control/errors_block.tpl'}-->
		<form class="login" name="agentForm" method="post">
			<fieldset>
				<ul class="recovery">
<!--{include file='control/input.tpl'
    type=text
    name=login
    value=$login
    res=form.field.login
    mandatory=true}-->
				</ul>
				<div class="submit">
					<input type="hidden" name="submitted" value="1" />
					<input type="submit" class="btn-login" value="<!--{get_res code="button.send"}-->" />
					
				</div>
				<!--{include file='control/asterisk_explain.tpl'}-->
			</fieldset>
		</form>
	</div>
</div>
<!--{include file='control/footer.tpl'}-->

