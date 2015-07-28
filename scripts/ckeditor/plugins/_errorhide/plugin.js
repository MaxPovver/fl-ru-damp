/**
 * 
 * hiding error red frame
 */

if (Browser && Browser.chrome) {
    CKEDITOR.on('instanceReady', function(){
        var i;
        for(i in CKEDITOR.instances) {
            if (!CKEDITOR.instances.hasOwnProperty(i)) continue;
            (function(j){
                CKEDITOR.instances[j].window.on('click', function(){
                    CKEDITOR.instances[j].focus();
                });
            })(i)
        }
    });
}
    
CKEDITOR.plugins.add( '_errorhide', {
    init: function(editor) {

        editor.on('focus', function(){
            this.container.removeClass('b-combo__input_error');
        });

        editor.on('show_error_frame', function(){
            this.container.addClass('b-combo__input_error');
        });
    }
});

