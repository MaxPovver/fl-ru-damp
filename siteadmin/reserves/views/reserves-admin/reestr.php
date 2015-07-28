<?php

$form_elements = $form->getElements();

$line_css = 'b-layout__txt b-layout__txt_padtop_5 b-layout__txt_fontsize_11 b-layout_inline-block';

?>
<div class="b-layout b-layout_float_right b-layout__txt_fontsize_13">
    <?php foreach ($menu as $menuMode => $menuLabel): ?>
    <?php if ($mode == $menuMode): ?>
        <span class="b-layout__txt b-layout__txt_bold"><?=$menuLabel?></span>
    <?php else: ?>
        <a class="b-layout__link b-layout__link_bold b-layout__link_bordbot_dot_0f71c8 b-layout__link_fontsize_13" 
           href="?action=reestr&mode=<?=$menuMode?>"><?=$menuLabel?></a>
    <?php endif; ?>
        <span class="b-layout__txt b-layout_margright_10">&nbsp;</span>
    <?php endforeach; ?>
</div>

<div class="b-layout b-layout_float_left">
<form action="." method="get" id="adminFrm">
    <input type="hidden" name="action" value="reestr" />
    <input type="hidden" name="mode" value="<?=$mode?>" />
    <div class="<?= $line_css ?> b-layout__txt_padright_20">
        Период:
    </div>
    <?php foreach ($form_elements as $form_element): ?>
        <?php if ($form_element->getName() == 'date_end'): ?>
            <div class="<?= $line_css ?> b-layout__txt_padleft_5 b-layout__txt_padright_5">
                &mdash;
            </div>
        <?php endif; ?>
        <?= $form_element->render(); ?>
    <?php endforeach; ?>
    <button class="b-button_margleft_10" style="height:27px;" type="submit">Генерировать</button>
</form>
</div>

<?php if (count($submenu)): ?>
<div class="b-layout__txt b-layout__txt_float_left b-layout_padleft_60 b-layout__txt_padtop_3">
    <?php foreach ($submenu as $item): ?>
    <a class="b-layout__link b-layout_margright_10" target="_blank" href="<?=$item['link']?>"><?=$item['anchor']?></a>
    <?php endforeach; ?>
</div>
<?php endif; ?>


<div class="b-layout b-layout_margbot_20 b-layout_padtop_20 b-layout_clear_both">
<?php if ($summary): ?>
    <table class="b-layout__table">
        <tr>
            <td class="b-layout__td b-layout__td_padbot_5 b-layout__td_padright_10">
                <span class="b-layout__txt b-layout__txt_fontsize_13 b-layout__txt_color_646464">
                    Сумма резервирования через Яндекс.Кассу
                </span>
            </td>
            <td class="b-layout__td b-layout__td_padbot_5 b-layout__td_padright_10">
                <span class="b-layout__txt b-layout__txt_fontsize_13 b-layout__txt_bold">
                    <?=view_cost_format($summary['sum_reserve_yk'], true, false, false)?>
                </span>
            </td>
        </tr>
        <tr>
            <td class="b-layout__td b-layout__td_padbot_5 b-layout__td_padright_10">
                <span class="b-layout__txt b-layout__txt_fontsize_13 b-layout__txt_color_646464">
                    Сумма выплат через Яндекс.Кассу
                </span>
            </td>
            <td class="b-layout__td b-layout__td_padbot_5 b-layout__td_padright_10">
                <span class="b-layout__txt b-layout__txt_fontsize_13 b-layout__txt_bold">
                    <?=view_cost_format($summary['sum_pay_yk'], true, false, false)?>
                </span>
            </td>
        </tr>
        <tr>
            <td class="b-layout__td b-layout__td_padbot_5 b-layout__td_padright_10">
                <span class="b-layout__txt b-layout__txt_fontsize_13 b-layout__txt_color_646464">
                    Сумма выплат нерезидентам через Яндекс.Кассу
                </span>
            </td>
            <td class="b-layout__td b-layout__td_padbot_5 b-layout__td_padright_10">
                <span class="b-layout__txt b-layout__txt_fontsize_13 b-layout__txt_bold">
                    <?=view_cost_format($summary['sum_pay_yk_norez'], true, false, false)?>
                </span>
            </td>
        </tr>
        <tr>
            <td class="b-layout__td b-layout__td_padbot_5 b-layout__td_padright_10">
                <span class="b-layout__txt b-layout__txt_fontsize_13 b-layout__txt_color_646464">
                    Сумма возвратов через Яндекс.Кассу
                </span>
            </td>
            <td class="b-layout__td b-layout__td_padbot_5 b-layout__td_padright_10">
                <span class="b-layout__txt b-layout__txt_fontsize_13 b-layout__txt_bold">
                    <?=view_cost_format($summary['sum_back_yk'], true, false, false)?>
                </span>
            </td>
        </tr>
    </table>
<?php endif; ?>
</div>


<?php if (!empty($data)): ?>
<form method="post" action="">
<table class="nr-a-tbl" cellspacing="5" style="table-layout:fixed">
    <colgroup>
        <?php if ($isDocMode): ?>
        <col style="width:20px" />
        <?php endif; ?>
        <?php foreach ($fields as $field):?>
        <col style="width:<?=$field['width']?>px" />
        <?php endforeach; ?>
    </colgroup>
    <thead>
        <tr>
            <?php if ($isDocMode): ?>
            <th></th>
            <?php endif; ?>
            <?php foreach ($fields as $field):?>
            <th><?=$field['name']?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    
    <tbody>
        <?php foreach ($data as $el): ?>
            <tr class="nr-a-tbl_tr">
                <?php if ($isDocMode): ?>
                <td>
                    <input type="checkbox" name="bs_ids[]" value="<?=$el['bs_id']?>" checked="checked" />
                </td>
                <?php endif; ?>
                <?php foreach ($fields as $param=>$field): ?>
                <td><?= $reestrModel->format($el[$param], $param) ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
    <?php if ($isDocMode): ?>
<div class="b-layout b-layout_padtop_20">
    <input type="hidden" name="date_range" value="<?=$form->getReadbleDateInterval()?>" />
    <a id="__create_archive" href="?action=archive" class="b-button b-button_flat b-button_flat_green">
        Создать архив документов
    </a>
</div>
    <?php endif; ?>
</form>    
<?php endif; ?>