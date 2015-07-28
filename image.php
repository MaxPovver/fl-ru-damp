<?
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/captcha.php");
	session_start();
    header("Content-type: image/gif");
    $num = $_GET['num'];
    
    $bgcolor = $_GET['bg'];
    $fgcolor = $_GET['fg'];
    
    // можно убрать после того как найдется оптимальный вариант каптчи
    if ($_GET['type']) {
        $num = mt_rand(1000, 9999);
        $captcha = new captcha($num, $bgcolor, $fgcolor);
        $captcha->setnumber();
        $method = 'getImage' . $_GET['type'];
        imagegif($captcha->$method());
        exit();
    }
    // ****************************************************************

	$captcha = new captcha($num, $bgcolor, $fgcolor);
    if($_GET['r']) $captcha->setnumber();
	imagegif($captcha->getImage11());
?>
