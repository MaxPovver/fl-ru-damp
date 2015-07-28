<div class="b-layout b-layout_pad_10 b-layout_bord_e6 b-layout_margbot_30">
    <h3 class="b-layout__h3">Привязанная платежная система</h3>
    <?php if(!WalletTypes::checkWallet($wallet)) { ?>
    <a href="javascript:void(0)" onclick="toggleWalletPopup(event);" class="b-button b-button_rectangle_color_green">
        <span class="b-button__b1">
            <span class="b-button__b2">
                <span class="b-button__txt">Привязать</span>
            </span>
        </span>
    </a>
    <?php } else { //if?>
    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_color_808080">
        <?= WalletTypes::getNameWallet($wallet->data['type'], 1)?> &#160; <?= $wallet->getWalletBySecure()?>
    </div>
    <div class="b-layout__txt">
        <a class="b-layout__link b-layout__link_dot_c10600 b-layout__link_fontsize_15" href="javascript:void(0)" onclick="toggleWalletPopup(event);">Настроить</a>
    </div>
    <?php } //else?>
</div>