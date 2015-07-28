<?php
class users_visits_daily {
    /**
     * @desc получить выборку данных для страницы user_visits/?mode=user_visits
     * @param string $from дата в формате Y-m-d
     * @param string $to   дата в формате Y-m-d
    **/
    static public function GetStatistics ($from, $to) {
        $pattern = "#^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$#";
        if (!preg_match($pattern, $from, $m) || !preg_match($pattern, $to, $m)) {
            return false;
        }
        $DB = new DB("stat");
        $row = $DB->cache(300)->row("SELECT
    SUM((n = 1 AND NOT is_emp)::int) AS f1,
    SUM((n BETWEEN 2 AND 5 AND NOT is_emp)::int) AS f2_5,
    SUM((n BETWEEN 6 AND 10 AND NOT is_emp)::int) AS f6_10,
    SUM((n > 10 AND NOT is_emp)::int) AS f11,
    SUM((n = 1 AND is_emp)::int) AS e1,
    SUM((n BETWEEN 2 AND 5 AND is_emp)::int) AS e2_5,
    SUM((n BETWEEN 6 AND 10 AND is_emp)::int) AS e6_10,
    SUM((n > 10 and is_emp)::int) AS e11
    FROM
(
SELECT user_id,
 COUNT(*) AS n, is_emp
 FROM users_visits_daily 
 WHERE visit_date >= '{$from}' AND visit_date <= '{$to}' GROUP BY user_id, is_emp
) AS s");
        $o = new StdClass();
        $o->f1 = $row["f1"]; //фриленсеры авторизовавшиеся 1 раз
        $o->e1 = $row["e1"]; //работодатели авторизовавшиеся 1 раз
        $o->f2_5 = $row["f2_5"]; //фриленсеры авторизовавшиеся от 2 до 5 раз
        $o->e2_5 = $row["e2_5"]; //работодатели авторизовавшиеся от 2 до 5 раз
        $o->f6_10 = $row["f6_10"]; //фриленсеры авторизовавшиеся от 6 до 10 раз
        $o->e6_10 = $row["e6_10"];; //работодатели авторизовавшиеся от 6 до 10 раз
        $o->f11 = $row["f11"];; //фриленсеры авторизовавшиеся более 10 раз
        $o->e11 = $row["e11"];; //работодатели авторизовавшиеся более 10 раз
        $o->total_emp = $o->e1 + $o->e2_5 + $o->e6_10 + $o->e11;
        $o->total_frl = $o->f1 + $o->f2_5 + $o->f6_10 + $o->f11;
        return $o;
    }
}