<? require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");
$stc = new static_compress();
$seed = $_GET['t'];
if ($seed){
		$stc->output($seed);
}
?>