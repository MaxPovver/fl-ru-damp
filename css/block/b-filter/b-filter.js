window.addEvent('domready', 
function() {
	$$('.b-filter__body .b-filter__link').addEvent('click',function(){		
		$$('.b-filter__toggle').addClass('b-filter__toggle_hide');
		this.getParent('.b-filter__body').getNext('.b-filter__toggle').removeClass('b-filter__toggle_hide');
		$$('.b-filter').setStyle('z-index','0')
		this.getParent('.b-filter').setStyle('z-index','10')
		var overlay=document.createElement('div');
		overlay.className='b-filter__overlay';
		$$('.overlay-cls').grab(overlay, 'top');
		$$('.b-filter__overlay').addEvent('click',function(){
			$$('.b-filter__toggle').addClass('b-filter__toggle_hide');
			$$('.b-filter__overlay').dispose();
		if (Browser.ie8){
			$$('.b-filter').setStyle('overflow','visible');
			if(this.getParent('.b-filter') != undefined) {
    			this.getParent('.b-filter').setStyle('overflow','hidden');
    			this.getParent('.b-filter').setStyle('overflow','visible');
			}
		}		
			});
		return false;
		})
	
	
	$$('.b-filter__item .b-filter__link').addEvent('click',function(){
		if((this.getParent('.b-filter__item').getChildren('.b-filter__marker').hasClass('b-filter__marker_hide'))){
				this.getParent('.b-filter__list').getChildren('.b-filter__item').getElement('.b-filter__marker').addClass('b-filter__marker_hide').getPrevious('.b-filter__link').removeClass('b-filter__link_no').addClass('b-filter__link_dot_0f71c8');
				this.getParent('.b-filter__item').getChildren('.b-filter__marker').removeClass('b-filter__marker_hide');
				this.removeClass('b-filter__link_dot_0f71c8').addClass('b-filter__link_no');
				this.getParent('.b-filter__toggle').getPrevious('.b-filter__body').getChildren('.b-filter__link').set('text',this.get('text'));
				$$('.b-filter__toggle').addClass('b-filter__toggle_hide');
				$$('.b-filter__overlay').dispose();
		if (Browser.ie8){
			$$('.b-filter').setStyle('overflow','visible');
			if(this.getParent('.b-filter') != undefined) {
    			this.getParent('.b-filter').setStyle('overflow','hidden');
    			this.getParent('.b-filter').setStyle('overflow','visible');
			}
		}		
            this.fireEvent('selected');
			return false;
			}
		});
		
	if($('calcForm') != undefined) sbr_calc($('calcForm'));
});

function setValueInput(name, val, act) {
    if(act == undefined) act = 'update';
    $(name).set('value', val);
    if(act == 'update') sbr_calc($('calcForm'), 'recalc');
}

function checkRole(val) {
    if(val == 1) {
        $('shadow_rez_type').removeClass('b-shadow_right_0');
        
        $('block_calc_emp_text').set('html', 'Работодатель заплатит');
        $('block_calc_frl_text').set('html', 'Вы получите');
        
        var frl_html = $('freelancer_block');
        var emp_html = $('emp_block');
        
        $('first_block').empty()
        $('first_block').grab(frl_html);
        $('second_block').empty()
        $('second_block').grab(emp_html);
        
        $('case_word').set('html', 'являющийся');
        $('calc_role').set('html', '&nbsp;хочу заключить с работодателем');
        $('second_block_tooltip').grab($('calc_role'), 'after');
        $('link_role').set('html', 'с работодателем');
    }
    if(val == 2) {
        $('shadow_rez_type').addClass('b-shadow_right_0');
        
        $('block_calc_emp_text').set('html', 'Вы заплатите');
        $('block_calc_frl_text').set('html', 'Исполнитель получит');
        
        var frl_html = $('freelancer_block');
        var emp_html = $('emp_block');
        
        $('first_block').empty();
        $('first_block').grab(emp_html);
        $('second_block').empty();
        $('second_block').grab(frl_html);
        
        
        $('case_word').set('html', 'являющимся');
        $('calc_role').set('html', '&nbsp;хочу заключить с фри-лансером');
        $('first_block_tooltip').grab($('calc_role'), 'after');
        $('link_role').set('html', 'с фри-лансером');
    }
}

function setBlockScheme(act, u) {
    if(u == undefined) u= 0;
    if($('frl_type').get('value') == 2) {
        act = 1;
    }
    if(act == 1) {
        if(u == 0) $('bank_scheme').fireEvent('click');//click();
        $('bank_scheme').getParent().removeClass('b-filter__item_padbot_10');
        $('block_scheme').getElements('li a').each(function(elm) {
            if(elm.hasClass('b-filter__link_no') == false) {
                elm.getParent().hide();
            } 
        });
    } else if(act == 2) {
        var scheme = $('scheme_type').get('value'); 
        $('bank_scheme').getParent().addClass('b-filter__item_padbot_10');
        $('block_scheme').getElements('li a').each(function(elm) {
            if(elm.hasClass('b-filter__link_no') == false) {
                elm.getParent().show();
            } 
            
            if( (scheme == 1 || scheme == 4) && elm.hasClass('b-filter__pdrd') == true) {
                elm.getParent().hide();
            }
            
            if( (scheme == 2 || scheme == 5 ) && elm.hasClass('b-filter__pskb') == true) {
                elm.getParent().hide();
            }
        });
    } else if(act == 3) {
        $('bank_scheme').fireEvent('click');
        $('bank_scheme').getParent().addClass('b-filter__item_padbot_10');
        $('block_scheme').getElements('li a').each(function(elm) {
            if(elm.hasClass('b-filter__pskb') ==  true) {
                elm.getParent().hide();
            } else {
                elm.getParent().show();
            } 
            if(elm.hasClass('b-filter__pdrd') ==  true) {
                elm.getParent().show();
            } 
        });
    } else if(act == 4) {
        $('bank_scheme').fireEvent('click');
        $('bank_scheme').getParent().addClass('b-filter__item_padbot_10');
        $('block_scheme').getElements('li a').each(function(elm) {
            if(elm.hasClass('b-filter__pskb') ==  true) {
                elm.getParent().show();
            } 
            if(elm.hasClass('b-filter__pdrd') ==  true) {
                elm.getParent().hide();
            } 
        });
    }
}







