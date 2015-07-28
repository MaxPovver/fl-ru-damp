<?php if (count($data)): ?>
<table>
    <thead>
        <tr>
            <th class="b-layout__td b-layout__td_pad_10"><strong>Промо-код</strong></th>
            <th class="b-layout__td b-layout__td_pad_10"><strong>Даты начала и окончания действия</strong></th>
            <th class="b-layout__td b-layout__td_pad_10"><strong>Размер скидки, %</strong></th>
            <th class="b-layout__td b-layout__td_pad_10"><strong>Размер скидки, руб.</strong></th>
            <th class="b-layout__td b-layout__td_pad_10"><strong>Остаток</strong></th>
            <th class="b-layout__td b-layout__td_pad_10"><strong>Услуги</strong></th>
        </tr>
    </thead>
    <tbody>
<?php foreach($data as $code): ?>
    <tr>
        <td class="b-layout__td b-layout__td_pad_10">
            <strong><?=$code['code']?></strong><br/>
            <a href="/siteadmin/promo_codes/?action=edit&id=<?=$code['id']?>">Изменить</a> или
            <a href="/siteadmin/promo_codes/?action=delete&id=<?=$code['id']?>">Удалить</a>
        </td>
        <td class="b-layout__td b-layout__td_pad_10"><?=dateFormat('d.m.Y', $code['date_start'])?> &mdash; <?=dateFormat('d.m.Y', $code['date_end'])?></td>
        <td class="b-layout__td b-layout__td_pad_10"><?=$code['discount_percent']?></td>
        <td class="b-layout__td b-layout__td_pad_10"><?=$code['discount_price']?></td>
        <td class="b-layout__td b-layout__td_pad_10"><?=$code['count'] - $code['count_used']?> (из <?=$code['count']?>)</td>
        <td class="b-layout__td b-layout__td_pad_10"><?=$code['service_string']?></td>
    </tr>
<?php endforeach; ?>
    <tbody>
</table>
<?php else: ?>
<p>Ни одного кода не найдено</p>
<?php 
endif;

