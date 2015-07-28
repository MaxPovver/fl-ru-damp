window.addEvent('domready', 
function() {
	$$( ".b-button-multi__item" ).addEvent( "click", function(e) {
        e.stop();
		if((!(this.hasClass('b-button-multi__item_disabled')))){
			if(!(this.hasClass('b-button-multi__item_active'))){
				this.getParent('.b-button-multi').getChildren('.b-button-multi__item').removeClass('b-button-multi__item_active');
				this.addClass( "b-button-multi__item_active" );
				if(this.hasClass('i-shadow')){
					this.getElement('.b-shadow').removeClass('b-shadow_hide');
					var overlay = new Element('div.b-shadow__overlay');
					$$('body').grab(overlay, 'top');
					$$('.b-button-multi__close').addEvent('click',function(){
						$$('.b-shadow__overlay').dispose();
						this.getParent('.b-shadow').addClass('b-shadow_hide');
						this.getParent('.b-button-multi__item').removeClass( "b-button-multi__item_active" );
						return false;
						})
					}
			}
		}
		return false;
	})
})



