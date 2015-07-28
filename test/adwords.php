<?php

ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once '../classes/stdf.php';
require_once '../classes/projects.php';

if ( empty($argv[1]) ) {
    
    new_projects::adWords('upload/adwords.csv');
    exit;
    
}

$filename = 'upload/adwords0.csv';

// syntax 2012-01-01:2012-12-31
$d = explode(':', $argv[1]);

if ( count($d) == 2 ) {
    
        $profs  = array();
        $groups = array();
        $rows = $DB->rows("SELECT * FROM professions");
        foreach ( $rows as $row ) {
            $profs[$row['id']] = $row;
        }
        $rows = $DB->rows("SELECT * FROM prof_group");
        foreach ( $rows as $row ) {
            $groups[$row['id']] = $row;
        }
        $sql = "
            SELECT
                p.id, e.compname, country.country_name, city.city_name,
                date_trunc('seconds', p.create_date) c_date, p.name, p.descr, p.cost, p.currency, p.priceby,
                array_agg(pts.category_id) cats, array_agg(pts.subcategory_id) subcats
            FROM
                projects p
            INNER JOIN
                employer e ON e.uid = p.user_id AND e.is_banned = B'0'
            LEFT JOIN
                country ON country.id = p.country
            LEFT JOIN
                city ON city.id = p.city
            LEFT JOIN
                project_to_spec pts ON pts.project_id = p.id
            LEFT JOIN
                projects_blocked pb ON pb.project_id = p.id
            WHERE
                pb.project_id IS NULL AND p.create_date >= '{$d[0]} 00:00:00' AND p.create_date <= '{$d[1]} 23:59:59'
            GROUP BY
                p.id, e.compname, country.country_name, city.city_name, c_date, p.name, p.descr, p.cost, p.currency, p.priceby
            ORDER BY
                id DESC
        ";
        $tmpfile = "/var/tmp/adwords.csv";
        $fp  = fopen($tmpfile, "w");
        $res = $DB->query($sql);
        
        $c = 0;
        
        while ( $row = pg_fetch_assoc($res) ) {
            $data = array();
            // ссылка
            $data['url'] = $GLOBALS['host'] . '/projects/' . $row['id'] . '/' . translit(strtolower(htmlspecialchars_decode($row['name'], ENT_QUOTES))) . '.html';
            // цена
            if ( !empty($row['cost']) ) {
                switch ( $row['currency'] ) {
                    case 0: {
                        $cost = "{$row['cost']}\$";
                        break;
                    }
                    case 1: {
                        $cost = "€{$row['cost']}";
                        break;
                    }
                    case 2: {
                        $cost = "{$row['cost']} руб.";
                        break;
                    }
                    case 4: {
                        $cost = "{$row['cost']} FM";
                        break;
                    }
                }
                switch ( $row['priceby'] ) {
                    case 1: {
                        $priceby = 'за час';
                        break;
                    }
                    case 2: {
                        $priceby = 'за день';
                        break;
                    }
                    case 3: {
                        $priceby = 'за месяц';
                        break;
                    }
                    case 4: {
                        $priceby = 'за проект';
                        break;
                    }
                }
                $data['Wage'] = "{$cost} {$priceby}";
            } else {
                $data['Wage'] = 'По договоренности';
            }
            // специализация (если несколько, берем только первую)
            $cats    = $DB->array_to_php($row['cats']);
            $subcats = $DB->array_to_php($row['subcats']);
            $data['Vacancy'] = '';
            $data['Vacancy_title'] = '';
            $data['Category 1'] = '';
            $data['Category 2'] = '';
            if ( $cats[0] ) {
                $data['Vacancy'] = $groups[(int) $cats[0]]['name_case'];
                $data['Category 1'] = $groups[(int) $cats[0]]['name'];
                $data['Category 1'] = preg_replace("/[\.\,\_\\\\\/\*\;\:\?]+/", " ", $data['Category 1']);
                $data['Category 1'] = preg_replace("/\\s{2,}/", " ", $data['Category 1']);
                $data['Category 1'] = preg_replace("/[^-A-Za-zА-Яа-яЁё0-9\\s]+/", "", $data['Category 1']);
            } else {
                $data['Category 1'] = 'Прочее';
            }
            if ( $subcats[0] ) {
                $data['Vacancy'] = $profs[(int) $subcats[0]]['name_case'];
                $data['Category 2'] = $profs[(int) $subcats[0]]['name'];
                $data['Category 2'] = preg_replace("/[\.\,\_\\\\\/\*\;\:\?]+/", " ", $data['Category 2']);
                $data['Category 2'] = preg_replace("/\\s{2,}/", " ", $data['Category 2']);
                $data['Category 2'] = preg_replace("/[^-A-Za-zА-Яа-яЁё0-9\\s]+/", "", $data['Category 2']);
            } else {
                $data['Category 2'] = $data['Category 1'];
            }
            if ( empty($data['Vacancy']) ) {
                $data['Vacancy'] = 'Прочее';
            } else {
                $data['Vacancy'] = preg_replace("/[\.\,\_\\\\\/\*\;\:\?]+/", " ", $data['Vacancy']);
                $data['Vacancy'] = preg_replace("/\\s{2,}/", " ", $data['Vacancy']);
                $data['Vacancy'] = preg_replace("/[^-A-Za-zА-Яа-яЁё0-9\\s]+/", "", $data['Vacancy']);
            }
            $data['Vacancy_title'] = LenghtFormatEx($data['Vacancy'], 30, '');
            $data['vacancy_id'] = $row['id'];
            // сохраняем
            if ( !$c ) {
                $rowsNames = array_keys($data);
                $dataStr = implode(',', $rowsNames)  . "\r\n";
                fwrite($fp, chr(255) . chr(254) . iconv('CP1251', 'UTF-16LE//TRANSLIT', $dataStr));
            }
            $dataStr = implode(',', $data)  . "\r\n";
            fwrite($fp, iconv('CP1251', 'UTF-16LE//TRANSLIT', $dataStr));
            $c++;
        }
        
        fclose($fp);
        
}