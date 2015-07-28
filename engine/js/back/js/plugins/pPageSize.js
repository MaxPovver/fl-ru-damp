JS_PACK=1;
Ext.namespace('Ext.ux.Andrie');

/**
 * @class Ext.ux.Andrie.pPageSize
 * @extends Ext.PagingToolbar
 * A combobox control that glues itself to a PagingToolbar's pageSize configuration property.
 * @constructor
 * Create a new PageSize plugin.
 * @param {Object} config Configuration options
 * @author Andrei Neculau - andrei.neculau@gmail.com / http://andreineculau.wordpress.com
 * @version 0.6
 */
Ext.ux.Andrie.pPageSize = function(config){
    Ext.apply(this, config);
};

Ext.extend(Ext.ux.Andrie.pPageSize, Ext.util.Observable, {
    /**
     * @cfg {String} beforeText
     * Text to display before the comboBox
     */
    beforeText: 'Показывать',
    
    /**
     * @cfg {String} afterText
     * Text to display after the comboBox
     */
    afterText: ' элементов',
    
    /**
     * @cfg {Mixed} addBefore
     * Toolbar item(s) to add before the PageSizer
     */
    addBefore: '-',
    
    /**
     * @cfg {Mixed} addAfter
     * Toolbar item(s) to be added after the PageSizer
     */
    addAfter: null,
    
    /**
     * @cfg {Bool} dynamic
     * True for dynamic variations, false for static ones
     */
    dynamic: false,
    
    /**
     * @cfg {Array} variations
     * Variations used for determining pageSize options
     */
    variations: [10, 20, 50, 100, 200],
    
    /**
     * @cfg {Object} comboCfg
     * Combo config object that overrides the defaults
     */
    comboCfg: undefined,
    
    init: function(pagingToolbar){
        this.pagingToolbar = pagingToolbar;
        this.pagingToolbar.pageSizeCombo = this;
        this.pagingToolbar.setPageSize = this.setPageSize.createDelegate(this);
        this.pagingToolbar.getPageSize = function(){
            return this.pageSize;
        }
        this.pagingToolbar.on('render', this.onRender, this);
    },
    
    //private
    addSize:function(value){
        if (value>0){
            this.sizes.push([value]);
        }
    },
    
    //private
    updateStore: function(){
        if (this.dynamic) {
            var middleValue = this.pagingToolbar.pageSize, start;
            middleValue = (middleValue > 0) ? middleValue : 1;
            this.sizes = [];
            var v = this.variations;
            for (var i = 0, len = v.length; i < len; i++) {
                this.addSize(middleValue - v[v.length - 1 - i]);
            }
            this.addToStore(middleValue);
            for (var i = 0, len = v.length; i < len; i++) {
                this.addSize(middleValue + v[i]);
            }
        }else{
            if (!this.staticSizes){
                this.sizes = [];
                var v = this.variations;
                var middleValue = 0;
                for (var i = 0, len = v.length; i < len; i++) {
                    this.addSize(middleValue + v[i]);
                }
                this.staticSizes = this.sizes.slice(0);
            }else{
                this.sizes = this.staticSizes.slice(0);
            }
        }
        this.combo.store.loadData(this.sizes);
        this.combo.collapse();
        this.combo.setValue(this.pagingToolbar.pageSize);
    },

    setPageSize:function(value, forced){
        var pt = this.pagingToolbar;
        this.combo.collapse();
        value = parseInt(value) || parseInt(this.combo.getValue());
        value = (value>0)?value:1;
        if (value == pt.pageSize){
            return;
        }else if (value < pt.pageSize){
            pt.pageSize = value;
            var ap = Math.round(pt.cursor/value)+1;
            var cursor = (ap-1)*value;
            var store = pt.store;
            if (cursor > store.getTotalCount()) {
                this.pagingToolbar.pageSize = value;
                this.pagingToolbar.doLoad(cursor-value);
            }else{
                store.suspendEvents();
                for (var i = 0, len = cursor - pt.cursor; i < len; i++) {
                    store.remove(store.getAt(0));
                }
                while (store.getCount() > value) {
                    store.remove(store.getAt(store.getCount() - 1));
                }
                store.resumeEvents();
                store.fireEvent('datachanged', store);
                pt.cursor = cursor;
                var d = pt.getPageData();
                pt.afterTextEl.el.innerHTML = String.format(pt.afterPageText, d.pages);
                pt.field.dom.value = ap;
                pt.first.setDisabled(ap == 1);
                pt.prev.setDisabled(ap == 1);
                pt.next.setDisabled(ap == d.pages);
                pt.last.setDisabled(ap == d.pages);
                pt.updateInfo();
            }
        }else{
            this.pagingToolbar.pageSize = value;
            this.pagingToolbar.doLoad(Math.floor(this.pagingToolbar.cursor/this.pagingToolbar.pageSize) * this.pagingToolbar.pageSize);
        }
        this.updateStore();
    },
    
    //private
    onRender: function(){
        this.combo = Ext.ComponentMgr.create(Ext.applyIf(this.comboCfg||{}, {
            store:new Ext.data.SimpleStore({
                fields:['pageSize'],
                data:[]
            }),
            displayField:'pageSize',
            valueField:'pageSize',
            mode:'local',
            triggerAction:'all',
            width:50,
            xtype:'combo'
        }));
        this.combo.on('select', this.setPageSize, this);
        this.updateStore();
        
        if (this.addBefore){
            this.pagingToolbar.add(this.addBefore);
        }
        if (this.beforeText){
            this.pagingToolbar.add(this.beforeText);
        }
        this.pagingToolbar.add(this.combo);
        if (this.afterText){
            this.pagingToolbar.add(this.afterText);
        }
        if (this.addAfter){
            this.pagingToolbar.add(this.addAfter);
        }
    }
})