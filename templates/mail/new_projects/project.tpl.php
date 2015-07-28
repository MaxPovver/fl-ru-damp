<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="600">
    <tbody>
    <tr>
        <td bgcolor="#ffffff" width="20" height="20" colspan="3"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td>
           <b>
               <font color="#000000" size="2" face="arial">
                    <a href="<?=$url?><?=$utm_param?>" target="_blank" style="color:<?php if($project_urgent){ ?>#d60003<?php }else{ ?>#006ed6<?php } ?>">
                        <?=$name?>
                    </a>
               </font>
           </b> &#160;
           <?php if($price){ ?><b><font color="#62a200" size="2" face="arial"><nobr><?=$price?></nobr></font></b><?php } ?>
        </td>
        <td bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20" height="10" colspan="3"></td>
    </tr>
    <tr>
        <td bgcolor="#ffffff" width="20"></td>
        <td><font color="#000000" size="2" face="arial"><?=$descr?></font></td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td  height="20">
            <font color="#a0a0a0" size="1" face="arial">
                <?php if ($project_kind == 2 || $project_kind == 7): // Конкурс ?>
                    <b>Конкурс</b> &#160;
                <?php elseif ($project_kind == 4): // Вакансия ?>
                    <b>В офис</b> &#160;
                <?php endif; ?>

                <?php if ($project_kind == 2 || $project_kind == 7){ ?>                  
                    <?php if (strtotime($end_date) > time()){ ?>
                        до окончания осталось: <?= ago_pub_x(strtotime($end_date), "ynjGx") ?>
                    <?php }else{ ?>
                        завершен
                    <?php } ?>
                     &#160;   
                <?php } ?>
                    
                <?php if ($project_pro_only): ?>
                    Только для <img src="<?=$host?>/images/letter/pro-f.png" width="25" height="12" style="margin-bottom:-3px;">&nbsp;
                <?php endif; ?>

                <?php if ($project_verify_only): ?>
                    <img src="<?=$host?>/images/letter/ver.png" width="15" height="15" style="margin-bottom:-4px;" border="0">&nbsp;
                <?php endif; ?>

                <?php if ($project_urgent): ?>
                    <img src="<?=$host?>/images/letter/fire.png" width="11" height="13" style="margin-bottom:-3px;"  border="0">
                <?php endif; ?>
            </td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>