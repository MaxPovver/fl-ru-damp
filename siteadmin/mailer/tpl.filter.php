<?$usersSender = mailer::getUsersSender();?>
<form method="GET" id="filter_form">
    <input type="hidden" name="act" value="filter">
    <input type="hidden" name="sort" value="<?=$filter['sort']?>">
    <div class="b-ext-filter b-ext-filter_margbot_15">
        <div class="b-ext-filter__inner">
            <div class="b-ext-filter__body">
                <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_15" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="b-layout__left b-layout__left_width_90">
                            <div class="b-layout__txt b-layout__txt_padtop_2 b-layout__txt_fontsize_11">Получатели</div>
                        </td>
                        <td class="b-layout__one b-layout__one_width_130">
                            <div class="b-check b-check_padtop_3">
                                <input id="b-check1" class="b-check__input" name="frl" type="checkbox" value="1" <?= $filter['frl']?"checked":""?>/>
                                <label for="b-check1" class="b-check__label">Фрилансеры</label>
                            </div>
                        </td>
                        <td class="b-layout__one b-layout__one_width_170">
                            <div class="b-check b-check_padtop_3">
                                <input id="b-check2" class="b-check__input" name="emp" type="checkbox" value="1" <?= $filter['emp']?"checked":""?>/>
                                <label for="b-check2" class="b-check__label">Работодатели</label>
                            </div>
                        </td>
                        <td class="b-layout__right">
                            <div class="b-select">
                                <label class="b-select__label b-select__label_fontsize_11" for="b-select__select">Отправитель&#160;&#160;</label>
                                <select id="b-select__select" name="users" class="b-select__select b-select__select_width_140">
                                    <option value="0">Любой</option>
                                    <?php if($usersSender) {?>
                                        <?php foreach($usersSender as $user) { ?>
                                        <option value="<?=$user['uid']?>" <?= ($filter['users'] == $user['uid']?'selected="selected"':'');?>><?= "{$user['uname']} {$user['usurname']} [{$user['login']}]"?></option>
                                        <?php }//foreach?>
                                    <?php }//if?>
                                </select>
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_15" border="0" cellpadding="0" cellspacing="0">
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_width_90">
                            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_5">Дата</div>
                        </td>
                        <td class="b-layout__right">
                            <div class="b-combo b-combo_inline-block">
                                <div class="b-combo__input b-combo__input_calendar b-combo__input_width_110 b-combo__input_arrow-date_yes use_past_date no_set_date_on_load">
                                    <input id="date_from" class="b-combo__input-text" name="from" type="text" size="80" value="<?= $filter['from'] !== null ? $filter['from'] : date('01.m.Y')?>" />
                                    <span class="b-combo__arrow-date"></span>
                                </div>
                            </div>
                            <div class="b-layout__txt b-layout__txt_padtop_3 b-layout__txt_inline-block">&#160;&mdash;&#160;</div>
                            <div class="b-combo b-combo_inline-block">
                                <div class="b-combo__input b-combo__input_calendar b-combo__input_width_100 b-combo__input_arrow-date_yes use_past_date no_set_date_on_load">
                                    <input id="date_to" class="b-combo__input-text" name="to" type="text" size="80" value="<?= $filter['to'] !== null  ? $filter['to'] : date('d.m.Y')?>" />
                                    <span class="b-combo__arrow-date"></span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
                <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_15" border="0" cellpadding="0" cellspacing="0">
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_width_90">
                            <label class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_lineheight_13">Ключевые<br />слова</label>
                        </td>
                        <td class="b-layout__right">
                            <div class="b-combo">
                                <div class="b-combo__input">
                                    <input id="c1" class="b-combo__input-text" onkeyup="if(event.keyCode == 13) $('filter_form').submit();" name="keyword" type="text" value="<?= stripslashes($filter['keyword'])?>" size="80" />
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
                <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_15" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="b-layout__left b-layout__left_width_90">
                            <div class="b-layout__txt b-layout__txt_padtop_2 b-layout__txt_fontsize_11">Тип</div>
                        </td>
                        <td class="b-layout__one b-layout__one_width_130">
                            <div class="b-check b-check_padtop_3">
                                <input id="b-check3" class="b-check__input" name="sending" type="checkbox" value="1" <?= $filter['sending']?"checked":""?>/>
                                <label for="b-check3" class="b-check__label">Разосланные</label>
                            </div>
                        </td>
                        <td class="b-layout__one b-layout__one_width_130">
                            <div class="b-check b-check_padtop_3">
                                <input id="b-check4" class="b-check__input" name="draft" type="checkbox" value="1" <?= $filter['draft']?"checked":""?>/>
                                <label for="b-check4" class="b-check__label">Черновики</label>
                            </div>
                        </td>
                        <td class="b-layout__one b-layout__one_width_170">
                            <div class="b-check b-check_padtop_3">
                                <input id="b-check5" class="b-check__input" name="regular" type="checkbox" value="1" <?= $filter['regular']?"checked":""?>/>
                                <label for="b-check5" class="b-check__label">Регулярные: рассылаемые</label>
                            </div>
                        </td>
                        <td class="b-layout__one">
                            <div class="b-check b-check_padtop_3">
                                <input id="b-check6" class="b-check__input" name="pause" type="checkbox" value="1" <?= $filter['pause']?"checked":""?>/>
                                <label for="b-check6" class="b-check__label">Регулярные: на паузе</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="b-layout__left b-layout__left_width_90">
                            <div class="b-layout__txt b-layout__txt_padtop_2 b-layout__txt_fontsize_11">&nbsp;</div>
                        </td>
                        <td class="b-layout__one">
                            <div class="b-check b-check_padtop_3">
                                <input id="digest-check6" class="b-check__input" name="digest" type="checkbox" value="1" <?= $filter['digest']?"checked":""?>/>
                                <label for="digest-check6" class="b-check__label">Дайджест</label>
                            </div>
                        </td>
                        <td class="b-layout__one" colspan="3">
                            <div class="b-check b-check_padtop_3">
                                <input id="mailer-check6" class="b-check__input" name="mailer" type="checkbox" value="1" <?= $filter['mailer']?"checked":""?>/>
                                <label for="mailer-check6" class="b-check__label">Рассылка</label>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="b-buttons b-buttons_padleft_87 b-buttons_padbot_20">
                <a class="b-button b-button_flat b-button_flat_grey"  href="javascript:void(0)" onclick="$('filter_form').submit();">Отфильтровать рассылки</a>
                <a href="javascript:void(0)" class="b-layout__link b-layout__link_italic b-layout__link_bordbot_dot_41" onclick="clearMainFilter();">Сбросить фильтр</a>
            </div>
        </div><!-- b-ext-filter__body -->
    </div><!-- b-ext-filter -->
<input type="hidden" name="page" value="<?= $page?>">
</form>
