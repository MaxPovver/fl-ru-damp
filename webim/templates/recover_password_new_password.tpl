<!--{include file='control/header_admin.tpl' title_key='page_recover_password.title'}-->
<div class="page">
	<h2 class="logotype"><a href="http://webim.ru">Веб Мессенджер</a></h2>
	<div class="loin-box">
		<h1><!--{get_res code="page_recover_password.title"}--></h1>
		<!--{include file='control/errors_block.tpl'}-->
		<form name="agentForm" method="post">
			<fieldset>
				<ul class="recovery">
<!--{include file='control/input.tpl'
    type=password
    name=password
    value=$password
    res=form.field.new_password
    res_descr=form.field.new_password.description
    mandatory=true}-->
<!--{include file='control/input.tpl'
    type=password
    name=password_confirm
    value=$password_confirm
    res=form.field.password_confirm
    res_descr=form.field.password_confirm.description
    mandatory=true}-->
				</ul>
				<div class="submit">
					<input type="hidden" name="submitted" value="1" />
					<input type="submit" class="btn-login" value="<!--{get_res code="button.save"}-->" />
				</div>
				<!--{include file='control/asterisk_explain.tpl'}-->
			</fieldset>
		</form>
		<!--{include file='control/footer.tpl'}-->
	</div>
</div>
<!--{include file='control/footer.tpl'}-->
