/* 
 * вызов XAJAX методов из разных модулей
 *
 * xajax_core.js обязательно должен быть подключен ДО этого файла
 *
 * TODO
 * была идея расширить класс Request от mootools и перетащить в него
 * весь функционал xajax_core.js
 */


Request.XAJAX = new Class({

    Extends: Request,

    options: {
        call: null,
        scope: {}
    },

    send: function(args) {
        if(!this.options.call) return false;

        var url = '/xajax/', func = '';

        tmpcall = this.options.call.split('.');
        url += tmpcall[0] + '.server.php';
        func = tmpcall[1];

        try {
            if (undefined == xajax.config) xajax.config = {};
        } catch (e) {
            if(!xajax) return false;
            
            xajax = {};
            xajax.config = {};
        }

        var _tmpUri = xajax.config.requestURI;

        xajax.config.requestURI = url;
//        xajax.config.statusMessages = false;
//        xajax.config.waitCursor = true;
//        xajax.config.version = "xajax 0.5 rc1";
//        xajax.config.legacy = false;
//        xajax.config.defaultMode = "asynchronous";
//        xajax.config.defaultMethod = "POST";

        xajax.callback.global.onFailure = (function(req) {
            this.fireEvent('onFailure', req);
        }).bind(this);

        xajax.callback.global.onSuccess = (function(req) {
            this.fireEvent('onSuccess', req);
        }).bind(this);

        xajax.request({xjxfun: func}, {parameters: args} );

        xajax.config.requestURI = _tmpUri;

        return this;
    }

});

(function(){

    var methods = {};

    methods['post'] = function(){
        return this.send(arguments);
    };

    Request.XAJAX.implement(methods);

})();