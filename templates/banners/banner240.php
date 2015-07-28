<?php seo_start();?>
<?php if($show_facebook): ?>
    <div class="b-social b-social_padbot_30">
        <div id="fb_soc" class="b-social__fb">
            <div id="fb-root"></div>
            <script>(function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s); js.id = id;
                js.src = "//connect.facebook.net/ru_RU/all.js#xfbml=1";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));</script>
            </script>
            <?php if(getOS()=='Macintosh'): ?>
                <div class="b-social__fb-inner"><div class="fb-like-box" data-href="http://www.facebook.com/freelanceru" data-width="250" data-show-faces="true" data-stream="false" data-border-color="#ffffff" data-header="false"></div></div>
                <div style="border-top: 1px solid #D8DEE5;margin: 10px 5px 0;padding: 5px;">
                     <a href="https://www.facebook.com/help/?page=209089222464503" target="_blank" style="color: gray;cursor: pointer;text-decoration: none; font-size:9px;"><i style="background-image: url('https://fbstatic-a.akamaihd.net/rsrc.php/v2/yI/x/1dQf_ATK831.png');background-repeat: no-repeat;  height: 14px;width: 14px; margin-right:5px; float:left"></i>Социальный плагин Facebook</a>
                </div>
            <?php else: ?>
                <div class="b-social__fb-inner">
                    <div class="fb-like-box" data-href="http://www.facebook.com/freelanceru" data-width="250" data-show-faces="true" data-stream="false" data-border-color="#ffffff" data-header="false"></div>
                </div>
                <div style="background:#fff;padding: 10px 0 0 5px;">
                    <div style="border-top: 1px solid #D8DEE5;padding: 5px;">
                         <a href="https://www.facebook.com/help/?page=209089222464503" target="_blank" style="color: gray;cursor: pointer;text-decoration: none; font-size:9px;"><i style="background-image: url('https://fbstatic-a.akamaihd.net/rsrc.php/v2/yI/x/1dQf_ATK831.png');background-repeat: no-repeat;  height: 14px;width: 14px; margin-right:5px; float:left"></i>Социальный плагин Facebook</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
<?= seo_end();?>
<div id="banner_wrap" class="b-layout b-layout_relative">
    <div id="b-banner_fix">
    <?php if (!$is_pro && $show_banner): ?>
        <div class="b-banner b-banner_layout_vert">
            <script type="text/javascript">window.getBannerTargeting = function() { return CUSTOM_TARGET; }</script>
            <div id="banner_right_side" data-sid="<?=BANNER_ADRIVER_SID?>"></div>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_center">
                <noindex>
                    <a rel="nofollow" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" href="https://www.adeasy.ru/website/fl.ru/2064" target="_blank">
                        Реклама на этом месте
                    </a>
                </noindex>
            </div>
        </div>
    <?php endif; ?>
    </div>
</div>