(function(){
    /**
     * сбрасывает все чекбоксы фильтра
     */
    function clearFiltre () {
        $$('#search_prof, #search_opinions, #search_portfolio, #search_visits, #search_projects').set('checked', false);
    }
    /**
     * проверка отмеченных параметров фильтра
     */
    function checkParams () {
        var params = $$('#search_prof, #search_opinions, #search_portfolio, #search_visits, #search_projects');
        if (params[0].get('checked') && params[1].get('checked') && params[2].get('checked') && params[3].get('checked') && params[4].get('checked')) {
            $('export2xls').set('disabled', false);
        } else {
            $('export2xls').set('disabled', true);
        }
    }
    
    window.addEvent('domready', function() {
        $('clear_filtre').addEvent('click', clearFiltre);
        $$('#search_prof, #search_opinions, #search_portfolio, #search_visits, #search_projects').addEvent('click', checkParams);
        checkParams();
    });
})()