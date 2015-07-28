<?php require_once('xajax.php'); ?>
<script type="text/javascript">
    var mA = null;
    window.addEvent('domready', function() {
        mA = new mAttach2(document.getElementById('files_block'), 10, {p:'btn-add', m:'btn-del', nv:true});
    });
    
    function htmldecode(string) {  
        string = string.toString();  
        string = string.replace(new RegExp("&amp;","g"),"&");  
        return string;  
    } 
    
    function updateSection(){
        var sid = $('section_id').get('value');
        var sname = $('new_section_name').get('value');
        if(!sname){
            alert('Вы должны указать имя раздела');
            return false;
        }
        if(!sid) xajax_AddSection(sname);
        else xajax_UpdateSection(sid, sname);
        updateSectionName(sid, sname);
    }
    
    function updateSectionName(id,name){
        if(!name) return false;
        var lst = $$('a[name=section_name_'+id+']');       
        for(var i = 0; i < lst.length; i++) lst[i].innerHTML = name;
    }
    
    function checkSel(){
        var sel = getSelectedDocsCB();
        var all = getAllDocsCB();
        $('cbm_top').checked = $('cbm_bottom').checked = sel.length == all.length;
    }

    function showAddDocsForm(clear, edit){
        if(typeof clear == 'undefined') clear = true;
        if(typeof edit == 'undefined') edit = false;
        if(clear){
            $('dosc_id_f').set('value','');
            $('frm_name').set('value','');
            $('frm_desc').set('value','');
            $('frm_section').set('value','');
            $('form_files_added').set('html','');
            $('doc_save_btn').set('value','Добавить');
        }
        if(!edit) {
            $('docs_admin').toggleClass('docs-admin-add-show').removeClass('docs-admin-groups-show'); 
        } else {
            $('docs_admin').removeClass('docs-admin-groups-show').addClass('docs-admin-add-show');
        }
    }

    function showSectionEdit(mode,sid){
        if(typeof mode == 'undefined') mode = 'new';
        if(typeof sid == 'undefined') sid = '';
        $('section_id').set('value',sid)
        $('docs-group-new').removeClass('dgn-hide');
        if(mode == 'new'){
            $('add_section_btn').set('value','Добавить');
        }else{
            var name = trim($('section_name_'+sid).get('value'));
            name = name.split('&shy;').join('');
            $('new_section_name').set('value',htmldecode(name));
            $('add_section_btn').set('value','Изменить');
        }
    }

    function hideSectionEdit(){
        $('section_id').set('value','');
        $('docs-group-new').addClass('dgn-hide');
        $('add_section_btn').set('value','Добавить');
        $('new_section_name').set('value','');
    }

    function setDockChecked(value){
        var list = getAllDocsCB();
        if(list){
            for(var i = 0; i < list.length; i++){
                list[i].set('checked',value);
            }
        }
        $('cbm_top').checked = $('cbm_bottom').checked = value;
    }

    function deleteSelectedDocs(){
        var list = getSelectedDocsCB();
        var ids = '';
        if(list){
            for(var i = 0; i < list.length; i++){
                ids += '|'+list[i].get('value');
            }
        }
        ids = trim(ids,'|');
        if(confirm('Вы действительно хотите удалить выбранные элементы?')){
            xajax_DeleteDoc(ids);
        }
    }

    function moveSelectedDocs(section){
        var list = getSelectedDocsCB();
        var ids = '';
        if(list){
            for(var i = 0; i < list.length; i++){
                ids += '|'+list[i].get('value');
            }
        }
        ids = trim(ids,'|');
        if(confirm('Вы действительно хотите переместить выбранные элементы?')){
            xajax_MoveDocs(ids, section);
        }
    }

    function getSelectedDocsCB(){
        return $('admin_docs').getElements('input[type=checkbox][name^=doc_]:checked');
    }

    function getAllDocsCB(){
        return $('admin_docs').getElements('input[type=checkbox][name^=doc_]');
    }
    
    function getSelectedSectionsCB(){
        return $('admin_sections').getElements('input[type=checkbox][name^=section_]:checked');
    }
    
    function deleteSections(){
        var list = getSelectedSectionsCB();
        if(list.length < 1) return false;
        if(!confirm("Удалить выбранные разделы?")) return false;
        var ids = '';
        for(var i = 0; i < list.length; i++){
            ids += list[i].value+':';
        }
        xajax_DeleteSections(ids);
    }

    function trim (str, charlist) {
        var whitespace, l = 0, i = 0;
        str += '';

        if (!charlist) {
            // default list
            whitespace = " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
        } else {
            // preg_quote custom list
            charlist += '';
            whitespace = charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
        }

        l = str.length;
        for (i = 0; i < l; i++) {
            if (whitespace.indexOf(str.charAt(i)) === -1) {
                str = str.substring(i);
                break;
            }
        }

        l = str.length;
        for (i = l - 1; i >= 0; i--) {
            if (whitespace.indexOf(str.charAt(i)) === -1) {
                str = str.substring(0, i + 1);
                break;
            }
        }

        return whitespace.indexOf(str.charAt(0)) === -1 ? str : '';
    }
    
    
    <?php if ($error_add_file !== false) {?>
    document.window.onload = function() {
        showAddDocsForm(false);  
        <?php if($_POST['action_form'] == 'edit') {?>  
        $('doc_save_btn').set('value','Сохранить');
        <?php } //if?>
    }
    <?php }?>
</script>
<h2>Услуги</h2>
<div class="docs-block c">
    <div class="docs-content c">
        <div class="docs-cnt">
            <h3>Администрирование / Шаблоны документов</h3>

            <? include('search_form.php'); ?>

            <div class="docs-admin" id="docs_admin">
                <b class="b1"></b>
                <b class="b2"></b>
                <div class="docs-admin-h">
                    <a href="javascript:void(0);" onclick="showAddDocsForm(); $('action_form').set('value', 'add'); return false;" class="lnk-dot-green">Добавить документ</a>&nbsp;&nbsp;
                    <a href="javascript:void(0);" onclick="$(this).getParent('div.docs-admin').removeClass('docs-admin-add-show').toggleClass('docs-admin-groups-show');" class="lnk-dot-grey">Управление разделами</a>
                </div>
                <div class="docs-add">
                    <form id="docs_form" action="/service/docs/admin/" method="post" enctype="multipart/form-data" onsubmit="if(!document.getElementById('frm_name').value) {alert('Вы должны указать имя файла');return false;}">
                        <input type="hidden" name="action_form" id="action_form" value="<?= ($_POST['action_form']?htmlspecialchars($_POST['action_form']):"add")?>">
                        <input type="hidden" name="dosc_id_f" id="dosc_id_f" value="<?=intval($_POST['dosc_id_f'])?>"/>
                        <?php if($error_add_file !== false && count($files_attache) > 0) {?>
                        <span id="attach_files_box" style="display:none">
                        <?php foreach($files_attache as $file){ if((int)$file->id == 0) continue;?>
                            <input type="hidden" name="attach_files_id[<?= (int)$file->id?>]" value="<?= (int)$file->id?>" id="attach_files_<?= (int)$file->id?>">
                        <?php } //foreach;?>
                        </span>
                        <?php }// if?>
                        <div class="form-block first">
                            <div class="form-el">
                                <label>Название:</label>
                                <input type="text" name="name" value="<?=htmlspecialchars((stripslashes($_POST['name'])), ENT_QUOTES)?>" class="docs-add-title"  id="frm_name">
                            </div>
                            <div class="form-el">
                                <label>Раздел:</label>
                                <select name="section" class="docs-add-group" id="frm_section">
                                    <?php foreach ($sections as $section) {
                                    ?>
                                        <option value="<?= $section['id']; ?>" <?php if($_POST['section']==$section['id']) print('selected')?>><?= htmlspecialchars($section['name']); ?></option>
                                    <? } // foreach ?>
                                </select>
                            </div>
                            <div class="form-el">
                                <label>Описание:</label>
                                <textarea name="desc" rows="10" cols="20" class="docs-add-txt" style="width:664px" id="frm_desc"><?= htmlspecialchars(stripslashes($_POST['desc']));?></textarea>
                            </div>
                            <div class="form-files" style="float:none">
                                
                                <div class="cl-form-files c">
                                    <ul class="form-files-added" id="form_files_added">
                                        <?php if (count($files)>0) require_once("admin_docs_uploaded_files.php");?>
                                        <?php if ($error_add_file !== false) require_once("admin_add_docs_uploaded_files.php");?>
                                    </ul>
                                    <ul class="form-files-list" style="width:500px">
                                        <li  id="files_block" class="c"><input name="attach[]" type="file" size="23" class="i-file"></li>
                                    </ul>
                                    <div class="form-files-inf">
                                        <strong class="form-files-max">Максимальный размер файла: 10 Мб.</strong>
														<!--Картинка: gif, jpg, png. 600x1000 пикселей, 300 Кб.<br>
														Файл: swf, zip, rar, xls, doc, rtf, pdf, psd, mp3.-->
                                    </div>
                                    <?php if ($error_add_file !== false) print("<div id='docs_files_error'>".view_error($error_add_file)."</div>")?>
                                </div>
                            </div>

                        </div>
                        <div class="form-btns">
                            <input type="submit" id="doc_save_btn" value="Добавить" class="i-btn i-bold">&nbsp; <a href="javascript:void(0);" onclick="$(this).getParent('div.docs-admin').toggleClass('docs-admin-add-show');" class="lnk-dot-666">Отменить</a>
                        </div>
                    </form>
                </div>
                <div class="docs-groups">
                    <div id="docs-group-new" class="docs-group-new dgn-hide">
                        <a href="javascript:void(0);" onclick="showSectionEdit('new'); return false;" class="lnk-dot-666 lnk-group-new">Новый раздел</a>
                        <div class="dgn">
                            <input type="hidden" name="section_id" id="section_id" value=""/>
                            <input id="new_section_name" type="text" value="" class="i-txt"> <input id="add_section_btn" onclick="updateSection(); return false;" type="button" value="Добавить" class="i-btn i-bold">&nbsp; <a href="javascript:void(0);" onclick="hideSectionEdit(); return false;" class="lnk-dot-666">Отменить</a>
                        </div>
                    </div>
                    <div id="admin_sections">
                        <? include('admin_sections.php'); ?>
                    </div>
                                <div class="dg-btns"><input type="button" onclick="deleteSections()" value="Удалить" class="i-btn"/></div>
                            </div>
                            <b class="b2"></b>
                            <b class="b1"></b>
                        </div>

            <div id="admin_docs">
                <? include('admin_docs.php'); ?>
            </div>
        </div>
    </div>
</div>
