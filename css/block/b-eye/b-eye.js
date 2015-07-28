window.addEvent('domready', 
function() {
	$$('.b-eye').getElement('.b-eye__link').addEvent('click',function(){
		this.toggleClass('b-eye__link_bordbot_dot_0f71c8').toggleClass('b-eye__link_bordbot_dot_808080');
		this.getElement('.b-eye__icon').toggleClass('b-eye__icon_open').toggleClass('b-eye__icon_close');
		this.getChildren('.b-eye__txt').toggleClass('b-eye__txt_hide');
		return false;
		})
	
})
