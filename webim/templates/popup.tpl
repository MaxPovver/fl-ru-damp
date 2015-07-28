<!--{ if $action == 'operators'}-->
	<a href="#" class="close" onclick="hidePopup()">close</a>
	<!--{if !empty($operators) }-->
        	<h2><!--{get_res code="popup.choose.operator"}--></h2>
        	<div style="margin-top:10px; margin-left:10px;">
        	<!--{if count(array_keys($operators)) > 1}-->
        		<select id="department" onchange="select_change_dep(this);">
        			<option value=""><!--{get_res code="choosedepartment.select"}--></option>
        			<!--{foreach from=$operators item=v key=departmentid}-->
        				<!--{if empty($departmentid)}-->
        					<!--{if count($v.operators) > 0}-->
        						<option value="wo-dep"><!--{get_res code="choosedepartment.wo_department"}--></option>
        					<!--{/if}-->
        				<!--{else}-->
	        				<option title="<!--{$webim_root}-->/operator/redirect.php?nextdepartmentid=<!--{$departmentid}-->&thread=<!--{$threadid}-->&token=<!--{$token}-->" value="<!--{$departmentid}-->">
	        					<!--{$v.departmentname}-->
	        				</option>
        				<!--{/if}-->
        			<!--{/foreach}-->
        		</select>&nbsp;
            <span id="operator-select-container">
        		<!--{foreach from=$operators item=v key=departmentid}-->
        			<!--{if count($v.operators) > 0}-->
	        			<!--{if empty($departmentid)}-->
	        				<select id="operators-wo-dep" style="display:none;" onchange="select_change_op(this);">
	        			<!--{else}-->
	        				<select id="operators-<!--{$departmentid}-->" style="display:none;" onchange="select_change_op(this);">
	        			<!--{/if}-->
	        			<option value="<!--{$webim_root}-->/operator/redirect.php?nextdepartmentid=<!--{$departmentid}-->&thread=<!--{$threadid}-->&token=<!--{$token}-->">
	        				<!--{get_res code="chooseoperator.any"}-->
	        			</option>
	        			<!--{foreach from=$v.operators item=o}-->
	                		<option value="<!--{$webim_root}-->/operator/redirect.php?nextdepartmentid=<!--{$departmentid}-->&nextoperatorid=<!--{$o.operatorid}-->&thread=<!--{$threadid}-->&token=<!--{$token}-->"><!--{$o.fullname}--></option>
	              		<!--{/foreach}-->
	        			</select>
        			<!--{/if}-->
        		<!--{/foreach}-->
            </span>
        		<script type="text/javascript">
        			var department_urls = {};
        			<!--{foreach from=$operators item=v key=departmentid}-->
        				<!--{if $departmentid}-->
        				department_urls[<!--{$departmentid}-->]='<!--{$webim_root}-->/operator/redirect.php?nextdepartmentid=<!--{$departmentid}-->&thread=<!--{$threadid}-->&token=<!--{$token}-->';
      					<!--{/if}-->					
                	<!--{/foreach}-->        					
        		</script>
        	<!--{else}-->
        		<!--{foreach from=$operators item=v key=departmentid}-->
	        		<select onchange="select_change_op(this);">
	        			<option value="">
	        				<!--{get_res code="chooseoperator.select"}-->
	        			</option>
	        			<!--{foreach from=$v.operators item=o}-->
	                		<option value="<!--{$webim_root}-->/operator/redirect.php?nextdepartmentid=<!--{$departmentid}-->&nextoperatorid=<!--{$o.operatorid}-->&thread=<!--{$threadid}-->&token=<!--{$token}-->"><!--{$o.fullname}--></option>
	              		<!--{/foreach}-->
	        		</select>
        		<!--{/foreach}-->
        	<!--{/if}-->
        	&nbsp;<input type="button" id="popup-redirect-btn" onclick="click_button(this);" value="<!--{get_res code="chooseoperator.redirect"}-->" disabled="disabled"/>
        	</div> 
	<!--{else}-->
		<h2><!--{get_res code="popup.no.online.operators"}--></h2>
	<!--{/if}-->	
<!--{/if}-->
<!--{ if $action == 'visitor_redirected'}-->
	<a href="<!--{$link}-->" class="close">close</a>
	<big>
	    <!--{get_res code="popop.chat.is.being.served"}-->
	</big>
<!--{/if}-->
<!--{ if $action == 'chat_closed'}-->
	<a href="<!--{$link}-->" class="close">close</a>
	<big>
	    <!--{get_res code="popup.chat.closed"}-->
	</big>
<!--{/if}-->