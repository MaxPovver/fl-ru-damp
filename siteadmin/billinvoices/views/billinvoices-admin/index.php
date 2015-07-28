<?php

//Шаблон списка счетов и формы фильтра

?>
<h3>Заказчики пополнение ЛС</h3>
<div class="form form-acnew b-layout b-layout_padbot_20">
	<b class="b1"></b>
	<b class="b2"></b>
	<div class="form-in">
        <h4 class="toggle"><a class="lnk-dot-666" onclick="var mySlide = new Fx.Slide('slideBlock').toggle();" href="javascript:void(0);">Фильтр</a></h4>
        <div class="slideBlock" id="slideBlock">
            <form action="." method="get" enctype="application/x-www-form-urlencoded">
            <input type="hidden" name="do" value="filter" />
            <div class="form-block first">
                <div class="form-el">
                    <label class="form-l">Логин или номер счета:</label>
                    <div class="form-value fvs">
                        <input size="25" type="text" class="i-txt fvsi" name="login" value="<?=@$filter['login']?>"/>
                    </div>
                </div>
                <div class="form-el">
                    <label class="form-l">За период:</label>
                    <div class="form-value fvs">
                        <input size="25" id="filterDatePicker" type="text" class="i-txt fvsi" name="date" value="<?=@$filter['date']?>"/>
                    </div>
                </div>
            </div>
            <div class="form-block last">
                <div class="form-el form-btns">
                    <button type="submit">Отфильтровать</button>
                </div>
            </div>
            </form>
        </div>
	</div>
	<b class="b2"></b>
	<b class="b1"></b>
</div>

<?php if($list): ?>
<div class="admin-lenta">
    <table>
        <colgroup>
            <col>
            <col>
            <col>
            <col>
            <col>
            <col>
        </colgroup>
        <thead>
            <tr>
                <th>Дата</th>
                <th>Файл</th>
                <th>Логин</th>
                <th>Сумма (руб)</th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($list as $el): ?>
            <tr>
                <td><?=date('d.m.Y H:i', strtotime($el['date']))?></td>
                <td>
                    <a target="_blank" href="<?=WDCPREFIX . '/' . $el['file']?>">
                        <?=$el['name']?>
                    </a>
                </td>
                <td>
                    <a target="_blank" href="<?=sprintf("/users/%s/", $el['login'])?>">
                        <?=$el['login']?>
                    </a>
                </td>
                <td>
                    <?=view_cost_format($el['price'], false)?>
                </td>
                <?php if($el['acc_op_id'] > 0): ?>
                <td>
                    <a target="_blank" href="<?=sprintf("/siteadmin/bill/?login=%s", $el['login'])?>" class="color-45a300">Зачислено</a>
                </td>
                <td>
                    <form action="." method="post" enctype="application/x-www-form-urlencoded">
                        <?php if($el['file_factura_id'] > 0): ?>
                        <a target="_blank" href="<?=WDCPREFIX . '/' . $el['file_factura']?>"><?=$el['name_factura']?></a>&nbsp;&nbsp;
                        <input type="hidden" name="num[<?=$el['uid']?>][<?=$el['invoice_id']?>]" value="<?=$el['file_factura_id']?>" />
                        <button type="submit">Удалить</button>
                        <input type="hidden" name="do" value="factura_delete" /> 
                        <?php else: ?>
                        <input type="text" name="num[<?=$el['uid']?>][<?=$el['invoice_id']?>]" maxlength="10" size="10" />
                        <input data-datepicker="true" type="text" name="date[<?=$el['uid']?>][<?=$el['invoice_id']?>]" maxlength="10" size="10" />
                        <button type="submit">Загрузить СФ</button>
                        <input type="hidden" name="do" value="factura" /> 
                        <?php endif; ?>
                        <input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>" />
                    </form>
                    <?php if($el['file_factura_id'] > 0): ?>
                    <form action="." method="post" enctype="multipart/form-data" class="b-layout_padtop_10">
                        <input type="hidden" name="invoice_id" value="<?=$el['invoice_id']?>" />
                        <input type="file" name="new_file" />
                        <button type="submit">Обновить CФ</button>
                        <input type="hidden" name="do" value="factura_update" /> 
                        <input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>" />
                    </form>
                    <?php endif; ?>
                </td>
                <?php else: ?>
                <td>
                </td>
                <td>
                    <form action="." method="post" enctype="application/x-www-form-urlencoded">
                        <input type="text" name="sum[<?=$el['uid']?>][<?=$el['invoice_id']?>]" maxlength="7" size="10" />
                        <button type="submit">Зачислить</button>
                        <input type="hidden" name="do" value="pay" />    
                    </form>                      
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <?php if($page_count > $limit): ?>
            <tr>
                <td colspan="6">
                    <div class="pager">
                        <?=new_paginator(
                                $page, 
                                ceil($page_count / $limit), 
                                10, 
                                "%s?{$filter_query}page=%d%s") ?>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
        </tfoot>
    </table>
</div>
<?php else: ?>
<div class="b-txt b-txt_center">
    Нет данных за указанный период
</div>
<?php endif; ?>

<script type="text/javascript">
    
    Locale.use('ru-RU');
    
    var filterDatePicker = new Picker.Date.Range($('filterDatePicker'), {
        timePicker: false,
        format: "%d.%m.%Y",
        columns: 3,
        positionOffset: {x: 0, y: 0}
    }); 
    
    var els = $$('input[data-datepicker]');
    
    if (els) {
        els.each(function(el){
            new Picker.Date(el, {
                timePicker: false,
                format: "%d.%m.%Y",
                positionOffset: {x: 0, y: 0}
            });
        });
    }
    
</script>