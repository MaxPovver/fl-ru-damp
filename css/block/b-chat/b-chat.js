window.addEvent('domready', 
function() {
	
			$$('.b-chat__list').addEvent('mouseover',function() {
				this.removeClass('b-chat_width_40').addClass('b-chat_width_200');
				this.getElement('.b-chat__foot').getElement('.b-chat__link').set('html', '<span class="b-chat__icon b-chat__icon_tune"></span>Настройка');
				this.getElements('.b-chat__name').removeClass('b-chat__name_hide');
				this.getElement('.b-chat__head_small').addClass('b-chat__head_hide');
				this.getElement('.b-chat__head_full').removeClass('b-chat__head_hide');
			}).addEvent('mouseout',function() {
				this.removeClass('b-chat_width_200').addClass('b-chat_width_40');
				this.getElement('.b-chat__foot').getElement('.b-chat__link').set('html', '<span class="b-chat__icon b-chat__icon_tune"></span>');
				this.getElements('.b-chat__name').addClass('b-chat__name_hide');
				this.getElement('.b-chat__head_small').removeClass('b-chat__head_hide');
				this.getElement('.b-chat__head_full').addClass('b-chat__head_hide');
			});
	
	
	
	
	$$('.b-chat__head_curt .b-chat__link_toggle').addEvent('click',function(){
		$$('.b-chat').addClass('b-chat__list');
		$$('.b-chat__head_curt').addClass('b-chat__head_hide');
		$$('.b-chat__head_full').removeClass('b-chat__head_hide');
		$$('.b-chat__users').removeClass('b-chat__users_hide');
		$$('.b-chat__foot').removeClass('b-chat__foot_hide');
		this.getParent('.b-chat').getElements('.b-chat__name').removeClass('b-chat__name_hide');
		return false;
		})
		
	$$('.b-chat__head_full .b-chat__link_toggle').addEvent('click',function(){
		$$('.b-chat').removeClass('b-chat__list');
		$$('.b-chat__head_curt').removeClass('b-chat__head_hide');
		$$('.b-chat__head_full').addClass('b-chat__head_hide');
		$$('.b-chat__head_small').addClass('b-chat__head_hide');
		$$('.b-chat__users').addClass('b-chat__users_hide');
		$$('.b-chat__foot').addClass('b-chat__foot_hide');
		this.getParent('.b-chat').getElements('.b-chat__name').addClass('b-chat__name_hide');
		return false;
		})
	
	$$('.b-chat__head_curt .b-chat__link_tune','.b-chat__foot .b-chat__link_tune').addEvent('click',function(){
		$$('.b-chat__contact').removeClass('b-chat__contact_hide');
		$$('.b-chat__head_contact').removeClass('b-chat__head_hide');
		$$('.b-chat__head_curt').addClass('b-chat__head_hide');
		$$('.b-chat__head_full').addClass('b-chat__head_hide');
		$$('.b-chat__head_small').addClass('b-chat__head_hide');
		$$('.b-chat__users').addClass('b-chat__users_hide');
		$$('.b-chat__foot').addClass('b-chat__foot_hide');
		return false;
		})
		
	$$('.b-chat__contact .b-buttons__link').addEvent('click',function(){
		$$('.b-chat__contact').addClass('b-chat__contact_hide');
		$$('.b-chat__head_contact').addClass('b-chat__head_hide');
		$$('.b-chat__head_curt').removeClass('b-chat__head_hide');
		return false;
		})
	
	$$('.b-chat__icon_sound-on').getParent('div.b-chat__txt').getElement('.b-chat__link').addEvent('click',function(){
		$$('.b-chat__icon_sound-off').getParent('div.b-chat__txt').removeClass('b-chat__txt_hide');
		this.getParent('div.b-chat__txt').addClass('b-chat__txt_hide');
		return false;
		})
	$$('.b-chat__icon_sound-off').getParent('div.b-chat__txt').getElement('.b-chat__link').addEvent('click',function(){
		$$('.b-chat__icon_sound-on').getParent('div.b-chat__txt').removeClass('b-chat__txt_hide');
		this.getParent('div.b-chat__txt').addClass('b-chat__txt_hide');
		return false;
		})
	
	
	
})
