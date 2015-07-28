<!--{include file='control/head.tpl'}--> 
<!--{if $u_token_key}-->
<script type="text/javascript" src="/scripts/mootools-new.js"></script>
<script type="text/javascript" src="/scripts/csrf.js"></script>
<script type="text/javascript">
window.addEvent('domready', function() {
   CSRF('<!--{$u_token_key}-->');
   updateTokenKey.delay(300000);
});
function updateTokenKey() {
    var href = '/u_token_key.php?action=updatetokenkey&token=' + _TOKEN_KEY;
    new Request.JSON({
        url: href,
        onSuccess: function(resp) {
            if(resp && resp.success) {
                if(resp.token) {
                    CSRF_Clear();
                    CSRF(resp.token);
                }
                updateTokenKey.delay(resp.delay);
            }
        }
    }).get();
}
</script>
<!--{/if}-->
		<div class="header">
			<!--{include file='control/header_inner.tpl' close_class="window-close-simple"}-->
			<h1><a href="<!--{$url}-->" onclick="window.open('<!--{$url}-->');return false;"><img src="<!--{$logo}-->" alt="<!--{$company}-->" /></a></h1>
			<h2><!--{get_res code="consult.title"}--> / <!--{get_res code="consult.title6"}--></h2>
		</div>
		<div class="content">
			<form id="start-chat-form" action="<!--{$to_url}-->" method="post">
				<div class="webim-form">
					<b class="b1"></b>
					<b class="b2"></b>
					<div class="form-in c">
						<p><!--{get_res code="start.chat.message"}--></p>
						<!--{if $canChangeName}-->
							<p id="error-name" class="<!--{if isset($errorname)}-->error<!--{else}-->error-hidden<!--{/if}-->"><strong><!--{get_res code="enter.name"}--></strong></p>
							<p id="error-name" class="<!--{if isset($errornameformat)}-->error<!--{else}-->error-hidden<!--{/if}-->"><strong><!--{get_res code="enter.name_format"}--></strong></p>
						<!--{/if}-->
						<p id="error-email-format" class="<!--{if isset($erroremailformat)}-->error<!--{else}-->error-hidden<!--{/if}-->"><strong><!--{get_res code="wrong.email.format"}--></strong></p>
						<p id="error-message" class="<!--{if isset($errormessage)}-->error<!--{else}-->error-hidden<!--{/if}-->"><strong><!--{get_res code="enter.message"}--></strong></p>
						<p id="error-message" class="<!--{if isset($errorcaptcha)}-->error<!--{else}-->error-hidden<!--{/if}-->"><strong><!--{get_res code="wrong.captcha"}--></strong></p>
						<div class="form-el c">
							<label class="form-label" for="visitorname"><!--{get_res code="your.name"}--></label>
							<span class="form-ctrl">
								<!--{if $canChangeName}-->
									<input id="visitorname" tabindex="1" type="text" name="visitorname" value="<!--{$visitorname}-->" maxlength="40"/>
								<!--{else}-->
									<!--{$visitorname}-->
								<!--{/if}-->
							</span>
						</div>
						<div class="form-el c">
							<label class="form-label" for="email"><!--{get_res code="email.address"}--></label>
							<span class="form-ctrl">
								<input id="email" tabindex="2" type="text" name="email" value="<!--{$email}-->" />
							</span>
						</div>
						


						
						<div class="form-el c">
							<label class="form-label" for="captcha">Введите текст:</label>
							<span class="form-ctrl">
								<input id="captcha" tabindex="3" type="text" name="captcha" value="<!--{$captcha_num}-->" />
                                <br/><br/>
                                <img src="/image.php" width="130" height="60" id="rndnumimage" onClick="document.getElementById('rndnumimage').src = '/image.php?r='+Math.random();" style="vertical-align: middle;" />
																																<a href="#" onClick="document.getElementById('rndnumimage').src = '/image.php?r='+Math.random(); return false;">Обновить код</a>
							</span>
						</div>
						<div class="form-el c" style="display: none;">
							<label class="form-label" for="phone"><!--{get_res code="phone"}--></label>
							<span class="form-ctrl">
								<input id="phone" tabindex="3" type="text" name="phone" value="<!--{$phone}-->" />
							</span>
						</div>
						<div class="form-el c" style="display:none;">
							<label class="form-label" for="message-area"><!--{get_res code="message"}--></label>
							<span class="form-ctrl">
								<textarea tabindex="4" id="message-area" cols="20" rows="5" name="message"><!--{get_res code="message2"}--><!--{$message}--></textarea>
							</span>
						</div>
					<input type="hidden" id="submitted" name="submitted" value="1"/>
					</div>
					<b class="b2"></b>
					<b class="b1"></b>
				</div>
				<div class="webim-btn">
					<input type="image" tabindex="5" src="/webim/themes/<!--{$theme}-->/img/btn-start.png" value="<!--{get_res code="start.chat"}-->" />
				</div>
			</form>
		</div>
<!--{include file='control/foo.tpl'}-->
