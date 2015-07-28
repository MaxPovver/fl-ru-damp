<!--{include file='control/header_admin.tpl' title_key="page_department.title"}-->

<div class="menu">
  <!--{include file='control/top_block.tpl' title_key="page_department.title" sub_menu_key='leftMenu.departments' sub_menu_link='/operator/departments.php'}-->
  <!--{if $mode == 'edit'}-->
    <!--{get_res code="page_departments.intro"}-->
  <!--{elseif $mode == 'new'}-->
    <!--{get_res code="page_departments.create_new"}-->
  <!--{/if}-->
</div>
<!--{include file='control/errors_block.tpl'}-->
<div class="info-box">
  <form name="agentForm" method="post" class="settings" action="">
    <fieldset>
      <ul>
        <!--{include file='control/input.tpl'
          type=text
          name=departmentname
          value=$departmentname
          res=form.field.departmentname
          res_descr=form.field.departmentname.description
          mandatory=true}-->

        <!--{include file='control/input.tpl'
          type=text
          name=departmentkey
          value=$departmentkey
          res=form.field.departmentkey
          res_descr=form.field.departmentkey.description
          mandatory=false}-->
      </ul>
    </fieldset>
    <!--{include file='control/save.tpl'}-->
    <!--{include file='control/asterisk_explain.tpl'}-->
  </form>
</div>
<!--{include file='control/footer.tpl'}-->