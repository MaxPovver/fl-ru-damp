<?php
if (preg_match("/^\d+\|\d+$/", $_GET['p'], $matches))
    $g_page_id = $matches[0];
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/banners.php");
session_start();


if ($g_page_id) {
    $banners = new banners;
    $banner = $banners->ViewBanner($g_page_id, 5);
}

?><!DOCTYPE html>
<html>
    <head>
        <style>
            body{margin: 0;padding: 0;}
            div{height: 90px;vertical-align:top; padding:0; margin:0}
            img{border: 0px; padding:0; margin:0;}
        </style>
        <script type="text/javascript" src="<?=WDCPREFIX?>/scripts/swfobject.js"></script> <? // должен быть линк на папку /scripts в папке вебдава ?>
    </head>
    <body>
        <div <?=($banner?'style="border-bottom:1px solid #ccc;"':'')?>>
                <? if ($banner) { ?>
                    <? if ($banner['code']) echo $banner['code']; ?>
                    <? if (!$banner['code']) { ?>
                        <? if ($banner['type'] != 4 && $banner['type'] != 13) { ?>
                            <a href="/banners/click.php?id=<?= $banner['id'] ?>" target="_blank"><img src="<?= WDCPREFIX ?>/banners/<?= $banner['filename'] ?>" alt="" height="<?= $banner['height'] ?>" /></a>
                        <? } else { ?>
                            <script type="text/javascript">
                                swfobject.embedSWF("<?= WDCPREFIX ?>/banners/<?= $banner['filename'] ?>?link1=/banners/click.php?id=<?= $banner['id'] ?>", "TopBannerSwf", "100%", "<?= $banner['height'] ?>", "9.0.0", "/scripts/expressInstall.swf", false, {wmode:"opaque"});
                            </script>
                            <div id="TopBannerSwf" style="height:<?= $banner['height'] ?>px">
                              <? if ($banner['stat_fname']) { ?>
                              <img src="<?= WDCPREFIX ?>/banners/<?= $banner['stat_fname'] ?>" alt="" width="<?= $banner['width'] ?>" height="<?= $banner['height'] ?>" />
                              <? } ?>
                            </div>
                        <? } ?>
                    <? } ?>
                <script>
                    try {
                    if (this.parent.document.getElement('div.b-banner_layout_horiz'))
                        this.parent.document.getElement('div.b-banner_layout_horiz').show();
                    } catch(e) {}
                </script>
                <? } ?>
                <?=$banner['pixel']?>
        </div>
    </body>
</html>