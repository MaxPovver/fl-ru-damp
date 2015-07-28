<?   
    $rpath='../';
    $stretch_page = true;
    header('Location: /404.php');
        exit;
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
    session_start();
    if(!($rank = __paramInit('int','rank')) || $rank > rating::MAX_RANK || $rank <= 0) {
        header('Location: /404.php');
        exit;
    }
    $page =  __paramInit('int','page',NULL,1);
    if ($page < 1) {
        $page = 1;
    }
    
    $COLCNT=3;
    $ROWCNT=20;
    
    $limit = $COLCNT * $ROWCNT;
    $offset = ($page-1) * $limit;
    
    $count = rating::CountByRank($rank);
    if ($count) {
        if ($offset > $count) {
            header_location_exit('/404.php');
        }
        $users = rating::GetByRank($rank, $limit, $offset);
    }
    
    $header = "../header.php";
    $content = "content.php";
    $content_bgcolor = '#ffffff';
    $footer = "../footer.html";
    include ("../template.php");
?>
