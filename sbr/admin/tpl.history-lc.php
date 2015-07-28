<?php if($history) {?>
    <table class="nr-a-opinions" cellspacing="0" style="width: 100%">
        <thead>
            <tr>
                <th>#ID</th>
                <th>Статус</th>
                <th>Дата</th>
                <th>Сделка</th>
                <th>Цель</th>
                <th>Сумма</th>
                <th>Номер</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($history as $pskb_lc) { ?>
            <tr class="<?= (++$i % 2 == 0 ? 'even' : 'odd') ?>">
                <td><?= $pskb_lc->id; ?></td>
                <td><?= $pskb_lc->state?></td>
                <td><?= $pskb_lc->date ? date('d.m.Y H:i', strtotime($pskb_lc->date)) : ' - ' ?></td>
                <td><?= $pskb_lc->uid?></td>
                <td><?= $pskb_lc->target == true ? 'Исполнитель' : 'Заказчик'?></td>
                <td><?= $pskb_lc->sum?></td>
                <td><?= $pskb_lc->account?></td>
            </tr>
            <?php } ?>  
        </tbody>
    </table>
<?php } else { ?>
Истории нет
<?php }//else?>

