<table class="b-layout__table b-layout__table_width_840 b-layout__table_center">
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_padbot_30 b-layout__one_width_50ps b-layout__one_center">
            <div class="b-layout__txt b-layout__txt_color_6db335 b-layout__txt_fontsize_46 b-layout__txt_bold"><?= number_format($promoStats['count'], 0, '', ' ') ?></div>
            <div class="b-layout__txt">Успешные сделки за год</div>
        </td>
        <td class="b-layout__one b-layout__one_padbot_30 b-layout__one_width_50ps b-layout__one_center">
            <div class="b-layout__txt b-layout__txt_color_6db335 b-layout__txt_fontsize_46 b-layout__txt_bold"><?= number_format(($roleStr === 'frl' ? $promoStats['frl_sum'] : $promoStats['emp_sum']), 0, '', ' ') ?> руб.</div>
            <div class="b-layout__txt"><?= $roleStr === 'frl' ? 'Сумма, выплаченная фрилансерам' : 'Общая сумма проведенных сделок' ?></div>
        </td>
    </tr>
</table>