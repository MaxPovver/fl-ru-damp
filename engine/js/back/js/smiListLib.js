
smiClass = {
    deleteItem : function (id, callback) {
        Ext.MessageBox.confirm('Подтверждение', 'Действительно желаете удалить?', function(btn, text){
            if (btn == 'yes'){
                Ext.Ajax.request({
                    url:'/adminback/smi/delete/',
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

classExt.add('smiList', 
function(){
    this.init = function() {
        this.idi = "smiList";
        
        useSync("plugins/RowActions");
        var action = new Ext.ux.grid.RowActions(
        {
            header:'Действия'
            ,autoWidth:true
            ,actions:[
                {
                    iconCls:'icon-application_get',
                    qtip:'Открыть на сайте',
                    handler:function(grid, record, action, rowIndex, col) {
                        window.open(String.format("http://{0}/press/smi/{1}/", MAIN_URL, record.get('id')));
                    }
                }/*,
                {
                    iconCls:'icon-cross',
                    qtip:'Удалить',
                    handler:function(grid, record, action, rowIndex, col) {
                        Ext.MessageBox.confirm('Подтверждение', 'Действительно желаете удалить?', function(btn, text){
                            if (btn == 'yes'){
                                Ext.Ajax.request({
                                    url:'/adminback/news/delete/',
                                    params: {id:record.get("id")},
                                    success: function(response, options){
                                        var data = Ext.util.JSON.decode(response.responseText);
                                        if(data.success == 1) {
                                           Ext.StoreMgr.get(this.idi+'-list').reload();
                                        }
                                    }.createDelegate(this)
                                });
                            }
                        }.createDelegate(this));
                    }.createDelegate(this)
                }   */
            ]
        }
        );
        
        var record = Ext.data.Record.create([
            {name: 'title'}
            ,{name: 'short'}
            ,{name: 'sign'}
            ,{name: 'link'}
            //,{name: 'date_create', type: 'date', dateFormat: "Y-m-d H:i:s"}
            ,{name: 'id', type: 'int'}
       ]);
        var store = new Ext.data.Store({
            autoLoad : false,
            storeId:this.idi+'-list',
            totalProperty: 'totalCount',
            remoteSort: true,
            url: '/adminback/smi/getlist/',
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
                ,{id:'title',sortable:true, sortable:true, resizable: true, menuDisabled:false, header: "Заголовок", dataIndex: 'title'
                    , qtip:{renderer:function(val, rec) {return {title:rec.get("title"), html:rec.get("short")};}}}
                ,action
            ],
            cls :'global-cell-selectable',
            stripeRows: true,
            viewConfig: {
                emptyText :'Нет СМИ о фри-лансе',
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
                         useSync("smiEditLib");
                         (new smiEdit({id:id})).open();
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
                            useSync("smiEditLib");
                            (new smiEdit({id:0})).open();    
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
        
        jScout.useSync("plugins/pPageSize");
        sett['bbar'] = new Ext.PagingToolbar({
            pageSize: 20,
            plugins:new Ext.ux.Andrie.pPageSize(),
            store: store,
            id:this.idi+'GridBBar',
            displayInfo: true,
            displayMsg: 'СМИ о фри-лансе показаны'+' {0} - {1} из {2}',
            emptyMsg: "Нет СМИ о фри-лансе в списке."
            
        });
        
        store.load();
        
        tabTrigger("СМИ о фри-лансе", this.idi, items, sett);
        
    }
}
);

smiList = Ext.apply(smiList, classExt.result('smiList'));