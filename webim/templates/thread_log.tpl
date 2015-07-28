 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<!--{$browser_charset}-->" />
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
    <title><!--{get_res code="app.title"}--> &#8212; <!--{get_res code="page_ban.title"}--></title> 
    <link rel="stylesheet" type="text/css" media="all" href="<!--{$webim_root}-->/css/admin_styles.css?<!--{$version}-->" /> 
    <link rel="shortcut icon" href="<!--{$webim_root}-->/images/favicon.ico" type="image/x-icon"/>
</head>
<body>

<div class="menu">
	<h1><!--{get_res code="thread.chat_log"}--></h1>
	<!--{ get_res code="thread.intro"}-->
</div>

<div class="info-box">
	<!--{if !$removed_thread}-->
	<div class="chat-log">
		<!--{foreach from=$messages item=i}-->
			<!--{$i}-->
		<!--{/foreach}-->
	</div>
	
	<p class="more">
		<a href="#" onClick="window.close(); return false;"><!--{get_res code="confirm.close"}--></a>
		<!--{if $is_admin}-->
			&nbsp;&nbsp;<a href="#" onClick="if(confirm('<!--{get_res code="chat.window.admin.history.remove_thread.confirm"}-->')) { location.href='<!--{$webim_root}-->/operator/threadprocessor.php?act=removethread&threadid=<!--{$threadid}-->'; } return false;">
				<!--{get_res code="chat.window.admin.history.remove_thread"}-->
			</a>
		<!--{/if}-->
	</p>
	<!--{else}-->
		<p><!--{get_res code="chat.window.admin.history.thread_removed"}--></p>
		<script type="text/javascript">
			if(window.opener != undefined) {
				window.opener.location.reload();
			}

			setTimeout(function() {
				window.close();
			}, 5000);
			
		</script>
	<!--{/if}-->
</div>

</body>
