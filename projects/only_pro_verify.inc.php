<?php if ($project['pro_only'] == 't' || $project['verify_only'] == 't'): ?>
  <div class="b-layout b-layout_padbot_15 <? if(!(isset($project) && isset($project['payed']) && $project['payed'] > 0) || ($project['is_pro'] === 't')) { ?>b-layout_margright_270 b-layout_marg_null_ipad<?php }?>">
      <table class="b-layout__table b-layout__table_width_full">
         <tr class="b-layout__tr">
            <td class="b-layout__td">
                <?php if ($project['pro_only'] == 't' || $project['verify_only'] == 't' || $project['urgent'] == 't' || $project['hide'] == 't'): ?>
                        <?php if ($project['urgent'] == 't' || $project['hide'] == 't'): ?>
                            <div class="b-layout__txt b-layout__txt_lineheight_1">
                                <?php if ($project['urgent'] == 't'): ?>
                                    <span class="b-layout__txt b-layout__txt_color_ba0000"><span class="b-icon b-icon__fire b-icon_top_2"></span>Срочный проект</span>&nbsp;&nbsp;
                                <?php endif; ?>
                                <?php if ($project['hide'] == 't'): ?>
                                    <span class="b-layout__txt"><span class="b-icon b-icon__eye b-icon_top_2"></span> Скрытый от поисковых систем и неавторизованных пользователей</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($project['pro_only'] == 't' || $project['verify_only'] == 't'): ?>
                            <div class="b-layout__txt b-layout__txt_lineheight_1">
                                Только для 
                                <?php if ($project['pro_only'] == 't'): echo ' '.view_pro(); endif; ?>
                                <?php if ($project['verify_only'] == 't'):?> <?= $project['pro_only'] == 't' ? 'и' : '' ?> верифицированныx пользователей <a href="/promo/verification" alt="верифицированных пользователей" title="верифицированных пользователей"><span class="b-icon b-icon__ver b-icon_valign_bot"></span></a><?php endif; ?>
                            </div>
                        <?php endif; ?>
                <?php endif; ?>
            </td>
            <td class="b-layout__td b-layout__td_width_270 b-layout__td_valign_bot b-layout__td_right">
                <div class="b-free-share" style="height:25px;">
                    <?= ViewSocialButtons('project', $sTitle, false, false, $sDescr, $project['id']);?>
                </div>
            </td>
         </tr>
      </table>
  </div>
<?php endif; ?>
<?php if (!($project['pro_only'] == 't' || $project['verify_only'] == 't')): ?>
  <div class="b-layout">
    <div class="b-free-share" style="height:25px; float:right; width:260px; padding-top:2px;">
        <?= ViewSocialButtons('project', $sTitle, false, false, $sDescr, $project['id']);?>
    </div>
  </div>
<?php endif; ?>


