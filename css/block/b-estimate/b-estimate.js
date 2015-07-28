window.addEvent('domready', 
function(){
	$$('.b-estimate__link').addEvent('click',function(){
		if(!this.getParent('.b-estimate__item').hasClass('b-estimate__item_active')){
				this.getParent('.b-estimate').getElements('.b-estimate__item').removeClass('b-estimate__item_active');
				this.getParent('.b-estimate__item').addClass('b-estimate__item_active');
				return false;
			}
		})
})







