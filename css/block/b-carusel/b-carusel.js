// эти функции перенесены в new.js
window.addEvent('domready', 
function() {
	 $$('.b-carusel__prev').addEvent('mouseover',function(){$(this).addClass('b-carusel__prev_hover');}).addEvent('mouseout',function(){$(this).removeClass('b-carusel__prev_hover');})
	 $$('.b-carusel__next').addEvent('mouseover',function(){$(this).addClass('b-carusel__next_hover');}).addEvent('mouseout',function(){$(this).removeClass('b-carusel__next_hover');})
		 
	$$( ".b-carusel__prev" ).addEvent( "mousedown", function() {
		this.addClass( "b-carusel__prev_active" );
	}).addEvent( "mouseup", function() {
		this.removeClass( "b-carusel__prev_active");
	}).addEvent( "mouseleave", function() {
		this.fireEvent( "mouseup" );
	});
		 
	$$( ".b-carusel__next" ).addEvent( "mousedown", function() {
		this.addClass( "b-carusel__next_active"  );
	}).addEvent( "mouseup", function() {
		this.removeClass( "b-carusel__next_active" );
	}).addEvent( "mouseleave", function() {
		this.fireEvent( "mouseup" );
	});

	function initHScroll() {
		var lst = $('top-payed');
		if (!lst) { return;}
		var cnt = lst.getParent();
		btns = cnt.getParent().getElements('span[class^=b-carusel__]');
		var btnl = btns[0];
		var btnr = btns[1];
		if (lst.getElements('li').length <= 4) {
			btnl.addClass('b-carusel__prev_disabled');
			btnr.addClass('b-carusel__next_disabled');
		} 
		all = lst.getElements('li').length;
		one = lst.getElements('li')[0].getSize();
		var myFx = new Fx.Scroll(cnt, {
			onComplete: function() {
				scr = cnt.getScroll();
				pos = 4 + (scr.x/one.x).floor();
				if(pos >= all) {
					btnr.addClass('b-carusel__next_disabled');
				} else {
					btnr.removeClass('b-carusel__next_disabled');
				}
				if((scr.x/one.x).floor() > 0) {
					btnl.removeClass('b-carusel__prev_disabled');
				} else {
					btnl.addClass('b-carusel__prev_disabled');
				}
			}
		});
		btns.addEvent('click', function(e) {
			e.preventDefault();
			if((this.hasClass('b-carusel__prev_disabled'))||(this.hasClass('b-carusel__next_disabled'))) return false;
			scr = cnt.getScroll();
			if(this.hasClass('b-carusel__next')) {
				pos = (scr.x/one.x).floor()+1;
			} else {
				pos = (scr.x/one.x).floor()-1;
			}
			nxt = lst.getElements('li')[pos];
			myFx.toElement(nxt, 'x');
		});
	}
	 
})






