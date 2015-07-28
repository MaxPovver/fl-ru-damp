/* 
 * Инициализация блока загрузки файлов при их наличии
 * Должен запускаться после /css/block/b-menu/b-menu.js
 */

var uploader;

function initUploader() {
    //Инициализация uploader
    if (typeof window.uploaderSet != "undefined") {
        for (var i = 0; i < window.uploaderSet.length; i++) {
            var qq_conf = window.uploaderSet[i];
            
            uploader = new qq.UploaderFactory({
                template: qq_conf.template,
                fileTemplate: qq_conf.fileTemplate,
                popupTemplate: qq_conf.popupTemplate,
                WDCPREFIX: qq_conf.WDCPREFIX,
                umask: qq_conf.umask                
            });
            
            if (qq_conf.elements.length) {
                for (var e = 0; e < qq_conf.elements.length; e++) {
                    uploader.create(qq_conf.elements[e][0], qq_conf.elements[e][1]);        
                }
            }
            window.uploaderSet.splice(0, 1);
        }        
    }
}

window.addEvent('domready', function() {
    initUploader();
});
