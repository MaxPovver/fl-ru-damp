function warning(num){
	//alert(navigator.userAgent);
	if (navigator.userAgent.match(/msie/i) && !navigator.userAgent.match(/opera/i)){
		results = window.showModalDialog("/warning.php", num, "dialogHeight: 145px; dialogWidth: 230px; edge: Raised; center: Yes; help: No; resizable: No; scroll: No; status: No;")
	} else {
		results = confirm("Вы уверены?");
	}
	return(results);
}

function warning_str(str){
	//alert(navigator.userAgent);
	if (navigator.userAgent.match(/msie/i) && !navigator.userAgent.match(/opera/i)){
		results = window.showModalDialog("/warning_str.php", str, "dialogHeight: 145px; dialogWidth: 230px; edge: Raised; center: Yes; help: No; resizable: No; scroll: No; status: No;")
	} else {
		results = confirm(str);
	}
	return(results);
}

function add(from, to){
    $('fav_title').set('text', 'У вас в избранных');
    $('fav_title').getParent().onclick = function() {
        del(from, to);
        return false;
    }
    xajax_AddInTeam(from, to);
    return false;
}

function del(from, to) {
    $('fav_title').set('text', 'Добавить в избранные');
    $('fav_title').getParent().onclick = function() {
        add(from, to);
        return false;
    }
    xajax_DelInTeam(from, to);
    return false;
}

// deprecated
function checkext(f) { try 
{
  if(!f) return true;
  var aext = '.gif.jpg.jpeg.png.bmp.swf.zip.rar.txt.xls.xlsx.doc.docx.rtf.pdf.psd.mp3.avi.wma.flv.ogg.mp4.wav.wmv.3gp.mpg.mpeg';
  var ext = f.split(/\.+/).pop().toLowerCase();
  if(!(r = (aext.search('.'+ext)>=0)))
    alert("Неверный тип файла.");
  return r;
} catch(e) { return true; } }

// file ext is in aext
function specificExt( fileName, aext ) {
    try {
        if ( !fileName || !aext ) return true;
        var ext  = fileName.split(/\.+/).pop().toLowerCase();
        var bRet = false;
        for ( var i=0; i < aext.length; i++ ) {
            if ( aext[i] == ext ) {
                bRet = true;
                break;
            }
        }
        if ( !bRet ) {
            alert('Неверный тип файла');
        }
        return bRet;
    }
    catch ( e ) {
        return true;
    }
}

// file ext is not in aext
function allowedExt( fileName ) {
    try {
        if ( !fileName ) return true;
        
        var ext  = fileName.split(/\.+/).pop().toLowerCase();
        var bRet = true;
        var aext = ["ade", "adp", "bat", "chm", "cmd", "com", "cpl", "exe",
            "hta", "ins", "isp", "jse", "lib", "mde", "msc", "msp",
            "mst", "pif", "scr", "sct", "shb", "sys", "vb", "vbe",
            "vbs", "vxd", "wsc", "wsf", "wsh"];
        
        for ( i=0; i < aext.length; i++ ) {
            if ( aext[i] == ext ) {
                alert('Выбранный тип файла запрещен к загрузке');
                bRet = false;
                break;
            }
        }
        
        return bRet;
    } 
    catch(e) {
        return true;
    }
}
/*
* Возвращает false если размер файла равен нулю
**/
function filesizeNotNull( inputFile ) {
    try {
        if ( inputFile.files.length && inputFile.files[0].size == 0 ) {
            alert('Нельзя загрузить пустой файл');
            return false;
        }
        return true;
    } 
    catch(e) {
        return true;
    }
}