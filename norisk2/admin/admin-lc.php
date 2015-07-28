<div class="norisk-admin c">
    <div class="norisk-in">
        <div class="form form-vigruzka">
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in">
                <form action="." method="get" id="adminFrm">
                    <div>
                        <div class="form-block first">
                            <div class="form-el">
                                <label class="form-label">№ «Безопасной Сделки» или аккр.</label>
                                <span class="form-input">
                                    <input type="text" name="search" value="<?= $f_search ?>" />
                                </span>
                            </div>
                            <div class="form-el">
                                <label class="form-label">Статус</label>
                                <span class="form-input">
                                    <select name="state">
                                        <option value="null">-- Не важно --</option>
                                        <? foreach (pskb::$state_adm_messages as $k => $v) { ?>
                                            <option value="<?= $k ?>" <?= $f_state == $k ? 'selected' : '' ?>><?= $v ?></option>
                                        <? } ?>
                                    </select>
                                </span>
                            </div>
                            <div class="form-el">
                                <label class="form-label">ПС Заказчика</label>
                                <span class="form-input">
                                    <select name="ps_emp">
                                        <option value="null">-- Не важно --</option>
                                        <? foreach (pskb::$psys[pskb::USER_EMP] as $k => $v) { ?>
                                            <option value="<?= $k ?>" <?= $f_ps_emp == $k ? 'selected' : '' ?>><?= $v ?></option>
                                        <? } ?>
                                    </select>
                                </span>
                            </div>
                            <div class="form-el">
                                <label class="form-label">ПС Исполнителя</label>
                                <span class="form-input">
                                    <select name="ps_frl">
                                        <option value="null">-- Не важно --</option>
                                        <? foreach (pskb::$psys[pskb::USER_FRL] as $k => $v) { ?>
                                            <option value="<?= $k ?>" <?=  $f_ps_frl == $k ? 'selected' : '' ?>><?= $v ?></option>
                                        <? } ?>
                                    </select>
                                </span>
                            </div>
                            <div class="form-el">
                                <label class="form-label">Дата покрытия</label>
                                <span class="form-input">
                                    <? 
                                    $name_element = 'date_cover';
                                    $filter       = $f_date_cover;
                                    include($_SERVER['DOCUMENT_ROOT'] . '/norisk2/tpl.filter-period.php');
                                    ?>
                                    <a href="javascript:void(0)" onclick="$$('select[name^=date_cover]').each(function(elm){ elm.selectedIndex = 0});">Очистить</a>
                                </span>
                            </div>
                            <div class="form-el">
                                <label class="form-label">Дата закрытия</label>
                                <span class="form-input">
                                    <? 
                                    $name_element = 'date_end';
                                    $filter       = $f_date_end;
                                    include($_SERVER['DOCUMENT_ROOT'] . '/norisk2/tpl.filter-period.php');
                                    ?>
                                    <a href="javascript:void(0)" onclick="$$('select[name^=date_end]').each(function(elm){ elm.selectedIndex = 0});">Очистить</a>
                                </span>
                            </div>
                        </div>
                        <div class="form-block last">
                            <div class="form-el form-btn">
                                <input type="submit" class="i-btn" value="Применить" />
                            </div>
                        </div>
                        <input type="hidden" name="site" value="<?= $site ?>" />
                        <input type="hidden" name="mode" value="<?= $mode ?>" />
                    </div>
                </form>
            </div>
            <b class="b2"></b>
            <b class="b1"></b>
        </div>
    </div>
</div>

<div class="norisk-admin c">
    <div class="norisk-in">
        <table class="nr-a-opinions" cellspacing="0" style="width: 100%">
            <col width="70" />
            <col width="70" />
            <col width="70" />
            <col width="70" />
            <col width="45" />
            <col width="45" />
            <col width="60" />
            <col width="230" />
            <col  />
            <thead>
                <tr>
                    <th>№ «Безопасной Сделки»</th>
                    <th>№ Аккр.</th>
                    <th>Сумма</th>
                    <th>пс-зак</th>
                    <th>пс-исп</th>
                    <th>Покрыт</th>
                    <th>Раскрыт</th>
                    <th>Статус</th>
                    <th>Сообщение банка</th>
                </tr>
                <tr>
                    <td colspan="10">&nbsp;</td>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="11">
                        <div class="pager">
                            <?= new_paginator($page, ceil($page_count / pskb::ADM_PAGE_SIZE), 10, "%s?{$url_build}&page=%d%s") ?>
                        </div>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <? foreach ($pskb_list as $row) { ?>
                    <tr class="<?= (++$j % 2 == 0 ? 'even' : 'odd') ?>">
                        <td id="pp-place-<?= $row['sbr_id'] ?>"><?= $row['sbr_id'] ?></td>
                        <td><a href="javascript:void(0)" onclick="xajax_aGetLCInfo(<?= $row['sbr_id'] ?>)"><?= $row['lc_id'] ?></a></td>
                        <td><?= $row['state'] ? $row['sum'] : '' ?></td>
                        <td><?= pskb::$ps_str[$row['ps_emp']] ?></td>
                        <td><?= pskb::$ps_str[$row['ps_frl']] ?></td>
                        <td><?= $row['covered'] ? date('d.m.Y', strtotime($row['covered'])) : '' ?></td>
                        <td><?= $row['ended'] ? date('d.m.Y', strtotime($row['ended'])) : '' ?></td>
                        <td><?= $row['state'] ? pskb::$state_adm_messages[$row['state']] : 'Ожидание резерва' ?></td>
                        <td><?= $row['stateReason'] ?></td>
                    </tr>
                <? } ?>
            </tbody>
        </table>
    </div>
</div>

<div id="lc-info-popup" class="i-shadow_center  b-shadow_hide">																						
    <div class="b-shadow b-shadow_width_950 b-shadow_zindex_11 b-shadow_hide">
        <div class="b-shadow__right">
            <div class="b-shadow__left">
                <div class="b-shadow__top">
                    <div class="b-shadow__bottom">
                        <div id="lc-info-popup-body" class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-shadow__tl"></div>
        <div class="b-shadow__tr"></div>
        <div class="b-shadow__bl"></div>
        <div class="b-shadow__br"></div>
    </div>
</div>