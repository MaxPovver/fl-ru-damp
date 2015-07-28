 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=<!--{$browser_charset}-->" />
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
	<title><!--{get_res code="app.title"}--> &#8212; <!--{$title}--></title> 
    <link rel="stylesheet" type="text/css" media="all" href="<!--{$webim_root}-->/css/admin_styles.css?<!--{$version}-->" />
    <script type="text/javascript" language="javascript" src="<!--{$webim_root}-->/js/common.js?<!--{$version}-->"></script>
    <script type="text/javascript" language="javascript" src="<!--{$webim_root}-->/js/init.js?<!--{$version}-->"></script>    
    <script type="text/javascript" language="javascript" src="<!--{$webim_root}-->/js/chat_list.js?<!--{$version}-->"></script>
    <script type="text/javascript" language="javascript">
        WM_initChatList("<!--{get_res code="pending.table.speak"}-->", "<!--{get_res code="pending.table.view"}-->",
            "<!--{get_res code="pending.table.ban"}-->", "<!--{get_res code="sound.on.title"}-->",
            "<!--{get_res code="sound.off.title"}-->", "<!--{get_res code="clients.no_clients"}-->",
            "<!--{$webim_root}-->/operator/update.php<!--{$lang_and_is_operator_param}-->",
            "<!--{$webim_root}-->", "<!--{$webim_root}-->/operator/agent.php<!--{$lang_param}-->","<!--{$whois_url}-->");
    </script> 
    <link rel="shortcut icon" href="<!--{$webim_root}-->/images/favicon.ico" type="image/x-icon"/>
</head> 
<!--{include file='control/body_start.tpl'}-->
<div class="menu">
	<!--{include file='control/top_block.tpl'}-->
	<a id="sound-control" href="#" class="action-button"><span id="sound-control-span"></span></a>
	<!--{get_res code="clients.intro"}-->
	<!--{get_res code="clients.how_to"}-->
</div>
<div class="visitors-table">
	<table class="pending-visitors" id="threadlist" >
		<tr>
			<th><!--{get_res code="pending.table.head.name"}--></th>
			<th><!--{get_res code="pending.table.head.address"}--></th>
			<th><!--{get_res code="pending.table.head.state"}--></th>
			<th><!--{get_res code="pending.table.head.page"}--></th>
			<th><!--{get_res code="pending.table.head.operator"}--></th>
			<th><!--{get_res code="pending.table.head.locale"}--></th>
			<th><!--{get_res code="pending.table.head.department"}--></th>
			<th><!--{get_res code="pending.table.head.total"}--></th>
			<th><!--{get_res code="pending.table.head.waittime"}--></th>
			<th class="last"><!--{get_res code="pending.table.head.etc"}--></th>
		</tr>
		<tr id="prio">
			<td colspan="10"><img src="<!--{$webim_root}-->/images/tblicusers.gif" alt="" /><!--{get_res code="clients.queue.prio"}--></td>
		</tr>
		<tr id="prioend">
			<td colspan="10" id="status"></td>
		</tr>
		<tr id="wait">
			<td colspan="10"><img src="<!--{$webim_root}-->/images/tblicusers2.gif" alt="" /><!--{get_res code="clients.queue.wait"}--></td>
		</tr>
		<tr id="waitend">
			<td colspan="10" id="status"></td>
		</tr>
		<tr id="chat">
			<td colspan="10"><img src="<!--{$webim_root}-->/images/tblicusers3.gif" alt="" /><!--{get_res code="clients.queue.chat"}--></td>
		</tr>
		<tr id="chatend">
			<td colspan="10" id="status"></td>
		</tr>
	</table>
        <table width="100%">
            <tr>
                <td align="right" class="text" id="connstatus"></td>
            </tr>
        </table>
</div>
<!--{include file='control/footer.tpl'}--> 
</body>
</html> 
