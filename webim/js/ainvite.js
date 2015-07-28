







function dump_object(obj, c) {
	var def_c = c;
	var t = "";
	
	for(var i = 0; i < c; i++ ) t += "\t";
	

	res = "{\n";

	for(var o in obj) {
		c = def_c ? def_c : 1;
		if(typeof(obj[o]) == 'object') {
			res += t + o + ':';
			res += ' ' + dump_object(obj[o], ++c);
			res += ",\n";
		} else {
			res += t + o + ':';
			res += ' ' + obj[o];
			res += ",\n";
		}
	}
	res += t.substring(1)+"}"
	
	return res;
}

function show_dump(str, refresh) {
	var body = document.getElementsByTagName("body")[0];
	var pre = null;
	if(!refresh) {
		pre = document.createElement("pre");
	} else {
		pre = body.getElementsByTagName("pre")[0];
	}
	pre.innerHTML = str;
	body.appendChild(pre);
}

function AInvite() {  
	this.init = function(rules, invite_callback) {
		if(! rules instanceof Array) {
			throw new Error("Wrong rules");
		}
		
		this.invite_callback = invite_callback;
		this.rules = rules;
		this.loadStats();
		this.createIniviteDiv();
		
		this.url = window.location.href;
		this.url = this.url.substring(this.url.indexOf('://') + 3);
		this.url = this.url.substring(this.url.indexOf('/'));

		var lp = this.stats.getLastPage();
		if(lp != undefined && lp.url == this.url) {
			this.current_page = this.stats.getLastPageIndex();
		} else {
			this.current_page = this.stats.addVisitedPage(this.url, 1, document.referrer);
		}
		this.storeStats();
	//	alert(document.cookie)
	//	alert(this.stats.getTotalTimeOnPage(url) + ' ' + this.stats.total_time + ' ' + this.stats.visited_pages.length);
		var o = this;
		
		setInterval(
			function() {
				o.pageUpdate(); 
				o.checkTimeOnSite(); 
				o.checkVisitedPages();
				//show_dump(dump_object(o.rules), true); 
				<?php if(!$no_operators && !$user_in_chat): ?>o.tryInvitation();<?php endif; ?>
			}, 
			1000
		);
		this.checkReferrer();
		this.checkNumberOfPages();
	};
	
	this.tryInvitation = function() {
		for(var o in this.rules) {
			var conds = this.rules[o].conditions;
			var passed = this.rules[o].passed;
			var conds_count = 0;
			var passed_count = 0;
		
			for(var k in conds) conds_count++;
			for(var i in passed) passed_count++;
			//alert(o + " " + passed_count + " : " +conds_count);
			if(passed_count == conds_count) {
				this.showInvitation(this.rules[o]);
				break;
			}
		}
	};
	
	this.showInvitation = function(rule) {
		if (this.invite_show == undefined) {
			this.messageSpan.innerHTML = rule.text;
			this.inviteDiv.style.display = 'block';
			if(this.invite_callback != undefined) {
				this.invite_callback(rule.id);
			}
			
			this.invite_show = true;
		}
	};
	
	this.checkVisitedPages = function() {
    if(!this.stats instanceof Stats) {
      return false;
    }
		for(var o in this.rules) {
			var rule = this.rules[o];
			var visited_pages_conditions = rule.conditions.visited_pages;
			var passed = false;
			if(! visited_pages_conditions instanceof Array) {
				continue;
			}
			
			if(!rule.conditions.order_matters) {				
				for(var k in visited_pages_conditions) {
					var cond = visited_pages_conditions[k];
					var time = this.stats.getTotalTimeOnPage(cond.url);
										
					if(cond.time > time) {
						passed = false;
						break;
					} else {
						passed = true;
					}
				}	
	 		} else {
				var offset = 0;
				passed = true;
				
				for(var k in visited_pages_conditions) {
					var cond = visited_pages_conditions[k];
					var vp_idx = this.stats.getPageIndex(cond.url, offset);
												
					if(vp_idx == null) {
						passed = false;
						break;
					}
					
					if(cond.time > this.stats.visited_pages[vp_idx].time) {
						passed = false;
						break;
					}
					
					offset = vp_idx;
				}
			}
			
			if(passed) {
				if(rule.passed == undefined) {
					rule.passed = new Object();
				}

				rule.passed.visited_pages = true;
			}
		}	
	};
	
	this.checkNumberOfPages = function() {
    if(!this.stats instanceof Stats) {
      return false;
    }
		for(var o in this.rules) {
			var rule = this.rules[o];
			var number_of_pages = rule.conditions.number_of_pages;
			if(number_of_pages == undefined || number_of_pages > this.stats.visited_pages.length) {
				continue;
			}
			
			if(rule.passed == undefined) {
				rule.passed = new Object();
			}
			
			rule.passed.number_of_pages = true;
 		}
	};
	
	this.checkTimeOnSite = function() {
    if(!this.stats instanceof Stats) {
      return false;
    }
		for(var o in this.rules) {
			var rule = this.rules[o];
			var time_on_site = rule.conditions.time_on_site;
			if(time_on_site == undefined || time_on_site > this.stats.total_time) {
				continue;
			}
			
			if(rule.passed == undefined) {
				rule.passed = new Object();
			}
			
			rule.passed.time_on_site = true;
 		}
		
	};
	
	this.checkReferrer = function() {
		var ref_url = document.referrer;
		var ref_url_wd = ref_url.substring(ref_url.indexOf('://') + 3);
		ref_url_wd = ref_url_wd.substring(ref_url_wd.indexOf('/'));
		
		for(var o in this.rules) {
			var rule = this.rules[o];
			var came_from_cond_value = rule.conditions.came_from;
			if(came_from_cond_value != ref_url && came_from_cond_value != ref_url_wd) {
				continue;
			}
			
			if(rule.passed == undefined) {
				rule.passed = new Object();
			}
			rule.passed.came_from = true;
			
 		}

	//	show_dump(dump_object(this.rules));
	};
	
	this.pageUpdate = function() {
    if(!this.stats instanceof Stats) {
      return false;
    }
	//	alert(this.current_page);
		this.stats.increaseVisitedPageTime(this.current_page, 1);
	};
	
	this.loadStats = function() {
		
		var cookies = document.cookie.split(';');
		var json = '';
		var total_time = 0;
		
		for(var i in cookies) {
			var tokens = trim(cookies[i]).split('=');
			if(tokens[0] == '<?php echo WEBIM_COOKIE_AUTOINVITE_STATS; ?>') {
				json = unescape(tokens[1]);
			} else if(tokens[0] == '<?php echo WEBIM_COOKIE_TOTAL_TIME_ON_SITE; ?>') {
				total_time = parseInt(tokens[1]);
			}
		}
		
		if(json != '' && total_time > 0) {
			try {
				this.stats = Stats.fromJSON(json);
				this.stats.total_time = total_time;
				return;
			} catch(e) {

			}
			
		}

		this.stats = new Stats();

		return;
	};
	
	this.shutdown = function() {
		this.storeStats();
	};
	
	this.storeStats = function () {
		if(!this.stats instanceof Stats) {
			return false;
    }		

		var c = ['<?php echo WEBIM_COOKIE_AUTOINVITE_STATS; ?>', '=', encodeURIComponent(this.stats.toJSON()), '; path=/'].join('');
		document.cookie = c;
		var date = new Date();
        date.setTime(date.getTime() + (30 * 24 * 60 * 60 * 1000));
		document.cookie = ['<?php echo WEBIM_COOKIE_TOTAL_TIME_ON_SITE; ?>', '=', encodeURIComponent(this.stats.total_time), '; path=/; expires=', date.toUTCString()]. join('');
		
		return true; 
	};
	
	this.createIniviteDiv = function() { 
		div = document.createElement("div");
		var body = document.getElementsByTagName('body')[0];
		div.id = 'invitediv';
		div.name = 'invitediv';
		div.style.display = 'none';
		div.style.width = '360px';
		div.style.height = '130px';
		div.style.left = '200px';
		div.style.top = '300px';
		div.style.position = 'absolute';
		div.innerHTML = "<a class=\"webim-invite\" href=\"#\" onclick=\"this.parentNode.style.display='none'; <?php echo $temp; ?>;\">"
			+ '<span class="webim-t"></span>'
			+ '<span class="webim-c">'
			+ '<span class="webim-im">'
			+ '<img id="webim-operator-image" src="<?php echo $invite_image;?>"/>'
			+ '<em> </em>'
			+ '</span>'
			+ '<strong>'
			+ '<span id="webim-invatation-message">'
			+ '<span id="webim-msg" style="display:inline;"></span> '
			+ '<img src="<?php echo WEBIM_ROOT.'/themes/'.Browser::getCurrentTheme().'/images/invite/bullet5.gif'?>"/>'
			+ '</span>'
			+ '</strong>'
			+ '</span>'
			+ '<span class="webim-b"></span>'
			+ '</a>'
			+ '<img class="webim-close" onclick="this.parentNode.style.display=\'none\';" style="" src="/webim/themes/default/images/closewin.gif"/>';

		body.appendChild(div);
		this.inviteDiv = div;
		this.messageSpan = document.getElementById('webim-msg');
	};
}

function Stats() {
	this.visited_pages = new Array();
	this.total_time = 0;
	
	this.addVisitedPage = function (url, time, referrer) {
		this.visited_pages.push({
			url : url,
			time : time,
			referrer: referrer
		});
		
		this.total_time += time;
		
		return this.visited_pages.length - 1;
	};
	
	this.getPageIndex = function(url, start_from_idx) {
		if(start_from_idx == undefined) {
			start_from_idx = 0
		}
		
		var i = start_from_idx;
		var len = this.visited_pages.length;
		
		for(i;i<len;i++) {
			if(this.visited_pages[i].url == url) {
				return i;
			}
		}
		
		return null;
	};
	
	this.getTotalTimeOnPage = function(url) {
		var result = 0;
		for (var o in this.visited_pages) {
			if(this.visited_pages[o].url == url) {
				result += this.visited_pages[o].time;
			}
		}
		
		return result;
	};
	
	this.getLastPageIndex = function() {
		return this.visited_pages.length-1;
	};
	
	this.getLastPage = function () {
		return this.visited_pages[this.getLastPageIndex()];
	};
	
	this.increaseVisitedPageTime = function (idx, inc_time) {
		this.total_time += inc_time;
		if(this.visited_pages[idx] == undefined) {
			return false;
		}
		
		this.visited_pages[idx].time += inc_time;
		return true;
	};
	
	this.toJSON = function () {
		var result = '{';
		result += 'visited_pages : [';
			for(var o in this.visited_pages) {
				var vp = this.visited_pages[o];

				result += '{';
				for(var i in vp) {
					result += i + ':';
					
					if(typeof(vp[i]) == 'number') {
						result +=  vp[i] + ",";
					} else {
						result += "'" + vp[i] + "',";
					}
					
				}
				result += '},'
			}
		result += '],';
		result += 'total_time : ' + this.total_time;
		result += '}';
		
		return result;
	};
}

Stats.fromJSON = function (str) {
	try {
		var tmp = eval('('+str+')');
	} catch (e) {
		throw new Error('Wrong json. (eval)');
	}
	
	if(tmp == undefined || 
		tmp.total_time == undefined || 
		typeof(tmp.total_time) != 'number' || 
		! (tmp.visited_pages instanceof Array)
	) {
		throw new Error('Wrong json');
	}
	
	var stats = new Stats();
	stats.total_time = tmp.total_time;
	for(var i in tmp.visited_pages) {
		var vp = tmp.visited_pages[i];
		if(vp.url == undefined || typeof(vp.time) != 'number' || vp.referrer == undefined) {
			throw new Error('Wrong json');
		}
		
		stats.addVisitedPage(vp.url, vp.time, vp.referrer);
	}
	
	return stats;
};

function trim(string) {
	return string.replace(/(^\s+)|(\s+$)/g, "");
}


function addEvent(object, eventType, callback) {
	if(object.addEventListener) {
		object.addEventListener(eventType, callback, false);
		return true;
	} else if (object.attachEvent) {
		return object.attachEvent('on'+eventType, callback);
	}
	
}

var ai = new AInvite();

var wmAnimationBox;
var wmAnimationBoxSty;
var wmDocWidth;
var wmDocHeight;
var wmDocXTop;
var wmCurrentX;
var wmCurrentY;
var wmXIncrementSaved;
var wmXIncrement;
var wmTimer = null;
var wmCurrentAminTime = 0;
var wmAnimationTime = <?php echo INVITE_ANIMATION_DURATION * 1000; ?>;
var wmSingleStepTime = 20;
var wmAnimationStep = 2;
var windowSize = getWindowSize();
wmDocWidth = windowSize[0];
wmDocHeight = windowSize[1];
var autoinviteid = null;

addEvent(window, "load", function() {
	ai.init(rules, startAnimation);
	wmAnimationBox = document.getElementById('invitediv');
	wmAnimationBoxSty = document.getElementById('invitediv').style;
	initAnimation();
});

addEvent(window, "scroll", function() {
	if(wmAnimationBoxSty) {
		var scrollXY = getScrollXY();
		wmAnimationBoxSty.left = (scrollXY[0] + wmCurrentX) + 'px';
		wmAnimationBoxSty.top = (scrollXY[1] + wmCurrentY) + 'px';
	}
});

addEvent(window, "unload", function() {
	ai.shutdown();
});

function startAnimation(id) {
	autoinviteid = id;
	wmTimer = setTimeout('animationStep()', 3000);
}

function stopAnimation() {
	wmAnimationBoxSty.display = 'none';
    clearTimeout(wmTimer);
    wmTimer = null;
}

function pauseAnimation() {
    clearTimeout(wmTimer);
    wmTimer = null;
}

function resumeAnimation(id) {
	wmTimer = setTimeout('animationStep()', wmSingleStepTime + 500);
}

function initAnimation() {
  var box_width = parseInt(wmAnimationBoxSty.width);
  var box_height = parseInt(wmAnimationBoxSty.height);

  wmDocXTop = wmDocWidth - box_width;

  wmXIncrementSaved = wmAnimationStep;
  wmXIncrement = wmXIncrementSaved;

  wmCurrentX = (wmDocWidth - box_width) * 0.1;
  wmCurrentY = (wmDocHeight - box_height) * 0.5;

  var scrollXY = getScrollXY();
  wmAnimationBoxSty.left = (scrollXY[0] + wmCurrentX) + 'px';
  wmAnimationBoxSty.top = (scrollXY[1] + wmCurrentY) + 'px';
  
  addEvent(wmAnimationBox, "mouseover", pauseAnimation);
  addEvent(wmAnimationBox, "mouseout", resumeAnimation);
}

function animationStep() {
	  if (wmAnimationBoxSty.display == 'none') {
    return;
  }

  var scrollXY = getScrollXY();
  var newX = wmCurrentX + wmXIncrement;
  var paddingRight = 20;

  if ((newX < wmDocXTop - paddingRight && wmXIncrement > 0) || (wmCurrentX > 0 && wmXIncrement < 0)) {
    wmCurrentX = newX;
    wmAnimationBoxSty.left = (scrollXY[0] + wmCurrentX) + 'px';
  } else if (wmXIncrement != 0) {
    wmXIncrementSaved = -wmXIncrementSaved;
    wmXIncrement = wmXIncrementSaved;
  }

  if (wmXIncrement != 0) {
    wmCurrentAminTime += wmSingleStepTime;
  }

  if (wmCurrentAminTime < wmAnimationTime) {
    wmTimer = setTimeout('animationStep()', wmSingleStepTime);
  } else {
    stopAnimation();
  }
}

function getScrollXY() {
	  var scrOfX = 0, scrOfY = 0;
	  if (typeof(window.pageYOffset) == 'number') {
    //Netscape compliant
    scrOfY = window.pageYOffset;
    scrOfX = window.pageXOffset;
  } else if (document.body && (document.body.scrollLeft || document.body.scrollTop)) {
    //DOM compliant
    scrOfY = document.body.scrollTop;
    scrOfX = document.body.scrollLeft;
  } else if (document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop)) {
    //IE6 standards compliant mode
    scrOfY = document.documentElement.scrollTop;
    scrOfX = document.documentElement.scrollLeft;
  }
  return [scrOfX, scrOfY];
}

function getWindowSize() {
  var myWidth = 0, myHeight = 0;
  if (typeof(window.innerWidth) == 'number') {
    //Non-IE
    myWidth = window.innerWidth;
    myHeight = window.innerHeight;
  } else if (document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight)) {
    //IE 6+ in 'standards compliant mode'
    myWidth = document.documentElement.clientWidth;
    myHeight = document.documentElement.clientHeight;
  } else if (document.body && ( document.body.clientWidth || document.body.clientHeight )) {
    //IE 4 compatible
    myWidth = document.body.clientWidth;
    myHeight = document.body.clientHeight;
  }
  return [myWidth, myHeight];
}

