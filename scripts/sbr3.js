//TODO слить потом все в sbr2.js
var Norisk = new Class({
    Implements: [Events, Options],
    
    options: {
        'id':               'norisk-form',      //ид формы
        'selector':    {
            'mincost':            '.mincost-val'
//            'stage'         :   '.norisk-stage-block'
        },
        
        'scheme_type':      0,
        'scheme_id':        0,
        'schemes':          [],                 //схемы расчета налогов
        'form_types':       [],                 //физ/юрлицо [зак, исп]
        'rez_types':        [],                 //типы резидентства [зак, исп]
        'onStageAdd':       Class.empty,
        'attaches':         []
    },
    
    form: null,
    
    initialize: function(options) {
        this.setOptions(options);
        
        this.form = new Norisk.Form(this, $(this.options.id));
        if (!this.form) {
            return;
        }
        
        // пересчет при изменении схемы
        this.form.addEvent('schemeChange', function(val, el, frm) {
            $('taxes_alert').hide();
            if(val == 2 || val == 5) { // Подряд
                $$('.pskb_info').hide();
                $$('.pdrd_info').show();
                if(!$('unknown_frl_rez').hasClass('b-fon_hide')) {
                    $('taxes_alert').show();
                }
                this.options.mincost = 1000;
            } else {
                $$('.pskb_info').show();
                $$('.pdrd_info').hide();
                this.options.mincost = 300;
            }
            
            this.options.scheme_type = val;
            this.form.recalcTotal();            
            $$(this.options.selector.mincost).set('html', this.options.mincost);
            this.form.checkStagesSum();
        }.bind(this));
        
        // пересчет при изменении стоимости сделки
        this.form.addEvent('totalcostChange', function(val, el, frm) {
            
            this.form.recalcStages();
            // 
        }.bind(this));
    }
    
});

Norisk.KEYUP_DELAY = 300;

Norisk.Form = new Class({
    Implements: [Events, Options],
    
    options: {
        'selector':   {
            'stage':        '.norisk-stage-block',
            'filled':       'filled-content',
            'stage_add':    '.norisk-stage-new a',
            'cost_input':   'input[name=cost_total]',
            
            'popup':        '.stages-recalc-popup'
        }
    },
    
    stages: [],
    
    stagesCnt: 0,
    
    initialize: function(p, el) {
        this.norisk = p;
        this.stages = this.initStages();
        this.element = el;
        this.initElements();
        this.totalCost = 0;
        this.setErrors();
    }, 
    
    addStage: function(el) {
        if (!el) el = null;
        
        this.hidePopups();
        
        var st = new Norisk.Stage(el, this);
        st.addEvent('onCostChange', this.recalcTotal.bind(this));
        st.addEvent('onClose', this.recalcTotal.bind(this));
        st.addEvent('onClose', this.hidePopups);
        
        this.stages.push(st);
        init_fileinfo();
        if(this.element) this.checkElements();
        
        // показать или скрыть кнопки "удалить этап"
        if ($$('.norisk-stage-block').length > 1) {
            $$('.close-block').removeClass('b-button_hide');
        } else {
            $$('.close-block').addClass('b-button_hide');
        }
        
        // ресайз textarea
        var textareaWrap = st.element.getElements('div.b-textarea');
        new resizableTextarea(st.element.getElements('div.b-textarea'), {
            handler: ".b-textarea__handler",
            modifiers: {x: false, y: true},
            size: {y:[100, 30000]},
            onResize: function(current) {}
        });
        
        var textarea = st.element.getElements('textarea.b-textarea__textarea');
        textarea.addEvent('focus', makeTextareaCurrent);
        textarea.addEvent('blur', makeTextareaNotCurrent);
        
        
        return st;
    },
    
    initStages: function() {
        var arr = [];
        
        $$(this.options.selector.stage).each(function(el) {
            arr.push(this.addStage(el));
        }.bind(this));
        
        return arr;
    },
    
    checkElements: function() {
        var filled = 0;
        this.element.getElements('input[type=text],textarea').each(function(el) {
            if(el.value == '') {
                filled = 1;
            }
        }.bind(this));
        
        if(filled) {
            //$(this.options.selector.filled).removeClass('b-layout__txt_visibility_hidden');
            $(this.options.selector.filled).removeClass('b-layout__txt_hide');
        } else {
            //$(this.options.selector.filled).addClass('b-layout__txt_visibility_hidden');
            $(this.options.selector.filled).addClass('b-layout__txt_hide');
        }
        
    },
    
    initElements: function() {
        // Ещё один этап
        var hnd = this.element.getElements(this.options.selector.stage_add);
        hnd.addEvent('click', this.addStage.bind(this, [null]));
        
        
        // изменение схемы
        this.element.getElements('input[name=scheme_type]').each(function(el) {
            el.addEvent('change', this.schemeChanged.bind(this, el));
        }.bind(this));
        
        /*this.element.getElements('input[type=text],textarea').each(function(el) {
            el.addEvent('change', this.checkElements.bind(this, el));
        }.bind(this));*/
        this.element.addEvent('change', function(event){
            if (event.target.match('input[type=text], textarea')) {
                this.checkElements();
            }
        }.bind(this));

        
        
        
        
        setTimeout(function() {
            var sh = this.element.getElement('input[name=scheme_type]:checked');
            if (!sh) {
                sh = this.element.getElement('input[type=hidden][name=scheme_type]');
            }
            sh.fireEvent('change', [sh, this]);
        }.bind(this), 10);
        
        
        // изменение способа резервирования
        this.element.getElements('input[name=cost_sys]').each(function(el) {
            el.addEvent('change', this.costsysChanged.bind(this, el));
        }.bind(this));
        
        // изменение общего бюджета сделки
        var costtotal = this.element.getElement('input[name=cost_total]');
        this.delay = 0;
        costtotal.addEvent('change', this.totalcostChanged.bind(this, costtotal));
        //costtotal.addEvent('change', this.totalcostChangeVal.bind(this, costtotal));
        
        costtotal.addEvent('focus', function(el) {
            this.is_null_value = (el.get('value') == '' || el.get('value') == '0');
        }.bind(this, costtotal));
        
        costtotal.addEvent('keyup', function(el) {
            var numeric = /[^0-9\.]/;
            var val = el.value.replace(numeric, '');
            if(val != el.value) {
                el.set('value', val);
            }
            if (this.delay) {
                clearTimeout(this.delay);
            }
            if (el.get('value') == el.retrieve('lastval')) {
                return;
            }
            
            this.delay = this.totalcostChanged.delay(Norisk.KEYUP_DELAY, this, el);
            el.store('lastval', el.get('value'));
        }.bind(this, costtotal));
    },
    
    schemeChanged: function(el) {
        //console.log('schemeChanged');
        // налоги
        this.element.getElements('[class*=sch_]').hide();
        this.element.getElements('.sch_' + el.get('value')).show();
        
        this.fireEvent('schemeChange', [el.get('value'), el, this]);
        
        this.recalcPopups();
    },
    
    costsysChanged: function(el) {
        //console.log('costsysChanged');
        this.fireEvent('costsysChange', [el.get('value'), el, this]);
    },
    
    totalcostChangeVal: function(el){
        var val = el.get('value').replace(',', '.');
        val = isNaN(parseFloat(val)) ? 0 : parseFloat(val);
        if (val < this.norisk.options.mincost) {
            var sum = (this.norisk.options.mincost + this.norisk.options.mincost * 0.05)  * this.stages.length;
            el.set('value', sum);
            this.fireEvent('totalcostChange', [el.get('value'), el, this]);
            el.getParent().removeClass('b-combo__input_error');
        }
    },
    
    totalcostChange: function(sum, fire_evt) {
        var el = this.element.getElement('input[name=cost_total]');
        el.set('value', sum);
        
        // скрыть/показать информацию о налогах и вычетах
        if (sum == 0) {
            $$('.nalogi').setStyle('display', 'none');
        } else {
            $$('.nalogi').setStyle('display', '');
        }
        
        if (fire_evt) {
            this.totalcostChanged(el);
        }
        
        return true;
    },
    
    totalcostChanged: function(el) {
        //console.log('totalcostChanged');
        this.fireEvent('totalcostChange', [el.get('value'), el, this]);
    },
    
    getCost: function() {
        var st_sum = 0;
        this.stages.each(function(stage) {
            st_sum = st_sum + parseFloat(stage.getCost());
        });
        return st_sum;
    },
    
    taxesRule: function(cost) {
        if(cost <= 3000) {
            return 0.139;
        } else if(cost > 3000 && cost <= 10000) {
            return 0.129;
        } else if(cost > 10000 && cost <= 50000) {
            return 0.119;
        } else if(cost > 50000 && cost <= 100000) {
            return 0.109;
        } else if(cost > 100000) {
            return 0.099;
        }   
    },
    
    recalcTotal: function(el, st) {
        //console.log('recalc');
        $$('.taxrow-class').addClass('b-tax__level_hide'); // Открывать будем посчитанные
        var cost = null, 
            cost_total = this.getCost(),
            SCHEMES = this.norisk.options.schemes,
            STYPE = this.norisk.options.scheme_type
            tax = 1 + this.taxesRule(cost_total);
        if(this.norisk.options.scheme_id <= 21) {
            tax = 1 + 0.07; // Старый процент
        }    
        cost_total = isNaN(cost_total) ? 0 : cost_total;
        
        cost = v2f(cost_total*tax);
        
        for(k in SCHEMES[STYPE]) {
            if (!$('taxrow_' + STYPE + '_' + k)) continue;
            if(this.norisk.options.scheme_id <= 21) {
                var emp_tax = 0.07;
            } else {
                var emp_tax = this.taxesRule(cost_total);
            }
            tx = mny(cost_total * emp_tax);
            $('taxsum_' + STYPE + '_' + k).set('html', fmt(mny(tx)));
            // 6 == sbr::TAX_NDS
            if(k == 6) {
                var txt = mny(SCHEMES[STYPE][k][1]*100) + ' % от бюджета + налоги';
                $('taxper_' + STYPE + '_' + k).set('html', txt);
            } else {
                //$('taxper_' + STYPE + '_' + k).set('html', mny(SCHEMES[STYPE][k][1]*100));
                $('taxper_' + STYPE + '_' + k).set('html', mny(emp_tax*100));
            }
            $('taxrow_' + STYPE + '_' + k).removeClass('b-tax__level_hide');
        }
        $('sch_' + STYPE + '_f').set('html', fmt(mny(cost_total)));
        
        rr = this.totalcostChange(cost);
        if (st && rr) {
            st.popup(cost);
        }
    },
    
    checkStagesSum: function() {
        var cost_sum = 0;
        for (i = 0; i < this.stages.length; i++) {
            st_cost = this.stages[i].getCost();
            var el = this.stages[i].element.getElement(this.stages[i].options.selector.cost_input);
            if(st_cost < this.norisk.options.mincost && st_cost != '') {
                //console.debug('red');
                el.getParent().addClass('b-combo__input_error');
            } else {
                el.getParent().removeClass('b-combo__input_error');
            }
            cost_sum += st_cost;  
        }
        
        if( ( this.norisk.options.reztype == 'UABYKZ' || this.norisk.options.ereztype == 'UABYKZ' ) && cost_sum > this.norisk.options.maxcost) { 
            for (i = 0; i < this.stages.length; i++) {
                var el = this.stages[i].element.getElement(this.stages[i].options.selector.cost_input);
                el.getParent().addClass('b-combo__input_error'); 
            }
        }
        if(sbr.options.emp_form_type == 1 && this.norisk.options.reztype == 'UABYKZ_FIZ' && this.norisk.options.ereztype == 'UABYKZ' && cost_sum > this.norisk.options.maxcost_fiz) {
            for (i = 0; i < this.stages.length; i++) {
                var el = this.stages[i].element.getElement(this.stages[i].options.selector.cost_input);
                el.getParent().addClass('b-combo__input_error');
            }
        }
    },
    
    recalcStages: function(show_pp) {
        show_pp = !show_pp;
        var cost = null,
            SCHEMES = this.norisk.options.schemes,
            STYPE = this.norisk.options.scheme_type
            tax = SCHEMES[STYPE]['t'][0],
            stages_cost = this.getCost(),
            new_cost = this.element.getElement(this.options.selector.cost_input).get('value');
            
        if (show_pp && $('stage-popup')) {
            $('stage-popup').retrieve('inst').close();
        }
        
        cost_total = v2f(new_cost/tax);
        
        var diff = 0;
        if (stages_cost > 0) {
            diff = (cost_total - stages_cost)/stages_cost;
        }
        
        //console.log([cost_total, stages_cost].join(' | '));
        
        for(k in SCHEMES[STYPE]) {
            if (!$('taxrow_' + STYPE + '_' + k)) continue;
            
            tx = mny(cost_total * SCHEMES[STYPE][k][0]);
            
            $('taxsum_' + STYPE + '_' + k).set('html', fmt(mny(tx)));
            
            // 6 == sbr::TAX_NDS
            if(k == 6) {
                var txt = mny(SCHEMES[STYPE][k][1]*100) + ' % от бюджета + налоги';
                $('taxper_' + STYPE + '_' + k).set('html', txt);
            } else {
                $('taxper_' + STYPE + '_' + k).set('html', mny(SCHEMES[STYPE][k][1]*100));
            }
        }
        $('sch_' + STYPE + '_f').set('html', fmt(mny(cost_total)));
        
        
        mp = stages_cost == 0 ? 1/this.stages.length : 0;
        for (i = 0; i < this.stages.length; i++) {
            st_cost = this.stages[i].getCost();
            diff = st_cost/stages_cost;
            if (mp) {
                diff = mp;
            }
            
            this.stages[i].costChange(0, mny(cost_total * diff));
        }
        
        if (show_pp)
            this.popup();
    },
    
    popup: function() {
        par = this.element.getElement(this.options.selector.cost_input).getParent('.b-tax__level');
        
        var rows = [];
        this.stages.each(function(stage) {
            rows.push({title: stage.getName(), value: stage.getCost(), err: (stage.getCost() < 100)});
        });
        if(this.is_null_value) return;
        new Norisk.Popup('form-popup', rows, par, {
            'header': 'Бюджеты этапов пересчитаны',
            'mincost': this.norisk.options.mincost,
            'onValueChange': function(el, st_id, pp) {
                if (!this.stages[st_id]) return;
                
                this.stages[st_id].setCost(el.get('value'));
                this.stages[st_id].fireEvent('costChange');
                
            }.bind(this)
        });
    },
    
    hidePopups: function() {
        if ($('stage-popup')) {
            $('stage-popup').retrieve('inst').close();
        }
        if ($('form-popup')) {
            $('form-popup').retrieve('inst').close();
        }
    },
    
    recalcPopups: function() {
        if ($('stage-popup')) {
            $('stage-popup').getElement('input[type=text]').set('value', this.element.getElement('input[name=cost_total]').get('value'));
        }
    },
    
    setErrors: function() {
        errs = this.norisk.options.errors;
        if (!Object.getLength(errs)) return;
        
        var q = [];
        Object.each(errs, function(v, k) {
            if (k != 'stages') {
                q.push(['[name=', k, ']'].join('')); 
            } else {
                Object.each(v, function(vv, i) {
                    Object.each(v[i], function(vvv, kk) {
                        q.push(['[name="stages[', i, '][', kk, ']"]'].join(''));
                    });
                });
            }
        });
        
        var scrolled = false; // была ли 
        setTimeout(function() {
            $$(q.join(',')).each(function(el){
                var parent;
                // textarea оборачивается в дополнительный div, по-этому простой getParent() не работает
                if (el.get('tag') === 'textarea') {
                    parent = el.getParent('.b-textarea');
                    parent.addClass('b-textarea_error');
                } else {
                    parent = el.getParent();
                    parent.addClass('b-combo__input_error');
                }
                // прокрутка к ошибке
                if (scrolled === false) {
                    scrolled = true;
                    JSScroll(parent);
                }
            });
        }, 1000);
    }
});

Norisk.Stage = new Class({
    Implements: [Options, Events],
    
    options: {
        'selector':   {
            'close_stage':          'a.close-block',
            'name_input':           'input[tmpname=name]',
            'work_time':            'input[tmpname=work_time]',
            'cost_input':           'input[tmpname=cost]',
            
            'popup':                '.stage-recalc-popup'
        }
    },
    
    initialize: function(el, frm, options) {
        this.setOptions(options);
        
        this.form = frm;
        
        if (!el) {
            el = this.form.stages.getLast();
            if (!el) {
                return false;
            }
            
            var el2 = el.element.clone();
            el2.inject(el.element, 'after');
            
            f = new Element('form');
            f.wraps(el2);
            f.reset();
            el2.inject(f, 'before');
            f.dispose();
            
            el2.getElements('input, textarea').set('value', '');
            el2.getElements('div.b-combo__input, div.b-combo__textarea, div.b-textarea').removeClass('b-combo__input_error').removeClass('b-textarea_error');
            
            el = el2;
        }
        
        this.element = el;
        this.initElements();
        this.fireEvent('create', [this]);
        this.form.stagesCnt = this.form.stagesCnt+1;
        // добавляем формы этапов только после наступления domready
        window.addEvent('domready', function(){
            this.form.norisk.fireEvent('stageAdd', this);
        }.bind(this));
    },
    
    close: function(e) {
        e.preventDefault();
//        console.log(this.form.stages.length);
        if (this.form.stages.length == 1) {
            // reset stage form
            return false;
        }
        
        // добавляем скрытый input чтобы этап удалился на сервере
        var stageID = +this.element.getElement('input[tmpname="id"]').get('value');
        if (stageID) {
            var delInput = new Element('input', {type: 'hidden', name: 'delstages[' + stageID + ']', value: stageID});
            delInput.inject(this.element, 'after');
        }
        
        this.element.dispose();
        this.form.stages.erase(this);
        
        this.form.stages.each(function(el, i) {
            var obj = el.element.getElement('.norisk-stage-header');
            if (obj) 
                obj.set('html', 'Этап {nm}'.substitute({'nm':(i+1)}));
        });
        this.fireEvent('close', this);
        
        if(this.form.element) this.form.checkElements();
        
        // показать или скрыть кнопки "удалить этап"
        if ($$('.norisk-stage-block').length > 1) {
            $$('.close-block').removeClass('b-button_hide');
        } else {
            $$('.close-block').addClass('b-button_hide');
        }
    },
    
    reset: function() {
        this.element.getElements('input, textarea').set('value', '');
    },
    
    initElements: function() {
        this.renameFields();
        
        if (this.element.getElement('.norisk-stage-header'))
            this.element.getElement('.norisk-stage-header').set('html', 'Этап {nm}'.substitute({'nm': (this.form.stages.length+1)}));
        
        // закрытие формы этапа
        var hnd = this.element.getElement(this.options.selector.close_stage);
        if (hnd)
            hnd.addEvent('click', this.close.bind(this));
        
        // изменение бюджета этапа
        var cost_el = this.element.getElement(this.options.selector.cost_input);
        this.timer_delay = 200;
        this.timer = null;
        this.delay = 0;
        
        cost_el.addEvent('change', this.costChange.bind(this, 1));
        cost_el.addEvent('change', this.costChangeVal.bind(this, 1));
        
        cost_el.addEvent('focus', function(el) {
            this.is_null_value = (el.get('value') == '');
        }.bind(this, cost_el));
        cost_el.addEvent('keyup', function(el) {
            var numeric = /[^0-9\.]/;
            var val = el.value.replace(numeric, '');
            if(val != el.value) {
                el.set('value', val);
            }
            if (this.delay) {
                clearTimeout(this.delay);
            }
            if (el.get('value') == el.retrieve('lastval')) {
                return;
            }
            
            this.delay = this.costChange.delay(Norisk.KEYUP_DELAY, this, 1);
            el.store('lastval', el.get('value'));
            
        }.bind(this, cost_el));
    },
    
    renameFields: function() {
        this.element.getElements('input, textarea').each(function(el) {
            if (el.get('name') && el.get('name').contains('attach') && !el.get('name').contains('[attachedfiles_session]')) return;
            var _name = el.get('tmpname');
            if (!_name) {
                _name = el.get('name');
                el.set('tmpname', _name);
            }

            el.set('name', 'stages[{index}][{nm}]'.substitute({'index' : (this.form.stagesCnt), 'nm': _name}));
        }.bind(this));
    },
    
    costChangeVal: function(fire_evt, sum) {
        var el = this.element.getElement(this.options.selector.cost_input);
        if (sum) {
            el.set('value', sum);
        }

        // если поле не активно, то никаких действий с ним не производим
        if (el.getParent('.b-combo__input').hasClass('b-combo__input_disabled')) {
            return;
        }
        
        var val = el.get('value').replace(',', '.');
        val = isNaN(parseFloat(val)) ? 0 : parseFloat(val);
        if (val < this.form.norisk.options.mincost) {
            el.set('value', this.form.norisk.options.mincost);
            this.fireEvent('costChange', [el, this]);
            el.getParent().removeClass('b-combo__input_error');
        }
    },
    
    costChange: function(fire_evt, sum) {
        var el = this.element.getElement(this.options.selector.cost_input);
        
        if (sum) {
            el.set('value', sum);
        }
        
        var val = el.get('value').replace(',', '.');
        val = isNaN(parseFloat(val)) ? 0 : parseFloat(val);
        
        el.store('lastval', el.get('value'));
        
        if (val < this.form.norisk.options.mincost) {
            el.getParent().addClass('b-combo__input_error');
//            this.popupHide();
//            return false;
        } else if( ( this.form.norisk.options.reztype == 'UABYKZ' || this.form.norisk.options.ereztype == 'UABYKZ' ) && val > this.form.norisk.options.maxcost) { 
            el.getParent().addClass('b-combo__input_error'); 
        } else if(sbr.options.emp_form_type == 1 && this.form.norisk.options.reztype == 'UABYKZ_FIZ' && this.form.norisk.options.ereztype == 'UABYKZ' && val > this.form.norisk.options.maxcost_fiz) {
            el.getParent().addClass('b-combo__input_error');
        } else {
            el.getParent().removeClass('b-combo__input_error');
        }
        
        if (fire_evt) {
            if ($('form-popup')) {
                $('form-popup').retrieve('inst').close();
            }
            //console.log('costChanged');
            this.fireEvent('costChange', [el, this]);
        }
    },
    
    getCost: function() {
        var el = this.element.getElement(this.options.selector.cost_input);
        var val = el.get('value').replace(',', '.');
        val = isNaN(parseFloat(val)) ? 0 : parseFloat(val);
        
        return val;
    },
    
    setCost: function(sum) {
        var el = this.element.getElement(this.options.selector.cost_input);
        if (!el) {
            return false;
        }
        el.set('value', sum);
    },
    
    getName: function() {
        var el = this.element.getElement(this.options.selector.name_input);
        n = el.get('value');
        
        if (n.trim().length == 0) {
            n = this.element.getElement('.norisk-stage-header').get('html');
        }
        
        return n;
    },
    
    popup: function(sum) {
        if(this.is_null_value) {
            this.popupHide();
            return;
        }
        if(this.element.getElement(this.options.selector.cost_input).getParent().hasClass('b-combo__input_disabled') ) {
            return;
        }
        par = this.element.getElement(this.options.selector.cost_input).getParent('table');        
        new Norisk.Popup('stage-popup', [{title: 'Итого к оплате', value: sum, mincost:this.form.norisk.options.mincost}], par, {
            'onValueChange': function(el, st_id, pp) {
                this.form.totalcostChange(el.get('value'));
                this.form.recalcStages(true);
            }.bind(this)
        });
    },
    
    popupHide: function() {
        if ($('stage-popup')) {
            $('stage-popup').retrieve('inst').close();
        }
    }
});


Norisk.Popup = new Class({
    Implements: [Options, Events],
    
    options: {
        'header':       'Бюджет проекта пересчитан',
        'place':        'before',
        
        'selector': {
            'tpl':          '.popup-tpl',
            'header':       '.popup-tpl-header',
            'rows':         '.popup-tpl-rows',
            'row':          '.popup-tpl-row',
            'title':        '.popup-tpl-title',
            'value':        'input',

            'close':        '.b-shadow__icon_close'
        },
        
        'onValueChange': Class.empty
    },
    
    initialize: function(id, rows, rel, options) {
        this.setOptions(options);
        var self = this;
        
        self.tpl = document.getElement(self.options.selector.tpl);
        
        self.container = self.tpl.clone();
        
        if (id && $(id) && $(id).retrieve('inst')) {
            self = $(id).retrieve('inst');
//            self.setValues(rows);
//            return;
        }
        
        if (id) {
            self.container.set('id', id);
        }
        
        self.container.getElement(self.options.selector.header).set('html', self.options.header);
        self.container.getElement(self.options.selector.rows).set('html', '');
        
        var title;
        for (i = 0; i < rows.length; i++) {
            r = self.tpl.getElement(self.options.selector.row).clone();
            title = reformat(rows[i].title, {spacing: 25}).trim() + ':';
            r.getElement(self.options.selector.title).set('html', title);
            r.getElement(self.options.selector.value).set('value', rows[i].value);
            r.getElement(self.options.selector.value).getParent('.b-combo__input').removeClass('b-combo__input_error');
            if (rows[i].err) {
                r.getElement(self.options.selector.value).getParent('.b-combo__input').addClass('b-combo__input_error');
            }
            r.getElement(self.options.selector.value).addEvent('change', function(el, st_id) {
                if(el.value < this.options.mincost) {
                    el.set('value', this.options.mincost);
                    this.fireEvent('valueChange', [el, st_id, this]);
                }
            }.bind(self, [r.getElement(self.options.selector.value), i]));
            
            r.getElement(self.options.selector.value).addEvent('keyup', function(el, st_id) {
                var numeric = /[^0-9\.]/;
                var val = el.value.replace(numeric, '');
                if(val != el.value) {
                    el.set('value', val);
                }
                this.fireEvent('valueChange', [el, st_id, this]);
            }.bind(self, [r.getElement(self.options.selector.value), i]));
            
            r.inject(self.container.getElement(self.options.selector.rows));
        }
        
        self.container.getElement(self.options.selector.close).addEvent('click', self.close.bind(self));
        self.container.getFirst().removeClass('b-shadow_hide');
        self.container.removeClass(self.options.selector.tpl);
        
        self.container.store('inst', self);
        self.container.inject(rel, self.options.place);
        
        return self;
    },
    
    setValues: function(rows) {
        //console.log('setValues');
        
        var tpl = this.container.getElements(this.options.selector.row);
        
        for (i = 0; i < tpl.length; i++) {
            r = tpl[i];
            if (!r) continue;
            r.getElement(this.options.selector.title).set('html', rows[i].title + ':');
            r.getElement(this.options.selector.value).set('value', rows[i].value);
            r.getElement(this.options.selector.value).getParent('.b-combo__input').removeClass('b-combo__input_error');
            if (rows[i].err) {
                r.getElement(this.options.selector.value).getParent('.b-combo__input').addClass('b-combo__input_error');
            }
        }
    },
    
    close: function() {
        this.container.dispose();
        this.fireEvent('close', [this]);
    }
});

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
function ndfl_round(s) {
    var si = Math.floor(s);
    if(s - si > 0.5)
        return Math.ceil(s);
    return si;
}

Norisk.Reserve = new Class({
    Implements: [Events, Options],
    
    options: {
        'action':  '',
        'types': {}
    },
    
    initialize: function(el, options) {
        this.setOptions(options);
        this.form = el;
        this.options.action = this.form.get('action');
    },
    
    send: function() {
        var type = this.form.getElement('input[name=mode_type]:checked');
        if (!type) {
            return false;
        }
        if(type.get('value').trim() == 115) {
           // $('close_res').removeClass('b-shadow_hide');
        } else {
           // $('close_res').addClass('b-shadow_hide');
        }
        this.type = this.options.types[type.get('value').trim()];
        
        if (!$('reserve-popup-box')) {
            return;
        }
        
        new Request.JSON({
            'url': window.location.href,
            'onSuccess': function(resp) {
                if (!resp.success) {
                    alert(resp.error);
                    return;
                }
                
                resp = resp.data;
                
                new Element('input', {
                    'type': 'hidden',
                    'name': 'nickname',
                    'value': resp.nickname
                }).inject(this.form);
                
                new Element('input', {
                    'type': 'hidden',
                    'name': 'order_id',
                    'value': resp.order_id
                }).inject(this.form);

                this.form.set('action', this.options.action);
                this.form.submit();
            }.bind(this)
        }).post(this.form);
        
        return;
    }
});

// для страницы оплаты сделки
// когда скроются все ошибки то активизируем кнопку
window.addEvent('domready', function(){
    var $inlineReqvs = $('inline_reqvs');
    var $sendBtn = $('send_btn');
    if (!$inlineReqvs || !$sendBtn) {
        return;
    }

    var $inputs = $inlineReqvs.getElements('input');
    $inputs.addEvent('focus', function(){
        var $errorInputs = $inlineReqvs.getElements('div.b-combo__input.b-combo__input_error');
        var errorInputsCount = $errorInputs.length;
        if (errorInputsCount === 0) {
            $sendBtn.removeClass('b-button_rectangle_color_disable');
        }
    });
});