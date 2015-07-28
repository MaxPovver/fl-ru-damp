<table class="docs-tbl">
<thead>
    <tr>
        <td class="chk">
            <input type="checkbox" id="cbm_top" onclick="setDockChecked(this.checked)">
        </td>
        <td colspan="4" class="thf">
            <input onclick="deleteSelectedDocs()" type="button" value="Удалить" class="btn-remove i-btn">
            <div id="sel_s1_parent" style="float:left; margin-right:2px; margin-top:1px">
            <select onclick="$('sel_s2').set('value',this.value)" id="sel_s1">
                <? if(is_array($sections)) foreach($sections as $section) { ?>
                                <option value="<?= $section['id'];?>"><?= htmlspecialchars($section['name']);?></option>
                <?} ?>
            </select>
            </div>
            <input onclick="moveSelectedDocs($('sel_s1').get('value'))" type="button" value="Перенести" class="i-btn">
        </td>
    </tr>
</thead>
<tfoot>
    <tr>
        <td class="chk">
            <input type="checkbox" id="cbm_bottom" onclick="setDockChecked(this.checked)">
        </td>
        <td colspan="4" class="thf">
            <input onclick="deleteSelectedDocs()" type="button" value="Удалить" class="btn-remove i-btn">
            <div id="sel_s2_parent" style="float:left; margin-right:2px; margin-top:1px">
            <select id="sel_s2" onclick="$('sel_s1').set('value',this.value)"><? if(is_array($sections))  foreach($sections as $section) { ?>
                                <option value="<?= $section['id'];?>"><?= htmlspecialchars($section['name']);?></option>
                <?} ?></select>
            </div>
            <input onclick="moveSelectedDocs($('sel_s2').get('value'))" type="button" value="Перенести" class="i-btn">
        </td>
    </tr>
</tfoot>
<tbody>

    <?php if (is_array($docs) && count($docs)) {
 ?>
    
<?php foreach ($docs as $doc) { ?>
            <tr id="doc_line_<?= $doc['id']; ?>">
<? include ('admin_docs_line.php'); ?>
        </tr>
<?php } ?>


<?php } ?>




</tbody>
</table>