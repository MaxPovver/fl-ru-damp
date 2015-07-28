 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <meta http-equiv="Content-Type" content="text/html; charset=<!--{$browser_charset}-->" />
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
	<title><!--{get_res code="app.title"}--> &#8212; <!--{get_res code="$title_key"}--></title> 
    <link rel="stylesheet" type="text/css" media="all" href="<!--{$webim_root}-->/css/admin_styles.css?<!--{$version}-->" />
    <script type="text/javascript" language="javascript" src="<!--{$webim_root}-->/js/init.js?<!--{$version}-->"></script>    
    <script type="text/javascript" language="javascript" src="<!--{$webim_root}-->/js/common.js?<!--{$version}-->"></script>
    <script type="text/javascript" language="javascript">
        WM_initVisitorList("<!--{get_res code="active.visits.invite"}-->", " <!--{get_res code="active.visits.no.visitors.pro"}-->  ",
            "<!--{get_res code="active.visits.view.visit.info"}-->", 
            "<!--{get_res code="pending.table.landing.page"}-->", "<!--{get_res code="pending.table.exit.page"}-->",
            "<!--{$webim_root}-->/operator/onsite.php",
            "<!--{$webim_root}-->", "<!--{$visit_details}-->", "<!--{$whois_url}-->");
    </script>
    <script type="text/javascript" language="javascript" src="<!--{$webim_root}-->/js/visitor_list.js?<!--{$version}-->"></script> 
    <link rel="shortcut icon" href="<!--{$webim_root}-->/images/favicon.ico" type="image/x-icon"/>
</head> 
<!--{include file='control/body_start.tpl'}-->
<div class="menu">
	<!--{include file='control/top_block.tpl'}-->
	<!--{get_res code="active.visits.intro"}-->
	<!--{get_res code="active.visits.how_to"}-->
</div>
<div class="visitors-table">
	<table class="pending-visitors" id="tracklist">
		<tr>
			<th><!--{get_res code="active.visits.table.head.name"}--></th>
			<th><!--{get_res code="active.visits.table.head.current.page"}--></th>
			<th><!--{get_res code="pending.table.head.address"}--></th>
			<th><!--{get_res code="active.visits.table.head.current.page.time"}--></th>
			<th><!--{get_res code="active.visits.table.head.browser.info"}--></th>
			<th><!--{get_res code="active.visits.table.head.locale"}--></th>
			<th class="last"><!--{get_res code="pending.table.head.etc"}--></th>
		</tr>
		<tr id="users">
			<td colspan="7"><img src='<!--{$webim_root}-->/images/tblicusers.gif' alt="" /><!--{get_res code="active.visits.queue"}--></td>
		</tr>
		<tr id="usersend">
			<td colspan="7" id="status"></td>
		</tr>
	</table>
	<p id="connstatus"></p>
</div> 
<!--{include file='control/footer.tpl'}-->