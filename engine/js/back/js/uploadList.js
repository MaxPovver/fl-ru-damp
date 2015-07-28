jScout.useSync("plugins/ux0media");
jScout.useSync("plugins/ux1flash");
jScout.useSync("plugins/ux3swfupload");

Ext.ux.UploadList = Ext.extend(Ext.Panel, {
    constructor: function(config) {
        Ext.Panel.superclass.constructor.call(this, config);
        
        this.hiddenId = Ext.id();
        this.delId = Ext.id();
        this.uploaderId = Ext.id();
        
        this.list = [];
        
        var fieldset = new Ext.form.FieldSet(
           {
                xtype:'fieldset',
                title: this.label,
                autoHeight:true,
                
                collapsed: false,
            }
           
           );
        this.add( fieldset);
        
       
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
        fieldset.add(
            {
                id:this.delId,
                name:this.fieldName+"_deleted",
                xtype:"hidden"
                
            });
       
        fieldset.add(
            {
                id:this.hiddenId,
                name:this.fieldName,
                xtype:"hidden"
                ,plugins:[new setImg()]
                ,listeners:{
                     change :function(vssss , newV) {
                        //alert()
                        var data = Ext.util.JSON.decode(newV);
                       // d(data)
                        if(data)
                        Ext.each(data, function(elem) {
                           // d(elem);
                            var exist = false;
                             Ext.each(this.list, function(elemList) {
                                if(elemList.id == elem.id) {
                                    exist = true;
                                }
                             });
                             if(!exist) {
                                this.list.push({success:true,db_id:elem.db_id,id:elem.id,path:elem.path,name:elem.id});
                             }
                        //     d(exist);
                            //{"success":true,"id":"f_4a486d6bb0166.jpg","path":"http:\/\/127.0.0.21\/temp\/f_4a486d6bb0166.jpg","name":"Desert.jpg"}
                        }.createDelegate(this));
                    //    d(this.list)
                        Ext.each(this.list, function(elem) {
                            if(elem.deleted && elem.el) {
                                elem.el.remove();
                                delete(elem.el);
                                return;
                            }
                            if(elem.id<=0 || elem.el || elem.deleted) return;

                            var chId = Ext.id();//+String.format(this.thumbFormat, elem.id)
                            
                            var elementView = elem.name;
                            if(
                            elementView.lastIndexOf(".jpg")!=-1
                            || elementView.lastIndexOf(".gif")!=-1
                            || elementView.lastIndexOf(".png")!=-1
                            )
                                elementView = "<img width=150px src=\""+elem.path+"\" />";
                            var el = this.listEl.el.insertFirst({ tag: 'div', style:"clear:left;display:block;", children:[{ tag: 'span', style:"float:left;margin:5px;", html:"<a target=\"_blank\" href='"+elem.path+"'>"+elementView+"</a>"}, { tag: 'span', id:chId}]});
                            elem["el"] = el;
                            var eee =  new Ext.Button({
                                renderTo:chId,
                                xtype:"button",
                                text:"X",
                                handler://function(ele) {
                                  //  var ele = ele;
                                   //return 
                                   function(ele) {
                                       ele.deleted = true;
                                       this.updateField();
                                   }.createDelegate(this, [elem])
                               // }.createDelegate(this)(elem)
                            })
                        }.createDelegate(this));
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
                oneFile:false,
                params:{type:"img", width:this.thumbWidth, height:this.thumbHeight},
                id:this.uploaderId,
                fileTypesDescription:this.fileTypesDescription?this.fileTypesDescription:'Изображения',
                buttonTextLeftPadding:this.buttonTextLeftPadding?this.buttonTextLeftPadding:false,
                buttonText:this.buttonText?this.buttonText:false,
                fileSizeLimit:'4MB'
                ,listeners:{
                    uploadSuccess:function(f, serverData) {
                        admin.global_wait.hide();
                        try {            
                            var data = Ext.util.JSON.decode(serverData);
                            if(data.success) {
                                data.temp = true;
                                this.list.push(data);
                                this.updateField();
                                
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
                            admin.global_wait.show("Отправление файла,<br/>подождите пожайлуста...");
                        }.createDelegate(this)
                 //   ,debug:function(f) {if(DEBUG) {console.debug(f);}}
                }
            }
        );
        this.listEl = fieldset.add({ tag: 'div', style:"margin-top:10px;"});
        if(this.description) fieldset.add({ tag: 'div', style:"clear:left;margin-top:5px;color:grey;", html:this.description});
    },
    updateField : function() {
        var value = "";
        var valueDeleted = "";
        var arr = [];
        var arrDel = [];
        Ext.each(this.list, function(elem) {
            if(!elem.deleted) {
                arr.push({id:elem.id, path:elem.path, temp:elem.temp});
            }
            else {
                arrDel.push({db_id: elem.db_id, id:elem.id, path:elem.path, temp:elem.temp});
            }
                               
                                                                       
        });
        Ext.getCmp(this.hiddenId).setValue(Ext.util.JSON.encode(arr));
        Ext.getCmp(this.delId).setValue(Ext.util.JSON.encode(arrDel));
        
    },
    setImgId : function(id) {        
        Ext.getCmp(this.hiddenId).setValue(id);
    }
});