<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
if(!$sbr) exit;

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/sbr.common.php");
$xajax->printJavascript('/xajax/');
?>
<h2 class="b-layout__title b-layout__title_padbot_20">Автозагрузка актов и с/ф</h3>

<form name="frm" method="post" action="" enctype="multipart/form-data">
    <input type="hidden" name="action" value="parse_report"/>
    <div class="form form-cnc">
        <b class="b1"></b>
        <b class="b2"></b>
        <div class="form-in">
            <div class="form-block first">
                <h3>Загрузка отчета</h3>
                <div class="form-el">
                    <div class="form-value">
                        <input type="file" name="report" class=""/>
                    </div>
                </div>
            </div>
            <div class="form-block last">
                <div class="form-el form-btns flm">
                    <button type="submit">Загрузить</button>
                </div>
            </div>
        </div>
        <b class="b2"></b>
        <b class="b1"></b>
    </div>
</form>

<blockquote>* <b>Статус</b> - отображает статус обработки. Принимает два значения: в очереди - ожидание обработки, обработано - документы сформированы</blockquote>
<form action="." method="get" id="invoice_form">
    <input type="hidden" name="site" value="invoice" />
    <input type="hidden" name="f_orderby" value="<?= $filter['f_orderby'] ?>" />
    <input type="hidden" name="f_desc" value="<?= $filter['f_desc'] ?>" />
<table class="tbl-cnc">
    <thead>
        <tr>
            <th width="50">
                СБР<br />
                <a href="<?= $orderLink ?>&f_orderby=sbr&f_desc=0"><img width="11" height="11" alt="v" src="/images/arrow-bottom<?= $filter['f_orderby'] === 'sbr' && !$filter['f_desc'] ? '-a' : '' ?>.png" /></a>
                <a href="<?= $orderLink ?>&f_orderby=sbr&f_desc=1"><img width="11" height="11" alt="v" src="/images/arrow-top<?= $filter['f_orderby'] === 'sbr' && $filter['f_desc'] ? '-a' : '' ?>.png" /></a>
            </th>
            <th width="60">
                Логин<br />
                <a href="<?= $orderLink ?>&f_orderby=login&f_desc=0"><img width="11" height="11" alt="v" src="/images/arrow-bottom<?= $filter['f_orderby'] === 'login' && !$filter['f_desc'] ? '-a' : '' ?>.png" /></a>
                <a href="<?= $orderLink ?>&f_orderby=login&f_desc=1"><img width="11" height="11" alt="v" src="/images/arrow-top<?= $filter['f_orderby'] === 'login' && $filter['f_desc'] ? '-a' : '' ?>.png" /></a>
            </th>
            <th width="80">
                #Аккр.<br />
                <a href="<?= $orderLink ?>&f_orderby=akkr&f_desc=0"><img width="11" height="11" alt="v" src="/images/arrow-bottom<?= $filter['f_orderby'] === 'akkr' && !$filter['f_desc'] ? '-a' : '' ?>.png" /></a>
                <a href="<?= $orderLink ?>&f_orderby=akkr&f_desc=1"><img width="11" height="11" alt="v" src="/images/arrow-top<?= $filter['f_orderby'] === 'akkr' && $filter['f_desc'] ? '-a' : '' ?>.png" /></a>
            </th>
            <th width="80">
                ДатаАкта<br />
                <a href="<?= $orderLink ?>&f_orderby=actdate&f_desc=0"><img width="11" height="11" alt="v" src="/images/arrow-bottom<?= $filter['f_orderby'] === 'actdate' && !$filter['f_desc'] ? '-a' : '' ?>.png" /></a>
                <a href="<?= $orderLink ?>&f_orderby=actdate&f_desc=1"><img width="11" height="11" alt="v" src="/images/arrow-top<?= $filter['f_orderby'] === 'actdate' && $filter['f_desc'] ? '-a' : '' ?>.png" /></a>
            </th>
            <th width="100">
                Дата Счета-ф<br />
                <a href="<?= $orderLink ?>&f_orderby=invdate&f_desc=0"><img width="11" height="11" alt="v" src="/images/arrow-bottom<?= $filter['f_orderby'] === 'invdate' && !$filter['f_desc'] ? '-a' : '' ?>.png" /></a>
                <a href="<?= $orderLink ?>&f_orderby=invdate&f_desc=1"><img width="11" height="11" alt="v" src="/images/arrow-top<?= $filter['f_orderby'] === 'invdate' && $filter['f_desc'] ? '-a' : '' ?>.png" /></a>
            </th>
            <th width="60">
                Сумма<br />
                <a href="<?= $orderLink ?>&f_orderby=sum&f_desc=0"><img width="11" height="11" alt="v" src="/images/arrow-bottom<?= $filter['f_orderby'] === 'sum' && !$filter['f_desc'] ? '-a' : '' ?>.png" /></a>
                <a href="<?= $orderLink ?>&f_orderby=sum&f_desc=1"><img width="11" height="11" alt="v" src="/images/arrow-top<?= $filter['f_orderby'] === 'sum' && $filter['f_desc'] ? '-a' : '' ?>.png" /></a>
            </th>
            <th width="70">
                Статус *<br />
                <a href="<?= $orderLink ?>&f_orderby=status&f_desc=0"><img width="11" height="11" alt="v" src="/images/arrow-bottom<?= $filter['f_orderby'] === 'status' && !$filter['f_desc'] ? '-a' : '' ?>.png" /></a>
                <a href="<?= $orderLink ?>&f_orderby=status&f_desc=1"><img width="11" height="11" alt="v" src="/images/arrow-top<?= $filter['f_orderby'] === 'status' && $filter['f_desc'] ? '-a' : '' ?>.png" /></a>
            </th>
            <th>
                Сообщ
            </th>
        </tr>
    </thead>
    <tbody>
        <tr class="pd">
            <td>
                <div class="b-input">
                    <input class="b-input__text filter_input" type="text" name="f_sbr" value="<?= $filter['f_sbr'] ?>" />
                </div>
            </td>
            <td>
                <div class="b-input">
                    <input class="b-input__text filter_input" type="text" name="f_login" value="<?= $filter['f_login'] ?>" />
                </div>
            </td>
            <td>
                <div class="b-input">
                    <input class="b-input__text filter_input" type="text" name="f_akkr" value="<?= $filter['f_akkr'] ?>" />
                </div>
            </td>
            <td>
                <div class="b-input">
                    <input class="b-input__text filter_input" type="text" name="f_actdate"  value="<?= $filter['f_actdate'] ?>" />
                </div>
            </td>
            <td>
                <div class="b-input">
                    <input class="b-input__text filter_input" type="text" name="f_invdate"  value="<?= $filter['f_invdate'] ?>" />
                </div>
            </td>
            <td>
                <div class="b-input">
                    <input class="b-input__text filter_input" type="text" name="f_sum"  value="<?= $filter['f_sum'] ?>" />
                </div>
            </td>
            <td>
                <select name="f_status" class="filter_select">
                    <option value="-1" <?= $filter['f_status'] == -1 ? 'selected' : '' ?>>Все</option>
                    <? foreach (sbr_adm::$invoice_state as $st_id => $st_name) { ?>
                    <option value="<?= $st_id ?>" <?= $filter['f_status'] == $st_id ? 'selected' : '' ?>><?= $st_name ?></option>
                    <? } ?>
                </select>
            </td>
            <td>
                <a id="filter_apply" href="javascript:void(0)" class="b-button b-button_rectangle_transparent_small">
                    <span class="b-button__b1">
                        <span class="b-button__b2">
                            <span class="b-button__txt">Применить</span>
                        </span>
                    </span>
                </a>                
                <a id="filter_reset" href="javascript:void(0)" class="b-button b-button_rectangle_transparent_small">
                    <span class="b-button__b1">
                        <span class="b-button__b2">
                            <span class="b-button__txt">Сбросить</span>
                        </span>
                    </span>
                </a>                
                
            </td>
        </tr>
        <? if ($data) { ?>
            <? foreach ($data as $row) { ?>
                <tr id="query<?= $row['id'] ?>">
                    <td id="pp-place-<?= $row['sbr_id'] ?>">
                        <?= !$row['sbr_id'] ? ' - ' : $row['sbr_id'] ?>
                    </td>
                    <td>
                        <?= $row['login'] ?>
                    </td>
                    <td>
                        <? if($row['sbr_id']) { ?>
                        <a href="javascript:void(0)" onclick="xajax_aGetLCInfo(<?= $row['sbr_id'] ?>)"><?= $row['lc_id'] ?></a>
                        <? } else { ?>
                        <?= $row['lc_id'] ?>
                        <? } ?>
                    </td>
                    <td>
                        <?= date('d.m.Y', strtotime($row['actdate'])) ?>
                    </td>
                    <td>
                        <?= date('d.m.Y', strtotime($row['invdate'])) ?>
                    </td>
                    <td>
                        <?= $row['sum'] ?>
                    </td>
                    <td style="<?= $row['status'] == 2 ? 'color: red' : '' ?><?= $row['status'] == 1 ? 'color: green' : '' ?>">
                        <?= sbr_adm::$invoice_state[$row['status']] ?>
                    </td>

                    <td style="<?= $row['err'] == 2 ? 'color: red' : '' ?>" class="c-prd <?= $order == 'act' ? 'c-id' : '' ?>">
                        <?= $row['err'] ?>
                    </td>
                </tr>
            <? } ?>
        <? } else { ?>
            <tr>
                <td colspan="8">
                    Ничего не найдено
                </td>
            </tr>
        <? } ?>
        <? if ($pagesCount > 1) { ?>
            <tr>
                <td colspan="8">
                    <div class="pager">
                        <?= new_paginator($page, $pagesCount, 10, "%s?site=invoice$filterParams&page=%d%s") ?>
                    </div>
                </td>
            </tr>
        <? } ?>
    </tbody>
</table>
</form>
<script type="text/javascript">
    (function(){
        window.addEvent('domready', function(){
            var 
                filterInputs = $$('.filter_input'),
                filterSelects = $$('.filter_select'),
                filterForm = $('invoice_form'),
                filterApply = $('filter_apply'),
                filterReset = $('filter_reset');
            
            // сабмит по энтеру
            filterInputs.addEvent('keypress', function(event){
                if (event.key === 'enter') {
                    filterForm.submit();
                }
            });
            
            // сабмит при выборе в селекте
            /*filterSelects.addEvent('change', function(event){
                filterForm.submit();
            });*/
        
            filterApply.addEvent('click', function(){
                filterForm.submit();
            });
            
            filterReset.addEvent('click', function(){
                filterInputs.set('value', '');
                filterSelects.set('value', '-1');
                filterForm.submit();
            });
            
            // выделение текста при фокусе
            filterInputs.addEvent('focus', function(event){
                this.select();
            }).addEvent('mouseup', function(event){
                event.preventDefault();
            });
            
            
        });
    })()
</script>


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