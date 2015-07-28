window.addEvent('domready', 
function() {
	$$('.b-shadow__icon_close').addEvent('click',function() {
		if(this.getParent('.b-shadow') && this.getParent('.b-shadow').hasClass('b-filter__toggle')){
			this.getParent('.b-shadow').addClass('b-filter__toggle_hide')
		}
		else if(this.getParent('.b-shadow')) {
			this.getParent('.b-shadow').addClass('b-shadow_hide');
            $$('div.b-filter__overlay').destroy();
		}
		})
	$$('.b-shadow__icon_quest').addEvent('click',function(){
		this.getParent('.i-shadow').getChildren('.b-shadow').removeClass('b-shadow_hide')
		});
	
	
	$$('.b-shadow__close').addEvent('click',function() {
            if (this.getParent('.b-shadow')) {
                this.getParent('.b-shadow').addClass('b-shadow_hide');
            }
		})
		
	
    // выравнивает по центру блоки с классом b-shadow_center
    function shadow_popup() {
        $$('.b-shadow_center').each(function (popup_elm) {
            var winSize = $(document).getSize();
            var elemSize = popup_elm.getSize();
            popup_elm.setPosition({
                x: (winSize.x - elemSize.x) / 2,
                y: (winSize.y - elemSize.y) / 2
            })
        });
    }
    shadow_popup();
    
});

function getPromoWin() {
    setTimeout('getViewPromoWin()', 10000);
}

function getViewPromoWin() {
    el = new Element('div', {'class': 'b-filter__overlay'});
    $(document.body).grab(el);
    $('promo_window').removeClass('b-shadow_hide');
}
