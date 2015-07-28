var stop_words = {
    regexTest: function() {
        if ( !this.regexEmpty() ) {
            return false;
        }
        
        if ( $('test').get('value') == '' ) {
            alert('Поле Тестовый текст не должно быть пустым');
            return false;
        }
        
        $('action').set('value', 'test');
        
        $('form_stop_words').submit();
        return true;
    },
    
    regexSubmit: function() {
        if ( !this.regexEmpty() ) {
            return false;
        }
        
        $('action').set('value', 'update');
        
        $('form_stop_words').submit();
        return true;
    },
    
    regexEmpty: function() {
        if ( $('regex').get('value') == '' ) {
            if ( !confirm('Удаление Запрещенныех выражений приведет к прекращению скрывания их от пользователей и исчезновению их подсветки при модерировании.\nВы действительно хотите удалить все Запрещенные выражения?') ) {
                return false;
            }
        }
        
        return true;
    },

    wordsSubmit: function() {
        if ( $('words').get('value') == '' ) {
            if ( !confirm('Удаление Подозрительных слов приведет к исчезновению их подсветки при модерировании.\nВы действительно хотите удалить все Подозрительные слова?') ) {
                return false;
            }
        }
        
        $('form_stop_words').submit();
        return true;
    },

    cancel: function(site) {
        window.location = '/siteadmin/stop_words/?site='+site;
    }
};