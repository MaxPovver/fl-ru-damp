window.addEvent('domready', 
function() {
	$$('.b-ext-filter__switch-on').getElement('.b-ext-filter__link').addEvent('click',function(){
			this.getParent('.b-ext-filter__switcher').toggleClass('b-ext-filter__switcher_on').toggleClass('b-ext-filter__switcher_off');
			//return false;
		})
	$$('.b-ext-filter__switch-off').getElement('.b-ext-filter__link').addEvent('click',function(){
			this.getParent('.b-ext-filter__switcher').toggleClass('b-ext-filter__switcher_on').toggleClass('b-ext-filter__switcher_off');
			//return false;
		})
})







