<!--{include file='control/head.tpl'}--> 
		<div class="header">
			<!--{include file='control/header_inner.tpl' close_class="window-close-simple"}-->
			<h1><a href="<!--{$url}-->" onclick="window.open('<!--{$url}-->');return false;"><img src="<!--{$logo}-->" alt="<!--{$company}-->" /></a></h1>
			<h2><!--{get_res code="consult.title"}--> / <!--{get_res code="consult.title5"}--></h2>
		</div>
		<div class="content">
			<form id="thread-mail-form" action="mail.php?theme=<!--{$theme}-->" method="post">
				<div class="webim-form">
					<b class="b1"></b>
					<b class="b2"></b>
					<div class="form-in c">
						<p><!--{get_res code="send.history"}--></p>
						<p id="error-email" class="<!--{if isset($erroremail)}-->error<!--{else}-->error-hidden<!--{/if}-->"><strong><!--{get_res code="enter.email"}--></strong></p>
						<p id="error-email-format" class="<!--{if isset($erroremailformat)}-->error<!--{else}-->error-hidden<!--{/if}-->"><strong><!--{get_res code="wrong.email.format"}--></strong></p>
						<div class="form-el c">
							<label class="form-label"><!--{get_res code="email.address"}--></label>
							<span class="form-ctrl">
								<input id="email" type="text" name="email" value="<!--{$email}-->" tabindex="1"/>
							</span>
						</div>
						<input id="threadid" type="hidden" name="threadid" value="<!--{$threadid}-->"/>
						<input id="token" type="hidden" name="token" value="<!--{$token}-->"/>
						<input id="level" type="hidden" name="level" value="<!--{$level}-->"/>
					</div> 
					<b class="b1"></b>
					<b class="b2"></b>
				</div>
				<div class="webim-btn">
					<input type="image" tabindex="2" src="/webim/themes/<!--{$theme}-->/img/btn-send.png" value="<!--{get_res code="send"}-->" />
				</div>
			</form>
		</div>
<!--{include file='control/foo.tpl'}-->