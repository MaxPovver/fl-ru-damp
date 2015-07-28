<?
ob_start();
	$id = intval(trim($_GET['id']));
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/banners.php");
		$banners = new banners;
        $link = $banners->ClickBanner($id);
		header("Location: ".((is_string($link) && $link!='') ? iconv("CP1251", "UTF-8", addhttp($link)) : HTTP_PFX.'free-lance.ru'));
                exit;
?>
