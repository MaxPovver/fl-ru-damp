<?php
// Задача https://beta.free-lance.ru/mantis/view.php?id=28240

ini_set('display_errors', 'on');

require_once("../classes/config.php");
require_once("../classes/opinions.php");

// Месяц для подсчета статистики
$date_start = '2014-09-01 00:00:00'; 
$date_end = '2014-10-01 00:00:00';

$sql = "
    select 
        uid, 
        login,
        (select count(*) from projects as p2 where p2.user_id = users.uid and p2.kind=4 and p2.post_date >= '$date_start' and p2.post_date < '$date_end') as vacancies_count,
        (select count(*) from projects as p3 where p3.user_id = users.uid and p3.payed = 0 and p3.kind=4 and p3.post_date >= '$date_start' and p3.post_date < '$date_end') as vacancies_not_payed,
        reg_date,
        (select 0-sum(ammount) from account_operations where billing_id=(select id from account where uid = users.uid) and ammount < 0) as amount
    from users where uid in (
        select user_id from projects where payed = 0 and kind=4 and post_date >= '$date_start' and post_date < '$date_end'
    ) 
    order by vacancies_not_payed desc
";

$count = 0;
$rows = $DB->rows($sql);
foreach ($rows as $row) {
    ++$count;

    $o = opinions::GetCounts($row['uid'], array('total'));
    
    $o_sum = intval($o['total']['p']) + intval($o['total']['m']);

    echo '"'.join('";"', $row).'"';
    echo ";\"{$o['total']['p']}\";\"{$o['total']['m']}\";";
        
    echo "\n";
}