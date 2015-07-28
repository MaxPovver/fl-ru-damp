<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=<!--{$browser_charset}-->" />
  <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
  <title><!--{get_res code="app.title"}--> &#8212; <!--{get_res code="page_intercept.title"}--></title>
  <link rel="stylesheet" type="text/css" media="all" href="<!--{$webim_root}-->/css/admin_styles.css?<!--{$version}-->" />
  <link rel="shortcut icon" href="<!--{$webim_root}-->/images/favicon.ico" type="image/x-icon"/>
</head>
<body>
<div class="page">
	<div class="loin-box">
		<!--{if $page.force}-->
				<!--{get_res code="confirm.take.head"}-->
			<!--{/if}-->
		<div class="force">
				<!--{if $page.force}-->
          <!--{if $page.priority}-->
            <!--{get_res code="confirm.take.waiting.for.another.operator" 0=$page.visitor 1=$page.agent}-->
          <!--{else}-->
					  <!--{get_res code="confirm.take.message" 0=$page.visitor 1=$page.agent}-->          
          <!--{/if}-->
					<ul>
						<li><input type="button" onclick="window.location='<!--{$page.takelink}-->'" value="<!--{get_res code="confirm.take.yes"}-->" /></li>
						<li><input type="button" onclick="javascript:window.close();" value="<!--{get_res code="confirm.take.no"}-->" /></li>
					</ul>
				<!--{else}-->
					<!--{if $page.closed}-->
						<!--{get_res code="confirm.thread.closed"}-->
					<!--{else}-->
					<!--{get_res code="confirm.is.being.served"}-->
				<!--{/if}-->
				
				<!--a href="javascript:window.close();"><!--{get_res code="confirm.close"}--></a-->
				<!--{/if}-->																
				<!--{if $page.closed}-->
					<!--{get_res code="confirm.view1"}-->
					<a target="_blank" href="threadprocessor.php?threadid=<!--{$page.thread_id}-->"><!--{get_res code="confirm.view2"}--></a>
				<!--{else}-->
					<!--{get_res code="confirm.view1"}--><a href="<!--{$page.viewlink}-->"><!--{get_res code="confirm.view2"}--></a>
				<!--{/if}-->				
		</div>
	</div>
</div>
	<!--{include file='control/footer.tpl'}-->
</body>
</html>