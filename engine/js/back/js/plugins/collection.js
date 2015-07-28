/*Ext.ux.TabCloseMenu*/
Ext.ux.TabCloseMenu = function(){
    var tabs, menu, ctxItem;
    this.init = function(tp){
        tabs = tp;
        tabs.on('contextmenu', onContextMenu);
    }
    function onContextMenu(ts, item, e){
        if(typeof menu == 'undefined'){
            menu = new Ext.menu.Menu([{
                id: tabs.id + '-close',
                text: 'Р—Р°РєСЂС‹С‚СЊ РІРєР»Р°РґРєСѓ',
                handler : function(){
                    tabs.remove(ctxItem);
                }
            },{
                id: tabs.id + '-close-others',
                text: 'Р—Р°РєСЂС‹С‚СЊ РґСЂСѓРіРёРµ РІРєР»Р°РґРєРё',
                handler : function(){
                    tabs.items.each(function(item){
                        if(item.closable && item != ctxItem){
                            tabs.remove(item);
                        }
                    });
                }
            }]);
        }
        ctxItem = item;
        var items = menu.items;
        items.get(tabs.id + '-close').setDisabled(!item.closable);
        var disableOthers = true;
        tabs.items.each(function(){
            if(this != item && this.closable){
                disableOthers = false;
                return false;
            }
        });
        items.get(tabs.id + '-close-others').setDisabled(disableOthers);
        menu.showAt(e.getPoint());
    }
};
/*END Ext.ux.TabCloseMenu*/

/*Ext.ux.grid.CellQtip*/
Ext.namespace("Ext.ux.grid");
Ext.ux.grid.CellQtip = function(cfg){if(cfg) Ext.apply(this, cfg)}
    
Ext.extend(Ext.ux.grid.CellQtip, Ext.util.Observable, 
{
    init:function(grid) {
        this.grid = grid;
        this.gridView = grid.getView();
        this.cm = grid.getColumnModel();
        this.st = grid.getStore();
        
        this.grid.on("mouseover", this.onMouseOver, this);
        this.grid.on("mouseout", this.onMouseOut, this);
        
        //this.grid.addEvents({"filterupdate": true});
    },
    hide : function() {
       if(this.qtip) {
            this.qtip.hide();
            this.qtip.destroy();
        }
        this.qtip = false;
    },
    onMouseOut : function(ev) {
        this.hide();
    },
    onMouseOver : function(ev) {
        ev.stopEvent(); 
        var row = this.gridView.findRowIndex(ev.target);
        var col = this.gridView.findCellIndex(ev.target);
        if(row === false || col === false) return false;
        
        this.hide();
        
        var col_model = this.cm.config[col];
        if(!col_model['qtip']) return;
        
        var config = {};
        
        config = Ext.apply(config, col_model['qtip']);
        
        var record = this.st.getAt(row);
        var value = record.get(col_model['dataIndex'])||false;
        
        if(col_model['qtip']['qtipOverflow']) {
            var text = Ext.DomQuery.selectNode("div", this.gridView.getCell(row, col)).innerHTML;
            var width = Ext.util.TextMetrics.measure(this.gridView.getCell(row, col) ,text).width;
            var cont_width = Ext.get(this.gridView.getCell(row, col)).getWidth();
            if(cont_width>=width) return;
            var renderer = this.cm.getRenderer(col);

            if(renderer && col_model.renderer) {
                config['html'] = col_model.renderer(value, false, record, row, col, this.st);
            } else {
                config['html'] = value;
            }
            //config['width'] = width;
        }
        
        if(col_model['qtip']['renderer']) {
            var res = col_model['qtip']['renderer'].call(this, value, record, row, col, config);
            if(res) config = Ext.apply(config, res);
        }
        
        this.qtip = new Ext.ToolTip(config);
        this.qtip.render(Ext.getBody());
        this.qtip.targetXY = ev.getXY();
        this.qtip.show();
    }
});

Ext.ux.InputHttpMask = function() {};       
Ext.ux.InputHttpMask.prototype = {
    init : function(field) {
        this.field = field;

        if (field.rendered){
            this.assignEl();
        } else {
            field.on('render', this.assignEl, this);
        }
    },
     assignEl : function() {
        this.inputTextElement = this.field.getEl().dom;
       this.field.getEl().on('click', this.processCLick, this);
    },
    processCLick : function() {
        if(!this.inputTextElement.value){
            this.inputTextElement.value = 'http://';
        }
    } 
    
};

Ext.override(Ext.form.FormPanel, {
    getAllValues: function() {
        var values = this.getForm().getValues();

        var el = this.findByType(EditorGridPanelInputed);
        Ext.each(el, function(elem) {
            if(elem.getValues) values = Ext.apply(values, elem.getValues());   
        });
        
        return values;
    }
    ,setAllValues: function(obj, classI) {
        for(var key in obj) {
        
            var el = Ext.getCmp(classI.idi + key);
            if(el) {
                el.store.loadData(obj[key])
                el = false;
            }   
        };
    }
});

Ext.override(Ext.form.TextField, {
    setValue: function(val) {
        Ext.form.TextField.superclass.setValue.call(this, val);
        this.fireEvent('setvalue', this);
    }
});


Ext.ns("Ext.ux.plugins.Bnx");
Ext.ux.plugins.Bnx.ComboValue = function(sett){
    this.init = function(tp){
        this.tp = tp;
        this.tp.getIdValue = this.getIdValue;
        this.tp.on("render", this.onRender, this);
        this.oldOnTriggerClick = this.tp.onTriggerClick.createDelegate(this.tp);
        this.tp.onTriggerClick = this.onTriggerClick.createDelegate(this);
    }
    this.onRender = function() {
        this.ownerForm = this.tp.findParentByType(Ext.form.FormPanel);
        if(!this.ownerForm || !sett.valueName) return;
        
        this.tp.on("change", this.onValueChange, this);
        this.tp.on("blur", this.onBlur, this);
        
        this.CustomValField = new Ext.form.Hidden({name:sett.valueName});
        this.ownerForm.add(this.CustomValField);
    }
    this.getIdValue = function() {
        return this.CustomValField.getValue();
    }
    this.onBlur = function(el) {
        if(el.el.dom.value == "") this.CustomValField.setValue("");
    }
    this.onValueChange = function() {
        this.CustomValField.setValue(this.tp.getValue());
    }
    this.onTriggerClick = function() {
        if(!this.tp.store.getCount()) {
            this.tp.lastQuery = "HACK";
        }
        this.oldOnTriggerClick()
    }
};

EditorGridPanelInputed = Ext.extend(Ext.grid.EditorGridPanel, 
{
    addedElements:{},
    allElements:{},
    deletedElements:{},
    onRender : function(ct, position){
        Ext.grid.EditorGridPanel.superclass.onRender.call(this, ct, position);
           
       // this.getStore().on('datachanged', function() {alert("F")}, this);
        //this.getStore().on('remove', this.onRemove, this);
        //this.getStore().on('add', this.onAdd, this);
        //this.getStore().on('update', this.onUpdate, this);
       // console.debug(this.ownerCt);
        //
    }
    ,getValues : function(st, recs) {
        var obj = this.store.getRange();
        var els = [];
        Ext.each(obj, function(el) {
            els.push(el.data)
        });

        var return_obj = {};
        return_obj[this.name] = els; 
        return return_obj;
    }
    ,onAdd : function(st, recs) {
       Ext.each(recs, function(val)
        {
               
        }.createDelegate(this)
       )
    }
    ,onUpdate : function() {
        //alert("s")
    }
    ,onRemove : function() {
       // alert("s")
    }
}
);


Ext.urlEncode = function(ob) {
    if(typeof ob == 'string') {return "&"+encodeURIComponent(ob);}
    if(typeof ob == 'object') {
        return parseArgsObject(ob);
    }
}
parseArgsObject = function(obj, pre) {
    var ar = "";
    var typeofres = typeof obj;
    //var typeofres2 = typeof obj[0];
    if(typeofres == 'object'){//} && typeofres2 == 'undefined') {
        for(var i in obj) {
            if(typeof obj[i] == 'function') {continue;}
            ar += parseArgsObject_iter(i, obj[i], pre);
        }
    }
    if(/*typeofres2 != 'undefined' || */typeofres == 'array') {
        for(var i=0,le=obj.length;i<le;i++) {
            if(typeof obj[i] == 'function') continue;
            ar += parseArgsObject_iter(i, obj[i], pre);                
        }
    }
            
    
    return ar;
}
parseArgsObject_iter = function(i, val, pre) {
    var ar = "";
    if(!pre) {var e = i + ""; } else {var e = pre + "["+ encodeURIComponent(i!==false?i:"") + "]"; }
    var typeofres = typeof val;
    if(typeofres == 'object' || typeofres == 'array') {
        ar += parseArgsObject(val, e);
    } else {
        ar += "&" + e + "=" + ((!val || val == 'undefined')?'':encodeURIComponent(val));
    }
    return ar;
}

Ext.ns("Ext.ux.plugins");

Ext.ux.plugins.ComboValueOrText = function(){
    this.init = function(tp){
        tp.hiddenName = tp.name;
        tp.on('blur', onBlur);
    }

    function onBlur(field) {
        var value_on_store = field.store.find(field.displayField, field.getRawValue())>-1?1:0;
        if(value_on_store) {
            field.setValue(field.getValue());
            return true;
        }
        field.setValue(field.getRawValue());
    }
};

Ext.ns("Ext.ux.form");

Ext.ux.form.Description = function(config) {
    Ext.apply(this, config, {
        text:false
        ,html:false
    });
    
    Ext.ux.form.Description.superclass.constructor.apply(this, arguments);  
};

Ext.extend(Ext.ux.form.Description, Ext.util.Observable, {
    init:function(field) {
        this.field = field;
        field.on("render", this.onRender, this);        
    }
    ,onRender: function() {
        if(!this.text && !this.html) return;
        var main_node = {id: 'filter-line',  tag: 'div'};
        main_node["class"] = "ux-form-desc-default";
        if(!this.html && this.text) {
            main_node["class"] += " ux-form-desc";
            this.html = this.text;
        }
        if(this.html) {
            main_node["html"] = this.html;
            var filterPanelDiv = Ext.DomHelper.insertAfter(this.field.container,main_node);
        }
    }
});

Ext.ux.Description = Ext.ux.form.Description;


Ext.ux.form.ServerValidator = function(config) {
    Ext.apply(this, config, {
         url:'/ajax/null/'
        ,method:'post'
        ,paramNames:{
             valid:'valid'
            ,reason:'reason'
        }
        ,validationEvent:'keyup'
        ,validationDelay:500
        ,logFailure:false
        ,logSuccess:false
    });
    Ext.ux.form.ServerValidator.superclass.constructor.apply(this, arguments);
};

Ext.extend(Ext.ux.form.ServerValidator, Ext.util.Observable, {
    init:function(field) {
        this.field = field;

        var isValid = field.isValid;
        var validate = field.validate;

        Ext.apply(field, {
//             serverValid: undefined !== this.serverValid ? this.serverValid : false
             serverValid: true

            ,isValid:function(preventMark) {
                if(this.disabled) {
                    return true;
                }
                return isValid.call(this, preventMark) && this.serverValid;
            }

            ,validate:function() {
                var clientValid = validate.call(this);

                if(!this.disabled && !clientValid) {
                    return false;
                }

                if(this.disabled || (clientValid && this.serverValid)) {
                    this.clearInvalid();
                    return true;
                }

                if(!this.serverValid) {
                    this.markInvalid(this.reason);
                    return false;
                }

                return false;
            }

        });

        this.field.on({
             render:{single:true, scope:this, fn:function() {
                this.serverValidationTask = new Ext.util.DelayedTask(this.serverValidate, this);
                
                this.field.el.on(this.validationEvent, function(e){
                    this.field.serverValid = true;
                    this.filterServerValidation(e);
                }, this);
//                this.field.el.on({
//                    keyup:{scope:this, fn:function(e) {
//                        this.field.serverValid = false;
//                        this.filterServerValidation(e);
//                    }}
////                    ,blur:{scope:this, fn:function(e) {
////                        this.field.serverValid = false;
////                        this.filterServerValidation(e);
////                    }}
//                });
            }}
        });
    }
    ,serverValidate:function() {
        var options = {
             url:this.url
            ,method:this.method
            ,scope:this
            ,success:this.handleSuccess
            ,failure:this.handleFailure
            ,params:this.params || {}
        };
        Ext.applyIf(options.params, {
            field:this.field.name || this.name
            ,value:this.field.getValue()
        });
        Ext.Ajax.request(options);
    }
    ,filterServerValidation:function(e) {
        if(this.field.value === this.field.getValue()) {
            this.serverValidationTask.cancel();
            this.field.serverValid = true;
            return;
        }
        if(!e.isNavKeyPress()) {
            this.serverValidationTask.delay(this.validationDelay);
        }
    }
    ,handleSuccess:function(response, options) {
        var o;
        try {o = Ext.decode(response.responseText);}
        catch(e) {
            if(this.logFailure) {
                this.log(response.responseText);
            }
        }
        if(true !== o.success) {
            if(this.logFailure) {
                this.log(response.responseText);
            }
        }
        //console.debug(this.paramNames.reason);
        console.debug(o);
        this.field.serverValid = true === o[this.paramNames.valid];
        this.field.reason = o[this.paramNames.reason];
        this.field.validate();
    }
    ,handleFailure:function(response, options) {
        if(this.logFailure) {
            this.log(response.responseText);
        }
    }
    ,log:function(msg) {
        if(console && console.log) {
            console.log(msg);
        }
    }
});

Ext.ux.ServerValidator = Ext.ux.form.ServerValidator;