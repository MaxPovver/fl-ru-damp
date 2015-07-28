<!--{include file='control/head.tpl'}--> 
		<div class="header">
			<!--{include file='control/header_inner.tpl' close_class="window-close-simple"}-->
			<h1><a href="<!--{$url}-->" onclick="window.open('<!--{$url}-->');return false;"><img src="<!--{$logo}-->" alt="<!--{$company}-->" /></a></h1>
			<h2><!--{get_res code="consult.title"}--> / <!--{get_res code="consult.title4"}--></h2>
		</div>
		<div class="content">
			<form id="message-sent-form">
				<div class="webim-form">
					<b class="b1"></b>
					<b class="b2"></b>
					<div class="form-in c">
						<p><!--{get_res code="thanks.for.question"}--></p>
					</div>
					<b class="b1"></b>
					<b class="b2"></b>
				</div>
			</form>
		</div>
<!--{include file='control/foo.tpl'}-->