
window.addEvent('domready', function() {

    try {
        new tcal ({
            'formname': 'frm',
            'controlname': 'sdate',
            'iconId': 'sdate-btn'
        });
        new tcal ({
            'formname': 'frm',
            'controlname': 'edate',
            'iconId': 'edate-btn'
        });
    } catch (err) {}


    if ($('sdate') && $('edate')) {
        $('sdate').addEvent('change', function() {
            re = /([\d]{2})-([\d]{2})-([\d]{4})/;
            ds = this.get('value');

            dt = new Date();
            dt.parse([ds.replace(re, '$3'), ds.replace(re, '$2'), ds.replace(re, '$1')].join('-'));
        });

        $('sdate').fireEvent('change');

        $('edate').addEvent('change', function() {
            re = /([\d]{2})-([\d]{2})-([\d]{4})/;
            ds = this.get('value');

            dt = new Date();
            dt.parse([ds.replace(re, '$3'), ds.replace(re, '$2'), ds.replace(re, '$1')].join('-'));
        });
        $('edate').fireEvent('change');
    }

    if ($('pagescats-all')) {
        $('pagescats-all').addEvent('click', function(){
            if($(this).getElement('input[type=checkbox]').getProperty('checked')){
                $('acnews-cats').getElements('input[type=checkbox]').setProperty('checked', true);
                $('acpages').getElements('input[type=checkbox]').setProperty('checked', true);
            }else{
                $('acnews-cats').getElements('input[type=checkbox]').setProperty('checked', false);
                $('acpages').getElements('input[type=checkbox]').setProperty('checked', false);
            }
        });

    }

    if ( $('acnews-all') ) {
        $('acnews-all').addEvent('click', function(){
            if($(this).getElement('input[type=checkbox]').getProperty('checked')){
                $('acnews-cats').getElements('input[type=checkbox]').setProperty('checked', true);
            }else{
                $('acnews-cats').getElements('input[type=checkbox]').setProperty('checked', false);
            }
        });
    }
    
    if ( $('acnews-cats') ) {
        $('acnews-cats').getElements('.acnews-col .lnk-dot-grey').addEvent('click', function(){
            $(this).getParent('.form').toggleClass('fs-w');
            return false;
        });
        
        $each($('acnews-cats').getElements('li.form'), function(el) {
            if($(el).getElements('input:checked').length) {
                $(el).toggleClass('fs-w');
            }
            if($(el).getElements('input:checked').length == $(el).getElements('.form-in ul input').length) {
                $(el).getElement('.form-in>input').set('checked', true);
            }
        });
        
        $('acnews-cats').getElements('.acnews-col .form-in > .i-chk').addEvent('click', function(){
            if($(this).getProperty('checked')){
                $(this).getParent('.form-in').getElements('.i-chk').setProperty('checked', true);
            }else{
                $(this).getParent('.form-in').getElements('.i-chk').setProperty('checked', false);
            }
        });
    }
});

function ClientForm(show) {
    c = $('form-cadd');
    f = c.getFirst('form');

    c.getElement('button').set('html', 'Добавить клиента');
    f.getElements('input,textarea,button').set('disabled', false);

    f.reset();

    if(show) {
        c.setStyle('display', 'block');
    } else {
        c.setStyle('display', 'none');
    }
}

function AddClient(resp) {
    c = $('form-cadd');
    f = c.getFirst('form');

    try{
        if(!resp) {
            c.getElement('button').set('html', 'Отправка...');
            f.getElements('input,textarea,button').set('disabled', true);

            data = new Hash();
            $each(f.getElements('input,textarea'), function(el) {
                data.set(el.get('name'), el.get('value'));
            });

            xajax_AddClient(data);
            return false;
        }

        if(resp.id) {
            s = $$('select[name=client]')[0];

            newel = new Element('option', {
                'html': resp.name,
                'value': resp.id
            });
            newel.inject(s);
            newel.set('selected', true);

            SetClient(resp.id);

            ClientForm(0);
        }
    } catch(e) {
        alert(e);
    }

    return false;
}

function SetClient(vl) {
    f = document.getElement('form[name=frm]');
    if(!f) return false;

    f.getElement('input[name=client]').set('value', vl);
}

function CheckLogin(resp) {
    el = $('ulogin');
    if(!el) return false;
    if(el.get('value').length == 0) return false;

    c = el.getParent();

    if(!resp) {
        c.getElements('input,button').set('disabled', true);
        xajax_CheckLogin(el.get('value'));

        return false;
    }

    c.getElements('input,button').set('disabled', false);

    if(resp.error) {
        alert(resp.error);
    }

    return false;
}


var SelectedCities = new Hash();

function GetCities(el, resp) {
    if(!resp && el) {
        if($('scity')) $('scity').set('id', '');
        el.set('id', 'scity');
        //        code = el.get('value');
        xajax_GetCities(el.get('value'));
        return false;
    }

    if(resp && $('scity')) {
        html = '<option value=0>Все города</option>';
        $each(resp, function(v) {
            html += '<option value={k}>{v}</option>'.substitute({
                k: v.id,
                v: v.cname
            });
        });

        $('scity').getNext('select').set('html', html);

    }
}


function AddCity(el) {
    el = !el ? $(el) : el;
    if(!el) return false;

    cn = el.getParent('li');
    cl = cn.clone();
    cl.inject(cn.getParent(), 'top');

    btn = cn.getElement('img[src*=btn-add-s');
    btn.set('src', btn.get('src').replace('btn-add-s', 'btn-remove-s'));
    btn.getParent('a').set('onclick', 'return DelCity(this)');
    cl.getElements('select').set('value', '0');

    return false;
}

function DelCity(el) {
    el = !el ? $(el) : el;
    if(!el) return false;

    el.getParent('li').dispose();
    return false;
}

function DelFile(type, obj) {
    el = null;
    switch(type) {
        case 0:
            el = document.getElement('input[name=remove_b_file]');
            break;
        case 1:
            el = document.getElement('input[name=remove_st_file]');
            break;
    }
    if(!el) {
        return false;
    }

    if(confirm("Точно удалить? \nФайл будет удален после сохранения изменений!")) {
        $(el).set('value', 1);
        $(el).getParent('.form-el').getElement('.swf-preview').dispose();
        if($(el).getParent('.form-el').getElement('.flm')) {
            $(el).getParent('.form-el').getElement('.flm').removeClass('flm');
        }
    }

    return false;
}