<!--{include file='control/header_admin.tpl' title_key='install.title'}-->
<div class="menu">
	<h1><!--{get_res code="install.title"}--></h1>
	<!--{get_res code="install.message.step2"}-->
</div>
<!--{include file='control/errors_block.tpl'}-->
<script type="text/javascript">
	function dbtype_change(s) {
		var dbtype = s.options[s.selectedIndex].value.toLowerCase();
		var li = document.getElementById("li-field-dbname");
		li.style.display = (dbtype == 'oracle' ? 'none' : ''); 
	}
	
</script>
<div class="info-box">
	<form name="setings-form" class="settings" method="post" action="<!--{$webim_root}-->/install/?act=step2">
		<fieldset>
			<ul>
					<li>
						<label><!--{get_res code="form.field.dbtype"}--><em>*</em></label>
						<div class="area">
							<select name="dbtype" onchange="dbtype_change(this);">
								<option id="mysql" <!--{if $page.dbtype=='MySQL'}--> selected=selected <!--{/if}-->>MySQL</option>
								<option id="oracle" <!--{if $page.dbtype=='Oracle'}--> selected=selected <!--{/if}-->>Oracle</option>
							</select>
						</div>
					</li>
				<!--{include file='control/input.tpl'
					type=text
					name=dbhost
					value=$page.dbhost
					res=form.field.dbhost
					res_descr=form.field.dbhost.description
					mandatory=true}-->

				<!--{include file='control/input.tpl'
					type=text
					name=dbname
					value=$page.dbname
					res=form.field.dbname
					res_descr=form.field.dbname.description
					mandatory=true}-->

				<!--{include file='control/input.tpl'
					type=text
					name=dbuser
					value=$page.dbuser
					res=form.field.dbuser
					res_descr=form.field.dbuser.description
					mandatory=true}-->

				<!--{include file='control/input.tpl'
					type=password
					name=dbpassword
					res=form.field.dbpassword
					mandatory=true}-->

				<!--{include file='control/input.tpl'
					type=text
					name=key
					value=$page.key
					res=form.field.key
					res_descr=form.field.key.description
					mandatory=true}-->

				<!--{include file='control/input.tpl'
					type=password
					name=adminpassword
					res=form.field.adminpassword
					mandatory=true}-->

				<!--{include file='control/input.tpl'
					type=password
					name=adminpasswordconfirm
					res=form.field.adminpasswordconfirm
					mandatory=true}-->

				<!--{include file='control/input.tpl'
					type=text
					name=adminemail
					value=$page.adminemail
					res=form.field.adminemail
					mandatory=true}-->
					
					<li>
						<label><!--{get_res code="form.field.encoding"}--><em>*</em></label>
						<div class="area">
							<select name="encoding">
								<option id="CP1251" <!--{if $page.encoding=='CP1251'}--> selected=selected <!--{/if}-->>CP1251</option>
								<option id="UTF-8" <!--{if $page.encoding=='UTF-8'}--> selected=selected <!--{/if}-->>UTF-8</option>
							</select>
						</div>
					</li>
			</ul>
		</fieldset>
		<div class="submit">
			<input type="hidden" name="submitted" value="1" />
			<input type="submit" value="<!--{get_res code="button.save"}-->" />
		</div>
		<div><!--{include file='control/asterisk_explain.tpl'}--></div>
	</form>
</div>
<!--{include file='control/footer.tpl'}-->