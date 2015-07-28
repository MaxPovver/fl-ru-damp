(function() {
	var cutCmd = {
		canUndo: false, // The undo snapshot will be handled by 'insertElement'.
		exec: function( editor ) {
			var element = editor.document.getElementsByTag( 'cut' ).getItem(0);

			if(element) {
				element.remove();
			}

			var cut = editor.document.createElement( 'cut' );
			editor.insertElement( cut );
		}
	};

	var pluginName = 'ecut';

	// Register a plugin named "cut".
	CKEDITOR.plugins.add( pluginName, {
		lang: 'en,ru', // %REMOVE_LINE_CORE%
		icons: 'ecut', // %REMOVE_LINE_CORE%
		init: function( editor ) {
			if ( editor.blockless )
				return;
			editor.addCommand( pluginName, cutCmd );
			if ( editor.ui.addButton ) {
				editor.ui.addButton( 'eCut', {
					label: editor.lang.ecut.toolbar,
					command: pluginName,
					toolbar: 'others,10'
				});
			}

			if ( editor.addMenuItems ) {
				editor.addMenuItems({
	                cut: {
	                    label: editor.lang.ecut.toolbar,
	                    command: pluginName,
	                    group: pluginName,
	                    order: 5
	                }
                });
			}
		}
	});
})();
