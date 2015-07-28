/**
 * @type Class Guest
 *  ласс скриптов дл€ модул€ /guest/
 */
var Guest = new Class({
    debug: false,
    form: null,
    link: null,
    id_link: 'guest-save-form-data',
    social_input: null,
    social_links: null,
    initialize: function()
    {
        var _this = this;
        
        this.gotoError();


        this.link =  $$('[' + this.id_link + ']').getLast();

        if (this.link) {
            this.form = this.link.getParent('form');
        }

        if (this.form) {
            var _form = this.form;

            this.link.addEvent('click', function(event) {
                var auth_input = _form.getElement('input[name=auth]');

                if (auth_input) {
                    auth_input.set('value', 1);
                    _form.submit();
                    return false;
                }
            });
        }
        
        this.social_input = $$('input[name=social]').getLast();
        this.social_links = $$('form.form_guest a.b-auth_btn');
        
        if (this.social_input && this.social_links.length) {
            this.social_links.addEvent('click', function() {
                _this.social_input.set('value', this.get('href'));
                _form.submit();
                return false;
            });
        }
        
        this.preferSbrWarning();
    },
    gotoError: function()
    {
        var first_field = $$('.validation-failed, .b-combo__input_error, .b-textarea_error')[0];

        if (first_field) {
            new Fx.Scroll(window, {
                duration: 300,
                wait: false,
                offset: {x: 0, y: -80}
            }).toElement(first_field);
        }
    },

    preferSbrWarning: function()
    {
        var prefer_def = $('el-prefer_sbr-1');
        if (prefer_def) {
            prefer_def.addEvent('change', function(e){
                alert("ќбращаем ваше внимание: при сотрудничестве напр€мую (вне сервиса \"Ѕезопасна€ сделка\") стороны несут все риски самосто€тельно.");
            });
       }               
    }
});

window.addEvent('domready', function() {
    window.guest = new Guest();
});