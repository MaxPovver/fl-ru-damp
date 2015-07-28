<ul class="operator-block">
	<!--{if !empty($operator_name)}-->
		<li><!--{get_res code="menu.operator" 0=$operator_name 1=$webim_root|cat:'/operator/profile.php'}--></li>
	<!--{/if}-->
	
  <!--{if !empty($sub_menu_key)}-->
		<li><a href="<!--{$webim_root}--><!--{$sub_menu_link}-->" title="<!--{get_res code=$sub_menu_key}-->"><!--{get_res code=$sub_menu_key}--></a></li>
	<!--{/if}-->
	
	<!--{if empty($is_main) || !$is_main}-->
		<li><a href="<!--{$webim_root}-->/operator/index.php" title="<!--{get_res code="menu.main"}-->"><!--{get_res code="menu.main"}--></a></li>
	<!--{/if}-->
  
  <li><a href="<!--{$webim_root}-->/operator/logout.php" title="<!--{get_res code="content.logoff"}-->"><!--{get_res code="topMenu.logoff"}--></a></li>
</ul>