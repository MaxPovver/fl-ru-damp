/*! adriver.core.2 v2.3.9 */
function adriver(ph, prm, defer){
	var my = this, p = ph;
	if(this instanceof adriver){
		if(typeof(p)=='string'){
			p=document.getElementById(ph);
		}else{
			ph=p.id;
		}

		if(!p){
			if(!adriver.isDomReady){adriver.onDomReady(function(){new adriver(ph, prm, defer);});}
			return {};
		}
		if(adriver(ph)){return adriver(ph);}

		my.p = p;
		my.defer = defer;
		my.prm = adriver.extend(prm, {ph: ph});

		my.loadCompleteQueue = new adriver.queue();
		my.domReadyQueue = new adriver.queue(adriver.isDomReady);

		adriver.initQueue.push(function(){my.init();});
		adriver.items[ph] = my;
		return my;
	}else{
		return ph ? adriver.items[ph] : adriver.items;
	}
}

adriver.prototype = {
	isLoading: 0,

	init: function(){},
	loadComplete: function(){},
	domReady: function(){},

	onLoadComplete: function(f){var my = this; my.loadCompleteQueue.push(function(){f.call(my);}); return my;},
	onDomReady: function(f){this.domReadyQueue.push(f); return this;},
	reset: function(){this.loadCompleteQueue.flush(); this.domReadyQueue.flush(adriver.isDomReady); return this;}
};

adriver.extend = function(){
	for(var l = arguments[0], i = 1, len = arguments.length, r, j; i<len; i++){
		r = arguments[i];
		for(j in r){
			if(r.hasOwnProperty(j)){
				if(r[j] instanceof Function){l[j] = r[j];}
				else if(r[j] instanceof Object){if(l[j]){adriver.extend(l[j], r[j]);}else{l[j] = adriver.extend(r[j] instanceof Array ? [] : {}, r[j]);}}
				else{l[j] = r[j];}
			}
		}
	}
	return l;
};

adriver.extend(adriver, {
	version: '2.3.9',

	defaults: {tail256: escape(document.referrer || 'unknown')},
	items: {},
	options: {},
	plugins: {},
	pluginPath: {},

	redirectHost: '//ad.adriver.ru',
	defaultMirror: '//content.adriver.ru',

	loadScript: function(req){
		try {
			var head = document.getElementsByTagName('head')[0],
				s = document.createElement('script');
			s.setAttribute('type', 'text/javascript');
			s.setAttribute('charset', 'windows-1251');
			s.setAttribute('src', req.split('![rnd]').join(Math.round(Math.random()*9999999)));
			s.onreadystatechange = function(){if(/loaded|complete/.test(this.readyState)){head.removeChild(s);s.onload = null;}};
			s.onload = function(){head.removeChild(s);};
			head.insertBefore(s, head.firstChild);
		}catch(e){}
	},

	isDomReady: false,
	onDomReady: function(f){adriver.domReadyQueue.push(f);},
	onBeforeDomReady: function(f){adriver.domReadyQueue.unshift(f);},
	domReady: function(){adriver.isDomReady = true;adriver.domReadyQueue.execute();},

	checkDomReady: function(f){
		try {
			var w = window, d = w.document, oldOnLoad, topLevel, ready = function(){if(adriver.isDomReady){return;}f();}, readyStateChange;
			if (d.readyState === 'complete') {
				ready();
			} else if (d.addEventListener) {
				d.addEventListener('DOMContentLoaded', ready, false);
				w.addEventListener('load', ready, false);
			} else if (d.attachEvent) {
				readyStateChange = function(){if(d.readyState==='complete'){d.detachEvent('onreadystatechange', readyStateChange);ready();}};
				d.attachEvent('onreadystatechange', readyStateChange);
				w.attachEvent('onload', ready);
				topLevel = false;
				try{topLevel = w.frameElement === null && d.documentElement;}catch(e){}
				if (topLevel && topLevel.doScroll) {
					(function doScrollCheck() {
						if (!adriver.isDomReady) {
							try {topLevel.doScroll('left');} catch(e) {return setTimeout(doScrollCheck, 50);}
							ready();
						}
					})();
				}
			} else if (/WebKit/i.test(navigator.userAgent)) {
				(function(){/loaded|complete/.test(d.readyState) ? ready() : setTimeout(arguments.callee, 50);})();
			} else {
				oldOnLoad = w.onload;
				w.onload = function(){if(oldOnLoad){oldOnLoad();} ready();};
			}
		}catch(e){}
	},

	onLoadComplete: function(f){adriver.loadCompleteQueue.push(f); return adriver;},
	checkLoadComplete: function(){
		var i, a;

		for (i in adriver.items) {
			if (adriver.items.hasOwnProperty(i)) {
				a = adriver.items[i];
				if (!a.prm.onScroll && typeof a.reply === 'undefined') { return false; }
			}
		}

		return true;
	},
	loadComplete: function(){
		if (adriver.checkLoadComplete()) { adriver.loadCompleteQueue.execute(false); }
		return adriver;
	},

	setDefaults: function(o){adriver.extend(adriver.defaults, o);},
	setOptions: function(o){adriver.extend(adriver.options, o);},
	setPluginPath: function(o){adriver.extend(adriver.pluginPath, o);},

	queue: function(flag){this.q = []; this.flag = flag ? true: false;},
	Plugin: function(id){
		if(this instanceof adriver.Plugin){
			if(id && !adriver.plugins[id]){
				this.id = id;
				this.q = new adriver.queue();
				adriver.plugins[id] = this;
			}
		}
		return adriver.plugins[id];
	}
});
adriver.sync = function(pair, N){
	if(!adriver.syncFlag){
		adriver.syncFlag = 1;
		var ar_duo=[];while(N--){ar_duo[N]=N+1;}ar_duo.sort(function(){return 0.5-Math.random();});
		adriver.synchArray = ar_duo;
	}
	return adriver.synchArray[(!pair||pair<=0?1:pair)-1];
};
adriver.queue.prototype = {
	push: function(f){this.flag ? f(): this.q.push(f);},
	unshift: function(f){this.flag ? f(): this.q.unshift(f);},
	execute: function(flag){var f, und; while((f=this.q.shift())){f();} if(flag == und){flag=true;} this.flag = flag ? true : false;},
	flush: function(flag){this.q.length = 0; this.flag = flag ? true: false;}
};
adriver.Plugin.prototype = {
	loadingStatus: 0,
	load: function(){this.loadingStatus = 1; adriver.loadScript((adriver.pluginPath[this.id.split('.').pop()] || (adriver.defaultMirror + '/plugins/')) + this.id + '.js');},
	loadComplete: function(){this.loadingStatus = 2; this.q.execute(); return this;},
	onLoadComplete: function(f){this.q.push(f); return this;}
};
adriver.Plugin.require = function(){
	var my = this, counter = 0;
	my.q = new adriver.queue();

	for(var i = 0, len = arguments.length, p; i < len; i ++){
		p = new adriver.Plugin(arguments[i]);
		if(p.loadingStatus != 2){
			counter++;
			p.onLoadComplete(function(){if(counter-- == 1){my.q.execute();}});
			if(!p.loadingStatus){p.load();}
		}
	}
	if(!counter){my.q.execute();}
};
adriver.Plugin.require.prototype.onLoadComplete = function(f){this.q.push(f); return this;};

adriver.domReadyQueue = new adriver.queue();
adriver.loadCompleteQueue = new adriver.queue();
adriver.initQueue = new adriver.queue();

adriver.checkDomReady(adriver.domReady);

new adriver.Plugin.require('autoUpdate.adriver').onLoadComplete(function(){adriver.initQueue.execute();});