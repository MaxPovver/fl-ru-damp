<!--{include file='control/header_admin.tpl'  }-->
<div class="menu">
	<!--{include file='control/top_block.tpl' sub_menu_key="menu.blocked" sub_menu_link="/operator/blocked.php"}-->
	<!--{get_res code="page_ban.intro"}-->	
</div>
<!--{include file='control/errors_block.tpl'}-->
<div class="info-box">
	<form name="banForm" method="post" action="" class="settings">
		<fieldset>
			<ul>
        <!--{include file='control/input.tpl'
          type=text
          name=address
          value=$address
          res=form.field.address
          res_descr=form.field.address.description
          mandatory=true}-->
        <!--{include file='control/input.tpl'
          type=text
          name=till
          value=$till
          res=form.field.till
          res_descr=form.field.till.description
          mandatory=true}-->
        <!--{include file='control/input.tpl'
          type=textarea
          name=comment
          value=$comment
          res=form.field.ban_comment
          res_descr=form.field.ban_comment.description
          }-->
			</ul>
		</fieldset>
		<!--{include file='control/save.tpl'}-->
		<!--{include file='control/asterisk_explain.tpl'}-->
	</form>
</div>
<!--{include file='control/footer.tpl'}-->
