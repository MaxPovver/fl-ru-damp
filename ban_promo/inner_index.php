<?php
if ( !defined('IS_ACCESS_PAGE') ) { 
    header('Location: /403.php'); 
    exit;  
}
?>

<div class="b-page">
    <h1 class="b-page__title">Статистика баннера <?= $bpromo->info['name']?></h1>
    
    <div class="b-page__txt b-page__txt_fontsize_15 b-page__txt_padbot_10">
        <table>
            <tr>
                <td colspan="2">
                <?php
                if($banner['type_ban'] == 'image') {
                    $banner_promo_img  = $banner["name_img"];
                    if (strpos($banner_promo_img, "/user") === 0) $banner_promo_img  = WDCPREFIX.$banner_promo_img; 
                    $banner_promo_text = $banner["linktext"];
                    $banner_promo_type = $banner["id"];
                    $banner_promo_link_style = $banner["link_style"];
                    $banner_promo_title      = $banner["img_title"];
                    ?>
                    <?php if (trim($banner_promo_img) == '' && trim($banner_promo_text) != '') {?>
                        <li class="b-menu__banner">
                            <a href="/a_promo.php?type=<?=$banner_promo_type?>" class="b-menu__link-banner" <? if (trim($banner_promo_link_style) != '') {?>style="<?php print $banner_promo_link_style?>"<? }?>><?php print $banner_promo_text?></a>
                        </li>
                    <?php } elseif (trim($banner_promo_img) != '' && trim($banner_promo_text) == '') {?>
                        <li class="b-menu__banner">
                        <a href="/a_promo.php?type=<?=$banner_promo_type?>" class="b-menu__link-pic">
                            <img <?php if( trim($banner_promo_title) != '') {?>alt="<?php print $banner_promo_title?>" title="<?php print $banner_promo_title?>" <?php }?>src="<?php print $banner_promo_img ?>" class="b-menu__pic">
                        </a>
                        </li>
                    <?php } elseif (trim($banner_promo_img) != '' && trim($banner_promo_text) != '') {?>
                        <li class="b-menu__banner">
                        <a href="/a_promo.php?type=<?=$banner_promo_type?>" class="b-menu__link-pic">
                            <img src="<?php print $banner_promo_img ?>" <?php print $banner_promo_image_style?> class="b-menu__pic" <?php if( trim($banner_promo_title) != '') {?> alt="<?php print $banner_promo_title?>" title="<?php print $banner_promo_title?>" <?php }?>>
                        </a>
                        <a href="/a_promo.php?type=<?=$banner_promo_type?>" class="b-menu__link-banner" <? if (trim($banner_promo_link_style) != '') {?>style="<?php print $banner_promo_link_style?>" <?php }?>><?php print $banner_promo_text?></a>
                        </li>
                    <?php }?>
                <? } elseif($banner['type_ban'] == 'code') { //if?>
                        <li class="b-menu__banner">
                            <?= $banner['code_text'];?>
                        </li>
                <? }//elseif?>   
                <div class="b-layout__txt b-layout__txt_padbot_10">&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td style="text-align:right"><div class="b-layout__txt b-layout__txt_padbot_10"><strong>Всего показов:</strong></div></td>
                <td><div class="b-layout__txt b-layout__txt_padleft_10"><?=intval($count['views'])?></div></td>
            </tr>
            <tr>
                <td style="text-align:right"><div class="b-layout__txt b-layout__txt_padbot_5"><strong>Всего кликов:</strong></div></td>
                <td><div class="b-layout__txt b-layout__txt_padleft_10"><?=intval($count['clicks'])?></div></td>
            </tr>
        </table>
        
        <? if($stats) { ?>
        <br><br>
        <table border="1">
            <tr>
                <td width="100">&nbsp;<strong>Дата</strong></td>
                <td width="50">&nbsp;<strong>Показы</strong></td>
                <td width="50">&nbsp;<strong>Клики</strong></td>
            </tr>
            <? foreach($stats as $stat ) { ?>
                <tr>
                    <td>&nbsp;<?=$stat['c_date']?></td>
                    <td>&nbsp;<?=$stat['views']?></td>
                    <td>&nbsp;<?=$stat['clicks']?></td>
                </tr>
            <? } ?>
        </table>
        <? } ?>
    </div>
</div>
