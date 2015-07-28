/**
 * @param catalog
 * @param top - верхний отступ
 */
function pay_place_top(catalog, top) {
    var image_id = "pay_place_top";
    $('pay_place_top').adopt(get_loaded_line(image_id, {height: '120px', display: 'block'}));
    new Request.JSON({
        url: '/xajax/blocks.server.php',
        onSuccess: function(resp) {
            if(resp.success) {
                $('loaded_image_'+image_id).destroy();
                $('pay_place_top').set('html', resp.html);
                initHScroll();
                
                //Для ре инициализации скриптов открытия попапа после аякса
                //В нашей версии mootools нет live
                if (typeof Bar_Ext !== "undefined") {
                    Bar_Ext.popuper();
                }
                
                
                var carusel_tizer = $('carusel_tizer');
                if (carusel_tizer) {
                    
                    if (Cookie.read('hide_carusel_tizer')) {
                        carusel_tizer.addClass('b-carusel__item_hide');
                        $('carusel_tizer_switcher').removeClass('b-carusel__link_hide');
                    }
                    
                    //Показываем тизер и скролим до него
                    $('carusel_tizer_switcher').addEvent('click', function(){
                        carusel_tizer.removeClass('b-carusel__item_hide');
                        $('carusel_tizer_switcher').addClass('b-carusel__link_hide');

                        var lst = $('top-payed');
                        if(lst) {
                            var cnt = lst.getParent();
                            var myFx = new Fx.Scroll(cnt);
                            var scr = cnt.getScroll();
                            myFx.start(0, scr.y);
                            $('carusel_shadow_left').hide();
                            $$('.b-carusel__next').removeClass('b-carusel__next_disabled');
                            $$('.b-carusel__prev').addClass('b-carusel__prev_disabled');
                        }
                        
                        Cookie.dispose('hide_carusel_tizer');
                    });

                    //Скрываем тизер по крестику в оном
                    $('carusel_tizer_close').addEvent('click', function(){
                        carusel_tizer.addClass('b-carusel__item_hide');
                        $('carusel_tizer_switcher').removeClass('b-carusel__link_hide');
                        Cookie.write('hide_carusel_tizer', true, {duration: 30});
                    });
                }
            }  
        }
    }).post({
        'xjxfun': 'pay_place_top',
        'xjxargs': ['N'+catalog, top],
        'u_token_key': _TOKEN_KEY
    });     
}

function qaccess() {
    new Request.JSON({
        url: '/xajax/blocks.server.php',
        onSuccess: function(resp) {
            if(resp.success) {
                $$('#qaccess_top').set('html', resp.html);
                initCpromo();
                initHideInput();
                initKword(); 
            }  
        }
    }).post({
        'xjxfun': 'qaccess',
        'u_token_key': _TOKEN_KEY
    });    
}

function catalog_promo(prof_id) {
    new Request.JSON({
        url: '/xajax/blocks.server.php',
        onSuccess: function(resp) {
            if(resp.success) {
                $$('#catalog_promo').set('html', resp.html);
            }  
        }
    }).post({
        'xjxfun': 'catalog_promo',
        'xjxargs': ['N'+prof_id],
        'u_token_key': _TOKEN_KEY
    });      
}

function seo_print(text) {
    document.write(text);
}

function clear_link(id, shref) {
    $$(id).setProperty('href', $$(id).getProperty('href') + shref);
}

function get_loaded_line(val, in_style) {
    var elm = new Element('div#loaded_image_' + val);
    elm.set('html', '&nbsp;');
    elm.set({styles: in_style});
    return elm;
}
