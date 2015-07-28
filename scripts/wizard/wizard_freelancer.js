function view_toggle_blocks(view_block, dispose_block) {
    $(view_block).removeClass('b-layout_hide');
    $(dispose_block).getParent().dispose();
    
    if($('left_hint_option')) {
        $('left_hint_option').dispose();
    }
}

var currency_data = {
    2:"Руб", 
    0:"USD", 
    1:"Евро"
};

var worktime = { 
    0: "Часов", 
    1: "Дней",
    2: "Месяцев"
};

var iTimeoutId = null;
var type_loading = 0;
// была ли хоть раз запущена функция search_project()
var projectsSearched = false;

function search_project(search) {
    // игнорируем первый запуск функции (запускается при инициализации комбо-элемента)
    // чтобы при обновлении страницы лишний раз не загружался список проектов
    if (!projectsSearched) {
        projectsSearched = true;
        return;
    }
    type_loading = 1;
    if(iTimeoutId != null) {
        clearTimeout(iTimeoutId);
        iTimeoutId = null;
    }
    if(($('category_column_id').get('value') == 0)|| (($('category_column_id').get('value') == 1)&&($('category_db_id').get('value') == 0)) ) {
        ComboboxManager.getInput("category").breadCrumbs[1] = -1;
    }
    
    Cookie.write('your_categories', ComboboxManager.getInput("category").breadCrumbs[0], {duration: 356, domain: domain4cookie});
    Cookie.write('your_subcategories', ComboboxManager.getInput("category").breadCrumbs[1], {duration: 356, domain: domain4cookie});
    
    
    iTimeoutId = setTimeout(function(){
        var categories = ComboboxManager.getInput("category").breadCrumbs;
        xajax_searchProject(search, categories);
    }, 1500);  
}

function loading_projects(page) {
    if(!$('load_project').getElement('.b-button').hasClass('b-button_disabled')) {
        var search = $('search-request').get('value');
        var categories = ComboboxManager.getInput("category").breadCrumbs; 
        
        xajax_searchProject(search, categories, page, type_loading);
    }
}

function boxWork(outerBox, uniqId, token) {

    var self = this;

    this.empty = function() {
        var html = '';
        html += '<form id="frmIMG_'+uniqId+'" method="post" action="/wizard/upload.php?type=work_example" target="fupload" enctype="multipart/form-data">';
        html += '<input type="hidden" name="u_token_key" value="'+token+'">';
        html += '<input type="hidden" name="position" value="'+uniqId+'">';
        html += '<input id="ps_action" name="action" type="hidden" value="add_pic" />';
        html += '<input name="MAX_FILE_SIZE" value="2097152" type="hidden" />';
        html += '<div class="b-file b-file__fon">';
        html += '    <div class="b-file__wrap b-file__wrap_margtop_75">';
        html += '        <input type="file" class="b-file__input" name="attach" style="font-size: 200px">';
        html += '        <a href="javascript:void(0)" class="b-button b-button_rectangle_color_transparent b-button_block">';
        html += '            <span class="b-button__b1">';
        html += '                <span class="b-button__b2">';
        html += '                    <span class="b-button__txt">Загрузить пример работы</span>';
        html += '                </span>';
        html += '            </span>';
        html += '        </a>';
        html += '    </div>';
        html += '</div>';
        html += '</form>';
        html += '<div class="b-layout__txt b-layout__txt_fontsize_11">Максимальный размер файла: 2 Мб.<br />Файлы следующих форматов запрещены к загрузке: ';
        html += 'ade, adp, bat, chm, cmd, com, cpl, exe, hta, ins, isp, jse, lib, mde, msc, msp, mst, pif, scr, sct, shb, sys, vb, vbe, vbs, vxd, wsc, wsf, wsh</div>';
        
        outerBox.set('html', html);
        
        outerBox.getElement('input[type=file]').addEvent('change', function() {
            document.getElementById('frmIMG_' + uniqId).submit();
            self.progress();
        });

        $('work_idfile_' + uniqId).set('value', '');
        $('work_namefile_' + uniqId).set('value', '');

    }

    this.progress = function() {
        var html = '';
        html += '<form id="frmIMG_'+uniqId+'" method="post" action="/wizard/upload.php?type=work_example" target="fupload" enctype="multipart/form-data">';
        html += '<input type="hidden" name="u_token_key" value="'+token+'">';
        html += '<input type="hidden" name="position" value="'+uniqId+'">';
        html += '<input id="ps_action" name="action" type="hidden" value="add_pic" />';
        html += '<input name="MAX_FILE_SIZE" value="2097152" type="hidden" />';
        html += '<div class="b-file b-file__fon">';
        html += '    <div class="b-file__wrap b-file__wrap_margtop_75">';
        html += '        <div href="javascript:void(0)" class="b-button b-button_block">';
        html += '            <img src="/images/loader-2.gif" alt="" border="0">';
        html += '        </div>';
        html += '    </div>';
        html += '</div>';
        html += '</form>';
        html += '<div class="b-layout__txt b-layout__txt_fontsize_11">Максимальный размер файла: 2 Мб.<br />Файлы следующих форматов запрещены к загрузке: ';
        html += 'ade, adp, bat, chm, cmd, com, cpl, exe, hta, ins, isp, jse, lib, mde, msc, msp, mst, pif, scr, sct, shb, sys, vb, vbe, vbs, vxd, wsc, wsf, wsh</div>';
        
        outerBox.set('html', html);
    }
    
    this.complete = function(fdata) {
        var ext  = getICOFile(fdata.name.substr(fdata.name.lastIndexOf('.', fdata.name) + 1, fdata.name.length).toLowerCase());
        var html = '';
        if ( ext == 'jpg' || ext == 'jpeg' || ext == 'gif' || ext == 'png' ) {
            html += '<form id="frmIMG_'+uniqId+'" method="post" action="/wizard/upload.php?type=work_example" target="fupload" enctype="multipart/form-data">';
            html += '<input type="hidden" name="u_token_key" value="'+token+'">';
            html += '<input type="hidden" name="position" value="'+uniqId+'">';
            html += '<input id="ps_action" name="action" type="hidden" value="add_pic" />';
            html += '<input name="MAX_FILE_SIZE" value="2097152" type="hidden" />';
            html += '<div class="i-button i-button_relative">';
            html += '    <a class="b-button b-button_admin_del b-button_right_25 b-button_top_5" href="javascript:void(0)"></a>';
            html += '    <img border="0" src='+fdata.link+' alt="" class="b-layout__pic">';
            html += '    <a href="" class="b-layout__link b-layout__link_fontsize_13"></a>';
            html += '</div>';
            html += '</form>';
            html += '<div class="b-layout__txt b-layout__txt_fontsize_11">Максимальный размер файла: 2 Мб.<br />Файлы следующих форматов запрещены к загрузке: ';
            html += 'ade, adp, bat, chm, cmd, com, cpl, exe, hta, ins, isp, jse, lib, mde, msc, msp, mst, pif, scr, sct, shb, sys, vb, vbe, vbs, vxd, wsc, wsf, wsh</div>';
        } else {
            html += '<form id="frmIMG_'+uniqId+'" method="post" action="/wizard/upload.php?type=work_example" target="fupload" enctype="multipart/form-data">';
            html += '<input type="hidden" name="u_token_key" value="'+token+'">';
            html += '<input type="hidden" name="position" value="'+uniqId+'">';
            html += '<input id="ps_action" name="action" type="hidden" value="add_pic" />';
            html += '<input name="MAX_FILE_SIZE" value="2097152" type="hidden" />';
            html += '<div class="b-file b-file__fon b-file_attached i-button">';
            html += '    <span class="b-icon b-icon_mid_'+ext+'"></span>';
            html += '    <a class="b-button b-button_admin_del b-button_float_right" href="javascript:void(0)"></a>';
            html += '</div>';
            html += '</form>';
            html += '<div class="b-layout__txt b-layout__txt_fontsize_11">Максимальный размер файла: 2 Мб.<br />Файлы следующих форматов запрещены к загрузке: ';
            html += 'ade, adp, bat, chm, cmd, com, cpl, exe, hta, ins, isp, jse, lib, mde, msc, msp, mst, pif, scr, sct, shb, sys, vb, vbe, vbs, vxd, wsc, wsf, wsh</div>';
        }
        outerBox.set('html', html);
        
        outerBox.getElement('a.b-button').addEvent('click', function() {
            self.empty();
        });
        
        $('work_idfile_' + uniqId).set('value', fdata.id);
        $('work_namefile_' + uniqId).set('value', fdata.name);
    }

    this.error = function(message) {
        this.empty();
        alert(message);
    }

    this.empty();
    
}    

function Work( options ) {
    var work_count = 1;
    var default_options = { 
        template_link : function(pos) {
            var body = '<td class="b-layout__left">';
            body    += '    <div class="b-combo">';
            body    += '        <div class="b-combo__input">';
            body    += '            <input  class="b-combo__input-text" name="link[' + pos + ']" type="text" size="80" value="" graytext="Ссылка" />';
            body    += '        </div>';
            body    += '    </div>';
            body    += '</td>';
            body    += '<td class="b-layout__right b-layout__right_padbot_10 b-layout__right_width_15 b-layout__right_padleft_10">&#160;</td>';
            
            html = new Element('tr', { 'class': 'b-layout__tr', 'html' : body });
            return html;
        },
        template_descr : function(pos) {
            var body = '<td class="b-layout__left">';
            body    += '    <div class="b-textarea">';
            body    += '        <textarea graytext="Введите описание работы" class="b-textarea__textarea tawl" name="descr[' + pos + ']" cols="" rows="" rel="1500"></textarea>';
            body    += '    </div>';
            body    += '</td>';
            body    += '<td class="b-layout__right b-layout__right_padbot_10 b-layout__right_width_15 b-layout__right_padleft_10">&#160;</td>';
            
            html = new Element('tr', { 'class': 'b-layout__tr', 'html' : body });
            return html;
        },
        template : function() {
            var body = '<div class="b-fon__body b-fon__body_pad_10 b-fon__body_bg_f0ffdf i-button">';
            body +=    '    <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full" work="' + work_count + '">';
            body +=    '        <tr class="b-layout__tr">';
            body +=    '            <td class="b-layout__left b-layout__left_padbot_15">';
            body +=    '                <div class="b-combo">';
            body +=    '                    <div class="b-combo__input" id="namefield-' + work_count + '">';
            body +=    '                        <input  graytext="Название работы" class="b-combo__input-text" name="name[' + work_count + ']" type="text" size="80" maxlength="80" value="" />';
            body +=    '                    </div>';
            body +=    '                </div>';
            body +=    '            </td>';
            body +=    '            <td class="b-layout__right b-layout__right_padbot_15 b-layout__right_width_15 b-layout__right_padleft_10">';
            body +=    '                <a href="javascript:void(0)" class="b-button b-button_admin_del"></a>';
            body +=    '            </td>';
            body +=    '        </tr>';
            body +=    '        <tr class="b-layout__tr">';
            body +=    '            <td class="b-layout__left">';
            body +=    '                <span>';
            body +=    '                    <input type="hidden" name="pict_id[' + work_count + ']" id="pict' + work_count + '_id" value="0">';
            body +=    '                    <div class="b-file b-file_inline-block b-file_padright_20">';
            body +=    '                        <div class="b-file__wrap  b-file__wrap_margleft_-3">';
            body +=    '                            <input type="file" name="pict[' + work_count + ']" class="b-file__input" onchange="upload.load(this)">';
            body +=    '                            <a href="#" class="b-button b-button_rectangle_color_transparent">';
            body +=    '                                <span class="b-button__b1"><span class="b-button__b2">';
            body +=    '                                    <span class="b-button__txt">Прикрепить файл</span>';
            body +=    '                                </span></span>';
            body +=    '                            </a>';
            body +=    '                        </div>';
            body +=    '                    </div>';
            body +=    '                </span>';
            body +=    '                <div class="b-layout__txt b-layout__txt_inline-block  b-layout__txt_valign_top b-layout__txt_padtop_7">';
            body +=    '                    <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-link-create_link" href="javascript:void(0)">Поставить ссылку</a> &#160;&#160;&#160; <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-link-create_descr" href="javascript:void(0)">Добавить описание</a>';
            body +=    '                </div>';
            body +=    '            </td>';
            body +=    '            <td class="b-layout__right b-layout__right_width_15 b-layout__right_padleft_10">&#160;</td>';
            body +=    '        </tr>';
            body +=    '    </table>';
            body +=    '</div>';
            
            var html = new Element('div', { 'class' : 'b-fon b-fon_width_full b-fon_padbot_20 b-fon-portfolio b-fon_overflow_hidden', 'html' : body, 'id': this.elm_prefix_id + 'portfolio' + work_count} );
            var _this = this;
            
            html.getElement('.b-link-create_link').addEvent('click', function() {
                _this.create_field(1, this);
            });
            html.getElement('.b-link-create_descr').addEvent('click', function() {
                _this.create_field(2, this);
            });
            html.getElement('.b-button_admin_del').addEvent('click', function() {
                _this.remove(this);
            })
            return html;
        },
        elm_prefix_id: 'work_'
    }
    
    for(var option in default_options) {
        this[option] = options && options[option]!==undefined ? options[option] : default_options[option];
    }
    
    // начальная высота блока добавления работы
    var portfHeight = 98;
    
    // Добавляем работу
    this.create = function() {
        var div = this.template();
        var last_work = $$('.b-fon-portfolio');
        
        if(last_work.length == 0) {
            last_work = $('end_of_portfolios');
        } else {
            last_work = last_work[0];
        }
        // эффект появления
        var fadeIn = new Fx.Morph(div, {
            onComplete: function () {
                div.setStyle('height', '');
            }
        })
        div.setStyles({
            'height': 0,
            'padding-bottom': 0
        });
        last_work.grab(div, 'before');
        fadeIn.start({
            'height': portfHeight,
            'padding-bottom': 20
        });
        ComboboxManager.createCombobox($('namefield-' + work_count));
        
        
        work_count++;
    }
    
    this.create_field = function(type, obj) {
        var parent = $(obj).getParent('table');
        if(type == 1) {
            tr = this.template_link(parent.getProperty('work'));
        } else {
            tr = this.template_descr(parent.getProperty('work'));            
        }
        
        var last   = parent.getLast('tr td.b-layout__left');
        if(last) {
            last.addClass('b-layout__left_padbot_15');
        }
        $(obj).getParent('td').addClass('b-layout__left_padbot_15');
        $(obj).getParent('table').adopt(tr);
        
        // подключаем b-combo для поля ввода ссылки
        if (type == 1) {
            var bCombo = $(tr).getElement('.b-combo__input');
            if (bCombo) {
                ComboboxManager.createCombobox(bCombo);
            }
        }
        if (type == 2) {
			var ls = tr.getElements(".b-textarea");
			if (ls[0]) {
			    var ls2 = $$(".b-textarea");
		        var t = [ls[0]];
			    var textarea = new resizableTextarea(t, {
				             handler: ".b-textarea__handler",
							 modifiers: {x: false, y: true},
							 size: {y:[100, 30000]},
							 onResize: function(current) {
							 }
			    });
                var ta = tr.getElements(".b-textarea__textarea")[0];
                new DynamicTextarea(ta);
                tawlTextareaInit();
                ta.addEvent('focus',function(){
	                this.getParent('.b-textarea').addClass('b-textarea_current');
	                this.addEvent('blur',function(){
		                 this.getParent('.b-textarea').removeClass('b-textarea_current')
		            })
	           });
			}
		}
        $(obj).dispose();
    }
    
    this.remove = function(obj) {
        var remObj = $(obj).getParent('.b-fon');
        var fadeOut = new Fx.Morph(remObj, {
            onComplete: function(){
                remObj.dispose();
            }
        });
        fadeOut.start({
            'height': 0,
            'padding-bottom': 0
        });
    }
    
    this.setWork = function(pos) {
        work_count = pos;
    }
}

var upload_block;

function upload_file(obj) {
    $('upload_form').adopt($(obj));
    $('upload_form').submit();
    
    upload_block = $(obj).getParent('.b-file').getParent('td');
}

function clearErrorBlock(obj) {
    var error = $(obj).getParent('td').getElement('.errorBox');
    if(error != undefined) {
        error.dispose();
    }
}

function Upload( options ) {
    var view_block;
    var name_upload;
    var submit_name;
    
    var default_options = {
        template_view : function(name, link) {
            ext = getICOFile(name.substr(name.lastIndexOf('.', name) + 1, name.length).toLowerCase());
            var body = '<div class="b-layout__txt b-layout__txt_inline-block  b-layout__txt_valign_top b-layout__txt_padtop_7">';
            body    += '    <a class="b-layout__link" href="' + link + '" target="_blank"><span class="b-icon b-icon_margtop_-3 b-icon_mid_' + ext + '"></span>' + name + '</a>&#160;&#160;';
            body    += '</div>';
            body    += '<a href="javascript:void(0)" class="b-button b-button_margtop_10 b-button_admin_del"></a>&#160;&#160;&#160;&#160;';
            
            html = new Element('span', { 'html' : body });
            
            var _this = this;
            html.getElement('.b-button_admin_del').addEvent('click', function() {
                _this.remove(this);
            });
            return html;
        },
        IDUploadForm : 'upload_form',
        NameClassElm : 'b-file',
        IDSubmit     : 'submit_button'
    }
    
    for(var option in default_options) {
        this[option] = options && options[option]!==undefined ? options[option] : default_options[option];
    }
    
    this.load = function( obj ) {
        view_block  = $(obj).getParent('.' + this.NameClassElm).getParent();
        name_upload = $(obj).getProperty('name');
        
        if(name_upload.lastIndexOf('[', name_upload) > 0) {
            name = name_upload.replace('[', '');
            name_upload = name.replace(']', '');
        }
        var clone  = $(obj).clone();
        var parent = $(obj).getParent();
        if($(this.IDUploadForm).getElement('input[type=file]')) {
            $(this.IDUploadForm).getElement('input[type=file]').dispose();
        }
        
        $(this.IDUploadForm).adopt($(obj));
        parent.grab(clone, 'top');
        
        $(this.IDUploadForm).getElement('input[type=file]').setProperty('name', 'upload_file');
        $(this.IDUploadForm).submit();
        $(this.IDSubmit).addClass('b-button_rectangle_color_disable');
        
        loader = new Element('img', {'src' : '/images/loader-white.gif', 'class':'loader'});
        view_block.getElement('.' + this.NameClassElm).setStyle('display', 'none');
        view_block.adopt(loader);
    }
    
    this.view = function(name, link, id) {
        view_block.getElement('.loader').dispose();
        view_block.getElement('#' + name_upload + '_id').set('value', id);
        view_block.adopt(this.template_view(name, link));
        
        $(this.IDSubmit).removeClass('b-button_rectangle_color_disable');
    }
    
    this.remove = function( obj ) {
        var remove_object = $(obj).getParent();
        var parent = remove_object.getParent();
        
        parent.getElement('input[type=hidden]').set('value', '0');
        parent.getElement('.' + this.NameClassElm).setStyle('display', null);
        remove_object.dispose();
    }
    
    this.error = function(error) {
        view_block.getElement('.loader').dispose();
        view_block.getElement('.' + this.NameClassElm).setStyle('display', null);
        alert(error);
    }
}
