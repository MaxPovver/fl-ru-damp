window.addEvent('domready', 
function() {
					$$('a[id^=a]').addEvent('click', function(){
	     if(this.hasClass('b-layout__link_bordbot_dot_0f71c8')){
								this.getParent('.b-layout__txt').getElements('.b-layout__link').removeClass('b-layout__link_color_000 b-layout__link_no-decorat').addClass('b-layout__link_bordbot_dot_0f71c8');
								this.addClass('b-layout__link_color_000 b-layout__link_no-decorat').removeClass('b-layout__link_bordbot_dot_0f71c8');
								this.getParent('.b-layout__txt').getNext('.b-layout__txt').fade('out');
								var pic=$$('#d'+this.getProperty('id').charAt(1))
								setTimeout(function() { 
											$$('div[id^=d]').addClass('b-layout__txt_hide'); 
											pic.removeClass('b-layout__txt_hide'); 
											$$('a[id^=a]').getParent('.b-layout__txt').getNext('.b-layout__txt').fade('in');
									}, 1000);
	   			}
							return false;
				})
})