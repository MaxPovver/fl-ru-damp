<table class="b-layout__table b-layout__table_width_full">
    <tr class="b-layout__tr">
    <td class="b-layout__left b-layout__left_padtop_30 b-layout__left_center ">
        <img class="b-layout__pic" src="/images/promo-icons/big/15.png" alt="" width="55" height="91" />
    </td>
    <td class="b-layout__right b-layout__right_width_72ps">        
        <div class="b-menu b-menu_crumbs">
            <ul class="b-menu__list">
                <li class="b-menu__item"><a class="b-menu__link" href="/service/">¬се услуги сайта</a>&nbsp;&rarr;&nbsp;</li>
            </ul>
        </div>
	<h1 class="b-page__title">ѕодн€тие закрепленного проекта</h1>
	<div class="b-layout__txt b-layout__txt_padbot_20">ƒл€ того, чтоб ваш проект стал самым первым в общей ленте и находилс€ выше всех остальных платных проектов (или конкурсов), воспользуйтесь услугой подн€ти€ закрепленного проекта. “ак ваше предложение увид€т все посетители главной страницы Free-lance.ru.</div>
    
    <table class="b-layout__table  b-layout__table_center" cellpadding="0" cellspacing="0" border="0">
    	<tr class="b-layout__tr">
        	<td class="b-layout__left b-layout__left_width_250">
            	<h3 class="b-layout__h3 b-layout__h3_padtop_17 b-layout__h3_padbot_5">—тоимость услуги</h3>
            	<div class="b-layout__txt b-layout__txt_padbot_5"><span class="b-layout__txt b-layout__txt_color_fd6c30">20 FM</span> дл€ проекта</div>
            	<div class="b-layout__txt"><span class="b-layout__txt b-layout__txt_color_fd6c30">35 FM</span> дл€ конкурса</div>
            </td>
        	<td class="b-layout__right">
                <div class="b-promo" <?php if (is_pro()) {?> style="display:none"<?}?>>
                    <div class="b-promo__note">
                        <div class="b-promo__note-inner">
                            <h3 class="b-promo__h3">— <span class="b-promo__pro b-promo__pro_emp"></span>   дешевле</h3>
                            <p class="b-promo__p b-promo__p_fontsize_13"><a class="b-promo__link" href="/payed-emp/"> упите профессиональный аккаунт</a>,</p>
                            <p class="b-promo__p b-promo__p_fontsize_13">и закрепление проекта и конкурса<br />станет дешевле на <span class="b-promo__txt b-promo__txt_color_fd6c30">10 FM</span></p>
                        </div>
                    </div>
                </div>            
            </td>
        </tr>
    </table>    
	</td>
    </tr>    
</table>


	<img  class="b-layout__pic" width="900" height="183" alt="" src="/images/promo/prj-top2-sheme.png" style="float:right; margin-bottom:30px;"  />
    <?php if ($_SESSION["uid"]) {?>
        <div style="float:right; width:900px">
            <div style="float:left;">
                <a title="¬ыбрать проект проект дл€ подн€ти€" href="/users/<?php print $_SESSION["login"]?>/setup/projects/" 
                    class="b-button b-button_round_green b-button_float_left ">
                        <span class="b-button__b1">
                            <span class="b-button__b2">
                                <span class="b-button__txt">¬ыбрать проект дл€ подн€ти€</span>
                            </span>
                        </span>
                    </a>
            </div>
        </div>
    <?php }?>