var disable = ' b-button_disabled';
window.onload = function () {
    document.disableFeedbackButtons = function() {
        var ls = this.getElementById('content').getElementsByTagName("span");        
        if (ls.length) {
            var className = ls[ls.length - 1].className = ls[ls.length - 1].className;
            if (className != "b-button__txt" && className != "mvisitor") {
                ls = this.getElementById('content').getElementsByTagName("a");
                for (var i = 0; i < ls.length; i++) {
                    if (ls[i].className.indexOf('b-button') != -1) {
                        ls[i].className = ls[i].className.replace(disable, '');
                        ls[i].className += disable;
                    }
                }
            }
        }
    }
}

function showFeedback(e, hideBackLink){
    var target = e.target;
    var lim = 5;
    var i = 0;
    while (target.tagName.toLowerCase() != "a") {
        target = target.parentNode;
        i++;
        if (i > lim) {
            break;
        }
    }
    if (target.tagName.toLowerCase() == "a") {
        if (target.className.indexOf(disable) != -1) {
            return false;
        }
    } else {
        var ls = document.getElementById('content').getElementsByTagName("a");
        for (var i = ls.length - 1; i > -1; i--) {
            if (ls[i].className.indexOf(disable) != -1) {
                return false;
            }else if ( ls[i].className.indexOf("webimFeedbackBtn") != -1 ) {
                break;
            }
        }
    }
    window.parent.location.href=window.parent.location.href + '&action=feedback&lastid=' + window.parent.lastid +  (hideBackLink == 1 ? "&hidebacklink=1":"");
    return false;
}
