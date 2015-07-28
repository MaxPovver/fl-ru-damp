var attachedFiles = {

    obj:                null,
    objID:              null,
    files:              null,
    maxCount:           0,
    maxSize:            0,
    disallowedFormats:  '',
    type:               '', 
    uid:                0,
    count:              0,
    newDesign:           false, // новый дизайн

    changeClasses: function() {
        $(this.objID).getElements('.b-fon__item_last').each(function(item) {item.removeClass('b-fon__item_last');});
        var el = $(this.objID).getElements('.b-fon__item').getLast();
        if(this.count==0) {$('attachedfiles_selectfile_div').addClass('b-fon__item_last');} else {el.addClass('b-fon__item_last');}
    },

    toggleFormatsInfo: function() {
        $('attachedfiles_extensions').toggleClass('b-file__slide_hide');
    },

    clearFileField: function() {
        document.getElementById('attachedfiles_file_div').innerHTML = document.getElementById('attachedfiles_file_div').innerHTML;
        if (this.newDesign) {
            if (temp = $('attachedfiles_file')) temp.addEvent('change', function(){
                attachedFiles.upload.call(attachedFiles)
            });
        }
    },

    upload: function() {
        this.hideError();
        // запоминаем что в настоящее время идет загрузка
        this.uploadingNow = true;
        
        $('attachedfiles_action').set('value', 'add');

        var action_field = '';
        var action_field_id;
        var action_field_name;
        
        
        action_field = this.obj.getParent('form').getElement('input[name=action]');
        if(action_field) {
            action_field_id = action_field.get('id');
            action_field_name = action_field.get('name');
            action_field.removeProperty('id');
            action_field.removeProperty('name');
        }

        var action = this.obj.getParent('form').get('action');
        this.obj.getParent('form').set('action', this.fileHandler);
        this.obj.getParent('form').set('target','attachedfiles_hiddenframe');

        if(action_field) {
            action_field.set('id', action_field_id);
            action_field.set('name', action_field_name);
        }

        this.obj.getParent('form').submit();
        $('attachedfiles_error').setStyle('display', 'none');
        $('attachedfiles_uploadingfile').setStyle('display', 'block');
        $('attachedfiles_file').set('disabled', true);

        action_field = this.obj.getParent('form').getElement('input[name=action]');
        if(action_field) {
            action_field_id = action_field.get('id');
            action_field_name = action_field.get('name');
            action_field.removeProperty('id');
            action_field.removeProperty('name');
        }
        
        this.obj.getParent('form').set('target','');
        this.obj.getParent('form').set('action',action);

        if(action_field) {
            action_field.set('id', action_field_id);
            action_field.set('name', action_field_name);
        }

        if (!this.newDesign) { // если не новый дизайн
            if(this.count==0) {
                $('attachedfiles_selectfile_div').removeClass('b-fon__item_last');
                $('attachedfiles_uploadingfile').addClass('b-fon__item_last');
            } else {
                this.changeClasses();
            }
        }
    },

    upload_done: function(fmessage) {
        this.uploadingNow = false;
        $('attachedfiles_file').set('disabled', false);
        $('attachedfiles_uploadingfile').setStyle('display', 'none');
        if (!this.newDesign) { // если старый дизайн
            if(fmessage.error != '') {
                $('attachedfiles_error').setStyle('display', 'block');
                $('attachedfiles_errortxt').set('html', fmessage.error);
                if(this.count==0) {
                    $('attachedfiles_selectfile_div').removeClass('b-fon__item_last');
                    $('attachedfiles_error').addClass('b-fon__item_last');
                } else {
                    this.changeClasses();
                }
            } else {
                $('attachedfiles_uploadingfile').setStyle('display', 'none');
                var newFileDiv  = new Element('div', {id: 'attachedfile_'+fmessage.id, html: attachedFiles.getHTMLItem(fmessage.id, fmessage.name, fmessage.path, fmessage.size, fmessage.type)});
                newFileDiv.setProperty('class', 'b-fon__item');
                newFileDiv.inject($('attachedfiles_error'), 'after');
                this.count++;
                this.changeClasses();
            }
        } else { // для нового дизайна (сообщества)
            if(fmessage.error != '') {
                $('attachedfiles_error').setStyle('display', 'block');
                $('attachedfiles_errortxt').set('html', fmessage.error);
            } else {
                this.newFile(fmessage.id, fmessage.name, fmessage.path, fmessage.size, fmessage.type);
                this.count++;
            }
            this.changeClassesNewDesign();
        }
    },
    
    del: function(fid) {
        if (this.uploadingNow) {
            return;
        }
        this.hideError();
        $('attachedfiles_action').set('value', 'delete');
        $('attachedfiles_delete').set('value', fid);

        $('attachedfile_'+fid).destroy();

        var action = this.obj.getParent('form').get('action');
        this.obj.getParent('form').set('action',this.fileHandler);
        this.obj.getParent('form').set('target','attachedfiles_hiddenframe');
        this.obj.getParent('form').submit();
        $('attachedfiles_deletingfile').setStyle('display', 'block');
        this.obj.getParent('form').set('target','');
        this.obj.getParent('form').set('action',action);
        if (!this.newDesign) {
            if(this.count==1) {
                $('attachedfiles_deletingfile').addClass('b-fon__item_last');
            } else {
                this.changeClasses();
            }
        }
    },

    del_done: function() {
        $('attachedfiles_deletingfile').setStyle('display', 'none');
        $('attachedfiles_action').set('value', '');
        $('attachedfiles_delete').set('value', '');
        this.count--;
        if (!this.newDesign) {
            this.changeClasses();
        } else {
            this.changeClassesNewDesign();
        }
    },

    hideError: function() {
        $('attachedfiles_error').setStyle('display', 'none');
        if (!this.newDesign) {
            this.changeClasses();
        }
    },

    getHTMLItem: function(fid, fname, fpath, fsize, ftype) {
        if(ftype=='docx') ftype = 'doc';
        if(ftype=='xlsx') ftype = 'xls';
        if(ftype=='jpg') ftype = 'jpeg';
        if(ftype=='mkv') ftype = 'hdv';
        if(!(ftype=='swf' || ftype=='mp3' || ftype=='rar' || ftype=='doc' || ftype=='pdf' || ftype=='ppt' || 
             ftype=='rtf' || ftype=='txt' || ftype=='xls' || ftype=='zip' || ftype=='jpeg' || ftype=='png' || 
             ftype=='ai' || ftype=='bmp' || ftype=='psd' || ftype=='gif' || ftype=='flv' || ftype=='wav' || 
             ftype=='ogg' || ftype=='wmv' || ftype=='tiff' || ftype=='avi' || ftype=='hdv' || ftype=='ihd' || ftype=='fla')
          ) {
            ftype = 'unknown';
        }
        if (!this.newDesign) {
            var htmlItem = "<table class='b-icon-layout wdh100'>\
                                    <tbody><tr>\
                                        <td class='b-icon-layout__icon'><i class='b-icon b-icon_attach_"+ftype+"'></i></td>\
                                        <td class='b-icon-layout__files'><a class='b-icon-layout__link' href='"+fpath+"' target='_blank'>"+fname+"</a></td>\
                                        <td class='b-icon-layout__size'>"+fsize+"&nbsp;&nbsp;</td>\
                                        <td class='b-icon-layout__operate'><a class='b-icon-layout__link b-icon-layout__link_dot_a23e3e' href='#' onClick='attachedFiles.del(\""+fid+"\"); return false;'>Удалить</a></td>\
                                    </tr></tbody>\
                                </table>";
            return htmlItem;
        } else {
            var file = $('attachedfiles_template').clone();
            file.setStyle('display', '').set('id', 'attachedfile_' + fid);
            var items = file.getElements('td');
            items[0].getElement('i').addClass('b-icon_attach_' + ftype);
            items[1].getElement('a').set('text', fname).set('href', fpath);
            items[1].set('html', items[1].get('html') + ', ' + fsize);
            
            return file;
        }
    },


    init: function(sObjID, sSession, sFiles, sMaxCount, sMaxSize, sDisallowedFormats, sType, sUID, sFileHandler) {
        this.objID = sObjID;
        this.sessionid = sSession;
        this.files = sFiles;
        this.maxCount = sMaxCount;
        this.maxSize = sMaxSize;
        this.disallowedFormats = sDisallowedFormats;
        this.type = sType;
        this.uid = sUID;
        if(sFileHandler == undefined) {
            this.fileHandler = '/attachedfiles.php';
        } else {
            this.fileHandler = sFileHandler;
        }
        
        this.count = 0;

        htmlDIV_s = "<b class='b-fon__b1'></b>\
                     <b class='b-fon__b2'></b>\
                      <div class='b-fon__body'>\
                        <div id='attachedfiles_selectfile_div' class='b-fon__item b-fon__item_first'>\
                            <table class='b-file'>\
                                <tbody><tr>\
                                    <td class='b-file__button'>\
                                        <div class='b-file__wrap' id='attachedfiles_file_div'>\
                							<input class='b-file__input' type='file' id='attachedfiles_file' name='attachedfiles_file' onChange='attachedFiles.upload(); return false;'>\
                    							<a class='b-button b-button_margright_5 b-button_flat b-button_flat_grey' onclick='return false' href='#'>Выбрать файл</span></a>\
                						</div>\
                                    </td>\
                                    <td class='b-file__text'>\
                                        <p class='b-file__descript b-file__descript_padtop_10'>\
                                        Общий размер загруженных файлов не более "+(this.maxSize / (1024*1024))+" Мб. <a class='b-file__link b-file__link_color_999 b-file__link_toggle b-file__link_dot_999' href='#' onClick='attachedFiles.toggleFormatsInfo(); return false;'>Запрещенные форматы</a><span class='b-file__slide b-file__slide_hide' id='attachedfiles_extensions'>: "+this.disallowedFormats+".</span>\
                                        </p>\
                                    </td>\
                                </tr></tbody>\
                            </table>\
                        </div>\
                        <div class='b-fon__item' id='attachedfiles_uploadingfile' style='display: none;'>\
                            <table class='b-icon-layout wdh100'>\
                                <tbody><tr>\
                                    <td class='b-icon-layout__icon'><img class='b-fon__loader' src='/images/loader-gray.gif' alt='' width='24' height='24'></td>\
                                    <td class='b-icon-layout__files'><div class='b-layout__txt b-layout__txt_padtop_4'>Идет загрузка файла…</div></td>\
                                    <td class='b-icon-layout__size'>&nbsp;</td>\
                                    <td class='b-icon-layout__operate'>&nbsp;</td>\
                                </tr>\
                            </tbody></table>\
                        </div>\
                        <div class='b-fon__item' id='attachedfiles_deletingfile' style='display: none;'>\
                            <table class='b-icon-layout wdh100'>\
                                <tbody><tr>\
                                    <td class='b-icon-layout__icon'><img class='b-fon__loader' src='/images/loader-gray.gif' alt='' width='24' height='24'></td>\
                                    <td class='b-icon-layout__files'><div class='b-layout__txt b-layout__txt_padtop_4'>Идет удаление файла…</div></td>\
                                    <td class='b-icon-layout__size'>&nbsp;</td>\
                                    <td class='b-icon-layout__operate'>&nbsp;</td>\
                                </tr>\
                            </tbody></table>\
                        </div>\
                        <div class='b-fon__item' id='attachedfiles_error' style='display: none;'>\
                            <table class='b-icon-layout wdh100'>\
                                <tbody><tr>\
                                    <td class='b-icon-layout__icon'><img class='b-fon__loader' src='/images/ico_error.gif' alt='' width='22' height='18'></td>\
                                    <td class='b-icon-layout__files' id='attachedfiles_errortxt' colspan='2'></td>\
                                    <td class='b-icon-layout__operate'><a class='b-icon-layout__link b-icon-layout__link_dot_666' href='#' onClick='attachedFiles.hideError(); return false;'>Скрыть</a></td>\
                                </tr>\
                            </tbody></table>\
                        </div>\
                    ";

        htmlDIV_e = "</div>\
                     <b class='b-fon__b2'></b>\
                     <b class='b-fon__b1'></b>\
                     <input type='hidden' id='attachedfiles_uid' name='attachedfiles_uid' value='"+this.uid+"'>\
                     <input type='hidden' id='attachedfiles_action' name='attachedfiles_action' value=''>\
                     <input type='hidden' id='attachedfiles_delete' name='attachedfiles_delete' value=''>\
                     <input type='hidden' id='attachedfiles_type' name='attachedfiles_type' value='"+this.type+"'>\
                     <input type='hidden' id='attachedfiles_session' name='attachedfiles_session' value='"+this.sessionid+"'>\
                     <iframe id='attachedfiles_hiddenframe' name='attachedfiles_hiddenframe' style='display: none;'></iframe>";

        this.obj = $(this.objID);
        if(attachedFiles.obj) {
            var html = '';
            html = html + htmlDIV_s;

            for (var n=0; n<this.files.length; n++) {
                html = html + "<div class='b-fon__item' id='attachedfile_"+this.files[n].id+"'>" + this.getHTMLItem(this.files[n].id, this.files[n].name, this.files[n].path, this.files[n].size, this.files[n].type) + "</div>";
                this.count++;
            }
            
            html = html + htmlDIV_e;
            this.obj.set('html', html);

            if(this.count==0) {
                $('attachedfiles_selectfile_div').addClass('b-fon__item_last');
            } else {
                this.changeClasses();
            }
        }
    },
    
    // *************************************
    // далее код для СООБЩЕСТВ *************
    // *************************************
    
    // инициализация
    initComm: function(sObjID, sSession, sFiles, sMaxCount, sMaxSize, sDisallowedFormats, sType, sUID, sFileHandler) {
        this.objID =                sObjID;
        this.sessionid =            sSession;
        this.files =                sFiles;
        this.maxCount =             sMaxCount;
        this.maxSize =              sMaxSize;
        this.disallowedFormats =    sDisallowedFormats;
        this.type =                 sType;
        this.uid =                  sUID;
        this.newDesign =            true;
        if(sFileHandler == undefined) {
            this.fileHandler = '/attachedfiles.php';
        } else {
            this.fileHandler = sFileHandler;
        }
        //this.fileHandler =          '/attachedfiles.php';
        
        this.obj = $(this.objID);
    },
    // запускается после domready
    initCommDomready: function () {
        var temp;
        // реакция на выбор файла
        if (temp = $('attachedfiles_file')) temp.addEvent('change', function(){
            attachedFiles.upload.call(attachedFiles)
        });
        // кнопка СКРЫТЬ ERROR
        if (temp = $('attachedfiles_hide_error')) temp.addEvent('click', function(){
            $('attachedfiles_error').setStyle('display', 'none');
        });
        // уже загруженные файлы
        var files = attachedFiles.files;
        for (var f in files) {
            if (!files.hasOwnProperty(f)) continue
            attachedFiles.newFile(files[f].id, files[f].name, files[f].path, files[f].size, files[f].type);
            attachedFiles.count++;
            attachedFiles.changeClassesNewDesign();
        }
        // закрыть окно с требованиями к файлам
        if (temp = $('attachedfiles_close_info')) temp.addEvent('click', attachedFiles.closeInfo);
    },
    // смена оформления
    changeClassesNewDesign: function () {
        if (this.count > 0) {
            $(this.objID).addClass('b-fon__body').addClass('b-fon__body_bg_f0ffdf');
        } else {
            $(this.objID).removeClass('b-fon__body').removeClass('b-fon__body_bg_f0ffdf');
        }
    },
    
    // добавить новый файл
    newFile: function (fid, fname, fpath, fsize, ftype) {
        var file = this.getHTMLItem(fid, fname, fpath, fsize, ftype);
        //file = file.inject('attachedfiles_table', 'top');
        file = file.inject('attachedfiles_table', 'before');
        file.getElements('td')[2].getElement('a').addEvent('click', function(){attachedFiles.del(fid)});
    },
    
    // закрыть окно с требованиями к файлу
    closeInfo: function () {
        $(this).getParent('div#attachedfiles_info').addClass('b-filter__toggle_hide');
    }
};

window.addEvent('domready', function(){
    if (attachedFiles.newDesign) attachedFiles.initCommDomready();
})

