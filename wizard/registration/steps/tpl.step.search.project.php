<?php foreach($projects as $k=>$project) { ?>
<div class="b-post <?= $project['is_color'] == 't' ? 'b-post_bg_fffded' : '';?> b-post_pad_10_15_15 b-post_margbot_15 b-layout">
    <table class="b-layout__table b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0">
        <tbody>
            <tr class="b-layout__tr">
                <td class="b-layout__left">
                    <h3 class="b-post__title"><?php if(strtotime($project['top_to']) >= time()) {?><span class="b-post__pin"></span><?php }//if?><a href="/wizard/registration/?role=2&project=<?=$project['id']?>" class="b-post__link"><?=$project['name']?></a></h3>
                </td>
                <td class="b-layout__right b-layout__right_padleft_10">
                    <div class="b-post__price <?php if($project['cost'] != 0) { ?>b-post__price_fontsize_15 b-post__price_bold<?} else {?> b-post__price_fontsize_13 <?} ?>">
                        <?php if($project['cost'] == 0) { ?>
                            По договоренности
                        <?php } else { $priceby_str = getPricebyProject($project['priceby']); //if?>
                            <?= CurToChar($project['cost'], $project['currency']) ?><?= $priceby_str?>
                        <?php }//else?>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <?php if($k == 0 && !$dont_show_hint) {?>
    <div class="i-prompt">
        <div class="b-prompt b-prompt_left_-270 b-prompt_width_180">
            <div class="b-prompt__txt b-prompt__txt_color_fd6c30 b-prompt__txt_italic">Кликните на заголовок для просмотра подробностей</div>
            <div class="b-prompt__arrow b-prompt__arrow_4 b-prompt__arrow_left_40 b-prompt__arrow_top_-50"></div>
        </div>
    </div>
    <?php }//if?>

    <div class="b-post__body b-post__body_padtop_15">
    <table class="b-layout__table b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0">
        <tbody>
            <tr class="b-layout__tr">
                <td class="b-layout__one">
        <?php if($project['link'] && ($project['logo_id'] || $project['logo_path'])) { ?>
        <a href="http://<?=$project['link']?>" class="b-post__link"><img alt="" src="<?= WDCPREFIX .'/'. ( $project['logo_path'] ? ($project['logo_path'] . $project['logo_name']) : ($project['path'] . $project['fname']) ); ?>" class="b-post__pic b-post__pic_float_right b-post__pic_margleft_10" /></a>
        <?php } elseif($project['logo_id'] || $project['logo_path']) {?>
        <img alt="" src="<?= WDCPREFIX .'/'. ( $project['logo_path'] ? ($project['logo_path'] . $project['logo_name']) : ($project['path'] . $project['fname']) ); ?>" class="b-post__pic b-post__pic_float_right b-post__pic_margleft_10" />
        <?php }//else?>
        <div class="b-post__txt <?= $project['is_bold'] == 't' ? 'b-post__txt_bold' : '';?>"><?= reformat($project['descr'], 50);?></div>
                </td>
            </tr>
        </tbody>
    </table>
    </div>
    <div class="b-post__foot b-post__foot_padtop_15">
        <div class="b-post__txt b-post__txt_fontsize_11">
            <span class="b-post__bold">
                <?= ( $project['kind'] == 1 ? "Фри-ланс" : ($project['kind'] == 7?"Конкурс":"Вакансии") )?><?= $project['country'] > 0? ", {$project['country_name']}" : ""?><?= $project['city'] > 0?", {$project['city_name']}":""?>
                <?php if($project['pro_only'] == 't') {?>
                &#160; только <span class="b-icon b-icon__pro b-icon__pro_f"></span>
                <?php }?>
            </span>
        </div>
        <?php if($k > 0 && $project['pro_only'] == 't' && !$is_pro_prompt && !$dont_show_hint) { $is_pro_prompt = true;?>
        <div class="i-prompt">
            <div class="b-prompt b-prompt_left_-270 b-prompt_top_-55 b-prompt_width_200">
                <div class="b-prompt__txt b-prompt__txt_color_fd6c30 b-prompt__txt_italic">Для ответа на этот проект необходимо приобрести аккаунт <span class="b-icon b-icon__pro b-icon__pro_f"></span></div>
                <div class="b-prompt__arrow b-prompt__arrow_3 b-prompt__arrow_left_80"></div>
            </div>
        </div>
        <?php }//if?>
    </div>
</div>
<?php } //foreach?>