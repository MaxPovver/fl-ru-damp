<!--{include file='control/header_admin.tpl' title_key="page_auto_invite.title"}-->

<div class="menu">
    <!--{if $mode == 'edit_invite'}-->
        <!--{include file='control/top_block.tpl' title_key="page_auto_invite.title" sub_menu_key='leftMenu.auto_invites' sub_menu_link='/operator/auto_invites.php'}-->
		<!--{get_res code="page_auto_invite.intro"}-->
    <!--{elseif $mode == 'new_invite'}-->
		<!--{include file='control/top_block.tpl' title_key="page_auto_invite.title" sub_menu_key='leftMenu.auto_invites' sub_menu_link='/operator/auto_invites.php'}-->
		<!--{get_res code="page_auto_invite.create_new"}-->
	<!--{/if}-->
</div>
<!--{include file='control/errors_block.tpl'}-->
<div class="info-box">
	<form name="agentForm" method="post"  enctype="multipart/form-data" class="settings" action="">
		<fieldset>
			<ul>
				<!--{include file='control/input.tpl'
					type=text
					name=text
					value=$text
					res=form.field.text
					res_descr=form.field.text.description
					mandatory=true}-->
				<!--{include file='control/input.tpl'
					type=text
					name=came_from
					value=$came_from
					res=form.field.came_from
					res_descr=form.field.came_from.description
					}-->
				<li>
					<label><!--{get_res code="form.field.visited_page"}--></label>
					<div class="area">
						<input type="text" name="visited_page[0]" size="40" value="<!--{$visited_page.0}-->" class="formauth"/>
						<select name="visited_page_time[0]" class="visited-time">
							<option value="0" <!--{if $visited_page_time.0 == 0 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.0"}--></option>
							<option value="5" <!--{if $visited_page_time.0 == 5 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.5"}--></option>
							<option value="30" <!--{if $visited_page_time.0 == 30 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.30"}--></option>
							<option value="60" <!--{if $visited_page_time.0 == 60 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.60"}--></option>
							<option value="300" <!--{if $visited_page_time.0 == 300 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.300"}--></option>
							<option value="600" <!--{if $visited_page_time.0 == 600 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.600"}--></option>
							<option value="1800" <!--{if $visited_page_time.0 == 1800 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.1800"}--></option>
						</select>
						<div class="description">
							 <!--{get_res code="form.field.visited_page.description"}-->
						</div>
					</div>
				</li>
				<li>
					<label><!--{get_res code="form.field.visited_page"}--></label>
					<div class="area">
						<input type="text" name="visited_page[1]" size="40" value="<!--{$visited_page.1}-->" class="formauth"/>
						<select name="visited_page_time[1]" class="visited-time">
							<option value="0" <!--{if $visited_page_time.1 == 0 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.0"}--></option>
							<option value="5" <!--{if $visited_page_time.1 == 5 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.5"}--></option>
							<option value="30" <!--{if $visited_page_time.1 == 30 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.30"}--></option>
							<option value="60" <!--{if $visited_page_time.1 == 60 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.60"}--></option>
							<option value="300" <!--{if $visited_page_time.1 == 300 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.300"}--></option>
							<option value="600" <!--{if $visited_page_time.1 == 600 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.600"}--></option>
							<option value="1800" <!--{if $visited_page_time.1 == 1800 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.1800"}--></option>
						</select>
						<div class="description">
							 <!--{get_res code="form.field.visited_page.description"}-->
						</div>
					</div>
				</li>
				<li>
					<label><!--{get_res code="form.field.visited_page"}--></label>
					<div class="area">
						<input type="text" name="visited_page[2]" size="40" value="<!--{$visited_page.2}-->" class="formauth"/>
						<select name="visited_page_time[2]" class="visited-time">
							<option value="0" <!--{if $visited_page_time.2 == 0 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.0"}--></option>
							<option value="5" <!--{if $visited_page_time.2 == 5 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.5"}--></option>
							<option value="30" <!--{if $visited_page_time.2 == 30 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.30"}--></option>
							<option value="60" <!--{if $visited_page_time.2 == 60 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.60"}--></option>
							<option value="300" <!--{if $visited_page_time.2 == 300 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.300"}--></option>
							<option value="600" <!--{if $visited_page_time.2 == 600 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.600"}--></option>
							<option value="1800" <!--{if $visited_page_time.2 == 1800 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.1800"}--></option>
						</select>
						<div class="description">
							 <!--{get_res code="form.field.visited_page.description"}-->
						</div>
					</div>
				</li>
				<li>
					<label><!--{get_res code="form.field.visited_page"}--></label>
					<div class="area">
						<input type="text" name="visited_page[3]" size="40" value="<!--{$visited_page.3}-->" class="formauth"/>
						<select name="visited_page_time[3]" class="visited-time">
							<option value="0" <!--{if $visited_page_time.3 == 0 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.0"}--></option>
							<option value="5" <!--{if $visited_page_time.3 == 5 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.5"}--></option>
							<option value="30" <!--{if $visited_page_time.3 == 30 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.30"}--></option>
							<option value="60" <!--{if $visited_page_time.3 == 60 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.60"}--></option>
							<option value="300" <!--{if $visited_page_time.3 == 300 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.300"}--></option>
							<option value="600" <!--{if $visited_page_time.3 == 600 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.600"}--></option>
							<option value="1800" <!--{if $visited_page_time.3 == 1800 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.1800"}--></option>
						</select>
						<div class="description">
							 <!--{get_res code="form.field.visited_page.description"}-->
						</div>
					</div>
				</li>
				<li>
					<label><!--{get_res code="form.field.visited_page"}--></label>
					<div class="area">
						<input type="text" name="visited_page[4]" size="40" value="<!--{$visited_page.4}-->" class="formauth"/>
						<select name="visited_page_time[4]" class="visited-time">
							<option value="0" <!--{if $visited_page_time.4 == 0 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.0"}--></option>
							<option value="5" <!--{if $visited_page_time.4 == 5 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.5"}--></option>
							<option value="30" <!--{if $visited_page_time.4 == 30 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.30"}--></option>
							<option value="60" <!--{if $visited_page_time.4 == 60 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.60"}--></option>
							<option value="300" <!--{if $visited_page_time.4 == 300 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.300"}--></option>
							<option value="600" <!--{if $visited_page_time.4 == 600 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.600"}--></option>
							<option value="1800" <!--{if $visited_page_time.4 == 1800 }-->selected<!--{/if}-->><!--{get_res code="form.field.visited_page_time.1800"}--></option>
						</select>
						<div class="description">
							 <!--{get_res code="form.field.visited_page.description"}-->
						</div>
					</div>
				</li>
				<li>
					<label><!--{get_res code="form.field.time_on_site"}--></label>
					<div class="area">
						<select name="time_on_site" class="visited-time">
							<option value="0" <!--{if $time_on_site == 0 }-->selected<!--{/if}-->><!--{get_res code="form.field.time_on_site.0"}--></option>
							<option value="5" <!--{if $time_on_site == 5 }-->selected<!--{/if}-->><!--{get_res code="form.field.time_on_site.5"}--></option>
							<option value="30" <!--{if $time_on_site == 30 }-->selected<!--{/if}-->><!--{get_res code="form.field.time_on_site.30"}--></option>
							<option value="60" <!--{if $time_on_site == 60 }-->selected<!--{/if}-->><!--{get_res code="form.field.time_on_site.60"}--></option>
							<option value="300" <!--{if $time_on_site == 300 }-->selected<!--{/if}-->><!--{get_res code="form.field.time_on_site.300"}--></option>
							<option value="600" <!--{if $time_on_site == 600 }-->selected<!--{/if}-->><!--{get_res code="form.field.time_on_site.600"}--></option>
							<option value="1800" <!--{if $time_on_site == 1800 }-->selected<!--{/if}-->><!--{get_res code="form.field.time_on_site.1800"}--></option>
						</select>
						<div class="description">
							 <!--{get_res code="form.field.time_on_site.description"}-->
						</div>
					</div>
				</li>
				<!--{include file='control/input.tpl'
					type=checkbox
					name=order_matters
					value=$order_matters
					res=form.field.order_matters
					res_descr=form.field.order_matters.description
					}-->
    			<!--{include file='control/input.tpl'
					type=text
					name=number_of_pages
					value=$number_of_pages
					res=form.field.number_of_pages
					res_descr=form.field.number_of_pages.description
					}-->
			</ul>
		</fieldset>
    <!--{include file='control/save.tpl'}-->
		<!--{include file='control/asterisk_explain.tpl'}-->
	</form>
</div>
<!--{include file='control/footer.tpl'}-->