<div id="pskb-frame-bg" class="b-shadow__overlay b-shadow__overlay_bg_black b-shadow_hide"></div>
<div id="pskb-frame" class="b-shadow b-shadow_width_780 b-shadow_zindex_110 b-shadow_center_top b-shadow_hide">
    <div class="b-shadow__right">
        <div class="b-shadow__left">
            <div class="b-shadow__top">
                <div class="b-shadow__bottom">
                    <div class="b-shadow__body b-shadow__body_bg_fff">
                        <iframe width="100%" height="420" scrolling="auto" frameborder="0" src="about:blank"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <span class="b-shadow__icon b-shadow__icon_close" onclick="pskb_frame_close(<?= intval($disable_reload) ?>);"></span>
</div>
<script>
    // Перекидываем в body
    document.body.appendChild(document.getElementById('pskb-frame'));
    
    var pskb_frame = function(id, sgn) {
        if (!parseInt(id)) {
            return false;
        }
        if (!sgn) {
            return false;
        }
        var f = $('pskb-frame');
        var bg = $('pskb-frame-bg');
        if (!bg || !f) {
            return false;
        }
        
        f.getElement('.b-shadow__icon_close').removeEvents('click');
        $$('#pskb-frame, #pskb-frame-bg').removeClass('b-shadow_hide');
        f.getElement('iframe').set('src', '<?= pskb::getCardsFrameUrl() ?>' + id + '&sign=' + sgn);
    };

    var pskb_frame_close = function(no_reload) {
        document.location.reload();
    };
</script>