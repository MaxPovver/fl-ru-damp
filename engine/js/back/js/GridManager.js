/**
 * GridManager registeres datagrids and renders them upon request
 *
 * @author vbolshov
 */
GridManager = {
	entries: {},// GridManagerEntry[] (hash)
	editors: {},// open editors (psedo-modal Ext.Window objects)
	// NOTE: name also specifies the ID of the element that the grid will be rendered to.
	register: function(name, url, fields, columns) {
		GridManager.entries[name] = new GridManagerEntry(name, url, fields, columns);
		return GridManager.entries[name];
	},
	
	render: function(name) {
		if (name in GridManager.entries)
		{
			GridManager.entries[name].render();
		} else {
			alert("No such entry: " + name);
		}
	},
	// hide editor for a certain grid
	hideEditor: function(name) {
		if (name in GridManager.editors)
		{
			GridManager.editors[name].hide();
		} else {
			alert("No such editor: " + name);
		}
	}
}
// a single grid wrapper
GridManagerEntry = function(name, url, fields, columns) {
	this.name = name;
	this.url = url;
	this.fields = fields;
	this.columns = columns;
	this.filters = [];
	this.width = 800;
	this.height = 600;
	this.editor_url_template = null;
	this.sort_info = {};
	this.label_field = 'label';
	this.rendered = false;
}
// GridManagerEntry instance methods
GridManagerEntry.prototype = {
	render: function() {
		if (this.rendered)
		{
			return;
		}
		var ds = new Ext.data.JsonStore({
			url:this.url,
			id: 'id',
			totalProperty: 'total',
			root: 'data',
			fields: this.fields,
			sortInfo: this.sort_info,
			remoteSort: true
	    });
		
		//var filters = this.filters ? new Ext.grid.GridFilters({filters: this.filters}) : null;
		var filters = null;
		
		var grid = new Ext.grid.GridPanel({
			store: ds,
		    columns: this.columns,
		    viewConfig: {
		        forceFit: true
		    },
		    sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
		    width:this.width,
		    height:this.height,
		    frame:true,
		    iconCls:'icon-grid',
			renderTo: this.name,
			plugins: filters,
			bbar: new Ext.PagingToolbar({
				store: ds,
				pageSize: 50,
				plugins: filters
			})
		});
		
		ds.load({params:{start: 0, limit: 50}});
		
		// upon a click on a row, open item-editor
		if (this.editor_url_template)
		{
			var tpl = this.editor_url_template;
			var that = this;
			grid.on('rowclick', function(grid, row_index){
				var r = grid.getStore().getAt(row_index);// Ext.data.Record
				GridManager.editors[that.name] = new Ext.Window({
					title: r.get(that.label_field),
					width: 800,// @todo make configurable
					height: 600,// @todo make configurable
					applyTo: document.body.appendChild(document.createElement('div')),
					modal:true
				});
				GridManager.editors[that.name].load({
					url: that.editor_url_template.replace(/\{id\}/, r.get('id'))
				});
				GridManager.editors[that.name].show();
			});
		}
		
		this.rendered = true;
	},
	// f: {type: DATATYPE,  dataIndex: DATA_INDEX}
	addFilter: function(f) {
		this.filters.push(f);
		return this;
	},
	setDimensions: function(width, height)
	{
		this.width = width;
		this.height = height;
		return this;
	},
	// the record editor will be loaded from this URL, with "{id}" replaced by record.id
	setEditorUrlTemplate: function(template) {
		this.editor_url_template = template;
		return this;
	},
	// sort_info: {field: FIELD_NAME, direction: DESC/ASC}
	setSortInfo: function(sort_info) {
		this.sort_info = sort_info;
		return this;
	},
	// when editor opens, it should have a title. the label_field specifies which record field to use as title
	setLabelField: function(field) {
		this.label_field = field;
		return this;
	}
}