<? if ($feedbacksFromEmp || $feedbacksFromFrl) { ?>
    <table class="b-layout__table b-layout__table_width_full">
        <tr class="b-layout__tr">
            <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_50ps" colspan="2">
                <? if ($feedbacksFromEmp) { ?>
                <div class="b-layout__title">Работодатели о БС </div>
                <? } ?>
            </td>
            <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_width_50ps b-layout__one_padleft_50" colspan="2">
                <? if ($feedbacksFromFrl) { ?>
                <div class="b-layout__title">Фрилансеры о БС</div>
                <? } ?>
            </td>
        </tr>

        <? for ($i = 0; $i < 3; $i++) {
            if (!$feedbacksFromEmp[$i] && !$feedbacksFromFrl[$i]) {
                break;
            } ?>

            <tr class="b-layout__tr">
                <? if ($feedbacksFromEmp[$i]) { ?>
                <td class="b-layout__one b-layout__one_padright_25"><a class="b-user__link" title="<?= $feedbacksFromEmp[$i]['uname'] . ' ' . $feedbacksFromEmp[$i]['usurname'] ?>" href="/users/<?= $feedbacksFromEmp[$i]['login'] ?>/"><?= view_avatar($feedbacksFromEmp[$i]['login'], $feedbacksFromEmp[$i]['photo']) ?></a></td>
                <td class="b-layout__one b-layout__one_padbot_40 b-layout__one_width_50ps">
                    <div class="b-user b-user_padbot_10"><a class="b-user__link" title="<?= $feedbacksFromEmp[$i]['uname'] . ' ' . $feedbacksFromEmp[$i]['usurname'] ?>" href="/users/<?= $feedbacksFromEmp[$i]['login'] ?>/"><?= $feedbacksFromEmp[$i]['uname'] . ' ' . $feedbacksFromEmp[$i]['usurname'] ?> <span class="b-user__login b-user__login_color_6db335"><span class="b-user__login-name"><?= $feedbacksFromEmp[$i]['login'] ?></span></span></a></div>
                    <div class="b-layout__txt b-promo__quot"><?= reformat($feedbacksFromEmp[$i]['descr'], 30, 0, 0, 1) ?></div>
                </td>
                <? } else { ?>
                <td class="b-layout__one b-layout__one_padright_25"></td>
                <td class="b-layout__one b-layout__one_padbot_40 b-layout__one_width_50ps"></td>
                <? } ?>
                <? if ($feedbacksFromFrl[$i]) { ?>
                <td class="b-layout__one b-layout__one_padright_25 b-layout__one_padleft_50"><a class="b-layout__link" title="<?= $feedbacksFromFrl[$i]['uname'] . ' ' . $feedbacksFromFrl[$i]['usurname'] ?>" href="/users/<?= $feedbacksFromFrl[$i]['login'] ?>/"><?= view_avatar($feedbacksFromFrl[$i]['login'], $feedbacksFromFrl[$i]['photo']) ?></a></td>
                <td class="b-layout__one b-layout__one_padbot_40 b-layout__one_width_50ps">
                    <div class="b-user b-user_padbot_10"><a class="b-user__link" title="<?= $feedbacksFromFrl[$i]['uname'] . ' ' . $feedbacksFromFrl[$i]['usurname'] ?>" href="/users/<?= $feedbacksFromFrl[$i]['login'] ?>/"><?= $feedbacksFromFrl[$i]['uname'] . ' ' . $feedbacksFromFrl[$i]['usurname'] ?> <span class="b-user__login b-user__login_color_fd6c30"><span class="b-user__login-name"><?= $feedbacksFromFrl[$i]['login'] ?></span></span></a></div>
                    <div class="b-layout__txt b-promo__quot"><?= reformat($feedbacksFromFrl[$i]['descr'], 30, 0, 0, 1) ?></div>
                </td>
                <? } else { ?>
                <td class="b-layout__one b-layout__one_padright_25 b-layout__one_padleft_50"></td>
                <td class="b-layout__one b-layout__one_padbot_40 b-layout__one_width_50ps"></td>
                <? } ?>
            </tr>    
        <? } ?>
    </table>
<? } ?>