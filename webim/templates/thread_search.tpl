  <!--{include file='control/header_admin.tpl'}-->
<div class="menu">
	<!--{include file='control/top_block.tpl'}-->
	<!--{get_res code="page_search.intro"}-->
</div>
<!--{if $errors}-->
	<!--{foreach from=$errors item=error}-->
		<!--{get_res code="errors.prefix"}--><!--{$error}--><!--{get_res code="errors.suffix"}-->
	<!--{/foreach}-->
	<!--{get_res code="errors.footer"}-->
<!--{/if}-->
<div class="info-box">
	<!--{if $advanced}--> 
    <form name="searchForm" method="get" action="" class="search general-search">
    <fieldset>
      <label><!--{get_res code="page_analysis.full.text.search"}--></label>
      
       
      <div>
        <input type="text" name="q" size="74" value="<!--{$page_settings.formq}-->" class="txt" />
        
        <input type="submit" class="btn-login" value="<!--{get_res code="button.search"}-->" />
      </div>
      <div class="show_empty">
        <input type="checkbox" name="show_empty" id="show_empty" value="1"
        <!--{if $smarty.get.show_empty == 1}-->
                  checked="checked"                                 
                <!--{/if}-->
              ><label for="show_empty"><!--{get_res code="search.show_empty"}--></label>
            </div>
    </fieldset>
		<ul>
			<li class="date">
				<label><!--{get_res code="search.dates"}--></label>
				<div>
					<label for="startday"><!--{get_res code="search.from"}--></label>
					<select name="startday" id="startday">
						<!--{foreach from=$page_settings.availableDays item=day}-->
						<option value="<!--{$day}-->"<!--{if $day == $page_settings.formstartday}--> selected="selected"<!--{/if}-->><!--{$day}--></option>
						<!--{/foreach}-->
					</select>
					 <select name="startmonth">
						<!--{foreach from=$page_settings.availableMonth key=key item=month}-->
						<option value="<!--{$key}-->"<!--{if $key == $page_settings.formstartmonth}--> selected="selected"<!--{/if}-->><!--{$month}--></option>
						<!--{/foreach}-->
					</select>
					<label for="endday"><!--{get_res code="search.till"}--></label>
					<select name="endday" id="endday">
						<!--{foreach from=$page_settings.availableDays item=day}-->
						<option value="<!--{$day}-->"<!--{if $day == $page_settings.formendday}--> selected="selected"<!--{/if}-->><!--{$day}--></option>
						<!--{/foreach}-->
					</select>
					<select name="endmonth">
						<!--{foreach from=$page_settings.availableMonth key=key item=month}-->
						<option value="<!--{$key}-->"<!--{if $key == $page_settings.formendmonth}--> selected="selected"<!--{/if}-->><!--{$month}--></option>
						<!--{/foreach}-->
					</select>
				</div>
			</li>
      <li>
        <label for="operator"><!--{get_res code="search.operator"}--></label>
        <select name="operator" id="operator">
          <!--{foreach from=$page_settings.operatorList key=key item=operator}-->
            <option value="<!--{$key}-->"<!--{if $key == $smarty.get.operator}--> selected="selected"<!--{/if}-->><!--{$operator}--></option>
          <!--{/foreach}-->
        </select>
      </li>
      <li>
        <label for="rate"><!--{get_res code="search.rate"}--></label>
        <select name="rate" id="rate">
          <option value="" <!--{if empty($smarty.get.rate)}--> selected="selected"<!--{/if}-->><!--{get_res code="search.anyrate"}--></option>
          <option value="negative" <!--{if $smarty.get.rate == 'negative'}--> selected="selected"<!--{/if}-->><!--{get_res code="search.negativerate"}--></option>
          <option value="positive" <!--{if $smarty.get.rate == 'positive'}--> selected="selected"<!--{/if}-->><!--{get_res code="search.positiverate"}--></option>
        </select>
      </li>
      <li>
        <label for="department"><!--{get_res code="search.department"}--></label>
        <select name="departmentid" id="departmentid">
          <option value="" <!--{if empty($smarty.get.departmentid)}--> selected="selected"<!--{/if}-->><!--{get_res code="search.anydepartment"}--></option>
          <!--{foreach from=$departments item=d}-->
            <option value="<!--{$d.departmentid}-->"<!--{if $d.departmentid == $smarty.get.departmentid}--> selected="selected"<!--{/if}-->><!--{$d.departmentname}--></option>
          <!--{/foreach}-->
        </select>
      </li>
      <li>
        <label for="locale"><!--{get_res code="search.locale"}--></label>
        <select name="locale" id="locale">
          <option value="" <!--{if empty($smarty.get.locale)}--> selected="selected"<!--{/if}-->><!--{get_res code="search.anylocale"}--></option>
          <!--{foreach from=$locales item=l}-->
            <option value="<!--{$l.localeid}-->"<!--{if $l.localeid == $smarty.get.locale}--> selected="selected"<!--{/if}-->><!--{$l.localename}--></option>
          <!--{/foreach}-->
        </select>
      </li>
      <li>
        <label for="offline"><!--{get_res code="search.online_offline"}--></label>
        <select name="offline" id="locale">
          <option value="" <!--{if empty($smarty.get.offline)}--> selected="selected"<!--{/if}-->><!--{get_res code="search.any_online_offline"}--></option>
          <option value="1"<!--{if 1 == $smarty.get.offline}--> selected="selected"<!--{/if}-->><!--{get_res code="search.select.online"}--></option>
          <option value="2"<!--{if 2 == $smarty.get.offline}--> selected="selected"<!--{/if}-->><!--{get_res code="search.select.offline"}--></option>
        </select>
      </li>
		</ul>
		<p class="more"> 
		     <a href='<!--{$webim_root}-->/operator/history.php'><!--{get_res code="page.analysis.general.search"}--></a> 
                    
		</p>
    </form> 
	<!--{else}-->
    <form name="searchForm" method="get" action="" class="general-search">
		<fieldset>
			<label><!--{get_res code="page_analysis.full.text.search"}--></label>
			<div>
				<input type="text" name="q" size="80" value="<!--{$smarty.get.q}-->" class="txt" />
				<input type="submit" class="btn-login" value="<!--{get_res code="button.search"}-->" />
				 
			</div>
			<div>
				<input type="checkbox" name="show_empty" id="show_empty" value="1"
				<!--{if $page_settings.show_empty == 1}-->
          		  	checked="checked"                                 
          	  	<!--{/if}-->
            	><label for="show_empty"><!--{get_res code="search.show_empty"}--></label>
            </div>
			<p class="more"> 
               			  <a href='<!--{$webim_root}-->/operator/adv_history.php'><!--{get_res code="page.analysis.advanced.search"}--></a> 
                                 
			</p>
		</fieldset>
    </form>
	 <!--{/if}-->
	<!--{if $page_settings.pagination && $page_settings.pagination_items}-->
		<div class="listof">
			<table class="pending-visitors history-log">
				<tr>
					<th><!--{get_res code="page.analysis.search.head_name"}--></th>
					<th><!--{get_res code="page.analysis.search.head_host"}--></th>
					<th><!--{get_res code="page.analysis.search.head_operator"}--></th>
					<th><!--{get_res code="page.analysis.search.head_messages"}--></th>
					<th class="last"><!--{get_res code="page.analysis.search.head_time"}--></th>
				</tr>
				<!--{foreach from=$page_settings.pagination_items item=item}-->
					<tr>
						<td class="name"> <a  href="<!--{$webim_root}-->/operator/threadprocessor.php?threadid=<!--{$item.threadid}-->" target="_blank" onclick="this.newWindow = window.open('<!--{$webim_root}-->/operator/threadprocessor.php?threadid=<!--{$item.threadid}-->', '', 'toolbar=0, scrollbars=1, location=0, status=1, menubar=0, width=600, height=420, resizable=1'); if (this.newWindow != null) {this.newWindow.focus();this.newWindow.opener=window;}return false;"><!--{$item.visitorname|escape:'html'}--></a> 
						</td>
						<td><!--{$item.remote}--></td>
						<td><!--{if $item.operatorfullname}--><!--{$item.operatorfullname}--><!--{/if}--></td>
						<td><!--{$item.size}--></td>
						<td class="last"><!--{$item.created}-->,  <!--{$item.diff}--></td>
					</tr>
				<!--{/foreach}-->
			</table>
			<div class="pagers">
				<!--{$pagination}-->
			</div>
		    <!--{/if}-->
			<!--{if isset($page_settings.formq) && !isset($page_settings.pagination_items)}-->
				<div class="pagination-items">
					<!--{get_res code="tag.pagination.no_items"}-->
				</div>
			<!--{/if}-->
		</div>
<!--{include file='control/footer.tpl'}-->
</div>
