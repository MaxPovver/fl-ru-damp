
jScout.useSync("editWindowLib");


partnersClass = {
    deleteItem : function (id, callback) {
        Ext.MessageBox.confirm('Подтверждение', 'Действительно желаете удалить?', function(btn, text){
            if (btn == 'yes'){
                Ext.Ajax.request({
                    url:'/adminback/partners/delete/',
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

partnersEdit = Ext.extend(editWindow, {
    getTitle : function(){
        if(this.id==0) {
            return "Создание партнера";
        }
        return "Редактирование партнера №" + this.id;
    }
    ,load: function() {
        Ext.Ajax.request({
            url: '/adminback/partners/getinfo/',
            params:{id:this.id},
            success:function(responseObj){
                var data = Ext.util.JSON.decode(responseObj.responseText);
                
                this.setValues(data);
            }
            ,scope:this
        });
    }
    ,save: function(close_after_save, reload) {
        
        Ext.Ajax.request({
            url: '/adminback/partners/save/',
            params:{id:this.id, form: this.form.getAllValues()},
            success:function(responseObj){
                var data = Ext.util.JSON.decode(responseObj.responseText);
                
                Ext.getCmp(this.idi+'-subpanel').getEl().unmask();
                if(close_after_save) this.close();
                if(reload) admin.reload();
            }
            ,scope:this
        });
    }
    ,getItems : function(){
        
        jScout.useSync("uploadComp");
    
        var leftTopColumn =
        [
            {
                layout:'column',
                items: [
                    {
                        columnWidth: 0.6,
                        layout:'form'
                        ,style:'padding:8px'
                        ,items:
                            {
                                xtype:"textarea"
                                ,fieldLabel: 'Подпись'
                                ,name:"sign"
                                ,width:"95%"
                                ,height:50   
                            }
                    }
                    ,{
                        columnWidth: 0.4,
                        layout:'form'
                        ,style:'padding:8px'
                        ,
                        items:
                            {
                                xtype:"textfield"
                                ,fieldLabel: 'Ссылка'
                                ,name:"link"
                                ,width:"95%"
                                
                            }
                    }
                ]
            },
            {
                layout:'column',
                items: [
                    {
                        columnWidth: 0.6,
                        layout:'form'
                        ,style:'padding:8px'
                        ,items:
                            {
                                xtype:"textarea"
                                ,fieldLabel: 'Краткий текст'
                                ,name:"msgtext"
                                ,width:"95%"
                                ,height:200
                            }
                    }
                    ,
                    {
                        columnWidth: 0.4,
                        layout:'form'
                        ,style:'padding:8px'
                        ,items:
                            {
                                columnWidth: 1
                                ,border:true
                                ,items:
                                new Ext.ux.UploadPanel({
                                    uploadURL:"/adminback/flashUpload/saveFile/"
                                    ,style:"margin:5px;"
                                    ,topButtons:true
                                    ,label:"Логотип"
                                    ,id:this.genId("logo")
                                    ,fieldName:"logo"
                                    ,thumbWidth:200
                                    ,thumbHeight:150
                                    ,width:230
                                    ,thumbFormat:"/adminback/flashUpload/openFile/{0}/?altDir=about/press/"
                                    ,errorListener:function(data) {
                                        Ext.MessageBox.alert("Ошибка", data.text);
                                    }
                                })
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