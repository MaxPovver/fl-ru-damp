window.addEvent('domready', 
function() {
	$$('.b-promo__gbgmright .b-promo__link').addEvent('click',function() {
	    var d = new Date();
        d.setMonth(d.getMonth() + 1);
        document.cookie='viewQuiz' + '=1; expires='+d.toGMTString() + '; path=/';
		$$('.b-promo__gbgm').addClass('b-promo__gbgm_hide');
		$$('.b-promo__gbg').removeClass('b-promo__gbg_hide');
		return false;
    });
    $$('.b-promo__gbgbot').getElement('a.b-promo__link').addEvent('click',function() {
        var d = new Date();
        d.setMonth(d.getMonth() + 1);
        document.cookie='viewQuiz' + '=0; expires='+d.toGMTString() + '; path=/';
		$$('.b-promo__gbg').addClass('b-promo__gbg_hide');
		$$('.b-promo__gbgm').removeClass('b-promo__gbgm_hide');
		return false;
	});
	
	
	
	$$('.b-promo__link_toggler').addEvent('click',function() {
    		this.getParent('.b-promo__p').setStyle('display','none');
    		var note = this.getParent('.b-promo__note-wrap');
    		var item = this.getParent('.b-promo__item').getElement('div.b-pay-answer');
    		var bp = new Fx.Morph(note, {duration:1000,transition: Fx.Transitions.Sine.easeOut});
    		//bp.addEvent('complete', function() {
    		  //note.addClass('b-promo__note-wrap_width_400 b-promo__note-wrap_inline-block');
    		  //note.setStyle("float", "none");      
    		//});
    		bp.start({'width': [706, 420]});
    		var bF = new Fx.Morph(item, {duration:1000, transition: Fx.Transitions.Sine.easeOut});
    		bF.start({'width': [0, 310]});
    		bF.addEvent('complete', function(){
    		  item.addClass('b-pay-answer__content');    
    		});
    		note.addClass('b-promo__note-wrap_width_420').addClass('b-promo__note-wrap_inline-block');
    		item.removeClass('b-pay-answer_hide');
    		return false;
		});
	
	$$('.b-buttons__link_toggler').addEvent('click',function() {
	    var note = this.getParent('.b-promo__item').getElement('.b-promo__note-wrap');
        var item = this.getParent('.b-promo__item').getElement('div.b-pay-answer');
        item.removeClass('b-pay-answer_width_300 b-pay-answer__content');  
        item.setStyle('overflow', 'hidden');
    		var bp = new Fx.Morph(note, {duration:1000,transition: Fx.Transitions.Sine.easeOut});
    		bp.addEvent('complete', function() { 
    		  note.removeClass('b-promo__note-wrap_width_420').removeClass('b-promo__note-wrap_inline-block');
    		  note.setStyle('width', '734px');
    		});
    		bp.start({'width': [420, 705]});
    		var bF = new Fx.Morph(item, {duration:1000, transition: Fx.Transitions.Sine.easeOut});
    		bF.start({'width': [310, 5]});
    		bF.addEvent('complete', function(){
    		  item.addClass('b-pay-answer_hide');  
    		});
		
		$$('.b-promo__link_toggler').getParent('.b-promo__p').setStyle('display','block');
		return false;
		})




		$$('.b-promo__buttons .b-button').addEvent('click',function(){
				this.getParent('.b-buttons').setStyle('display','none');
				$$('.b-promo__zayvka').setStyle('display','block');
				return false
			})
		$$('.b-promo__buttons .b-buttons__call-zakaz').addEvent('click',function(){
				this.getParent('.b-buttons').setStyle('display','none');
				$$('.b-promo__zakaz-call').setStyle('display','block');
				return false
			})
		$$('.b-buttons__zayavka-add').addEvent('click',function() {
                this.getParent('.b-promo__zakaz-call').setStyle('display','none');
				$$('.b-promo__zayvka').setStyle('display','block');
				return false
			})
		$$('.b-buttons__zayavka-call').addEvent('click',function(){
				this.getParent('.b-promo__zayvka').setStyle('display','none');
				$$('.b-promo__zakaz-call').setStyle('display','block');
				return false
			})
		$$('.b-promo__link_zayavka-add').addEvent('click',function(){ 	 
				$$('div.b-promo__buttons').setStyle('display','none');
                $$('.b-promo__zakaz-call').setStyle('display','none'); 	 
				$$('.b-promo__zayvka').setStyle('display','block'); 	 
				return false 	 
			})

		/*$$('.b-promo__zakaz-call').getElement('.b-button_rectangle_color_disable').addEvent('click',function(){
				$$('.b-promo__zakaz-call').getElement('.b-input__required').addClass('b-input__error');
				$$('.b-promo__zakaz-call').getElement('.b-form_hide').removeClass('b-form_hide');
				return false;
			
			});*/
})


function error_hide(obj) {
    $(obj).getParent().removeClass('b-input_error');   
    $(obj).getParent().removeClass('b-textarea_error'); 
    
    
    var i = 0;
    $$('.b-input_error').each(function() {
        i++;
    });
    
    $$('.b-textarea_error').each(function(){
        i++;
    });
    
    
    if(i == 0) {
        if($('error_block_msg') != undefined) $('error_block_msg').addClass('b-form_hide');
    }
}






