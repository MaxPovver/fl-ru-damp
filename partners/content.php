<div class="b-page__title b-page__title_center">В мае мы запускаем<br>партнерскую программу</div>

<div class="b-layout b-layout_center">
   <div class="b-layout__txt b-layout__txt_center b-layout__txt_padbot_40 b-layout__txt_fontsize_22">Если вы хотите получать<br><span class="b-layout__bold">деньги за привлечение</span><br>пользователей на наш сайт,<br>оставьте свой e-mail:</div>
</div>

<?php if ($success): ?>
    <div class="b-layout b-layout_center">
        <div class="b-layout__txt b-layout__txt_center b-layout__txt_padbot_40 b-layout__txt_fontsize_22">
            Вы подписались!
        </div>
    </div>
<?php else: ?>
    <div class="b-layout b-layout_center b-layout_width_250">
       <form action="./" method="post">
         
                <div class="b-combo">
                    <div class="b-combo__input <?php if ($form_error): ?>b-combo__input_error<?php endif;?>">
                       <input type="text" value="<?php echo htmlspecialchars($email);?>" size="80" name="email" class="b-combo__input-text" placeholder="Введите e-mail">
                    </div>
                </div>
                <?php if ($form_error): ?>
                <span class="b-page__desktop b-page__ipad">
                    <div class="i-shadow">
                    <div class="b-shadow b-shadow_m b-shadow_top_-30 b-shadow_left_260">
                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_10">
                            <div id="error_txt_captchanum" class="b-layout__txt b-layout__txt_nowrap b-layout__txt_padright_15 b-layout__txt_color_c4271f">
                                <span class="b-form__error"></span>Пожалуйста, укажите корректный e-mail адрес.
                            </div>
                        </div>
                        <span class="b-shadow__icon b-shadow__icon_close b-shadow__icon_right_12 b-shadow__icon_top_12"></span> 
                        <span class="b-shadow__icon b-shadow__icon_nosik-left b-shadow__icon_top_10 b-shadow__icon_left_-4"></span> 
                    </div>
                    </div> 
                 </span>
                 <?php endif; ?>

                
				<?php if ($form_error): ?>
                <span class="b-page__iphone">
                    <div class="b-shadow b-shadow_m">
                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_10">
                            <div id="error_txt_captchanum" class="b-layout__txt b-layout__txt_nowrap b-layout__txt_padright_15 b-layout__txt_color_c4271f">
                                <span class="b-form__error"></span>Пожалуйста, укажите<br>&#160;&#160;&#160;&#160;&#160;корректный e-mail адрес.
                            </div>
                        </div>
                        <span class="b-shadow__icon b-shadow__icon_close b-shadow__icon_right_12 b-shadow__icon_top_12"></span>
                        <span class="b-shadow__icon b-shadow__icon_nosik"></span> 
                    </div>
                 </span>
                <?php endif; ?>
                    
            <div class="b-buttons b-buttons_padtop_20">
              <button type="submit" class="b-button b-button_block b-button_flat b-button_flat_green">Узнать о запуске первым</button> 
            </div>          
       </form>
    </div>
<?php endif; ?>
