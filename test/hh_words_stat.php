<?php
/**
 * Скрипт собирает статистику по поисковым словам запросов с HeadHunter
 * 
 * @example
 * php hh_words_stat.php /var/tmp/external.log as1 90000 [/var/tmp/ext-hh.sql]
 * 
 * Где: 
 * /var/tmp/external.log = $file_path - путь до файла который мы будем разбирать
 * as1                   = $table_sfx - суфикс таблицы в которую пойдет запись слов
 * 90000                 = $stack_max - максимальное количество выборки слов
 * /var/tmp/ext-hh.sql   = $name_sql_file_path - Дополнительный параметр, куда сохранить скрипт SQL (можно опустить)
 * 
 */
setlocale(LC_ALL, 'ru_RU.CP1251');
if(!isset($argv[1])) {
    print "\n";
    print "ERROR\n";
    print "Example: php hh_words_stat.php /var/tmp/external.log as1 90000\n";
    exit;
}

$file_path = $argv[1]; 
$table_sfx = $argv[2];
$stack_max = intval($argv[3]);

if($argv[4]) $name_sql_file_path = $argv[4];
else $name_sql_file_path = "/var/tmp/hh_stat_{$table_sfx}.sql";

$TABLE_NAME = "__hh_log{$table_sfx}";
if($stack_max <= 0) $stack_max = 5000; // default

$sql_string = 
"SET client_encoding = 'WIN1251';\n
CREATE TABLE {$TABLE_NAME} (
   \"name\" text
)WITH (
  OIDS = FALSE
);\n\n";

$sql_string .= "COPY {$TABLE_NAME} FROM stdin;\n";

$res = @fopen($file_path, "r");
if ($res) {
    print "\n";
    print "Processing...\n\n";
    $stack = 0;
    while (($line_string = fgets($res)) !== false) {
        if(preg_match('/xmlns:hh="http:\/\/www.free-lance.ru\/external\/api\/hh/mix', $line_string)) {
            $find_match = preg_match_all('/k="kword">(.*?)<\/v>/mix', $line_string, $find);
            if($find_match) {
                $keyword = explode(',', current($find[1]));
                foreach($keyword as $key=>$word) {
                    if(trim($word) == '') continue;
                    $stack++;
                    if($stack > $stack_max) break;
                    $sql_string .= "{$word}\n";
                }  
            } 
        } else {
            continue;
        }
    }
    $sql_string .= "\\.";
    file_put_contents($name_sql_file_path, $sql_string);
    fclose($res);
    setlocale(LC_ALL, "en_US.UTF-8");
    
    print "Done\n";
} else {
    print "\n";
    print "ERROR\n";
    print "Failed to open file '{$file_path}'\n";
    exit;
}
?>