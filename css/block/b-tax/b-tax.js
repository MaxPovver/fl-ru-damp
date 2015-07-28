window.addEvent('domready', 
function() {
	
	$$('.open-tax').addEvent('click',function(){
			this.getParent('.b-layout__txt').getNext('.b-tax').toggleClass('b-tax_hide');
			return false;
		})
		
		
		
});

