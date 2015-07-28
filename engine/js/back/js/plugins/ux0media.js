//JS_PACK=1;
/* global Ext */
/*
 * @class Ext.ux.Media,
 *        Ext.ux.MediaComponent (xtype: media),
 *        Ext.ux.MediaPanel     (xtype: mediapanel),
 *        Ext.ux.MediaWindow
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

 License: ux.Media classes are licensed under the terms of
 the Open Source GPL 3.0 license (details: http://www.gnu.org/licenses/gpl.html).

 Donations are welcomed: http://donate.theactivegroup.com

 Commercial use is prohibited without a Commercial License. See http://licensing.theactivegroup.com.

 Notes: the <embed> tag is NOT used(or necessary) in this implementation

 Version:  2.0
           Height/Width now honors inline style as well,
           Added Component::mediaEl(eg: 'body', 'el') for targeted media rendering.
           Added scale and status macros.
           Added px unit assertion for strict DTDs.
           Final Quicktime config.
           Adds new PDF(Iframe), Remote Desktop Connection, Silverlight, Office Web Connect-XLS (IE),
                Powerpoint, Wordpress player profiles.


 Version:  Rc1
           Adds inline media rendering within markup: <div><script>document.write(String(new Ext.ux.Media(mediaCfg)));</script></div>
           New extensible classes :
              ux.Media
              ux.MediaComponent
              ux.MediaPanel

           Solves the Firefox reinitialization problem for Ext.Components with embedded <OBJECT> tags
           when the upstream DOM is reflowed.

           See Mozilla https://bugzilla.mozilla.org/show_bug.cgi?id=262354

 Version:  .31 Fixes to canned WMV config.
 Version:  .3  New class Heirarchy.  Adds renderMedia(mediaCfg) method for refreshing
               a mediaPanels body with a new/current mediaCfg.
 Version:  .2  Adds JW FLV Player Support and enhances mediaClass defaults mechanism.
 Version:  .11 Modified width/height defaults since CSS does not seem to
                honor height/width rules
 Version:  .1  initial release

 mediaCfg: {Object}
     { mediaType : mediaClass defined by ux.Media.mediaTypes[mediaClass]
      ,url       : Url resource to load when rendered
      ,requiredVersion : may specify a specific player/plugin version (for use with inline plugin updates where implemented)
      ,loop      : (true/false) (@macro enabled)
      ,scripting : (true/false) (@macro enabled)
      ,start     : (true/false) (@macro enabled)
      ,volume    : (number%, default: 20 ) audio volume level % (@macro enabled)
      ,height    : (default: 100%) (@macro enabled)
      ,width     : (default: 100%) (@macro enabled)
      ,scale     : (default: 1) (@macro enabled)
      ,status    : (default: false) (@macro enabled)
      ,autoSize  : (true/false) If true the rendered <object> consumes 100% height/width of its
                     containing Element.  Actual container height/width are available to macro substitution
                     engine.
      ,controls  : optional: show plugins control menu (true/false) (@macro enabled)
      ,unsupportedText: (String,DomHelper cfg) Text to render if plugin is not installed/available.
      ,listeners  : {"mouseover": function() {}, .... } DOM listeners to set each time the Media is rendered.
      ,params   : { }  members/values unique to Plugin provider
     }

*/


(function(){


    /*
    * Base Media Class
    */

    Ext.ux.Media = function(config){
        Ext.apply(this,config||{});
        this.toString = this.mediaMarkup;
        this.initMedia();
        };

    var ux = Ext.ux.Media;

    ux.mediaTypes =
     {
       "PDF" : Ext.apply({  //Acrobat plugin thru release 8.0 all crash FF3
                tag     :'object'
               ,cls     : 'x-media x-media-pdf'
               ,type    : "application/pdf"
               ,data    : "@url"
               ,autoSize:true
               ,params  : { src : "@url" }

               },Ext.isIE?{
                   classid :"CLSID:CA8A9780-280D-11CF-A24D-444553540000"
                   }:false)

      ,"PDFFRAME" : {  //Most reliable method for all browsers!!
                  tag      : 'iframe'
                 ,cls      : 'x-media x-media-pdf-frame'
                 ,frameBorder : 0
                 ,style    : {overflow:'none',width:'100%',height:'100%'}
                 ,src      : "@url"
                 ,autoSize :true
        }

      ,"WMV" : Ext.apply(
              {tag      :'object'
              ,cls      : 'x-media x-media-wmv'
              ,type     : 'application/x-mplayer2'
              ,data     : "@url"
              ,autoSize : false
              ,params  : {

                  filename     : "@url"
                 ,displaysize  : 0
                 ,autostart    : "@start"
                 ,showControls : "@controls"
                 ,showStatusBar: "@status"
                 ,showaudiocontrols : true
                 ,stretchToFit  : true
                 ,Volume        :"@volume"
                 ,PlayCount     : 1

               }
               },Ext.isIE?{
                   classid :"CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95" //default for WMP installed w/Windows
                   ,codebase:"http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701"
                   ,type:'application/x-oleobject'
                   }:
                   {src:"@url"})

       /* WMV Interface Notes
        * On IE only, to retrieve the object interface (for controlling player via JS)
        * use mediaComp.getInterface().object.controls
        *
        * For all other browsers use: mediaComp.getInterface().controls
        Related DOM attributes for WMV:
        DataFormatAs :
        Name :
        URL :
        OpenState:6
        PlayState:0
        Controls :
        Settings :
        CurrentMedia:null
        MediaCollection :
        PlaylistCollection :
        VersionInfo:9.0.0.3008
        Network :
        CurrentPlaylist :
        CdromCollection :
        ClosedCaption :
        IsOnline:false
        Error :
        Status :
        Dvd :
        Enabled:true
        FullScreen:false
        EnableContextMenu:true
        UiMode:full
        StretchToFit:false
        WindowlessVideo:false
        IsRemote:false
        */

       ,"SWF" :  Ext.apply({
                  tag      :'object'
                 ,cls      : 'x-media x-media-swf'
                 ,type     : 'application/x-shockwave-flash'
                 ,scripting: 'sameDomain'
                 ,standby  : 'Loading..'
                 ,loop     :  true
                 ,start    :  false
                 ,unsupportedText : {cn:['The Adobe Flash Player is required.',{tag:'br'},{tag:'a',cn:[{tag:'img',src:'http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif'}],href:'http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash',target:'_flash'}]}
                 ,params   : {
                      movie     : "@url"
                     ,menu      : "@controls"
                     ,play      : "@start"
                     ,quality   : "high"
                     ,allowscriptaccess : "@scripting"
                     ,allownetworking : 'all'
                     ,allowfullScreen : false
                     ,bgcolor   : "#FFFFFF"
                     ,wmode     : "opaque"
                     ,loop      : "@loop"
                    }

                },Ext.isIE?
                    {classid :"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000",
                     codebase:"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0"
                    }:
                    {data     : "@url"})


        ,"JWP" :  Ext.apply({
              tag      :'object'
             ,cls      : 'x-media x-media-swf x-media-flv'
             ,type     : 'application/x-shockwave-flash'
             ,data     : "@url"
             ,loop     :  false
             ,start    :  false
             ,params   : {
                 movie     : "@url"
                ,flashVars : {
                               autostart:'@start'
                              ,repeat   :'@loop'
                              ,height   :'@height'
                              ,width    :'@width'
                              ,id       : '@id'
                              }
                }

        },Ext.isIE?{
             classid :"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
            ,codebase:"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0"
            }:false)

         /* QT references:
          * http://developer.apple.com/documentation/quicktime/Conceptual/QTScripting_JavaScript/aQTScripting_Javascro_AIntro/chapter_1_section_1.html
          */
        ,"QT" : Ext.apply({
                       tag      : 'object'
                      ,cls      : 'x-media x-media-quicktime'
                      ,type     : "video/quicktime"
                      ,style    : {position:'relative',"z-index":1 ,behavior:'url(#qt_event_source)'}
                      ,scale    : 'aspect'  // ( .5, 1, 2 , ToFit, Aspect )
                      ,unsupportedText : '<a href="http://www.apple.com/quicktime/download/">Get QuickTime</a>'
                      ,scripting : true
                      ,volume   : '50%'
                      ,data     : '@url'
                      ,params   : {
                           src          : Ext.isIE?'@url': null
                          ,href        : "http://quicktime.com"
                          ,target      : "_blank"
                          ,autoplay     : "@start"
                          ,targetcache  : true
                          ,cache        : true
                          ,wmode        : 'transparent'
                          ,controller   : "@controls"
                      ,enablejavascript : "@scripting"
                          ,loop         : '@loop'
                          ,scale        : '@scale'
                          ,volume       : '@volume'
                          ,QTSRC        : '@url'

                       }

                     },Ext.isIE?
                          { classid      :'clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B'
                           ,codebase     :'http://www.apple.com/qtactivex/qtplugin.cab#version=7,2,1,0'

                       }:
                       {
                         PLUGINSPAGE  : "http://www.apple.com/quicktime/download/"

                    })

        //For QuickTime DOM event support include this <object> tag structure in the <head> section
        ,"QTEVENTS" : {
                   tag      : 'object'
                  ,id       : 'qt_event_source'
                  ,cls      : 'x-media x-media-qtevents'
                  ,type     : "video/quicktime"
                  ,params   : {}
                  ,classid      :'clsid:CB927D12-4FF7-4a9e-A169-56E4B8A75598'
                  ,codebase     :'http://www.apple.com/qtactivex/qtplugin.cab#version=7,2,1,0'
                 }

        //WordPress Audio Player : http://wpaudioplayer.com/
        ,"WPMP3" : Ext.apply({
                       tag      : 'object'
                      ,cls      : 'x-media x-media-audio x-media-wordpress'
                      ,type     : 'application/x-shockwave-flash'
                      ,data     : '@url'
                      ,start    : true
                      ,loop     : false
                      ,params   : {
                           movie        : "@url"
                          ,width        :'@width'  //required
                          ,flashVars : {
                               autostart    : "@start"
                              ,controller   : "@controls"
                              ,enablejavascript : "@scripting"
                              ,loop         :'@loop'
                              ,scale        :'@scale'
                              ,initialvolume:'@volume'
                              ,width        :'@width'  //required
                              ,encode       : 'no'  //mp3 urls will be encoded
                              ,soundFile    : ''   //required
                          }
                       }
                    },Ext.isIE?{classid :"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"}:false)


        ,"REAL" : Ext.apply({
                tag     :'object'
               ,cls     : 'x-media x-media-real'
               ,type    : "audio/x-pn-realaudio"
               ,data    : "@url"
               ,controls: 'imagewindow,all'
               ,start   : false
               ,standby : "Loading Real Media Player components..."
               ,params   : {
                          src        : "@url"
                         ,autostart  : "@start"
                         ,center     : false
                         ,maintainaspect : true
                         ,controller : "@controls"
                         ,controls   : "@controls"
                         ,volume     :'@volume'
                         ,loop       : "@loop"
                         ,console    : "_master"
                         ,backgroundcolor : '#000000'
                         }

                },Ext.isIE?{classid :"clsid:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA"}:false)

        ,"SVG" : {
                  tag      : 'object'
                 ,cls      : 'x-media x-media-img x-media-svg'
                 ,type     : "image/svg+xml"
                 ,data     : "@url"
                 ,params   : { src : "@url"}
        }
        ,"GIF" : {
                  tag      : 'img'
                 ,cls      : 'x-media x-media-img x-media-gif'
                 ,src     : "@url"
        }
        ,"JPEG" : {
                  tag      : 'img'
                 ,cls      : 'x-media x-media-img x-media-jpeg'
                 ,src     : "@url"
        }
        ,"JP2" :{
                  tag      : 'object'
                 ,cls      : 'x-media x-media-img x-media-jp2'
                 ,type     : "image/jpeg2000-image"
                 ,data     : "@url"
                }

        ,"PNG" : {
                  tag      : 'img'
                 ,cls      : 'x-media x-media-img x-media-png'
                 ,src     : "@url"
        }
        ,"HTM" : {
                  tag      : 'iframe'
                 ,cls      : 'x-media x-media-html'
                 ,frameBorder : 0
                 ,style    : {overflow:'auto',width:'100%',height:'100%'}
                 ,src     : "@url"
        }
        ,"TXT" : {
                  tag      : 'object'
                 ,cls      : 'x-media x-media-text'
                 ,type     : "text/plain"
                 ,style    : {overflow:'auto',width:'100%',height:'100%'}
                 ,data     : "@url"
        }
        ,"RTF" : {
                  tag      : 'object'
                 ,cls      : 'x-media x-media-rtf'
                 ,type     : "application/rtf"
                 ,style    : {overflow:'auto',width:'100%',height:'100%'}
                 ,data     : "@url"
        }
        ,"JS" : {
                  tag      : 'object'
                 ,cls      : 'x-media x-media-js'
                 ,type     : "text/javascript"
                 ,style    : {overflow:'auto',width:'100%',height:'100%'}
                 ,data     : "@url"
        }
        ,"CSS" : {
                  tag      : 'object'
                 ,cls      : 'x-media x-media-css'
                 ,type     : "text/css"
                 ,style    : {overflow:'auto',width:'100%',height:'100%'}
                 ,data     : "@url"
        }
        ,"SILVERLIGHT" : {
              tag      : 'object'
             ,cls      : 'x-media x-media-silverlight'
             ,type      :"application/ag-plugin"
             ,data     : "@url"
             ,params  : { MinRuntimeVersion: "1.0" , source : "@url" }
        }
        ,"SILVERLIGHT2" : {
              tag      : 'object'
             ,cls      : 'x-media x-media-silverlight'
             ,type      :"application/x-silverlight-2-b2"
             ,data     : "data:application/x-silverlight-2-b2,"
             ,params  : { MinRuntimeVersion: "2.0" }
             ,unsupportedText: '<a href="http://go2.microsoft.com/fwlink/?LinkID=114576&v=2.0"><img style="border-width: 0pt;" alt="Get Microsoft Silverlight" src="http://go2.microsoft.com/fwlink/?LinkID=108181"/></a>'
        }
        ,"DATAVIEW" : {
              tag      : 'object'
             ,cls      : 'x-media x-media-dataview'
             ,classid  : 'CLSID:0ECD9B64-23AA-11D0-B351-00A0C9055D8E'
             ,type     : 'application/x-oleobject'
             ,unsupportedText: 'MS Dataview Control is not installed'

        }


        ,"OWC:XLS" : Ext.apply({     //experimental IE only
              tag      : 'object'
             ,cls      : 'x-media x-media-xls'
             ,type      :"application/vnd.ms-excel"
             ,controltype: "excel"
             ,params  : { DataType : "CSVURL"
                        ,CSVURL : '@url'
                        ,DisplayTitleBar : true
                        ,AutoFit         : true
                     }
             },Ext.isIE?{
                   codebase: "file:msowc.cab"
                  ,classid :"CLSID:0002E510-0000-0000-C000-000000000046" //owc9
                 //classid :"CLSID:0002E550-0000-0000-C000-000000000046" //owc10
                 //classid :"CLSID:0002E559-0000-0000-C000-000000000046" //owc11

                 }:false)

        ,"OWC:CHART" : Ext.apply({     //experimental
              tag      : 'object'
             ,cls      : 'x-media x-media-xls'
             ,type      :"application/vnd.ms-excel"
             ,data     : "@url"
             ,params  : { DataType : "CSVURL" }
             },Ext.isIE?{
                    classid :"CLSID:0002E500-0000-0000-C000-000000000046" //owc9
                  //classid :"CLSID:0002E556-0000-0000-C000-000000000046" //owc10
                  //classid :"CLSID:0002E55D-0000-0000-C000-000000000046" //owc11
                 }:false)

        ,"OFFICE" : {
              tag      : 'object'
             ,cls      : 'x-media x-media-office'
             ,type      :"application/x-msoffice"
             ,data     : "@url"
        }
        ,"POWERPOINT" : Ext.apply({     //experimental
                      tag      : 'object'
                     ,cls      : 'x-media x-media-ppt'
                     ,type     :"application/vnd.ms-powerpoint"
                     ,file     : "@url"
                     },Ext.isIE?{classid :"CLSID:EFBD14F0-6BFB-11CF-9177-00805F8813FF"}:false)

        ,"XML" : {
              tag      : 'iframe'
             ,cls      : 'x-media x-media-xml'
             ,style    : {overflow:'auto'}
             ,src     : "@url"
        }
        ,"VLC" : {
              tag      : 'object'
             ,cls      : 'x-media x-media-vlc'
             ,type     : "application/x-vlc-plugin"
             ,version  : "VideoLAN.VLCPlugin.2"
             ,pluginspage:"http://www.videolan.org"
             ,data     : "@url"
         }
         ,"RDP" : Ext.apply({
              tag      : 'object'
             ,cls      : 'x-media x-media-rdp'
             ,type     : "application/rds"
             ,unsupportedText: "Remote Desktop Web Connection ActiveX control is required. <a target=\"_msd\" href=\"http://go.microsoft.com/fwlink/?linkid=44333\">Download it here</a>."
             ,params:{
                  Server : '@url'
                 ,Fullscreen : false
                 ,StartConnected : false
                 ,DesktopWidth : '@width'
                 ,DesktopHeight : '@height'


             }
         },Ext.isIE?{
             classid :"CLSID:9059f30f-4eb1-4bd2-9fdc-36f43a218f4a"
            ,CODEBASE :"msrdp.cab#version=5,2,3790,0"


         }:false)

    };

    var stateRE = /4$/i;
    var pollReadyState = function(media, cb, readyRE){
        if(media && typeof media.readyState != 'undefined'){
            (readyRE || stateRE).test(media.readyState) ? cb({type:'load'}) : pollReadyState.defer(10,null,[media,cb]);
        }
    };

    if(parseFloat(Ext.version) < 2.1){ throw "Ext.ux.Media and sub-classes are not License-Compatible with your Ext release.";}

    Ext.extend(ux, Object , {

         mediaObject     : null,
         mediaCfg        : null,
         mediaVersion    : null,
         requiredVersion : null,
         unsupportedText : null,


         /* Component Plugins Interface
           extends the Component instance with ux.Media.* interfaces
         */
         init        : function(component){

            if(component && this.getEl === undefined){
                Ext.applyIf(component,this);
            }

         },

         // private (called once by initComponent)
         initMedia      : function(){

            if(!Ext.isIE && this.initialConfig){
               //Attach the Visibility Fix to the current class Component
               new ux.VisibilityFix({
                 mode: this.visibilityCls,
                 hideMode: this.hideMode,
                 elements:this.visModeTargets || null }
               ).init(this);
             }


             /* mediarender Event is raised when the media has been inserted into the DOM.
              * The media however, may NOT be in a usable state when the event is raised.
              */
             if(this.events){
                 this.addEvents(
                      /**
                      * @event mediarender
                      * Fires immediately after the markup has been rendered.
                      * @param {Object} This Media Class object instance.
                      * @param {Object} mediaObject The {@link Ext.Element} object rendered.
                      */

                     'mediarender',
                      /**
                      * @event mediarender
                      * Fires when the mediaObject has reported a loaded state (IE, Opera Only)
                      * @param {Object} This Media Class object instance.
                      * @param {Object} mediaObject The {@link Ext.Element} object loaded.
                      */
                     'mediaload'

                     );
              }
         },

         getMediaType: function(type){
             return ux.mediaTypes[type];
         },

         //Assert default values and exec as functions
         assert : function(v,def){
              v= typeof v === 'function'?v.call(v.scope||null):v;
              return Ext.value(v ,def);
         },

         mediaMarkup : function(mediaCfg, width, height, ct){

             mediaCfg = mediaCfg ||this.mediaCfg;

             if(!mediaCfg){return '';}

             var m= Ext.apply({url:false,autoSize:false}, mediaCfg); //make a copy

             m.url = this.assert(m.url,false);

             if( m.mediaType){

                 var value,p, El = Ext.Element;

                 var media = Ext.apply({}, this.getMediaType(this.assert(m.mediaType,false)) || false );

                 var params = Ext.apply(media.params||{},m.params || {});


                 for(var key in params){

                    if(params.hasOwnProperty(key)){
                      m.children || (m.children = []);
                      p = this.assert(params[key],null);
                      if(p !== null){
                         m.children.push({tag:'param'
                                         ,name:key
                                         ,value: typeof p === 'object'?Ext.urlEncode(p):encodeURI(p)
                                         });
                      }
                    }
                 }
                 delete   media.params;

                 //childNode Text if plugin/object is not installed.
                 var unsup = this.assert(m.unsupportedText|| this.unsupportedText || media.unsupportedText,null);
                 if(unsup){
                     m.children || (m.children = []);
                     m.children.push(unsup);
                 }

                 if(m.style && typeof m.style != "object") { throw 'Style must be JSON formatted'; }

                 m.style = this.assert(Ext.apply(media.style || {}, m.style || {}) , {});
                 delete media.style;

                 m.height = this.assert(height || m.height || media.height || m.style.height, null);
                 m.width  = this.assert(width  || m.width  || media.width || m.style.width , null);



                 m = Ext.apply({tag:'object'},m,media);


                 //Convert element height and width to inline style to avoid issues with display:none;
                 if(m.height || m.autoSize)
                 {
                    Ext.apply(m.style, {
                      height:El.addUnits( m.autoSize ? '100%' : m.height ,El.prototype.defaultUnit)});
                 }
                 if(m.width || m.autoSize)
                 {
                    Ext.apply(m.style, {
                      width :El.addUnits( m.autoSize ? '100%' : m.width ,El.prototype.defaultUnit)});
                 }

                 m.id || (m.id = Ext.id());
                 m.name = this.assert(m.name, m.id);

                 var _macros= {
                   url       : m.url || ''
                  ,height    : (/%$/.test(m.height)) ? m.height : parseInt(m.height,10)||100
                  ,width     : (/%$/.test(m.width)) ? m.width : parseInt(m.width,10)||100
                  ,scripting : this.assert(m.scripting,false)
                  ,controls  : this.assert(m.controls,false)
                  ,scale     : this.assert(m.scale,1)
                  ,status    : this.assert(m.status,false)
                  ,start     : this.assert(m.start, false)
                  ,loop      : this.assert(m.loop, false)
                  ,volume    : this.assert(m.volume, 20)
                  ,id        : m.id
                 };

                 delete   m.url;
                 delete   m.mediaType;
                 delete   m.controls;
                 delete   m.status;
                 delete   m.start;
                 delete   m.loop;
                 delete   m.scale;
                 delete   m.scripting;
                 delete   m.volume;
                 delete   m.autoSize;
                 delete   m.params;
                 delete   m.unsupportedText;
                 delete   m.renderOnResize;
                 delete   m.listeners;
                 delete   m.height;
                 delete   m.width;


                 return Ext.DomHelper.markup(m)
                   .replace(/(%40url|@url)/g          ,_macros.url)
                   .replace(/(%40start|@start)/g      ,_macros.start+'')
                   .replace(/(%40controls|@controls)/g,_macros.controls+'')
                   .replace(/(%40scale|@scale)/g      ,_macros.scale+'')
                   .replace(/(%40status|@status)/g    ,_macros.status+'')
                   .replace(/(%40id|@id)/g            ,_macros.id+'')
                   .replace(/(%40loop|@loop)/g        ,_macros.loop+'')
                   .replace(/(%40volume|@volume)/g    ,_macros.volume+'')
                   .replace(/(%40scripting|@scripting)/g ,_macros.scripting+'')
                   .replace(/(%40width|@width)/g      ,_macros.width+'')
                   .replace(/(%40height|@height)/g    ,_macros.height+'');

             }else{
                 var unsup = this.assert(m.unsupportedText|| this.unsupportedText || media.unsupportedText,null);
                 unsup = unsup ? Ext.DomHelper.markup(unsup): null;
                 return String.format(unsup || 'Media Configuration/Plugin Error',' ',' ');
             }

         }
         //Private
         ,setMask  : function(el) {

             if(this.mediaMask && !this.mediaMask.enable){
                    el = Ext.get(el);
                    if(this.mediaMask = new Ext.ux.IntelliMask(el || this[this.mediaEl],
                       Ext.apply({fixElementForMedia:true},this.mediaMask))){
                            this.mediaMask.el.addClass('x-media-mask');
                      }

            }


         }
          /*
          *  This method updates the target Element with a new mediaCfg object,
          *  or supports a refresh of the target based on the current mediaCfg object
          *  This method may be invoked inline (in Markup) before the DOM is ready
          *  param position indicate the DomHeper position for Element insertion (ie 'afterbegin' the default)
          */
          ,renderMedia : function(mediaCfg, ct, domPosition , w , h){
              if(!Ext.isReady){
                  Ext.onReady(this.renderMedia.createDelegate(this,Array.prototype.slice.call(arguments,0)));
                  return;
              }
              var mc = (this.mediaCfg = mediaCfg || this.mediaCfg) ;
              ct = Ext.get(ct || this.lastCt || (this.mediaObject?this.mediaObject.dom.parentNode:null));

              this.onBeforeMedia.call(this, mc, ct, domPosition , w , h);

              if(ct){
                  this.lastCt = ct;
                  var markup;

                  if(mc && (markup = this.mediaMarkup(mc, w, h, ct))){
                     this.setMask(ct);
                     this.clearMedia();

                     ct.update(markup);
                     if(this.mediaMask && this.autoMask){
                          this.mediaMask.show();
                     }

                  }
              }
              this.onAfterMedia(ct);


          }
          //Remove an existing mediaObject from the DOM.
          ,clearMedia : function(){
            if(Ext.isReady && this.mediaObject){
              try{
                this.mediaObject.removeAllListeners();
                this.mediaObject.remove();
              }catch(er){}
            }
            this.mediaObject = null;
          }

          ,onBeforeMedia  : function(mediaCfg, ct, domPosition ){

            var m = mediaCfg || this.mediaCfg, mt;

            if( m && (mt = this.getMediaType(m.mediaType)) ){
                m.autoSize = m.autoSize || mt.autoSize===true;

                //Calculate parent container size for macros (if available)
                if(m.autoSize && (ct = Ext.isReady?Ext.get(ct||this.lastCt):null)){
                  m.height = ct.getHeight(true) || this.assert(m.height,'auto');
                  m.width  = ct.getWidth(true)  || this.assert(m.width ,'auto');
                }

             }


          },
          // Private
          // Media Load Handler, called when a mediaObject reports a loaded readystate
          onMediaLoad : function(e){
               if(e && e.type == 'load'){
                  this.fireEvent('mediaload',this, this.mediaObject);
                  if(this.mediaMask && this.autoMask){ this.mediaMask.hide(); }
               }
          },
          onAfterMedia   : function(ct){

               if(this.mediaCfg && ct && (this.mediaObject = ct.child('.x-media') )){
                   this.mediaObject.ownerCt = this;

                    //Load detection for non-<object> media (iframe, img)
                   if(this.mediaCfg.tag !== 'object'){
                      this.mediaObject.on({
                       load  :this.onMediaLoad
                      ,scope:this
                      ,single:true
                     });
                   }

                   //IE, Opera possibly others, support a readyState on <object>s
                   pollReadyState(this.mediaObject.dom, this.onMediaLoad.createDelegate(this));

                   var L; //Reattach any DOM Listeners after rendering.
                   if(L = this.mediaCfg.listeners ||null){
                       this.mediaObject.on(L);  //set any DOM listeners
                     }
                   this.fireEvent('mediarender',this, this.mediaObject);


               }
           }

         ,getInterface  : function(){
              return this.mediaObject?this.mediaObject.dom||null:null;
          }

         ,detectVersion  : Ext.emptyFn


         /* Class default: IE provides sufficient DOM readyState for object tags to manage mediaMasks automatically
            (No other browser does), so masking must either be directed manually or use the autoHide option
            of the Ext.ux.IntelliMask.
          * if true the Component attempts to manage the mediaMask based on events/media status
          * false to control mask visibility manually.
          */

         ,autoMask   : Ext.isIE

    });

    //Private adapter class
    var mediaComponentAdapter = function(){};

    Ext.extend(mediaComponentAdapter, Object, {

         hideMode      : !Ext.isIE?'nosize':'display'

        ,animCollapse  : false

        ,visibilityCls : !Ext.isIE ?'x-hide-nosize':null

        ,autoScroll    : true

        ,shadow        : false

        ,bodyStyle     : {position: 'relative'}

        ,resizeMedia   : function(comp, w, h){
            var mc = this.mediaCfg;

            if(mc && this.boxReady){

                // Ext.Window.resizer fires this event a second time
                if(arguments.length > 3 && (!this.mediaObject || mc.renderOnResize )){
                    this.refreshMedia(this[this.mediaEl]);
                }
            }

         }

        ,onAfterRender: function(visModeTargets){

            if(this.mediaCfg.renderOnResize ){
                this.on('resize', this.resizeMedia, this);
            }else{
                this.renderMedia(this.mediaCfg,this[this.mediaEl] || this.getEl());
            }
        }

        ,doAutoLoad : Ext.emptyFn

        ,refreshMedia  : function(target){
            if(this.mediaCfg) {this.renderMedia(null,target);}
        }
        ,mediaMask  : false

    });

    /* Generic Media BoxComponent */
    Ext.ux.MediaComponent = Ext.extend(Ext.BoxComponent, {
        ctype           : "Ext.ux.MediaComponent",
        autoEl          : 'div',
        cls             : "x-media-comp",
        mediaEl         : 'el',   //the containing target element for the media,

        getId           : function(){
            return this.id || (this.id = "media-comp" + (++Ext.Component.AUTO_ID));
        },
        initComponent   : function(){
            this.visModeTargets  = [this.actionMode];
            this.initMedia();

            Ext.ux.MediaComponent.superclass.initComponent.apply(this,arguments);
        },

        onRender  : function(){

              Ext.ux.MediaComponent.superclass.onRender.apply(this, arguments);
              this.onAfterRender();

        },

        afterRender     :  function(ct){

            this.setAutoScroll();
            Ext.ux.MediaComponent.superclass.afterRender.apply(this,arguments);


        },
        beforeDestroy   : function(){
            this.clearMedia();
            Ext.destroy(this.mediaMask,this.loadMask);
            Ext.ux.MediaComponent.superclass.beforeDestroy.call(this);
         },
         //private
        setAutoScroll   : function(){
            if(this.rendered && this.autoScroll){
                this.getEl().setOverflow('auto');
            }
        }

    });

    //Inherit the Media.Flash class interface
    Ext.apply(Ext.ux.MediaComponent.prototype, Ext.ux.Media.prototype);
    Ext.apply(Ext.ux.MediaComponent.prototype, mediaComponentAdapter.prototype);
    Ext.reg('media', Ext.ux.MediaComponent);

    ux.Panel = Ext.extend( Ext.Panel, {

         ctype         : "Ext.ux.Media.Panel"
        ,cls           : "x-media-panel"
        ,mediaEl       : 'body'   //the containing target element for the media

        ,initComponent : function(){
            this.visModeTargets  = [this.collapseEl,this.floating? null: this.actionMode];
            this.initMedia();
            this.html = this.contentEl = this.items = null;

            ux.Panel.superclass.initComponent.call(this);
        }
        ,onRender  : function(){

              ux.Panel.superclass.onRender.apply(this, arguments);
              this.onAfterRender();

         }
        ,beforeDestroy : function(){
            this.clearMedia();
            ux.Panel.superclass.beforeDestroy.call(this);
         }
    });

    //Inherit the Media class interface
    Ext.apply(ux.Panel.prototype,ux.prototype);
    Ext.apply(ux.Panel.prototype, mediaComponentAdapter.prototype);
    Ext.reg('mediapanel', Ext.ux.MediaPanel = ux.Panel);

    ux.Window = Ext.extend( Ext.Window ,{

         cls           : "x-media-window"
        ,ctype         : "Ext.ux.Media.Window"
        ,mediaEl       : 'body'   //the containing target element for the media
        ,initComponent : function(){

            this.visModeTargets  = [this.collapseEl,this.floating? null: this.actionMode];
            this.initMedia();
            this.html = this.contentEl = this.items = null;

            ux.Window.superclass.initComponent.call(this);
        }

        ,onRender  : function(){
           ux.Window.superclass.onRender.apply(this, arguments);
           this.onAfterRender();
         }
         ,beforeDestroy : function(){
            this.clearMedia();
            ux.Window.superclass.beforeDestroy.call(this);
         }

    });

    //Inherit the Media class interface
    Ext.apply(ux.Window.prototype, ux.prototype);
    //then the Adaptor
    Ext.apply(ux.Window.prototype, mediaComponentAdapter.prototype);
    Ext.reg('mediawindow', Ext.ux.MediaWindow = ux.Window);


    Ext.onReady(function(){
        //Generate CSS Rules if not defined in markup
        var CSS = Ext.util.CSS, rules=[];
        CSS.getRule('.x-media') || (rules.push('.x-media{width:100%;height:100%;display:block;overflow:none;outline:none;}'));
        CSS.getRule('.x-media-mask') || (rules.push('.x-media-mask{width:100%;height:100%;position:relative;zoom:1;}'));

        //default Rule for IMG:  h/w: auto;
        CSS.getRule('.x-media-img') || (rules.push('.x-media-img{background-color:transparent;width:auto;height:auto;zoom:1;}'));

        /* These two important rule solves many of the <object/iframe>.reInit issues encountered
         * when setting display:none on an upstream(parent) element (on all Browsers except IE).
         * This default rule enables the new Panel:hideMode 'nosize'. The rule is designed to
         * set height/width to 0 cia CSS if hidden or collapsed.
         * Additional selectors also hide 'x-panel-body's within layouts to prevent
         * container and <object, img, iframe> bleed-thru.  (Also see ux.VisibilityFix below)
         */
        CSS.getRule('.x-hide-nosize') || (rules.push('.x-hide-nosize,.x-hide-nosize *{height:0px!important;width:0px!important;border:none!important;}'));

        if(!!rules.length){
             CSS.createStyleSheet(rules.join(''));
        }

    });

  /* overrides add a third visibility feature to Ext.Element:
    * Now an elements' visibility may be handled by application of a custom (hiding) CSS className.
    * The class is removed to make the Element visible again
    */

    Ext.apply(Ext.Element.prototype, {
      setVisible : function(visible, animate){
            if(!animate || !Ext.lib.Anim){
                if(this.visibilityMode === Ext.Element.DISPLAY){
                    this.setDisplayed(visible);
                }else if(this.visibilityMode === Ext.Element.VISIBILITY){
                    this.fixDisplay();
                    this.dom.style.visibility = visible ? "visible" : "hidden";
                }else {
                    this[visible?'removeClass':'addClass'](String(this.visibilityMode));
                }

            }else{
                // closure for composites
                var dom = this.dom;
                var visMode = this.visibilityMode;

                if(visible){
                    this.setOpacity(.01);
                    this.setVisible(true);
                }
                this.anim({opacity: { to: (visible?1:0) }},
                      this.preanim(arguments, 1),
                      null, .35, 'easeIn', function(){

                         if(!visible){
                             if(visMode === Ext.Element.DISPLAY){
                                 dom.style.display = "none";
                             }else if(visMode === Ext.Element.VISIBILITY){
                                 dom.style.visibility = "hidden";
                             }else {
                                 Ext.get(dom).addClass(String(visMode));
                             }
                             Ext.get(dom).setOpacity(1);
                         }
                     });
            }
            return this;
        },
        /**
         * Checks whether the element is currently visible using both visibility and display properties.
         * @param {Boolean} deep (optional) True to walk the dom and see if parent elements are hidden (defaults to false)
         * @return {Boolean} True if the element is currently visible, else false
         */
        isVisible : function(deep) {
            var vis = !(this.getStyle("visibility") === "hidden" || this.getStyle("display") === "none" || this.hasClass(this.visibilityMode));
            if(deep !== true || !vis){
                return vis;
            }
            var p = this.dom.parentNode;
            while(p && p.tagName.toLowerCase() !== "body"){
                if(!Ext.fly(p, '_isVisible').isVisible()){
                    return false;
                }
                p = p.parentNode;
            }
            return true;
        } });

        var ElementMaskFixes = {
          mask : function(msg, msgCls,maskCls){
            if(this.getStyle("position") == "static"){
                this.setStyle("position", "relative");
            }
            if(this._maskMsg){
                this._maskMsg.remove();
            }
            if(this._mask){
                //this._mask.remove();
            }

            this._mask || (this._mask = Ext.DomHelper.append(this.dom, {cls:maskCls || "ext-el-mask"}, true));

            //removed, causes DOM reflow on non-IE browsers when
            //overflow:hidden is applied to <object>'s parent Element
            //and <object>'s go to sleep when visibility:hidden is applied.

            !Ext.isIE || this.addClass("x-masked");

            this._mask.setVisible(true);

            if(typeof msg == 'string'){

                var mm = this._maskMsg = Ext.DomHelper.append(this.dom,
                 {
                     cls:"ext-el-mask-msg " + msgCls || ''
                    ,style:{
                        visibility:'hidden'
                     }
                     ,cn:{tag:'div',html:msg }
                    }, true);

                 var el = this.dom;
                 (function(){
                    try{ mm.center(el).setVisible(true); } catch(e){}
                 }).defer(4);

            }
            if(Ext.isIE && !(Ext.isIE7 && Ext.isStrict) && this.getStyle('height') == 'auto'){ // ie will not expand full height automatically
                   this._mask.setSize(this.dom.clientWidth, this.getHeight());
            }
            return this._mask;
        },
        /**
         * Removes a previously applied mask.
         */
        unmask : function(remove){

            if(this._maskMsg){
                if(remove){
                    this._maskMsg.remove();
                    delete this._maskMsg;
                } else {
                    this._maskMsg.setVisible.defer(4,this._maskMsg,[false]);
                }
            }
            if(this._mask){
                 if(remove){
                    this._mask.remove();
                    delete this._mask;
                } else {
                    this._mask.setVisible(false);
                }
            }

            this.removeClass("x-masked");
       }
     };



    /* Ext.ux.Media.VisibilityFix plugin.

      This plugin provides an alternate visibility mode to Components that support hideMode.
      If included in a Component, it sets the hideMode

      config options
           elements(array,optional)      :  [list of component members to also adjust visibility for]  (eg. ['bwrap','toptoolbar']
             mode (string,optional)      :   a specific CSS classname (or 1 or 2 ) to use for custom visibility
           hideMide (string;default 'nosize'):  the Component hideMode to assign

       Examples:

       .add({
           xtype:'panel',
           plugins: [new Ext.ux.Media.VisibilityFix({mode:'x-hide-nosize'}) ],
           ...
         });

         //or, enable on parent and all child items:

         var V = new Ext.ux.Media.VisibilityFix({mode:'x-hide-nosize'});
         new Ext.TabPanel({
            plugins: V,
            defaults: {
                plugins: V
            },
            items:[....]
         });
    */

    ux.VisibilityFix = function(opt) {
        opt||(opt={});

        this.init = function(c) {

               c.hideMode = opt.hideMode || c.hideMode;

               c.on('render',
                 function(co){

                    var els = [co.collapseEl, (co.floating? null: co.actionMode)].concat(opt.elements||[]);
                    var El = Ext.Element;

                    var mode = opt.mode || co.visibilityCls || El[co.hideMode.toUpperCase()] || El.VISIBILITY;

                    Ext.each(els, function(el){
                        var e = co[el] || el;
                        if(e && e.setVisibilityMode){e.setVisibilityMode(mode);}
                    });

                 },
                 c,
                 {single:true}
               );

        };


    };

    /**
     * @class Ext.ux.Media.MediaMask
     * A custom utility class for generically masking elements while loading media.
     * @constructor
     * Create a new LoadMask
     * @param {Mixed} el The element or DOM node, or its id
     * @param {Object} config The config object
     */
    Ext.ux.IntelliMask = function(el, config){

        Ext.apply(this, config);
        this.el = Ext.get(el);
        this.removeMask = Ext.value(this.removeMask, true);

        if(el && this.fixElementForMedia){
            Ext.apply(el, ElementMaskFixes );
        }

    };

    Ext.ux.IntelliMask.prototype = {

        /**
         * @cfg {Boolean} removeMask
         * True to create a single-use mask that is automatically destroyed after loading (useful for page loads),
         * False to persist the mask element reference for multiple uses (e.g., for paged data widgets).  Defaults to false.
         */
        /**
         * @cfg {String} msg
         * The text to display in a centered loading message box (defaults to 'Loading Media...')
         */
        msg : 'Loading Media...',
        /**
         * @cfg {String} msgCls
         * The CSS class to apply to the loading message element (defaults to "x-mask-loading")
         */
        msgCls : 'x-mask-loading',


        /** @cfg {Number} zIndex
         * the optional zIndex applied to the masking Elements
         */
        zIndex : null,

        /**
         * Read-only. True if the mask is currently disabled so that it will not be displayed (defaults to false)
         * @type Boolean
         */
        disabled: false,

        /**
         * Read-only. True if the mask is currently applied to the element.
         * @type Boolean
         */
        active: false,

        /**
         * True or millisecond value hides the mask if the @hide method is not called within the specified time limit.
         * @cfg {Boolean/Integer} (true, millisecond timeout, false)
         */
        autoHide: false,

        /**
         * Disables the mask to prevent it from being displayed
         */
        disable : function(){
           this.disabled = true;
        },

        /**
         * Enables the mask so that it can be displayed
         */
        enable : function(){
            this.disabled = false;
        },

        /**
         * Show this Mask over the configured Element.
         * Typical usage:

           mask.show({autoHide:3000});   //show defaults and hide after 3 seconds.

           mask.show('Loading Content', null, loadContentFn); //show msg and execute fn

           mask.show({
               msg: 'Loading Content',
               msgCls : 'x-media-loading',
               fn : loadContentFn,
               fnDelay : 100,
               scope : window,
               autoHide : 2000   //remove the mask after two seconds.
           });
         */
        show: function(msg, msgCls, fn, fnDelay ){
            if(this.disabled || !this.el){
                return null;
            }
            var opt={}, autoHide = this.autoHide;
            fnDelay = parseInt(fnDelay,10) || 20; //ms delay to allow mask to quiesce if fn specified

            if(typeof msg == 'object'){
                opt = msg;
                msg = opt.msg;
                msgCls = opt.msgCls;
                fn = opt.fn;
                autoHide = typeof opt.autoHide != 'undefined' ?  opt.autoHide : autoHide;
                fnDelay = opt.fnDelay || fnDelay ;
            }

            var mask = this.el.mask(msg || this.msg, msgCls || this.msgCls);

            this.active = !!this.el._mask;
            if(this.active){
                if(this.zIndex){
                    this.el._mask.setStyle("z-index", this.zIndex);
                    if(this.el._maskMsg){
                        this.el._maskMsg.setStyle("z-index", this.zIndex+1);
                    }
                }
            }
            if(typeof fn === 'function'){
                fn.defer(fnDelay ,opt.scope || null);
            } else { fnDelay = 0; }

            if(autoHide && (autoHide = parseInt(autoHide , 10)||2000)){
                this.hide.defer(autoHide+(fnDelay ||0),this);
            }

            return this.active? {mask: this.el._mask , maskMsg: this.el._maskMsg} : null;
        },

        /**
         * Hide this mediaMask.
         */
        hide: function(remove){
            if(this.el){
                this.el.unmask(remove || this.removeMask);
            }
            this.active = false;
            return this;
        },

        // private
        destroy : function(){this.hide(true); this.el = null; }
     };

})();