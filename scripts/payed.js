
var block_payed = 0;
function toggleBlockService(id, check) {
    if(check == undefined || id == undefined) return false;
    if(check) {
        block_payed += 1;
        document.getElementById(id).style.display = 'block';
    } else {
        block_payed -= 1;
        document.getElementById(id).style.display = 'none';
    }

    if(block_payed > 0) {
        document.getElementById('block_payed').style.display = 'block';
    } else {
        document.getElementById('block_payed').style.display = 'none';
    }

    sumPayed();
}

function changePrice(price, id, x) {
    var sum = price*x;
    var numericExpression = /^ *(?:\d[\d ]*|\d*( \d+)*[.,]\d*) *$/;
    if(!sum.toString().match(numericExpression)){
        sum = 0;
    }
    document.getElementById(id).innerHTML = sum.round(2);
}

function noSumAmmount(sum, block_id, id) {
    if(typeof id == 'undefined') return;
    var ammount = account_sum;
    if(!document.getElementById(id)) return;
    _btn = $(id).getParent('form').getElement('a.btn-blue');
    
    if(_btn) {
        _btn.addClass('btn-disabled');
    }

    sum_add = sum-ammount;
    document.getElementById(block_id).style.display = 'block';
    document.getElementById(id).innerHTML = sum_add.round(2);
    document.getElementById(id + '_curr').innerHTML = ending(sum_add, 'рубль', 'рубля', 'рублей');
    
}

function sumPayed() {
    var sum = 0;
    
    if(document.getElementById('block_answer') != undefined) {
        if(document.getElementById('block_answer').style.display == 'block') {
            document.getElementById('i_answers_sum').value = op[document.getElementById('num_answers').value];
    
            //document.getElementById('answers_sum').innerHTML;
            sum = sum - (-document.getElementById('add_answers_sum').innerHTML);
        } else {
            document.getElementById('i_answers_sum').value = 0;
        }
    }

    if(document.getElementById('block_spec').style.display == 'block') {
        document.getElementById('i_spec_sum').value = document.getElementById('add_spec_sum').innerHTML;
        sum = sum - (-document.getElementById('add_spec_sum').innerHTML);
    } else {
        document.getElementById('i_spec_sum').value = 0;
    }

    noSumAmmount(sum, 'block_pay_sum', 'pay_sum_add');

    document.getElementById('payed_sum_add').value = sum.round(2);
}

function checkBalance(err, frm) {
    return submitLock(document.getElementById(frm));
}

function checkTestBalance(el) {
    if (account_sum < 1) {
        el.addClass('btn-disabled');
        $$('form#testfrmbuy div.lnk-pay').setStyle('display', 'block');
    }
}


var moveSpecLock=0;
var mvSpec=0;
function moveSpec(bxid, dir, tt, err, mode) {
    //    if(dir<0){--bxid;dir=1;}
    if(!mvSpec && !moveSpecLock) {
        if(!SPARAMS[bxid]) return;
        var pid=SPARAMS[bxid][0];
        var scid=SPARAMS[bxid][1];
        if(!scid)return;
        var t,noserver=(mode!=null);

        if(moveSpecLock) return;
        ++moveSpecLock;
        xajax_moveSpec(pid, scid, dir);

        mvSpec = [bxid, dir];
        return;
    }
    if(mvSpec && !err) {
        bxid = mvSpec[0];
        dir = mvSpec[1];
        if(!SPARAMS[bxid]) return;

        var mvbx=document.getElementById('spec_box'+bxid);
        var nrid=bxid+dir,nrbx=document.getElementById('spec_box'+nrid);
        var nrp=SPARAMS[nrid],mvp=SPARAMS[bxid];
        var mvdt=document.getElementById('spec_dt'+bxid);
        var mvnm=document.getElementById('spec_name'+bxid);
        var nrdt=document.getElementById('spec_dt'+nrid);
        var nrnm=document.getElementById('spec_name'+nrid);
        t=mvdt.innerHTML;
        mvdt.innerHTML=nrdt.innerHTML;
        nrdt.innerHTML=t;
        t=mvnm.innerHTML;
        mvnm.innerHTML=nrnm.innerHTML;
        nrnm.innerHTML=t;
        t=mvbx.className;
        mvbx.className=nrbx.className;
        nrbx.className=t;
        SPARAMS[nrid]=mvp;
        SPARAMS[bxid]=nrp;
    }
    --moveSpecLock;
    mvSpec=0;
}

function setSpecAuto(bxid, res, err) {
    var bx,pid=SPARAMS[bxid][0],noserver=(res!=null);
    if(!noserver)
        xajax_setSpecAutoPay(bxid, pid);
    else if(!err) {
        bx=document.getElementById('spec_box'+bxid);
        bx.className=res==1?'psi-grn':'';
    }
    else
        alert(err);
}

function prolongSpecs(res, err) {
    var noserver=(res!=null);
    if(!noserver)
        xajax_prolongSpecs();
    else
        alert(res);
}

function initDatePicker(new_date) {
    ds = $('date-selector');
    if(!ds) return;

    cur = new Date();
    cur.increment('day', 1);

    from_date = ds.getElement('input[name=from_date]');

    dt1 = new Date().parse(from_date.get('value'));


    dt1 = new Date().parse(from_date.get('value'));

    if(dt1 < cur && !from_date.hasClass('freeze_set')) from_date.set('value', cur.format('%Y-%m-%d'));

    max_str = ds.getElement('input[name=pro_last]').get('value');
    maxd = new Date().parse(max_str);

    initSelect = function(_date, first) {
        d1 = new Date();
        d1.parse(_date.get('value'));
        cd = new Date().increment('day', 1);
        
        selectors = _date.getAllNext('select');
        opts = selectors[0].getElements('option');
        
        for(i = 1; i <= 31; i++) {
            if(i > d1.get('lastdayofmonth')) {
                if(i <= opts.length) {
                    opts[i-1].dispose();
                }
            }
        }
        for(i = 1; i <= 31; i++) {
            opt = selectors[0].getChildren()[i-1];

            if(!opt && i <= d1.get('lastdayofmonth')) {
                opt = new Element('option', {
                    'html' : i,
                    'value': i
                });
                opt.inject(selectors[0]);
            }
            if(!opt) continue;
            
            opt.set('disabled', false);

            if(i == d1.format('%d').toInt()) opt.set('selected', true);
            if(d1.format('%Y%m') == cd.format('%Y%m')) {
                if(i < cd.format('%d').toInt()) opt.set('disabled', true);
            }
            if(first && d1.format('%Y%m') == maxd.format('%Y%m') && i > maxd.format('%d').toInt()) {
                opt.set('disabled', true);
            }
        }
        selectors[0].removeEvents('change');
        selectors[0].addEvent('change', function() {
            inp = this.getPrevious('input');

            d = new Date().parse(inp.get('value'));
            d.set('date', this.options[this.selectedIndex].get('html').toInt());

            inp.set('value', d.format('%Y-%m-%d'));

            initDatePicker(d.format('%Y-%m-%d'));
        });

        for(i = 0; i < 12; i++) {
            opt = selectors[1].getChildren()[i];
            opt.set('disabled', false);
            if((i+1) == d1.format('%m').toInt()) opt.set('selected', true);
            if(d1.format('%Y') == cd.format('%Y')) {
                if((i+1) < cd.format('%m').toInt() || (first && d1.format('%Y') == maxd.format('%Y') && (i+1) > maxd.format('%m'))) opt.set('disabled', true);
            }
        }
        selectors[1].removeEvents('change');
        selectors[1].addEvent('change', function() {
            inp = this.getPrevious('input');
            
            d = new Date().parse(inp.get('value'));
            d.set('month', this.options[this.selectedIndex].get('html').toInt()-1);

            inp.set('value', d.format('%Y-%m-%d'));
            
            initDatePicker(d.format('%Y-%m-%d'));
        });

        for(i = 0; i < 3; i++) {
            opt = $(selectors[2].options[i]);
            if(opt.get('html').toInt() == d1.format('%Y').toInt()) opt.set('selected', true);
            if(first && $(opt).get('html').toInt() > maxd.format('%Y').toInt()) {
                $(opt).set('disabled', 'true');
            }
        }
        selectors[2].removeEvents('change');
        selectors[2].addEvent('change', function() {
            inp = this.getPrevious('input');

            d = new Date().parse(inp.get('value'));
            d.set('year', this.options[this.selectedIndex].get('html').toInt());

            inp.set('value', d.format('%Y-%m-%d'));

            initDatePicker(d.format('%Y-%m-%d'));
        });
        
        selectors.each(function (_el) {
            _el.blur();
        });
    };

    initSelect(from_date, 1);
//    initSelect(to_date);
}

function togglePayedBox(id) {
    if(id != undefined) {
        $('payed_success_' + id).toggleClass('b-layout__txt_hide');
        $('payed_form_' + id).toggleClass('b-layout__txt_hide');
    }
}
var disabled_btn = new Object();
function sendBuyPro(form) {
    window.document.body.style.cursor = 'wait';
    $$('.b-button_block').addClass('b-button_rectangle_color_disable');
    form.submit();
    return true;
    // @todo deprecated
    if(disabled_btn['payed'] == true) return;
    disabled_btn['payed'] = true;
    new Request.JSON({
        url: '/payed/ajax_buy.php',
        onSuccess: function(resp) {
            if(resp.success) {
                if(resp.opcode == 47) {
                    $('is_enough_' + resp.opcode).destroy();
                }
                if(resp.opcode != 47 && $('pro_payed_47')) {
                    $('pro_payed_47').destroy();
                }
                
                replaceClsDate('date_max_limit_', resp.date_max_limit);
                $$('.pro_info').addClass('b-layout__txt_hide');
                $$('.buyed_pro').removeClass('b-layout__txt_hide');
                
                togglePayedBox(resp.opcode);
                $('header_payed_pro').set('html', 'Профессиональный аккаунт <div class="b-layout__txt b-layout__txt_center b-layout__txt_fontsize_20">Действует до ' + resp.pro_last + '</div>');
                $$('.payed_pro_last').set('text', resp.pro_last);
                $$('#pro_payed_' + resp.opcode + ' input[name=transaction_id]').set('value', resp.transaction);
                

                if(resp.is_not_enough) 
                for(var opcode in resp.is_not_enough) {
                    if(opcode == '') continue;
                    var dcost = resp.is_not_enough[opcode];
                    $('is_enough_' + opcode).addClass('b-layout__txt_hide');
                    $('is_not_enough_' + opcode).removeClass('b-layout__txt_hide');
                    $('is_not_enough_sum_' + opcode).set('text', dcost);
                    $('is_not_enough_sum_btn_' + opcode).set('href', '/bill/?paysum='+dcost);
                    $('is_not_enough_sum_btn_' + opcode).set('onclick', "Cookie.write('need_paysum', '" + dcost + "');");
                }
                
                
            } else {
                if(resp.error != '') {
                    alert(resp.error);
                }
            }
            $$('.b-button_block').removeClass('b-button_rectangle_color_disable');
            disabled_btn['payed'] = false;
            window.document.body.style.cursor = 'default';
        }
    }).post({
         'mnth': form.getElement('input[name=mnth]').value,
         'transaction_id': form.getElement('input[name=transaction_id]').value,
         'oppro': form.getElement('input[name=oppro]').value,
         'action': form.getElement('input[name=action]').value,
         'u_token_key': _TOKEN_KEY
    });
}

function replaceClsDate(remove, replace) {
    if(replace == undefined) replace = '';
    var cls = $('freez_date').getParent().get('class').split(" ");
    for(i=0;i<cls.length;i++) {
        if(cls[i].indexOf(remove) !== -1) {
            $('freez_date').getParent().removeClass(cls[i]);
        }
    }
    $('freez_date').getParent().addClass(replace);
    ComboboxManager.initCombobox($('freez_calendar').getElements('.b-combo__input'));
}

function freezeDisabled(action) {
    $('freeze_on').removeClass("b-layout__txt_hide");
    $('freeze_enable').addClass("b-layout__txt_hide");
    $('action_freeze').set('value', action);
    $$('.freezed_btn').set('text', ( action == 'freeze_cancel' ? 'Отменить' : 'Разморозить' ) );
    /*$('freez_date').getParent().addClass("b-combo__input_disabled");
    $('freez_date').set('readonly', true);
    
    $('freez_type').getParent().addClass("b-combo__input_disabled");
    $('freez_type').set('readonly', true);
    
    $('action_freeze').set('value', action);
    
    $$('.freezed_btn').set('text', ( action == 'freeze_cancel' ? 'Отменить' : 'Разморозить' ) );*/
    
}

function freezeEnabled(time) 
{
    
    $('freeze_on').addClass("b-layout__txt_hide");
    $('freeze_enable').removeClass("b-layout__txt_hide");
    
    //$('action_freeze').set('value', 'freeze');
    //$$('.freezed_btn').set('text', 'Заморозить' );
    
    
    
    /*$('freez_date').getParent().removeClass("b-combo__input_disabled");
    $('freez_date').set('readonly', false);
    
    $('freez_type').getParent().removeClass("b-combo__input_disabled");
    $('freez_type').set('readonly', false);
    
    $('action_freeze').set('value', 'freeze');
    $$('.freezed_btn').set('text', 'Заморозить' );
    
    var d = new Date(time);
    var month = d.getMonth() - (-1);
    var day = ((d.getDate() < 10)? "0" + d.getDate() : d.getDate() );
    var month = ((month < 10)? "0" + month : month );
    ComboboxManager.setDefaultValue("freez_date", day + '.' + month +'.' + d.getFullYear() );
    $("freez_date").set('value', day + '.' + month +'.' + d.getFullYear() );
    
    $("freez_date_eng_format").destroy();
    ComboboxManager.initCombobox($('freez_calendar').getElements('.b-combo__input'));
    $("freez_date").getParent().removeClass("b-combo__input_error");*/
}

function freezeClosed() {
    $('freeze_disable').removeClass('b-layout__txt_hide');
    $('freeze_enable').addClass('b-layout__txt_hide');
    $('freeze_on').addClass('b-layout__txt_hide');
}

window.addEvent('domready', function() {
    $(document.body).addEvent("click", function() {
        $$('.terms_btn').getPrevious().addClass('b-shadow_hide');
    }); 
    
    $$('.freeze_type').addEvent("click", function() {
        if($(this).hasClass("b-layout__text-selected")) return;
        var now_type = $('freez_type').get('value');
        
        $$('.freeze_type').addClass("b-layout__text-noselected").removeClass("b-layout__text-selected").removeClass("b-post__label");
        
        if($(this).get('id')=='ftype1') {
            $('freez_type').set('value', 1);
            $('ftype1').removeClass('b-layout__text-noselected').addClass('b-layout__text-selected');
            if($('ftype2')) { $('ftype2').addClass('b-post__label'); }
            if($('ftype3')) { $('ftype3').addClass('b-post__label'); }
            if($('ftype4')) { $('ftype4').addClass('b-post__label'); }    
        }
        if($(this).get('id')=='ftype2') {
            $('freez_type').set('value', 2);
            if($('ftype1')) { $('ftype1').addClass('b-post__label'); }
            $('ftype2').removeClass('b-layout__text-noselected').addClass('b-layout__text-selected');
            if($('ftype3')) { $('ftype3').addClass('b-post__label'); }
            if($('ftype4')) { $('ftype4').addClass('b-post__label'); }
        }
        if($(this).get('id')=='ftype3') {
            $('freez_type').set('value', 3);
            if($('ftype1')) { $('ftype1').addClass('b-post__label'); }
            if($('ftype2')) { $('ftype2').addClass('b-post__label'); }
            $('ftype3').removeClass('b-layout__text-noselected').addClass('b-layout__text-selected');
            if($('ftype4')) { $('ftype4').addClass('b-post__label'); }
        }
        if($(this).get('id')=='ftype4') {
            $('freez_type').set('value', 4);
            if($('ftype1')) { $('ftype1').addClass('b-post__label'); }
            if($('ftype2')) { $('ftype2').addClass('b-post__label'); }
            if($('ftype3')) { $('ftype3').addClass('b-post__label'); }
            $('ftype4').removeClass('b-layout__text-noselected').addClass('b-layout__text-selected');
        }
    });
    
    $$('.terms_btn').getPrevious().addEvent('click', function(e) {
        e.stopPropagation();
    });
    
    $$('.terms_btn').addEvent("click", function(e) {
        e.stopPropagation();
        var popup = $(this).getPrevious();
        if(popup.hasClass('b-shadow_hide')) {
            popup.removeClass('b-shadow_hide');
        } else {
            popup.addClass('b-shadow_hide');
        }
        
    });
    
    $$('.freezed_btn').addEvent("click", function() {
        if(disabled_btn['freeze'] == true) return;
        disabled_btn['freeze'] = true;
        
        var freezed_action = $('action_freeze').get('value');
        var freezed_time   = $('freez_date_eng_format').get('value');
        var freezed_type   = $('freez_type').get('value');
        
        if(freezed_action == 'freeze_stop') {
            var cf = "Вы уверены, что хотите разморозить аккаунт?\r\n\
Вы частично использовали срок заморозки. При разморозке неиспользованные дни от выбранного периода сгорят.";
            
            if(confirm(cf)) {
                xajax_freezePro( freezed_action, freezed_time, freezed_type);
            }
        } else {
            xajax_freezePro( freezed_action, freezed_time, freezed_type);
        }
    });
    
    $$('.auto_prolong').addEvent('click', function() {
        if(disabled_btn['prolong'] == true) return;
        disabled_btn['prolong'] = true;
        if($(this).hasClass("auto_prolong_btn")) {
            this.checked = $(this).get("data-check") == 'f' ? true : false;
            $(this).set("data-check", this.checked == true ? 't': 'f');
        }
        window.document.body.style.cursor = 'wait';
        var postUrl = '/payed/';
        if(role != undefined && role == 'EMP')  postUrl = '/payed-emp/';
        if(this.checked == true) {
            new Request.JSON({ 
                url: postUrl, 
                onSuccess: function(res) {
                    if(res.wallet_popup) {
                        toggleWalletPopup();
                        $$('.walletSelect').toggleClass('b-layout__txt_hide');
                    }

                    $$('.auto_prolong').set('checked', true);
                    $$('.auto_prolong_btn').set('html', 'Выключить');
                    $$('.auto_prolong_btn').set('data-check', 't');
                    window.document.body.style.cursor = 'default';
                    disabled_btn['prolong'] = false;
                } 
            }).post({'pro_auto_prolong': 'on', 'u_token_key': _TOKEN_KEY});
            
        } else {
            new Request.JSON({ 
                url: postUrl, 
                onSuccess: function(res) {
                    $('autoprolong_html').set('html', '');
                    window.document.body.style.cursor = 'default';
                } 
            }).post({'pro_auto_prolong': 'off', 'u_token_key': _TOKEN_KEY});
        }
    });
    
    //по-умолчанию покупка ПРО 19FM
    var default_pro = $$('input[name=oppro][value=48]');
    if(default_pro) {
        default_pro.set('checked', true);
        default_pro.addEvent('click', function() {
            eval(this.get('onclick'))
        });
        default_pro.fireEvent('click');
    }

    if($('testfrmbuy')) checkTestBalance($('testfrmbuy').getElement('a.btn-blue'));

    initDatePicker();
    
    if ($('freez-attent') && document.getElement('select[name=to_date]')) {
        document.getElement('select[name=to_date]').addEvent('change', function() {
            $('freez-attent').setStyle('display', (this.get('value') == 2 ? '' : 'none'));
        });
    }
});