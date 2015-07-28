<?php

?>
    <table class="nr-a-tbl" cellspacing="5" style="table-layout:fixed">
        <colgroup>
            <col style="width:100px" />
            <col style="width:90px" />
            <col />
            <col />
            <col />
            <col style="width:120px" />
            <col style="width:120px" />
        </colgroup>
        <thead>
            <tr>
                <th>№ дог.</th>
                <th>Бюджет (руб)</th>
                <th>Название</th>
                <th>Заказчик</th>
                <th>Исполнитель</th>
                <th>Дата резерва</th>
                <th>Дата выплаты</th>
            </tr>
        </thead>
        <?php if(!empty($reserves)): ?>
        <tfoot>
            <tr>
                <td colspan="7">
                    <div class="pager">
                        <?=new_paginator(
                                $page, 
                                ceil($page_count / $limit), 
                                10, 
                                "%s?action=frod&page=%d%s") ?>
                    </div>
                </td>
            </tr>
        </tfoot>
        
        <tbody>
                <?php foreach($reserves as $reserve): ?>
            <tr class="nr-a-tbl_tr">
                <td><a target="_blank" href="?action=details&num=<?=$reserve->getSrcId()?>"><?=$reserve->getReserveNum()?></a></td>
                <td class="nr-a-td-sum"><?=$reserve->getPriceByKey('reserve_price', false)?></td>
                <td>
                    <a target="_blank" href="<?=$reserve->getTypeUrl()?>">
                        <?=$reserve->getSrcTitle()?>
                    </a>                    
                </td>
                <td>
                    <a target="_blank" href="/users/<?=$reserve->getReserveDataByKey('emp_login')?>">
                        <?=$reserve->getReserveDataByKey('emp_login')?>
                    </a>
                </td>
                <td>
                    <a target="_blank" href="/users/<?=$reserve->getReserveDataByKey('frl_login')?>">
                        <?=$reserve->getReserveDataByKey('frl_login')?>
                    </a>
                </td>
                <td><?=$reserve->getDateByKey('date_reserve')?></td>
                <td><?=$reserve->getDateByKey('date_payout')?></td>
            </tr>
                <?php endforeach; ?>
        </tbody>
        <?php endif; ?>
    </table>