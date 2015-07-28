window.addEvent('domready', 
    function() {
        $(document.body).addEvent('click', function() {
            $$('.body-shadow-close').addClass('b-shadow_hide');
        });
        
        $$('.body-shadow-close').addEvent('click', function (e) {
            e.stopPropagation();
        });

        $$('.removeWallet').addEvent('click', function() {

            if(confirm('Удалить доступ к оплате?')) {
                var type = $(this).getParent('div').getElement('input[name=wallet]').get('value');
                xajax_walletRevoke(type);
            }
        });

        $$('.walletActivate').addEvent('click', function(){
            var selectType = $$('.walletTypes').getElement('input[type=radio]:checked')[0];

            if(selectType == null || selectType == undefined) {

            } else {
                xajax_walletActivate(selectType.get('value'), window.location.pathname);
            }

        });

        $$('.select-payment-systems').addEvent('click', function() {
            var active = $('active-systems');

            var a = $(this).clone().cloneEvents($(this));
            a.set('data-system', active.get('data-system'));
            a.set('text', active.get('text'));
            var span = new Element('span', {
                'id':           'active-systems',
                'data-system':  $(this).get('data-system'),
                'text':         $(this).get('text')
            });

            a.replaces(active);
            span.replaces($(this));

            $$('.payment-system').addClass('b-layout__txt_hide');
            $($(this).get('data-system')).removeClass('b-layout__txt_hide');
        });
        
        
        $$('.prepare-payment').addEvent('click', function() {
            if(!$(this).hasClass('b-button_rectangle_color_disable')) {
                if($(this).get('data-payment') != undefined) {
                    $(this).addClass('b-button_rectangle_color_disable');
                    var check = true;
                    if($(this).get('data-checked') != '' && $(this).get('data-checked') != null) {
                        var fn    = $(this).get('data-checked');
                        check = eval(fn + '()');
                    }
                    
                    if(check) {
                        xajax_preparePaymentServices($(this).get('data-payment'));
                    } else {
                        $(this).removeClass('b-button_rectangle_color_disable');
                    }
                } else {
                    $(this).removeClass('b-button_rectangle_color_disable');
                }
            }
        });
        
        $$('.cancel-reserve-orders').addEvent('click', function() {
            if(confirm('Изменить список заказов?')) {
                if($(this).get('data-reserve') != undefined) {
                    xajax_cancelReservedOrders($(this).get('data-reserve'));
                }
            }
        });
        
        //#0024711 - сохраняем значения полей в различных формах на случай переключения между ними
        if ($$(".js-payform_input").length) {
            $$(".js-payform_input").addEvent("keyup", storeInputValue).addEvent("change", storeInputValue);
            $$(".js-payform_input").each(
                function(item) {
                    restoreInputValue(item);
                }
            );
        }
        
        //##0024827 - пункт 6
        if ($$(".js-not_zero_numeric_input").length) {
            $$(".js-not_zero_numeric_input").addEvent("keydown", 
                function (e) {
                    if ((e.code == 48 || e.code == 96) && !this.value.length) {
                        return false;
                    }
                }
            ).addEvent("keyup", 
                function (e) {
                    if (this.value.trim() == "0") {
                        this.value = 1;
                        var serv_id = this.id.replace(/\D/g, '');
                        if (this.get("data-input-type") == "projects") {
                            recalc_projects(serv_id, this);
                        }
                        return false;
                    }
                }
            );
        }
    }
);
    
Number.prototype.to_money = function(c, d, t) {
    var n = this,
            c = isNaN(c = Math.abs(c)) ? 0 : c,
            d = d == undefined ? "." : d,
            t = t == undefined ? " " : t,
            s = n < 0 ? "-" : "",
            i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
            j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};

var Service = new Class({
    Implements: [Events, Options],
    html: null,
    
    options: {
        selector: {
            btnpay:         '.btn-pay',
            promo:          '.promo-link',
            pay_popup:      '.popup-pay-service',
            popup_mini:     '.popup-mini',
            btn_period:     '.popup-mini-open',
            period_type:    '.select-type', 
            period_auto:    '.select-auto-type',
            upd_period:     '.upd-period-data'
        },
        parent:         null,
        element:        null,
        service: {
            name:       '',
            order_id:   0,
            opcode:     0,
            cost:       0,
            cost_pro:   0,
            count:      1,
            auto:       0
        }
    }, 
                
    initialize: function(options){
        this.setOptions(options);
        
        var html = this.options.element;
        var obj  = this;
        
        if(html.getElement('.auto-prolong')) {
            html.getElement('.auto-prolong').addEvent('click', function() {
                if($(this).checked == true) {
                    obj.setServiceOptions({auto: 1});
                } else {
                    obj.setServiceOptions({auto: 0});
                }
            });
        }

        if(html.getElement('.auto-prolong-update')) {
            if(html.getElement('.auto-prolong') != undefined || html.getElement('.auto-prolong') != null) {
                html.getElement('.auto-prolong').addEvent('click', function() {
                    var name = obj.options.service.name.replace(/_\d+/g, '');
                    $$('.auto-' + name).set("checked", $(this).checked);
                    xajax_updateAutoProlong(obj.options.service);
                });
            }

            if(html.getElement('.auto-prolong-active') != undefined) {
                html.getElement('.auto-prolong-active').addEvent('click', function() {
                    xajax_updateProAuto( (this.checked ? 'on' : 'off') );
                });
            }
        }
        
        if(html.getElement('.service-remove')) {
            html.getElement('.service-remove').addEvent('click', function() {
                if(confirm('Удалить услугу из списка?')) {
                    var cnt = parseInt( $('count_orders').get('text') );
                    $('count_orders').set('text', cnt - 1);
                    xajax_removeOrder(obj.options.service);
                    html.destroy();
                    if(orders) {
                        delete orders.list[obj.options.service.name];
                        orders.calcServiceSum();
                    }
                }
            });
        }
        
        var popup = html.getElement('.popup-pay-service');
        
        if(html.getElement('.popup-top-mini-open')) {
            var period_popup = html.getElement('.change-select-period');
            
            if(period_popup) {
                html.getElements('.popup-top-mini-open').addEvent('click', function(e) {
                    if(period_popup.hasClass('b-shadow_hide')) {
                        period_popup.removeClass('b-shadow_hide');
                    } else {
                        period_popup.addClass('b-shadow_hide');
                        var serv_id = this.get("data-service-id");
                        var type = "projects";
                        inp = $('day_'+ serv_id);
                        if (!inp) {
                            inp = $('weeks_'+ serv_id);
                            type = '';
                        }
                        if (inp) {
                            inp.set("value", this.get("data-cancel-value"));
                            if (type == "projects") {
                                recalc_projects(serv_id, inp);
                            }
                        }
                    }
                    e.stopPropagation();
                });
                
                period_popup.getElements('.update-service-first_page').addEvent('click', function() {
                    var parent = $(this).getParent('.i-shadow');
                    var cnt = parseInt( period_popup.getElement('input[name=weeks]').get('value') );
                    if(cnt <=0 || isNaN(cnt)) {
                        period_popup.getElement('input[name=weeks]').set('value', 1);
                        cnt = 1;
                    }
                    var amm = period_popup.getElement('input[name=ammount]').get('value');
                    period_popup.addClass('b-shadow_hide');
                    parent.getElement('.upd-auto-period-data').set('text', cnt + ' ' + ending(cnt, 'неделю', 'недели', 'недель'));
                    var old_cost = obj.options.service.cost;
                    
                    obj.setServiceOptions({
                        cost:     cnt*amm,
                        cost_pro: cnt*amm,
                        count:    cnt
                    });
                    
                    if(old_cost != $(this).get('data-cost')) {
                        xajax_updateOrder(obj.options.service);
                    }
                });
                
                period_popup.getElements('.update-service-projects').addEvent('click', function() {
                    var parent = $(this).getParent('.i-shadow');
                    var cnt = parseInt( period_popup.getElement('input[name=days]').get('value') );
                    if(cnt <=0 || isNaN(cnt)) {
                        period_popup.getElement('input[name=days]').set('value', 1);
                        cnt = 1;
                    }
                    var amm     = period_popup.getElement('input[name=ammount]').get('value');
                    var pro_amm = period_popup.getElement('input[name=pro_ammount]').get('value');

                    period_popup.addClass('b-shadow_hide');
                    parent.getElement('.upd-auto-period-data').set('text', cnt + ' ' + ending(cnt, 'день', 'дня', 'дней'));
                    var old_cost = obj.options.service.cost;
                    
                    obj.setServiceOptions({
                        cost:       cnt*amm,
                        cost_pro:   cnt*pro_amm,
                        count:      cnt
                    });
                    
                    if(old_cost != $(this).get('data-cost')) {
                        xajax_updateOrder(obj.options.service);
                    }
                });
                
                period_popup.getElements('.select-auto-type').addEvent('click', function() {
                    var parent = $(this).getParent('.i-shadow');
                    parent.getElements('.select-name').removeClass('b-layout__txt_color_808080');
                    $(this).getElement('.select-name').addClass('b-layout__txt_color_808080');
                    parent.getElement('.upd-auto-period-data').set('text', $(this).get('data-period'));
                    period_popup.addClass('b-shadow_hide');
                    var old_cost = obj.options.service.cost;
                    obj.setServiceOptions({
                        opcode:     $(this).get('data-opcode'),
                        cost:       $(this).get('data-cost'),
                        cost_pro:   $(this).get('data-cost')
                    });
                    
                    if(old_cost != $(this).get('data-cost')) {
                        xajax_updateOrder(obj.options.service);
                    }
                }); 
                
            }
        }
        
        if(popup && html.getElement('.btn-pay')) {
            if(popup.getElement('.btn_add_service')) {
                popup.getElement('.btn_add_service').addEvent('click', function() {
                    if(!$(this).hasClass('b-button_rectangle_color_disable')) {
                        $(this).addClass('b-button_rectangle_color_disable');
                        xajax_addService(obj.options.service);
                    }
                });
            }
            
            if(popup.getElement('.popup-mini-open')) {
                var period_popup = popup.getElement('.period-pro-popup');
                if(period_popup) {
                    period_popup.getElements('.select-type').addEvent('click', function() {
                        var parent = $(this).getParent('.i-shadow');
                        parent.getElements('.select-name').removeClass('b-layout__txt_color_808080');
                        $(this).getElement('.select-name').addClass('b-layout__txt_color_808080');
                        parent.getElement('.upd-period-data').set('text', $(this).get('data-period'));
                        period_popup.addClass('b-shadow_hide');
                        
                        obj.setServiceOptions({
                            opcode:     $(this).get('data-opcode'),
                            cost:       $(this).get('data-cost'),
                            cost_pro:   $(this).get('data-cost')
                        });
                    });
                    
                    popup.getElement('.popup-mini-open').addEvent('click', function(){
                        if(period_popup.hasClass('b-shadow_hide')) {
                            period_popup.removeClass('b-shadow_hide');
                        } else {
                            period_popup.addClass('b-shadow_hide');
                        }
                    });
                }
            }
            
            html.getElement('.btn-pay').addEvent('click', function(e) {
                popup.removeClass('b-shadow_hide');
                e.stopPropagation();
            });
        } else if(html.getElement('.btn-pay')) {
            html.getElement('.btn-pay').addEvent('click', function() {
                this.forwardPromo();
            }.bind(this));
        }
        
        
    },
    
    forwardPromo: function() {
        window.location =  this.options.element.getElement('.promo-link').get('href');
    },
    
    setServiceOptions: function(obj) {
        var old_cost = this.options.service.cost;
        this.setOptions({service : obj});
        
        if(obj.cost != undefined && old_cost != obj.cost) {
            this.costChange();
        }
    },
            
    costChange: function() {
        var cost = orders.checkProServices() ? parseInt(this.options.service.cost_pro) : parseInt(this.options.service.cost);
        this.options.element.getElement('.upd-cost-sum').set('text', cost.to_money() );
        if(orders) {
            orders.calcServiceSum();
        }
    },

    updatedSum : function() {
        if(this.options.service.cost != this.options.service.cost_pro) {
            if(!orders.checkProServices()) {
                var cost = orders.checkProServices() ? parseInt(this.options.service.cost_pro) : parseInt(this.options.service.cost)
                this.options.element.getElement('.upd-cost-sum').set('text', cost.to_money() );
                if(!orders.checkProServices()) {
                    $$('.sum-currency').set("text", 'руб.');
                }
                if($('sum' + this.options.service.order_id) != undefined) {
                    $('sum' + this.options.service.order_id).set('text', orders.checkProServices() ? parseInt(this.options.service.cost_pro) : parseInt(this.options.service.cost));
                }
            }
        }
    }
});

var Services = new Class({
    Implements: [Events, Options],
    list: {},
    
    options: {
        pro_opcodes: [15, 118, 119, 120],
        selector: {
            main:       '#services-list',
            service:    '.service'
        },
        payed_sum:  0,
        acc_sum:    0,
        min_sum:    10
    },
            
    initialize: function(options){
        this.setOptions(options);
        this.setServices();
        
        if($('payed_acc_sum')) {
            $('payed_acc_sum').addEvent('click', function() {
                this.updatePaySum();
            }.bind(this));
        }
        
        if($$('.service-clear-confirm')) {
            $$('.service-clear-confirm').addEvent('click', function() {
                $('clear_confirm').toggleClass('b-shadow_hide');
            });
        }
        
        if($$('.service-orders-clear')) {
            $$('.service-orders-clear').addEvent('click', function() {
                xajax_clearOrdersServices();
            });
        }
    },

    calcPayedSum: function(Y, X) {
        if(X == undefined) X = this.options.acc_sum;

        var min_payed = this.options.min_sum;
        var payed_sum = new Object();
        var R = Y - X;
        if(R <= 0) {
            payed_sum = {
                'pay' : Y, // Сумма на кнопке
                'acc' : Y, // С личного счета будет списано
                'ref' : -1  // Возврат на счет
            }
        } else {
            var N = Math.ceil(R) < min_payed  ? min_payed : Math.ceil(R);

            if(N == Y) {
                payed_sum = {
                    'pay' : N,
                    'acc' : -1,
                    'ref' : -1
                }
            }

            if(N != Y && Y > min_payed) {
                if(Y - N < 0) {
                    payed_sum = {
                        'pay' : N,
                        'acc' : -1,
                        'ref' : (N - Y).to_money(2)
                    }
                } else {
                    payed_sum = {
                        'pay' : N,
                        'acc' : (Y - N).to_money(2),
                        'ref' : -1
                    }
                }
            }

            if(N != Y && Y < min_payed) {
                payed_sum = {
                    'pay' : min_payed,
                    'acc' : -1,
                    'ref' : (N - Y).to_money(2)
                }
            }
        }

        return payed_sum;
    },
            
    updatePaySum: function() {
        if($('add_pay_sum') == undefined || $('add_pay_sum') == null) return;
        var sum = this.options.payed_sum;
        $('refund_sum').addClass('b-layout__txt_hide');

        if (this.options.acc_sum >= sum) {
            $('pay_btn_name').set('text', 'Оплатить');
            $$('.payed_account_sum').set('text', sum.to_money(2));
            $('add_pay_sum').addClass('b-layout__txt_hide');
        } else {
            $('payacc_sum').addClass('b-layout__txt_hide');
            $('pay_btn_name').set('text', 'Перейти к оплате');
            $('add_pay_sum').removeClass('b-layout__txt_hide');

            var payed = this.calcPayedSum(this.options.payed_sum);

            if(payed.acc !== -1) {
                $('payacc_sum').removeClass('b-layout__txt_hide');
                $$('.payed_account_sum').set('text', payed.acc);
            }

            if(payed.ref !== -1) {
                $('refund_sum').removeClass('b-layout__txt_hide');
                $('refund_account_sum').set('text', payed.ref);
            }

            $$('.add_payed_sum').set('text', payed.pay.to_money(2));
        }
    },
            
    setServices: function() {
        var services = $$(this.options.selector.main).getElements(this.options.selector.service);
        
        for(var i=0; i < services[0].length; i++) {
            this.setService(services[0][i]);
        }
    },
    
    setService: function(el) {
        var list_id = el.get('data-name').split('_');
        list_id = list_id[list_id.length-1];
        var list_element = new Service({
            service: {
                name:       el.get('data-name'),
                order_id:   ( list_id ? list_id : 0 ) ,
                opcode:     el.getElement('input[name=opcode]') ? el.getElement('input[name=opcode]').get('value') : 0,
                cost:       el.get('data-cost'),
                cost_pro:   ( parseFloat(el.get('data-cost-pro')) > 0 || el.get('pro-discount') == 1 ? el.get('data-cost-pro') : el.get('data-cost') ),
                auto:       el.get('data-auto')
            },
            element: el
        });
        this.options.payed_sum += parseInt(el.get('data-cost'));
        this.list[el.get('data-name')] = list_element;
    },

    checkProServices: function() {
        for(var k in this.list) {
            var serv = this.list[k].options.service;
            if(this.options.pro_opcodes.join(",").indexOf(serv.opcode) != -1) {
                return true;
                break;
            }
        }
        return false;
    },
    
    calcServiceSum: function() {
        var is_exist_pro = this.checkProServices();
        if($$('.payed-sum') == undefined) return;
        var payed_sum = 0;
        for(var s in this.list) {
            var opt = this.list[s];
            this.list[s].updatedSum();
            payed_sum += parseFloat( is_exist_pro && opt.options.service.cost_pro > 0 ? opt.options.service.cost_pro : opt.options.service.cost );
        }
        $$('.payed-sum').set('text', payed_sum.to_money(2));
        this.options.payed_sum = payed_sum;
        
        if($('payed_acc_sum') != undefined) {
            this.updatePaySum($('payed_acc_sum').checked);
        } else {
            $$('.add_payed_sum').set('text', payed_sum.to_money(2));
        }
        
        this.updatePaySum();
    }
});

function forwardMain() {
    window.location = '/bill/';
}

function forwardList() {
    window.location = '/bill/orders/';
}

function recalc_projects(day, inp) {
    if(parseInt(inp.value) <= 0 || inp.value == '' || isNaN(parseInt(inp.value))) inp.value = 1;
    var ammount = orders.checkProServices() ? $('pro_ammount_'+day).get('value') : $('ammount_'+day).get('value');
    var sum     = ammount * inp.value;
    if(sum > 0) {
        $('sum'+day).set('text', sum.to_money());
        $(inp).getParent('.b-combo').getNext('.pay_place_item_day').set('text', ending(inp.value, 'день', 'дня', 'дней'));
    }
}

function getUpDay(day, calc, obj) {
   var inp = obj.getParent().getElement('input[id=day_'+day+']');
   if(inp.value < 99) inp.value = inp.value-(-calc);
   recalc_projects(day, inp);
}

function getDownDay(day, calc, obj) {
   var inp = obj.getParent().getElement('input[id=day_'+day+']');
   if(inp.value > 1) inp.value = inp.value-calc; 
   recalc_projects(day, inp);
}

function checkBankFizFields() {
    var fio  = $('fio');
    var address = $('address');
    
    var error = false;
    
    if (fio.value == '' || address.value == '') {
        if (fio.value == '')
           $('fio').getParent().addClass("b-combo__input_error");
        if (address.value == '')
           $('address').getParent().addClass("b-textarea_error");
       
       error = true;
    }
    
    return !error;
}

function checkCardFields() {
    var txt = '<strong>Поле заполнено некорректно</strong>';
    var lastname  = $('LastName').value;
    var firstname = $('FirstName').value;
    var email     = $('Email').value;
    var address   = $('Address').value;
    var phone     = $('Phone').value;
    var city      = $('City').value;
    var emailExp  = /^[A-Za-z0-9А-Яа-я\.\x21\x23\x24\x25\x26\x27\x2a\x2b\x2d\x2f\x3d\x3f\x5e\x5f\x60\x7b\x7c\x7d\x7e]{1,63}@[A-Za-z0-9А-Яа-я-]{1,63}(\.[A-Za-z0-9А-Яа-я]{1,63})*\.[A-Za-zрфРФ]{1,6}$/;

    var error = false;
    if (!lastname.match(/[a-zA_Zа-яА-Я]+/)) {
        $('LastName').getParent().addClass("b-combo__input_error");
        error = true;
    }
    ;
    if (!firstname.match(/[a-zA_Zа-яА-Я]+/)) {
        $('FirstName').getParent().addClass("b-combo__input_error");
        error = true;
    }
    ;
    if (lastname == '' || firstname == '' || !email.match(emailExp) || address == '' || phone == '' || city == '') {
        if (lastname == '')
           $('LastName').getParent().addClass("b-combo__input_error");
        if (firstname == '')
            $('FirstName').getParent().addClass("b-combo__input_error");
        if (!email.match(emailExp))
            $('Email').getParent().addClass("b-combo__input_error");
        if (address == '')
            $('Address').getParent().addClass("b-combo__input_error");
        if (phone == '')
            $('Phone').getParent().addClass("b-combo__input_error");
        if (city == '')
            $('City').getParent().addClass("b-combo__input_error");
        error = true;
    }

    return !error;
}

function checkQIWIPurseFields() {
    var error = false;
    var phone = $('reg_phone').get('value').replace("+", "");
    if(phone == '7' || phone == '77') {
        $('reg_phone').getParent().addClass("b-combo__input_error");
        error = true;
    }
    if(!phone.match(/\d{10}/)) {
        error = true;
        $('reg_phone').getParent().addClass("b-combo__input_error");
    }
    return !error;
}

function checkMobileSysFields() {
    var error = false;
    var phone = $('reg_phone').get('value').replace("+", "");
    if(phone == '7' || phone == '77') {
        $('reg_phone').getParent().addClass("b-combo__input_error");
        error = true;
    }
    if(!phone.match(/\d{10}/)) {
        error = true;
        $('reg_phone').getParent().addClass("b-combo__input_error");
    }
    return !error;
}

function checkOKPAYFields() {
    var error = false;
    var phone = $('reg_phone').get('value').replace("+", "");
    if(!phone.match(/\d{10}/)) {
        error = true;
        $('reg_phone').getParent().addClass("b-combo__input_error");
    }
    return !error;
}

function loadCities(hide) {
	if (!hide) hide = 0;
    var cities = ComboboxManager.getInput("city");	 
    var id = $("country_db_id").get("value");    
    if (Number(id)) {
        cities.loadData("getcities", id, hide, '', !hide);
    }
    else {
    	cities.clear(0, 0, 1);
    	ComboboxManager.setDefaultValue("city", "Все города", 0);
    }
}

function toggleWalletPopup(e) {
    if(e == undefined) e = null;
    $('wallet').getElement('div.b-shadow').toggleClass('b-shadow_hide');
    if(e !== null) {
        e.stopPropagation();
    }
}

function toggleAutoPayed(e) {
    if(e == undefined) e = null;
    $$('.serviceAutoprolong').toggleClass('b-shadow_hide');
    if(e !== null) {
        e.stopPropagation();
    }
}

//#0024711 - сохраняем значения полей в различных формах на случай переключения между ними
function getPayformInputStorageKey(o) {
    var s = window.location.href;
    s = s.substring( s.indexOf("/", s.indexOf("/") + 2 ) ).replace(/\?/g, "").replace(/=/g, "").replace(/\//g, "");
    s = s + "_" + (o.id? o.id : o.name);
    return s;
}

function storeInputValue(evt) {
    localStorage.setItem( getPayformInputStorageKey(evt.target), evt.target.value);
    if (evt.target.id == "country" || evt.target.id == "city") {
        var i = $(evt.target.id + "_db_id");
        localStorage.setItem( getPayformInputStorageKey( i ), i.value);
    }
}

function restoreInputValue(o) {
    var s = String(localStorage.getItem( getPayformInputStorageKey(o) ));
    if (!s.length || s == 'null') {
        return;
    }
    if (o.id == "country") {
        ComboboxManager.getInput(o.id).reload(s, 0);
        o.removeClass("b-combo__input-text_color_67");
        o.getParent("div.b-combo__input").removeClass("b-combo__input_error");
    } else if (o.id == "city") {
        var city = {id:"city_db_id"};
        var country = {id:"country_db_id"};
        ComboboxManager.getInput(o.id).selectItemById(localStorage.getItem( getPayformInputStorageKey(city) ), "getcities", "", localStorage.getItem( getPayformInputStorageKey(country) ));
        $("city").value = s;
        o.removeClass("b-combo__input-text_color_67");
    } else {
        o.value = s;
    }
}

/**
 * #0024779 - пересчитываем сумму заказа, когда пользователь удаляет pro
 * @param Array ordersIds
* */
function recalcTotalWithotPro(ordersIds) {
    var sum = 0;
    var found = 0;
    for (var i = 0; i < ordersIds.length; i++) {
        var id = ordersIds[i];
        if ( $('ammount_' + id) ) {
            if ( $('no_pro_ammount_' + id) ) {
                found = 1;
                var val = $('no_pro_ammount_' + id).value;
                $('ammount_' + id).value = val;
                if ($("sum" + id)) {
                    //поиск множителя - дни, недели и так далее
                    var mult = 1;
                    if ($("day_" + id) && $("day_" + id).value) {
                        mult = parseInt($("day_" + id).value);
                    }
                    val *= mult;
                    $("sum" + id).set("text", Number(val).to_money(2) );
                }
                $('ammount_' + id).getParent("div.service").getElement("span.upd-cost-sum").set("text", Number(val).to_money(2) );
                sum += Number(val);
            }
        }
    }
    if (!sum && !found) {
        return;
    }
    $("payment").getElements("span.payed-sum").set("text", Number(sum).to_money(2));
    if ($("payment").getElements("span.add_payed_sum") && $("payment").getElements("span.add_payed_sum").set) {
        $("payment").getElements("span.add_payed_sum").set("text", Number(sum).to_money(2));
    }
    var account = false;
    $$("a.b-bar__btn").each(
        function (a) {
            var re = new RegExp(".*/bill/$");
            if ( re.test(a.href) ) {
                account = parseFloat(a.get("text").replace(/\s+/, "").replace(",", ".").trim());
            }
        }
    );
    if (account !== false) {
        val = sum < account ? sum : account;
        $("payment").getElements("span.payed_account_sum").set("text", Number(val).to_money(2));
    }
}
