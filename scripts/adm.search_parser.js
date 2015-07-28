
window.addEvent('domready', function() {
    
});


function deleteQuery(el) {
    el = $(el);
    if (!el) return false;
    
    rw = el.getParent('tr');
    frw = $('deleteFrm');
    frw.setStyle('display', 'table-row');
    frw.inject(rw, 'after');
    
    f = frw.getElement('form');
    
    f.getElement('input[name=query]').set('value', rw.get('id').replace('query', ''));
    f.getElement('input[name=word]').set('value', rw.getChildren()[1].childNodes[0].nodeValue.trim());
    
    return false;
}

function deleteQueryOnly(el) {
    el = $(el);
    if (!el) return false;
    
    f = el.getParent('form');
    id = f.getElement('input[name=query]').get('value');
    document.location.href = './?action=remove&id='+id;
    
    return false;
}