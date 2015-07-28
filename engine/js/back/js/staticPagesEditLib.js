
jScout.useSync("editWindowLib");
          

staticPagesEdit = Ext.extend(editWindow, {
    getTitle : function(){
		var ls = document.getElementsByTagName("div");
		for (var i = 0; i < ls.length; i++) {
			var cname = ls[i].getAttribute("class");
			if (cname == "rcol-big") {
				for (var j = 1; j < 5; j++) {
					var h = ls[i].getElementsByTagName("h" + j);
					if (h.length > 0) {
						if (h[0].innerHTML.length > 0) return "Редактирование страницы: \"" + h[0].innerHTML +  '"';
					}
				}
				break;
			}
		}/**/
        return "Редактирование страницы: " + this.id;
    }
    ,load: function() {		
        Ext.Ajax.request({
            url: '/adminback/static_pages/getinfo/',
            params:{id:this.id, u_token_key:_TOKEN_KEY},
            success:function(responseObj){
                var data = Ext.util.JSON.decode(responseObj.responseText);
                this.setValues(data);
            }
            ,scope:this
        });
    }
    ,save: function(close_after_save, reload) {
        
        Ext.Ajax.request({
            url: '/adminback/static_pages/save/',
            params:{id:this.id, form: this.form.getAllValues(), u_token_key:_TOKEN_KEY},
            success:function(responseObj){
                var data = Ext.util.JSON.decode(responseObj.responseText);
                
                Ext.getCmp(this.idi+'-subpanel').getEl().unmask();
                if(close_after_save) this.close();
                if(reload) admin.reload();
            }
            ,scope:this
        });
    }
     ,afterOpen : function() {

    }
    ,getItems : function(){
        var leftTopColumn =
        [
            {
                layout:'column',
                items: [
                    {
                        columnWidth: 1,
                        layout:'form',
                        items:
                            {
                                xtype:'textfield',
                                fieldLabel: 'Заголовок(где-то может не использоваться)',
                                name:"title",
                                anchor:'100%',
                                allowBlank: true,
                                 msgTarget :'under'
                                ,blankText: 'Заголовок - обязательное поле.'
                            }
                    }
                ]
            }
            ,{
                layout:'column',
                items: [
                    {
                        columnWidth: 1,
                        layout:'form',
                        items:
                            {
                                xtype:"textarea"
                                ,fieldLabel: 'Текст'
                                ,name:"n_text"
                               /// ,id:"df"
                                ,width:800
                                ,height:300
                              //  ,style:'padding:8px'
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
                        columnWidth: 1,
                        items:leftTopColumn
                    }
                ]
            }
        ];
        return items;
    }
});
