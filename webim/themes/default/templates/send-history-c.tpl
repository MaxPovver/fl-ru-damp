<!--{include file='control/head.tpl'}--> 
<div class="header">
    <!--{include file='control/header_inner.tpl' close_class="window-close-simple"}-->
    <h1><a href="<!--{$url}-->" onclick="window.open('<!--{$url}-->');return false;"><img src="<!--{$logo}-->" alt="<!--{$company}-->" /></a></h1>
    <h2><!--{get_res code="consult.title"}--> / <!--{get_res code="consult.title5"}--></h2>
</div>
<div class="content">
    <form id="thread-mail-form" action="/webim/mail.php" method="post">
        <div class="webim-form">
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in c">
                <p><!--{get_res code="send.history"}--></p>
                
                <p id="error-email" class="<!--{if isset($erroremail_from)}-->error<!--{else}-->error-hidden<!--{/if}-->"><strong><!--{get_res code="enter.email"}--></strong></p>	 
                <p id="error-email-format" class="<!--{if isset($erroremailformat_from)}-->error<!--{else}-->error-hidden<!--{/if}-->"><strong><!--{get_res code="wrong.email.format"}--></strong></p>
                
                <div class="form-el c">
                    <label class="form-label"><!--{get_res code="mailthread.enter_email_from"}--></label>
                    <span class="form-ctrl">
                        <input id="email_from" type="text" name="email_from" value="<!--{$email_from}-->" tabindex="1" style="width: 320px;"/>
                    </span>
                </div>
                
                <div class="form-el c">
                    <label class="form-label"><!--{get_res code="mailthread.enter_dept"}--></label>
                    <span class="form-ctrl">
                        <select name="dept" id="dept" style="width: 324px;">
                        <!--{foreach from=$depts key=myId item=aOne}-->
                        <option value="<!--{$aOne.value}-->"><!--{$aOne.title}--></option>
                        <!--{/foreach}-->
                        </select>
                    </span>
                </div>
                
                <input id="mode" type="hidden" name="mode" value="cons"/>
                <input id="threadid" type="hidden" name="threadid" value="<!--{$threadid}-->"/>
                <input id="token" type="hidden" name="token" value="<!--{$token}-->"/>
                <input id="level" type="hidden" name="level" value="<!--{$level}-->"/>
            </div> 
            <b class="b1"></b>
            <b class="b2"></b>
        </div>
        <div class="webim-btn">
            <input type="image" tabindex="2" src="/webim/themes/<!--{$theme}-->/img/btn-send.png" value="<!--{get_res code="send"}-->" />
        </div>
    </form>
</div>
<!--{include file='control/foo.tpl'}-->