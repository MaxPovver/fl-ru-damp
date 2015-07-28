<!--{include file='control/header_admin.tpl' title_key="leftMenu.departments"}-->
<div class="menu">
  <!--{include file='control/top_block.tpl' title_key="leftMenu.departments"}-->
  <!--{get_res code="page_departments.intro"}-->
</div>
<div class="visitors-table">
  <!--{if !empty($departments)}-->
  <table class="pending-visitors operators">
    <tr>
      <th><!--{get_res code="form.field.departmentname"}--></th>
      <th class="last"><!--{get_res code="page_agents.action"}--></th>
    </tr>
      <!--{foreach from=$departments item=d}-->
      <tr>
        <td><a href='<!--{get_admin_url link_name="department" is_with_param_postfix="true"}-->id=<!--{$d.departmentid}-->'><!--{if !empty($d.departmentname)}--><!--{$d.departmentname}--><!--{else}--><!--{get_res code="page_depaprtments.empty"}--><!--{/if}--></a></td>
        <td class="action">
          <form action="<!--{get_admin_url link_name="department"}-->" method="POST">
            <input type="hidden" id="id" name="id" value="<!--{$d.departmentid}-->"/>
            <input type="hidden" id="act" name="act" value="delete"/>
            <input type="hidden" name="submitted" value="1" />
            <input type="submit" class="btn-save" value="<!--{get_res code="page_agents.action.delete"}-->" onclick="return confirm('<!--{get_res code="page_depaprtments.confirm.delete"}-->')" />
          </form>
        </td>
      </tr>
      <!--{/foreach}-->
  </table>
  <!--{/if}-->
  <form action="<!--{get_admin_url link_name="department"}-->">
    <input type="submit" class="btn-save" value="<!--{get_res code="page_departments.new_department"}-->" />
  </form>
</div>
<!--{include file='control/footer.tpl'}-->