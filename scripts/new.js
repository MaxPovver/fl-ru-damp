
//  Добавляем для Element метод setFocus()
Element.implement({
  setFocus: function(index) {
    this.setAttribute('tabIndex',index || 0);
    this.focus();
  }
});


document.addEvent('domready', function() {

    if($('floginToggle') && $('b-login')) {
        
        if ($('sc_mask')) {
            $('sc_mask').dispose();
        }
        
        msk = new Element('div', {
            'id'  : 'sc_mask',
            styles: {
                'position': 'fixed',
                'height' : document.getScrollSize().y,
                'width'  : document.getScrollSize().x,
                'top'    : 0,
                'left'   : 0,
                'z-index': 8,
                'display': 'none'
            }
        });
        msk.inject($('b-login'), 'before');
        msk.addEvent('click', function() {
            $('floginToggle').fireEvent('click');
        });
        
        $('floginToggle').addEvent('click', function(e) {
            if (!$('b-login').hasClass('b-login_visible')) {
                $('sc_mask').show();
                $$('.b-consultant').setStyle('z-index', 8);
            } else {
                $('sc_mask').hide();
                $$('.b-consultant').setStyle('z-index', 20);
            }
            
            $('b-login').toggleClass('b-login_visible');
            if($('b-login').hasClass('b-login_visible')) {
                $('b-login__text').setFocus();
            }
            return false;
        });
    }

    if ($('top-payed')) {
        initHScroll();
    }

    initCpromo();

//    Cookie.dispose('nfastpromo');

    
    if ($('frl-filters')) {
        frlFiltersInit();
    }
    
    // проверка бюджета проекта
    $$('#pf_cost_from', '#pf_cost_to').addEvent('change', checkBudget);
    
});

// проверка бюджета проекта
// минимальный бюджет не может быть больше максимального
function checkBudget () {
    var from    = $('pf_cost_from').get('value');
    var to      = $('pf_cost_to').get('value');
    if (!isNaN(from) && !isNaN(to) && (from !== '') && (to !== '') && (+from > +to)) {
        alert("Минимальный бюджет не может превышать максимальный!");
        return false;
    }
    return true;
}

function initCpromo() {
    if($('n-h-promo')) {

        $$('#n-h-promo a').addEvent('click', function(noauto) { 

            $$('#n-h-promo li').removeClass('a');
            cls = $(this).getParent('li').className.trim();
            $(this).getParent('li').addClass('a');
            
            $$('.n-fast-frl,.n-fast-emp').removeClass('n-fast-select');
            $$('.n-fast-'+cls).addClass('n-fast-select');

            if(!$('hhh').hasClass('n-fast-hide-tgl')) {
                $$('.n-fast-frl,.n-fast-emp').hide();
                $$('.n-fast-' + cls).show();
            }

            idx = $(this).getParent('ul').getElements('li').indexOf($(this).getParent('li'));
            cpromo = nfastGetCookie();
            Object.append(cpromo, {
                toggler: idx
            });
            Cookie.write('nfastpromo_x', JSON.encode(cpromo), {
                duration: 365
            });

            return false;
        });
        
        cpromo = nfastGetCookie();
        if (cpromo.toggler && $$('#n-h-promo a')[cpromo.toggler]) {
            $$('#n-h-promo a')[cpromo.toggler].fireEvent('click', ['str']);
        }
    }
    
    if ($('hhh')) {
        $$('#hhh .n-fast-nav a').addEvent('click', function (e) {
            
            this.getParent('div').getElements('.n-fast-nav a').removeClass('active');
            this.addClass('active');

            this.getParent('div').getElements('.n-fast-i').hide();
            blockid = this.get('id').replace(/n-fast-(emp|frl)-m(\d+)/, 'n-fast-$1-i$2');

            if ($(blockid)) {
                bid = this.get('id').replace(/n-fast-(emp|frl)-m(\d+)/, '$2');
                btype = this.get('id').replace(/n-fast-(emp|frl)-m(\d+)/, '$1');

                out = {
                    'frl' : bid
                };
                if (btype == 'emp') {
                    out = {
                        'emp' : bid
                    };
                }

                cpromo = JSON.decode(Cookie.read('nfastpromo_x'));

                if (!cpromo) {
                    cpromo = {};
                }

                if (!cpromo.nav) {
                    cpromo.nav = {};
                }

                Object.append(cpromo.nav, out);
                Cookie.write('nfastpromo_x', JSON.encode(cpromo), {
                    duration: 365
                });
                

                $(blockid).show();
            }

            return false;
        });

       
        $$('#hhh .n-fast-hide a').addEvent('click', nfastToggle);
        $$('.close1').addEvent('click', nfastClose);
        
        if (Cookie.read('nfastpromo_x')) {
            cpromo = JSON.decode(Cookie.read('nfastpromo_x'));
            
            if(cpromo.state == 1) {
                nfastToggle();
            }

            if (cpromo.nav) {
                if (cpromo.nav.emp && $('n-fast-emp-m' + cpromo.nav.emp.toInt())) {
                    $('n-fast-emp-m' + cpromo.nav.emp.toInt()).fireEvent('click');
                }
                if (cpromo.nav.frl && $('n-fast-frl-m' + cpromo.nav.frl.toInt())) {
                    $('n-fast-frl-m' + cpromo.nav.frl.toInt()).fireEvent('click');
                }
            }
        } else {
            nfastToggle();
            $$('#hhh .n-fast-nav')[0].getElement('a').fireEvent('click');
            if ($$('#hhh .n-fast-nav').length > 1) {
                $$('#hhh .n-fast-nav')[1].getElement('a').fireEvent('click');
            }
        }
    }
}

function nfastGetCookie() {
    cpromo = {};
    
    if (Cookie.read('nfastpromo_x')) {
        cpromo = JSON.decode(Cookie.read('nfastpromo_x'));
    } 
    
    return cpromo;
}

function nfastToggle() {
    if($('hhh')) {
        cpromo = nfastGetCookie();
        $$('#hhh .n-fast-select').toggle();
        if($('hhh').hasClass('n-fast-hide-tgl')) {
            Object.append(cpromo, {
                state: 1
            });
            $('hhh').removeClass('n-fast-hide-tgl');
            $('hhh').getElement('a').set(
                'html', 
                $('hhh').getElement('a').get('html').replace('Показать', 'Скрыть')
            );
            $$('#hhh .select-role').show();
        } else {
            Object.append(cpromo, {
                state: '0'
            });
            $('hhh').addClass('n-fast-hide-tgl');
            $('hhh').getElement('a').set(
                'html', 
                $('hhh').getElement('a').get('html').replace('Скрыть', 'Показать')
            );
            $$('#hhh .select-role').hide();
        }
        Cookie.write('nfastpromo_x', JSON.encode(cpromo), {
            duration: 365
        });
    }
    return false;
}


function nfastClose() {
    el = $('hhh');
    
    if (!el) {
        return false;
    }
    
    cpromo = nfastGetCookie();
    
    Object.append(cpromo, {
        close: 1
    });
    
    Cookie.write('nfastpromo_x', JSON.encode(cpromo), {
        duration: 365
    });
    
    $('hhh').destroy();
    
    return false;
}


    

	function initHScroll() 
    {
		var lst = $('top-payed');
        
		if (!lst) { 
            return;
        }
        
		var cnt = lst.getParent();
        var tizer_link = lst.getParent('.b-carusel').getElement('.b-carusel__tizer-link');
        var t = lst.getParent('.b-carusel').getElements('.b-carusel-ubtn');
        
        if (t.length == 2) {
            var btns = lst.getParent('.b-carusel').getElements('.b-carusel-ubtn');
        } else {
            var btns = cnt.getParent().getElements('span[class^=b-carusel__]');
        }
        
		var btnl = btns[0];
		var btnr = btns[1];
        
		if (lst.getElements('li').length <= 4) {
			btnl.addClass('b-carusel__prev_disabled');
			btnr.addClass('b-carusel__next_disabled');
            $$('carusel_shadow_right').setStyle('display', 'none');
		} 
        
        $$('carusel_shadow_left').setStyle('display', 'none');
		var all = lst.getElements('li').length;
		var one = lst.getElements('li')[0].getSize();
        
        /*
        @todo: динамический размер работает 
               но я отказался сделал пока в стилях
        if (one && tizer_link) {
            lst.setStyle('width',(all*(one.x + 6)));
        }
        */
       
		var myFx = new Fx.Scroll(cnt, {
			onComplete: function() {

                //****************
                // определяем положение последнего блока карусели
                var lastBlock = $$('#top-payed !^')[0];
                var lastBlockWidth = lastBlock ? lastBlock.getSize().x : null;
                var lastBlockRightPoint = lastBlock.getPosition().x + lastBlockWidth;
                var container = $$('#pay_place_top .b-carusel__inner')[0];
                var containerLeftPoint = container.getPosition().x;
                var containerRightPoint = containerLeftPoint + container.getSize().x;
                if (containerRightPoint > lastBlockRightPoint) {
                    $('carusel_shadow_right').setStyle('display', 'none');
                    btnr.addClass('b-carusel__next_disabled');
                } else {
                    $('carusel_shadow_right').setStyle('display', '');
                    btnr.removeClass('b-carusel__next_disabled');
                }
                //****************
                var firstBlock = $$('#top-payed .b-carusel__item')[0];
                var firstBlockLeftPoint = firstBlock.getPosition().x;
				if(containerLeftPoint > firstBlockLeftPoint) {
					btnl.removeClass('b-carusel__prev_disabled');
                    $('carusel_shadow_left').setStyle('display', '');
				} else {
					btnl.addClass('b-carusel__prev_disabled');
                    $('carusel_shadow_left').setStyle('display', 'none');
                    
                    if (tizer_link) {
                        tizer_link.show();
                    }
				}
			}
		});
		btns.addEvent('click', function(e) {
			e.preventDefault();
			if((this.hasClass('b-carusel__prev_disabled'))||(this.hasClass('b-carusel__next_disabled'))) return false;
			var scr = cnt.getScroll();
			if(this.hasClass('b-carusel__next')) {
				var pos = (scr.x/one.x).floor()+1;
                                var nxt = lst.getElements('li')[pos];

                                if (pos >= 1 && tizer_link){
                                    tizer_link.hide();
                                }

                                // контроллируем чтобы последнее объявление не оставалось наполовину показаным, и сразу полностью его выдвигаем
                                var offset2 = 0;
                                var container = $$('#pay_place_top .b-carusel__inner')[0];
                                var hiddenRightX = ($$('#top-payed !^')[0].getPosition().x + one.x) - (container.getPosition().x + container.getSize().x); // сколько пикселей карусели скрыто справа
                                var k = hiddenRightX / one.x; // сколько объявлний скрыто
                                if (1 < k && k < 1.5) {
                                    offset2 = hiddenRightX - one.x + 30;
                                }
                                
                                // смещение требуется для карусели без резиновой верстки (см.#0018195)
                                var offset = (typeof _SHORT_CAROUSEL != 'undefined' && pos == 1)? 20: 0;
                                myFx.start(scr.x + one.x + offset + offset2, scr.y);
			} else {
				var pos = (scr.x/one.x).floor()-1;
                                var nxt = lst.getElements('li')[pos];
                                // смещение требуется для карусели без резиновой верстки (см.#0018195)
                                var offset = (typeof _SHORT_CAROUSEL != 'undefined' && pos == 0)? 20: 0;
                                var scrollX = scr.x - one.x - offset;
                                scrollX = scrollX > 0 ? scrollX : 0;
                                myFx.start(scrollX, scr.y);
			}
		});
		
		 $$('.b-carusel__prev').addEvent('mouseover',function(){$(this).addClass('b-carusel__prev_hover');}).addEvent('mouseout',function(){$(this).removeClass('b-carusel__prev_hover');})
		 $$('.b-carusel__next').addEvent('mouseover',function(){$(this).addClass('b-carusel__next_hover');}).addEvent('mouseout',function(){$(this).removeClass('b-carusel__next_hover');})
			 
		$$( ".b-carusel__prev" ).addEvent( "mousedown", function() {
			this.addClass( "b-carusel__prev_active" );
		}).addEvent( "mouseup", function() {
			this.removeClass( "b-carusel__prev_active");
		}).addEvent( "mouseleave", function() {
			this.fireEvent( "mouseup" );
		});
			 
		$$( ".b-carusel__next" ).addEvent( "mousedown", function() {
			this.addClass( "b-carusel__next_active"  );
		}).addEvent( "mouseup", function() {
			this.removeClass( "b-carusel__next_active" );
		}).addEvent( "mouseleave", function() {
			this.fireEvent( "mouseup" );
		});
		
	}












function ProjectsToggle () {
    el = $$('.prj-full-display');

    if (el.length) {
        if (isPrjCssOpened) {
            $$('.prj-one .prj-clogo').setStyle('float', 'none');
            el.hide();
        } else {
            $$('.prj-one .prj-clogo').setStyle('float', 'right');
            el.show();
        }
        isPrjCssOpened = !isPrjCssOpened;
        xajax_OpenAllProjects(isPrjCssOpened);

        if (isPrjCssOpened) {
            $('pl_toggler').innerHTML = 'Свернуть все проекты';
        } else {
            $('pl_toggler').innerHTML = 'Развернуть все проекты';
        }
    }
}


function fixedPayPlaceLeftBlock(id) {
    var _id = "mgContent" + id;
    var elm = $(_id);
    if(navigator.userAgent.toLowerCase().indexOf("7.0") != -1) {
        var w = elm.offsetWidth;
    } else {
        var w = elm.clientWidth;
    }
    if(elm.getElement('div.b-shadow-first')) {
        elm.getElement('div.b-shadow-first div.b-shadow__txt').setStyle('width', w);
    }
}

function payPlaceLeftClose(obj) {
    obj = $(obj);
    if(obj.getParent('.b-shadow-comby') != undefined) {
        obj.getParent('.b-shadow-comby').dispose();
        $$('.lnk-edit-place-hide').setStyle('visibility', 'visible');
    }
    hideOverlayFromBottomOfBody();
}

/**
 * закрыть все открытые "подробности" под меню каталога фрилансеров
 */
function payPlaceLeftCloseAll(obj) {
    $('pay_place_left').getElements('.b-shadow-comby').dispose();
    $$('.lnk-edit-place-hide').setStyle('visibility', 'visible');
    hideOverlayFromBottomOfBody();
}

function add_work_place(pos, pict, thumb, link) {
    delete_work_place(pos);
    
    var work = $('portfolio' + pos);
    work.getElement(".file-empty").hide();
    
    work_block = new Element( "div", {"class": "i-button i-button_relative b-preview-work", 
                                      "pf_id" : pict, 
                                      "id": "idTdW" + pos});
                                  
    close_btn  = new Element( "a", {"class"   : "b-button b-button_admin_del b-button_right_25 b-button_top_5", 
                                    "href"    : "javascript:void(0)",
                                    "onclick" : "delete_work_place('" + pos +"')"});
    
    work_link  = new Element( "a", {"class"  : "b-layout__link", 
                                    "href"   : link,
                                    "target" :"_blank"});
    work_img   = new Element( "img", {"class" : "b-layout__pic", "src" : thumb, "border" : "0"});    
    
    work_link.grab(work_img);
    work_block.grab(close_btn);
    work_block.grab(work_link);
    
    work.grab(work_block, 'top');
}

/**
 * добавляет ссылку на файл
 */
function add_work_place_file(pos, pict, ext, link, fileName) {
    delete_work_place(pos);
    
    var work = $('portfolio' + pos);
    work.getElement(".file-empty").hide();
    
    var work_block = new Element( "div", {"class": "i-button i-button_relative b-preview-work", 
                                      "pf_id" : pict, 
                                      "id": "idTdW" + pos});
                                  
    var close_btn  = new Element( "a", {"class"   : "b-button b-button_admin_del b-button_right_25 b-button_top_5", 
                                    "href"    : "javascript:void(0)",
                                    "onclick" : "delete_work_place('" + pos +"')"});
    
    var work_link  = new Element( "a", {"class"  : "b-layout__link", 
                                    "href"   : link,
                                    "target" :"_blank",
                                    "text": "загрузить"});
    var work_ico   = new Element( "span", {"class" : "f-" + ext});    
    var work_wrap  = new Element( "div", {"class" : "flw_offer_attach"});    
    var work_em    = new Element( "em");    
    
    work_em.grab(work_ico);
    work_em.grab(close_btn);
    work_em.grab(work_link);
    work_wrap.grab(work_em);
    work_block.grab(work_wrap);
    
    work.grab(work_block, 'top');
}

function check_length(message){
    var maxLen = 50;
    if (message.value.length > maxLen) {
        alert('Слишком длинный текст');
        message.value = message.value.substring(0, maxLen);
    }
}

function delete_work_place (pos) {
    var work = $('portfolio' + pos);
    if(!work) return;
    if(work.getElement(".b-preview-work")) {
        work.getElement(".file-empty").show(); //b-work-empty
        work.getElement(".b-preview-work").dispose();
    }
}

function get_selected_works () {
    var profs = [];
    $$('.b-layout__table .b-preview-work').each( function(el) {
        if (el.get('pf_id')) {
            profs.push(el.get('pf_id'));
        }
    });
    profs = profs.join(',');
    return profs;
}

/**
 * добавляем оверлей
 */
function putOverlayToBottomOfBody () {
    // если оверлей уже есть, то выходим
    if ($('pay_place_overlay')) return;
    var overlay  = new Element('div', {'class': 'b-shadow__overlay', 'id': 'pay_place_overlay'});
    // закрытие всех окон и самоуничтожение при клике по оверлею
    overlay.addEvent('click', function(){
        payPlaceLeftCloseAll();
        this.dispose();
    });
    $('pay_place_left').grab(overlay, 'top');
}
/**
 * скрыть оверлей
 */
function hideOverlayFromBottomOfBody () {
    if (!$('pay_place_overlay')) return;
    $('pay_place_overlay').dispose();
}

function init_button_filter_payPlace_left() {
    $$('.b-filter__body .b-filter__link').addEvent('click',function(){
		$$('.b-filter__toggle').addClass('b-filter__toggle_hide');
		this.getParent('.b-filter__body').getNext('.b-filter__toggle').removeClass('b-filter__toggle_hide');
		$$('.b-filter').setStyle('z-index','0')
		this.getParent('.b-filter').setStyle('z-index','10')
		var overlay=document.createElement('div');
		overlay.className='b-filter__overlay';
		$$('.overlay-cls').grab(overlay, 'top');
		$$('.b-filter__overlay').addEvent('click',function(){
			$$('.b-filter__toggle').addClass('b-filter__toggle_hide');
			$$('.b-filter__overlay').dispose();
		if (Browser.ie8){
			$$('.b-filter').setStyle('overflow','visible');
			if(this.getParent('.b-filter') != undefined) {
    			this.getParent('.b-filter').setStyle('overflow','hidden');
    			this.getParent('.b-filter').setStyle('overflow','visible');
			}
		}		
			});
		return false;
    });
    
    $$('.b-shadow__icon_close').addEvent('click',function() {
        if(this.getParent('.b-shadow').hasClass('b-filter__toggle')){
            this.getParent('.b-shadow').addClass('b-filter__toggle_hide')
        }
        else{
            this.getParent('.b-shadow').addClass('b-shadow_hide');
            $$('div.b-filter__overlay').destroy();
        }
    })
}

function showInfoNewCallback(cnt, is_edit) {
    el = $('mg_rightContent');
    if (!el) {
        return;
    }

    if (el.get('html')) el.store('tmp', el.get('html'));
    el.set('html', cnt);

    if (is_edit) {
        $('pay_place_edit_left').inject(el.getParent('.lp-one')
            .getElement('.lp-inf-txt'), 'after');
        $('pay_place_edit_left').show();

        el.getParent('.lp-one').getElement('.lp-inf-txt').hide();
    }

}

function payPlaceEditCancel() {
    el = $('mg_rightContent');
    if (!el) {
        return;
    }

    if ($('pay_place_edit_left')) {
        $('pay_place_edit_left').dispose();
    }
    
    if (el.retrieve('tmp') == null) {
        payPlaceHide();
        return;
    }

    if (el.retrieve('tmp').trim().length != 0) {
        el.set('html', el.retrieve('tmp'));
    }

    par = el.getParent('.lp-one');
    par.getElement('.lp-inf-txt').show();

    if (el.getElement('p.lp-full-descr') || $('idFpFullDescr')) {
        if (((el.getElement('p.lp-full-descr') && el.getElement('p.lp-full-descr').get('html').trim().length == 0)
            || ($('idFpFullDescr') && $('idFpFullDescr').get('value').length == 0))
            && $$('.lp-preview img').length == 0) {
            par.getElement('a.lnk-show-details').hide();
        } else {
            par.getElement('a.lnk-show-details').setStyle('display', '');
        }
    }
    $$('a.lnk-edit-place').show();
}



function payPlaceHide() {
    el = $('mg_rightContent');
    if (!el) {
        return;
    }
    
    par = el.getParent('.lp-one');
    par.removeClass('a');

    if ($('expose')) {
        $('expose').dispose();
    }

    if ( $$( ".lp-user-left" ) ) {
        $$( ".lp-user-left" ).dispose();
    }

    if ( $$( ".lp-user-right" ) ) {
        $$( ".lp-user-right" ).dispose();
    }

    if (el.getElement('p.lp-full-descr') || $('idFpFullDescr')) {
        if (((el.getElement('p.lp-full-descr') && el.getElement('p.lp-full-descr').get('html').trim().length == 0)
            || ($('idFpFullDescr') && $('idFpFullDescr').get('value').length == 0))
            && $$('.lp-preview img').length == 0) {
            par.getElement('a.lnk-show-details').hide();
        } else {
            par.getElement('a.lnk-show-details').setStyle('display', '');
        }
    }
    
    $$('.lp-one a.lnk-edit-place').show();
    $$('.lp-one .lp-inf-txt').show();

    if ($('pay_place_edit_left')) {
        $('pay_place_edit_left').dispose();
    }
    
    el.dispose();
}


function fp_getSelProfsNew () {
    var profs = [];
    $$('.lp-preview div.lp-preview-one').each( function(el) {
        if (el.get('pf_id')) {
            profs.push(el.get('pf_id'));
        }
    });
    profs = profs.join(',');
    
    return profs;
}

function payPlaceDelWork (e, id) {
    if (e) e.stop();
    _self = this;
    
    pls = $$('.lp-preview div.lp-preview-one');
    if (!id) {
        pos = $$('.lp-preview ul.lp-preview-ops li.lp-del a').indexOf(_self);
    } else {
        pos = pls.indexOf(document.getElement('.lp-preview div.lp-preview-one[pf_id=' + id + ']'));
        _self = $$('.lp-preview ul.lp-preview-ops li.lp-del a')[pos];
    }
    
    if (!_self) {
        return;
    }

    p = _self.getParent('.lp-preview');

    el = new Element('div', {
        'class' : 'lp-preview-one',
        'html' : '<div style="height:200px;width:202px;border:1px solid #c0c0c0;background: #dfdfdf;">&nbsp;</div>'
    });
    el.inject(pls[(pls.length-1)], 'after');

    id = pls[pos].get('pf_id');

    pls[pos].dispose();
    _self.hide();
    
    inp = document.getElement('input[pf_id=' + id + ']');
    if (inp) {
        inp.set('checked', false);
    }

    payPlaceEditInit();
}


function payPlaceEditInit() {
    
    works = $$('.lp-preview div.lp-preview-one a');
    places = $$('.lp-preview div.lp-preview-one');
    
    dl = $$('.lp-preview ul.lp-preview-ops li.lp-del a');
    
    dl.removeEvents('click');
    dl.addEvent( 'click', payPlaceDelWork);
    

    $$('.lp-preview-ops li[class^=lp-sort] img').each(function (el) {
        el.set('src', el.get('src').replace('0.gif', '.gif'));
    });

    if (works.length < 3) {
        $$('.lp-preview-ops li.lp-sort2 img').each(function(el) {
            el.set('src', el.get('src').replace('.gif', '0.gif'));
        });
    }

    if (works.length < 2) {
        $$('.lp-preview-ops li.lp-sort1 img').each(function (el) {
            el.set('src', el.get('src').replace('.gif', '0.gif'));
        });
    }

    $$('.lp-preview-ops li[class^=lp-sort] img').removeEvents('click');
    $$('.lp-preview-ops li[class^=lp-sort] img').addEvent('click', function() {
        
        dir = this.get('src').replace(/\/images\/ico_(left|right)\.gif/i, '$1');

        if (dir != 'left' && dir != 'right') {
            return false;
        }

        works = $$('.lp-preview div.lp-preview-one');
        
        if (this.getParent('li').hasClass('lp-sort1')) {
            works[1].inject(this.getParent('.lp-preview'), 'top');
        } else {
            works[1].inject($$('.lp-preview div.lp-preview-one')[2], 'after');
        }

    });
    
    dl.hide();
    $$('.lp-preview div.lp-preview-one a').each( function(el, i) {
        dl[i].show();
    });
    
    
    if ($$('.lp-preview div.lp-preview-one a').length == 3) {
        $$('div.lp-works-block, div.lp-file').hide();
        $('exceededMaxWork').show();
    } else {
        $$('div.lp-works-block, div.lp-file').show();
        $('exceededMaxWork').hide();
    }
    
}


function payPlaceAddWork(el, pict, thumb, path) {
    el = $(el);
    if (el && !el.get('checked')) {
        payPlaceDelWork(null, el.get('pf_id'));
        return;
    }
    
    is_img = false;
    if (!el) {
        el = new Element('input', {
            'pf_id' : pict
        });
        is_img = true;
    }
    
    works = $$('.lp-preview div.lp-preview-one a');
    places = $$('.lp-preview div.lp-preview-one');
    
    if (works.length == 3) {
        return;
    }
    
    cp = document.getElement('.lp-preview-one-tpl').clone();
    cp.removeClass('lp-preview-one-tpl');
    cp.addClass('lp-preview-one');
    cp.set('id', 'idTdW' + works.length);
    cp.set('pf_id', el.get('pf_id'));
    
    if (is_img) {
        cp.getElement('a').set('href', path + pict);
    } else {
        cp.getElement('a').set('href', cp.getElement('a').get('href') + el.get('pf_id'));
    }
    
    var re = /\/images\/ico_(\w+)\.gif/gi
    if (re.test(thumb)) {
		cp.getElement('img').set('src', thumb);
		cp.getElement('img').set('class', 'thumb-ic');
	}else {
		cp.getElement('img').set('src', path + thumb);
	}
    cp.replaces(places[works.length]);
    cp.show();
    
    dls = $$('.lp-preview ul.lp-preview-ops li.lp-del a');
    
    dls[(works.length)].show();    
    payPlaceEditInit();
    
}


function frlFiltersInit() {
    el = $('frl-filters');
    if (!el) {
        return;
    }
    
    $$('#frl-filters .f-tgl a').addEvent('click', function(e, use_cookie) {
 
        b = this.getParent('.f-tgl').getNext();
        b.toggle();
        
        num = $$('#frl-filters .f-tgl a').indexOf(this);

        if (use_cookie != 'no') {
            tgl = Cookie.read('f_tgl2');

            if (tgl) {
                tgl = tgl.split(',');
            } else {
                tgl = [];
            }

            if (tgl.contains(num)) {
                tgl.erase(num);
            } else {
                tgl.push(num);
            }

            Cookie.write('f_tgl2', tgl.join(','), {duration: 365});
        }

        return false;
    });
    
    tgl = Cookie.read('f_tgl2');
    
    if (tgl) {
        tgl = tgl.split(',');

        tgl.each(function (i) {
            if (i >= 0) {
                $$('#frl-filters .f-tgl a')[i].fireEvent('click', [null, 'no']);
            }
        });
    }
}

function frlFiltersToggle(el) {
    tgl = Cookie.read('f_tgl2');
    tgl = tgl ? tgl.split(',') : [];
    
    if (el) {
        el = $(el);
        el.getParent('div.cat-flt').toggleClass('cat-flt-hidden');
        
        if (tgl.contains('all')) {
            tgl.erase('all');
        } else {
            tgl.push('all');
        }
        Cookie.write('f_tgl2', tgl.join(','), {duration: 365});
        
        return;
    }
    
    el = $('frlFiltersToggle');
    if (!el) {
        return;
    }
    
    if (tgl.contains('all')) {
        el.getParent('div.cat-flt').removeClass('cat-flt-hidden');
    }
}

function SetGiftResv(id) {
    new Request.JSON({
        url: '/xajax/users.server.php',
        onSuccess: function(resp) {
            if(resp && resp.success) {
                el = $('last_gift' + resp.id);
                if (el) {
                    el.dispose();
                }
                gfs = $$('div.last-gift-block');
                if (gfs.length > 0) {
                    gfs[0].removeClass('b-fon_hide');
                } else {
                    // если больше нет информационных блоков, то сдвигаем промоблок и прочие блоки
                    shiftPromo(60);
                }
            }
        }
    }).post({
        'xjxfun': 'SetGiftResv',
        'xjxargs': [id],
        'u_token_key': _TOKEN_KEY
    });
}

    
function applySubcat(cat){
    if(typeof sub[cat] != 'undefined')
        for(var i = 0; i < sub[cat].length; i++){
            var option = document.createElement('option');
            option.value = sub[cat][i][0];
            option.innerHTML = sub[cat][i][1];
            document.getElementById('rss_sub').appendChild(option);
        }
}
    
function getRssUri(){
    var sub = document.getElementById('popup_profgroup_db_id').value;
    var cat = sub;
    if ($('popup_profgroup_column_id').value == 1) {
	    var v = ComboboxManager.getInput('popup_profgroup');
	    cat = v.breadCrumbs[0];
	}
	if ($('popup_profgroup_column_id').value == 0) {
	    sub = 0;
	}
    var xml_path = RSS_LINK;
    if(sub){
        return xml_path+'?subcategory='+sub+(cat ? '&category='+cat : '');
    }else if(cat){
        return xml_path+'?category='+cat;
    }else{
        return xml_path;
    }
}

function gotoRSS(){
    document.location.href = getRssUri();
}
    
function showRSS(){
    $('rsso').toggleClass('b-shadow_hide');
}
    
    
function FilterSubCategoryRSS(category) {
    var objSel = $('rss_sub');
    objSel.options.length = 0;
    objSel.disabled = 'disabled';
    objSel.options[objSel.options.length] = new Option('Весь раздел', 0);
    if(category == 0) {
        objSel.set('disabled', true);
    } else {
        objSel.set('disabled', false);
    }
    //  var ft = true;
    applySubcat(category);
    //  for (i in filter_specs[category]) {
    //  if (filter_specs[category][i][0]) {
    //  objSel.options[objSel.options.length] = new Option(filter_specs[category][i][1], filter_specs[category][i][0], ft, ft);
    //  ft = false;
    //  }
    //  }
    objSel.value = 0;
}

function setDirectExternalLinks( uid, val ) {
    $('a-rem').set('disabled', true);
    xajax_setDirectExternalLinks( uid, val );
}

// устанавливает eventListener на область за пределами подробной информации о фрилансере под каталогом
function shadowOverlay () {
    $$('.b-shadow__overlay').addEvent('click', function(){
        payPlaceLeftClose(this);
    })
}
// добавление/удаление из закладок в процессе
favInProgress = false;
function addUserToFav (from, to) {
    if (favInProgress) return;
    favInProgress = true
    xajax_AddInTeam(from, to, true);
}
function delUserFromFav (from, to) {
    if (favInProgress) return;
    favInProgress = true
    xajax_DelInTeam(from, to, true);
}

/**
 * сдвигает промо-блок и карусель на заданное количество пикселей
 */
function shiftPromo (height) {
    var promo = $$('.b-promo_main')[0];
    if (promo) {
        var marginTop = promo.getStyle('margin-top').toInt();
        marginTop -= height;
        promo.setStyle('margin-top', marginTop);
    }
    var carusel = $('pay_place_carusel');
    if (carusel) {
        var top = carusel.getStyle('top').toInt();
        top -= height;
        carusel.setStyle('top', top);
    }
}
