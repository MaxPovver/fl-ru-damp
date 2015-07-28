var myAgent = "";
var $u = 0;
var myRealAgent = "";

function ed() {
    var fd = ["opera", "msie", "safari", "firefox", "netscape", "mozilla"];
    var bp = navigator.userAgent.toLowerCase();
    for (var i = 0; i < fd.length; i++) {
        var gd = fd[i];
        if (bp.indexOf(gd) != -1) {
            myAgent = gd;
            if (!window.RegExp) break;
            var ad = new RegExp(gd + "[ \/]?([0-9]+(\.[0-9]+)?)");
            if (ad.exec(bp) != null) {
                $u = parseFloat(RegExp.$1);
            }
            break;
        }
    }
    myRealAgent = myAgent;
    if (navigator.product == "Gecko") myAgent = "moz";
}
ed();

function getEl(name) {
    return document.getElementById(name);
}