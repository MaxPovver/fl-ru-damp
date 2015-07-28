<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><!--{$visit_session.visitorname|escape:'html'}--> (<!--{$visit_session.ip}--><!--{if $visitor_geodata}--> <!--{$visitor_geodata.city}-->, <!--{$visitor_geodata.country}--><!--{/if}--><!--{if $visit_session.remotehost}--> <!--{$visit_session.remotehost}--><!--{/if}-->)</title>
  <link rel="shortcut icon" href="<!--{$webim_root}-->/images/favicon.ico" type="image/x-icon"/>
  <meta http-equiv="Content-Type" content="text/html; charset=<!--{$browser_charset}-->" />

  <style media="all" type="text/css">@import "<!--{$webim_root}-->/css/admin_window.css?<!--{$version}-->";</style>
  <script type="text/javascript" language="javascript" src="<!--{$webim_root}-->/js/main.js?<!--{$version}-->"></script>
  <script type="text/javascript" language="javascript" src="<!--{$webim_root}-->/js/common.js?<!--{$version}-->"></script>
  <script type="text/javascript" language="javascript" src="<!--{$webim_root}-->/js/brws.js?<!--{$version}-->"></script>
  <script type="text/javascript" language="javascript" src="<!--{$webim_root}-->/js/init.js?<!--{$version}-->"></script>
  <script type="text/javascript" language="javascript" src="<!--{$webim_root}-->/js/chat.js?<!--{$version}-->&e=1"></script>
  <script type="text/javascript" language="javascript" src="<!--{$webim_root}-->/js/redirect_op.js?<!--{$version}-->"></script>
  <script type="text/javascript" language="javascript">
      WM_initOperatorChat("<!--{get_res code="sound.on.title"}-->", "<!--{get_res code="sound.off.title"}-->",
          "<!--{get_res code="chat.agent.push.url.prompt"}-->", "<!--{get_res code="chat.agent.confirm.closing.thread"}-->",
          "<!--{$webim_root}-->", <!--{$thread.threadid}-->, 
          <!--{$thread.token}-->, <!--{if $mode eq 'viewonly'}-->1<!--{else}-->0<!--{/if}-->,
          "<!--{$webim_root}-->/css/admin_chat.css", "<!--{$whois_url}-->");
  </script>

</head>
<body id="chat-ajaxed">
	<div id="pagewidth">
		<div class="row" id="h">
			<div id="header">
				<a id="thread-close" href="#" class="close" title="<!--{get_res code="close.window"}-->">close</a>
				<p id="powered-by"><!--{get_res code="chat.window.poweredby"}--> <a id="powered-by-lnk" href="<!--{get_res code='site.url'}-->" title="<!--{get_res code='company.webim'}-->" target="_blank"><!--{get_res code="chat.window.poweredreftext"}--></a></p>
				<div class="user-info">
					<h1>
            <!--{if $visitor_name_link }-->
                <a id="visitor-name-lnk" href="/users/<!--{$visit_session.fl_login|escape:'html'}-->" target="_blank" title="<!--{get_res code='active.visits.visit.info'}-->" ><!--{$visitor_name}--> </a>&nbsp;
            <!--{else}-->
                <a id="visitor-name-lnk" href="/users/<!--{$visit_session.fl_login|escape:'html'}-->" target="_blank" title="<!--{get_res code='chat.window.visitor.dialogs'}-->" ><!--{$visitor_name}--><!--{if $visit_session.fl_login}--> [<!--{$visit_session.fl_login|escape:'html'}-->]<!--{/if}--> </a>&nbsp;
            <!--{/if}-->
              <a target="_blank" href="<!--{$whois_url}--><!--{$visit_session.ip}-->">
                <!--{get_res code="chat.window.ip"}-->: <!--{$visit_session.ip}--> 
                <img height="16" width="16" border="0" src="/webim/images/whois.gif"/>
              </a>
					</h1>
			<span class="composing" id="thread-typing"></span>
              <!--{if $visit_session.remotehost }-->
			  <ul class="chat-info">
				<li><!--{get_res code="chat.window.remotehost"}-->: <!--{$visit_session.remotehost}--></li>
			  </ul>
              <!--{/if}-->
					<ul class="chat-info">
            <li><!--{get_res code="chat.window.id"}-->: <!--{$visit_session.visitorid}--></li>
            <!--{if ($browser)}-->
              <li><!--{get_res code="chat.window.browser"}-->: <!--{$browser}--></li>
            <!--{/if}-->
            <!--{if ($chats_count)}-->
              <li>
                <a href="<!--{add_params servlet_root=$servlet_root servlet=$history_servlet path_vars=$history_params}-->" target="_blank"><!--{get_res code="chat.window.chats"}-->: <!--{$chats_count}--></a>
              </li>
            <!--{/if}-->
          </ul>
          <!--{if $visitor_geodata}-->
          <ul class="chat-info">
              <li>
                <!--{get_res code="chat.window.geolocation"}-->
                <a href="http://maps.google.com/maps?q=<!--{$visitor_geodata.lat}-->, <!--{$visitor_geodata.lng}-->" target="_blank">
                  <!--{$visitor_geodata.city}-->, <!--{$visitor_geodata.country}-->
                </a>
              </li>
          </ul>
          <!--{/if}-->		  
          <div id="connection-status" style="display:none;">
             <img src="<!--{$webim_root}-->/images/redpoint.png"/>
          </div>
          <div>
             <a id="sound-control" href="#" class="action-button"><span id="sound-control-span"></span></a>
          </div>
				</div>
			</div>
		</div> 
		<!--{if $mode eq 'viewonly'}-->
    <div>
		<!--{else}-->
    <div class="pro">
		<!--{/if}-->  
      <!--{if $mode != 'viewonly'}-->
			<div id="sidebar">
				<ul>
          <li><a id="redirect" href="#" title="<!--{get_res code='chat.window.toolbar.redirect_visitor'}-->"><!--{get_res code='chat.window.toolbar.redirect_visitor'}--></a></li> 
					<li><a id="pushurl" href="#" title="<!--{get_res code='chat.window.toolbar.push.url'}-->"><!--{get_res code='chat.window.toolbar.push.url'}--></a></li>
					<li><a id="requestcontacts" href="#" title="<!--{get_res code='chat.window.toolbar.request.contacts'}-->"><!--{get_res code='chat.window.toolbar.request.contacts'}--></a></li>
                                        <li><a onclick="this.newWindow = window.open('<!--{$snd_uri}-->', 'ForwardMail', 'toolbar=0, scrollbars=0, location=0, statusbar=1, menubar=0, width=528, height=342, resizable=0'); if (this.newWindow != null) {this.newWindow.focus();this.newWindow.opener=window;}return false;" id="mailhistory" href="#" title="<!--{get_res code='chat.window.toolbar.mail_history'}-->"><!--{get_res code='chat.window.toolbar.mail_history'}--></a></li>
					 
				</ul>
			</div>
			<!--{/if}--> 
			<div class="row">
				<div id="histry">
					<a href="#" class="btn-history"><!--{get_res code='chat.window.history'}--></a>
					<div class="iframe">
						<div class="box">
							<iframe id="thread" width="100%" height="100%" src="<!--{if $userAgent|stristr:'safari/'}--><!--{$webim_root}-->/blank.html<!--{/if}-->" frameborder="0"></iframe>
						</div>
					</div>
				</div>
			</div>
			<div class="row" id="m"> 
        <!--{if $mode != 'viewonly'}-->
				<div class="message">
					<a href="#" id="btn-message"><!--{get_res code='chat.window.send_message_short' 0='${send_shortcut}'}--></a>
					<div class="textarea">
						<div class="frame">
							<textarea id="message-area" cols="20" rows="10"></textarea>
						</div>
					</div>
				</div>
				<!--{/if}--> 
			</div>
			<div class="row" id="f"> 
        <!--{if $mode != 'viewonly'}-->
				<div id="footer">
					<div class="options">
						<a id="message-send" href="#" class="enter"><!--{get_res code='chat.window.send_message_short' 0=$send_shortcut}--></a>
						<select id="predefined" size="1" class="answer">
							<option><!--{get_res code="chat.window.predefined.select_answer"}--></option>
							<!--{foreach from=$predefined_answers item=it}-->
								<option><!--{$it}--></option>
							<!--{/foreach}-->
						</select>
					</div>
				</div>
				<!--{else}-->
					<div id="footer">
						<div class="options">
							<a href="agent.php?thread=<!--{$thread.threadid}-->&force=true" class="btn-close"><!--{get_res code="chat.window.force.join"}--></a>
						</div>
					</div>
				<!--{/if}--> 
			</div>
		</div>
	</div>
	<div style="display:none;" id="fader"></div>
	<div style="display:none;" id="popup"></div>
</body>
</html>