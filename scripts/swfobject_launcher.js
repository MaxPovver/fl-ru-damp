window.addEvent('domready', function() {
    if (typeof videoPlayers == 'object') {
        for(var index in videoPlayers) { 
            console.log(index); 
            window["videoPlayers"][index]();
        }        
    }
});



