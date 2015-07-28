<!--{include file='control/header_admin.tpl' title_key='install.title'}-->
<div class="menu">
	<h1><!--{get_res code="install.title"}--></h1>
	<!--{$page.installmessage}-->
</div>
<div class="info-box">
	<div class="install">
		<ul>
			<li>
				<!--{if $page.nextnotice}-->
					<!--{$page.nextnotice}-->
				<!--{/if}-->
			</li>
		</ul>
		<a href="<!--{$page.nextstepurl}-->"><!--{$page.nextstep}--></a>
	</div>
</div>
<!--{include file='control/footer.tpl'}-->