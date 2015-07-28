
jScout.useSync("editWindowLib");

cblogClass = {
    deleteItem : function (id, callback) {
        Ext.MessageBox.confirm('Подтверждение', 'Действительно желаете удалить?', function(btn, text){
            if (btn == 'yes'){
                Ext.Ajax.request({
                    url:'/myblog/delete/',
                    params: {id:id},
                    success: function(response, options){
                        var data = Ext.util.JSON.decode(response.responseText);
                        
                        if(callback) callback(data.success);
                    }.createDelegate(this)
                });
            }
        }.createDelegate(this));
    }
};

cblogEdit = Ext.extend(editWindow, {
    getTitle : function(){
        if(this.configCommand && this.configCommand.comment) 
            return "Комментирование";
        if(this.id==0) {
            return "Создание блога";
        }
        return "Редактирование";
    }
    ,load: function() {
        if(!this.configCommand || !this.configCommand.comment ) {
        Ext.Ajax.request({
            url: '/myblog/getinfo/',
            params:{id:this.id, comment:(this.configCommand &&this.configCommand.comment !== null)?this.configCommand.comment:false},
            success:function(responseObj){
                var data = Ext.util.JSON.decode(responseObj.responseText);
                
                this.setValues(data);
            }
            ,scope:this
        });
        } else {
            Ext.getCmp(this.idi+'-subpanel').getEl().unmask();
        }
    }
    ,save: function(close_after_save, reload) {
        
        Ext.Ajax.request({
            url: '/myblog/save/',
            params:{id:this.id, 
                form: this.form.getAllValues(), 
                comment:(this.configCommand &&this.configCommand.comment)?true:false, 
                parent:(this.configCommand &&this.configCommand.parent)?this.configCommand.parent:0,
                htmlMode:(this.configCommand &&this.configCommand.htmlMode)?this.configCommand.htmlMode:"normal"
            },
            success:function(responseObj){
                var data = Ext.util.JSON.decode(responseObj.responseText);
                Ext.getCmp(this.idi+'-subpanel').getEl().unmask();
                
                if(data.success) {
                    if(close_after_save) this.close();
                    if(data.html && this.configCommand && this.configCommand.afterOk) this.configCommand.afterOk.apply(this, [data]);
                    //if(reload) admin.reload();
                } else {
                    for(var name in data.validate) {
                        this.form.getForm().findField(name).markInvalid(data.validate[name]);
                    }
                }
            }.createDelegate(this)
            ,scope:this
        });
    }
    ,getItems : function(){
        jScout.useSync("uploadList");
    
        var leftTopColumn =
        [
            {
                layout:'column',
                width:"100%",
                items: [
                    {
                        columnWidth: 1,
                        layout:'form',
                        items:
                            {
                                xtype:'textfield',
                                fieldLabel: 'Заголовок',
                                name:"title",
                                anchor:'100%',
                                allowBlank: false,
                                 msgTarget :'under',
                                blankText: 'Заголовок - обязательное поле.'
                            }
                    }
                ]
            }
            ,{
                layout:'column',
                items: [
                    {
                        columnWidth: 0.6,
                        layout:'form',
                        items:
                            {
                                xtype:"textarea"
                                ,fieldLabel: 'Текст'
                                ,name:"msg"
                                ,width:"100%"
                                ,height:300
                                ,anchor:'98%'
                                 ,msgTarget :'under'
                                 ,allowBlank: false
                                ,blankText: 'Текст - обязательное поле.'
                                ,plugins:[
                                    /*new Ext.ux.ServerValidator(
                                    {
                                        url:'/ajax/register/domenExists/'
                                    }
                                    )
                                    ,*/
                                    new Ext.ux.Description(
                                        {
                                            text:"Можно использовать &lt;b>&lt;i>&lt;p>&lt;ul>&lt;li>&lt;cut>&lt;h>"
                                        }
                                    )
                                 ]
                               // ,style:'padding:8px'
                            }
                    }
                    ,{
                        columnWidth: 0.4,
                        layout:'form',
                        items:
                            {
                                layout:'column',
                                items:[
                                     {
                                        layout:'form',
                                        columnWidth: 1
                                       // ,style:'padding-left:5px'
                                        ,items:
                                        {
                                            xtype:'textfield',
                                            fieldLabel: 'Cсылка на YouTube видео',
                                            name:"yt_link",
                                            anchor:'100%',
                                             allowBlank: true
                                             ,msgTarget :'under'
                                             ,blankText: 'Введите правильную ссылку'
                                            ,plugins:[
                                                new Ext.ux.ServerValidator(
                                                {
                                                    url:'/myblog/checkYoutube/'
                                                }
                                                )
                                                , new Ext.ux.Description(
                                                    {
                                                        text:"Введите ссылку, если хотите прикрепить видео"
                                                    }
                                                )
                                             ]
                                        }
                                     }
                                    ,{
                                        columnWidth: 1,
                                        items:
                                        new Ext.ux.UploadList({
                                            uploadURL:"/flash/saveFile/"
                                           // ,style:"margin:5px;"
                                            ,buttonText:"Добавить..."
                                            ,buttonTextLeftPadding:6
                                            ,label:"Файлы"
                                            ,description: "Возможно загрузить:<br> Картинку: gif, jpeg. 600x1000 пикселей. 300 Кб.<br> Файл: gif, jpeg, png, swf, zip, rar, xls, doc, rtf, pdf, psd, mp3.<br>Максимальный размер файла: 2 Мб."
                                            //,width:500
                                            ,fileTypesDescription:"ЫВсе"
                                            ,fileTypes:"*.jpg;*.gif;*.png;*.txt;*.jpeg;*.swf;*.zip;*.rar;*.xls;*.doc;*.rtf;*.pdf;*.psd;*.mp3"
                                            ,id:this.genId("files")
                                            ,fieldName:"files"
                                            //,thumbFormat:"/adminback/flashUpload/openFile/{0}/?altDir=team/userpics/"
                                            ,errorListener:function(data) {
                                                Ext.MessageBox.alert("Ошибка", data.text);
                                            }
                                        })
                                    }
                                ]
                            }
                    }
                ]
            }
        ];
        var items = [
            {
                layout:'column',
                autoHeight:true,
                items: [
                    {
                        columnWidth: 1,
                        items:leftTopColumn
                    }
                ]
            }
        ];
        return items;
    }
});