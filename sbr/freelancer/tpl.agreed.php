<div class="b-layout b-layout__page "> 

    <? include($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.sbr-crumbs.php") ?>
    <? include($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.stage-user.php") ?>
    <div class="b-fon b-fon_width_full">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
            <div class="b-fon__txt b-fon__txt_padbot_5"><span class="b-icon b-icon_sbr_gok b-icon_margleft_-20"></span>Вы согласились на сделку, работодатель уже получил уведомление об этом. <br />Не приступайте к работе, пока работодатель не зарезервирует деньги. Перейти к  <a class="b-fon__link" href="/sbr/?id=<?= $sbr->id ?>">списку сделок</a>.</div>
            <div class="b-fon__txt">Если у вас возникнут вопросы, обращайтесь в <a class="b-fon__link" href="https://feedback.fl.ru/">службу поддержки</a>.</div>
        </div>
    </div>
</div>