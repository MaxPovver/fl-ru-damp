
jScout.useSync("editWindowLib");


faqEdit = Ext.extend(editWindow, {
    getTitle : function(){
        if(this.id==0) {
            return "Создание помощи";
        }
        return "Редактирование помощи №" + this.id;
    }
    ,load: function() {
        Ext.Ajax.request({
            url: '/adminback/faq/getinfo/',
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
            url: '/adminback/faq/save/',
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
                        columnWidth: 1,
                        layout:'form',
                        items:
                            {
                                xtype:'textfield',
                                fieldLabel: 'Вопрос',
                                name:"question",
                                anchor:'100%',
                                allowBlank: false,
                                blankText: 'Вопрос - обязательное поле.'
                            }
                    }
                ]
            }
            ,{
                layout:'column',
                items: [
                    {
                        columnWidth: 0.6,
                        layout:'form'
                        ,style:'padding:8px'
                        ,items:
                            {
                                xtype:"textarea"
                                ,fieldLabel: 'Ответ'
                                ,name:"answer"
                                ,width:"95%"
                                ,height:150
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
                                ,fieldLabel: 'Cсылка'
                                ,name:"url"
                                ,width:"95%"
                                
                            }
                    }
                ]
            }
            ,{
                layout:'column',
                items: [
                    {
                        columnWidth: 0.4,
                        layout:'form'
                        ,style:'padding:8px'
                        ,items:
                            {fieldLabel:'Категория',
                                xtype:'combo',
                                anchor:'95%',
                                id:this.genId("category_combo"),
                                name:'cat_name',
                                plugins:[new Ext.ux.plugins.Bnx.ComboValue({valueName:'faqcategory_id'})],
                                store: new Ext.data.JsonStore({
                                    root:'data',
                                    url:'/adminback/faq/getCategs/', fields:['id', 'name'], autoLoad: false}),
                                displayField:'name',
                                valueField:'id',
                                minChars:1,
                                triggerAction:'all',
                                forceSelection: false,
                                typeAhead: true
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