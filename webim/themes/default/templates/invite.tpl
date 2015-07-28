<!-- webim visitors tracker body -->
<a onclick="return openChat('','','','');" class="webim-invite" href="#">
	<span class="webim-t"></span>
	<span class="webim-c">
    <!--{if $operatorimage}-->
		  <span class="webim-im">
			  <img id="webim-operator-image" src="<!--{$operatorimage}-->" />
			  <em>&nbsp;</em>
		  </span>
    <!--{/if}-->
		<strong>
			<span id="webim-invatation-message"><!--{$message}--></span>
		</strong>
	</span>
	<span class="webim-b"></span>
</a>
<img src="<!--{$addressprefix}-->/webim/themes/<!--{$theme}-->/images/closewin.gif" style="" onclick="closeInvitation();" class="webim-close" />
<!-- /webim visitors tracker body -->