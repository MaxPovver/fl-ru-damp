jQuery.noConflict();
jQuery(document).ready(function() {

	if(jQuery("[data-dropdown]").length)
		initializeDropdowns();
		
	if(jQuery("[data-rotator]").length)
		initializeRotators();
		
// 	0026700  вопрос №8
   jQuery('.b-dropdown-concealment-options-clause-link').bind('click',function(){jQuery('.b-dropdown-concealment-options-current-clause').removeClass('b-dropdown-concealment-options-current-clause');})
});


/* Dropdowns */
var dropdowns = {};
var dropdownsSettings = {};
var openedDropdown = null;

function initializeDropdowns() {
	
	jQuery("[data-dropdown]").each(function() {
		
		var dropdownID = generateIdentificator();
		var dropdownDescriptor = (jQuery(this).attr("data-dropdown-descriptor") ? jQuery(this).attr("data-dropdown-descriptor") : "");
		
		dropdowns[dropdownID] = {
			
			dropdown: jQuery(this),
			descriptor: dropdownDescriptor,
			color: (jQuery(this).attr("data-dropdown-color") ? jQuery(this).attr("data-dropdown-color") : ""),
			opener: jQuery(this).find("[data-dropdown-opener]"),
			concealment: jQuery(this).find("[data-dropdown-concealment]"),
			repository: (jQuery(this).closest("[data-dropdown-repository]").length ? jQuery(this).closest("[data-dropdown-repository]") : null),
			options: (jQuery(this).find("[data-dropdown-option]").length ? jQuery(this).find("[data-dropdown-option]") : null),
			optionsRepository: jQuery(this).find("[data-dropdown-options-repository]"),
			currentOption: jQuery(this).find("[data-dropdown-option][data-dropdown-option-descriptor='current']"),
			properties: (dropdownsSettings[dropdownDescriptor] ? dropdownsSettings[dropdownDescriptor] : {})
		};
		
		dropdowns[dropdownID].dropdown.attr("data-dropdown-identificator", dropdownID);
		
		dropdowns[dropdownID].opener.click(function() {
			
			return toggleDropdown(dropdownID);
		});
		
		dropdowns[dropdownID].concealment.find("[data-dropdown-option-link]").click(function() {

			dropdowns[dropdownID].opener.trigger("click");
		});

		dropdowns[dropdownID].dropdown.click(function(e) {

			e.stopPropagation();
		});

		jQuery(document).click(function() {

			if(openedDropdown && !openedDropdown.concealment.hasClass("g-hidden"))
				openedDropdown.opener.trigger("click");
		});
		
		if(dropdowns[dropdownID].properties.initializeFunction)
			executeFunction(dropdowns[dropdownID].properties.initializeFunction, null, dropdownID);
	});
}

function toggleDropdown(dropdownID) {
	
	if(dropdowns[dropdownID].repository && dropdowns[dropdownID].properties.repositoryAdditionalClass)
		dropdowns[dropdownID].repository.toggleClass(dropdowns[dropdownID].properties.repositoryAdditionalClass);
		
	if(dropdowns[dropdownID].concealment.is(":hidden") && !dropdowns[dropdownID].concealment.hasClass("g-hidden")) {
		
		dropdowns[dropdownID].concealment.css("display", "block");
		dropdowns[dropdownID].concealment.addClass("g-hidden");
	}
	
	if(dropdowns[dropdownID].concealment.hasClass("g-hidden") && openedDropdown)
		openedDropdown.opener.trigger("click");

	openedDropdown = (dropdowns[dropdownID].concealment.hasClass("g-hidden")) ? dropdowns[dropdownID] : null;

	dropdowns[dropdownID].dropdown.toggleClass("b-opened-dropdown");

	if(dropdowns[dropdownID].descriptor)
		dropdowns[dropdownID].dropdown.toggleClass("b-opened-" + dropdowns[dropdownID].descriptor + "-dropdown");
		
	dropdowns[dropdownID].concealment.toggleClass("g-hidden");
			
	return false;
}


/* Rotators */
var rotators = {};
var rotatorsProperties = {

	"promos": {
	
		animation: "displaying",
		automation: true,
		automationInterval: 5000,
		positionsPointsCurrentAdditionalClass: "b-promos-rotated-previews-positions-current-clause"
	}
};

function initializeRotators() {
	
	jQuery("[data-rotator]").each(function() {

		var rotatorID = generateIdentificator();
		var rotatorDescriptor = (jQuery(this).attr("data-rotator-descriptor") ? jQuery(this).attr("data-rotator-descriptor") : "");
		
		rotators[rotatorID] = {
			
			rotator: jQuery(this),
			descriptor: rotatorDescriptor,
			articles: jQuery(this).find("[data-rotator-article]"),
			articleWidth: jQuery(this).find("[data-rotator-article]").eq(0).width(),
			articlesCount: jQuery(this).find("[data-rotator-article]").length,
			articlesDistance: (parseInt(jQuery(this).find("[data-rotator-article]").last().css("marginLeft")) ? parseInt(jQuery(this).find("[data-rotator-article]").last().css("marginLeft")) : 0),
			articlesRepository: (jQuery(this).find("[data-rotator-articles-repository]").length ? jQuery(this).find("[data-rotator-articles-repository]") : jQuery(this)),
			articlesRepositoryWidth: (jQuery(this).find("[data-rotator-articles-repository]").length ? jQuery(this).find("[data-rotator-articles-repository]").width() : jQuery(this).width()),
			rollers: {

				backward: (jQuery(this).find("[data-rotator-roller][data-rotator-roller-descriptor='backward']").length ? jQuery(this).find("[data-rotator-roller][data-rotator-roller-descriptor='backward']") : null),
				forward: (jQuery(this).find("[data-rotator-roller][data-rotator-roller-descriptor='forward']").length ? jQuery(this).find("[data-rotator-roller][data-rotator-roller-descriptor='forward']") : null)
			},
			rollersTitles: {
				
				backward: (jQuery(this).find("[data-rotator-roller][data-rotator-roller-descriptor='backward']").find("[data-rotator-roller-title]").length ? jQuery(this).find("[data-rotator-roller][data-rotator-roller-descriptor='backward']").find("[data-rotator-roller-title]") : null),
				forward: (jQuery(this).find("[data-rotator-roller][data-rotator-roller-descriptor='forward']").find("[data-rotator-roller-title]").length ? jQuery(this).find("[data-rotator-roller][data-rotator-roller-descriptor='forward']").find("[data-rotator-roller-title]") : null)
			},
			currentPosition: 0,
			positionsPoints: (jQuery(this).find("[data-rotator-points-article]").length ? jQuery(this).find("[data-rotator-points-article]") : null),
			positionsPointsRepository: jQuery(this).find("[data-rotator-points]"),
			currentPositionsPoint: jQuery(this).find("[data-rotator-points-article][data-rotator-points-article-descriptor='current']"),
			indicator: (jQuery(this).find("[data-rotator-indicator]").length ? jQuery(this).find("[data-rotator-indicator]") : null),
			indicatorPosition: (jQuery(this).find("[data-rotator-indicator-position]").length ? jQuery(this).find("[data-rotator-indicator-position]") : null),
			indicatorQuantity: (jQuery(this).find("[data-rotator-indicator-quantity]").length ? jQuery(this).find("[data-rotator-indicator-quantity]") : null),
			paused: false,
			automationPaused: true,
			properties: (rotatorsProperties[rotatorDescriptor] ? rotatorsProperties[rotatorDescriptor] : {})
		}
		
		rotators[rotatorID].articlesRepository.scrollLeft(0);
		
		rotators[rotatorID].rotator.attr("data-rotator-identificator", rotatorID);
		
		if(rotators[rotatorID].indicatorPosition)
			rotators[rotatorID].indicatorPosition.text(rotators[rotatorID].currentPosition + 1);
			
		if(rotators[rotatorID].indicatorQuantity)
			rotators[rotatorID].indicatorQuantity.text(rotators[rotatorID].articlesCount);
		
		rotators[rotatorID].viewedArticlesCount = Math.ceil(rotators[rotatorID].articlesRepositoryWidth / (rotators[rotatorID].articleWidth + rotators[rotatorID].articlesDistance));
		
		if(rotators[rotatorID].rollers.backward) {
			
			rotators[rotatorID].rollers.backward.click(function(event) {

				event.preventDefault();
				rotators[rotatorID].automationPaused = true;
				turnRotator(rotatorID, "backward");
			});
		}
		
		if(rotators[rotatorID].rollers.forward) {
			
			if(rotators[rotatorID].properties.rollersDisabledClass && rotators[rotatorID].articlesCount > rotators[rotatorID].viewedArticlesCount)
				rotators[rotatorID].rollers.forward.toggleClass(rotators[rotatorID].properties.rollersDisabledClass);
			
			rotators[rotatorID].rollers.forward.click(function(event) {

				event.preventDefault();
				rotators[rotatorID].automationPaused = true;
				turnRotator(rotatorID, "forward");
			});
		}
		
		if(rotators[rotatorID].properties.swipe) {
			
			rotators[rotatorID].swipeStatus = false;
			rotators[rotatorID].swipePositions = {};
			
			rotators[rotatorID].rotator.on("touchstart mousedown", function (e) {
				
				rotators[rotatorID].swipeStatus = true;
				rotators[rotatorID].swipePositions = {
					
					x: e.originalEvent.pageX,
					y: e.originalEvent.pageY
				};
			});

			rotators[rotatorID].rotator.on("touchend mouseup", function (e) {
				
				rotators[rotatorID].swipeStatus = false;
				rotators[rotatorID].swipePositions = null;
			});
			
			rotators[rotatorID].rotator.on( "touchmove mousemove", function (e) {
								
				if (!rotators[rotatorID].swipeStatus)
					return;
					
				if(Math.abs(getSwipeInformation(e, rotators[rotatorID].swipePositions).offset.x) > (rotators[rotatorID].articleWidth / 3)) {
					
					turnRotator(rotatorID, (getSwipeInformation(e, rotators[rotatorID].swipePositions).direction.x == "left" ? "forward" : "backward"));
					
					rotators[rotatorID].swipePositions = {
					
						x: e.originalEvent.pageX,
						y: e.originalEvent.pageY
					};
				}
				
				e.preventDefault();
			});
		}

		if(rotators[rotatorID].positionsPoints) {

			rotators[rotatorID].positionsPoints.each(function(index) {

				jQuery(this).click(function(e) {

					e.preventDefault();
					hurlRotator(rotatorID, index);
				});
			})
		}
		
		if(rotators[rotatorID].properties.automation) {

			rotators[rotatorID].automationPaused = false;
			rotators[rotatorID].automationID = setInterval(function() {

				if (!rotators[rotatorID].automationPaused)
					turnRotator(rotatorID, 'forward');

			}, rotators[rotatorID].properties.automationInterval);

			rotators[rotatorID].rotator.mouseenter(function() {
				
				rotators[rotatorID].automationPaused = true;
				
			})
			.mouseleave(function() {
				
				rotators[rotatorID].automationPaused = false;
				
			});
		}
	
		if(rotators[rotatorID].properties.initializeFunction)
			executeFunction(rotators[rotatorID].properties.initializeFunction, null, rotatorID);
	});
}

function turnRotator(rotatorID, direction) {

	if(rotators[rotatorID].properties.animation == "conveyor") {
		
		var animation = { scrollLeft: ((direction == "forward") ? "+" : "-") + "=" + (rotators[rotatorID].articleWidth + rotators[rotatorID].articlesDistance)};
		
		if(rotators[rotatorID].properties.cycle) {
		
			if(direction == "backward") {
			
				rotators[rotatorID].rotator.find("[data-rotator-article]").filter(":last").clone(true).prependTo(rotators[rotatorID].articlesRepository);
				rotators[rotatorID].rotator.find("[data-rotator-article]").filter(":last").remove();
				
				rotators[rotatorID].articlesRepository.scrollLeft((rotators[rotatorID].articleWidth + rotators[rotatorID].articlesDistance));
				
				rotators[rotatorID].articlesRepository.stop(true, true).animate(animation, 350);
			
			} else {
				
				rotators[rotatorID].rotator.find("[data-rotator-article]").filter(":first").clone(true).appendTo(rotators[rotatorID].articlesRepository);
								
				rotators[rotatorID].articlesRepository.stop(true, true).animate(animation, 350, function() {

					rotators[rotatorID].rotator.find("[data-rotator-article]").filter(":first").remove();
					rotators[rotatorID].articlesRepository.scrollLeft(0);
				});
				
			}

		} else {
		
			if((direction == "forward" && rotators[rotatorID].currentPosition < (rotators[rotatorID].articlesCount - rotators[rotatorID].viewedArticlesCount)) || (direction == "backward" && rotators[rotatorID].currentPosition > 0))
				rotators[rotatorID].articlesRepository.stop(true, true).animate(animation, 350);
			else
				return false;
		}

		rotators[rotatorID].currentPosition = ((direction == "forward") ? (rotators[rotatorID].currentPosition + 1) : (rotators[rotatorID].currentPosition - 1));

		if(rotators[rotatorID].properties.rollersDisabledClass && ((rotators[rotatorID].currentPosition > 0 && rotators[rotatorID].rollers.backward.hasClass(rotators[rotatorID].properties.rollersDisabledClass)) || (rotators[rotatorID].currentPosition == 0 && !rotators[rotatorID].rollers.backward.hasClass(rotators[rotatorID].properties.rollersDisabledClass))))
			rotators[rotatorID].rollers.backward.toggleClass(rotators[rotatorID].properties.rollersDisabledClass);

		if(rotators[rotatorID].properties.rollersDisabledClass && ((rotators[rotatorID].currentPosition < (rotators[rotatorID].articlesCount - rotators[rotatorID].viewedArticlesCount) && rotators[rotatorID].rollers.forward.hasClass(rotators[rotatorID].properties.rollersDisabledClass)) || (rotators[rotatorID].currentPosition == (rotators[rotatorID].articlesCount - rotators[rotatorID].viewedArticlesCount) && !rotators[rotatorID].rollers.forward.hasClass(rotators[rotatorID].properties.rollersDisabledClass))))
			rotators[rotatorID].rollers.forward.toggleClass(rotators[rotatorID].properties.rollersDisabledClass);
		
	} else if(rotators[rotatorID].properties.animation == "displaying") {
		
		rotators[rotatorID].articles.eq(rotators[rotatorID].currentPosition).animate({ opacity: 0 }, 350, function() {
			
			jQuery(this).addClass("g-hidden");
			
			if(direction == "forward")
				rotators[rotatorID].currentPosition = (((rotators[rotatorID].currentPosition + 1) < rotators[rotatorID].articlesCount) ? (rotators[rotatorID].currentPosition + 1) : 0);
			else
				rotators[rotatorID].currentPosition = (((rotators[rotatorID].currentPosition - 1) >= 0) ? (rotators[rotatorID].currentPosition - 1) : (rotators[rotatorID].articlesCount - 1));
			
			rotators[rotatorID].articles.eq(rotators[rotatorID].currentPosition).css("opacity", "0").removeClass("g-hidden").animate({ opacity: 1 }, 350);
			
			if(rotators[rotatorID].positionsPoints && rotators[rotatorID].properties.positionsPointsCurrentAdditionalClass) {

				rotators[rotatorID].currentPositionsPoint
					.toggleClass(rotators[rotatorID].properties.positionsPointsCurrentAdditionalClass)
					.removeAttr("data-rotator-positions-point-descriptor");

				rotators[rotatorID].positionsPoints.eq(rotators[rotatorID].currentPosition)
					.addClass(rotators[rotatorID].properties.positionsPointsCurrentAdditionalClass)
					.attr("data-rotator-positions-point-descriptor", "current");

				rotators[rotatorID].currentPositionsPoint = rotators[rotatorID].positionsPoints.eq(rotators[rotatorID].currentPosition);
			}
			
			return true;
		});
	
	} else if(rotators[rotatorID].properties.animation == "swipe") {
		
		
		
	} else if(!rotators[rotatorID].properties.animation || rotators[rotatorID].properties.animation == "simple") {
		
		rotators[rotatorID].articles.eq(rotators[rotatorID].currentPosition).addClass("g-hidden");
			
		if(direction == "forward")
			rotators[rotatorID].currentPosition = (((rotators[rotatorID].currentPosition + 1) < rotators[rotatorID].articlesCount) ? (rotators[rotatorID].currentPosition + 1) : 0);
		else
			rotators[rotatorID].currentPosition = (((rotators[rotatorID].currentPosition - 1) >= 0) ? (rotators[rotatorID].currentPosition - 1) : (rotators[rotatorID].articlesCount - 1));
			
		rotators[rotatorID].articles.eq(rotators[rotatorID].currentPosition).removeClass("g-hidden");
	}
	
	if(rotators[rotatorID].positionsPoints && rotators[rotatorID].properties.positionsPointsCurrentAdditionalClass) {
		
		rotators[rotatorID].currentPositionsPoint
			.toggleClass(rotators[rotatorID].properties.positionsPointsCurrentAdditionalClass)
			.removeAttr("data-rotator-positions-point-descriptor");
		
		rotators[rotatorID].positionsPoints.eq(rotators[rotatorID].currentPosition)
			.addClass(rotators[rotatorID].properties.positionsPointsCurrentAdditionalClass)
			.attr("data-rotator-positions-point-descriptor", "current");
		
		rotators[rotatorID].currentPositionsPoint = rotators[rotatorID].positionsPoints.eq(rotators[rotatorID].currentPosition);
	}
}

function hurlRotator(rotatorID, articleCounter) {

	if(rotators[rotatorID].properties.animation == "displaying") {
		
		if(!rotators[rotatorID].paused) {
			
			rotators[rotatorID].paused = true;
			
			if(rotators[rotatorID].rotator.find("[data-rotator-background]").length)
				rotators[rotatorID].rotator.find("[data-rotator-background]").fadeOut(350);
		
			rotators[rotatorID].articles.eq(rotators[rotatorID].currentPosition).animate({ opacity: 0 }, 350, function() {
			
				jQuery(this).addClass("g-hidden");
			
				rotators[rotatorID].currentPosition = (((articleCounter) < rotators[rotatorID].articlesCount) ? (articleCounter) : 0);
					
				if(rotators[rotatorID].indicatorPosition)
					rotators[rotatorID].indicatorPosition.text(articleCounter);
					
				if(rotators[rotatorID].articles.eq(rotators[rotatorID].currentPosition).data("rotator-article-background-url")) {
				
					//jQuery("[data-rotator-background]").attr("class", "b-promos-rotated-previews-background b-promos-rotated-previews-" + rotators[rotatorID].articles.eq(rotators[rotatorID].currentPosition).attr("data-rotator-article-descriptor") + "-background").fadeIn(350);
					jQuery("[data-rotator-background]").css({ backgroundImage: "url(" + rotators[rotatorID].articles.eq(rotators[rotatorID].currentPosition).data("rotator-article-background-url") + ")" }).fadeIn(350);
				}
			
				rotators[rotatorID].articles.eq(rotators[rotatorID].currentPosition).css("opacity", "0").removeClass("g-hidden").animate({ opacity: 1 }, 350);
			
				if(rotators[rotatorID].positionsPoints && rotators[rotatorID].properties.positionsPointsCurrentAdditionalClass) {

					rotators[rotatorID].currentPositionsPoint
						.toggleClass(rotators[rotatorID].properties.positionsPointsCurrentAdditionalClass)
						.removeAttr("data-rotator-positions-point-descriptor");

					rotators[rotatorID].positionsPoints.eq(rotators[rotatorID].currentPosition)
						.addClass(rotators[rotatorID].properties.positionsPointsCurrentAdditionalClass)
						.attr("data-rotator-positions-point-descriptor", "current");

					rotators[rotatorID].currentPositionsPoint = rotators[rotatorID].positionsPoints.eq(rotators[rotatorID].currentPosition);
				}
				
				rotators[rotatorID].paused = false;
			
				return true;
			});
		}
	
	} else if(rotators[rotatorID].properties.animation == "swipe") {
		
		
		
	} else if(!rotators[rotatorID].properties.animation || rotators[rotatorID].properties.animation == "simple") {
		
		rotators[rotatorID].articles.eq(rotators[rotatorID].currentPosition).addClass("g-hidden");
			
		if(direction == "forward")
			rotators[rotatorID].currentPosition = (((rotators[rotatorID].currentPosition + 1) < rotators[rotatorID].articlesCount) ? (rotators[rotatorID].currentPosition + 1) : 0);
		else
			rotators[rotatorID].currentPosition = (((rotators[rotatorID].currentPosition - 1) >= 0) ? (rotators[rotatorID].currentPosition - 1) : (rotators[rotatorID].articlesCount - 1));
			
		rotators[rotatorID].articles.eq(rotators[rotatorID].currentPosition).removeClass("g-hidden");
	}
	
	if(rotators[rotatorID].positionsPoints && rotators[rotatorID].properties.positionsPointsCurrentAdditionalClass) {
		
		rotators[rotatorID].currentPositionsPoint
			.toggleClass(rotators[rotatorID].properties.positionsPointsCurrentAdditionalClass)
			.removeAttr("data-rotator-positions-point-descriptor");
		
		rotators[rotatorID].positionsPoints.eq(rotators[rotatorID].currentPosition)
			.addClass(rotators[rotatorID].properties.positionsPointsCurrentAdditionalClass)
			.attr("data-rotator-positions-point-descriptor", "current");
		
		rotators[rotatorID].currentPositionsPoint = rotators[rotatorID].positionsPoints.eq(rotators[rotatorID].currentPosition);
	}
	
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