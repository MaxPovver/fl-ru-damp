/*
 * jScout 
 * Version 0.2
 * 
 * Copyright(c) 2008, Nickolay Platonov
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details. 
 * 
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *  
 */


/**
 * full-featured JS/CSS on-demand loader
 * @author Nickolay Platonov aka SamuraiJack
 * @version 0.3
 */
jScout = {
	VERSION : '0.3',
	libRoot : ['lib'],
	useSyncDepth : 0
};

jScout.packageMgr = function () {
    var allPackages = [];
    
    var namedPackages = {};   
    
    var collectGarbage = function() {
        var i = 0;
        while (i < allPackages.length) {
            if (allPackages[i].anonymous && allPackages[i].ready) {
                allPackages.splice(i,1);
                continue;
            }
            
            i++;
        }
    } 
    
    return {
        
        declare : function (name, packageDefinition, scope, sync) {
            var s = name.split('::');
            s.pop();
            
            Ext.ns(s.join('.'));
            
            var pack = this.getPackage(name);
            if (pack.declared) throw "Double declaration of: " + name;
            
            pack.declared = true;

            if (typeof scope == 'boolean' && typeof sync == 'undefined') {
            	sync = scope;
            	scope = window;
            }
			
			if (jScout.useSyncDepth) sync = true;
            
            if (sync) {
            	packageDefinition.call(scope || window, pack.useSync.createDelegate(pack), pack.checkUsages.createDelegate(pack));
            } else {
            	packageDefinition.call(scope || window, pack.use.createDelegate(pack), pack.checkUsages.createDelegate(pack));
            }
            
            pack.checkUsages();
        },
        
        getPackage : function(name) {
            if (!namedPackages[name]) namedPackages[name] = new jScout.packageObj( { name: name } );
            return namedPackages[name];
        },
        
        use : function (packageSpecs, callback, scope) {
            collectGarbage();
            
            var pack = new jScout.packageObj( { anonymous: true } );
            
            pack.use.call(pack,packageSpecs,callback, scope);
        },
        
        useSync : function (packageSpecs, callback, scope) {
            collectGarbage();
            
            var pack = new jScout.packageObj( { anonymous: true } );
            
            pack.useSync.call(pack,packageSpecs,callback, scope);
        },
        
        allPackages : allPackages        
    }
}();
use = jScout.use = jScout.packageMgr.use.createDelegate(jScout.packageMgr);
useSync = jScout.useSync = jScout.packageMgr.useSync.createDelegate(jScout.packageMgr);
declare = jScout.declare = jScout.packageMgr.declare.createDelegate(jScout.packageMgr);
    

jScout.packageObj = Ext.extend(Ext.util.Observable,{
    
    name : '',    
    evalStr : '',
    URL : '',
    
    redefinitionAllowed : false,
    
    externalLoading : null,
    interval : null,
    
    loading : false,
    loaded : false,
    declared : false,
    ready : false,
    anonymous : false,

    usages : null,
    
    constructor : function (packageSpec) {
        Ext.apply(this,packageSpec);
        
        this.usages = [];
        
        this.addEvents('ready');
        jScout.packageMgr.allPackages.push(this);
        
        return this;
    },
    
    applySpec : function (packageSpec) {
        this.redefinitionAllowed = packageSpec.redefinitionAllowed;
        
        if (!this.redefinitionAllowed && packageSpec.URL && this.URL && packageSpec.URL != this.URL) throw "URL redefinition for: " + this.name;
        if (packageSpec.URL) this.URL = packageSpec.URL;
        
        if (!this.redefinitionAllowed && packageSpec.evalStr && this.evalStr && packageSpec.evalStr != this.evalStr) throw "evalStr redefinition for: " + this.name;
        if (packageSpec.evalStr) this.evalStr = packageSpec.evalStr;
        
        if (!this.redefinitionAllowed && typeof packageSpec.externalLoading == 'boolean' && typeof this.externalLoading == 'boolean' && packageSpec.externalLoading != this.externalLoading) throw "Externalloading redefinition for: " + this.name;
        if (typeof packageSpec.externalLoading == 'boolean') {
            this.externalLoading = packageSpec.externalLoading;
        }
        
        if (!this.redefinitionAllowed && typeof packageSpec.interval == 'number' && typeof this.interval == 'number' && packageSpec.interval != this.interval) throw "interval redefinition for: " + this.name;
        if (typeof packageSpec.interval == 'number') {
            this.interval = packageSpec.interval;
        }
    },
    
    getUrl : function () {
        if (this.URL) return this.URL;
        
        var s = this.name.split('::');
        s = jScout.libRoot.concat(s);
        
        var filename = s.pop().split('.');        
        if (!filename[1]) filename[1] = 'js';
        s.push(filename.join('.'));
        
        return '/' + s.join('/');
    },
    
    getType : function () {
        var s = this.getUrl().split('.');
        return s.pop();
    },
    
    getEvalStr : function () {
        if (this.evalStr) return this.evalStr;
    
        return this.name.replace(/::/g,'.');
    },
    
    
    isLoaded : function (){
        
        if (this.ready || this.loaded) return true;
        if (this.declared) return false;

        try {
            if ( eval(this.getEvalStr()) ) return true;            
        }
        catch(e) {}
        finally {}
        
        return false;
    },
    
    load : function (sync) {
        if (this.isLoaded()) {
            this.checkUsages();
            return;
        }
        
        ///XXX if package is currently loading asyncly and attempt was made to load it syncly
		if (this.loading) return;
        
        if (this.externalLoading && this.interval) {
            
            var runner = new Ext.util.TaskRunner();
            
            runner.start({
                interval : this.interval,
                scope : this,
                run : function(){
                    if (this.isLoaded()) {
                        runner.stopAll();
                        this.checkUsages();
                    }
                }
            });
        };
        
        if (this.externalLoading) return;
        
        
        
        this.loading = true;
        
        if (sync) {
	        Ext.Ajax.request({
	        	url : this.getUrl(),
                disableCaching:DEBUG?true:false,
	        	method : 'GET',
	        	params : {
	        		useSyncDepth : jScout.useSyncDepth
                    ,ver:VERSION
	        	},
	        	callback : function (options, success, response){
	        		if (!success) {
	        			throw "Loading of " + this.name + ' failed.';
	        			return;
	        		}
	        		
	        		this.loading = false;
	                this.loaded = true;                
                    
                    //eval.call(window, response.responseText);
                    if (window.execScript) {
                        //eval.call(response.responseText);
                        window.execScript(response.responseText);
                    } else {
	                    window.eval(response.responseText);	                
                    }
	        	},
	        	scope : this,
	        	sync : true
	        });
	        
	        this.checkUsages();
	        
        } else {        
        
	        var loaderNode;
	        if (this.getType() == 'css') {
	            loaderNode = document.createElement("link");
	            loaderNode.setAttribute("rel", "stylesheet");
	            loaderNode.setAttribute("type", "text/css");
	            loaderNode.setAttribute("href", this.getUrl());
	        } else {
	            loaderNode = document.createElement("script");
	            loaderNode.setAttribute("type", "text/javascript");
	            loaderNode.setAttribute("src", this.getUrl());
	        }
	        
	        var self = this;
	
	        loaderNode.onload = loaderNode.onreadystatechange = function() {
	            if (!loaderNode.readyState || loaderNode.readyState == "loaded" || loaderNode.readyState == "complete" || loaderNode.readyState == 4 && loaderNode.status == 200) {
	                self.loading = false;
	                self.loaded = true;
	                self.checkUsages.defer(10,self);
	            }
	        };
	        
	        document.getElementsByTagName("head")[0].appendChild(loaderNode);
        }
    },
    
    checkUsages : function () {
        var i = 0;
        
        while (i < this.usages.length) {
            var depended = false;
            for (var j in this.usages[i].dependencies) {
                if (this.usages[i].dependencies[j].externalLoading && this.usages[i].dependencies[j].isLoaded()) {
                    this.deleteDependencyFromUsage.call(this.usages[i],j);
                    return;
                } else {
                    depended = true;
                    break;
                }
            }
            if (depended) { i++; continue; }
            var releasedUsage = this.usages.splice(i,1);
            releasedUsage[0].fireEvent('ready');
        }
        
        if (this.usages.length) return;
        
        this.ready = true;
        this.fireEvent('ready');        
    },
    
    deleteDependencyFromUsage : function (packageName) {
        if (!this.dependencies[packageName]) throw "Attempt to delete non-existing dependency";
        
        delete this.dependencies[packageName];
        this.myPackage.checkUsages();
    },
    
    
    use : function (packageSpecs, callback, scope, sync) {
        if (!callback) callback = Ext.emptyFn;
        
        var thisUsage = new Ext.util.Observable();        
        
        thisUsage.addEvents('ready');        
        thisUsage.dependencies = {};
        thisUsage.myPackage = this;        
        
        this.usages.push(thisUsage);
        
        if (sync) {
			thisUsage.on('ready',callback.createDelegate(scope, [this.useSync.createDelegate(this), this.checkUsages.createDelegate(this)] ),scope, { single : true });
		} else {
			thisUsage.on('ready',callback.createDelegate(scope, [this.use.createDelegate(this), this.checkUsages.createDelegate(this)] ),scope, { single : true });
		}
        
        if (typeof packageSpecs.pop != 'function') packageSpecs = [ packageSpecs ];
        
        for (var i = 0; i < packageSpecs.length; i++) {
            if (typeof packageSpecs[i] == 'string') {
                packageSpecs[i] = { name : packageSpecs[i] };
            }
            
            var pack = jScout.packageMgr.getPackage(packageSpecs[i].name);
            pack.applySpec(packageSpecs[i]);
            
            thisUsage.dependencies[pack.name] = pack;
            this.ready = false;
            
            pack.on('ready', this.deleteDependencyFromUsage.createDelegate(thisUsage,[pack.name]), thisUsage, { single : true } );
        }
        
        for (i in thisUsage.dependencies) {
            thisUsage.dependencies[i].load(sync);
        }
        
    },
    
    useSync : function (packageSpecs, callback, scope) {
        jScout.useSyncDepth++;
        this.use( packageSpecs, callback, scope, true);
        jScout.useSyncDepth--;
    }
    
}); //eof extend



//patching core
Ext.lib.Ajax.request = function(method, uri, cb, data, options) {
    if(options){
        var hs = options.headers;
        if(hs){
            for(var h in hs){
                if(hs.hasOwnProperty(h)){
                    this.initHeader(h, hs[h], false);
                }
            }
        }
        if(options.xmlData){
            this.initHeader('Content-Type', 'text/xml', false);
            method = 'POST';
            data = options.xmlData;
        }else if(options.jsonData){
            this.initHeader('Content-Type', 'text/javascript', false);
            method = 'POST';
            data = typeof options.jsonData == 'object' ? Ext.encode(options.jsonData) : options.jsonData;
        }
        if (options.sync) {
            return this.syncRequest(method, uri, cb, data);
        }
    }
    return this.asyncRequest(method, uri, cb, data);
};


Ext.lib.Ajax.syncRequest = function(method, uri, callback, postData)
{
    var o = this.getConnectionObject();

    if (!o) {
        return null;
    }
    else {
        o.conn.open(method, uri, false);

        if (this.useDefaultXhrHeader) {
            if (!this.defaultHeaders['X-Requested-With']) {
                this.initHeader('X-Requested-With', this.defaultXhrHeader, true);
            }
        }

        if(postData && this.useDefaultHeader){
            this.initHeader('Content-Type', this.defaultPostHeader);
        }

        if (this.hasDefaultHeaders || this.hasHeaders) {
            this.setHeader(o);
        }

        o.conn.send(postData || null);
        this.handleTransactionResponse(o, callback);
        return o;
    }
};