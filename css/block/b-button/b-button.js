window.addEvent('domready', 
function() {
	$$( "a.b-button" ).addEvent( "mousedown", function() {
		this.addClass( "b-button_active" );
	}).addEvent( "mouseup", function() {
		this.removeClass( "b-button_active" );
	}).addEvent( "mouseleave", function() {
		this.fireEvent( "mouseup" );
	});
})







