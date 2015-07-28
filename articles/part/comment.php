<?php

/**
 * Один коммент
 * 
 * @param array $data Массив данных для коммента
 * @param integer $uid ид пользователя
 * @param boolean $first TRUE- комментарий является первым в ветке.
 */
function commentHTML($data, $uid, $attaches, $lvt, $wordlength = 45, $has_child = false, $is_hidden = false) {
    global $session;
    ob_start();

    $is_author = ($data['from_id'] == $uid);
    $first = ($data['parent_id'] === NULL);
    $parent_id = $data['parent_id'];

    $is_new = ($lvt!==NULL && $lvt < $data['created_time']);
    ?>
    <? if($is_new) { ?>
    <a name="unread"></a>
    <? } ?>
    <div class="cl-li-in cl-li<?= $first ? '-first' : '' ?> <?=$is_new ? 'cl-li-new' : ''?>">
        <a name="c_<?=$data['id']?>"></a>
        <ul class="cl-i">
            <li><a href="#c_<?=$data['id']?>" class="cl-anchor">#</a></li>
            <li class="cl-time"><?=date('d.m.Y H:i', strtotime($data['created_time']))?></li>
            <li class="p-edited">
                <? if($data['modified_id'] && $data['modified_id'] == $data['from_id']) { ?>
                <img src="/images/ico-e-u.png" alt="Отредактировано пользователем" title="Внесены изменения <?=date('d.m.Y в H:i', strtotime($data['modified_time']))?>" />
                <? } ?>
                <? if($data['modified_id'] && $data['modified_id'] != $data['from_id'] ) {
                    $moduser = (hasPermissions('articles')) ? " ({$data['mod_login']} : {$data['mod_uname']} {$data['mod_usurname']})" : "";
                    ?>
                <img src="/images/ico-e-a.png"
                         alt="Отредактировано модератором"
                         title="Отредактировано модератором<?=$moduser?>: <?=date('d.m.Y в H:i', strtotime($data['modified_time']))?>" />
                <? } ?>
            </li>
        </ul>
        <div class="cl-arr">
            <? if(!$first) { ?>
            <a href="#c_<?=$parent_id?>" class="u-anchor">&darr;</a>
            <? } ?>
            <a href="#c_3" class="d-anchor">&darr;</a>
        </div>
        <a href="/users/<?=$data['login']?>" class="freelancer-name"><?=view_avatar_info($data['login'], $data['photo'], 1)?></a>
        <div class="user-info">
            <div class="username">
                <?
                $stat = '';
                if ($data['is_pro'] == 't')
                    $stat .= (is_emp($data['role']) ? view_pro_emp()  : view_pro2($data['is_pro_test'] == "t")) . "&nbsp;&nbsp;";

                ?>
                <?=$stat?><a href="/users/<?=$data['login']?>" class="<?= is_emp($data['role']) ? 'employer' : 'freelancer' ?>-name"><?= $data['uname'] . ' ' . $data['usurname'] . ' [' . $data['login'] . ']'?></a>
                
            </div>
            <div class="comment-body utxt">
            <? if($data['deleted_id'] === NULL) { ?>
                <p>
                <?= reformat($data['msgtext'], $wordlength, 0, 0, 1)?>
                </p>
                
                <? if($data['youtube_link'] !== NULL) { ?>
                <div class="added-video">
                    <?= show_video($data['id'], $data['youtube_link']) ?>
                </div>
                <? } ?>

                <? if($attaches) { ?>
                    <?= viewattachListNew ($attaches, 'upload') ?>
                <? } ?>
            <? } else { ?>
                Комментарий удален <?=$data['deleted_id'] == $data['from_id'] ? 'автором' : 'модератором'?>
            <? } ?>
            </div>
            
            <ul class="cl-o">
                <? if($uid && $data['deleted_id'] === NULL) { ?>
                    <li class="cl-com first"><a href="javascript:void(0)" onclick="commentAdd(this)">Комментировать</a></li>
                    <? if($uid == $data['from_id'] || hasPermissions('articles')) { ?>
                        <li class="cl-edit"><a href="javascript:void(0)" onclick="commentEdit(this)">Редактировать</a></li>
                        <li class="cl-del"><a href="./?task=del-comment&id=<?=$data['id']?>" onclick="return (confirm('Вы уверены?'));">Удалить</a></li>
                    <? } ?>
                <? } elseif(hasPermissions('articles')) { ?>
                        <li class="cl-del"><a href="./?task=restore-comment&id=<?=$data['id']?>" onclick="return (confirm('Вы уверены?'));">Восстановить</a></li>
                <? } ?>
                <? if($has_child) { ?>
                <li class="last"><a href="" class="cl-thread-toggle"><?=$is_hidden ? 'Развернуть ' : 'Свернуть '?> ветвь</a></li>
                <? } ?>
            </ul>
        </div>
    </div>
    <?
    $out = ob_get_contents();
    ob_clean();

    return $out;
}
