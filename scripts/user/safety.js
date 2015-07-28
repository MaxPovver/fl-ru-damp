window.addEvent('load', function(){
   
    if ($('multilevel_switchoff')) {
        $('multilevel_switchoff').addEvent('click', function() {
            if (confirm("Вы действительно хотите отключить двухэтапную аутентификацию")) {
                var form = new Element('form', {'action':'.','method':'post'});
                var elemAction = new Element('input', {'type':'hidden', 'name':'action', 'value':'safety_social'});
                var elemStatus = new Element('input', {'type':'hidden', 'name':'status', 'value':'off'});
                var token = new Element('input', {'type':'hidden', 'name':'u_token_key', 'value':_TOKEN_KEY});
                
                form.adopt(elemAction, elemStatus, token);
                form.setStyle('display','none').inject($(document.body), 'bottom');
                form.submit();
            }
        });
    }
    
});