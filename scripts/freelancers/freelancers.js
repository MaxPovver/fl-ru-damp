
/**
 * Работа с каталогом фрилансеров
 * @todo: использую Mootools
 */

function Freelancers()
{
    Freelancers=this; // ie ругался без этого, пока не понял.

    //--------------------------------------------------------------------------
    
    var SEARCH_ADVANCED_BUTTON  = 'search-advanced-button';
    var SEARCH_ADVANCED_ID      = 'advanced-search';
    var SEARCH_ACTION           = 'search-action';
    var SEARCH_RESET_BTN        = 'search_reset_btn';
    var SEARCH_FORM             = 'main-search-form';
    
    var sSearchForm;
    
    //--------------------------------------------------------------------------
    
    
    //Начальная инициализация
    this.init = function() 
    {
        var sResetbtn = $(SEARCH_RESET_BTN);
        var sAdvbtn = $(SEARCH_ADVANCED_BUTTON);
        var sAdvBlock = $(SEARCH_ADVANCED_ID);
        var sActionInput = $(SEARCH_ACTION);
        Freelancers.sSearchForm = $(SEARCH_FORM);
        
        if (sAdvbtn && sAdvBlock) {
            sAdvbtn.addEvent('click', function() {
                if (sAdvBlock.isDisplayed()) {
                    sAdvBlock.hide();
					sResetbtn.addClass('b-layout_hide');
                    sActionInput.set('value','search');
                } else {
                    sAdvBlock.show();
					sResetbtn.removeClass('b-layout_hide');
                    sActionInput.set('value','search_advanced');
                } 
                
                return false;
            });
        }
        
        if (sResetbtn) {
            sResetbtn.getElement('a').addEvent('click', function(){
                Freelancers.filterCatalogClearForm();
                return false;
            });
        }
        
        //TODO
    };
    

    //--------------------------------------------------------------------------


    this.filterCatalogClearForm = function()
    {
        if(Freelancers.sSearchForm) {
            var form = Freelancers.sSearchForm;
            for(var i = 0; i < form.elements.length; i++) {
                var elm = form.elements[i];
                if(elm.type == 'checkbox') {
                    elm.checked=false;
                    continue;
                }
                
                if(elm.type != "button" && 
                   elm.type != "submit" && 
                   elm.tagName != 'SELECT' && 
                   elm.type != "hidden" && 
                   elm.name != 'search_string') {
                        elm.value = "";
                   }
            }
        }
        
        
        //var profession = ComboboxManager.getInput("profession");
        //profession.clear(0);
        //profession.reload('');
        $('profession_db_id').set('value',0);
        $('profession_column_id').set('value',0);
        $('profession').set('value','');
    
        //var location = ComboboxManager.getInput("location");
        //location.clear(0);
        //location.reload('');
        $$('[name="location_columns[0]"], [name="location_columns[1]"]').set('value',0);
        $('location_column_id').set('value',0);
        $('location_db_id').set('value',0);
        $('profession').set('location','');
        
        var curr_type = ComboboxManager.getInput("curr_type");
        curr_type.reload('Руб');
        
        var cost_type = ComboboxManager.getInput("cost_type");
        cost_type.reload('За месяц');
    };
    


    //--------------------------------------------------------------------------


    //Запуск инициализации
    this.init();    
}

window.addEvent('domready', function() {
    new Freelancers();
});