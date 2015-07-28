// Показать иконки редактирования и удаления категории
function help_cat_show_cmd(cat) {
    id = this.get('id');
    try {
        if($('del_'+id).getStyle('display')=='none' && $('edit_'+id).getStyle('display')=='none') {
            $('cmd_'+id).setStyle('display','block');
        }
    } catch(err) { }
}

// Скрыть иконки редактирования и удаления категории
function help_cat_hide_cmd(cat) {
    id = this.get('id');
    $('cmd_'+id).setStyle('display','none');
}

// Показать кнопки удаления категории
function help_cat_show_del(cat) {
    $('cmd_'+cat).setStyle('display','none');
    $('del_'+cat).setStyle('display','block');
}

// Скрыть кнопки удаления категории
function help_cat_hide_del(cat) {
    try {
        $('del_'+cat).setStyle('display','none');
        $('cmd_'+cat).setStyle('display','block');
    } catch(err) { }
    help_is_click = false;
}

// Удаление категории
function help_cat_del(cat) {
    id = cat.replace(/cat_/g,'');
    count_items = $(''+id+'').getParent().getParent().getChildren().length;
    if(cat_q[id]==0) {
        if($(id).getParent().getElement('ul')) {
            alert('Вы не можете удалить категорию, т.к. в категории есть подкатегории');
        } else {
            if(count_items==1) {
                if($(id).getParent().getParent().get('id')!='tree') {
                    ul = $(id).getParent().getParent();
                    $(id).getParent().destroy();
                    ul.destroy();
                }
            } else {
                $(id).getParent().destroy();
            }
            cat_l[''+id+''] = '';
            cat_q[''+id+''] = '';
            xajax_DeleteCategory(id);
        }
    } else {
        alert('Вы не можете удалить категорию, т.к. в категории есть вопросы');
    }

    help_cat_hide_del(cat);
    help_is_click = false;
}

// Показать кнопки редактирования категории
function help_cat_show_edit(cat) {
    $('cmd_'+cat).setStyle('display','none');
    $('edit_'+cat).setStyle('display','block');
    var s = $('edit_t_'+cat).get('html');
    s = s.replace(/&amp;/,'&');
    $('edit_f_'+cat).set('value',s);
    $('edit_f_'+cat).setStyle('display','inline');
    $('edit_t_'+cat).setStyle('display','none');
}

// Скрыть кнопки редактирования категории
function help_cat_hide_edit(cat) {
    $('edit_'+cat).setStyle('display','none');
    $('edit_f_'+cat).setStyle('display','none');
    $('edit_t_'+cat).setStyle('display','inline');
    $('cmd_'+cat).setStyle('display','block');
    help_is_click = false;
}

// Сохранение категории
function help_cat_save(cat) {
    name = $('edit_f_'+cat).get('value');
    xajax_SaveCategoryName(cat,name);
    help_cat_hide_edit(cat);
    help_is_click = false;
}

// Добавление категории
function help_cat_add() {
    name = $('new_cat_name').get('value');
    xajax_AddCategory(name);
    $('new_cat_name').set('value','');
}

//  Изменение порядка следования вопросов
function change_order_question(c) {
    xajax_ChangeQuestionOrder(s_questions.serialize());
}

// Проверка полей в форме добавления/редактирования вопроса
function check_question_form() {
    cat = document.getElementById('dir');
    cat_id = cat.options[cat.selectedIndex].value;
    subcat = document.getElementById('subdir-'+cat_id);
    subcat_id = subcat.options[subcat.selectedIndex].value;
    if(subcat_id==0) {
        alert('Вопрос может быть только в подкатегории');
        return false;
    }
    document.getElementById('category_id').value = subcat_id;
    if(document.getElementById('q_name').value=='') {alert('Вы не ввели название вопроса'); /*return false;*/}
    
    CKEDITOR.instances.answer.updateElement();
    
    vl = CKEDITOR.instances.answer.getData().trim();
    $$("form[name='question_form'] textarea[name='answer']").set('value', vl);
    
    if(vl=='' || vl=='<br />') {alert('Вы не ввели ответ на вопрос');return false;}
    return true;
}

// Изменение select подкатегорий в форме добавления/редактирования фопроса
function change_subdir() {
    cat = document.getElementById('dir');
    cat_id = cat.options[cat.selectedIndex].value;
    $$('.subdir').setStyle('display','none');
    $('subdir-'+cat_id).setStyle('display','inline');
}

// Проверка полей в форме редактирования категории
function check_category_form() {
    if(document.getElementById('c_name').value=='') {alert('Вы не ввели название категории');return false;}
    return true;
}

// Превью вопроса
function preview() {
	$$('.cke_button .cke_button_preview')[0].onclick();
}

function setSizeToCookie(size){
    var expiry = new Date();
    expiry.setTime(expiry.getTime() + 24*60*60*1000);
    document.cookie='help_fs='+size+'; path=/; expires=' + expiry.toGMTString();
}


window.addEvent('domready',
    function() {
        if ( typeof(font_size_area) != 'undefined' && $(font_size_area) ) {
            var fsi = $(font_size_area).getStyle('font-size').toInt();
            var bpy = Math.ceil(fsi/2)+(Math.ceil(fsi/8));
            $$('ul.'+font_size_area+' li').setStyle('background', 'url(../images/sprite-help.png) no-repeat -134px '+bpy+'px');
            
            if($(document.body).getElement('.post-fs-minus') && $(document.body).getElement('.post-fs-plus')) {
                $(document.body).getElement('.post-fs-minus').addEvent('click', function(){
                    var fs = $(font_size_area).getStyle('font-size').toInt();
                    if(fs==6) {
                        return false;
                    } else {
                        var fsa = fs-2;
                        if(fsa==6) {
                            $(this).addClass('post-fs-d');
                        }
                        if(fsa==22) {
                            $(document.body).getElement('.post-fs-plus').removeClass('post-fs-d');
                        }
                        $(font_size_area).setStyle('font-size', fsa+'px');
                        var bpy = Math.ceil(fsa/2)+(Math.ceil(fsa/8));
                        $$('ul.'+font_size_area+' li').setStyle('background', 'url(../images/sprite-help.png) no-repeat -134px '+bpy+'px');
                        setSizeToCookie(fsa);
                    }
                });
                $(document.body).getElement('.post-fs-plus').addEvent('click', function(){
                    var fs = $(font_size_area).getStyle('font-size').toInt();
                    if(fs==24) {
                        return false;
                    } else {
                        var fsa = fs+2;
                        if(fsa==24) {
                            $(this).addClass('post-fs-d');
                        }
                        if(fsa==8) {
                            $(document.body).getElement('.post-fs-minus').removeClass('post-fs-d');
                        }
                        $(font_size_area).setStyle('font-size', fsa+'px');
                        var bpy = Math.ceil(fsa/2)+(Math.ceil(fsa/8));
                        $$('ul.'+font_size_area+' li').setStyle('background', 'url(../images/sprite-help.png) no-repeat -134px '+bpy+'px');
                        setSizeToCookie(fsa);
                    }
                });
            }
        }
    }
);

