<!--{include file='control/header_admin.tpl' title_key="leftMenu.client_gen_button"}-->
<div class="menu">
	<!--{include file='control/top_block.tpl' title_key="leftMenu.client_gen_button"}-->
	<!--{get_res code="page.gen_button.intro"}-->
</div>
<div class="info-box">
	<form name="buttonCodeForm" method="get" action="<!--{$webim_root}-->/operator/getcode.php" class="settings">
		<fieldset>
			<ul>
      <!--{foreach from=$params item=p key=name}-->
        <li>
          <label for="<!--{$name}-->"><!--{get_res code=$p.name_key}--></label>
          <div class="area">
          <!--{if $p.type eq 'list'}-->
            <select id="<!--{$name}-->" name="<!--{$name}-->" onChange="this.form.submit();">
              <!--{foreach from=$p.values item=i}-->
                <option value="<!--{$i.key}-->" <!--{if $i.key == $smarty.get.$name || (empty($smarty.get.$name) && $i.key==$p.default)}--> selected="selected"<!--{/if}-->><!--{$i.value}--></option>
              <!--{/foreach}-->
            </select>
          <!--{elseif $p.type eq 'checkbox'}-->
            <input id="<!--{$name}-->" name="<!--{$name}-->" type="checkbox" class="message" value="y"<!--{if $smarty.get.$name == 'y' || (empty($smarty.get.$name) && $p.default == 'y')}--> checked="checked"<!--{/if}--> onchange="this.form.submit();" />
          <!--{/if}-->
          </div>
        </li>
      <!--{/foreach}-->
      
      
				<li>
					<label for="html-code"><!--{get_res code="page.gen_button.code"}--></label>
					<div class="area">
						<textarea id="html-code" onFocus="this.select()" cols="60" rows="10"><!--{$code}--></textarea>
						<div><!--{get_res code="page.gen_button.code.description"}--></div>
					</div>
				</li>
				<li>
					<label><!--{get_res code="page.gen_button.sample"}--></label>
					<div class="area"><!--{$code_raw}--></div>
				</li>
			</ul>
		</fieldset>
	</form>
</div>
<!--{include file='control/footer.tpl'}-->