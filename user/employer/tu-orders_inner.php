<?php


?>
<div class="b-layuot b-layout_pad_20">
	  <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_nowrap b-layout_block_iphone b-layout__txt_padbot_10">
		<?php if(!$tu_order_status){ ?>
        <span class="b-layout__bold">Все</span>
        <?php }else{ ?>
        <a href="<?php echo "/users/{$user->login}/tu-orders/" ?>" class="b-layout__link b-layout__link_bold">Все</a> 
        <?php } ?>
        (<?= (int)$tu_orders_cnts['total'] ?>)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      </span>
      
      <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_nowrap b-layout_block_iphone b-layout__txt_padbot_10">
		<?php if($tu_order_status == 'new'){ ?>
        <span class="b-layout__bold">Ожидают подтверждения</span>
        <?php }else{ ?>
        <a href="?s=new" class="b-layout__link b-layout__link_bold">Ожидают подтверждения</a> 
        <?php } ?>
        (<?= (int)$tu_orders_cnts['new'] ?>)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      </span>
      
      <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_nowrap b-layout_block_iphone b-layout__txt_padbot_10">
		<?php if($tu_order_status == 'accept'){ ?>
        <span class="b-layout__bold">В работе</span>
        <?php }else{ ?>
        <a href="?s=accept" class="b-layout__link b-layout__link_bold">В работе</a> 
        <?php } ?>
        (<?= (int)$tu_orders_cnts['accept'] ?>)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      </span>
      
      <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_nowrap b-layout_block_iphone">
		<?php if($tu_order_status == 'close'){ ?>
        <span class="b-layout__bold">Закрытые</span> 
        <?php }else{ ?>
        <a href="?s=close" class="b-layout__link b-layout__link_bold">Закрытые</a> 
        <?php } ?>
        (<?= (int)$tu_orders_cnts['close'] ?>)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      </span> 
</div>
<span style="width:100%; height:1px; margin:5px auto; display:block; padding:0; background:#d7d7d7"></span>
<?php
if(count($orders_list))
{
    foreach($orders_list as $order)
    {
        $tserviceOrderStatusWidget->setOrder($order);
        $tserviceOrderStatusWidget->setEmployer(array('login' => $user->login));
        $tserviceOrderStatusWidget->setUser(array('login' => $order['login'],'uname' => $order['uname'],'usurname' => $order['usurname']));
        $count = $modelMessage->getCount($order['id'], $user->uid);
?>
<div class="b-layout b-layout_pad_10_20">
    <div class="b-layout b-layout_float_right b-layout__txt_right b-page__desktop">
        <div class="b-layout__txt">
            Стоимость: <span class="b-layout__bold"><?php echo tservices_helper::cost_format($order['order_price'],true) ?></span><br/>
            Срок: <span class="b-layout__bold"><?php echo tservices_helper::days_format($order['order_days']) ?></span></div>
    </div>
    <h2 class="b-layout__title">
        <a class="b-layout__link" href="<?php echo tservices_helper::getOrderCardUrl($order['id']); ?>">
            <?php echo reformat(htmlspecialchars($order['title']), 30, 0, 1); ?>
        </a>
    </h2>
    <div class="b-layout__txt b-layout__txt_lineheight_1 b-layout__txt_padbot_20 b-layout_padbot_10_ipad">
        Исполнитель: 
        <a class="b-layout__link b-layout__link_bold b-layout__link_color_000" href="/users/<?=$order['login']?>/"><?php echo "{$order['uname']} {$order['usurname']} [{$order['login']}]" ?></a> 
        <?php echo view_mark_user2($order) ?>
    </div>
    <div class="b-layout__txt b-layout__txt_padbot_10 b-page__ipad b-page__iphone">
        Стоимость: <span class="b-layout__bold"><?php echo tservices_helper::cost_format($order['order_price'],true) ?></span><br/>
        Срок: <span class="b-layout__bold"><?php echo tservices_helper::days_format($order['order_days']) ?></span>
    </div>
    <div class="b-layout b-layout_float_right b-layout_width_240 b-page__desktop">
        <div class="b-layout__txt b-layout__txt_right"><a class="b-layout__link" href="<?php echo tservices_helper::getOrderCardUrl($order['id']); ?>#messages">Переписка в заказе</a><br>(<?php if ($count['all']) {
            echo $count['all'] . ' ' . ending($count['all'], 'сообщение', 'сообщения', 'сообщений');
            if ($count['new']) echo ', <span class="b-layout__txt_color_6db335">'.$count['new'].' '.ending($count['new'], 'новое', 'новых', 'новых').'</span>';
        } else echo 'Нет сообщений'; ?>)
        </div>
    </div>
    <div class="b-layout__txt b-layout__txt_padbot_10 b-page__ipad b-page__iphone"><a class="b-layout__link" href="<?php echo tservices_helper::getOrderCardUrl($order['id']); ?>#messages">Переписка в заказе</a> (<?php if ($count['all']) {
            echo $count['all'] . ' ' . ending($count['all'], 'сообщение', 'сообщения', 'сообщений');
            if ($count['new']) echo ', <span class="b-layout__txt_color_6db335">'.$count['new'].' '.ending($count['new'], 'новое', 'новых', 'новых').'</span>';
        } else echo 'Нет сообщений'; ?>)
    </div>
    <div id="tservices_order_status_<?=$order['id']?>" class="b-fon b-fon_bg_f5 b-fon_pad_10 b-fon_margbot_20 b-layout_margright_250 b-layout_marg_null_ipad b-layout_margbot_20_ipad">
        <?php $tserviceOrderStatusWidget->run(); ?>
    </div>
</div>
<?php

    }
    
?>
<div style="padding: 0 19px; margin: 19px 0;">
    <table cellpadding="0" cellspacing="0"  width="100%">
        <tr>
            <td style="width:100%" >
                <?php
                    $pages = ceil($tu_orders_cnts['total'] / $on_page);
                    echo new_paginator2($page, $pages, 4, "%s/users/{$user->login}/tu-orders/?page=%d" . ($t ? "&amp;t=$t" : "") . "%s");
                ?>
            </td>
        </tr>
    </table>
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