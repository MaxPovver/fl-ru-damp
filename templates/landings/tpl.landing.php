<div class="h-land">
     <div class="b-layout__txt b-layout__txt_center b-layout__txt_color_fff b-layout__txt_bold b-layout__txt_padtop_35 b-layout__txt_uppercase let-space">Крупнейшая биржа удаленной работы</div>
     <h1 class="b-page__title b-page__title_center b-page__title_color_fff b-page__title_size80 b-page__title_padnull">1 150 000</h1>
     <div class="b-layout__txt b-layout__txt_center b-layout__txt_color_fff b-layout__txt_fontsize_24"><span class="b-page__desktop b-page__ipad">&mdash;</span>  исполнителей ждут вас  <span class="b-page__desktop b-page__ipad">&mdash;</span></div>
     <div class="b-layout b-layout_center b-layuot_width_1000 b-layout_padtop_50 b-layout_padtop_10_iphone b-layout__one_width_full_ipad">
         <table class="b-layout__table b-layout__table_width_full b-layout__table_ipad">
            <tr class="b-layout__tr">
               <td class="b-layout__td b-layout__td_center b-layout__td_width_320 b-layout__td_ipad b-layout__td_block_iphone">
                    <a class="create_tu_button b-layout__link b-page__desktop b-page__ipad" href="/tu/">
                        <img class="b-layout__pic" src="/images/landing/s1.png" width="100" height="100">
                    </a>
                    <div class="b-layout__txt b-layout__txt_bold b-layout__txt_color_fff b-layout__txt_fontsize_18 b-layout__txt_padtop_20 b-layout__txt_padbot_10 b-page__desktop b-page__ipad">
                        <a class="create_tu_button b-layout__link b-layout__link_color_fff b-layout__link_no-decorat b-layout__link_color_fff_hover" href="/tu/">
                            Закажите услугу
                        </a>
                    </div>
                    <div class="b-layout__txt b-layout__txt_color_fff b-layout__txt_fontsize_15 b-layout__txt_padbot_20 b-page__desktop b-page__ipad">
                        <a class="create_tu_button b-layout__link b-layout__link_color_fff b-layout__link_no-decorat b-layout__link_color_fff_hover" href="/tu/">
                            Выбирайте из более чем 15&nbsp;000 <br>услуг по фиксированной цене
                        </a>
                    </div>
                    <a class="create_tu_button b-button b-button_flat b-button_flat_green b-button_flat_sbig b-button_width_210 b-button_width_full_iphone" href="/tu/">
                        Заказать <span class="b-page__iphone">услугу</span>
                    </a>
               </td>
               <td class="b-layout__td b-layout__td_center b-layout__td_width_320 b-layout__td_ipad b-layout__td_block_iphone">
                    <a class="create_project_button b-layout__link b-page__desktop b-page__ipad" href="/public/?step=1&kind=1&red=">
                        <img class="b-layout__pic" src="/images/landing/s2.png" width="100" height="100">
                    </a>
                    <div class="b-layout__txt b-layout__txt_bold b-layout__txt_color_fff b-layout__txt_fontsize_18 b-layout__txt_padtop_20 b-layout__txt_padbot_10 b-page__desktop b-page__ipad">
                        <a class="create_project_button b-layout__link b-layout__link_color_fff b-layout__link_no-decorat b-layout__link_color_fff_hover" href="/public/?step=1&kind=1&red=">
                            Опубликуйте проект
                        </a>
                    </div>
                    <div class="b-layout__txt b-layout__txt_color_fff b-layout__txt_fontsize_15 b-layout__txt_padbot_20 b-page__desktop b-page__ipad">
                        <a class="create_project_button b-layout__link b-layout__link_color_fff b-layout__link_no-decorat b-layout__link_color_fff_hover" href="/public/?step=1&kind=1&red=">
                            Получайте десятки предложений <br>от исполнителей
                        </a>
                    </div>
                    <a class="create_project_button b-button b-button_flat b-button_flat_green b-button_flat_sbig b-button_width_210 b-button_width_full_iphone" href="/public/?step=1&kind=1&red=">
                        Опубликовать <span class="b-page__iphone">проект</span>
                    </a>
               </td>
               <td class="b-layout__td b-layout__td_center b-layout__td_width_320 b-layout__td_ipad b-layout__td_block_iphone">
                    <a class="choose_freelancer_button b-layout__link b-page__desktop b-page__ipad" href="/freelancers/">
                        <img class="b-layout__pic" src="/images/landing/s3.png" width="100" height="100">
                    </a>
                    <div class="b-layout__txt b-layout__txt_bold b-layout__txt_color_fff b-layout__txt_fontsize_18 b-layout__txt_padtop_20 b-layout__txt_padbot_10 b-page__desktop b-page__ipad">
                        <a class="choose_freelancer_button b-layout__link b-layout__link_color_fff b-layout__link_no-decorat b-layout__link_color_fff_hover" href="/freelancers/">
                            Выберите исполнителя
                        </a>
                    </div>
                    <div class="b-layout__txt b-layout__txt_color_fff b-layout__txt_fontsize_15 b-layout__txt_padbot_20 b-page__desktop b-page__ipad">
                        <a class="choose_freelancer_button b-layout__link b-layout__link_color_fff b-layout__link_no-decorat b-layout__link_color_fff_hover" href="/freelancers/">
                            У нас огромный каталог <br>с отзывами и примерами работ
                        </a>
                    </div>
                    <a class="choose_freelancer_button b-button b-button_flat b-button_flat_green b-button_flat_sbig b-button_width_210 b-button_width_full_iphone" href="/freelancers/">
                        Выбрать <span class="b-page__iphone">исполнителя</span>
                    </a>
               </td>
            </tr>
         </table>
     </div>
</div>

<?php if ($tservices_chunks): ?>
    <h2 class="b-page__title b-page__title_center">Популярные услуги</h2>
    <div class="b-layout b-layout_overflow_hidden">
        <?php foreach ($tservices_chunks as $chunk): ?>
                <?php foreach ($chunk as $chunk_num => $tservice): ?>
                    <?php
                    $user = $tservice['user'];
                    $user_url = sprintf('/users/%s', $user['login']);
                    $tservice_url = sprintf('/tu/%d/%s.html', $tservice['id'], tservices_helper::translit($tservice['title']));
                    $avatar_url = tservices_helper::photo_src($user['photo'], $user['login']);

                    //$hasVideo = !empty($tservice['videos']) && count($tservice['videos']);
                            $hasVideo = false; //Теперь вместо видео-кадра - превью
                    if ($hasVideo)
                    {
                        $video = current($tservice['videos']);
                        $video_thumbnail_url = tservices_helper::setProtocol($video['image']);
                        $thumbnail200x150 = '<img width="200" height="150" class="b-pic" src="'.$video_thumbnail_url.'">';
                    } elseif(!empty($tservice['file']))
                    {
                        $hasVideo = false;
                        $image_url = tservices_helper::image_src($tservice['file'],$user['login']);
                        $thumbnail200x150 = '<img width="200" height="150" class="b-pic" src="'.$image_url.'">';
                    } else
                    {
                        $thumbnail200x150 = '<div class="b-pic b-pic_no_img b-pic_w200_h150 b-pic_bg_f2"></div>';
                    }

                    $hasVideo = !empty($tservice['videos']) && count($tservice['videos']);
                            
                            $sold_count = isset($tservice['count_sold']) ? $tservice['count_sold'] : $tservice['total_feedbacks'] // Пока сфинск не считает все покупки, будем брать отзывы. #0026584
                    ?>                
                    <div class="b-layout__tu-cols">
                        <figure class="i-pic i-pic_port i-pic_port_z-index_inherit i-pic_margbot_30 i-pic_pad_10 i-pic_height_265 i-pic_bord_green_hover">
                            <div class="b-layout b-layout_relative">
                                <a class="b-pic__lnk b-pic__lnk_relative" href="<?=$tservice_url?>">
                                    <?php if ($hasVideo) { ?><div class="b-icon b-icon__play b-icon_absolute b-icon_bot_4 b-icon_right_4"></div><?php } ?>
                                    <?=$thumbnail200x150?>
                                </a>
                                <a onclick="TServices_Catalog.orderNow(this);" data-url="<?=$tservice_url?>" href="javascript:void(0);" class="b-pic__price-box b-pic__price-box_pay b-pic__price-box b-pic__price-box_noline">
                                    <?=tservices_helper::cost_format($tservice['price'],true)?>
                                    <?php if ($sold_count != 0): ?>        
                                        <span title="Количество продаж услуги"><span class="b-icon b-icon__tu2 b-icon_top_2"></span><?=number_format($sold_count, 0, '', ' ')?></span>
                                    <?php endif; ?>
                                </a>
                            </div>
                            <figcaption class="b-layout__txt b-layout__txt_padtop_10 b-layout_overflow_hidden">
                                <a class="b-layout__link b-layout__link_no-decorat b-layout__link_color_000 b-layout__link_inline-block" href="<?=$tservice_url?>">
                                    <?=reformat($tservice['title'], 20, 0, 1)?>
                                </a>
                            </figcaption>
                            <div class="b-user b-user_padtop_10">
                                <a class="b-user__link b-user__link_color_ec6706" title="<?=$user['uname'].' '.$user['usurname']?>" href="<?=$user_url?>">
                                    <img width="15" height="15" class="b-user__pic b-user__pic_15" src="<?=$avatar_url?>" alt="">
                                    <?=view_fullname($user)?>
                                </a>
                            </div>
                        </figure>
                    </div>
                <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (isset($profs) && $profs): ?>
    <h2 class="b-page__title b-page__title_center">Исполнители по категориям</h2>
    <div class="b-layout b-layout_col_4 b-layout_col_2_ipad b-layout_col_1_iphone">
    <?php foreach ($profs as $prof): ?>
        <?php if (!isset($groups_repeat[$prof['grouplink']]) && ($groups_repeat[$prof['grouplink']] = 1)): ?>
                <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padbot_10"><a class="b-layout__link b-layout__link_no-decorat b-layout__link_fontsize_15" href="/freelancers/<?=$prof['grouplink']?>"><?=$prof['groupname']?></a></div><br>
        <?php endif; ?>
    <?php endforeach; ?>
                <div class="b-layout__txt b-layout__txt_inline-block"><span class="b-layout__txt b-layout__txt_float_left b-layout__txt_padtop_3"><?php require_once($_SERVER['DOCUMENT_ROOT'] . "/banner_promo.php"); ?></span></div>
    </div>
<?php endif; ?>

<div class="b-layout b-layout_clear_both b-layout_bordtop_b2 b-layout_padtop_20 b-layout_top_100">
    <h2 class="b-page__title b-page__title_center">Fl.ru &mdash; крупнейшая русскоязычная биржа удаленной работы </h2>
    <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20">
        <tr class="b-layout__tr">
            <td class="b-layout__td b-layout__td_width_50ps b-layout__td_padright_70">
                <h3 class="b-layout__title b-layout__title_center b-layout__title_padbot_20">Для тех, кому нужен специалист (фрилансер)</h3>
                <p class="b-layout__txt b-layout__txt_padbot_5">
                    У нас вы можете найти лучших фриланс специалистов среди 1 млн исполнителей, зарегистрированных на сайте FL.ru. Программисты, дизайнеры, художники, копирайтеры, юристы, бухгалтеры, инженеры, фотографы - тысячи удаленных сотрудников по любым freelance специализациям.
                </p>
                <p class="b-layout__txt b-layout__txt_padbot_5">
                    Вам достаточно опубликовать проект, конкурс или вакансию - и заинтересованные фрилансеры сами предложат свои услуги, помогут вам сформировать задание, определить бюджет и сроки выполнения работы. Останется только выбрать лучшего исполнителя из числа откликнувшихся фриланс специалистов и начать с ним сотрудничество.
                </p>
                <p class="b-layout__txt b-layout__txt_padbot_5">
                    Если же совсем нет времени на проекты, вы можете быстро найти и заказать нужную услугу (с фиксированной ценой и сроком выполнения) в нашем каталоге услуг. А также выбрать удаленных специалистов в каталоге, оценив их портфолио с представленными работами - и в 2 клика предложить заказ.
                </p>
                <p class="b-layout__txt b-layout__txt_padbot_5">
                    FL.ru за безопасность freelance сотрудничества! Используйте наш сервис "Безопасная сделка" с резервированием суммы на сайте для сотрудничества с фрилансерами - и мы гарантируем вам возврат средств, если работа будет выполнена некачественно и/или не в срок.
                </p>
                <p class="b-layout__txt b-layout__txt_padbot_30">Хороших вам исполнителей!</p>      
            </td>

            <td class="b-layout__td b-layout__td_padleft_50">
                <h3 class="b-layout__title b-layout__title_center b-layout__title_padbot_20">Тем, кто ищет работу на дому (фрилансерам)</h3>
                <p class="b-layout__txt b-layout__txt_padbot_5">
                    Для вас ежедневно свыше 1500 фриланс проектов, конкурсов и вакансий с поиском исполнителей. Если вы ищете удаленную работу фрилансером и умеете создавать сайты, писать тексты, администрировать, консультировать, оказывать freelance услуги на дому - добро пожаловать на сайт FL.ru.
                </p>
                <p class="b-layout__txt b-layout__txt_padbot_5">
                    Начиная работу с нашим сайтом, вам прежде всего необходимо заполнить портфолио фрилансера, добавив примеры успешно выполненных вами проектов и заказов. Указать в профиле всю необходимую информацию о своих навыках и опыте, добавить контактные данные.
                </p>
                <p class="b-layout__txt b-layout__txt_padbot_5">
                    Фрилансерам для получения работы рекомендуем регулярно просматривать ленту проектов, отвечая на подходящие вам предложения - в случае заинтересованности заказчики обязательно предложат вам сотрудничество по freelance проектам с дальнейшим выполнением работы.
                </p>
                <p class="b-layout__txt b-layout__txt_padbot_5">
                    Чтобы повысить шансы получить работу, также рекомендуем фрилансеру оформить и добавить на сайт все свои услуги, описав их и указав стоимость и срок выполнения того или иного объема работ. И не забудьте приобрести аккаунт PRO - сервис, который значительно расширит возможности вашего фриланс профиля и поможет привлечь еще больше выгодных заказов.
                </p>
                <p class="b-layout__txt b-layout__txt_padbot_30">Успешного поиска работы!</p>
            </td>
        </tr>
        <tr class="b-layout__tr">
            <td class="b-layout__td b-layout__td_padbot_40 b-layout__td_width_50ps b-layout__td_padright_70">
                <div class="b-buttons b-buttons_center"><a class="b-button b-button_flat b-button_flat_green"  href="/public/?step=1&kind=1&red=">Опубликовать проект и найти специалиста</a></div>
            </td>
            <td class="b-layout__td b-layout__td_padbot_40 b-layout__td_padleft_50">
                <div class="b-buttons b-buttons_center "><a class="b-button b-button_flat b-button_flat_green"  href="/registration/">Стать фрилансером и найти работу</a></div>
            </td>
    </table>
</div>