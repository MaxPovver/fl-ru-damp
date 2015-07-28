<?php if(is_array($files_attache) && count($files_attache)) foreach($files_attache as $file){?>
<li id="file_<?= (int)$file->id?>" <?if(intval($file->id) == 0) echo "style='background-color:#ffcece'"; // !!!todo Вынести в CSS (skif)?>>
<?php if(intval($file->id) == 0) { ?>
    <strike>
<?php } else { ?>
    <a href="javascript:void(0)" onclick="if(confirm('Вы действительно хотите удалить выбранный файл?')) xajax_DeleteEditFile(<?= (int)$file->id;?>)" title="Удалить"><img src="/images/btn-remove2.png" alt="Удалить"></a>
<?php } // if?>
    <a href="javascript:void(0)" title="<?= trim($file->original_name);?>" class="mime <?= $file->getext($file->original_name);?>"><?= CutFileName(trim($file->original_name), 60, " ... ");?></a>
    <?= intval($file->id) == 0?"</strike>":"";?>
</li>    
<?php } //foreach?>