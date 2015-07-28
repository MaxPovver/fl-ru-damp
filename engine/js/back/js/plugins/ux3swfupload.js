//JS_PACK=1;
(function(){
    Ext.namespace("SWFUpload.instances");
    Ext.namespace("Ext.ux.FileUpload");
    
    var FileUploadAdapter = Ext.extend( Ext.ux.Media.Flash , {
       requiredVersion : 9,
       unsupportedText : {cn:['The Adobe Flash Player{0}is required.',{tag:'br'},{tag:'a',href:'http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash',target:'_flash'}]}
       ,instances:{}   
       ,files:[]   
       ,initMedia   : function(){
            this.id = this.initialConfig['id'] || Ext.id();
            var defaults = {
                        url       : '/engine/js/back/swf/swfupload.swf'                        
                        ,id    : this.id
                        ,height    : 21
                        ,width     : 75
                        ,params:{
                            allowScriptAccess:"always"
                            ,flashvars:{
                                movieName:this.id,
                                filePostName: "Filedata",
                                debugEnabled:DEBUG?true:false,
                                fileTypes:'*.*',
                                fileTypesDescription:'asdasd',
                                fileSizeLimit:'20MB',
                                fileUploadLimit:'0',
                                fileQueueLimit:'15',
                                buttonImageURL:'/engine/js/back/images/upload_button.png',
                                buttonWidth:'75',
                                buttonHeight:'21',
                                buttonText:this.initialConfig['buttonText']?this.initialConfig['buttonText']:'Обзор...',
                                buttonTextLeftPadding:this.initialConfig['buttonTextLeftPadding']?this.initialConfig['buttonTextLeftPadding']:15,
                                buttonTextTopPadding:1,
                                buttonTextStyle:'color: #FFFFFF; font-size: 16pt;',
                                buttonDisabled:false,
                                buttonAction:this.initialConfig['oneFile']?-100:-110
                            }
                        }
            };
            SWFUpload.instances[this.id] = this;
            var config_main = ['url', 'height', 'width'];
            Ext.each(config_main, function(v,k) {
                if(this.initialConfig[v]) {
                    defaults[v] =  this.initialConfig[v];   
                }
            }, this);
            var config_flash_vars = [
                'fileTypes'
                ,'fileTypesDescription'
                ,'fileSizeLimit'
                ,'fileUploadLimit'
                ,'fileQueueLimit'
                ,'buttonImageURL'
                ,'buttonText'
                ,'uploadURL'
                ];
            Ext.each(config_flash_vars, function(v,k) {
                if(this.initialConfig[v]) {
                    defaults['params']['flashvars'][v] =  this.initialConfig[v];   
                }
            }, this);

            if(this.initialConfig['height']) {
                defaults['height'] = this.initialConfig['height'];
                defaults['params']['flashvars']['height'] = this.initialConfig['height'];
            }
            if(this.initialConfig['width']) {
                defaults['width'] = this.initialConfig['width'];
                defaults['params']['flashvars']['width'] = this.initialConfig['width'];
            }
            
            defaults['params']['flashvars']['params'] = this.buildParamString(this.initialConfig['params']);
            
            this.mediaCfg = defaults;
            FileUploadAdapter.superclass.initMedia.call(this);
       }
        ,getStats: function () {
            return this.callFlash("GetStats");
        }
        ,startUpload: function (fileId) {
           // d(fileId)
            this.callFlash("StartUpload", fileId);
        }
        ,getFile : function (fileID) {
            if (typeof(fileID) === "number") {
                return this.callFlash("GetFileByIndex", [fileID]);
            } else {
                return this.callFlash("GetFile", [fileID]);
            }
        }
        ,addFileParam : function (fileID, name, value) {
            return this.callFlash("AddFileParam", [fileID, name, value]);
        }
        ,removeFileParam : function (fileID, name) {
            this.callFlash("RemoveFileParam", [fileID, name]);
        }
        ,setUploadURL : function (url) {
            this.callFlash("SetUploadURL", [url]);
        }
        ,setPostParams : function (paramsObject) {
            this.callFlash("SetPostParams", [paramsObject]);
        }
        ,addPostParam : function (name, value) {
            this.callFlash("SetPostParams", [this.settings.post_params]);
        }
        ,removePostParam : function (name) {
            delete this.settings.post_params[name];
            this.callFlash("SetPostParams", [this.settings.post_params]);
        }
        ,setFileTypes : function (types, description) {
            this.settings.file_types = types;
            this.settings.file_types_description = description;
            this.callFlash("SetFileTypes", [types, description]);
        }
        ,setFileSizeLimit : function (fileSizeLimit) {
            this.settings.file_size_limit = fileSizeLimit;
            this.callFlash("SetFileSizeLimit", [fileSizeLimit]);
        }
        ,setFileUploadLimit : function (fileUploadLimit) {
            this.settings.file_upload_limit = fileUploadLimit;
            this.callFlash("SetFileUploadLimit", [fileUploadLimit]);
        }
        ,setFileQueueLimit : function (fileQueueLimit) {
            this.settings.file_queue_limit = fileQueueLimit;
            this.callFlash("SetFileQueueLimit", [fileQueueLimit]);
        }
        ,setFilePostName : function (filePostName) {
            this.settings.file_post_name = filePostName;
            this.callFlash("SetFilePostName", [filePostName]);
        }
        ,setUseQueryString : function (useQueryString) {
            this.settings.use_query_string = useQueryString;
            this.callFlash("SetUseQueryString", [useQueryString]);
        }
        ,setRequeueOnError : function (requeueOnError) {
            this.settings.requeue_on_error = requeueOnError;
            this.callFlash("SetRequeueOnError", [requeueOnError]);
        }
        ,setDebugEnabled : function (debugEnabled) {
            this.settings.debug_enabled = debugEnabled;
            this.callFlash("SetDebugEnabled", [debugEnabled]);
        }
        ,setButtonImageURL : function (buttonImageURL) {
            this.settings.button_image_url = buttonImageURL;
            this.callFlash("SetButtonImageURL", [buttonImageURL]);
        }
        ,setButtonDimensions : function (width, height) {
            this.settings.button_width = width;
            this.settings.button_height = height;
            
            // FIXME -- resize the movie
            
            this.callFlash("SetButtonDimensions", [width, height]);
        }
        ,setButtonText : function (html) {
            this.settings.button_text = html;
            this.callFlash("SetButtonText", [html]);
        }
        ,setButtonTextStyle : function (left, top) {
            this.settings.button_text_top_padding = top;
            this.settings.button_text_left_padding = left;
            this.callFlash("SetButtonTextPadding", [left, top]);
        }
        ,setButtonTextStyle : function (css) {
            this.settings.button_text_style = css;
            this.callFlash("SetButtonTextStyle", [css]);
        }
        ,setButtonDisabled : function (isDisabled) {
            this.settings.button_disabled = isDisabled;
            this.callFlash("SetButtonDisabled", [isDisabled]);
        }
        ,setButtonAction : function (buttonAction) {
            this.settings.button_action = buttonAction;
            this.callFlash("SetButtonAction", [buttonAction]);
        }
        ,fileDialogStart: function () {this.fireEvent('fileDialogStart', this);}
        ,flashReady: function () {this.fireEvent('flashReady', this);}
        ,uploadStart : function (file) {
            this.returnUploadStart.call(this, file);
        }
        ,returnUploadStart : function (file) {
            
           /* var returnValue;
            if (typeof this.settings.upload_start_handler === "function") {
                file = this.unescapeFilePostParams(file);
                returnValue = this.settings.upload_start_handler.call(this, file);
            } else if (this.settings.upload_start_handler != undefined) {
                throw "upload_start_handler must be a function";
            }

            // Convert undefined to true so if nothing is returned from the upload_start_handler it is
            // interpretted as 'true'.
            if (returnValue === undefined) {
                returnValue = true;
            }
            
            
            returnValue = !!returnValue;*/
            returnValue = 1;
            this.callFlash("ReturnUploadStart", [returnValue]);
        }
        ,fileQueued: function (file) {
            var index = this.files.length;
            this.files[index] = file;
            this.fireEvent('fileQueued', index, this.files, this);
        }
        ,fileQueueError: function(file, errorCode, message) {
            this.fireEvent('fileQueueError', file, errorCode, message, this);
        }
        ,fileDialogComplete : function(numFilesSelected, numFilesQueued) {
            this.fireEvent('fileDialogComplete', numFilesSelected, numFilesQueued, this);
            if(this.initialConfig['onSelectComplete']==true) {
                if (numFilesQueued > 0) {
                    this.startUpload();
                }
            }
        }
        ,uploadProgress : function (file, bytesComplete, bytesTotal) {
            this.fireEvent('uploadProgress', file, bytesComplete, bytesTotal, this);
        }
        ,uploadError : function (file, errorCode, message) {
            this.fireEvent('uploadError', file, errorCode, message, this);
        }
        ,uploadSuccess : function (file, serverData) {
            this.fireEvent('uploadSuccess', file, serverData, this);
        }
        ,uploadComplete : function (file) {
            this.fireEvent('uploadComplete', file, this);
            
            this.startUpload();

        }
        ,debug : function (str) {
            this.fireEvent('debug', str, this);
        }
        
        ,callFlash: function () {
            functionName = arguments[0];
            if (typeof(this.getSWFObject()[functionName]) === "function") {
                if (arguments.length === 1) {
                    return this.getSWFObject()[functionName]();
                } else if (arguments.length === 2) {
                    return this.getSWFObject()[functionName](arguments[1]);
                } else if (arguments.length === 3) {
                    return this.el[functionName](arguments[1], arguments[2]);
                } else if (arguments.length === 4) {
                    return this.el[functionName](arguments[1], arguments[2], arguments[3]);
                } else {
                    throw "Too many arguments";
                }
            } else {
                throw "Invalid function name";
            }

        }
        ,buildParamString : function (par) {
            var uri = Ext.urlEncode(Ext.apply(this.getCookies(), par));
            return uri.replace(new RegExp("&", 'g'),"&amp;");
        }
        ,getCookies : function () {
            var postParams = {};
            var i, cookieArray = document.cookie.split(';'), caLength = cookieArray.length, c, eqIndex, name, value;
            for (i = 0; i < caLength; i++) {
                c = cookieArray[i];
                while (c.charAt(0) === " ") {
                    c = c.substring(1, c.length);
                }
                eqIndex = c.indexOf("=");
                if (eqIndex > 0) {
                    name = c.substring(0, eqIndex);
                    value = c.substring(eqIndex + 1);
                    postParams[name] = value;
                }
            }
            return postParams;
        }
    });
    
    FileUpload = Ext.extend(Ext.ux.FlashComponent, { ctype : 'Ext.ux.FileUpload' });
    Ext.apply(FileUpload.prototype, FileUploadAdapter.prototype);
    Ext.reg('fileupload', FileUpload);
})();  