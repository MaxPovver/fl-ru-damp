
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
    } catch (err) { alert(err); }

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


    if($('tbcr')) {
        hnd = $('tbcr').getElement('div.rsbar');
        lft = hnd.getParent('.tbl-bc-left');
        rgt = hnd.getParent('.tbl-bc-left').getNext('.tbl-bc-right');
        
        d = new Drag(hnd, {
            onStart: function (el) {
                if(!el._lastpos) el._lastpos = el.getPosition();
            },
            onDrag: function(el){
                diff = el._lastpos.x - el.getPosition().x;
                lh = lft.getStyle('width').toInt() + diff;
                rh = rgt.getStyle('width').toInt() - diff;
                
                lft.setStyle('width', lh);
                rgt.setStyle('width', rh);
                el.setStyle('right', 0);
                
                if(lft.getStyle('width').toInt() < el.getStyle('width').toInt()*2 ||
                   rgt.getStyle('width').toInt() < el.getStyle('width').toInt()*3) {
                    this.stop('complete');
                }
            },
            onComplete: function (el) {
                el._lastpos = el.getPosition();
            },
            modifiers: {x: 'right', y: false}
        });
        d.attach();

        $('tbcr').getParent().addEvent('mouseleave', function() {
            d.stop('complete');
        });
    }

});


function GetClientBanners(cid, resp) {
    f = document.getElement('form[name=frm]');
    if(!f) return false;

    if(!resp) {
        f.getElements('select#s_cid,select[name=id]').set('disabled', true);

        xajax_GetClientBanners(cid);

        return false;
    }

    f.getElements('select#s_cid,select[name=id]').set('disabled', false);
    el = f.getElement('select[name=id]');

    el.empty();
    $each(resp, function(row) {
        op = new Element('option', {
           'value': row.id,
           'html': row.name
        });
        op.inject(el);
    });

    return false;
}