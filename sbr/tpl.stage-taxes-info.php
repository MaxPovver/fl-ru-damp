<div class="b-tax b-tax_margbot_15 <?= $tax_hide ? "b-tax_hide" : ""; ?>" id="tax_info">
    <div class="b-tax__fon">
        <div class="b-tax__rama-t">
            <div class="b-tax__rama-b">
                <div class="b-tax__rama-l">
                    <div class="b-tax__rama-r">
                        <div class="b-tax__content">
                            <div class="b-tax__level b-tax__level_padbot_12">
                                <div class="b-tax__txt b-tax__txt_width_220 b-tax__txt_inline-block">Бюджет этапа</div>
                                <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block" id="budget_stage"><?= sbr_meta::view_cost($this->data['cost'], $this->sbr->cost_sys) ?></div>
                            </div>
                            <? if($taxes) { ?>
                            <div class="b-tax__level b-tax__level_padbot_12 b-tax__level_double">
                                <div class="b-tax__txt b-tax__txt_padleft_1 b-tax__txt_width_220 b-tax__txt_inline-block b-tax__txt_fontsize_11">Налоги и вычеты</div>
                                <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block b-tax__txt_fontsize_11">Сумма, руб.</div>
                                <div class="b-tax__txt b-tax__txt_width_130 b-tax__txt_inline-block b-tax__txt_fontsize_11">% от бюджета проекта</div>
                            </div>
                            <? }//if?>
                            <? foreach ($taxes as $k => $tax) { ?>
                                <div class="b-tax__level b-tax__level_padbot_12 b-tax__level_padtop_15">
                                    <div class="b-tax__txt b-tax__txt_width_220 b-tax__txt_inline-block">
                                        <? if (18 <= $tax['tax_id'] && $tax['tax_id'] <= 35) { ?>
                                            <div class="b-tax__txt b-tax__txt_width_220 b-tax__txt_inline-block">
                                                <?= $tax['name'] ?>
                                            </div>
                                        <? } else { ?>
                                        <div class="i-shadow i-shadow_inline-block i-shadow_margleft_-16">
                                            <span class="b-shadow__icon b-shadow__icon_margright_5 b-shadow__icon_quest"></span><div 
                                            class="b-shadow b-shadow_width_270 b-shadow_left_-117 b-shadow_top_15 b-shadow_hide b-moneyinfo">
                                                <div class="b-shadow__right">
                                                    <div class="b-shadow__left">
                                                        <div class="b-shadow__top">
                                                            <div class="b-shadow__bottom">
                                                                <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                                                                    <div class="b-shadow__txt"><?= $tax['name'] ?></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="b-shadow__tl"></div>
                                                <div class="b-shadow__tr"></div>
                                                <div class="b-shadow__bl"></div>
                                                <div class="b-shadow__br"></div>
                                                <span class="b-shadow__icon b-shadow__icon_close"></span>
                                                <span class="b-shadow__icon b-shadow__icon_nosik"></span>
                                            </div>
                                        </div><?= $tax['abbr'] ?><? if ($tax['tax_id'] == 2 || $tax['tax_id'] == 3) { ?> free-lance.ru<? } ?>
                                        <? } ?>
                                    </div>
                                    <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block tax-cost tax-cost-all" id="taxcost_<?=$cost_sys?>">&minus; <?= sbr_meta::view_cost($tax['tax_cost'], $cost_sys, false) ?></div>
                                    <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block tax-cost b-tax_hide tax-cost-<?=exrates::FM?>" id="taxcost_">&minus; <?= sbr_meta::view_cost(round($tax['tax_cost']/30, 2), exrates::FM, false) ?></div>
                                    <div class="b-tax__txt b-tax__txt_width_130 b-tax__txt_inline-block b-tax__txt_fontsize_11"><?= $tax['percent'] ?></div>
                                </div>
                            <? } //foreach ?>
                            <div class="b-tax__level b-tax__level_padtop_15" id="tax_sum">
                                <div class="b-tax__txt b-tax__txt_bold b-tax__txt_width_220 b-tax__txt_inline-block">Вы получите</div>
                                <div class="b-tax__txt b-tax__txt_inline-block"><span class="b-tax__txt b-tax__txt_bold" id="tax_ammount"><?= sbr_meta::view_cost( $this->type_payment != exrates::FM ? $total_sum : $total_sum_fm, $this->type_payment != exrates::FM ? $cost_sys : $this->type_payment  ) ?></span> и <?= $RT?> <?= ending($RT, 'балл', 'балла', 'баллов')?> рейтинга</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <span class="b-tax__nosik"></span>
</div>