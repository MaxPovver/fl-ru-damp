<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-verification">
    <tbody>
        <tr class="b-layout__tr">
            <td class="b-layout__right_padbot_5 b-layout__right_width_72ps b-promo" style="text-align: center">
                <h1 class="b-page__title">Получи MacBook PRO 13" оплачивая услуги fl.ru в июне чаще других</h1>
            </td>
        </tr>
        <tr class="b-layout__tr">
            <td class="b-layout__right_padbot_5 b-layout__right_width_72ps b-promo" style="text-align: center">
                <div class="b-layout__txt b-layout__txt_fontsize_15">              
                    <h3>Участвуют в конкурсе &mdash; <?=$macbook_top_10_total?> чел.</h3>
                </div>
            </td>
        </tr>        
        <tr class="b-layout__tr">
            <td class="b-layout__right_padbot_5 b-layout__right_width_72ps b-promo" style="text-align: center">
                <img class="b-pic" src="https://st.fl.ru/about/macbookpro.jpg?123">
            </td>
        </tr>
        <tr class="b-layout__tr">
            <td class="b-layout__right_padbot_5 b-layout__right_width_72ps b-promo" style="text-align: center">
                <div class="b-layout__txt b-layout__txt_fontsize_15">
                    <h3>Правила конкурса:</h3>
                </div>
            </td>
        </tr>
        <tr class="b-layout__tr">
            <td class="b-layout__right_padbot_5 b-layout__right_width_72ps b-promo">
                <div class="b-layout__txt b-layout__txt_fontsize_15">
                    <ol>
                        <li>В конкурсе принимают участие только фрилансеры.</li>
                        <li>Для участия необходимо в течение июня 2015 года приобрести одну из платных услуг на сайте
                            <a href="https://www.fl.ru">FL.ru</a>:
                            <ul>
                                <li>аккаунт <a href="/payed/" target="blank">PRO</a> или <a href="/profi/" target="blank">PROFI</a>;</li>
                                <li>закрепление в каталоге фрилансеров;</li>
                                <li>закрепление в каталоге услуг;</li>
                                <li>закрепление в блоке Популярные услуги на главной странице.</li>
                            </ul>
                        </li>
                        <li>
                            После каждой покупки обновляется ТОП-10 фрилансеров, чаще других купивших в июне 2015 года вышеприведенные услуги. При подсчете учитывается количество покупок, а не их сумма.
                        </li>
                        <li>
                            Среди тех, кто по состоянию на 30 июня вошел в ТОП-10 случайным образом разыгрывается MacBook PRO 13".
                        </li>
                        <li>
                            Итоги розыгрыша будут объявлены 6 июля 2015 года.
                        </li>
                        <li>
                            Обладатель главного приза конкурса соглашается с проведением фотосессии в момент вручения приза.
                        </li>
                        <li>
                            Лицо, которое стало обладателем приза конкурса, соглашается с тем, что его имя, фамилия и другие материалы о нём могут быть использованы Организатором и третьими лицами без получения дополнительного согласия и без дополнительной платы с целью информирования о результатах проведения конкурса, а также в рекламных целях.
                        </li>
                        <li>
                            Организатор конкурса оставляет за собой право вносить дополнения и уточнения к настоящим Правилам во время проведения конкурса.
                        </li>
                    </ol>
                </div>  
            </td>
        </tr>
        <tr class="b-layout__tr">
            <td class="b-layout__right_padbot_5 b-layout__right_width_72ps b-promo" style="text-align: center">
                <div class="b-layout__txt b-layout__txt_fontsize_15">
                    <h2>Рейтинг (TOP-10)</h2>
                </div>

                <?php if ($macbook_top_10): ?>
                    <?php foreach ($macbook_top_10 as $data): ?>
                        <div class="b-layout__txt b-layout__txt_color_64 b-layout__txt_fontsize_15 b-layout__txt_lineheight_1 b-layout__txt_padbot_15">
                            <?php if ($data['user']->uname || $data['user']->usurname): ?>
                                <a class="b-layout__link b-layout__link_color_64 b-layout__link_bold b-layout_hover_link_decorated" href="<?=$data['user']->getProfileUrl()?>"><?=htmlspecialchars($data['user']->uname) . ' ' . htmlspecialchars($data['user']->usurname)?></a> 
                            <?php endif; ?>
                            [<a class="b-layout__link b-layout_h b-layout__link_color_64 b-layout__link_no-decorat" href="<?=$data['user']->getProfileUrl()?>"><?=$data['user']->login?></a>]
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </td>
        </tr>
    </tbody>
</table>
