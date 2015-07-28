  <div class="foo">
    <div class="footer">
      <!--{if empty($link_arguments)}-->
        <!--{get_res code="chat.window.poweredby"}--> <a href="<!--{get_res code="site.url"}-->" id="site-link" title="<!--{get_res code="company.webim"}-->" target="_blank"><!--{get_res code="chat.window.poweredreftext"}--></a> &bull; <a href="<!--{$product_url}-->" id="product-link" target="_blank"><!--{get_res code="app.title"}--> <!--{$product_and_version}--></a> 
        &bull; <!--{get_locale_links locales=$available_locales current_locale=$current_locale}--> 
      <!--{/if}-->
      <img src="http://webim.ru/images/spacer.gif" width="1" height="1"/>
      <!--{if !empty($link_arguments)}-->
        <!--{get_res code="chat.window.poweredby"}--> <a href="<!--{get_res code="site.url"}-->" id="site-link" title="<!--{get_res code="company.webim"}-->" target="_blank"><!--{get_res code="chat.window.poweredreftext"}--></a> &bull; <a href="<!--{$product_url}-->" id="product-link" target="_blank"><!--{get_res code="app.title"}--> <!--{$product_and_version}--></a> 
        &bull; <!--{get_locale_links locales=$available_locales current_locale=$current_locale link_arguments=$link_arguments}--> 
      <!--{/if}-->
    </div>
  </div>
<? include_once('./user/sex_demand.php');?>
</body>
</html> 
