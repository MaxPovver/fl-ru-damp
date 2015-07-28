
jScout.useSync("editWindowLib");


teamEdit = Ext.extend(editWindow, {
    getTitle : function(){
        if(this.id==0) {
            return "Создание нового работника";
        }
        return "Редактирование работника №" + this.id;
    }
    ,load: function() {
        Ext.Ajax.request({
            url: '/adminback/team/getinfo/',
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
            url: '/adminback/team/save/',
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
                        columnWidth: 0.5,
                        layout:'form',
                        items:
                            {
                                xtype:'textfield',
                                fieldLabel: 'Имя, фамилия',
                                name:"name",
                                anchor:'95%',
                                //msgTarget :"side",
                                allowBlank: false,
                                blankText: 'Имя, фамилия - обязательное поле.'
                            }
                    }
                    ,{
                        columnWidth: 0.5,
                        layout:'form',
                        items:
                            {
                                xtype:"textfield"
                                ,fieldLabel: 'E-mail'
                                ,name:"email"
                                ,vtype:'email'
                                ,anchor:'95%'
                            }
                    }
                ]
            }
            ,{
                layout:'column',
                items: [
                   {
                        columnWidth: 0.5,
                        layout:'form',
                        items:
                            {
                                xtype:"textfield"
                                ,fieldLabel: 'Должность'
                                ,name:"occupation"
                                ,anchor:'95%'
                                ,allowBlank: false
                            }
                    }
                    ,{
                        columnWidth: 0.5,
                        layout:'form',
                        items:
                            {
                                xtype:"textfield"
                                ,fieldLabel: 'ICQ'
                                ,name:"icq"
                                ,anchor:'95%'
                            }
                    }
                ]
            }
            ,{
                layout:'column',
                items: [
                    
                     {
                        columnWidth: 0.5,
                        layout:'form',
                        items:
                            {
                                xtype:'textfield',
                                fieldLabel: 'Логин',
                                name:"login",
                                anchor:'95%'
                            }
                    }
                    ,{
                        columnWidth: 0.5,
                        layout:'form',
                        items:
                            {
                                xtype:"textfield"
                                ,fieldLabel: 'Skype'
                                ,name:"skype"
                                ,anchor:'95%'
                            }
                    }
                ]
            }
            ,{
                layout:'column',
                items: [
                    {
                        columnWidth: 0.5,
                        layout:'form',
                        items:
                            {fieldLabel:'Категория',
                                xtype:'combo',
                                anchor:'95%',
                                id:this.genId("group_combo"),
                                name:'group_name',
                                plugins:[new Ext.ux.plugins.Bnx.ComboValue({valueName:'groupid'})],
                                store: new Ext.data.JsonStore({
                                    root:'data',
                                    url:'/adminback/team/getGroups/', fields:['id', 'title'], autoLoad: false}),
                                displayField:'title',
                                valueField:'id',
                                minChars:1,
                                triggerAction:'all',
                                forceSelection: false,
                                allowBlank: false,   
                                typeAhead: true
                             }
                    }
                    , {
                        columnWidth: 0.5,
                        layout:'form',
                        items:
                            {
                                xtype:"textarea"
                                ,fieldLabel: 'Дополнения'
                                ,name:"additional"
                                //,width:800
                                ,anchor:'95%'
                                ,height:100
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
                        columnWidth: 0.8,
                        items:leftTopColumn
                    }
                    ,{
                        columnWidth: 0.2,
                        items:
                            {
                                layout:'column',
                                items:[
                                    {
                                        items:
                                            new Ext.ux.UploadPanel({
                                                uploadURL:"/flash/saveFile/"
                                                ,style:"margin:5px;"
                                                ,topButtons:true
                                                ,label:"Аватарка(100 на 100)"
                                                ,id:this.genId("userpic")
                                                ,fieldName:"userpic"
                                                ,thumbWidth:100
                                                ,thumbHeight:100
                                                ,thumbFormat:"/adminback/flashUpload/openFile/{0}/?altDir=team/userpics/"
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
        return items;
    }
});