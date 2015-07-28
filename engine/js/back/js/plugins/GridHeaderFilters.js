JS_PACK=1;
Ext.namespace("Ext.ux.grid");

Ext.ux.grid.GridHeaderFilters = function(cfg){if(cfg) Ext.apply(this, cfg)}
    
Ext.extend(Ext.ux.grid.GridHeaderFilters, Ext.util.Observable, 
{
    height: 32,
    padding: 4,
    hidden:true,
    isHidden:false,
    createOnRender:false,
    rendered:false,
    filtered:false,
    prefixName:'filter[{0}]',
    init:function(grid) {
        this.grid = grid;
        this.gridView = null;
        this.filters = {};

        this.unq = Ext.id();
        
        this.grid.isUxFiltered = function() {
            return (this.filtered == 0)?false:true;
        }.createDelegate(this)
        
        
        this.headerCells = null;
        if(this.createOnRender) this.grid.on("render", this.onRender, this);
        this.grid.on("resize", this.onResize, this);
        //this.grid.on("columnmove", this.onResize, this);
        this.grid.on("columnresize", this.onResize, this);
        
        //this.grid.on("beforestatesave", this.saveFilters, this);
        //this.grid.on("beforestaterestore", this.loadFilters, this);
        
        //this.grid.addEvents({"filterupdate": true});
        
        //this.grid.stateEvents[this.grid.stateEvents.length] = "filterupdate";
        //this.grid.on("filterupdate", this.grid.saveState, this.grid, {delay: 100});
    },
    allocateText: function(val, allocText) {
        return val.toString().replace(new RegExp(allocText.toString(),"i"), '<span class="allocatespan">'+allocText+'</span>');
    },
    allocateStore: function(plugin) {
        return function(Store, records, options) {
            if(this.isHidden) return;
            //d(this.isHidden)
            var map = {};
            var seted = false;
            for(var e in Store.baseParams) {
                if(!Store.baseParams[e] || Store.baseParams[e] == '') continue;
                var e2 = plugin.filter_to_index[e];
                if(!plugin.cm.config[e2]['filter']['allocate']) continue;
                map[plugin.cm.config[e2]['dataIndex']] = Store.baseParams[e];
                seted = true;
            };
            if(!seted) return;
            Ext.each(records, function(v) {
                for(var v2 in v.data) {
                   if(typeof map[v2] != 'undefined') {
                        v.set(v2, plugin.allocateText(v.data[v2], map[v2]));
                   }
                };
                v.commit();
            });
        }
    },
    onRenderCell: function(oldRenderer, plugin) {
        return function(value, p, record, rowIndex, colIndex, store) {
            //d(p);
            value = plugin.overText(value);
            record.data = plugin.overArr(record.data);
            if(oldRenderer) value = oldRenderer.call(this, value, p, record, rowIndex, colIndex, store);
            return value;
        }
    },
    onResize: function() {
        this.cm = this.grid.getColumnModel();
        for ( var i = 0; col = this.cm.config[i]; i++) 
        {
            var iColIndex = this.cm.getIndexById(col.id);  
            var filterPanelDiv = Ext.get("filter-control-"+this.unq+col.id);
            if(!filterPanelDiv) continue;
            var iColWidth = this.cm.getColumnWidth(iColIndex);
            var padd_delta = 4;
            var fil_control = Ext.getCmp("filter-control-"+this.unq+col.id);
            if(
                fil_control.xtype == 'combo'
                    || fil_control.xtype == 'datefieldplus'
                ) 
                    padd_delta = 20;
            filterPanelDiv.setWidth(iColWidth-this.padding*2-padd_delta); 
        }
        
    },
    hide: function() {
        Ext.get('filter-line'+this.unq).setVisibilityMode(Ext.Element.DISPLAY).setVisible(false);
        this.isHidden = true;
        this.dropFilter();
    },
    show: function() {
        if(!this.rendered) {
            this.hidden = false;
            this.onRender();
            this.onResize();
        } else {
            Ext.get('filter-line'+this.unq).setVisible(true);
        }
        this.isHidden = false;
    },
    onRender: function() {
        this.cm = this.grid.getColumnModel();
        this.grid.getStore().on('load', this.allocateStore(this).createDelegate(this));        
        this.gridView = this.grid.view;
        var headTr = Ext.DomQuery.selectNode("tr",this.gridView.mainHd.dom);
        columns_nodes = [];
        for ( var i = 0; col = this.cm.config[i]; i++) 
        {
            columns_nodes.push({id:'filter-'+this.unq+col.id, tag:'td'});
        }
        
        
        var main_node = {id: 'filter-line'+this.unq,  tag: 'tr', children: [columns_nodes]};
        main_node["class"] = "x-grid3-hd-row";
        var filterPanelDiv = Ext.DomHelper.insertAfter(headTr,main_node);
        
        if(this.hidden) this.hide();
        
        var config = {
            listeners:{
                "change": this.applyFilter,
                "specialkey": function(el,ev){if(ev.getKey() == ev.ENTER) this.applyFilter(el)},
                "select": this.applyFilter,
                scope: this
            },
            autoWidth:true
        };
        this.filter_to_index = {};
        for ( var i = 0; col = this.cm.config[i]; i++) 
        {
            if(!col.filter) continue;
            
            var filterName = (col.filter.filterName?col.filter.filterName:col.id);
            filterName = String.format(col.filter.prefixName?col.filter.prefixName:this.prefixName, filterName);
            
            var config_n = Ext.apply({xtype:"textfield"}, {});
            config_n = Ext.apply(config_n,col.filter);
            config_n = Ext.apply(config_n,{id:"filter-control-"+this.unq+col.id, filterName:filterName});
            config_n = Ext.apply(config_n,config);

            this.filter_to_index[filterName] = i;
            
            if(col.xtype != "combo")
                    config_n.enableKeyEvents = true;
            config_n['anchor']=  '100%';
            
            if(config_n.store && !config_n.store.autoLoad) {
                config_n.store.reload();
            }
            
            var panelConfig = {
                id: "filter-panel-"+this.unq+col.id,
                renderTo: 'filter-'+this.unq+col.id, 
                border: false,
                autoWidth:true,
                baseCls: 'x-plain',
                bodyStyle: "padding: "+this.padding+"px",
                bodyBorder: false,
                //layout: "fit",
                items: [config_n]
            };
            var filterPanel = new Ext.Panel(panelConfig);
        }
        this.rendered = true;
    },
    onRefresh: function(){
        this.onRender();
    },
/*    
    saveFilters: function(grid, status)
    {
        var vals = {};
        for(var name in this.filters)
        {
            vals[name] = this.filters[name];
        }
        status["headFilters"] = vals;
        return true;
    },
    
    loadFilters: function(grid, status)
    {
        var vals = status.headFilters;
        if(vals)
        {
            this.filters = {};
            var bOne = false;
            for(var name in vals)
            {
                this.filters[name] = vals[name];
                this.grid.store.baseParams[name] = vals[name];
                bOne = true;
            }
            if(bOne)
                this.grid.store.reload();
        }
        
    },
*/    
    getFieldValue: function(eField) {
        if(eField.xtype == 'datefieldplus') {
            var val =  eField.getValue();
            for(var i = 0;v = val[i];i++) {
                val[i] = v.format('Y-m-d');
                //d(v);
            }
            return val;
        }
        return eField.getValue();
    },
    
    clearFieldValue: function(eField) {
        eField.setValue('');
    },
    setFieldValue: function(eField, value)
    {
        return eField.setValue(value);
    },

    dropFilter: function() {
        for(var ee in this.filters) {
            delete(this.grid.store.baseParams[ee]);
            if(this.grid.store.lastOptions['params']) delete(this.grid.store.lastOptions['params'][ee]);
        }
        for ( var i = 0; col = this.cm.config[i]; i++) 
        {
            if(!col.filter) continue;
            
            this.clearFieldValue(Ext.getCmp("filter-control-"+this.unq+col.id));
        }
        this.filters = {};
        this.filtered = 0;
        //this.grid.fireEvent("filterupdate",el.filterName,sValue,el);
        
        this.grid.store.reload();
    },
    applyFilter: function(el) {
        if(!el) return;
        if(typeof el.getValue != 'function') return;
        if(typeof el.isValid == 'function' && !el.isValid()) return;
        
        var sValue = this.getFieldValue(el);
        if(this.filters[el.filterName] == sValue)  return;
        
        this.grid.store.baseParams[el.filterName] = sValue;
        //console.debug(this.grid.store.baseParams)
        delete(this.grid.store.lastOptions.params["anode"])
        this.filters[el.filterName] = sValue;
        this.filtered = 1;
       // this.grid.fireEvent("filterupdate",el.filterName,sValue,el);
       
        if(this.grid.store.lastOptions['params'] && this.grid.store.lastOptions['params']["start"] >0 ) this.grid.store.lastOptions['params']["start"] = 0;
       
        this.grid.store.reload();
    }
});