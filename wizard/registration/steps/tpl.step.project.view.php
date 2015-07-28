<? $is_konkurs = $project['kind'] == 7; ?>
<div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right  b-layout__right_padbot_20">
			<div class="b-fon b-fon_width_full b-fon_padbot_30">
					<div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
							<span class="b-fon__ok"></span>Ваш проект будет опубликован после регистрации.
					</div>
			</div>				
    <div class="b-post <?= $project['is_color'] == 't' ? 'b-post_bg_fffded b-post_pad_10_15_15_20 b-post_margright_-15' : '';?> b-post_margleft_-60 b-post_margbot_40 b-post_relative">
        <?php if($project['top_count'] > 0) {?><span class="b-post__pin <?= $project['is_color'] == 't' ? 'b-post__pin_left_-5' : '';?> "></span><?php }//if?>
								<div class="b-post__body">
            <div class="b-post__avatar b-post__avatar_margright_10">
                <a href="" class="b-post__link" onclick="return false;"><img width="50" height="50" class="b-post__userpic" alt="" src="/images/no_foto.gif" /></a>
            </div>
            <div class="b-post__content b-post__content_margleft_60">
                <div class="b-username b-username_padbot_10">
                    <span class="b-username__login b-username__login_bold b-username__login_color_6db335">Это вы&nbsp;&nbsp;<span title="Сейчас на сайте" class="b-username__mark b-username__mark_online "></span></span> <span class="b-username__txt">сейчас на сайте</span>
                </div>			
                <table class="b-layout__table b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0"><tbody><tr class="b-layout__tr">
                            <td class="b-layout__left">
                                <h3 class="b-post__title b-post__title_padbot_10"><?= reformat($project['name'], 30)?></h3>
                            </td>
                            <td class="b-layout__right b-layout__right_padleft_10">
                                <div class="b-post__price <?php if($project['cost'] != 0) { ?> b-post__price_fontsize_15 b-post__price_bold<?php } else {?>b-post__price_fontsize_13  <?}?>">
                                    <?php if($project['cost'] == 0) { ?>
                                        По договоренности
                                    <?php } else { $priceby_str = getPricebyProject($project['priceby']); //if?>
                                        <?= CurToChar($project['cost'], $project['currency']) ?><?= $priceby_str?>
                                    <?php }//else?>
                                </div>
                            </td>
                        </tr></tbody></table>
                <div class="i-prompt">
                    <div class="b-prompt b-prompt_left_-270 b-prompt_width_200">
                        <div class="b-prompt__txt b-prompt__txt_color_6db335 b-prompt__txt_italic">Так увидят ваш <?= $is_konkurs ? "конкурс" : "проект" ?> фри-лансеры</div>
                        <div class="b-prompt__arrow b-prompt__arrow_left_50 b-prompt__arrow_1"></div>
                    </div>
                </div>
                <table class="b-layout__table b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0"><tbody><tr class="b-layout__tr">
                            <td class="b-layout__one">
                <?php if($project['logo_link']) { ?>
                <a href="http://<?=$project['logo_link']?>" class="b-post__link"><img alt="" src="<?= WDCPREFIX .'/'. $project['path'] . $project['fname']; ?>" class="b-post__pic b-post__pic_float_right b-post__pic_margleft_10" /></a>
                <?php } elseif($project['logo_id']) {?>
                <img alt="" src="<?= WDCPREFIX .'/'. $project['path'] . $project['fname']; ?>" class="b-post__pic b-post__pic_float_right b-post__pic_margleft_10" />
                <?php }//else?>
                
                <div class="b-post__txt b-post__txt_padbot_10 <?= $project['is_bold'] == 't' ? 'b-post__txt_bold' : '';?>">
                    <?= reformat($project['descr'], 50);?>
                </div>
                            </td>
                        </tr></tbody></table>
            </div>
        </div>
    </div>							
                            <div class="i-prompt">
                                <div class="b-prompt b-prompt_padbot_30">
                                    <div class="b-prompt__txt b-prompt__txt_color_6db335 b-prompt__txt_italic">Через какое-то время вы получите ответы от фри-лансеров.<br />Они будут выглядеть так:</div>
                                    <div class="b-prompt__arrow b-prompt__arrow_left_350 b-prompt_top_35 b-prompt__arrow_6 b-prompt__arrow_zindex_1"></div>
                                </div>
                            </div>
				
    <div class="b-shadow b-shadow_m b-shadow_inline-block b-shadow_margleft_-80">
        <div class="b-shadow__right">
            <div class="b-shadow__left">
                <div class="b-shadow__top">
                    <div class="b-shadow__bottom">
                        <div class="b-shadow__body b-shadow__body_bg_fff">

                            <div class="i-prompt">
                                <div class="b-prompt b-prompt_left_-200 <?= $is_konkurs ? "b-prompt_top_430" : "b-prompt_top_100" ?> b-prompt_width_200">
                                    <? if (!$is_konkurs)  { ?>
                                    <div class="b-prompt__txt b-prompt__txt_color_6db335 b-prompt__txt_italic">Откажите фри-лансеру,<br />определите его<br /> кандидатом или<br /> исполнителем или<br />просто ответьте ему</div>
                                    <div class="b-prompt__arrow b-prompt__arrow_left_50 b-prompt__arrow_1"></div>
                                    <? } ?>
                                </div>
                            </div>
                            <? if ($is_konkurs) { ?>
                            <img class="b-shadow__pic" src="/images/master/tmp2.png" alt="" />
                            <? } else { ?>
                            <img class="b-shadow__pic" src="/images/master/tmp1.png" alt="" />
                            <? } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-shadow__tl"></div>
        <div class="b-shadow__tr"></div>
        <div class="b-shadow__bl"></div>
        <div class="b-shadow__br"></div>
    </div>

    <div class="b-buttons b-buttons_padtop_40">
        <form method="POST" id="next_step">
            <input type="hidden" name="action" value="next">
            <input type="hidden" name="complited" id="complited_step" value="0">
        </form>
        <a href="javascript:void(0)" onclick="$('complited_step').set('value', 1); $('next_step').submit();" class="b-button b-button_rectangle_color_green">
            <span class="b-button__b1">
                <span class="b-button__b2 b-button__b2_padlr_15">
                    <span class="b-button__txt">Продолжить</span>
                </span>
            </span>
        </a>&#160;&#160;
        <span class="b-buttons__txt">&#160;или&#160;</span>
        <a href="/wizard/registration/?action=exit" class="b-buttons__link b-buttons__link_color_c10601">выйти из мастера</a>
    </div>


</div>