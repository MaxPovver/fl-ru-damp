
editWindow = 
function(conf) {
    Ext.apply(this, conf);
    this.init = function(popup) {
        admin.global_wait.hide();
        if(!popup) popup = false;
        this.idi = this.getIdi();
        this.popup = popup;
        
        if(!this.idi)
            this.idi = "editWindow" + Ext.id();
        
        var formSett = {
            baseCls: 'x-plain',
            layout:'fit',
            border: false,
             labelAlign :'top',
            //defaultType: 'textfield',
            //bodyStyle: 'margin-bottom:30px;',
            autoHeight:true
            //height:"100%"
        };
        var items = this.getItems();
        if(items)
            formSett["items"] = items;
            
        
        var buttons = this.getButtons();
        if(Ext.isArray(buttons))
            formSett["buttons"] = buttons;
        
        this.form = new Ext.form.FormPanel(formSett);
        
        var items = {
            id:this.idi+'-panel',
          //  layout: 'fit',
            autoScroll :true,
            border:false,
           // baseCls: 'x-plain',
           // bodyStyle: 'padding:15px;',
            items: {
                title:popup?false: this.getTitle(),
                id:this.idi+'-subpanel',
                layout: 'fit',
                //frame: false,
                bodyStyle: popup?false: 'padding:10px 5px 5px;',//margin-bottom:-30px;
                items: this.form
            }
        };
        
        sett = {};
        //d(this.configCommand);
        if(!popup) {
            tabTrigger(this.getTitle(), this.idi, items, sett);
        } else {
            this.win = new Ext.Window({
                id:this.idi,
                width:"85%",
                title:this.getTitle() + " [После нажатия кнокпи \"ОК\" страница будет перезагружена]",
                autoHeight:true,
                maximizable:true,
                animateTarget: (this.configCommand && this.configCommand.clickEl) ? Ext.get(this.configCommand.clickEl) : false,
                closeAction:'destroy',
                plain: true,
                //border:false,
                modal:true,
                hideLabel: true
                ,resizable: false
                ,items:items
            });
            this.win.show()
        }
        
        this.afterOpen();
    }
    
    this.close = function() {
        if(this.popup) {
            this.win.close();
            if(this.configCommand && this.configCommand.afterClose) this.configCommand.afterClose(this);
           
        }
        else{
            tabClose(this.idi);  
        }
    }
    
    this.setTitle = function(str) {
        var tab = Ext.getCmp(this.idi);
        if(tab) {
            tab.setTitle(str);
        }
        tab = false;
        tab = Ext.getCmp(this.idi+'-subpanel');
        if(tab) {
            tab.setTitle(str);
        }
    }
    this.genId = function(str) {
        return this.idi + str;
    }
    this.getCmp = function(str) {
        return Ext.getCmp(this.idi + str);
    }
    this.setValues = function(data) {
        if(data.all)
            this.form.setAllValues(data.all, this);
        if(data.form)
            this.form.getForm().setValues(data.form);
        Ext.getCmp(this.idi+'-subpanel').getEl().unmask();
    }
}

editWindow = Ext.extend(editWindow, {
    getTitle : function(){return "-";}
    ,open : function() {
        this.init();
        
        if(this.id) {
            Ext.getCmp(this.idi+'-subpanel').getEl().mask("Получение данных...");
            this.load();
        }
    }
    ,openPopup : function() {
        this.init(true);
        
        if(this.id) {
            Ext.getCmp(this.idi+'-subpanel').getEl().mask("Получение данных...");
            this.load();
        }
    }
    ,afterOpen : function() {
    
    }
    ,getButtons : function() {
        var buttons = [{
                    text: 'OК'
                    ,handler: function() {
                        if(this.form.getForm().isValid() 
                            && Ext.getCmp(this.idi+'-subpanel').getEl().mask() 
                            && this.save(true, this.popup?true:false)
                        ) {
                            this.close();
                        } else {
                        
                        }
                    }.createDelegate(this)
                },{
                    text: 'Отмена'
                    ,handler: function() {
                        this.close();
                    }.createDelegate(this)
                }
                ];
        if(this.id>0) {
            buttons.push({
                    text: 'Применить'
                    ,handler: function() {
                        if(this.form.getForm().isValid() 
                            && Ext.getCmp(this.idi+'-subpanel').getEl().mask() 
                            && this.save()
                        ) {
                            //alert("df")
                        } else {
                        
                        }
                    }.createDelegate(this)
                });
        } else {
            /*buttons.push({
                    text: 'Сохранить и переоткрыть'
                    ,handler: function() {
                        if(this.form.getForm().isValid() 
                            && Ext.getCmp(this.idi+'-subpanel').getEl().mask() 
                            && this.save(true, true)
                        ) {
                            //alert("df")
                        } else {
                        
                        }
                    }.createDelegate(this)
                });
            */
        }
        return buttons;
    }
    ,save : function() {
       console.debug(this.form.getAllValues())
       // return "sdfsdf";
    }
    ,getIdi : function() {
       // return "sdfsdf";
    }
    
    ,getItems : function() {
        return [{
                xtype: 'label',
                html: '\
                From<br>\
                From<br>\
                From<br>\
                From<br>\
                From<br>\
                From<br>\
                sdfsdf:'
            }];
    }
    
});

//editWindow = Ext.extend(editWindowClass, editWindow);