window.addEvent('domready', 
function() {
	$$( ".b-post__star" ).addEvent( "click", function() {
		this.toggleClass('b-post__star_white').toggleClass('b-post__star_yellow');
	});
	$$('.b-post__qwest').addEvent('click',function(){ 
		this.toggleClass('.b-post__qwest_active');
		})
	
	
	
	
	
})







