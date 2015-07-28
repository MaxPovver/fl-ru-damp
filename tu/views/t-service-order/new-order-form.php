<?php

global $session;
global $page_title;
$page_title = 'Предложить заказ';


?>
<h1 class="b-page__title">
    <?=$title?>
</h1>

<form method="post" action="">
<table class="b-layout__table b-layout__table_width_full">
    <tbody>
        <tr class="b-layout__tr">
            <td class="b-layout__td b-layout__td_padbot_20">
                <div class="b-layout__txt b-layout__txt_fontsize_15">
                    Исполнитель
                </div>
            </td>
            <td class="b-layout__td b-layout__td_padbot_20">
                <?=$session->view_online_status($freelancer->login)?>
                <a class="b-layout__link b-layout__link_fontsize_13 b-layout__link_no-decorat b-layout__link_color_000 b-layout__link_bold" 
                   href="/users/<?=$freelancer->login?>/">
                    <?=view_fullname($freelancer)?>
                </a>
                <?=view_mark_user(array(
                    "login"         => $freelancer->login,
                    "is_pro"        => $freelancer->is_pro,
                    "is_profi"      => $freelancer->is_profi,
                    "is_pro_test"   => $freelancer->is_pro_test,
                    "is_team"       => $freelancer->is_team,
                    "role"          => $freelancer->role), '', true, "&nbsp;");
                ?>
            </td>
            <td class="b-layout__td b-layout__td_width_70 b-layout__td_padbot_20"></td>
        </tr>      
        <tr class="b-layout__tr">
            <?=$form->getElement('title')->render(); ?>
            <td class="b-layout__td b-layout__td_width_70 b-layout__td_padbot_20"></td>
        </tr>        
        <tr class="b-layout__tr">
            <?=$form->getElement('description')->render(); ?>
            <td class="b-layout__td b-layout__td_width_70 b-layout__td_padbot_20"></td>
        </tr>
        <tr class="b-layout__tr">
            <?=$form->getElement('order_days')->render(); ?>
            <td class="b-layout__td b-layout__td_width_70 b-layout__td_padbot_20"></td>
        </tr>
        <tr class="b-layout__tr">
            <?=$form->getElement('order_price')->render(); ?>
            <td class="b-layout__td b-layout__td_width_70 b-layout__td_padbot_20"></td>
        </tr>        
        <tr class="b-layout__tr">
            <?php
                $pay_type_element = $form->getElement('pay_type');
                if($pay_type_element) echo $pay_type_element->render(); 
            ?>
            <td class="b-layout__td b-layout__td_width_70 b-layout__td_padbot_20"></td>
        </tr>
        <tr class="b-layout__tr">
            <td class="b-layout__td b-layout__td_width_120 b-layout__td_padbot_20"></td>
            <td class="b-layout__td" colspan="2">
                <div class="b-buttons">
                    <button type="submit" class="b-button b-button_flat b-button_flat_green">
                        <?=$submit_title?>
                    </button>
                </div>
            </td>          
        </tr>
    </tbody>
</table>
</form>