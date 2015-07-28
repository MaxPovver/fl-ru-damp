jQuery.noConflict();

jQuery(document).ready(function( $ ) {

	if ($("[data-accordion]").length)
		initializeAccordions();

	if ($("[data-menu]").length)
		initializeMenu();

	if ($(window).width() <= 740 && !$('#filtrToggle').hasClass('b-layout_hide'))
		$('[onclick="togF(this);"]').click(); 

	var isAndroid = navigator.userAgent.toLowerCase().indexOf("android") > -1;
	if(isAndroid) {
        
		$('body').addClass('android');
		
		$('[onclick="togF(this);"]').on("touchend", function(event) {

			event.preventDefault();
			togF(this);
		});
	}

});

/* Accordions */

var accordions = {};
var accordionsProperties = {

	"nav": {

		openedclass: 'b-menu-opened',
		preventDefault: false
	},
	"worktype": {

		openedclass: 'b-page__filter-opened',
		preventDefault: true
	}, 
	"freelancertype": {

		openedclass: 'b-catalog-opened',
		preventDefault: true
	}
};

function initializeAccordions() {
	
	jQuery("[data-accordion]").each(function() {

		var accordionID = generateIdentificator();
		var accordionDescriptor = (jQuery(this).attr("data-accordion-descriptor") ? jQuery(this).attr("data-accordion-descriptor") : "");
		
		accordions[accordionID] = {
			
			accordion: jQuery(this),
			descriptor: accordionDescriptor,
			opener: jQuery('[data-accordion-opener][data-accordion-descriptor="' + accordionDescriptor + '"]'),
			opened: false,
			properties: (accordionsProperties[accordionDescriptor] ? accordionsProperties[accordionDescriptor] : {})
		};
		
		accordions[accordionID].accordion.attr("data-accordion-identificator", accordionID);

		accordions[accordionID].opener.click( function(event) {

			if (jQuery(window).width() <= 1000 && accordions[accordionID].properties.preventDefault)
				event.preventDefault();
			accordions[accordionID].accordion.toggleClass(accordions[accordionID].properties.openedclass);
		});

	});
}

/* Menus */

var menu = {};
var menuProperties = {

	"nav": {

		openedclass: 'b-menu-opened',
		preventDefault: true
	},
	"worktype": {

		openedclass: 'b-page__filter-opened',
		preventDefault: true
	}, 
	"freelancer-type": {

		openedclass: 'b-catalog-opened',
		preventDefault: true
	},
	"main": {

		openedclass: 'b-shadow_hide',
		preventDefault: true
	}, 
	"profile-nav": {

		openedclass: 'b-menu__list-opened',
		preventDefault: true
	},
	"search-nav": {

		openedclass: 'b-menu__list-opened',
		preventDefault: true
	},
	"community-list": {

		openedclass: 'b-menu__list-opened',
		preventDefault: true
	},
	"community-discussions": {

		openedclass: 'b-menu__list-opened',
		preventDefault: true
	}
};

function initializeMenu() {
	
	jQuery("[data-menu]").each(function() {

		var menuID = generateIdentificator();
		var menuDescriptor = (jQuery(this).attr("data-menu-descriptor") ? jQuery(this).attr("data-menu-descriptor") : "");
		
		menu[menuID] = {
			
			menu: jQuery(this),
			descriptor: menuDescriptor,
			opener: jQuery('[data-menu-opener][data-menu-descriptor="' + menuDescriptor + '"]'),
			opened: false,
			properties: (menuProperties[menuDescriptor] ? menuProperties[menuDescriptor] : {})
		};
		
		menu[menuID].menu.attr("data-menu-identificator", menuID);


        if(jQuery('body').hasClass('android')){
			menu[menuID].opener.touchstart( function(event) {
	
				if (jQuery(window).width() <= 1000 && menu[menuID].properties.preventDefault)
					event.preventDefault();
				menu[menuID].menu.toggleClass(menu[menuID].properties.openedclass);
			});
			}else{
		
		menu[menuID].opener.click( function(event) {

			if (jQuery(window).width() <= 1000 && menu[menuID].properties.preventDefault)
				event.preventDefault();
			menu[menuID].menu.toggleClass(menu[menuID].properties.openedclass);
		});
		}
	});
}



/* Unsorted */
function executeFunction(name, context) {
	
	var context = context ? context : window;
	var properties = Array.prototype.slice.call(arguments).splice(2, 100);
	var namespaces = name.split(".");
	var func = namespaces.pop();
	
	for(var i = 0; i < namespaces.length; i++) {
		
		context = context[namespaces[i]];
	}
	
	return context[func].apply(this, properties);
}

function getElementPercentageWidth(element) {
	
	var width = element.width();
	var parentWidth = element.offsetParent().width();
	
	return Math.ceil(100 * (width / parentWidth));
}

function getSubstring(string, substringPattern) {
	
	var searchResults = string.match(substringPattern);
	
	return ((searchResults && searchResults[1]) ? searchResults[1] : "");
}

var identificators = {};

function generateIdentificator() {

	var identificator = '';
	var identificatorLength = 10;
	var charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	var charsetLength = charset.length;

	for (i = 0; identificatorLength > i; i += 1) {
  
		var charIndex = Math.random() * charsetLength;  
		identificator += charset.charAt(charIndex);  
	}
	
	identificator = identificator.toLowerCase();

	if (identificators[identificator])
		return generateIdentificator();

	identificators[identificator] = true;  

	return identificator;
}