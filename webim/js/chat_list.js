var pu = 'sound-control';
var du = 'sound-control-span';
var yl;
var zl;
sq.$l = mn.nn();
mn.sn(sq.$l, sq.lq, {
    qn: function (pq) {
        this.oq(pq);
        this.pq.onComplete = this._l.ln(this);
        this.pq.jr = this.ih.ln(this);
        this.pq.hr = this._q.ln(this);
        this.pq.timeout = 0x7530;
        this.frequency = (this.pq.frequency || 2);
        this.jh = {};
        this.sh();
        kr.lr(window, 'onunload', this.onunload.ln(this));
    },
    onunload: function () {
        this.pq.fq = "status=0";
        this.jh = new sq.vq(this.pq.url, this.pq);
    },
    ih: function (xh, yh) {
        if (this.pq.ii) {
            this.pq.ii("offline, reconnecting");
        }
        this.$h();
        this.rh = setTimeout(this.sh.ln(this), 01750);
    },
    _q: function (xh) {
        if (this.pq.ii) {
            this.pq.ii("timeout, reconnecting");
        }
        this.$h();
        this.rh = setTimeout(this.sh.ln(this), 01750);
    },
    $h: function () {
        if (this.jh.pq) {
            this.jh.pq.onComplete = undefined;
        }
        clearTimeout(this.rh);
    },
    sh: function () {
        if (this.pq.mo) {
            this.pq.fq = (this.pq.mo)();
        }
        this.jh = new sq.vq(this.pq.url, this.pq);
        no.qi(ft().it());
    },
    _l: function (qo) {
        try {
            var ui = sq.uq(qo);
            if (ui) {
                (this.pq.hi || sq.kq)(ui);
            } else {
                if (this.pq.ii) this.pq.ii("reconnecting");
            }
        } catch (e) {}
        this.rh = setTimeout(this.sh.ln(this), this.frequency * (655 + 345));
    }
});

var no = {
    ro: function (es) {
        var ws = es.insertCell(-1);
        ws.style.backgroundImage = 'url(' + yl + '/images/tablediv3.gif)';
        ws.innerHTML = '<img src="' + yl + '/images/free.gif" width="3" height="1" border="0" alt="">';
    },
    so: function (link, title, to, uo) {
        return '<a href="' + link + '" target="_blank" title="' + title + '">' + uo + '</a>';
    },
    ho: function (link, title, to, uo, width, height, io) {
        return '<a href="' + link + '"' + (io != null ? 'class="' + io + '"' : '') + 'target="_blank" title="' + title + '" onclick="this.newWindow = window.open(\'' + link + '\', \'' + to + '\', \'toolbar=0,scrollbars=0,location=0,menubar=0,width=' + width + ',height=' + height + ',resizable=1\');this.newWindow.focus();this.newWindow.opener=window;return false;">' + uo + '</a>';
    },
    jo: function (content) {
        return '<table><tr>' + content + '</tr></table>';
    },
    ko: function (lo, oo, id, po, eo, fo, gh, ao) {
        var bo = 2;
        var link = oo + (oo.indexOf("?") != -1 ? "&" : "?") + "thread=" + id;
        var co = '<td align="left" class="no-border">';
        co += no.ho(ao ? link : link + "&viewonly=true", eo ? WM_localized.joinChat : WM_localized.viewChat, "webim" + id, lo, 01270, 0x1c8, fo);
        co += '</td><td class="no-border"><img src="' + yl + '/images/free.gif" width="5" height="1" border="0" alt=""></td>';
        if (eo) {
            co += '<td width="30" align="center"class="no-border">';
            co += no.ho(link, WM_localized.joinChat, "webim" + id, '<img src="' + yl + '/images/tbliclspeak.gif" width="15" height="15" border="0" alt="' + WM_localized.joinChat + '">', (662 + 34), 0710, null);
            co += '</td>';
            bo++;
        }
        if (po) {
            co += '<td width="30" align="center" class="no-border">';
            co += no.ho(link + "&viewonly=true", WM_localized.viewChat, "webim" + id, '<img src="' + yl + '/images/tbliclread.gif" width="15" height="15" border="0" alt="' + WM_localized.viewChat + '">', (574 + 122), (432 + 24), null);
            co += '</td>';
            bo++;
        }
        if (gh != "") {
            co += '</tr><tr><td class="firsmessage" align="right" colspan="' + bo + '"><a href="javascript:void(0)" title="' + gh + '" onclick="return false;">';
            co += gh.length > (29 + 1) ? gh.substring(0, (18 + 12)) + '...' : gh;
            co += '</a></td>';
        }
        return no.jo(co);
    },
    vo: function (wo) {
        var url = yl + '/operator/ban.php?address=';
        return '<td align="center" class="no-border">' + no.so(url + wo, WM_localized.banVisitor, "ban" + wo, '<img src="' + yl + '/images/ban.gif" width="15" height="15" border="0" alt="' + WM_localized.banVisitor + '">') + '</td>';
    },
    xo: function (wo, yo, zo, $o, _o, mp) {
        var rq = wo + ' <a href="' + zl + wo + '" target="_blank"><img src="' + yl + '/images/whois.gif" width="16" height="16" border="0"/></a>';
        if (yo && yo != wo) {
            rq = rq + '<br />' + yo;
        }
        if (zo && $o) {
            rq = rq + '<br />' + '<a href="http://maps.google.com/maps?q=' + _o + ', ' + mp + '" target="_blank">' + zo + ', ' + $o + '</a>';
        }
        return rq;
    },
    np: function (url, title) {
        if (!url || !title) return '';
        return '<a href="' + url + '" title="' + url + '" target="_blank">' + title + '</a>';
    },
    qi: function (uk) {
        $(du).className = uk ? "action-sound-off" : "action-sound-on";
        $(pu).title = uk ? WM_localized.soundOff : WM_localized.soundOn;
    }
};

sq.qp = mn.nn();

mn.sn(sq.qp, sq.lq, {
    qn: function (pq) {
        this.oq(pq);
        this.pq.mo = this.mo.ln(this);
        this.pq.ii = this.ii.ln(this);
        this.pq.hi = this.hi.ln(this);
        this.pq.rp = 0;
        this.sp = new Object();
        this.tp = 0;
        this.t = this.pq.up;
        this.hp = new sq.$l(this.pq);
    },
    mo: function () {
        return "company=" + this.pq.company + "&since=" + this.pq.rp + "&status=1";
    },
    ip: function (jp) {
        this.pq.status.innerHTML = jp;
    },
    ii: function (s) {
        this.ip(s);
    },
    kp: function (nk) {
        var id, lp, op, po = false,
            eo = false,
            fo = null;

        // maximum 10 threads
        var thrCount = this.t.querySelectorAll('tr[id^="thr"]').length;
        if (thrCount >= 10) {
            return;
        }

        for (var i = 0; i < nk.attributes.length; i++) {
            var pp = nk.attributes[i];
            if (pp.nodeName == "id") {
                id = pp.nodeValue;
            } else if (pp.nodeName == "stateid") {
                lp = pp.nodeValue;
            } else if (pp.nodeName == "state") {
                op = pp.nodeValue;
            } else if (pp.nodeName == "canopen") {
                eo = true;
            } else if (pp.nodeName == "canview") {
                po = true;
            } else if (pp.nodeName == "ban") {
                fo = pp.nodeValue;
            }
        }
        function dp(ds, ep, id, fp) {
            var ws = ls.fs(id, ep, ds);
            if (ws) {
                ws.innerHTML = fp;
            }
        }
        var ep = ls.os("thr" + id, this.t);
        if (lp == "closed") {
            if (ep) {
                this.t.deleteRow(ep.rowIndex);
            }
            this.sp[id] = null;
            return;
        }
        var gp = qs.rs(nk, "name");
        var wo = qs.rs(nk, "addr");
        var ap = qs.rs(nk, "time");
        var bp = qs.rs(nk, "agent");
        var gh = qs.rs(nk, "message");
        var zo = qs.rs(nk, "city");
        var $o = qs.rs(nk, "country");
        var _o = qs.rs(nk, "lat");
        var mp = qs.rs(nk, "lng");
        var yo = qs.rs(nk, "host");
        var cp = qs.rs(nk, "current_page_url");
        var vp = qs.rs(nk, "current_page_title");
        var wp = qs.rs(nk, "locale");
        var xp = qs.rs(nk, "department");
        var yp = '<td class="no-border">' + qs.rs(nk, "other") + '</td>';
        if (fo != null) {
            yp = '<td class="no-border">' + qs.rs(nk, "reason") + '</td>';
        }
        yp += no.vo(wo);
        yp = no.jo(yp);
        var zp = ls.os(lp, this.t);
        var $p = ls.os(lp + "end", this.t);
        if (ep != null && (ep.rowIndex <= zp.rowIndex || ep.rowIndex >= $p.rowIndex)) {
            this.t.deleteRow(ep.rowIndex);
            this.sp[id] = null;
            ep = null;
        }
        if (ep == null) {
            ep = this.t.insertRow(zp.rowIndex + 1);
            ep.id = "thr" + id;
            ep.className = (id % 4 == 0) ? 'even' : 'odd';
            this.sp[id] = new Array(ap, lp);
            ls.insertCell(ep, "name", "table", null, (23 + 7), no.ko(gp, this.pq.agentservl, id, po, eo, fo, gh, lp != 'chat'));
            ls.insertCell(ep, "contid", "table", "center", null, no.xo(wo, yo, zo, $o, _o, mp));
            ls.insertCell(ep, "state", "table", "center", null, op);
            ls.insertCell(ep, "current_page", "table", "center", null, no.np(cp, vp));
            ls.insertCell(ep, "op", "table", "center", null, bp);
            ls.insertCell(ep, "locale", "table", "center", null, wp);
            ls.insertCell(ep, "department", "table", "center", null, xp);
            ls.insertCell(ep, "time", "table", "center", null, this._p(ap));
            ls.insertCell(ep, "wait", "table", "center", null, (lp != 'chat' ? this._p(ap) : '-'));
            ls.insertCell(ep, "etc", "table", "center", null, yp);
            if (lp == 'wait' || lp == 'prio' || lp == 'invite') {
                return true;
            }
        } else {
            this.sp[id] = new Array(ap, lp);
            dp(this.t, ep, "name", no.ko(gp, this.pq.agentservl, id, po, eo, fo, gh, lp != 'chat'));
            dp(this.t, ep, "contid", no.xo(wo, yo, zo, $o, _o, mp));
            dp(this.t, ep, "state", op);
            dp(this.t, ep, "current_page", no.np(cp, vp));
            dp(this.t, ep, "op", bp);
            dp(this.t, ep, "locale", wp);
            dp(this.t, ep, "department", xp);
            dp(this.t, ep, "time", this._p(ap));
            dp(this.t, ep, "wait", (lp != 'chat' ? this._p(ap) : '-'));
            dp(this.t, ep, "etc", yp);
        }
        return false;
    },
    md: function () {
        function nd(t, id, qd) {
            var zp = t.rows[id];
            var $p = t.rows[id + "end"];
            if (zp == null || $p == null) return;
            var rd = $p.cells["status"];
            if (rd == null) return;
            rd.innerHTML = (zp.rowIndex + 1 == $p.rowIndex) ? qd : "";
            rd.height = (zp.rowIndex + 1 == $p.rowIndex) ? (20 + 10) : 012;
        }
        nd(this.t, "wait", WM_localized.noClients);
        nd(this.t, "prio", WM_localized.noClients);
        nd(this.t, "chat", WM_localized.noClients);
        nd(this.t, "invite", WM_localized.noClients);
    },
    _p: function (sd) {
        var td = Math.floor(((new Date()).getTime() - sd - this.tp) / 01750);
        var ud = Math.floor(td / 074);
        var hd = "";
        td = td % (51 + 9);
        if (td < 012) td = "0" + td;
        if (ud >= (40 + 20)) {
            var jd = Math.floor(ud / (44 + 16));
            ud = ud % (44 + 16);
            if (ud < 012) ud = "0" + ud;
            hd = jd + ":";
        }
        return hd + ud + ":" + td;
    },
    kd: function () {
        for (var i in this.sp) {
            if (this.sp[i] != null) {
                var value = this.sp[i];
                var ep = ls.os("thr" + i, this.t);
                if (ep != null) {
                    function dp(ds, ep, id, fp) {
                        var ws = ls.fs(id, ep, ds);
                        if (ws) ws.innerHTML = fp;
                    }
                    dp(this.t, ep, "time", this._p(value[0]));
                    dp(this.t, ep, "wait", (value[1] != 'chat' ? this._p(value[0]) : '-'));
                }
            }
        }
    },
    hi: function (ld) {
        var od = false;
        if (ld.tagName == 'threads') {
            var pd = qs.ks(ld, "time");
            var dd = qs.ks(ld, "revision");
            if (pd) {
                this.tp = (new Date()).getTime() - pd;
            }
            if (dd) {
                this.pq.rp = dd;
            }
            for (var i = 0; i < ld.childNodes.length; i++) {
                var nk = ld.childNodes[i];
                if (nk.tagName == 'thread') {
                    if (this.kp(nk)) {
                        od = true;
                    }
                }
            }
            this.md();
            this.kd();
            this.ip("Up to date");
            if (od) {
                gt();
                window.focus();
            }
        } else if (ld.tagName == 'error') {
            this.ip(qs.rs(ld, "descr"));
        } else {
            this.ip("reconnecting");
        }
    }
});
er.lr({
    '#sound-control': function (hl) {
        hl.onclick = function () {
            var ol = !ft().it();
            no.qi(ol);
            ft().ut(ol);
            return false;
        };
    }
});
kr.lr(window, 'onload', function () {
    yl = WM_params.wroot;
    zl = WM_params.whoisUrl;
    dt(yl + '/sounds/new_user.wav', 'WEBIM_OPERATOR_SOUND_ACTIVATION_STATE');
    no.qi(ft().it());
    new sq.qp(un(({
        up: $("threadlist"),
        status: $("connstatus")
    }), WM_params || {}));
});