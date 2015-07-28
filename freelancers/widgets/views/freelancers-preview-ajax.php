<?php

/**
 * Ўаблон превью работ/услуг дл€ каталога фрилансеров
 */

if ($list):
    $item = current($list);

    if($item->isPortfolio()):
        if ($item->isText()):
?>
            <h4 class="b-layout__txt b-layout__txt_center b-layout__txt_fontsize_11 b-layout__txt_ellipsis b-layout__txt_width_225">
                <a class="b-layout__link b-layout__link_bold" href="<?=$item->getUrl()?>" target="_blank" title="<?=$item->getAttrTitle()?>">
                    <?=$item->getTitleFull()?>
                </a>
            </h4>
			<?=$item->getDescr()?>       
<?php        
        else:
?>
            <h4 class="b-layout__txt b-layout__txt_center  b-layout__txt_fontsize_11 b-layout__txt_ellipsis b-layout__txt_width_225 b-layout_center b-layout__txt_padbot_5">
                <a class="b-layout__link b-layout__link_bold" href="<?=$item->getUrl()?>" target="_blank" title="<?=$item->getAttrTitle()?>" itemprop="name">
                    <?=$item->getTitleFull()?>
                </a>
            </h4>
            <a href="<?=$item->getUrl()?>" target="_blank" title="<?=$item->getAttrTitle()?>"><?=$item->getThumbnail()?></a>
            <?php if(false): //@todo: непон€тно зачем на каждой картинке один и тот же текст? ?>
            <span class="b-layout_hide" itemprop="description"><?=SeoTags::getInstance()->getImageDescription() ?></span>   
            <?php endif; ?>
<?php        
        endif;
    else:
?>
            <div class="i-pic i-pic_port i-pic_port_z-index_inherit i-pic_pad_10 i-pic_height_220 i-pic_bord_green_hover i-pic_port_inline_block">
                <div class="b-layout b-layout_relative">
                    <a href="<?=$item->getUrl()?>" class="b-pic__lnk b-pic__lnk_relative">
                        <?php if($item->hasVideo()): ?>
                            <div class="b-icon b-icon__play b-icon_absolute b-icon_bot_4 b-icon_left_4"></div>
                        <?php endif; ?>
                        <?php if ($image_url = $item->getThumbnailUrl()): ?>
                            <img width="200" height="150" class="b-pic" src="<?=$image_url?>" alt="" title=""/>
                        <?php else: ?>
                            <div class="b-pic b-pic_no_img b-pic_w200_h150 b-pic_bg_f2"></div>
                        <?php endif; ?>
                    </a>
                    <a class="b-pic__price-box b-pic__price-box_pay b-pic__price-box b-pic__price-box_noline" href="javascript:void(0);" data-url="<?=$item->getUrl()?>" onclick="TServices_Catalog.orderNow(this);"><?=$item->getPrice()?>			
                    <?php if (($sold_count = $item->getSoldCount()) > 0): ?>
                        <span title=" оличество продаж услуги"><span class="b-icon b-icon__tu2 b-icon_top_2"></span> <?=$sold_count?></span>
                    <?php endif; ?>
                    </a>
                </div>
                <div class="b-layout__txt b-layout__txt_padtop_10 b-layout_overflow_hidden">
                    <a href="<?=$item->getUrl()?>" class="b-layout__link b-layout__link_no-decorat b-layout__link_color_000 b-layout__link_inline-block"><?=$item->getTitle()?></a>
                </div>
            </div>
<?php endif; ?>
<?php endif; ?>