window.addEvent('domready', function(){
    
    var hash = window.location.hash;
    if ( hash != '' ){
        hash = hash.split('_');
        if ( hash[0] == '#comment' ) {
            if($('new_msgs_'+hash[1])) { $('new_msgs_'+hash[1]).set('need_change', 0); } dialogue_toggle(hash[1]);markRead(hash[1]);
            var a = $('comment_'+hash[2]);
            if (a) {
                new Fx.Scroll(window,{duration:0}).toElement($(a));
            }
        }
    }
    
    if (typeof PROJECT_BANNED_PID != "undefined") {
        banned.addContext(PROJECT_BANNED_PID, 3, PROJECT_BANNED_URI, PROJECT_BANNED_NAME);
    }
});

