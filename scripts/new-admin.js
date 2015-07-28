window.addEvent('domready',
	function() {
				$$('.ban-razban .toggle a').addEvent('click', function(){
														   this.getParent('h4').getNext('.slideBlock').toggleClass('filtr-hide');
														   return false;
														   });
				$$('.search-item-info .toggle a').addEvent('click', function(){
														   this.getParent('.toggle').getNext('.edit-data').toggleClass('form-hide');
														   return false;
														   });
				$$('.ov-btns a.lnk-dot-grey').addEvent('click', function(){
														  $$('#ov-notice3','#ov-notice22', '#ov-notice').setStyle('display','none');return false;
														  })
				$$('#ov-notice4 a.close').addEvent('click', function(){
														  $$('#ov-notice4').setStyle('display','none');return false;
														  })

});
