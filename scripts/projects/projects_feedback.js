function ProjectsFeedback()
{
    ProjectsFeedback = this; // ie ругался без этого, пока не понял.
    
    //--------------------------------------------------------------------------
    
    var is_debug = true;

    var FEEDBACK_ID           = 'project_feedback_popup';
    var FEEDBACK_SUBMIT_LABEL = '#project_feedback_submit_label';
    var FEEDBACK_LABEL        = '#project_feedback_label';
    
    var block = $(FEEDBACK_ID);
    var form = block.getElement('form');
    var submit_label = block.getElement(FEEDBACK_SUBMIT_LABEL);
    var feedback = form.getElement('textarea');
    var formValidator;
    var only_close = true;
    var is_set_rating = false;
    
    var BTN_TXT_ONLY_CLOSE    = 'Закрыть проект';
    var BTN_TXT_WITH_FEEDBACK = 'Оставить отзыв и закрыть проект';
    var BTN_TXT_ONLY_FEEDBACK = 'Оставить отзыв';

    var BTN_CLICK_ONLY_CLOSE = "yaCounter6051055.reachGoal('proj_close'); ProjectsFeedback.submit();";
    var BTN_CLICK_WITH_FEEDBACK = "ProjectsFeedback.submit();";
    var BTN_CLICK_ONLY_FEEDBACK = "ProjectsFeedback.submit();";
    
    var feedback_events = {};
    
    
    //--------------------------------------------------------------------------
    
    
    //Начальная инициализация
    this.init = function() 
    {
        if(!block || !form) return this.log('Not found some elements.','error');

        feedback_events = {keyup:this.feedbackTouch,mouseup:this.feedbackTouch};
        feedback.addEvents(feedback_events);

        Locale.use("ru-RU");
        Locale.define(Locale.getCurrent().name, 'FormValidator', {});

        formValidator = new Form.Validator(form, {
            //useTitles: true,
            serial:false,
            onElementPass: function(el) {},
            onElementFail: function(el, validator) {},
            onElementValidate: function(passed, element, validator, is_warn){}
        });
        
        formValidator.add('rating',{
            errorMsg:Form.Validator.getMsg.pass('required'),
            test: function(element, props){
                var is_feedback = (feedback.get('value').length > 0);
                is_set_rating = is_set_rating || element.get('checked');
                if(is_feedback) return is_set_rating;
                return true;
            }        
        });
    };
   
    
    //--------------------------------------------------------------------------
    
    
    
    this.feedbackTouch = function()
    {
        if((this.get('value').length > 0 && !only_close) || 
           (this.get('value').length == 0 && only_close)) return true;
        
        only_close = !only_close;
        submit_label.set('html',(only_close)?BTN_TXT_ONLY_CLOSE:BTN_TXT_WITH_FEEDBACK);
        submit_label.getParent().set('onclick',(only_close)?BTN_CLICK_ONLY_CLOSE:BTN_CLICK_WITH_FEEDBACK);

        return true;
    };
   
   
    //--------------------------------------------------------------------------
    
    
    
    this.submit = function()
    {
        var is_validate = formValidator.validate();

        if(is_validate) 
        {
            var param = xajax.getFormValues(formValidator.element);
            param.status = 'close';
            xajax_changeProjectStatus(param);
            this.close();
        }
        
        return is_validate;
    };
    
   
   
    //--------------------------------------------------------------------------
    
    

    this.close = function()
    {
        block.addClass('b-shadow_hide');
        return false;
    };

    
    //--------------------------------------------------------------------------



    this.open = function(project_id, hash, is_close, rating)
    {
        if(is_close)
        {
            feedback.set('data-validators', feedback.get('data-validators') + ' minLength:4 required');
            feedback.removeEvents(feedback_events);
            submit_label.set('html', BTN_TXT_ONLY_FEEDBACK);
            submit_label.getParent().set('onclick', BTN_CLICK_ONLY_FEEDBACK);
            $$(FEEDBACK_LABEL).hide();
        }
        else
        {
            feedback.set('data-validators', 'maxLength:500');
            feedback.addEvents(feedback_events);
            submit_label.set('html', BTN_TXT_ONLY_CLOSE);
            submit_label.getParent().set('onclick', BTN_CLICK_ONLY_CLOSE);
            $$(FEEDBACK_LABEL).show();
        }
        
        form.getElement('[name="project_id"]').set('value',project_id);
        form.getElement('[name="hash"]').set('value',hash);
        feedback.set('value','');
        only_close = true;

        if(rating > 0) form.getElement('#plus').set('checked',true);
        else if(rating < 0) form.getElement('#minus').set('checked',true);
        else {
            form.getElement('[type="radio"]').set('checked',false);
            if(is_close) form.getElement('#plus').set('checked',true);
        }

        block.removeClass('b-shadow_hide');
    };

   
   
    //--------------------------------------------------------------------------
    
    
    
    this.log = function(message, level) 
    {
        "use strict";
        
        if(!is_debug) return false;
        
        if (window.console) {
            if (!level || level === 'info') {
                window.console.log(message);
            }
            else
            {
                if (window.console[level]) {
                    window.console[level](message);
                }
                else {
                    window.console.log('<' + level + '> ' + message);
                }
            }
        }
        
        return false;
    };
    
    
    //--------------------------------------------------------------------------
    
    
    //Запуск инициализации
    this.init();    
}

window.addEvent('domready', function() {
    new ProjectsFeedback();
});