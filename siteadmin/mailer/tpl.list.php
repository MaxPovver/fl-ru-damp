<table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" border="0" cellpadding="0" cellspacing="0">
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_width_100 b-layout__one_padbot_10 b-layout__one_valign_bot">
            <?php list($link, $img) = mailer_sort_url(1, 2, $filter['sort']);?>
            <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bordbot_double_ccc">
                <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_italic b-layout__link_bordbot_dot_41" href="<?= $link?>">Получатели</a>
                <?php if($img) {?>
                &#160;<a class="b-layout__link" href="<?= $link?>"><img src="<?=$img?>" alt="" /></a>
                <?php }?>
            </div>
        </td>
        <td class="b-layout__one b-layout__one_padbot_10 b-layout__one_valign_bot">
            <?php list($link, $img) = mailer_sort_url(3, 4, $filter['sort']);?>
            <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bordbot_double_ccc">
                <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_italic b-layout__link_bordbot_dot_41" href="<?= $link?>">Тема письма</a>
                <?php if($img) {?>
                &#160;<a class="b-layout__link" href="<?= $link?>"><img src="<?=$img?>" alt="" /></a>
                <?php }?>
            </div>
        </td>
        <td class="b-layout__one b-layout__one_width_130 b-layout__one_padbot_10 b-layout__one_valign_bot">
            <?php list($link, $img) = mailer_sort_url(5, 6, $filter['sort']);?>
            <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bordbot_double_ccc">
                <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_italic b-layout__link_bordbot_dot_41" href="<?= $link?>">Отправитель</a>
                <?php if($img) {?>
                &#160;<a class="b-layout__link" href="<?= $link?>"><img src="<?=$img?>" alt="" /></a>
                <?php }?>
            </div>
        </td>
        <td class="b-layout__one b-layout__one_width_130 b-layout__one_padbot_10 b-layout__one_valign_bot">
            <?php list($link, $img) = mailer_sort_url(7, 8, $filter['sort']);?>
            <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bordbot_double_ccc">
                <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_italic b-layout__link_bordbot_dot_41" href="<?= $link?>">Дата и время</a>
                <?php if($img) {?>
                &#160;<a class="b-layout__link" href="<?= $link?>"><img src="<?=$img?>" alt="" /></a>
                <?php }?>
            </div>
        </td>
    </tr>
    <?php foreach($list_mailer as $content) { ?>
    <?php
    
    if($content['filter_frl'] > 0 && $content['filter_emp'] > 0 ) {
        $class_role = "b-username__role_all";
    } else if($content['filter_frl'] > 0) {
        $class_role = "b-username__role_frl";
    } else if($content['filter_emp'] > 0) {
        $class_role = "b-username__role_emp";
    } else {
        $class_role = "b-username__role_all";
    }
    
    if($content['is_digest'] !== null) {
        if($content['filter_frl'] !== null && $content['filter_emp'] !== null) {
            $class_role = "b-username__role_all";
            $sum_digest = $content['count_rec_frl'] + $content['count_rec_emp'];
        } elseif($content['filter_frl'] !== null) {
            $class_role = "b-username__role_frl";
            $sum_digest = $content['count_rec_frl'];
        } elseif($content['filter_emp'] !== null) {
            $class_role = "b-username__role_emp";
            $sum_digest = $content['count_rec_emp'];
        }
    }
    
    if($content['status_message'] == 1 && $content['status_sending'] != 2) {
        $link = "?action=report&id={$content['id']}";
    } elseif($content['status_message'] == 0 && $content['status_sending'] != 2 && $content['is_digest'] !== null) {
        $link = "?action=digest_edit&id={$content['id']}";
    } elseif($content['status_message'] == 0 && $content['status_sending'] != 2) {
        $link = "?action=edit&id={$content['id']}";
    } else {
        $link = "javascript:alert('Идет рассылка')";
    }
    
    $sum = $mailer->calcSumRecipientsCount($content, array($content['count_rec_emp'], $content['count_rec_frl']));
    ?>
    <tr class="b-layout__tr <?= $content['status_sending'] == 2? "b-layout__tr_loadfon":"";?><?/*b-layout__tr_loadfon*/?>">
        <td class="b-layout__one b-layout__one_right b-layout__one_padbot_5 b-layout__one_padtop_2 ">
            <div class="b-layout__txt b-layout__txt_padright_20">
                <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_valign_middle">
                    <?php if ($content['filter_file']) { ?>
                        <a href="<?=WDCPREFIX.'/mailer/'.$content['filter_file']?>" target="_blank">файл</a>
                    <?php } else echo number_format( ($content['is_digest'] !== null ? $sum_digest : $sum ), 0, ",", " "); ?>
                </span>&#160;<span class="b-username b-username__role b-username__role_valign_middle <?=$class_role?>"></span></div>
        </td>
        <td class="b-layout__one b-layout__one_padbot_5 b-layout__one_padtop_2 ">
            <div class="b-layout__txt b-layout__txt_padright_10 <?php if(!(($content['status_sending'] == 3)||($content['type_regular'] > 1 && $content['status_sending'] != 3))) { ?>b-layout__txt_padleft_15<?php }//if?>">
                <?php if($content['status_sending'] == 3) { ?>
                <span class="b-layout__mail-icon b-layout__mail-icon_top_4 b-layout__mail-icon_pause"></span>
                <?php }//if?>
                <?php if($content['type_regular'] > 1 && $content['status_sending'] != 3) {?>
                <span class="b-layout__mail-icon b-layout__mail-icon_top_4 b-layout__mail-icon_<?= ($content['in_draft'] == 't' || $content['status_sending'] == 3?"black":"red")?>"></span>
                <?php } //if?>
                <a class="b-layout__link <?=$mailer->getColorMailer($content);?>"  href="<?= $link?>"><?= reformat(stripslashes($content['subject']), 45);?></a>
            </div>
        </td>
        <td class="b-layout__one b-layout__one_padbot_5 b-layout__one_padtop_2 ">
            <div class="b-layout__txt b-layout__txt_padright_10"><a class="b-layout__link b-layout__link_fontsize_11" href="/users/<?=$content['login']?>/"><?= $content['login']?></a></div>
        </td>
        <td class="b-layout__one b-layout__one_padbot_5 b-layout__one_padtop_2 ">
            <div class="b-layout__txt b-layout__txt_fontsize_11"><?= date('Y.m.d в H:i', $mailer->getDateSubscr($content))?></div>
        </td>
    </tr>
    <?php }//foreach?>
</table>