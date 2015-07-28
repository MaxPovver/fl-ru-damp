<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
if ( !(hasPermissions('advstat') && hasPermissions('adm')) ) {
    exit;
}
?>
<h1>Промо баннеры</h1>
<?php if(isset($banners)) { ?>
<script type="text/javascript">
var maxW = 165;
window.addEvent("load", checkLinkWidth);
function checkLinkWidth() {
    var ls = $('bannerlist').getElements("li.b-menu__banner");
    function _showWarning(node) {
    	var p = node.parentNode;
        if (p) {
            p = p.parentNode;
            if (p) {
                var th = p.getElements("th");
                if (th.length > 0) {
                    th[0].getElement('div.banner-error')
                            .removeClass('b-layout_hide')
                            .set('text', "Ширина ссылки превышает " + maxW + " пикселей");
                } 
            }
        }
    }
    for (var i = 0; i < ls.length; i++) {
        if (ls[i].clientWidth > maxW) {
            _showWarning(ls[i]);
        }else {
            var wText = 0;
            var links = ls[i].getElements("a.b-menu__link-banner");
            if (links.length > 0) {
                wText = links[0].clientWidth;
            }
            var wPic  = 0;
            var links = ls[i].getElements("a.b-menu__link-pic");
            if (links.length > 0) {
                wPic = links[0].clientWidth;
            }
            if (wPic + wText > maxW) {
                _showWarning(ls[i]);
            }
        }
    }
}
</script>
<div class="b-buttons b-buttons_padtb_10"><a href="?new" class="b-button_flat b-button_flat_green">Создать баннер</a></div>
<br/>
<table class="payed-compar" style="border:1px silver solid" cellspacing="0" id="bannerlist">
    <col width="150"/>
    <col width="100" />
    <col width="100" />
    <col width="63" />
    <thead>
        <tr>
            <td><strong>Название</strong></td>
            <td style="border-left:1px silver solid; border-right:1px silver solid"><strong>Ссылка</strong></td>
            <td style="border-left:1px silver solid; border-right:1px silver solid"><strong>Период</strong></td>
            <td><strong>Рекламный</strong></td>
            <td style="border-left:1px silver solid;"><img src="/images/ico_edit_news.gif"></td>
        </tr>    
    </thead>
    <tbody>
        <?php foreach($banners as $banner) { ?>
        <tr id="ban_<?=$banner['id']?>">
            <th <?=($banner['is_activity'] == 't'?'class="td-pro"':"")?>>
                <a href="?type=<?=$banner['id']?>"><?= htmlspecialchars($banner['name'])?></a>
                <div class="b-layout b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_lineheight_13 b-layout__txt_padtop_5 b-layout__txt_color_c4271f banner-error b-layout_hide"></div>
            </th>            
            <td <?=($banner['is_activity'] == 't'?'class="td-pro"':"")?> style="border-left:1px silver solid; border-right:1px silver solid">
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
            </td>
            <td <?=($banner['is_activity'] == 't'?'class="td-pro"':"")?> style="border-left:1px silver solid; border-right:1px silver solid"><?= date("d.m.Y", strtotime($banner['from_date']));?> - <?=date("d.m.Y", strtotime($banner['to_date']))?></td>
            <td <?=($banner['is_activity'] == 't'?'class="td-pro"':"")?>><?= $banner['advertising'] == 't'?'Да':'Нет'?></td>
            <td <?=($banner['is_activity'] == 't'?'class="td-pro"':"")?> style="border-left:1px silver solid;">
                <a href="?edit=<?=$banner['id']?>"><img src="/images/btn-edit4.png" align="center"></a>
                <a id="del_banner_id_<?=$banner['id']?>" href="/siteadmin/ban_promo/?delete=<?=$banner['id']?>" onclick="return addTokenToLink('del_banner_id_<?=$banner['id']?>', 'Вы действительно хотите удалить ссылку?')"><img src="/images/btn-remove4.png" align="right"></a>  
            </td>
        </tr>
        <?php }//foreach?>
    </tbody>
</table>
<br/>

<div class="b-buttons b-buttons_padtop_10"><a href="?new" class="b-button_flat b-button_flat_green">Создать баннер</a></div>
<?php } else {//if?>
<strong>В настойщий момент в базе нет промо банеров</strong>
<?php }//else
