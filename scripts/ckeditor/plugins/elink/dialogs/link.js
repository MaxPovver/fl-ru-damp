CKEDITOR.dialog.add( 'elink', function ( editor ) {
    var plugin = CKEDITOR.plugins.elink;
    var linkLang = editor.lang.elink;

    // Loads the parameters in a selected link to the link dialog fields.
    var javascriptProtocolRegex = /^javascript:/,
        emailRegex = /^mailto:([^?]+)(?:\?(.+))?$/,
        emailSubjectRegex = /subject=([^;?:@&=$,\/]*)/,
        emailBodyRegex = /body=([^;?:@&=$,\/]*)/,
        anchorRegex = /^#(.*)$/,
        urlRegex = /^((?:http|https|ftp|news):\/\/)?(.*)$/,
        selectableTargets = /^(_(?:self|top|parent|blank))$/,
        encodedEmailLinkRegex = /^javascript:void\(location\.href='mailto:'\+String\.fromCharCode\(([^)]+)\)(?:\+'(.*)')?\)$/,
        functionCallProtectedEmailLinkRegex = /^javascript:([^(]+)\(([^)]+)\)$/;

    var popupRegex = /\s*window.open\(\s*this\.href\s*,\s*(?:'([^']*)'|null)\s*,\s*'([^']*)'\s*\)\s*;\s*return\s*false;*\s*/;
    var popupFeaturesRegex = /(?:^|,)([^=]+)=(\d+|yes|no)/gi;

    var parseLink = function( editor, element ) {
            var href = ( element && ( element.data( 'cke-saved-href' ) || element.getAttribute( 'href' ) ) ) || '',
                javascriptMatch, emailMatch, anchorMatch, urlMatch,
                retval = {};

            if ( ( javascriptMatch = href.match( javascriptProtocolRegex ) ) ) {
                if ( emailProtection == 'encode' ) {
                    href = href.replace( encodedEmailLinkRegex, function( match, protectedAddress, rest ) {
                        return 'mailto:' +
                            String.fromCharCode.apply( String, protectedAddress.split( ',' ) ) +
                            ( rest && unescapeSingleQuote( rest ) );
                    });
                }
                // Protected email link as function call.
                else if ( emailProtection ) {
                    href.replace( functionCallProtectedEmailLinkRegex, function( match, funcName, funcArgs ) {
                        if ( funcName == compiledProtectionFunction.name ) {
                            retval.type = 'email';
                            var email = retval.email = {};

                            var paramRegex = /[^,\s]+/g,
                                paramQuoteRegex = /(^')|('$)/g,
                                paramsMatch = funcArgs.match( paramRegex ),
                                paramsMatchLength = paramsMatch.length,
                                paramName, paramVal;

                            for ( var i = 0; i < paramsMatchLength; i++ ) {
                                paramVal = decodeURIComponent( unescapeSingleQuote( paramsMatch[ i ].replace( paramQuoteRegex, '' ) ) );
                                paramName = compiledProtectionFunction.params[ i ].toLowerCase();
                                email[ paramName ] = paramVal;
                            }
                            email.address = [ email.name, email.domain ].join( '@' );
                        }
                    });
                }
            }

            if ( !retval.type ) {
                if ( ( anchorMatch = href.match( anchorRegex ) ) ) {
                    retval.type = 'anchor';
                    retval.anchor = {};
                    retval.anchor.name = retval.anchor.id = anchorMatch[ 1 ];
                }
                // Protected email link as encoded string.
                else if ( ( emailMatch = href.match( emailRegex ) ) ) {
                    var subjectMatch = href.match( emailSubjectRegex ),
                        bodyMatch = href.match( emailBodyRegex );

                    retval.type = 'email';
                    var email = ( retval.email = {} );
                    email.address = emailMatch[ 1 ];
                    subjectMatch && ( email.subject = decodeURIComponent( subjectMatch[ 1 ] ) );
                    bodyMatch && ( email.body = decodeURIComponent( bodyMatch[ 1 ] ) );
                }
                // urlRegex matches empty strings, so need to check for href as well.
                else if ( href && ( urlMatch = href.match( urlRegex ) ) ) {
                    retval.type = 'url';
                    retval.url = {};
                    retval.url.protocol = urlMatch[ 1 ];
                    retval.url.url = urlMatch[ 2 ];
                } else
                    retval.type = 'url';
            }

            // Load target and popup settings.
            if ( element ) {
                var target = element.getAttribute( 'target' );
                retval.target = {};
                retval.adv = {};

                // IE BUG: target attribute is an empty string instead of null in IE if it's not set.
                if ( !target ) {
                    var onclick = element.data( 'cke-pa-onclick' ) || element.getAttribute( 'onclick' ),
                        onclickMatch = onclick && onclick.match( popupRegex );
                    if ( onclickMatch ) {
                        retval.target.type = 'popup';
                        retval.target.name = onclickMatch[ 1 ];

                        var featureMatch;
                        while ( ( featureMatch = popupFeaturesRegex.exec( onclickMatch[ 2 ] ) ) ) {
                            // Some values should remain numbers (#7300)
                            if ( ( featureMatch[ 2 ] == 'yes' || featureMatch[ 2 ] == '1' ) && !( featureMatch[ 1 ] in { height:1,width:1,top:1,left:1 } ) )
                                retval.target[ featureMatch[ 1 ] ] = true;
                            else if ( isFinite( featureMatch[ 2 ] ) )
                                retval.target[ featureMatch[ 1 ] ] = featureMatch[ 2 ];
                        }
                    }
                } else {
                    var targetMatch = target.match( selectableTargets );
                    if ( targetMatch )
                        retval.target.type = retval.target.name = target;
                    else {
                        retval.target.type = 'frame';
                        retval.target.name = target;
                    }
                }

                var me = this;
                var advAttr = function( inputName, attrName ) {
                        var value = element.getAttribute( attrName );
                        if ( value !== null )
                            retval.adv[ inputName ] = value || '';
                    };
                advAttr( 'advId', 'id' );
                advAttr( 'advLangDir', 'dir' );
                advAttr( 'advAccessKey', 'accessKey' );

                retval.adv.advName = element.data( 'cke-saved-name' ) || element.getAttribute( 'name' ) || '';
                advAttr( 'advLangCode', 'lang' );
                advAttr( 'advTabIndex', 'tabindex' );
                advAttr( 'advTitle', 'title' );
                advAttr( 'advContentType', 'type' );
                advAttr( 'advCharset', 'charset' );
                advAttr( 'advStyles', 'style' );
                advAttr( 'advRel', 'rel' );
            }

            // Find out whether we have any anchors in the editor.
            var anchors = retval.anchors = [],
                i, count, item;

            // Record down the selected element in the dialog.
            this._.selectedElement = element;
            return retval;
        };

    return {
        title: linkLang.title,
        resizable: CKEDITOR.DIALOG_RESIZE_NONE,
        minWidth: 350,
        minHeight: 50,

        contents: [
            {
                id:    'popup-link',
                label: '',
                elements: [
                    {   
                        type:  'text',
                        id:    'elink',
                        label: '',
                        onLoad: function( data ) {
                            this.allowOnChange = true;
                        },
                        validate: function() {
                            var func = CKEDITOR.dialog.validate.notEmpty( linkLang.noUrl );
                            return func.apply( this );
                        },
                        setup: function( data ) {
                            this.allowOnChange = false;
                            if ( data.url )
                                    this.setValue( data.url.url );
                            this.allowOnChange = true;
                        },
                        commit: function( data ) {
                            if ( !data.url )
                                    data.url = {};

                            data.url.url = this.getValue();
                            this.allowOnChange = false;
                        }
                    }
                ]
            }
        ],
        onShow: function() {
            var editor = this.getParentEditor(),
                selection = editor.getSelection(),
                element = null;

            // Fill in all the relevant fields if there's already one link selected.
            if ( ( element = plugin.getSelectedLink( editor ) ) && element.hasAttribute( 'href' ) )
                selection.selectElement( element );
            else
                element = null;

            this.setupContent( parseLink.apply( this, [ editor, element ] ) );
        },
        onOk: function() {
            var attributes = {},
                removeAttributes = [],
                data = {},
                editor = this.getParentEditor();

                this.commitContent( data );
                
                var url = ( data.url && CKEDITOR.tools.trim( data.url.url ) ) || '';
                attributes[ 'data-cke-saved-href' ] = ( url.indexOf( 'http' ) === 0 ) ? url : 'http://' + url;
                
                var selection = editor.getSelection();

                attributes.href = attributes[ 'data-cke-saved-href' ];

                if ( !this._.selectedElement ) {
                    var range = selection.getRanges( 1 )[ 0 ];

                    // Use link URL as text with a collapsed cursor.
                    if ( range.collapsed ) {
                        // Short mailto link text view (#5736).
                        var text = new CKEDITOR.dom.text( data.type == 'email' ? data.email.address : attributes[ 'data-cke-saved-href' ], editor.document );
                        range.insertNode( text );
                        range.selectNodeContents( text );
                    }

                    // Apply style.
                    var style = new CKEDITOR.style({
                        element: 'a', 
                        attributes: attributes
                    } );
                    style.type = CKEDITOR.STYLE_INLINE; // need to override... dunno why.
                    style.applyToRange( range );
                    range.select();
                } else {
                    // We're only editing an existing link, so just overwrite the attributes.
                    var element = this._.selectedElement,
                    href = element.data( 'cke-saved-href' ),
                    textView = element.getHtml();

                    element.setAttributes( attributes );
                    element.removeAttributes( removeAttributes );

                    if ( data.adv && data.adv.advName && CKEDITOR.plugins.link.synAnchorSelector )
                        element.addClass( element.getChildCount() ? 'cke_anchor' : 'cke_anchor_empty' );

                    // Update text view when user changes protocol (#4612).
                    if ( href == textView || data.type == 'email' && textView.indexOf( '@' ) != -1 ) {
                        // Short mailto link text view (#5736).
                        element.setHtml( data.type == 'email' ? data.email.address : attributes[ 'data-cke-saved-href' ] );
                    }

                    selection.selectElement( element );
                    delete this._.selectedElement;
                }
        }
    };
});