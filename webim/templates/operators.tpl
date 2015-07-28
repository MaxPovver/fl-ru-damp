<!--{include file='control/header_admin.tpl' title_key="leftMenu.client_agents"}-->
<div class="menu">
	<!--{include file='control/top_block.tpl' title_key="leftMenu.client_agents"}-->
	<!--{get_res code="page_agents.intro"}-->
</div>
<div class="visitors-table">
	<table class="pending-visitors operators">
		<tr>
			<th><!--{get_res code="page_agents.login"}--></th>
			<th><!--{get_res code="page_agents.agent_name"}--></th>
			<th class="last"><!--{get_res code="page_agents.action"}--></th>
		</tr>
		<!--{foreach from=$operators item=agent}-->
		<tr>
			<td><a href='<!--{$webim_root}-->/operator/operator.php?operatorid=<!--{$agent.operatorid}-->'><!--{$agent.login}--></a> <!--{if $agent.isonline}-->online<!--{/if}--></td>
			<td><!--{$agent.fullname}--></td>
			<td class="action">
			  <form action="<!--{$webim_root}-->/operator/operator.php" method="POST">
				<input type="hidden" id="operatorid" name="operatorid" value="<!--{$agent.operatorid}-->"/>
				<input type="hidden" id="act" name="act" value="delete"/>
				<input type="hidden" name="submitted" value="1" />
				<input type="submit" class="btn-save" value="<!--{get_res code="page_agents.action.delete"}-->" onclick="return confirm('<!--{get_res code="page_agents.confirm.delete"}-->')" />
			  </form>
			</td>
		</tr>
		<!--{/foreach}-->
	</table>
  <!--{if $operators|@count < 50}-->
	<form action="<!--{$webim_root}-->/operator/operator.php">
	  <input type="submit" class="btn-save" value="<!--{get_res code="page_agents.new_agent"}-->" />
	</form>
  <!--{else}-->
    <!--{get_res code="page_agents.limit_exceed" 0="50"}-->  
  <!--{/if}-->
</div>
<!--{include file='control/footer.tpl'}-->