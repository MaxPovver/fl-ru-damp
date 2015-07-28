<?
	if ($g_page_id) {
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/banners.php");
		if (!$ban_cat) $ban_cat = 2;
		$banners = new banners;
		$banner = $banners->ViewBanner($g_page_id, $ban_cat);
	}
if ($banner) {?><?=$banner['pixel']?>
<? if ($banner['type'] != 4 && $banner['type'] != 13) { ?>
	<div><a href="/banners/click.php?id=<?=$banner['id']?>" target="_blank">
		<img src="<?=WDCPREFIX?>/banners/<?=$banner['filename']?>" alt="" width="<?=$banner['width']?>" height="<?=$banner['height']?>" border="0">
	</a></div>
<? } else { ?>
	<script type="text/javascript">
		swfobject.embedSWF("<?=WDCPREFIX?>/banners/<?=$banner['filename']?>?link1=/banners/click.php?id=<?=$banner['id']?>", "CatBannerSwf", "<?=$banner['width']?>", "<?=$banner['height']?>", "9.0.0", "/scripts/expressInstall.swf");
	</script>
<div id="CatBannerSwf" style="height:<?=$banner['height']?>px">
	<a href="/banners/click.php?id=<?=$banner['id']?>" target="_blank">
		<img src="<?=WDCPREFIX?>/banners/<?=$banner['stat_fname']?>" alt="" border="0" width="<?=$banner['width']?>" height="<?=$banner['height']?>" />
	</a>
</div>
<? } } ?>
