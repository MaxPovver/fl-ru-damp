<?php
/**
 * Генерация графиков svg
 *
 */

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/promotion.php");

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating_svg_daily.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating_svg_monthly.php");

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
session_start();

$u_name = __paramInit('string', 'name');
$u_user = new users();
$u_user->GetUser($u_name);
if($u_user->uid) {
    $uid = $u_user->uid;
    $u_login = $u_user->login;
} else {
    $uid = get_uid(false);
    if(!$uid) {
    //    header('Location: /fbd.php');
        echo "<script>this.parent.document.location.reload();</script>";
        exit();
    }
    $u_login = $_SESSION['login'];
}



$rating = new rating();

$ratingmode = __paramInit('string', 'ratingmode');
$file_ext = stristr($_SERVER['HTTP_USER_AGENT'], 'msie ') ? 'html' : 'svg';

switch($ratingmode) {
    case 'prev':
        $TIME = mktime(0, 0, 0, date('m')-1, date('d'), date('Y'));
        $file_name = 'rating_prev';
        $get_rating_by = 'getRatingByMonth';
        $get_rating_date = date('Y-m-d', $TIME);

        $graph_class = 'Rating_Svg_Daily';

        $pro_periods_date = date('Y-m-01', $TIME);
        
        $file = "/users/" . substr($u_login, 0, 2) . "/{$u_login}/upload/$file_name.$file_ext";
        $cfile = new CFile($file);
        $overwrite = (date('Y-m') != date('Y-m', strtotime($cfile->modified)));

        break;
    case 'year':
        $TIME = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $file_name = 'rating_year';
        $get_rating_by = 'getRatingByYear';
        $get_rating_date = date('Y', $TIME);

        $graph_class = 'Rating_Svg_Monthly';

        $pro_periods_date = date('Y-01-01', $TIME);

        $file = "/users/" . substr($u_login, 0, 2) . "/{$u_login}/upload/$file_name.$file_ext";
        $cfile = new CFile($file);

        $periods = rating::getMonthParts(date('Y-01-01'));
        $cur_period = $periods[intval(date('m', $TIME))-1];

        if($TIME <= $cur_period[0]) {
            $file_maxtime = mktime(0,0,0, date('m', $cur_period[0]), 0, date('Y', $cur_period[0]));
        } elseif($TIME > $cur_period[0] && $TIME <= $cur_period[1]) {
            $file_maxtime = $cur_period[0];
        } elseif($TIME > $cur_period[1] && $TIME <= $cur_period[2]) {
            $file_maxtime = $cur_period[1];
        } elseif($TIME > $cur_period[2] && $TIME <= $cur_period[3]) {
            $file_maxtime = $cur_period[2];
        }

        $overwrite = (strtotime($cfile->modified) < $file_maxtime);

        break;
    default:
        $TIME = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $file_name = 'rating';
        $get_rating_by = 'getRatingByMonth';
        $get_rating_date = date('Y-m-d', $TIME);

        $graph_class = 'Rating_Svg_Daily';

        $pro_periods_date = date('Y-m-01', $TIME);

        $file = "/users/" . substr($u_login, 0, 2) . "/{$u_login}/upload/$file_name.$file_ext";
        $cfile = new CFile($file);
        $overwrite = (date('Y-m-d') != date('Y-m-d', strtotime($cfile->modified)));
}

//    var_dump($overwrite); exit();
//    $data = $rating->$get_rating_by($uid, $get_rating_date);
//
//    $graph = new $graph_class($TIME, $data);
//    $graph->setPro(promotion::GetUserProPeriods($uid, $pro_periods_date));
//    $svg = $graph->render();
//        header('Content-type: image/svg+xml');
//    echo $svg;
//    exit();

$overwrite = true;

if(!$cfile->id || $overwrite) {

    if($cfile->id) $cfile->Delete ($cfile->id);
    $file2 = "/users/" . substr($u_login, 0, 2)
                       . "/{$u_login}/upload/$file_name."
                       . ($file_ext == 'svg' ? 'html' : 'svg');
    $cf2 = new CFile($file2);
    if($cf2->id) $cf2->Delete ($cf2->id);

    $data = $rating->$get_rating_by($uid, $get_rating_date);

    $graph = new $graph_class($TIME, $data);
    $graph->setPro(promotion::GetUserProPeriods($uid, $pro_periods_date));

//    $graph->setPro(array(
//        array(
//            'from_time' => '2010-01-03',
//            'to_time' => '2010-06-15'
//        ),
//        array(
//            'from_time' => '2010-12-02',
//            'to_time' => '2010-12-28'
//        )
//    ));

    $svg = $graph->render();

    $cfile = new CFile();
    $cfile->name = "$file_name.$file_ext";
    $cfile->path = "/users/" . substr($u_login, 0, 2) . "/{$u_login}/upload/";
    $cfile->modified = '';
    $cfile->putContent($file, $file_ext == 'html' ? svgToVml($svg) : $svg);

    $file_ext = ($file_ext == 'svg' ? 'html' : 'svg');
    $file = "/users/" . substr($u_login, 0, 2) . "/{$u_login}/upload/$file_name.$file_ext";
    $cfile = new CFile();
    $cfile->name = "$file_name.$file_ext";
    $cfile->path = "/users/" . substr($u_login, 0, 2) . "/{$u_login}/upload/";
    $cfile->modified = '';
    $cfile->putContent($file, $file_ext == 'html' ? svgToVml($svg) : $svg);

    $svg = stristr($_SERVER['HTTP_USER_AGENT'], 'msie ') ? svgToVml($svg) : $svg;

    if(stristr($_SERVER['HTTP_USER_AGENT'], 'msie ')) {
        header('Content-type: text/html');
    } else {
        header('Content-type: image/svg+xml');
    }
    echo $svg;
} else {
    header("Location: ".WDCPREFIX.$file);
}
