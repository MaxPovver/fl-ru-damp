var at = 'message-area';
var bt = 'message-contacts';
var ct = 'message-send';
var vt = 'thread';
var wt = 'thread-refresh';
var xt = 'thread-send';
var yt = 'thread-close';
var zt = 'thread-typing';
var $t = 'thread-rate';
var _t = 'thread-rate-select';
var mu = 'thread-rate-btn';
var nu = 'visitor-name';
var qu = 'visitor-name-lnk';
var ru = 'visitor-name-field';
var su = 'visitor-name-btn';
var tu = 'operator-name';
var uu = 'avatar';
var hu = 'powered-by';
var iu = 'powered-by-lnk';
var ju = 'contacts-name';
var ku = 'contacts-email';
var lu = 'contacts-phone';
var ou = 'connection-status';
var pu = 'sound-control';
var du = 'sound-control-span';

function GET(key) {
    var s = window.location.href;
    var start = s.indexOf(key + '=') + key.length + 1;
    var end = s.indexOf('&', start);
    var r = s.substring(start, end);
    if (end == -1) {
        r = s.substring(start);
    }
    return r;
}

var eu = {
    fu: function (gu) {
        if (gu.contentDocument) {
            return gu.contentDocument;
        } else if (gu.contentWindow) {
            return gu.contentWindow.document;
        } else if (gu.document) {
            return gu.document;
        } else {
            return null;
        }
    },
    au: function (gu) {
        var bu = this.fu(gu);
        bu.open();
        bu.write("<html><head>");
        bu.write("<link  rel=\"stylesheet\"type=\"text/css\"media=\"all\" href=\"" + WM_params.framecss + "\"/>");
        if (navigator.userAgent.toLowerCase().indexOf('safari') != -1) {
            bu.write("<script type=\"text/javascript\" src=\"/webim/js/frame.js?e=7\"></script>");
        }
        bu.write("</head><body bgcolor='#FFFFFF' text='#000000' link='#C28400' vlink='#C28400' alink='#C28400' marginwidth='0' marginheight='0' leftmargin='0' rightmargin='0' topmargin='0' bottommargin='0'>");
        bu.write("<table width='100%' cellspacing='0' cellpadding='0' border='0'><tr><td valign='top' class='message' id='content'></td></tr></table><a id='bottom'/>");
        bu.write("</body></html>");
        bu.close();
        gu.onload = function () {
            if (gu.cu) {
                eu.fu(gu).getElementById('content').innerHTML += gu.cu;
                eu.vu(gu);
            }
        };
    },
    wu: function (gu, xu) {
        var yu = this.fu(gu).getElementById('content');
        if (yu === null) {
            if (!gu.cu) gu.cu = "";
            gu.cu += xu;
        } else {
            yu.innerHTML += xu;
            if (navigator.userAgent.toLowerCase().indexOf('safari') != -1) {
                this.fu(gu).disableFeedbackButtons();
            } else {
                if (! (this.fu(gu).disableFeedbackButtons instanceof Function) ) {
                    var disable = ' b-button_disabled';
                    this.fu(gu).disableFeedbackButtons = function() {
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
                    gu.contentWindow.showFeedback = function(e, hideBackLink) {
                        var document = gu.contentDocument;
                        if (navigator.userAgent.toLowerCase().indexOf('msie') != -1) {
                            document = gu.contentWindow.document;
                        }
                        var d = "";
                        for (var I in e) {
                            d += "e[" + I + "] = " + e[I];
                        }
                        var target = e.target;
                        if (!target) {
                            target = e.srcElement;
                        }
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
                                } else if ( ls[i].className.indexOf("webimFeedbackBtn") != -1 ) {
                                    break;
                                }
                            }
                        }
                        window.parent.location.href=window.parent.location.href + '&action=feedback&lastid=' + window.parent.lastid + (hideBackLink == 1 ? "&hidebacklink=1":"");
                        return false;
                    }
                }
                this.fu(gu).disableFeedbackButtons();
            }
        }
    },
    vu: function (gu) {
        var zu = this.fu(gu).getElementById('bottom');
        if (myAgent == 'opera' && $u < "9.61") {
            gu.contentWindow.scrollTo(0, this.fu(gu).getElementById('content').clientHeight);
        } else if (zu) {
            zu.scrollIntoView(false);
        }
    }
};
var _u = mn.nn();
_u.prototype = {
    mh: 0,
    nh: false,
    qh: "",
    qn: function (pq) {
        this.qh = document.title;
    },
    stop: function () {
        this.nh = false;
        clearTimeout(this.rh);
        document.title = this.qh;
    },
    start: function () {
        this.stop();
        this.mh = 0;
        this.sh();
        this.nh = true;
    },
    th: function () {
        if (!this.nh) {
            return;
        }
        this.mh = this.mh == 0 ? 1 : 0;
        document.title = this.mh != 0 ? this.qh : '* ' + this.qh;
    },
    sh: function () {
        this.th();
        this.rh = setTimeout(this.sh.ln(this), 01274);
    }
};
sq.uh = mn.nn();
mn.sn(sq.uh, sq.lq, {
    qn: function (pq) {
        this.oq(pq);
        this.pq.onComplete = this.hh.ln(this);
        this.pq.jr = this.ih.ln(this);
        this.pq.hr = this._q.ln(this);
        this.pq.timeout = 072460;
        this.jh = {};
        this.frequency = (this.pq.frequency || 2);
        this.kh = 0;
        this.lh = true;
        this.oh = true;
        this.ph = true;
        this.dh = new _u();
        this.eh = '';
        eu.au(this.pq.fh);
        if (this.pq.gh != null) {
            this.pq.gh.onkeydown = ah.bh.ln(ah);
            this.pq.gh.focus();
            if (this.pq.isvisitor) {
                $(ju).onkeydown = ah.bh.ln(ah);
                $(ku).onkeydown = ah.bh.ln(ah);
                $(lu).onkeydown = ah.bh.ln(ah);
            }
        }
        this.sh();
        if (this.pq.isvisitor) {
            this.ch();
        }
    },
    ch: function () {
        var vh = $(iu);
        var wh = $(hu);
        if (vh == null || wh == null) {
            alert("Web Messenger modifications violate the terms of the license agreement");
            return;
        }
        wh.style.visibility = "";
        vh.style.visibility = "";
        vh.href = "http://webim.ru";
        vh.innerHTML = "webim.ru";
        vh.onclick = function (e) {
            window.open("http://webim.ru");
            return false;
        };
    },
    ih: function (xh, yh) {
        this.zh();
        this.$h();
        this.rh = setTimeout(this.sh.ln(this), 01750);
    },
    _q: function (xh) {
        this.zh();
        this.$h();
        this.rh = setTimeout(this.sh.ln(this), (893 + 107));
    },
    _h: function (mi) {
        window.lastid = (this.pq.ni || 0);
        this.pq.fq = 'act=' + mi + '&thread=' + (this.pq.threadid || -1) + '&token=' + (this.pq.token || 0) + '&lastid=' + (this.pq.ni || 0);
        if (this.pq.isvisitor) {
            this.pq.fq += "&visitor=true";
        }
        if (mi == 'refresh' && this.pq.gh && this.pq.gh.value != '') {
            this.pq.fq += "&typed=1";
        }
        if (this.pq.isViewOnly == 1) {
            this.pq.fq += "&viewonly=true";
        }
    },
    $h: function () {
        if (this.jh.pq) {
            this.jh.pq.onComplete = undefined;
        }
        clearTimeout(this.rh);
    },
    sh: function () {
        this._h("refresh");
        this.jh = new sq.vq(this.pq.servl, this.pq);
        if (ft()) {
            this.qi(ft().it());
        }
    },
    hh: function (ri) {
        var si = this.ti(ri);
        this.rh = setTimeout(this.sh.ln(this), this.frequency * 1000);
        return si;
    },
    ti: function (ri) {
        var si = true;
        try {
            var ui = sq.uq(ri);
            if (ui && ui.tagName == 'thread') {
                this.hi(ui);
            } else {
                si = false;
                this.ii(hq, ui, 'refresh messages failed');
            }
        } catch (e) {
            si = false;
        }
        return si;
    },
    ji: function () {
        $('visitor-name-field').value = $('contacts-name').value;
        $('visitor-name-lnk').set("text", $('contacts-name').value);
        Cookie.write("WEBIM_VISITOR_NAME", $('contacts-name').value);
        var name = $(ju).value;
        var ki = $(ku).value;
        var li = $(lu).value;
        var params = 'act=contacts&name=' + name + '&email=' + ki + '&phone=' + li + '&thread=' + (this.pq.threadid || -1) + '&visitor=true' + '&token=' + (this.pq.token || 0);
        new sq.vq(this.pq.servl, {
            fq: params
        });
    },
    oi: function () {
        this.$h();
        if (this.pi++ >= 3) {
            this.pq.gh.disabled = false;
            this.pq.gh.focus();
            this.di = '';
            this.lh = true;
            this.sh();
            return;
        }
        this.lh = false;
        this._h("post");
        var ei = un({}, this.pq);
        ei.fq += "&message=" + encodeURIComponent(this.di);
        ei.onComplete = (function (ri) {
            if (!this.ti(ri)) {
                this.rh = setTimeout(this.oi.ln(this), this.frequency * 01750);
            } else if (this.pq.gh) {
                this.pq.gh.disabled = false;
                this.pq.gh.value = '';
                this.pq.gh.focus();
                this.di = '';
                this.lh = true;
                this.rh = setTimeout(this.sh.ln(this), this.frequency * (942 + 58));
            }
        }).ln(this);
        ei.hr = (function () {
            this.rh = setTimeout(this.oi.ln(this), this.frequency * 01750);
        }).ln(this);
        if (this.pq.gh) {
            this.pq.gh.disabled = true;
        }
        this.jh = new sq.vq(this.pq.servl, ei);
    },
    fi: function (gh) {
        var gi = $(at);
        var ai = $(bt);
        var bi = gi.style.display == "none";
        var ci = new Date();
        if (!bi && (gh == "" || !this.lh)) {
            return false;
        }
        if (this.pq.isvisitor) {
            var vi = /\?\@/;
            var wi = vi.exec(gh);
            if (wi != null) {
                gh = gh.replace(vi, "\077 \100");
            }
        }
        if (bi && this.pq.isvisitor) {
            var xi = $(at);
            xi.style.display = 'inline';
            ai.style.display = 'none';
            this.ji();
            return true;
        }
        if (this.pq.isvisitor) {
            var vi = /\!((http|ftp|https)\:\/\/[^\"]*)/;
            var wi = vi.exec(gh);
            if (wi != null) {
                gh = gh.replace(vi, "!\040" + wi[1]);
            }
        }
        $(ct).focus();
        this.di = gh;
        this.pi = 0;
        this.oi();
        return true;
    },
    yi: function (zi) {
        new sq.vq(this.pq.servl, {
            fq: 'act=rename&thread=' + (this.pq.threadid || -1) + '&token=' + (this.pq.token || 0) + '&name=' + encodeURIComponent(zi) + '&visitor=true'
        });
    },
    $i: function (hq) {
        var ui = sq.uq(hq);
        if (ui && ui.tagName == 'closed') {
           window.close();
           window.location.href = '/';
        } else {
            this.ii(hq, ui, 'cannot close');
        }
    },
    _i: function (mj) {
        this.$h();
        var nj = 'act=rate&thread=' + (this.pq.threadid || -1) + '&token=' + (this.pq.token || 0) + '&rate=' + encodeURIComponent(mj);
        if (this.pq.isvisitor) {
            nj += "&visitor=true";
        }
        new sq.vq(this.pq.servl, {
            fq: nj
        });
    },
    qj: function () {
        var url = window.prompt(WM_localized.urlPrompt, "");
        if (url && url.length > 0) {
            if (url.indexOf('http') != 0) {
                url = "http://" + url;
            }
            this.fi("\041" + url);
        }
    },
    rj: function () {
        var nj = 'act=close&thread=' + (this.pq.threadid || -1) + '&token=' + (this.pq.token || 0);
        if (this.pq.isvisitor) {
            nj += "&visitor=true";
        }
        if (this.pq.isViewOnly) {
            nj += "&viewonly=true";
        }
        new sq.vq(this.pq.servl, {
            fq: nj,
            onComplete: this.$i.ln(this)
        });
    },
    sj: function () {
        var nj = 'act=browser_unload&thread=' + (this.pq.threadid || -1) + '&token=' + (this.pq.token || 0);
        if (this.pq.isvisitor) {
            nj += "&visitor=true";
        }
        new sq.vq(this.pq.servl, {
            fq: nj,
            onComplete: this.$i.ln(this)
        });
    },
    tj: function () {
        if (this.pq.isvisitor) {
            this.sj();
        }
    },
    uj: function () {
        if (!this.pq.isvisitor && !this.pq.isViewOnly && this.eh != 'closed') {
            return confirm(WM_localized.confirmClosing);
        }
        return true;
    },
    hj: function (ij, gh) {
        var jj = qs.us(gh);
        var vi = /\!((http|ftp|https)\:\/\/[^\"]*)/;
        var wi = vi.exec(jj);
        if (wi != null) {
            if (this.pq.isvisitor) {
                window.open(wi[1]);
            }
            jj = jj.replace(vi, wi[1]);
        }
        var vi = /\?\@/;
        var wi = vi.exec(jj);
        if (wi != null) {
            jj = jj.replace(vi, "");
            if (this.pq.isvisitor) {
                var gi = $(at);
                gi.style.display = 'none';
                var ai = $(bt);
                ai.style.display = 'block';
                $(ju).focus();
                if ( $(ju).value.length == 0 ) {
                    $(ju).value = Cookie.read("WEBIM_VISITOR_NAME").replace("+", " ");
                }
            }
        }
        eu.wu(ij, jj);
    },
    kj: function (lj) {
        if ($(zt)) {
            if (lj) {
                $(zt).style.display = 'inline';
            } else {
                $(zt).style.display = 'none';
            }
        }
    },
    oj: function (pj) {
        var dj = qs.us(pj);
        if (this.pq.pj && this.pq.isvisitor) {
            if (dj.length > 0 && (this.pq.pj.src != dj || this.pq.pj.style.display != 'block')) {
                this.pq.pj.style.display = 'block';
                this.pq.pj.src = dj;
            } else {
                this.pq.pj.style.display = 'none';
            }
        }
    },
    hi: function (ui) {
        var ej = false;
        var fj = this.pq.fh;
        var gj = qs.ks(ui, "lastid");
        if (gj) {
            this.pq.ni = gj;
        }
        var aj = qs.ks(ui, "typing");
        if (aj) {
            this.kj(aj == '1');
        }
        var isViewOnly = qs.ks(ui, "viewonly");
        if (isViewOnly == '1' && this.pq.isViewOnly == 0) {
            document.location = document.location + "&viewonly=true";
        }
        var bj = qs.ks(ui, "visitorname");
        var fl_login = qs.ks(ui, "fl_login");
        bj = bj.replace(/</g, '&lt');
        fl_login = fl_login.replace(/</g, '&lt');
        if ( bj /*|| fl_login*/ ) {
            var fullName    = bj;// + (fl_login ? ' ['+fl_login+']' : '');
            var visitorName = $(qu);
            if (visitorName != null && visitorName.innerHTML != fullName) {
                visitorName.innerHTML = fullName;
                if (!this.pq.isvisitor) {
                    var titleSplit = document.title.split('(', 2);
                    this.dh.qh = fullName + ' (' + titleSplit[1];
                    document.title = fullName + ' (' + titleSplit[1];
                }
            }
        }
        var cj = qs.ks(ui, "operatorfullname");
        var vj = qs.ks(ui, "israted");
        var wj = qs.ks(ui, "rate");
        var xj = $($t);
        var yj = $(mu);
        var zj = $(_t);
        this.eh = qs.ks(ui, "state");
        if (this.pq.isvisitor) {
            var $j = zj.options[zj.selectedIndex].value;
            if (wj != "" && wj != $j) {
                for (var i = 0; i < zj.options.length; i++) {
                    if (zj.options[i].value == wj) {
                        zj.selectedIndex = i;
                        zj.options[i].selected = true;
                        break;
                    }
                }
            }
            var _j = $(tu);
            if (_j) {
                if (cj && _j.innerHTML != cj && vj == 'false') {
                    zj.options[0].selected = true;
                }
                mk = cj ? cj : '&nbsp;';
                if (mk != _j.innerHTML) {
                    _j.innerHTML = mk;
                }
            }
            if (cj && vj == 'false') {
                if (xj != null) {
                    xj.className = '';
                }
                if (zj != null) {
                    zj.disabled = false;
                }
                if (yj != null) {
                    yj.disabled = false;
                }
            } else {
                if (xj != null) {
                    xj.className = 'disabled';
                }
                if (zj != null) {
                    zj.disabled = true;
                }
                if (yj != null) {
                    yj.disabled = true;
                }
            }
        }
        for (var i = 0; i < ui.childNodes.length; i++) {
            var nk = ui.childNodes[i];
            if (nk.tagName == 'message') {
                ej = true;
                this.hj(fj, nk);
            } else if (nk.tagName == 'avatar') {
                this.oj(nk);
            }
        }
        this.qk();
        if (ej) {
            eu.vu(this.pq.fh);
            if (qs.ks(ui, "needtoalert") == "true" && !this.ph) {
                this.dh.start();
                if (this.pq.isvisitor) {
                    window.focus();
                    $(at).focus();
                }
                gt();
            }
        }
    },
    ii: function (hq, ui, rk) {
        this.zh();
    },
    zh: function () {
        if (this.sk) {
            clearTimeout(this.sk);
        }
        this.tk();
        this.sk = setTimeout(this.qk.ln(this), (2319 + 681));
    },
    tk: function () {
        if ($(ou)) {
            $(ou).style.display = 'block';
        }
    },
    qk: function () {
        if ($(ou)) {
            $(ou).style.display = 'none';
        }
    },
    qi: function (uk) {
        if ($(du)) {
            var hk = uk ? "action-sound-off" : "action-sound-on";
            if ($(du).className != hk) {
                $(du).className = hk;
            }
        }
        if ($(pu)) {
            var ik = uk ? WM_localized.soundOff : WM_localized.soundOn;
            if ($(pu).title != ik) {
                $(pu).title = ik;
            }
        }
    },
    jk: function () {
        if (this.ph) {
            return;
        }
        this.ph = true;
        if (this.dh != null) {
            this.dh.stop();
        }
    }
});
kk = mn.nn();
kk.prototype = {
    qn: function (pq) {
        this.pq = pq;
        this.lk = 0;
        if (this.pq.ok && this.pq.pk && this.pq.dk) {
            this.pq.dk.onmousedown = this.ek.ln(this);
            this.pq.dk.onmouseup = this.fk.ln(this);
            this.pq.dk.onmousemove = this.gk.ln(this);
        }
    },
    ek: function (e) {
        var or = e || event;
        if (this.pq.dk.setCapture) {
            this.pq.dk.setCapture();
        }
        this.ak = this.pq.ok.style.pixelHeight || this.pq.ok.clientHeight;
        this.bk = or.screenY;
        this.pq.ck = this.pq.ok.style.pixelHeight + this.pq.pk.clientHeight - this.pq.vk;
        this.lk = 1;
    },
    fk: function () {
        if (this.lk) {
            if (this.pq.dk.releaseCapture) this.pq.dk.releaseCapture();
            this.lk = 0;
        }
    },
    gk: function (e) {
        var or = e || event;
        if (this.lk) {
            var wk = this.ak - (or.screenY - this.bk);
            if (wk > this.pq.ck) {
                wk = this.pq.ck;
            } else if (wk < this.pq.xk) {
                wk = this.pq.xk;
            }
            if (myAgent == 'moz') {
                this.pq.ok.style.height = wk + 'px';
            } else {
                this.pq.ok.style.pixelHeight = wk;
            }
        }
    }
};
var yk = {
    zk: {},
    $k: {},
    _k: function () {
        yk.zk.yi($(ru).value);
        $(nu).style.display = 'none';
        $(qu).style.display = 'inline';
        $(qu).innerHTML = $(ru).value;
    },
    ml: function () {
        $(nu).style.display = 'inline';
        $(qu).style.display = 'none';
        $(ru).focus();
    },
    _i: function () {
        var nl = $(_t).selectedIndex;
        var ql = $(_t).options[nl].value;
        if (nl != 0) {
            yk.zk._i(ql);
            var xj = $($t);
            var yj = $(mu);
            var zj = $(_t);
            if (xj != null) {
                xj.className = 'disabled';
            }
            if (zj != null) {
                zj.disabled = true;
            }
            if (yj != null) {
                yj.disabled = true;
            }
        }
    },
    rl: function () {
        var ki = $('email');
        if (ki) {
            if (ki.value.length == 0 && $('error-email')) {
                $('error-email').className = 'error';
                return false;
            } else {
                $('error-email').className = 'error-hidden';
            }
            var vi = /[a-zA-Z0-9_\-\.]*\@[a-zA-Z0-9_\-]*\.[a-zA-Z0-9_\-\.]*/;
            if (!vi.exec(ki.value) && $('error-email')) {
                $('error-email-format').className = 'error';
                return false;
            } else {
                $('error-email-format').className = 'error-hidden';
            }
        }
        return true;
    },
    sl: function () {
        var name = $('name');
        if (name) {
            if (name.value.length == 0 && $('error-name')) {
                $('error-name').className = 'error';
                return false;
            } else {
                $('error-name').className = 'error-hidden';
            }
        }
        return true;
    },
    tl: function () {
        var gh = $('message');
        if (gh) {
            if (gh.value.length == 0 && $('error-message')) {
                $('error-message').className = 'error';
                return false;
            } else {
                $('error-message').className = 'error-hidden';
            }
        }
        return true;
    },
    ar: function () {
        this.ul = WM_params.wroot;
        this.$k = new kk({
            dk: $("spl1"),
            ok: $("msgwndtd"),
            pk: $("chatwndtd"),
            xk: (20 + 10),
            vk: 036
        });
        this.zk = new sq.uh(un(({
            fh: myRealAgent == 'safari' ? self.frames[0] : $(vt),
            pj: $(uu),
            gh: $(at)
        }), WM_params || {}));
    }
};
er.lr({
    '#message-send': function (hl) {
        hl.onclick = function () {
            var gh = $(at);
            if (gh) {
                yk.zk.fi(gh.value);
            }
            return false;
        };
        if (myRealAgent == 'opera') {
            hl.innerHTML = hl.innerHTML.replace('Ctrl-', '');
        }
        hl.onblur = function () {
            yk.zk.oh = false;
        };
        hl.onfocus = function () {
            yk.zk.oh = true;
        };
    },
    '#visitor-name-lnk': function (hl) {
        if (WM_params.isvisitor) {
            hl.onclick = function () {
                yk.ml();
                return false;
            };
        }
    },
    '#visitor-name-btn': function (hl) {
        hl.onclick = function () {
            yk._k();
            return false;
        };
    },
    '#visitor-name-field': function (hl) {
        hl.onkeydown = function (e) {
            var or = e || event;
            if (or.keyCode == 015) {
                yk._k();
            }
        };
    },
    '#thread-refresh': function (hl) {
        hl.onclick = function () {
            yk.zk.$h();
            yk.zk.sh();
            return false;
        };
    },
    '#thread-close': function (hl) {
        hl.onclick = function () {
            if (yk.zk.uj()) {
                yk.zk.$h();
                yk.zk.rj();
            }
            return false;
        };
    },
    '#thread-rate-btn': function (hl) {
        hl.onclick = function () {
            yk._i();
            return false;
        };
    },
    'select#predefined': function (hl) {
        hl.onchange = function () {
            if (this.selectedIndex > 0) {
                var gh = $(at);
                gh.value = this.options[this.selectedIndex].innerText || this.options[this.selectedIndex].innerHTML;
                this.selectedIndex = 0;
                gh.focus();
            }
            return false;
        };
    },
    'a.closethread': function (hl) {
        hl.onclick = function () {
            yk.zk.rj();
            return false;
        };
    },
    'a#requestcontacts': function (hl) {
        hl.onclick = function () {
            yk.zk.fi("?@");
            return false;
        };
    },
    'a#pushurl': function (hl) {
        hl.onclick = function () {
            yk.zk.qj();
            return false;
        };
    },
    '#thread-send': function (hl) {
        hl.onclick = function () {
            this.il = window.open(document.location.href.replace(/visitorname=.*&/, 'visitorname='+$('visitor-name-lnk').get('html')+'&') + '&act=mailthread', 'ForwardMail', 'toolbar=0,scrollbars=0,location=0,statusbar=1,menubar=0,width=603,height=325,resizable=1');
            if (this.il != null) {
                this.il.focus();
                this.il.opener = window;
            }
            return false;
        }
    },
    '#leave-message-form': function (hl) {
        hl.onsubmit = function () {
            return yk.sl() && yk.rl() && yk.tl();
        }
    },
    '#message-sent-form': function (hl) {
        hl.onsubmit = function () {
            window.close();
            return false;
        }
    },
    '#thread-mail-form': function (hl) {
        hl.onsubmit = function () {
            return yk.rl();
        }
    },
    'a.window-close': function (hl) {
        hl.onclick = function () {
            yk.zk.rj();
            return false;
        }
    },
    '.window-close-simple': function (hl) {
        hl.onclick = function () {
            var req = new Request({
                url: '/webim/thread.php', 
                onSuccess: null,
                onFailure: null
            });
            req.post("act=close&thread=" + GET('thread') + '&token=' + GET('token') + '&lastid=' + GET('lastid') + '&visitor=true');
            window.close();
            window.location.href = '/';
            return false;
        }
    },
    'a#redirect': function (hl) {
        hl.onclick = function () {
            var jl = $('fader');
            var kl = $('popup');
            new sq.vq(yk.ul + '/operator/popup.php', {
                fq: 'action=operators&thread=' + WM_params.threadid + '&token=' + WM_params.token,
                onComplete: function (hq) {
                    if (jl && kl) {
                        var ri = hq.responseText;
                        kl.innerHTML = ri;
                        ll();
                    }
                }.ln(this)
            });
            return false;
        }
    },
    '#sound-control': function (hl) {
        hl.onclick = function () {
            var ol = !ft().it();
            yk.zk.qi(ol);
            ft().ut(ol);
            return false;
        };
    }
});
var ah = {
    pl: function (dl) {
        if (dl.selectionStart) {
            return dl.selectionStart;
        } else if (!document.selection) {
            return 0;
        }
        var el = document.selection.createRange();
        var fl = el.duplicate();
        fl.moveToElementText(dl);
        fl.setEndPoint('EndToEnd', el);
        return fl.text.length - el.text.length;
    },
    gl: function (al, bl) {
        if (al.setSelectionRange) {
            al.focus();
            al.setSelectionRange(bl, bl);
        } else if (al.createTextRange) {
            var cl = bl - (al.value.substring(0, bl).split('\n').length - 1);
            var el = al.createTextRange();
            el.collapse(true);
            el.moveEnd('character', cl);
            el.moveStart('character', cl);
            el.select();
        }
    },
    bh: function (vl) {
        var keyCode, wl;
        if (!$(at)) {
            return false;
        }
        if (vl) {
            wl = vl.ctrlKey;
            keyCode = vl.which;
        } else if (event) {
            wl = event.ctrlKey;
            keyCode = event.keyCode;
        } else {
            return false;
        }
        if (keyCode == 015 && wl) {
            var xl = this.pl($(at));
            var text = $(at).value;
            $(at).value = text.substring(0, xl) + "\n" + text.substring(xl);
            this.gl($(at), xl + 1);
        } else if (keyCode == (10 + 3) || keyCode == 012) {
            var gh = $(at).value;
            if (myRealAgent == 'opera') {
                gh = gh.replace(/[\r\n]+$/, '');
            }
            if ($('leave-message-form')) {
                if (yk.sl() && yk.rl() && yk.tl()) {
                    $('leave-message-form').submit();
                } else {
                    rq = false;
                }
            } else {
                yk.zk.fi(gh);
            }
            return false;
        }
        return true;
    }
};
kr.lr(window, 'onload', function () {
    if ($('chat-ajaxed')) {
        yk.ar();
    }
    if ($('message-sent-form') && $('message-sent-close')) {
        $('message-sent-close').focus();
    }
    if ($('thread-mail-form') && $('email')) {
        $('email').focus();
    }
    if (WM_params) {
        dt(yk.ul + '/sounds/new_message.wav', WM_params.isvisitor ? 'WEBIM_VISITOR_SOUND_ACTIVATION_STATE' : 'WEBIM_OPERATOR_SOUND_ACTIVATION_STATE');
        yk.zk.qi(ft().it());
    }
});
kr.lr(window, 'onunload', function () {
    if ($('chat-ajaxed') && typeof (yk.zk.tj) != "undefined") {
        yk.zk.tj();
    }
});
if (navigator.userAgent.indexOf("MSIE") == -1) {
    kr.lr(window, 'onfocus', function () {
        if (typeof (yk.zk.jk) != "undefined") {
            yk.zk.jk();
        }
    });
    kr.lr(window, 'onblur', function () {
        yk.zk.ph = false;
    });
} else {
    kr.lr(document, 'onfocusin', function () {
        if (typeof (yk.zk.jk) != "undefined") {
            yk.zk.jk();
        }
    });
    kr.lr(document, 'onfocusout', function () {
        yk.zk.ph = false;
    });
}
