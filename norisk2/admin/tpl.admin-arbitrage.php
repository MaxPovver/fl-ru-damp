<div class="norisk-admin c">
    <div class="norisk-in">
        <form action="." method="get" id="adminFrm">
            <div>
                <h1 class="b-layout__title"><?= date('j') . ' ' . monthtostr(date('m'), true) . ' ' . date('H:i') ?></h1>

                <table class="nr-a-tbl nr-a-tbl_adm" cellspacing="5" style="table-layout:fixed">
                    <colgroup>
                        <col style="width:40px">
                        <col style="width:90px">
                        <col>
                        <col style="width:150px">
                        <col style="width:110px">
                        <col style="width:80px">
                        <col style="width:130px">
                    </colgroup>
                    <thead>
                        <tr>
                            <th><a href="javascript:SBR.changeFormDir(0,'DESC')"><img width="11" height="11" alt="v" src="/images/arrow-bottom<?= $dir_col == 0 && $dir == 'DESC' ? '-a' : '' ?>.png"></a> <a href="javascript:SBR.changeFormDir(0,'ASC')"><img width="11" height="11" alt="v" src="/images/arrow-top<?= $dir_col == 0 && $dir == 'ASC' ? '-a' : '' ?>.png"></a> </th>
                            <th> Договор <a href="javascript:SBR.changeFormDir(1,'DESC')"><img width="11" height="11" alt="v" src="/images/arrow-bottom<?= $dir_col == 1 && $dir == 'DESC' ? '-a' : '' ?>.png"></a> <a href="javascript:SBR.changeFormDir(1,'ASC')"><img width="11" height="11" alt="v" src="/images/arrow-top<?= $dir_col == 1 && $dir == 'ASC' ? '-a' : '' ?>.png"></a> </th>
                            <th> Проект <a href="javascript:SBR.changeFormDir(2,'DESC')"><img width="11" height="11" alt="v" src="/images/arrow-bottom<?= $dir_col == 2 && $dir == 'DESC' ? '-a' : '' ?>.png"></a> <a href="javascript:SBR.changeFormDir(2,'ASC')"><img width="11" height="11" alt="v" src="/images/arrow-top<?= $dir_col == 2 && $dir == 'ASC' ? '-a' : '' ?>.png"></a> </th>
                            <th> Время посл. ответа <a href="javascript:SBR.changeFormDir(3,'DESC')"><img width="11" height="11" alt="v" src="/images/arrow-bottom<?= $dir_col == 3 && $dir == 'DESC' ? '-a' : '' ?>.png"></a> <a href="javascript:SBR.changeFormDir(3,'ASC')"><img width="11" height="11" alt="v" src="/images/arrow-top<?= $dir_col == 3 && $dir == 'ASC' ? '-a' : '' ?>.png"></a> </th>
                            <th> Ожидаем до <a href="javascript:SBR.changeFormDir(4,'DESC')"><img width="11" height="11" alt="v" src="/images/arrow-bottom<?= $dir_col == 4 && $dir == 'DESC' ? '-a' : '' ?>.png"></a> <a href="javascript:SBR.changeFormDir(4,'ASC')"><img width="11" height="11" alt="v" src="/images/arrow-top<?= $dir_col == 4 && $dir == 'ASC' ? '-a' : '' ?>.png"></a> </th>
                            <th> Арбитр <a href="javascript:SBR.changeFormDir(5,'DESC')"><img width="11" height="11" alt="v" src="/images/arrow-bottom<?= $dir_col == 5 && $dir == 'DESC' ? '-a' : '' ?>.png"></a> <a href="javascript:SBR.changeFormDir(5,'ASC')"><img width="11" height="11" alt="v" src="/images/arrow-top<?= $dir_col == 5 && $dir == 'ASC' ? '-a' : '' ?>.png"></a> </th>
                            <th> Срок арбитража <a href="javascript:SBR.changeFormDir(6,'DESC')"><img width="11" height="11" alt="v" src="/images/arrow-bottom<?= $dir_col == 6 && $dir == 'DESC' ? '-a' : '' ?>.png"></a> <a href="javascript:SBR.changeFormDir(6,'ASC')"><img width="11" height="11" alt="v" src="/images/arrow-top<?= $dir_col == 6 && $dir == 'ASC' ? '-a' : '' ?>.png"></a> </th>
                        </tr>
                        <tr class="pd">
                            <td>&nbsp;</td>
                            <td>
                                <div class="b-input">
                                    <input class="b-input__text" type="text" name="filter[sbr]" value="<?= html_attr($filter['sbr']) ?>" onkeydown="if(event.keyCode==13)SBR.form.submit()" onfocus="this.select()">
                                </div>
                            </td>
                            <td>
                                <div class="b-input">
                                    <input class="b-input__text" type="text" name="filter[stage]" value="<?= html_attr($filter['stage']) ?>" onkeydown="if(event.keyCode==13)SBR.form.submit()" onfocus="this.select()">
                                </div>
                            </td>
                            <td>&nbsp;</td>
                            <td>
                                <div class="b-input">
                                    <input class="b-input__text" type="text" name="filter[date_to_answer]" value="<?= html_attr($filter['date_to_answer']) ?>" onkeydown="if(event.keyCode==13)SBR.form.submit()" onfocus="this.select()">
                                </div>
                            </td>
                            <td>
                                <div class="b-input">
                                    <input class="b-input__text" type="text" name="filter[arbitr_name]" value="<?= html_attr($filter['arbitr_name']) ?>" onkeydown="if(event.keyCode==13)SBR.form.submit()" onfocus="this.select()">
                                </div>
                            </td>
                            <td>
                                <div class="b-input">
                                    <input class="b-input__text" type="text" name="filter[days_left]" value="<?= html_attr($filter['days_left']) ?>" onkeydown="if(event.keyCode==13)SBR.form.submit()" onfocus="this.select()">
                                </div>
                            </td>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <td colspan="6">
                                <div class="pager">
                                    <?=new_paginator($page, ceil($page_count/sbr_adm::PAGE_SIZE), 10, "%s?site=admin&mode=arbitrage{$filter_prms}&dir_col={$dir_col}&dir={$dir}&page=%d%s")?>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?
                        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/LocalDateTime.php';
                        foreach ($sbr_all['data'] as $sbr_data) {
                            // в какой цвет покрасить строку таблицы
                            if (!$sbr_data['last_msg_id_arbitr']) {
                                $color = 'color_fd6c30'; // красный
                            } elseif ($sbr_data['last_msg_id_users'] && $sbr_data['last_msg_id_arbitr'] && $sbr_data['last_msg_id_users'] > $sbr_data['last_msg_id_arbitr']) {
                                $color = 'color_00b0f0'; // синий
                            } else {
                                $color = ''; // черный
                            }
                            ?>
                            <tr class="<?= (++$i%2==0 ? 'even' : 'odd')?> <?= $color ?>">
                                <td class="adm-center"><?= $sbr_data['arbitrage_alert'] === 't' ? '!' : '' ?></td>
                                <td class="adm-center"><?=$sbr->getContractNum($sbr_data['sbr_id'], $sbr_data['scheme_type'], $sbr_data['posted'])?></td>
                                <td class="adm-prj">
                                    <span><a href="?access=A&site=Stage&id=<?= $sbr_data['stage_id'] ?>">#<?= sbr_stages::getOuterNum($sbr_data['stage_id'], $sbr_data['num']) ?> <?= reformat($sbr_data['name'], 30, 0, 1) ?></a></span>
                                </td>
                                <td class="adm-center"><?= $sbr_data['last_msg_post_date'] ? ago_arbitrage_answered(strtotime($sbr_data['last_msg_post_date'])) : 'Нет комментариев' ?></td>
                                <td class="adm-center"><?= date('d.m.Y', strtotime($sbr_data['date_to_answer_'])) ?></td>
                                <td class="adm-center"><?= $sbr_data['arbitr_name'] ?></td>
                                <td class="adm-center"><?= $sbr_data['days_to_end'] . ' ' . ending($sbr_data['days_to_end'], 'день', 'дня', 'дней') ?></td>
                            </tr>
                        <? } ?>
                    </tbody>
                </table>
            </div>
            <input type="hidden" name="site" value="<?=$site?>" />
            <input type="hidden" name="mode" value="<?=$mode?>" />
            <input type="hidden" name="dir_col"  value="<?=$dir_col?>" />
            <input type="hidden" name="dir"  value="<?=$dir?>" />
        </form>
    </div>
</div>