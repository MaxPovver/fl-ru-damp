

teamClass = {
    editRadzelItem : function (id, title, callback) {
        Ext.Msg.prompt('Изменение заголовка раздела:', 'Введите заголовок:', function(btn, text){
            if (btn == 'ok'){
                Ext.Ajax.request({
                    url:'/adminback/team/editRadzel/',
                    params: {id:id, title:text},
                    success: function(response, options){
                        var data = Ext.util.JSON.decode(response.responseText);
                        if(callback) callback(data.success);   
                    }.createDelegate(this)
                }); 
            }
        }, false, false, title);
    }
    ,
    deleteItem : function (id, callback) {
        Ext.MessageBox.confirm('Подтверждение', 'Действительно желаете удалить?', function(btn, text){
            if (btn == 'yes'){
                Ext.Ajax.request({
                    url:'/adminback/team/delete/',
                    params: {id:id},
                    success: function(response, options){
                        var data = Ext.util.JSON.decode(response.responseText);
                        
                        if(callback) callback(data.success);
                    }.createDelegate(this)
                });
            }
        }.createDelegate(this));
    }
    ,
    moveGroupItem : function (groupid, direction, callback) {
        Ext.Ajax.request({
            url:'/adminback/team/moveGroup/',
            params: {group_id:groupid, direction:direction},
            success: function(response, options){
                var data = Ext.util.JSON.decode(response.responseText);
                
                if(callback) callback(data.success);
            }.createDelegate(this)
        });
    }
    ,
    moveUserItem : function (groupid, id, direction, callback) {
        Ext.Ajax.request({
            url:'/adminback/team/moveUser/',
            params: {id:id, group_id:groupid, direction:direction},
            success: function(response, options){
                var data = Ext.util.JSON.decode(response.responseText);
                
                if(callback) callback(data.success);
            }.createDelegate(this)
        });
    }
};

classExt.add('teamList', 
function(){
	this.init = function() {
        this.idi = "teamList";
        useSync("plugins/RowActions");
        
        var action = new Ext.ux.grid.RowActions(
        {
            header:'Действия'
            ,autoWidth:true
            ,actions:[
                {
                    iconCls:'icon-cross',
                    qtip:'Удалить',
                    handler:function(grid, record, action, rowIndex, col) {
                        teamClass.deleteItem(record.get('id'), function(success) {
                            if(success) Ext.StoreMgr.get(this.idi+'-list').reload();
                        }.createDelegate(this))
                    }.createDelegate(this)
                }
            ]
        }
        );
        
        var record = Ext.data.Record.create([
            {name: 'name'}
            ,{name:'occupation'}
            ,{name: 'login'}
            ,{name:'email'}
            ,{name:'icq'}
            ,{name:'skype'}
            ,{name:'groupid', type: 'int'}
            ,{name:'additional'}
            ,{name: 'id', type: 'int'}
       ]);
        
        var store = new Ext.data.Store({
            autoLoad : false,
            storeId:this.idi+'-list',
            totalProperty: 'totalCount',
            remoteSort: true,
            url: '/adminback/work/getlist/',
            sortInfo:{field: "id", direction: "ASC"},
            reader: new Ext.data.JsonReader({root: 'data', id: 'id', successProperty: 'success', totalProperty: 'totalCount'}, record)
        });
        
        var grid = new Ext.grid.GridPanel({
            store: store,
            region:'center',
            id:this.idi+'grid',
            layout:'fit',
            loadMask:true, 
            columns: [
                {id:'id', resizable: true, sortable:true, menuDisabled:false, fixed :true, header: "ID", width:70, dataIndex: 'id'}
                ,{id:'name',sortable:true, sortable:true, resizable: true, menuDisabled:false, header: "Имя", dataIndex: 'name'}
                ,{id:'login',sortable:true, resizable: true, menuDisabled:false, header: "Логин", dataIndex: 'login'
                }
                ,{id:'occupation', sortable:true, header:'Должность', dataIndex:'occupation'}
                ,{id:'email', sortable:true, header:'E-mail', dataIndex:'email'}
                ,{id:'icq', sortable:true, header:'ICQ', dataIndex:'icq'}
                ,{id:'skype', sortable:true, header:'Skype', dataIndex:'skype'}
                ,{id:'goupid', sortable:true, header:'Группа', dataIndex:'groupid'}
                ,{id:'additional', sortable:true, header:'Дополнительно', dataIndex:'additional'}
                ,action
            ],
            cls :'global-cell-selectable',
            stripeRows: true,
            viewConfig: {
                emptyText :'Нет команды',
                forceFit:true
            }
            ,listeners:{
                rowdblclick:function(th, rowIndex) {
                    var st = Ext.getCmp(this.idi+'grid').getStore();
                    var rec = st.getAt(rowIndex);   
                    if(rec) {
                        var id = rec.get('id');
                    }
                    if(id) {
                         useSync("teamEditLib");
                         (new teamEdit({id:id})).open();
                    }
                }.createDelegate(this)
            },
            plugins: [new Ext.ux.grid.CellQtip(), action],
            onRender: function() {
                Ext.grid.GridPanel.prototype.onRender.apply(this, arguments);
                this.addEvents("beforetooltipshow");
            }
            ,sm: new Ext.grid.RowSelectionModel(
                {
                    singleSelect:true
                }
            )
            ,tbar:new Ext.Toolbar({
                items: [
                    {
                        text: 'Добавить'
                        ,iconCls:'icon-add'
                        ,handler:function() {
                            useSync("teamEditLib");
                            (new teamEdit({id:0})).open();    
                        }.createDelegate(this)
                    }
                    ,'-',
                    {
                        text: 'Обновить'
                        ,iconCls:'x-tbar-loading'
                        ,handler:function() {
                            Ext.StoreMgr.get(this.idi+'-list').reload();
                        }.createDelegate(this)
                    }
                ]
            })
        });
        
        
        var items = {
            id:this.idi+'-panel',
            layout:'border',
            bodyBorder: false,
            defaults: {
                split: true,
                animFloat: false,
                autoHide: false,
                useSplitTips: true
            },
            items: [
                grid
            ]
        };
        
        sett = {};
        
        store.load();
        
        tabTrigger("Команда", this.idi, items, sett);
        
    }
}
);

teamList = Ext.apply(teamList, classExt.result('teamList'));