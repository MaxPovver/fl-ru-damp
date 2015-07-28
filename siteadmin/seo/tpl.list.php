<table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" border="0" cellpadding="0" cellspacing="0">
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_width_100 b-layout__one_padbot_10 b-layout__one_valign_bot">
            <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bordbot_double_ccc">ID</div>
        </td>
        <td class="b-layout__one b-layout__one_padbot_10 b-layout__one_valign_bot">
            <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bordbot_double_ccc">Раздел / специализация</div>
        </td>
        <td class="b-layout__one b-layout__one_width_130 b-layout__one_padbot_10 b-layout__one_valign_bot">
            <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bordbot_double_ccc">&nbsp;</div>
        </td>
    </tr>
    <?php foreach($list as $content) { ?>
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_padbot_10 b-layout__one_padtop_9 b-layout__txt_bordbot_b2">
            <?php echo $content['parent_id']; ?>
        </td>
        <td class="b-layout__one b-layout__one_padbot_10 b-layout__one_padtop_9  b-layout__txt_bordbot_b2 b-layout__txt_color_000">
            <?=$content['is_spec']=='t'?$content['prof_title']:$content['prof_group_title']; ?>
        </td>
        <td class="b-layout__one b-layout__one_padbot_10 b-layout__one_padtop_9  b-layout__txt_bordbot_b2">
            <a href="/siteadmin/seo/?action=edit&id=<?=$content['id']?>">изменить</a>
        </td>
    </tr>
    <?php }//foreach?>
</table>