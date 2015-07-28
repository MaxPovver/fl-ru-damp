<?php


?>

<form method="post" action="" enctype="multipart/form-data">
    <div class="b-form b-form_padbot_20">
        <input name="file" type="file" />
        <button type="submit" class="b-button_margleft_15">
            Загрузить реестр
        </button>
    </div>
</form>

<?php if(!empty($files)): ?>
<h3>Загруженные реестры</h3>
<br/>
<table class="nr-a-tbl" cellspacing="5" style="table-layout:fixed">
    <colgroup>
        <col style="width:100px" />
        <col />
        <col />
        <col />
    </colgroup>
    <thead>
        <tr>
            <th>Дата</th>
            <th>Файл</th>
        </tr>
    </thead>
    
    <tfoot>
        <tr>
            <td colspan="2">
                <div class="pager">
                    <?=new_paginator(
                            $page, 
                            ceil($page_count / $limit), 
                            10, 
                            "%s?action=factura&page=%d%s") ?>
                </div>
            </td>
        </tr>
    </tfoot>

    <tbody>
            <?php foreach($files as $file): ?>
        <tr class="nr-a-tbl_tr">
            <td>
                <?=date('d.m.Y H:i', strtotime($file['modified']))?>
            </td>
            <td>
                <a href="<?=WDCPREFIX . $file['path'] . $file['fname']?>">
                    <?=$file['original_name']?>
                </a>
            </td>
        </tr>
            <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>