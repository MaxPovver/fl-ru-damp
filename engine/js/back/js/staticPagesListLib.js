
classExt.add('staticPagesList', 
function(){
    this.init = function() {
        this.idi = "staticPagesList";
        
        var record = Ext.data.Record.create([
            {name: 'title'}
            ,{name: 'alias'}
            ,{name: 'n_text'}
       ]);
        var store = new Ext.data.Store({
            autoLoad : false,
            storeId:this.idi+'-list',
            totalProperty: 'totalCount',
            remoteSort: true,
            url: '/adminback/static_pages/getlist/',
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
                {id:'title',sortable:true, sortable:true, resizable: true, menuDisabled:false, header: "Заголовок", dataIndex: 'title'
                    , qtip:{renderer:function(val, rec) {return {title:rec.get("header"), html:rec.get("n_text")};}}}                
            ],
            cls :'global-cell-selectable',
            stripeRows: true,
            viewConfig: {
                emptyText :'Нет страниц',
                forceFit:true
            }
            ,listeners:{
                rowdblclick:function(th, rowIndex) {
                    var st = Ext.getCmp(this.idi+'grid').getStore();
                    var rec = st.getAt(rowIndex);   
                    if(rec) {
                        var id = rec.get('alias');
                    }
                    if(id) {
                         useSync("staticPagesEditLib");
                         (new staticPagesEdit({id:id})).open();
                    }
                }.createDelegate(this)
            },
            plugins: [new Ext.ux.grid.CellQtip()],
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
        
        tabTrigger("Статические страницы", this.idi, items, sett);
        
    }
}
);

staticPagesList = Ext.apply(staticPagesList, classExt.result('staticPagesList'));