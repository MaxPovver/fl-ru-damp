<ul class="b-button-multi b-button-multi_mid b-button-multi_float_right">
    <li class="b-button-multi__item b-button-multi__item_first <?= $this->main->getCss();?> <?= $this->getColorMain();?>">
        <a href="<?= $this->main->getLink();?>" class="b-button-multi__link" <?= $this->main->getEvents();?>>
            <span class="b-button-multi__inner"><span class="b-button-multi__txt b-button-multi__txt_bold"><?= $this->main->name;?></span></span>
        </a>
    </li>
    <li class="b-button-multi__item b-button-multi__item_last <?= $this->main->getCss();?> <?= $this->getColorMain();?> i-shadow"> <!-- b-button-multi__item_green -->
        <a href="javascript:void(0)" class="b-button-multi__link" id="b-button-more">
            <span class="b-button-multi__inner ">
                <span class="b-button-multi__txt b-button-multi__txt_bold">или</span>&#160;<span class="b-button-multi__arrow"></span>
            </span>
        </a>
        <div class="b-shadow b-shadow_m b-shadow_width_205 b-shadow_left_-130 b-shadow_top_30 b-shadow_hide">
            <div class="b-shadow__right">
                <div class="b-shadow__left">
                    <div class="b-shadow__top">
                        <div class="b-shadow__bottom">
                            <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_10_15_5">
                                <? foreach($this->buttons as $button) {?>
                                <div class="b-shadow__txt b-shadow__txt_lineheight_14 b-shadow__txt_padbot_5">
                                    <a class="b-layout__link b-button-multi__close <?= $button->getCss();?> <?= $this->getColorLink($button->getColor());?>" href="<?=$button->getLink();?>" <?= $button->getEvents();?>><?= $button->getName();?></a>
                                </div>
                                <? } //foreach?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="b-shadow__tl"></div>
            <div class="b-shadow__tr"></div>
            <div class="b-shadow__bl"></div>
            <div class="b-shadow__br"></div>
            <span class="b-shadow__icon b-shadow__icon_left_160 b-shadow__icon_nosik"></span>
        </div>
    </li>
</ul>