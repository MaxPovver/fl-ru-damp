
var ReservesAdmin = new Class({
    
    filter_form: null,
    add_archive_url: '?action=archive',
    
    initialize: function(id)
    {
        var _this = this;
        this.filter_form = $(id);
        
        if (this.filter_form){
            var _filter_form = this.filter_form;
            this.filter_form.getElements('input').addEvent('keydown', function(event){
                if(event.code == 13) {
                    _filter_form.submit();
                }
            });
            this.filter_form.getElements('select').addEvent('change', function(event){
                _filter_form.submit();
            });
        }
        
        
        var create_archive_submit = $('__create_archive');
        if (create_archive_submit) {
            create_archive_submit.addEvent('click', function (event){
                event.stop();
                this.addClass('b-button_disabled')
                    .getParent('form')
                    .set('action', this.get('href'))
                    .submit();
            });
        }
    },     
            
    changeDir: function(dir_col, dir)
    {
        this.filter_form.getElement('#dir_col').set('value', dir_col);
        this.filter_form.getElement('#dir').set('value', dir);
        this.filter_form.submit();
    }
});

window.addEvent('domready', function() {
    window.reserves_admin = new ReservesAdmin('adminFrm');
});