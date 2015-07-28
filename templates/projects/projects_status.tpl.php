<?php

    $status = @$project['status'];
    $offer_status = @$offer['status'];
    $is_emp = is_emp();
    $project_id = intval(@$project['id']);
 
    
    $emp_feedback = @$project['emp_feedback'];
    $is_emp_feedback = (!empty($emp_feedback));
    $emp_is_good = (@$project['emp_rating'] > 0);
    $emp_rating = intval(@$project['emp_rating']);
    
    $emp_color = ($emp_is_good)?'6db335':'c10600';
    $emp_feedback = reformat($emp_feedback, 30);      
    
    
    $frl_feedback = @$project['frl_feedback'];
    $is_frl_feedback = (!empty($frl_feedback));
    $frl_is_good = (@$project['frl_rating'] > 0);
    $frl_rating = intval(@$project['frl_rating']);    
    
    $frl_color = ($frl_is_good)?'6db335':'c10600';
    $frl_feedback = reformat($frl_feedback, 30); 
    
    
    $kind = @$project['kind'];
    
    
    $emp_warn_txt = 'Обращаем внимание, что при сотрудничестве вы самостоятельно несете все риски, связанные с несвоевременным или некачественным выполнением работы или отсутствием ожидаемого результата.';
    $frl_warn_txt = 'Обращаем внимание, что при сотрудничестве вы самостоятельно несете все риски, связанные с оплатой работы и процессом ее выполнения.';
    
?>
<table class="b-layout__table b-layout__table_width_full">
    <tr class="b-layout__tr">
        <td class="b-layout__td b-layout__td_width_60">
<?php
    $icon = 'tu/ico_po_offers.png';
    switch($status)
    {
        case projects_status::STATUS_NEW:
            if (!$project['exec_id']) {
                if((!$is_emp && $offer_status == projects_status::STATUS_DECLINE) || 
                   ($is_emp && $kind == 9 && $offer_status == projects_status::STATUS_CANCEL)) $icon = 'tu/ico_po_refuse.png';
                elseif((!$is_emp && $offer_status == projects_status::STATUS_CANCEL) || 
                   ($is_emp && $kind == 9 && $offer_status == projects_status::STATUS_DECLINE)) $icon = 'tu/ico_po_canceled.png';
            }   
            break;
        case projects_status::STATUS_ACCEPT:
            $icon = 'tu/ico_po_executor.png';
            break;
        case projects_status::STATUS_FRLCLOSE:
        case projects_status::STATUS_EMPCLOSE:
            $icon = 'tu/ico_po_executor.png';
            if($is_emp && $is_emp_feedback) $icon = ($emp_is_good)?'good.png':'bad.png';
            elseif(!$is_emp && $is_frl_feedback) $icon = ($frl_is_good)?'good.png':'bad.png';
            break;
    }
?>
             <img class="b-user__pic"  alt="" src="/images/<?=$icon?>"/>
        </td>
        <td class="b-layout__td">
<?php 

if($status == projects_status::STATUS_NEW)
{
     if($is_exec)
     {
         if($is_adm)
         {
?>             
             <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                Исполнитель выбран, но еще не подтвердил участие в проекте.
            </div>  
<?php    } 
         elseif($is_emp)
         {           
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                Исполнитель выбран, но еще не подтвердил участие в проекте.
            </div>            
            <div class="b-layout__txt b-layout__txt_padbot_10">
                 <?php echo $fullname ?> получил ваше предложение стать исполнителем проекта.<br/> 
                 Как только он подтвердит его, начнется выполнение работы. Ожидайте, пожалуйста. 
            </div>
            <div class="b-buttons">
                <a class="b-button b-button_flat b-button_flat_red" 
                   href="javascript:void(0);" 
                   onClick="yaCounter6051055.reachGoal('proj_cancel'); ProjectsStatus.changeStatus(<?=projects_helper::getJsParams($project_id, 'cancel')?>);">
                   Отменить предложение
                </a>
            </div>
<?php
         }
         else
         {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                Заказчик предлагает вам стать исполнителем проекта.
            </div>
            <div class="b-layout__txt b-layout__txt_padbot_10">
                Заказчик <?php echo $fullname ?> предлагает вам стать исполнителем проекта.<br/>
                Вы можете согласовать все условия сотрудничества и начать выполнение проекта или отказаться от него.
            </div>
            <div class="b-buttons b-buttons_padbot_10">
                <a href="javascript:void(0);" 
                   class="b-button b-button_flat b-button_flat_green" 
                   onClick="yaCounter6051055.reachGoal('proj_apply'); ProjectsStatus.changeStatus(<?=projects_helper::getJsParams($project_id, 'accept')?>);">
                    Начать выполнение проекта
                </a>
                <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; или &#160;</span>
                <a class="b-button b-button_flat b-button_flat_red" 
                   href="javascript:void(0);" 
                   onClick="yaCounter6051055.reachGoal('proj_decline'); ProjectsStatus.changeStatus(<?=projects_helper::getJsParams($project_id, 'decline')?>);">
                    Отказаться от него
                </a>
            </div>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                <span class="b-icon b-icon_sbr_oattent b-icon_top_5 b-icon_margleft_-20"></span>Нажимая кнопку "Начать выполнение проекта", 
                вы соглашаетесь выполнить работу, заявленную в проекте, на согласованных с заказчиком условиях. <br/>Вы самостоятельно несете все риски, 
                связанные с оплатой работы, порядком ее выполнения и получением соответствующего отзыва.
            </div>            
<?php            
         }
     }
     else
     {
         if($is_adm)
         {
             //Пресональный проект статус для админа
             if($kind == 9 && $offer_status == projects_status::STATUS_CANCEL)
             {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_c7271e">
                Заказчик отменил проект.
            </div>
<?php                        
             }
             if($offer_status == projects_status::STATUS_DECLINE)
             {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_c7271e">
                Исполнитель отказался от проекта.
            </div>
<?php            
             }
             else
             {
?>            
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                Исполнитель проекта пока не выбран.
            </div>
<?php
             }  
         } 
         elseif($is_emp)
         {
             //Персональный проект заказчик отказался
             if($kind == 9 && $offer_status == projects_status::STATUS_CANCEL)
             {
?>
            <div class="b-layout__txt">
                Вы отменили проект.<br/>
                Рекомендуем выбрать и заказать услуги фрилансеров или предложить проект другому исполнителю.
            </div>
<?php            
             }
             elseif($kind == 9 && $offer_status == projects_status::STATUS_DECLINE)
             {
?>
            <div class="b-layout__txt b-layout__txt_color_c7271e">
                К сожалению, исполнитель <?php echo $fullname ?> отказался от выполнения вашего проекта.<br/>
                Рекомендуем посмотреть и заказать услуги фрилансеров или предложить проект другому исполнителю.
            </div>            
<?php            
             }
             elseif($offer_status == projects_status::STATUS_DECLINE)
             {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_c7271e">
                Исполнитель отказался от проекта.
            </div>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_color_c7271e">
                К сожалению, исполнитель <?php echo $fullname ?> отказался от выполнения вашего проекта.<br/>
                Рекомендуем вам:<br/>
                <ol>
                    <li>Выбрать другого исполнителя из числа ответивших фрилансеров.</li>
                    <li>Как только исполнитель подтвердил участие в проекте, начать с ним сотрудничество.</li>
                    <li>Получать от исполнителя результат работы.</li>
                    <li>Завершить проект и обменять отзывами.</li>
                </ol>
            </div>
<?php            
             }
             else
             {
?>            
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                Исполнитель проекта пока не выбран.
            </div>
            <div class="b-layout__txt">
                Рекомендуем вам:<br/>
                <ol>
                    <li>Из числа ответивших фрилансеров определить нескольких кандидатов (претендентов на выполнение работы).</li>
                    <li>По результатам общения с кандидатами определить одного исполнителя.</li>
                    <li>Как только исполнитель подтвердит участие в проекте, начать с ним сотрудничество.</li>
                    <li>Получить от исполнителя результат работы.</li>
                    <li>Завершить проект и обменяться отзывами.</li>
                </ol>
            </div>   
<?php
             }
         }
         else
         {
             if($offer_status == projects_status::STATUS_DECLINE)
             {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                Вы отказались от проекта.
            </div>
            <div class="b-layout__txt">
                Вы отклонили предложение заказчика стать исполнителем проекта.<br/>
                <?php if($project['kind']!=9) { ?>Ваш статус в проекте изменен с "Исполнитель" на "Кандидат".<?php } ?>
            </div>  
<?php            
             }
             elseif($offer_status == projects_status::STATUS_CANCEL)
             {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_c7271e">
                Заказчик отменил свое предложение.
            </div>
            <div class="b-layout__txt b-layout__txt_color_c7271e">
                К сожалению, <?php echo $fullname ?> отменил предложение вам стать исполнителем по проекту.<br/>
				<?php if($project['kind']!=9) { ?>Ваш статус в проекте изменен с "Исполнитель" на "Кандидат".<?php } ?>
            </div>            
<?php               
             }           
         }
     }    
}
elseif($status == projects_status::STATUS_ACCEPT)
{
    if($is_adm)
    {
?>             
             <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_6db335">
                Проект в работе.
            </div>            
            <div class="b-layout__txt b-layout__txt_color_6db335 b-layout__txt_padbot_15">
                Исполнитель подтвердил участие в проекте и выполняет его.<br/>
            </div> 
<?php    
    } 
    elseif($is_emp)
    {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_6db335">
                Проект в работе.
            </div>            
            <div class="b-layout__txt b-layout__txt_color_6db335 b-layout__txt_padbot_15">
                Исполнитель <?php echo $fullname ?> подтвердил участие в проекте и выполняет его.<br/>
                В любой момент вы можете завершить сотрудничество с исполнителем, произвести оплату выполненной работы и оставить отзыв (положительный или отрицательный).
            </div>
            <div class="b-buttons b-buttons_padbot_15">
                <a class="b-button b-button_flat b-button_flat_green" 
                   href="javascript:void(0);" 
                   onClick="ProjectsFeedback.open(<?=projects_helper::getJsCloseParams($project_id)?>);">
                    Завершить сотрудничество
                </a>
            </div> 
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span><?php echo $emp_warn_txt ?>
            </div>      
<?php
    }
    else
    {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_6db335">
                Проект в работе.
            </div>            
            <div class="b-layout__txt b-layout__txt_color_6db335 b-layout__txt_padbot_15">
                Вы подтвердили участие в проекте в качестве исполнителя. 
                Далее вам необходимо выполнить работу и передать результат заказчику, получив от него сумму оплаты.<br/>
                В любой момент вы можете завершить сотрудничество по проекту и оставить отзыв (положительный или отрицательный).
            </div>
            
            <div class="b-buttons b-buttons_padbot_15">
                <a class="b-button b-button_flat b-button_flat_green" 
                   href="javascript:void(0);" 
                   onClick="ProjectsFeedback.open(<?=projects_helper::getJsCloseParams($project_id)?>);">
                    Завершить сотрудничество
                </a>
            </div>
            
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span><?php echo $frl_warn_txt ?>
            </div>
<?php            
    }
}
elseif($status == projects_status::STATUS_EMPCLOSE)
{
    if($is_adm)
    {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                Проект завершен и закрыт заказчиком. 
            </div>
            <?php if($is_emp_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$emp_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                Отзыв заказчика:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$emp_color?> b-layout__txt_color_<?=$emp_color?>">
               <?=$emp_feedback?>
            </div>                    
             <?php } ?>
            <?php if($is_frl_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$frl_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                Отзыв исполнителя:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$frl_color?> b-layout__txt_color_<?=$frl_color?>">
               <?=$frl_feedback?>
            </div>
            <?php } ?>
<?php            
    }
    elseif($is_emp)
    {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_<?=$emp_color?>">
                Проект завершен и закрыт. 
            </div>
            <?php if($is_emp_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_color_<?=$emp_color?>">
                Вы завершили сотрудничество с исполнителем и закрыли проект.
            </div>
            <div class="b-layout__txt b-layout__txt_color_<?=$emp_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                Ваш отзыв исполнителю:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$emp_color?> b-layout__txt_color_<?=$emp_color?>">
               <?=$emp_feedback?>
            </div>                    
            <?php }elseif($is_allow_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_ b-layout__txt_color_6db335 b-layout__txt_padbot_10">
                Вы можете оставить отзыв до <?=$date_feedback?>
            </div>
            <div class="b-buttons b-buttons_padbot_15">
                <a class="b-button b-button_flat b-button_flat_green" 
                   href="javascript:void(0);" 
                   onclick="ProjectsFeedback.open(<?=projects_helper::getJsCloseParams($project_id, true, $frl_rating)?>);">
                    Оставить отзыв
                </a>
            </div> 
            <?php }else{ ?>
            <div class="b-layout__txt b-layout__txt_color_ b-layout__txt_color_6db335 b-layout__txt_padbot_10">
                Вы завершили сотрудничество с исполнителем и закрыли проект.
            </div>                    
            <?php } ?>
            <?php if($is_frl_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$frl_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                Отзыв исполнителя:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$frl_color?> b-layout__txt_color_<?=$frl_color?>">
               <?=$frl_feedback?>
            </div>
            <?php } ?>
            <?php if(!$is_emp_feedback && $is_allow_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span><?php echo $emp_warn_txt ?>
            </div>
            <?php } ?>
<?php
    }
    else
    {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_<?=$frl_color?>">
                Проект завершен и закрыт. 
            </div>
            <?php if($is_frl_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_color_<?=$frl_color?>">
                Заказчик <?php echo $fullname ?> завершил сотрудничество и закрыл проект.
            </div>
            <div class="b-layout__txt b-layout__txt_color_<?=$frl_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                Ваш отзыв заказчику:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$frl_color?> b-layout__txt_color_<?=$frl_color?>">
                <?=$frl_feedback?>
            </div>
            <?php if($is_emp_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$emp_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                Отзыв заказчика:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$emp_color?> b-layout__txt_color_<?=$emp_color?>">
                <?=$emp_feedback?>
            </div>  
            <?php } ?>                
            <?php }else{ ?>
            <?php if($is_emp_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$emp_color?> b-layout__txt_padbot_5">
                <?php echo $fullname ?> завершил сотрудничество и оставил вам <?php if($emp_is_good){ ?>положительный отзыв.<?php }else{ ?>отрицательный отзыв.<?php } ?>
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$emp_color?> b-layout__txt_color_<?=$emp_color?>">
                <?=$emp_feedback?>
            </div>  
            <?php if($is_allow_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$emp_color?> b-layout__txt_padbot_10">
                Вы можете оставить ответный отзыв до <?=$date_feedback?>
            </div>
            <?php } ?>
            <?php }else{ ?>
            <div class="b-layout__txt b-layout__txt_color_6db335">
                Заказчик <?php echo $fullname ?> завершил сотрудничество и закрыл проект.
            </div> 
            <?php if($is_allow_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_6db335 b-layout__txt_padbot_10">
                Вы можете оставить отзыв до <?=$date_feedback?>
            </div>  
            <?php } ?>
            <?php } ?>
            <?php if($is_allow_feedback){ ?>
            <div class="b-buttons b-buttons_padbot_15">
                <a class="b-button b-button_flat b-button_flat_green" 
                   href="javascript:void(0);" 
                   onclick="ProjectsFeedback.open(<?=projects_helper::getJsCloseParams($project_id, true, $emp_rating)?>);">
                    Оставить отзыв
                </a>
            </div>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span><?php echo $frl_warn_txt ?>
            </div> 
            <?php } ?>
            <?php } ?>            
<?php            
    }
}
elseif($status == projects_status::STATUS_FRLCLOSE)
{
    if($is_adm)
    {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                Проект завершен и закрыт исполнителем. 
            </div>
            <?php if($is_emp_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$emp_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                Отзыв заказчика:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$emp_color?> b-layout__txt_color_<?=$emp_color?>">
               <?=$emp_feedback?>
            </div>                    
             <?php } ?>
            <?php if($is_frl_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$frl_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                Отзыв исполнителя:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$frl_color?> b-layout__txt_color_<?=$frl_color?>">
               <?=$frl_feedback?>
            </div>
            <?php } ?>
<?php            
    }
    elseif($is_emp)
    {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_<?=$emp_color?>">
                Проект завершен и закрыт. 
            </div>           
            <?php if($is_emp_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_color_<?=$emp_color?>">
                <?php echo $fullname ?> завершил сотрудничество и закрыл проект.
            </div>
            <div class="b-layout__txt b-layout__txt_color_<?=$emp_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                Ваш отзыв исполнителю:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$emp_color?> b-layout__txt_color_<?=$emp_color?>">
                <?=$emp_feedback?>
            </div>
            <?php if($is_frl_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$frl_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                Отзыв исполнителя:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$frl_color?> b-layout__txt_color_<?=$frl_color?>">
                <?=$frl_feedback?>
            </div>  
            <?php } ?>                
            <?php }else{ ?>
            <?php if($is_frl_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$frl_color?> b-layout__txt_padbot_5">
                <?php echo $fullname ?> завершил сотрудничество и оставил вам <?php if($frl_is_good){ ?>положительный отзыв.<?php }else{ ?>отрицательный отзыв.<?php } ?>
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$frl_color?> b-layout__txt_color_<?=$frl_color?>">
                <?=$frl_feedback?>
            </div>
            <?php if($is_allow_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$frl_color?> b-layout__txt_padbot_10">
                Вы можете оставить ответный отзыв до <?=$date_feedback?>
            </div>
            <?php } ?>
            <?php }else{ ?>
            <div class="b-layout__txt b-layout__txt_color_6db335">
                <?php echo $fullname ?> завершил сотрудничество и закрыл проект.
            </div> 
            <?php if($is_allow_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_6db335 b-layout__txt_padbot_10">
                Вы можете оставить отзыв до <?=$date_feedback?>
            </div> 
            <?php } ?>
            <?php } ?>
            <?php if($is_allow_feedback){ ?>
            <div class="b-buttons b-buttons_padbot_15">
                <a class="b-button b-button_flat b-button_flat_green" 
                   href="javascript:void(0);" 
                   onclick="ProjectsFeedback.open(<?=projects_helper::getJsCloseParams($project_id, true, $frl_rating)?>);">
                    Оставить отзыв
                </a>
            </div>                    
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span><?php echo $emp_warn_txt ?>
            </div>
            <?php } ?>
            <?php } ?>
<?php
    }
    else
    {
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold b-layout__txt_color_<?=$frl_color?>">
                Проект завершен и закрыт. 
            </div> 
            <?php if($is_frl_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_color_<?=$frl_color?>">
                Вы завершили сотрудничество и закрыли проект.
            </div>
            <div class="b-layout__txt b-layout__txt_color_<?=$frl_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                Ваш отзыв заказчику:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$frl_color?> b-layout__txt_color_<?=$frl_color?>">
                <?=$frl_feedback?>
            </div>
            <?php }else{ ?>
            <?php if($is_allow_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_ b-layout__txt_color_6db335 b-layout__txt_padbot_10">
                Вы можете оставить отзыв до <?=$date_feedback?>
            </div>
            <div class="b-buttons b-buttons_padbot_15">
                <a class="b-button b-button_flat b-button_flat_green" 
                   href="javascript:void(0);" 
                   onclick="ProjectsFeedback.open(<?=projects_helper::getJsCloseParams($project_id, true, $emp_rating)?>);">
                    Оставить отзыв
                </a>
            </div> 
            <?php }else{ ?>
            <div class="b-layout__txt b-layout__txt_color_6db335 b-layout__txt_padbot_10">
                Вы завершили сотрудничество и закрыли проект.
            </div>                    
            <?php } ?>
            <?php } ?>
            <?php if($is_emp_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_color_<?=$emp_color?> b-layout__txt_padbot_5 b-layout__txt_bold">
                Отзыв заказчика:
            </div>
            <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?php echo $emp_color ?> b-layout__txt_color_<?=$emp_color?>">
                <?=$emp_feedback?>
            </div>
            <?php } ?>
            <?php if(!$is_frl_feedback && $is_allow_feedback){ ?>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span><?php echo $frl_warn_txt ?>
            </div>
            <?php } ?>
<?php            
    }
}
?>            
        </td>                
    </tr> 
</table>