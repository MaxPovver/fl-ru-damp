<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
if ( !(hasPermissions('advstat') && hasPermissions('adm')) ) {
    exit;
}

?>
<script type="text/javascript">
window.addEvent('domready', function() {
    change_type_ban('<?= isset($_POST['type_ban']) ? htmlspecialchars($_POST['type_ban']) : $bpromo->info['type_ban'];?>');
    new tcal ({ 'formname': 'frm', 'controlname': 'from_date', 'iconId': 'from_date' });
    new tcal ({ 'formname': 'frm', 'controlname': 'to_date', 'iconId': 'to_date' });     
});
</script>
<style type="text/css">
.errmsg {
    font-weight:bold;
    color:#aa0000;
    text-align:center;
    width:100%;
    padding-bottom: 7px;
}
</style>
[<a href="/siteadmin/ban_promo/">назад</a>]<br/><br/>
<strong>Редактирование баннера «<a href="?type=<?=$bpromo->info['id']?>"><?=htmlspecialchars(stripslashes($bpromo->info['name']))?></a>»</strong><br/><br/>
<div class="b-layout b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_lineheight_13 b-layout__txt_padbot_10 b-layout__txt_color_c4271f b-layout_hide" id="banner-error"></div>
<?if($error_string) print(view_error($error_string))."<br/>";?>

<?if($success_string) print("<strong style='color:green'>".$success_string."</strong><br/>");?>
<script type="text/javascript">
var maxW = 165;
window.addEvent("load", checkLinkWidth);
function checkLinkWidth() {
    var ls = $('linkpreview').getElements("li.b-menu__banner");
    for (var i = 0; i < ls.length; i++) {
        if (ls[i].clientWidth > maxW) {
            $('banner-error').set('text', "Ширина ссылки превышает " + maxW + " пикселей").removeClass('b-layout_hide');
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
                $('banner-error').set('text', "Ширина ссылки превышает " + maxW + " пикселей").removeClass('b-layout_hide');
            }
        }
    }
}
</script>
<form method="POST" name="frm" id="frm" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?=$bpromo->info['id']?>">    
    <table cellpadding="5">
        <tr height="30">
            <td>Название:</td> 
            <td><input type="text" id="name" name="name" size="24" value="<?=isset($_POST['name']) ? htmlspecialchars(stripslashes($_POST['name'])):htmlspecialchars(stripslashes($bpromo->info['name']))?>"></td>
            <td>&nbsp;</td>
        </tr>
        <?php if($error&&$error->nameError) {?>
        <tr>
            <td colspan="2">
                <div class="errmsg"><?php print $error->nameError ?></div>
            </td>
        </tr>
        <?php }?>
        <tr height="30">
            <td>Период размещения:</td>
            <td>
                <input type="text" size="9" maxlength="9" name="from_date" id="from_date" value="<?= isset($_POST['from_date']) ? htmlspecialchars(stripslashes($_POST['from_date'])): date("d-m-Y", strtotime($bpromo->info['from_date']));?>" readonly="readonly"> 
                - <input type="text" name="to_date" id="to_date" size="9" maxlength="9" value="<?= isset($_POST['to_date']) ? htmlspecialchars(stripslashes($_POST['to_date'])): date("d-m-Y", strtotime($bpromo->info['to_date']));?>" readonly="readonly">
            </td>
            <td>&nbsp;</td>
        </tr>
        <?php if($error&&$error->dateError) {?>
        <tr>
            <td colspan="2">
                <div class="errmsg"><?php print $error->dateError ?></div>
            </td>
        </tr>
        <?php }?>
        <tr height="30">
            <td>Ссылка редиректа:</td> 
            <td colspan="2"><input type="text" name="banner_link" size="44" value="<?=isset($_POST['banner_link']) ? htmlspecialchars(stripslashes($_POST['banner_link'])):htmlspecialchars(stripslashes($bpromo->info['banner_link']))?>"></td>
        </tr>
        <?php if($error&&$error->linkError) {?>
        <tr>
            <td colspan="2">
                <div class="errmsg"><?php print $error->linkError ?></div>
            </td>
        </tr>
        <?php }?>
        <tr height="30">
            <td>Текст ссылки:</td> 
            <td><input type="text" id="linktext" name="text" size="24" value="<?=isset($_POST['text']) ? htmlspecialchars(stripslashes($_POST['text'])):htmlspecialchars(stripslashes($bpromo->info['linktext']))?>"></td>
        </tr>
        <tr height="30">
            <td>Где показываем</td>
            <td>
                <select name="page_target">
                    <? foreach(banner_promo::$target_page as $id=>$name) { ?>
                    <option value="<?= $id?>" <?= ($_POST['page_target'] == $id || $bpromo->info['page_target'] == $id ? "selected" : "")?>><?=$name?></option>
                    <? }//foreach?>
                </select>
            </td>
        </tr>
        <tr height="30">
            <td>Тип баннера</td>
            <td>
                <select name="type_ban" onchange="change_type_ban(this.value);">
                    <? foreach(banner_promo::$type_ban as $id=>$name) { ?>
                    <option value="<?= $id?>" <?= isset($_POST['type_ban']) ? ($_POST['type_ban'] == $id ? "selected" : "") : ($bpromo->info['type_ban'] == $id ? "selected" : "") ;?>><?=$name?></option>
                    <? }//foreach?>
                </select>
            </td>
        </tr>
        <tr height="30">
            <td>Доступ (логины)</td>
            <td>
                <input type="text" id="login_access" name="login_access" size="44" value="<?=isset($_POST['login_access']) ? htmlspecialchars(stripslashes($_POST['login_access'])):htmlspecialchars(stripslashes($bpromo->info['login_access']))?>">
            </td>
        </tr>
        <tr height="160" class="type_code ban_types">
            <td>Код баннера</td>
            <td>
                <textarea name="code_text" cols="90" rows="10"><?= isset($_POST['code_text']) ? htmlspecialchars(stripslashes($_POST['code_text'])) : $bpromo->info['code_text'] ;?></textarea>
            </td>
        </tr>
        <tr height="30" class="type_image ban_types">
            <td colspan="2"><strong>Файл изображения:</strong></td>
        </tr>
        <tr height="30" class="type_image ban_types">
            <td>На сервере:</td> 
            <td>
            <input type="text" name="name_img" size="24" value="<?=isset($_POST['name_img']) ? htmlspecialchars(stripslashes($_POST['name_img'])):htmlspecialchars(stripslashes($bpromo->info['name_img']))?>">
            </td>
            <td><?if($bpromo->info['name_img'] != "") {?> 
            <img src="<?=$bpromo->info['name_img']?>" 
                <?=($bpromo->info['img_title']!=""?'title="'.$bpromo->info['img_title'].'"':'')?>
                <?=($bpromo->info['img_title']!=""?'alt="'.$bpromo->info['img_title'].'"':'')?>
                <?=($bpromo->info['img_style']!=""?'style="'.$bpromo->info['img_style'].'"':'')?>/><?}?>
            </td>
        </tr>
        <tr height="30" class="type_image ban_types">
            <td>Загрузить новый:</td> 
            <td><input type="file" name="file_main"></td>            
        </tr>
        <?php if($error&&$error->entityError) {?>
        <tr>
            <td colspan="2">
                <div class="errmsg"><?php print $error->entityError ?></div>
            </td>
        </tr>
        <?php }?>
        <tr height="30" class="type_image ban_types">
            <td>Title картинки баннера:</td> 
            <td><input type="text" name="img_title" size="24" value="<?=isset($_POST['img_title']) ? htmlspecialchars(stripslashes($_POST['img_title'])):htmlspecialchars(stripslashes($bpromo->info['img_title']))?>"></td>
            <td>&nbsp;</td>
        </tr>
        <tr height="30" class="type_image ban_types">
            <td>Стиль картинки:</td> 
            <td colspan="2"><input type="text" name="img_style" size="44" value="<?=isset($_POST['img_style']) ? htmlspecialchars(stripslashes($_POST['img_style'])): htmlspecialchars(stripslashes($bpromo->info['img_style']))?>"></td>
        </tr>
        <tr height="30" class="type_image ban_types">
            <td>Стиль ссылки:</td> 
            <td colspan="2"><input type="text" name="link_style" size="44" value="<?=isset($_POST['link_style']) ? htmlspecialchars(stripslashes($_POST['link_style'])):htmlspecialchars(stripslashes($bpromo->info['link_style']))?>"></td>
        </tr>
        <tr height="30">
            <td>Активировать:</td> 
            <td><input type="checkbox" name="is_activity" size="24" <? print $activeChecked ?>></td>
            <td>&nbsp;</td>
        </tr>
        <tr height="30">
            <td>Рекламный:</td> 
            <td><input type="checkbox" name="advertising" size="24" <?php print $advChecked ?>></td>
        </tr>
        <tr height="30" class="type_image ban_types">
            <td colspan="2"><strong>Показывать для пользователей:</strong></td>
        </tr>
        <tr height="30">
            <td>Только PRO:</td> 
            <td><input type="checkbox" name="is_pro" size="24" <?= $isPROChecked; ?>></td>
        </tr>
        <tr height="30">
            <td>Только не PRO:</td> 
            <td><input type="checkbox" name="is_not_pro" size="24" <?= $isNotPROChecked; ?>></td>
        </tr>
        <tr height="30">
            <td>Только фрилансеры:</td> 
            <td><input type="checkbox" name="is_frl" size="24" <?= $isFrlChecked; ?>></td>
        </tr>
        <tr height="30">
            <td>Только работодатели:</td> 
            <td><input type="checkbox" name="is_emp" size="24" <?= $isEmpChecked; ?>></td>
        </tr>
        
        <tr height="30">
            <td colspan="3"><input type="submit" name="save" value="Сохранить"></td>
        </tr>
    </table>    
</form>
<div>Превью</div>
<div id="linkpreview" style="float:left">
<?php

if($bpromo->info['type_ban'] == 'code') {
   echo "<br/>";
   echo $bpromo->info['code_text']; 
} elseif($bpromo->info['type_ban'] == 'image') {

    $banner_promo_img  = $bpromo->info["name_img"];
    if (strpos($banner_promo_img, "/user") === 0) $banner_promo_img  = WDCPREFIX.$banner_promo_img;
    $banner_promo_text = $bpromo->info["linktext"];
    $banner_promo_type = $bpromo->info["id"];
    $banner_promo_link_style = $bpromo->info["link_style"];
    $banner_promo_title      = $bpromo->info["img_title"];
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
<? }?>
</div>