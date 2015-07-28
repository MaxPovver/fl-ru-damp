<h2 class="b-shadow__title b-shadow__title_padbot_15">ѕрофессиональный аккаунт на  
    <span class="i-shadow">
        <a href="javascript:void(0)" class="b-layout__link b-layout__link_inline-block b-layout__link_bold b-layout__link_fontsize_18 b-layout__link_ygol popup-mini-open upd-period-data"><?= $last_operation['month']?> <?= ending($last_operation['month'], 'мес€ц', 'мес€ца', 'мес€цев')?></a>
        <div class="b-shadow b-shadow_m b-shadow_left_-11 b-shadow_top_25 b-shadow_hide b-shadow_width_220 popup-mini body-shadow-close period-pro-popup">
            <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_10">
            <?php foreach($popup_mini_data as $data) { $eco   = ( $data['month'] * payed::PRICE_EMP_PRO - $data['cost'] ); ?>
            <div class="b-layout__txt b-layouyt__txt_weight_normal">
                <a class="b-layout__link b-layout__link_no-decorat select-type" href="javascript:void(0)"
                   data-opcode="<?= $data['opcode']?>"
                   data-cost="<?= $data['cost']?>" 
                   data-period="<?= $data['month']?> <?= ending($data['month'], 'мес€ц', 'мес€ца', 'мес€цев')?>">
                    <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_width_90 <?= $data['opcode'] == $service['last_operation']['op_code'] ? "b-layout__txt_color_808080" : ""?> b-layout__txt_fontsize_15 select-name">
                        <?= $data['month']?> <?= ending($data['month'], 'мес€ц', 'мес€ца', 'мес€цев')?>
                    </span>
                    <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_width_90 b-layout__txt_fontsize_15 b-layout__txt_color_fd6c30 b-layout__txt_nowrap"><?= $data['cost']?> рублей</span>
                    <? if($eco > 0) { ?>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_6db335 b-layout__txt_nowrap" style="padding-left:90px;">&#160;<?= $eco;?> руб. экономии</span>
                    <? }//if?>
                </a>
            </div>
            <?php }//foreach?>
            </div>
            <span class="b-shadow__icon b-shadow__icon_nosik b-shadow__icon_left_30"></span>
        </div>
    </span> 
</h2>
<?/*
<div class="b-check">
    <input id="auto-pro" class="b-check__input auto-prolong" name="auto-prolong" type="checkbox" value="1">
    <label for="auto-pro" class="b-check__label b-check__label_fontsize_15">≈жемес€чное автопродление аккаунта</label>
</div>
*/ ?>
<div class="b-buttons b-buttons_padtop_15">
    <a href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green btn_add_service"> упить за <span class="upd-cost-sum"><?= $last_operation['sum']?></span> руб.</a>
</div>