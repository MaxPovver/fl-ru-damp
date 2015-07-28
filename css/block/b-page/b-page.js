window.addEvent('domready', 
function() {
    
    $$('.b-ext-filter__slide').addEvent('click',function(){
			$$('.b-ext-filter__slide').toggleClass('b-ext-filter__slide_hide');
			$$('.b-ext-filter').toggleClass('b-ext-filter_hide');
            flt_updateCookie();
			return false;
	});
    
    $$('.b-menu__filter_switcher').addEvent('click',function(){
			$$('.b-ext-filter__slide').toggleClass('b-ext-filter__slide_hide');
			$$('.b-ext-filter').toggleClass('b-ext-filter_hide');
            flt_updateCookie();
			return false;
		});    
    
    initPBlock();
});


function initPBlock() {
    $$('.b-promo__slide').getElement('.b-promo__link').addEvent('click', function(){ mainPromoToggle(this); });
	
    /*if (Cookie.read('nfastpromo')) {
        cpromo = JSON.decode(Cookie.read('nfastpromo'));
        if(cpromo.state == 0 && $('main_link_promo_tgl') != undefined) {
            mainPromoToggle($('main_link_promo_tgl'));
        }
    } else if($('main_link_promo_tgl') != undefined) {
        mainPromoToggle($('main_link_promo_tgl'));
    }*/    
    
	$$('.b-post__close').addEvent('click',function(){
			this.getParent('.b-post').addClass('b-post_hide');
		})
        
	if($('post-rolling'))	
        $('post-rolling').addEvent('click',function(){
            rollPrj();
            return false;
        })
    
    if($('post-opening'))
        $('post-opening').addEvent('click',function(){
            openPrj();
            return false;
        })
    
    rollProjects();

    /**
     * сворачивание/разворачивание и закрытие промо-блока
     */
    function mainPromoToggle(el) {
        
        if ( promoDisableToogle ) {
            return false;
        }
        
        // ЗАКРЫТИЕ
        if(el.getParent('.b-promo__slide').hasClass('b-promo__slide_close')) {
            
            // подготовка анимации
            var promoMain = $$('.b-promo_main')[0];
            var promoFxClose = new Fx.Morph(promoMain, {
                duration: 'long',
                onComplete: function () {
                    $$('.b-promo_main').addClass('b-promo_hide');
                    // мигаем кнопкой настройки
                    blinkSettingsButton();
                }
            })
            /*var promoFxCarusel = new Fx.Morph('pay_place_carusel', {
                duration: 'long'
            })*/
            var layoutPage = $$('.b-layout__page')[0];
            var promoFxPage = new Fx.Morph(layoutPage, {
                duration: 'long'
            })
            // процесс закрытия
            promoMain.setStyle('overflow', 'hidden');
            promoFxClose.start({height: [116, 0]});
            var carStart = $('pay_place_carusel').getStyle('top').toInt();
            //promoFxCarusel.start({'top': [carStart, carStart - 116]});
            var layoutStart = $$('.b-layout__page')[0].getStyle('margin-top').toInt();
            promoFxPage.start({'margin-top': [layoutStart, layoutStart - 116]});
            
        // СВОРАЧИВАНИЕ/РАЗВОРАЧИВАНИЕ
        } else {
            // текущее состояние промо-блока
            var promoState = $('b-promo__main-inner').hasClass('b-promo_height_80') ? 'min' : 'max';
            //*************************
            // анимация при сворачивании/разворачивании
            var promoFx = new Fx.Morph('b-promo__main-inner', {
                duration: 'long',
                onComplete: function () {
                    if (promoState === 'max') {
                        $$('.b-promo__main-inner').toggleClass('b-promo_height_80');
                        $$('.b-promo__main-block').addClass('b-layout_hide'); // содержимое промо блока
                        $$('#promo-close-forever').removeClass('b-promo__slide_hide'); // кнопка закрыть совсем
                    }
                    if (promoState === 'min') {
                        $$('.b-promo__main-inner').removeClass('b-promo_overflow_hidden');
                    }
                    promoDisableToogle = false;
                }
            })
            var promoFxCarusel = new Fx.Morph('pay_place_carusel', {
                duration: 'long'
            })
            var layoutPage = $$('.b-layout__page')[0];
            var promoFxPage = new Fx.Morph(layoutPage, {
                duration: 'long'
            })
            //***************************
            
            if (promoState === 'max') { // сейчас развернут, будем сворачивать
                $$('#promo-minimize').addClass('b-promo__slide_hide'); // кнопка свернуть
                $$('#promo-maximize').removeClass('b-promo__slide_hide'); // кнопка развернуть
                promoFx.start({'height': [360, 80]}); // анимация промоблока
                var carStart = $('pay_place_carusel').getStyle('top').toInt();
                promoFxCarusel.start({'top': [carStart, carStart - 280]}); // анимация карусели (сдвиг вверх)
                var layoutStart = $$('.b-layout__page')[0].getStyle('margin-top').toInt();
                promoFxPage.start({'margin-top': [layoutStart, layoutStart - 280]}); // анимация основного контента страницы
                $$('.b-promo__main-inner').toggleClass('b-promo_overflow_hidden');
                
            } else if (promoState === 'min') { // сейчас свернут, будем разворачивать
                $$('.b-promo__main-block').removeClass('b-layout_hide'); // содержимое промо-блока
                $$('#promo-minimize').removeClass('b-promo__slide_hide');
                $$('#promo-maximize').addClass('b-promo__slide_hide');
                $$('#promo-close-forever').addClass('b-promo__slide_hide');
                promoFx.start({'height': [80, 360]});
                var carStart = $('pay_place_carusel').getStyle('top').toInt();
                promoFxCarusel.start({'top': [carStart, carStart + 280]});
                var layoutStart = $$('.b-layout__page')[0].getStyle('margin-top').toInt();
                promoFxPage.start({'margin-top': [layoutStart, layoutStart + 280]});
                $$('.b-promo__main-inner').toggleClass('b-promo_height_80');
            }
            promoDisableToogle = true;
        }
        return false;
    }
}

var promoDisableToogle = false;

function promoSaveCookie(st) {
    cpromo = nfastGetCookie();
    Object.append(cpromo, {
        state: st
    });
    
    Cookie.write('nfastpromo_x', JSON.encode(cpromo), {
        duration: 365
    });
    
    cpromo = nfastGetCookie();
}


function mainPromoClose() {
    cpromo = nfastGetCookie();
    
    Object.append(cpromo, {
        close: 1
    });
    
    Cookie.write('nfastpromo_x', JSON.encode(cpromo), {
        duration: 365
    });
    Cookie.write('nfastpromo_open', 0, {
        duration: 365
    });
    
    //$('mainPromo').destroy();
    
    return false;
}

function flt_updateCookie() {
    var fbox = $('b_ext_filter');
    fbox.f_isShw = ! fbox.hasClass('b-ext-filter_hide');
    var d = new Date();
    d.setMonth(d.getMonth() + 1);
    document.cookie='new_pf'+fbox.getAttribute('page')+'='+(fbox.f_isShw-0)+'; expires='+d.toGMTString() + '; path=/';
}
        

// проверяет должны ли проекты быть свернутыми и если доллжны - сворачивает их
function rollProjects () {
    // проверяем свернутость проектов
    var lentaMode = Cookie.read('lentaMode');
    if (lentaMode != 'roll') return; // если развернутый режим
    rollPrj();
}

// свернуть проекты
function rollPrj () {
    var btn;
    if (!(btn = $('post-rolling'))) return;
    Cookie.write('lentaMode', 'roll');
    btn.getParent('.b-layout__right').getElement('.b-page__lenta').getElements('.b-post__body').addClass('b-post__body_hide');
    btn.getParent('.b-layout__right').getElement('.b-page__lenta').getElements('.b-post__foot').addClass('b-post__foot_hide');
    btn.getParent('.b-layout__right').getElement('.b-page__lenta').getElements('.b-post__pic').removeClass('b-post__pic_clear_right');
    btn.getParent('.b-layout__right').getElement('.b-page__lenta').getElements('.project_logo_wrap').addClass('b-post_hide');
    btn.getParent('.b-page__title').getElement('.b-icon__pt').removeClass('b-icon__pt_dis');
    btn.getParent('.b-page__title').getElement('.b-icon__pf').addClass('b-icon__pf_dis');
}
// развернуть проекты
function openPrj () {
    var btn;
    if (!(btn = $('post-opening'))) return;
    Cookie.write('lentaMode', 'open');
    btn.getParent('.b-layout__right').getElement('.b-page__lenta').getElements('.b-post__body').removeClass('b-post__body_hide');
    btn.getParent('.b-layout__right').getElement('.b-page__lenta').getElements('.b-post__foot').removeClass('b-post__foot_hide');
    btn.getParent('.b-layout__right').getElement('.b-page__lenta').getElements('.b-post__pic').addClass('b-post__pic_clear_right');
    btn.getParent('.b-layout__right').getElement('.b-page__lenta').getElements('.project_logo_wrap').removeClass('b-post_hide');
    btn.getParent('.b-page__title').getElement('.b-icon__pf').removeClass('b-icon__pf_dis');
    btn.getParent('.b-page__title').getElement('.b-icon__pt').addClass('b-icon__pt_dis');
}

