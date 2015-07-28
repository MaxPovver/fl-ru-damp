<li id="li-field-<!--{$name}-->">
	<label><!--{get_res code="$res"}--><!--{if isset($mandatory) && $mandatory}--> <em><font color="red">*</font></em><!--{/if}--></label>
	<div class="area">
	<!--{if $type eq 'text'}-->
            <input type="text" name="<!--{$name}-->" size="40" value="<!--{$value|escape:"html"}-->" <!--{if isset($tabindex)}-->tabindex=<!--{$tabindex}--><!--{/if}--> <!--{if $autocomplete == false}-->autocomplete="off"<!--{/if}--> class="formauth"/>
	<!--{elseif $type eq 'password'}-->
            <input type="password" name="<!--{$name}-->" size="40" <!--{if isset($tabindex)}-->tabindex=<!--{$tabindex}--><!--{/if}--> <!--{if $autocomplete == false}-->autocomplete="off"<!--{/if}--> value="<!--{$value}-->" class="formauth"/>
	<!--{elseif $type eq 'textarea'}-->
            <textarea rows="7" cols="38" name="<!--{$name}-->" class="message" <!--{if isset($tabindex)}-->tabindex=<!--{$tabindex}--><!--{/if}-->><!--{$value}--></textarea>
        <!--{elseif $type eq 'checkbox'}-->
            <input type="checkbox" name="<!--{$name}-->" class="message" value="on"<!--{if $value}--> checked="checked"<!--{/if}--> <!--{if isset($tabindex)}-->tabindex=<!--{$tabindex}--><!--{/if}-->></input>
        <!--{elseif $type eq 'checkboxlist'}-->
            <!--{foreach from=$options item=o}-->
                <div class="departments"><input id="<!--{$name}--><!--{$o.$idfield}-->" type="checkbox" name="<!--{$name}-->::<!--{$o.$idfield}-->" class="message" value="on"<!--{if $o.$checkedfield}--> checked="checked"<!--{/if}--> /><label for="<!--{$name}--><!--{$o.$idfield}-->"><!--{$o.$valuefield}--></label></div>
            <!--{/foreach}-->

        <!--{elseif $type eq 'selectlist'}-->
            <select name="<!--{$name}-->" <!--{if isset($tabindex)}-->tabindex=<!--{$tabindex}--><!--{/if}-->>
            <!--{foreach from=$selectarray key=key item=value}-->
                <option value="<!--{$key}-->" <!--{if $key eq $default_option}-->selected<!--{/if}-->><!--{$value}--></option>
            <!--{/foreach}-->
            </select>
	<!--{/if}-->
		<div class="description">
			<!--{if isset($res_descr)}--> <!--{get_res code="$res_descr"}--><!--{/if}-->
		</div>
	</div>
</li>