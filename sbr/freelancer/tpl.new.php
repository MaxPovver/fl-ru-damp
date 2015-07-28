<div id="sbrList<?= $curr_sbr->id;?>" class="b-fon b-fon_relative <?= $i > 1 ? "b-fon_margtop_10" : ""?> ">
    <div class="b-fon__body b-fon__body_pad_5_0_15_15 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
        <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full">
            <tbody>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_padbot_10">
                        <h2 class="b-layout__title b-layout__title_pad_null b-layout__title_lineheight_28"><?= reformat($curr_sbr->data['name'], 35, 0, 1) ?> <?= $curr_sbr->getContractNum() ?></h2>
                    </td>
                    <td class="b-layout__right b-layout__right_width_575 b-layout__right_padbot_10 b-layout__right_valign_bot" >
                        <div class="b-username b-username_padbot_10">
                            <a href="/users/<?= $curr_sbr->data[$curr_sbr->apfx . 'login'] ?>/" class="b-username__link b-username__link_color_6db335" target="_blank"><?= ($curr_sbr->data[$curr_sbr->apfx . 'uname'] . ' ' . $curr_sbr->data[$curr_sbr->apfx . 'usurname']); ?></a> 
                            <span class="b-username__login b-username__login_color_6db335">
                                [<a href="/users/<?= $curr_sbr->data[$curr_sbr->apfx . 'login'] ?>/" target="_blank" class="b-username__link"><?= $curr_sbr->data[$curr_sbr->apfx . 'login'] ?></a>] 
                                <span class="b-username__marks"><?
                                    $data = $curr_sbr->data;
                                    $pfx = $curr_sbr->apfx;
                                    ?><?= view_mark_user_div($data[$pfx . "is_pro"] === 't', $pfx === "emp_", $data[$pfx . "is_team"] === 't', "") ?><?= $curr_sbr->data[$curr_sbr->apfx . 'is_verify'] == 't' ? view_verify() : '';?>
                                </span>
                            </span>
                        </div>
                    </td>
                </tr>
                <? foreach ($curr_sbr->stages as $num => $stage) { $stage->initNotification();?>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_padbot_10">
                        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padleft_20 b-layout__txt_fontsize_15  b-layout__txt_lineheight_18"><?= reformat($stage->data['name'], 35, 0, 1) ?></div>
                    </td>
                    <td class="b-layout__right b-layout__right_padbot_10 b-layout__right_width_560">
                        <div class="b-layout__txt">Бюджет этапа <span class="b-layout__bold"><?= sbr_meta::view_cost($stage->data['cost'], $curr_sbr->cost_sys)?></span></div>
                    </td>
                </tr>
                <? }//foreach?>
                <tr class="b-layout__tr">
                    <td class="b-layout__left">
                        <a href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?site=master&id=<?= $curr_sbr->id;?>" class="b-button b-button_margleft_18 b-button_flat b-button_flat_grey">Посмотреть условия сделки</a>
                    </td>
                    <td class="b-layout__right b-layout__right_width_560"></td>
                </tr>
            </tbody>
        </table>
    </div>
    <span class="b-fon__new"></span>
</div>