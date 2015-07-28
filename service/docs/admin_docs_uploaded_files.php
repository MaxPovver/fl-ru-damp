<?php if(is_array($files) && count($files) ) foreach($files as $file){?>
<li id="file_<?= $file['id']?>">
    <span class="ffa-sort">
        <img onclick="xajax_FileMoveTo(<?= $file['id'];?>,'up'); return false;" style="cursor:pointer" src="/images/arrow2-top<?= $file['is_first'] == 't' ? '-a' : '';?>.png" alt="">
        <img onclick="xajax_FileMoveTo(<?= $file['id'];?>,'down'); return false;" style="cursor:pointer" src="/images/arrow2-bottom<?= $file['is_last'] == 't' ? '-a' : '';?>.png" alt="">
    </span>
    <a href="javascript:void(0)" onclick="if(confirm('Вы действительно хотите удалить выбранный файл?')) xajax_DeleteFile(<?= $file['id'];?>)" title="Удалить"><img src="/images/btn-remove2.png" alt="Удалить"></a><a href="" title="<?= trim($file['file_name']);?>" class="mime <?= $file['ico_class'];?>"><?= CutFileName(trim($file['file_name']), 60, " ... ");?></a>
</li>
<?php } ?>