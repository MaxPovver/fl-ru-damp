window.addEvent('domready', 
function() {
	$$('.b-pay-answer__but').addEvent('click',function(){
		if(!(this.hasClass('b-button_active'))){
			this.addClass('b-button_active');
			this.getParent('.b-pay-answer__txt').getParent('.b-fon__body').getFirst('.b-pay-answer__txt').addClass('b-pay-answer__txt_hide');
			$$('.b-pay-answer__content').removeClass('b-pay-answer__content_hide')
			}
		else{
			this.removeClass('b-button_active');
			this.getParent('.b-pay-answer__txt').getParent('.b-fon__body').getFirst('.b-pay-answer__txt').removeClass('b-pay-answer__txt_hide');
			$$('.b-pay-answer__content').addClass('b-pay-answer__content_hide')
			}
		return false;
     });
     
     if($('close_payed') != undefined) 
     $('close_payed').addEvent('click',function() {
         $$('.b-pay-answer__but').removeClass('b-pay-answer__button_active');
	     $$('.b-pay-answer__but').getParent('.b-pay-answer__txt').getParent('.b-fon__body').getFirst('.b-pay-answer__txt').removeClass('b-pay-answer__txt_hide');
		 $$('.b-pay-answer__content').addClass('b-pay-answer__content_hide'); 
     });
})










