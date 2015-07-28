<? if ($sort) { ?>
<li>
    <a class="b-layout__link" href="<?= $opinionsLink . $ratingFilterLinkParams?>#op_head">Все</a> <?= $opinionsAll ?>
</li>
<? } else { ?>
<li class="a">
    <span class="a"><span><span>Все <?= $opinionsAll ?></span></span></span>
</li>
<? } ?>


<? if ($sort != 1) { ?>
<li>
    <a class="b-layout__link" href="<?= $opinionsLink . $ratingFilterLinkParams ?>&sort=1#op_head">Положительные</a> <?= $opinionsTotal['p'] ?>
</li>
<? } else { ?>
<li class="a">
    <span class="a"><span><span>Положительные <?= $opinionsTotal['p'] ?></span></span></span>
</li>
<? } ?>


<? if ($sort != 2) { ?>
<li>
    <a class="b-layout__link" href="<?= $opinionsLink . $ratingFilterLinkParams ?>&sort=2#op_head">Нейтральные</a> <?= $opinionsTotal['n'] ?>
</li>
<? } else { ?>
<li class="a">
    <span class="a"><span><span>Нейтральные <?= $opinionsTotal['n'] ?></span></span></span>
</li>
<? } ?>


<? if ($sort != 3) { ?>
<li>
    <a class="b-layout__link" href="<?= $opinionsLink . $ratingFilterLinkParams ?>&sort=3#op_head">Отрицательные</a> <?= $opinionsTotal['m'] ?>
</li>
<? } else { ?>
<li class="a">
    <span class="a"><span><span>Отрицательные <?= $opinionsTotal['m'] ?></span></span></span>
</li>
<? } ?>

<li class="b-testimonials-filter-chose-clause" style="float:right; padding-right:65px;">
    <div class="b-filter">
        <div class="b-filter__body">
            <a href="javascript:void(0)" class="b-filter__link b-filter__link_ie7_top_3 b-filter__link_dot_0f71c8 "><?= $filter_string ?></a> <span class="b-filter__arrow b-filter__arrow_0f71c8"></span>
        </div>
        <div class="b-shadow b-shadow_marg_-32 b-filter__toggle b-filter__toggle_hide">
            <div class="b-shadow__right">
                <div class="b-shadow__left">
                    <div class="b-shadow__top">
                        <div class="b-shadow__bottom">
                            <div class="b-shadow__body b-shadow__body_pad_15_20 b-shadow__body_bg_fff b-shadow_overflow_hidden">
                                <ul class="b-filter__list" id="period_filter">
                                    <li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_15">
                                        <a href="<?= $opinionsLink . $periodFilterLinkParams ?>#op_head" class="b-filter__link<?= !$period ? ' b-filter__link_no' : '' ?>">За всё время</a> <?= $filterCounts['last_total'] ?>
                                        <span class="b-filter__marker b-filter__marker_top_5  b-filter__marker_galka<?= $period ? ' b-filter__marker_hide' : '' ?>"></span>
                                    </li>
                                    <li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_15">
                                        <a href="<?= $opinionsLink . $periodFilterLinkParams ?>&period=1#op_head" class="b-filter__link<?= $period == 1 ? ' b-filter__link_no' : '' ?>">За последний год</a> <?= $filterCounts['last_year'] ?>
                                        <span class="b-filter__marker b-filter__marker_top_5  b-filter__marker_galka<?= $period != 1 ? ' b-filter__marker_hide' : '' ?>"></span>
                                    </li>
                                    <li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_15">
                                        <a href="<?= $opinionsLink . $periodFilterLinkParams ?>&period=2#op_head" class="b-filter__link<?= $period == 2 ? ' b-filter__link_no' : '' ?>">За последние полгода</a> <?= $filterCounts['last_half_year'] ?>
                                        <span class="b-filter__marker b-filter__marker_top_5  b-filter__marker_galka<?= $period != 2 ? ' b-filter__marker_hide' : '' ?>"></span>
                                    </li>
                                    <li class="b-filter__item b-filter__item_lineheight_15">
                                        <a href="<?= $opinionsLink . $periodFilterLinkParams ?>&period=3#op_head" class="b-filter__link<?= $period == 3 ? ' b-filter__link_no' : '' ?>">За последний месяц</a> <?= $filterCounts['last_month'] ?>
                                        <span class="b-filter__marker b-filter__marker_top_5  b-filter__marker_galka<?= $period != 3 ? ' b-filter__marker_hide' : '' ?>"></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</li>
<li class="b-testimonials-filter-chose-clause" style="float:right;">
    <div class="b-filter">
        <div class="b-filter__body">
            <a href="javascript:void(0)" class="b-filter__link b-filter__link_ie7_top_3 b-filter__link_dot_0f71c8 "><?= $author_filter_string ?></a> <span class="b-filter__arrow b-filter__arrow_0f71c8"></span>
        </div>
        <div class="b-shadow b-shadow_marg_-32 b-filter__toggle b-filter__toggle_hide">
            <div class="b-shadow__right">
                <div class="b-shadow__left">
                    <div class="b-shadow__top">
                        <div class="b-shadow__bottom">
                            <div class="b-shadow__body b-shadow__body_pad_15_20 b-shadow__body_bg_fff b-shadow_overflow_hidden">
                                <ul class="b-filter__list" id="period_filter">
                                    <li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_15">
                                        <a href="<?= $opinionsLink . $authorFilterLinkParams ?>&author=0#op_head" class="b-filter__link<?= !$author ? ' b-filter__link_no' : '' ?>">От всех пользователей</a> <?= $filterCounts['from_total'] ?>
                                        <span class="b-filter__marker b-filter__marker_top_5  b-filter__marker_galka<?= $author ? ' b-filter__marker_hide' : '' ?>"></span>
                                    </li>
                                    <li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_15">
                                        <a href="<?= $opinionsLink . $authorFilterLinkParams ?>&author=1#op_head" class="b-filter__link<?= $author == 1 ? ' b-filter__link_no' : '' ?>">От фрилансеров</a> <?= $filterCounts['from_frl'] ?>
                                        <span class="b-filter__marker b-filter__marker_top_5  b-filter__marker_galka<?= $author != 1 ? ' b-filter__marker_hide' : '' ?>"></span>
                                    </li>
                                    <li class="b-filter__item b-filter__item_lineheight_15">
                                        <a href="<?= $opinionsLink . $authorFilterLinkParams ?>&author=2#op_head" class="b-filter__link<?= $author == 2 ? ' b-filter__link_no' : '' ?>">От работодателей</a> <?= $filterCounts['from_emp'] ?>
                                        <span class="b-filter__marker b-filter__marker_top_5  b-filter__marker_galka<?= $author != 2 ? ' b-filter__marker_hide' : '' ?>"></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</li>
<script type="text/javascript">
    $$('#period_filter a.b-filter__link').addEvent('selected', function(){
        window.location.href = this.get('href');
    });
</script>