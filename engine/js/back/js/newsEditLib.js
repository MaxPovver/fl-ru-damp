
jScout.useSync("editWindowLib");


newsEdit = Ext.extend(editWindow, {
    getTitle : function(){
        if(this.id==0) {
            return "Создание новости";
        }
        return "Редактирование новости №" + this.id;
    }
    ,load: function() {
        Ext.Ajax.request({
            url: '/adminback/news/getinfo/',
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
            url: '/adminback/news/save/',
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
        var leftTopColumn =
        [
            {
                layout:'column',
                items: [
                    {
                        columnWidth: 0.6,
                        layout:'form',
                        items:
                            {
                                xtype:'textfield',
                                fieldLabel: 'Заголовок',
                                name:"header",
                                anchor:'95%',
                                allowBlank: false,
                                blankText: 'Заголовок - обязательное поле.'
                            }
                    }
                    ,{
                        columnWidth: 0.4,
                        layout:'form',
                        items:
                            {
                                xtype:'datefield',
                                fieldLabel: 'Дата',
                                name:"post_date",
                                format: "Y-m-d",
                                anchor:'95%',
                                allowBlank: false,
                                blankText: 'Дата - обязательное поле.'
                            }
                    }
                ]
            }
            ,{
                layout:'column',
                items: [
                    {
                        columnWidth: 1,
                        layout:'form',
                        items:
                            {
                                xtype:"htmleditor"
                                ,fieldLabel: 'Текст'
                                ,name:"n_text"
                                ,width:800
                                ,height:300
                                ,style:'padding:8px'
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