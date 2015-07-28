<div class="form nr-budjet-details">
    <b class="b1"></b>
    <b class="b2"></b>
    <div class="form-in">
        <table>
            <caption>
              <?=sbr::$scheme_types[$curr_sbr->scheme_type][0]?>
              <?=(isset($curr_sbr->v_data['scheme_type']) && $curr_sbr->scheme_type != $curr_sbr->v_data['scheme_type'] ? '<span class="rarr">&nbsp;&nbsp;&nbsp;&larr;&nbsp;&nbsp;&nbsp;</span><span class="date-old">'.sbr::$scheme_types[$curr_sbr->v_data['scheme_type']][0].'</span>' : '')?>
            </caption>
            <col width="610" />
            <col width="70" />
            <col width="145" />
            <tbody>
                <tr>
                    <th>Общая сумма «Безопасной Сделки»</th>
                    <td>&mdash;</td>
                    <td class="last"><?=sbr_meta::view_cost($tmp_ts, $curr_sbr->cost_sys, false)?></td>
                </tr>
                <? foreach($sh_info as $tid=>$tax) { ?>
                    <tr>
                        <th><?=$tax['name']?></th>
                        <td><?=$tax['percent']?></td>
                        <td class="last"><?=$tax['cost']?></td>
                    </tr>
                <? } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th><strong><?=($curr_sbr->isFrl() ? 'К перечислению Фрилансеру' : 'Итого исполнитель получит')?></strong></th>
                    <td>&mdash;</td>
                    <td class="last"><strong><?=sbr_meta::view_cost($total_sum, $curr_sbr->cost_sys, false)?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <b class="b2"></b>
    <b class="b1"></b>
</div>
