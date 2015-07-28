
admin = {};

VERSION = "1.0";

/*Режим(частичный) совместимости Firebug debug*/
if(typeof console == 'undefined') {
    console = {};
//    console.debug = function(var1) {alert(var1);}
}

/*Алиас для console.debug => d*/
if(typeof INLINE_ADMIN == 'undefined')
    INLINE_ADMIN = 0;

if(typeof console != 'undefined') {
    d = console.debug;
}

/*Gif прозрачный, адрес*/
Ext.BLANK_IMAGE_URL = '/engine/js/back/ext-2.2/resources/images/default/s.gif';

/*??нициализация jScout настроек*/
jScout.libRoot = ['engine/js/back/js'];

/*Функции используемые только в режиме отладки*/
hrefGet = function(key) {
    var regexS = "[\\?&]"+key+"=([^&#]*)";
    var regex = new RegExp( regexS );
    var tmpURL = window.location.href;
    var results = regex.exec( tmpURL );
    if( results == null ) {
        return "";
    } else {
        return results[1];
    }
}

admin.reload = function() {
    document.location.reload();
}

beforerequestTrigger  = function(fu) {
    return function(conn, options) {
        if(window.NEO && window.ServerOptions) {
        	if(options.url.match(/\.js/)) {
                options.url = ServerOptions.creakerHost + options.url;
            }
        }
        if(fu!='') {
            if(!options.params) options.params = {};
            if(typeof options.params == 'string') {
                options.url += (/\?/.test(options.url)?'&':'?')+"fu="+fu;
            } else {
                options.params.fu = fu;
            }
        }
    }
}

if(DEBUG) Ext.Ajax.on('beforerequest', beforerequestTrigger(hrefGet('fu')), this);
/*END*/

Ext.QuickTips.init();

//if(!DEBUG) DEBUG = 0;

classExt = new function() {
    this.el = {};
    this.add = function(name, a) {
        if(typeof this.el[name] == 'undefined') this.el[name] = [];
        this.el[name].push(a);
    }
    this.result = function(name) {
        var full_class = {};
        Ext.each(this.el[name], function(part_class) {
            var part_class_instance = new part_class();
            for(var key in part_class_instance) {
                full_class[key] = part_class_instance[key];
            }
        });
        this.el[name] = [];
        return full_class;
    }
};

/*Активация вкладки*/
tabTrigger = function(title, idi, items, settings) {
    if(!settings) settings = {};
    if(Ext.getCmp(idi)) {
        Ext.getCmp("tabs").activate(idi);
        return 1;
    }
    var sett = {
        title: title,
        id:idi,
        closable:true,
        region:'center',
        layout: 'card'
       // ,bbar: this.getBbar()    
    };
    if(items) sett['items'] = items;
    if(settings) sett = Ext.apply(sett, settings);
    
    Ext.getCmp("tabs").add(sett).show();
    if(Ext.getCmp(idi+"-panel")) {
        Ext.getCmp(idi).layout.setActiveItem(idi+"-panel");
        Ext.getCmp(idi+"-panel").doLayout();
    }
}
tabClose = function(idi) {
    Ext.getCmp("tabs").remove(idi);  
}


/*Создание объектов основного интерфейса*/
var rootMenu = new Ext.Toolbar({
    id: 'mainMenu'
    ,height: 26
    ,items:[
        'Фри-ланс.ру - Бэкэнд'
        ,{xtype: 'tbfill'}
        
        //,'Вы авторизованы:'+login
]});


jScout.useSync("plugins/collection");

var appCenter = new Ext.TabPanel({
    region: 'center',
    id: 'tabs',
    layoutOnTabChange: true,
    activeTab: 0,
    enableTabScroll: true,
    items: [{
        id: '0',
        title: 'Добро пожаловать',
        autoScroll: true
    }]
    ,plugins: new Ext.ux.TabCloseMenu()
});

var bottomFooter = new Ext.Toolbar({
    id: 'bottomFooter',
    height: 26,
    items: [{
        text: 'Free-lance.ru &copy 2009'
    }]
});

var menuPanel = new Ext.Panel({
    baseCls: 'x-plain',
    bodyBorder: false,
    items:[
  /*  {
        title: 'Модули',
        
        bodyBorder: false,
        frame:true,
        style:'padding:5px;',
        titleCollapse: true
        ,items:[
            {
                baseCls: 'x-plain',
                bodyStyle:"padding:5px;line-height:17px;",
                html:'\
                    <a href="javascript:void(0)" onclick="testsList.open()">Тестирование</a><br/>\
                '
            }
        ]
    }
    ,
    */
    {
        title: 'Пресс-центр',
        
        bodyBorder: false,
        frame:true,
        style:'padding:5px;',
        titleCollapse: true
        ,items:[
            {
                baseCls: 'x-plain',
                bodyStyle:"padding:5px;line-height:17px;",
                html:'\
                    <a href="javascript:void(0)" onclick="newsList.open()">Новости</a><br/>\
                    <a href="javascript:void(0)" onclick="staticPagesList.open()">Статика</a><br/>\
                    <a href="javascript:void(0)" onclick="teamList.open()">Команда</a><br/>\
                    <a href="javascript:void(0)" onclick="cblogList.open()">Корп.блог</a><br/>\
                    <a href="javascript:void(0)" onclick="smiList.open()">СМ?? о Фри-лансе</a><br/>\
                    <a href="javascript:void(0)" onclick="faqList.open()">Помощь</a><br/>\
                '
            }
        ]
    }
    ]
}
);

var items = [
    appCenter,
    {
        region:"west",
        split: false,
        baseCls: 'x-plain',
        bodyBorder: false,
        width:120,
        items:menuPanel
    },
    {
        xtype: 'panel',
        region: 'north',
        border: false,
        layout:'anchor',
        height:26,
        bodyBorder: false,
        split: false,
        baseCls: 'x-plain',
        items:[
            {height:26,items:rootMenu}
        ]    
    },{
        region: 'south',
        id: 'AppSouth',
        border: true,
        height: 26,
        items: [
            bottomFooter
        ]
    }
];

/*END*/

Ext.namespace('newsList'
    ,"editWindow"
    ,"staticPagesList"
    ,"testsList"
    ,"teamList"
    ,"cblogList"
    ,"smiList"
    ,"faqList"    
    /*, '..'*/);

staticPagesList.open = function() {
    jScout.useSync("staticPagesListLib");
    staticPagesList.init();
}
faqList.open = function() {
    jScout.useSync("faqListLib");
    faqList.init();
}
smiList.open = function() {
    jScout.useSync("smiListLib");
    smiList.init();
}

newsList.open = function() {
    jScout.useSync("newsListLib");
    newsList.init();
}
cblogList.open = function() {
    jScout.useSync("cblogListLib");
    cblogList.init();
}
testsList.open = function() {
    jScout.useSync("testsListLib");
    testsList.init();
}

editWindow.open = function() {
    jScout.useSync("editWindowLib");
    editWindow.init();
}

teamList.open = function() {
    jScout.useSync("teamListLib");
    teamList.init();    
}



Ext.onReady(function(){
    if(!DEBUG) Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
    
/*    Основной контейнер интерфейса*/
    if(!INLINE_ADMIN)
    var viewport = new Ext.Viewport({
        layout: 'border',
        items: items
    });
    
    if(DEBUG) {
      //  testsList.open();
        //jScout.useSync("actionsEditLib"); (new actionsEdit({id:946})).open();
    }
    
    admin.global_wait = {
        hide : function() {
            if(Ext.MessageBox.isVisible())Ext.MessageBox.hide();
        },
        show : function(msg) {
			Ext.MessageBox.show({
               msg: msg?msg:false,
//               progressText:"As",
               width:300,
               wait:true,
               waitConfig: {interval:200}
           });

        }
    };
    
    admin.openPopup = function(name, id, configArr) {
        var id_str = '"'+id+'"';
        try {
			admin.global_wait.show();   
		}catch (e) {
			var s = navigator.userAgent.toLowerCase();
			if (s.indexOf("msie") != -1) {
				var is9 = 0;
				try {
					document.getElementsByClassName("noexcssclass");
					is9 = 1;
				}
				catch(e1) {
					is9 = 0;
				}
				//start temp code
				var tplLoader = '<div style="left: 482px; top: 360px; width: 300px; display: block; visibility: visible; position: absolute; z-index: 11000;" id="editWndLoader" class="x-window x-window-plain x-window-dlg"><div class="x-window-tl"><div class="x-window-tr"><div class="x-window-tc"><div id="ext-gen16" class="x-window-header x-unselectable x-window-draggable"><div style="display: none;" id="ext-gen61" class="x-tool x-tool-close">&nbsp;</div><span id="ext-gen59" class="x-window-header-text">&nbsp;</span></div></div></div></div><div id="ext-gen17" class="x-window-bwrap"><div class="x-window-ml"><div class="x-window-mr"><div class="x-window-mc"><div style="width: 268px; height: auto;" id="ext-gen18" class="x-window-body"><div id="ext-gen68"><div id="ext-gen69" class="ext-mb-icon x-hidden"></div><div class="ext-mb-content"><span id="ext-gen70" class="ext-mb-text">&nbsp;</span><br><div class="ext-mb-fix-cursor"><input style="display: none;" id="ext-gen71" class="ext-mb-input" type="text"><textarea style="display: none;" id="ext-gen73" class="ext-mb-textarea"></textarea></div></div><div style="width: 266px;" id="ext-comp-1010" class="x-progress-wrap"><div class="x-progress-inner"><div style="width: 0px; height: 16px;" id="ext-gen75" class="x-progress-bar"><div style="width: 0px; z-index: 99;" id="ext-gen76" class="x-progress-text "><div style="width: 266px; height: 18px;" id="ext-gen78">&nbsp;</div></div></div><div id="ext-gen77" class="x-progress-text x-progress-text-back"><div style="width: 266px; height: 18px;" id="ext-gen79">&nbsp;</div></div></div></div><div id="ext-gen80" class="x-clear"></div></div></div></div></div></div><div class="x-window-bl"><div class="x-window-br"><div class="x-window-bc"><div id="ext-gen19" class="x-window-footer"><div class="x-panel-btns-ct"><div class="x-panel-btns x-panel-btns-center"><table cellSpacing="0"><tbody><tr><td id="ext-gen22" class="x-panel-btn-td x-hide-offsets"><table style="width: 75px;" id="ext-comp-1006" class="x-btn-wrap x-btn" border="0" cellSpacing="0" cellPadding="0"><tbody><tr><td class="x-btn-left"><i>&nbsp;</i></td><td class="x-btn-center"><em unselectable="on"><button id="ext-gen24" class="x-btn-text" type="button">OK</button></em></td><td class="x-btn-right"><i>&nbsp;</i></td></tr></tbody></table></td><td id="ext-gen30" class="x-panel-btn-td x-hide-offsets"><table style="width: 75px;" id="ext-comp-1007" class="x-btn-wrap x-btn" border="0" cellSpacing="0" cellPadding="0"><tbody><tr><td class="x-btn-left"><i>&nbsp;</i></td><td class="x-btn-center"><em unselectable="on"><button id="ext-gen32" class="x-btn-text" type="button">Да</button></em></td><td class="x-btn-right"><i>&nbsp;</i></td></tr></tbody></table></td><td id="ext-gen38" class="x-panel-btn-td x-hide-offsets"><table style="width: 75px;" id="ext-comp-1008" class="x-btn-wrap x-btn" border="0" cellSpacing="0" cellPadding="0"><tbody><tr><td class="x-btn-left"><i>&nbsp;</i></td><td class="x-btn-center"><em unselectable="on"><button id="ext-gen40" class="x-btn-text" type="button">Нет</button></em></td><td class="x-btn-right"><i>&nbsp;</i></td></tr></tbody></table></td><td id="ext-gen46" class="x-panel-btn-td x-hide-offsets"><table style="width: 75px;" id="ext-comp-1009" class="x-btn-wrap x-btn" border="0" cellSpacing="0" cellPadding="0"><tbody><tr><td class="x-btn-left"><i>&nbsp;</i></td><td class="x-btn-center"><em unselectable="on"><button id="ext-gen48" class="x-btn-text" type="button">Отмена</button></em></td><td class="x-btn-right"><i>&nbsp;</i></td></tr></tbody></table></td></tr></tbody></table><div class="x-clear"></div></div></div></div></div></div></div></div><a id="ext-gen54" class="x-dlg-focus" tabIndex="-1" href="#">&nbsp;</a></div>';
				var div = document.createElement("div");
				div.innerHTML = tplLoader;
				document.body.appendChild(div);
				function rm (id) { var o = document.getElementById(id); var p = o.parentNode; p.removeChild(o); }
				new Request.HTML({
					url: '/adminback/static_pages/getinfo/', 
					onSuccess: function(html) { // запрос выполнен уcпешно
						rm("editWndLoader");
						var ls = document.getElementsByClassName("x-window-dlg");
						if (ls.length > 0) rm(ls[0].id);
						var ls = document.getElementsByClassName("ext-el-mask");
						if (ls.length == 0) {
							var tpl = '<div class="ext-el-mask" id="bgshadow" style="display: block; width: ' + document.body.offsetWidth + 'px; height: ' + document.body.offsetHeight + 'px; z-index: 2500;"></div>';
							var div = document.createElement("div");
							div.innerHTML = tpl;
							document.body.appendChild(div);
						}
						var div = document.createElement("div");
						var tpl = '<div class="x-window x-window-plain" id="editWindow" style="position: absolute; z-index: 9013; visibility: visible; left: 101px; top: 28px; width: 85%; display: block;"><input type="hidden" id="actionId" value="' + id + '" /><div class="x-window-tl"><div class="x-window-tr"><div class="x-window-tc"><div class="x-window-header x-unselectable x-window-draggable" id="ext-gen200" style="-moz-user-select: none;"><div class="x-tool x-tool-close" id="ext-gen221" onclick="closeWnd()">&nbsp;</div><div class="x-tool x-tool-restore" id="ext-gen216" style="display: none;">&nbsp;</div><span class="x-window-header-text" id="ext-gen210">Редактирование страницы [После нажатия кнокпи "ОК" страница будет перезагружена]</span></div></div></div></div><div class="x-window-bwrap" id="ext-gen201"><div class="x-window-ml"><div class="x-window-mr"><div class="x-window-mc"><div class="x-window-body" id="ext-gen202" style="height: auto;"><div class="x-panel x-panel-noborder" id="editWindowext-gen198-panel"><div class="x-panel-bwrap" id="ext-gen228"><div class="x-panel-body x-panel-body-noheader x-panel-body-noborder" id="ext-gen229" style="overflow: auto;"><div class="x-panel " id="editWindowext-gen198-subpanel"><div class="x-panel-bwrap" id="ext-gen230"><div class="x-panel-body x-panel-body-noheader" id="ext-gen231"><div class="x-plain x-plain-noborder" id="ext-comp-1022" style="width: 1059px;"><div class="x-plain-bwrap" id="ext-gen232"><form id="ext-gen199" method="POST" class="x-plain-body x-plain-body-noheader x-plain-body-noborder x-form" style="height: auto; width: 1059px;"><div class="x-panel" id="ext-comp-1023" style="width: 1059px;"><div class="x-panel-bwrap" id="ext-gen251"><div class="x-panel-body x-panel-body-noheader x-column-layout-ct" id="ext-gen252" style="height: auto; width: 1057px;"><div class="x-column-inner" id="ext-gen253" style="width: 1057px;"><div class="x-panel x-column" id="ext-comp-1024" style="width: 1057px;"><div class="x-panel-bwrap" id="ext-gen255"><div class="x-panel-body x-panel-body-noheader" id="ext-gen256" style="width: 1055px;"><div class="x-panel" id="ext-comp-1025"><div class="x-panel-bwrap" id="ext-gen257"><div class="x-panel-body x-panel-body-noheader x-column-layout-ct" id="ext-gen258"><div class="x-column-inner" id="ext-gen261" style="width: 1053px;"><div class="x-panel x-form-label-top x-column" id="ext-comp-1026" style="width: 1053px;"><div class="x-panel-bwrap" id="ext-gen263"><div class="x-panel-body x-panel-body-noheader" id="ext-gen264" style="width: 1051px;"><div tabindex="-1" class="x-form-item "><label class="x-form-item-label" style="width:auto;" for="titleedit">Заголовок(где-то может не использоваться):</label><div style="padding-left:0;" id="x-form-el-ext-comp-1027" class="x-form-element"><input type="text" name="title" id="titleedit" autocomplete="off" size="20" class=" x-form-text x-form-field" style="width: 1045px;"></div><div class="x-form-clear-left"></div></div></div></div></div><div class="x-clear" id="ext-gen262"></div></div></div></div></div><div class="x-panel" id="ext-comp-1028"><div class="x-panel-bwrap" id="ext-gen259"><div class="x-panel-body x-panel-body-noheader x-column-layout-ct" id="ext-gen260"><div class="x-column-inner" id="ext-gen269" style="width: 1053px;"><div class="x-panel x-form-label-top x-column" id="ext-comp-1029" style="width: 1053px;"><div class="x-panel-bwrap" id="ext-gen271"><div class="x-panel-body x-panel-body-noheader" id="ext-gen272" style="width: 1051px;"><div tabindex="-1" class="x-form-item "><label class="x-form-item-label" style="width:auto;" for="popuptextedit">Текст:</label><div style="padding-left:0;" id="x-form-el-ext-comp-1030" class="x-form-element"><textarea name="n_text" id="popuptextedit" autocomplete="off" style="width: 792px; height: 294px;" class=" x-form-textarea x-form-field"></textarea></div><div class="x-form-clear-left"></div></div></div></div></div><div class="x-clear" id="ext-gen270"></div></div></div></div></div></div></div></div><div class="x-clear" id="ext-gen254"></div></div></div></div></div></form><div class="x-plain-footer x-plain-footer-noborder" id="ext-gen233"><div class="x-panel-btns-ct"><div class="x-panel-btns x-panel-btns-center"><table cellspacing="0"><tbody><tr><td class="x-panel-btn-td" id="ext-gen234"><table cellspacing="0" cellpadding="0" border="0" class="x-btn-wrap x-btn" id="ext-comp-1031" style="width: 75px;"><tbody><tr><td class="x-btn-left"><i>&nbsp;</i></td><td class="x-btn-center"><em unselectable="on"><button type="button" class="x-btn-text" id="ext-gen236" onclick="wndSendData()">&nbsp;&nbsp;OК&nbsp;&nbsp;</button></em></td><td class="x-btn-right"><i>&nbsp;</i></td></tr></tbody></table></td><td class="x-panel-btn-td" id="ext-gen242"><table cellspacing="0" cellpadding="0" border="0" class="x-btn-wrap x-btn" id="ext-comp-1032" style="width: 75px;"><tbody><tr><td class="x-btn-left"><i>&nbsp;</i></td><td class="x-btn-center"><em unselectable="on"><button type="button" class="x-btn-text" id="ext-gen244" onclick="closeWnd()">Отмена</button></em></td><td class="x-btn-right"><i>&nbsp;</i></td></tr></tbody></table></td></tr></tbody></table><div class="x-clear"></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div><div class="x-window-bl x-panel-nofooter"><div class="x-window-br"><div class="x-window-bc"></div></div></div></div><a tabindex="-1" class="x-dlg-focus" href="#" id="ext-gen205">&nbsp;</a></div>';
						div.innerHTML = tpl;
						document.body.appendChild(div);
						var top = document.getElementById("ext-gen200");
						top.onmousedown=startDragWnd;
						top.onmouseup=stopDragWnd;
						top.onmousemove=onDragWnd;
						var d = html[0];
						
						//var data = eval("(" + d.data + ")");
						//for (var j in d ) alert (j +  " = "+ d[j]);
						var data = Ext.util.JSON.decode(d.data);
						//alert(data.form.title);
						function setv(id, v) {
							var i   = document.getElementById(id);
							i.value = v;
						};
						setv("titleedit",     data.form.title);
						setv("popuptextedit", data.form.n_text);
					},
					onFailure: function() { // ошибка запроса
						alert("Error ajax request");
					}
				}).post("id=" + id + "&u_token_key=" + _TOKEN_KEY);
				return;/*-*/
				//end temp code
			}	
		}
        if(id === 0) id_str = 0;
        eval('jScout.useSync("'+name+'EditLib"); (new '+name+'Edit({id:'+id_str+', configCommand:configArr})).openPopup();');
    }
    
    admin.loadAndExec = function(name, command, params, configArr) {
        admin.global_wait.show();
        eval('jScout.useSync("'+name+'Lib"); ('+command+'.apply(this, params));');
    }
    
    admin.loadByCarret = function() {
        var regex = new RegExp( "[#]([^#]*)" );
        var tmpURL = window.location.href;
        var results = regex.exec( tmpURL );
        if( results == null ) {
            return "";
        }
        var splited = results[1].split(":");
        
        switch(splited[0]) {
            case "js":
                eval(splited[1]);
            break;
            case "open":
                eval('jScout.useSync("'+splited[1]+'EditLib"); (new '+splited[1]+'Edit({id:'+splited[2]+'})).open();');
            break;
            case "list":
                eval(' '+splited[1]+'List.open();  ');
            break;
        }
    }();
});

function closeWnd() {
	var div = document.getElementById("editWindow");
	var p = div.parentNode;
	p.removeChild(div);
	try {
		var ls = document.getElementsByClassName("ext-el-mask");
		while (ls.length > 0) {
			var id = ls[0].id;
			var div = document.getElementById(id);
			var p = div.parentNode;
			p.removeChild(div);	
		}
	}catch(e) {}
	doc.idformdragobject = "undefined";
}

function wndSendData() {
	function getv(id) {
		var o = document.getElementById(id);
		return o.value;
	}
	var data= "id=" + getv("actionId") + "&form[title]=" + getv("titleedit") + "&form[n_text]=" + getv("popuptextedit") + "&u_token_key=" + _TOKEN_KEY;
	new Request.HTML({
					url: '/adminback/static_pages/save/', 
					onSuccess: function(html) { // запрос выполнен уcпешно
						var d = html[0];
						var data = eval("(" + d.data + ")");
						if (data.success == true) {
							closeWnd();
							var adr = window.location.href;
							window.location.href = adr;
						}else {
							alert("Произошла непонятная ошибка. Попробуйте еще раз");
						}
						return;
					},
					onFailure: function() { // ошибка запроса
						alert("Произошла непонятная ошибка. Попробуйте еще раз");
					}
				}).post(data);/*-*/
}


var doc = document;
function onDragWnd(e) {
	if (String(doc.idformdragobject) != "undefined")
	{
		var o = mousePageXY(e);
		var oldX = doc.idformdragobject_x;
		var oldY = doc.idformdragobject_y;
		var dx  = (oldX - o.x);
		var dy  = (oldY - o.y);
		var sid = String(doc.idformdragobject);
		var id = sid.replace("_dragarea", "");
		var div = doc.getElementById(id);
		var y = parseInt(div.style.top);
		var x = parseInt(div.style.left);
		div.style.top  = y - dy + "px";
		div.style.left = x - dx + "px";
		doc.idformdragobject_x = o.x
		doc.idformdragobject_y = o.y
	}
}

function stopDragWnd(e) {
	doc.idformdragobject = "undefined";
}

function startDragWnd(e) {
	doc.idformdragobject = "editWindow";
	var o = mousePageXY(e);
	doc.idformdragobject_x = o.x;
	doc.idformdragobject_y = o.y;
}


function mousePageXY(e)
{
  var x = 0, y = 0;

  if (!e) e = window.event;

  if (e.pageX || e.pageY)
  {
    x = e.pageX;
    y = e.pageY;
  }
  else if (e.clientX || e.clientY)
  {
    x = e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft) - document.documentElement.clientLeft;
    y = e.clientY + (document.documentElement.scrollTop || document.body.scrollTop) - document.documentElement.clientTop;
  }

  return {"x":x, "y":y};
}
