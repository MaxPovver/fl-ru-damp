function viewprojNextLink( url ) {
    a = new Element('a', {href: url});
    var tg = $(document.body).getElement('td#proj_pict img');
    if(tg) a.wraps(tg);
}