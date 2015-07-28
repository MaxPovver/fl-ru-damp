<div class="b-layout b-layout_padtop_40 g-txt_center">
  
    <h1 class="b-layout__title b-layout__title_lh_1 b-layout__title_fs24 b-layout__title_color_333 b-layout__title_padbot_25">
        Быстрый вход
    </h1>

    <div class="b-layout__txt b-layout__txt_padbot_75">
        <?php
            view_social_buttons(false, array(
                'vkontakte' => 'data-ga-event="{ec: \'user\', ea: \'authorization_started\',el: \'vk\'}"',
                'facebook' => 'data-ga-event="{ec: \'user\', ea: \'authorization_started\',el: \'fb\'}"',
                'odnoklassniki' => 'data-ga-event="{ec: \'user\', ea: \'authorization_started\',el: \'od\'}"'
            ));
        ?>
        <?php if (isset($_SESSION['opauth_error']) && $_SESSION['opauth_error']): ?>
            <div class="b-layout__txt b-layout__txt_padtop_10 b-layout__txt_color_c4271f">
                <?=$_SESSION['opauth_error']?>
            </div>
            <?php unset($_SESSION['opauth_error']); ?>
        <?php endif; ?>
    </div>

    <h2 class="b-layout__title b-layout__title_lh_1 b-layout__title_fs24 b-layout__title_color_333 b-layout__title_padbot_25">
        Войти
    </h2>
    
    <div class="b-layout b-layout_inline-block b-layout_width_330 b-layout_padbot_20 b-layout_width_full_iphone">
        <?=$form?>
    </div>
    
    <div class="b-layout__txt">
        <a href="/remind/" class="b-layout__link b-layout__link_fontsize_18 b-layout__link_no-decorat">Восстановить забытый пароль</a>
        <br/>
        <a href="/registration/" class="b-layout__link b-layout__link_fontsize_18 b-layout__link_no-decorat">Регистрация</a>
    </div>

</div>    

