<div class="b-fon <?= ($i > 1 ? "b-fon_padtop_40" : "")?>" id="sbrList<?= $curr_sbr->id;?>">
    <div class="b-fon__body b-fon__body_pad_5_0_15_15 b-fon__body_fontsize_13 b-fon__body_bg_fff">
        <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full">
            <tbody>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_padbot_10">
                        <h2 class="b-layout__title b-layout__title_pad_null b-layout__title_lineheight_28"><?= reformat($curr_sbr->data['name'], 35, 0, 1) ?> <?= $curr_sbr->getContractNum() ?></h2>
                    </td>
                    <td class="b-layout__right b-layout__right_width_575 b-layout__right_padbot_10 b-layout__right_valign_bot" >
                        <div class="b-username b-username_padbot_10">
                            <a href="/users/<?= $curr_sbr->data[$curr_sbr->apfx . 'login'] ?>/" class="b-username__link b-username__link_color_f2922a" target="_blank"><?= ($curr_sbr->data[$curr_sbr->apfx . 'uname'] . ' ' . $curr_sbr->data[$curr_sbr->apfx . 'usurname']); ?></a> 
                            <span class="b-username__login b-username__login_color_f2922a">
                                [<a href="/users/<?= $curr_sbr->data[$curr_sbr->apfx . 'login'] ?>/" target="_blank" class="b-username__link"><?= $curr_sbr->data[$curr_sbr->apfx . 'login'] ?></a>] 
                                <span class="b-username__marks">
                                    <?//= view_mark_user($curr_sbr->data, $curr_sbr->apfx) ?>
                                    <? $apfx = $curr_sbr->apfx; ?><?= view_mark_user_div($curr_sbr->data[$apfx . "is_pro"] === "t", $apfx === "emp_", $curr_sbr->data[$apfx . "is_team"] === 't', "") ?><?= $curr_sbr->data[$curr_sbr->apfx . 'is_verify'] == 't' ? view_verify() : '';?>
                                </span>
                            </span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <? 
        $stpos = 0;
        $stcount = sizeof($curr_sbr->stages);
        foreach ($curr_sbr->stages as $num => $stage) { $stage->initNotification(); $stpos++;?>
            <? include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.stage-list.php") ?>
        <? }//foreach?>
        
        <style type="text/css">
		.b-fon__body_pad_5_0_15_15 .b-layout__txt_padleft_35{ padding-left:20px;}
		</style>
        
    </div>
</div>
<div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_lineheight_1 b-layout__txt_padleft_35">
    <form action="?id=<?= $curr_sbr->id;?>" method="post" id="currentsFrm<?= $curr_sbr->id;?>">
        Ваша заявка отправлена исполнителю. Подождите, пока он не согласится на сделку.
        <a class="b-layout__link b-layout__link_color_c10600" href="javascript:void(0)" onclick="if(confirm('Отменить сделку?')) { submitForm(document.getElementById('currentsFrm<?= $curr_sbr->id;?>'), {cancel:1}); }">Отменить сделку</a>
        <input type="hidden" name="cancel" value="" />
        <input name="id" value="<?= $curr_sbr->id;?>" type="hidden">
        <input name="action" value="status_action" type="hidden">
    </form>
</div>