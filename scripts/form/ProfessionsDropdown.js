/**
 * Класс элемента Zend_Form_Element_ProfessionsDropdown
 * @type Class
 */
var ElementProfessionsDropdown = new Class({
    
    element: null,
    name: null,
    element_input: null,
    element_spec: null,
        
    initialize: function(element)
    {
        var _this = this;
        
        this.element = element;
        this.name = element.get('data-element-professions-name');
        this.element_input = element.getElement('input[type="text"]');
        this.element_spec = $$('[data-element-professions-spec-name="' + this.name + '-spec"]');

        if (this.element_input && this.element_spec) {
            var element_input_id = this.element_input.get('id');
            this.element_input.addEvent('change', function(){
                _this.element_spec.removeClass('b-combo_hide');
                var spec = window.ComboboxManager.getInput(element_input_id + '-spec');
                if (spec) {
                    var dbid = $(element_input_id + '_db_id').get("value");
                    if (Number(dbid)) {
                        spec.breadCrumbs[-1] = dbid;  
                        spec.reload('');    
                        //spec.show(true);                    
                    }
                }
            });
        }
    }   
});

/**
 * Спомощью фабрики создаем обьекты описанного выше класса 
 * существующие в данный момнт на странице
 */
window.addEvent('domready', function() {
    if (typeof window.elements_factory !== "undefined") {
        window.elements_factory.addElements('element-professions-name','ElementProfessionsDropdown');
    }
});