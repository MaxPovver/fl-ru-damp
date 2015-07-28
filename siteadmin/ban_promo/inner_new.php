<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } 
if ( !(hasPermissions('advstat') && hasPermissions('adm')) ) {
    exit;
}

?>
<script type="text/javascript">
window.addEvent('domready', function() {
    change_type_ban('<?=htmlspecialchars($_POST['type_ban']);?>');
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
<strong>Создание банера</strong><br/><br/>
<form method="POST" name="frm" id="frm" enctype="multipart/form-data">
    <table cellpadding="5">
        <tr height="30">
            <td>Название:</td> 
            <td><input type="text" name="name" size="24" value="<?=htmlspecialchars(stripslashes($_POST['name']))?>"></td>
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
                <input type="text" size="9" maxlength="9" name="from_date" id="from_date" value="<?=htmlspecialchars(stripslashes($_POST['from_date']))?>" readonly="readonly"> 
                - <input type="text" name="to_date" id="to_date" size="9" maxlength="9" value="<?=htmlspecialchars(stripslashes($_POST['to_date']))?>" readonly="readonly">
            </td>
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
            <td><input type="text" name="banner_link" size="44" value="<?=htmlspecialchars(stripslashes($_POST['banner_link']))?>"></td>
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
            <td><input type="text" name="text" size="24" value="<?=htmlspecialchars(stripslashes($_POST['text']))?>"></td>
        </tr>
        <tr height="30">
            <td>Где показываем</td>
            <td>
                <select name="page_target">
                    <? foreach(banner_promo::$target_page as $id=>$name) { ?>
                    <option value="<?= $id?>" <?= ($_POST['page_target'] == $id ? "selected" : "")?>><?=$name?></option>
                    <? }//foreach?>
                </select>
            </td>
        </tr>
        <tr height="30">
            <td>Тип баннера</td>
            <td>
                <select name="type_ban" onchange="change_type_ban(this.value);">
                    <? foreach(banner_promo::$type_ban as $id=>$name) { ?>
                    <option value="<?= $id?>" <?= ($_POST['type_ban'] == $id ? "selected" : "")?>><?=$name?></option>
                    <? }//foreach?>
                </select>
            </td>
        </tr>
        <tr height="160" class="type_code ban_types">
            <td>Код баннера</td>
            <td>
                <textarea name="code_text" cols="90" rows="10"><?=htmlspecialchars(stripslashes($_POST['code_text']))?></textarea>
            </td>
        </tr>
        <tr height="30" class="type_image ban_types">
            <td colspan="2"><strong>Файл изображения:</strong></td>
        </tr>
        <tr height="30" class="type_image ban_types">
            <td>На сервере:</td> 
            <td>
            <input type="text" name="name_img" size="24" value="<?=htmlspecialchars(stripslashes($_POST['name_img']))?>">
            </td>
            <td><?if($bpromo->info['name_img'] != "") {?> 
            <img src="/images/<?=$bpromo->info['name_img']?>" 
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
            <td>Title картинки:</td> 
            <td><input type="text" name="img_title" size="24" value="<?=htmlspecialchars(stripslashes($_POST['img_title']))?>"></td>
        </tr>
        <tr height="30" class="type_image ban_types">
            <td>Стиль картинки:</td> 
            <td colspan="2"><input type="text" name="img_style" size="44" value="<?=htmlspecialchars(stripslashes($_POST['img_style']))?>"></td>
        </tr>
        <tr height="30" class="type_image ban_types">
            <td>Стиль ссылки:</td> 
            <td colspan="2"><input type="text" name="link_style" size="44" value="<?=htmlspecialchars(stripslashes($_POST['link_style']))?>"></td>
        </tr>
        <tr height="30">
            <td>Активировать:</td> 
            <td><input type="checkbox" name="is_activity" size="24" <?php print $activeChecked ?>></td>
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
            <td>&nbsp;</td> 
            <td align="right"><input type="submit" name="new" value="Создать"></td>
        </tr>
    </table>
</form>