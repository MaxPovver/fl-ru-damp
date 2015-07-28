<? include($_SERVER['DOCUMENT_ROOT'] . '/projects/tpl.lenta-item.php'); ?>
<div class="b-prompt b-prompt_absolute b-prompt_left_-200 b-prompt_top_15">
    <div class="b-prompt__txt b-prompt__txt_color_6db335 b-prompt__txt_italic">Ваш проект на<br>главной странице</div>
    <div class="b-prompt__arrow b-prompt__arrow_1 b-prompt__arrow_left_30"></div>
</div>
<div class="b-buttons b-buttons_padtop_35 b-buttons_absolute b-buttons_left_0">
    <a class="b-button b-button_rectangle_color_green project_preview_send_btn project_preview_save_btn" href="javascript:void(0)">
        <span class="b-button__b1">
            <span class="b-button__b2">
                <span class="b-button__txt"><span class="b-button__txt project_preview_save_btn_text"></span><span class="b-button__colored b-button__colored_fd6c30 project_preview_save_btn_sum"></span></span>
            </span>
        </span>
    </a>&nbsp;&nbsp;&nbsp;
    <span class="project_preview_need_money" style="display:none">
        <span class="b-buttons__txt b-buttons__link_color_fff project_preview_need_money_text"></span>&#160;&#160;&#160;
        <a class="b-buttons__link b-buttons__link_color_fff" href="/bill/" id="top-payed-bill" style="display:hide">пополнить счёт</a>&#160;&#160;&#160;
    </span>
    <a class="b-buttons__link b-buttons__link_color_fff project_preview_edit_btn" href="javascript:void(0)">отредактировать</a>
</div>
<div class="b-post__preview-full">
    <link href="/css/projects3.css" rel="stylesheet" type="text/css">
    <? include($_SERVER['DOCUMENT_ROOT'] . '/projects/tpl.prj-main-info.php'); ?>
    <div class="b-prompt b-prompt_absolute b-prompt_left_-200 b-prompt_top_-10">
        <div class="b-prompt__txt b-prompt__txt_color_6db335 b-prompt__txt_italic">Внутри проект<br>будет выглядеть так</div>
        <div class="b-prompt__arrow b-prompt__arrow_1 b-prompt__arrow_left_30"></div>
    </div>
</div>