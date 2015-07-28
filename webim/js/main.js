function ae() {
    var d = document.getElementById("thread");
    var be = document.getElementById("h");
    var gh = document.getElementById("m");
    var ce = document.getElementById("f");
    if (d && be) {
        ve = be.offsetHeight;
        we = gh.offsetHeight;
        xe = ce.offsetHeight;
        ye = document.documentElement.clientHeight - ve - we - xe - 024;
        if (ye > 0260) {
            d.style.height = ye + "px";
        } else {
            d.style.height = (112 + 64) + "px";
        }
    }
}
if (window.addEventListener) {
    window.addEventListener("resize", ae, false);
    window.addEventListener("load", ae, false);
} else if (window.attachEvent) {
    window.attachEvent("onresize", ae);
    window.attachEvent("onload", ae);
}
function ll() {
    var kl = document.getElementById('popup');
    var jl = document.getElementById('fader');
    var ze = document.getElementsByTagName('select');
    var $e = kl.getElementsByTagName('select');
    for (var i = 0; i < ze.length; i++) {
        ze[i].style.visibility = 'hidden';
    }
    for (var i = 0; i < $e.length; i++) {
        $e[i].style.visibility = 'visible';
    }
    jl.style.width = document.documentElement.scrollWidth + 'px';
    jl.style.height = document.documentElement.scrollHeight + 'px';
    kl.style.display = 'block';
    jl.style.display = 'block';
    kl.style.top = (document.documentElement.scrollTop + document.documentElement.clientHeight / 2 - kl.clientHeight / 2) + 'px';
    kl.style.left = (document.documentElement.scrollLeft + document.documentElement.clientWidth / 2 - kl.clientWidth / 2) + 'px';
}
function hidePopup() {
    var kl = document.getElementById('popup');
    var jl = document.getElementById('fader');
    kl.style.display = 'none';
    jl.style.display = 'none';
    var ze = document.getElementsByTagName('select');
    for (var i = 0; i < ze.length; i++) {
        ze[i].style.visibility = 'visible';
    }
}