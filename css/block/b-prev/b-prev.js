window.addEvent('domready', function() {
	$$('.b-prev__link').addEvent('click',function(){
		this.getParent('.b-prev__list').getElements('.b-prev__dt').removeClass('b-prev__dt_active');
		this.getParent('.b-prev__list').getElements('.b-prev__dd').addClass('b-prev__dd_hide');
		this.getParent('.b-prev__dt').addClass('b-prev__dt_active');
		this.getParent('.b-prev__dt').getNext('.b-prev__dd').removeClass('b-prev__dd_hide');
		return false;
		})
	});





