/**
 * Фабрика для создания обьектов элементов форм
 * 
 * Если элемент формы имеет класс то он должен использовать созданную здесь
 * фабрику для своей инициализации см пример класс Spinner
 * 
 * @type Class
 */
var ElementsFactory = new Class({
    
    initialize: function()
    {
        window.form_elements = {}; 
    },
    
    addElements: function(name, class_name)
    {
        var elements = $$('[data-'+name+']');
        
        if (!elements) {
            return false;
        }           
        
        elements.each(function(element){
            var name = element.get('data-' + name);
            if (typeof window[class_name] !== "undefined") {
                window.form_elements[name] = new window[class_name](element);
            }
        });
    },
    
    getElement: function(name)
    {
        return (typeof window.form_elements[name] !== "undefined")?window.form_elements[name]:null;
    }
});   

window.addEvent('domready', function() {
    window.elements_factory = new ElementsFactory();
});