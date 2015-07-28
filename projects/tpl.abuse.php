<?php
$ctypes = array();
foreach ($complain as $compl) {
    $ctypes[] = $compl['type'];
}
?>
<input type="hidden" name="project_id" id="project_id_abuse" value="<?= $project['id'] ?>">
<input type="hidden" name="prj_abuse_id" id="prj_abuse_id" value="0">
<!-- окно для работодателей -->
<div class="b-shadow b-shadow_hide b-shadow_top_30 b-shadow_width_400 b-shadow_zindex_11 b-shadow_left_-68" id="abuse_employer_project_popup">
    <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">
    <?php foreach ($complainTypes as $abuse) {
        $is_check = (in_array($abuse['id'], $ctypes));
        $notkind = explode(",", $abuse['notkind']);
        if ($abuse['moder'] || in_array($project['kind'], $notkind)) {
            continue;
        }
        ?>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_15 b-layout__txt_padbot_5 abuse-cause-block <?= $is_check ? "b-layout__txt_color_71 abuse-checked" : "" ?>" id="abuse<?= $abuse['id'] ?>">
            <img class="b-layout__pic b-layout__pic_margleft_-15 abuse-check <?= $is_check ? 'abuse-checked' : 'b-layout__txt_hide' ?>" src="/images/galka.png">
            <span class="abuse-check-name <?= $is_check ? 'abuse-checked' : '' ?>"><?= $is_check ? $abuse['name'] : '' ?></span>
            <a class="b-layout__link b-layout__link_bold b-layout__link_bordbot_dot_0f71c8 <?= !$is_check ? "abuse-cause-link" : "abuse-checked" ?> <?= $is_check ? 'b-layout__txt_hide' : '' ?>" href="javascript:void(0)" data-cause="<?= $abuse['id'] ?>" data-textarea="<?= (int) $abuse['textarea'] ?>"><?= $abuse['name'] ?></a>
        </div>
    <? }//foreach ?>
    </div>
    <span class="b-shadow__icon b-shadow__icon_close"></span>
    <span class="b-shadow__icon  b-shadow__icon_nosik" style=" left:270px"></span>
</div>
<!-- // окно для работодателей -->

<!-- окно для модераторов -->                           
<div class="b-shadow b-shadow_hide b-shadow_top_30 b-shadow_width_400 b-shadow_zindex_11 b-shadow_left_-68" id="abuse_moderator_project_popup">
    <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">

        <span id="form_abuse" style="display:none">
            <div class="b-textarea b-textarea_margbot_10">
                <textarea class="b-textarea__textarea b-textarea_noresize" name="prj_abuse_msg" id="prj_abuse_msg" cols="" rows=""></textarea>
            </div>
            <span id="abuse_uploader"></span>
            <div class="b-buttons b-buttons_padbot_20">   
                <a href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green abuse-btn-send">Пожаловаться</a>
            </div>
        </span>

        <?php if ($is_project_complain_sent): ?>
        
        <div class="b-layout__txt b-layout__txt_fontsize_15">
            На проект уже отправлена жалоба модераторам.
        </div>
        
        <?php else: ?>
        
        <?php foreach ($complainTypes as $abuse) {
            $is_check = (in_array($abuse['id'], $ctypes));
            $notkind = explode(",", $abuse['notkind']);
            if (!$abuse['moder'] || in_array($project['kind'], $notkind)) {
                continue;
            }
            ?>
            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_15 b-layout__txt_padbot_5 abuse-cause-block <?= $is_check ? "b-layout__txt_color_71 abuse-checked" : "" ?>" id="abuse<?= $abuse['id'] ?>">
                <img class="b-layout__pic b-layout__pic_margleft_-15 abuse-check <?= $is_check ? 'abuse-checked' : 'b-layout__txt_hide' ?>" src="/images/galka.png">
                <span class="abuse-check-name <?= $is_check ? 'abuse-checked' : '' ?>"><?= $is_check ? $abuse['name'] : '' ?></span>
                <a class="b-layout__link b-layout__link_bold b-layout__link_bordbot_dot_0f71c8 
                    <?= !$is_check ? "abuse-cause-link" : "abuse-checked" ?> 
                    <?= $is_check ? 'b-layout__txt_hide' : '' ?>" 
                   href="javascript:void(0)" 
                   data-cause="<?= $abuse['id'] ?>" 
                   data-textarea="<?= (int) $abuse['textarea'] ?>" 
                   data-required="<?= (int) $abuse['required'] ?>">
                    <?= $abuse['name'] ?>
                </a>
            </div>
        <? }//foreach ?>

        <?php endif; ?>
        
        <div id="abuse-cause-error" class="b-layout__txt b-layout__txt_hide b-layout__txt_padtop_10 b-layout__txt_bold b-layout__txt_lineheight_1 b-layout__txt_color_red">
            Не удалось отправить жалобу, попробуйте еще раз.
        </div>
        
    </div>
    <span class="b-shadow__icon b-shadow__icon_close"></span>
    <span class="b-shadow__icon b-shadow__icon_right_32 b-shadow__icon_nosik"></span>
</div>
<!-- // окно для модераторов -->