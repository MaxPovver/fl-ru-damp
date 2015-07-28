
classExt.add('testsList', 
function(){
    this.init = function() {
        this.idi = "testsList";
           
        useSync("plugins/treegrid/RowExpander"); 
        useSync("plugins/treegrid/TreeGrid"); 
        
        this.record = Ext.data.Record.create([
        {name:"title"},
        {name:"path"},
        {name:"result", type: 'int'},
        {name:"result_text"},
        {name:"run_path"},
         {name: '_id', type: 'auto'},
         {name: '_parent', type: 'auto'},
         {name: '_is_leaf', type: 'bool'},
         {name: 'testable', type: 'bool'},
         {name: 'selected', type: 'bool'}
       ]);
        
        this.store = new Ext.ux.maximgb.treegrid.AdjacencyListStore({
            autoLoad : true,
            storeId:this.idi+'-list',
            parent_id_field_name: '_parent',
             remoteSort :true,
            url: '/adminback/tests/getlist/',
                reader: new Ext.data.JsonReader(
                    {
                        id: '_id',
                        root: 'data',
                        totalProperty: 'total',
                        successProperty: 'success'
                    }, 
                    this.record
                )
        });
        
        useSync("plugins/RowActions");
        useSync("plugins/grid_checkbox");

        var action = new Ext.ux.grid.RowActions(
        {
            header:'Действия'
            ,autoWidth:true
            ,actions:[
                {
                    iconCls:'icon-application_get',
                    qtip:'Открыть',
                    hideIndex:'hide',
                    handler:function(grid, record, action, rowIndex, col) {
                        window.open('/test_runner.php?file='+record.get("run_path"), record.get("run_path"));
                    }.createDelegate(this)
                }
                ,{
                    iconCls:'icon-play',
                    qtip:'Запустить',
                    hideIndex:'hide',
                    handler:function(grid, record, action, rowIndex, col) {
                        this.runTest(record);    
                    }.createDelegate(this)
                }
            ],getData:function(value, cell, record, row, col, store) {
                return {hide:(record.get("testable")?0:1)};
            }
            
        }
        );
         
        var smod = new Ext.grid.SmartCheckboxSelectionModel({
            dataIndex:'selected',
            email: true
        });
        
        var result_renderer = function(val) {
            var tytes = ["Не выполнено", "Выполняется...","Успешно", "Ошибка", "<b>Не возможно выполнение</b>", "Прервано", "Завершено"];
            return tytes[val];
        }
        var log_renderer = function(val, rec) {
            if(!val) return "";
            return "<div style=\"background-color:red;\">" + val + "</div>";
        }
        
        this.grid = new Ext.ux.maximgb.treegrid.GridPanel({
          store: this.store,
          master_column_id : 'title',
          columns: [
                    smod,
                   // {id:'id', resizable: true, width:100,  header: "id",fixed :true, sortable: true, dataIndex: 'id'},
                    {id:"title",header: "Имя", width:35, qtip:{qtipOverflow:true},  resizable: true,  dataIndex: 'title'},
                    {id:"path",header: "Путь", qtip:{qtipOverflow:true},  resizable: true,  dataIndex: 'path'},
                    {id:"result",header: "Результат", width:80, fixed:true, renderer:result_renderer, qtip:{qtipOverflow:true},  resizable: true,  dataIndex: 'result'},
                    {id:"result_text",header: "Лог", renderer:log_renderer, qtip:{qtipOverflow:true},  resizable: true,  dataIndex: 'result_text'}
                    ,action
          ],
          plugins: [new Ext.ux.grid.CellQtip(), action],
          stripeRows: true,
          autoExpandColumn: 'title',
          region:'center',
          id:this.idi+'grid',
          layout:'fit',
          viewConfig: {
               // emptyText :'Нет',
                //templates: {
                //    header: headerTpl
                //},
                forceFit:true
            },
          root_title: 'Тесты'
          ,
        expandAllNodes : function()
        {
            var store = this.getStore();
            store.data.each(function(record){
                if(!store.isLeafNode(record) && store.isLoadedNode(record)){store.expandNode(record);}
            });
            
        }
        
          ,tbar:new Ext.Toolbar({
                items: [
                    {
                        text: 'Обновить список'
                        ,iconCls:'x-tbar-loading'
                        ,handler:function() {
                            Ext.StoreMgr.get(this.idi+'-list').reload();
                        }.createDelegate(this)
                    }
                    ,"-"
                    ,{
                        text: 'Выполнить'
                        ,id:this.idi + "run"
                        ,handler:function() {
                            var to_test = [];
                            this.grid.store.each(function(rec) {
                                if(rec.get("selected") && rec.get("testable")) {
                                    to_test.push(rec);
                                }
                            });   

                            if(to_test.length == 0 ) {
                                Ext.Msg.alert("Уведомление", "Выберите объекты для тестирования!");
                                return;
                            }
                            this.runTests(to_test);
                        }.createDelegate(this)
                    },{
                        text: 'Остановить'
                        ,disabled:true
                        ,id:this.idi + "stop"
                        ,handler:function() {
                            this.stopTests();
                        }.createDelegate(this)
                    }
                ]
            })
          ,bbar:new Ext.Toolbar({
                items: [
                    "Выполнено:",
                    {
                        text: '0'
                        ,id:this.idi + "all"
                        ,handler:function() {}
                    }
                    ,"-"
                    
                    ,"Успешно:"
                    ,{
                        text: '0'
                        ,id:this.idi + "succ"
                        ,handler:function() {}
                    }
                    
                    ,"Не успешно:"                    
                    ,{
                        text: '0'
                        ,id:this.idi + "fail"
                        ,handler:function() {}
                    }
                    
                ]
            })
            ,sm : smod
            ,listeners:{
                rowdblclick:function(th, rowIndex) {
                    var st = this.store;
                    var rec = st.getAt(rowIndex);   
                    
                    if(rec.get("result_text"))
                        Ext.Msg.alert("Результат - описание", rec.get("result_text"));
                    
                }.createDelegate(this)
            }
        });

        this.grid.getStore().on('load', function(th, store, opt){
            this.grid.expandAllNodes();
        }.createDelegate(this));
        
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
                 this.grid
            ]
        };
        
        sett = {};
        
        tabTrigger("Тесты", this.idi, items, sett);
        
    }
    ,this.to_run = []
    ,this.runTests = function(to_run) {
        this.stopTests();
        
        this.to_run = to_run;
        
        Ext.getCmp(this.idi + "all").setText("выполняется...");
        Ext.getCmp(this.idi + "succ").setText(0);
        Ext.getCmp(this.idi + "fail").setText(0);
        
        this.nextTest();
        
        Ext.getCmp(this.idi + "run").disable();
        Ext.getCmp(this.idi + "stop").enable();
    }
    ,this.nextTest = function() {
        if(this.to_run.length == 0) {
            this.stopTests();
            //Ext.Msg.alert("Уведомление", "Завершено!");
            
            var html = "успешно";
            if(Ext.getCmp(this.idi + "fail").getText() > 0) {
                html = "<div style=\"background-color:red;padding:2px;\">" + "С ОШИБКАМИ" + "</div>";
            }
            
            Ext.getCmp(this.idi + "all").setText(html);
            
            return;
        }
        
        for(var el in this.to_run) {
            var el = this.to_run[el];
            this.to_run.remove(el);
            this.runTest(el);
            break;
        }
    }
    ,this.runTest = function(el) {
        el.set("result", 1);
             Ext.Ajax.request({
                url:'/test_runner.php',
                params: {file:el.get("run_path"), format:"json"},
                success: function(response, options){
                    var data = Ext.util.JSON.decode(response.responseText);                    
                    
                    var text = "";
                    if(data.errors.length > 0) {
                        Ext.each(data.errors, function(dat) {
                            text += dat.text;
                        });
                    }
                    
                    el.set("result_text", text);
                    el.set("result", data.status ? 2 : 3);
                    
                    if(data.status) {
                        Ext.getCmp(this.idi + "succ").setText(parseInt(Ext.getCmp(this.idi + "succ").getText()) + 1);
                    } else {
                        Ext.getCmp(this.idi + "fail").setText(parseInt(Ext.getCmp(this.idi + "fail").getText()) + 1);
                    }
                    
                    this.nextTest();
                }.createDelegate(this),
                failure: function(response, options){
                    this.nextTest();
                }.createDelegate(this)
            }
            );
    }
    ,this.stopTests = function() {
        if(this.to_run.length > 0) { 
            Ext.each(this.to_run, function(el) {
                if(el.get("result") == 1) {
                    el.set("result", 5);
                }
            });
            this.to_run = [];
        }
        Ext.getCmp(this.idi + "all").setText("ПРЕРВАНО");
        Ext.getCmp(this.idi + "stop").disable();
        Ext.getCmp(this.idi + "run").enable();
    }
}
);

testsList = Ext.apply(testsList, classExt.result('testsList'));