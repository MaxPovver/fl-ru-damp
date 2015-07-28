<!--{include file='control/head.tpl'}--> 
		<div class="header">
			<!--{include file='control/header_inner.tpl' close_class="window-close-simple"}-->
			<h1><a href="<!--{$url}-->" onclick="window.open('<!--{$url}-->');return false;"><img src="<!--{$logo}-->" alt="<!--{$company}-->" /></a></h1>
			<h2><!--{get_res code="consult.title"}--> / <!--{get_res code="chooseoperator.title"}--></h2>
		</div>
		<div class="content">
			<script type="text/javascript" src="<!--{$webim_root}-->/js/choose_op_dep.js"></script>		
			<div class="webim-form">
				<b class="b1"></b>
				<b class="b2"></b>
				<div class="form-in c">
					<p>
						<select id="choose-select" onchange="<!--{if $mode == 'mandatory'}-->select_change<!--{else}-->select_change_optional<!--{/if}-->(this);">
							<!--{foreach from=$onlineOperators item=operator}-->
								<option value="<!--{$to_url}--><!--{$operator.operatorid}-->"><!--{$operator.fullname}--></option>
							<!--{/foreach}-->
						</select>
					</p>
				</div>
				<b class="b2"></b>
				<b class="b1"></b>
			</div>
			<div class="webim-btn">
				<input  type="image" src="/webim/themes/<!--{$theme}-->/img/btn-start.png" id="start-chat" <!--{if $mode == 'mandatory'}-->disabled="disabled"<!--{/if}--> onclick="click_button(this);" value="<!--{get_res code="button.start_chat"}-->" />
			</div>
		</div>
<!--{include file='control/foo.tpl'}-->