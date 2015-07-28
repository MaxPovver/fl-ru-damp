window.addEvent('domready', function() { 

    var fixVideoFooter = function() {
        var blockHeight = $$('.b-video')[0].getSize().y;
        var windowHeight = $(window).getSize().y;
        var offset = $(window).getScroll().y;
        
        if(offset <= (blockHeight - windowHeight)) {
            $$('.b-video__foot').addClass('b-video__foot_fix');
        } else {
            $$('.b-video__foot').removeClass('b-video__foot_fix');
        }
    }
	  var vidos = $('vidos');
	  vidos.oncanplaythrough=fixVideoFooter;

    window.addEvent('scroll', function() {
        fixVideoFooter();
    }); 

    window.addEvent('resize', function() {
        fixVideoFooter();
    }); 
    fixVideoFooter();
    
});