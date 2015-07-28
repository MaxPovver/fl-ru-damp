var CHAT_DELAY = .5;
var QuickChat = function (){
    var ChatManager = {                    //хранит информацию об количестве диалоговых окон на странице
            minZ: 50,                      //минимальный z-index
            chatDialogs  : new Array(),    //массив со ссылками на "окна" чата, ключ - идентификатор пользователя или 0 для окна списка контактов
            activeWindow : null,           //активное окно чата
            drag : 0,                      //признак того, что надо перетаскивать активное окно
            maxZ : 50,                     //максимальный z-index
            topLimit : 32,                 //верхняя граница окна
            padding  : 20,                 //отступ от границ окна браузера
            maxDialogHeight  : 250,        //предел, до которого растягивается окно диалога с собеседником по вертикали, если его не растянуть вручную
            minScrollHandleH : 50,         //предел, до которого сжимается "рукоятка" скроллинга в окне
            ajaxRequestUrl   : '/chat.php',         //скрипт, к которому совершаются ajax запросы
            avatar           : '',         //аватар пользователя
            name             : '',         //имя и фамилия пользователя
            login            : '',         //логин пользователя
            refreshContactsInterval : 60,  //интервал запроса списка контактов с сервера (sec)
            settings         : {},         //настройки окна
            indicateCount         : 4,     //сколько раз "мигает" количество контактов
            blueBorder            : 4,     //используется при "мигании" рамки открытого окна чата
            narrowDx              : 32,    //приращение при анимации сужения окна
            wideStateLimit        : 5,     //время, которое окно остается широким при соответствующей настройке (sec)
            disconnect            : 0,     //признак, что больше не надо поддерживать постоянное соединение с сервером
            addDialog        : function (uid, div) {     //добавление "окна"
                if (!div) {
                    return;
                }
                ChatManager.chatDialogs[uid] = div;
                var uid = int(div.get("uid"));
                if (uid > 0) {
                    var s = String(cookie_read("q4ls" + _UID));
                    var arr = s.split('.');
                    var f = 1;
                    var uid2s58 = dec2s58(uid);
                    for (var i = 0; i < arr.length; i++) {
                        if (arr[i] == uid2s58) {
                            f = 0;
                            break;
                        }
                    }
                    if (f) {
                        arr.push(uid2s58);
                        cookie_write("q4ls" + _UID, arr.join("."));
                    }
                }
            },
            playSound : new Array(),      //массив для хранения списка "окон" в которых надо озвучить входящее сообщение при загрузке истории
            defaultAvatar : "/images/temp/small-pic.gif",
            sendedMsg : new Array(),    //массив для хранения неудачно отправлено сообщений
            incomeCache : new Array(),   //массив для хранения сообщений, пришедших до загрузки истории
            historyIsLoaded : new Array(), //массив для хранения флагов, что история по данному пользователю загружена
            historyMaxId    : new Array(),  //массив для хранения максимальных идентификаторов для запроса истории (например было входящее сообщение до того как чат запросил историю)
            queue    : new Array(),  //массив для хранения отправленных, но не подтвержденных сервером сообщений
    }

    var chatOn = _QUICK_CHAT_ON;           //переменная устанавливается php скриптом (в шаблоне), 1 если быстрочат у пользователя включен
    var contactsList = null;              //этой переменной присваивается объект окна списка контактов
    /*алфавит для компрессии данных, которые пишем в куки*/
    
    //Пременные, сязанные с алгоритмами сжатия кук
    var alphabet = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ. ~!@#$,%^&*()_+=-?[]{}()0123456789\n\\|/№;:<>\"'`абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ«»";
    /*алфавит 58-ричной системы счисления*/
    var sys58 = "0123456789abcdefghijklmnopqrstuvwxyzABCDEGHIJKLMNOPQRTVW_Z";
    var s58L  = sys58.length;
    var qch_compress = "1";  //префикс который дает понять, что строка которую считали из кук сжата
    
    var ping = 0;
    var cid = '';  // (client id) - это уникальный ключ для каждой запущенной копии быстрочата,  нужен чтобы отличать запущенные в разных браузерах/вкладках клиенты. Синтаксис - [a-zA-Z0-9]{8}
    var ckey = 0;  // (client key) - это ключ, чтобы определять разные копии чата запущенных в одном браузере. Со стороны клиента передается Math.floor(screen.width + screen.height + screen.colorDepth) и на сервере к нему "дописываются" данные HTTP_USER_AGENT и ip. Таким образом запросы приходящие с одинаковым HTTP_USER_AGENT, одинаковым ip и одинаковым разрешением экрана считаются запросами от одного браузера, но с запущенным быстрочатом в разных вкладках.

    
    QuickChat.prototype.defaultAvatar = function() {
        return ChatManager.defaultAvatar;
    }
    
    QuickChat.prototype.setChatOn = function() {
        if (!ChatManager.chatOnRequest || chatOn != 1) {
            chatOn = 1;
            ChatManager.chatOnRequest = 1;
            request("settings", {chat:1}, function () {
                initalize();
                if ($('qchat_swicth')) {
                    $('qchat_swicth').set("text", "Отключить");
                    $('qchat_swicth').set("onclick", "quickchat_off(); return false;");
                }
                if ($('qchat_link_wrapper')) {
                    $('qchat_link_wrapper').set("text", "включен");
                }
                ChatManager.chatOnRequest = 0;
            }, null, this.self);
        }
    }
        
    var currentVScrollHandle = null;   //текущая нажатая "рукоятка" вертикальног скрола (для обработки в window.onmouseup)
    function initCKeys() {
        var mask = [
                    'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R',
                    'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j',
                    'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '1', '2',
                    '3', '4', '5', '6', '7', '8', '9', '0'
                ];
                for ( var i=0; i<8; i++ ) {
                    var max = mask.length;
                    cid += mask[Math.floor(Math.random() * max)];
                }
                ckey = Math.floor(screen.width + screen.height + screen.colorDepth);
    }
    document.addEvent('domready', initalize);
    //initalize();
    function initalize() {
        if (chatOn) {
            if (sessionStorage) {
                localStorage.setItem("q4off", 0);
                if (sessionStorage.getItem("q4_utk") != _TOKEN_KEY || sessionStorage.getItem("q4_UID") != _UID) {
                    sessionStorage.removeItem("q4h");                
                }
                sessionStorage.setItem("q4_utk", _TOKEN_KEY);
                sessionStorage.setItem("q4_UID", _UID);                
            }
            ChatManager.name     = cookie_read("uname_" + _UID);
            ChatManager.login    = cookie_read("login_" + _UID);
            var avatar = ChatManager.avatar = cookie_read("avatar_" + _UID);
            if (avatar.indexOf("/http") != -1) {
                avatar = false;
            }
            if (ChatManager.avatar != 'd' && ChatManager.avatar) {
                ChatManager.avatar = ChatManager.avatar.replace(___WDCPREFIX + "/users/" + ChatManager.login + "/foto/", "");
                ChatManager.avatar = ChatManager.avatar.replace(___WDCPREFIX.replace("https", "http") + "/users/" + ChatManager.login + "/foto/", "");
                ChatManager.avatar = ___WDCPREFIX + "/users/" + ChatManager.login + "/foto/" + ChatManager.avatar;
            } else {
                ChatManager.avatar = ChatManager.defaultAvatar;
            }
            if (!avatar || !ChatManager.name || !ChatManager.login) {
                request("user", {"uid":null}, onUserData);
            } else {
                contactsList = new ChatContactsList(); //создать окно контактов
                restoreChatDialogs();                  //восстановить окна диалогов с пользователями
            }
            stream();
        }
    }
        /**
         * Вызывается для добавлениия нового входящего сообщения
         * @param String text
         * @param int    uid     - ИД отправителя
         * @param Array  files   - массив объектов [{img:"иконка", link:"ссылка на файл"}]
         * @param String date    - дата или время, если сообщение сегодняшнее
         * @param Bool   soundOn - воспроизводить ли звук при входящем сообщении, по умолчанию true
         * @param int   reciever_id - ИД получателя, используется только в том случае, если uid == _UID
         * @param int   msg_id - ИД сообщения
         * */
        window.incomingMessage = function(text, uid, files, date, soundOn, reciever_id, msg_id) {
            if (!chatOn) {
                return;
            }
            if ( !ChatManager.historyIsLoaded[uid] || !ChatManager.chatDialogs[uid] ) {
                if ( !(ChatManager.incomeCache[uid] instanceof Array) ) {
                    ChatManager.incomeCache[uid] = [];
                }
                var o = {    text:text, 
                            uid:uid, 
                            files:files, date:date, 
                            soundOn:soundOn, 
                            reciever_id:reciever_id, msg_id: msg_id};                
                ChatManager.incomeCache[uid].push(o);
                ChatManager.historyMaxId[uid] = msg_id;
            }
            if (soundOn == undefined) {
                soundOn = true;
            }
            if (soundOn != true) {
                soundOn = false;
            }
            var incoming = true;
            if (_UID == uid) {
                incoming = false;
                uid = reciever_id;
            }
            var s = text; 
            if (uid && !ChatManager.chatDialogs[uid]) {
                openChatWindow(uid);
                if (!ChatManager.playSound) {
                    ChatManager.playSound = new Array();
                }
                if (soundOn) {
                    ChatManager.playSound[uid] = 1;
                }
                if (contactsList) {
                    contactsList.contactHasNewMsg(uid, true);
                }
            } else if (ChatManager.chatDialogs[uid].get && ChatManager.chatDialogs[uid].get("uid") == uid && ChatManager.historyIsLoaded[uid]) {
                ChatManager.chatDialogs[uid].addNewItem(s, files, incoming, date, (soundOn?1:0), 0, msg_id);
                $$('.b-chat_active').removeClass("b-chat_active");
                if (incoming) {
                    if (ChatManager.blueBorderWnd && ChatManager.chatDialogs[ChatManager.blueBorderWnd]) {
                        ChatManager.chatDialogs[ChatManager.blueBorderWnd].removeClass("b-chat_active");
                    } 
                    ChatManager.blueBorderWnd = uid;
                    cookie_write("qch_flick_wnd", uid);
                }
                if (contactsList) {
                    contactsList.contactHasNewMsg(uid, true);
                }
            }
        }
        window.onresize=function() {
            for (var i in ChatManager.chatDialogs) {
                if (parseInt(i) || parseInt(i) == 0) {
                    var div = ChatManager.chatDialogs[i];
                    if (getClientWidth() > qw(div) && (getClientHeight() - ChatManager.topLimit) > div.getHeight() ) {
                        if (qy(div) + div.getHeight() > getClientHeight()) {
                            qy(div, getClientHeight() - div.getHeight() - 20);
                        }
                        if (qx(div) + qw(div) > getClientWidth()) {
                            qx(div, getClientWidth() - qw(div));
                        }
                    }
                }
            }
        }
        document.addEvent('mousemove', function(e) {
                if (chatOn) {
                    ChatManager.mouseX = e.client.x;
                    ChatManager.mouseY = e.client.y;
                    if (ChatManager.drag && ChatManager.activeWindow) {
                        if (ChatManager.closeSettingsInterval || ChatManager.settingsInterval) {
                            return false;
                        }
                        if (!Browser.ie || Browser.version > 8) {
                                window.getSelection().removeAllRanges();
                        } else if (Browser.version < 9){
                                document.selection.empty();
                        }                        
                        var wnd = ChatManager.activeWindow;
                        var top =  Number(wnd.get("startTop")) + Number(e.client.y - Number(wnd.get("startY")));
                        var left = Number(wnd.get("startLeft")) + Number(e.client.x - Number(wnd.get("startX")));
                        var width  = qw(wnd);
                        var height = qh(wnd);
                        var clientHeight = getClientHeight();
                        var clientWidth = getClientWidth();                        
                        if (top + height <= clientHeight && top > ChatManager.topLimit) {
                            wnd.style.top = top + 'px';
                        }else if (top < ChatManager.topLimit){
                            wnd.style.top = ChatManager.topLimit + 'px';
                        } else if (top + height > clientHeight) {
                            wnd.style.top = clientHeight - height + 'px';
                        }
                        if (left + width <= clientWidth && left > 0) {
                            wnd.style.left = left + 'px';
                        } else if (left < 0) {
                            wnd.style.left = '0px';
                        } else if (left > left + width) {
                            wnd.style.left = clientWidth - width + 'px';
                        }
                        if (wnd.get("uid") == 0) {
                            ChatManager.lastPivot = ChatManager.lastPivotCL = qy(wnd) + qh(wnd);
                        }
                    }
                    //resize top active window
                    if (ChatManager.resizeTop && ChatManager.activeWindow) {
                        if (!Browser.ie || Browser.version > 8) {
                                window.getSelection().removeAllRanges();
                        } else if (Browser.version < 9){
                                document.selection.empty();
                        }
                        var wnd = ChatManager.activeWindow;
                        var top =  Number(wnd.get("startTop")) + Number(e.client.y - Number(wnd.get("startY")));
                        var height = qh(wnd);
                        var resizeDiv = wnd.getElement("div.scroll_outer");                        
                        var clientHeight = getClientHeight();
                        if (top >= ChatManager.topLimit) {
                            wnd.style.top = top + 'px';
                        }else if (top < ChatManager.topLimit){
                            wnd.style.top = ChatManager.topLimit + 'px';
                        }
                        var tape   = wnd.getElement("div.scroll_inner");
                        var top    = int(wnd.style.top);
                        var oldTop = int(wnd.get("startTop"));
                        var oldH   = int(wnd.get("startH"));  //высота контейнера со скролом на момент ресайза 
                        var oldY   = int(wnd.get("startScrollY"));  //высота прокручиваемой ленты на момент ресайза
                        var viewportH = oldH + oldTop - top;
                        if (viewportH < 0) {
                            viewportH = 0;
                        }
                        resizeDiv.style.height = viewportH + 'px';
                        var tapeY = (oldY + oldTop - top);
                        if (tapeY > 0) {
                            tapeY = 0;
                        }
                        if (viewportH < qh(tape)) {
                            if (oldY != 0) {
                                tape.style.top = tapeY + 'px';
                            } else {
                                tape.style.top = (viewportH - qh(tape)) + 'px';
                            }
                        }

                        var height = qh(wnd);
                        if (height + top > getClientHeight()) {
                            top = getClientHeight() - height;
                        }
                        if (top < ChatManager.topLimit) {
                            top = ChatManager.topLimit;
                        }
                        wnd.setStyle("top", top + 'px');
                        for (var i = 0; i < wnd.scrollbars.length; i++) {
                            wnd.scrollbars[i].reset();
                        } 
                    }
                    
                    if (ChatManager.activeWindow && ChatManager.activeWindow.getHeight) {
                        if (hitTest()) {
                            if (!Browser.ie || Browser.version > 8) {
                                window.getSelection().removeAllRanges();
                            } else if (Browser.version < 9){
                                    document.selection.empty();
                            }
                        }                        
                    }
                    
                    if (ie8()) {
                        if (Math.round(new Date().getTime()/1000.0) - ChatManager.activeTimestamp > ChatManager.wideStateLimit) {
                            if (cookie_read("qch_window_type") == 'autowidth' && cookie_read("qch_settings") != '1') {
                                quickNarrow();
                                //animateNarrow();
                            }
                        }
                    }
                }
        });

        /**
         * @return Bool true если курсор мыши находится над "главным окном" чата
         * */
        function hitTestMain() {
            if (!contactsList) {
                return;
            }
            var win = contactsList.win;
            if (!win) {
                return;
            }
            var x = ChatManager.mouseX;
            var y = ChatManager.mouseY;            
            var y1 = qy(win);
            var x1 = qx(win);
            var x2 = x1 + qw(win);
            var h = win.getHeight();
            var y2 = y1 + h;
            if ((x >= x1) && (x <= x2) && (y >= y1) && (y <= y2)) {
                return true;
            }
            return false;
        }

        /**
         * @return Bool true если курсор мыши находится над одним из "окон" чата
         * */
        function hitTest() {
            var x = ChatManager.mouseX;
            var y = ChatManager.mouseY;
            for (var i = 0; i < ChatManager.chatDialogs.length; i++) {
                if (!ChatManager.chatDialogs[i] || ChatManager.chatDialogs[i] == ChatManager.activeWindow) {
                    continue;
                }
                var y1 = qy(ChatManager.chatDialogs[i]);
                var x1 = qx(ChatManager.chatDialogs[i]);
                var x2 = x1 + qw(ChatManager.chatDialogs[i]);
                var h = ChatManager.chatDialogs[i].getHeight();
                var y2 = y1 + h;
                if ((x >= x1) && (x <= x2) && (y >= y1) && (y <= y2)) {
                    return true;
                }
            }
            return false;
        }

        document.addEvent('mouseup', function(e) {
            if (chatOn) {
                ChatManager.drag = 0;
                ChatManager.resizeTop = 0;
                if (currentVScrollHandle && currentVScrollHandle.handlePushDown) {
                    currentVScrollHandle.handlePushDown = 0;
                }
                writeGeometry(ChatManager.activeWindow);
            }
        });

         /**
         * Инициализация данных пользователя
         * */
         function onUserData(data) {
             ChatManager["name"] = data["name"];
             cookie_write("uname_" + _UID, ChatManager["name"]);
             
             ChatManager["login"] = data["login"];
             cookie_write("login_" + _UID, ChatManager["login"]);
             
             ChatManager["avatar"] = data["avatar"] ? data["avatar"] : "d";
             cookie_write("avatar_" + _UID, ChatManager["avatar"].replace(___WDCPREFIX + "/users/" + ChatManager["login"] + "/foto/", "").replace(___WDCPREFIX.replace("https", "http") + "/users/" + ChatManager["login"] + "/foto/", ""));
             if (ChatManager["avatar"] == "d") {
                 ChatManager["avatar"] = ChatManager.defaultAvatar;
             }
             contactsList = new ChatContactsList(); //создать окно контактов
             restoreChatDialogs();                  //восстановить окна диалогов с пользователями
         }
         /**
          * @param String   action   - идентификатор запроса (например, getuserlist)
          * @param String   data     - дополнительные параметры запроса (например, a=paramA&b=paramB)
          * @param Function callback - обработчик успешного ответа
          * @param [Function onerror]  - обработчик неуспешного ответа, опционально
          * @param [Object requestSender]  - объект, из которого вызываась функция request, опционально 
          * */
         function request(action, data, callback, onerror, requestSender) {
             if ( !cid ) {
                 initCKeys();
             }
             if (!onerror) {
                 onerror = null;
             }
             var req = new Request.JSON(
                {
                    url: ChatManager.ajaxRequestUrl,
                    onSuccess: callback,
                    onFailure: onerror
                }
            );
            if (requestSender) {
                 req.self = requestSender;
            }
            _data  = {func: action, attr:JSON.encode(data), u_token_key:_TOKEN_KEY,
                      cid:cid, ckey:ckey};
            req.post(_data);
         }
         
         
         function event(text) {
            if ( text ) {
                if ( text == ' ' ) {
                    //this.ping = 
                } else {
                    var r = JSON.decode(text);
                    if ( r ) {
                        var sound = true;
                        for ( var i in r ) {
                            if ( r[i].func == 'income' ) {
                                sound = sound && !r[i]['cckey'];
                                incomingMessage(r[i].attr.text, r[i].attr.uid, r[i].files,  r[i].attr.date, sound, r[i].attr.cuid, r[i].attr.id);
                                sound = false;
                            }
                            if ( r[i].func == 'error' ) {
                                error(r[i].attr.num, r[i].attr.text, r[i].attr.die);
                            }
                        }
                    }
                }
            }
        }
         
        function error(num, text, critical) {
             if ( critical ) {
                 streamReq.cancel();
                 if (ChatManager.criticalError != 1) {
                     contactsList.showError(text);
                     if (num == 2) {
                         ChatManager.criticalError = 1;
                     }
                 }                 
             } else {
                 if (ChatManager.errorDialog) {
                     ChatManager.lastNumError = num;
                     ChatManager.errorDialog.addNewItem(text, null, false, "", false, true, -1);
                 }
             }
         }
         
         function stream() {
            if (ChatManager.criticalError) {
                return;
            }
            var type  = 'drop';
            var agent = window.navigator.userAgent.match(/(Gecko|AppleWebKit|MSIE ([\.0-9]+))/i);
            if ( agent ) {
                // MSIE < 10 not work hold mode
                if ( agent[2] && parseFloat(agent[2]) < 10 ) {
                    type = 'drop';
                } else {
                    type = 'hold';
                }
            }
            if ( !cid ) {
                initCKeys();
            }
            
            var bcnt = 0;
            var params = {
                url:       '/chat.php',
                method:    'post',
                async:     true,
                onSuccess: function(resp) {
                    try {
                        event(resp.substring(bcnt));
                    } catch(e) {;}
                    stream();
                },
                onFailure: function(e) {
                    //stream();
                }
            };
            if ( type == 'hold' ) {
                params.onProgress = function(e) {
                    try {
                        event(e.currentTarget.responseText.substring(bcnt));
                        bcnt = e.currentTarget.responseText.length;
                    } catch (e) {;}
                }
            }
            streamReq = new Request(params);
            streamReq.send('u_token_key=' + _TOKEN_KEY + '&stream=' + type + '&cid=' + cid + '&ckey=' + ckey);
         }

        /**
         * ищет у контейнера элементы с определенными селекторами и в зависимости от их наличия
         * делает див таскаемым, растягиваемым по вертикали и т. п.
         * */
        function setChatWindowBehavior(div) {
            if (Browser.ie) {
                function  False() {
                    return false;
                }
                function  True() {
                    return false;
                }
                //div.onselectstart=False;
                div.getElements('div').each(
                    function (item) {
                        if (!item.hasClass('selectable_tag')) {
                            //item.onselectstart=False;
                            item.set('unselectable', 'on');
                        } else {
                            item.onselectstart=True;
                        }
                    }
                );
                div.getElements('span').each(
                    function (item) {
                        if (!item.hasClass('selectable_tag')) {
                            item.onselectstart=False;
                            item.set('unselectable', 'on');
                        } else {
                            item.onselectstart=True;
                        }
                    }
                );
                div.set('unselectable', 'on');
            }
            div.onclick = function(e) {
                if (div.get("uid") == ChatManager.blueBorderWnd) {
                    ChatManager.chatDialogs[ChatManager.blueBorderWnd].removeClass('b-chat_active');
                    cookie_write("qch_flick_wnd", -1);
                    ChatManager.blueBorderWnd = -1;
                    if (contactsList) {
                        contactsList.contactHasNewMsg(div.get("uid"), false);
                    }
                }
                swapZIndex(this);
            }
            //move
            if (div.getElement("div.b-chat__head")) {
                div.getElements("div.b-chat__head").addEvent('mousedown', function(e){
                    swapZIndex(div);
                    ChatManager.drag = 1;
                    div.set("startX", ChatManager.mouseX);
                    div.set("startY", ChatManager.mouseY);
                    div.set("startTop",  int(div.style.top));
                    div.set("startLeft", int(div.style.left));
                    return false;
                });
            }
            //resize
            if (div.getElement("span.resize_top")) {
                div.getElement("span.resize_top").addEvent('mousedown', function(e){
                    swapZIndex(div);
                    ChatManager.resizeTop = 1;
                    ChatManager.drag = 0;                    
                    div.set("startY", ChatManager.mouseY);
                    div.set("startTop",  int(div.style.top));
                    var resizeArea = div.getElement("div.scroll_outer");
                    var tape       = div.getElement("div.scroll_inner");
                    div.set("startH",  qh(resizeArea));
                    div.set("startScrollY", qy(tape));
                    return false;
                });/**/
            }
            //close window       
            if (div.getElement("span.b-chat__icon_close")) {
                div.getElement("span.b-chat__icon_close").addEvent('mousedown', function(e){
                    ChatManager.drag = 0;
                    var uid = div.get("uid");
                    ChatManager.historyIsLoaded[uid] = 0;
                    var tempArr = new Array();
                    var cookie  = new Array();
                    for (var i in ChatManager.chatDialogs) {
                        if (ChatManager.chatDialogs[i] instanceof HTMLDivElement && ChatManager.chatDialogs[i] != div) {
                            tempArr[i] = ChatManager.chatDialogs[i];
                            var cUid = ChatManager.chatDialogs[i].get("uid");
                            if (int(cUid) != 0 && cUid != uid) {
                                cookie.push(dec2s58(cUid));
                            }
                        }                        
                    }
                    ChatManager.chatDialogs = tempArr;
                    delete div.self;
                    var p = div.parentNode;
                    p.removeChild(div);
                    cookie_write("q4ls" + _UID, cookie.join("."));
                    ChatManager.activeWindow = contactsList.win;
                    return false;
                });
            }
            
            //maximize
            if (div.getElement(".b-chat__head_curt .b-chat__link_toggle")) {
                div.getElement(".b-chat__head_curt .b-chat__link_toggle").addEvent('click', function() {
                    if (ie8()) {
                        maximize(div);
                        if (ChatManager.settings["qch_window_type"] == 'narrow') {
                            contactsList.setNarrow();
                        }
                    } else {
                        maximizeAnimate(div);
                    }
                    return false;
                });                
            }
            //minimize
            if (div.getElement(".b-chat__head_full .b-chat__link_toggle")) {
                div.getElement(".b-chat__head_full .b-chat__link_toggle").addEvent('click', function() {
                    if (ie8()) {
                        minimize(div);
                    } else {
                        minimizeAnimate(div);
                    }
                    return false;
                });
            }
            
            if (div.getElement("span.b-chat__icon_arr-bot")) {
                div.getElement("span.b-chat__icon_arr-bot").addEvent('click', function() {
                    if (ie8()) {                        
                        contactsList.setWide(1);
                        minimize(div);
                    } else {
                        ChatManager.minimizeAfterWide = 1;
                        animateWide();
                    }
                    return false;
                });
            }
            
            //settings
            //open
            if (div.getElement('.b-chat__head_curt .b-chat__link_tune')) {
                div.getElement('.b-chat__head_curt .b-chat__link_tune').addEvent('mousedown',function(){
                    if (ChatManager.drag == 1) {
                        return false;
                    }
                    div.set("openContacts", 0);
                    if (ie8()) {
                        showSettings(div);
                    } else {
                        showSettingsAnimate(div, 0); //0 - анимируем только настройки
                    }
                    return false;
                })
            }
                        
            if (div.getElement('.b-chat__foot .b-chat__link_tune')) {
                div.getElement('.b-chat__foot .b-chat__link_tune').addEvent('mousedown',function(){
                    if (ChatManager.drag == 1) {
                        return false;
                    }
                    div.set("openContacts", 1);
                    if (ie8()) {
                        showSettings(div);
                    } else {
                        showSettingsAnimate(div, 1); //1 - анимируем настройки & contactsList
                    }
                    return false;
                })
            }
            //close            
            if (div.getElement('.b-chat__contact .b-buttons__link')) {
                div.getElement('.b-chat__contact .b-buttons__link').addEvent('click', function (){
                    if (animateProcess()) {
                        return false;
                    }
                    if (ie8() || (ChatManager.settings["qch_window_type"] == "narrow" && int(div.get("openContacts")) ) ) {
                        closeSettings(div);
                    } else {
                        closeSettingsAnimate(div);
                    }
                    readSettings();
                    return false;
                });
            }            
            //close and save
            if (div.getElement('.b-button_rectangle_color_green')) {
                div.getElement('.b-button_rectangle_color_green').addEvent('click', function () {
                    if (animateProcess()) {
                        return false;
                    }
                    if (ie8() || (ChatManager.settings["qch_window_type"] == "narrow" && int(div.get("openContacts")) ) ) {
                        closeSettings(div);
                    } else {
                        closeSettingsAnimate(div);
                    }
                    cookie_write("qch_sound", ChatManager.settings["qch_sound"]);
                    cookie_write("qch_window_type", ChatManager.settings["qch_window_type"]);
                    var s = int(cookie_read("qch_online"));
                    var doReload = 0;
                    if (s != ChatManager.settings["qch_online"]) {
                        doReload = true;
                    }
                    var contact_type = cookie_read("qch_contacts_type");
                    if (contact_type !=  ChatManager.settings["qch_contacts_type"]) {
                        doReload = true;
                    }
                    cookie_write("qch_contacts_type", ChatManager.settings["qch_contacts_type"]);
                    cookie_write("qch_online", ChatManager.settings["qch_online"]);
                    if (doReload) {                        
                        contactsList.reloadContacts();
                    }
                    return false;
                });
            }
            
            /**
             * @param HtmlDivElement      div - "окно"
             * @param Bool                animateContactsList - true когда надо разворачивать список контактов
             **/
            function closeSettingsAnimate(div) {
                ChatManager.currentPivot = qy(div) + qh(div);
                ChatManager.animateFlag  = !div.getElement('.b-chat__users').hasClass('b-chat__users_hide');
                ChatManager.dH = Math.round(qh(div.getElements("div.scroll_outer")[1]) / 8);
                var ls = div.getElements("div.scroll_outer");
                ChatManager.settingsStoredH = qh(ls[1]);
                var openContacts = int(div.get("openContacts"));
                if (openContacts) {
                    
                    if (!Browser.opera) {
                        closeSettings(div);
                        maximize(div);
                        ChatManager.currentMaxH = qh(ls[0]);
                        ChatManager.dH2 = Math.round(ChatManager.currentMaxH / 16);
                        minimize(div);
                        var storeLastPivot = ChatManager.lastPivot;
                        showSettings(div);
                        ChatManager.lastPivot = storeLastPivot;
                    } else {
                        var ulist = div.getElements("div.b-chat__user");
                        ChatManager.currentMaxH = 17*2 + 21*int(ulist.length);
                        ChatManager.dH2 = Math.round(ChatManager.currentMaxH / 16);
                    }
                    
                    div.addClass('b-chat__list');
                    div.addClass('wnd_open');                    
                    div.getElements('.b-chat__head_full').removeClass('b-chat__head_hide');
                    div.getElements('.b-chat__users').removeClass('b-chat__users_hide');                    
                    div.getElements('.b-chat__name').removeClass('b-chat__name_hide');
                    qh(div.getElements('.b-chat__users'), 0);
                    
                    qy(div, ChatManager.currentPivot - qh(div));
                }
                
                ChatManager.closeSettingsInterval = setInterval(
                    function () {
                        function resetPivot(div, dY, h, div_0, openContacts) {
                            if (h <= 0) {                                
                                if (openContacts) {
                                    div.addClass('b-chat__list');
                                    div.addClass('wnd_open');
                                    div.getElements('.b-chat__head_curt').addClass('b-chat__head_hide');
                                    div.getElements('.b-chat__head_full').removeClass('b-chat__head_hide');
                                    div.getElements('.b-chat__users').removeClass('b-chat__users_hide');
                                    div.getElements('.b-chat__foot').removeClass('b-chat__foot_hide');
                                    div.getElements('.b-chat__name').removeClass('b-chat__name_hide');
                                    cookie_write("qch_open_" + int(div.get("uid")), 1);
                                    ChatManager["qch_open_" + int(div.get("uid"))] = 1;
                                    
                                } else {
                                    div.getElement('.b-chat__head_curt').removeClass('b-chat__head_hide');
                                }                            
                                div.getElement('.b-chat__contact').addClass('b-chat__contact_hide');
                                div.getElement('.b-chat__head_contact').addClass('b-chat__head_hide');
                            }
                            if (!dY) dY = 1;
                            if (ChatManager.currentPivot == ChatManager.lastPivot) {
                                return true;
                            }
                            dY = Math.abs(dY);
                            if (ChatManager.currentPivot > ChatManager.lastPivot) {
                                dY = -1 * dY;
                            }                            
                            var y = qy(div);
                            var Y = y + dY;
                            var changeSignFlag = 1;
                            var forceCorrect   = 0;
                            while (changeSignFlag) {
                                changeSignFlag = 0;
                                if (
                                       (Y + qh(div) > ChatManager.lastPivot && ChatManager.currentPivot < ChatManager.lastPivot)
                                       || (Y + qh(div) < ChatManager.lastPivot && ChatManager.currentPivot > ChatManager.lastPivot)
                                   ) 
                                   {
                                       changeSignFlag = 1;
                                       dY = Math.floor(dY / 2);
                                       if (Math.abs(dY) == 0) {
                                           forceCorrect = 1;
                                           break;
                                       }
                                       if (
                                           (Y + qh(div) > ChatManager.lastPivot && ChatManager.currentPivot < ChatManager.lastPivot)
                                           || (Y + qh(div) < ChatManager.lastPivot && ChatManager.currentPivot > ChatManager.lastPivot)
                                       ) {
                                           forceCorrect = 1;
                                           break;
                                       }
                                       Y = y + dY;                                       
                                   }
                            }                            
                            var doMove = 1;                            
                            if (Y < ChatManager.topLimit) {
                                doMove = 0;
                            }
                            
                            if (Y + qh(div) > getClientHeight()) {
                                doMove = 0;
                            }
                            if (doMove) {
                                qy(div, Y);
                                ChatManager.currentPivot = qy(div) + qh(div);
                            }
                            if (forceCorrect) {
                                qy(div, ChatManager.lastPivot - qh(div) - 30);
                                ChatManager.currentPivot = ChatManager.lastPivot;
                                return true;
                            }
                            return false;
                        }
                        
                        var openContacts = int(div.get("openContacts"));
                        //close settings step
                        var dH = ChatManager.dH;
                        var ls = div.getElements("div.scroll_outer");
                        var div_1 = ls[1];
                        var div_0 = div_1;
                        var h = qh(div_1) - dH;
                        if (qh(div_1) > 0) {
                            while (h < 0) {
                                dH = Math.ceil(dH / 2);
                                h = qh(div_1) - dH;
                                if (h <= 0 && dH <= 1) {
                                    qh(div_1, 1);
                                    dH = 1;
                                    break;
                                }
                            }
                        }
                        h = qh(div_1) - dH;
                        if (h >= 0) {
                            qh(div_1, h);
                            var y = ChatManager.currentPivot - qh(div);
                            if (y < ChatManager.topLimit) {
                                y = ChatManager.topLimit
                            }
                            qy(div, y);
                        }
                        
                        //open contacts list step
                        var flag = resetPivot(div, dH, h, div_0, openContacts);
                        if (openContacts) {
                            flag = false;
                            dH = ChatManager.dH2;
                            div_1 = ls[0];
                            
                            var h2 = qh(div_1) + dH;
                            var max = ChatManager.currentMaxH;
                            if (qh(div_1) < max) {
                                while (h2 > max) {
                                    dH = Math.ceil(dH / 2);
                                    h2 = qh(div_1) + dH;
                                    if (h2 >= max && dH <= 1) {
                                        qh(div_1, max - 1);
                                        dH = 1;
                                        break;
                                    }
                                }
                            }
                            h2 = qh(div_1) + dH;
                            if (h2 <= max) {
                                qh(div_1, h2);
                                var y = ChatManager.currentPivot - qh(div);
                                if (y < ChatManager.topLimit) {
                                    y = ChatManager.topLimit
                                }                        
                                qy(div, y);
                            }
                            if (h2 >= max) {
                                flag = true;
                            }
                        }
                        if (h <= 0 && flag) {
                            clearInterval(ChatManager.closeSettingsInterval);
                            ChatManager.closeSettingsInterval = 0;                            
                            div_0.setStyle("height", ChatManager.settingsStoredH);
                            if (openContacts) {
                                div.addClass('b-chat__list');
                                div.addClass('wnd_open');
                                div.getElements('.b-chat__head_curt').addClass('b-chat__head_hide');
                                div.getElements('.b-chat__head_full').removeClass('b-chat__head_hide');
                                div.getElements('.b-chat__users').removeClass('b-chat__users_hide');
                                div.getElements('.b-chat__foot').removeClass('b-chat__foot_hide');
                                div.getElements('.b-chat__name').removeClass('b-chat__name_hide');
                                cookie_write("qch_open_" + int(div.get("uid")), 1);
                                ChatManager["qch_open_" + int(div.get("uid"))] = 1;
                                try {
                                    div.setScrollBarVisible();
                                } catch(e){;}
                            } else {
                                div.getElement('.b-chat__head_curt').removeClass('b-chat__head_hide');
                            }                            
                            div.getElement('.b-chat__contact').addClass('b-chat__contact_hide');
                            div.getElement('.b-chat__head_contact').addClass('b-chat__head_hide');
                            if (ChatManager.lastPivot) {
                                ChatManager.currentPivot = ChatManager.lastPivot;
                            }
                            var y = ChatManager.currentPivot - qh(div);
                            if (y < ChatManager.topLimit) {
                                y = ChatManager.topLimit;
                            }                        
                            qy(div, y);
                            
                            cookie_write("qch_settings", 0);
                            writeGeometry(div);
                            ChatManager.settingsIsShow = 0;
                            ChatManager.closeSettingsInterval = 0;
                            return;
                        }
                    }
                ,1000 / 24);
            }
        }
        function closeSettings(div) {
                var openContacts = int(div.get("openContacts"));
                var top = qy(div);
                var y = top + qh(div);
                div.getElement('.b-chat__contact').addClass('b-chat__contact_hide');
                div.getElement('.b-chat__head_contact').addClass('b-chat__head_hide');
                div.getElement('.b-chat__head_curt').removeClass('b-chat__head_hide');
                top = y - qh(div);
                if (top != ChatManager.lastPivot) {
                    top = ChatManager.lastPivot - qh(div);
                }
                qy(div, top);
                if (openContacts) {
                    maximize(div);                    
                }
                cookie_write("qch_settings", 0);
                if (ChatManager.settings["qch_window_type"] == "narrow" && int(div.get("openContacts")) ) {
                    contactsList.setNarrow(0);                    
                }
                writeGeometry(div);
                ChatManager.settingsIsShow = 0;
                return false;
        }
        //читаем настройки чата из кук, если нет, оставляем по умолчанию, но запрашиваем с сервера
        function readSettings() {
            var id = cookie_read("qch_contacts_type");
            if (!$(id)) {
                id = "all_contacts";
                cookie_write("qch_contacts_type", id);                
            }
            ChatManager.settings["qch_contacts_type"] = id;
            $(id).checked = true; 
            $("online").checked = int(cookie_read("qch_online")) != 0 ?true:false;            
            var t = ChatManager.settings["qch_online"] = int(cookie_read("qch_online"));
            cookie_write("qch_online", t);
            var id = cookie_read("qch_window_type");
            if (!$(id)) {
                id = 'autowidth';
                cookie_write("qch_window_type", id);
            }
            ChatManager.settings["qch_window_type"] = id;
            $(id).checked = true;
            
            var snd = cookie_read("qch_sound");
            $('chatsoundoff').removeClass("b-chat__txt_hide");
            $('chatsoundon').removeClass("b-chat__txt_hide");
            if (snd == 0) {
                $('chatsoundoff').addClass("b-chat__txt_hide");
            } else {
                $('chatsoundon').addClass("b-chat__txt_hide");
            }                
        }
        
        function showSettings(div) {
            if (int(div.get('uid'))) {
                return;
            }
            div.self.setWide(1, 1);
            ChatManager.lastPivot = qy(div) + qh(div); //перед раскрытием настроек записываем pivot
            var top = qy(div);
            var y = top + qh(div);
            div.removeClass('wnd_open');
            div.getElement('.b-chat__contact').removeClass('b-chat__contact_hide');
            div.getElement('.b-chat__head_contact').removeClass('b-chat__head_hide');
            div.getElement('.b-chat__head_curt').addClass('b-chat__head_hide');
            div.getElement('.b-chat__head_full').addClass('b-chat__head_hide');
            div.getElement('.b-chat__head_small').addClass('b-chat__head_hide');
            div.getElement('.b-chat__users').addClass('b-chat__users_hide');
            div.getElement('.b-chat__foot').addClass('b-chat__foot_hide');            
            top = y - qh(div);
            qy(div, top);
            resetWndTop(div);
            cookie_write("qch_settings", 1);
            writeGeometry(div);
            ChatManager.settingsIsShow = 1;
        }
        /**
         * @param HTMLDiv div
         * @param Bool    animateContactsList = 0 если не 0, анимируем список контактов
         * */
        function showSettingsAnimate(div, animateContactsList) {
            if (int(div.get('uid'))) {
                return;
            }
            ChatManager.lastPivot = qy(div) + qh(div); //перед раскрытием настроек записываем pivot
            var skipShift = 1;
            if (ChatManager.settings.qch_window_type == 'narrow' && int(div.get("openContacts"))) {
                skipShift = 0;
            }
            div.self.setWide(1, skipShift);
            var top = qy(div);
            var h = qh(div);
            var y = top + h;
            var prevH = h;
            var ls = div.getElements("div.scroll_outer");
            
            div.getElement('.b-chat__contact').removeClass('b-chat__contact_hide');
            div.getElement('.b-chat__head_contact').removeClass('b-chat__head_hide');
            div.getElement('.b-chat__head_curt').addClass('b-chat__head_hide');
            div.getElement('.b-chat__head_small').addClass('b-chat__head_hide');
            div.getElements('.b-chat__head_full').addClass('b-chat__head_hide');
            h = qh(div);
            y -= h;
            qy(div, y);
            if (animateContactsList) {
                if (y < ChatManager.topLimit) {
                    var tH = qh(ls[0]) + prevH - h;
                    if (tH < 0) {
                        tH = null;
                    }
                    qh(ls[0], tH);
                    qy(div, ChatManager.topLimit);
                }
                ChatManager.currentContactsH  = qh(ls[0]);
                ChatManager.animateContactsList = 1;
            } else {
                ChatManager.animateContactsList = 0;
            }
            
            if (ls.length == 2) {
                ChatManager.currentSettingsH  = qh(ls[1]);                
                ChatManager.currentSettingsY  = qy(div);
                qh(ls[1], 0);
            
                y += h  - qh(div);
                if (y < ChatManager.topLimit) {
                    y = ChatManager.topLimit;
                }
                qy(div, y);

                ChatManager.currentSh = 0;
                ChatManager.currentPivot = qy(div) + qh(div);
                if (animateContactsList) {
                    var y = qy(div) + qh(div);
                    div.getElement('.b-chat__foot').addClass('b-chat__foot_hide');
                    qy(div, y - qh(div));
                }
                ChatManager.settingsInterval = setInterval(
                    function () {
                        var dH  = Math.ceil(ChatManager.currentSettingsH / 30);
                        var dH3 = Math.ceil(ChatManager.currentContactsH / 5);
                        var maxH = ChatManager.currentSettingsH;
                                            
                        function process (currentHKey, div_1, maxH, dH) {
                            if (ChatManager[currentHKey] >= maxH) {
                                return true;
                            }
                            var h  = ChatManager[currentHKey] + dH;
                            var y = qy(div);
                            while (h > maxH) {
                                dH = Math.round(dH / 2);
                                h = ChatManager[currentHKey] + dH;
                                if (h > maxH && dH <= 1) {
                                    h = maxH - 1;
                                    dH = 1;
                                    //y = ChatManager.currentSettingsY + 1;
                                    break; 
                                }
                            }
                            if (h < maxH) {
                                h = ChatManager[currentHKey] + dH;
                                y -= dH;
                            }
                            div.setStyle("top", y + "px");
                            div_1.setStyle("height", h + "px");
                            ChatManager[currentHKey] = h;
                            if (h >= maxH) {
                                if (!ChatManager.animateContactsList) {
                                    div.setStyle("top", ChatManager.currentSettingsY + "px");
                                }
                                div_1.setStyle("height", maxH + "px");
                                return true;
                            }
                            return false;
                        }
                        //уменьшение списка контактов 
                        function process2 (div_1, dH, settingsIsOpen) {
                            var h  = ChatManager.currentContactsH - dH;
                            if (ChatManager.currentContactsH <= 0) {
                                return true;
                            }
                            var y = qy(div);
                            if (settingsIsOpen) {
                                h = ChatManager.currentContactsH = 1;
                                dH = 1;
                            }
                            while (h < 0) {
                                dH = Math.round(dH / 2);
                                h = ChatManager.currentContactsH - dH;
                                if ((h <= 0 && dH <= 1) || settingsIsOpen) {
                                    h = ChatManager.currentContactsH = 1;
                                    dH = 1;
                                    break; 
                                }
                            }
                            if (h > 0) {
                                h = ChatManager.currentContactsH - dH;
                                y += dH;
                            }
                            div.setStyle("top", y + "px");
                            div_1.setStyle("height", h + "px");
                            ChatManager.currentContactsH = h;
                            if (h <= 0) {
                                div_1.setStyle("height", null);
                                div.getElement('.b-chat__users').addClass('b-chat__users_hide');
                                return true;
                            }
                            return false;
                        }

                        var ls = div.getElements("div.scroll_outer");
                        var f1 = process("currentSh", ls[1], ChatManager.currentSettingsH, dH);
                        var f2 = true;
                        if (ChatManager.animateContactsList) {
                            f2 = process2(ls[0], dH3, f1);
                        }
                        var y = ChatManager.currentPivot - qh(div);
                        if (y < ChatManager.topLimit) {
                            y = ChatManager.topLimit;
                        }
                        qy(div, y);
                        if (f1 && f2) {
                            clearInterval(ChatManager.settingsInterval);
                            ChatManager.settingsInterval = 0;
                            minimize(div);
                            ChatManager.animateContactsList = 0;                  
                            div.getElement('.b-chat__foot').addClass('b-chat__foot_hide');
                            div.getElement('.b-chat__head_curt').addClass('b-chat__head_hide');                            
                            div.getElement('.b-chat__users').addClass('b-chat__users_hide');
                            y = ChatManager.currentPivot - qh(div);
                            if (y < ChatManager.topLimit) {
                                y = ChatManager.topLimit;
                            }
                            qy(div, y);
                            cookie_write("qch_settings", 1);
                            writeGeometry(div);
                            ChatManager.settingsIsShow = 1;
                            return;
                        }
                    }
                ,1000 / 48);
            }
    }
        
        function maximize(div) {
            if (div.hasClass('wnd_open')) {
                return;
            }
            var top = qy(div);
            var y = top + qh(div);
            ChatManager.lastPivotCL = y;
            div.addClass('b-chat__list');
            div.addClass('wnd_open');
            div.getElements('.b-chat__head_curt').addClass('b-chat__head_hide');
            div.getElements('.b-chat__head_full').removeClass('b-chat__head_hide');
            div.getElements('.b-chat__users').removeClass('b-chat__users_hide');
            div.getElements('.b-chat__foot').removeClass('b-chat__foot_hide');
            div.getElements('.b-chat__name').removeClass('b-chat__name_hide');

            var top = y - qh(div);
            qy(div, top);
            resetWndTop(div);
            cookie_write("qch_open_" + int(div.get("uid")), 1);
            ChatManager["qch_open_" + int(div.get("uid"))] = 1;
            writeGeometry(div);            
            return false;
        }
        
        function maximizeAnimate(div) {
            var top = qy(div); //store y
            var y = top + qh(div);
            ChatManager.lastPivotCL = y;
            if (!Browser.opera) {
                maximize(div);
                //get limits
                var max = qh(div.getElement("div.scroll_outer"));
                ChatManager.currentMaxY = qy(div);
                ChatManager.dH = Math.round(max / 5);
                ChatManager.currentMaxH = max;
                minimize(div); //minimize and restore y
                var top = y - qh(div);
                qy(div, top);
            } else {
                var ls = div.getElements("div.b-chat__user");
                ChatManager.currentMaxH = 2*17 + int(ls.length)*21;
                ChatManager.dH = Math.round(ChatManager.currentMaxH / 5);
            }
                
            //open window
            var top = qy(div);
            var y = top + qh(div);
            div.addClass('b-chat__list');
            div.addClass('wnd_open');
            div.getElements('.b-chat__head_curt').addClass('b-chat__head_hide');
            div.getElements('.b-chat__head_full').removeClass('b-chat__head_hide');
            div.getElements('.b-chat__users').removeClass('b-chat__users_hide');
            div.getElements('.b-chat__foot').removeClass('b-chat__foot_hide');
            div.getElements('.b-chat__name').removeClass('b-chat__name_hide');
            div.getElement('div.scroll_outer').setStyle("height", '0px');
            var top = y - qh(div);
            qy(div, top);
            resetWndTop(div);

            ChatManager.maximizeInterval = setInterval(
                function () {
                    var dH = ChatManager.dH;
                    var ls = div.getElements("div.scroll_outer");
                    var h  = qh(ls[0]);
                    var y  = qy(div);
                    var sY = y;
                    var sH = h;
                    h += dH;
                    y = y - dH;
                    while (h > ChatManager.currentMaxH) {
                        dH = Math.round(dH / 2);
                        h = sH + dH;
                        y = sY - dH;
                        if (h >= ChatManager.currentMaxH && dH == 1) {
                            h = ChatManager.currentMaxH;
                            y = ChatManager.currentMaxY;                            
                            break;
                        }
                    }

                    qh(ls[0], h);
                    qy(div, y);
                    resetWndTop(div);
                    if (h == ChatManager.currentMaxH) {
                        clearInterval(ChatManager.maximizeInterval);
                        resetWndTop(div);
                        cookie_write("qch_open_" + int(div.get("uid")), 1);
                        ChatManager["qch_open_" + int(div.get("uid"))] = 1;
                        writeGeometry(div);
                        if (ChatManager.settings["qch_window_type"] == 'narrow') {
                            animateNarrow(div);
                        }
                        return;                
                    }
                },
                1000 / 24
            );
        }
        
        function minimize(div) {
            if (ChatManager.criticalError) {
                return;
            }
            var top = qy(div);
            var y = top + qh(div);
 
            div.removeClass('b-chat__list');
            div.removeClass('wnd_open');
            div.getElements('.b-chat__head_curt').removeClass('b-chat__head_hide');
            div.getElements('.b-chat__head_full').addClass('b-chat__head_hide');
            div.getElements('.b-chat__users').addClass('b-chat__users_hide');
            div.getElements('.b-chat__foot').addClass('b-chat__foot_hide');
            div.getElements('.b-chat__name').addClass('b-chat__name_hide');
            top = y - qh(div);
            qy(div, top);
            cookie_write("qch_open_" + int(div.get("uid")), 0); 
            ChatManager["qch_open_" + int(div.get("uid"))] = 0;
            writeGeometry(div);
            div.getElement("div.b-chat__head_full").addClass('b-chat__head_hide');
        }

        function minimizeAnimate(div) {
            if (animateProcess()) {
                return;
            }
            ChatManager.dH = Math.round( qh(div.getElement("div.scroll_outer")) / 5);
            var storePivot = ChatManager.lastPivotCL;
            if (!Browser.opera) {
                minimize(div);
                ChatManager.currentMinY = qy(div);
                maximize(div);
            } else {
                var ls = div.getElements("div.b-chat__user");
                ChatManager.currentMinY = qy(div) + 21 * int(ls.length);
            }
            if (storePivot) {
                ChatManager.lastPivotCL = storePivot;
            }
            ChatManager.currentPivot = qy(div) + qh(div);
            ChatManager.minimizeInterval = setInterval(
                function () {
                    function resetPivot(div, dY) {
                        if (!dY) dY = 1;
                        if (ChatManager.currentPivot == ChatManager.lastPivotCL) {
                            return true;
                        }                        
                        if (ChatManager.currentPivot > ChatManager.lastPivotCL) {
                            var y = qy(div) - dY;
                            if (y + qh(div) < ChatManager.lastPivotCL) {
                                y = ChatManager.lastPivotCL - qh(div);
                            }
                            qy(div, y);
                            ChatManager.currentPivot = y + qh(div);
                        } else {
                            ChatManager.currentPivot = ChatManager.lastPivotCL;
                        }
                        return false;
                    }
                    
                    var dH = ChatManager.dH;
                    var ls = div.getElements("div.scroll_outer");
                    var h  = qh(ls[0]);
                    var y  = qy(div);
                    var sY = y;
                    var sH = h;
                    h -= dH;
                    y = y + dH;
                    while (h < 0) {
                        dH = Math.round(dH / 2);
                        h = sH - dH;
                        y = sY + dH;
                        if (h <= 0 && dH == 1) {
                            h = 0;
                            y = ChatManager.currentMinY;
                            break;
                        }
                    }                    
                    if (h != 0) {
                        qh(ls[0], h);
                        qy(div, y);
                    }
                    resetWndTop(div);
                    var flag = resetPivot(div, dH);
                    if (h == 0 && flag) {
                        clearInterval(ChatManager.minimizeInterval);                        
                        ChatManager.minimizeInterval = 0;
                        var y = qy(div) + qh(div);
                        minimize(div);
                        qy(div, ChatManager.currentPivot - qh(div));
                        ls[0].setStyle("height", null);
                        resetWndTop(div);
                        cookie_write("qch_open_" + int(div.get("uid")), 0);
                        ChatManager["qch_open_" + int(div.get("uid"))] = 0;
                        writeGeometry(div);
                        return;                
                    }
                },
                1000 / 24
            );
        }
        /**
         * @return Bool true если выполняется анимация
         * */
        function animateProcess() {
            if ( ChatManager.settingsInterval
                 || ChatManager.wideNarrowInerval
                 || ChatManager.minimizeInterval
                 || ChatManager.closeSettingsInterval
               ) {
                return true;
            }
            return false;
        }
        
        /**
         * Контроль положения окна внутри вьюпорта браузера
         * */
        function resetWndTop(div) {
            var height = qh(div);
            var top    = qy(div);
            //если вылезли ниже вьюпорта браузера, то перемещаемся вверх
            if (height + top > getClientHeight()) {
                top = getClientHeight() - height;
            }
            if (top < ChatManager.topLimit) {
                top = ChatManager.topLimit;
            }
            div.setStyle("top", top + 'px');/**/
            if (height >= div.get('maxH')) {
                resetGeometry(div, div.get('maxH'));
            }
            try {
                div.setScrollBarVisible();
            } catch(e){;}
        }
        /**
         * Класс для реализации окна списка контактов
         * */
        function ChatContactsList() {
            this.winTemplate = '<div id="qchsound"></div>\<div class="b-chat__inner"' + (Browser.opera?'style="background:#909090 !important"':'') + '>\
                <div class="b-chat__body">\
                <div class="b-chat__head b-chat__head_curt">\
                <div class="b-chat__txt b-chat__txt_pad_5 b-chat__txt_float_right"><a class="b-chat__link  b-chat__link_float_right b-chat__link_toggle b-chat__link_color_f88b00" href="#"><span class="b-chat__icon b-chat__icon_people"></span><span id="qchcount1">32</span><span class="b-chat__icon b-chat__icon_arr-up"></span></a></div>\
                <div class="b-chat__txt b-chat__txt_inline-block b-chat__txt_pad_5"><a class="b-chat__link b-chat__link_tune" href="#"><span class="b-chat__icon b-chat__icon_tune"></span>Настройка</a></div>\
            </div>\
            <div class="b-chat__head b-chat__head_small b-chat__head_hide">\
                     <div class="b-chat__txt b-chat__txt_pad_5_0"><a href="#" class="b-chat__link b-chat__link_float_right"><span class="b-chat__icon b-chat__icon_arr-bot"></span></a><span id="qchcount3"></span></div>\
            </div>\
            <div class="b-chat__head b-chat__head_full b-chat__head_hide" style="overflow:hidden">\
                     <div class="b-chat__txt" ><a class="b-chat__link b-chat__link_toggle b-chat__link_float_right" href="#"><span class="b-chat__icon b-chat__icon_arr-bot"></span></a><span class="b-chat__icon b-chat__icon_people"></span><span id="qchcount2">7 активных контакта</span></div>\
            </div>\
            <div class="b-chat__head b-chat__head_contact b-chat__head_hide">\
                <div class="b-chat__txt b-chat__txt_pad_5"><span class="b-chat__icon b-chat__icon_tune"></span>Настройка</div>\
            </div>\
            <div class="b-chat__users b-chat__users_hide scroll_outer">\
            \
            <div class="scroll_inner" style="position:relative; width:200px">\
            </div>\
                <div class="status_message" style="display:none; color:#D3101F; height:42px; z-index:10; position:absolute; left:7px;background-color:#F3F6F7; font-size:9pt;text-align:center; font-family:arial; padding-top:2px;"></div>\
                <div class="b-chat__scroll-holder" style="display:none"><div class="b-chat__scroll"></div></div>\
            </div>\
            <div class="b-chat__contact b-chat__contact_hide" id="qchat_settings">\
                <div class="scroll_outer" style="position:relative; overflow:hidden; height:250px">\
                    <div class="scroll_inner">\
                        <h4 class=" b-chat__title">Вид контакт-листа</h4>\
                        <div class="b-radio b-radio_layout_vertical b-radio_padleft_5">\
                            <div class="b-radio__item b-radio__item_padbot_5">\
                                <input id="all_contacts" class="b-radio__input" name="qwe" type="radio" value="" />\
                                <label class="b-radio__label" for="all_contacts" style="display:inline">Все контакты</label>\
                            </div>\
                            <div class="b-radio__item b-radio__item_padbot_5">\
                                <input id="active" class="b-radio__input" name="qwe" type="radio" value="" />\
                                <label class="b-radio__label" for="active">Только активные<br />(с хотя бы одним сообщением<br />за последний месяц)</label>\
                            </div>\
                        </div>\
                        <div class="b-check b-check_padleft_5 b-check_padbot_5">\
                            <input id="online" class="b-check__input" name="qwe" type="checkbox" value="" />\
                            <label class="b-check__label b-check__label_color_41" for="online">Только онлайн</label>\
                        </div>\
                        \
                        <h4 class=" b-chat__title">Окно контакт-листа</h4>\
                        <div class="b-radio b-radio_layout_vertical b-radio_padleft_5">\
                            <div class="b-radio__item b-radio__item_padbot_5">\
                                <input id="narrow" class="b-radio__input" name="sg2" type="radio" value="" />\
                                <label class="b-radio__label" for="narrow">Всегда узкое</label>\
                            </div>\
                            <div class="b-radio__item b-radio__item_padbot_5">\
                                <input id="wide" class="b-radio__input" name="sg2" type="radio" value="" />\
                                <label class="b-radio__label" for="wide">Всегда широкое</label>\
                            </div>\
                            <div class="b-radio__item b-radio__item_padbot_5">\
                                <input id="autowidth" class="b-radio__input" name="sg2" type="radio" value="" />\
                                <label class="b-radio__label" for="autowidth">Динамическое</label>\
                            </div>\
                        </div>\
                        <div id="chatsoundon" class="b-chat__txt b-chat__txt_pad_5"><span class="b-chat__icon b-chat__icon_sound-off"></span><span class="b-chat__txt b-chat__txt_padleft_3 b-chat__txt_valign_top b-chat__txt_inline-block b-chat__txt_color_41">Звук входящих сообщений<br />отключен. <a class="b-chat__link b-chat__link_dot_414141" href="#" id="sndOn">Включить</a></span></div>\
                        <div id="chatsoundoff" class="b-chat__txt b-chat__txt_hide b-chat__txt_pad_5"><span class="b-chat__icon b-chat__icon_sound-on"></span><span class="b-chat__txt b-chat__txt_padleft_3 b-chat__txt_valign_top b-chat__txt_inline-block b-chat__txt_color_41">Звук входящих сообщений<br />включен. <a class="b-chat__link b-chat__link_dot_414141" href="#" id="sndOff">Отключить</a></span></div>\
                    </div><!-- inner -->\
                    <div class="b-chat__scroll-holder" style="display:none"><div class="b-chat__scroll"></div></div>\
                </div> <!-- outer -->\
            <div id="qchoff" class="b-chat__txt b-chat__txt_pad_5"><a class="b-chat__link b-chat__link_dot_c7271e" href="javascript:void(0)">Не буду пользоваться быстрочатом</a></div>\
            <div class="b-buttons b-buttons_padtb_10 b-buttons_padleft_5">\
                <a class="b-button b-button_rectangle_color_green" href="#">\
                        <span class="b-button__b1">\
                            <span class="b-button__b2">\
                                <span class="b-button__txt">Сохранить</span>\
                            </span>\
                        </span>\
                    </a>&#160;\
                    <a href="#" class="b-buttons__link b-buttons__link_dot_c10601">отмена</a>\
                </div>\
            </div>\
               <div class="b-chat__foot b-chat__foot_hide b-chat__txt" style="overflow:hidden"><div style="width:210px"><a class="b-chat__link b-chat__link_tune" href="#"><span class="b-chat__icon b-chat__icon_tune"></span><span id="settingLabel">Настройка</span></a></div></div>\
        </div>\
</div>';

            this.itemTemplate = '<a class="b-user__link" href="{$profilelink}" ><img id="{$avatarid}" class="b-chat__userpic" src="{$avatar}" onerror="qchat_onavatar_error(\'{$avatarid}\')" title="{$fullname}" alt="{$fullname}" width="16" height="16" style="border:none; text-decoration:none;"/></a>\
            <span class="b-chat__name b-chat__txt {$usertype} b-chat__name_hide">{$name}</span><span style="display:none" class="b-chat__mess"></span>\
            <span class="b-chat__{$online}"></span>';
            
            var winHeight = 30;
            var x = getClientWidth() - 210 - ChatManager.padding;
            var y = getClientHeight() - winHeight - ChatManager.padding;
            this.win = new Element("div", {"class":"b-chat b-chat_width_210", "html":this.winTemplate, "style":"position:fixed; top:" + y + "px; left:" + x + "px; -ms-user-select: none; -webkit-user-select: none; -moz-user-select: none;z-index:" + ChatManager.maxZ, id:"quick_chat_contact_list"});
            this.win.set('maxH', getClientHeight() - ChatManager.topLimit);
            this.win.inject(document.getElementsByTagName('body')[0], 'bottom');

            var count = int(cookie_read("q4cc_" + _UID));
            $('qchcount1').set("text", count);
            //добавляем аудио
            var html5 = '<audio id="qchsnd"><source src="/css/block/b-chat/incom.mp3" type="audio/mpeg"><source src="/css/block/b-chat/incom.ogg" type="audio/ogg"></audio>';
            this.audiofunction = "play";
            var swfObj= '<object width="1" height="1" id="qchsnd" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"  codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0"><param name="allowScriptAccess" value="sameDomain" /><param name="allowFullScreen" value="false" /><param name="flashvars" value="sound=/css/block/b-chat/incom.mp3" /><param name="movie" value="/css/block/b-chat/sound.swf" /><param name="quality" value="high" /><param name="bgcolor" value="#F3F6F7" /></object>';
            var s = html5;
            if (Browser.ie && Browser.version < 9) {
                s = swfObj;
                this.audiofunction = "qch_playAudio";
            }
            $("qchsound").set("html", s);
            
            readSettings();                      //пытаемся считать настройки чата пользователя из кук
            setChatWindowBehavior(this.win);     //делаем див таскаемым, растягиваемым и т. п.
            this.contactScroll  = new VScrollBar(this.win.getElement('div.scroll_outer'), this.win.getElement('div.scroll_inner'), this.win.getElement('div.scroll_outer').getElement('div.b-chat__scroll'));
            this.settingsScroll = new VScrollBar($('qchat_settings').getElement('div.scroll_outer'), $('qchat_settings').getElement('div.scroll_inner'), $('qchat_settings').getElement('div.scroll_outer').getElement('div.b-chat__scroll'));
            this.win.scrollbars = [this.contactScroll, this.settingsScroll];
            this.win.self = this;
            this.win.set("uid", 0);
            ChatManager.chatDialogs[0] = this.win;            
            this.onlineCounter = [];  //этот массив использую для хранения id онлайн пользователей, что необходимо для индикации цифры в случае появления в сети нового
            /**
             * Вывод сообщения об ошибке в том случае, если нет открытых окон чата
             * */
            ChatContactsList.prototype.showError = function(msg) {
                var o = this.win;
                if (o.getElements("div.b-chat__user")) {
                    o.getElements("div.b-chat__user").setStyle("display", "none");
                }
                o.getElement("div.status_message").setStyle("display", "block").set("html", msg + "<br/>Попробуйте обновить страницу");
                if (qw(o) < 60) {
                    this.setWide(true);
                }
                maximize(o);
                qh(o.getElements("div.scroll_outer")[0], 52);                
            }
            
            ChatContactsList.prototype.onContactsListData = function(data) {
                if (!data) {
                    data = ChatManager.cacheList;
                }
                if (animateProcess()) {
                    ChatManager.cacheList = data;
                    setTimeout(function () {
                            contactsList.onContactsListData();
                        }, 2000);
                    return;
                }
                var obj = this.self;
                if (!obj) {
                    obj = this;
                }
                var pivot = qy(obj.win) + qh(obj.win);
                var scrollY = qy(obj.win.getElement('div.scroll_inner'));
                if (!Browser.opera) {
                    obj.win.getElement('div.scroll_inner').set("html", "");
                }
                obj.win.getElement('div.scroll_outer').setStyle("height", null);
                var offline = new Array();
                var count = 0;
                var onlineCounter = [];
                for (var i = 0; i < data.length; i++) {
                    count++;
                    var o = data[i];
                    if (o.online) {
                        if (obj.onlineCounter[o.uid] != 1) {
                            ChatManager.indicate = 0;
                            var a = $('qchcount1').parentNode;
                            a.removeClass("b-chat__link_color_f88b00");
                            onlineCounter[o.uid] = 1;
                        }
                        obj.win.addNewItem(o.uid, o.name, o.login, o.online, o.avatar, o.emp);
                    } else {
                        offline.push(o);
                    }                    
                    //$('incomMessageId').options[$('incomMessageId').options.length] = new Option(o.uid, o.uid); //debug
                }
                obj.onlineCounter = onlineCounter;
                for (var i = 0; i < offline.length; i++) {                    
                    var o = offline[i];
                    obj.win.addNewItem(o.uid, o.name, o.login, o.onlie, o.avatar, o.emp);
                }
                if (Browser.opera) {
                    var viewport = obj.win.getElement('div.scroll_outer');
                    viewport.setStyle('height', null);
                    obj.win.getElement("div.scroll_inner").set("html", obj.contactsHtml);
                    var ls = obj.win.getElement("div.scroll_inner").getElements("div.b-chat__user");
                    ls.getElements("span").each( function (item) {
                        item.addEvent('click', function(e) {
                            openChatWindow(this.parentNode.getAttribute('uid'));
                        })});
                    ls.getElement("a").each( function (item) {
                        item.addEvent('click', function(e) {
                            if (qw(contactsList.win) < 60) {
                                openChatWindow(this.parentNode.getAttribute('uid'));
                                return false;
                            }
                    })});
                }
                cookie_write("q4cc_" + _UID, count);               
                $('qchcount1').set("text", count);
                var meas = "контакт";
                var active = "активных";
                var sN = String(count);
                var n = sN.charAt(sN.length - 1);
                var m = 0;
                if (sN.length > 1) {
                    m = sN.charAt(sN.length - 2);
                }
                if (n == 1) {
                    meas = "контакт";
                    active = "активный";
                }
                if (n > 1 && n < 5) {
                    meas = "контакта";
                } 
                if (n > 4) {
                    meas = "контактов";
                }
                if (m == 1 || n == 0) {
                    active = "активных";
                    meas = "контактов";
                }
                var s = " " + active + " " + meas;
                if (!$('active').checked) {
                    s = " " + meas;
                }
                $('qchcount2').set("text", count + s);
                $('qchcount3').set("text", count);
                qy(obj.win, pivot - qh(obj.win));
                var isOpen = int(cookie_read("qch_open_0"));
                var settingsIsOpen = int(cookie_read("qch_settings"));
                if (isOpen && !settingsIsOpen) {
                     maximize(obj.win);
                     if (1 == cookie_read('qch_narrow')) {
                         obj.setNarrow(!(qw(obj.win) > 50));
                     }
                }
                ChatManager.lastPivotCL = pivot; 
                //readGeometry(obj.win);
                var y = pivot - qh(obj.win);
                if (y < ChatManager.topLimit) {
                    y = ChatManager.topLimit;
                }
                qy(obj.win, y);
                var mH = getClientHeight();
                if (y + qh(obj.win) > mH) {
                    var viewport = obj.win.getElement("div.scroll_outer");
                    qh(viewport, qh(viewport) - (y + qh(obj.win) - mH));
                }
                qy(obj.win.getElement('div.scroll_inner'), scrollY);
                obj.contactScroll.reset();
                //получить локальные данные
                if (sessionStorage) {
                    var data = JSON.decode(sessionStorage.getItem("q4h"));
                    if (!data) {
                        data = {};
                        data[_UID] = [];
                    }
                    data = data[_UID];
                    if (!data) {
                        data = [];
                    }
                    var search = [];
                    for (var i = 0; i < data.length; i++) {
                        search.push(data[i].uid);
                    }
                    search = search.join(",") + ",";
                    //получить ид-шники пользователеq из списка контактов
                    var uids = [];
                    //для каждого из них выполнить поиск в локальных данных если нет, добавить номер в uids
                    obj.win.getElement("div.scroll_inner").getElements("div.b-chat__user").each(
                        function(i) {
                            var uid = int(i.get("uid"));
                            if (uid > 0 && search.indexOf(uid + ",") == -1) {
                                uids.push(uid);
                            }
                        }
                    );
                    if (uids.length) {
                        request("roster", { type:"inc", uids:uids.join(",") }, obj.onLoadHistoryContacts, null, obj);
                    }
                } else {
                    //куки
                }                
            }
            
            /**
             * Обработка истории сообщений для всех пользователей
             * */
            ChatContactsList.prototype.onLoadHistoryContacts = function(data) {
                getLocalHistory(false);
                var obj = this.self;
                for (var i = 0; i < data.length; i++) {
                    uid = data[i].uid;
                    ChatManager.localHistory.push(data[i]);
                    ChatManager.historyIsLoaded[uid] = 1;
                    var avatar = String(data[i].avatar).replace(___WDCPREFIX + "/users/" + data[i].login + "/foto/", "").replace(___WDCPREFIX.replace("https", "http") + "/users/" + data[i].login + "/foto/", "");
                    if (!avatar) {
                        avatar = 'd';
                    }
                    cookie_write("uname_" + uid, data[i].name);
                    cookie_write("login_" + uid, data[i].login);
                    cookie_write("emp_" + uid, data[i].emp?1:0);
                    cookie_write("pro_" + uid, data[i].pro?1:0);
                    cookie_write("avatar_" + uid, avatar);
                }
                if (sessionStorage) {
                    var data = JSON.decode(sessionStorage.getItem("q4h"));
                    if (!data) {
                        data = {};
                        data[_UID] = [];
                    }
                    data[_UID] = ChatManager.localHistory;
                    sessionStorage.setItem("q4h", JSON.encode(data));
                } else {
                    //куки
                }
            }
            
            /**
             * Делает окно контактов узким
             * @param Bool skipShift = false - если true то не перемещает при этом окно влево
             * */
            ChatContactsList.prototype.setNarrow = function(skipShift) {
                var win = this.win;
                win.getElement("div.b-chat__head_small").removeClass('b-chat__head_hide');
                win.getElement("div.b-chat__head_full").removeClass('b-chat__head_hide');
                win.getElement("div.b-chat__head_full").addClass('b-chat__head_hide');            
                win.getElement("div.scroll_inner").getElements("span.b-chat__name").addClass("b-chat__name_hide");
                $('settingLabel').setStyle("display", "none");
                win.setStyle("width", "50px");
                if (!skipShift) {
                    var x = qx(win) + 160;
                    if (x < 0) {
                        x = 0;
                    }
                    if (x > getClientWidth() - 50) {
                        x = getClientWidth() + 50;
                    }
                    win.setStyle("left", x + "px");
                }
                cookie_write("qch_narrow", 1);
                if ( cookie_read("qch_window_type") == "autowidth") {
                    this.win.getElements("div.b-chat__scroll-holder")[0].setStyle("display", "none");
                }
            }
            /**
             * Делает окно контактов широким
             * @param Bool skipWrite = false - если true то не записывает в куки состояние окна как широкое
             * @param Bool skipShift = false - если true то не перемещает при этом окно влево
             * */
            ChatContactsList.prototype.setWide = function(skipWrite, skipShift) {
                var win = this.win;
                if (!ChatManager.qch_open_0) {
                    return;
                }
                win.getElement("div.b-chat__head_small").removeClass('b-chat__head_hide');
                win.getElement("div.b-chat__head_full").removeClass('b-chat__head_hide');
                win.getElement("div.b-chat__head_small").addClass('b-chat__head_hide');
                win.getElement("div.scroll_inner").getElements("span.b-chat__name").removeClass("b-chat__name_hide");
                $('settingLabel').setStyle("display", null);
                win.setStyle("width", "210px");
                if (!skipShift) {
                    var x = qx(win)  - 160;
                    if (x < 0) {
                        x = 0;
                    }
                    win.setStyle("left", x + "px");
                }
                if (!skipWrite) {
                    cookie_write("qch_narrow", 0);
                }
                this.contactScroll.reset();
            }
            /**
             * Перезагрузка списка контактов
             * */
            ChatContactsList.prototype.reloadContacts = function(write_geometry) {
                if (write_geometry !== 0) {
                    writeGeometry(this.win);
                }
                var type = cookie_read("qch_contacts_type");
                var data = { type: type, online: cookie_read("qch_online") }
                request("contacts", data, this.onContactsListData, this.onFailLoadContacts, this);
            }
            //запрашиваем список контактов
            this.reloadContacts(0);
            
            this.win.getHeight = function() {
                if (!this.get("uid")) {
                    return qh(this);
                } else {
                    return qh(this.getElement("div.b-chat__body"));
                }
            }
            
            /**
             * Добавить контакт в список
             * @param uid      - идентификатор пользователя в БД
             * @param name     - имя и фамилия  пользователя
             * @param login    - логин пользователя             
             * @param isOnline - в сети ли нет
             * @param avatar   - путь к изображению
             * @param isEmpl   - работодатель ли
             * */
            this.win.addNewItem = function() {                
                this.self.addNewItem(arguments);
            }
            ChatContactsList.prototype.addNewItem = function() {
                var maxH = getClientHeight();
                var args = arguments[0];
                var div = this.win.getElement("div.scroll_inner");
                var s = this.itemTemplate;
                var login = "[" + args[2] + "]";
                var name = args[1];
                var fullName = name;
                if (name == undefined || login == undefined ) {
                    return;
                }
                /*var L = name.length + login.length + 2;
                var lim = 16;
                if (L > lim) {
                    var test = name + ' ' + login;
                    test = test.substring(0, lim);
                    var n = test.lastIndexOf("[");
                    if (n != -1) {
                        var _login = login;
                        login = test.substring(n);
                        if (test.indexOf(_login) == -1) {
                            login = login.substring(0, login.length - 2) + "...";
                        }
                    } else {
                        login = '';
                        name = test + '...';
                    }
                }/**/
                s = s.replace("{$name}", name);
                s = s.replace("{$login}", login);
                s = s.replace("{$profilelink}", "/users/" + args[2]);
                s = s.replace("{$fullname}", fullName);
                s = s.replace("{$fullname}", fullName);                
                s = s.replace("{$avatarid}", "aid_" + args[0]);
                s = s.replace("{$avatarid}", "aid_" + args[0]);
                var online = 'online';
                if (!args[3]){
                    online = 'offline';
                }
                s = s.replace("{$online}", online);
                if (___WDCPREFIX.indexOf("https") != -1 ) {
                    args[4] = args[4].replace("http://", "https://");
                }
                if (!args[4] || args[4] == 'd'){
                    args[4] = ChatManager.defaultAvatar;
                }
                s = s.replace("{$avatar}", args[4]);
                
                var usertype = 'b-chat__txt_color_6db335';
                if (!args[5]){
                    usertype = 'b-chat__txt_color_fd6c30';
                }
                s = s.replace("{$usertype}", usertype);
                
                if (!Browser.opera) {
                    var item = new Element("div", {"class":"b-chat__user"});
                    item.set("html", s);
                    item.set("uid", args[0]);
                    var prevHeight = qh(this.win);
                    if (this.win.hasClass("wnd_open")) {
                        item.getElement("span.b-chat__name").removeClass('b-chat__name_hide');
                    }
                    item.getElements("span").addEvent('click', function(e) {
                            openChatWindow(this.parentNode.getAttribute('uid'));
                    });
                    item.getElement("a").addEvent('click', function(e) {
                            if (qw(contactsList.win) < 60) {
                                openChatWindow(this.parentNode.getAttribute('uid'));
                                return false;
                            }
                    });
                    var viewport = this.win.getElement('div.scroll_outer');
                    viewport.setStyle('height', null);
                    item.inject(div, "bottom");
                    //set geometry                
                    if (this.win.hasClass("wnd_open")) {
                        resetGeometry(this.win, maxH, prevHeight);
                        this.contactScroll.reset();
                    }
                } else {
                    var prevHeight = qh(this.win);
                    if (this.win.hasClass("wnd_open")) {
                        s.replace('b-chat__name_hide', '');
                    }
                    if (this.contactsHtml == undefined) {
                        this.contactsHtml = '';
                    }
                    this.contactsHtml += '<div class="b-chat__user" uid="' + args[0] + '">' + s + '</div>';
                }
            }
            
            this.win.onmousemove = function (e) {
                if (ChatManager.settings["qch_window_type"] == "narrow") {
                    return;
                }
                ChatManager.activeTimestamp = Math.round(new Date().getTime()/1000.0); //IE8
                if (!ChatManager.wide2narrowinterval && ChatManager.qch_open_0 == 1 && ChatManager.settingsIsShow != 1) {
                    animateWide();
                }
            }
            
            this.win.removeItem = function() {
                return this.self.removeItem(arguments);
            }
            ChatContactsList.prototype.removeItem = function() {
                var args = arguments[0];
                var uid = args[0];
                var ls = this.win.getElement('div.scroll_inner').getElements('div');
                if (ls && ls.length) {
                    var innerH = qh(this.win.getElement('div.scroll_inner'));
                    var outerH = qh(this.win.getElement('div.scroll_outer'));
                    if (outerH < innerH) {//Если скролбар показан
                        var itemH = Math.round(innerH / ls.length);
                        //определяем, к какой части ленты относится удаляемый элемент
                        //верхней, средней (видимой) или нижней
                        var tapeY       = Math.abs(qy(this.win.getElement('div.scroll_inner')));
                        var topLimit    = Math.ceil(tapeY / itemH);
                        var middleLimit = Math.ceil( (tapeY + outerH)/ itemH);
                        var part = 'top';
                        for (var i = 0; i < ls.length; i++) {
                            if (ls[i].get('uid') == uid){
                                if (i > topLimit && i <= middleLimit) {
                                    part = 'middle';
                                } else if (i > middleLimit) {
                                    part = 'bottom';
                                }
                                break;
                            }
                        }
                        function tapeMoveDown(div, y, dy) {
                            var top = y + dy;
                            if (top > 0) {
                                top = 0;
                            }
                            div.getElement('div.scroll_inner').setStyle('top', top + 'px');
                            return top;
                        }
                        if (tapeY) {
                            tapeY *= -1;
                        }
                        //если относится к верхней, смещаем полосу вниз на itemH                        
                        if (part == 'top') {
                            tapeMoveDown(this.win, tapeY, itemH);
                        } else if (part == 'middle') {//если к средней и после удаления нижняя часть исчезнет
                            //если после удаления нижняя часть исчезнет
                            var h = innerH - itemH + tapeY;
                            if (h < outerH) {
                                //смещаем ленту вниз на itemH
                                var newTapeY = tapeMoveDown(this.win, tapeY, itemH);
                                //если после этого нижней части все равно не будет
                                h = innerH - itemH + newTapeY;
                                if (h < outerH) {
                                    //подгоняем высоту outer
                                    this.win.getElement('div.scroll_outer').setStyle('height', h + 'px');
                                }
                            }
                        }                        
                    } else {
                        this.contactScroll.reset(true);
                        this.win.getElement('div.b-chat__users').setStyle('height', null);
                    }
                    var prevHeight = qh(this.win);
                    this.win.getElement('div.scroll_inner').removeChild(ls[ls.length - 1]);
                    if (this.contactScroll.reset() > 0) {
                        this.win.getElement('div.b-chat__users').setStyle('height', null);
                    }
                    var height = qh(this.win);
                    if (height < prevHeight) {
                        var topN = qy(this.win) + (prevHeight - height);
                        if (topN + height < getClientHeight()) {
                            qy(this.win, topN);
                        }
                    }
                    this.contactScroll.reset();
                }
                return (uid - 1);// !! временно, нужно для отладки
            }
            /**
             * Скрывает или показывает скролбар
             * Возвращает разность высот outerDiv и innerDiv, то есть контейнера и ленты
             * */
            this.win.setScrollBarVisible = function() {
                return this.self.contactScroll.reset();
            }
            /**
             * Закрытие чата при нажатии на "Не буду пользоваться быстрочатом"
             * */
            ChatContactsList.prototype.onQChatOff = function(data) {
                if (!ChatManager.chatOffAction) {
                    return;
                }
                var self = this.self;
                if (!self) {
                    self = this;
                }
                streamReq.cancel();
                ChatManager.chatOffAction = 0;
                chatOn = 0;
                cookie_write("qch_settings", 0);
                ChatManager.settingsIsShow = 0;
                var h = qh(self.win);                
                var y = qy(self.win);                
                cookie_write("qch_y_0", y + h);
                clearInterval(ChatManager.reloadInterval);
                ChatManager.reloadInterval = 0;                
                clearInterval(ChatManager.flashingInterval);
                ChatManager.flashingInterval = 0;                
                clearInterval(ChatManager.flashingCountInterval);
                ChatManager.flashingCountInterval = 0;                
                clearInterval(ChatManager.narrowCountInterval);
                ChatManager.narrowCountInterval = 0;                
                clearInterval(ChatManager.wideNarrowInerval);
                ChatManager.wideNarrowInerval = 0;
                clearInterval(ChatManager.wide2narrowinterval);
                ChatManager.wide2narrowinterval = 0;
                ChatManager.disconnect = 1;
                for (var i in ChatManager.chatDialogs) {
                    if (ChatManager.chatDialogs[i] instanceof HTMLDivElement){
                        var p = ChatManager.chatDialogs[i].parentNode;
                        p.removeChild(ChatManager.chatDialogs[i]);
                    }
                }
                if ($('qchat_swicth')) {
                    $('qchat_swicth').set("text", "Включить");
                    $('qchat_swicth').set("onclick", "quickchat_on(); return false;");
                }
                if ($('qchat_link_wrapper')) {
                    $('qchat_link_wrapper').set("text", "отключен");
                }
                if (localStorage) {
                    localStorage.setItem("q4off", 1);
                }
                return false;
            }
            /**
             * @desc  Пометить контакт как имеющий или не имеющий новые сообщения
             * @param Number uid номер пользователя
             * @param Bool   show если true - показать признак новых сообщений, если false - скрыть
            **/
            ChatContactsList.prototype.contactHasNewMsg = function(uid, show) {
                var o = this.win.getElement("div.b-chat__user[uid=" + uid + "]");
                if (o) {
                    o = o.getElement("span.b-chat__mess");
                    if (o) {
                        o.setStyle("display", show ? null : "none" );
                    }
                }
            }
            //получение из кук положения окна контактов
            readGeometry(this.win);
            writeGeometry(this.win);             //записываем геометрию в куки 
            
            $('sndOn').addEvent("click", function() {
                $('chatsoundoff') .removeClass("b-chat__txt_hide");
                $('chatsoundon')  .removeClass("b-chat__txt_hide");
                $('chatsoundon')  .addClass("b-chat__txt_hide");
                //cookie_write("qch_sound", true);
                ChatManager.settings["qch_sound"] = 1;
                return false;
            });
            $('sndOff').addEvent("click", function() {
                $('chatsoundoff') .removeClass("b-chat__txt_hide");
                $('chatsoundon')  .removeClass("b-chat__txt_hide");
                $('chatsoundoff') .addClass("b-chat__txt_hide");
                //cookie_write("qch_sound", false);
                ChatManager.settings["qch_sound"] = 0;
                return false;
            });
            $("narrow").addEvent("click", qch_window_type);
            $("wide").addEvent("click", qch_window_type);
            $("autowidth").addEvent("click", qch_window_type);
            function qch_window_type() {
                if (this.checked) {
                    //cookie_write("qch_window_type", this.id);
                    ChatManager.settings["qch_window_type"] = this.id;
                }
            }
            
            $("all_contacts").onchange = qch_contacts_type;
            $("active").onchange = qch_contacts_type;
            function qch_contacts_type() {
                if (this.checked) {
                    //cookie_write("qch_contacts_type", this.id);
                    ChatManager.settings["qch_contacts_type"] = this.id;
                }
            }
            
            $("online").onchange = function () {
                    //cookie_write("qch_online", this.checked);
                    ChatManager.settings["qch_online"] = this.checked?1:0;
            }
            
            $("qchoff").self = this;            
            $("qchoff").onclick = quickchat_off;
        }//END OF Class contactsList
        
        /**
         * Отключение чата
         * */
        QuickChat.prototype.chatDeactivate = function () {
            if (!ChatManager.chatOffAction) {
                ChatManager.chatOffAction = 1;
                if (!confirm("Вы сможете снова включить чат на странице контактов.\nОтключить чат?")) {
                    ChatManager.chatOffAction = 0;
                    return;
                }
                if ( typeof streamReq != 'undefined' ) {
                    streamReq.cancel();
                }
                request("settings", {chat:0}, contactsList.onQChatOff, null, contactsList);
            }
        }
        
        /**
         * Класс для реализации окна диалога чата
         * */
        function ChatDialog(uid) {
            this.isproFTpl = '<span class="b-icon b-icon__pro b-icon__pro_f"></span>';
            this.isproETpl = '<span class="b-icon b-icon__pro b-icon__pro_e"></span>';
            this.winTemplate = '<div class="b-chat__inner"' + (Browser.opera?'style="background:#909090 !important"':'') + '>\
        <span class="b-chat__resize resize_top"></span>\
        <div class="b-chat__body">\
            <div class="b-chat__head" style="{$browser}user-select: none;">\
                <span class="b-chat__icon b-chat__icon_close"></span>\
                <div class="b-user b-user_padlr_15_5 user_name_login">\
                    <a class="b-user__link b-user__link_bold b-user__link_fontsize_11 {$userclr}" title="{$uname}" href="{$profilelink}">\
                        {$uname}\
                    </a>\
                    {$pro}\
                </div>\
            </div>\
            <div class="b-chat__talk b-chat__users scroll_outer">\
                    <div class="scroll_inner" style="position:relative; {$browser}user-select: text;">\
                    </div>\
                <div class="b-chat__scroll-holder" style="display:none; {$browser}user-select: none;"><div class="b-chat__scroll"></div></div>\
            </div>\
            <div class="b-chat__talk" style="padding-top:15px" style="{$browser}user-select: none;">\
                <div class="b-qfrm b-qfrm_margbot_15 js-tareadiv">\
                                        <img class="b-qfrm__avatar b-chat_av_id_' + _UID + '"  src="{$avatar}"  alt="{$name}" title="{$name}" width="16" height="16" onerror="qchat_onavatar_error(' + _UID + ', 1)"/>\
                                        <textarea class="b-qfrm__text" name="" cols="" rows="" >Ваш ответ...</textarea>\
                                        <div class="b-qfrm__btn"><span class="b-qfrm__enter"></span></div>\
                </div>\
            </div>\
        </div>\
    </div>';

            this.itemTemplate = '<span style="{$browser}user-select: text;" class="selectable_tag"><img class="b-chat__userpic b-chat__userpic_margleft_-21 b-chat_av_id_{$aid}" src="{$img}" onerror="qchat_onavatar_error(\'{$aid}\', true)" alt="{$name}" title="{$name}" width="16" height="16" />{$text}<div><span class="filelist">{$files}</span><span class="b-chat__time js-display-time" style="{$browser}user-select: none;" onselectstart="{return false;}" data-rawtime="{$rawtime}">{$time}</span></div></span>';
            
            this.selfAvatar    = ChatManager.avatar;
            this.uname         = ChatManager.name;
            this.login         = ChatManager.login;
            
            this.contactLogin  = cookie_read("login_" + uid);
            this.contactIsPro  = cookie_read("pro_" + uid);
            this.contactIsEmp  = cookie_read("emp_" + uid);
            var avatar = cookie_read("avatar_" + uid);
            if (avatar.indexOf("/http") != -1) {
                cookie_write("avatar_" + uid, "");
                avatar = '';
                sessionStorage.clear();
            }
            this.contactAvatar = (avatar == 'd' || avatar == '')? ChatManager.defaultAvatar : ___WDCPREFIX + "/users/" + this.contactLogin + "/foto/" + avatar;
            this.contactUname  = cookie_read("uname_" + uid);
            
            this.uid = uid;
            
            ChatDialog.prototype.correctPlace = function () {
                var win = this.win;
                var dlgs = ChatManager.chatDialogs;                
                var delta = 5;
                function checkIntersect(win, x, y, w, h, dlgs, intersectedList) {
                    
                    function intersect(x, y, w, h, div) {
                        var x1 = x;
                        var x2 = x + w;
                        var y1 = y;
                        var y2 = y + h;
                        
                        var a1 = qx(div);
                        var a2 = qx(div) + qw(div);
                        var b2 = qy(div) + div.getHeight();
                        var b1 = qy(div);
                        
                        if (x2 > getClientWidth() ) return true;
                        if (y2 > getClientHeight() ) return true;
                        if (x1 < 0) return true;
                        if (y1 < ChatManager.topLimit) return true;
                        
                        if ( ( (a1<=x1 && x1<=a2) || (x1<=a1 && a1<=x2) ) && ( (b1<=y1 && y1<=b2) || (y1<=b1 && b1<=y2) ) ) {
                            return true;
                        }
                        return false;
                    }
                    
                    for (var i in dlgs) {
                        if (dlgs[i] instanceof HTMLDivElement && win.get("uid") != dlgs[i].get("uid")) {
                            if (intersect(x, y, w, h, dlgs[i])) {
                                if (intersectedList instanceof Array) {
                                    intersectedList.push(dlgs[i]);
                                }
                                return true;
                            }
                        }
                    }
                    return false;
                }
                
                var x = qx(win);
                var y = qy(win);
                var w = qw(win);
                var h = win.getHeight();
                var intersectedList = []; //сюда помещаем ссылку на окно, с которым пересеклись.
                if (checkIntersect(win, x, y, w, h, dlgs, intersectedList)) {
                    for (var j = 0; j < Math.floor(getClientHeight() / h); j++) {
                        for (var i = 0; i < Math.floor(getClientWidth() / w); i++) {
                            if (!checkIntersect(win, x + i*(w + delta), y - j*(h + delta), w, h, dlgs, intersectedList)) {
                                qx(win, x + i*(w + delta));
                                qy(win, y - j*(h + delta));
                                resetWndTop(win);
                                return;
                            }
                        }                        
                        for (var i = 0; i < intersectedList.length; i++) {
                            var div = intersectedList[i];
                            if (!checkIntersect(win, qx(div), qy(div) - delta - win.getHeight(), w, h, dlgs)) {
                                qx(win, qx(div));
                                qy(win, qy(div) - delta - win.getHeight());
                                resetWndTop(win);
                                return;
                            }
                        }
                        intersectedList = [];
                    }
               }
             }
            
            ChatDialog.prototype.loadHistory = function () {
                var data = getLocalHistory(this.uid);
                if (data) {
                    this.onLoadHistory(data);
                } else {
                    request("history", { uid: this.uid, maxId:ChatManager.historyMaxId[this.uid]?ChatManager.historyMaxId[this.uid]: null}, this.onLoadHistory, null, this);
                }
            }
            /**
             * Обработка истории сообщений для отдельного пользователя
             * */
            ChatDialog.prototype.onLoadHistory = function(data) {
                var obj = this.self;
                if (!obj) {
                    obj = this;
                }
                for (var i = 0; i < data.length; i++) {
                    var o = data[i];
                    obj.win.addNewItem(o.text, o.files, o.incoming, o.time, 0, 0, o.id);                    
                }
                if (ChatManager.playSound[obj.uid] == 1) {
                    obj.playSound();
                    ChatManager.playSound[obj.uid] = 0;
                    $$('.b-chat_active').removeClass("b-chat_active");
                    ChatManager.blueBorderWnd = obj.uid;
                }
                var s = cookie_read("qchlastmsg_" + obj.uid);                
                var t = obj.win.getElement('textarea');
                if (s) {                    
                    t.value = s;                    
                }
                var lex= "Ваш ответ...";
                if (t.value == lex) {
                    t.value = '';
                }
                obj.textareaResize(t);
                if (t.value == '') {
                    t.value = lex;
                }
                if (qy(obj.win) + obj.win.getHeight() > getClientHeight()) {
                    qy(obj.win, getClientHeight() - obj.win.getHeight());
                }
                ChatManager.historyIsLoaded[obj.uid] = 1;
                if (ChatManager.incomeCache[obj.uid] instanceof Array) for (var i = 0; i < ChatManager.incomeCache[obj.uid].length; i++) {
                    var o = ChatManager.incomeCache[obj.uid][i];
                    incomingMessage(o.text, o.uid, o.files, o.date, o.soundOn, o.reciever_id, o.msg_id);
                }
                ChatManager.incomeCache[obj.uid] = [];
            }

            ChatDialog.prototype.createWindow = function (visible) {
                if (visible) {
                    visible = "";
                } else {
                    visible = "display:none";
                }
                var uid = this.uid;
                var winHeight = ChatManager.maxDialogHeight;
                var x = ChatManager.padding; 
                var y = getClientHeight() - winHeight - ChatManager.padding;
                ChatManager.maxZ++;
                var s = this.winTemplate;
                var browser = '-webkit-';
                if (Browser.ie) {
                    browser = '-ms-';
                }
                if (Browser.firefox) {
                    browser = '-moz-';
                }
                this.browser = browser;
                s = s.replace(/\{\$browser\}/g, browser);
                s = s.replace(/\{\$name\}/g, this.uname + " [" + this.login + "]");
                
                s = s.replace(/\{\$uname\}/g, this.contactUname);
                s = s.replace(/\{\$ulogin\}/g, this.contactLogin);
                s = s.replace(/\{\$profilelink\}/g, "/users/" + this.contactLogin);
                s = s.replace("{$pro}", (this.contactIsPro == 1)?((this.contactIsEmp == 1)?this.isproETpl:this.isproFTpl):'');
                s = s.replace("{$userclr}", (this.contactIsEmp == 1)?"b-user__link_color_55b02e":"b-user__link_color_ec6706");
                if (___WDCPREFIX.indexOf("https://") != -1 ) {
                    this.selfAvatar = this.selfAvatar.replace("http://", "https://");
                }
                s = s.replace("{$avatar}", this.selfAvatar);
                s = s.replace("{$selfuid}", _UID);
                this.win = new Element("div", {"class":"b-chat b-chat_width_270 wnd_open", "html":s, "style":"position:fixed; top:" + y + "px; left:" + x + "px; z-index:" + ChatManager.maxZ + ";" + visible});
                this.win.set('maxH', ChatManager.maxDialogHeight);
                this.win.set('uid', uid);
                //получениe из кук положения и высоты окна, ифла пользователя[, история сообщений?]
                this.win.inject(document.getElementsByTagName('body')[0], 'bottom');
                qy(this.win, getClientHeight() - ChatManager.padding - qh(this.win));
                this.win.set("x1", x);
                this.win.set("y1", qy(this.win));                
                this.win.getElement("textarea").setStyle("height", "17px").setStyle("min-height", "17px");
                if (!readGeometry(this.win) && visible == '') {
                    this.correctPlace();
                }                
                setChatWindowBehavior(this.win);     //делаем див таскаемым, растягиваемым и т. п.
                this.scrollbar  = new VScrollBar(this.win.getElement('div.scroll_outer'), this.win.getElement('div.scroll_inner'), this.win.getElement('div.scroll_outer').getElement('div.b-chat__scroll'));
                this.win.scrollbars = [this.scrollbar];
                this.win.getElement("textarea").self = this.win.self = this;
                ChatManager.addDialog(uid, this.win);                
                var flicUid = cookie_read("qch_flick_wnd");
                if (flicUid == uid) {
                    ChatManager.blueBorderWnd = uid;
                }
                this.win.getElement("textarea").onkeyup = function(e) {
                    if (!e) {
                        e = window.event;
                    }
                    this.self.textareaOnKeyUp(e);                    
                };

                this.win.getElement("textarea").onfocus = function(e) {
                    this.self.win.getElement("div.b-qfrm").removeClass("b-qfrm_current").addClass("b-qfrm_current");
                    if (this.value == 'Ваш ответ...') {
                        this.value = '';
                        this.setStyle("height", "17px");
                        this.setStyle("min-height", "17px");
                    }else {
                        this.setStyle("height", null);
                        this.setStyle("min-height", null);
                        this.self.textareaResize(this);                        
                    }
                };
                
                this.win.getElement("textarea").onblur = function(e) {
                    var win = this.self.win;
                    var h = win.getHeight();
                    var y = qy(win) + h;
                    var prevH = qh(win.getElement("div.scroll_outer"));
                    win.getElement("div.b-qfrm").removeClass("b-qfrm_current");                                        
                    if (this.value == '') {
                        this.value = 'Ваш ответ...';                        
                        this.setStyle("height", "17px");
                        this.setStyle("min-height", "17px");
                        qh(win.getElement("div.js-tareadiv"), 31);
                    }
                    var viewportH = prevH + (h - win.getHeight());                    
                    qh(win.getElement("div.scroll_outer"), viewportH);
                    win.scrollbars[0].reset(0, 1);
                    qy(win, y -win.getHeight());
                };
                
                this.win.getElement("textarea").onkeydown = function(e) {
                    if (!e) {
                        e = window.event;
                    }
                    return this.self.textareaOnKeyDown(e);
                };
                this.win.getElement("span.b-qfrm__enter").self = this;
                this.win.getElement("span.b-qfrm__enter").onclick = function(e) {
                    this.self.sendMessage(this.self.win.getElement("textarea").value);                    
                };                
                
                /**
                 * Добавить новое сообщение
                 * @param String text     - текст сообщения
                 * @param Array  files    - массив объектов [{иконка, ссылка на файл}]
                 * @param bool   incoming - true если входящее
                 * @param string time     - вроемя сообщения
                 * @param bool   soundOff - воспроизводить ли звук (если входящее сообщение в несколько открытых браузерах)
                 * @param bool   isErrorMsg - выводить ли сообщение как ошибку 
                 * @param bool   msg_id    -  идентификатор сообщения
                 * */
                this.win.addNewItem = function() {
                    this.self.addNewItem(arguments);
                }
                
                /**
                 * Скрывает или показывает скролбар
                 * Возвращает разность высот outerDiv и innerDiv, то есть контейнера и ленты
                 * */
                this.win.setScrollBarVisible = function() {
                    return this.self.scrollbar.reset();
                }                
            }
            
            ChatDialog.prototype.onUserData = function (data) {
                var obj = this.self;
                obj.contactAvatar = data.avatar?data.avatar : ChatManager.defaultAvatar;
                obj.contactUname = data.name;
                obj.contactLogin = data.login;
                obj.contactIsPro = data.pro?1:0;
                obj.contactIsEmp = data.emp?1:0;
                //here append history
                if (sessionStorage) {
                    var localHistory = getLocalHistory(obj.uid);
                    if (! (localHistory instanceof Array) ) {
                        localHistory = [];
                        ChatManager.localHistory.push({uid:obj.uid, login:obj.contactLogin, name:obj.contactUname, avatar:obj.contactAvatar, emp:obj.contactIsEmp, pro:obj.contactIsPro, history:localHistory});
                    }
                    if (data.history) {
                        for (var i = 0; i < data.history.length; i++) {
                            var o = data.history[i];
                            var f1 = (localHistory.length == 0); 
                            var f2 = ((localHistory[localHistory.length - 1] instanceof Object) && int(localHistory[localHistory.length - 1].id) > 0 && localHistory[localHistory.length - 1].id < o.id);
                            if (f1 || f2) {      
                                localHistory.push(o);
                            }
                        }
                        var hdata = JSON.decode(sessionStorage.getItem("q4h"));
                        if (!hdata) {
                            hdata = {};
                            hdata[_UID] = [];
                        }
                        hdata[_UID] = ChatManager.localHistory;
                        sessionStorage.setItem("q4h", JSON.encode(hdata));
                    }
                }
                //  / here append history
                obj.loadHistory();
                cookie_write("pro_"  + data.uid, obj.contactIsPro);
                cookie_write("emp_"  + data.uid, obj.contactIsEmp);
                cookie_write("avatar_" + data.uid, String(data.avatar).replace(___WDCPREFIX + "/users/" + data.login + "/foto/", "").replace(___WDCPREFIX.replace("https", "http") + "/users/" + data.login + "/foto/", ""));
                cookie_write("uname_"  + data.uid, data.name);
                cookie_write("login_"  + data.uid, data.login);
                //obj.createWindow();                
                var win = obj.win;
                //avatar
                win.getElement(".scroll_inner").getElements("img.b-chat__userpic").each(
                    function (img) {
                        if (img.parentNode.parentNode.className.indexOf("b-chat__txt_color_41") != -1) {
                            img.src = obj.contactAvatar;
                            img.alt = obj.contactLogin;
                            img.title = obj.contactUname + " [" + obj.contactLogin + "]";
                        } else {
                            img.src = obj.selfAvatar;
                        }
                    }
                );
                //username and pro and emp
                var html = '<a class="b-user__link b-user__link_bold" title="' + obj.contactUname + '" href="/users/' + obj.contactLogin + '">\
                            ' + obj.contactUname + ' <span class="b-user__login ' + ((obj.contactIsEmp == 1)?"b-user__login_color_6db335":"b-user__login_color_fd6c30") + '"><span class="b-user__login-name">' + obj.contactLogin + '</span></span>\
                            </a>\
                            ' + ((obj.contactIsPro == 1)?((obj.contactIsEmp == 1)?obj.isproETpl:obj.isproFTpl):'');
                win.getElement("div.user_name_login").set("html", html);                
                win.setStyle("display", null);
                obj.correctPlace();
            }
            /**
             * Возвращает, сколько прошло времени с момента datetime до Date.now()
             * */
            ChatDialog.prototype.getTime = function(datetime) {
                
                if (!datetime) {
                    return '';
                }
                var dt = datetime.split(" ");
                var rawDate = dt[0];
                var time = dt[1].split(":");
                var h = time[0];
                var i = time[1];
                var s = time[2];
                time = time.join(":");
                var date = rawDate.split("-");
                dt = new Date();
                var sdt = new Date(rawDate);
                sdt.setHours(int(h), int(i), int(s));
                var diff = Math.round( (dt.getTime() - sdt.getTime() ) / 1000);
                var days = Math.floor(diff / (24*3600) );
                if (days > 0) { 
                    var N = Math.floor(days / 31);
                    if ( N > 0) {
                        var Y = Math.floor(days / 365);
                        if ( Y > 0) {
                        //верну количество лет
                        return Y + " " + getSuffix(Y, "", "год", "года", "лет") + " назад";
                        }
                        //верну количество месяцев
                        return N + " " + getSuffix(N, "месяц", "", "а", "ев") + " назад";
                    }
                    //верну количество дней
                    return days + " " + getSuffix(days, "", "день", "дня", "дней") + " назад";
                }
                var hours = Math.floor(diff / 3600); //кол-во полных часов
                if (hours > 0) {
                    return hours + " " + getSuffix(hours, "час", "", "а", "ов") + " назад";
                }
                var m = Math.floor(diff / 60); //кол-во полных минут
                if (m > 0) {
                    return m + " " + getSuffix(m, "минут", "у", "ы", "") + " назад";
                }
                return diff + " " + getSuffix(diff, "секунд", "у", "ы", "") + " назад";
            }
            /**
             * Делает поле ввода недоступным для ввода
             * */
            ChatDialog.prototype.disableTextarea = function() {
                this.win.getElement("textarea").disabled = true;
                this.win.getElement("textarea").value = "Отправка сообщений этому пользователю запрещена";
                this.win.getElement("textarea").className = "b-qfrm__text b-qfrm__text_color_c10600";                    
                this.win.getElement("div.b-qfrm_margbot_15").removeClass("b-qfrm_current");
                this.win.getElement("div.b-qfrm_margbot_15").addClass("b-qfrm_disabled");
                this.win.getElement("div.b-qfrm__right").removeClass("b-qfrm__right_btn");
                this.win.getElement("div.b-qfrm__btn").setStyle("display", "none");
            }
            /**
                 * Добавить новое сообщение
                 * @param Array [0     - текст сообщения
                 *               1    - массив объектов [{иконка, ссылка на файл}]
                 *               2 - true если входящее
                 *               3 - время сообщения
                 *               4 - воспроизводить ли звук (если входящее сообщение в несколько открытых браузерах)
                 *               5 - выводить ли сообщение как ошибку 
                 *               6   -  идентификатор сообщения
                 *             ]
                 * */
            ChatDialog.prototype.addNewItem = function() {
                var args = arguments[0];
                var playSound = args[4];
                if (!int(args[6])) {
                    alert("Не указан ид сообщения!");
                }
                if ($("q4msg" + args[6])){
                    return;
                }    
                if (playSound != 0) {
                    playSound = 1;
                } else {
                    playSound = 0;
                }
                var maxH = this.win.get('maxH');
                var args = arguments[0];
                var tape = this.win.getElement("div.scroll_inner");
                var selfCss = '';
                var name = this.uname + " [" + this.login + "]";
                if (args[2]) {
                    selfCss = '  b-chat__txt_color_41';
                    name = this.contactUname + " [" + this.contactLogin + "]";
                }
                if (args[5]) {
                    selfCss += " b-chat__txt_color_c10600";
                }
                var item = new Element("div", {"class":"b-chat__txt b-chat__txt_padleft_21 b-chat__txt_padbot_10" + selfCss, id:"q4msg" + args[6]});
                var s = this.itemTemplate;
                s = s.replace(/\{\$browser\}/g, this.browser);
                s = s.replace("{$time}", this.getTime(args[3]));
                s = s.replace("{$rawtime}", args[3]);
                if (___WDCPREFIX.indexOf("https://") != -1 ) {
                    this.selfAvatar = this.selfAvatar.replace("http://", "https://");
                    this.contactAvatar = this.contactAvatar.replace("http://", "https://");
                }
                var img = this.selfAvatar;
                var avid = _UID;
                if (args[2]) {
                    img = this.contactAvatar;
                    if (img.indexOf("/http") != -1) {
                        cookie_write("avatar_" + this.uid, "");
                        sessionStorage.clear();
                    }
                    avid = this.uid;
                }                
                var text = args[0];
                
                s = s.replace("{$img}", img);
                s = s.replace("{$aid}", avid);
                s = s.replace("{$aid}", avid);
                s = s.replace("{$img}", img);
                s = s.replace("{$name}", name);
                s = s.replace("{$name}", name);
                s = s.replace("{$text}", text);
                //------------------------------------------------------
                var files = args[1];
                
                function getExt(src) {
                    var arr = src.split("?");
                    arr = arr[0].split(".");
                    ext = arr[arr.length - 1];
                    ext = ext.toLowerCase();
                    if (ext == "jpg" || ext == "jpe") {
                        ext = "jpeg";
                    }
                    ext = "/images/ico_" + ext + ".gif";
                    return ext;
                }
                if (files instanceof Array) {
                    var filesTpl = [];
                    for (var i = 0; i < files.length; i++) {
                        filesTpl.push('<li><img src="' +  getExt(files[i].link) + '" >&nbsp;&nbsp;&nbsp;<a style="vertical-align:middle; padding-bottom:16px;" href="' + files[i].link + '" target="_blank">' + files[i].filename + '</a></li>');
                    }
                    filesTpl = '<ul style="list-style:none;padding:0px;margin:0">' + filesTpl.join('\n') + '</ul>';
                    s = s.replace('{$files}', filesTpl);
                } else {
                    s = s.replace('{$files}', '');
                }
                //------------------------------------------------------
                if (args[5]) {
                    s += '<span class="b-chat__icon b-chat__icon_close"></span>';
                }
                item.set("html", s);
                item.self = this;
                item.getElements("img").addEvent("error", function() {
                    if (this.className.indexOf("b-chat__userpic") == -1) {
                        this.src = "/images/ico_unknown.gif";
                    }
                });
                var removeBtn = item.getElement("span.b-chat__icon_close");
                if (removeBtn) {
                    removeBtn.addEvent("click", function() {
                        var r = this.parentNode;
                        var h = qh(r);
                        var c = r.parentNode;                        
                        var o = r.self;
                        c.removeChild(r);
                        var viewport = o.win.getElement("div.scroll_outer");
                        var y = qy(o.win) + o.win.getHeight();
                        qh(viewport, qh(viewport) - h);
                        qy(o.win, y - o.win.getHeight());
                        o.scrollbar.reset();                        
                    });
                }
                var prevHeight = qh(this.win);
                var viewport = this.win.getElement('div.scroll_outer');
                var prevViewportHeight = qh(viewport);
                item.inject(tape, "bottom");
                item.getElement("span.js-display-time").self = this;
                if (args[5]) {
                    var img = item.getElement("img.b-chat__userpic");
                    var p = img.parentNode;
                    p.removeChild(img);
                    if (ChatManager.lastNumError == 4) {
                        this.disableTextarea();
                    }
                }
                
                var h = qh(viewport);
                if (!h) {
                    h = 0;
                }
                var iH = qh(tape);
                if (!iH) {
                    iH = 0;
                }
                viewport.setStyle('height', null);
                //set geometry
                if (this.win.hasClass("wnd_open")) {
                    resetGeometry(this.win, maxH, prevHeight, prevViewportHeight);
                }
                var top = Number(h - iH);
                qy(tape, top);
                this.scrollbar.reset();
                if (args[2] && playSound != 0) {
                    this.playSound();
                }
                this.win.removeClass("b-chat_active");
                swapZIndex(this.win);
                this.textareaResize(this.win.getElement("textarea"), 1);
                //append in local history
                if (sessionStorage) {
                    var localHistory = getLocalHistory(this.uid);
                    if (! (localHistory instanceof Array) ) {
                        localHistory = [];
                        ChatManager.localHistory.push({uid:this.uid, login:this.contactLogin, name:this.contactUname, avatar:this.contactAvatar, emp:this.contactIsEmp, pro:this.contactIsPro, history:localHistory});
                    }
                    var f1 = (localHistory.length == 0); 
                    var f2 = ((localHistory[localHistory.length - 1] instanceof Object) && int(localHistory[localHistory.length - 1].id) > 0 && localHistory[localHistory.length - 1].id < int(args[6]));
                    if (f1 || f2) {      
                        var o = {id:int(args[6]), time:args[3], incoming:args[2], files:args[1], text:args[0]};
                        localHistory.push(o);
                    }
                    var data = JSON.decode(sessionStorage.getItem("q4h"));
                    if (!data) {
                        data = {};
                        data[_UID] = [];
                    }
                    data[_UID] = ChatManager.localHistory;
                    sessionStorage.setItem("q4h", JSON.encode(data));
                }
            }
            
            /**
             * Воспроизведение звука
             * */
            ChatDialog.prototype.playSound = function() {
                if (cookie_read('qch_sound') != 0) {
                    if (Browser.ie && Browser.version < 9) {
                        try {
                            $('qchsnd').qch_playAudio();
                            } catch(e){;}
                    } else {
                        $('qchsnd').play();
                    }
                }
            }
            
            /**
             * Ресайзит поле ввода по вертикали
             * @param Textarea textarea
             * @return int изменение по высоте после ресайза
             * */
            ChatDialog.prototype.textareaResize = function(textarea) {
                var win = this.win;
                var pivotY = qy(win) + win.getHeight();
                var prevWinH = qh(win);
                var t = textarea;
                var srcTextH = qh(t.parentNode);         //высота контейнера с textarea
                var textDivH = this.getMessageHeight(t); //высота области с текстовым полем
                var dY = textDivH - srcTextH;            //на сколько надо изменить высоту окна 
                //насколько реально надо изменить высоту окна
                var scrollBlock = win.getElement("div.scroll_outer");
                var tape         = win.getElement("div.scroll_inner");
                var scrollH = qh(scrollBlock);
                var tapeH   = qh(tape);
                var scrollDy = 0;                         //на сколько надо изменить длину скрола
                var storeDy = dY;
                if (scrollH - tapeH > 0) { //
                    scrollDy = dY;         //
                    dY = 0;
                    if ( (scrollH - tapeH) < dY ) {
                        scrollDy = storeDy - (scrollH - tapeH);
                        dY       = storeDy - scrollDy;
                    }
                }
                if (qy(win) - dY <= ChatManager.topLimit) {
                    scrollDy += dY;
                    dY = 0;
                }
                if (scrollDy != 0 || dY != 0) {
                    if (scrollDy != 0) {                        
                        qh(scrollBlock, scrollH - scrollDy);                        
                        this.scrollbar.reset(0, 1);
                    }
                    if (dY != 0) {
                        qy(win, qy(win) - dY);
                    }
                }else {
                    if (dY != 0) {
                        qy(win, qy(win) - dY);
                    }
                }
                t.parentNode.style.height = textDivH + "px";
                t.setStyle("height", "100%");
                //-------------control----------------------------------
                //контроль style:top
                qy(win, pivotY - win.getHeight());
            }
            ChatDialog.prototype.getMessageHeight = function (textarea) {
                if (Browser.ie || Browser.opera) {
                    return this.getMessageHeightIe(textarea);
                }
                return this.getMessageHeightGecko(textarea);
            }
            ChatDialog.prototype.getMessageHeightGecko = function (textarea) {
                var t = textarea;
                var v = '';
                var j = 0;
                for (var i = 0; i < t.value.length; i++) {
                    if (t.value.charAt(i) != "\n") {
                        if (i % 2 == 0) {
                            v += " ";
                        } else {
                            if (j % 3 != 0) {
                                v += "Щ";
                            } else {
                                v += t.value.charAt(i);
                            }
                            j++;
                        }
                    } else {
                        v += t.value.charAt(i);
                    }
                }
                var maxW = 198;
                var dY1 = 0;
                var div = new Element("div", {style:"border: 1px solid #000; font-family:" + t.getStyle("font-family") + "; font-size:" + t.getStyle("font-size") + ";width:" + maxW + "px" + ";max-width:" + maxW + "px;line-height:" + t.getStyle("line-height")});
                div.inject(document.getElementsByTagName("body")[0], "top");
                var v1 = v.replace(/\n/g, "<br/>&nbsp;");
                div.set("html", v1);
                var h = qh(div) + dY1;
                if (h > 300) {
                    t.setStyle("overflow", "auto");
                    h = 300;
                } else {
                    t.setStyle("overflow", "hidden");
                }
                if (h < 17) {
                    h = 17;
                }
                h += 14;
                var p = div.parentNode;
                p.removeChild(div);
                return h;
            }
            
            ChatDialog.prototype.getMessageHeightIe = function (textarea) {
                var t = textarea;
                var charW = 12;  //допустим, 12
                var taW   = 192;//17*12; //ширина области ввода
                var lineH = 12; //int( t.getStyle("line-height") );
                //сколько переносов строк?
                var arr = t.value.split("\n");
                var caretN = arr.length;
                var h = caretN;
                //сколько строк занимает каждая реальная строка?
                for (var i = 0; i < caretN; i++) {
                    var w = charW * arr[i].length;
                    w -= (arr[i].split(' ').length) * charW;
                    w += (arr[i].split(' ').length) * 4;
                    h += Math.floor( w / taW );
                }
                h = (h - 1) * lineH;
                if (h > 300) {
                    t.setStyle("overflow", "auto");
                    h = 300;
                } else {
                    t.setStyle("overflow", "hidden");
                }
                if (h < 17) {
                    h = 17;
                }
                h += 14;
                return h;
            }
            
            
            ChatDialog.prototype.textareaOnKeyUp = function(e) {
                if (this.emptyMessage == true) {
                    this.emptyMessage = false;
                    return false;
                }
                if (!e) {
                    e = window.event;
                }
                var t = this.win.getElement("textarea");
                cookie_write("qchlastmsg_" + this.uid, t.value);                
                this.textareaResize(t); //увеличить область с textarea
            }
            
            /**
             * Обработка нажатия клавиши Enter
             * */
            ChatDialog.prototype.textareaOnKeyDown = function(e) {
                if (e.keyCode == 13) {
                    if (this.win.getElement("textarea").value.trim() == '') {
                        this.emptyMessage = true;
                        return false;
                    }
                    if (!e.ctrlKey && !e.shiftKey) {
                        var ta = this.win.getElement("textarea");
                        this.sendMessage(ta.value);
                        ta.value = '';
                        return false;
                    } else {
                        this.textareaResize(this.win.getElement("textarea"), 1);
                        if (!e.shiftKey && e.ctrlKey) {
                            this.win.getElement("textarea").value += "\n";
                        }
                        return true;
                    }
                } else {
                     this.textareaResize(this.win.getElement("textarea"), 1);
                }
                return true;
            }            
            /**
             * Отправка сообщения
             * */
            ChatDialog.prototype.sendMessage = function(msg) {
                if (this.win.getElement("textarea").value == 'Ваш ответ...') {
                    return;
                }
                this.winHeightBeforeSendMsg = this.win.getHeight();
                var uid = this.win.get("uid");
                if (!(this.sended instanceof Array)) {
                    this.sended = new Array();
                }
                var data = {
                        uid:  this.uid,
                        text: this.win.getElement("textarea").value
                    }
                if (this.sended[uid] != 1) {
                    this.sended[uid] = 1;                    
                    ChatManager.sendedMsg[this.uid] = data.text;
                    request("send" , data, this.onSendMsg, this.onSendMsgError, this);
                    this.win.getElement("textarea").value = '';
                } else {
                    if (! (ChatManager.queue[this.uid] instanceof Array)) {
                        ChatManager.queue[this.uid] = new Array();
                    }
                    ChatManager.queue[this.uid].push(data);
                }
            }
            /**
             * 
             * */
            ChatDialog.prototype.onSendMsgError = function() {
                var s = cookie_read("qchlastmsg_" + this.self.uid);
                var win = this.self.win;
                win.addNewItem("Ошибка при попытке отправить сообщение. Попробуйте еще раз.", '', 0, '', 0, true, -1);
                var ta = win.getElement("textarea");
                ta.value = s;
                this.self.textareaResize(ta);
                this.self.obj.sended[this.self.uid] = 0;
            }
            
            /**
             * Обработка успешной отправки сообщения
             * */
            ChatDialog.prototype.onSendMsg = function(data) {
                var obj = this.self;
                cookie_write("qchlastmsg_" + obj.uid, '');
                if (data[0] && data[0].func && data[0].func == 'error') {
                    ChatManager.errorDialog = ChatManager.chatDialogs[uid];                    
                    with (data[0].attr) {                        
                        error(num, text, die);
                    }
                    this.self.sended[uid] = 0;
                    return;
                }                
                cookie_write("qchlastmsg_" + obj.uid, '');
                if (data.forbidden == 1) {                    
                    obj.addNewItem(["Пользователь добавил вас в игнор, оптравка сообщений запрещена.", 0, true, 0, false, true, -1]);
                    return;
                }
                ChatManager.sendedMsg[obj.uid] = '';
                obj.sended[obj.uid] = 0;
                obj.win.getElement("textarea").value = '';
                var t = obj.win.getElement("textarea");
                obj.textareaResize(t, 1);
                if (obj.winHeightBeforeSendMsg > obj.win.getHeight()) {
                    var pivotY = qy(obj.win) + obj.win.getHeight();
                    var scrollArea = obj.win.getElement("div.scroll_outer");
                    obj.win.getElement("div.b-chat__body").setStyle("height",null);                    
                    qh(scrollArea, qh(scrollArea) + obj.winHeightBeforeSendMsg - obj.win.getHeight());                    
                    obj.scrollbar.reset(0, 1);
                    qy(obj.win, pivotY - obj.win.getHeight());
                }
                cookie_write('qchlastmsg_' + obj.uid, '');
                
                obj.sended[uid] = 0;
                if ((ChatManager.queue[uid] instanceof Array)) {
                    if (ChatManager.queue[uid].length) {
                        var data = ChatManager.queue[uid][0];
                        ChatManager.sendedMsg[uid] = data.text;
                        obj.sended[uid] = 1;
                        request("send" , data, obj.onSendMsg, obj.onSendMsgError, obj);
                        ChatManager.queue[uid] = ChatManager.queue[uid].slice(1);
                    }
                }                
            }
            
            if (!avatar || !this.contactUname || !this.contactLogin || this.contactIsPro == '' || this.contactIsEmp == '') {
                request("contact", {uid: uid}, this.onUserData, null, this);
                this.createWindow(0);
            } else {
                this.createWindow(1);
                this.loadHistory();
            }
        }
        
        /**
         * 
         * */
         function openChatWindow(uid) {
             var dlgs = ChatManager.chatDialogs;
             var found = 0;             
             if ( !ChatManager.chatDialogs[uid]) {
                 var d = new ChatDialog(uid);                 
             } else {
                 swapZIndex(ChatManager.chatDialogs[uid]);
             }
         }
         
         /**
         * Считывает положение окна на экране
         * */
         function readGeometry(div) {
             var N = int(div.get("uid"));
             var x = parseInt(cookie_read("qch_x_" + N));
             var y = parseInt(cookie_read("qch_y_" + N));             
             var settingsIsOpen = int(cookie_read("qch_settings"));
             if (!isNaN(x) && !isNaN(y)) {
                 if (settingsIsOpen) {
                     showSettings(div);
                 }
                 var h = qh(div);
                 if (N) {
                     h = qh(div.getElement("div.b-chat__body"));
                 }
                 y -= h;
                 if (y < ChatManager.topLimit) {
                     y = ChatManager.topLimit;
                 }
                 if (y + div.getHeight() > getClientHeight()) {
                     y = getClientHeight() - div.getHeight() - ChatManager.padding;
                 }
                 if (x + qw(div) > getClientWidth()) {
                     x = getClientWidth() - qw(div) - ChatManager.padding;
                 }
                 qy(div, y);
                 qx(div, x);
                 return true;
             }
             return false;
         }
         /**
         * Записывает в куки top, left, height "окна"  
         **/
         function writeGeometry(div) {
             if (!div) {
                 return;
             };
             var N = int(div.get("uid"));
             var x = qx(div);
             if (N == 0) {
                var isOpen =   int(cookie_read("qch_open_0"));
                var isNarrow = int(cookie_read('qch_narrow'));
                if (isOpen && isNarrow) {
                    x -= 160;
                }
             }
             
             cookie_write("qch_x_" + N, x);
             var h = qh(div);
             if (N) {
                 h = qh(div.getElement("div.b-chat__body"));
             }
             cookie_write("qch_y_" + N, qy(div) + h);
         }

        /**
         * Читает куки и воссоздает окна чатов с пользователями на тех же местах, что и на предыдущей странице   
         * */
        function restoreChatDialogs() {
            var s = cookie_read("q4ls" + _UID);
            if (s) {
                var arr = String(s).split(".");
                for (var i = 0; i < arr.length; i++) {
                    if (String(arr[i]) == "undefined") {
                        continue;
                    }
                    var n = int(s58toDec(arr[i]));
                    if (n && String(arr[i]) != 'null') {
                        openChatWindow(n);
                    }
                }
            }
            if (contactsList) {
                swapZIndex(contactsList.win)
            }
        }

               
        /**
         * делает z-index окна больше чем у остальных div.b-chat
         **/
        function swapZIndex(div) {
            if (ChatManager.skipSwapZIndex) {
                ChatManager.skipSwapZIndex = 0;
                return;
            }
            ChatManager.activeWindow = div;
            var dlgs = ChatManager.chatDialogs;
            var indexes = new Array();
            for (var i in dlgs) {// N
                if (dlgs[i] instanceof HTMLDivElement) {
                    indexes.push(i);
                }
            }
            for (var i = 0; i < indexes.length; i++) {
                for (var j = i; j < indexes.length; j++) {
                    var d1 = dlgs[indexes[i]];
                    var d2 = dlgs[indexes[j]];
                    var zI_1 = int(d1.getStyle("z-index"));
                    var zI_2 = int(d2.getStyle("z-index"));
                    if (zI_1 < zI_2) {
                        var buf = indexes[j];
                        indexes[j] = indexes[i];
                        indexes[i] = buf;
                    }
                }
            }
            var obj  = dlgs[indexes[0]];
            if (obj == div) {
                return;
            }
            var max  = int(obj.getStyle("z-index"));
            var uid_1 = int(obj.get("uid"));
            var uid_2 = int(div.get("uid"));
            div.setStyle("z-index", max);
            max--;
            obj.setStyle("z-index", max);
            max--;
            for (var i = 0; i < indexes.length; i++) {
                var current = dlgs[indexes[i]];
                var cUid = int(current.get("uid"));
                if (cUid != uid_1 && cUid != uid_2) {            
                    current.setStyle("z-index", max);
                    max--;
                }
            }
            
        }
                
        /**
         * Получить ширину вьюпорта окна браузера 
         * */
        function getClientWidth() {
             var w = window.innerWidth;
             if (!w && document.documentElement && document.documentElement.clientWidth) {
                 w = document.documentElement.clientWidth;
             } else if (!w) {
                 w = document.getElementsByTagName('body')[0].clientWidth;
             }
             return w;
        }        
        /**
         * Получить высоту вьюпорта окна браузера 
         * */
        function getClientHeight() {
            var h = window.innerHeight;
            if (!h && document.documentElement && document.documentElement.clientHeight) {
                h = document.documentElement.clientHeight;
            } else if (!h) {
                h = document.getElementsByTagName('body')[0].clientHeight;
            }
            return h;        
        }
        /**
         * Функция изменяет размеры окна при добавлении контакта или раскрытии списка
         * @param HtmlDiv wnd            - "окно" параметры которого надо изменить
         * @param int maxH               -  максимально допустимая высота окна 
         * @param int prevHeight = 0     -  высота окна до ресайза, необязательный параметр
         * @param int prevViewportHeight = 0     -  высота вьюпорта окна до ресайза, необязательный параметр
         * */
         function resetGeometry (wnd, maxH, prevHeight, prevViewportHeight) {
             if (!int(prevHeight)) {
                 prevHeight = 0;
             }
             var height = qh(wnd);
             var viewport = wnd.getElement('div.scroll_outer');
             //если превысили максимальную высоту, то не растем
             if (height > maxH && prevHeight <= maxH) {
                var dH = height - maxH;
                var viewportH = qh(viewport) - dH;
                qh(viewport, viewportH);
             }
             if (prevHeight > maxH) {
                 qh(viewport, prevViewportHeight);
                 return;
             }
             //растем
             height = qh(wnd);
             if (height > prevHeight) {
                var top  = qy(wnd);
                var nTop = top - (height - prevHeight);
                if (nTop < ChatManager.topLimit) {
                    top = ChatManager.topLimit;
                } else {
                    top  = nTop;
                }
                qy(wnd, top);
             }
         }
         
         function int(n) {
             var v = parseInt(n);
             document.lastIntIsNan = 0;
             if (String(v) == "NaN") {
                 document.lastIntIsNan = 1;
                 v = 0;
             }
             return v;
         }
         
         function getSuffix(n, root, one, less4, more19) {
            var m = int(n);
            if (String(m).length > 1) {
                m =  int(String(m).charAt( (String(m).length - 2) ) + String(m).charAt( (String(m).length - 1) )) ;
            }
            var lex = root + one;
            if (m > 20) {
                var r = String(n);
                var i = int( r.charAt( (String(r).length - 1) ) );
                if (i == 1) {
                    lex = root + one;
                } else {
                    if (i == 0 || i > 4) {
                       lex = root + more19;
                    }else {
                        lex = root + less4;
                    }
                }
            } else if (m > 4) {
                lex = root + more19;
            } else if (m == 1) {
                lex = root + one;
            } else {
                lex = root + less4;
            }
            return lex;
        }
        /**
         * Задаем поведение при скроллинге
         * @param  HtmlDiv block - див с полосой скролла
         * @param  HtmlDiv scrollBlock - див который перемещается при скроллинге
         * @param  HtmlDiv handle      - "рукоятка"
         * */
        function VScrollBar(block, scrollBlock, handle) {
            this.block = block;
            this.scrollBlock = scrollBlock;
            this.handle = handle;
            handle.self = this;
            block.self  = this;
            block.addEvent('mousewheel',    function (e) {
                this.self.onMouseWheel(e);
                return false;
            });
            VScrollBar.prototype.onMouseWheel = function (e) {
                if (this.handle.parentNode.style.display == "none") {
                    return;
                }
                var dY = 10 * e.wheel;
                var top = qy(this.scrollBlock);
                if (!top) {
                    top = 0;
                }
                var h = qh(this.scrollBlock);
                var outerH = qh(this.block);
                top += dY;
                if (top > 0) {
                    top = 0;
                }
                if (top + h < outerH) {
                    top = outerH - h;
                }
                qy(this.scrollBlock, top);
                this.reset();
            }
            
            //переменные для реализации скроллинга
            this.handlePushDown = 0;   //нажата ли "рукоятка" 
            this.startY = 0;           //позиция указателя мыши в момент нажатия
            this.mouseStartY  = 0;     //позиция указателя мыши в момент нажатия
            this.handleStartY = 0;     //style.top "рукоятки"
            
            //startDrag
            handle.addEvent("mousedown", function (e) {
                this.self.onStartDrag(e);
            });
            VScrollBar.prototype.onStartDrag = function (e) {
                if (Browser.ie) {
                    this.block.getElements('div').each(
                        function (item) {
                            if (!item.hasClass("selectable_tag")) {
                                item.set('unselectable', 'on');
                            }
                        }
                    );
                }
                this.handlePushDown = 1;
                currentVScrollHandle = this;
                this.mouseStartY  = e.client.y;
                this.handleStartY = qy(this.handle);
            }
            
            //drag
            document.addEvent("mousemove", function (e) {
                if (currentVScrollHandle && currentVScrollHandle.handle && currentVScrollHandle.handle.self) {
                    currentVScrollHandle.handle.self.onMouseMove(e);
                }
            });
            VScrollBar.prototype.onMouseMove = function (e) {
                if (this.handlePushDown == 1) {
                    if (!Browser.ie) {
                        e.target.ownerDocument.defaultView.getSelection().removeAllRanges();
                    }
                    currentVScrollHandle = this;
                    var dY = e.client.y - this.mouseStartY;
                    var y = this.handleStartY + dY;
                    var outerH = qh(this.block);
                    var innerH = qh(this.scrollBlock);
                    if (outerH < innerH) {
                        //перемещаем "рукоятку"
                        var handleH = qh(this.handle);
                        var newY = handleH + y;
                        var L  = outerH - handleH; //свободное пространство в котором может перемещаться "рукоятка"
                        var p1 = L / 100;          //один процент от этого пространства
                        var yAsPercents = Math.round(y / p1); //Y - в процентах
                        if (y >= 0 && newY < outerH) {
                            this.handle.setStyle('top', y + 'px');                            
                        } else if (y < 0) {
                            this.handle.setStyle('top', '0px');
                            yAsPercents = 0;
                        } else if (newY > outerH) {
                            qy(this.handle, (outerH - handleH));
                            yAsPercents = 100;
                        }
                        //перемещаем див c элементами
                        var L2 = innerH - outerH;
                        var p2 = L2 / 100;
                        var dY = yAsPercents * p2;
                        qy(this.scrollBlock, -1*dY);
                    }
                }
            }
            /**
             * Устанавливает размер по вертикали бегунка скроллбара в зависимости от размера по вертикали ленты
             * Устанавливает позицию бегунка в зависимости от положения ленты относительно контейнера
             * @param resetYOnTop = false   - если true то устанавливает прокрутку в 0
             * @param resetYOnBtm = false   - если true то устанавливает прокрутку в нижнюю точку
             * **/
            VScrollBar.prototype.reset = function (resetYOnTop, resetYOnBtm) {
                if (resetYOnTop) {
                    this.scrollBlock.setStyle('top', '0px');
                }                
                var innerH = qh(this.scrollBlock);
                var outerH = qh(this.block);
                if (resetYOnBtm) {
                    var y = outerH - innerH;
                    qy(this.scrollBlock, y);
                }
                var visible = 'none';
                if (innerH > outerH) {
                    visible = 'block';
                    //код масштабирует бегунок относительно полосы
                    var divHandle = this.handle;                    
                    var minH = ChatManager.minScrollHandleH;            //минимальная высота "рукоятки"
                    var p1 = innerH / 100;
                    var hAsPercents = outerH / p1;
                    var p2  = outerH / 100;
                    var len = Math.round(p2 * hAsPercents);
                    if (len < minH) {
                        len = minH;
                    }
                    divHandle.setStyle('height', len + 'px');
                    
                    //this.block.getElement('div.b-chat__scroll-holder').setStyle("display", visible);
                    //код позиционирует бегунок относительно полосы
                    var tapeY = qy(this.scrollBlock);
                    if (!tapeY) {
                        tapeY = 0;
                    }
                    //берем разность высот ленты и контейнера, принимаем за 100 процентов
                    var L = innerH - outerH;
                    //вычитаем из этой разности модуль tapeY  получившееся значение смотрим в процентах
                    var dY = Math.abs(tapeY);
                    var p1 = L / 100;
                    var dYAsPercents = dY / p1; 
                    //вычитаем из высоты контейнера высоту бегунка (len), разность принимаем за 100%
                    var L2 = outerH - len;
                    var p2 = L2 / 100;
                    //пересчитываем предыдущее значение в процентах в пиксели и смещаем на эту величину бегунок
                    var top = Math.round(dYAsPercents * p2);
                    if (top + len > outerH) {
                        top = outerH - len;
                    }
                    divHandle.setStyle('top', top + 'px');
                } else {
                    this.handle.setStyle('top', '0px');
                    this.scrollBlock.setStyle('top', '0px');
                }
                this.block.getElement('div.b-chat__scroll-holder').setStyle("display", visible);
                return (outerH - innerH);
            }
        }
        
        function qx(tag, v) {
            return modStyle(tag, "left", v);
        }
        function qy(tag, v) {
            return modStyle(tag, "top", v);
        }
        function qh(tag, v) {
            return modStyle(tag, "height", v);
        }
        function qw(tag, v) {
            return modStyle(tag, "width", v);
        }
        function modStyle(tag, css, value) {
            if (value || value === 0 || value === '0') {
                if (value != null) {
                    value += 'px'
                }
                tag.setStyle(css, value);
            }
            return int(tag.getStyle(css));
        }

        function quickNarrow() {
            if (!ChatManager.qch_open_0) {
                return;
            }
            var win = contactsList.win;
            var w = qw(win);
            if (w > 50) {
                contactsList.setNarrow();
                writeGeometry(win);
            }
        }
        
        function animateNarrow() {
            if (!contactsList || !contactsList.win) {
                return;
            }
            var win = contactsList.win;
            if (qw(win) < 210 || animateProcess()) {
                return;
            }
            ChatManager.currentX = qx(win);
            ChatManager.wideNarrowInerval = setInterval(function () {
                    var dX = ChatManager.narrowDx;
                    var win = contactsList.win;
                    var w = qw(win);
                    var sW = w;
                    w -= dX;
                    while (w < 50) {
                        dX = Math.round(dX/2);
                        if (sW - dX > 50) {                
                            break;
                        }
                        if (sW - dX < 50 && dX <= 1) {
                            sW = 51;
                            break;
                        }
                    }
                    w = sW - dX;
                    if (w <= 50) {                    
                        contactsList.setNarrow(1);
                        clearInterval(ChatManager.wideNarrowInerval);
                        ChatManager.wideNarrowInerval = 0;
                        var x = ChatManager.currentX + 160;
                        qx(win, x);
                        writeGeometry(win);                        
                        return;
                    }
                    qw(win, w);
                    var x = qx(win);
                    x += dX;
                    qx(win, x);
                }, 
                (1000 / 24));
        }
        
        function animateWide() {
            contactsList.win.getElement("div.scroll_inner").getElements("span.b-chat__name").removeClass("b-chat__name_hide");
             if (!ie8() && qw(contactsList.win)  < 210) {
                        clearInterval(ChatManager.wideNarrowInerval); //останавливаем сужение
                        
                        ChatManager.wide2narrowinterval = setInterval(
                            function () {
                                var dX = ChatManager.narrowDx;
                                var win = contactsList.win;
                                var w = qw(win);
                                var sW = w;
                                w += dX;
                                while (w > 210) {
                                    dX = Math.round(dX/2);
                                    if (sW + dX > 210) {                
                                        break;
                                    }
                                    if (sW + dX > 210 && dX <= 1) {
                                        w = 209;
                                        break;
                                    }
                                }
                                w = sW + dX;
                                if (w > 210) {
                                    contactsList.setWide(0, 1);
                                    clearInterval(ChatManager.wide2narrowinterval);
                                    ChatManager.wide2narrowinterval = 0;
                                    writeGeometry(win);
                                    if (ChatManager.minimizeAfterWide == 1) {
                                        ChatManager.minimizeAfterWide = 0;
                                        minimizeAnimate(win);
                                    }
                                    return;
                                }
                                win.setStyle("width", w + 'px');
                                var x = qx(win);
                                x -= dX;
                                win.setStyle("left", x + 'px');
                            },
                            Math.round(1000 / 24)
                        );
                    } else {
                        var win = contactsList.win;
                        var w = qw(win);
                        if (w < 210) {
                            contactsList.setWide();
                            writeGeometry(win);
                            //win.setStyle("left", int(win.getStyle("left")) - 160 + 'px');
                            ChatManager.activeTimestamp = Math.round(new Date().getTime()/1000.0); //IE8
                        }
                    }
        }
        
        function ie8() {
            if ( (Browser.ie && Browser.version < 9) || Browser.opera) {
                return true;
            }
            return false;
        }

//анимация сужения окна
//сужение списка контактов в случае отсутствия активности (установка флага начала анимации)
ChatManager.narrowCountInterval = setInterval(function () {
    if (!chatOn || ChatManager.criticalError == 1) {
        return;
    }
    if (ChatManager.wideStateCounter == undefined) {
        ChatManager.wideStateCounter = 0;
    }
    var hitTest = hitTestMain();
    if (ChatManager.wideStateCounter >= ChatManager.wideStateLimit) {        
        if (cookie_read('qch_window_type') != 'wide' && !hitTest && cookie_read("qch_settings") != 1 && int(cookie_read("qch_open_0"))) {
            if (!ie8()) {
                //----------------------------------------------------------
                if (animateProcess()) {
                    return;
                }
                animateNarrow();
                //----------------------------------------------------------
            } else {
                quickNarrow();
            }
        }
        ChatManager.wideStateCounter = -1;
    }
    if (hitTest) {
        ChatManager.wideStateCounter = -1;
    }
    ChatManager.wideStateCounter++;
}, 
1000);

//"моргание"  количества контактов
ChatManager.flashingCountInterval = setInterval(function () {
    if (ChatManager.indicate == undefined) {
        ChatManager.indicate = ChatManager.indicateCount;
    }
    if (ChatManager.indicate >= ChatManager.indicateCount) {
        return;
    }
    var a = $('qchcount1');
    if (a.getStyle("color") == "#f88b00") {
        a.setStyle("color", null);
    } else {
        a.setStyle("color", "#f88b00");
    }
    
    var a = $('qchcount2').parentNode;
    if (a.getStyle("color") == "#f88b00") {
        a.setStyle("color", null);
    } else {
        a.setStyle("color", "#f88b00");
    }
    
    var a = $('qchcount1').parentNode;
    a.toggleClass("b-chat__link_color_f88b00");
    
    var a = $('qchcount3');
    if (a.getStyle("color") == "#f88b00") {
        a.setStyle("color", null);
    } else {
        a.setStyle("color", "#f88b00");
    }
    
    ChatManager.indicate += .5;
}, 
.5*1000);

//"моргание"  рамки окна  и проверка, а не закрыли ли чат в другой вкладке
ChatManager.flashingInterval = setInterval(function () {
    if (localStorage) {
        if (localStorage.getItem('q4off') == 1 && contactsList instanceof Object) {
            ChatManager.chatOffAction = 1;
            contactsList.onQChatOff();
            localStorage.removeItem('q4off');
            return;
        }
    }
    var uid = int(ChatManager.blueBorderWnd);
    if (ChatManager.chatDialogs == undefined) {
        return;
    }        
    if (ChatManager.chatDialogs[uid] == undefined) {
        return;
    }
    if (uid <= 0) {
        return;
    }
    ChatManager.chatDialogs[uid].toggleClass("b-chat_active");
},
.5*1000);

//периодическое обновление списка контактов
ChatManager.reloadInterval = setInterval(function () {
    if (contactsList && ChatManager.criticalError != 1) {
        contactsList.reloadContacts();
    }
}, 
ChatManager.refreshContactsInterval*1000);

//периодическое обновление времен сообщений (раз в минуту)
ChatManager.resetTimeInterval = setInterval(function () {
    $$("span.js-display-time").each(
        function (i) {
            var postTime = i.get("data-rawtime");
            i.set("html", i.self.getTime(postTime) );
        }
    );
},
60*1000);

function decompress(s) {
    if (!s) {
        return s;
    }
    if (s.charAt(0) != qch_compress) {
        return s.substring(1);
    }
    s = s.replace(qch_compress, "");
    var base = 53;
    var str = '';
    var n = 0;
    for (var i = 0; i < s.length; i++) {
        var char = s.charAt(i);
        var m = parseInt(char);        
        if (!isNaN(m)) {
            n = m;
            continue;
        }
        var j = alphabet.indexOf(char) + base * n;
        str += alphabet.charAt(j);        
    }
    return str;
}

function compress(value) {
    if (!isNaN(parseInt(value)) || localStorage) {
        return ('0' + value);
    }
    var base = 53;
    var N = -1;
    var fail = 0;        
    var str = '';
    if (!value) {
        return value;
    }
    for (var i = 0; i < value.length; i++) {
         var char = value.charAt(i);         
         var n = alphabet.indexOf(char);
         var m = n;
         if (n != -1) {
             n = Math.floor(n / base);
             if (n != N) {
                 str += String(n);
                 N = n;
             }                 
             str += alphabet[m % base];
         } else {
             fail = 1;
             break;
         }
    }
    if (fail == 0) {
        value = qch_compress + str;
    } else {
        value = '0' + value;
    }
    return value;
}


function dec2s58(n) {
    n = parseInt(n);
    if (isNaN(n)) {
        return  n;
    }
    var data = '';
    while (n) {
        var m = n % s58L;
        n = Math.floor(n / s58L);
        var s = sys58.charAt(m);
        if (s == '') { //check on "all" browsers!!!
            return parseInt('z');
        }
        data = s + data;
    }
    return data;
}

function s58toDec(n) {
    var s = String(n);
    if (s == '') {
        return s;
    }
    var dec = 0;
    for (var i = 0; i < s.length; i++) {
        var a = sys58.indexOf(s.charAt(i));
        if (a == -1) {
            return parseInt("z");//return NaN
        }
        dec += a * Math.pow(s58L, s.length - (1 + i));
    }
    return dec;
}

function splitCompressList(s) {
    s = String(s);
    if (s.length == 0) {
        return ['1','','','1'];
    }
    var L = alphabet.indexOf(s.charAt(0));
    if (L > 52 || L < 0) {
        return ['1','','','1'];
    }
    var arr = [];
    arr.push(s.substring(1, L + 1));
    s = s.substring(L + 1);
    
    L = alphabet.indexOf(s.charAt(0));
    if (L > 52 || L < 0) {
        arr.push[''];
        arr.push[''];
        arr.push[1];
        return arr;
    }
    arr.push(s.substring(1, L + 1));
    s = s.substring(L + 1);

    L = parseInt(s.substring(0, 2), 16);
    if (!isNaN(L)) {
        arr.push(s.substring(2, L + 2));
    } else {
        arr.push[''];
        arr.push[1];
        return arr;
    }
    
    s = s.substring(L + 2);    
    if (s.charAt(0) != '0' && s.charAt(0) != '1') {
        arr.push[1];
        return arr;
    }
    arr.push(s);
    return arr;
}

function joinCompressList(a) {
    if ( !(a instanceof Array) ) {
        return "b1a001";
    }
    for (var i = 0; i < a.length - 1; i++) {
        var s = String(a[i]);
        var L;
        if (i < 2){ 
            L = alphabet.charAt(s.length);
        } else {
            L = s.length.toString(16);
            if (L.length < 2) {
                L = "0" + L;
            }
        }
        a[i] = L + a[i];
    }    
    var r = a.join("");    
    return r;
}


function get_raw_cookie(name, uid) {
    var r;
    if (localStorage) {
        r = localStorage.getItem(name);
    } else {
        r = Cookie.read(name);
    }
    if (!r || r.indexOf('X') == -1 || r.indexOf('Y') == -1) {
        r = "XY0";
        if (uid == 0) {
            r = "XY59";
        }
    }
    return r;
}

function cookie_read(key) {
    if (key.indexOf("q4cc_") == 0) {
        var uid = int(key.replace("q4cc_", ""));
        if (uid) {
            return simple_cookie_read(key);
        }
    } else
    if (key.indexOf("q4ls") == 0) {
        var uid = int(key.replace("q4ls", ""));
        if (uid) {
            return simple_cookie_read("q4ls" + dec2s58(uid));
        }
    } else 
    if (key.indexOf("qch_x_") == 0) {
        var uid = int(key.replace("qch_x_", ""));
        if (uid || uid === 0 || uid === '0') {
            var cookie_name = "qch_" + uid;
            if (uid > 0) {
                cookie_name = "qch_" + dec2s58(uid);
            }
            var raw = get_raw_cookie(cookie_name, uid);                        
            var r = raw.substring(0, raw.indexOf('X'));
            return s58toDec(r);
        }
    } else 
    if (key.indexOf("qch_y_") == 0) {
        var uid = int(key.replace("qch_y_", ""));
        if (uid || uid === 0 || uid === '0') {
            var cookie_name = "qch_" + uid;
            if (uid > 0) {
                cookie_name = "qch_" + dec2s58(uid);
            }
            var raw = get_raw_cookie(cookie_name, uid);
            var rawY = raw.substring(raw.indexOf('X') + 1, raw.indexOf('Y'));
            return s58toDec(rawY);
        }
    } else 
    if (String("qch_onlineqch_settingsqch_open_0qch_narrowqch_contacts_typeqch_window_typeqch_sound").indexOf(key) != -1) {
        var cookie_name = "qch_0";
        var raw = get_raw_cookie(cookie_name, uid);
        //для получения первого байта настроек
        var N = 1;
        var M = 2;
        if (String("qch_contacts_typeqch_window_typeqch_sound").indexOf(key) != -1) {
            //для получения второго байта настроек
            N++;
            M++;            
        }
        var char = raw.substring(raw.indexOf('Y') + N, raw.indexOf('Y') + M);        
        var bstr = parseInt(char, 16).toString(2);
        while (bstr.length < 4) {
            bstr = '0' + bstr;
        }        
        var i;        
        if (key == "qch_window_type") {
            bstr = bstr.substring(1, 3);
            switch (bstr) {
                case "00":
                    return "autowidth";
                case "01":
                    return "wide";
                case "10":
                    return "narrow";
            }
        }
        
        if (key == "qch_online" || key == "qch_contacts_type") {i = 0;}
        if (key == "qch_contacts_type") {            
            return  (bstr.charAt(3) == 1 ? "active" : "all_contacts");
        }
        if (key == "qch_settings") {i = 1;}
        if (key == "qch_open_0")   {i = 2;}
        if (key == "qch_narrow" || key == "qch_sound") {i = 3;}
        var r = bstr.charAt(4 - i - 1);        
        return  r;
    } else 
    if (key == "qch_flick_wnd") {
        var cookie_name = "qch_0";
        var raw = get_raw_cookie(cookie_name, 0);
        var v     = raw.substring(raw.indexOf('Y') + 3);
        return s58toDec(v);
    } else 
    if (key.indexOf("uname_") == 0 || key.indexOf("login_") == 0 || key.indexOf("avatar_") == 0 || key.indexOf("qchlastmsg_") == 0) {
        var kb = key.split("_")[0] + "_";
        var uid = int(key.replace(kb, ""));
        if (uid) {
            var cookie_name = "qch_" + dec2s58(uid);
            var raw = get_raw_cookie(cookie_name, uid);
            var data = splitCompressList(raw.substring(raw.indexOf('Y') + 2));
            //i - номер части в строке raw, c - содержит ли часть флаг сжатия в нулевом символе
            var m = {"uname_":{i:0, c:1},"login_":{i:1}, "avatar_":{i:2}, "qchlastmsg_":{i:3, c:1}};
            var v = data[m[kb].i];
            if (m[kb].c == 1) {
                v = decompress(v);
            }
            return v;
        }
    }
    if (key.indexOf("pro_") == 0 || key.indexOf("emp_") == 0) {
        var kb = key.split("_")[0] + "_";
        var uid = int(key.replace(kb, ""));
        if (uid) {
            var cookie_name = "qch_" + dec2s58(uid);
            var raw = get_raw_cookie(cookie_name, uid);
            var char  = raw.substring(raw.indexOf('Y') + 1, raw.indexOf('Y') + 2);
            var bstr = parseInt(char, 10).toString(2);
            while (bstr.length < 2) {
                bstr = '0' + bstr;
            }
            var cfg = {"pro_":0, "emp_":1};
            return bstr.charAt(cfg[kb]);
        }
    } else 
    if (key == "q4h") {        
        if (localStorage) {
            return JSON.decode(localStorage.getItem(key))[_UID];
        }else {
            //будем посмотреть с куками
        }    
    }
    return '';
}

/**
 *Чтение истории из локального хранилища данных или кук             
 * @param uid  - номер пользователя
 * */
function getLocalHistory(uid) {
    if (sessionStorage) {
        var data;
        if (sessionStorage.getItem("q4h")) {
            data = JSON.decode(sessionStorage.getItem("q4h"));
        }
        if (data) {
            data = data[_UID];
        } 
        if (!data) {
            data = new Object();
            data[_UID] = [];
            data = data[_UID];
        }
        ChatManager.localHistory = data;
        for (var i = 0; i < data.length; i++) {
            if (data[i].uid == uid) {
                return data[i].history;
            }
        }
    } else {
        //чтение из кук
    }
    return 0;
}

function cookie_write(key, value) {
    if ((value == undefined || value.length == 0) && key.indexOf("qchlastmsg_") != 0 && key.indexOf("q4ls") != 0) {
        return;
    }    
    
    /*
     * @param int  i номер бита    [0-3]
     * @param int  v значение бита 0|1
     * @param char N исходный полубайт ['0'-'F']
     * */
    function replace_bit(i, v, N) {
        var hex = "0123456789ABCDEF";
        var n = hex.indexOf(N);
        if (n == -1) {
            return N;
        }
        if (v) {
            v = 1;
        } else {
            v = 0;
        }
        v = v << i;
        var tail = n;
        if (i > 0) {
            tail = tail << (32 - i);
            tail = tail >>> (32 - i);
        } else {
            tail = 0;
        }
        var head = n; 
        head = head >>> (i + 1);
        head = head << (i + 1);
        var j = head|v|tail;
        return hex.charAt(j);
    }
    if (key.indexOf("q4cc_") != 0 && key.indexOf("q4ls") != 0) {
        Cookie.dispose(key);
    }
    if (key.indexOf("q4cc_") == 0) {
        var uid = int(key.replace("q4cc_", ""));
        if (uid) {
            simple_cookie_write(key, value);
        }
    } else
    if (key.indexOf("q4ls") == 0) {
        var uid = int(key.replace("q4ls", ""));
        if (uid) {
            simple_cookie_write("q4ls" + dec2s58(uid), (value != ''?value:0));
        }
    } else 
    if (key.indexOf("qch_x_") == 0) {
        var uid = int(key.replace("qch_x_", ""));
        if (uid || uid === 0 || uid === '0') {
            var cookie_name = "qch_" + uid;
            if (uid > 0) {
                cookie_name = "qch_" + dec2s58(uid);
            }
            var raw = get_raw_cookie(cookie_name, uid);
            var tail = raw.substring(raw.indexOf('X') + 1);
            raw = dec2s58(value) + 'X' + tail;
            simple_cookie_write(cookie_name, raw);
        }
    } else 
    if (key.indexOf("qch_y_") == 0) {
        var uid = int(key.replace("qch_y_", ""));
        if (uid || uid === 0 || uid === '0') {
            var cookie_name = "qch_" + uid;
            if (uid > 0) {
                cookie_name = "qch_" + dec2s58(uid);
            }
            var raw = get_raw_cookie(cookie_name, uid);
            var rawY = raw.substring(raw.indexOf('X') + 1, raw.indexOf('Y'));
            raw = raw.replace(rawY + 'Y', dec2s58(value) + 'Y');
            simple_cookie_write(cookie_name, raw);
        }
    } else 
    if (String("qch_onlineqch_settingsqch_open_0qch_narrowqch_contacts_typeqch_window_typeqch_sound").indexOf(key) != -1) { 
        var cookie_name = "qch_0";
        var raw = get_raw_cookie(cookie_name, uid);
        //для получения первого байта настроек
        var N = 1;
        var M = 2;
        if (String("qch_contacts_typeqch_window_typeqch_sound").indexOf(key) != -1) {
            //для получения второго байта настроек
            N++;
            M++;
            if (key == "qch_contacts_type") {
                value = (value == "active") ? 1:0;
            }
        }
        var char = raw.substring(raw.indexOf('Y') + N, raw.indexOf('Y') + M);        
        var head     = raw.substring(0, raw.indexOf('Y') + N);
        var tail     = raw.substring(raw.indexOf('Y') + M);
        
        var i;
        if (key == "qch_online" || key == "qch_contacts_type") {i = 0;}
        if (key == "qch_settings") {i = 1;}
        if (key == "qch_open_0")   {i = 2;}
        if (key == "qch_narrow" || key == "qch_sound") {i = 3;}
        if (key == "qch_window_type") {
            i = 1;
            var v = 0;
            if (value == "autowidth" || value == "wide") {
                if (value == "wide") {
                    v = 1;
                }
                value = 0;
            } else { //narrow
                value = 1;
            }
            char = replace_bit(i, v, char);
            i = 2;
        }
        char = replace_bit(i, value, char);
        simple_cookie_write(cookie_name, head + char + tail);
    } else 
    if (key == "qch_flick_wnd") {
        var cookie_name = "qch_0";
        var raw = get_raw_cookie(cookie_name, 0);
        var head     = raw.substring(0, raw.indexOf('Y') + 3);        
        simple_cookie_write(cookie_name, head + dec2s58(value));
    } else 
    if (key.indexOf("uname_") == 0 || key.indexOf("login_") == 0 || key.indexOf("avatar_") == 0 || key.indexOf("qchlastmsg_") == 0) {
        var kb = key.split("_")[0] + "_";
        var uid = int(key.replace(kb, ""));
        if (uid) {
            var cookie_name = "qch_" + dec2s58(uid);
            var raw = get_raw_cookie(cookie_name, uid);
            var data = splitCompressList(raw.substring(raw.indexOf('Y') + 2));
            //i - номер части в строке raw, c - содержит ли часть флаг сжатия в нулевом символе
            var m = {"uname_":{i:0, c:1},"login_":{i:1}, "avatar_":{i:2}, "qchlastmsg_":{i:3, c:1}};
            
            var cv = value;
            if (m[kb].c == 1) {                
                cv = compress(value); 
                if (cv.length > escape(value).length) {
                    cv = '0' + value;
                }
            }
            
            data[m[kb].i] = cv;
            var h = raw.substring(0, raw.indexOf('Y') + 2);
            if (h.charAt(h.length - 1) == 'Y') {
                h += '0';
            }
            var result = h + joinCompressList(data);            
            simple_cookie_write(cookie_name, result);
        }
    }
    if (key.indexOf("pro_") == 0 || key.indexOf("emp_") == 0) {
        var kb = key.split("_")[0] + "_";
        var uid = int(key.replace(kb, ""));
        if (uid) {
            var cookie_name = "qch_" + dec2s58(uid);
            var raw = get_raw_cookie(cookie_name, uid);
            var bstr = raw.substring(raw.indexOf('Y') + 1, raw.indexOf('Y') + 2);
            var cfg = {"pro_":2, "emp_":1};
            if (cfg[kb]) {
                var r = replace_bit(cfg[kb] - 1, value, bstr);
            }
            var result = raw.substring(0, raw.indexOf('Y') + 1) + r + raw.substring(raw.indexOf('Y') + 2);
            simple_cookie_write(cookie_name, result);
        }
    } else 
    if (key == "q4h") {
        if (localStorage) {
            var o = JSON.decode(localStorage.getItem("q4h"));
            if (! (o instanceof Object)) {
                o = {};
                o[_UID] = [];
            }
            o[_UID] = value;
            localStorage.setItem(key, JSON.encode(o));
        }else {
            //будем посмотреть с куками
        }
    }
}

function simple_cookie_read(key) {
    if (localStorage) {
        return localStorage.getItem(key);
    }
    return Cookie.read(key);
}

function simple_cookie_write(key, value) {
    if (value === undefined || value.length == 0) {
        return;
    }
    if (localStorage) {
        var store = localStorage.getItem(key);
        localStorage.removeItem(key);
        var sz = 0;
        if (!Browser.ie) {
            for (var i in localStorage) {
                if (localStorage[i].length) {
                    sz += localStorage[i].length + String(i).length + 1;
                }
            }
            if (Number(sz + String(value).length + 1) < 10000000) {//alert(localStorage.remainingSpace) в IE показывает 5 000 000 что заставляет считать что в данном случае приставка "Мега" используется на новый для ИТ лад
                localStorage.setItem(key, value);
            } else {
                localStorage.setItem(key, store);
            }
        } else {
            if (localStorage.remainingSpace > value.length) {
                localStorage.setItem(key, value);
            } else {
                localStorage.setItem(key, store);
            }
        }
        return;
    }
    var store = Cookie.read(key);
    Cookie.dispose(key);
    if (document.cookie.length + escape(value).length < 4096) {
        Cookie.write(key, value);
    } else {
        Cookie.write(key, store);
    }
}

//отладка и тестирование
        function _l(o){
            try {                
                console.log(o);
            }catch(e){;}
            /*if (!$('trace')) {
                var trace = new Element("textarea", {"style":"z-index:90000; position:absolute; top:0px; width:500px", id:"trace"});
                trace.inject(document.getElementsByTagName("body")[0], "top");            
            }
            $('trace').value += o + '\n';/**/
        }
}//end QuickChat

var QCH = null;

setTimeout (
function () {
    QCH = new QuickChat();
},
CHAT_DELAY*1000
);

function qchat_onavatar_error(id, byClass) {
    var src = QCH.defaultAvatar();
    if (!byClass) {
        $(id).set("src", src);
    } else {
        if ($$("img.b-chat_av_id_" + id)) {
            $$("img.b-chat_av_id_" + id).set("src", src);
        }
    }
}

function quickchat_on() {
    if (!QCH) {
        setTimeout (
        function () {            
            QCH.setChatOn();
        },
        (CHAT_DELAY + 1)*1000
       );
    } else {
        QCH.setChatOn();
    }
}


function quickchat_off() {
    if (!QCH) {
        setTimeout (
        function () {            
            QCH.chatDeactivate();
        },
        (CHAT_DELAY)*1000
       );
    } else {
        QCH.chatDeactivate();
    }
}
