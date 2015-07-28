window.addEvent('domready', 
function() {
	$$('.b-fon__close').addEvent('click', function() {
        if(this.is_confirm != undefined) {
            if(this.is_confirm == false) {
                this.getParent('.b-fon').setStyle('display','none');
            }
        } else {
            this.getParent('.b-fon').setStyle('display','none');
        }
	});
});







