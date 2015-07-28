<?php
/**
 * Шаблон попап формы быстрого редактирования комментариев, которыне на общем движке (пока только файлы)
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IN_STDF') ) { 
    header("HTTP/1.0 404 Not Found"); // ибо нефиг
    exit();
}
?>
<div class="b-menu b-menu_rubric b-menu_padbot_10">
    <ul class="b-menu__list">
        <li id="adm_edit_tab_i1" class="b-menu__item b-menu__item_active"><span class="b-menu__b1"><span class="b-menu__b2">Файлы</span></span></li>
        <li id="adm_edit_tab_i2" class="b-menu__item"><a class="b-menu__link" href="#" onClick="adm_edit_content.editMenu(2); return false;">Причина редактирования</a></li>
    </ul>
</div>

<input type="hidden" name="resource" value="<?=$mess['resource']?>">
<input type="hidden" name="parent_id" value="<?=$mess['parent_id']?>">
<input type="hidden" name="user_id" value="<?=$mess['author_uid']?>">
<input type="hidden" name="user_login" value="<?=$mess['author_login']?>">
<input type="hidden" name="user_uname" value="<?=$mess['author_uname']?>">
<input type="hidden" name="user_usurname" value="<?=$mess['author_usurname']?>">

<?=_parseHiddenParams($aParams)?>

<div id="adm_edit_tab_div1"<?/*style="display: none;"*/?>>
    <div class="b-form">
        <div id="adm_edit_attachedfiles" class="b-fon" style="width:635px"></div>
    </div>
</div>