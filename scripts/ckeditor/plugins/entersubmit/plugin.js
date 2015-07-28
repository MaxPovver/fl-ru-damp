(function()
{
    var entersubmit = {
        exec : function( editor ) {
            var form = document.getElementById( editor.name );
            if($ != undefined) { // used MooTools
                var parent = $(form).getParent('form');
                parent.submit();
            }
            return;
        }
    };
    var pluginName = 'entersubmit';
    CKEDITOR.plugins.add( pluginName,
        {
            init : function( editor ) {
                editor.addCommand( pluginName, entersubmit );
            }
        });

    CKEDITOR.config.keystrokes = [
        [ CKEDITOR.CTRL + 13, 'entersubmit']
    ];
})();