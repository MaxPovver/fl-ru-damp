<!--{include file='control/header_admin.tpl' body_class='page-menu'}-->
<div class="menu">
	<!--{include file='control/top_block.tpl' is_main="true"}-->
	<!--{get_res code="admin.content.description"}-->
</div>
<div class="info-box">
	<!--{foreach from=$items item=i key=k}-->
		<!--{if $i.name}-->
			<div class="box <!--{$i.cssclass}-->">
				<h2><a href='<!--{$i.link}-->'><!--{get_res code=$i.name}--></a></h2>
				<p><!--{get_res code=$i.description}--></p>
			</div>
		<!--{/if}-->
	<!--{/foreach}-->
</div>
<!--{include file='control/footer.tpl'}-->
