<?
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr_meta.php");
    
session_start();
get_uid();
	
if ( !(hasPermissions('sbr') || hasPermissions('sbr_finance') || hasPermissions('tmppayments')) ) {
    header_location_exit("/404.php");
}
$css_file = array('moderation.css','/css/block/b-menu/_tabs/b-menu_tabs.css','nav.css');
$js_file = array('highcharts/mootools-adapter.js', 'highcharts/highcharts.js');

$show_results = __paramInit('bool', 'show_results', null, false);
$tab = __paramInit('string', 'tab', null, 'graph');


if ($show_results) {
    $period_param = __paramInit('string', 'period', null, 'today');
    $custom_period_from = __paramInit('string', 'custom_period_from', null, '');
    $custom_period_to = __paramInit('string', 'custom_period_to', null, '');
    $akkr_param = __paramInit('bool', 'akkr', null, false);
    $pdrd_param = __paramInit('bool', 'pdrd', null, false);

    $period = array();
    if ($period_param === 'today') {
        $period[0] = date("Y-m-d 00:00:00", time());
        $period[1] = date("Y-m-d 23:59:59", time());
        $groupBy = 'day';
        $periodText = "за сегодня";
    } elseif ($period_param === 'week') {
        $period[0] = date("Y-m-d 00:00:00", time() - (3600 * 24 * 7));
        $period[1] = date("Y-m-d 23:59:59", time());
        $groupBy = 'day';
        $periodText = "за прошедшую неделю";
    } elseif ($period_param === 'month') {
        $period[0] = date("Y-m-d 00:00:00", time() - (3600 * 24 * 30));
        $period[1] = date("Y-m-d 23:59:59", time());
        $groupBy = 'day';
        $periodText = "за прошедший месяц";
    } elseif ($period_param === 'year') {
        $period[0] = date("Y-m-d 00:00:00", time() - (3600 * 24 * 365));
        $period[1] = date("Y-m-d 23:59:59", time());
        $groupBy = 'month';
        $periodText = "за прошедший год (статистика по месяцам)";
    } elseif ($period_param === 'alltime') {
        $groupBy = 'year';
        $periodText = "за все время (статистика по годам)";
    } elseif ($period_param === 'custom') {
        $from = explode('.', $custom_period_from);
        $to = explode('.', $custom_period_to);
        $fromTime = mktime(0, 0, 0, $from[1], $from[0], $from[2]);
        $toTime = mktime(0, 0, 0, $to[1], $to[0], $to[2]);
        
        // дата начала периода не должна быть позже конца периода
        if ($fromTime > $toTime) {
            
            $tmpTime = $fromTime;
            $fromTime = $toTime;
            $toTime = $tmpTime;
            
            $custom_period_tmp = $custom_period_from;
            $custom_period_from = $custom_period_to;
            $custom_period_to = $custom_period_tmp;
            
        }
            
        $period[0] = date("Y-m-d 00:00:00", $fromTime);
        $period[1] = date("Y-m-d 23:59:59", $toTime);
        $groupBy = 'day';
        $periodText = "с $custom_period_from по $custom_period_to";
    }
    
    
    $sbr_meta = new sbr_meta();
    $sbr_data = $sbr_meta->getSbrStats($period, $groupBy, $akkr_param, $pdrd_param);
    
    // подготавливаем для таблицы, так как для некоторых графиков могут быть пропущены даты
    // заодно и дату приведем к нормальному формату
    $dates = array();
    foreach ($sbr_data as $type => $data) {
        foreach ($data as $date => $values) {
            if (!$dates[$date]) {
                if ($groupBy === 'day') {
                    $dates[$date] = substr($date, 6, 2) . '.' . substr($date, 4, 2);
                } elseif ($groupBy === 'month') {
                    $dates[$date] = substr($date, 4, 2) . '.' . substr($date, 0, 4);
                } elseif ($groupBy === 'year') {
                    $dates[$date] = substr($date, 0, 4);
                }
            }
        }
    }
    ksort($dates);
    
    // перечень графиков
    // ключ 'name'  - название графика
    // ключ 'index' - ключ из массива полученного из функции sbr_meta::getStatsDaysLC
    // ключ 'value' - какие данные использовать
    // ключ 'unit'  - единица измерения
    // ключ 'descr' - описание
    // ключ 'color' - цвет графика
    // ключ 'type'  - 'normal' - обычный график, 'ps' - подробные данные по каждой платежной системе, 'avg_perc' - средний процент от сделки
    $sbr_data_types = array (
        array('name' => 'Количество заведенных СБР',                            'index' => 1, 'value' => 'cnt', 'unit' => 'СБР', 'descr' => 'Количество',   'color' => '#89A54E', 'type' => 'normal'),
        array('name' => 'Общее количество покрытых СБР',                        'index' => 2, 'value' => 'cnt', 'unit' => 'СБР', 'descr' => 'Количество',   'color' => '#89A54E', 'type' => 'normal'),
        array('name' => 'Количество покрытых СБР для каждой ПС',                'index' => 2, 'value' => 'cnt', 'unit' => 'СБР', 'descr' => 'Количество',   'color' => '#89A54E', 'type' => 'ps'),
        array('name' => 'Общее количество открытых СБР',                        'index' => 3, 'value' => 'cnt', 'unit' => 'СБР', 'descr' => 'Количество',   'color' => '#89A54E', 'type' => 'normal'),
        array('name' => 'Количество открытых СБР для каждой ПС',                'index' => 3, 'value' => 'cnt', 'unit' => 'СБР', 'descr' => 'Количество',   'color' => '#89A54E', 'type' => 'ps'),
        array('name' => 'Количество возвратов по СБР (вернули работодателю)',   'index' => 4, 'value' => 'cnt', 'unit' => 'СБР', 'descr' => 'Количество',   'color' => '#89A54E', 'type' => 'normal'),
        array('name' => 'Сумма покрытых, общая',                                'index' => 2, 'value' => 'sum', 'unit' => 'руб', 'descr' => 'Сумма',        'color' => '#4572A7', 'type' => 'normal'),
        array('name' => 'Сумма покрытых для каждой ПС',                         'index' => 2, 'value' => 'sum', 'unit' => 'руб', 'descr' => 'Сумма',        'color' => '#4572A7', 'type' => 'ps'),
        array('name' => 'Средний бюджет покрытых сделок',                       'index' => 2, 'value' => 'avg', 'unit' => 'руб', 'descr' => 'Сумма',        'color' => '#4572A7', 'type' => 'normal'),
        array('name' => 'Сумма открытия, общая (исполнителю переведено)',       'index' => 3, 'value' => 'sum', 'unit' => 'руб', 'descr' => 'Сумма',        'color' => '#4572A7', 'type' => 'normal'),
        array('name' => 'Сумма открытия для каждой ПС (исполнителю переведено)','index' => 3, 'value' => 'sum', 'unit' => 'руб', 'descr' => 'Сумма',        'color' => '#4572A7', 'type' => 'ps'),
        array('name' => 'Сумма возвратов (вернули работодателю)',               'index' => 4, 'value' => 'sum', 'unit' => 'руб', 'descr' => 'Сумма',        'color' => '#4572A7', 'type' => 'normal'),
        array('name' => 'Процент от Работодателей',                             'index' => 5, 'value' => 'sum', 'unit' => 'руб', 'descr' => 'Сумма',        'color' => '#4572A7', 'type' => 'normal'),
        array('name' => 'Процент от Исполнителей',                              'index' => 6, 'value' => 'sum', 'unit' => 'руб', 'descr' => 'Сумма',        'color' => '#4572A7', 'type' => 'normal'),
        array('name' => 'Средний процент',                                      'index' => 5, 'value' => 'avg', 'unit' => 'руб', 'descr' => 'Cумма',        'color' => '#4572A7', 'type' => 'avg_perc'),
    );
    
    // перечень строк в таблице
    $sbr_table_types = array (
        array('type' => 1, 'value' => 'cnt',        'name' => 'Кол-во заведенных'),
        array('type' => 2, 'value' => 'cnt',        'name' => 'Кол-во покрытых, всего'),
        array('type' => 2, 'value' => 'cnt_wmr',    'name' => 'Кол-во покрытых, WebMoney'),
        array('type' => 2, 'value' => 'cnt_yd',     'name' => 'Кол-во покрытых, Я.Деньги'),
        array('type' => 2, 'value' => 'cnt_card',   'name' => 'Кол-во покрытых, пластик'),
        array('type' => 2, 'value' => 'cnt_bank',   'name' => 'Кол-во покрытых, банк'),
        array('type' => 2, 'value' => 'cnt_ww',     'name' => 'Кол-во покрытых, веб-кошел.'),
        array('type' => 2, 'value' => 'cnt_fm',     'name' => 'Кол-во покрытых, руб.'),
        array('type' => 3, 'value' => 'cnt',        'name' => 'Кол-во открытых, всего'),
        array('type' => 3, 'value' => 'cnt_wmr',    'name' => 'Кол-во открытых, WebMoney'),
        array('type' => 3, 'value' => 'cnt_yd',     'name' => 'Кол-во открытых, Я.Деньги'),
        array('type' => 3, 'value' => 'cnt_card',   'name' => 'Кол-во открытых, пластик'),
        array('type' => 3, 'value' => 'cnt_bank',   'name' => 'Кол-во открытых, банк'),
        array('type' => 3, 'value' => 'cnt_ww',     'name' => 'Кол-во открытых, веб-кошел.'),
        array('type' => 3, 'value' => 'cnt_fm',     'name' => 'Кол-во открытых, руб.'),
        array('type' => 4, 'value' => 'cnt',        'name' => 'Кол-во возвратов'),
        array('type' => 2, 'value' => 'sum',        'name' => 'Сумма покрытия, общая'),
        array('type' => 2, 'value' => 'sum_wmr',    'name' => 'Сумма покрытия, WebMoney'),
        array('type' => 2, 'value' => 'sum_yd',     'name' => 'Сумма покрытия, Я.Деньги'),
        array('type' => 2, 'value' => 'sum_card',   'name' => 'Сумма покрытия, пластик'),
        array('type' => 2, 'value' => 'sum_bank',   'name' => 'Сумма покрытия, банк'),
        array('type' => 2, 'value' => 'sum_ww',     'name' => 'Сумма покрытия, веб-кошелек'),
        array('type' => 2, 'value' => 'sum_fm',     'name' => 'Сумма покрытия, руб.'),
        array('type' => 3, 'value' => 'sum',        'name' => 'Сумма открытия, общая'),
        array('type' => 3, 'value' => 'sum_wmr',    'name' => 'Сумма открытия, WebMoney'),
        array('type' => 3, 'value' => 'sum_yd',     'name' => 'Сумма открытия, Я.Деньги'),
        array('type' => 3, 'value' => 'sum_card',   'name' => 'Сумма открытия, пластик'),
        array('type' => 3, 'value' => 'sum_bank',   'name' => 'Сумма открытия, банк'),
        array('type' => 3, 'value' => 'sum_ww',     'name' => 'Сумма открытия, веб-кошелек'),
        array('type' => 3, 'value' => 'sum_fm',     'name' => 'Сумма открытия, руб.'),
        array('type' => 4, 'value' => 'sum',        'name' => 'Сумма возвратов'),
        array('type' => 5, 'value' => 'sum',        'name' => 'Процент от раб-лей'),
        array('type' => 6, 'value' => 'sum',        'name' => 'Процент от исп-лей'),
    );
    
}

$content = "../content.php";


$inner_page = "inner_index.php";

$header = $rpath."header.php";
$footer = $rpath."footer.html";

$stretch_page = true;

include ($rpath."template.php");

?>
