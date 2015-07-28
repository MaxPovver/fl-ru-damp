// Перейти на следующую страницу опроса

var skip_error = 0;
var a_t_id = {};
var a_o_id = {};

function ShowNextPage() {
    var error = 0;
    for (var key in q) {
        var val = q [key];
        if($('question-'+key)) {
            if($('question-'+key).getStyle('display')!='none') {
				if(val == 0) { 
                    error = 1; 
                }
            }
        }
    }
	if(error==1 && skip_error==0) {
        $('opros_btn_next').set('value', 'Пропустить и продолжить');
        $('opros_btn_next').set('class', 'i-btn btn-quiz-mnext');
        $('opros_not_filled_questions').set('html','');
        var q_list_html = '';
        for (var key in q) {
            var val = q [key];
            if($('question-'+key)) {
                if($('question-'+key).getStyle('display')!='none') {
					if(val == 0) {
                        q_list_html = q_list_html + '<li><a href="#question-'+key+'" class="lnk-dot-grey">'+$('question_name_'+key).get('html')+'</a></li> ';
                    }
                }
            }
        }
        $('opros_not_filled_questions').set('html',q_list_html);
        $('opros_not_filled_error').setStyle('display', 'block');
        if(skip_error==0) { skip_error = 1; return; }
    }
    $('opros_action').set('value', 'next');
    $('opros').submit();
}

function ShowNextPageQiwi() {
    var error = 0;
    for (var key in q) {
        var val = q [key];
        if($('question-'+key)) {
            if($('question-'+key).getStyle('display')!='none') {
				if(val == 0) { 
                    error = 1; 
                }
            }
        }
    }
	if(error==1 && skip_error==0) {
        $('opros_not_filled_questions').set('html','');
        var q_list_html = '';
        for (var key in q) {
            var val = q [key];
            if($('question-'+key)) {
                if($('question-'+key).getStyle('display')!='none') {
					if(val == 0) {
                        q_list_html = q_list_html + '<li><a href="#question-'+key+'" class="lnk-dot-grey">'+$('question_name_'+key).get('html')+'</a></li> ';
                    }
                }
            }
        }
        $('opros_not_filled_questions').set('html',q_list_html);
        $('opros_not_filled_error').setStyle('display', 'block');
        if(skip_error==0) { skip_error = 1; return; }
    }
    $('opros_action').set('value', 'next');
    $('opros').submit();
}

// Перейти на предыдущую страницу опроса
function ShowPrevPage() {
    $('opros_action').set('value', 'prev');
    $('opros').submit();
}

// Фиксируем что пользователь ответил на вопрос
function filledField(a_id, q_id, type) {
    $('opros_btn_next').set('value', 'Продолжить »');
    $('opros_btn_next').set('class', 'i-btn btn-quiz-next');
    skip_error = 0;
    switch(type) {
        case 1:
            // CHECKBOX
            if($('a_'+a_id).get('checked')==true) {
                q[q_id]++;
                BlockAnswers(a_id);
                BlockQuestions(a_id);
            } else {
                q[q_id]--;
                if(q[q_id]<0) { q[q_id] = 0; }
                if(b[a_id]) {
                    for (var key in b[a_id]) {
                        var val = b[a_id][key];
                        if(a_t_id[val]!=1) {
                            $('a_'+val).set('disabled',false);
                            if($('t_'+val)) { $('t_'+val).set('disabled',false); }
                        }
                        a_o_id[val] = 0;
                    }
                }
                UnBlockQuestions(a_id);
            }
            break;
        case 2:
            // RADIO
            q[q_id] = 1;
            $$('#question-'+q_id+' > ul > li > span > input').each(function (el) { answ_id = el.get('id'); UnBlockQuestions(answ_id.substr(2,answ_id.length)); });
            BlockQuestions(a_id);
            break;
        case 3:
            // INPUT
            if($('t_'+a_id)) {
                if($('t_'+a_id).value!='') {
                    q[q_id] = 1;
                } else {
                    q[q_id] = 0;
                }
            }
		case 4:
			// SELECT
			if($('a_'+a_id).value!='' && $('a_'+a_id).value!=0) {
				q[q_id] = 1
			} else {
				q[q_id] = 0;
			}
    }
}

// Блокируем ответ в другом вопросе
function BlockAnswerInQuestion(id, a_id, a_q_id) {
    if($('a_'+id).get('checked')==true) {
        if($('a_'+a_id).get('checked')==true) {
                $('a_'+a_id).set('checked', false);
                q[a_q_id]--;
                if(q[a_q_id]<0) { q[a_q_id] = 0; }
        }
        $('a_'+a_id).set('disabled',true);
        a_t_id[a_id] = 1;
    } else {
        if(a_o_id[a_id]!=0) {
            $('a_'+a_id).set('disabled',false);
        }
        a_t_id[a_id] = 0;
    }
}

// Блокируем ответы если выбран определенный ответ
function BlockAnswers(a_id) {
    if(b[a_id]) {
        for (var key in b[a_id]) {
            var val = b[a_id][key];
            a_o_id[val] = 1;
            $('a_'+val).set('disabled',true);
            if($('t_'+val)) { $('t_'+val).set('disabled',true); }
            }
    }
}

// Блокируем вопросы если выбрат определенный ответ
function BlockQuestions(a_id) {
    if(b_q[a_id]) {
        for (var key in b_q[a_id]) {
            var val = b_q[a_id][key];
            $('question-'+val).setStyle('display','none');
            }
    }
}

// Разблокируем вопросы если выбрат определенный ответ
function UnBlockQuestions(a_id) {
    if(b_q[a_id]) {
        for (var key in b_q[a_id]) {
            var val = b_q[a_id][key];
            $('question-'+val).setStyle('display','block');
            }
    }
}

// trim для JS
function strtrim(string)
{
    var str = new String(string);
    return str.replace(/(^\s+)|(\s+$)/g, "");
}

// Проверка на число
function check_number(e) {
	var key = (typeof e.charCode == 'undefined' ? e.keyCode : e.charCode); 
	if (e.ctrlKey || e.altKey || key < 32) return true; 
	key = String.fromCharCode(key); 
	return /[\d]/.test(key);
}

