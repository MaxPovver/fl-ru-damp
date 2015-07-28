<?php


?>
<?php if($list->valid()): ?>
<form method="post" action="">
<table class="nr-a-tbl b-layout__table" cellspacing="5" style="table-layout:fixed">
    <colgroup>
        <?php if(false): ?>
        <col style="width:20px" />
        <?php endif; ?>
        <col style="width: 100px;" />
        <col style="width: 200px;"/>
        <col />
        <col style="width: 100px;" />
        <col style="width: 80px;" />
    </colgroup>
    <thead>
        <tr>
            <?php if(false): ?>            
            <th></th>
            <?php endif; ?>            
            <th><strong>Создание</strong></th>
            <th><strong>Период</strong></th>
            <th><strong>В архиве</strong></th>
            <th><strong>Статус</strong></th>
            <th class="b-layout__td_center"><strong>Попыток</strong></th>
        </tr>
    </thead>
    
    <tfoot>
        <tr>
            <td colspan="5">
                <div class="pager">
                    <?=new_paginator(
                            $page, 
                            ceil($page_count / $limit), 
                            10, 
                            "%s?action=archive&page=%d%s") ?>
                </div>
            </td>
        </tr>
    </tfoot>

    <tbody>
            <?php foreach($list as $archive): ?>
        <tr class="nr-a-tbl_tr">
            <?php if(false): ?>
            <td><input type="checkbox" name="archive[]" value="<?=$archive->getId()?>" /></td> 
            <?php endif; ?>
            <td><?=$archive->getDate()?></td>
            <td>
                <?php if($archive->isStatusSuccess()): ?>
                <a href="<?=$archive->getArchiveLink()?>">
                    <?=$archive->getName()?>
                </a>
                <?php else: ?>
                <?=$archive->getName()?>
                <?php endif; ?>
            </td>
            <td><?=$archive->getParams()?></td>
            <td class="b-layout__td">
                <span class="b-label b-label_fs_11 b-label_<?=$archive->getStatusColor()?>">
                    <?=$archive->getStatusText()?>
                </span>
                <?php if($archive->isStatusError()): ?>
                <a href="javascript:void(0);" title="<?=$archive->getTechMessage()?>"><span class="b-icon b-icon_sbr_rattent b-icon_margleft_5"></span></span></a>
                <?php endif; ?>
            </td>
            <td class="b-layout__td_center">
                <?=$archive->getTryCount()?>
            </td>
        </tr>
            <?php endforeach; ?>
    </tbody>
</table>
<?php if(false): ?>
<div class="b-layout b-layout_padtop_20">
    <a id="__change_letters" href="?action=archive" class="b-button b-button_flat b-button_flat_green">
        Отметить что письма по документам отправлены
    </a>
</div>
<?php endif; ?>    
</form>    
<?php endif; ?>