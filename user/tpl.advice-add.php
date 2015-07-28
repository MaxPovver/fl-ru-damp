<?php
/**
 *@deprecated #0019740 
 */
return;
?>
<div class="b-post b-post_pad_10_15_15 b-fon__body_bg_f0ffdf b-fon__body_bordbot_dfedcf  advice-add-form b-textarea_hidden">
    <div class="b-post__body ">
        <div class="b-post__avatar b-post__avatar_margright_10">
            <a class="b-post__link" href="#"><?= view_avatar($_user->login, $_user->photo, 1, 1, 'b-post__userpic') ?></a>
        </div>
        <div class="b-post__content b-post__content_margleft_60 b-post__content_overflow_hidden">
            <div class="b-post__txt b-post__txt_float_right"><a class="b-post__link b-post__link_fontsize_11 b-post__link_color_4e" href="https://feedback.fl.ru/article/details/id/199" target="_blank">Что такое рекомендация?</a></div>
            <div class="b-username b-username_bold b-username_padbot_10">Ваш отзыв</div>			

            <div class="b-post__voice b-post__voice_positive"></div>&#160;<div class="b-post__txt b-post__txt_top_-3 b-post__txt_fontsize_11 b-post__txt_inline-block">Рекомендация может быть только положительной</div>
            <form action="">
                <input type="hidden" name="user_to" value="<?= $user->uid ?>"/>
                <div class="b-textarea">
                    <textarea class="b-textarea__textarea b-textarea__textarea_height_120 tawl" rel="<?= paid_advices::MAX_DESCR_ADVICE?>" id="advice_text" name="name" cols="80" rows="5"></textarea>
                </div>
            </form>
            <?php if(!is_emp($_SESSION['role'])) { ?>
            <div class="b-post__foot b-post__foot_padtop_15">У вас должен быть заполнен раздел «<a class="b-post__link" href="/users/<?=$_SESSION['login']?>/setup/finance/" target="_blank">Финансы</a>»</div>
            <?php }//if?>
        </div>
    </div>
    <div class="b-post__foot b-post__foot_padtop_15 b-post__foot_padleft_60 b-buttons">
        <a class="b-button b-button_rectangle_color_transparent_green advice-new" onclick="return false" href="#">
            <span class="b-button__b1">
                <span class="b-button__b2">
                    <span class="b-button__txt">Отправить</span>
                </span>
            </span>
        </a>
        <span class="b-buttons__txt b-buttons__txt_padleft_5">рекомендацию на проверку <?= is_emp($user->role)?'работодателю':'фрилансеру'?> или</span> <a class="b-buttons__link b-buttons__link_dot_c10601" onclick="adviceAddFormClose()" href="javascript:void(0)"> закрыть не отправляя </a>
    </div>
</div>