var RegistrationComplete = new Class({
    
    form: null,
    resendCounter: null,
    resendLink: null,
    
    counter: 20,
    
    initialize: function()
    {
        this.form = $('form_mail_send');
        this.resendLink = $('resend_activate_link');
        this.resendCounter = $('resend_activate_counter');
        
        if (this.form) {
            this.initCounter();
        
            var _this = this;
            this.resendLink.addEvent('click', function(){
                if (!this.hasClass('disabled')) {
                    _this.form.submit();
                }
                return false;
            });
        }        
    },
    
    initCounter: function()
    {
        _this = this;
        setTimeout(function(){
            _this.nextStep();
        }, 1000);
    },
    
    nextStep: function()
    {
        this.counter -= 1;
        
        this.showSeconds();
        if (this.counter > 0) {
            setTimeout(function(){
                _this.nextStep();
            }, 1000);
        } else {
            this.resendLink.removeClass('b-layout__link_color_a7a7a6 b-layout__link_no-decorat disabled');
        }
    },
    
    showSeconds: function()
    {
        var text = this.counter > 0 ? ' (' + this.counter + ')' : '';
        this.resendCounter.set('text', text);
    }
            
});

window.addEvent('domready', function() {
    new RegistrationComplete();
});
