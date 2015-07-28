<? if(!$curr_sbr->reserved_id && $curr_sbr->status == sbr::STATUS_PROCESS && $curr_sbr->state != 'new') { ?>
<div class="b-buttons b-buttons_padtop_15 b-buttons_padbot_15 b-buttons_padleft_17">
    <a class="b-button b-button_flat b-button_flat_grey" href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?site=reserve&id=<?= $curr_sbr->id;?>">Зарезервировать деньги</a>
    <span class="b-buttons__txt b-buttons__txt_fontsize_13 b-buttons__txt_padleft_20"><span class="b-icon b-icon_sbr_rattent"></span>Исполнитель не начнет работать, пока деньги не зарезервированы!</span>
</div>
<? }//if?>