<?
if (!defined('IS_SITE_ADMIN') || !(hasPermissions('sbr') || hasPermissions('tmppayments')) ) {
    header_location_exit('/404.php');
}
$totalArray = array();
?>

<div id="sbr_stat_table">
    

    <table class="b-layout__table b-layout__table_width_full b-layout__table_bord_ccc" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_bordbot_ccc b-layout__left_pad_5_10 b-layout__left_width_185 b-layout__left_bordright_ccc">
                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold">Параметр</div>
            </td>

            <td class="b-layout__middle" rowspan="<?= count($sbr_table_types) + 2 ?>">
                <div id="sbr_stat_table_data" class="b-layout b-layout_width_510 b-layout_overflow_auto">
                    <table class="b-layout__table b-layout__table_width_full b-layout__table_bordbot_ccc" cellpadding="0" cellspacing="0" border="0">
                        <tr class="b-layout__tr">
                            <? foreach ($dates as $date => $formatDate) { ?>
                            <td class="b-layout__one b-layout__one_pad_5_10 b-layout__one_width_30 b-layout__one_center b-layout__one_bordbot_ccc">
                                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold"><?= $formatDate ?></div>
                            </td>
                            <? } ?>
                        </tr>
                        <? foreach ($sbr_table_types as $table_type_data) {
                            $current_type_data = $sbr_data[$table_type_data['type']];
                            $current_type_value = $table_type_data['value'];
                            $totalArray[$table_type_data['type'] . $table_type_data['value']] = 0;
                            ?>
                        <tr class="b-layout__tr">
                            <? foreach ($dates as $date => $formatDate) { ?>
                            <td class="b-layout__one b-layout__one_pad_5_10 b-layout__one_width_30 b-layout__one_center">
                                <? $value = $current_type_data[$date][$current_type_value];
                                $totalArray[$table_type_data['type'] . $table_type_data['value']] += $value;
                                ?>
                                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold"><?= $value ? round($value, 2) : '&nbsp;' ?></div>
                            </td>
                            <? } ?>
                        </tr>
                        <? } ?>
                    </table>
                </div>
            </td>

            <td class="b-layout__right b-layout__right_bordbot_ccc b-layout__right_pad_5_10 b-layout__right_right b-layout__right_width_60 b-layout__right_bordleft_ccc ">
                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold">Всего</div>
            </td>
        </tr>
        <? foreach ($sbr_table_types as $table_type_data) {
            //$current_type = $table_type_data['type'];
            //$current_type_value = $table_type_data['value'];
            ?>
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_pad_5_10 b-layout__left_width_185 b-layout__left_bordright_ccc">
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold"><?= $table_type_data['name'] ?></div>
                </td>
                <td class="b-layout__right b-layout__right_pad_5_10 b-layout__right_right b-layout__right_width_60 b-layout__right_bordleft_ccc ">
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold">
                        <?//= round($sbr_data_total[$current_type][$current_type_value], 2) ?>
                        <?= round($totalArray[$table_type_data['type'] . $table_type_data['value']], 2) ?>
                    </div>
                </td>
            </tr>
        <? } ?>
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_pad_10 b-layout__left_width_185 b-layout__left_bordright_ccc b-layout__left_bordtop_ccc"></td>
            <td class="b-layout__right b-layout__right_pad_10 b-layout__right_right b-layout__right_width_60 b-layout__right_bordleft_ccc b-layout__right_bordtop_ccc"></td>
        </tr>
    </table>
    
</div>