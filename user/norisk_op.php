<?php
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
$edited = $theme['update_time'] ? date("d.m.Y H:i", strtotime($theme['update_time'])) : false;
$posted = $theme['posted_time'] ? date("d.m.Y H:i", strtotime($theme['posted_time'])) : false;
$show[$theme['id']] = true; 
?>
<div class="ops-one c">
    <div class="ops-one-cnt">
        <ul class="ops-i">
            <?php if ($edited) { ?>
                <li><img src="/images/ico-e-u.png" title="Отедактировано <?= $edited; ?>" alt="Редактировал <?= $edited; ?>" /></li>
            <?php } ?>
            <li class="ops-time"><?= $posted; ?></li>
            <li><a onclick="hlAnchor('s',<?= $theme['id'] ?>)" href="#s_<?= $theme['id'] ?>" class="ops-anchor">#</a></li>
        </ul>
        <?= strtr(view_avatar($theme['login'], $theme['photo']), array('<img' => '<img style="user-avatar"')) ?>
        <div class="user-info">
            <div class="username" style="font-size: 12px"><?= __prntUsrInfo($user); ?>
                <? if (hasPermissions('users')) { ?>
                <i>На сайте <?= ElapsedMnths(strtotime($user->reg_date)) ?></i>
                <? } ?>
            </div>
            <? $curr_sbr_id = $theme['sbr_id']; ?>
            <?
            --$i;
            do {
                $theme = $msgs[++$i];
                $edited = $theme['update_time'] ? date("d.m.Y H:i", strtotime($theme['update_time'])) : false;
                $posted = $theme['posted_time'] ? date("d.m.Y H:i", strtotime($theme['posted_time'])) : false;
                $cls_suff = $theme['sbr_rating'] == -1 
                          ? 'minus' 
                          : ($theme['sbr_rating'] == 1 ? 'plus' : 'neitral');
                ?>
                <a name="s_<?= $theme['id'] ?>"></a>
                <div id="cont_<?= $theme['id'] ?>" class="ops-nr-stage ops-nr-stage-first ops-one-<?= $cls_suff;?>">
                    
                    <b class="ops-vs"></b>
                    <b class="b1"></b>
                    <b class="b2"></b>
                    <div class="ops-nr-stage-in">
                        <?php if($show[$theme['id']] !== true){ ?>
                        <ul class="ops-i">
                            <?php if ($edited) { ?>
                                <li><img src="/images/ico-e-u.png" title="Отедактировано <?= $edited; ?>" alt="Редактировал <?= $edited; ?>" /></li>
                            <?php } ?>
                            <li class="ops-time"><?= $posted; ?></li>
                            <li><a onclick="hlAnchor('s',<?= $theme['id'] ?>)" href="#s_<?= $theme['id'] ?>" class="ops-anchor">#</a></li>
                        </ul>
                        <?php }?>
                        <?php
                        if ($print_sbr_name) {
                            // Первый этап


                            $sSbrNameText = reformat($theme['sbr_name'], 40, 0, 1);

                            if ($uid == $theme['frl_id'] || $uid == $theme['emp_id']) {
                                ?>
                                <h3><a href="/norisk2/?id=<?= $theme['sbr_id'] ?>"><?= $sSbrNameText ?></a></h3>
                                <?php
                            } elseif ($theme['project_id']) {
                                ?>
                                <h3><a href="/projects/?pid=<?= $theme['project_id'] ?>"><?= $sSbrNameText ?></a></h3>
                                <?php
                            } else {
                                ?>
                                <h3><?= $sSbrNameText ?></h3>
                                <?php
                            }
                        }
                        ?>

                        <?php if ($theme['stage_status'] == sbr_stages::STATUS_ARBITRAGED) {// арбитраж ?>
                            <h3 class="ops-nr-arb">Завершено арбитражем</h3>
                        <?php } ?>
                        <?php $sStageName = reformat($theme['stage_name'], 40, 0, 1); ?>
                        <?php if ($uid == $theme['frl_id'] || $uid == $theme['emp_id']) { ?>
                            <p>Этап: <a href="/norisk2/?site=Stage&id=<?= $theme['stage_id'] ?>"><?= $sStageName ?></a></p>
                        <?php } else { ?>
                            <p>Этап: <?= $sStageName ?></p>
                        <?php } ?>
                        <p><span>Категория: <?php echo professions::GetProfNameWP($theme['sub_category']) ?></span></p>
                        <p><span><?=date("d.m.Y H:i", strtotime($theme['stage_closed']))?></span></p>
                    </div>
                    <b class="b2"></b>
                    <b class="b1"></b>
                </div>
                <? /* !!! классы sbrmsgblock, editsbrblock, editFromSbr не существуют, необходимы для точной обработки логики вызывания через XAJAX функции редактирования нескольких отзывов*/?>
                <div id="op_message_<?= $theme['id'] ?>" class="utxt sbrmsgblock ops-nr-utxt<?php echo $theme['stage_status'] == sbr_stages::STATUS_ARBITRAGED ? ' ops-nr-arb' : ''; ?>">
                    <p><?= reformat($theme['descr'], 30, 0, 1, 1) ?></p>
                </div>
                <?php if ($uid == $user->uid || hasPermissions('users')) { ?>
                    <ul class="opsa-op editsbrblock" id="ops_edit_link_<?= $theme['id'] ?>">
                        <? if (hasPermissions('users') || (strtotime($theme['posted_time'])+3600*24 > time())) { ?>
                        <li><a class="lnk-dot-red" href="javascript:void(0)" onclick="xajax_EditSBROpForm(<?= $theme['id'] ?>, '<?=$user->login?>')">Редактировать</a></li>
                        <? } ?>
                        <? if (hasPermissions('users')) { ?>
                        <li><a class="lnk-dot-red" href="javascript:void(0)" onclick="if(confirm('Уверены??')) xajax_DeleteFeedback(<?= $theme['stage_id'] ?>, <?= $theme['id'] ?>)">Удалить</a></li>
                        <? } ?>
                    </ul>
                    <div id="form_container_<?= $theme['id'] ?>" class="editFormSbr" style="display:none"></div>
                <?php } ?>
                <?
                $print_sbr_name = false;
            } while ($curr_sbr_id == $msgs[$i + 1]['sbr_id']);
            ?>

        </div>
    </div>
</div>
