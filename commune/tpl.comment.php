<table width="100%" style="margin:15px 0 0 0" border="0" cellspacing="0" cellpadding="0">
    <tr valign="top">
        <td style="padding-left:<?= $padding ?>px;<?= ($active_id == $comment['id'] ? 'background-color:#fff7dd' : '') ?>">
            <?= view_avatar_info($comment['user_login'], $comment['user_photo'], 1) ?>
        </td>
        <td style="padding:0 15px 0 10px;<?= ($active_id == $comment['id'] ? 'background-color:#fff7dd' : '') ?>" width="100%">
            <?
            print( __commPrntUsrInfo($comment, 'user_') .
                    '&nbsp;&nbsp;' .
                    date("[d.m.Y | H:i]", $created_time));

            if ($is_deleted) {
                if ($comment['deleted_id'] == $comment['user_id'])
                    print('<br><br>Комментарий удален автором ');
                else if ($comment['deleted_id'] == $top['user_id'])
                    print('<br><br>Комментарий удален автором темы ');
                else if ($comment['deleted_id'] == $top['commune_author_id'])
                    print('<br><br>Комментарий удален создателем сообщества ');
                else if ($comment['deleted_by_commune_admin'])
                    print('<br><br>Комментарий удален администратором сообщества ');
                else {
                    print('<br><br>Комментарий удален модератором ');
                    if ($mod & commune::MOD_MODER)
                        print(' ( ' . $comment['deleted_login'] . ' : ' . $comment['deleted_usurname'] . ' ' . $comment['deleted_uname'] . ' ) ');
                }
                print(dateFormat("[d.m.Y | H:i]", $comment['deleted_time']));
            }
            else if ($comment['modified_id']) {
                print(' &nbsp;');
                if ($comment['modified_id'] == $comment['user_id'])
                    print('[внесены изменения: ');
                else if ($comment['modified_id'] == $top['commune_author_id'])
                    print('Отредактировано создателем сообщества [');
                else if ($comment['modified_by_commune_admin'])
                    print('Отредактировано администратором сообщества [');
                else {
                    print('Отредактировано модератором');
                    if ($mod & commune::MOD_MODER)
                        print(' ( ' . $comment['modified_login'] . ' : ' . $comment['modified_usurname'] . ' ' . $comment['modified_uname'] . ' )');
                    print(' [');  // !!! Каким модератором.
                }
                print(dateFormat("d.m.Y | H:i]", $comment['modified_time']));
            }

            if (!$is_deleted || ($mod & (commune::MOD_ADMIN))) { // | commune::MOD_COMM_MODERATOR | commune::MOD_COMM_AUTHOR)))
                print('<br/><br/>');

                if (($comment['member_is_banned'] || $comment['user_is_banned'])
                        && !($mod & (commune::MOD_MODER))) { // | commune::MOD_COMM_MODERATOR | commune::MOD_COMM_AUTHOR)))
                    print('Ответ от заблокированного пользователя');
                } else {
                    if ($is_deleted)
                        print('<font class="del-color">');

                    if ($comment['user_is_banned'] || $comment['member_is_banned'])
                        print('<font color="#000000"><b>Пользователь забанен' . (!$top['user_is_banned'] ? ' в сообществе' : '') . '.</b></font><br/><br/>');

                    if ($comment['title'])
                        print('<font class="bl_name">' . reformat2($comment['title'], 25, 0, 1) . '</font><br>');

                    print(reformat2($comment['msgtext'], 82 - round(((($level > 19) ? 19 : $level) - 1) * 1.9), 0, -($comment['user_is_chuck'] == 't'), 1) . '<br/>');

                    if ($comment['youtube_link'])
                        print show_video($comment['id'], $comment['youtube_link']);

                    if ($comment['attach']) {
                        //$commune['attach'] = array_reverse($commune['attach']);
                        foreach ($comment['attach'] as $attach) {
                            $att_ext = CFile::getext($attach['fname']);
                            $str = '';
                            $str = viewattachLeft($comment['user_login'], $attach['fname'], 'upload', $file, commune::MSG_IMAGE_MAX_HEIGHT, commune::_MSG_IMAGE_MAX_WIDTH, commune::MSG_IMAGE_MAX_SIZE, !($attach['small'] == 't'), (int) ($attach['small'] == 't'));

                            print("<br/><br/>" . $str . "<br/>");
                        }
                    }

                    if ($is_deleted)
                        print('</font>');

                    print('<br/>');

                    if (!$is_deleted && !$comment['member_is_banned']) {
 ?>

                        <table width="100%" border="0" cellpadding="0" cellspacing="0">
                            <tr valign="middle" class="red-link vv">
                                <td align="left" nowrap width="100%">
            <?
                        if ((($comment['user_id'] == $user_id
                                || $user_id == $top['user_id']) && $comment['member_is_accepted'])
                                || ($mod & (commune::MOD_ADMIN | commune::MOD_MODER | commune::MOD_COMM_ADMIN | commune::MOD_COMM_MODERATOR | commune::MOD_COMM_AUTHOR))) {
            ?>
                                        <a href="javascript:void(0)"
                                           onclick="if(warning(1))
                                               xajax_DeleteComment('idMessage_<?= $comment['id'] ?>', <?= $comment['id'] ?>, <?= $user_id ?>, <?= $mod ?>, <?= $om ?>, <?= $level ?>, <?= $is_last ?>);">
    														Удалить
                            </a> |
                        <?
                        } if (($comment['user_id'] == $user_id && $comment['member_is_accepted'])
                                || ($mod & (commune::MOD_ADMIN | commune::MOD_COMM_MODERATOR | commune::MOD_COMM_AUTHOR))) {
                        ?>
                         <a href="javascript:void(0);" onclick="__commCF(<?= $comment['id'] ?>, <?= $top['id'] ?>, <?= $commune_id ?>, <?= $om ?>, 0, 'Edit.post', <?= $mod ?>, <?= count($comment['attach']) ?>)">Редактировать</a> |
<? } ?>
                        <? if ($top['close_comments'] == 'f' || $top['user_id'] == $user_id) {
 ?>

                            <a <? if (!$user_id) {
                        ?>href="/fbd.php"<?
                            } else if (!($mod & (commune::MOD_ADMIN | commune::MOD_COMM_ACCEPTED | commune::MOD_COMM_AUTHOR))) {
                        ?>href="error" onclick="alert('Вы не являетесь членом данного сообщества. Данная функция Вам недоступна.'); return false;"<?
                            } else {
                        ?>href="javascript:void(0)" onclick="__commCF(<?= $comment['id'] ?>, <?= $top['id'] ?>, <?= $commune_id ?>, <?= $om ?>, 0, 'Create.post', <?= $mod ?>);"
                            <? } ?>
                            >Комментировать</a> |

                            <? } ?>
                        <a href="?id=<?= $commune_id ?>&site=Topic&post=<?= $top['id'] ?>.<?= $comment['id'] ?><?= ($om ? '&om='.$om : '') ?>#o<?= $comment['id'] ?>">Ссылка</a>
                    </td>
                    <td NOWRAP>
                        <?
                            if (($mod & (commune::MOD_COMM_ADMIN | commune::MOD_COMM_MODERATOR | commune::MOD_COMM_AUTHOR))
                                    && $comment['member_id']
                                    && $comment['user_id'] != $user_id
                                    && !$comment['member_is_admin']
                                    && $comment['commune_author_id'] != $comment['user_id']
                                    && !hasPermissions('communes',$comment['user_id'])) {
                                $href = 'javascript:void(0)';
                                $onclick = "onclick=\"if(warning()) xajax_BanMemberForComment('idMessage_', {$comment['id']}, {$comment['member_id']}, {$user_id}, {$mod}, {$om}, {$level}, {$is_last});\"";
                                if (($mod & (commune::MOD_ADMIN | commune::MOD_MODER)) && $top['member_warn_count'] >= 3) {
                                    $href = "/userban/?uid={$top['user_login']}";
                                    $onclick = '';
                                }
                        ?>
                                <a class="red-link vv" <?= $onclick ?> href="<?= $href ?>">
                        <?
                                if ($top['member_warn_count'] < 3)
                                    print("Сделать предупреждение ({$comment['member_warn_count']})");
                                else
                                    print("Забанить!");
                        ?>
                            </a>
                            <? } ?>
                    </td>
                </tr>
            </table>
<?
                        }
                    }
                }
?>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding:0 15 0 15">
            <div<?= ($level == 1 || $is_last ? ' style="border-bottom: 1px solid #DCDBD9;"' : '') ?>>
                <br>
            </div>
        </td>
    </tr>
</table>
