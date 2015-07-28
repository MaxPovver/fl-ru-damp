<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }

if (!$parser) {
    header("Location: /404.php");
    exit;
}

$content    = 'tpl.top.php';

$start = __paramInit('string', 's', null, null);
if($start == null) $start = "all";
//$parser = search_parser::factory();
$page = __paramInit('int', 'p', null, 1);
$limit = 40;
if($page<=0) $page=1;
$offset = ($page-1)*$limit;
$pages = 0;
$data = $parser->getTopQueiesAdmin($start, $limit, $offset, $pages);
$pages = ceil($pages/$limit);
?>
