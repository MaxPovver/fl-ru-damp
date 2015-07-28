<div class="b-layout__page b-promo">
    <h1 class="b-page__title b-page__title_center b-page__title_ptsans" style="margin:0 !important;">Новый сервис Типовые услуги</h1>
    <div class="b-txt b-txt_center b-txt_padbot_10">Друзья, в следующем месяце мы запускаем Типовые услуги — новый сервис на сайте, позволяющий фрилансерам эффективно предлагать свои услуги, а работодателям — легко и быстро находить исполнителей.</div>
    <div class="b-txt b-txt_center b-txt_padbot_10">По традиции хотим начать с презентации дизайна и будущего функционала сервиса. Надеемся, что вам все очень понравится. Будем рады любым замечаниям и идеям!</div>
    <div class="b-txt b-txt_center b-txt_padbot_30">PS: скриншоты кликабельны и доступны в большом разрешении.</div>
    <div class="b-promo__wave b-promo__wave_padbot_30"></div>
    <h2 class="b-txt__title b-txt__title_center">Карточки типовых услуг</h2>
    <div class="b-txt b-txt_center">Всё, что необходимо знать об услуге и исполнителе — на одной странице, в удобной форме!</div>
    <div class="b-txt b-txt_center"><a id="d1" class="b-txt__lnk promo-servise promo-servise_1" target="_blank" href="/images/promo-service/11.png"></a></div>
    <div class="b-promo__wave b-promo__wave_padbot_30"></div>
    <h2 class="b-txt__title b-txt__title_center">Каталоги и списки типовых услуг</h2>
    <div class="b-txt b-txt_center b-txt_padbot_10">Выбирайте реальные услуги, а не портфолио и «красивые обещания»!</div>
    <div class="b-txt b-txt_center b-txt_bold">
        <a id="a2" class="b-txt__lnk b-txt__lnk_color_000 b-txt__lnk_lh_1" href="#">Новая главная</a>&nbsp;&nbsp;&nbsp;      
        <a id="a3" class="b-txt__lnk b-txt__lnk_dot_0f71c8 b-txt__lnk_lh_1" href="#">Каталог типовых услуг</a>&nbsp;&nbsp;&nbsp;       
        <a id="a4" class="b-txt__lnk b-txt__lnk_dot_0f71c8 b-txt__lnk_lh_1" href="#">Поиск и списки</a>&nbsp;&nbsp;&nbsp;       
        <a id="a5" class="b-txt__lnk b-txt__lnk_dot_0f71c8 b-txt__lnk_lh_1" href="#">Профиль фрилансера</a>       
    </div>
    <div class="b-txt b-txt_center b-txt_padbot_10">
        <div id="d2" class="b-txt b-txt_center"><a class="b-txt__lnk promo-servise promo-servise_2" target="_blank" href="/images/promo-service/22.jpg"></a></div>
        <div id="d3" class="b-txt b-txt_center b-txt_hide"><a class="b-txt__lnk promo-servise promo-servise_3" target="_blank" href="/images/promo-service/33.png"></a></div>
        <div id="d4" class="b-txt b-txt_center b-txt_hide"><a class="b-txt__lnk promo-servise promo-servise_4" target="_blank" href="/images/promo-service/44.png"></a></div>
        <div id="d5" class="b-txt b-txt_center b-txt_hide"><a class="b-txt__lnk promo-servise promo-servise_5" target="_blank" href="/images/promo-service/55.png"></a></div>
    </div>
    
    <div class="b-txt b-txt_center b-txt_padbot_10">Разработка сервиса будет поэтапной. И на первом этапе мы добавим в профили раздел «Типовые услуги» (он будет доступен вместе с портфолио). У фрилансеров появится возможность бесплатно создавать, редактировать и удалять карточки своих типовых услуг. У работодателей — выбирать нужные услуги и в один клик заказывать их, резервируя сумму покупки.</div>
    <div class="b-txt b-txt_center b-txt_padbot_30">На следующем этапе добавим каталоги типовых услуг (по аналогии с каталогом фрилансеров), расширим поиск, а на главной странице — вместо списка проектов для работодателей мы покажем перечень рекомендуемых услуг.</div>
    
    <div class="b-txt b-txt_center"><a href="https://www.fl.ru/commune/drugoe/5100/free-lanceru/8463245/anons-tipovyih-uslug.html?utm_source=mailing&utm_medium=email&utm_campaign=tp_anons1" class="b-button b-button_flat b-button_flat_green">Обсудить раздел «Типовые услуги» в сообществе</a></div>
</div>

<style>
div[id^=d] .b-txt__lnk, #d1{ outline:none}
.promo-servise{ background-image: url(../images/promo-servise.png); background-repeat:no-repeat; width:920px; display:inline-block}
.promo-servise_1{ background-position:10px -50px; height:710px;}
.promo-servise_2{ background-position:-42px -760px; height:350px;}
.promo-servise_3{ background-position:15px -1110px; height:620px;}
.promo-servise_4{ background-position:15px -1760px; height:700px;}
.promo-servise_5{ background-position:15px -2470px; height:680px;}
</style>

<script>
window.addEvent('domready', 
function() {
                    $$('a[id^=a]').addEvent('click', function(){
         if(this.hasClass('b-txt__lnk_dot_0f71c8')){
                                this.getParent('.b-txt').getElements('.b-txt__lnk').removeClass('b-txt__lnk_color_000 ').addClass('b-txt__lnk_dot_0f71c8');
                                this.addClass('b-txt__lnk_color_000 ').removeClass('b-txt__lnk_dot_0f71c8');
                                this.getParent('.b-txt').getNext('.b-txt').fade('out');
                                var pic=$$('#d'+this.getProperty('id').charAt(1))
                                setTimeout(function() { 
                                            $$('div[id^=d]').addClass('b-txt_hide'); 
                                            pic.removeClass('b-txt_hide'); 
                                            $$('a[id^=a]').getParent('.b-txt').getNext('.b-txt').fade('in');
                                    }, 1000);
                }
                            return false;
                })
})
</script>

