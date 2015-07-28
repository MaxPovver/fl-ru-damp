                        <div id="rating-tpl" style="display: none; float: right; margin: 0pt 0pt 0pt 25px; width: 270px;">
                            <p style="margin: 0 0 11px 0;">Пожалуйста оцените сотрудничество с фрилансером по трем критериям.</p>
                        </div>

                        <div class="utxt ops-nr-utxt"  id="message-tpl" style="display:none; margin:0">
                            <form method="POST" action="">
                            <div>
                                <input type="hidden" name="id" value="" />
                                <input type="hidden" name="stage_id" value="" />
                                <input type="hidden" name="p_rate" value="" />
                                <input type="hidden" name="n_rate" value="" />
                                <input type="hidden" name="a_rate" value="" />
                                <input type="hidden" name="login" value="" />
                                <div class="ops-nr-e">
                                    <textarea name="to_user_feedback" onkeydown="check_length(this)" cols="10" rows="5"></textarea>
                                    <div class="errorBox" style="display:none;">
                                        <img width="22" height="18" src="/images/ico_error.gif" alt="" />
                                        <span></span>
                                    </div>
                                    <div class="ops-nr-e-btns">
                                        <input onclick="saveRating()" type="button" value="Сохранить" />&nbsp;&nbsp;<a href="#" class="lnk-dot-666" onclick="closeForm(); return false;">Отменить</a>
                                    </div>
                                </div>
                            </div>
                            </form>
                        </div>

                        <? if ($user->uid == get_uid() && !is_emp($user->role)) { ?>
                        <div class="response-inform">
                            <div class="form">
                                <b class="b1"></b>
                                <b class="b2"></b>
                                <div class="form-in">Здесь отображаются Рекомендации работодателей, которые оставлены вам по результатам сотрудничества через сервис «Безопасная Сделка». Для того чтобы получить Рекомендацию, вам необходимо выполнять проекты через данный сервис. </div>
                                <b class="b2"></b>
                                <b class="b1"></b>
                            </div>                            
                        </div>
                        <? } ?>

                        <?
                        if ($msgs) {
                            $i = 0;
                            $print_sbr_name = true;
                            for ($i = 0; $i < count($msgs); $i++) {
                                $theme = $msgs[$i];
                                
                                $cnt_role = is_emp($theme['role']) ? 'emp' : 'frl';
                                $user = new users();
                                $user->GetUserByUID($theme['fromuser_id']);
                                $print_sbr_name = true;
                                
                                include(dirname(__FILE__).'/norisk_op.php');
                                continue;
                                ?>
                                <div class="ops-one c <?php $i == 0 ? 'first' : ''; ?>">
                                    <b class="ops-vs"></b>
                                    <div class="ops-one-cnt">
                                        <a href="" class="employer-name"><?= strtr(view_avatar($theme['login'], $theme['photo']), array('<img' => '<img style="float: left"')) ?></a>
                                        <div class="user-info" style="height:auto">
                                            <div class="username"><?= view_user($user); ?>
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
                                                ?>
                                                <a name="s_<?= $theme['id'] ?>"><img src="/images/1.gif" width="1" height="1" alt="" /></a>
                                                <div class="ops-nr-stage ops-nr-stage-first" id="ops_stage_<?= $theme['id'] ?>">
                                                    <b class="b1"></b>
                                                    <b class="b2"></b>
                                                    <div class="ops-nr-stage-in">
                                                        <ul class="ops-i">
                <?php if ($edited) { ?>
                                                                <li><img src="/images/ico-e-u.png" title="Редактировал <?= $edited; ?>" alt="Редактировал <?= $edited; ?>" /></li>
                <?php } ?>
                                                            <li class="ops-time"><?= $posted; ?></li>

                                                            <li><a onclick="hlAnchor('s',<?= $theme['id'] ?>)" href="#s_<?= $theme['id'] ?>" class="ops-anchor">#</a></li>
                                                        </ul>


                                                        <?php
                                                        if ($print_sbr_name) {
                                                            // Первый этап
                                                            $print_sbr_name = false;

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
                                                            ?>
                                                        <?php } ?>

                                                        <?php if ($theme['stage_status'] == sbr_stages::STATUS_ARBITRAGED) {// Первый этап ?>
                                                            <h3 class="ops-nr-arb">Завершено арбитражем</h3>
                                                        <?php } ?>

                                                        <?php $sStageName = reformat($theme['stage_name'], 40, 0, 1); ?>
                                                        <?php if ($uid == $theme['frl_id'] || $uid == $theme['emp_id']): ?>
                                                            <p>Этап: <a href="/norisk2/?site=Stage&id=<?= $theme['stage_id'] ?>"><?= $sStageName ?></a></p>
                                                        <?php else: ?>
                                                            <p>Этап: <?= $sStageName ?></p>
                                                        <?php endif; ?>


                <?php if ((int) $theme['sub_category']) { ?>
                                                            <p><span>Категория: <?php echo professions::GetProfNameWP($theme['sub_category']) ?></span></p>
                                                <?php } ?>
                                                    </div>
                                                    <b class="b2"></b>
                                                    <b class="b1"></b>
                                                </div>
                <?php if ($theme['stage_status'] != sbr_stages::STATUS_ARBITRAGED) { ?>
                                                    <ul class="vote ops-nr-vote" id="rating<?= $theme['id'] ?>">
                                                        <li class="c">
                                                            <label>Профессионализм</label>
                                                            <span id="p_stars_<?= $theme['id'] ?>" class="stars-vote vote-<?= $theme['p_rate'] ?>">
                                                                <span>
                    <?
                    echo drawStars('p', $theme['id']);
                    ?>
                                                                </span>
                                                            </span>
                                                        </li>
                                                        <li class="c">
                                                            <label>Надежность</label>
                                                            <span id="n_stars_<?= $theme['id'] ?>" class="stars-vote vote-<?= $theme['n_rate'] ?>">
                                                                <span>
                    <?
                    echo drawStars('n', $theme['id']);
                    ?>
                                                                </span>
                                                            </span>
                                                        </li>
                                                        <li class="c">
                                                            <label>Корректность</label>
                                                            <span id="a_stars_<?= $theme['id'] ?>" class="stars-vote vote-<?= $theme['a_rate'] ?>">
                                                                <span>
                    <?
                    echo drawStars('a', $theme['id']);
                    ?>
                                                                </span>
                                                            </span>
                                                        </li>
                                                    </ul>
                                                <? } ?>
                                                <div class="utxt ops-nr-utxt<?= $theme['stage_status'] == sbr_stages::STATUS_ARBITRAGED ? ' ops-nr-arb' : ''; ?>">
                                                    <p id="message<?= $theme['id'] ?>"><?= reformat($theme['descr'], 30, 0, 1, 1) ?></p>
                                                </div>
                                                <?php if ($theme['fromuser_id'] == $_SESSION['uid'] || hasPermissions('users')) { //Мой комент или я админ ?>
                                                    <ul class="opsa-op" id="edit_block_<?= $theme['id'] ?>">
                                                        <li><a href="#" onclick="feedbackEditForm(<?= $theme['stage_id'] ?>,<?= $theme['id'] ?>,'<?= $theme['login'] ?>'); return false;" class="lnk-dot-red">Редактировать</a></li>
                                                    </ul>
                <?php } ?>
                                <? } while ($curr_sbr_id == $msgs[$i + 1]['sbr_id']); ?>
                                        </div>
                                    </div>
                                </div>

        <? };
    } ?>


<div id='no_messages' style='font-size:12px; display: <?= $msgs ? 'none' : 'block' ?>'>
    <br />
    <table>
        <tr>
            <td>&nbsp;</td>
            <td style="padding-bottom: 10px;">Сообщений нет</td>
            <td>&nbsp;</td>
        </tr>
    </table>
</div>

