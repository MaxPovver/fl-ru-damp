var coords;
var fixPoint;
var footPoint = 0;
var scrolled;

function fix_banner() {
    var banWrap = document.getElementById('banner_wrap');
    var bfix = document.getElementById('b-banner_fix');
    
    var spec = document.getElementById('specialis');
    var foot = document.getElementById('i-footer');
    var seo_block = document.getElementById('seo_block');
    
    if (spec) {
        footPoint = spec.getCoordinates().top;      // коодната элемента над подвалом
    } else if (foot) {
        footPoint = foot.getCoordinates().top;		// координата подвала
    }

    if (bfix) {
        bfixH = bfix.offsetHeight; // высота банера
        coords = banWrap.getCoordinates().top - 80; // статичная координата, где стоит банер

        fixPoint = footPoint - bfixH - 80;  // верхняя координата, от которой банер прижимается к подвалу

        function scrolBanner() {
            var offsetLeft = (banWrap.offsetWidth - 240) / 2;
            scrolled = window.pageYOffset || document.documentElement.scrollTop; // высота прокрутки вверх
            if (coords < scrolled) {
                bfix.setStyle('margin-left', offsetLeft + 'px');
                if (fixPoint+9 > scrolled) {
                    if (fixPoint > coords+130) {
                        bfix.addClass('b-banner_fixed');
                        bfix.removeClass('b-banner_abs');
                        bfix.setStyle('top', '');
                        if (seo_block) seo_block.hide();
                    }
                } else if (fixPoint > coords+130) {
                    bfix.addClass('b-banner_abs');
                    bfix.removeClass('b-banner_fixed');
                    bfix.setStyle('top', fixPoint - coords-25);
                }
            } else {
                bfix.removeClass('b-banner_fixed');
                bfix.setStyle('top', '');
                bfix.setStyle('margin-left', '');
                if (seo_block) seo_block.show();
            }
        }
        scrolBanner();
    }
}

fix_banner();

window.addEvent('resize', fix_banner);




