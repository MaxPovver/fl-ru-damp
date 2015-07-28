<div class="b-layout">
    <div class="b-layout__right b-layout__right_width_73ps b-layout__right_float_right">
	    <h1 class="b-page__title b-page__title_padbot_30">
            <?php if($type){ ?>
                Отписка от рассылки
            <?php } else { ?>
                Отписка от рассылок
            <?php } ?>
            </h1>
   </div>


<div class="b-layout__right b-layout__right_width_73ps b-layout__right_float_right">
    <div class="b-fon b-fon_inline-block b-fon_padbot_10">
        <div class="b-fon__body b-fon__body_pad_15  b-fon__body_padleft_30 b-fon__body_lineheight_18 b-fon__body_padright_40 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
		    <span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span>Почта <span class="b-layout__bold"><?=$email ?></span> отписана 
                    <?php if ($type == 'new_projects'): ?>
                    от рассылки проектов FL.ru.
                    <?php elseif ($type == 'mailer'): ?>
                    от рассылки новостей FL.ru.
                    <?php else: ?>
                    от всех рассылок FL.ru.
                    <?php endif; ?>
		</div>
</div>