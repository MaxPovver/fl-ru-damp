function CSRF(key) {
    if(window.Server) return;
    var forms = document.getElementsByTagName("form");
    for(var i = 0;i<forms.length;i++) {
        var token = new Element('input', {type: 'hidden', value:key, name:'u_token_key'});
        if(forms[i].method.toLowerCase() == "post") {
            forms[i].appendChild(token);
        }
    }
    /*$$('form').each(function(elm){
        if(elm.getProperty('rel') != 'access') {
            var token = new Element('input', {type: 'hidden', value:key, name:'u_token_key'});
            elm.grab(token, 'top');  
        }
    }); */
}

function CSRF_Clear() {
    $$('input[name=u_token_key]').each(function(elm){
        $(elm).destroy();
    });
}