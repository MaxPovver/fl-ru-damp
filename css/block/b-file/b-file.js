window.addEvent('domready', 
function() {
	$$( ".b-file__link_toggle" ).addEvent( "click", function() {
			if(this.getNext('.b-file__slide').hasClass('b-file__slide_hide')){
				this.removeClass('b-file__link_dot_999').addClass('b-file__link_dot_999_invers');
				this.getNext('.b-file__slide').removeClass('b-file__slide_hide'); return false;
			}
			else{
				this.removeClass('b-file__link_dot_999_invers').addClass('b-file__link_dot_999');
				this.getNext('.b-file__slide').addClass('b-file__slide_hide'); return false;
			}; return false;
	})
	$$('.b-file__input').addEvent('mouseover',function(){this.getNext('.b-button').addClass('b-button_hover')})
	$$('.b-file__input').addEvent('mouseout',function(){this.getNext('.b-button').removeClass('b-button_hover')})
	/*
	$$('.b-file__input').addEvent('click',function(){this.getNext('.b-button').addClass('b-button_active')})
	$$('.b-file__input').addEvent('mouseout',function(){this.getNext('.b-button').removeClass('b-button_active')})
	*/
});
