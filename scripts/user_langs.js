/**
* Не выбирать скрытый язык при наборе значения с клавиатуры
**/
function keyboard_select_off(evt) {
    var N  = this.options.selectedIndex;
    var n = 0;
    while ( this.options[N].style.display == 'none' ) {
        N = this.options.selectedIndex + 1;
        if (N >= this.options.length) {
            N = 0;
        }
        this.options.selectedIndex = N;
        if (n == this.options.length) { //на всякий случай
            break;
        }
        n++;
    }
    if (n) {
        return false;
    }
}
/**
* Пропустить скрытый язык при использовании клавиш вверх и вниз  
**/
function skip_hide_lang(evt) {
    var d = 1;
    var N  = this.options.selectedIndex;
    var n = 0;
    var code = 0;
    if (!evt) {
        code = window.event.keyCode;
    } else {
        code = evt.keyCode;
    }
    if (code == 38  && N - 1 > 0 && this.options[ N - 1 ].style.display == 'none') {
        N -= 2;
        this.options.selectedIndex--;
        d = -1;
    }else
    if (code == 40 && N + 1 < this.options.length && this.options[ N + 1 ].style.display == 'none') {
       N += 2;
       this.options.selectedIndex++;
    }
    while ( this.options[N].style.display == 'none' ) {
        N = this.options.selectedIndex += d;
        if (n == this.options.length) { //на всякий случай
            break;
        }
        n++;
    }
    if (n) {
        return false;
    }
}
/**
*Скрыть элемент списка языков, если он выбран в другом списке
* @see remove_duplicate_lang
* @param select - HtmlSelect элемент, в котором выбран элемент со знаением value
* @param value  - элемент списка с этим значением будет скрыт в остальнх списках языка на странице
*/
function lang_hide_item(select, value) {
    if (value == 0) return 0;
    var __break = 0;
    $$("tr.langitem").each(
        function (tr) {
            if ( __break ) return;
            tr.getElements("select").each(
                function (sel) {
                    if ( sel != select ) {
                         for (var i = 0; i < sel.options.length; i++) {
                            var opt = sel.options[i];
                            if (opt.value == value) {
                                if ( (Browser.opera || Browser.ie) && opt.selected == true ) {
                                    alert("Вы уже выбрали \"" + opt.text + "\" в другом списке");
                                    select.getElements("options").set("option", false);
                                    __break = 1;
                                } else {
                                    try {
                                        opt.setStyle("display", "none");
                                    } catch(e) {;} //IE8
                                }
                            }
                        }
                    }
                }
            );
        }
    );
    return __break;
}
/**
* Скрыть дублирующие друг друга элементы списков языков
*/
function remove_duplicate_lang() {
    $$("tr.langitem").each(
        function (tr) {
            tr.getElements("select").each(
                function (sel) {
                     for (var i = 0; i < sel.options.length; i++) {
                        var opt = sel.options[i];
                        try {
                            opt.setStyle("display", null);
                        } catch(e) {;} //IE8
                     }
                }
            );
        }
    );
    var _break = 0;
    $$("tr.langitem").each(
        function (tr) {
            if (_break) return;
            tr.getElements("select").each(
                function (sel) {
                     for (var i = 0; i < sel.options.length; i++) {
                        var opt = sel.options[i];
                        if ( opt.selected ) {
                           _break = lang_hide_item(sel, opt.value);
                           break;
                        }
                     }
                }
            );
        }
    );
}
/**
* Установить определенный элемент списка выделеным
* @param n        - порядковый номер блока выбора языка
* @param lang_id  - идентификатор языка
* @param quality  - степень изучения языка 1 - начальный, 2 - средний, 3 - продвинутывй, 4 - родной 
*/
function lang_set_selected_item(n, lang_id, quality) {
    var opt = $("langs-" + n).options;
    for (var i = 0; i < opt.length; i++) {
        if ( opt[i].value == Number( (lang_id > 0 ? lang_id : 0) ) ) {
            opt[i].selected = true;
            lang_hide_item($("langs-" + n), opt[i].value);
            break;
        }
    }
    $("langs-" + n).onchange = remove_duplicate_lang;
    var ls = $("lang_item_" + n).getElements("input[type=radio]");
    if ( parseInt(quality) ) {
        if ( ls [ parseInt(quality) - 1 ] ) {
            ls [ parseInt(quality) - 1 ].checked = "true";
        }
    }
    remove_duplicate_lang();
    $("langs-" + n).onkeydown = skip_hide_lang;
    $("langs-" + n).onkeyup   = keyboard_select_off;
}
/*
* Добавить блок выбора языка
**/
function lang_add() {
    var ls = $$("tr.langitem");
    if (ls.length > 9) {
        alert("Вы не можете указать более десяти языков");
        return false;
    }
    if (ls.length == 1) {
        $$('label.sign_first_row').setStyle("margin-left", "4px");
    }
    var o = ls[ls.length - 1];
    var id = o.id.replace(/[\D]/g, '');
    id++;
    var e = new Element("tr", {id:("lang_item_" + id), "class":"langitem" });
    var html = o.get("html").replace(/return lang_del\([\d]+\)/, "return lang_del(" + id + ")").replace("<strong>Язык:</strong>", "&nbsp;");
    e.set( "html", html/*.replace(/<script.[^>]>.*<\/script>/, "")*/ );
    e.getElements("select").each(
        function (s) {
            s.id = "langs-" + id;
            s.name = "langs[" + id + "]";
            s.onchange = remove_duplicate_lang;
            s.onkeydown = skip_hide_lang;
            s.onkeyup   = keyboard_select_off;
            if (Browser.ie ) s.options[0].selected = true;
        }
    );
    var I = 0;
    e.getElements("input[type=radio]").each(
        function (r) {
            r.name = "lang-q[" + id + "]";
            r.id = "b-radio__input" + id + (I + 1);
            if (I == 1) {
                r.checked = true;
            }
            I++;
        }
    );
    I = 0;
    e.getElements("label").each(
        function (L) {
            L.set("for", "b-radio__input" + String(id) + (I + 1));
            L.setStyle("margin-left", "4px");
            I++;
        }
    );
    e.getElements("input[type=hidden]").each(
        function (h) {
            h.id = "lang-id-" + id;
            h.name = "lang-id[" + id + "]";
            h.value = null;
        }
    );
    var tpl = '<div class="b-layout__txt b-layout__txt_padtop_7"><a onclick="return lang_del(' + id + ');" class="b-layout__link b-layout__link_dot_c10600" href="#">- Удалить</a></div>';
    e.getElements("td.rem_add_btn").each(
        function(link) {
            link.set("html", tpl);
        }
    );
    
    e.inject(o, "after");
    remove_duplicate_lang();
    var opts = e.getElements("select")[0].options;
    for (var i = 0; i < opts.length; i++) {
        if (opts[i].style.display != "none") {
            opts[i].selected = true;
            break;
        }
    }
    return false;
}

/**
* Удалить блок выбора языка
* @param Number n  порядковый номер поля ввода языка на странице
*/
function lang_del(n) {
    var parent = $('lang_item_' + n).parentNode;
    parent.removeChild( $('lang_item_' + n) );
    if ($$("tr.langitem").length === 1) {
        $$('label.sign_first_row').setStyle("margin-left", "4px");
    }
    return false;
}
