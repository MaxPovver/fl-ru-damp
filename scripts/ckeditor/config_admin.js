CKEDITOR.editorConfig = function( config ) {
	
	// %REMOVE_START%
	// The configuration options below are needed when running CKEditor from source files.
	config.plugins = 'image,dialogui,dialog,basicstyles,blockquote,clipboard,panel,floatpanel,menu,resize,button,toolbar,elementspath,list,indent,enterkey,entities,popup,floatingspace,listblock,format,htmlwriter,wysiwygarea,fakeobjects,pastefromword,removeformat,sourcearea,specialchar,menubutton,stylescombo,tab,undo,wsc';
	
	// Other plugins
	config.plugins +=  ',elink';
	config.skin = 'moono';
	// %REMOVE_END%

	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbarGroups = [
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'styles', group: ['format'] },
		{ name: 'paragraph',   groups: [ 'list' ] },
		{ name: 'links'},
        { name: 'insert'},
		{ name: 'undo' },
		{ name: 'others' },
	];
    config.disableNativeSpellChecker = false;
	config.autoParagraph = false;
	config.enterMode = CKEDITOR.ENTER_BR;
	config.shiftEnterMode = CKEDITOR.ENTER_P;
	// Remove some buttons, provided by the standard plugins, which we don't
	// need to have in the Standard(s) toolbar.
	config.removeButtons = 'SpecialChar,Highlight,eCut,Styles,Underline,Subscript,Superscript,RemoveFormat';
	config.contentsCss = CKEDITOR.basePath + 'contents.css';//'/css/wysiwyg-txt.css';//[CKEDITOR.basePath + 'contents.css', '/scripts/highlight/default.css']
};
