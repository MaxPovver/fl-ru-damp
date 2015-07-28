/**
 * Расширение функционала новой шапки
 * @todo: использую Mootools
 */
function Bar_Ext()
{
    Bar_Ext=this; // ie ругался без этого, пока не понял.

    
    //--------------------------------------------------------------------------
    
    //Начальная инициализация
    this.init = function() 
    {
        this.toggler({
            'antiuser':{
                //При клике на ссылку переключения
                'click':function(el){
                    var dd = el.getParent('[data-dropdown-descriptor]');
                    var is_open = dd.hasClass('b-opened-dropdown');
                    if(typeof toggleDropdown !== "undefined" && dd && is_open)
                    {
                       toggleDropdown(dd.get('data-dropdown-identificator'));     
                    }
                },
                //Что сделать с элементами включающимися
                'on':function(el){
                    el.show();
                    
                    var winSize = $(window).getSize();
                    if(winSize.x <= 800)
                    {
                        var is_auth = (el.get('data-dropdown-descriptor') == 'identification');
                        if(typeof toggleDropdown !== "undefined" && is_auth)
                        {
                            toggleDropdown(el.get('data-dropdown-identificator')); 
                        }  
                    }
                },
                //Что делать с элементами выключающимися
                'off':function(el){
                    el.hide();
                }
            }
        });
        
        
        this.popuper();
        this.showOrHide();
        this.scroller();
        
        this.onLoginDataSaver();
    };
    

    //--------------------------------------------------------------------------
    
    /**
     * Переключатель свойств элементов
     * 
     * @param object params
     * @returns boolean
     */
    this.toggler = function(params)
    {
        var togglers = $$('[data-toggle-action]');
        if(!togglers) return false;
        
        togglers.addEvent('click',function(){
            var id = this.get('data-toggle-action');
            if(typeof params[id] === "undefined") return false;
            params[id].click(this);
            
            var toggles = $$('[data-' + id + ']');
            if(toggles) toggles.each(function(e){
                var tg = e.get('data-' + id);
                if(tg == 'true') params[id].on(e);
                else params[id].off(e);
                e.set('data-' + id, (tg == 'true')?false:true); 
            });
            return false;
        });
        
        return true;
    };
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Авторизация на противоположный привязанный аккаунт
     * 
     * @param obj form
     * @param string anti_login
     * @returns Boolean
     */
    this.antiuserSubmit = function(form, anti_login)
    {
        if(!form) return true;
        var qu = form.toQueryString();
        var _action = 'switch';
        var login = form.getElement('input[name=a_login]').get('value');
        if(login != anti_login) _action = 'change_au';
        
        form.getElements('.b-text-field input').addEvent('focus',function(){
            this.getParent().removeClass('b-text-field-error');
        });
        
        new Request.JSON({
	url: form.get('action'),
	data: qu + "&action=" + _action,
	onSuccess: function(resp){
            if(resp) 
            {
                if($chk(resp.redir)) document.location.href = resp.redir;
                else if(resp.success) document.location.reload();
                else resp=null;
            }
            
            if(!resp)
            {
                form.getElements('.b-text-field')
                    .addClass('b-text-field-error');    
            }
            
        }}).post();
    
        return false;
    };
    
    
    //--------------------------------------------------------------------------

    
    /**
     * Разлогиниться
     */
    this.logout = function()
    {
        var form = new Element('form', {'action':'/logout/','method':'post'});
        var action = new Element('input', {'type':'hidden', 'value':'logout','name':'action'});
        var token = new Element('input', {'type':'hidden','value':_TOKEN_KEY,'name':'u_token_key'});  
        
        form.adopt(action,token);
        form.setStyle('display','none').inject($(document.body), 'bottom');
        form.submit();
    };
    
    
    
    //--------------------------------------------------------------------------
    
    
    /**
     * Открываем попапы и обрабатываем события внутри.
     * Для этого на странице должнем быть скрытый попап и на ссылке которая его должна
     * открывать указываем data-popu="popup_name" - где popup_name это id="popup_name"
     * окна попапа. На кнопке в попапе по которой нужно закрыть и произвести какие-либо действия
     * указываем data-popup-ok="true" а на onclick или href="javascript: ..." нужную операцию.
     * 
     * Если нужно скопировать атрибут из ссылки по которой открывается попап то на кнопке в попапе
     * указывается свойство data-popup-copy-attr="href" (href - например)
     * 
     * Если после загрузки попап не найден то на ссылку вешается событие
     * отправки POST запроса с параметром по имени попапа из data-popup
     * и со значением равным 1 на урл в data-url ссылки. Это может быть
     * использовано для открытия попапа сразу после редиректа туда где он есть.
     * 
     * 
     * @returns Boolean
     */
    this.popuper = function()
    {
        var popups = $$('[data-popup]');
        if(!popups) return false;  

        popups.each(function(link){
            
            Bar_Ext.bindPopup(link);
        });

        return true;
    };
    
    /**
     * Привязывает попап к ссылке
     * 
     * @param {type} link
     * @returns Boolean
     */
    this.bindPopup = function(link) {
        var id = link.get('data-popup');
        var popup = $(id);
        
        link.removeEvents();
        
        if(popup)
        {
            link.addEvent('click', function(event){
                popup.removeClass('b-shadow_hide').fireEvent('showpopup', this);
                var okBtns = popup.getElements('[data-popup-ok]');
                if(!okBtns) return false;
                okBtns.each(function(btn){
                    var attr = btn.get('data-popup-copy-attr');
                    if(!attr) return;
                    btn.set(attr,link.get(attr));
                });
                okBtns.addEvent('click',function(){popup.addClass('b-shadow_hide');});
                return false;
            });

            var cross = popup.getElement('.b-shadow__icon_close');
            if(cross) cross.addEvent('click', function(){popup.addClass('b-shadow_hide');});

            if(!popup.hasClass('b-shadow_hide')) link.fireEvent('click');
            popup.store('called_link',link);
        }
        else
        {
            link.addEvent('click',function(){
                var url = this.get('data-url');
                if(!url) return false;
                Bar_Ext.sendHideForm(url,id);
                return false;
            });        
        }
    };
    
    
    
    
    /**
     * По клику на ссылке прокручиваем страницу к элементу, заданному в атрибуте
     * data-scrollto. При прокрутке учитывается высота шапки и отступы. Если цель
     * не найдена на странице, выполняем переход по адресу, указанному в параметре
     * data-url
     * @todo Функция похожа на popuper, и может использоваться в разных местах сайта,
     * но почему же они в классе скриптов шапки? - согласен, теперь когда здесь собралось
     * функций разных можно файл обозвать по другому (commons или helpers ..) и выделить в class mootools
     */
    this.scroller = function() {
        var scrolls = $$('[data-scrollto]');
        if(!scrolls) return false;  

        scrolls.each(function(link){
            var id = link.get('data-scrollto');
            var target = $(id);
            
            if(target)
            {
                link.addEvent('click', function(){
                    var myFx = new Fx.Scroll(window, {
                        duration: 300,
                        wait: false,
                        offset: {
                            x: 0,
                            y: -80
                        }
                    }).toElement('form-block');
                    
                });
            
            }
            else
            {
                link.addEvent('click',function(){
                    var url = this.get('data-url');
                    if(!url) return;
                    Bar_Ext.sendHideForm(url,id);
                    return false;
                });        
            }
        });

        return true;
    };
    
    /**
     * Отправляем скрытую форму POST запросом 
     * на указанный url c name названием параметра.
     * 
     * @param {type} url
     * @param {type} name
     * @returns {undefined}
     */
    this.sendHideForm = function(url, name)
    {
        var form = new Element('form', {'action':url,'method':'post'});
        var idx = new Element('input', {'type':'hidden','value':1,'name':name});
        var token = new Element('input', {'type':'hidden','value':_TOKEN_KEY,'name':'u_token_key'});
        
        form.adopt(idx,token);
        form.setStyle('display','none').inject($(document.body), 'bottom');
        form.submit();
    };
    
    
    //--------------------------------------------------------------------------
    
    
    
    /**
     * Хелпер для включения/выключения классов по клику на каком-либо элементе
     * 
     * @returns {Boolean}
     */
    this.showOrHide = function()
    {
        var showAct = $$('[data-show-class]');
        var hideAct = $$('[data-hide-class]');
        if(!showAct && !hideAct) return false;
        
        showAct.addEvent('click', function(){
            var cls = this.get('data-show-class');
            var display = this.get('data-show-display');
            if (!display) display = 'block';
            if(cls) $$(cls).show(display);
        });
        
        hideAct.addEvent('click', function(){
            var cls = this.get('data-hide-class');
            if(cls) $$(cls).hide();
        });
        
        return true;
    };
    
    
    this.onLoginDataSaver = function()
    {
        var login_form = $('lfrm');
        if (login_form) {
            login_form.addEvent('submit', function(){
                var guestForms = $$('.form_guest');
                if (guestForms.length) {
                    var formQuerySting = guestForms[0].toQueryString();

                    new Element('input', {
                        'type': 'hidden',
                        'name': 'guest_query',
                        'value': formQuerySting
                    }).inject(this);
                }
            });
        }
    };
    
    
    //--------------------------------------------------------------------------
    
    
    
    //Запуск инициализации
    this.init();    
}

window.addEvent('domready', function() {
    new Bar_Ext();
});