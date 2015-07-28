<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/commune.common.php");
$xajax->printJavascript('/xajax/');

global $id, $uid, $om, $comm, $top, $page, $session, $message_id, $comment_id, $request, $alert, $action, $user_mod;

// Топ-тема должна быть получена заранее.
$favs = commune::GetFavorites($uid, $top['id']);
$top['last_viewed_time'] = !$uid ? 0 : commune::GetMessageLVT($top['id'], $uid);

//print($top['member_is_banned']);
//print($user_mod & (commune::MOD_ADMIN | commune::MOD_COMM_AUTHOR | commune::MOD_COMM_MANAGER));
// Дерево сообщений.
if (!($thread = commune::GetAsThread($top['theme_id'])))
    $thread = array();


$tree = transformArray2Tree($thread, 'id', 'parent_id', $top['id'], 'SIMPLE');
$is_site_admin = hasPermissions('communes');
?>
<?php if (true) {
 ?>

<?php
    $aGroup = commune::getGroupById( $comm['group_id'] );
    $sGroup = $aGroup['name'];
    $crumbs = array();
    $crumbs[] = array("title"=>"Сообщества", "url"=>"/commune/");
    if($comm['id'] != commune::COMMUNE_BLOGS_ID) $crumbs[] = array("title"=>$sGroup, "url"=>"/commune/?gr={$comm['group_id']}");
    $crumbs[] = array("title"=>$comm['name'], "url"=>getFriendlyURL('commune_commune', $comm['id']));
    $crumbs[] = array("title"=>$top['category_name'], "url"=>getFriendlyURL('commune_commune', $comm['id'])."?om=".(__paramInit("int", "om")?__paramInit("int", "om"):'0').'&cat='.$top['category_id']);
?>

<div class="b-community-discussion">
<?=getCrumbs($crumbs, "commune")?>
<?php $sTitle   = /*$top['moderator_status'] === '0' ? $stop_words->replace($top['title']) :*/ $top['title']; ?>
<h1 class="b-page__title"><?= $sTitle ?>
                <? if ($_SESSION['login']) {
                    if(!isset($favs)) $favs = commune::GetFavorites(get_uid(false), NULL);
                    //$onclick = ($site == 'Lenta') ? "ShowFavFloatLenta($msg_id, $user_id, 'CM')" : "ShowFavFloat($msg_id, $user_id, $om)";
                    //$ids = ($site == 'Lenta' ? 'CM' : '') . $msg_id;
                    //$pr = ($favs[$msg_id] || ($site == 'Lenta' && $favs['CM' . $msg_id])) ? ($site == 'Lenta' ? $favs['CM' . $msg_id]['priority'] : $favs[$msg_id]['priority']) : '0_empty';
                    //$alt_text = $favs[$msg_id] || ($site == 'Lenta' && $favs['CM' . $msg_id]) ? 'Редактировать приоритет' : 'Добавить в закладки';
                    // с помощью id передаем параметры в js код
                ?>
                    <span id="fav_star_<?= $msg_id ?>_<?= $user_id ?>_<?= $om ?>_<?= $favs[$msg_id]['priority'] ? $favs[$msg_id]['priority'] : 0 ?>" class="b-post__star b-post__star_<?= $favs[$msg_id] ? "yellow" : "white" ?>"></span>
                <? } ?>
</h1>

<div id="idTop_<?= $top['id'] ?>">
<?= __commPrntTopic($top, $uid, $user_mod, $om, NULL, 'Topic', ($favs == NULL ? 0 : 1)) ?>
</div>
<?php include_once(dirname(__FILE__) . '/comments_tree.php'); ?>
    












<?php } else { /* не используется ?>
    <table border="0" width="100%" cellpadding="0" cellspacing="0">
        <tr valign="middle">
            <td align="left">
                <h1><a style="color:#666" href="?id=<?= $comm['id'] ?>">Сообщество &laquo;<?= $comm['name'] ?>&raquo;</a></h1>
            </td>
        </tr>
    </table>

    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr valign="top">
            <td height="400" bgcolor="#FFFFFF" class="ba bClr commune">
                <div style="text-align:right;padding:15px 15px 0 0">
                    <a href="?post=<?= $top['id'] ?><?= ($om ? '&om='.$om : '') ?>&mode=Back" class="blue"><b>[Назад]</b></a>
                </div>
                <a name="o<?= $comment['id'] ?>"></a>
                <div id='idTop_<?= $top['id'] ?>'>
<?= __commPrntTopic($top, $uid, $user_mod, $om, NULL, 'Topic', ($favs == NULL ? 0 : 1)) ?>
            </div>

<? if ($top['close_comments'] == 't') { ?>
            <div style="padding: 20px 20px 0; font: 18px Tahoma;">
                Автор запретил оставлять комментарии
            </div>
            <? } ?>
            <? if ($top['is_private'] == 't') { ?>
                <div style="padding: 20px 20px 0; font: 18px Tahoma;">
                    Автор запретил просматривать эту запись
                </div>
            <? } ?>

            <div id='idEditCommentForm_<?= $top['id'] ?>' class="editmsg">
                <?
                if (($top['user_id'] == $uid || $top['close_comments'] == 'f') && (($user_mod & (commune::MOD_COMM_ACCEPTED | commune::MOD_COMM_AUTHOR)) && !$comm['is_blocked'])
                        || $user_mod & commune::MOD_ADMIN) {
                    if ($request // do...
                            && ( ($message_id && $top['id'] == $message_id) // do.edit.message
                            || (!$message_id && $top['id'] == $request['parent_id']) // do.create.message
//                      || ($top['close_comments'] == 'f' || $top['user_id'] == $uid )
                            )) {
                        print(__commPrntCommentForm($top['commune_id'],
                                        $om,
                                        NULL,
                                        $action,
                                        $top['id'],
                                        $message_id,
                                        NULL,
                                        $request,
                                        $alert, 'Topic', $user_mod));
                    } else if (!$request && empty($thread)) { // Со страницы сообщества сразу ткнули "Комментировать" на топе.
                        print(__commPrntCommentForm($top['commune_id'],
                                        $om,
                                        NULL,
                                        $action,
                                        $top['id'],
                                        NULL,
                                        $top['id'],
                                        NULL,
                                        NULL, 'Topic', $user_mod));
                    }
                }
                ?>
            </div>

            <script type="text/javascript">
                poll.init('Commune', document.getElementById('idEditCommentForm_<?= $top['id'] ?>'), <?= commune::POLL_ANSWERS_MAX ?>, '<?= $_SESSION['rand'] ?>');
                if (document.getElementById('question')) maxChars('question', 'polls_error', <?= commune::POLL_QUESTION_CHARS_MAX ?>);
            </script>

            <?
                if ($len = count($tree))
                    list($nidx, $nlevel) = split(":", $tree[0]);

                if ($len) {
            ?>
                    <script>var __commCCnt=<?= $len ?></script>
                    <div id="idCommentsHeader" style="padding:0 0 10px 15px">
                        <h1>Комментарии:</h1>
                    </div>


            <?
                }
                //var_dump($thread);
                $unread_set = true;
                for ($i = 0; $i < $len; $i++) {
                    $level = $nlevel;
                    $comment = $thread[$nidx];
                    list($nidx, $nlevel) = split(":", $tree[$i + 1]);
                    $is_last = $nlevel == 1 ? 1 : 0;
                    $hl_unred = ($uid && !$top['is_viewed']
                            && $comment['user_id'] != $uid
                            && strtotimeEx($comment['created_time']) > strtotimeEx($top['last_viewed_time'])
                            && $top['current_count'] !== NULL);

                    $created_time = strtotimeEx($comment['created_time']);
                    $last_viewed_time = strtotimeEx($top['last_viewed_time']);
                    if ($uid && !$top['is_viewed'] && $comment['user_id'] != $uid && $created_time > $last_viewed_time && $top['current_count'] !== NULL) {
                        $is_new = true;
                    } else {
                        $is_new = false;
                    }
            ?>
            <? if ($i == $len - 1 && !$alert) {
            ?>
                        <a name="o-last"></a>
            <? } ?>
            <? if ($is_new) {
            ?>
                        <a name="unread"></a>
            <? } ?>
                    <div id="idMessage_<?= $comment['id'] ?>" <?php echo $is_new ? 'style="background: #F0FFE2; margin: 0 0 15px 0; padding: 0;"' : 'style="margin: 0 0 15px 0; padding: 0;"'; ?>>
                        <a name="o<?= $comment['id'] ?>"></a>
                <? if ($comment_id == $comment['id']): ?><a name="op"></a><? endif; ?>
                <?= __commPrntComment($top, $comment, $uid, $user_mod, $om, $comment_id, $level, $is_last); ?>
                    </div>

                    <div id='idEditCommentForm_<?= $comment['id'] ?>' class="editmsg">
                <?
                        if (($top['user_id'] == $uid || $top['close_comments'] == 'f') && (($user_mod & (commune::MOD_COMM_ACCEPTED | commune::MOD_COMM_AUTHOR)) && !$comm['is_blocked'])
                                || $user_mod & commune::MOD_ADMIN) {
                            if ($request // do...
                                    && ( ($message_id && $comment['id'] == $message_id) // do.Edit.post
                                    || (!$message_id && $comment['id'] == $request['parent_id']) )) { // do.Create.post
                                print(__commPrntCommentForm($top['commune_id'],
                                                $om,
                                                NULL,
                                                $action,
                                                $top['id'],
                                                $message_id, // Если do.Create.post, то тут NULL, иначе $comment['id'].
                                                NULL, // Здесь необязательно указывать, т.к. если есть $request, то он несет в себе ид. родителя.
                                                $request,
                                                $alert, 'Topic', $user_mod));

                                if ($alert) {
                                    //print "<script type='text/javascript'>\n  alert(document.getElementById('idAlertedCommentForm').tagName); document.getElementById('idAlertedCommentForm').scrollIntoView(true); \n</script>";
                                }
                            }
                        }
                ?>
                    </div>

<? } ?>
                <div style="text-align:right;padding:35px 15px 20px 0">
                    <a class="vv" style="color:#666" href="#top">Наверх</a>
                </div>
            </td>
        </tr>
    </table>
<?php*/ } ?>










<?
                // Ставим запись, что топик просмотрен.
                if ($uid)
                    commune::SetMessageLVT($top['id'], $uid, -1, ($top['a_count']-1)); // в a_count содержится на 1 комментарий больше, не знаю почему
?> 

<?php 
if ( $user_mod & (
        commune::MOD_COMM_ADMIN
        | commune::MOD_ADMIN
        | commune::MOD_MODER
        | commune::MOD_COMM_MODERATOR
        | commune::MOD_COMM_AUTHOR
    ) 
) {
	include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/ban_overlay.php' );
	include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/warn_overlay.php' );
}
?>

<a id="upper" class="b-page__up" href="#" style=" visibility:hidden;"></a>
</div>
