<tr>
    <td class="first">Стоимость работы, в т.ч. НДС</td>
    <td></td>
    <td>&mdash;</td>
    <td class="last"><?= sbr_meta::view_cost($cost, $cost_sys, false) ?></td>
</tr>
<? foreach ($taxes as $k => $tax) { ?>
    <tr>
        <td class="first"><?= $tax['name'] ?></td>
        <td></td>
        <td><?= $tax['percent'] ?>%</td>
        <td class="last"><?= sbr_meta::view_cost($tax['tax_cost'], $cost_sys, false) ?></td>
    </tr>
<? } ?>
<tr class="last">
    <td class="first"><strong>Итого вы получите</strong></td>
    <td></td>
    <td>&mdash;</td>
    <td class="last"><strong><?= sbr_meta::view_cost($total_sum, $cost_sys) ?></strong></td>
</tr>