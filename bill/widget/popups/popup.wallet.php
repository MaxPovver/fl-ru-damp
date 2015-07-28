<div class="b-layout__title ">Автоматическая оплата услуги</div>
<div class="b-layout__txt b-layout__txt_fontsize_15">Вы включили автопродление услуги</div>
<div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_20">Пожалуйста, выберите способ регулярного платежа.</div>
<div class="b-radio b-radio_layout_vertical walletTypes">
    <? $ignore_wallet = array(); foreach(WalletTypes::getListWallets() as $wallet) { $ignore_wallet[] = $wallet['type']; ?>
        <div class="b-radio__item b-radio__item_padbot_10">
            <input id="wallet<?=$wallet['type']?>" class="b-radio__input b-radio__input_top_1" name="wallet" type="radio" value="<?= $wallet['type']?>" <?= ($wallet['active'] == 't'? 'checked': '');?>>
            <label class="b-radio__label b-radio__label_fontsize_15" for="wallet<?=$wallet['type']?>">
                <?= WalletTypes::getNameWallet($wallet['type'])?>
                <? if($wallet['access_token'] != '') { ?>
                <span id="walletInfo<?=$wallet['type']?>">
                    <span class="b-fon b-fon_bg_f2 b-fon_pad_2_5 b-fon__border_radius_3 b-layout__txt b-layout__txt_fontsize_15"><?= Wallet::secureString($wallet['wallet'])?></span>
                    <a href="javascript:void(0)" class="b-button b-button_admin_del b-button_top_-3 removeWallet"></a>
                </span>
                <? }//if?>
            </label>
        </div>
    <? }//foreach?>
    <? foreach(WalletTypes::getAllTypes() as $type) { if(in_array($type, $ignore_wallet)) continue;?>
        <div class="b-radio__item b-radio__item_padbot_10">
            <input id="wallet<?=$type?>" class="b-radio__input b-radio__input_top_1" name="wallet" type="radio" value="<?= $type?>"  >
            <label class="b-radio__label b-radio__label_fontsize_15" for="wallet<?= $type?>">
                <?= WalletTypes::getNameWallet($type)?>
            </label>
        </div>
    <? } //foreach?>
</div>
<div class="b-buttons b-buttons_padtop_20">
    <a href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green walletActivate">Сохранить изменения</a> &nbsp;
    <span class="b-buttons__txt">или</span>
    <a class="b-buttons__link" href="javascript:void(0)" onclick="toggleWalletPopup();">отменить</a>
</div>
<form id="walletForm" method="POST" action=""></form>
