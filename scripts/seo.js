window.addEvent('domready',
	function() {
	    $$('.seo-left li i').addEvent('click', function(){
            var activeItems = new Array();
            if(JSON.decode(Cookie.read('seocatalogmenu'))==null) {
                activeItems = new Array();
            } else {
                activeItems = JSON.decode(Cookie.read('seocatalogmenu'));
            }
            var itemId = this.getParent('li').get('id').replace(/section_/,'');
            if(this.getParent('li').hasClass('active')) {
                var aLength = activeItems.length;
                for (var i=0; i < aLength; i++) {
                    if(activeItems[i]==itemId) {
                        delete activeItems[i];
                    }
                }
                delete activeItems[itemId];
            } else {
                activeItems.push(itemId);
            }
            Cookie.write('seocatalogmenu', JSON.encode(activeItems));

	        this.getParent('li').toggleClass('active');
	        return false;
	    });
	});
	

function init_collapse_button(name) {
    $$('#'+name+' i').addEvent('click', function(){
        this.getParent('li').toggleClass('active');
        return false;
	});
}

function toggle_tree(action) {
    if(action == 1) {
        $$('li[id^=section_] i').each(function(el){
            el.getParent('li').addClass('active');
        });
    } else {
       $$('li[id^=section_] i').each(function(el){
            el.getParent('li').removeClass('active');
       }); 
    }
}
	
function getFormToArray(form_name) {
    var form = $(form_name).elements;
    var arr = new Array();
    for(var i = 0; i < form.length;i++) {
        var elm = form[i];
        if($(elm).hasClass('ckeditor')) {
            arr[elm.name] = CKEDITOR.instances[elm.id].getData();
        } else if(elm.name) {
            arr[elm.name] = elm.value;
        }
    }
    return arr;
}

function initCI(ciid){
    var ci,cis;
    if(ci=document.getElementById(ciid)){
        ctgCI.push(cis=ci.style);
        cis.display='none';
    }
}

function initCtg(gr_num) {
    gr_num=gr_num==null?-1:gr_num;
    var ci,myAccordion;
    while(ci=ctgCI.pop()) ci.display='';
    myAccordion = new Accordion($('accordion'), 'a.toggler', 'ul.element', {
        opacity: false, 
        alwaysHide: true, 
        show: gr_num, 
        duration: 400,
        onActive: function(toggler, element) {
            toggler.setStyle('backgroundPosition', '-169px -279px');
            toggler.addClass('a');
        },
        onBackground: function(toggler, element) {
            toggler.setStyle('backgroundPosition', '-149px -291px');
            toggler.removeClass('a');
        }
    });
}
