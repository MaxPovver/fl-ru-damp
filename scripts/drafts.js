/*
Тип черновика: 1 - проекты, 2 - личка, 3 - блоги, 4 - сообщества
*/
var draft_type = 0;
var draft_count_chars = 100;
var draft_timer_sec = 300;
var draft_timer;

var draft_saved = 0;
var draft_firsttime_saved = 0;

var draft_commune_timer;
var draft_commune_ftime = 1;

var save_now = 0;

$extend(Element.NativeEvents, { 
    'paste': 2, 'input': 2 
}); 

Element.Events.paste = { 
        base : (Browser.Engine.presto || (Browser.Engine.gecko && Browser.Engine.version < 19)) ? 'input' : 'paste', 
        condition: function(e){ 
            this.fireEvent('paste', e, 1); 
            return false; 
        } 
    }; 

// Сохранить черновик
function DraftSave() {
    if (save_now) {
        return;
    }
    save_now = 1;
    switch(draft_type) {
        case 1:
            // Проекты
            if(draft_saved==0) {
                DraftSaveProject();
            }
            break;
        case 2:
            // Личка
            DraftSaveContacts();
            break;
        case 3:
            // Блоги
            if(draft_saved==0) {
                DraftSaveBlog();
            }
            break;
        case 4:
            // Сообщества
            if(draft_saved==0) {
                DraftSaveCommune();
            }
            break;
    }
    clearInterval(draft_timer);
    draft_timer = setInterval('DraftSave()', 1000*draft_timer_sec);
}

// Инициализация черновика
function DraftInit(type) {
    draft_type = type;
    switch(type) {
        case 1:
            // Проекты
            if($('f2')) {
                $('f2').addEvents({
                    'keydown': function(){
                        str = new String();
                        str = this.get('value');
                        if(draft_firsttime_saved==0) {
                            if(str.length>=draft_count_chars && draft_saved==0) {
                                draft_firsttime_saved = parseInt(str.length/draft_count_chars);
                                DraftSave();
                                draft_saved = 1;
                            }
                        } else {
                            if(parseInt(str.length/draft_count_chars) != draft_firsttime_saved) {
                                draft_firsttime_saved = parseInt(str.length/draft_count_chars);
                                DraftSave();
                                draft_saved = 1;
                            }
                        }
                    },
                    'paste': function() {
                        this.fireEvent('keydown'); 
                    }
                });
                CheckDrafts(1);
                /*if($('draft_id')) {
                    if($('draft_id').get('value')>0) { xajax_FillDraftForm($('draft_id').get('value'), 1); }
                }*/
            }
            break;
        case 2:
            // Личка
            if($('msg')) {
                $('msg').addEvents({
                  'keydown': function(){
                        str = new String();
                        str = this.get('value');
                        if(draft_firsttime_saved==0) {
                            if(str.length>=draft_count_chars && draft_saved==0) {
                                draft_firsttime_saved = parseInt(str.length/draft_count_chars);
                                DraftSave();
                                draft_saved = 1;
                            }
                        } else {
                            if(parseInt(str.length/draft_count_chars) != draft_firsttime_saved) {
                                draft_firsttime_saved = parseInt(str.length/draft_count_chars);
                                DraftSave();
                                draft_saved = 1;
                            }
                        }
                    },
                    'paste': function() {
                        this.fireEvent('keydown'); 
                    }
                });
            }
                CheckDrafts(2);

            break;
        case 3:
            // Блоги
            blog_msg_textarea_id = '';
            if($('msg_dest')) {
                blog_msg_textarea_id = 'msg_dest';
            }
            if($('msg')) {
                blog_msg_textarea_id = 'msg';
            }
            if($(blog_msg_textarea_id)) {
                $(blog_msg_textarea_id).addEvents({
                    'keydown': function(){
                        str = new String();
                        str = this.get('value');
                        if(draft_firsttime_saved==0) {
                            if(str.length>=draft_count_chars && draft_saved==0) {
                                draft_firsttime_saved = parseInt(str.length/draft_count_chars);
                                DraftSave();
                                draft_saved = 1;
                            }
                        } else {
                            if(parseInt(str.length/draft_count_chars) != draft_firsttime_saved) {
                                draft_firsttime_saved = parseInt(str.length/draft_count_chars);
                                DraftSave();
                                draft_saved = 1;
                            }
                        }
                    },
                    'paste': function() {
                        this.fireEvent('keydown'); 
                    }
                });
            }
            break;
        case 4:
            // Сообщества
            if($('msg')) {
                clearInterval(draft_commune_timer);
                draft_commune_timer = setInterval('DraftCheckLenCommune()', 1000);
                CheckDrafts(4);
//                if($('draft_id')) {
//                    if($('draft_id').get('value')>0) { xajax_FillDraftForm($('draft_id').get('value'), 4); }
//                }
            }
            break;
    }
}

// Проверка длинны сообщения в сообществе
function DraftCheckLenCommune() {
    //str = new String();
    var str = CKEDITOR.instances['msg'].getData();
    /*$each($$("textarea.wysiwyg"), function(el) { 
        if($(el).retrieve("MooEditable")) { 
            if(el.get("id")=="msg") { 
                str = $(el).retrieve("MooEditable").getContent(); 
            } 
        } 
    });*/

    if(draft_firsttime_saved==0) {
        if(str.length>=draft_count_chars && draft_saved==0) {
            draft_firsttime_saved = parseInt(str.length/draft_count_chars);
            clearInterval(draft_commune_timer);
            $('msg_source').set('value',str);
            DraftSave();
            draft_saved = 1;
        }
    } else {
        if(parseInt(str.length/draft_count_chars) > draft_firsttime_saved) {
            draft_firsttime_saved = parseInt(str.length/draft_count_chars);
            clearInterval(draft_commune_timer);
            $('msg_source').set('value',str);
            DraftSave();
            draft_saved = 1;
        }
    }
}


function BlogSetQuanityDraft(n) {
	var div = $("draft_div_info_text");
	if (String(div) != "null") {
		var ls =  div.getElements("a"); 
		if (ls.length > 0) {
			var a = ls[0];
			var blog = "блогов";
			var save = "сохранено";
			if ((n > 0) && (n < 5)) blog = "блога";
			if (n == 1) {
				blog = "блог";
				save = "сохранен";
			}
			a.innerHTML = save + " " + n + " " + blog;
		}
	}else {
		var tpl = '<b class="b1"></b><b class="b2"></b><div id="draft_div_info_text" class="form-in">Не забывайте, у вас в черновиках <a href="/drafts/?p=blogs">сохранен 1 блог</a></div><b class="b2"></b><b class="b1"></b>';
		var div = new Element("div").set("id", "draft_div_info");
		div.inject($("editmsg"), "top");
		var div = $("draft_div_info");
		div.set("class", "form fs-p drafts-v");
		div.set("html", tpl);
	}
}

// Сохранить черновик блога
function DraftSaveBlog() {
	var txt = '';
    if($('msg')) { txt = $('msg').value; }
    if($('msg_dest')) { txt = $('msg_dest').value; }
    if(txt.replace(/(^\s+)|(\s+$)/g, "")!=''  || attachedFiles.count!=0) {

        $("btn" ).removeClass("b-button_rectangle_color_green");
        $("btn" ).addClass("b-button_rectangle_color_disable");
        $('btn').set('disabled',true); 
        $('draft_time_save').removeClass('time-save');
        $('draft_time_save').addClass('time-save-process');
        $("draft_time_save").set("style", "display:inline-block");
        $('draft_time_save').set('html', "Сохранение черновика...");

        new Request.JSON({
            url: '/xajax/drafts.server.php',
            onSuccess: function(resp) {
                if(resp && resp.success) {
                    $("draft_time_save").set("style", "display:inline-block");
                    $('draft_id').set('value', resp.id);
                    $('draft_time_save').set('html', resp.html);
                    draft_saved = 0;
                    BlogSetQuanityDraft(resp.count);
                    $("btn" ).addClass("b-button_rectangle_color_green");
                    $("btn" ).removeClass("b-button_rectangle_color_disable");
                    $('btn').set('disabled',false); 
                    $('draft_time_save').addClass('time-save');
                    $('draft_time_save').removeClass('time-save-process');
                }
                save_now = 0;
            }
        }).post({
           'xjxfun': 'SaveDraftBlog',
           'xjxargs': [xajax.getFormValues('frm')],
           'u_token_key': _TOKEN_KEY
        });
    } else {
        save_now = 0;
    }
}

// Сохранить черновик сообщества
function DraftSaveCommune() {
    if( $$("textarea.wysiwyg").length > 0 ) {
        var editors = $$("textarea.wysiwyg");
    } else if($$("textarea.ckeditor").length > 0) {
        var editors = $$("textarea.ckeditor");
    } else {
        return;
    }
    
    $each(editors, function(el) { 
        if($(el).retrieve("MooEditable")) { 
            if(el.get("id")=="msg") { 
                str = $(el).retrieve("MooEditable").getContent(); 
            } 
        } else if($(el).hasClass('ckeditor')) {
            if(el.get("id")=="msg") { 
                editor = CKEDITOR.instances.msg;
                str = editor.getData();
            }
        }
    });
    $('msg_source').set('value',str);
    if( ! str.replace(/(<([^>]+)>)/ig,"").replace(/(&nbsp;)/ig,"").replace(/(^\s+)|(\s+$)/g, "")) {
        str = $('question').get('value');
    }
    if( ! str.replace(/(<([^>]+)>)/ig,"").replace(/(&nbsp;)/ig,"").replace(/(^\s+)|(\s+$)/g, "")) {
        str = $('youtube_link').get('value');
    }
    var xjxargs = null;
    if($('msg_form')) {
        if(str.replace(/(<([^>]+)>)/ig,"").replace(/(&nbsp;)/ig,"").replace(/(^\s+)|(\s+$)/g, "")) {
            xjxargs = xajax.getFormValues('msg_form');
        }
    } else {
        if(str.replace(/(<([^>]+)>)/ig,"").replace(/(&nbsp;)/ig,"").replace(/(^\s+)|(\s+$)/g, "")) {
            xjxargs = xajax.getFormValues('idAlertedCommentForm');
        }
    }
    // старый id черновика
    var oldDraftId = $('draft_id').get('value');
    if (xjxargs) {
        var tfs=$("topic_form_submit"),dts=$("draft_time_save");
        if(tfs) {
           tfs.removeClass("b-button_rectangle_color_green");
           tfs.addClass("b-button_rectangle_color_disable");
           tfs.set('disabled',true);
        }
        if(dts) {
           dts.removeClass('b-buttons__txt_hide');
           dts.set('html', "Сохранение черновика...");
        }

        new Request.JSON({
            url: '/xajax/commune.server.php',
            onSuccess: function(resp) {
                if(resp && resp.success) {
                    $("draft_time_save").removeClass('b-buttons__txt_hide');
                    $('draft_id').set('value',resp.id);
                    $('draft_time_save').set('html',resp.html);
                    draft_saved = 0;
                    // обновляем счетчик черновиков
                    if (oldDraftId != resp.id) UpdateDraftsCount(1);
                }
                save_now = 0;
                var tfs=$("topic_form_submit");
                if(tfs) {
                    tfs.addClass("b-button_rectangle_color_green");
                    tfs.removeClass("b-button_rectangle_color_disable");
                    tfs.set('disabled',false);
                }
            }
        }).post({
           'xjxfun': 'SaveDraftCommune',
           'xjxargs': [xjxargs],
           'u_token_key': _TOKEN_KEY
        });
    }
    else {
        save_now = 0;
    }
    
    clearInterval(draft_commune_timer);
    draft_commune_timer = setInterval('DraftCheckLenCommune()', 1000);
}

// Сохранить черновик проекта
function DraftSaveProject() {
    if($('f2').value.replace(/(^\s+)|(\s+$)/g, "")!='') {
        var newTemplate = (window.Public && Public.newTemplate) ? 1 : 0;
        new Request.JSON({
            url: '/xajax/drafts.server.php',
            onSuccess: function(resp) {
                if(resp && resp.success) {
                    $("draft_time_save").setStyle("display", "inline-block");
                    $('draft_id').set('value',resp.id);
                    $('draft_time_save').set('html',resp.html);
                    draft_saved = 0;
                }
                save_now = 0;
            }
        }).post({
           'xjxfun': 'SaveDraftProject',
           'xjxargs': [xajax.getFormValues('frm'), newTemplate],
           'u_token_key': _TOKEN_KEY
        });
    } else {
        save_now = 0;
    }
}

// Сохранить черновик сообщения в личке
function DraftSaveContacts() {
    var xjxargs = null;
    if($('msg_frm')) {
        if($('msg').value.replace(/(^\s+)|(\s+$)/g, "")!='' || attachedFiles.count!=0) {
            xjxargs = xajax.getFormValues('msg_frm');
        } 
    }
    if($('idAlertedCommentForm')) {
        xjxargs = xajax.getFormValues('idAlertedCommentForm');
    }
    
    if (xjxargs) {
        is_sending = 1;
        $("btn" ).addClass("b-button_disabled");
        $('btn_text').set('disabled',true); 

        $('draft_time_save').removeClass('time-save');
        $('draft_time_save').addClass('time-save-process');
        $("draft_time_save").set("style", "display:inline-block");
        $('draft_time_save').set('html', "Сохранение черновика...");

        new Request.JSON({
            url: '/xajax/drafts.server.php',
            onSuccess: function(resp) {
                if(resp && resp.success) {
                    $("draft_time_save").set("style", "display:inline-block");
                    $('draft_id').set('value',resp.id);
                    $('draft_time_save').set('html',resp.html);
                }
                save_now = 0;
                is_sending = 0;
                $('draft_time_save').addClass('time-save');
                $('draft_time_save').removeClass('time-save-process');
                $("btn" ).removeClass("b-button_disabled");
                $('btn_text').set('disabled',false); 
            }
        }).post({
           'xjxfun': 'SaveDraftContacts',
           'xjxargs': [xjxargs],
           'u_token_key': _TOKEN_KEY
        });
    }
    else {
        save_now = 0;
    }
}

// Проверяет наличие ранее сохраненных черновиков
function CheckDrafts(type) {
    switch(type) {
        case 1:
            // Проекты
            var newTemplate = (window.Public && Public.newTemplate) ? 1 : 0;
            xajax_CheckDraftsProject(newTemplate);
            break;
        case 2:
            // Личка
            xajax_CheckDraftsContacts($('to_login').get('value'));
            break;
        case 3:
            // Блог
            xajax_CheckDraftsBlog();
            break;
        case 4:
            // Сообщество
            xajax_CheckDraftsCommune();
            break;
    }
}

function DraftsToggleDeleteAll(el) {
    var el = $(el.id);
    if($(el).get('checked')==true) {
        $$('#draft_frm input[type=checkbox]').each(function(el) {
            el.set('checked', true);
        });
    } else {
        $$('#draft_frm input[type=checkbox]').each(function(el) {
            el.set('checked', false);
        });
    }
}

function DraftsCheckToggleDeleteAll(el) {
    var el = $(el.id);
    if(el.get('checked')==false) {
        $('dellall_draft').set('checked', false);
    }
}

function DraftDeleteSubmit(id) {
    if(id==0) {
        selected_drafts = 0;
        $$('#draft_frm input[type=checkbox]').each(function(el) {
            if(el.get('checked')) { selected_drafts++; }
        });
        if(selected_drafts!=0) {
            if(confirm('Вы действительно хотите удалить выбранные черновики?')) {
                $('draft_frm_action').set('value', 'delete');
                $('draft_frm').submit();
            }
        }
    } else {
        if(confirm('Вы действительно хотите удалить черновик?')) {
            $$('#draft_frm input[type=checkbox]').each(function(el) {
                el.set('checked', false);
            });
            $('del_draft_'+id).set('checked', true);
            $('draft_frm_action').set('value', 'delete');
            $('draft_frm').submit();
        }
    }
}

/**
 * обновляет счетчик черновиков в шапке сайта
 */
function UpdateDraftsCount (delta) {
    var drafts;
    if (drafts = $(document).getElement('li.b-userbar__drafts a')) {
        var html = drafts.get('html');
        var match = html.match(/^(.*Черновики\s+\()(\d+)(\))$/i);
        if (!match) return;
        var draftCount = +match[2] + delta;
        html = match[1] + draftCount + match[3];
        drafts.set('html', html);
    }
    
}
