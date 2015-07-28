/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

(function() {
	var imageDialog = function( editor, dialogType ) {
			// Load image preview.
			var IMAGE = 1,
				LINK = 2,
				PREVIEW = 4,
				CLEANUP = 8,
				regexGetSize = /^\s*(\d+)((px)|\%)?\s*$/i,
				regexGetSizeOrEmpty = /(^\s*(\d+)((px)|\%)?\s*$)|^$/i,
				pxLengthRegex = /^\d+px$/;

			var updatePreview = function( dialog ) {
					//Don't load before onShow.
					if ( !dialog.originalElement || !dialog.preview )
						return 1;

					// Read attributes and update imagePreview;
					dialog.commitContent( PREVIEW, dialog.preview );
					return 0;
				};

			// Custom commit dialog logic, where we're intended to give inline style
			// field (txtdlgGenStyle) higher priority to avoid overwriting styles contribute
			// by other fields.
			function commitContent() {
				var args = arguments;
				var inlineStyleField = this.getContentElement( 'advanced', 'txtdlgGenStyle' );
				inlineStyleField && inlineStyleField.commit.apply( inlineStyleField, args );

				this.foreach( function( widget ) {
					if ( widget.commit && widget.id != 'txtdlgGenStyle' )
						widget.commit.apply( widget, args );
				});
			}

			// Avoid recursions.
			var incommit;

			// Synchronous field values to other impacted fields is required, e.g. border
			// size change should alter inline-style text as well.
			function commitInternally( targetFields ) {
				if ( incommit )
					return;

				incommit = 1;

				var dialog = this.getDialog(),
					element = dialog.imageElement;
				if ( element ) {
					// Commit this field and broadcast to target fields.
					this.commit( IMAGE, element );

					targetFields = [].concat( targetFields );
					var length = targetFields.length,
						field;
					for ( var i = 0; i < length; i++ ) {
						field = dialog.getContentElement.apply( dialog, targetFields[ i ].split( ':' ) );
						// May cause recursion.
						field && field.setup( IMAGE, element );
					}
				}

				incommit = 0;
			}


			var resetSize = function( dialog ) {
					var oImageOriginal = dialog.originalElement;
					if ( oImageOriginal.getCustomData( 'isReady' ) == 'true' ) {
						var widthField = dialog.getContentElement( 'info', 'txtWidth' ),
							heightField = dialog.getContentElement( 'info', 'txtHeight' );
						widthField && widthField.setValue( oImageOriginal.$.width );
						heightField && heightField.setValue( oImageOriginal.$.height );
					}
					updatePreview( dialog );
				};

			var setupDimension = function( type, element ) {
					if ( type != IMAGE )
						return;

					function checkDimension( size, defaultValue ) {
						var aMatch = size.match( regexGetSize );
						if ( aMatch ) {
							if ( aMatch[ 2 ] == '%' ) // % is allowed.
							{
								aMatch[ 1 ] += '%';
							}
							return aMatch[ 1 ];
						}
						return defaultValue;
					}

					var dialog = this.getDialog(),
						value = '',
						dimension = this.id == 'txtWidth' ? 'width' : 'height',
						size = element.getAttribute( dimension );

					if ( size )
						value = checkDimension( size, value );
					value = checkDimension( element.getStyle( dimension ), value );

					this.setValue( value );
				};

			var previewPreloader;

			var onImgLoadEvent = function() {
					// Image is ready.
					var original = this.originalElement;
					original.setCustomData( 'isReady', 'true' );
					original.removeListener( 'load', onImgLoadEvent );
					original.removeListener( 'error', onImgLoadErrorEvent );
					original.removeListener( 'abort', onImgLoadErrorEvent );

					// Hide loader
					//CKEDITOR.document.getById( imagePreviewLoaderId ).setStyle( 'display', 'none' );

					// New image -> new domensions
					if ( !this.dontResetSize )
						resetSize( this );

					if ( this.firstLoad )
						CKEDITOR.tools.setTimeout( function() {

					}, 0, this );

					this.firstLoad = false;
					this.dontResetSize = false;
				};

			var onImgLoadErrorEvent = function() {
					// Error. Image is not loaded.
					var original = this.originalElement;
					original.removeListener( 'load', onImgLoadEvent );
					original.removeListener( 'error', onImgLoadErrorEvent );
					original.removeListener( 'abort', onImgLoadErrorEvent );

					// Set Error image.
					var noimage = CKEDITOR.getUrl( CKEDITOR.plugins.get( 'image' ).path + 'images/noimage.png' );

					if ( this.preview )
						this.preview.setAttribute( 'src', noimage );

					// Hide loader
					//CKEDITOR.document.getById( imagePreviewLoaderId ).setStyle( 'display', 'none' );

				};

			var numbering = function( id ) {
					return CKEDITOR.tools.getNextId() + '_' + id;
				},
				//imagePreviewLoaderId = numbering( 'ImagePreviewLoader' ),
				previewLinkId = numbering( 'previewLink' ),
				previewImageId = numbering( 'previewImage'),
                imageUploadId  = numbering( 'imageUpload' );

			return {
				title: editor.lang.image[ dialogType == 'image' ? 'title' : 'titleButton' ],
				minWidth: 420,
				minHeight: 360,
				onShow: function() {
                    if($(imageUploadId).get('html') == '') {
                        window.parent.uploader.create(imageUploadId, null);
                    }

					this.imageElement = false;
					this.linkElement = false;

					// Default: create a new element.
					this.imageEditMode = false;
					this.linkEditMode = false;

					this.lockRatio = true;
					this.userlockRatio = 0;
					this.dontResetSize = false;
					this.firstLoad = true;
					this.addLink = false;

					var editor = this.getParentEditor(),
						sel = editor.getSelection(),
						element = sel && sel.getSelectedElement(),
						link = element && editor.elementPath( element ).contains( 'a', 1 );

					//Hide loader.
					//CKEDITOR.document.getById( imagePreviewLoaderId ).setStyle( 'display', 'none' );
					// Create the preview before setup the dialog contents.
					previewPreloader = new CKEDITOR.dom.element( 'img', editor.document );
					this.preview = CKEDITOR.document.getById( previewImageId );

					// Copy of the image
					this.originalElement = editor.document.createElement( 'img' );
					this.originalElement.setAttribute( 'alt', '' );
					this.originalElement.setCustomData( 'isReady', 'false' );

					if ( link ) {
						this.linkElement = link;
						this.linkEditMode = true;

						// Look for Image element.
						var linkChildren = link.getChildren();
						if ( linkChildren.count() == 1 ) // 1 child.
						{
							var childTagName = linkChildren.getItem( 0 ).getName();
							if ( childTagName == 'img' || childTagName == 'input' ) {
								this.imageElement = linkChildren.getItem( 0 );
								if ( this.imageElement.getName() == 'img' )
									this.imageEditMode = 'img';
								else if ( this.imageElement.getName() == 'input' )
									this.imageEditMode = 'input';
							}
						}
						// Fill out all fields.
						if ( dialogType == 'image' )
							this.setupContent( LINK, link );
					}

					if ( element && element.getName() == 'img' && !element.data( 'cke-realelement' ) || element && element.getName() == 'input' && element.getAttribute( 'type' ) == 'image' ) {
						this.imageEditMode = element.getName();
						this.imageElement = element;
					}

					if ( this.imageEditMode ) {
						// Use the original element as a buffer from  since we don't want
						// temporary changes to be committed, e.g. if the dialog is canceled.
						this.cleanImageElement = this.imageElement;
						this.imageElement = this.cleanImageElement.clone( true, true );

						// Fill out all fields.
						this.setupContent( IMAGE, this.imageElement );
					} else
						this.imageElement = editor.document.createElement( 'img' );

					// Dont show preview if no URL given.
					if ( !CKEDITOR.tools.trim( this.getValueOf( 'info', 'txtUrl' ) ) ) {
						this.preview.removeAttribute( 'src' );
						this.preview.setStyle( 'display', 'none' );
					}
				},
				onOk: function() {
					// Edit existing Image.
					if ( this.imageEditMode ) {
						var imgTagName = this.imageEditMode;

						// Image dialog and Input element.
						if ( dialogType == 'image' && imgTagName == 'input' && confirm( editor.lang.image.button2Img ) ) {
							// Replace INPUT-> IMG
							imgTagName = 'img';
							this.imageElement = editor.document.createElement( 'img' );
							this.imageElement.setAttribute( 'alt', '' );
							editor.insertElement( this.imageElement );
						}
						// ImageButton dialog and Image element.
						else if ( dialogType != 'image' && imgTagName == 'img' && confirm( editor.lang.image.img2Button ) ) {
							// Replace IMG -> INPUT
							imgTagName = 'input';
							this.imageElement = editor.document.createElement( 'input' );
							this.imageElement.setAttributes({
								type: 'image',
								alt: ''
							});
							editor.insertElement( this.imageElement );
						} else {
							// Restore the original element before all commits.
							this.imageElement = this.cleanImageElement;
							delete this.cleanImageElement;
						}
					} else // Create a new image.
					{
						// Image dialog -> create IMG element.
						if ( dialogType == 'image' )
							this.imageElement = editor.document.createElement( 'img' );
						else {
							this.imageElement = editor.document.createElement( 'input' );
							this.imageElement.setAttribute( 'type', 'image' );
						}
						this.imageElement.setAttribute( 'alt', '' );
					}

					// Create a new link.
					if ( !this.linkEditMode )
						this.linkElement = editor.document.createElement( 'a' );

					// Set attributes.
					this.commitContent( IMAGE, this.imageElement );
					this.commitContent( LINK, this.linkElement );

					// Remove empty style attribute.
					if ( !this.imageElement.getAttribute( 'style' ) )
						this.imageElement.removeAttribute( 'style' );

					// Insert a new Image.
					if ( !this.imageEditMode ) {
						if ( this.addLink ) {
							//Insert a new Link.
							if ( !this.linkEditMode ) {
								editor.insertElement( this.linkElement );
								this.linkElement.append( this.imageElement, false );
							} else //Link already exists, image not.
							editor.insertElement( this.imageElement );
						} else
							editor.insertElement( this.imageElement );
					} else // Image already exists.
					{
						//Add a new link element.
						if ( !this.linkEditMode && this.addLink ) {
							editor.insertElement( this.linkElement );
							this.imageElement.appendTo( this.linkElement );
						}
						//Remove Link, Image exists.
						else if ( this.linkEditMode && !this.addLink ) {
							editor.getSelection().selectElement( this.linkElement );
							editor.insertElement( this.imageElement );
						}
					}
				},
				onLoad: function() {
					if ( dialogType != 'image' )
						this.hidePage( 'Link' ); //Hide Link tab.
					var doc = this._.element.getDocument();


					this.commitContent = commitContent;
				},
				onHide: function() {
					if ( this.preview )
						this.commitContent( CLEANUP, this.preview );

					if ( this.originalElement ) {
						this.originalElement.removeListener( 'load', onImgLoadEvent );
						this.originalElement.removeListener( 'error', onImgLoadErrorEvent );
						this.originalElement.removeListener( 'abort', onImgLoadErrorEvent );
						this.originalElement.remove();
						this.originalElement = false; // Dialog is closed.
					}

					delete this.imageElement;
				},
				contents: [
					{
					id: 'info',
					label: editor.lang.image.infoTab,
					accessKey: 'I',
					elements: [
						{
						type: 'vbox',
						padding: 0,
						children: [
							{
							type: 'hbox',
							widths: [ '280px', '110px' ],
							align: 'right',
							children: [
								{
								id: 'txtUrl',
								type: 'text',
								label: 'Ссылка на изображение',
								required: true,
                                onFocus: function() {
                                    var dialog = this.getDialog(),
                                        newUrl = this.getValue();

                                    //Update original image
                                    if ( newUrl.length > 0 ) //Prevent from load before onShow
                                    {
                                        dialog = this.getDialog();
                                        var original = dialog.originalElement;

                                        dialog.preview.removeStyle( 'display' );

                                        original.setCustomData( 'isReady', 'false' );
                                        // Show loader
                                        //var loader = CKEDITOR.document.getById( imagePreviewLoaderId );
                                        //if ( loader )
                                        //    loader.setStyle( 'display', '' );

                                        original.on( 'load', onImgLoadEvent, dialog );
                                        original.on( 'error', onImgLoadErrorEvent, dialog );
                                        original.on( 'abort', onImgLoadErrorEvent, dialog );
                                        original.setAttribute( 'src', newUrl );

                                        // Query the preloader to figure out the url impacted by based href.
                                        previewPreloader.setAttribute( 'src', newUrl );
                                        dialog.preview.setAttribute( 'src', previewPreloader.$.src );
                                        updatePreview( dialog );
                                    }
                                    // Dont show preview if no URL given.
                                    else if ( dialog.preview ) {
                                        dialog.preview.removeAttribute( 'src' );
                                        dialog.preview.setStyle( 'display', 'none' );
                                    }

                                },
								onChange: function() {
									var dialog = this.getDialog(),
										newUrl = this.getValue();

									//Update original image
									if ( newUrl.length > 0 ) //Prevent from load before onShow
									{
										dialog = this.getDialog();
										var original = dialog.originalElement;

										dialog.preview.removeStyle( 'display' );

										original.setCustomData( 'isReady', 'false' );
										// Show loader
										//var loader = CKEDITOR.document.getById( imagePreviewLoaderId );
										//if ( loader )
										//	loader.setStyle( 'display', '' );

										original.on( 'load', onImgLoadEvent, dialog );
										original.on( 'error', onImgLoadErrorEvent, dialog );
										original.on( 'abort', onImgLoadErrorEvent, dialog );
										original.setAttribute( 'src', newUrl );

										// Query the preloader to figure out the url impacted by based href.
										previewPreloader.setAttribute( 'src', newUrl );
										dialog.preview.setAttribute( 'src', previewPreloader.$.src );
										updatePreview( dialog );
									}
									// Dont show preview if no URL given.
									else if ( dialog.preview ) {
										dialog.preview.removeAttribute( 'src' );
										dialog.preview.setStyle( 'display', 'none' );
									}
								},
								setup: function( type, element ) {
									if ( type == IMAGE ) {
										var url = element.data( 'cke-saved-src' ) || element.getAttribute( 'src' );
										var field = this;

										this.getDialog().dontResetSize = true;

										field.setValue( url ); // And call this.onChange()
										// Manually set the initial value.(#4191)
										field.setInitValue();
									}
								},
								commit: function( type, element ) {
									if ( type == IMAGE && ( this.getValue() || this.isChanged() ) ) {
										element.data( 'cke-saved-src', this.getValue() );
										element.setAttribute( 'src', this.getValue() );
									} else if ( type == CLEANUP ) {
										element.setAttribute( 'src', '' ); // If removeAttribute doesn't work.
										element.removeAttribute( 'src' );
									}
								},
								validate: CKEDITOR.dialog.validate.notEmpty( editor.lang.image.urlMissing )
							},
								{
								type: 'button',
								id: 'browse',
								// v-align with the 'txtUrl' field.
								// TODO: We need something better than a fixed size here.
								style: 'display:inline-block;margin-top:10px;',
								align: 'center',
								label: editor.lang.common.browseServer,
								hidden: true,
								filebrowser: 'info:txtUrl'
							}
							]
						}
						]
					},
						{
						id: 'txtAlt',
						type: 'text',
						label: editor.lang.image.alt,
						accessKey: 'T',
						'default': '',
						onChange: function() {
							updatePreview( this.getDialog() );
						},
						setup: function( type, element ) {
							if ( type == IMAGE )
								this.setValue( element.getAttribute( 'alt' ) );
						},
						commit: function( type, element ) {
							if ( type == IMAGE ) {
								if ( this.getValue() || this.isChanged() )
									element.setAttribute( 'alt', this.getValue() );
							} else if ( type == PREVIEW ) {
								element.setAttribute( 'alt', this.getValue() );
							} else if ( type == CLEANUP ) {
								element.removeAttribute( 'alt' );
							}
						}
					},
						{
						type: 'hbox',
						children: [
							{
							type: 'vbox',
							height: '250px',
							children: [
								{
								type: 'html',
								id: 'htmlPreview',
								style: 'width:95%;',
								html: '<div>' + CKEDITOR.tools.htmlEncode( editor.lang.common.preview ) + '<br>' +
									'<div class="ImagePreviewBox"><table><tr><td>' +
										'<a href="javascript:void(0)" target="_blank" onclick="return false;" id="' + previewLinkId + '">' +
										'<img id="' + previewImageId + '" alt="" /></a>' +
										( editor.config.image_previewText || 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. ' +
									      'Maecenas feugiat consequat diam. Maecenas metus. Vivamus diam purus, cursus a, commodo non, facilisis vitae, nulla.') +
									'</td></tr></table></div></div>'
								}
							]
						}
						]
					}
					]
				},
					{
					id: 'Link',
					label: editor.lang.image.linkTab,
					padding: 0,
					elements: [
						{
						id: 'txtUrl',
						type: 'text',
						label: editor.lang.common.url,
						style: 'width: 100%',
						'default': '',
						setup: function( type, element ) {
							if ( type == LINK ) {
								var href = element.data( 'cke-saved-href' );
								if ( !href )
									href = element.getAttribute( 'href' );
								this.setValue( href );
							}
						},
						commit: function( type, element ) {
							if ( type == LINK ) {
								if ( this.getValue() || this.isChanged() ) {
									var url = decodeURI( this.getValue() );
									element.data( 'cke-saved-href', url );
									element.setAttribute( 'href', url );

									if ( this.getValue() || !editor.config.image_removeLinkByEmptyURL )
										this.getDialog().addLink = true;
								}
							}
						}
					},
						{
						type: 'button',
						id: 'browse',
						filebrowser: {
							action: 'Browse',
							target: 'Link:txtUrl',
							url: editor.config.filebrowserImageBrowseLinkUrl
						},
						style: 'float:right',
						hidden: true,
						label: editor.lang.common.browseServer
					},
						{
						id: 'cmbTarget',
						type: 'select',
						label: editor.lang.common.target,
						'default': '',
						items: [
							[ editor.lang.common.notSet, '' ],
							[ editor.lang.common.targetNew, '_blank' ],
							[ editor.lang.common.targetTop, '_top' ],
							[ editor.lang.common.targetSelf, '_self' ],
							[ editor.lang.common.targetParent, '_parent' ]
							],
						setup: function( type, element ) {
							if ( type == LINK )
								this.setValue( element.getAttribute( 'target' ) || '' );
						},
						commit: function( type, element ) {
							if ( type == LINK ) {
								if ( this.getValue() || this.isChanged() )
									element.setAttribute( 'target', this.getValue() );
							}
						}
					}
					]
				},
					{
					id: 'Upload',
					hidden: false,
					label: editor.lang.image.upload,
					elements: [
                        {
                            type: 'html',
                            id: 'htmlImageUpload',
                            html: '<div id="' + imageUploadId + '"></div>'
                        }
					]
				}
				]
			};
		};

	CKEDITOR.dialog.add( 'image', function( editor ) {
		return imageDialog( editor, 'image' );
	});

	CKEDITOR.dialog.add( 'imagebutton', function( editor ) {
		return imageDialog( editor, 'imagebutton' );
	});
})();
