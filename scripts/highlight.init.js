if(hljs) {
    window.addEvent('domready', function() {
        hljsDomready();
    });
}

function hljsDomready() {
    $$("p.code").each(function(el) {
        cd = new Element('code', {
            'html': el.get('html'),
            'class': el.className.replace('code', '').trim()
        });
        el.set('html', '');
        cd.inject(el, 'after');
        el.wraps(cd);

        pr = new Element('pre');
        pr.inject(el, 'after');
        pr.wraps(cd);

        hljs.highlightBlock(cd, '    ')
    });
}