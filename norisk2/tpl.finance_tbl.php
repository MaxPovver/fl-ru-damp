<table class="finance-tbl <?=($tbl['rez_type'] ? 'rez--itm'.$tbl['rez_type'] : '')?>"<?=($tbl['rez_type'] && !($tbl['rez_type'] & $rez_type) ? ' style="display:none"' : '')?>>
    <caption>
        <?=$tbl_caption?>: 
    </caption>
    <thead><tr><th colspan="3"><?=$tbl_header?></th></tr></thead>
    <tbody>
        <? $pos=0; foreach($tbl as $key=>$field) { if (!$field['name']) continue; $pos++;?>
            <?php if($tbl_subheader['pos'] == $pos) {?>
            <tr><td colspan="3"><?= ( $tbl_subheader['anchor'] ? "<a name='{$tbl_subheader['anchor']}'></a>" : "" ) ?><?= $tbl_subheader['title']?>:</td></tr>
            <?php }//if?>
            <tr class="<?=($field['rez_type'] ? 'rez--itm'.$field['rez_type'] : '')?>"<?=($field['rez_type'] && !($field['rez_type'] & $rez_type) ? ' style="display:none"' : '')?>>
                <th style="padding-right:15px"><label for="ft<?=$form_type?>-<?=$key?>"><?=$field['name']?>:</label></th>
                <td class="f-tbl-i"><div class="form-el">
                    <span class="form-input<?=($field['rez_required'] ? ' rez--req'.$field['rez_required'] : '')?>
                    <?=($field['rez_required'] && ($field['rez_required'] & $rez_type) ? ' form-imp' : '')?>"><input type="text" id="ft<?=$form_type?>-<?=$key?>" name="ft<?=$form_type?>[<?=$key?>]" value="<?=html_attr($reqvs[$form_type][$key])?>" maxlength="<?=$field['maxlength']?>" /></span>
                    <div class="tip" style="left:100%"></div>
                  </div>
                </td>
                <td><?=$field['example']?></td>
            </tr>
        <? } ?>
    </tbody>
</table>
        
