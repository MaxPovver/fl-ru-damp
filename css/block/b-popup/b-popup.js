window.addEvent('domready', 
function() {
	popup();
	function popup() {
	    
	    $$('.b-popup_center').each(function(popup_elm) {
	       /////////////////////////////////////////////
		// calculate height and width window       //
		/////////////////////////////////////////////                           
		   var w = 0, h = 0;
		// for opera
			var isOpera = (navigator.userAgent.indexOf("Opera") != -1);
			if(isOpera){    
			$$('html','body').setStyle('height','100%');
			w = document.body.clientWidth - parseInt(popup_elm.getStyle('width'));                        
			h = document.body.clientHeight -  parseInt(popup_elm.getStyle('height'));
		  }
		// for ie 
			var isIE = ((!isOpera)&&(navigator.appName.indexOf("Microsoft Internet Explorer") != -1));
			if(isIE){
			w = document.documentElement.clientWidth - parseInt(popup_elm.getStyle('width'));             
			h = document.documentElement.clientHeight - parseInt(popup_elm.getStyle('height')); 
			}
		// for firfox
			var isMozzila = (navigator.userAgent.toLowerCase().indexOf("gecko")!=-1)
			if(isMozzila){    
			w = window.innerWidth - parseInt(popup_elm.getStyle('width'));                                 
			h = window.innerHeight - parseInt(popup_elm.getStyle('height'));                      
		  } 
		 popup_elm.setStyle('top', h/2);
		 popup_elm.setStyle('left', w/2);  
	    });
	    
	    $$('.b-popup__close').addEvent('click',function(){
			$(this).getParent('.b-popup').setStyle('display', 'none'); 
			return false;
	     });
	    
	    $$('.b-popup').setStyle('margin-left', '0');
	    $$('.b-popup').setStyle('display', 'none');
	}
});







