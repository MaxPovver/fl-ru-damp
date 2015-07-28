
var BudgetExt = new Class({
    
    name: null,
    element_budget: null,
    element_agreement: null,
    element_currency: null,
    element_priceby: null,
    error_screen: null,
    
    initialize: function(name, element)
    {
        var _this = this;
        this.name = name;
        this.element_budget = element;
        this.element_agreement = $('el-' + this.name + '_agreement');
        this.element_currency = $('el-' + this.name + '_currency');
        this.element_priceby = $('el-' + this.name + '_priceby');
        this.error_screen = $('el-' + this.name + '-error');
        
        if (this.element_agreement) {
           this.element_agreement.addEvent('click', function(){
                _this.hideError();
                if (this.get('checked')) {
                    _this.disableElement();
                } else {
                    _this.enableElement();
                }
           }); 
        }
    },
     
    disableElement: function() 
    {
        this.element_budget && this.element_budget
                .getParent('.b-combo__input')
                .addClass('b-combo__input_disabled');
        
        this.element_budget && 
        !this.element_budget.get('value') && 
        this.element_budget.set('value',0);
        
        this.element_currency && this.element_currency
                .getParent('.b-combo__input')
                .addClass('b-combo__input_disabled');        
        
        this.element_priceby && this.element_priceby
                .getParent('.b-combo__input')
                .addClass('b-combo__input_disabled');        
    },
     
    enableElement: function() 
    {
        this.element_budget && this.element_budget
                .getParent('.b-combo__input')
                .removeClass('b-combo__input_disabled');
        
        this.element_currency && this.element_currency
                .getParent('.b-combo__input')
                .removeClass('b-combo__input_disabled');        
        
        this.element_priceby && this.element_priceby
                .getParent('.b-combo__input')
                .removeClass('b-combo__input_disabled');          
    },        
            
    hideError: function()
    {
        if (!this.error_screen) {
            return false;
        }
        
        this.element_budget.getParent('.b-combo__input').removeClass('b-combo__input_error');
        this.error_screen.addClass('b-layout_hide');
        return true;
    }
});

var BudgetExtFactory = new Class({
    initialize: function()
    {
        var elements = $$('[data-budget-name]');
        if(!elements) {
            return false;
        }
        window.budgetexts = {};
        elements.each(function(element){
            var name = element.get('data-budget-name');
            if(name) {
                window.budgetexts[name] = new BudgetExt(name, element);
            }
        });
    },
            
    /**
     * Получить обьект по его имени
     */
    getBudgetExt: function(name)
    {
        return (typeof window.budgetexts[name] !== "undefined")?
            window.budgetexts[name]:false;
    }
});

window.addEvent('domready', function() {
    window.budgetext_factory = new BudgetExtFactory();
});