            <table class="b-layout__table">
               <tr class="b-layout__tr">
                  <td class="b-layout__td" height="60"><img class="b-pic" src="<?=WDCPREFIX?>/images/reclama2/fl-logo.png" width="60" height="60"></td>
                  <td class="b-layout__td b-layout__td_valign_mid"><div class="b-layout__txt b-layout__txt_color_6db335 b-layout__txt_fontsize_34"> &#160; &mdash; ЭТО ЭФФЕКТИВНАЯ РЕКЛАМА</div>
                  </td>
                  <td rowspan="2" class="b-layout__td"><img class="b-pic b-pic_zindex_2" src="<?=WDCPREFIX?>/images/reclama2/peaple.png" width="333" height="383"></td>
               </tr>
               <tr class="b-layout__tr">
                  <td class="b-layout__td"></td>
                  <td class="b-layout__td">
                     <div class="b-layout__txt b-layout__txt_fontsize_34 b-layout__txt_inline-block b-layout__txt_padlr_10 b-fon b-fon_bg_e1 b-fon_rad_5 b-layout__txt_margbot_10">для 100 000 посетителей в день</div><br>
                     <div class="b-layout__txt b-layout__txt_fontsize_34 b-layout__txt_inline-block b-layout__txt_padlr_10 b-fon b-fon_bg_e1 b-fon_rad_5 b-layout__txt_margbot_10">на 24 миллионах страниц в месяц</div><br>
                     <div class="b-layout__txt b-layout__txt_fontsize_34 b-layout__txt_inline-block b-layout__txt_padlr_10 b-fon b-fon_bg_e1 b-fon_rad_5 b-layout__txt_margbot_10"><a class="b-layout__link b-layout__link_bordbot_dot_000" href="#btn1">в 6 разных форматах</a></div>
                     <div class="b-buttons b-buttons_padtop_40">
                        <a class="b-button b-button_flat b-button_flat_green b-button_flat_big" href="mailto:adv@fl.ru?subject=Заказ%20рекламы&body=Здравствуйте!%20Интересует%20размещение%20рекламы%20на%20сайте%20FL.ru.%20Хотелось%20бы%20рассмотреть%20и%20выбрать%20варианты.">ЗАКАЗАТЬ РЕКЛАМУ</a>
                        <span class="b-layout__txt b-layout__txt_fontsize_16">&#160;&#160;или изучить наши возможности <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="#btn1">подробнее</a>!</span>
                     </div>
                  </td>
               </tr>
            </table>
         
        <script>
            window.addEvent('domready', function() {

                var slider = $('slider');
                var uslider = new uSlider(slider, {
                    directionnav: false,
                    effect: 'slide'
                });

                /* Event handlers */
                $$('.b-promo__slider-arl').addEvent('click', function() {
                    uslider.back();
                });

                $$('.b-promo__slider-arr').addEvent('click', function() {
                    uslider.next();
                });

            });

        </script>
         
         
                     
            <div class="b-fon b-fon_bg_f5 b-fon_pad_20 b-promo__slider">
               <div id="slider">
                  <div class="b-promo__slider-inner uSlider-slides">
                     <div class="b-promo__slider-item">
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_color_6db335">А знали ли вы, что</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_padtop_5">FL.ru &mdash; крупнейшая русскоязычная биржа удаленной работы<br>c 1 150 000 зарегистрированных специалистов.</div>
                     </div>
                     <div class="b-promo__slider-item">
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_color_6db335">А знали ли вы, что</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_padtop_5">На FL.ru ежемесячно публикуется свыше 30 000 проектов,<br>вакансий и конкурсов по различным специализациям.</div>
                     </div>
                     <div class="b-promo__slider-item">
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_color_6db335">А знали ли вы, что</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_padtop_5">Аудитория FL.ru &mdash; это активные пользователи Интернета от 20 до 50 лет из 27 стран мира<br>(Россия, Украина, Беларусь, Казахстан и т.д.)</div>
                     </div>
                  </div>
               </div>
               <div class="b-promo__slider-arl"></div>
               <div class="b-promo__slider-arr"></div>
            </div>
            
            <div class="b-anchor"><a class="b-anchor__link" id="btn1"></a></div>                  
            <div class="b-layout__txt b-layout__txt_color_6db335 b-layout__txt_fontsize_34 b-layout__txt_center b-layout__txt_padbot_30">ВЫБЕРИТЕ МАКСИМАЛЬНО УДОБНЫЙ ВАМ ФОРМАТ:</div>
            
            <table class="b-layout__table">
               <tr class="b-layout__tr">
                  <td class="b-layout__td b-layout__td_width_240 i-menu__promo">
                     <div id="promo_wrap" class="b-layout b-layout_relative">
                        <div id="promo_btns" class="b-menu b-menu_promo">
                           <ul class="b-menu__list">
                              <li class="b-menu__item b-menu__item_active"><a class="b-menu__link" href="#btn1">Промо-кнопки</a></li>
                              <li class="b-menu__item"><a class="b-menu__link" href="#btn2">Баннер 240 х  400</a></li>
                              <li class="b-menu__item"><a class="b-menu__link" href="#btn3">Баннер в email-рассылке</a></li>
                              <li class="b-menu__item"><a class="b-menu__link" href="#btn4">Пост в сообществе</a></li>
                              <li class="b-menu__item"><a class="b-menu__link" href="#btn5">Рекламный проект</a></li>
                              <li class="b-menu__item"><a class="b-menu__link" href="#btn6">Пост в социальных сетях</a></li>
                           </ul>
                           <div class="b-menu__ask">Есть вопросы или<br>появились идеи?<br>Пишите на<br><a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="mailto:adv@fl.ru">adv@fl.ru</a></div>
                        </div>
                     </div>
                  </td>
                  <td class="b-layout__td b-layout__td_padleft_15">
                  
                     <div class="b-layout b-layuot_width_720 b-layout_padbot_30">
                        <img class="b-pic" src="<?=WDCPREFIX?>/images/reclama2/1.png" width="714" height="251">
                        <div class="b-buttons b-buttons_center b-buttons_padbot_30">
                           <a class="b-button b-button_flat b-button_flat_green b-button_flat_big" href="mailto:adv@fl.ru?subject=Заказ%20промо-кнопки&body=Здравствуйте!%20Интересует%20размещение%20рекламы%20на%20сайте%20FL.ru.%20Хотелось%20бы%20разместить%20промо-кнопку.">ЗАКАЗАТЬ КНОПКУ</a>
                           <span class="b-layout__txt b-layout__txt_fontsize_16">&#160;&#160;или посмотреть <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="#btn2">на следующий формат</a>!</span>
                        </div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_padbot_20 b-layout__txt_center">Небольшой текстовый и/или графический баннер, размещенный сверху на самых посещаемых страницах сайта (в каталоге фрилансеров и услуг, в ленте проектов и списке сообществ).</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_bold">Таргетинг:</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_padbot_20 b-layout__txt_italic">тип пользователя (фрилансер, работодатель), наличие аккаунта PRO.</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_bold">Показатели:</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_padbot_20 b-layout__txt_italic">более 2 млн показов в неделю &mdash; от 2000 переходов;<br>коэффициент переходов (CTR) ~ 0.1%.</div>
                     </div>
                     
                     <div class="b-anchor"><a class="b-anchor__link" id="btn2"></a></div>                  
                     <div class="b-layout b-layuot_width_720 b-layout_padbot_30">
                        <img class="b-pic" src="<?=WDCPREFIX?>/images/reclama2/2.png" width="714" height="252">
                        <div class="b-buttons b-buttons_center b-buttons_padbot_30">
                           <a class="b-button b-button_flat b-button_flat_green b-button_flat_big" href="mailto:adv@fl.ru?subject=Заказ%20баннера&body=Здравствуйте!%20Интересует%20размещение%20рекламы%20на%20сайте%20FL.ru.%20Хотелось%20бы%20разместить%20баннер.">ЗАКАЗАТЬ БАННЕР</a>
                           <span class="b-layout__txt b-layout__txt_fontsize_16">&#160;&#160;или посмотреть <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="#btn3">на следующий формат</a>!</span>
                        </div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_padbot_20 b-layout__txt_center">Статичный или анимированный баннер, размещенный справа или слева на всех основных страницах сайта FL.ru (каталог, профиль, сообщества, проекты, конкурсы и вакансии).</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_bold">Таргетинг:</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_padbot_20 b-layout__txt_italic">страна, регион, тип пользователя (фрилансер, работодатель).</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_bold">Показатели:</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_padbot_20 b-layout__txt_italic">10 млн показов и 800 тысяч уникальных пользователей в месяц.<br>Коэффициент переходов (CTR) ~ 0.5%.</div>
                     </div>
                     
                     <div class="b-anchor"><a class="b-anchor__link" id="btn3"></a></div>                  
                     <div class="b-layout b-layuot_width_720 b-layout_padbot_30">
                        <img class="b-pic" src="<?=WDCPREFIX?>/images/reclama2/3.png" width="714" height="252">
                        <div class="b-buttons b-buttons_center b-buttons_padbot_30">
                           <a class="b-button b-button_flat b-button_flat_green b-button_flat_big" href="mailto:adv@fl.ru?subject=Заказ%20баннера%20в%20рассылке&body=Здравствуйте!%20Интересует%20размещение%20рекламы%20на%20сайте%20FL.ru.%20Хотелось%20бы%20разместить%20баннер%20в%20рассылке.">ЗАКАЗАТЬ БАННЕР</a>
                           <span class="b-layout__txt b-layout__txt_fontsize_16">&#160;&#160;или посмотреть <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="#btn4">на следующий формат</a>!</span>
                        </div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_padbot_20 b-layout__txt_center">Статичный или анимированный баннер,<br>размещенный сверху в ежедневной рассылке проектов по фрилансерам.</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_bold">Таргетинг:</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_padbot_20 b-layout__txt_italic">Все фрилансеры с активной подпиской на рассылку проектов.</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_bold">Показатели:</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_padbot_20 b-layout__txt_italic">350 тысяч получателей рассылки, от 700 переходов по баннеру.<br>Коэффициент переходов (CTR) ~ 0.2%.</div>
                     </div>
                     
                     <div class="b-anchor"><a class="b-anchor__link" id="btn4"></a></div>                  
                     <div class="b-layout b-layuot_width_720 b-layout_padbot_30">
                        <img class="b-pic" src="<?=WDCPREFIX?>/images/reclama2/4.png" width="714" height="252">
                        <div class="b-buttons b-buttons_center b-buttons_padbot_30">
                           <a class="b-button b-button_flat b-button_flat_green b-button_flat_big" href="mailto:adv@fl.ru?subject=Заказ%20поста%20в%20сообществе&body=Здравствуйте!%20Интересует%20размещение%20рекламы%20на%20сайте%20FL.ru.%20Хотелось%20бы%20разместить%20пост%20в%20сообществе.">ЗАКАЗАТЬ ПОСТ</a>
                           <span class="b-layout__txt b-layout__txt_fontsize_16">&#160;&#160;или посмотреть <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="#btn5">на следующий формат</a>!</span>
                        </div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_padbot_20 b-layout__txt_center">Рекламное сообщение с графикой и ссылками, размещенное и закрепленное сверху в <a class="b-layout__link" href="/commune/drugoe/5100/ofitsialnoe-soobschestvo-flru/">корпоративном сообществе FL.ru</a> (с перепостом в официальные группы FL в социальных сетях).</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_bold">Показатели:</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_padbot_20 b-layout__txt_italic">4300 подписчиков в сообществе и 50 тысяч подписчиков в группах,<br>от 800 переходов по ссылкам в новых постах.<br>Коэффициент переходов (CTR) ~ 1.5%.</div>
                     </div>
                     
                     <div class="b-anchor"><a class="b-anchor__link" id="btn5"></a></div>                  
                     <div class="b-layout b-layuot_width_720 b-layout_padbot_30">
                        <img class="b-pic" src="<?=WDCPREFIX?>/images/reclama2/5.png" width="714" height="251">
                        <div class="b-buttons b-buttons_center b-buttons_padbot_30">
                           <a class="b-button b-button_flat b-button_flat_green b-button_flat_big" href="mailto:adv@fl.ru?subject=Заказ%20рекламного%20проекта&body=Здравствуйте!%20Интересует%20размещение%20рекламы%20на%20сайте%20FL.ru.%20Хотелось%20бы%20разместить%20рекламный%20проект.">ЗАКАЗАТЬ ПРОЕКТ</a>
                           <span class="b-layout__txt b-layout__txt_fontsize_16">&#160;&#160;или посмотреть <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="#btn6">на следующий формат</a>!</span>
                        </div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_padbot_20 b-layout__txt_center">Единичный проект, вакансия или конкурс (с графикой, ссылками и видео),<br>опубликованный и закрепленный сверху на неделю в ленте проектов.</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_bold">Показатели:</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_padbot_20 b-layout__txt_italic">60 тысяч фрилансеров, ежедневно просматривающих проекты,<br>от 1500 переходов по ссылкам в проекте, от 50 откликов по заданию.<br>Коэффициент переходов (CTR) ~ 2.5%.</div>
                     </div>
                     
                     <div class="b-anchor"><a class="b-anchor__link" id="btn6"></a></div>                  
                     <div class="b-layout b-layuot_width_720 b-layout_padbot_30">
                        <img class="b-pic" src="<?=WDCPREFIX?>/images/reclama2/6.png" width="714" height="252">
                        <div class="b-buttons b-buttons_center b-buttons_padbot_30">
                           <a class="b-button b-button_flat b-button_flat_green b-button_flat_big" href="mailto:adv@fl.ru?subject=Заказ%20поста%20в%20социальных%20сетях&body=Здравствуйте!%20Интересует%20размещение%20рекламы%20на%20сайте%20FL.ru.%20Хотелось%20бы%20разместить%20пост%20в%20социальных%20сетях.">ЗАКАЗАТЬ ПОСТ</a>
                           <span class="b-layout__txt b-layout__txt_fontsize_16">&#160;&#160;или <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="mailto:adv@fl.ru">предложить свои идеи</a>!</span>
                        </div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_padbot_20 b-layout__txt_center">Рекламное сообщение с графикой, видео и ссылками,<br>размещенное во всех официальных группах FL.ru в социальных сетях (<a target="_blank" href="http://vk.com/free_lanceru" class="b-layout__link b-layout__link_no-decorat">Вконтакте</a>, <a target="_blank" href="http://www.facebook.com/freelanceru" class="b-layout__link b-layout__link_no-decorat">Facebook</a>, <a target="_blank" href="https://twitter.com/free_lanceru" class="b-layout__link b-layout__link_no-decorat">Twitter</a>, <a target="_blank" href="https://plus.google.com/+Free-lanceru/" class="b-layout__link b-layout__link_no-decorat">G+</a>, <a target="_blank" href="http://www.odnoklassniki.ru/freelanceru" class="b-layout__link b-layout__link_no-decorat">Odnoklassniki</a>, <a target="_blank" href="https://www.linkedin.com/company/free-lance-ru" class="b-layout__link b-layout__link_no-decorat">LinkedIn</a>).</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_bold">Показатели:</div>
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_padbot_20 b-layout__txt_italic">Суммарно 50 тысяч подписчиков, 3000 посетителей в день, от 500 переходов по ссылкам в новых постах. Коэффициент переходов (CTR) ~ 1%.</div>
                     </div>
                  </td>
               </tr>
            </table>
            
<script>
var btn1 = document.getElementById('btn1');
var btn2 = document.getElementById('btn2');
var btn3 = document.getElementById('btn3');
var btn4 = document.getElementById('btn4');
var btn5 = document.getElementById('btn5');
var btn6 = document.getElementById('btn6');
btn1 = btn1.getCoordinates().top;
btn2 = btn2.getCoordinates().top;
btn3 = btn3.getCoordinates().top;
btn4 = btn4.getCoordinates().top;
btn5 = btn5.getCoordinates().top;
btn6 = btn6.getCoordinates().top;

var pomo_btns = document.getElementById('promo_btns');

var scrol;

scrolFunk();
window.onscroll = scrolFunk;
function scrolFunk(){
	scrol = window.pageYOffset || document.documentElement.scrollTop;
	fix_promo();
	scrolMenu();
}
function fix_promo(){
	var promo_wrap = document.getElementById('promo_wrap');
	var foot = document.getElementById('i-footer');
	
	var coord, promo_btnsH, td_height;
	
	
	promo_btnsH = promo_btns.offsetHeight;
	td_height = promo_wrap.getParent('td').offsetHeight;
	coord = promo_wrap.getCoordinates().top - 80;
	
	
   if(scrol>coord){
		if(td_height + coord - promo_btnsH < scrol){
			promo_btns.setStyles({
				'position':'absolute', 
				'top': td_height  - promo_btnsH
				})
			}
	   else	promo_btns.setStyles({
			'position':'fixed',
			'top': ''
			})
		} else {
		promo_btns.setStyles({
			'position':'',
			'top': ''
			})
		}
	
}

function scrolMenu(){   
   if(scrol<btn2){
		pomo_btns.getElements('.b-menu__item').removeClass('b-menu__item_active');
		pomo_btns.getElements('[href=#btn1]').getParent('.b-menu__item').addClass('b-menu__item_active');
		}
	else if((btn2<=scrol)&&(scrol<btn3)){
		pomo_btns.getElements('.b-menu__item').removeClass('b-menu__item_active');
		pomo_btns.getElements('[href=#btn2]').getParent('.b-menu__item').addClass('b-menu__item_active');
		}
	else if((btn3<=scrol)&&(scrol<btn4)){
		pomo_btns.getElements('.b-menu__item').removeClass('b-menu__item_active');
		pomo_btns.getElements('[href=#btn3]').getParent('.b-menu__item').addClass('b-menu__item_active');
		}
	else if((btn4<=scrol)&&(scrol<btn5)){
		pomo_btns.getElements('.b-menu__item').removeClass('b-menu__item_active');
		pomo_btns.getElements('[href=#btn4]').getParent('.b-menu__item').addClass('b-menu__item_active');
		}
	else if((btn5<=scrol)&&(scrol<btn6)){
		pomo_btns.getElements('.b-menu__item').removeClass('b-menu__item_active');
		pomo_btns.getElements('[href=#btn5]').getParent('.b-menu__item').addClass('b-menu__item_active');
		}
	else{
		pomo_btns.getElements('.b-menu__item').removeClass('b-menu__item_active');
		pomo_btns.getElements('[href=#btn6]').getParent('.b-menu__item').addClass('b-menu__item_active');
		}
}

window.addEvent('domready',function() {
	pomo_btns.getElements('.b-menu__link').addEvent('click',function(){
        scrolFunk();
    });
})
</script>