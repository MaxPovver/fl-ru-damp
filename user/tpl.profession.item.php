<? if($success) { ?>
<div class="b-fon b-fon_width_full b-fon_padbot_17" onclick="$(this).dispose()">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_35 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf b-fon__body_bordbot_dfedcf">
        <span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span>Данные успешно сохранены
    </div>
</div>
<? }//if?>

<div class="stripe-r"><p><?= nl2br(reformat($pinfo['portf_text'], 54, 0, 1)) ?></p></div>    	   	
<div class="stripe-l">
    <h4>
        <a name="<?= $prof_id ?>"></a><a href="/users/<?= $user->login ?>/#<?= $prof_id ?>" class="inherit">#</a>&nbsp;
        <?= access_view('<strong style="color:#000000">' . ($prof_id >= 0 ? $pinfo['mainprofname'] . ' / ' : '') . $pinfo['profname'] . '</strong>', '<a href="/freelancers/' . $pinfo['proflink'] . '/" class="inherit">%s</a>', !$pinfo['is_pro_profession']); ?>
    </h4>
    <?php if ($ukeys[$prof_id]) { ?>
        <p>
            <?= implode(", ", $ukeys[$prof_id]['links_keyword']); ?>
            <?php if ($ukeys[$prof_id]['count'] > kwords::MAX_KWORDS_PORTFOLIO) { ?>
                <span class="prtfl-hellip">&hellip;</span>
                <span class="prfl-tags"><a href="javascript:void(0)">Все <?= $ukeys[$prof_id]['count'] ?> <?= ending($ukeys[$prof_id]['count'], 'тег', 'тега', 'тегов') ?></a></span>
                <span class="prfl-tags-more" style="display:none"><?= implode(',', $ukeys[$prof_id]['links_keyword_hide']) ?></span>
            <? } ?>
        </p>
    <?php } //if?>
    <?php if ($pinfo['proftext'] == 't') { ?>
        <?= access_view('', '<p>Стоимость тысячи знаков: <span class="money">' . $pinfo['cost_text'] . '</span></p>', ($pinfo['cost_text'] != '')); ?>
        <?= access_view('', '<p>Оценка часа работы: <span class="money">' . $pinfo['cost_hour_text'] . '</span></p>', ($pinfo['cost_hour_text'] != '')); ?>
    <?php } else { //if?>
        <?= access_view('', '<p>Стоимость работ: <span class="money">' . $pinfo['from_text'] . " " . $pinfo['to_text'] . '</span></p>', ($pinfo['cost_to_text'] != '' || $pinfo['cost_from_text'] != '')); ?>
        <?= access_view('', '<p>Оценка часа работы: <span class="money">' . $pinfo['cost_hour_text'] . '</span></p>', ($pinfo['cost_hour_text'] != '')); ?>
        <?= access_view('', '<p>Сроки: ' . $pinfo['time_text'] . '</p>', ($pinfo['time_text'] != '')); ?>
    <?php } // else?>

    <?php if (hasPermissions('users') && !$is_owner) { ?>
        <br/>
        <a class="admn" href="javascript:void(0);" onclick="adm_edit_content.editContent('admEditPortfChoice', '<?= $user->uid ?>', 0, '', {'sProfId': <?= $prof_id ?>})">Редактировать</a>
    <?php } elseif ($is_owner) { //if?>
        <br/>
        <a class="admn" href="javascript:void(0);" onclick="portfolio.editContent('openProfession', '<?= $user->uid ?>', {'sProfId': <?= $prof_id ?>})">Редактировать</a>
    <?php }//elseif?>
</div>