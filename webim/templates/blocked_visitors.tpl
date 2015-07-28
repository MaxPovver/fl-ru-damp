<!--{include file='control/header_admin.tpl' title_key="menu.blocked"}-->
<div class="menu">
   <!--{include file='control/top_block.tpl' title_key="menu.blocked"}-->
   <!--{get_res code="page_ban.intro"}-->
</div>
<!--{if $errors}-->
	<!--{$err_tmpl.errors_header}-->
	<!--{foreach from=$errors item=e}-->
		<!--{$err_tmpl.errors_prefix}-->
			<!--{$e}-->
		<!--{$err_tmpl.errors_suffix}-->
	<!--{/foreach}-->
	<!--{$err_tmpl.errors_footer}-->
<!--{/if}-->
<div class="visitors-table">
	<!--{if $page_settings.pagination && $page_settings.pagination_items}-->
		<table class="pending-visitors history-log">
			<tr>
				<th><!--{get_res code="form.field.address"}--></th>
				<th><!--{get_res code="page_bans.to"}--></th>
				<th><!--{get_res code="form.field.ban_comment"}--></th>
				<th class="last"><!--{get_res code="page_agents.action"}--></th>
			</tr>
			<!--{foreach from=$page_settings.pagination_items item=b}-->
				<tr>
					<td class="t1"> 
						<a href="ban.php?banid=<!--{$b.banid}-->"><!--{$b.address}--></a> 
					</td>
					<td class="t2"><!--{$b.till}--></td>
					<td class="t3"><!--{$b.comment|truncate:30}--></td>
					<td class="last">
            <form>
              <input type="hidden" id="id" name="id" value="<!--{$b.banid}-->"/>
              <input type="hidden" id="act" name="act" value="delete"/>
              <input type="hidden" name="submitted" value="1" />
              <input type="submit" class="btn-save" value="<!--{get_res code="page_agents.action.delete"}-->" onClick="if (!confirm('<!--{get_res code="page_ban.confirm.delete"}-->')) return false;" />
            </form>
					</td>
				</tr>
			<!--{/foreach}-->
		</table>
		<!--{$pagination}--><br/>
	<!--{/if}-->
	<!--{if $page_settings.pagination && !$page_settings.pagination_items}-->
		<!--{get_res code="tag.pagination.no_items.elements"}-->
	<!--{/if}--> 
	<form action="<!--{$webim_root}-->/operator/ban.php"> 
 
	  <input type="submit" class="btn-save" value="<!--{get_res code="page_bans.add"}-->" />
	</form> 
</div>
<!--{include file='control/footer.tpl'}-->
