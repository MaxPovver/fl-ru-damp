    <h1 class="b-page__title">Услуги сайта</h1>
    <table class="b-layout__table b-layout__table_margbot_20 b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__td b-layout__td_padtop_15 b-layout__td_width_140 b-layout__td_width_null_ipad">
                <img class="b-layout__pic b-layout__pic_center b-page__desktop" src="/images/promo-icons/big/11.png" alt="" width="82" height="90" />
            </td>
            <td class="b-layout__td b-layout__td_padright_20">
                <h2 class="b-layout__title"><a class="b-layout__link" href="/promo/bezopasnaya-sdelka/">Безопасная Сделка</a></h2>
                <ul class="b-promo__list">
                    <?php if($forEmp && !$guest) {?>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_1"></span>Вы полностью застрахованы от недобросовестных исполнителей.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_2"></span>Все сотрудничество ведется онлайн &mdash; без бумажной волокиты.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_3"></span>Возможность официально провести сделку.</li> 
                    <?php } elseif($forFrl && !$guest) {?>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_1"></span>Вы полностью застрахованы от недобросовестных заказчиков.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_2"></span>Все требования и сроки по проекту зафиксированы внутри сделки.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_3"></span>Все сотрудничество ведется онлайн &mdash; без бумажной волокиты.</li> 
                    <?php } else {//else?>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_1"></span>Вы полностью застрахованы от недобросовестных заказчиков и исполнителей.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_2"></span>Все сотрудничество ведется онлайн &mdash; без бумажной волокиты.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_3"></span>Возможность официально провести сделку.</li>
                    <?php }//else?>
                </ul>
                <?php if ($forEmp && !$guest) { ?><div class="b-buttons b-buttons_padtop_15"> 
                    <?php /*<a href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?site=new" class="b-button b-button_flat b-button_flat_green">Начать новую сделку</a>*/ ?>
                </div> <?php } else { //if?><div class="b-buttons b-buttons_padtop_15"> 
                    <a href="/promo/bezopasnaya-sdelka/" class="b-button b-button_flat b-button_flat_green">Подробнее</a>
                </div><?php } ?>
            </td>                       
            <td class="b-layout__td b-layout__td_padtop_15 b-layout__td_width_140 b-layout__td_width_null_ipad">
                <?php if($forEmp && !$guest) { ?>
                <span title="Платный аккаунт" class="b-icon b-icon__mpro b-icon__mpro_e b-page__desktop"></span>
                <?php } elseif($forFrl && !$guest) {//if?>
                <span title="Платный аккаунт" class="b-icon b-icon__mpro b-icon__mpro_f b-page__desktop"></span>
                <?php } else {//elseif?>
                <span title="Платный аккаунт" class="b-icon b-icon__mpro b-icon__mpro_fe b-page__desktop"></span>
                <?php }//else?>
            </td>
            <td class="b-layout__td">
                <h2 class="b-layout__title">
                    <?php if(!$guest) {?>
                    <a class="b-layout__link" href="<?= $forFrl ? '/payed/' : '/payed-emp/'?>">Профессиональный аккаунт</a>
                    <?php }else {//if?>
                    Профессиональный аккаунт
                    <?php }//else?>
                </h2>
                <ul class="b-promo__list">
                    <?php if($forEmp && !$guest) {?>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_1"></span>Вы видите контакты всех исполнителей.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_2"></span>Вы получаете скидки на платные<br />услуги.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_3"></span>Вы можете указать больше информации о себе.</li>
                    <?php } elseif($forFrl && !$guest) { //elseif?>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_1"></span>Вы видите контакты всех <br />работодателей.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_2"></span>Вы можете неограниченно отвечать <br />на проекты.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_3"></span>Вы можете указать 4 дополнительные специализации.</li> 
                    <?php } else {//if?>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_1"></span>Вы видите контакты всех <br />работодателей.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_2"></span>Вы видите контакты всех исполнителей.</li>
                    <li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_lineheight_18"><span class="b-promo__item-number b-promo__item-number_3"></span>Дополнительные бонусы и скидки на платные услуги.</li>
                    <?php }//else?>
                </ul>
                <div class="b-buttons b-buttons_padtop_15"> 
                    <a href="<?= $forFrl ? '/payed/' : '/payed-emp/'?>" class="b-button b-button_flat b-button_flat_green">Купить PRO</a>
                </div>
            </td>
        </tr>
    </table>
    <div class=" b-promo__line b-promo__line_padbot_30  b-page__desktop"></div>

<?php if ($forEmp) { ?>
    <?php if ($guest) { ?>
    <h2 class="b-layout__title">Для работодателей</h2>
    <?php }//if?>
    
    <div class="b-layout__one b-layout__one_width_33ps b-layout__one_width_full_iphone b-layout__one_inline-block b-promo__servis b-promo__servis_cont">
        <div class="b-layout__txt b-layout__txt_margleft_70 b-layout__txt_padright_15 b-layout__txt_margleft_null_iphone">
            <h3 class="b-layout__h3"><a class="b-layout__link b-layout__link_bold" href="/public/?step=1&kind=7">Платные конкурсы</a></h3>
            <div class="b-layout__txt b-layout__txt_padbot_20">Объявить конкурс может любой работодатель. Фрилансеры выполнят конкурсное задание, после чего будет выбран победитель.</div>
        </div>
    </div>
    <div class="b-layout__one b-layout__one_width_33ps b-layout__one_width_full_iphone b-layout__one_inline-block b-promo__servis b-promo__servis_let">
        <div class="b-layout__txt b-layout__txt_margleft_70 b-layout__txt_padright_15 b-layout__txt_margleft_null_iphone">
            <h3 class="b-layout__h3"><a class="b-layout__link b-layout__link_bold" href="/masssending/">Рассылка по каталогу</a></h3>
            <div class="b-layout__txt b-layout__txt_padbot_20">Возможность обратиться сразу к большому количеству фрилансеров из различных разделов каталога.</div>
        </div>
    </div>
	<?php if($guest) { ?>
    <div class="b-layout__one b-layout__one_width_33ps b-layout__one_width_full_iphone b-layout__one_inline-block">
        <div class="b-layout__txt b-layout__txt_margleft_70 b-layout__txt_padright_15 b-layout__txt_relative b-layout__txt_margleft_null_iphone">
            <h3 class="b-layout__h3"><a class="b-layout__link b-layout__link_bold" href="/payed-emp/">Профессиональный аккаунт</a></h3>
            <div class="b-layout__txt b-layout__txt_padbot_20">Возможность просматривать контакты всех пользователей, скидки на дополнительные услуги, размещение в особой зоне PRO каталога работодателей и другие бонусы.</div>
            <span class="b-page__desktop b-page__ipad"><span title="PRO" class="b-icon b-icon__spro b-icon__spro_e" style="position:absolute; left:-60px; top:10px;"></span></span>
        </div>
    </div>
	<?php }//if?>
    <div class="b-layout__one b-layout__one_width_33ps b-layout__one_width_full_iphone b-layout__one_inline-block b-promo__servis b-promo__servis_prj">
        <div class="b-layout__txt b-layout__txt_margleft_70 b-layout__txt_padright_15 b-layout__txt_margleft_null_iphone">
            <h3 class="b-layout__h3"><a class="b-layout__link b-layout__link_bold" href="/public/?step=1&kind=1">Бесплатные проекты</a></h3>
            <div class="b-layout__txt b-layout__txt_padbot_20">Публикация проекта — это бесплатная услуга, доступная всем зарегистрированным работодателям.</div>
        </div>
    </div>
<?php }//if?>
<?php if ($forFrl) { ?>
    <?php if ($guest) { ?>
    <h2 class="b-layout__title b-layout__title_padtop_40">Для фрилансеров</h2>
    <?php }//if?>
    
    
    <?php if($guest) { ?>
    <div class="b-layout__one b-layout__one_width_33ps b-layout__one_width_full_iphone b-layout__one_inline-block">
        <div class="b-layout__txt b-layout__txt_margleft_70 b-layout__txt_padright_15 b-layout__txt_relative b-layout__txt_margleft_null_iphone">
            <h3 class="b-layout__h3"><a class="b-layout__link b-layout__link_bold" href="/payed/">Профессиональный аккаунт</a></h3>
            <div class="b-layout__txt b-layout__txt_padbot_20">Контакты фри-ласера видны всем работодателям и фрилансерам, размещение в особой зоне каталога фрилансеров, неограниченные ответы на проекты и повышенное внимание со стороны заказчиков</div>
            <span class="b-page__desktop b-page__ipad"><span title="PRO" class="b-icon b-icon__spro b-icon__spro_f" style="position:absolute; left:-60px; top:10px;"></span></span>
        </div>
    </div>
    <?php }//if?>
    
    <?php
    
    //@todo: оставил на случай если понадобиться блок о карусели
    
    /*
    <div class="b-layout__one b-layout__one_width_33ps b-layout__one_width_full_iphone b-layout__one_inline-block b-promo__servis b-promo__servis_pl-car">
        <div class="b-layout__txt b-layout__txt_margleft_70 b-layout__txt_padright_15 b-layout__txt_margleft_null_iphone">
            <h3 class="b-layout__h3"><a class="b-layout__link b-layout__link_bold" href="#">Место на «карусели»</a></h3>
            <div class="b-layout__txt b-layout__txt_padbot_20">Рекламное место наверху каталогов сайта. Ваше объявление увидят тысячи пользователей.</div>
        </div>
    </div>
     */
    ?>
<?php }//if ?>
    <h2 class="b-layout__title b-layout__title_padtop_40"><?= ($guest ? "Общие бесплатные услуги" : "Бесплатные услуги")?></h2>
    
    <div class="b-layout__one b-layout__one_width_33ps b-layout__one_width_full_iphone b-layout__one_inline-block b-promo__servis b-promo__servis_blog">
        <div class="b-layout__txt b-layout__txt_margleft_70 b-layout__txt_padright_15 b-layout__txt_margleft_null_iphone">
            <? if (BLOGS_CLOSED == false) { ?>
                <h3 class="b-layout__h3"><a class="b-layout__link b-layout__link_bold" href="/commune/">Сообщества</a> и <a class="b-layout__link b-layout__link_bold" href="/blogs/">блоги</a></h3>
                <div class="b-layout__txt b-layout__txt_padbot_20">Бесплатные сервисы, предназначенные для общения пользователей, обмена опытом, тематических и профессиональных обсуждений.</div>
            <? } else { ?>
                <h3 class="b-layout__h3"><a class="b-layout__link b-layout__link_bold" href="/commune/">Сообщества</a></h3>
                <div class="b-layout__txt b-layout__txt_padbot_20">Бесплатный сервис, предназначенный для общения пользователей, обмена опытом, тематических и профессиональных обсуждений.</div>
            <? } ?>
        </div>
    </div>
    <div class="b-layout__one b-layout__one_width_33ps b-layout__one_width_full_iphone b-layout__one_inline-block b-promo__servis b-promo__servis_help">
        <div class="b-layout__txt b-layout__txt_margleft_70 b-layout__txt_padright_15 b-layout__txt_margleft_null_iphone">
            <h3 class="b-layout__h3"><noindex><a rel="nofollow" class="b-layout__link b-layout__link_bold" href="https://feedback.fl.ru/">Помощь</a></noindex></h3>
            <div class="b-layout__txt b-layout__txt_padbot_20">Ответы на вопросы, помощь в затруднительных ситуациях, инструкции ко всем сервисам сайта.</div>
        </div>
    </div>