<!--{include file='control/header_admin.tpl' title_key="page_agent.title"}-->

<div class="menu">
	<!--{if $mode == 'profile'}-->
		<!--{include file='control/top_block.tpl' title_key="page_agent.title"}-->
		<!--{get_res code="page_agent.profile"}-->
  <!--{elseif $mode == 'edit_operator'}-->
      <!--{include file='control/top_block.tpl' title_key="page_agent.title" sub_menu_key='leftMenu.client_agents' sub_menu_link='/operator/operators.php'}-->
		<!--{get_res code="page_agent.intro"}-->
  <!--{elseif $mode == 'new_operator'}-->
		<!--{include file='control/top_block.tpl' title_key="page_agent.title" sub_menu_key='leftMenu.client_agents' sub_menu_link='/operator/operators.php'}-->
		<!--{get_res code="page_agent.create_new"}-->
	<!--{/if}-->
</div>
<!--{include file='control/errors_block.tpl'}-->
<div class="info-box">
	<form name="agentForm" method="post"  enctype="multipart/form-data" class="settings" action="">
		<fieldset>
			<ul>
				<!--{include file='control/input.tpl'
					type=text
					name=login
					value=$login
					res=form.field.login
					res_descr=form.field.login.description
					mandatory=true}-->

        <!--{if $mode == 'profile' or $mode == 'edit_operator'}-->					
				  <!--{include file='control/input.tpl'
					  type=password
					  name=password
					  res=form.field.new_password
					  res_descr=form.field.new_password_or_leave.description
            autocomplete=false}-->
          <!--{include file='control/input.tpl'
              type=password
              name=password_confirm
              res=form.field.password_confirm
              res_descr=form.field.password_confirm.description
              autocomplete=false}-->
        <!--{else}-->
            <!--{include file='control/input.tpl'
                type=password
                name=password
                res=form.field.password
                res_descr=form.field.password.description
                autocomplete=false
                mandatory=true}-->
            <!--{include file='control/input.tpl'
                type=password
                name=password_confirm
                res=form.field.password_confirm
                res_descr=form.field.password_confirm.description
                autocomplete=false
                mandatory=true}-->
        <!--{/if}-->
        <!--{if $mode == 'profile'}-->
				  <!--{include file='control/input.tpl'
					  type=password
					  name=password_existing
					  res=form.field.existing_password
					  res_descr=form.field.existing_password.description
            autocomplete=false}-->
        <!--{/if}-->

				<!--{include file='control/input.tpl'
					type=text
					name=fullname
					value=$fullname
					res=form.field.agent_name
					res_descr=form.field.agent_name.description
					mandatory=true}-->

				<!--{include file='control/input.tpl'
					type=text
					name=email
					value=$email
					res=form.field.agent_email
					mandatory=true}-->

				<!--{if $mode != 'profile'}-->
				<!--{include file='control/input.tpl'
					  type=checkboxlist
            		  name=locales
					  options=$locales
            		  idfield=localeid
            		  valuefield=localename
            		  checkedfield=ishaslocale
					  res=form.field.agent_locales
					  res_descr=form.field.agent_locales.description}-->
          
          <!--{include file='control/input.tpl'
            type=checkbox
            name=is_admin
            value=$is_admin
            res=form.field.is_admin
            res_descr=form.field.is_admin.description}-->
				  <!--{include file='control/input.tpl'
					  type=checkboxlist
            name=departments
					  options=$departments
            idfield=departmentid
            valuefield=departmentname
            checkedfield=isindepartment
					  res=form.field.departments
					  res_descr=form.field.departments.description}-->
				<!--{/if}--> 
			<!--{if $avatar}-->
				<li>
					<label><!--{get_res code="form.field.avatar.current"}--></label>
          					<div class="area">
						<img src="<!--{$avatar}-->"/>
						<!--{if $mode == 'profile'}-->
              <a class="formauth" onclick="return window.confirm('<!--{get_res code="confirm.del_photo"}-->')" href='<!--{$webim_root}-->/operator/profile.php?act=delphoto'><!--{get_res code="page_agent.clear_avatar"}--></a>
						<!--{else}-->
						  <a class="formauth" onclick="return window.confirm('<!--{get_res code="confirm.del_photo"}-->')" href='<!--{$webim_root}-->/operator/operator.php?operatorid=<!--{$operatorid}-->&amp;act=delphoto'><!--{get_res code="page_agent.clear_avatar"}--></a>
						<!--{/if}-->
						<input type="hidden" name="avatar" value="<!--{$avatar}-->" />
						<div class="description">
							&mdash; <!--{get_res code="form.field.avatar.current.description"}-->
						</div>
					</div>
				</li>
			<!--{/if}-->
			<li>
				<label><!--{get_res code="form.field.avatar.upload"}--></label>
				<div class="area">
					<input type="file" class="avatarfile" name="avatarFile" size="25" value="<!--{$avatarFile}-->" />
					<div class="description">
						&mdash; <!--{get_res code="form.field.avatar.upload.description"}-->
					</div>
				</div>
			</li> 
			</ul>
		</fieldset>
    <!--{include file='control/save.tpl'}-->
		<!--{include file='control/asterisk_explain.tpl'}-->
	</form>
</div>
<!--{include file='control/footer.tpl'}-->