var traversal = typeof document
                           .createElement('div')
                               .childElementCount != 'undefined';

var firstChild = traversal ? function(node) {
    return node.firstElementChild;
} : function(node) {
    node = node.firstChild;
    while(node && node.nodeType != 1) node = node.nextSibling;
    return node;
};

var lastChild = traversal ? function(node) {
    return node.lastElementChild;
} : function(node) {
    node = node.lastChild;
    while(node && node.nodeType != 1) node = node.previousSibling;
    return node;
};

var next = traversal ? function(node) {
    return node.nextElementSibling;
} : function(node) {
    while(node = node.nextSibling) if(node.nodeType == 1) break;
    return node;
};

var previous = traversal ? function(node) {
    return node.previousElementSibling;
} : function(node) {
    while(node = node.previousSibling) if(node.nodeType == 1) break;
    return node;
};

var children = typeof document
                          .createElement('div')
                              .children != 'undefined';

var child = children ? function(node) {
    return node.children;
} : function(node) {
    var list = node.childNodes,
    length = list.length,
    i = -1,
    array = [];
    while(++i < length)
        if(list[i].nodeType == 1)
            array.push(list[i]);
    return array;
};

function MultiInput(container_id,line_id, publisher_is_pro){
        this.max_inputs = 3;
	this.line_id = line_id;
        this.container_id = container_id;
        this.publisher_is_pro = publisher_is_pro;
        this.container = null;
        this.line = null;
	var self = this;

	this.init = function(){
            this.container = document.getElementById(this.container_id);
            this.line = document.getElementById(this.line_id);
            this.resetButtons();
        };

        this.removeButton = function(obj){
            var xx = null;
            if(xx = lastChild(obj)){
                if(xx.tagName.toLowerCase() == 'a') obj.removeChild(xx);
            }
            return obj;
        };

        this.resetButtons = function(){
            var ele = firstChild(this.container);
            var last = lastChild(this.container);
            
                while(ele){
                    if(ele.id != self.line.id) {
                        ele = ele.nextSibling;
                    }else{
                        ele = self.removeButton(ele);
                        ele.appendChild(self.getDeleteButton());
                        ele = ele.nextSibling;
                    }
                }

            last = this.removeButton(last);
            // дополнительные специальности только у про работодателей
            if (this.publisher_is_pro) {
                if(child(this.container).length < self.max_inputs){
                    last.appendChild(self.getAddButton());
                }else{
                    last.appendChild(self.getDeleteButton());
                }
            }
            if(this.container_id=='cat_con' && this.line_id=='cat_line') {
                setMinAvgMaxBudgetPrice();
            }
        };

        this.addLine = function(){
            if(child(this.container).length >= self.max_inputs){
                alert('превышено максимальное допустимое число полей');
                return false;
            }
            var obj = this.line.cloneNode(true);
            obj = this.removeButton(obj);
            
            
            
            if(obj.firstElementChild != undefined) {
                if(obj.firstElementChild.options[0] != undefined) {
                    obj.firstElementChild.options[0].selected = true; // !!! проверить 
                } 
                if(obj.lastElementChild.options[0] != undefined) {
                    obj.lastElementChild.options[0].innerHTML = 'Выберите специализацию';
                    obj.lastElementChild.options[0].selected = true;  
                    obj.lastElementChild.disabled = true;             
                }     
            } else { // Для IE
                var childs = obj.childNodes;
                if(childs[0] != undefined) {
                    childs[0].options[0].selected = true; // !!! проверить 
                } 
                if(childs[2] != undefined) {
                    childs[2].options[0].innerHTML = 'Выберите специализацию';
                    childs[2].options[0].selected = true;  
                    childs[2].disabled = true;             
                }      
            }
            
            this.container.appendChild(obj);
            this.resetButtons();
        };

        this.getAddButton = function(){
            var a = document.createElement('a');
            var img = document.createElement('img');
            a.href = "javascript:void(0)";
            a.title = "Добавить";
            a.onclick = function(){
                self.addLine();
            }
            img.src = "/images/btns/btn-f-add.png";
            a.appendChild(img);
            return a;
        };

        this.getDeleteButton = function(){
            var a = document.createElement('a');
            var img = document.createElement('img');
            a.href = "javascript:void(0)";
            a.title = "Удалить";
            a.onclick = function(){
                self.removeLine(a);
            }
            img.src = "/images/btns/btn-f-remove.png";
            a.appendChild(img);
            return a;
        };

        this.removeLine = function(obj){
            this.container.removeChild(obj.parentNode);
            this.resetButtons();
        };
};

window.addEvent('domready', function() {
    if ($('agreement')) {
        $('agreement').addEvent('click', function(){
            $$('.budget-select').toggleClass('disable');
            $$('.apf-o-budjet input[name=cost], .apf-o-budjet select').set('disabled', (document.getElement('.budget-select').hasClass('disable')));
        });
        
        if ($('agreement').get('checked') == true) {
            $('agreement').fireEvent('click');
        }
    }


    if($('emp_prj_sort_filter')) {
        $$('.b-filter__item .b-filter__link').addEvent('click',function(){
            if((this.getParent('.b-filter__item').getChildren('.b-filter__marker').hasClass('b-filter__marker_hide'))){
                this.getParent('.b-filter__list').getChildren('.b-filter__item').getElement('.b-filter__marker').addClass('b-filter__marker_hide').getPrevious('.b-filter__link').removeClass('b-filter__link_no').addClass('b-filter__link_dot_0f71c8');
                this.getParent('.b-filter__item').getChildren('.b-filter__marker').removeClass('b-filter__marker_hide');
                this.removeClass('b-filter__link_dot_0f71c8').addClass('b-filter__link_no');
                this.getParent('.b-filter__toggle').getPrevious('.b-filter__body').getChildren('.b-filter__link').set('text',this.get('text'));
                $$('.b-filter__toggle').addClass('b-filter__toggle_hide');
                $$('.b-filter__overlay').dispose();
                if (Browser.ie8){
                    $$('.b-filter').setStyle('overflow','visible');
                    if(this.getParent('.b-filter') != undefined) {
                        this.getParent('.b-filter').setStyle('overflow','hidden');
                        this.getParent('.b-filter').setStyle('overflow','visible');
                    }
                }       
                var c_url_s = document.location.href;
                var n_url_s = c_url_s;
                if(c_url_s.search(/sort=[a-z]{0,}/)!=-1) {
                    n_url_s = c_url_s.replace(/sort=[a-z]{1,}/, 'sort='+this.get('cmd'));
                } else {
                    if(document.location.search!='') {
                        n_url_s = c_url_s.replace(/\?/, '?sort='+this.get('cmd')+'&');
                    } else {
                        n_url_s = c_url_s.replace(/\.html/, '.html?sort='+this.get('cmd'));
                    }
                }
                if(n_url_s!=c_url_s) {
                    window.location = n_url_s;
                }
                return false;
            }
        });
    }

    var payBlock = $('pay_services');
    if (payBlock && payBlock.hasClass('autoscroll')) {
        var myFx = new Fx.Scroll(window, {
            duration: 300,
            wait: false,
            offset: {
                x: 0,
                y: -30
            }
        }).toElement('pay_services');
    }
    

});

/** Отправляет форму смены публицации проекта.
 * project_id ИД проекта
 * kind Текущая вкладка типа проектов
 * do_close Закрыть или открыть. Перекидывает на вкладку результата действия
 * 
 */
function closeProject(project_id, kind, do_close) 
{
    var form = new Element('form', {'action':'.','method':'post'});
    var elemAction = new Element('input', {'type':'hidden', 'name':'action', 'value':'prj_close'});
    var elemPid = new Element('input', {'type':'hidden', 'name':'project_id', 'value':project_id});
    var elemKind = new Element('input', {'type':'hidden', 'name':'kind', 'value':kind});
    var elemDo = new Element('input', {'type':'hidden', 'name':'do_close', 'value':do_close});
    var token = new Element('input', {'type':'hidden', 'name':'u_token_key', 'value':_TOKEN_KEY});
        
    form.adopt(elemAction, elemPid, elemKind, elemDo, token);
    form.setStyle('display','none').inject($(document.body), 'bottom');
    form.submit();
}

/** Отправляет форму публикации вакансии минуя форму редактирования
 *  project_id ИД проекта
 */
function publicVacancy(project_id) 
{
    var form = new Element('form', {'action':'.','method':'post'});
    var elemAction = new Element('input', {'type':'hidden', 'name':'action', 'value':'prj_express_public'});
    var elemPid = new Element('input', {'type':'hidden', 'name':'project_id', 'value':project_id});
    var token = new Element('input', {'type':'hidden', 'name':'u_token_key', 'value':_TOKEN_KEY});
    var location = new Element('input', {'type':'hidden', 'name':'location', 'value':window.location.href});
    
    form.adopt(elemAction, elemPid, token, location);
    form.setStyle('display','none').inject($(document.body), 'bottom');
    form.submit();
}

/** Отправляет форму перемещения проекта в/из корзины.
 * project_id ИД проекта
 * do_remove Убрать или восстановить. Перекидывает на вкладку результата действия
 * 
 */
function moveTrashProject(project_id, do_remove) 
{
    var form = new Element('form', {'action':'.','method':'post'});
    var elemAction = new Element('input', {'type':'hidden', 'name':'action', 'value':'prj_trash'});
    var elemPid = new Element('input', {'type':'hidden', 'name':'project_id', 'value':project_id});
    var elemDo = new Element('input', {'type':'hidden', 'name':'do_remove', 'value':do_remove});
    var token = new Element('input', {'type':'hidden', 'name':'u_token_key', 'value':_TOKEN_KEY});
    var location = new Element('input', {'type':'hidden', 'name':'location', 'value':window.location.href});
    
    form.adopt(elemAction, elemPid, elemDo, token, location);
    form.setStyle('display','none').inject($(document.body), 'bottom');
    form.submit();
}

function mass_sendit() {
    if (mass_spam.busy) {
        return false;
    }
    if ($('mass_max_users').get('value') == 0) {
        alert('Нет пользователей для рассылки.');
        return false;
    }
    quickMAS_show();
    //document.getElementById('mass_frm').submit();
}


var mass_spam = {

    values: { },
    calc: { },
    busy: 0,
    count: 0,

    send: function() {
        if (this.busy > 0) {
            return;
        }

        this.busy = 1;


        xajax_mass_Calc(xajax.getFormValues('mass_frm'));
        
        document.body.style.cursor = 'default';
    },

    addcat: function() {
        var s = $('mass_cats').get('value');
        var re = /\s*: \s*/
        var str = s.split(re);

        var cat_name = '';
        var is_m = 0;
        if($('mass_cats_column_id').get('value')=='1') {
            cat_name = str[1];
            is_m = 0;
        } else {
            cat_name = str[0];
            is_m = 1;
        }

        if($('mass_cat_span_'+$('mass_cats_db_id').get('value')+'_'+is_m)) { return; }
        if(is_m==1) {
            var el = new Element('span', { 'id': 'mass_cat_span_'+$('mass_cats_db_id').get('value')+'_'+is_m, 'class':'b-frm-fltr__spec' } );
        } else {
            var el = new Element('span', { 'id': 'mass_cat_span_'+$('mass_cats_db_id').get('value')+'_'+is_m, 'class':'b-frm-fltr__spec b-frm-fltr__spec_dop' } );
        }
        el.injectBefore($('mass_clist'));
        $('mass_cat_span_'+$('mass_cats_db_id').get('value')+'_'+is_m).set('html', cat_name+'<span class="b-frm-fltr__spec-close" onclick="mass_spam.delcat(\''+$('mass_cats_db_id').get('value')+'_'+is_m+'\');"></span>');

        $('mass_f_cats').set('value', '');
        var c_str = '';
        $$('.b-frm-fltr__spec').each(function(e) { c_str = c_str + e.get('id') + ','; });
        $('mass_f_cats').set('value', c_str);

        mass_spam.send();

    },

    delcat: function(id) {
        $('mass_cat_span_'+id).dispose();
        $('mass_f_cats').set('value', '');
        var c_str = '';
        $$('.b-frm-fltr__spec').each(function(e) { c_str = c_str + e.get('id') + ','; });
        $('mass_f_cats').set('value', c_str);
        mass_spam.send();
    }
}


function initNote() 
{
    var 
        noteTextBlock = $('noteTextBlock'),
        noteEditBlock = $('noteEditBlock'),
        noteEditBtn = $('noteEditBtn'),
        noteSaveBtn = $('noteSaveBtn'),
        noteTextarea = $('noteTextarea'),
        noteText = $('noteText');

    function editNote () {
        noteTextBlock.toggleClass('b-fon_hide');
        noteEditBlock.toggleClass('b-fon_hide');
        noteTextarea.fireEvent('checkSizeForced');
    }

    function saveNote () {
        var text = noteTextarea.get('value');
        xajax_saveHeaderNoteFromProject(PROJECTS_NOTE_LOGIN, text, null, true);
    }

    function noteSaved (text) {
        noteTextBlock.toggleClass('b-fon_hide');
        noteEditBlock.toggleClass('b-fon_hide');
        if (text.length < 1) {
            noteEditBtn.set('text', 'Добавить');
            noteText.set('html', '');
        } else {
            noteEditBtn.set('text', 'Редактировать');
            noteText.set('html', text);
        }
    }

    window.addEvent('domready', function(){
        noteEditBtn.addEvent('click', editNote);
        noteSaveBtn.addEvent('click', saveNote);
        noteTextBlock.addEvent('noteSaved', noteSaved);
    });
}


window.addEvent('domready', function() {
    if ($('b_ext_filter')) {    
        $('currency_text').set('value', currencyList[PROJECTS_FILTER_CURRENCY]);
    
        FilterAddBulletNew(0,0,0,0);

        var hash = window.location.hash;
        if (hash === '#prj_filter') {
            var el = $('b_ext_filter');
            if (el) {
                xScroll = window.getScroll().x;
                yScroll = el.getPosition().y - 30;
                yScroll = yScroll < 0 ? 0 : yScroll;
                window.scrollTo(xScroll, yScroll);
            }
        }

        if ($('popup_qedit_prj_attachedfiles')) {
            attachedFiles.init(
                'popup_qedit_prj_attachedfiles', 
                new Array(), 
                PROJECTS_MAX_FILE_COUNT, 
                PROJECTS_MAX_FILE_SIZE, 
                PROJECTS_FILE_DISALLOWED, 
                'project', 
                false
            );
        }
    
    }
    
    $$('.b-promo__slide1').getElement('.b-promo__link').addEvent('click',function(){
        $$('.b-promo__slide1').toggleClass('b-promo__slide_hide')
        this.getParent('.b-promo').getElement('.b-layout').toggleClass('b-layout_hide');
        this.getParent('.b-promo').getElement('.b-promo__h2').toggleClass('b-promo__h2__hide');
        
        // сохраняем статус блока
        var status = $(this).get('id') == "rcmd_frl_show"; //$('recommended_freelancers_rollup').hasClass('b-promo__slide_hide');
                
        new Request({
            url: window.location.href
        }).get("p=setRcmdFrlStatus&status=" + status);
        return false;
    });
    
	$$( ".b-username__star" ).addEvent( "click", function() {
		this.toggleClass('b-username__star_white').toggleClass('b-username__star_yellow');
		this.getParent('.b-username__txt').getElement('.b-username__link_elect').toggleClass('b-username__link_dot_0f71c8').toggleClass('b-username__link_dot_000')
	});
    
	$$( ".b-username__link_elect" ).addEvent( "click", function() {
		this.toggleClass('b-username__link_dot_0f71c8').toggleClass('b-username__link_dot_000');
		this.getParent('.b-username__txt').getElement('.b-username__star').toggleClass('b-username__star_white').toggleClass('b-username__star_yellow')
		return false;
	});
    
    if (typeof PROJECTS_NOTE_LOGIN != "undefined") {
        initNote();
    }
    
    if (typeof offer_works != "undefined") {
        for (var i = 0; i < offer_works.length; i++) {
            clear_work((i + 1), offer_works[i][0]);
            add_work(offer_works[i][0], offer_works[i][1], offer_works[i][2]);
        }
    }
    
});