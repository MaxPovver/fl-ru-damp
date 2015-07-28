<? foreach ($doc_act as $doc) { ?> 
<tr class="<?= (++$i % 2 == 0 ? 'even' : 'odd') ?>" id="doc_<?= $doc['id']; ?>">
    <td>
        <i class="b-icon b-icon_attach_pdf"></i> <a class="b-layout__link" href="<?= WDCPREFIX; ?>/<?=$doc['file_path'] . $doc['file_name']?>" target="_blank"><?= $doc['name']?></a>, <?= ConvertBtoMB($doc['file_size'])?>
    </td>
    <td><?= date('d.m.Y H:i', strtotime($doc['publ_time'])) ?></td>
    <td><?= $doc['id'] == $doc['first_doc_id'] ? "<strong style='color:red'>удаленный" : "<strong style='color:green'>действующий"?></strong></td>
    <td text-align="right">
        <input type="submit" id="sbmt_add_<?= $doc['id']; ?>" onclick="xajax_aRecreateDocLC('<?= $doc['id']?>', '<?= $sbr->frl_id; ?>', <?= $stage->id; ?>);" value="Создать новый" <?= ( $doc['id'] == $doc['first_doc_id'] ? "disabled" : "" ) ?>>
        <? if( $doc['id'] == $doc['first_doc_id'] ) { ?>
        <input type="submit" id="sbmt_del_<?= $doc['id']; ?>" onclick="xajax_aRecreateDocLC('<?= $doc['id']?>', '<?= $sbr->frl_id; ?>', <?= $stage->id; ?>, 'remove');" value="Восстановить">
        <? }//if?>
    </td>
</tr>
<? } //foreach?>