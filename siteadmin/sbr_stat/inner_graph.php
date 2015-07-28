<?
if (!defined('IS_SITE_ADMIN') || !(hasPermissions('sbr') || hasPermissions('tmppayments')) ) {
    header_location_exit('/404.php');
}
?>
<div id="sbr_stat_graph">
<? foreach ($sbr_data_types as $type => $params) { ?>
    
<?
$index = $params['index'];
$value = $params['value'];
$name = $params['name'];
$unit = $params['unit'];
$descr = $params['descr'];
$color = $params['color'];
$gr_type = $params['type'];

$data = $sbr_data[$index];

// если нечего выводить, то и график не рисуем
if (count($data) === 0) {
    continue;
}
?>
    
<div id="sbr_graph_<?= $type ?>"></div>
<script type="text/javascript">
    window.addEvent('domready', function() {
        var chart,
            categories = [],
            data = [],
            series;
        <? // разделяем на платежные системы, если надо
        if ($gr_type === 'ps') { ?>
            series = [
                {data: [], name: 'WebMoney'},
                {data: [], name: 'Яндекс.Деньги'},
                {data: [], name: 'Пластик'},
                {data: [], name: 'Банк'}
            ];
            <? if ($index === 3) { ?>
            series.push({data: [], name: 'Веб Кошелек'});
            series.push({data: [], name: 'руб.'});
            <? } ?>
        <? } elseif ($gr_type === 'normal' || $gr_type === 'avg_perc') { ?>
            series = [
                {data: [], name: '<?= $descr ?>', color: '<?= $color ?>'}
            ];
        <? } ?>
        <? foreach ($dates as $dateKey => $date) { ?>
            categories.push('<?= $date ?>');
            <? if ($gr_type === 'ps') { ?>
                series[0].data.push(<?= round((float)$data[$dateKey][$value . '_wmr'], 2) ?>);
                series[1].data.push(<?= round((float)$data[$dateKey][$value . '_yd'], 2) ?>);
                series[2].data.push(<?= round((float)$data[$dateKey][$value . '_card'], 2) ?>);
                series[3].data.push(<?= round((float)$data[$dateKey][$value . '_bank'], 2) ?>);
                <? if ($index === 3) { ?>
                series[4].data.push(<?= round((float)$data[$dateKey][$value . '_ww'], 2) ?>);
                series[5].data.push(<?= round(_bill((float)$data[$dateKey][$value . '_fm']), 2) ?>);
                <? } ?>
            <? } elseif ($gr_type === 'normal') { ?>
                series[0].data.push(<?= round((float)$data[$dateKey][$value], 2) ?>);
            <? } elseif ($gr_type === 'avg_perc') { ?>
                series[0].data.push(<?= round((float)($sbr_data[5][$dateKey]['avg'] + $sbr_data[6][$dateKey]['avg']), 2) ?>);
            <? } ?>
        <? } ?>
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'sbr_graph_<?= $type ?>',
                type: 'column'
            },
            title: {
                text: '<?= $name ?>'
            },
            subtitle: {
                text: '<?= $periodText ?>'
            },
            xAxis: {
                categories: categories,
                title: {
                    text: 'Дата',
                    style: {
                        color: '<?= $color ?>'
                    }
                }
            },
            yAxis: [
                {   min: 0,
                    allowDecimals: false,
                    labels: {
                        formatter: function() {
                            return this.value +' <?= $unit ?>';
                        },
                        style: {
                            color: '<?= $color ?>'
                        }
                    },
                    title: {
                        text: '<?= $descr ?>',
                        style: {
                            color: '<?= $color ?>'
                        }
                    }    
                }
            ],
            tooltip: {
                formatter: function() {
                    return '' + this.y + ' <?= $unit ?>' + '<br>' + this.series.name;
                },
                positioner: function (boxWidth, boxHeight, p) {                    
                    return {x: p.barX + boxWidth / 2, y: p.plotY};
                }
            },
            legend: {
                enabled: <?= $gr_type === 'ps' ? 'true' : 'false' ?>
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
            series: series
        });
    });
</script>

<? } ?>

</div>
