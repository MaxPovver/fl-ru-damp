jScout.useSync("plugins/ux0media");
jScout.useSync("plugins/ux1flash");
jScout.useSync("plugins/ux3swfupload");

Ext.ux.UploadPanel = Ext.extend(Ext.Panel, {
    constructor: function(config) {
        Ext.Panel.superclass.constructor.call(this, config);
        //alert("f")
        this.imgId = Ext.id();
        this.hiddenId = Ext.id();
        this.delId = Ext.id();
        this.uploaderId = Ext.id();
        
        var fieldset = new Ext.form.FieldSet(
           {
                xtype:'fieldset',
                title: this.label,
                autoHeight:true,
                
                collapsed: false,
            }
           
           );
        this.add( fieldset);
        
        //if(!this.topButtons) this.add({html:"<img style='display:none;margin-bottom:5px;margin-top:5px;' id='"+this.imgId+"'/>"});
        if(!this.topButtons) fieldset.add({
            xtype: 'box',
            autoEl: {cn:"<img style='display:none;margin-bottom:5px;margin-top:5px;' id='"+this.imgId+"'/>"}
        });
       
       var setImg = function(){
            this.init = function(tp){
                this.tp = tp;
                
                this.tp.setValueOriginal = this.tp.setValue;
                this.tp.setValue = function(val) {
                    this.setValueOriginal.call(this, val);
                    this.fireEvent('change', this, val); 
                    
                }
            }
       }
       
       this.thumbFormatFuncOriginal = function(newV) {
            return String.format(this.thumbFormat, newV); 
       }.createDelegate(this)
       
       this.thumbFormatFunc = this.thumbFormatFuncOriginal;
       
        fieldset.add(
            {
                id:this.hiddenId,
                name:this.fieldName,
                xtype:"hidden"
                ,plugins:[new setImg()]
                ,listeners:{
                     change :function(vssss , newV) {
                        if(newV<=0) return;
                        Ext.get(this.imgId).dom.src = this.thumbFormatFunc(newV);  
                        Ext.get(this.imgId).show();   
                     //   Ext.get(this.uploaderId).hide();   
                        Ext.getCmp(this.delId).show();
                     }.createDelegate(this)
                }
            }
        );
        
        
        fieldset.add(
            {
                xtype:'fileupload',
                uploadURL:this.uploadURL,
                fileTypes:this.fileTypes?this.fileTypes:'*.jpg;*.gif;*.png',
                onSelectComplete:true,
                oneFile:true,
                params:{type:"img", width:this.thumbWidth, height:this.thumbHeight},
                id:this.uploaderId,
                fileTypesDescription:this.fileTypesDescription?this.fileTypesDescription:'Изображения',
                fileSizeLimit:'4MB'
                ,listeners:{
                    uploadSuccess:function(f, serverData) {
                        try {            
                            var data = Ext.util.JSON.decode(serverData);
                            if(data.success) {
                               // if(data.path) {
                               //     this.thumbFormatFunc = function() {return data.path;};
                               // } else {
                              //      this.thumbFormatFunc = this.thumbFormatFuncOriginal;
                              //  }
                                Ext.getCmp(this.hiddenId).setValue(data.id);
                                if(this.completeListener) {
                                    this.completeListener(data);
                                }
                            } else {
                                if(this.errorListener) {
                                    this.errorListener(data);
                                }
                            }
                        } catch (ex) {
                            this.getUploader().debug(ex);
                        }
                    }.createDelegate(this)
                    ,uploadProgress:
                        function(file, bytesLoaded) {
                            try {
                                var percent = Math.ceil((bytesLoaded / file.size) * 100);
                            
                            } catch (ex) {
                                this.getUploader().debug(ex);
                            }

                        }
                    ,debug:function(f) {console.debug(f)}
                }
            }
        );
        
        
        if(this.topButtons) fieldset.add({
            xtype: 'box',
            autoEl: {cn:"<img style='display:none;margin-bottom:5px;margin-top:5px;' id='"+this.imgId+"'/>"}
        });
        
        fieldset.add(
            {
                id:this.delId,
                xtype:"button",
                text:"Удалить",
                style:'padding:8px',
                handler:function() {
                    Ext.getCmp(this.hiddenId).setValue(); 
                    Ext.get(this.imgId).dom.src = "";   
                    Ext.get(this.imgId).hide(); 
                   // Ext.get(this.uploaderId).show();   
                    Ext.getCmp(this.delId).hide();     
                }.createDelegate(this)
            }
        );
        
        Ext.getCmp(this.delId).hide();
        
       // if(this.topButtons) fieldset.add({html:"<img style='display:none;margin-bottom:5px;' id='"+this.imgId+"'/>"});
        //alert(this.asas)
        //Ext.get(this.imgId).hide();
    },
    setImgId : function(id) {
        if(id) {
           // Ext.get(this.imgId).dom.src = String.format(this.thumbFormat, newV);  
           // Ext.get(this.imgId).show();   
          //  Ext.get(this.uploaderId).hide();   
          //  Ext.getCmp(this.delId).show();
        } else {
        
        }
        Ext.getCmp(this.hiddenId).setValue(id);
    }
});