// ****** Editable block ******
var ar_width	= '240';
var ar_height	= '400';
var ar_html		= 'https://www.fl.ru/googleadsense.html';
/* only for ajax */
/*Do not touch any below*/

var p = document.getElementById(ar_ph);
var a = p.adriver;
a.detachScript(); 

a.onDomReady(function(){
	p.innerHTML = '<iframe width="'+ar_width+'" height="'+ar_height
		+'" marginwidth=0 marginheight=0 scrolling=no frameborder=0 src="'+ar_html+'"><\/iframe>';
});