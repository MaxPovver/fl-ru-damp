<?
// кнопка "Закрыть" нужна не на каждом сплэше
$closeButton = !strpos($tpl_splash, "splash-messages.tpl.php");
?>
<div id="i-shad_wrap" class="i-shadow i-shadow_zindex_110 ">
    <div class="b-shadow b-shadow_width_950 b-shadow_vertical-center b-shadow_main_content" >
        <div class="b-shadow__right">
            <div class="b-shadow__left">
                <div class="b-shadow__top">
                    <div class="b-shadow__bottom">
                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20 b-layout">
                            <? include ($tpl_splash);?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-shadow__tl"></div>
        <div class="b-shadow__tr"></div>
        <div class="b-shadow__bl"></div>
        <div class="b-shadow__br"></div>
        <? if ($closeButton) { ?>
            <a href="javascript:void(0);" onclick="$('b-shadow__overlay').dispose();$('i-shad_wrap').dispose(); return false;"><span class="b-shadow__icon b-shadow__icon_close"></span></a>
        <? } ?>
    </div>
</div>

<div id="b-shadow__overlay"  class="b-shadow__overlay b-shadow__overlay_bg_black"></div>

