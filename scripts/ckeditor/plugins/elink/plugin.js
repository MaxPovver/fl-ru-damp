CKEDITOR.plugins.add( 'elink', {
    requires: 'dialog,fakeobjects',
    lang: 'en,ru', // %REMOVE_LINE_CORE%
    icons: 'link,unlink', // %REMOVE_LINE_CORE%
    onLoad: function() {
		
    },

    init: function( editor ) {
        // Add the link and unlink buttons.
        editor.addCommand( 'link', new CKEDITOR.dialogCommand( 'elink' ) );
        editor.addCommand( 'unlink', new CKEDITOR.unlinkCommand() );

        editor.setKeystroke( CKEDITOR.CTRL + 76 /*L*/, 'link' );

        if ( editor.ui.addButton ) {
            editor.ui.addButton( 'Link', {
                label: editor.lang.elink.toolbar,
                command: 'link',
                toolbar: 'links,10'
            });
            editor.ui.addButton( 'Unlink', {
                label: editor.lang.elink.unlink,
                command: 'unlink',
                toolbar: 'links,20'
            });
        }

        CKEDITOR.dialog.add( 'elink', this.path + 'dialogs/link.js' );

        // If the "menu" plugin is loaded, register the menu items.
        if ( editor.addMenuItems ) {
            editor.addMenuItems({
                link: {
                    label: editor.lang.elink.menu,
                    command: 'link',
                    group: 'link',
                    order: 1
                },

                unlink: {
                    label: editor.lang.elink.unlink,
                    command: 'unlink',
                    group: 'link',
                    order: 5
                }
            });
        }
    }
});


/**
 * Set of link plugin's helpers.
 *
 * @class
 * @singleton
 */
CKEDITOR.plugins.elink = {
    /**
     * Get the surrounding link element of current selection.
     *
     *      CKEDITOR.plugins.link.getSelectedLink( editor );
     *
     *      // The following selection will all return the link element.
     *
     *      <a href="#">li^nk</a>
     *      <a href="#">[link]</a>
     *      text[<a href="#">link]</a>
     *      <a href="#">li[nk</a>]
     *      [<b><a href="#">li]nk</a></b>]
     *      [<a href="#"><b>li]nk</b></a>
     *
     * @since 3.2.1
     * @param {CKEDITOR.editor} editor
     */
    getSelectedLink: function( editor ) {
        var selection = editor.getSelection();
        var selectedElement = selection.getSelectedElement();
        if ( selectedElement && selectedElement.is( 'a' ) )
            return selectedElement;

        var range = selection.getRanges( true )[ 0 ];

        if ( range ) {
            range.shrink( CKEDITOR.SHRINK_TEXT );
            return editor.elementPath( range.getCommonAncestor() ).contains( 'a', 1 );
        }
        return null;
    },

    /**
     * Opera and WebKit don't make it possible to select empty anchors. Fake
     * elements must be used for them.
     *
     * @readonly
     * @property {Boolean}
     */
    fakeAnchor: CKEDITOR.env.opera || CKEDITOR.env.webkit,

    /**
     * For browsers that don't support CSS3 `a[name]:empty()`, note IE9 is included because of #7783.
     *
     * @readonly
     * @property {Boolean}
     */
    synAnchorSelector: CKEDITOR.env.ie,

    /**
     * For browsers that have editing issue with empty anchor.
     *
     * @readonly
     * @property {Boolean}
     */
    emptyAnchorFix: CKEDITOR.env.ie && CKEDITOR.env.version < 8,

    /**
     * @param {CKEDITOR.editor} editor
     * @param {CKEDITOR.dom.element} element
     * @todo
     */
    tryRestoreFakeAnchor: function( editor, element ) {
        if ( element && element.data( 'cke-real-element-type' ) && element.data( 'cke-real-element-type' ) == 'anchor' ) {
            var link = editor.restoreRealElement( element );
            if ( link.data( 'cke-saved-name' ) )
                return link;
        }
    }
};

// TODO Much probably there's no need to expose these as public objects.
CKEDITOR.unlinkCommand = function() {};
CKEDITOR.unlinkCommand.prototype = {
    exec: function( editor ) {
        var style = new CKEDITOR.style( {
            element:'a',
            type:CKEDITOR.STYLE_INLINE,
            alwaysRemoveElement:1
        } );
        editor.removeStyle( style );
    },

    refresh: function( editor, path ) {
        // Despite our initial hope, document.queryCommandEnabled() does not work
        // for this in Firefox. So we must detect the state by element paths.

        var element = path.lastElement && path.lastElement.getAscendant( 'a', true );

        if ( element && element.getName() == 'a' && element.getAttribute( 'href' ) && element.getChildCount() )
            this.setState( CKEDITOR.TRISTATE_OFF );
        else
            this.setState( CKEDITOR.TRISTATE_DISABLED );
    },

    contextSensitive: 1,
    startDisabled: 1
};

CKEDITOR.tools.extend( CKEDITOR.config, {
    linkShowAdvancedTab: true,
    linkShowTargetTab: true
});
