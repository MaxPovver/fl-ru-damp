window.addEvent('domready', 
function() {
	$$('.b-layout__slider').setStyle('display','none');
	$$( ".b-layout__toggler" ).addEvent( "click", function() {
		if(this.hasClass('b-layout__link_bordbot_dot_0f71c8')){
			this.addClass( "b-layout__link_bordbot_dot_000" ).removeClass( "b-layout__link_bordbot_dot_0f71c8"); 
			this.getParent('.b-layout__h4').getNext('.b-layout__slider').toggle(); return false;
		}
		else{
			this.addClass( "b-layout__link_bordbot_dot_0f71c8" ).removeClass( "b-layout__link_bordbot_dot_000");
			this.getParent('.b-layout__h4').getNext('.b-layout__slider').toggle(); return false;
		}
	})
});







