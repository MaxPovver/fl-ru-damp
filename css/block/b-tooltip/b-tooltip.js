window.addEvent('domready', 
function() {
	$$( ".b-tooltip__close" ).addEvent( "click", function() {
		if(this.getParent('.b-tooltip').getParent('.i-tooltip')){
			this.getParent('.i-tooltip').addClass('i-tooltip_hide');
		}
		else{
			this.getParent('.b-tooltip').addClass('b-tooltip_hide');
			}
	})
	
	$$('.b-tooltip__ic').addEvent('click',function(){ 
		this.getParent('.b-tooltip').getParent('div').getNext('.i-tooltip').toggleClass('i-tooltip_hide');
		this.toggleClass('.b-tooltip__ic_active');
		})
})






