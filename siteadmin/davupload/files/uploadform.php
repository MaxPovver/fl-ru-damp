<script type="text/javascript">
var sended = false;
function E(i) {return document.getElementById(i);}
/**
 * Проверка полей и отправка формы 
 **/
function diasbleForm() {
    if ( sended ) {
        return false;
    }
    if ( E( "document" ).value.length == 0 ) {
        E( "uploadError" ).innerHTML = "Необходимо выбрать файл";
        return false;
    }
    if ( E( "document" ).value.length > 64 ) {
        E( "uploadError" ).innerHTML = "Слишком длинное имя файла. (max 64 )";
        return false;
    }
    var path = E( "path" ).value.replace(/^\s+/, '').replace(/\s+$/, '');
    if ( path.length == 0 ) {
        E( "path" ).value = '';
        E( "path" ).focus();
        E( "pathError" ).innerHTML = "Необходимо ввести путь к каталогу";
        return false;
    }
    
    if ( ! ( window.parent.allowedExt( E( "document" ).value ) ) ) {
        E( "uploadError" ).innerHTML = "Недопустимый к загрузке тип файла.";
        return false;
    }
    sended = true;
    return true;
}
window.onload=function() {
    window.parent.about_docs_showInfo('<?=$info?>','<?=$name?>','<?=$link?>','<?=$old_link?>', '<?=$rename_name?>');
}
</script>
<div style="font-family:arial;">
<div style="text-align:center; font-size:9pt;">
<span style="color:red"><?=$error_msg ?></span>
<span style="color:green"><?=$status_msg ?></span>
</div>
<form method="POST" action="/siteadmin/davupload/?mode=files&view=form" enctype="multipart/form-data" >
<div style="font-size: 14px" id="uploadform">
        <div style="padding:5px">Добавить файл</div>
        <div style="clear: both">&nbsp;</div>
        <div style="float:left; width:20%">
            <label for="filename" style="padding-right:7px; width:100%">Название файла (необязательно)</label>
        </div>
        <div style="float:right; width:80%">
            <input id="filename" type="text" name="filename" style="padding-right:7px; width:100%;" maxlength="59"/><br/>
            <span style="color:red;font-size:8pt" id="filenameError"></span>
            <span style="color:green;fomnt-size:8pt;display:none" id="updateLegend"></span>
        </div>
        <div style="clear: both">&nbsp;</div>
        
        <!-- path -->
        <div style="float:left; width:20%">
            <label for="path" style="padding-right:7px; width:100%">Каталог на dav сервере *</label>
        </div>
        <div style="float:right; width:80%">
            <input id="path" type="text" name="path" style="padding-right:7px; width:100%;" value="<?=($path ? $path : "about/documents") ?>"/><br/>
            <span style="color:red;font-size:8pt" id="pathError"><?=$error_folder ?></span>
            <span style="color:green;fomnt-size:8pt;display:none" id="pathLegend"></span>
        </div>
        <div style="clear: both">&nbsp;</div>
        <!-- /path -->
        
        <div style="text-align:right">
            * <input type="file" name="document" id="document" onclick="E( 'uploadError' ).innerHTML = ''"/><br/>
            <span style="color:red;font-size:8pt" id="uploadError"></span>
        </div>
        <div style="clear: both">&nbsp;</div>
        <div style="text-align:right">
            <input type="submit" id="upload" value="Добавить" onclick="return diasbleForm();"/>
            <input type="hidden" id="action" name="action" value="upload" />
            <input type="hidden"  name="u_token_key" value="<?=$_SESSION['rand'] ?>" />
            <input type="hidden" name="fid" id="fid" />
            <input type="hidden" name="rid" id="rid" />
        </div>
        <div style="clear: both">&nbsp;</div>
</div>
</form>
</div>
