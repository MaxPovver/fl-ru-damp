<!--{include file='control/header_admin.tpl'}-->
<div class="menu">
	<!--{include file='control/top_block.tpl'}-->
	<!--{ get_res code='statistics.description'}-->
</div>
<!--{if $errors}-->
	<!--{get_res code="errors.header"}-->
	<!--{foreach from=$errors item=e}-->
		<!--{get_res code="errors.prefix"}-->
			<!--{$e}-->
		<!--{get_res code="errors.suffix"}-->
	<!--{/foreach}-->
	<!--{get_res code="errors.footer"}-->
<!--{/if}-->
<div class="info-box">
	<form name="statisticsForm" method="get" action="" class="statisticsform">
		<fieldset>
			<dl>
				<dt><label><!--{ get_res code='statistics.dates'}--></label></dt>
				<dd>
					<label for="startday"><!--{ get_res code='statistics.from'}--></label>
					<select name="startday" id="startday">
						<!--{foreach from=$page_settings.availableDays item=k}-->
						<option value="<!--{$k}-->"
							<!--{if $page_settings.formstartday && $k == $page_settings.formstartday}-->
								selected="selected">
							<!--{else}-->
							>
							<!--{/if}-->
							<!--{$k}-->
						</option>
						<!--{/foreach}-->
					</select>
					<select name="startmonth">
					<!--{foreach key=k item=v from=$page_settings.availableMonth}-->
						<option value="<!--{$k}-->"
						<!--{if $page_settings.formstartmonth && $k == $page_settings.formstartmonth}-->
							selected="selected">
						<!--{else}-->
							>
						<!--{/if}-->
						<!--{$v}-->
						</option>";
					<!--{/foreach}-->
					</select>
					<label for="endday"><!--{ get_res code='statistics.till'}--></label>
					<select name="endday" id="endday">
					<!--{foreach from=$page_settings.availableDays item=k}-->
						<option value="<!--{$k}-->"
							<!--{if $page_settings.formendday && $k == $page_settings.formendday}-->
								selected="selected">
							<!--{else}-->
							>
							<!--{/if}-->
							<!--{$k}-->
						</option>
						<!--{/foreach}-->
					</select>
					<select name="endmonth">
					<!--{foreach key=k item=v from=$page_settings.availableMonth}-->
						<option value="<!--{$k}-->"
						<!--{if $page_settings.formendmonth && $k == $page_settings.formendmonth}-->
							selected="selected">
						<!--{else}-->
							>
						<!--{/if}-->
						<!--{$v}-->
						</option>
					<!--{/foreach}-->
					</select>
				</dd>
				<!--{if $page_settings.departments or $page_settings.locales }-->
				<dt><label><!--{ get_res code='statistics.department_and_locale'}--></label></dt>
				<dd>
					<label for="departmentid"><!--{ get_res code='statistics.department'}--></label>
					<select name="departmentid" id="departmentid">
						<option value=""><!--{ get_res code='statistics.all_departments'}--></option>
						<!--{foreach from=$page_settings.departments item=k}-->
						<option value="<!--{$k.departmentid}-->"
							<!--{if $page_settings.departmentid && $k.departmentid == $page_settings.departmentid}-->
								selected="selected">
							<!--{else}-->
							>
							<!--{/if}-->
							<!--{$k.departmentname}-->
						</option>
						<!--{/foreach}-->
					</select>
					<label for="locale"><!--{ get_res code='statistics.locale'}--></label>
					<select name="locale" id="locale">
						<option value=""><!--{ get_res code='statistics.all_locales'}--></option>
						<!--{foreach from=$page_settings.locales item=k}-->
						<option value="<!--{$k.localeid}-->"
							<!--{if $page_settings.locale && $k.localeid == $page_settings.locale}-->
								selected="selected">
							<!--{else}-->
							>
							<!--{/if}-->
							<!--{$k.localename}-->
						</option>
						<!--{/foreach}-->
					</select>
				</dd>
				<!--{/if}-->
				<dd><input type="submit" class="btn-login" value="<!--{get_res code='button.search'}-->" /></dd>
			</dl>
		</fieldset>
	</form>
</div>
<div class="listof statistics">
	<!--{if $page_settings.showresults}-->
		<h2><!--{ get_res code='report.bydate.title'}--></h2>
		<!--{if $page_settings.reportByDate}-->
			<table class="pending-visitors">
				<tr>
					<th><!--{ get_res code='report.bydate.1'}--></th>
					<th><!--{ get_res code='report.bydate.2'}--></th>
					<th><!--{ get_res code='report.bydate.3'}--></th>
					<th class="last"><!--{ get_res code='report.bydate.4'}--></th>
				</tr>
				<!--{foreach from=$page_settings.reportByDate item=row}-->
					<tr>
						<td><!--{$row.date}--></td>
						<td><!--{$row.threads}--></td>
						<td><!--{$row.agents}--></td>
						<td class="last"><!--{$row.visitors}--></td>
					</tr>
				<!--{/foreach}-->
				<tr>
					<th><!--{ get_res code='report.total'}--></th>
					<th><!--{$page_settings.reportByDateTotal.threads}--></th>
					<th><!--{$page_settings.reportByDateTotal.agents}--></th>
					<th class="last"><!--{$page_settings.reportByDateTotal.visitors}--></th>
				</tr>
			</table>
		<!--{else}-->
			<div class="pagers">
				<!--{ get_res code='report.no_items'}-->
			</div>
		<!--{/if}-->
		<h2><!--{ get_res code='report.byoperator_date.title'}--></h2>
		<!--{if $page_settings.reportByAgentByDate}-->
			<table class="pending-visitors">
				<tr>
					<th><!--{ get_res code='report.byoperator.1'}--></th>
					<th><!--{ get_res code='report.byoperator.2'}--></th>
					<th><!--{ get_res code='report.byoperator.3'}--></th>
					<th><!--{ get_res code='report.byoperator.4'}--></th>
					<th><!--{ get_res code='report.byoperator.6'}--></th>
					<th><!--{ get_res code='report.byoperator.7'}--></th>
					<th><!--{ get_res code='report.byoperator.8'}--></th>
					<th><!--{ get_res code='report.byoperator.9'}--></th>
					<th><!--{ get_res code='report.byoperator.10'}--></th>
					<th class="last"><!--{ get_res code='report.byoperator.11'}--></th>
				</tr>
				<!--{foreach from=$page_settings.reportByAgentByDate key=date item=operator}-->
					<tr><td colspan="10" class="last"><h3><!--{$date}--></h3></td></tr>
					<!--{foreach from=$operator item=row}-->
						<tr>
							<td><!--{$row.name}--></td>
							<td><!--{$row.threads}--></td>
							<td><!--{$row.msgs}--></td>
							<td><!--{$row.avglen}--></td>
							<td><!--{$row.online_time}--></td>
							<td><!--{$row.online_chatting_time}--></td>
							<td><!--{$row.online_sum_chatting_time}--></td>
							<td><!--{$row.online_avg_chatting_time}--></td>
							<td><!--{$row.avg_answer_time}--> ( <!--{ get_res code='report.standart_deviation' 0=$row.answer_time_st_deviation }--> ) </td>
							<td class="last"><!--{$row.invited_users}--></td>
						</tr>
					<!--{/foreach}-->
				<!--{/foreach}-->
			</table>
		<!--{else}-->
			<div class="pagers">
				<!--{ get_res code='report.no_items'}-->
			</div>
		<!--{/if}-->
		
		<h2><!--{ get_res code='report.byoperator.title'}--></h2>
		<!--{if $page_settings.reportByAgent}-->
			<table class="pending-visitors">
				<tr>
					<th><!--{ get_res code='report.byoperator.1'}--></th>
					<th><!--{ get_res code='report.byoperator.2'}--></th>
					<th><!--{ get_res code='report.byoperator.3'}--></th>
					<th><!--{ get_res code='report.byoperator.4'}--></th>
					<th><!--{ get_res code='report.byoperator.5'}--></th>
					<th><!--{ get_res code='report.byoperator.6'}--></th>
					<th><!--{ get_res code='report.byoperator.7'}--></th>
					<th><!--{ get_res code='report.byoperator.8'}--></th>
					<th><!--{ get_res code='report.byoperator.9'}--></th>
					<th><!--{ get_res code='report.byoperator.10'}--></th>
					<th class="last"><!--{ get_res code='report.byoperator.11'}--></th>
					
				</tr>
				<!--{foreach from=$page_settings.reportByAgent item=row}-->
					<tr>
						<td><!--{$row.name}--></td>
						<td><!--{$row.threads}--></td>
						<td><!--{$row.msgs}--></td>
						<td><!--{$row.avglen}--></td>
						<td><!--{$row.rating}--> (<!--{ get_res code='report.rate_count' 0=$row.rate_count }--> )</td>
						<td><!--{$row.online_time}--></td>
						<td><!--{$row.online_chatting_time}--></td>
						<td><!--{$row.online_sum_chatting_time}--></td>
						<td><!--{$row.online_avg_chatting_time}--></td>
						<td><!--{$row.avg_answer_time}--> ( <!--{ get_res code='report.standart_deviation' 0=$row.answer_time_st_deviation }--> ) </td>
						<td class="last"><!--{$row.invited_users}--></td>
					</tr>
				<!--{/foreach}-->
			</table>
    <p><!--{ get_res code='report.byoperator.agenda'}--></p>
		<!--{else}-->
			<div class="pagers">
				<!--{ get_res code='report.no_items'}-->
			</div>
		<!--{/if}-->
		<h2><!--{ get_res code='report.lostvisitors.title'}--></h2>
		<!--{if $page_settings.reportLostVisitors}-->
			<table class="pending-visitors">
				<tr>
					<th><!--{ get_res code='report.lostvisitors.1'}--></th>
					<th><!--{ get_res code='report.lostvisitors.2'}--></th>
					<th class="last"><!--{ get_res code='report.lostvisitors.3'}--></th>
				</tr>
				<!--{foreach from=$page_settings.reportLostVisitors item=row}-->
					<tr>
						<td><!--{$row.name}--></td>
						<td><!--{$row.lost_vistors_count}--></td>
						<td class="last"><!--{$row.avg_waittime_str}--> ( <!--{ get_res code='report.standart_deviation' 0=$row.st_deviation }--> )</td>
					</tr>
				<!--{/foreach}-->
			</table>
		<!--{else}-->
			<div class="pagers">
				<!--{ get_res code='report.no_items'}-->
			</div>
		<!--{/if}-->
		
		<h2><!--{ get_res code='report.interceptedvisitors.title'}--></h2>
		<!--{if $page_settings.reportInterceptedVisitors}-->
			<table class="pending-visitors">
				<tr>
					<th><!--{ get_res code='report.interceptedvisitors.1'}--></th>
					<th><!--{ get_res code='report.interceptedvisitors.2'}--></th>
					<th class="last"><!--{ get_res code='report.interceptedvisitors.3'}--></th>
				</tr>
				<!--{foreach from=$page_settings.reportInterceptedVisitors item=row}-->
					<tr>
						<td><!--{$row.name}--></td>
						<td><!--{$row.lost_vistors_count}--></td>
						<td class="last"><!--{$row.avg_waittime_str}--> ( <!--{ get_res code='report.standart_deviation' 0=$row.st_deviation }--> )</td>
					</tr>
				<!--{/foreach}-->
			</table>
		<!--{else}-->
			<div class="pagers">
				<!--{ get_res code='report.no_items'}-->
			</div>		
		<!--{/if}-->
	<!--{/if}-->
</div>
<!--{include file='control/footer.tpl'}-->