<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }

if (!$parser) {
    header("Location: /404.php");
    exit;
}

$content = 'tpl.filters.php';

$data = $parser->getFilters();
$rules = $parser->getRules();


$action = __paramInit('string', 'action', 'action');

switch ($action) {
    case 'delete_filter':
        $id = __paramInit('int', 'id');

        if ($id) {
            $parser->deleteFilterById($id);
        }

        header_location_exit($_SERVER['HTTP_REFERER']);
        break;
    case 'add_filter':
        $filter_rule = __paramInit('int', null, 'filter_rule');
//        $word = __paramInit('string', null, 'word');
        $word = trim($_POST['word']);

        if (!strlen($word)) {
            header_location_exit($_SERVER['HTTP_REFERER']);
        }

        $parser->addFilter($word, $filter_rule, TRUE);

        header_location_exit($_SERVER['HTTP_REFERER']);
        break;
}