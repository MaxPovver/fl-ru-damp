<div class="b-layout b-layout__page b-promo"> 
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_center b-layout__left_padtop_80 ">
                <img class="b-promo__pic" src="/images/promo-icons/big/1.png" alt="" />
            </td>
            <td class="b-layout__right b-layout__right_width_72ps">
                <h1 class="b-page__title">Проект опубликован</h1>

                <div class="b-fon b-fon_padbot_30">
                <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
                    <div class="b-fon__txt b-fon__txt_padbot_5"><span class="b-fon__ok"></span>Ваш проект успешно опубликован и размещён на главной странице. <a class="b-fon__link" href="<?= $prj_url ?>">Перейти к проекту</a>.</div>
                    <div class="b-fon__txt">Если у вас возникнут вопросы, обращайтесь в <a class="b-fon__link" href="https://feedback.fl.ru/">службу поддержки</a>.</div>
                </div>
                </div>
                

                <?
                $teasersExclude = array('no-public', 'contest');
                include($abs_path . '/teasers/include-teaser.php');
                ?>

            </td>							
        </tr>
    </table>
</div>
