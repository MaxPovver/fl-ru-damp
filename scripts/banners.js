window.addEvent('domready', function() {
    var banner_block = $('banner_right_side');
    if (banner_block) {
        var adriver_sid = banner_block.get('data-sid');
        new adriver("banner_right_side", {sid:adriver_sid, bt:52, bn:2, keyword: CUSTOM_TARGET });
    }
    
    var banner_top = $('banner_top');
    if (banner_top) {
        var adriver_sid = banner_top.get('data-sid');
        new adriver("banner_top", {sid:adriver_sid, bt:52, bn:1, keyword: CUSTOM_TARGET });
    }
});
