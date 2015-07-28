/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.plugins.add( 'ehighlight', {
	requires: 'richcombo',
	lang: 'en,ru', // %REMOVE_LINE_CORE%
	init: function( editor ) {
		if ( editor.blockless )
			return;

		var config = editor.config,
			lang = editor.lang.ehighlight;

		// Gets the list of tags from the settings.
		var tags = config.format_code.split( ';' );
		// Create style objects for all defined styles.
		var styles = {};
		var pre    = new CKEDITOR.style( { element: 'pre'});
		for ( var i = 0; i < tags.length; i++ ) {
			var tag = tags[ i ];
			styles[ tag ] = new CKEDITOR.style( { element: 'p', attributes: {'class' : 'code ' + tag.toLowerCase()} });
		}

		editor.ui.addRichCombo( 'Highlight', {
			label: lang.label,
			title: lang.panelTitle,
			toolbar: 'others,20',

			panel: {
				css: [ CKEDITOR.skin.getPath( 'editor' ) ].concat( config.contentsCss ),
				multiSelect: false,
				attributes: { 'aria-label': lang.panelTitle }
			},

			init: function() {
				this.startGroup( lang.panelTitle );
				for ( var tag in styles ) {
					var label = lang[ 'code_' + tag.toLowerCase() ];

					// Add the tag entry to the panel list.
					this.add( tag, label, label );
				}
			},

			onClick: function( value ) {
				//editor.focus();
				editor.fire( 'saveSnapshot' );

				var style = styles[ value ],
					elementPath = editor.elementPath();
				
			    editor.insertText('{code_' + value.toLowerCase() + '}' + editor.getSelection().getSelectedText() + '\r\n{/code_'+ value.toLowerCase() +'}');
				//editor[ style.checkActive( elementPath ) ? 'removeStyle' : 'applyStyle' ]( style );
				//editor[ style.checkActive( elementPath ) ? 'removeStyle' : 'applyStyle' ]( pre );
				
				// Save the undo snapshot after all changes are affected. (#4899)
				setTimeout( function() {
					editor.fire( 'saveSnapshot' );
				}, 0 );
			},

			onRender: function() {
				editor.on( 'selectionChange', function( ev ) {

					var currentTag = this.getValue(),
						elementPath = ev.data.path,
						isEnabled = !editor.readOnly && elementPath.isContextFor( 'p' );

					// Disable the command when selection path is "blockless".
					this[ isEnabled ? 'enable' : 'disable' ]();

					if ( isEnabled ) {

						for ( var tag in styles ) {
							if ( styles[ tag ].checkActive( elementPath ) ) {
								if ( tag != currentTag )
									this.setValue( tag, editor.lang.ehighlight[ 'code_' + tag.toLowerCase() ] );
								return;
							}
						}

						// If no styles match, just empty it.
						this.setValue( '' );
					}
				}, this );
			}
		});
	}
});

/**
 * A list of semi colon separated style names (by default tags) representing
 * the style definition for each entry to be displayed in the Format combo in
 * the toolbar. Each entry must have its relative definition configuration in a
 * setting named `'format_(tagName)'`. For example, the `'p'` entry has its
 * definition taken from `config.format_p`.
 *
 *		config.format_tags = 'p;h2;h3;pre';
 *
 * @cfg {String} [format_tags='p;h1;h2;h3;h4;h5;h6;pre;address;div']
 * @member CKEDITOR.config
 */
CKEDITOR.config.format_code = 'Bash;XML;HTML;Cpp;SQL;CSS;PHP;Python;Perl;Ruby;Cs;Java:JavaScript';