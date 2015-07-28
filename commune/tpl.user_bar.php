<? if ($top['close_comments'] == 't') { ?>
    <li class="b-post__links-item b-post__links-item_padleft_10">
        <a href="<?=getFriendlyURL('commune', $msg_id)?><?= ($page>1?'?bp='.$page : '')?>">Комментирование закрыто</a>
    </li>
<? } else { 
    $unread = $top['a_count']-1 - $top['current_count'];
?>
    <li class="b-post__links-item b-post__links-item_padleft_10">
        <a class="b-post__link b-post__link" href="<?=getFriendlyURL('commune', $msg_id)?><?= ($page>1?'?bp='.$page : '') ?>">
            <?= ($top['a_count'] - 1)." ".ending($top['a_count'] - 1, комментарий, комментария, комментариев)  ?>
        </a>
        <? if(intval($top['a_users_count'])) { ?>
        &nbsp;(от <?= $top['a_users_count']." ".ending($top['a_users_count'], пользователя, пользователей, пользователей)  ?>)
        <? } ?>
    </li>
    <? if ($unread > 0 && get_uid(false) && $unread != ($top['a_count'] - 1)) { ?>
    <li class="b-post__links-item b-post__links-item_padleft_10">
        <a class="b-post__link b-post__link_color_6db335" href="<?=getFriendlyURL('commune', $msg_id)?><?= ($om ? '?om='.$om : '') ?>#unread">
            <?= $unread." ".ending($unread, новый, новых, новых)  ?>
        </a>
    </li>
    <? } ?>
<? } ?>