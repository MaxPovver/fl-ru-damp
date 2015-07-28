<?php 
if (preg_match("/^\d+\|\d+$/",$_GET['p'], $matches))
  $g_page_id = $matches[0];
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/banners.php");
session_start();
?>
<!DOCTYPE html>
<html>
	<head>
        <script type="text/javascript" src="<?=WDCPREFIX?>/scripts/swfobject.js"></script> <? // должен быть линк на папку /scripts в папке вебдава ?>
		<style>
			*{
				margin: 0;
				padding: 0;
				}
			.top-banner{
				display:table-cell;
				width: 300px;
				height: 90px;
				vertical-align:middle;
				}
			*+html .middled{
				display:block;
				height: auto;
				width: auto;
				margin-top: expression((parentNode.offsetHeight - this.offsetHeight)<0 ? "0" : (parentNode.offsetHeight - this.offsetHeight)/2 + "px");
			}
			*html .middled{
				display:block;
				height: auto;
				width: auto;
				margin-top: expression((parentNode.offsetHeight - this.offsetHeight)<0 ? "0" : (parentNode.offsetHeight - this.offsetHeight)/2 + "px");
			}
			.top-banner img{
				float:left;
				border: 0px;
				}
		</style>
	</head>
	<body>
		<div class="top-banner"><span class="middled">
		<?
        if ($g_page_id) {
            $banners = new banners;
			$banner = $banners->ViewBanner($g_page_id, 4);
        }
        if ($banner) {
        if ($banner['code']) { echo $banner['code']; } else { ?>
        
            <? if ($banner['type'] != 4 && $banner['type'] != 13) { ?>
                <a href="/banners/click.php?id=<?=$banner['id']?>" target="_blank"><img src="<?=WDCPREFIX?>/banners/<?=$banner['filename']?>" alt="" width="<?=$banner['width']?>" height="<?=$banner['height']?>" /></a>
            <? } else { ?>
                <script type="text/javascript">
                    swfobject.embedSWF("<?=WDCPREFIX?>/banners/<?=$banner['filename']?>?link1=/banners/click.php?id=<?=$banner['id']?>", "TopBannerSwf300", "<?=$banner['width']?>", "<?=$banner['height']?>", "9.0.0", "/scripts/expressInstall.swf", false, {wmode:"opaque"});
                </script>
                <div id="TopBannerSwf300" style="height:<?=$banner['height']?>px"><img src="<?=WDCPREFIX?>/banners/<?=$banner['stat_fname']?>" alt="" width="<?=$banner['width']?>" height="<?=$banner['height']?>" /></div>
            <? } ?>
            <?=$banner['pixel']?>
        <? } } else { ?><img src="/images/dot_grey.gif" alt="" width="300" height="60" />
        <? } ?></span>
		</div>
	</body>
</html>