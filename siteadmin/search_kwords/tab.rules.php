<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }

if (!$parser) {
    header("Location: /404.php");
    exit;
}

$content = 'tpl.rules.php';

$data = $parser->getRules();

$action = __paramInit('string', 'action', 'action');

switch ($action) {
    case 'delete_rule':
        $id = __paramInit('int', 'id');

        if ($id) {
            $parser->deleteRuleById($id);
        }

        header_location_exit($_SERVER['HTTP_REFERER']);
        break;
    case 'add_rule':
        $name = __paramInit('string', null, 'rule_name');
        $pattern = trim($_POST['pattern']);

        if (!strlen($name) || !strlen($pattern)) {
            header_location_exit($_SERVER['HTTP_REFERER']);
        }

        $parser->addRule($name, $pattern);

        header_location_exit($_SERVER['HTTP_REFERER']);
        break;
}