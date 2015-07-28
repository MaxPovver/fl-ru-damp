<div class="b-tax b-tax_margbot_20 b-tax_width_700 b-layout_hide finance-taxrows taxrow_ps_<?= $ps ?> <?= ( $this->data['cost'] <= pskb::WW_ONLY_SUM && ( $ps == pskb::WW || !$taxes ) ) ? "taxrow_WW" : ""?> <?= ($this->data['cost'] > pskb::WW_ONLY_SUM ? "taxrow_ps_WW_".$ps : "")?>">
    <div class="b-tax__fon">
        <div class="b-tax__rama-t">
            <div class="b-tax__rama-b">
                <div class="b-tax__rama-l">
                    <div class="b-tax__rama-r">
                        <div class="b-tax__content b-tax__content_width_600">
                            <div class="b-tax__level b-tax__level_padbot_20">
                                <div class="b-tax__txt b-tax__txt_fontsize_22 b-tax__txt_width_340 b-tax__txt_inline-block b-tax__txt_valign_top">Бюджет <?= ($this->data['num'] + 1)?>-го этапа
                                    <div class="b-tax__txt b-tax__txt_fontsize_11">&laquo;<?= $this->data['descr']?>&raquo;</div>
                                </div>
                                <div class="b-tax__txt b-tax__txt_fontsize_22 b-tax__txt_inline-block"><?= number_format($this->data['cost'], 0, ',', ' ')?> руб.</div>
                            </div>
                            <?php if($taxes) { ?>
                            <div class="b-tax__level b-tax__level_padbot_12 b-tax__level_double">
                                <div class="b-tax__txt b-tax__txt_width_340 b-tax__txt_inline-block b-tax__txt_fontsize_11">Налоги и вычеты</div>
                                <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block b-tax__txt_fontsize_11">Сумма, руб.</div>
                                <div class="b-tax__txt b-tax__txt_width_130 b-tax__txt_inline-block b-tax__txt_fontsize_11">% от бюджета проекта</div>
                            </div>
                            <?php }//if?>
                            <?php foreach ($taxes as $k => $tax) { ?>
                            <div class="b-tax__level b-tax__level_padbot_10 b-tax__level_margbot_10 b-tax__level_padtop_15 b-tax__level_bordbot_cfd0c5">
                                <div class="b-tax__txt b-tax__txt_lineheight_16 b-tax__txt_width_340 b-tax__txt_inline-block">
                                    <?= $tax['name']?>
                                </div>

                                <div class="b-tax__txt b-tax__txt_bold b-tax__txt_width_120 b-tax__txt_inline-block">&minus; <?= sbr_meta::view_cost($tax['tax_cost'], exrates::BANK, false) ?></div>
                                <div class="b-tax__txt b-tax__txt_width_130 b-tax__txt_inline-block b-tax__txt_fontsize_11">
                                    <? if($tax['tax_code'] == 'TAX_FL') { ?>
                                    <img class="b-tax__pic b-tax__pic_float_right b-tax__pic_margtop_-12" src="/images/f.png" alt="" />
                                    <? } //if?>
                                    <?= $tax['percent'] ?>
                                </div>
                            </div>
                            <?php } //foreach?>
                            
                            <div class="b-tax__level b-tax__level_padtop_15">
                                <div class="b-tax__txt b-tax__txt_fontsize_22 b-tax__txt_valign_top b-tax__txt_width_340 b-tax__txt_inline-block">Вы получите
                                    <div class="b-tax__txt b-tax__txt_fontsize_11">
                                        на <?= ($this->data['cost'] <= pskb::WW_ONLY_SUM && $this->sbr->user_reqvs['form_type'] == sbr::FT_PHYS)? "Веб-кошелек" : sbr_meta::view_type_payment(pskb::$exrates_map[$ps]);?>
                                    </div>
                                </div>
                                <div class="b-tax__txt b-tax__txt_fontsize_11 b-tax__txt_inline-block b-tax__txt_padbot_5">
                                    <div class="b-tax__txt b-tax__txt_padbot_5 b-tax__txt_fontsize_22"><?= number_format($total_sum, 2, ',', ' ')?> руб.</div>
                                    и <span class="b-tax__txt_bold"><?= $RT?> <?= ending($RT, 'балл', 'балла', 'баллов')?></span> рейтинга
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>