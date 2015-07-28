<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><!--{$visit_session.visitorname}--> (<!--{$visit_session.ip}--><!--{if $visitor_geodata}--> <!--{$visitor_geodata.city}-->, <!--{$visitor_geodata.country}--><!--{/if}--><!--{if $visit_session.remotehost}--> <!--{$visit_session.remotehost}--><!--{/if}-->)</title>
	<meta http-equiv="Content-Type" content="text/html; charset=<!--{$browser_charset}-->" />
  <style media="all" type="text/css">@import "<!--{$webim_root}-->/css/admin_window.css?<!--{$version}-->";</style>
  <link rel="shortcut icon" href="<!--{$webim_root}-->/images/favicon.ico" type="image/x-icon"/>
</head>
<body>
	<div id="pagewidth">
		<div class="row" id="h">
			<div id="header">
				<a id="thread-close" href="#" class="close" title="<!--{get_res code="close.window"}-->">close</a>
				<p id="powered-by"><!--{get_res code="chat.window.poweredby"}--> <a id="powered-by-lnk" href="<!--{get_res code='site.url'}-->" title="<!--{get_res code='company.webim'}-->" target="_blank"><!--{get_res code="chat.window.poweredreftext"}--></a></p>
				<h1><!--{get_res code="company.webim"}--></h1>
			</div>
		</div>
		<div class="row">
			<div class="messagesent">
        <p><!--{get_res code="chat.redirected.content"}--></p>
				<a href="javascript:window.close();" class="btn-close"><!--{get_res code="chat.redirected.closewindow"}--></a>
			</div>
		</div>
	</div>
</body>
</html>