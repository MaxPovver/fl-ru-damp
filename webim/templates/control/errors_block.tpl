<!--{if $errors}-->
	<!--{if $errors}-->
	<!--{get_res code="errors.header"}-->
		<!--{foreach from=$errors item=error}-->
			<!--{get_res code="errors.prefix"}--><!--{$error}--><!--{get_res code="errors.suffix"}-->
		<!--{/foreach}-->
	<!--{get_res code="errors.footer"}-->
	<!--{/if}-->
<!--{/if}-->
