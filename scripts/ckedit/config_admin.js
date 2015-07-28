CKEDITOR.editorConfig = function(a) {
    a.toolbarGroups = [{
        name: "document",
        groups: ["mode", "document", "doctools"]
    }, {
        name: "basicstyles",
        groups: ["basicstyles", "cleanup"]
    }, {
        name: "styles",
        group: ["format"]
    }, {
        name: "paragraph",
        groups: ["list"]
    }, {
        name: "links"
    }, {
        name: "insert"
    }, {
        name: "undo"
    }, {
        name: "others"
    }];
    a.disableNativeSpellChecker = !1;
    a.autoParagraph = !1;
    a.enterMode = CKEDITOR.ENTER_BR;
    a.shiftEnterMode = CKEDITOR.ENTER_P;
    a.removeButtons = "SpecialChar,Highlight,eCut,Styles,Underline,Subscript,Superscript,RemoveFormat";
    a.contentsCss = CKEDITOR.basePath + "contents.css";
    
    //@todo: не работает! все равно вырезается атрибуты/стили
    a.allowedContent = true;
    a.removeFormatAttributes = "";
    a.removeFormatTags = "";
};