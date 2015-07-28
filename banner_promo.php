<?php
if (!defined('IN_STDF')) {
    header("HTTP/1.0 404 Not Found");
    exit;
}


$date = date("Ymd");
$banner_promo_show = false;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/banner_promo.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/kwords.php");
$ban_promo = new banner_promo();
//$ban_promo->setType($ban_promo->getActiveBanner(), 1);
$ban_promo->setTypeByPage($g_page_id);
if (($no_banner != 1 || $no_banner != true) && $date >= date('Ymd', strtotime($ban_promo->info['from_date'])) && $date <= date('Ymd', strtotime($ban_promo->info['to_date']))) {
    $banner_promo_show = false;
    $banner_promo_type_ban = $ban_promo->info['type_ban'];
    $banner_promo_img = $ban_promo->info['name_img'];
    $banner_promo_code = $ban_promo->info['code_text'];
    $banner_promo_image_style = ($ban_promo->info['img_style'] != "" ? 'style="' . $ban_promo->info['img_style'] . '"' : '');
    $banner_promo_title = htmlspecialchars(stripslashes($ban_promo->info['img_title']));
    $banner_promo_text = htmlspecialchars(stripslashes($ban_promo->info['linktext']));
    $banner_promo_link_style = htmlspecialchars(stripslashes($ban_promo->info['link_style']));
    $banner_promo_type = intval($ban_promo->info['id']);
    $banner_link = $ban_promo->info['banner_link'];
    $ban_promo->writeViewStat();
    if ($ban_promo->info['page_target'] == '0|0' || $ban_promo->info['page_target'] == $g_page_id) {
        $banner_promo_show = true;
    }
}

$banner_promo_class = 'b-menu__banner b-menu__banner_ln1';
if ($banner_promo_inline) {
    $banner_promo_class .= ' b-menu__banner_inline';
}

$banner_promo_link_class = 'b-menu__link-banner b-menu__link-banner_margtopnull';
?>
<script type="text/javascript">
    function writeClickStat(id) {
        var link = "/a_promo.php?type=" + id;
        var req = new Request({
            url: link,
            onSuccess: onBLinkSuccess,
            onFailure: onBLinkFail
        });
        req.post(/*"u_token_key=" + _TOKEN_KEY*/);
        return false;
    }
    function onBLinkSuccess() {
    }
    function onBLinkFail() {
    }
</script>
<?php if ($banner_promo_show == true): ?>
    <?php if (isset($banner_outer_class)): ?>
        <div class="<?= $banner_outer_class ?>">
    <?php endif; ?>
    <? ob_start(); ?>                  
    <?php if ($banner_promo_type_ban == 'image') { ?>
        <?php if (trim($banner_promo_img) == '' && trim($banner_promo_text) != '') { ?>
            <div class="<?= $banner_promo_class ?>">
                <noindex><a rel="nofollow" target="_blank" onclick="javascript:writeClickStat(<?= $banner_promo_type ?>)" href="<?= $banner_link ?>" class="<?= $banner_promo_link_class ?>" <? if (trim($banner_promo_link_style) != '') { ?>style="<?php print $banner_promo_link_style ?>"<? } ?>> <?php print $banner_promo_text ?></a></noindex>
            </div>
        <?php } elseif (trim($banner_promo_img) != '' && trim($banner_promo_text) == '') { ?>
            <div class="<?= $banner_promo_class ?>">
                <noindex><a rel="nofollow" target="_blank" onclick="javascript:writeClickStat(<?= $banner_promo_type ?>)" href="<?= $banner_link ?>" class="b-menu__link-pic" <? if (trim($banner_promo_link_style) != '') { ?>style="<?php print $banner_promo_link_style ?>"<? } ?>>
                    <img <?php if (trim($banner_promo_title) != '') { ?>alt="<?php print $banner_promo_title ?>" <?php print $banner_promo_image_style ?> title="<?php print $banner_promo_title ?>" <?php } ?>src="<?php print $banner_promo_img ?>" class="b-menu__pic" />
                </a></noindex>
            </div>
        <?php } elseif (trim($banner_promo_img) != '' && trim($banner_promo_text) != '') { ?>
            <div class="<?= $banner_promo_class ?>">
                <noindex><a rel="nofollow" target="_blank" onclick="javascript:writeClickStat(<?= $banner_promo_type ?>)" href="<?= $banner_link ?>" class="b-menu__link-pic">
                    <img src="<?php print $banner_promo_img ?>" <?php print $banner_promo_image_style ?> class="b-menu__pic" <?php if (trim($banner_promo_title) != '') { ?> alt="<?php print $banner_promo_title ?>" title="<?php print $banner_promo_title ?>" <?php } ?> />
                </a></noindex>
                <noindex><a rel="nofollow" target="_blank" onclick="javascript:writeClickStat(<?= $banner_promo_type ?>)" href="<?= $banner_link ?>" class="<?= $banner_promo_link_class ?>" <? if (trim($banner_promo_link_style) != '') { ?>style="<?php print $banner_promo_link_style ?>" <?php } ?>><?php print $banner_promo_text ?></a></noindex>
            </div>
        <?php } ?>
    <? } elseif ($banner_promo_type_ban == 'code') {//if ?>
        <div class="<?= $banner_promo_class ?>">
            <?= $banner_promo_code; ?>
        </div>
    <? }//else ?>
    <? $bhtml = clearTextForJS(ob_get_clean()); ?><script type="text/javascript">document.write('<?= $bhtml ?>');</script>
    <?php if (isset($banner_outer_class)): ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
