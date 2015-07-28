window.addEvent('domready', 
function() {
	try {
	$$('.b-filter__body').getElement('.b-filter__link').addEvent('click',function(){
        if($(this).hasClass('b-filter__sbr_order')) return;
		$$('.b-filter__toggle').removeClass('b-filter__toggle_hide');
		var overlay=document.createElement('div');
		overlay.className='b-filter__overlay';
		$$('.b-filter').grab(overlay, 'bottom');
		$$('.b-filter__overlay').addEvent('click',function(){
			$$('.b-filter__toggle').addClass('b-filter__toggle_hide');
			$$('.b-filter__overlay').dispose();
		});
		return false;
	});
	} catch(e) { } 
	$$('.b-filter__link_toggler').addEvent('click',function(){
		$$('.b-filter__toggle').addClass('b-filter__toggle_hide');
		$$('.b-filter__overlay').dispose();
		return false;
	});
});





