<!--{include file='control/head.tpl'}--> 
		<div class="header">
			<!--{include file='control/header_inner.tpl' close_class="window-close"}-->
			<h1><a href="<!--{$url}-->" onclick="window.open('<!--{$url}-->');return false;"><img src="<!--{$logo}-->" alt="<!--{$company}-->" /></a></h1>
			<h2><!--{get_res code="consult.title"}--> / <!--{get_res code="consult.title2"}--></h2>
		</div>
		<div class="content" id="chat-ajaxed">
			<script type="text/javascript" language="javascript">
				WM_initVisitorChat("<!--{get_res code="turn.on.sound"}-->", "<!--{get_res code="turn.off.sound"}-->",
					"<!--{$webim_root}-->", <!--{$threadid}-->, <!--{$token}-->, 
					"<!--{$webim_root}-->/themes/<!--{$theme}-->/css/frame.css", "<!--{$whois_url}-->");
			</script>
			<div class="webim-form">
				<b class="b1"></b>
				<b class="b2"></b>
				<div class="form-in c">
					<ul class="chat-btns">
						<li><a id="sound-control" href="#" class="action-button"><span id="sound-control-span"></span></a></li>
						<li><a id="thread-send" href="#" class="action-button" title="<!--{get_res code="chat.window.toolbar.mail_history"}-->"><img src="<!--{$webim_root}-->/themes/<!--{$theme}-->/images/mail.gif" alt="<!--{get_res code="alt.send_mail"}-->" /></a></li>
						<li><a id="thread-refresh" href="#" class="action-button" title="<!--{get_res code="chat.window.toolbar.refresh"}-->"><img src="<!--{$webim_root}-->/themes/<!--{$theme}-->/images/reload.gif" alt="<!--{get_res code="alt.refresh"}-->" /></a></li>
					</ul>
					<div id="rate-panel" class="rate" style="display:none;">
						<div id="thread-rate" class="disabled">
							<select id="thread-rate-select" disabled="disabled">
								<option><!--{get_res code="rate.operator"}--></option>
								<option value="2"><!--{get_res code="rate.2"}--></option>
								<option value="1"><!--{get_res code="rate.1"}--></option>
								<option value="-1"><!--{get_res code="rate.-1"}--></option>
								<option value="-2"><!--{get_res code="rate.-2"}--></option>
							</select>
							<input id="thread-rate-btn" style="float:right;" type="image" src="<!--{$webim_root}-->/themes/<!--{$theme}-->/images/btn-go.gif" alt="<!--{get_res code="alt.rate"}-->" />
						</div>
					</div>
					<p><strong><span id="operator-name">&nbsp;</span> <span id="thread-typing" class="composing"></span></strong></p>
				</div>
				<b class="b2"></b>
				<b class="b1"></b>
			</div>
			<div class="webim-form">
				<b class="b1"></b>
				<b class="b2"></b>
				<div class="form-in c">
					<div class="form-el c">
						<div id="histry">
							<iframe id="thread" width="100%" height="100%" src="<!--{if $userAgent|stristr:'safari/'}--><!--{$webim_root}-->/blank.html<!--{/if}-->" frameborder="0"></iframe>
						</div>
					</div>
					<div class="form-el c form-visitor">
						<!--{if $canChangeName}-->
									<a href="#" id="visitor-name-lnk"><!--{$name}--></a>
									<div id="visitor-name">
										<input id="visitor-name-field" type="text" class="txt" value="<!--{$name}-->" maxlength="40"/>
										<input id="visitor-name-btn" type="image" src="<!--{$webim_root}-->/themes/<!--{$theme}-->/images/btn-go.gif"  alt="<!--{get_res code="alt.change_name"}-->" />
									</div>
						<!--{else}-->
							<span><!--{$name}--></span>
						<!--{/if}-->
					</div>
					<div class="form-el c">
						<span class="form-ctrl2">
							<textarea id="message-area" tabindex="1" cols="20" rows="10"></textarea>
						</span>
					</div>
					<div id="message-contacts" style="display:none;">
						<div class="form-el c">
							<label class="form-label" for="contacts-name"><!--{get_res code="your.name"}--></label>
							<span class="form-ctrl">
								<input id="contacts-name" type="text"  value="<!--{$fl_name}-->" tabindex="3"/>
							</span>
						</div>
						<div class="form-el c">
							<label class="form-label" for="contacts-email"><!--{get_res code="email.address"}--></label>
							<span class="form-ctrl">
								<input id="contacts-email" type="text" value="<!--{$fl_email}-->" tabindex="4"/>
							</span>
						</div>
						<div class="form-el c" style="display:none;">
							<label class="form-label" for="contacts-phone"><!--{get_res code="phone"}--></label>
							<span class="form-ctrl">
								<input id="contacts-phone" type="text"  tabindex="5"/>
							</span>
						</div>
					</div>
				</div>
				<b class="b2"></b>
				<b class="b1"></b>
			</div>
			<div class="webim-btn">
				<span>Пожалуйста, <a target="parent" href="/about/evaluate/<!--{$threadid}-->_<!--{$visitorid}-->/">оставьте свой отзыв</a> о работе консультанта</span>
				<input id="message-send" type="image" tabindex="2" src="/webim/themes/<!--{$theme}-->/img/btn-send.png" value="<!--{get_res code="send"}-->" />
			</div>
		</div>
<!--{include file='control/foo.tpl'}-->