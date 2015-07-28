<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/tservices_orders.common.php");
$xajax->printJavascript('/xajax/');

?>
<ul class="frl-prj-sort">
    <li class="<?= (!$status ? 'fp-s0 active' : '') ?>"><div><a href="/tu-orders/"><strong>Все заказы</strong></a> <span><?= $cnts['total'] ?></span></div></li>
    <li class="fp-s1 b-page__desktop b-page__ipad"><div><a href="/proj/?p=list">Все проекты</a> <span id="prjfld_cnt0"><?php //$pocnt[0] ?></span></div></li>
    
    <li class="fp-s2<?= ($status == 'new' ? ' active' : '') ?>"><div><a href="?s=new">Согласование условий</a> <span id="prjfld_cnt1"><?= $cnts['new'] ?></span></div></li>
    <li class="fp-s3<?= ($status == 'accept' ? ' active' : '') ?>"><div><a href="?s=accept">В работе</a> <span id="prjfld_cnt1"><?= $cnts['accept'] ?></span></div></li>
    <li class="fp-s4<?= ($status == 'close' ? ' active' : '') ?>"><div><a href="?s=close">Закрытые</a> <span id="prjfld_cnt2"><?= $cnts['close'] ?></span></div></li>
        
    <?php if(false){ ?>
    <li class="fp-s6<?= ($folder == 5 ? ' active' : '') ?>"><div><a href="?p=list&fld=5">Корзина</a> <span id="prjfld_cnt5"><?= $pocnt[5] ?></span></div></li>
    <?php } ?>
    <li class="fp-s1 b-page__iphone"><div style="padding-top:10px;"><a href="/proj/?p=list">Все проекты</a> <span id="prjfld_cnt0"><?php //$pocnt[0] ?></span></div></li>
</ul>
<?php
if(count($orders_list))
{
    foreach($orders_list as $order)
    {
        $this->tserviceOrderStatusWidget->setOrder($order);
        $this->tserviceOrderStatusWidget->setUser(array('login' => $order['login'],'uname' => $order['uname'],'usurname' => $order['usurname']));
        $this->tserviceOrderStatusWidget->setFreelancer(array('login' => $_SESSION['login']));
        
        //@todo: этот каунт когданибуть прибъет тут все. Нужно переделать!
        $count = $this->modelMessage->getCount($order['id'], get_uid(FALSE));
?>
<div  class="project-preview"> 
    <div class="b-layout b-layout_float_right b-page__desktop">
        <div class="b-layout__txt b-layout__txt_right">
            Стоимость: <span class="b-layout__bold"><?php echo tservices_helper::cost_format($order['order_price'],true) ?></span>
        </div>
        <div class="b-layout__txt b-layout__txt_right">
            Срок: <span class="b-layout__bold"><?php echo tservices_helper::days_format($order['order_days']) ?></span>
        </div>
    </div>

    <h2 class="b-layout__title">
        <a class="b-layout__link" href="<?php echo tservices_helper::getOrderCardUrl($order['id']); ?>">
            <?php echo reformat(htmlspecialchars($order['title']), 30, 0, 1); ?>
        </a> 
        <?php if(false){ ?>
        <a onclick="xajax_WstProj(80031, 1)" href="javascript:;"> <img class="frl-prj-del" alt="Убрать в корзину" src="../../images/frl-prj-del.png"> </a> 
        <?php } ?>
    </h2>
    <div class="b-page__ipad b-page__iphone">
        <div class="b-layout__txt">
            Стоимость: <span class="b-layout__bold"><?php echo tservices_helper::cost_format($order['order_price'],true) ?></span>
        </div>
        <div class="b-layout__txt b-layout__txt_padbot_10">
            Срок: <span class="b-layout__bold"><?php echo tservices_helper::days_format($order['order_days']) ?></span>
        </div>
    </div>
    
    <div class="b-layout__txt b-layout__txt_lineheight_1 b-layout__txt_padbot_20">
        Заказчик: 
        <a class="b-layout__link b-layout__link_bold b-layout__link_color_6db335" href="/users/<?=$order['login']?>/">
        <?php echo "{$order['uname']} {$order['usurname']} [{$order['login']}]" ?></a> 
        <?php echo view_mark_user2($order) ?>
    </div>

    <div class="b-layout b-layout_float_right b-layout_width_240 b-page__desktop">
        <div class="b-layout__txt b-layout__txt_right"><a class="b-layout__link" href="<?php echo tservices_helper::getOrderCardUrl($order['id']); ?>#messages">Переписка в заказе</a></div>
        <div class="b-layout__txt b-layout__txt_right">(<?php if ($count['all']) {
            echo $count['all'] . ' ' . ending($count['all'], 'сообщение', 'сообщения', 'сообщений');
            if ($count['new']) echo ', <span class="b-layout__txt_color_6db335">'.$count['new'].' '.ending($count['new'], 'новое', 'новых', 'новых').'</span>';
        } else echo 'Нет сообщений'; ?>)</div>
    </div>
    
    <div id="tservices_order_status_<?=$order['id']?>" class="b-fon b-fon_bg_f5 b-fon_pad_10 b-fon_margbot_20 b-layout_margright_250 b-layout_marg_null_ipad b-layout_margbot_20_ipad">
        <?php $this->tserviceOrderStatusWidget->run() ?>
    </div>
    <div class="b-layout b-page__ipad b-page__iphone">
        <div class="b-layout__txt"><a class="b-layout__link" href="<?php echo tservices_helper::getOrderCardUrl($order['id']); ?>#messages">Переписка в заказе</a></div>
        <div class="b-layout__txt">(<?php if ($count['all']) {
            echo $count['all'] . ' ' . ending($count['all'], 'сообщение', 'сообщения', 'сообщений');
            if ($count['new']) echo ', <span class="b-layout__txt_color_6db335">'.$count['new'].' '.ending($count['new'], 'новое', 'новых', 'новых').'</span>';
        } else echo 'Нет сообщений'; ?>)</div>
    </div>
    
</div>
<?php
    }
?>
<div class="b-layout b-layout_padtop_20">
                <?php
                    $current_cnt = @$cnts[(!$status)?'total':$status];
                    $pages = ceil($current_cnt / $on_page);
                    echo new_paginator2($page, $pages, 4, "%s/tu-orders/?" . ($status?"s={$status}&":"") . "page=%d" . ($t ? "&amp;t=$t" : "") . "%s");
                ?>
</div>
<?php
}
else
{
?>
<div class="b-txt b-txt_center b-txt_padtop_20 b-txt_padbot_40">
    Ничего не найдено.
</div>
<?php
}