JS_PACK=1;
/* global Ext */
/*
 * @class
 *        Ext.ux.Media.Flash,
 *        Ext.ux.FlashComponent (xtype: uxflash)
 *        Ext.ux.FlashPanel     (xtype: uxflashpanel, flashpanel)
 *        Ext.ux.FlashWindow
 *
 * Version:  2.0
 * Author: Doug Hendricks. doug[always-At]theactivegroup.com
 * Copyright 2007-2008, Active Group, Inc.  All rights reserved.
 *
 ************************************************************************************
 *   This file is distributed on an AS IS BASIS WITHOUT ANY WARRANTY;
 *   without even the implied warranty of MERCHANTABILITY or
 *   FITNESS FOR A PARTICULAR PURPOSE.
 ************************************************************************************

 License: ux.Media.Flash classes are licensed under the terms of
 the Open Source GPL 3.0 license (details: http://www.gnu.org/licenses/gpl.html).

 Commercial use is prohibited without a Commercial License. See http://licensing.theactivegroup.com.

 Donations are welcomed: http://donate.theactivegroup.com

 Notes: the <embed> tag is NOT used(or necessary) in this implementation

 Version:
        1.0
           Addresses the Flash visibility/re-initialization issues for all browsers.
           Adds bi-directional fscommand support for all A-Grade browsers.


        Rc1
           Adds inline media rendering within markup: <div><script>document.write(String(new Ext.ux.Media.Flash(mediaCfg)));</script></div>
           New extensible classes :
              ux.Media.Flash
              ux.FlashComponent
              ux.FlashPanel
              ux.FlashWindow

   A custom implementation for advanced Flash object interaction
       Supports:
            version detection,
            version assertion,
            Flash Express Installation (inplace version upgrades),
            and custom Event Sync for interaction with SWF.ActionScript.

    mediaCfg: {Object}
         {
           url       : Url resource to load when rendered
          ,loop      : (true/false)
          ,start     : (true/false)
          ,height    : (defaults 100%)
          ,width     : (defaults 100%)
          ,scripting : (true/false) (@macro enabled)
          ,controls  : optional: show plugins control menu (true/false)
          ,eventSynch: (Bool) If true, this class initializes an internal event Handler for
                       ActionScript event synchronization
          ,listeners  : {"mouseover": function() {}, .... } DOM listeners to set on the media object each time the Media is rendered.
          ,requiredVersion: (String,Array,Number) If specified, used in version detection.
          ,unsupportedText: (String,DomHelper cfg) Text to render if plugin is not installed/available.
          ,installUrl:(string) Url to inline SWFInstaller, if specified activates inline Express Install.
          ,installRedirect : (string) optional post install redirect
          ,installDescriptor: (Object) optional Install descriptor config
         }
    */

(function(){

   var ux = Ext.ux.Media;

    ux.Flash = Ext.extend( ux, {
        constructor: function() {
            ux.Flash.superclass.constructor.apply(this, arguments);
        },

        SWFObject      : null,

        varsName       :'flashVars',

        hideMode       : 'nosize',

        mediaType: Ext.apply({
              tag      : 'object'
             ,cls      : 'x-media x-media-swf'
             ,type     : 'application/x-shockwave-flash'
             ,loop     : null
             ,scripting: "sameDomain"
             ,start    : true
             ,unsupportedText : {cn:['The Adobe Flash Player{0}is required.',{tag:'br'},{tag:'a',cn:[{tag:'img',src:'http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif'}],href:'http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash',target:'_flash'}]}
             ,params   : {
                  movie     : "@url"
                 ,play      : "@start"
                 ,loop      : "@loop"
                 ,menu      : "@controls"
                 ,quality   : "high"
                 ,bgcolor   : "#FFFFFF"
                 ,wmode     : "opaque"
                 ,allowscriptaccess : "@scripting"
                 ,allowfullscreen : false
                 ,allownetworking : 'all'
                }
             },Ext.isIE?
                    {classid :"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000",
                     codebase:"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0"
                    }:
                    {data     : "@url"}),

        getMediaType: function(){
             return this.mediaType;
        },
        // private (called once by initComponent)
        initMedia : function(){

            var mc = Ext.apply({}, this.mediaCfg||{});
            var requiredVersion = (this.requiredVersion = mc.requiredVersion || this.requiredVersion|| false ) ;
            var hasFlash  = !!(this.playerVersion = this.detectVersion());
            var hasRequired = hasFlash && (requiredVersion?this.assertVersion(requiredVersion):true);

            var unsupportedText = this.assert(mc.unsupportedText || this.unsupportedText || (this.getMediaType()||{}).unsupportedText,null);
            if(unsupportedText){
                 unsupportedText = Ext.DomHelper.markup(unsupportedText);
                 unsupportedText = mc.unsupportedText = String.format(unsupportedText,
                     (requiredVersion?' '+requiredVersion+' ':' '),
                     (this.playerVersion?' '+this.playerVersion+' ':' Not installed.'));
            }

            if(!hasRequired ){
                this.autoMask = false;

                //Version check for the Flash Player that has the ability to start Player Product Install (6.0r65)
                var canInstall = hasFlash && this.assertVersion('6.0.65');

                if(canInstall && mc.installUrl){

                       mc =  mc.installDescriptor || {
                             tag      : 'object'
                            ,cls      : 'x-media x-media-swf x-media-swfinstaller'
                            ,id       : 'SWFInstaller'
                            ,type     : 'application/x-shockwave-flash'
                            ,data     : "@url"
                            ,url              : mc.installUrl
                            //The dimensions of playerProductInstall.swf must be at least 310 x 138 pixels,
                            ,width            : (/%$/.test(mc.width)) ? mc.width : ((parseInt(mc.width,10) || 0) < 310 ? 310 :mc.width)
                            ,height           : (/%$/.test(mc.height))? mc.height :((parseInt(mc.height,10) || 0) < 138 ? 138 :mc.height)
                            ,loop             : false
                            ,start            : true
                            ,unsupportedText  : unsupportedText
                            ,params:{
                                      quality          : "high"
                                     ,movie            : '@url'
                                     ,allowscriptacess : "always"
                                     ,align            : "middle"
                                     ,bgcolor          : "#3A6EA5"
                                     ,pluginspage      : mc.pluginsPage || this.pluginsPage || "http://www.adobe.com/go/getflashplayer"
                                   }
                        };
                        mc.params[this.varsName] = "MMredirectURL="+( mc.installRedirect || window.location)+
                                            "&MMplayerType="+(Ext.isIE?"ActiveX":"Plugin")+
                                            "&MMdoctitle="+(document.title = document.title.slice(0, 47) + " - Flash Player Installation");
                } else {
                    //Let superclass handle with unsupportedText property
                    mc.mediaType=null;
                }
            }

            /*
            *  Sets up a eventSynch between the ActionScript environment
            *  and converts it's events into native Ext events.
            *  When this config option is true, binds an ExternalInterface definition
            *  to the ux.Media.Flash class method Ext.ux.Media.Flash.eventSynch.
            *
            *  The default binding definition pass the following flashVars to the Flash object:
            *
            *  allowedDomain,
            *  elementID (the ID assigned to the DOM <object> )
            *  eventHandler (the globally accessible function name of the handler )
            *     the default implementation expects a call signature in the form:
            *
            *    ExternalInterface.call( 'eventHandler', elementID, eventString )

            *  For additional flexibility, your own eventSynch may be defined to match an existing
            *  ActionScript ExternalInterface definition.
            */

            if(mc.eventSynch){
                mc.params || (mc.params = {});
                var vars = mc.params[this.varsName] || (mc.params[this.varsName] = {});
                if(typeof vars === 'string'){ vars = Ext.urlDecode(vars,true); }
                var eventVars = (mc.eventSynch === true ? {
                         allowedDomain  : vars.allowedDomain || document.location.hostname
                        ,elementID      : mc.id || (mc.id = Ext.id())
                        ,eventHandler   : 'Ext.ux.Media.Flash.eventSynch'
                        }: mc.eventSynch );

                Ext.apply(mc.params,{
                     allowscriptaccess  : 'always'
                })[this.varsName] = Ext.applyIf(vars,eventVars);
            }

            delete mc.requiredVersion;
            delete mc.installUrl;
            delete mc.installRedirect;
            delete mc.installDescriptor;
            delete mc.eventSynch;


            mc.mediaType = "SWF";
            this.mediaCfg = mc;

            if(this.events){
                this.addEvents(
                     /**
                     * @event flashinit
                     * Fires when the Flash Object reports an initialized state via a public callback function.
                     * this callback must implemented to be useful.  Example:
                     *
                     * //YouTube Global ready handler
                       var onYouTubePlayerReady = function(playerId) {

                           //Search for a ux.Flash-managed player.
                           var flashComp, el = Ext.get(playerId);
                           if(flashComp = (el?el.ownerCt:null)){
                              flashComp.onFlashInit();
                           }

                       };
                     *
                     * @param {Ext.ux.Flash} this
                     * @param {Element} the SWFObject interface
                     */
                    'flashinit',

                    /**
                     * @event fscommand
                     * Fires when the Flash Object issues an fscommand to the ux.Flash Component
                     * @param {Ext.ux.Flash} this
                     * @param command {string} the command string
                     * @param args {string} the arguments string
                     */
                    'fscommand'
               );
            }
            ux.Flash.superclass.initMedia.call(this);

        }


        /*
        * Asserts the desired version against the installed Flash Object version.
        * Acceptable parameter formats for versionMap:
        *
        *  '9.0.40' (string)
        *   9  or 9.1  (number)
        *   [9,0,43]  (array)
        *
        *  Returns true if the desired version is => installed version
        *  and false for all other conditions
        */
        ,assertVersion : function(versionMap){

            var compare;
            versionMap || (versionMap = []);

            if(Ext.isArray(versionMap)){
                compare = versionMap;
            } else {
                compare = String(versionMap).split('.');
            }
            compare = (compare.concat([0,0,0,0])).slice(0,3); //normalize

            var tpv;
            if(!(tpv = this.playerVersion || (this.playerVersion = this.detectVersion()) )){ return false; }

            if (tpv.major > parseFloat(compare[0])) {
                        return true;
            } else if (tpv.major == parseFloat(compare[0])) {
                   if (tpv.minor > parseFloat(compare[1]))
                            {return true;}
                   else if (tpv.minor == parseFloat(compare[1])) {
                        if (tpv.rev >= parseFloat(compare[2])) { return true;}
                        }
                   }
            return false;
        },
        /*
        * Flash version detection function
        * Modifed version of the detection strategy of SWFObject library : http://blog.deconcept.com/swfobject/
        * returns a {major,minor,rev} version object or
        * false if Flash is not installed or detection failed.
        */
        detectVersion : function(){
            if(this.mediaVersion){
                return this.mediaVersion;
            }
            var version=false;
            var formatVersion = function(version){
              return version && !!version.length?
                {major:version[0] !== null? parseInt(version[0],10): 0
                ,minor:version[1] !== null? parseInt(version[1],10): 0
                ,rev  :version[2] !== null? parseInt(version[2],10): 0
                ,toString : function(){return this.major+'.'+this.minor+'.'+this.rev;}
                }:false;
            };
            var sfo= null;
            if(Ext.isIE){

                try{
                    sfo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");
                }catch(e){
                    try {
                        sfo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");
                        version = [6,0,21];
                        // error if player version < 6.0.47 (thanks to Michael Williams @ Adobe for this solution)
                        sfo.allowscriptaccess = "always";
                    } catch(ex) {
                        if(version && version[0] === 6)
                            {return formatVersion(version); }
                        }
                    try {
                        sfo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
                    } catch(ex1) {}
                }
                if (sfo) {
                    version = sfo.GetVariable("$version").split(" ")[1].split(",");
                }
             }else if(navigator.plugins && navigator.mimeTypes.length){
                sfo = navigator.plugins["Shockwave Flash"];
                if(sfo && sfo.description) {
                    version = sfo.description.replace(/([a-zA-Z]|\s)+/, "").replace(/(\s+r|\s+b[0-9]+)/, ".").split(".");
                }
            }
            return (this.mediaVersion = formatVersion(version));

        }
        //Private
        ,_applyFixes : function() {
            var o ;
             // Fix streaming media troubles for IE
             // IE has issues with loose references when removing an <object>
             // before the onload event fires (all <object>s should have readyState == 4 after browsers onload)

             // Advice: do not attempt to remove the Component before onload has fired on IE/Win.

            if(Ext.isIE && Ext.isWindows && (o =this.SWFObject)){
                o.style.display = 'none'; //hide it regardless of state
                if(o.readyState == 4){
                    for (var x in o) {
                        if (typeof o[x] == 'function') {
                            o[x] = null;
                        }
                    }
                }

            }

        }
        ,onAfterMedia : function(ct){

              ux.Flash.superclass.onAfterMedia.apply(this,arguments);
              this.SWFObject = this.getInterface();
              if(this.mediaObject){
                  var id = this.mediaObject.id;
                  if(Ext.isIE ){

                    //fscommand bindings
                    //implement a fsCommand event interface since its not supported on IE when writing innerHTML

                    if(!(Ext.query('script[for='+id+']').length)){
                      writeScript('var c;if(c=Ext.getCmp("'+this.id+'")){c.onfsCommand.apply(c,arguments);}',
                                  {event:"FSCommand", htmlFor:id});
                    }
                  }else{
                      window[id+'_DoFSCommand'] || (window[id+'_DoFSCommand']= this.onfsCommand.createDelegate(this));

                  }

              }

         },
        //Remove (safely) an existing mediaObject from the Component.
        clearMedia  : function(){

           //de-register fscommand hooks
           if(this.mediaObject){
               var id = this.mediaObject.id;
               if(Ext.isIE){
                    Ext.select('script[for='+id+']',true).remove();
               } else {
                    window[id+'_DoFSCommand']= null;
                    delete window[id+'_DoFSCommand'];
               }
               this._applyFixes();
           }

           ux.Flash.superclass.clearMedia.call(this);
           this.SWFObject = null;
        },

        getSWFObject : function() {
            return this.getInterface();
        },


        //http://www.northcode.com/blog.php/2007/09/11/FSCommand-and-getURL-Bug-in-Flash-Player-9
        onfsCommand : function( command, args){

            if(this.events){
                this.fireEvent('fscommand', this, command ,args );
            }

        },

        //Use Flash's SetVariable method if available
        //returns false if the function is not supported.
        setVariable : function(vName, value){
            var fo = this.getInterface();
            if(fo && typeof fo.SetVariable != 'undefined'){
                fo.SetVariable(vName,value);
                return true;
            }
            return false;

        },

        //Use Flash's SetVariable method if available
        //returns false if the function is not supported.
        getVariable : function(vName){
            var fo = this.getInterface();
            if(fo && typeof fo.GetVariable != 'undefined'){
                return fo.GetVariable(vName);
            }
            return undefined;

        },

        /* this function is designed to be used when a player object notifies the browser
         * if its initialization state
         */
        onFlashInit  :  function(){


            if(this.mediaMask && this.autoMask){this.mediaMask.hide();}
            this.fireEvent.defer(10,this,['flashinit',this, this.getInterface()]);

        },
        /**
         * Dispatches events received from the SWF object (when defined by the eventSynch mediaConfig option).
         *
         * @method _handleSWFEvent
         * @private
         */
        _handleSWFEvent: function(event)
        {
            var type = event.type||event||false;
            if(type){
                 if(this.events && !this.events[String(type)])
                     { this.addEvents(String(type));}

                 return this.fireEvent.apply(this, [String(type), this].concat(Array.prototype.slice.call(arguments,0)));
            }
        }

    });
    // Class Method to handle defined Flash interface events
    ux.Flash.eventSynch = function(elementID, event /* additional arguments optional */ ){
            var SWF = Ext.get(elementID);
            if(SWF && SWF.ownerCt){
                return SWF.ownerCt._handleSWFEvent.apply(SWF.ownerCt, Array.prototype.slice.call(arguments,1));
            }
        };

    /* Generic Flash BoxComponent */
   Ext.ux.FlashComponent = Ext.extend(Ext.ux.MediaComponent,{
           ctype : "Ext.ux.FlashComponent",
           getId : function(){
                 return this.id || (this.id = "flash-comp" + (++Ext.Component.AUTO_ID));
           }

   });

   //Inherit the Media.Flash class interface
   Ext.apply(Ext.ux.FlashComponent.prototype, ux.Flash.prototype);
   Ext.reg('uxflash', Ext.ux.FlashComponent);

   ux.Panel.Flash = Ext.extend(ux.Panel,{
          ctype : "Ext.ux.Media.Panel.Flash"
   });

   Ext.apply(ux.Panel.Flash.prototype, ux.Flash.prototype);

   Ext.reg('flashpanel', Ext.ux.MediaPanel.Flash = Ext.ux.FlashPanel = ux.Panel.Flash);
   Ext.reg('uxflashpanel', ux.Panel.Flash);

   Ext.ux.FlashWindow = (ux.Window.Flash = Ext.extend(ux.Window, {ctype : "Ext.ux.FlashWindow", animCollpase:true}));
   Ext.apply(ux.Window.Flash.prototype, ux.Flash.prototype);

   var writeScript = function(block, attributes) {
        attributes = Ext.apply({},attributes||{},{type :"text/javascript",text:block});

         try{
            var head,script, doc= document;
            if(doc && doc.getElementsByTagName){
                if(!(head = doc.getElementsByTagName("head")[0] )){

                    head =doc.createElement("head");
                    doc.getElementsByTagName("html")[0].appendChild(head);
                }
                if(head && (script = doc.createElement("script"))){
                    for(var attrib in attributes){
                          if(attributes.hasOwnProperty(attrib) && attrib in script){
                              script[attrib] = attributes[attrib];
                          }
                    }
                    return !!head.appendChild(script);
                }
            }
         }catch(ex){}
         return false;
    }
})();