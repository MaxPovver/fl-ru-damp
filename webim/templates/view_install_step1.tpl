<!--{include file='control/header_admin.tpl' title_key='install.title'}-->
<div class="menu">
	<h1><!--{get_res code="install.title"}--></h1>
	<!--{get_res code="install.message"}-->
</div>
<div class="info-box">
	<div class="install">
		<!--{get_res code="install.next"}-->
		<ul>
			<li>
				<!--{if $page.nextnotice}--><!--{$page.nextnotice}--><!--{/if}-->
			</li>
		</ul>
		<form action="<!--{$webim_root}-->/install/">
			<input type="hidden" name="act" value="step2"/>
			<input type="submit" class="btn-save" value="<!--{$page.nextstep}-->" />
		</form>
	</div>
</div>
<!--{include file='control/footer.tpl'}-->