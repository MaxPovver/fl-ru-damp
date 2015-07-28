<?php

$form_elements = $form->getElements();
$dir = $form->getElement('dir')->getValue();
$dir_col = $form->getElement('dir_col')->getValue();

?>
<form action="." method="get" id="adminFrm">
    <table class="nr-a-tbl" cellspacing="5" style="table-layout:fixed">
        <colgroup>
            <col style="width:120px" />
            <col style="width:120px" />
            <col style="width:120px" />
            <col style="width:120px" />
            <col  />
            <col style="width:90px" />
            <col style="width:60px" />
            <col style="width:60px" />
            <col style="width:60px" />
            <col style="width:100px" />
            <col style="width:120px" />
        </colgroup>
        <?php if($form_elements): ?>
        <thead>
            <tr>
                <?php 
                      foreach($form_elements as $form_element): 
                        if($form_element->getAttrib('data_hide')): 
                            continue; 
                        endif;
                        $idx = $form_element->getName();
                ?>
                <th>
                    <?=$label = $form_element->getLabel()?>
                    <?php if(!empty($label) && !$form_element->getAttrib('data_stop_order')): ?>
                    <a onclick="reserves_admin.changeDir('<?=$idx?>','desc');" href="javascript:void(0);"><img width="11" height="11" alt="v" src="/images/arrow-bottom<?=($dir_col==$idx && $dir=='desc' ? '-a' : '')?>.png" /></a> 
                    <a onclick="reserves_admin.changeDir('<?=$idx?>','asc');" href="javascript:void(0);"><img width="11" height="11" alt="v" src="/images/arrow-top<?=($dir_col==$idx && $dir=='asc' ? '-a' : '')?>.png" /></a>
                    <?php endif; ?>
                </th>
                <?php endforeach; ?>
            </tr>
            <tr class="pd">
                <?php 
                      foreach($form_elements as $form_element): 
                        if($form_element->getAttrib('data_hide')): 
                            continue; 
                        endif;
                ?>
                <td><?=$form_element->render(); ?></td>
                <?php endforeach; ?>
            </tr>
        </thead>
        <?php endif; ?>
        <?php if(!empty($reserves)): ?>
        <tfoot>
            <tr>
                <td colspan="11">
                    <div class="pager">
                        <?=new_paginator(
                                $page, 
                                ceil($page_count / $limit), 
                                10, 
                                "%s?action=index{$params}&page=%d%s") ?>
                    </div>
                </td>
            </tr>
            <?php if ($summary): ?>
            <tr class="nr-a-tbl_tr_summary">
                <td colspan="5"><strong>»ÚÓ„Ó:</strong></td>
                <td class="nr-a-td-sum"><?=$summary?></td>
                <td colspan="5">&nbsp;</td>
            </tr>
            <?php endif; ?>
        </tfoot>
        
        <tbody>
                <?php foreach($reserves as $reserve): ?>
            <tr class="nr-a-tbl_tr">
                <td><?=$reserve->getSrcDate()?></td>
                <td><?=$reserve->getReserveDate()?></td>
                <td><?=$reserve->getCompleteDate()?></td>
                <td><a target="_blank" href="?action=details&num=<?=$reserve->getSrcId()?>"><?=$reserve->getReserveNum()?></a></td>
                <td>
                    <a target="_blank" href="<?=$reserve->getTypeUrl()?>">
                        <?=$reserve->getSrcTitle()?>
                    </a>
                </td>
                <td class="nr-a-td-sum"><?=$reserve->getSrcPrice()?></td>
                <td class="nr-a-td-val" style="text-align: center;">
                    <?php if($reserve->isStatusReserved() || $reserve->isInvoice()): ?>
                        <?php if(!$reserve->isReserveByService()): ?>¡Õ<?php else: ?>ﬂ <?php endif; ?>
                    <?php else: ?>
                        &mdash; 
                    <?php endif; ?>
                </td>
                <td class="nr-a-td-val" style="text-align: center;">
                    <?php if($reserve->isStatusPayPayed()): ?>
                        <?php if(!$reserve->isPayoutByService()): ?>¡Õ<?php else: ?>ﬂ <?php endif; ?>
                    <?php else: ?>
                        &mdash; 
                    <?php endif; ?>
                </td>
                <td class="nr-a-td-val" style="text-align: center;">
                    <?php if($reserve->isStatusBackPayed()): ?>
                        <?php if(!$reserve->isReserveByService() && $reserve->isInvoice()): ?>¡Õ<?php else: ?>ﬂ <?php endif; ?>
                    <?php else: ?>
                        &mdash; 
                    <?php endif; ?>                    
                </td>                
                <td><?=$reserve->getSrcDays()?></td>
                <td><?=$reserve->getStatusText()?></td>
            </tr>
                <?php endforeach; ?>
        </tbody>
        <?php endif; ?>
    </table>
<?php 
    foreach($form_elements as $form_element):
        if($form_element->getAttrib('data_hide')):
            echo $form_element->render();
        endif;
    endforeach; 
?>
</form>