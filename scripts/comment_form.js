// скрипт для формы добавления комментариев и поста
(function(){
    var max_answers = 10; // максимальное количество ответов
    var question_max_length = 256; // максимальная длина вопроса
    var youtube_default = 'Ссылка на видеоролик Youtube, Rutube или Vimeo'; // дефолтный текст в поле ввода ссылки на видео
    var submit_flag = 1;
    // показать поле для ввода ссылки на видео
    function show_video_input () {
        $('add_yt_box').setStyle('display', 'none');
        $('yt_box').setStyle('display', 'block');
        youtube_set_default();
        return false;
    }
    // скрыть поле для ввода ссылки на видео
    function hide_video_input () {
        $('add_yt_box').setStyle('display', 'block');
        $('yt_box').setStyle('display', 'none');
        $('youtube_link').setProperty('value','');
        check_youtube();
        return false;
    }
    // при фокусе на ютуб
    function check_youtube () {
        var you = $('youtube_link');
        if (you.getProperty('value') == youtube_default) you.setProperty('value', '');
        you.removeClass('b-combo__input-text_color_a7');
    }
    // заполнить дефолтной надписью
    function youtube_set_default () {
        var temp = $('youtube_link');
        if (temp.get('value') === '') {
            temp.set('value', youtube_default);
            temp.addClass('b-combo__input-text_color_a7');
        }
    }

    // показать окно для добавления опроса
    function show_poll () {
        $('pool_box').removeClass('b-form_hide');
        $('add_poll').addClass('b-form_hide');
        return false;
    }
    // скрыть окно добавления опроса
    function hide_poll () {
        $('pool_box').addClass('b-form_hide');
        $('add_poll').removeClass('b-form_hide');
        $('question').value = ''; // стираем вопрос
        // удаляем все ответы кроме первого, его просто очищаем, и вешаем событие input
        $each(
            $$('table[id^=poll-]'),
            function (el, index) {
                if (index === 0) {
                    el.getElement('input[type=text]').value = '';
                    el.getElement('a[id^=del_answer_btn_]').setStyle('display', 'none');
                    el.getElement('input[type=text]').addEvent('input', answer_changed);
                } else {
                    el.dispose();
                }
            }
        )
        return false;
    }
    // только один ответ
    function one_answer () {
        $$( '#poll-radio').setStyle('display','none');
        $$( '#poll-check').setStyle('display','block');
        $$( '#multiple').setProperty('value', '1');
        $each($$('table[id^=poll-] input[type=radio]'), function(el){
            el.setStyle('display', 'none')
        });
        $each($$('table[id^=poll-] input[type=checkbox]'), function(el){
            el.setStyle('display', 'block')
        });
        return false;
    }
    // несколько ответов
    function many_answers () {
        $$( '#poll-check').setStyle('display','none');
        $$( '#poll-radio').setStyle('display','block');
        $$( '#multiple').setProperty('value', '0');
        $each($$('table[id^=poll-] input[type=radio]'), function(el){
            el.setStyle('display', 'block')
        });
        $each($$('table[id^=poll-] input[type=checkbox]'), function(el){
            el.setStyle('display', 'none')
        });
        return false;
    }
    // показать/скрыть счетчик длины вопроса
    function show_question_counter () {
        var counter = $('poll_counter');
        var length = $('question').get('value').length;
        if (length > question_max_length) {
            counter.addClass('tawlr');
            counter.set('html', '<span>Исчерпан лимит символов для поля (' + question_max_length + ' символов)</span>');
        } else {
            if (counter) {
                if(counter.hasClass('tawlr')) {
                    counter.removeClass('tawlr');
                }
                counter.set('html', '<span>Доступно ' + (question_max_length - length) + ' символов</span>');
            }
        };
    }
    function hide_question_counter () {
        if ($('poll_counter')) {
            $('poll_counter').getElement('span') && $('poll_counter').getElement('span').dispose();
        }
    }

    // ответ изменен (добавлен)
    function answer_changed () {
        // показываем кнопку УДАЛИТЬ ОТВЕТ
        this.getParent('table').getElement('a[id^=del_answer_btn_]').setStyle('display', '');
        add_new_answer(this);
    }
    // показать кнопку ДОБАВИТЬ ОТВЕТ
    function add_new_answer () {var answers = $$('table[id^=poll-]'); // список ответов
    var s  = answers.length - 1; // номер последнего ответа
    if (s + 1 >= max_answers) return;
    var input = answers[s].getElement('input[type=text]');
    input.removeEvent('input', answer_changed); // удаляем обработчик нажатия
    input.removeEvent('keypress', answer_changed); // удаляем обработчик нажатия
    var sr = answers[s]; // последний ответ до добавления (потом становится предпоследним)
    
    var clone = sr.cloneNode(true); // дублируем последний ответ
    var id = +clone.id.match(/poll-(\d+)/)[1];
    var new_id = id + 1; // id нового ответа        
    clone.id = 'poll-' + new_id;
    clone.getElement('a[id^=del_answer_btn_]').setStyle('display', 'none'); // скрываем кнопку УДАЛИТЬ ОТВЕТ
    sr.parentNode.appendChild(clone, sr.parentNode); // добавляем поле ля нового ответа        
    var td = clone.getElement('td.b-layout__middle').set("html", '');
    ComboboxManager.append(td, "b-combo__input", 'answer_input_' + new_id);
    var clone_input = ComboboxManager.getInput('answer_input_' + new_id).b_input;
    clone_input.value = '';
    clone_input.name = 'answers[]';
    clone_input.tabIndex = '20' + s + 1;
    $(clone_input).set('maxlength', 100);
    var dr = $('poll-' + new_id); // новый последний ответ
    $(dr).getElement('input[type=text]').addEvent('input', answer_changed);
    $(dr).getElement('input[type=text]').addEvent('keypress', answer_changed);
    $(dr).getElement('a[id^=del_answer_btn_]').addEvent('click', del_answer);
    }
    // удалить ответ
    function del_answer () {
        this.getParent('table').dispose();
        // если до удаления было максимальное кол-во ответов и последнее поле заполнено
        var answers = $$('table[id^=poll-] input[type=text]');
        var length = answers.length;
        if (length === (max_answers - 1) && answers[length-1].get('value') !== '') {
            add_new_answer();
        }        
    }
    
    // сохранить пост
    function save_post_button () {
        if (submit_flag && this.get('disabled')!=true) {
            if (!check_post()) return;
            submit_flag = 0;
            this.getParent('form').submit();
        }
        return false;
    }
    
    // отправка формы
    function form_submit (event) {
        if((event.control) && ((event.code==10)||(event.code==13))) {
            if (!check_post()) return;
            this.submit();
        }
    }
    function form_keydown (event) {
        if(event.control && event.code==13 && submit_flag==1){
            if (!check_post()) return;
            submit_flag=0;
            this.submit();
        }
    }
    
    /**
     * проверка всего поста
     * если вернет false, то пост проверку не прошел
     */    
    function check_post () {
        check_youtube();
        // проверяем длину вопроса
        var length = $('question').get('value').length;
        if (length > question_max_length) {
            alert ('Длина вопроса превышает ' + question_max_length + ' символов');
            return false;
        }
        // проверка пройдена
        return true;
    }
    
    // сохранить черновик
    function save_as_draft () {
        //href="javascript:DraftSave();" onclick="this.blur();"
        this.blur();
        DraftSave();
        return false;
    }
    
    // скрыть error
    function hide_error (context) {
        var temp;
        if (context === 'youtube') {
            if (temp = $('youtube_link').getParent('div.b-combo__input_error')) temp.removeClass('b-combo__input_error');
            if (temp = $('msgtext_error_youtube')) temp.setStyle('display', 'none');
        } else if (context === 'question') {
            if (temp = $('question').getParent('div.b-textarea_error')) temp.removeClass('b-textarea_error');
            if (temp = $('msgtext_error_polls')) temp.setStyle('display', 'none');
        } else if (context === 'answer') {
            $$('table[id^=poll-] div.b-combo__input_error').removeClass('b-combo__input_error');
            if (temp = $('msgtext_error_polls_question')) temp.setStyle('display', 'none');
        }
    }
    
    // назначение событий
    window.addEvent('domready', function() {
        var temp;
        // видео
        $('add_yt_box1').addEvent('click', show_video_input);
        $('add_yt_box2').addEvent('click', show_video_input);
        $('hide_yt_box').addEvent('click', hide_video_input);
        temp = $('youtube_link');
        temp.addEvent('focus', check_youtube);
        temp.addEvent('blur', youtube_set_default);
        
        // опрос
        $('add_poll1').addEvent('click', show_poll);
        $('add_poll2').addEvent('click', show_poll);
        $('hide_poll').addEvent('click', hide_poll);
        $$('#poll-radio').getElement('.b-menu__link').addEvent('click', one_answer);
        $$('#poll-check').getElement('.b-menu__link').addEvent('click', many_answers);
        // счетчик символов в вопросе
        $('question').addEvent('focus', show_question_counter);
        $('question').addEvent('blur', hide_question_counter);
        $('question').addEvent('input', show_question_counter);
        $('question').addEvent('keyup', show_question_counter);
        // ответы опроса
        var answers = $$('input[id^=answer_input]');
        var answers_count = answers.length
        // поле для последнего ответа должно реагировать на нажатия клавиш
        answers[answers_count - 1].addEvent('input', answer_changed);
        answers[answers_count - 1].addEvent('keypress', answer_changed);
        // показать кнопки удаления ответа
        $each( $$('table[id^=poll-]'), function (el) {
            if (el.getElement('input[id^=answer_input_]').value !== '') {
                el.getElement('a[id^=del_answer_btn_]').setStyle('display', '');
            }
            el.getElement('a[id^=del_answer_btn_]').addEvent('click', del_answer);
        })
        // если последнее поле уже содержит ответ, то добавляем новое пустое
        if  (answers[answers_count - 1].value !== '') {
            add_new_answer(answers[answers_count - 1]);
            //$('add_poll').setStyle('display', 'none');
        }
        
        // кнопка сохранить топик
        $('topic_form_submit').addEvent('click', save_post_button);
        // форма
        var form = $('idAlertedCommentForm') || $('msg_form');
        form.addEvent('submit', form_submit);
        form.addEvent('keydown', form_keydown);
        
        // черновик
        DraftInit(4);
        $('save_as_draft').addEvent('click', save_as_draft);
        $('save_as_draft').addEvent('keypress', save_as_draft);
        
        // чтобы убиралась красная рамка error
        if (temp = $('youtube_link')) temp.addEvent('focus', function(){hide_error('youtube')});
        if (temp = $('question')) temp.addEvent('focus', function(){hide_error('question')});
        if (temp = $$('input[id^=answer_input_]')) temp.addEvent('focus', function(){hide_error('answer')});
    });
    
})();
    
