<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }

if (!$parser) {
    header("Location: /404.php");
    exit;
}

$content = "tpl.queries.php";


$start = __paramInit('string', 's', null, 'а', 1);
if (in_array($_GET['s'], array('others', 'num', 'all', 'users', 'more', 'projects'))) {
    $start = $_GET['s'];
}
$action = __paramInit('string', 'action','action');
$page = __paramInit('int', 'p', null, 1);
$limit = 40;

preg_match_all("/(\D)/si", "йцукенгшщзхъфывапролджэ€чсмитьбю", $rus);
preg_match_all("/(\D)/si", "qwertyuiopasdfghjklzxcvbnm", $eng);
$rus = $rus[0];
$eng = $eng[0];
sort($rus);
sort($eng);

//$parser = search_parser::factory();
//$parser->parseRaw();
//$parser->filterRaw();
//$parser->filterRaw('users');
//$parser->filterRaw('projects');
//$parser->cleanup();

switch ($action) {
    //удаление запроса (новый фильр не создаетс€)
    case 'remove':
        $qid = __paramInit('int', 'id');
        
        if ($qid) {
            $parser->removeQuery($qid);
        }
        
        header_location_exit($_SERVER['HTTP_REFERER']);
        break;
    case 'add_filter':
        $qid = __paramInit('int', null, 'query');
        $filter_rule = __paramInit('int', null, 'filter_rule');
//        $word = __paramInit('string', null, 'word');
        $word = trim($_POST['word']);
        
        if (!strlen($word)) {
            header_location_exit($_SERVER['HTTP_REFERER']);
        }
        
        $parser->addFilter($word, $filter_rule, TRUE);
        
        if ($qid) {
            $parser->removeQuery($qid);
        }
        header_location_exit($_SERVER['HTTP_REFERER']);
        break;
    default:
        if($page <=0) $page = 1;
        $offset = ($page-1)*$limit;
        $data = $parser->getQueries($start, $limit, $offset, $pages);
        
        $pages = ceil($pages/$limit);
}

//$first_chars = $parser->getFirstChars();
$rules = $parser->getRules();