<!--{include file='control/header_admin.tpl'}-->
<div class="menu">
	<!--{include file='control/top_block.tpl'}-->
	<!--{get_res code="page_settings.intro"}-->
</div>
<!--{include file='control/errors_block.tpl'}-->
<div class="info-box">
	<form name="settings" class="settings" enctype="multipart/form-data" method="post" action="<!--{$webim_root}-->/operator/settings.php">
		<fieldset>
			<h3><!--{get_res code="settings.company"}--></h3>
			<ul>
				<!--{include file='control/input.tpl'
					type=text
					name=company_name
					value=$company_name
					res=settings.company.name
					res_descr=settings.company.name.description
					mandatory=true}-->
				<!--{if $logo}-->
					<li>
						<label><!--{get_res code="form.field.logo.current"}--></label>
						<div class="area">
							<img src="<!--{$logo}-->"/>
							<a class="formauth" href='<!--{$webim_root}-->/operator/settings.php?dellogo=1'><!--{get_res code="page_agent.clear_logo"}--></a>
							<input type="hidden" name="logo" value="<!--{$logo}-->" />
							<div class="description">
								&mdash; <!--{get_res code="form.field.logo.current.description"}-->
							</div>
						</div>
					</li>
				<!--{/if}-->

				<li>
					<label><!--{get_res code="settings.logo"}--></label>
					<div class="area">
						<input type="file" class="avatarfile" name="logo" size="25" value="<!--{$avatarFile}-->" />
						<div class="description">
							&mdash; <!--{get_res code="settings.logo.description"}-->
						</div>
					</div>
				</li>

				<!--{include file='control/input.tpl'
					type=text
					name=hosturl
					value=$hosturl
					res=settings.host
					res_descr=settings.host.description}-->

			</ul>
		</fieldset>
		<fieldset>
			<h3><!--{get_res code="settings.emails"}--></h3>
			<ul>
				<!--{include file='control/input.tpl'
					type=text
					name=offline_email
					value=$offline_email
					res=settings.offline_email
					res_descr=settings.offline_email.description
					mandatory=true}-->

				<!--{include file='control/input.tpl'
					type=text
					name=superviser_email
					value=$superviser_email
					res=settings.superviser_email
					res_descr=settings.superviser_email.description
					mandatory=true}-->

				<!--{include file='control/input.tpl'
					type=text
					name=from_email
					value=$from_email
					res=settings.from_email
					res_descr=settings.from_email.description
					mandatory=true}-->
				<!--{include file='control/input.tpl'
					type=text
					name=stats_email
					value=$stats_email
					res=settings.stats_email
					res_descr=settings.stats_email.description
					mandatory=true}-->
			</ul>
		</fieldset> 
			<fieldset>
      <h3><!--{get_res code="settings.misc"}--></h3>
				<ul>
					<!--{include file='control/input.tpl'
						type=textarea
						name=$answers_key
						value=$answers_value
						res=settings.predefined.answers
						res_descr=settings.predefined.answers.description}-->
				</ul>
			</fieldset>  
			<fieldset>
      <h3><!--{get_res code="settings.sessions"}--></h3>
				<ul>
					<!--{include file='control/input.tpl'
						type=text
						name=max_sessions
						value=$max_sessions
						res=settings.predefined.max_sessions
						res_descr=settings.predefined.max_sessions.description}-->
				</ul>
			</fieldset> 
    <!--{include file='control/save.tpl'}-->
		<div><!--{include file='control/asterisk_explain.tpl'}--></div>
	</form>
</div>
<!--{include file='control/footer.tpl'}-->