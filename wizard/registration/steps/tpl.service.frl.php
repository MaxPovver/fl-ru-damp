<h2 class="b-layout__title b-layout__title_padtop_50"><a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="toggleServices('sbr', this)">Работа без риска</a></h2>
<div class="b-layout__txt b-layout__txt_fontsize_11 services-sbr-default">Наш сервис «Безопасная сделка» защитит вас от безответственных заказчиков и поможет вести бизнес легально и открыто. Используя «Безопасную сделку», вы сведете до минимума все возможные риски, которые могут возникнуть в процессе сотрудничества с работодателем.</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_relative b-layout_hide services-sbr"><span class="b-icon b-icon_absolute b-icon_left_-140 b-icon_big_sbr"></span>«Безопасная сделка» — это сервис, который позволяет снизить все риски, возникающие в процессе сотрудничества фрилансеров и работодателей, до минимума. Воспользовавшись «Безопасной сделкой», работодатель может быть уверен в том, что его задание будет выполнено согласно установленным требованиям и в срок, а фрилансеру гарантирована оплата в полном объеме в том случае, если он вовремя предоставит заказчику выполненный проект.</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout_hide services-sbr">Безопасность сотрудничеству обеспечивает официальное документальное сопровождение — в полном соответствии с законодательством заключаются договора и предоставляются все необходимые «закрывающие» документы:</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout_hide services-sbr">&mdash; оферта на заключение договора об использовании «Безопасной Сделки»;</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout_hide services-sbr">&mdash; агентский договор или договор подряда;</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout_hide services-sbr">&mdash; акт и счет-фактура по договору.</div>
<div class="b-layout__txt b-layout_hide services-sbr"><a class="b-layout__link" href="/promo/sbr/" target="_blank">Подробнее о &laquo;Безопасной сделке&raquo;</a></div>

<input type="hidden" name="pro-frl" value="<?= $pro_frl ? $pro_frl : "0" ?>" id="pro-frl"> 
<h2 class="b-layout__title b-layout__title_padtop_50"><a class="b-layout__link <?= $pro_frl == 1 ? "b-layout__link_bordbot_dot_000" : "b-layout__link_bordbot_dot_0f71c8" ?>" href="javascript:void(0)" onclick="toggleServices('pro', this); if($(this).hasClass('b-layout__link_bordbot_dot_0f71c8')) { $('pro-frl').set('value', 0); } else { $('pro-frl').set('value', 1); }">Профессиональный аккаунт</a></h2>
<div class="b-layout__txt b-layout__txt_fontsize_11 services-pro-default <?= $pro_frl == 1 ? "b-layout_hide" : "" ?>">Повышает доверие к вам и делает вас профессионалом в глазах работодателей. С аккаунтом pro вы получите увеличенный рейтинг на сайте, а также сможете отвечать на специальные проекты для <span class="b-icon b-icon__pro b-icon__pro_f"></span>.</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_relative <?= $pro_frl == 0 ? "b-layout_hide" : "" ?> services-pro"><span class="b-icon b-icon_absolute b-icon_left_-140 b-icon_big_fpro"></span>Владельцы аккаунта  <span class="b-icon b-icon__pro b-icon__pro_f"></span> &mdash; это наиболее активная и серьезная часть аудитории Free-lance.ru.</div>
<div class="b-layout__txt b-layout__txt_padbot_10 <?= $pro_frl == 0 ? "b-layout_hide" : "" ?> services-pro">Вот некоторые преимущества профессионального аккаунта:</div>
<div class="b-layout__txt b-layout__txt_padbot_10 <?= $pro_frl == 0 ? "b-layout_hide" : "" ?> services-pro">&mdash; возможность отвечать на проекты &laquo;только для <span class="b-icon b-icon__pro b-icon__pro_f"></span>&raquo;;</div>
<div class="b-layout__txt b-layout__txt_padbot_10 <?= $pro_frl == 0 ? "b-layout_hide" : "" ?> services-pro">&mdash; бесплатные ответы на любые проекты;</div>
<div class="b-layout__txt b-layout__txt_padbot_20 <?= $pro_frl == 0 ? "b-layout_hide" : "" ?> services-pro">&mdash; повышение рейтинга и размещение в специальной зоне каталога.</div>
<div class="b-radio b-radio_layout_vertical <?= $pro_frl == 0 ? "b-layout_hide" : "" ?> services-pro">
    <div class="b-radio__item b-radio__item_padbot_20">
        <input type="radio" value="1week" name="pro" class="b-radio__input" id="b-radio__answer1" <?= ($op_code == 76?"checked":"")?>>
        <label for="b-radio__answer1" class="b-radio__label b-radio__label_fontsize_15 b-radio__label_lineheight_1"><span class="b-pay-answer__txt b-pay-answer__txt_fontsize_15 b-pay-answer__txt_bold b-pay-answer__txt_inline-block b-pay-answer__txt_width_90">1 неделя</span><span class="b-pay-answer__fm b-pay-answer__fm_fontsize_15">210 руб.</span></label>
    </div>
    <div class="b-radio__item b-radio__item_padbot_20">
        <input type="radio" value="1" name="pro" class="b-radio__input" id="b-radio__answer2" <?= ($op_code == 48?"checked":"")?>>
        <label for="b-radio__answer2" class="b-radio__label b-radio__label_fontsize_15 b-radio__label_lineheight_1"><span class="b-pay-answer__txt b-pay-answer__txt_fontsize_15 b-pay-answer__txt_bold b-pay-answer__txt_inline-block b-pay-answer__txt_width_90">1 месяц</span><span class="b-pay-answer__fm b-pay-answer__fm_fontsize_15">570 руб.</span><span class="b-pay-answer__economy">Экономия 36%</span></label>
    </div>
    <div class="b-radio__item b-radio__item_padbot_20">
        <input type="radio" value="3" name="pro" class="b-radio__input" id="b-radio__answer3" <?= ($op_code == 49?"checked":"")?>>
        <label for="b-radio__answer3" class="b-radio__label b-radio__label_fontsize_15 b-radio__label_lineheight_1"><span class="b-pay-answer__txt b-pay-answer__txt_fontsize_15 b-pay-answer__txt_bold b-pay-answer__txt_inline-block b-pay-answer__txt_width_90">3 месяца</span><span class="b-pay-answer__fm b-pay-answer__fm_fontsize_15">1620 руб.</span><span class="b-pay-answer__economy">Экономия 40%</span></label>
    </div>
    <div class="b-radio__item b-radio__item_padbot_20">
        <input type="radio" value="6" name="pro" class="b-radio__input" id="b-radio__answer3" <?= ($op_code == 50?"checked":"")?>>
        <label for="b-radio__answer3" class="b-radio__label b-radio__label_fontsize_15 b-radio__label_lineheight_1"><span class="b-pay-answer__txt b-pay-answer__txt_fontsize_15 b-pay-answer__txt_bold b-pay-answer__txt_inline-block b-pay-answer__txt_width_90">6 месяцев</span><span class="b-pay-answer__fm b-pay-answer__fm_fontsize_15">3060 руб.</span><span class="b-pay-answer__economy">Экономия 43%</span></label>
    </div>
    <div class="b-radio__item b-radio__item_padbot_20">
        <input type="radio" value="12" name="pro" class="b-radio__input" id="b-radio__answer3" <?= ($op_code == 51?"checked":"")?>>
        <label for="b-radio__answer3" class="b-radio__label b-radio__label_fontsize_15 b-radio__label_lineheight_1"><span class="b-pay-answer__txt b-pay-answer__txt_fontsize_15 b-pay-answer__txt_bold b-pay-answer__txt_inline-block b-pay-answer__txt_width_90">1 год</span><span class="b-pay-answer__fm b-pay-answer__fm_fontsize_15">5400 руб.</span><span class="b-pay-answer__economy">Экономия 50%</span></label>
    </div>
    <div class="b-radio__item b-radio__item_padbot_20">
        <input type="radio" value="-1" name="pro" class="b-radio__input" id="b-radio__answer1" <?= ($op_code > 0?"":"checked")?>>
        <label for="b-radio__answer1" class="b-radio__label b-radio__label_valign_top b-radio__label_fontsize_13">Не покупать аккаунт PRO <span class="b-radio__txt b-radio__txt_valign_top b-radio__txt_inline-block b-radio__txt_color_c10600">&mdash; прикреплённые вами примеры работ и дополнительные<br />&#160;&#160;&#160;&#160;ответы на проекты не опубликуются</span></label>
    </div>
</div>
<div class="b-layout__txt <?= $pro_frl == 0 ? "b-layout_hide" : "" ?> services-pro"><a class="b-layout__link" href="/payed/" target="_blank">Подробнее о профессиональном аккаунте</a></div>

<h2 class="b-layout__title b-layout__title_padtop_50"><a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="toggleServices('adv', this)">Реклама аккаунта</a></h2>
<div class="b-layout__txt b-layout__txt_fontsize_11 services-adv-default">На нашем сайте зарегистрировано большое количество фрилансеров, которые ежедневно подыскивают для себя новые заказы. Для того чтобы выделиться среди конкурентов и стать заметнее для работодателей, вы можете воспользоваться дополнительными сервисами.</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_relative b-layout_hide services-adv"><span class="b-icon b-icon_absolute b-icon_left_-140 b-icon_big_reclam"></span>Привлечь заказчиков к своему аккаунту можно несколькими способами:</div>
<table class="b-layout__table b-layout_hide services-adv" border="0" cellpadding="0" cellspacing="0">
    <tr class="b-layout__tr">
        <td class="b-layout__left">
            <h3 class="b-layout__h3">Платное место в карусели на главной странице</h3>
            <div class="b-layout__txt b-layout__txt_padbot_10">Вы оплачиваете стоимость услуги и занимаете рекламное место, слева в строке. И находитесь на этом месте до тех пор, пока следующий пользователь не оплатит такую же услугу. В таком рекламном блоке публикуются ваша фотография, специализация и короткое сообщение о предлагаемых услугах.</div>
            <div class="b-layout__txt b-layout__txt_padbot_20"><a class="b-layout__link" href="/pay_place/top_payed.php" target="_blank">Заказать размещение наверху главной страницы</a></div>
        </td>
        <td class="b-layout__right  b-layout__right_padbot_20 b-layout__right_padleft_20">
            <div class="b-shadow b-shadow_m b-shadow_inline-block">
                <div class="b-shadow__right">
                    <div class="b-shadow__left">
                        <div class="b-shadow__top">
                            <div class="b-shadow__bottom">
                                <div class="b-shadow__body b-shadow__body_bg_fff">
                                    <img class="b-layout__pic" src="/images/master/tmp3.png" alt="" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-shadow__tl"></div>
                <div class="b-shadow__tr"></div>
                <div class="b-shadow__bl"></div>
                <div class="b-shadow__br"></div>
            </div>
        </td>
    </tr>
</table>
<table class="b-layout__table b-layout_hide services-adv" border="0" cellpadding="0" cellspacing="0">
    <tr class="b-layout__tr">
        <td class="b-layout__left">
            <h3 class="b-layout__h3">Платное место в карусели в каталоге фрилансеров</h3>
            <div class="b-layout__txt b-layout__txt_padbot_10">С помощью этого сервиса вы можете разместить небольшое рекламное объявление, содержащее ваши фотографию, специализацию и короткое сообщение, в специальной строке наверху каталога фрилансеров. По мере оплаты такой же услуги другими пользователями ваше объявление будет постепенно сдвигаться вправо.</div>
            <div class="b-layout__txt b-layout__txt_padbot_20"><a class="b-layout__link" href="/pay_place/top_payed.php?catalog" target="_blank">Заказать размещение наверху каталога</a></div>
        </td>
        <td class="b-layout__right b-layout__right_padleft_20">
            <div class="b-shadow b-shadow_m b-shadow_inline-block">
                <div class="b-shadow__right">
                    <div class="b-shadow__left">
                        <div class="b-shadow__top">
                            <div class="b-shadow__bottom">
                                <div class="b-shadow__body b-shadow__body_bg_fff">
                                    <img class="b-layout__pic" src="/images/master/tmp4.png" alt="" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-shadow__tl"></div>
                <div class="b-shadow__tr"></div>
                <div class="b-shadow__bl"></div>
                <div class="b-shadow__br"></div>
            </div>
        </td>
    </tr>
</table>

<h2 class="b-layout__title b-layout__title_padtop_50"><a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="toggleServices('blog', this)">Блоги и сообщества</a></h2>
<div class="b-layout__txt b-layout__txt_fontsize_11 services-blog-default">У нас одно из крупнейших сообществ фрилансеров и работодателей в Рунете. Каждый день в «Блогах» публикуются сотни сообщений на самые различные темы. Вы можете задать любой вопрос пользователям нашего сайта.</div>
<div class="b-layout__txt b-layout__txt_padtop_10 b-layout__txt_fontsize_11 services-blog-default">Также у нас есть сообщества &ndash; это группы пользователей по интересам. Выбирайте понравившиеся вам сообщества, вступайте, общайтесь и ищите единомышленников.</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_relative b-layout_hide services-blog"><span class="b-icon b-icon_absolute b-icon_left_-140 b-icon_big_blog"></span>На нашем сайте одно из крупнейших сообществ фрилансеров и заказчиков. Каждый день публикуется около 1000 новых постов, каждый пост в среднем комментирует 10 человек.</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout_hide services-blog">
    Пользователи общаются на различные темы, как профессиональные, так и общечеловеческие: <?php foreach($themes_blogs as $key=>$theme) { ?> <a class="b-layout__link" href="/blogs/<?=$theme['link']?>/" target="_blank"><?= ($theme['t_name'])?></a><?= ($key+1 != count($themes_blogs)) ? ",":""?><?php }//foreach?> и другие.
</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout_hide services-blog">
    Кроме блогов есть сообщества — это группы пользователей по интересам. Есть множество интересных сообществ в которых вы найдете единомышленников: <?php foreach($themes_commune as $key=>$ctheme) { ?> <a class="b-layout__link" href="/commune/?id=<?=$ctheme['id']?>/" target="_blank"><?= ($ctheme['name'])?></a><?= ($key+1 != count($themes_commune)) ? ",":""?><?php }//foreach?> и многие другие.
</div>