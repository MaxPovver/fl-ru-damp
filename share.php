<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
$title = urldecode( __paramInit('string', 'title') );
$login = urldecode( __paramInit('string', 'login') );
$name  = urldecode( __paramInit('string', 'name') );
$img   = urldecode( __paramInit('string', 'img') );
$gr_name  = urldecode( __paramInit('string', 'gr_name') );
//$id    = __paramInit( 'int', 'id' );
$from  = __paramInit( 'string', 'from' );
$id = $_GET['id'];
if(is_array($id)) {
   $id = array_map("intval", $id);
} else {
   $id = intval($id);
}
?>
<!DOCTYPE HTML>
<html>
    <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style type="text/css">
		body {margin: 0;padding: 0;background:transparent;}
	</style>
    </head>
    <body>
        <?= SocialButtonsSrc( $id, $title, $img, $from, $login, $name, $gr_name ) ?>
    </body>
</html>