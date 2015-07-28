<!--{include file='control/header_admin.tpl'}-->
<div class="menu">
	<!--{include file='control/top_block.tpl'}-->
	<!--{get_res code="page_auto_invites.intro"}-->
</div>
<div class="visitors-table">
	<!--{if $invites}-->
	<table class="pending-visitors operators">
		<tr>
			<th><!--{get_res code="page_auto_invites.text"}--></th>
			<th class="last"><!--{get_res code="page_auto_invites.action"}--></th>
		</tr>
		
		<!--{foreach from=$invites item=invite}-->
		<tr>
			<td><a href='<!--{get_admin_url link_name="auto_invite" is_with_param_postfix="true"}-->  autoinviteid=<!--{$invite.autoinviteid}-->'><!--{$invite.text}--></a></td>
			<td class="action">
			  <form action="<!--{get_admin_url link_name="auto_invite"}-->">
				<input type="hidden" id="operatorid" name="autoinviteid" value="<!--{$invite.autoinviteid}-->"/>
				<input type="hidden" id="act" name="act" value="delete"/>
				<input type="hidden" name="submitted" value="1" />
				<input type="submit" class="btn-save" value="<!--{get_res code="page_auto_invites.action.delete"}-->" onClick="if (!confirm('<!--{get_res code="page_auto_invites.confirm.delete"}-->')) return false;" />
			  </form>
			</td>
		</tr>
		<!--{/foreach}-->
	</table>
	<!--{/if}-->
	<form action="<!--{get_admin_url link_name="auto_invite"}-->">
	  <input type="submit" class="btn-save" value="<!--{get_res code="page_auto_invites.new_rule"}-->" />
	</form>
</div>
<!--{include file='control/footer.tpl'}-->