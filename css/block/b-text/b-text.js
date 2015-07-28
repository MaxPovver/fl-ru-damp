window.addEvent('domready', 
function() {
	$$( ".b-text__link_toggle" ).addEvent( "click", function() {
		if(this.hasClass('b-text__link_color_dotted_999')){
			this.addClass( "b-text__link_color_dotted_333" ).removeClass( "b-text__link_color_dotted_999"); 
			if(this.getParent('.b-text__p').getNext('.b-text__slide').hasClass('b-text__slide_hide')){
				this.getParent('.b-text__p').getNext('.b-text__slide').removeClass('b-text__slide_hide'); return false;
			}
			else{
				this.getParent('.b-text__p').getNext('.b-text__slide').addClass('b-text__slide_hide'); return false;
			}
		}
		else{
			this.addClass( "b-text__link_color_dotted_999" ).removeClass( "b-text__link_color_dotted_333");
			if(this.getParent('.b-text__p').getNext('.b-text__slide').hasClass('b-text__slide_hide')){
				this.getParent('.b-text__p').getNext('.b-text__slide').removeClass('b-text__slide_hide'); return false;
			}
			else{
				this.getParent('.b-text__p').getNext('.b-text__slide').addClass('b-text__slide_hide'); return false;
			}
		}
	})
});







