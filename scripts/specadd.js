window.addEvent('domready', function() {
    var el,specsTbl=document.getElementById('specsTbl');
    if(specsTbl) {
        for(var i=0,sr=specsTbl.rows,len=sr.length;i<len;i++) {
            sr[i].SPARAMS=SPARAMS[i];
        }
    }
    if(window.HTML_KWORDTMPL && (el=$$('.prfl-tags'))) {
        el.getElement('a').addEvent('click', function() {
            var h,par=this.getParent('p');
            if (!par) {
                return false;
            }
            if (el = par.getElement('.prfl-tags-more')) {
                h=','+el.get('html').replace(/([^,]+),,([^,]+)/g, ' '+HTML_KWORDTMPL);
                el.set('html',h);
                el.setStyle('display','inline');
            }
            if (el = par.getElement('.prfl-tags')) el.setStyle('display','none');
            if (el = par.getElement('.prtfl-hellip')) el.setStyle('display','none');
            return false;
        });
    }
} );


function changeCat(ccid) {
    var bx=document.getElementById('subcat_box');
    if(bx)bx.innerHTML = genSubCat(ccid);
}
function genSubCat(ccid, scid) {
    var i=0,sfrm=document.getElementById('saveSpecFrm');
    var h='<select class="es-sel2" name="prof_id" onchange="document.getElementById(\'saveSpecFrm\').savespec_btn.disabled=!!EX_SPECS[this.value]">';
    sfrm.savespec_btn.disabled=false;
    if(!(-ccid))
        h+='<option value="0">&lt;—пециализаци€ не выбрана&gt;</option>';
    if(CATEGORIES[ccid]!=null) {
        for(var k in CATEGORIES[ccid]) {
            for(var n in CATEGORIES[ccid][k]) {
                h +='<option value="'+n+'"'+(n==scid ? ' selected="true"' : '')+(EX_SPECS[n] ? ' style="color:#666666"' : '')+'>'+CATEGORIES[ccid][k][n]+'</option>';
                if((n==scid||!scid&&!i) && EX_SPECS[n])
                    sfrm.savespec_btn.disabled=true;
                i++;
            }
        }
    }
    return h+'</select>';
}

function genCat(ccid) {
    var h='<select class="es-sel1" onchange="changeCat(this.value)"><option value="0">&lt;–аздел не выбран&gt;</option>';
    for(var k in CATEGORIES) {
        for(var n in CATEGORIES[k]) {
            h +='<option value="'+k+'"'+(k==ccid ? ' selected="true"' : '')+'>'+n+'</option>';
        }
    }
    return h+'</select>';
}

function cleanEdit() {
    var edbx=document.getElementById('editspec_box');
    if(edbx && edbx.trbx!==undefined) {
        edbx.trbx.style.display='';
        edbx.trbx=undefined;
        edbx.style.display='none';
        $(edbx).inject(edbx.getParent());
    }
    return edbx;
}

function getSpecBx(itm) {
    while(itm=itm.parentNode)if(itm.tagName=='TR')return itm;
}

function editSpec(itm) {
    var sfrm,trbx,edbx=cleanEdit();
    trbx=getSpecBx(itm);
    var pid=trbx.SPARAMS[0];
    var scid=trbx.SPARAMS[1];
    var ucid=trbx.SPARAMS[2] ? trbx.SPARAMS[2] : scid;
    var ccid=trbx.SPARAMS[3];
    edbx=trbx.parentNode.insertBefore(edbx, trbx);
    edbx.trbx=trbx;
    edbx = $(edbx);
    trbx = $(trbx);
    edbx.getChildren('td')[0].innerHTML=trbx.getChildren('td')[0].innerHTML;
    edbx.getChildren('td')[1].innerHTML=trbx.getChildren('td')[1].innerHTML;
    edbx.getChildren('td')[2].innerHTML=genCat(ccid);
    edbx.getChildren('td')[3].innerHTML=genSubCat(ccid,ucid);
    edbx.className=pid?'es-payed':'';
    edbx.style.display='';
    trbx.style.display='none';
    if(sfrm=document.getElementById('saveSpecFrm')) {
        sfrm.oldprof_id.value=scid;
        sfrm.paid_id.value=pid;
    }
}

var movingSpec = null;
function moveSpec(itm, dir, mode, err, new_pid) {
    cleanEdit();
    itm = $(itm);
    if(itm) {
        if(movingSpec) return;

        from_el = itm.getParent('tr');
        if(!from_el.SPARAMS) return;

        to_el = dir < 0 ? from_el.getPrevious('tr') : from_el.getNext('tr');

        movingSpec = [from_el, to_el, dir];

        xajax_moveSpec(from_el.SPARAMS[0], from_el.SPARAMS[1], dir);
        return;
    }

    if(!err && movingSpec) {

        from_el = movingSpec[0];
        to_el = movingSpec[1];

        from_paid = from_el.hasClass('es-payed') && !to_el.hasClass('es-payed');
        to_paid = !from_el.hasClass('es-payed') && to_el.hasClass('es-payed');
        if(from_paid || to_paid) {
            //перемещение между блоками
            q = dir < 0 ? 'tr[class!=es-payed][id!=editspec_box]' : 'tr[class=es-payed][id!=editspec_box]';
            rows = from_el.getParent().getElements(q);
            rows_n = dir < 0 ? 0 : rows.length-1;

            tl = dir < 0 ? to_el.getPrevious() : to_el.getNext();

            if(from_paid) {
                prl = from_el.getElement('img[src*=prolong-on]');
                if(prl) {
                    prl.set('src', prl.get('src').replace('prolong-on', 'prolong-off'));
                }
            }

            if(!rows[rows_n].SPARAMS[1] ) {

                to_el = rows[rows_n];
                if(new_pid) to_el = $('paid' + new_pid);

                to_el.inject(from_el, (dir < 0 ? 'before' : 'after'));

                to_el.getChildren('td')[1].getChildren('img').setStyle('display', 'none');
                to_el.getChildren('td')[1].getChildren('a').setStyle('display', '');

                to_el_copy = to_el.clone();
                from_el_copy = from_el.clone();

                if(!from_el.SPARAMS[1]) {
                    from_el.getChildren('td')[2].set('colspan', 1);
                    from_el.insertCell(3);

                    to_el.deleteCell(2);
                    to_el.getChildren('td')[2].set('colspan', 2);
                }

                if(!to_el.SPARAMS[1]) {
                    from_el.deleteCell(2);
                    from_el.getChildren('td')[2].set('colspan', 2);

                    to_el.getChildren('td')[2].set('colspan', 1);
                    to_el.insertCell(3);
                }

                to_el.getChildren('td')[2].set('html', from_el_copy.getChildren('td')[2].get('html'));
                from_el.getChildren('td')[2].set('html', to_el_copy.getChildren('td')[2].get('html'));

                if(from_el.SPARAMS[1] && !to_el.SPARAMS[1]) {
                    to_el.getChildren('td')[3].set('html', from_el_copy.getChildren('td')[3].get('html'));
                } else if (!from_el.SPARAMS[1] && to_el.SPARAMS[1]) {
                    from_el.getChildren('td')[3].set('html', to_el_copy.getChildren('td')[3].get('html'));
                } else {
                    from_el.getChildren('td')[3].set('html', to_el_copy.getChildren('td')[3].get('html'));
                    to_el.getChildren('td')[3].set('html', from_el_copy.getChildren('td')[3].get('html'));
                }

                if(!to_el.SPARAMS[1] || !from_el.SPARAMS[1]) {
                    if(from_paid && !to_el.getPrevious().SPARAMS[1]) {
                        to_el.getChildren('td')[1].getChildren('img')[0].setStyle('display', '');
                        to_el.getChildren('td')[1].getChildren('a')[0].setStyle('display', 'none');
                    }
                    if(to_paid && !to_el.getNext().SPARAMS[1]) {
                        to_el.getChildren('td')[1].getChildren('img')[1].setStyle('display', '');
                        to_el.getChildren('td')[1].getChildren('a')[1].setStyle('display', 'none');
                    }

                    from_el.inject(from_el.getParent(), (from_paid ? 'bottom' : 'top'));

                    from_el.getChildren('td')[1].getChildren('img').setStyle('display', '');
                    from_el.getChildren('td')[1].getChildren('a').setStyle('display', 'none');
                }

                ts = $A(to_el.SPARAMS);
                fs = $A(from_el.SPARAMS);

                for(i = 0; i < 4; i++){
                    if((from_paid || to_paid) && i == 0) continue;
                    to_el.SPARAMS[i] = fs[i];
                    from_el.SPARAMS[i] = ts[i];
                }

                $each(from_el.getParent().getElements('tr[id!=editspec_box] td.es-c1'), function(el, i) {
                    el.set('html', (i+1) + '.');
                });

            } else {

                to_el_copy = to_el.clone();
                from_el_copy = from_el.clone();

                if(!from_el.SPARAMS[1]) {
                    from_el.getChildren('td')[2].set('colspan', 1);
                    from_el.insertCell(3);

                    to_el.deleteCell(2);
                    to_el.getChildren('td')[2].set('colspan', 2);
                }

                if(!to_el.SPARAMS[1]) {
                    from_el.deleteCell(2);
                    from_el.getChildren('td')[2].set('colspan', 2);

                    to_el.getChildren('td')[2].set('colspan', 1);
                    to_el.insertCell(3);
                }

                to_el.getChildren('td')[2].set('html', from_el_copy.getChildren('td')[2].get('html'));
                from_el.getChildren('td')[2].set('html', to_el_copy.getChildren('td')[2].get('html'));

                if(from_el.SPARAMS[1] && !to_el.SPARAMS[1]) {
                    to_el.getChildren('td')[3].set('html', from_el_copy.getChildren('td')[3].get('html'));
                } else if (!from_el.SPARAMS[1] && to_el.SPARAMS[1]) {
                    from_el.getChildren('td')[3].set('html', to_el_copy.getChildren('td')[3].get('html'));
                } else {
                    from_el.getChildren('td')[3].set('html', to_el_copy.getChildren('td')[3].get('html'));
                    to_el.getChildren('td')[3].set('html', from_el_copy.getChildren('td')[3].get('html'));
                }

                ts = $A(to_el.SPARAMS);
                fs = $A(from_el.SPARAMS);

                for(i = 0; i < 4; i++){
                    if((from_paid || to_paid) && i == 0) continue;
                    to_el.SPARAMS[i] = fs[i];
                    from_el.SPARAMS[i] = ts[i];
                }

            }
        } else {
            //перемещение внутри блоков
//            from_el.inject(to_el, dir < 0 ? 'before' : 'after');

            var ts = to_el.SPARAMS;
            var fs = from_el.SPARAMS;

            var from_el_copy = from_el.clone();

            for(i = 2; i < 5; i++) {
                from_el.getElements('td')[i].set('html', to_el.getElements('td')[i].get('html'));
                to_el.getElements('td')[i].set('html', from_el_copy.getElements('td')[i].get('html'));
            }

            from_el.SPARAMS = ts;
            to_el.SPARAMS = fs;
        }

    }

    movingSpec = null;
}


function setSpecAuto(bxid, res, err) {
    var bx,pid=SPARAMS[bxid][0],noserver=(res!=null);

    if(SPARAMS[bxid][1] == 0) {
        return false;
    }

    if(!noserver)
        xajax_setSpecAutoPay(bxid, pid);
    else if(!err) { 
        bx=$('spec-auto-'+bxid);
        if(bx) {
            bx.getElement('img').set('src', '/images/prolong-' + (res==1?'on':'off') + '.png');
        }
    }
    else
        alert(err);
}