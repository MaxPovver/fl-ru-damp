var _OPERA = navigator.userAgent.search(/Opera/i) >= 0;
var _SAFARI = navigator.userAgent.search(/Safari/i) >= 0;
function Sbr(form_id) {
    this.STAGE_CLS = 'fieldset.nr-task';
    this.STAGE_NUM_CLS = 'a.nr-task-anchor';
    this.ERROR_CSS = 'invalid';
    this.HTML_ERROR = function(msg,exmpl) {return '<div class="tip-in"><div class="tip-txt"><div class="tip-txt-in"><span class="middled"><strong>'+msg+'</strong>'+(exmpl!=null?'<em>'+exmpl+'</em>':'')+'</span></div></div></div>';}
    this.cost = this.COST;
    this.stages = [];
    this.itmcache = {};
    this.errorCount = 0;
    this.scheme_type = this.SCHEME_TYPE;
    if(form_id) {
        this.form_id = form_id;
        this.form = document.getElementById(form_id);
    }

    this.cancelEnter=function(evnt) {return !((window.event?window.event:evnt).keyCode==13);};

    this.addFormParam
    =function(name, value) {
        if(!this.form) return;
        var inp = document.createElement('INPUT');
        inp.type = 'hidden';
        inp.name = name;
        inp.value = value;
        this.form.appendChild(inp);
    };

    this.addStage
    =function() {
        var i,dbox,stgcnt = this.stages.length;
        this.stages[stgcnt]=this.stages[stgcnt-1].clone();
        this.stages[stgcnt].bx_category.options[0].selected = true;
        for(i=0,++stgcnt;i<stgcnt;i++) {
            if(this.stages[i]) {
                if((dbox=document.getElementById('delstage_box'+i)))
                    dbox.style.display='inline';
            }
        }

        go_anchor('stage'+stgcnt);
    };
    this.delStage
    =function(itm,sid,err) {
        var l,dbox,stg,noserver=(!sid);
        if(sid) {
            this.addFormParam('delstages['+sid+']', sid);
        }
        stg=typeof(itm)=='object'?this.getStageByItem(itm):this.getStage(itm);
        if(!stg) return;
        stg.changeCost(0.00);
        if(stg.num>=(l=this.stages.length)-1)
            this.stages.pop();
        else
            delete this.stages[stg.num];
        stg.node.parentNode.removeChild(stg.node);
        stg = null;
        for(var nbox,tstg,lstg,j=0,i=0;i<l;i++) {
            if(tstg=this.stages[i]) {
                lstg=tstg;
                j++;
                if(nbox=lstg.node.getElement(this.STAGE_NUM_CLS)) {
                    lstg.outnum=j;
                    nbox.innerHTML=j;
                    nbox.name='stage'+j;
                    if(lstg.outnum == 1) {
                        this.bx_cost_sys = lstg.bx_cost_sys;
                        lstg.bx_cost_sys.disabled=false;
                    }
                }
            }
        }

        if(j==1 && lstg) {
            if((dbox=document.getElementById('delstage_box'+lstg.num)))
                dbox.style.display='none';
        }
        // !!! в случае iframe аттачей, удалить их.
    };

    this.getStage
    =function(num) {
        return this.stages[num];
    };

    this.getStageByItem
    =function(itm) {
        var stgnumbx,stgnode=$(itm).getParent(this.STAGE_CLS);
        if(stgnode && (stgnumbx=stgnode.getElement(this.STAGE_NUM_CLS))) {
            //alert(stgnode.outerHTML);
            return this.getStage(stgnumbx.getAttribute('innum'));
        }
    };

    this.addFrl
    =function(html, ftype, rtype, err) {
        var login,itm=this.form['frl_login'],noserver=(html || err);
        if(!noserver) {
            login=itm.value.trim(); // !!! trim сделать.
            // если логин не поменялся - выходим
            if (login == this.oldLogin) {
                return false;
            }
            if(login=='' || login=='логин') {
                err = 'Введите логин исполнителя';
                itm.value = '';
                noserver=true;
            }
            else {
                xajax_addFrl(login);
            }
        }

        if(noserver) {
            if(!err) {
                login=itm.value.trim();
                this.oldLogin  = login;
                document.getElementById('frlbx').innerHTML = html;
                if(this.DYN_SEND && this.form.send)
                    this.form.send.value = (login != this.FRL_LOGIN) ? 'Отправить на утверждение исполнителю' : 'Сохранить';
                this.FRL_LOGIN=login;
                this.RT_FRL = rtype;
                this.FT_FRL = ftype;
                this.changeFrlRezType(this.RT_FRL);
                this.changeSchemeType(this.scheme_type);
            }
            this.adErrCls(itm, err);
        }
    };


    this.adErrCls
    =function(itm, msg) {
        if(!itm) return;
        if(msg && typeof(msg) == 'object') {
            var exmpl = msg[1];
            msg = msg[0];
        }
        var eitm = this.getItemError(itm);
        if(!msg && !eitm.msg || msg==eitm.msg) return;
        if(eitm.msg)
            itm.className = itm.className.replace(new RegExp(this.ERROR_CSS+'\\s*$'), '');
        if(msg) {
            if(eitm.box) {
                eitm.box.innerHTML = this.HTML_ERROR(msg,exmpl);
                eitm.box.style.display = 'block';
                if(!this.errorCount) {
                    //var a = document.createElement('A');
                    //a.name = 'error';
                    //a=itm.parentNode.insertBefore(a,itm);
                    //itm.parentNode.parentNode.scrollIntoView(true);
                    JSScroll(itm.parentNode.parentNode);
                }
            }
            if(!itm.onfocus) itm.onfocus = function() {try {return SBR.adErrCls(itm);} catch(e) {}};
            itm.className += ' '+this.ERROR_CSS;
            eitm.msg = msg;
            this.errorCount++;
        } else {
            if(eitm.box) {
                eitm.box.innerHTML = '';
                eitm.box.style.display = '';
            }
            eitm.msg = null;
            this.errorCount--;
        }
    };


    this.setItemCache
    =function(itm) {
        if(!this.itmcache[itm.name])
            this.itmcache[itm.name] = {};
    };

    this.getItemError
    =function(itm) {
        this.setItemCache(itm);
        if(!this.itmcache[itm.name].error) {
            var error = {box:null,msg:null};
            var ebx=itm.parentNode;
            while((ebx=ebx.nextSibling)!=null) {
                if(ebx.nodeType==1) break;
            }
            if(ebx && ebx.tagName.toLowerCase()=='div' && ebx.className.match(/^\s*tip(\s|$)/)!=null) {
                error.box = ebx;
                if(ebx.innerHTML.match(/>\s*([\wа-я][^<]+)\s*</i)!=null)
                    error.msg = RegExp.$1;
            }
            this.itmcache[itm.name].error = error;
        }
        return this.itmcache[itm.name].error;
    };

    this.changeSys
    =function() {
        for(var i=0;i<this.stages.length;i++) {
            if(this.stages[i])
               this.stages[i].changeSys();
        }
        this.setTaxes();
    };

    this.changeSchemeType
    =function(val) {
        this.scheme_type=val;
        for(var i=0;i<this.stages.length;i++) {
            if(this.stages[i])
               this.stages[i].changeCost(this.stages[i].cost);
        }
        this.adErrCls(this.form['scheme_type_err']);
        this.openCloseCalc();
    };


    this.openCloseCalc
    =function(dont_settax) {
        if(this.scheme_type==null) return;
        if(this.bx_ascheme)
            this.bx_ascheme.style.display='none';
        this.bx_ascheme=document.getElementById('sch_'+this.scheme_type);
        this.bx_ascheme.style.display='block';
        if(this.bx_schalert) this.bx_schalert.style.display='none';
        if(this.bx_schalert = document.getElementById('schalert'+this.scheme_type))
            this.bx_schalert.style.display='block';
        if(!dont_settax)
            this.setTaxes();
    };

    this.setTaxes
    =function() {
        if(this.scheme_type==null) return;
        if(!this.bx_ascheme) return;
        if(this.SCHEMES[this.scheme_type]) {
            var tbx,tx,k,ssys=re_sys(this.bx_cost_sys.options[this.bx_cost_sys.selectedIndex].innerHTML);

            for(var i=0,rr=this.bx_ascheme.rows,len=rr.length;i<len;i++) {
                if(rr[i].id.search(/^taxrow_/i)>=0)
                    rr[i].style.display='none';
            }

            for(k in this.SCHEMES[this.scheme_type]) {
                if(trow=document.getElementById('taxrow_'+this.scheme_type+'_'+k)) {
                    tx=0;
                    for(i=0;i<this.STAGES_COSTS.length;i++) {
                        tx += mny(this.STAGES_COSTS[i]*this.SCHEMES[this.scheme_type][k][0]);
                    }
                    trow.style.display = '';
                    if(tbx=document.getElementById('taxsum_'+this.scheme_type+'_'+k))
                        tbx.innerHTML = fmt(mny(tx))+'&nbsp;'+ssys;
                    if(tbx=document.getElementById('taxper_'+this.scheme_type+'_'+k))
                        tbx.innerHTML = mny(this.SCHEMES[this.scheme_type][k][1]*100);
                }
            }
            document.getElementById('sch_'+this.scheme_type+'_f').innerHTML = fmt(mny(this.cost))+'&nbsp;'+ssys;
        }
    };

    this.onfrlblur
    =function(itm) {
        if(itm.value=='')
            this.adErrCls(itm);  // !!! на keydown нужно убирать сразу... или нет.
    };

    this.onfrlfocus
    =function(itm) {
        if(itm.value=='логин')itm.value=''
        if(this.getItemError(itm).msg)
            itm.select();
    }

    this.switchReqvRT
    =function(vrt) {
        var vb,hb,vr,hr,hrt=vrt==1?2:1;
        if(vb=$$('.rez--itm'+vrt)) vb.each( function(e) { e.style.display=vsty(e.tagName); } );
        if(hb=$$('.rez--itm'+hrt)) hb.each( function(e) { e.style.display='none'; } );
        if(vr=$$('.rez--req'+hrt)) vr.removeClass('form-imp');
        if(hr=$$('.rez--req'+vrt)) hr.addClass('form-imp');
    };

    this.switchReqvFT
    =function(etype,dtype) {
        var elnk;
        if(elnk=document.getElementById('ftlink'+etype)) {
            if(SBR.form.form_type)
                SBR.form.form_type.value=etype;
            elnk.style.fontWeight='bold';
            document.getElementById('ftlink'+dtype).style.fontWeight='normal';
        }
        var dsb = document.getElementById('ft'+dtype+'_set');
        var enb = document.getElementById('ft'+etype+'_set');
        if(dsb)dsb.style.display='none';
        if(enb)enb.style.display=vsty(enb.tagName);
        
    };


    this.emp_rtype=null;
    this.schemesCache={e:{},f:{}};
    this.changeEmpRezType
    =function(rt,resp) {
        if(this.emp_rtype==rt) return;
        if(resp==null) {
            if(this.schemesCache['e'][rt]==null) {
                xajax_changeEmpRezType(this.FRL_LOGIN,rt);
                return;
            }
            this.SCHEMES=this.schemesCache['e'][rt];
        } else {
            this.schemesCache['e'][rt]=this.SCHEMES;
        }
        this.emp_rtype=rt;
        this.changeSchemeType(this.scheme_type);
        if(rt != 2) {
            this.form.send.disabled=false;
            if(this.form.draft)
                this.form.draft.disabled=false;
            document.getElementById('norez_info').style.display='none';
            for(var i=0,so=this.bx_cost_sys.options,slen=so.length;i<slen;i++)
                so[i].disabled=false;
        }
        else {
            this.form.send.disabled=false;
            if(this.form.draft)
                this.form.draft.disabled=false;
            document.getElementById('norez_info').style.display='block';          
        }
    };

    this.changeFrlRezType
    =function(rt) {
        var mxcs=$$('.norez_maxcost_block');
        document.getElementById('unknown_frl_rez').style.display=(rt ? 'none' : 'block');
        if(rt != 2) {
            mxcs.setStyle('display', 'none');
        }
        else {
            mxcs.setStyle('display', 'block');
        }
    };


    // < currents

    this.rt_cache={};
    this.changeRezTypeFrl
    =function(sid, rt, html) {
        var server=(html!=null);
        if(!this.rt_cache[sid])
            this.rt_cache[sid]={};
        if(!server) {
            if(!(html=this.rt_cache[sid][rt])) {
                xajax_changeRezTypeFrl(sid, rt);
                return;
            }
        }
        $('ok_btn'+sid).removeClass('btnr-disabled');
        if(this.rt_cache[sid].ok_blocked)
            $('ok_btn'+sid).addClass('btnr-disabled');
        this.rt_cache[sid][rt]=html;
        this.rt_cache[sid].ok_blocked=(rt==2&&document.getElementById('ok_blocked_alert'+sid)!=null);
        document.getElementById('sch_info'+sid).innerHTML=html;
        document.getElementById('norez_info'+sid).style.display=(rt==1?'none':'block');
    };

    this.hideStatusBox
    =function(itm,ss) {
        var ssy,cc,d,ssbx = document.getElementById('ssbox'+ss);
        if(!ssbx)return;
        d=new Date();
        d.setMonth(d.getMonth()+3);
        if((ssy=ssbx.style).display=='none') {
            ssy.display='block';
            itm.innerHTML='Скрыть';
            cc=0;
        } else {
            ssy.display='none';
            itm.innerHTML='Показать';
            cc=1;
        }
        document.cookie='sbr_ss'+ss+'='+cc+'; expires='+d.toGMTString();
    };

    this.bx_arb_descr=null;
    this.getArbDescr
    =function(bxsfx,sid,html) {
        var abx,noserver=(html!=null),bxid='arb_descr_box'+bxsfx;
        if(this.bx_arb_descr && bxid != this.bx_arb_descr.id) {
            this.bx_arb_descr.style.display='none';
            this.bx_arb_descr.innerHTML='';
        }
        if(!noserver) {
            this.bx_arb_descr=document.getElementById(bxid);
            xajax_getArbDescr(sid);
            return;
        }
        if(this.bx_arb_descr) {
            this.bx_arb_descr.style.display='block';
            this.bx_arb_descr.innerHTML=html;
        }
    };

    this.submitLock
    =function(form,p,nolock) {
        if(!nolock&&form.submitting===1) return false;
        if(form.onsubmit && form.onsubmit()===false) return false;
        if(p!=null) {
            for(var k in p) {
                form[k].value = p[k];
            }
        }

        form.submitting=1;
        form.submit();
        return true;
    };


    // > currents

    //////////////////////////////////////////////

    if( ! this.form ) return;

    this.changeFormDir
    =function(col,dir) {
        this.form['dir'].value=dir;
        this.form['dir_col'].value=col;
        this.form.submit();
    };

    this.sendForm
    =function(p,nolock) {
        return this.submitLock(this.form, p, nolock);
    };

    if(this.form_id == 'createFrm') {
        // !!! перетаскать функции в блок.
        this.bx_calc = document.getElementById('nr-calc');

        var i,j,s,ss=$(document.body).getElements(this.STAGE_CLS);
        for(i=0;i<ss.length;i++) {
            if(!i) { }
            s = new SbrStage(ss[i],this);
            this.stages[s.num] = s;
        }
        this.openCloseCalc();
        if(this.errorCount > 0)
            go_anchor('error');
    };

    if(this.form_id == 'completeFrm') {
        this.changeRecSys
        =function(incode) {
            if(incode==null) {
                var ss=this.form['credit_sys'];
                if(ss) {
                    if(!ss.length)ss=[ss];
                    for(var i=0,len=ss.length;i<len;i++) {
                        if(ss[i].checked) {
                            incode=ss[i].value;
                            break;
                        }
                    }
                }
            }
            if(incode!=null) {
                if((incode == this.WM_SYS  || incode == this.YM_SYS)&& $('finance_require')) {
                    $('finance_require').setStyle('display', null);
                } else if($('finance_require')) {
                    $('finance_require').setStyle('display', 'none');
                }
                
                this.bx_credit_sum.innerHTML = 'На ваш счет будет зачислено '
                                             + fmt(this.EXCODES[incode][this.EXIDX]) + ' ' + this.EXCODES[incode][0]
                                             + (incode==this.RUR_SYS?'&nbsp;(Банковский перевод)':'');

                if(!this.bx_submit_btn.onclick)
                    this.bx_submit_btn.onclick=this.bx_submit_btn.oldclick;
            }
        };

        this.setNoNP
        =function(itm) {
            this.EXIDX=itm.checked+1
            this.changeRecSys();
        };
        
        this.onrateOver
        =function() {
            this.pbox.THIS.setStar(this.pbox.stars, this.rval);
        };
        this.onrateClick
        =function() {
            try {
                val = this.getParents('span').length-1;
                topsp = this.getParents('span.stars-vote-a');
                inp = topsp.getElement('input');
                
                inp.set('value', val);
                topsp.set('class', topsp.get('class').toString().replace(/vote-(\d+)/, 'vote-'+val));

                if(this.getParent('ul').getElement('div.tip-t2')) {
                   this.getParent('ul').getElement('div.tip-t2').setStyle('display', 'none');
                }

                return false;
            } catch(e) {
                //alert(e);
                return false;
            }
        };
        this.onrateOut
        =function() {
            this.THIS.setStar(this.stars, this.rbox.value);
        };

        this.setStar
        =function(stars,curr) {
            for(var s,cs,i=1;i<stars.length;i++) {
                s=stars[i];
                cs=s.className.replace(/ s-a$/,'');
                if(i>curr) {
                    if(cs!=s.className) s.className=cs;
                } else {
                    cs+=' s-a';
                    if(cs!=s.className) s.className=cs;
                }
            }
        };

        /// receive_sys
        if(this.bx_credit_sum = document.getElementById('credit_sum')) {
            this.bx_submit_btn = document.getElementById('submit_btn');
            var rclen,fval=null,fenbl=null,rcsys=this.form['credit_sys'];
            if(rcsys && (rclen=rcsys.length)) {
                for(var i=0;i<rclen;i++) {
                    if(!fenbl && !rcsys[i].disabled)
                        fenbl=rcsys[i];
                    if(rcsys[i].checked) {
                        this.changeRecSys(rcsys[i].value);
                        break;
                    }
                } 
                if(!(rcsys[i] && rcsys[i].checked)) {
                    if(fenbl) {
                        fenbl.checked=true;
                        fval=fenbl.value;
                    }
                    this.changeRecSys(fval);
                }
            }
        };

        /// feedback
        var rates = [];
        frm = $(this.form_id);
        rates = frm.getElements('input[type=hidden][name^=feedback],input[type=hidden][name^=sbr_feedback]');
        if(rates.length) {
            for(i = 0; i<rates.length; i++) {
                sp = rates[i].getNext('span');
                if(!sp) continue;
                st = sp.getElements('a').addEvent('click', this.onrateClick);
                sp.getElements('a').set('href', 'javascript:void(0)');
            }
        }
    }


    if(this.form_id == 'draftFrm') {
        this.sel_dft_any = 0;
        this.sel_dft_nordy = 0;
        this.selectDraft
        =function(itm,rdy) {
            var inc = itm.checked*2-1;
            this.sel_dft_any += inc;
            this.sel_dft_nordy += inc*(!rdy);
            this.form['send'].disabled = this.sel_dft_nordy || !this.sel_dft_any;
            this.form['delete'].disabled = !this.sel_dft_any;
        };
    }

    if(this.form_id == 'arbitragedFrm') {
        this.bx_fp=this.form['frl_percent'];
        this.bx_ep=this.form['emp_percent'];
        this.ap_cache={};
        this.setArbPercent
        =function(itm,sid,esum,fsum) {
            var aitm,apc,server=(itm==null);
            if(!server) {
                var fper,per=v2i(itm.value);
                var MX=10;
                if(per!=0&&per<MX)per=MX;
                if(per>100)per=100;
                if(per!=100&&per>100-MX)per=100-MX;
                if(itm==this.bx_fp) {
                    aitm=this.bx_ep;
                    fper=per;
                } else {
                    aitm=this.bx_fp;
                    fper=100-per;
                }
                itm.value=per;
                aitm.value=100-per;
                if(apc=this.ap_cache[fper])
                    this.setArbPercent(null,sid,apc[0],apc[1]);
                else 
                    xajax_setArbPercent(sid,fper/100);
                return;
            }
            this.ap_cache[this.bx_fp.value]=[esum,fsum];
            this.form['e_sum'].value=f2v(esum);
            this.form['f_sum'].value=f2v(fsum);
            this.adErrCls(this.bx_fp);
        };
        if(this.bx_fp.value!='')
            this.setArbPercent(this.bx_fp);

        this.changeRepReason
        =function(rsel, txtid, reasons, ppins) {
            var pp=rsel.options[rsel.selectedIndex].value;
            document.getElementById(txtid).value=reasons[pp] ? (ppins!=null ? pp+' ' : '') + reasons[pp].replace(/<br\/>/g, "\n") : '';
        };
        
        this.clearPercentSum
        =function() {
            $('emp_sum').set("value", "");
            $('frl_sum').set("value", "");
            $('emp_percent').set("value", "");
            $('frl_percent').set("value", "");
            $('emp_percent').setProperty('disabled', "");
            $('frl_percent').setProperty('disabled', "");
        }
        
        this.changeRepReasonNew
         =function(rsel, txtid, reasons, ppins) {
            var pp=rsel.options[rsel.selectedIndex].value;
            if(rsel.name == 'pp_init' && rsel.selectedIndex == 3) {
                if(this.form['pp_result'].selectedIndex <=0) {
                    this.clearPercentSum();
                }
            }
            if(rsel.name == 'pp_reason' && rsel.selectedIndex == 0) {
                $(txtid).hide();
            } else if(rsel.name == 'pp_reason') {
                $(txtid).show();
            }
            
            if(this.DEPEND_PAY[rsel.name]) {
                var dep_pay = this.DEPEND_PAY[rsel.name];
                var sel = rsel.selectedIndex;
                
                if(sel < 0) {
                    this.clearPercentSum();
                } else {
                    var k = dep_pay[sel];

                    if(k.emp == 100 && k.frl == 0) {
                        $('emp_percent').set('value', 100);
                        $('frl_percent').set('value', '');
                        //$('emp_percent').setProperty('disabled', true);
                        //$('frl_percent').setProperty('disabled', true);
                        this.setArbPercent($('emp_percent'), this.STAGE_ID);
                    } else if(k.emp == 0 && k.frl == 100) {
                        $('frl_percent').set('value', 100);
                        $('emp_percent').set('value', '');
                        //$('emp_percent').setProperty('disabled', true);
                        //$('frl_percent').setProperty('disabled', true);
                        this.setArbPercent($('emp_percent'), this.STAGE_ID);
                    } else {
                        this.clearPercentSum();
                    }
                }
            }
            
            
            if(this.DEPEND_SCHEME[rsel.name]) {
                var dep_scheme = this.DEPEND_SCHEME[rsel.name];
                if(pp != dep_scheme.select) {
                    this.form[dep_scheme.select_name[0]].selectedIndex = false;
                    for(var i=0; i < dep_scheme.select_name.length; i++) {
                        this.form[dep_scheme.select_name[i]].disabled = false;
                    }
                    
                    $(dep_scheme.textid[0]).set('value', '');
                    for(var i=0; i < dep_scheme.textid.length; i++) {
                        $(dep_scheme.textid[i]).setProperty('disabled', '');
                    }
                    
                    if(dep_scheme.depend != null) {
                        for(var i = 0; i < this.form[dep_scheme.select_name[0]].options.length; i++) {
                            var option = this.form[dep_scheme.select_name[0]].options[i];
                            option.style.display = 'block';
                            for(var dp in dep_scheme.depend) {
                                if(option.value == dp && pp == dep_scheme.depend[dp]) {
                                    option.style.display = 'none';
                                } 
                            }
                        }
                    }
                    
                } else {
                    for(var i=1; i < dep_scheme.select_name.length; i++) {
                        this.form[dep_scheme.select_name[i]].disabled = false;
                    }
                    for(var i=1; i < dep_scheme.textid.length; i++) {
                        $(dep_scheme.textid[i]).setProperty('disabled', '');
                    }

                    this.form[dep_scheme.select_name[0]].selectedIndex = null;
                    this.form[dep_scheme.select_name[0]].disabled = true;
                    $(dep_scheme.textid[0]).set('value', '').setProperty('disabled', 'true');
                }
            }
             
            document.getElementById(txtid).value=reasons[pp] ? (ppins!=null ? pp+' ' : '') + reasons[pp].replace(/<br\/>/g, "\n") : '';
        };
    }

    if(this.form_id == 'arbitrageFrm') {
        var mA = new mAttach2(document.getElementById('arb_files_list').firstChild, 10, {p:'sbr-btn-add', m:'i-btn sbr-btn-del', f:'.cl-form-files', nv:true});
        var fsSld = new Fx.Slide(document.getElementById('arb_files_box'), {duration: 200}); 
        if(!this.ERRORS['err_attach']) {
            fsSld.hide(); 
        } else {
            if(this.form['err_attach'] && mA.objs[0]) {
                var flch = mA.objs[0].childNodes;
                var THIS=this;
                for(var i=0;i<flch.length;i++) {
                    if(flch[i].nodeType==1) $(flch[i]).addEvent('click', function() {THIS.adErrCls(THIS.form['err_attach']);});
                }
            }
        }
        document.getElementById('arb_fs_toggler').onclick = function() {fsSld.toggle()};
    }


    if(this.form_id == 'docsEditFrm' || this.form_id == 'docsAddFrm') {
        this.bx_doc_edit = document.getElementById('doc_edit_box');
        this.bx_docsTbl = document.getElementById('docsTbl');
        var etr=document.getElementById('edit_tr');
        if(etr) this.edit_tr=etr;

        this.initDocForm
        =function(itm, sid, id, form) {
            var noserver=(form!=null);
            if(!noserver) {
                var etr=itm.parentNode.parentNode;
                if(this.edit_tr==etr) {
                    this.edit_tr.className = this.edit_tr.className.replace(/\s*tr-edit/,'');
                    this.bx_doc_edit.innerHTML='';
                    delete this.edit_tr;
                } else {
                    this.edit_tr_old = this.edit_tr;
                    this.edit_tr = etr;
                    xajax_getDocForm(sid,id);
                }
                return;
            }
            if(this.edit_tr_old) {
                this.edit_tr_old.className = this.edit_tr_old.className.replace(/\s*tr-edit/,'');
                delete this.edit_tr_old;
            }
            this.edit_tr.className += ' tr-edit';
            this.bx_doc_edit.innerHTML=form;
        };

        this.delDoc
        =function(itm, sid, id) {
            var noserver=(itm==null),rlen,rows;
            if(!noserver) {
                if(window.confirm('Вы действительно хотите удалить документ?')) {
                    this.del_tr=itm.parentNode.parentNode;
                    if(this.edit_tr==this.del_tr) {
                        delete this.edit_tr;
                        this.bx_doc_edit.innerHTML='';
                    }
                    xajax_delDoc(sid,id);
                }
            }
            else if(this.del_tr) {
                var numc,i=this.del_tr.rowIndex;
                this.del_tr.parentNode.removeChild(this.del_tr);
                if((rlen=(rows=this.bx_docsTbl.rows).length) != 0) {
                    try {
                        for(i;i<rlen;i++) {
                            numc=rows[i].cells[1];
                            numc.innerHTML=(numc.innerHTML.replace(/\D/g,'')-1)+'.';
                        }
                        rows[0].className += ' first';
                        rows[rlen-1].className += ' last';
                    } catch(e) {alert(e)}
                }
            }
        };


    }

    if(this.form_id == 'reserveFrm') {
        this.switchReqvMode
        =function(sid,mode,html,err) {
            var noserver=(html!=null);
            if(!noserver) {
                xajax_getInvoiceForm(sid,mode);
                return;
            }
            if(!err) {
                this.form.parentNode.innerHTML = html;
                this.form = document.getElementById('reserveFrm');
            }
        };
    }



    // periods
    this.fillDays
    =function(pfx, name_elm) {
        if(name_elm == undefined) name_elm = 'filter';
        var y=this.form[name_elm + '['+pfx+'][year]'].value;
        var m=this.form[name_elm + '['+pfx+'][month]'].value;
        var ds=this.form[name_elm + '['+pfx+'][day]'];
        var d=ds.value;
        ds.innerHTML='';
        var i,opt,mcnt,cd=new Date();
        if(y==0)
            y=cd.getFullYear();
        if(m==0)
            m=cd.getMonth()+1;
        mcnt=dym(y)[m-1];
        for(i=0;i<=mcnt;i++) {
            opt=document.createElement('OPTION');
            opt.value=i;
            if(!i)
                opt.innerHTML=(pfx=='to'?'+':'-')+'&infin;';
            else {
                if(i==d) opt.selected=true;
                opt.innerHTML=('0'+i).replace(/^0*(..)$/, '$1');
            }
            ds.appendChild(opt);
        }
    };

/*
    if(this.form_id == 'financeFrm') {
        this._rdStatus = null;
        this.rezDocCloseWin // deprecated
        =function() {
            this._rdStatus = null;
            document.getElementById('ov-rez').style.display='none';
            document.getElementById('rezdoc_comment').innerHTML='';
        };
        this.rezDocOpenWin  // deprecated
        =function(status) {
            this._rdStatus=status;
            document.getElementById('ov-rez').style.display='block';
        };
        this.rezDocChange   // deprecated
        =function(html) { 
            var server=(html!=null);
            if(!server) {
                xajax_rezDocChange(this.LOGIN, document.getElementById('rezdoc_comment').innerHTML, this._rdStatus);
                return;
            }
            this.rezDocCloseWin();
            document.getElementById('rezdoc_box').innerHTML=html;
        };
    }
*/

    if(this.form_id == 'siteadminFrm') {
        this.delDoc
        =function(itm, sid, id, anc, html) {
            var noserver=(itm==null);
            if(!noserver) {
                if(window.confirm('Вы действительно хотите удалить документ?')) {
                    if(!this.lst_td) this.lst_td={};
                    this.lst_td[id]=itm.parentNode;
                    xajax_delDoc(sid,id,anc);
                }
            }
            else if(this.lst_td && this.lst_td[id] && html) {
                this.lst_td[id].innerHTML=html;
                delete this.lst_td[id];
            }
        };

        this.closeDocLoader
        =function() {
            if(this.last_dlbox) {
                this.last_dlbox.lnkbox.style.display='inline';
                this.last_dlbox.parentNode.removeChild(this.last_dlbox);
                this.last_dlbox = null;
            }
        };

        this.openDocLoader
        =function(itm,sid,anc,t,s,a) {
            this.form.setAttribute('action', '.#'+anc);
            this.form.method = 'post';
            this.form.setAttribute('enctype', 'multipart/form-data');
            this.closeDocLoader();
            this.last_dlbox = document.createElement('SPAN');
            this.last_dlbox.lnkbox = itm.parentNode;
            this.last_dlbox.innerHTML = 
            '<input type="hidden" name="stage_id" value="'+sid+'"/>\
             <input type="hidden" name="anchor" value="'+anc+'"/>\
             <input type="hidden" name="u_token_key" value="'+U_TOKEN_KEY+'"/>\
             <input type="hidden" name="type" value="'+t+'"/>\
             <input type="hidden" name="status" value="'+s+'"/>\
             <input type="hidden" name="access_role" value="'+a+'"/>\
             <label style="float:left;width:145px;display:block;overflow:hidden;margin-right:2px"><input style="font-size:8px;position:relative;top:1px" type="file" name="attach" /></label>\
             <input type="submit" name="add_doc" value="ok" title="Загрузить" onclick="if(!this.form.attach.value) return false"/>\
             &nbsp;<a href="javascript:;" title="Отменить" onclick="SBR.closeDocLoader()"><img src="/images/ico-close.png" alt="Отменить" /></a>\
             ';
            this.last_dlbox.lnkbox.parentNode.appendChild(this.last_dlbox);
            this.last_dlbox.lnkbox.style.display='none';
        };
        this.setRecvDocs
        =function(itm, key, mode, del) {
            var subx,html,noserver=(itm==null);
            if(!noserver) {
                if(mode==0 && !window.confirm('Сделка уже находится в выплатах, вы действительно хотите это отменить?'))
                    return;
                if(!this.lst_rdtr) this.lst_rdtr={};
                this.lst_rdtr[key]=itm.parentNode;
                xajax_setDocsReceived(key,mode);
            }
            else if(this.lst_rdtr && this.lst_rdtr[key]) {
                if(!del || !this.SCHEME) {
                    if(mode==0)
                        html='Документы ожидаются <input type="button" value="Пришли" onclick="SBR.setRecvDocs(this, \''+key+'\', 1)" />';
                    else
                        html='Документы получены&nbsp; <a href="javascript:;" class="lnk-dot-666" onclick="SBR.setRecvDocs(this, \''+key+'\', 0)">Отменить</a>';
                    this.lst_rdtr[key].innerHTML=html;
                } else {
                    subx=document.getElementById('subx_'+key);
                    subx.parentNode.removeChild(subx);
                }

                delete this.lst_rdtr[key];
            }
        };

        this.printFinWin
        =function(fwid) {
            var fw=document.getElementById(fwid);
            var w=window.open('', '_blank', "height=600,width=800,left=200,top=10,toolbar=yes,scrollbars=yes,status=yes,menubar=yes,location=no,titlebar=no,resizable=yes");
            w.document.open();
            w.document.write('<style>* {font-size:14px;font-family:arial} body {margin: 2mm 2mm 2mm 2mm} td,th {text-align:left; vertical-align:baseline; border-bottom:1px solid #d7d7d7; padding:2mm} table {width:100%;table-layout:fixed; border-collapse:collapse} th {width:300px}</style>'
                             +fw.innerHTML)
            w.document.close();
            w.print();
        };

        this._notNpItm=null;
        this.setNotNp
        =function(itm,uid,sid,np) {
            var server=(itm==null);
            if(!server) {
                if(this._notNpItm) return;
                this._notNpItm=itm;
                xajax_setNotNp(uid,sid,np);
                return;
            }
            var npbx=this._notNpItm.parentNode.parentNode.parentNode;
            npbx.parentNode.removeChild(npbx);
            this._notNpItm=null;
        };
    }

    this.checkedElms = 0;
    this.incChecked
    =function(nch,och) {
        this.checkedElms+=(nch-och);
        if(this.checkChecked)
            this.checkChecked();
    };

    this.setAllChecked
    =function(itm, nm) {
        var len,ecoll=this.form[nm];
        if(!ecoll) return;
        if(!(len=ecoll.length)) {
            ecoll=[ecoll];
            len=1;
        };
        for(var i=0;i<len;i++) {
            this.incChecked(itm.checked, ecoll[i].checked);
            ecoll[i].checked=itm.checked;
        };
    }

    
    this.setRemoved = function(id, resp) {
        if(!id) return false;

        el = $('subx_'+id);
        msg = 'Уверены?';
        if(el)  {
            msg = el.getElement('h4>img').get('src').contains('flt-on') ? 'Восстановить?' : 'Удалить?';
        }
        if(!resp && !confirm(msg)) return false;

        if(!resp) {
            xajax_setRemoved(id);
            return false;
        }

        if(resp && el) {
            if(this.SCHEME) {
                el.dispose();
            } else {
                btn = el.getElement('h4>img');
                if(btn) {
                    btn.set('src', '/images/flt-' + (resp.is_removed == 't' ? 'on' : 'close') + '.png');
                }
            }
        }
        
        return false;
    };


    for(n in this.ERRORS) {
        this.adErrCls(this.form[n], this.ERRORS[n]);
    }

};

function SbrStage(node, _SBR) {
    this.node=node;
    this.cost=0.00;
    this.cost_total=0.00;
    this.Sbr=_SBR;
    this.mA=null;

    this.clear
    =function() {
        var bx;
        if(this.outnum > 1) {
            this.bx_cost_sys.selectedIndex = this.Sbr.bx_cost_sys.selectedIndex;
            this.bx_cost_sys.disabled=true;
        }
        this.changeCost(0.00);
        this.changeCat(0);
        (bx=this.Sbr.form['stages['+this.num+'][name]']).value='';
        this.Sbr.adErrCls(bx);
        (bx=this.Sbr.form['stages['+this.num+'][descr]']).value='';
        this.Sbr.adErrCls(bx);
        (bx=this.Sbr.form['stages['+this.num+'][work_time]']).value='';
        this.Sbr.adErrCls(bx);
        this.Sbr.adErrCls(this.bx_cost);
        if(this.bx_id) { 
            this.bx_id.value='';
            this.id=null;
        }
        this.initDSButt(this.id);
        this.bx_falist.innerHTML = '';
        if(this.mA==null || this.mA.count>1)
            this.initAttach();

        return this;
    };

    this.clone
    =function() {
        var nanch,stn,re,innum=this.num+1,outnum,nstg=this.node.cloneNode(true);
        nstg=this.node.parentNode.insertBefore(nstg, this.node.nextSibling);
        re=new RegExp( '(name\\s*=\\s*"?(?:stages|cost_sys)\\[|id\\s*=\\s*"?delstage_box)'+this.num, 'ig' );
        nstg.innerHTML=nstg.innerHTML.replace(re, '$1'+innum);
        nanch=$(nstg).getElement(this.Sbr.STAGE_NUM_CLS);
        outnum=nanch.innerHTML-0+1;
        nanch.setAttribute('innum',innum);
        nanch.innerHTML=outnum;
        nanch.name='stage'+outnum;

        // выезжалку лечим. !!! под вопросом.
        var cntnode = $(nstg).getElement('.flt-cnt');
        var cntclone = cntnode.cloneNode(true);
        var cntparent = cntnode.parentNode.parentNode;
        cntclone.removeAttribute('style',0);
        cntparent.removeChild(cntnode.parentNode);
        cntparent.appendChild(cntclone);
        $(cntparent).getElement('.flt-tgl-lnk').set('text','Прикрепленные файлы (развернуть)');
        cntparent.className='flt-hide';

        return new SbrStage(nstg, this.Sbr).clear();
    };

    this.genSubCat
    =function(ccid, scid) {
        var h='<select name="stages['+this.num+'][sub_category]"><option value="0">&lt;Выберите подраздел&gt;</option>';
        if(this.Sbr.CATEGORIES[ccid]!=null) {
            for(var k in this.Sbr.CATEGORIES[ccid]) {
                for(var n in this.Sbr.CATEGORIES[ccid][k]) {
                    h +='<option value="'+n+'"'+(n==scid ? ' selected="true"' : '')+'>'+this.Sbr.CATEGORIES[ccid][k][n]+'</option>';
                }
            }
        }
        return h+'</select>';
    };

    this.changeCat
    =function(ccid) {
        var subc=this.Sbr.form['stages['+this.num+'][sub_category]'];
        subc.parentNode.innerHTML = this.genSubCat(ccid, subc.id);
        this.Sbr.form['stages['+this.num+'][sub_category]'].disabled = (this.Sbr.CATEGORIES[ccid]==null);
    };

    this.setCost
    =function(cost, cost_total) {
        var tax=this.Sbr.SCHEMES[this.Sbr.scheme_type]['t'][0];
        if(cost==null)
            cost = v2f(cost_total/tax);
        if(cost_total==null) {
            if(v2f(this.cost_total/tax)!=cost)
                cost_total = v2f(cost*tax);
            else
                cost_total = this.cost_total;   // не меняем рубли, если обратная конвертация предыдущей суммы в рублях в округлении соответствует введенной сумме бюджета.
        }
        this.Sbr.cost += (cost - this.cost);
        this.cost = cost;
        this.Sbr.STAGES_COSTS[this.num] = this.cost;
        this.cost_total = cost_total;
        this.Sbr.setTaxes();
    };

    this.changeCost
    =function(val) {
        val = v2f(val==null ? this.bx_cost.value : val);
        this.setCost(val, null);
        this.bx_cost_total.value = f2v(this.cost_total);
        this.bx_cost.value = f2v(this.cost);
    };

    this.changeCostTotal
    =function(val) {
        val = v2f(val==null ? this.bx_cost_total.value : val);
        this.setCost(null, val);
        this.bx_cost_total.value = f2v(this.cost_total);
        this.bx_cost.value = f2v(this.cost);
    };

    this.changeSys
    =function() {
        var radd,sysidx = this.Sbr.bx_cost_sys.selectedIndex;
        this.bx_cost_sys.selectedIndex = sysidx;
        this.bx_cost_total.nextSibling.nextSibling.innerHTML = re_sys(this.bx_cost_sys.options[sysidx].innerHTML);
        if((radd=this.bx_cost_sys.nextSibling)&&radd.nodeType==1)
            radd.innerHTML = this.bx_cost_sys.options[sysidx].value==this.Sbr.RUR_SYS?'&nbsp;Банковский перевод':'';
    };

    this.showHideAttDescr
    =function(show) {
        if(!this.bx_flist) return;
        if(!this.bx_attdescr) {
            var n=this.bx_flist;
            while(n=n.nextSibling) {
                if(n.nodeType==1&&n.className=='form-files-inf') {
                    this.bx_attdescr=n;
                    break;
                }
            }
        }
        if(this.bx_attdescr) {
            this.bx_attdescr.style.display=(show ? '':'none');
        }
    };

    this.initAttach
    =function() {
        var fcnt = (this.Sbr.STAGE_FILES_COUNT[this.num] ? this.Sbr.STAGE_FILES_COUNT[this.num] : 0)
        this.max_file_count = this.Sbr.MAX_FILES - fcnt;
        this.bx_flist.innerHTML = this.HTML_FILE_ITEM();
        this.mA = new mAttach2(this.bx_flist.firstChild, this.max_file_count, {p:'sbr-btn-add', m:'i-btn sbr-btn-del', f:'.flt-cnt', nv:true});
        this.showHideAttDescr(this.max_file_count>0);
        this.bx_err_attach = this.Sbr.form['stages['+this.num+'][err_attach]'];
        if(this.bx_err_attach && this.mA.objs[0]) {
            var flch = this.mA.objs[0].childNodes;
            var THIS=this;
            for(var i=0;i<flch.length;i++) {
                if(flch[i].nodeType==1) {
                    $(flch[i]).addEvent('click', function() {THIS.Sbr.adErrCls(THIS.bx_err_attach);});
                }
            }
        }
    };

    this.delAttach
    =function(itm,id) {
        if(!id) return;
        var abox=itm.parentNode;
        this.Sbr.addFormParam('stages['+this.num+'][del_attach]['+id+']', 't');
        abox.parentNode.removeChild(abox);
        this.max_file_count++;
        this.showHideAttDescr(this.max_file_count>0);
        this.mA.incMax();
    };

    this.setWTime
    =function(add,dir,noabx) {
        var y,m,d,sy,cy,t,nv,nD,dmth,sD=this.Sbr.STAGE_DEADLINES[this.num],cT=new Date(),cD=new Date(cT.toDateString());
        var abx = this.Sbr.form['stages['+this.num+'][add_work_time]'];
        if(!abx||!sD) return;
        if(dir) {
            return this.setWTime(abx.value, null, noabx);
        }
        var dbx = this.Sbr.form['stages['+this.num+'][dead_day]'];
        var mbx = this.Sbr.form['stages['+this.num+'][dead_month]'];
        var ybx = this.Sbr.form['stages['+this.num+'][dead_year]'];
        var swch = this.Sbr.form['stages['+this.num+'][add_wt_switch]'];
        sy=sD.getFullYear();
        if(add!==undefined) {
            add=parseInt(add);
            if((add=isNaN(add)?0:add)<0) add=0;
            nD = new Date(sD.toString());
            nD.setDate(sD.getDate()+add*(swch.selectedIndex?-1:1));
        }
        else {
            sy=sD.getFullYear();
            cy=cD.getFullYear();
            y=parseInt(ybx.value);
            m=parseInt(mbx.value);
            d=parseInt(dbx.value);
            y=isNaN(y)?0:y;
            //if((y=isNaN(y)?0:y)<cy) y=cy;
            dmth = dym(y);
            if((d=isNaN(d)?0:d)>dmth[m]) d=dmth[m];
            if(d<=0) d=1;
            nD = new Date(y,m,d);
        }
        var cn=nD>sD&&(Math.round((nD-cD)/3600000/24)>this.MAX_WORK_TIME);
        if(nD<cD||add!==undefined||cn) {
            if(cn)(nD=cD).setDate(cD.getDate()+this.MAX_WORK_TIME);
            else if(nD<cD&&nD<sD)nD=sD<cD?sD:cD;
            else if(nD<cD&&add!==0)nD=cD;
            d=nD.getDate();
            m=nD.getMonth();
            y=nD.getFullYear();
        }
        dbx.value = d;
        mbx.selectedIndex = m;
        ybx.value = y;
        nv = Math.round(((nD-sD)-(nD.getTimezoneOffset()-sD.getTimezoneOffset())*60000)/3600000/24);
        if(nv)
            swch.selectedIndex = (nv<0)-0;
        if(!noabx)
            abx.value = (t=Math.abs(nv))?t:'';
    };

    this.changeStatus
    =function(sid,status) {
        xajax_changeStageStatus(sid,status);
    };

    this.msg_forms = {};
    this.initMsgForm
    =function(fkey, to_edit, elmsfx, fs_count, fs_show, yt_show, nofocus) {
        var fo = this.msg_forms[fkey];
        if(!fo)fo={};
        fo.to_edit = to_edit;
        fo.max_fs_count = this.MAX_MSG_FILES - fs_count;
        fo.mA = new mAttach2(document.getElementById('msg_files_list'+elmsfx).firstChild, fo.max_fs_count, {p:'sbr-btn-add', m:'i-btn sbr-btn-del', f:'.cl-form-files', nv:true});
        fo.fsSld = new Fx.Slide(document.getElementById('msgs_files_box'+elmsfx), {duration: 200});
        fo.ytSld = new Fx.Slide(document.getElementById('msgs_yt_box'+elmsfx), {duration: 200});
        fo.form = document.getElementById('msg_form'+elmsfx);
        if(!fs_show)
            fo.fsSld.hide();
        if(!yt_show)
            fo.ytSld.hide();
        document.getElementById('msg_fs_toggler'+elmsfx).onclick = function() {fo.fsSld.toggle()};
        document.getElementById('msg_yt_toggler'+elmsfx).onclick = function() {fo.ytSld.toggle()};
        if(!nofocus && fo.form) {
            this._tmp = fo.form.msgtext;
            window.setTimeout("if(window.SBR_STAGE && window.SBR_STAGE._tmp){window.SBR_STAGE._tmp.focus();delete window.SBR_STAGE._tmp}",0);
        }
        if(fkey!==0) {
            fo.box = this.getMsgFormBox(fkey);
            this.msg_forms[fkey] = fo;
        }
    };

    this.delMsgForm
    =function(fkey) {
        var fo = this.msg_forms[fkey];
        try{fo.box.innerHTML='';}catch(e){}
        delete this.msg_forms[fkey];
    };

    this.getMsgFormBox
    =function(fkey) {
        var fo = this.msg_forms[fkey];
        return ( fo && fo.box ? fo.box : document.getElementById('msg_form_box'+fkey) );
    };

    this.getMsgForm
    =function(msgid, to_edit, msg_form, fs_count, fs_show, yt_show) {
        var fo,mbx,fbx,fkey=msgid,no_server=(msg_form!==undefined);
        fo = this.msg_forms[fkey];
        if(fo && fo.to_edit === to_edit) {
            this.delMsgForm(fkey);
            return;
        }
        if(!no_server) {
            xajax_getMsgForm(this.id, msgid, to_edit);
        }
        else {
            fbx = this.getMsgFormBox(fkey);
            fbx.innerHTML = msg_form;
            
            // инициализируем комбо-календарь
            var $combo = fbx.getElements('.b-combo__input_calendar');
            if ($combo) {
                ComboboxManager.initCombobox($combo);
            }
            
            for(var k in this.msg_forms) {
                if(k!=fkey)
                    this.delMsgForm(k);
            }
            this.initMsgForm(fkey, to_edit, to_edit ? msgid : '', fs_count, fs_show, yt_show);
        }
    };

    this.delMsgAttach
    =function(itm, msgid, id) {
        if(!this.id) return false;
        var dinp,abox = itm.parentNode;
        if(this.msg_forms[msgid]) {
            dinp = document.createElement('INPUT');
            dinp.type = 'hidden';
            dinp.name = 'delattach['+id+']';
            dinp.value = id;
            this.msg_forms[msgid].form.appendChild(dinp);
            this.msg_forms[msgid].mA.incMax();
            abox.parentNode.removeChild(abox);
        }
    };

    this.delMsg
    =function(msgid, msg_node) {
        var noserver=(msg_node!==undefined);
        if(!noserver) {
            if(!window.confirm('Вы действительно хотите удалить сообщение?'))
                return false;
            xajax_delMsg(msgid, this.id);
            return;
        }
        this.delMsgForm(msgid);
        var nbox = document.getElementById('c_'+msgid);
        nbox.innerHTML = msg_node;
    };

    this.msg_anchors=[];
    this.setMsgAnchor
    =function(nid,tm) {
        if(tm==null && nid!=null && _SAFARI && window.SBR_STAGE) {
            window.setTimeout('SBR_STAGE.setMsgAnchor("'+nid+'", 1)',0);
            return;
        }
        var nbox,obox;
        if(nid==null) {
            document.location.href.match(/#(.*)$/);
            nid=RegExp.$1
        }
        if(nid && (nbox=document.getElementById(nid))) {
            while(obox=this.msg_anchors.pop()) {
                obox.className = obox.className.replace(/ cl-li-this/, '');
            }
            nbox.className += ' cl-li-this';
            this.msg_anchors.push(nbox);
        }
    };

    this.initDSButt
    =function(sid) {
        var dsb;
        if(!this.bx_delstage_butt && (dsb=$('delstage_box'+this.num)))
            this.bx_delstage_butt = dsb.getElement('a');
        if(this.bx_delstage_butt)
            this.bx_delstage_butt.onclick = function() {_SBR.delStage(this, sid);};
    };


    //////////////////////////////////////////////
    // !!! Это только для создания/редактирования

    if(this.node) {
        var _id,stgnumbx = this.node.getElement(this.Sbr.STAGE_NUM_CLS);
        this.num = stgnumbx.getAttribute('innum')-0;
        this.outnum = stgnumbx.innerHTML-0+1;
        this.bx_cost  = this.Sbr.form['stages['+this.num+'][cost]'];
        this.bx_cost_total = this.Sbr.form['stages['+this.num+'][cost_total]'];
        this.bx_cost_sys = this.Sbr.form['cost_sys['+this.num+']'];
        if(!this.Sbr.bx_cost_sys) // !!! вынести в SBR
            this.Sbr.bx_cost_sys = this.bx_cost_sys;
        this.changeCost(); // !!!
        this.changeSys();

        this.bx_category = this.Sbr.form['stages['+this.num+'][category]'];
        this.bx_flist = $(this.node).getElement('.form-files-list');
        this.bx_falist = $(this.node).getElement('.form-files-added');
        if(this.bx_id = this.Sbr.form['stages['+this.num+'][id]'])
            this.id = parseInt(this.bx_id.value);

        this.changeCat(this.bx_category.value);
        this.initAttach();
        flt($(this.node).getElement('.flt-cnt').parentNode, 'slow');
        this.initDSButt(this.id);
    }
};

function go_anchor(name,tryscrl) { 
    if(name=='') return;
    if(tryscrl && !_OPERA) {
        var abox;
        if(!(abox=document.anchors[name]))try{abox=document.anchors(name);}catch(e){abox=document.anchors.item(name);}
        if(abox) try {
            new Fx.Scroll(window, {duration : 0}).toElement($(abox));
            //abox.parentNode.scrollIntoView(true);
            return true;
        } catch(e){ }
        return false;
    }
    document.location.href = document.location.href.replace(/#.*$/, '') + '#'+name;
}
function f2v(s) {return (s<=0 ? '' : mny(s));}
function v2f(s) {var f=parseFloat(s.toString().replace(/,/g,'.'));return (isNaN(f)||f<0 ? 0 : mny(f));}
function f2f(s) {return s.toString().replace(/\./g, ',');}
function i2v(s) {return (s<=0 ? '' : s);}
function v2i(s) {var i=parseInt(s);return (isNaN(i)||i<0 ? 0 : mny(i));}
function rnd(s,c) {var p=Math.pow(10,c);return Math.round(s*p)/100;}
function mny(s) {return rnd(s,2);}
function fmt(s) {
    s=mny(s);
    var y,x,pp=[];
    s=s.toString();
    if(s.indexOf('.')==-1)s+='.00';
    pp=s.split('.');
    x=pp[0];
    while(x!=y) {
        y=x;
        x=y.replace(/(\d)(\d{3})($|&)/, '$1&nbsp;$2$3');
    }
    return (x+'.'+pp[1]+'0000').replace(/(\.\d{2}).+$/, '$1');
}
function re_sys(s) {
    return s.replace(/рубл.*/i, 'р.').replace(/Яндекс\.Деньги/i, 'ЯД');
}
function dym(y) {return [31,(y%4==0&&(y%100!=0||y%400==0))?29:28,31,30,31,30,31,31,30,31,30,31];}
function ndfl_round(s) {
    var si = Math.floor(s);
    if(s - si > 0.5)
        return Math.ceil(s);
    return si;
}
function vsty(t) {return t=='TABLE'?'table':(t=='TR'?'table-row':'block');}


function sbr_calc (frm, act) {
    if (!frm) return;
    _cache = {};
    _timer = null;
    
    var recalc_cb = function(resp) {
        $('text_error').getParent().hide();
        clearTimeout(_timer);
        
        if (!resp) return;
        
        _cache[this] = resp;
        
        if (!resp.success) {
            if (resp.msg) {
                $('text_error').set('html', resp.msg);
                $('text_error').getParent().show();
                $$('.sbr_taxes').hide();
                $('rating_get').hide();
                $$('.table_title').hide();
            }
            return;
        }
        $$('.sbr_taxes').hide();
        var sign = '';
        if(resp.usr_type == 1) {
            //var sign = '<span class="b-form__txt b-form__txt_inline b-form__txt_padright_3 b-form__txt_color_red">+</span>';    
        } else {
            //var sign = '<span class="b-form__txt b-form__txt_inline b-form__txt_padright_3 b-form__txt_color_red">&minus;</span>';
        }
        var rating_string = ending( Math.floor(resp.rating_get), ' балл рейтинга', ' балла рейтинга', ' баллов рейтинга');
        $('rating_get').set('html', 'и ' + resp.rating_get + rating_string);
        $('rating_get').setStyle('display', 'inline');
        if(resp.taxes.frl != undefined) {
            $$('.table_title_frl').show();
        } else {
            $$('.table_title_frl').hide();
        }
        if(resp.taxes.emp != undefined) {
            $$('.table_title_emp').show();
        } else {
            $$('.table_title_emp').hide();
        }
        var scheme_id = 0;
        $each(resp.taxes.emp, function (tax) {
            scheme_id = tax.scheme;
            el = $(['taxrow', tax.scheme, tax.id].join('_'));
            el.getElement('.second').set('html', sign + f2f(tax.cost));
            el.show();
        });
        
        $each(resp.taxes.frl, function (tax) {
            scheme_id = tax.scheme;
            el = $(['taxrow', tax.scheme, tax.id].join('_'));
            el.getElement('.second').set('html', sign + f2f(tax.cost));
            el.show();
        });
        
        /*if(resp.sbr_cost < 15000 && scheme_id == 1) {
            $('webm_scheme').fireEvent('click');
            $('webm_scheme').getParent().removeClass('b-filter__item_padbot_10');
            $('block_scheme').getElements('li a').each(function(elm) {
                if(elm.hasClass('b-filter__pskb') == false) {
                    elm.getParent().hide();
                } 
            });
        } else if(scheme_id == 1) {
            $('webm_scheme').getParent().addClass('b-filter__item_padbot_10');
            $('block_scheme').getElements('li a').each(function(elm) {
                if(elm.hasClass('b-filter__pskb') == false && elm.hasClass('b-filter__pdrd') == false) {
                    elm.getParent().show();
                } 
            });
        }*/
        
        $('emp_cost').set('value', f2f(resp.emp_cost));
        $('frl_cost').set('value', f2f(resp.frl_cost));
        $('sbr_cost').set('value', f2f(resp.sbr_cost));
        
        $('hash_link').set('value', resp.hash);
        
        //$$('.sbr_taxes').show();
    };
        
    var recalc = function(f) {
        if (!f) return;
        sum = 0;
        f.getElements('input[name=sbr_cost],input[name=emp_cost],input[name=frl_cost]').each(function(el) {
            sum += el.get('value').trim().length == 0 ? 0 : el.get('value').toFloat();
        });
        if (sum <= 0) {
            $$('.sbr_taxes .second').set('html', '0,00');
            $$('.sbr_taxes').hide();
            $('rating_get').hide();
            $$('.table_title').hide();
            return;
        }
        
        _key = f.toQueryString();
        if (_cache[_key]) {
            recalc_cb.bind(_key, _cache[_key]).call();
            return;
        }
            
        r = new Request.JSON({
            'url': '/xajax/sbr.server.php',
            'onComplete' : recalc_cb.bind(_key)
        });
            
        params = {};            
        $each(f.getElements('input[type=hidden],input[type=text]'), function(el) {
            params[el.get('name')] = el.get('value');
        });
        r.post({
            'xjxfun': 'sbrCalc',
            'xjxargs': params,
            'u_token_key': _TOKEN_KEY
        });
    };
    
    if( act != undefined && act == 'recalc') {
        recalc(frm);
        return;
    }
    
    if (Browser.ie || Browser.opera) {
        frm.getElements('input[type=text]').addEvent('keyup', function(evt) {
            if (evt.key != 'enter') {
                return;
            }
            $(this).fireEvent('change');
        });
    }
    
    frm.getElements('input[type=text]').addEvent('change', function() {
        el = this;
        frm.getElements('input[type=text]').each(function(_el) {
            if (_el.get('name') != el.get('name')) _el.set('value', '');
        });
        recalc(this.getParent('form'));
    });
    
    frm.getElements('input[type=radio]').addEvent('click', function(evt) {
        
        _name = this.get('name');
        
        if (_name == 'residency' || _name == 'frl_type' ) {
            
            if (_name == 'residency' && $('max_cost_hint')) {
                $('max_cost_hint').setStyle('display', (this.get('value') == 1 ? 'none' : ''));
            }
            
            inp2 = this.get('name') == 'residency' ? 'frl_type' : 'residency';
            inp2 = frm.getElement('input[name='+inp2+']:checked').get('value');
            
            if (inp2 != 2) {
                $$('input[name=currency]').each(function(inp) {
                    if (inp.get('value') != 5) {
                        inp.set('disabled', (this.get('value') != 1));
                    }
                }, this);
            }

            if (frm.getElement('input[name=currency]:checked').get('value') != 5) {
                frm.getElements('input[name=currency]').set('checked', false);
                frm.getElement('input[name=currency]').set('checked', true);
                frm.getElement('input[name=currency]').fireEvent('click');
                return;
            }
        }
        
        recalc(this.getParent('form'));
            
        if (this.get('name') == 'currency') {
            switch(this.get('value')) {
                case '4':
                    ctxt = 'ЯД';
                    break;
                case '3':
                    ctxt = 'WMR';
                    break;
                default:
                    ctxt = 'руб.';
                    break;
            }
                
            $$('.currency_type').set('html', ctxt);
        }
    });
    
    $$('.calc-select .last').addEvent('click',function(){
        $$('.calc-select li a').removeClass('active'); 
        $(this).addClass('active'); 
        $$('.page-calc .form').addClass('active');
        return false;
    })
    $$('.calc-select .first').addEvent('click',function(){
        $$('.calc-select li a').removeClass('active'); 
        $(this).addClass('active'); 
        $$('.page-calc .form').removeClass('active');
        return false;
    });
    
    if (frm.getElement('input[name=frl_type][value=2]:checked')) {
        frm.getElement('input[name=frl_type][value=2]:checked').fireEvent('click');
    }
    
    if (frm.getElement('input[name=residency][value=2]:checked')) {
        frm.getElement('input[name=residency][value=2]:checked').fireEvent('click');
    }
}

function checkWMDoc() {
    xajax_checkWMDoc();
    $('act_error').set('html', '');
}

function clearCheckWMDoc() {
    if($('wmdoc_alert')) $('wmdoc_alert').dispose();
    $('submit_btn').removeClass('btnr-disabled');
    $('act_error').set('html', '');
}

/**
 * вешает обработчики на "Требования к файлам" и 'закрыть "Требования к файлам"'
 */
function init_fileinfo() {
    $$('.b-shadow__icon_close:not([id="help_popup_close"])').removeEvents('click');
    $$('.b-shadow__icon_close:not([id="help_popup_close"])').addEvent('click', function() {
        var parent = $(this).getParent();
        parent.toggleClass('b-shadow_hide');
    });

    $$('.b-fileinfo').removeEvents('click'); // очищаем мусор из файлов b-menu.js
    $$('.b-fileinfo').addEvent('click', function(event){
        event.stop();
        var parent = $(this).getParents('.b-filter');
        parent.getElement('.b-fileinfo-shadow').toggleClass('b-shadow_hide');
    });
    $$('.b-fileinfo-shadow').addEvent('click', function(event){
        event.stop();
    });
    $(document.body).addEvent('click', function() {
        $$('.b-fileinfo-shadow').addClass('b-shadow_hide');
        $$('div.b-filter__overlay').destroy();
    });
}

