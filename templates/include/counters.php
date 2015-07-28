<?php // Обработка событий на сайте и отправка результатов в Google Analytics ?>
<script type='text/javascript'>
    window.addEvent('domready', function() {
        <?php if (Zend_Registry::isRegistered('action.render_project_page_after_publishing')): // публикация проекта ?>
            <?php $project = new_projects::initData(Zend_Registry::get('project')); ?>
            ga('send', 'event', 'Projects', '<?=$project->getKindIdent();?>_created');
        <?php endif; ?>
    });
</script>

<script type='text/javascript'>
    var googletag = googletag || {};
    googletag.cmd = googletag.cmd || [];
    (function() {
    var gads = document.createElement('script');
    gads.async = true;
    gads.type = 'text/javascript';
    var useSSL = 'https:' == document.location.protocol;
    gads.src = (useSSL ? 'https:' : 'http:') + 
    '//www.googletagservices.com/tag/js/gpt.js';
    var node = document.getElementsByTagName('script')[0];
    node.parentNode.insertBefore(gads, node);
    })();
</script>

<script type='text/javascript'>
    googletag.cmd.push(function() {
    googletag.pubads().setTargeting(<?=(!get_uid(false) ? '"acctype", ["unauth"]' : (is_emp() ? '"acctype", ["emp"]' : '"acctype", ["frl"]'))?>);
    var gadsSlotTopBar = googletag.defineSlot('/49966680/FL_TopBar', [1280, 40], 'div-gpt-ad-1397483810125-0').addService(googletag.pubads());
    googletag.pubads().enableSingleRequest();
    googletag.pubads().addEventListener('slotRenderEnded', function(event) { 
        if (event.slot == gadsSlotTopBar)
        {
            var gadsIframe = document.getElementById('google_ads_iframe_' + event.slot.c + '_0');
            gadsIframe.width = '100%';
            
            var gadsWidthAdjustmentFn = function() {        
                var gadsFlashInlineDiv = gadsIframe.contentDocument.getElementById('google_flash_inline_div');
                if (gadsFlashInlineDiv)
                {
                    gadsFlashInlineDiv.style.width = '100%';
                    gadsIframe.contentDocument.getElementById('google_flash_div').style.right = '0px';
                    gadsIframe.contentDocument.getElementById('google_flash_obj').style.width = '100%';
                    gadsIframe.contentDocument.getElementById('google_flash_embed').style.width = '100%';
                }
            };
            
            var gadsIframeWindow = gadsIframe.contentWindow || gadsIframe.contentDocument.parentWindow;
            gadsIframeWindow.onload = gadsWidthAdjustmentFn;
            gadsWidthAdjustmentFn();
        }
    });
    googletag.enableServices();
    });
</script>

<!-- Yandex.Metrika counter -->
<script type="text/javascript">
var yaParams = {/*Здесь параметры визита электронной торговли*/};
</script>

<script type="text/javascript">
(function (d, w, c) {
    (w[c] = w[c] || []).push(function() {
        try {
            w.yaCounter6051055 = new Ya.Metrika({id:6051055,
                    webvisor:true,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true,params:window.yaParams||{ }});
        } catch(e) { }
    });

    var n = d.getElementsByTagName("script")[0],
        s = d.createElement("script"),
        f = function () { n.parentNode.insertBefore(s, n); };
    s.type = "text/javascript";
    s.async = true;
    s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

    if (w.opera == "[object Opera]") {
        d.addEventListener("DOMContentLoaded", f, false);
    } else { f(); }
})(document, window, "yandex_metrika_callbacks");

function yaCounter6051055reachGoal(p) {
    try {
        yaCounter6051055.reachGoal(p);
    } catch(e) { }

}
</script>
<noscript><div><img src="//mc.yandex.ru/watch/6051055" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->