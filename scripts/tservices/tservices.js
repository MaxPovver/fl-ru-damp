var tuDayList = {
    1:'1 день',
    2:'2 дня',
    3:'3 дня',
    4:'4 дня',
    5:'5 дней',
    6:'6 дней',
    7:'7 дней',
    8:'8 дней',
    9:'9 дней',
    10:'10 дней',
    14:'14 дней',
    21:'21 день',
    30:'30 дней',
    45:'45 дней',
    60:'60 дней',
    90:'90 дней'
};

var tuDayListWithZero = {
    0:'тот же срок',
    1:'1 день',
    2:'2 дня',
    3:'3 дня',
    4:'4 дня',
    5:'5 дней'
};
  
function TServices()
{
    // Здесь уже domready.

    TServices=this; // ie ругался без этого, пока не понял.
    
    var serviceCloseMsgDisable = 'Типовая услуга будет немедленно снята с публикации. Подтверждаете?';
    var serviceCloseMsgEnable  = 'Типовая услуга будет опубликована. Подтверждаете?';
    var serviceDeleteMsg       = 'Типовая услуга будет удалена. Подтверждаете?';
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Общая инициализация 
     * 
     */
    this.init = function() 
    {
       //выпадающие списки на кнопках в профиле
       $$('.b-button-multi__link_arr').addEvent('click',function(){
           this.getNext('.b-shadow').toggleClass('b-shadow_hide');
           return false;
       });

       //-----------------------------------------------------------------------
       
        $$('.i-shadow.__tooltip').addEvents({
            mouseover:function(){this.getElement('.b-shadow').removeClass('b-shadow_hide');},
            mouseout:function(){this.getElement('.b-shadow').addClass('b-shadow_hide');}
        });

       //-----------------------------------------------------------------------

       this.initCard();

       //-----------------------------------------------------------------------

       this.initFormNewTServices('__form__tu_new');
    };    

    this.checkPreview = function() {
        var uploader_element = $('preview_uploader');
        if (uploader_element.getElements('ul.qq-upload-list-selector li.b-file_attach-files_element').length) {
            return true;
        }
        
        var uploader_error_element = uploader_element.getElement('.error-message');
                   
        if(uploader_error_element) {
            var hide_error = function(){
                this.addClass('b-shadow_hide');
                return false;
            };
            uploader_error_element.addEvents({
                'click':hide_error
            }).removeClass('b-shadow_hide').getElement('.b-txt').set('html','Обязательно для заполнения.');
        }

        return false;
    }

    //----------------------------------------------------------------------- 


    /**
     * Инициализация формы добавления/редактирования типовой услуги
     * 
     */
    this.initFormNewTServices = function(id)
    {
        var form = $(id);
        if(!form) return false;

        Locale.use("ru-RU");
        Locale.define(Locale.getCurrent().name, 'FormValidator', {
            requiredChk:$('agree').get('title'),
            required:'Обязательно для заполнения.',
            price:'Обязательно для заполнения.<br/>Минимум 300 рублей.',
            uint:'Введите целое <br/>значение стоимости.',
            category:'Выберите категорию.',
            video:'Некорректная ссылка.',
            minLength:'Пожалуйста, введите от {minLength} символов (Вы ввели {length}).',
            maxLength:'Пожалуйста, введите до {maxLength} символов (Вы ввели {length}).',
            tags:'Укажите до 10 слов через запятую.'
        });

        var submit = $$('.__send_btn');
        
        var validElems = [];
        $$("[data-validators]").each(function(el){
            if( !el.get("data-validators") && !el.hasClass('validate-required-check') ) return;
            validElems[el.get("name")] = {
                is_valid:false,
                elid:el.get('id'),
                is_error:el.getParent().hasClass('b-combo__input_error') || el.getParent().hasClass('b-textarea_error') 
            };
        });
        Array.prototype.is_valid = function(){
            //submit.addClass('b-button_disabled');
            for(key in this) {
                if(typeof this[key].is_valid === "undefined") continue;
                if(!this[key].is_valid) return false;
            }
            //submit.removeClass('b-button_disabled');
            return true;
        };
        Array.prototype.get_error_element = function(){
            var els = [];
            for(key in this) if(this[key].is_error) els.push(this[key].elid);
            return els;    
        };
        
        var formValidator = new Form.Validator($(id), {
            //useTitles: true,
            serial:false,
            onElementPass: function(el) {
                
                if(el.hasClass('ignoreValidation')) return false;
                
                if(el.type === 'textarea') el.getParent().removeClass('b-textarea_error');
                else if(el.type === 'input') el.getParent().removeClass('b-combo__input_error');
                
                var error_id = $('error_' + el.get('id'));
                if(!error_id) return;
                error_id.addClass('b-shadow_hide');
                
                validElems[el.get('name')].is_valid = true;
                validElems.is_valid();
            },
            
            onElementFail: function(el, validator) {
                var error_css = null;
                if(el.type === 'textarea') error_css = 'b-textarea_error';
                else if(el.type === 'input') error_css = 'b-combo__input_error';
                
                if(error_css) el.getParent().addClass(error_css);
                
                var elid = el.get('id');
                var error_txt = $('error_txt_' + elid);
                
                if(error_txt)
                {
                    error_txt.set('html',this.getValidator(validator[0]).getError(el));
                    $('error_' + elid).removeClass('b-shadow_hide');
                }
                
                validElems[el.get("name")].is_valid = false;
                validElems.is_valid();
            },
                    
            onElementValidate: function(passed, element, validator, is_warn){}
        });

        /* ВРЕМЕННО ДЛЯ ОТЛАДКИ ОТКЛЮЧАЕМ ВАЛИДАЦИЮ НА КЛИЕНТЕ*/
        //formValidator.stop();
        //submit.removeClass('b-button_disabled');
        
        //Валидатор заполнения двух полей
        formValidator.add('relation',{
            errorMsg:Form.Validator.getMsg.pass('required'),
            test: function(element, props){
                var rel_name = element.get('data-rel');
                
                if(!rel_name) return false;
                
                rel_name = rel_name.split(':');
                var master_name = element.get('name');
                var slave_name = master_name.replace(rel_name[0],rel_name[1]);
                
                if(!slave_name) return false;
                
                var master_value = element.get('value');
                var slave_value = $(slave_name).get('value');                

                if(!master_value.length && slave_value.length) return false;
                
                return true;
            }        
        });
        
        //Валидатор только цифр если они есть
        formValidator.add('intOrEmpty', {
            errorMsg:Form.Validator.getMsg.pass('uint'),
            test: function(element, props){
                var value = element.get('value');
                if(!value.length) return true;
                return (/^[\-]?[1-9]\d*$/).test(value);
            }        
        });        
        
        //Добавим валидатор только цифр
        formValidator.add('uint', {
            errorMsg:Form.Validator.getMsg.pass('uint'),
            test: function(element, props){
                return (/^[1-9]\d*$/).test(element.get('value'));
            }        
        });
        
        //Добавим валидатор для категорий
        formValidator.add('category', {
            errorMsg:Form.Validator.getMsg.pass('category'),
            test: function(element, props){
                var element_db_id = $(element.get('name') + '_db_id');
                if(!element_db_id) return false;
                return (/^[1-9]\d*$/).test(element_db_id.get('value'));
            }
        });
        
        //Добавим валидатор для ссылок на видео
        formValidator.add('video', {
            errorMsg:Form.Validator.getMsg.pass('video'),
            test: function(element, props){
                var value = element.get('value');
                var result = (value.length === 0) || 
                       //Check YouTube link
                       (/^(?:(?:http|https):\/\/|)(?:www\.|)youtube\.com\/watch\?(?:.*)?v=([a-zA-Z0-9_\-]+)/i).test(value) || 
                       (/^(?:(?:http|https):\/\/|)(?:www\.|)youtube\.com\/embed\/([a-zA-Z0-9_\-]+)/i).test(value) || 
                       (/^(?:(?:http|https):\/\/|)(?:www\.|)youtu\.be\/([a-zA-Z0-9_\-]+)/i).test(value) || 
                       //Check Vimeo link
                       (/^(?:(?:http|https):\/\/|)(?:www\.|)vimeo\.com\/([a-zA-Z0-9_\-]+)(&.+)?/i).test(value) || 
                       (/^(?:(?:http|https):\/\/|)player\.vimeo\.com\/video\/([a-zA-Z0-9_\-]+)(&.+)?/i).test(value) || 
                       //Check RuTube link
                       (/^(?:(?:http|https):\/\/|)(?:www\.|)rutube\.ru\/video\/embed\/([a-zA-Z0-9_\-]+)/i).test(value) || 
                       (/^(?:(?:http|https):\/\/|)(?:www\.|)rutube\.ru\/tracks\/([a-zA-Z0-9_\-]+)(&.+)?/i).test(value) || 
                       (/^(?:(?:http|https):\/\/|)(?:www\.|)rutube\.ru\/video\/([a-zA-Z0-9_\-]+)\//i).test(value);
               
               if(result) $('add-video').show();
               else $('add-video').hide();
               
               return result;
            }
        });
        
        //Добавим валидатор для стоимости
        formValidator.add('price', {
            errorMsg:Form.Validator.getMsg.pass('price'),
            test: function(element, props){
                var value = parseInt(element.get('value'));
                return (value >= 300);
            }
        });
        
        //Добавим валидатор для тегов
        formValidator.add('tags', {
            errorMsg:Form.Validator.getMsg.pass('tags'),
            test: function(element, props){
                var value = element.get('value').split(',').length;
                return (value <= 10);
            }
        });
        

        //Клик на сообщение об ошибке - убираем ошибку
        $$(".error-message").addEvent("click", function(){
            this.addClass('b-shadow_hide');
            var input = $(this.id.replace("error_", ""));
            if(!input) return;
            var error_css = 'b-combo__input_error';
            if(input.type === 'textarea') error_css = 'b-textarea_error';
            input.getParent().removeClass(error_css);
        }).setStyle("cursor", "pointer");
        
        
        //Клик в поле ввода с ошибкой - убираем ошибку
        $$('.b-combo__input-text, .b-textarea__textarea').addEvent('focus',function(){
            if(this.type === 'textarea') this.getParent().removeClass('b-textarea_error');
            var error_id = $('error_' + this.get('id'));
            if(!error_id) return;
            error_id.addClass('b-shadow_hide');
        });
       
        //Клик на кнопку отправки формы
        submit.addEvent("click", function(){
            if(this.hasClass('b-button_disabled')) return false;
            
            var is_errors = ($$('.b-combo__input_error, .b-textarea_error').length > 0);
            var is_without_publish = this.hasClass('__send_without_publish_btn');
            var is_validate = formValidator.validate();
            var is_preview = (TServices.checkPreview() || is_without_publish);

            if(!is_errors && is_preview && is_validate) 
            {
                if(is_without_publish) $$('input[name=active]').set('value',0);
                
                submit.addClass('b-button_disabled');
                
                formValidator.element.submit();
            }
            else 
            {
                var first_field = $$('.validation-failed, .b-combo__input_error, .b-textarea_error, .error-message:not(.b-shadow_hide)')[0];
                if(first_field) 
                {
                    var fcoord = first_field.getCoordinates();
                    new Fx.Scroll(window).start(0,fcoord.top - 80);
                }
            }
            return false;
        });
        
        
        //Если на сервере валидация не прошла по возвращению формы
        //запускаем валидацию на клиенте игнорируя поля с ошибками от сервера
        /*
        if((typeof revalidate_tu_form !== "undefined") && 
           (revalidate_tu_form === true))
        {
            var els = validElems.get_error_element();
            els.each(function(value){ formValidator.ignoreField(value); });
            formValidator.validate();
            els.each(function(value){ formValidator.enforceField(value); });
        }
        //Иначе помечаем пустое поле видео-ссылки как валидным на клиенте
        else
        {
            validElems['videos[0]'].is_valid = true;
            validElems['extra[0][price]'].is_valid = true;
            validElems['extra[0][title]'].is_valid = true;
        }
        */
       
        //Поскольку отказалить от блокированных кнопок отправки формы смысла использования
        //массива валидных полей validElems и превалидации на клиенте при ошибке
        //на сервере нет.
        
        
        //----------------------------------------------------------------------
        
        
        //Клонируем события рекурсивно
        Element.implement({
            cloneEventsDeep: function(from, type){
                this.getChildren().each(function(item, index) {
                    item.cloneEventsDeep(from.getChildren()[index], type);
                });
                return this.cloneEvents(from, type);
            }
        });
        
        
        //Вешаем событие на удаление video полей
        $$('#video_items .b-button').addEvent('click',function(){
            var p = this.getParent('table');
            var input = p.getElement('input');
            
            if(input)
            {
                formValidator.ignoreField(input);
                
                delete validElems[input.get('name')];
                validElems.is_valid();
            }

            p.destroy();
            return false;
        });
        
        //Делаем шаблон для video полей
        var video_fields = $$('#video_items table:first-child')[0];
        var video_fields_template = video_fields.clone().cloneEventsDeep(video_fields);
        video_fields_template.getElements('input').set('value','');
        video_fields_template.getElements('.b-combo__input_error').removeClass('b-combo__input_error');
        video_fields_template.getElements('.error-message').addClass('b-shadow_hide').getElement('.b-txt').set('html','');
        
        var video_tabindex = 1;
        
        //Скрываем и убиваем события на первом крестике
        $$('#video_items table:first-child .b-button').setStyle('visibility','hidden').removeEvents('click');
        
        //Если есть шаблон можно добавлять
        if(video_fields_template)
        {
             $('add-video').addEvent('click',function(){
                 var cnt = $$('#video_items table').length;
                 var element = video_fields_template.clone().cloneEventsDeep(video_fields_template);
                 
                 element.getElements('input').each(function(el){
                    var name = el.name.replace(/\[\d+\]/g, '[' + cnt + ']');
                    var label = el.set({'name':name,'id':name,'tabindex':parseInt(el.get('tabindex'))+video_tabindex}).getNext('label');
                    if(label) label.set('for',name);
                    
                    var combo = el.getParent('.b-combo__input');
                    if(combo) ComboboxManager.initCombobox([combo]);
                    
                    var err = el.getParent('.b-combo').getNext('.error-message');
                    if(err) 
                    {
                        err.set('id','error_' + name)
                           .getElement('.b-txt')
                           .set('id','error_txt_' + name);
                    }
                    
                    if(el.get("data-validators"))
                    {
                        formValidator.watchFields([el]);
                        validElems[name] = {
                            elid:name,
                            is_valid:true,
                            is_error:false
                        };
                    }
                });

                $('video_items').adopt(element);   
                video_tabindex++;
                return false;
             });
        }        
        
        //----------------------------------------------------------------------
        
        
        //Вешаем событие на удаление extra полей
        $$('#extra_items .b-button').addEvent('click',function(){
            var p = this.getParent('table');
            p.getElements('input').each(function(el){
                formValidator.ignoreField(el);
                delete validElems[el.get('name')];
                validElems.is_valid();               
            });
            p.destroy();
            return false;
        });
        
        
        //Делаем шаблончик для extra полей
        var extra_fields = $$('#extra_items table:first-child')[0];
        var extra_fields_template = extra_fields.clone().cloneEventsDeep(extra_fields);
        extra_fields_template.getElements('input[type=hidden]').destroy();
        extra_fields_template.getElements('input:not(:readonly)').set('value','');
        extra_fields_template.getElements('.b-combo__input_error').removeClass('b-combo__input_error');
        extra_fields_template.getElements('.error-message').addClass('b-shadow_hide').getElement('.b-txt').set('html','');

        var extra_tabindex = 3;

        //Скрываем и убиваем события на первом крестике
        $$('#extra_items table:first-child .b-button').setStyle('visibility','hidden').removeEvents('click');
        
        //Если есть шаблон можно добавлять
        if(extra_fields_template)
        {

            //Добавление новых extra полей на основе шаблончика
            $('add-extra').addEvent('click',function(){
                var cnt = $$('#extra_items > table').length;
                var element = extra_fields_template.clone().cloneEventsDeep(extra_fields_template);
                
                element.getElements('input').each(function(el){
                    var name = el.name.replace(/\[\d+\]/g, '[' + cnt + ']');
                    var label = el.set({'name':name,'id':name,'tabindex':parseInt(el.get('tabindex'))+extra_tabindex}).getNext('label');
                    if(label) label.set('for',name);

                    var combo = el.getParent('.b-combo__input');
                    if(combo) {
                        var classes = combo.get('class').replace(/drop_down_default_\d+/g, 'drop_down_default_1');
                        combo.set('class',classes);
                        ComboboxManager.initCombobox([combo]);
                    }
                    
                    var err = el.getParent('.b-combo').getNext('.error-message');
                    if(err) 
                    {
                        err.set('id','error_' + name)
                           .getElement('.b-txt')
                           .set('id','error_txt_' + name);
                    }
              
                    if(el.get("data-validators"))
                    {
                        formValidator.watchFields([el]);
                        validElems[name] = {
                            elid:name,
                            is_valid:true,
                            is_error:false
                        };
                    }
                });

                $('extra_items').adopt(element);
                extra_tabindex+=3;
                return false;
            });
        }
        

        //"Могу выполнить срочно за дополнительные" поля
        //вешаем и управляем валидатором 
        //и общей группой статусов проверок по форме elements_status
        $('express_activate').addEvent('click',function(){
            var inputs = this.getParent('td').getElements('input[type="text"]');
            if(!inputs) return false;
            
            if(this.get('checked'))
            {
                inputs.set({'disabled':false})
                      .getParent()
                      .removeClass('b-combo__input_disabled');

               inputs[0].set({'data-validators':'required uint'});    

               if(!formValidator.hasValidator(inputs[0],'required'))
               {
                   formValidator.watchFields([inputs[0]]);
               }
               else
               {
                   formValidator.enforceField(inputs[0]);
               }
               
               var name = inputs[0].get('name');
               validElems[name] = {
                   elid:inputs[0].get('name'),
                   is_valid:false,
                   is_error:false
               };
               
               formValidator.validateField(inputs[0]);
            }
            else
            {
                inputs.set('disabled',true)
                      .getParent()
                      .addClass('b-combo__input_disabled')
                      .removeClass('b-combo__input_error')
                      .getNext()
                      .addClass('b-shadow_hide');
              
                if(formValidator.hasValidator(inputs[0],'required'))
                {
                     formValidator.ignoreField(inputs[0]);
                     
                     delete validElems[inputs[0].get('name')];
                }
            }
            
            validElems.is_valid();
        });
        
        
        //Инициализация загрузчиков
        this.initUploader(form, 'preview');
        this.initUploader(form, 'files');
        
        
        //----------------------------------------------------------------------
        
        //Вешаем событие на отображение/скрытие всплывающих подсказок
        $$('.b-filter__body .b-filter__link').removeEvents('click');
        $$('.b-filter__body .b-filter__link').addEvents({
            'mouseover':this.toogle_tooltip,
            'mouseout':this.toogle_tooltip//,
            //'click':null//function(event){event.stop();}
        });
        
        
        //----------------------------------------------------------------------
        
        
        
        var days = ComboboxManager.getInput("days");
        var express_days = ComboboxManager.getInput("express[days]");
        
        days.b_input.addEvent('bcombochange',function(){
            
            var max = parseInt($("days_db_id").get("value"));
            var cur = parseInt($("express[days_db_id]").get("value"));
            var params = new String('max=' + $("days_db_id").get("value"));
            
            express_days.MIN_COUNT_ITEMS = 1;
            express_days.loadData("getdays", 0, 1, params);
            express_days.selectItemById((cur>=max)?1:cur);
            $("express[days_db_id]").set("value",(cur>=max)?1:cur);
            
            var express_activate = $('express_activate');
            var express = express_activate.getParent('tr');
            
            if(max == 1)
            {
                if(express_activate.get('checked')) express_activate.set('checked','');
                express.hide();
            }
            else
            {
                express.show();
            }
            
        }).fireEvent('bcombochange');
        
        
    };


    //--------------------------------------------------------------------------

    /**
     * Инициализирует файловый загрузчик
     * 
     * @param {String} name - "preview" или "files" 
     * @returns {undefined}
     */
    this.initUploader = function(form, name) {
        var sessName, is_multiple, extentions;
        if (name == "files") {
            sessName = 'uploader_sess';
            is_multiple = !qq.ios7();
            extentions = ['jpeg', 'jpg', 'gif', 'png'];
        }
        if (name == "preview") {
            sessName = 'preview_sess';
            is_multiple = false;
            extentions = ['jpeg', 'jpg', 'png'];
        }
        
        var uploader_element = $(name+'_uploader');
        
        var _exist_files = uploader_element.getElements('.test');
        var exist_files = null;
        
        if(_exist_files)
        {
            exist_files = _exist_files.clone();
            _exist_files.destroy();
        }

        var sess = function(){return uploader_element.getElement('#'+sessName).get('value');};
        
        var thumbnailuploader = new qq.FineUploader({
            multiple: is_multiple,
            template: uploader_element,//"qq-simple-thumbnails-template",
            element: uploader_element,
            request: {
                endpoint: '/tu/uploader.php',//'/json/uploader/tservices/',
                params: {
                    name: name,
                    sendThumbnailUrl:true,
                    u_token_key:_TOKEN_KEY,
                    sess:sess,
                    owner_id:_OUID
                }
            },
            validation: {
                allowedExtensions: extentions//,
               // sizeLimit: 51200 ,
               //itemLimit: 3
            },
            deleteFile: {
                enabled:true,
                method:'POST',
                endpoint:'/tu/uploader.php',//'/json/uploader/tservices/',
                params:{
                    name: name,
                    sess:sess,
                    u_token_key:_TOKEN_KEY,
                    owner_id:_OUID
                }
            },
            display: {
                prependFiles: false
            },
            classes: {
                hide:'b-file_attach-files_hide',
                dropActive:'b-file_dragdrop_active'
            },
            /*
            chunking: {
                enabled: true,
                partSize:200000
            },*/
            text: {
                defaultResponseError: 'Ошибка загрузки файла.'//,
                //sizeSymbols: ['kB', 'MB', 'GB', 'TB', 'PB', 'EB']
                //formatProgress: "{percent}%" //{percent}% of {total_size}
            },
            callbacks: {
               /* 
               onComplete: function(id, fileName, responseJSON){
                   if(responseJSON.success)
                   {
                       var item = thumbnailuploader.getItemByFileId(id);
                       item.set('data-image',responseJSON.previewUrl).addClass('mylinks');


                   }
               },*/ 
               onSubmit: function() {
                   var uploader_error_element = uploader_element.getElement('.error-message');
                   if(uploader_error_element) uploader_error_element.addClass('b-shadow_hide');
               }, 
               onError: function(id, name, reason, xhr) {
                   var uploader_error_element = uploader_element.getElement('.error-message');
                   
                   if(uploader_error_element)
                   {
                       var hide_error = function(){
                           this.addClass('b-shadow_hide');
                           return false;
                       };
                       uploader_error_element.addEvents({
                           'click':hide_error//,
                           //'mouseover':hide_error
                       }).removeClass('b-shadow_hide')
                         .getElement('.b-txt')
                         .set('html',reason);
                   }

                   var item = thumbnailuploader.getItemByFileId(id);
                   if(item) item.hide();
                }
            },
            showMessage: function(message){},
            messages: {
                typeError: 'Файл {file} имеет недопустимый формат. Разрешены: {extensions}.',
                sizeError: 'Размер файла {file} превышает допустимые {sizeLimit}.',
                minSizeError: 'Размер файла {file} меньше допустимого {minSizeLimit}.',
                emptyError: 'Файл {file} пуст.',
                noFilesError: 'Отсутствуют файлы для загрузки',
                tooManyItemsError: 'Слишком много файлов ({netItems}) планируется загрузить, когда лимит всего {itemLimit}.',
                retryFailTooManyItems: 'Попытка не удалась - вы достигли лимита загрузки файлов.',
                onLeave: 'Файл еще загружаются, если вы уйдёте загрузка будет прервана.'
            },/*   
            failedUploadTextDisplay: {
                mode: 'custom',
                //maxChars: 40,
                //responseProperty: 'error',
                enableTooltip: false
            },*/
            debug: false//,
            //demoMode: true // Undocumented -> Only for the gh-pages demo website
        });
        
        
        if(exist_files && uploader_element)
        {
            uploader_element.getElement('.qq-upload-list-selector').adopt(exist_files);  

            
            exist_files.each(function(el){
                var deleteElem = el.getElement('.qq-upload-delete-selector');
                if (deleteElem != null) {
                    deleteElem.addEvent('click',function(){
                    
                        this.getPrevious().removeClass('b-file_attach-files_hide');

                        var req = new Request.JSON({
                            url: thumbnailuploader._options.deleteFile.endpoint,
                            onSuccess: function(responseJSON){
                                //if(responseJSON.error){}
                                if(responseJSON.success === true)
                                {
                                   el.destroy();  
                                }
                            }
                        });

                        req.delete({
                            sess:sess(),
                            qquuid:this.get('data-qquuid'),
                            u_token_key:_TOKEN_KEY,
                            hash:this.get('data-hash'),
                            id:form.getElement('input[name=id]').get('value'),
                            owner_id:_OUID
                        });

                        return false;
                    });
                }
            });
        }
        
    };
        
    /**
     * Создает прилипающий заголовок
     * 
     * @param {type} id
     * @param {type} is_try_stop
     * @returns {Boolean}
     */
    this.stick_it = function(id, is_try_stop)
    {
        var sticky = $(id);
        if(!sticky) return false;  
        
        var default_coords = sticky.getCoordinates();
        var sticky_parent = sticky.getParent();
        var bar = $$('.b-bar');
        var bar_size = (bar)?bar[0].getSize():{y:0};
        var stop_stick = false;
        var is_done = false;
		var winSize;
        if(bar_size.y > 0) bar_size.y -= 2;
        
        window.addEvent('scroll', function() {
			
			winSize = $(window).getSize();
			if(winSize.x>999){
			
            var scrollTop = window.getScrollTop();
            var sticky_size = sticky.getSize();

            if((scrollTop > default_coords.top - bar_size.y) 
               && !stop_stick)
            {
                if(!is_done)
                {
                    sticky_parent.setStyles({
                        'height':sticky_size.y
                    });
                    sticky.setStyles({
                        'position':'fixed',
                        'top':bar_size.y,
                        'z-index':'9',
                        'width':sticky_size.x,
                        'height':sticky_size.y,
                        'overflow':'visible'
                    });
                    is_done = true;
                }
            }
            else if((scrollTop + bar_size.y) <= default_coords.top)
            {
                if(is_done)
                {
                    sticky_parent.setStyles({
                        'height':'auto'
                    });              
                    sticky.setStyles({
                        'top':0,
                        'position':'static',
                        'width':'auto',
                        'height':'auto',
                        'overflow':'visible'
                    });
                
                    is_done = false;
                }
            }
            
			}
        }).fireEvent('scroll');
        
        
        //Обрабатываем событие если нужно блокировать прилипание
        window.addEvent('is_stop_stick', function(is_stop_stick){
            if(is_try_stop == true) stop_stick = is_stop_stick;  
        });
        
        
        //Адаптация при ресайзе
        window.addEvent('resize', function(){
			winSize = $(window).getSize();
			if(winSize.x>999){
			
            if(sticky.style.position === 'fixed')
            {
                //TODO: привязка к верстке - некрасиво :)
                var layout_size = $$('.b-layout__side_content').getLast().getSize();

                sticky.setStyles({
                    //'top':bar_size.y,
                    'width':layout_size.x
                });
            }
            window.fireEvent('scroll');
			
			}
        }).fireEvent('resize');
        
        return true;
    };
    
    
    
    

    /**
     * Инициализация прилипающих 
     * при прокрутке заголовков
     * 
     * @param {type} id
     * @returns {Boolean}
     */
    this.init_sticky = function()
    {
        var sticky_top_id = 'sticky';
        var sticky_bot_id = 'sticky-bottom';
        
        var sticky_top = $(sticky_top_id);
        var sticky_bot = $(sticky_bot_id);
        
        //Создаем прилипание для шапок
        TServices.stick_it(sticky_top_id,true);
        TServices.stick_it(sticky_bot_id,false);
        
        
        //Создаем взаимодействие шапок
        if(sticky_top && sticky_bot)
        {
            var sticky_top_default_coords = sticky_top.getCoordinates();
            var sticky_bot_default_coords = sticky_bot.getCoordinates();
            var is_stop_stick = false;
            var is_morph_done = false;

            //Создаем событие говорящее на что обе шапки в пределах видимости окна
            //поэтому нет смысла прилипать одной расположенной выше sticky_top
            window.addEvent('resize', function(){
                var wndSize = window.getSize();
                is_stop_stick = (wndSize.y > (sticky_bot_default_coords.top - sticky_top_default_coords.top));
                window.fireEvent('is_stop_stick',is_stop_stick);
            }).fireEvent('resize'); 
            
            
            //Создаем анимацию верхней шапки если в области видимости показалась нижняя
            //Если обе на экране - анимацию отключаем
            window.addEvent('scroll', function() {
                if(is_stop_stick) return false;
                
                var scrollTop = window.getScrollTop();
                var bar = $$('.b-bar');
                var bar_size = (bar)?bar[0].getSize():{y:0};
                var sticky_top_size = sticky_top.getSize();
                
                if(bar_size.y > 0) bar_size.y -= 2;
                
                var wndSize = window.getSize();
                var is_sticky_bot_viewport = (sticky_bot_default_coords.top < (scrollTop + wndSize.y));

                    
                if(is_sticky_bot_viewport)
                {
                    if(!is_morph_done)
                    {
                        sticky_top.set('morph', {
                            duration: 200, 
                            link: 'ignore'
                        })
                        .morph({
                            'top':(sticky_top_size.y + bar_size.y)*-1
                        });
                        
                        is_morph_done = true;
                    }
                }
                else
                {
                    if(is_morph_done)
                    {
                        sticky_top.set('morph', {
                            duration: 100, 
                            link: 'ignore'
                        })
                        .morph({
                            'top':bar_size.y
                        });
                        
                        is_morph_done = false;
                    }
                 }
                
            }).fireEvent('scroll');
        }
        
        return true;
    };


    //----------------------------------------------------------------------- 


    /**
     * Показываем/скрываем и позицианируем 
     * всплывающие подсказки 
     * 
     * @returns {Boolean}
     */
    this.toogle_tooltip = function()
    {
        var hover_size = this.getSize();
        var tooltip = this.getNext('.b-filter__toggle');

        if(!tooltip) return false;

        if(tooltip.hasClass('b-filter__toggle_hide'))
        { 
            var tooltip_size = tooltip.removeClass('b-filter__toggle_hide').getSize();
            tooltip.setStyles({
                'top': hover_size.y - tooltip_size.y/2,
                'left': hover_size.x + 20,
                'z-index':10
            });
        }
        else
        {
            tooltip.addClass('b-filter__toggle_hide');
        }        
    };
    
    
    //----------------------------------------------------------------------- 
    
    
    /**
     * Отправка формы с запросом на удаление услуги
     * 
     * @param {type} itm
     * @param {type} idx
     * @returns {undefined}
     */
    this.onServiceDeleteSubmit = function(itm, idx)
    {
        if(window.confirm(serviceDeleteMsg))
        {
            this.onServiceActionSubmit(itm,idx);
        }
    };
    
    
    //----------------------------------------------------------------------- 
    
    
    /**
     * Отправка формы с запросом на снятие/публикацию услуги 
     * 
     * @param {type} itm
     * @param {type} idx
     * @returns {undefined}
     */
    this.onServiceCloseSubmit = function(itm, idx)
    {
        var is_active = (parseInt(itm.get('data-active')) === 1);
        var msg = (is_active)?serviceCloseMsgDisable:serviceCloseMsgEnable;
        if(window.confirm(msg))
        {
            this.onServiceActionSubmit(itm,idx);
        }
    };
    
    
    //----------------------------------------------------------------------- 
    
    
    /**
     * Создание и отправка скрытой формы с параметрами
     * и блокировка кнопки от двойного нажатия
     * 
     * @param {type} itm
     * @param {type} idx
     * @returns {Boolean}
     */
    this.onServiceActionSubmit = function(itm, idx)
    {
        if(itm.retrieve('is_submit')) return false;
        var form = new Element('form', {'action':itm.get('data-url'),'method':'post'});
        var idx = new Element('input', {'type':'hidden', 'value':idx,'name':'id'});
        var token = new Element('input', {'type':'hidden','value':_TOKEN_KEY,'name':'u_token_key'});
        
        form.adopt(idx,token);
        form.setStyle('display','none').inject(itm, 'after');
        form.submit();
        itm.store('is_submit',true).addClass('b-button_disabled');
    };
    
    
    
    //----------------------------------------------------------------------- 
    
    
    /**
     * Ajax запрос на снятие/публикацию услуги
     * 
     * @param {type} itm
     * @param {type} idx
     * @returns {Boolean}
     */
    /*
    this.onServiceClose = function(itm, idx) 
    {
        var is_active = (parseInt(itm.get('data-active')) === 1);
        var msg = (is_active)?serviceCloseMsgDisable:serviceCloseMsgEnable;
        
        if(window.confirm(msg))
        {
            Server.create().send({id:idx, action:'close'}, itm.get('data-url'), {
                    onError: function() {},
                    onSuccess: function(resp) {
                        var res = JSON.decode(resp.result.tree);
                        if (res.success) {
                            itm.set({
                                'data-active':(!is_active)?1:0,
                                'html':itm.get('data-txt-' + ((!is_active)?'disable':'enable'))
                            });

                            if(window.location.pathname !== res.redirect)
                                window.location = res.redirect;
                        }
                    }
                }
            );
        }
        return false;
    };
    */
    
    //-----------------------------------------------------------------------    
    
    
    /**
     * Отправляем форму скриптом
     * 
     * @param element itm
     * @param string form
     * @returns Boolean
     */
    this.onSendToCbr = function(itm ,form)
    {
        if(itm.retrieve('is_submit')) return false;
        itm.store('is_submit',true).addClass('b-button_disabled');
        
        var form_element = $(form);
        if(!form_element) return false;
        
        var paytype_popup = $$('input[name=paytype]:checked');
        var paytype_value = (paytype_popup.length)?paytype_popup[0].get('value'):'0';
        var paytype_element = new Element('input', {'type':'hidden', 'value':paytype_value,'name':'order_paytype'});

        form_element.adopt(paytype_element);
        form_element.submit();
        
        return false;
    };
    
    //-----------------------------------------------------------------------
    
    /**
     * Показываем попап при заказе услуги
     * 
     * @returns {Boolean}
     */
    this.showPopup = function()
    {
        $('tservices_orders_status_popup').removeClass('b-shadow_hide');
        return false;
    };
    
    
    
    //-----------------------------------------------------------------------
    
    
    /**
     * Скрываем попап
     * 
     * @returns {Boolean}
     */
    this.closePopup = function()
    {
        $('tservices_orders_status_popup').addClass('b-shadow_hide');
        return false;
    };
    
    
    //-----------------------------------------------------------------------

    
    /**
     * Инициализация скрипта пересчета стоимости и сроков услуги
     * в зависимости от выбранных доп.опций и срочности
     *
     * @returns {Boolean}
     */
    this.initPriceCalc = function()
    {
        var __tservice_price = $$('.__tservice_price');
        var __tservice_price2 = $$('.__tservice_price2');
        var __tservice_price3 = $$('.__tservice_price3');
        var __tservice_days = $$('.__tservice_days');
        var __tservice_extras = $$('.__tservice_on_extra');
        var __tservice_express = $$('.__tservice_on_express')[0];

        var base_price = parseInt(__tservice_price.get('data-price'));
        var base_days = parseInt(__tservice_days.get('data-days'));

        var update_info = function(){
            var _price = base_price;
            var _days = base_days;
            
            __tservice_extras.each(function(el){
                if(!el.get('checked')) return true;
                _price += parseInt(el.get('data-price'));
                _days += parseInt(el.get('data-days'));                
            });


            if(__tservice_express && __tservice_express.get('checked')){
                _price += parseInt(__tservice_express.get('data-price'));
                _days = parseInt(__tservice_express.get('data-days'));                     
            }
            
            //_price.format({decimal: ".",group: " ",decimals: 0,prefix: "",suffix: ""});
            __tservice_price.set('html',TServices.number_format(_price, 0, '.', ' '));
            if(__tservice_price2) __tservice_price2.set('html',TServices.number_format(_price, 0, '.', ' ') + ' ' + TServices.plural(_price,'рубль', 'рубля', 'рублей'));
                __tservice_days.set('html',_days + ' ' + TServices.plural(_days,'день','дней','дня'));
                
                
            if (__tservice_price3 && typeof RESERVE_ALL_TAX !== "undefined") {
                var __tservice_reserve_tax = $$('.__tservice_reserve_tax');
                var tax = 0;
                
                for(key in RESERVE_ALL_TAX){
                    var itemTax = RESERVE_ALL_TAX[key];
                    if(_price >= itemTax.min  && _price <= itemTax.max){
                        tax = itemTax.tax;break;
                    }
                }

                var priceWithTax = _price + Math.floor(_price * tax);
                __tservice_price3.set('html',TServices.number_format(priceWithTax, 0, '.', ' ') + ' ' + TServices.plural(priceWithTax,'рубль', 'рубля', 'рублей'));
                if(__tservice_reserve_tax) __tservice_reserve_tax.set('html',Math.round(tax * 1000)/10);
            }
            
        };

        __tservice_extras.addEvent('click',function(){update_info();});
        if(__tservice_express) __tservice_express.addEvent('click',function(){update_info();});  
        
        return true;
    };
    
    
    //-----------------------------------------------------------------------    
    
    
    /**
     * Инициализация скриптов 
     * для карточки услуги
     */
    this.initCard = function()
    {
        //Прилипающий заголовок
        this.init_sticky();
        
        //Пересчет цены/сроков
        this.initPriceCalc();

        //Простенький переключатель слайдов с картинками/видео
        var gallery_wrapper = $$('.b-gallery__wrapper');
        var gallery_cache = [];
        var gallery_links = $$('.b-gallery__link').addEvent('click', function() {
            
            var source = this.get('data-source');
            var element = null;
            var stop_cache = false;
            
            gallery_wrapper.addClass('b-gallery__spinner').set('html','');

            if(typeof(element = gallery_cache[source]) !== 'undefined')
            {
                gallery_wrapper.adopt(element).removeClass('b-gallery__spinner');
                return false;
            }
            
            switch(this.get('data-type'))
            {
                case 'image':
                    element = new Element('img',{
                        'src':source,
                        'class':'b-gallery__pic b-gallery__pic_resize',
                        'style':'display:inline-block;'
                    });
                    break;
                    
                case 'video':
                    if(this.get('data-autoplay') == 1) source += '?autoplay=1';
                    else {
                        this.set('data-autoplay',1);
                        stop_cache = true;
                    }
                    
                    element = new Element('iframe',{
                        'src':source,
                        //'width':889,
                        //'height':500,
                        'frameborder':0,
                        'allowfullscreen':true,
                        'style':'display:inline-block;'
                    });
                    break;
            }
            
            element.hide().addEvent('load',function(){
                this.show();
                gallery_wrapper.removeClass('b-gallery__spinner');
            });
            
            gallery_wrapper.adopt(element);
            if(!stop_cache) gallery_cache[source] = element;
            
            return false;
        });
        
        if(gallery_links.length) gallery_links[0].fireEvent('click');



        $$('.__tservice_emp_only').addEvents({
            mouseover:function(){this.getElement('.b-shadow').removeClass('b-shadow_hide');},
            mouseout:function(){this.getElement('.b-shadow').addClass('b-shadow_hide');}
        });
    };
    
    
    
    //-----------------------------------------------------------------------    
    
    
    /**
     * Загрузчик страниц для отзывов
     * 
     * @param {type} el
     * @returns {undefined}
     */
    /*
    this.onFeedbacksMore = function(el)
    {
        var block=Server.block('tservices/feedbacks-items');
        var page = (el.retrieve('page'))?el.retrieve('page'):2;
        var last=block.getElement('article:last-child');
        var t;
        
        Server.setSpinner(el, {html:'&nbsp;', class:'b-spinner_line', duration:500})
              .updateBottom(block)
              .send({page: page}, null, {
                onReqBlocksSuccess:function(req) {
                    if(t = last.getNext()) 
                    {
                        new Fx.Scroll(window, {duration: 470, offset: {y:-Page.elms.header_bar.getSize().y - 100} }).toElement(t);
                        last = block.getElement('article:last-child');
                        if(!last || last.get('data-is_last')) {el.destroy();}
                    }
                    else
                    {
                        el.destroy();
                    }
                    
                    el.store('page',page + 1);
               } 
            });
    };
    */
    
    
    //-----------------------------------------------------------------------
    
    
    
    /**
     * Универсальный загрузчик страниц
     * 
     * @param {type} el - обьект ссылки
     * @param {type} name - название блока
     * @param {type} offset - смещение для скроллера
     */
    
    /*
    this.onMore = function(el, name, offset)
    {
        var block=Server.block(name);
        var page = (el.retrieve('page'))?el.retrieve('page'):2;
        var last=block.getLast();
        var t;
        
        Server.setSpinner(el, {html:'&nbsp;', class:'b-spinner_line', duration:500})
              .updateBottom(block)
              .send({page: page}, null, {
                onReqBlocksSuccess:function(req) {
                    if(t = last.getNext()) 
                    {
                        new Fx.Scroll(window, {duration: 470, offset: {y:-Page.elms.header_bar.getSize().y - offset} }).toElement(t);
                        last = block.getLast();
                        if(!last || last.get('data-is_last')) {el.destroy();}
                    }
                    else
                    {
                        el.destroy();
                    }
                    
                    el.store('page',page + 1);
               } 
            });
    };
    */
    
    
    
    //-----------------------------------------------------------------------
     
    
    /**
     * Возвращает правильный вариант 
     * окончания существительного для числа
     * 
     * @param {type} a
     * @param {type} str1
     * @param {type} str2
     * @param {type} str3
     * @returns {unresolved}
     */
    this.plural = function(a, str1, str2, str3)
    {
        var number = a;
        var p1 = number%10;
        var p2 = number%100;
        
        if(number == 0) return str2;
        if(p1==1 && !(p2>=11 && p2<=19)) return str1;
        if(p1>=2 && p1<=4 && !(p2>=11 && p2<=19)) return str3;
        
        return str2;
    };
    
    
    
    //-----------------------------------------------------------------------
    
    
    /**
     * Форматирование числа
     * Format a number with grouped thousands
     * 
     * @param {type} number
     * @param {type} decimals
     * @param {type} dec_point
     * @param {type} thousands_sep
     * @returns {Number|String}
     */
    this.number_format = function (number, decimals, dec_point, thousands_sep) 
    {	
        // 
        // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
        // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +	 bugfix by: Michael White (http://crestidg.com)

        var i, j, kw, kd, km;

        // input sanitation & defaults
        if (isNaN(decimals = Math.abs(decimals))) {
            decimals = 2;
        }
        if (dec_point == undefined) {
            dec_point = ",";
        }
        if (thousands_sep == undefined) {
            thousands_sep = ".";
        }

        i = parseInt(number = (+number || 0).toFixed(decimals)) + "";

        if ((j = i.length) > 3) {
            j = j % 3;
        } else {
            j = 0;
        }

        km = (j ? i.substr(0, j) + thousands_sep : "");
        kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);
        //kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).slice(2) : "");
        kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : "");

        return km + kw + kd;
    };
    
    
    //-----------------------------------------------------------------------
    
    
    //Запуск
    this.init();
}

/**
 * Класс обработки аякс пагинации в связке с xAjax
 * 
 * @type Class
 */
var AjaxPaginator = new Class({
    Implements: [Options, Events],
    options:{
        spinner:null,
        spinner_class:'b-spinner_line',
        html:'',
        xajax_fn:null,
        xajax_callback:null,
        id:null,
        total:null,
        next_page:2
    },
    initialize: function(element, options) {
        this.element = $(element);
        
        if(!this.element) return false;
        
        this.options.id = this.element.get('data-id');
        this.options.total = this.element.get('data-total');
        this.setOptions(options);
        
        if(!this.options.xajax_fn || 
           !this.options.id || 
           !this.options.total || 
           typeof xajax === "undefined") return false;

        this.options.xajax_callback = xajax.callback.create(100, 10000);
        this.options.xajax_callback.onComplete = this._onComplete.pass(this.element, this);
        this.options.xajax_callback.onRequest = this._onRequest.pass(this.element, this);
        //this.options.xajax_callback.beforeResponseProcessing = this._beforeResponseProcessing.pass(this.element, this);

        this.element.addEvent('click',this._onMore.pass(this.element, this));
    }/*,
    _beforeResponseProcessing: function(){
        this._onComplete();   
    }*/,        
    _onRequest: function(){
        this.options.html = this.element.get('html');
        this.options.spinner = new Spinner(this.element,{class:this.options.spinner_class});
        this.element.set('html','&nbsp;');
        this.options.spinner.show();
    },        
    _onComplete: function(){
        this.options.spinner.destroy();
        this.element.set('html',this.options.html);
    },        
    _onMore: function(){
        var fn = window[this.options.xajax_fn]; 
        fn(this.options.id,this.options.next_page,this.options.total);
        this.options.next_page++;
    },
    getXajaxCallback: function(){
        return this.options.xajax_callback;
    },
    //метод можно преопределить в наследуемом классе или вынести в options        
    setContent: function(html, is_last){
      //вставляем контент перед кнопкой загрузки
      var els = Elements.from(html);
      els.inject(this.element, 'before');
      
      if(is_last) this.element.destroy();
      
      //скролим на добавленный контент
      var offset = 0;
      var header_bar = $$('.b-bar');
      if(header_bar) offset = header_bar[0].getSize().y;
      new Fx.Scroll(window, {duration: 470, offset: {y:-offset-100}}).toElement(els[0]);
    }        
});


// Здесь можно задать свойства до domready.
//Services.prototype.some_prop = 100;


window.addEvent('domready', function() {
    new TServices();
    
    //Создаем пагинацию для отзывов ТУ
    window.ap_feedbacks = new AjaxPaginator('feedbacks_next_page',{xajax_fn:'xajax_more_feedbacks'});
    window.xajax_callback_feedbacks = ap_feedbacks.getXajaxCallback();
});