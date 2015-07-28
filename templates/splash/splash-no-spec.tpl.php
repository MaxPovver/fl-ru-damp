<div class="i-shadow i-shadow_zindex_110">
<div class="b-shadow b-shadow_inline-block b-shadow_width_710 b-shadow_gorizont_center b-shadow_top_140" >
	<div class="b-shadow__right">
		<div class="b-shadow__left">
			<div class="b-shadow__top">
				<div class="b-shadow__bottom">
					<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20 b-layout">
						
      
      <table class="b-layout__table b-layout__table_margbot_40 b-layout__table_width_full">
         <tr class="b-layout__tr">
            <td class="b-layout__one">
                    <h2 class="b-shadow__title b-shadow__title_padbot_30 b-shadow__title_fontsize_22">Вас нет в каталоге фрилансеров</h2>
						
                    <div class="b-layout__txt b-layout__txt_padbot_30 b-layout__txt_fontsize_15"><a class="b-layout__link" href="/users/<?= $_SESSION['login'] ?>/setup/specsetup/">Выберите специализацию</a>, чтобы ссылка на ваш профиль<br />
                       появилась в каталоге фрилансеров.<br />
                       Полностью заполненный профиль (специализация, имя, <br />
                       возраст, город, страна и хотя бы одна работа в портфолио) <br />
                       добавят 100 баллов к вашему рейтингу.
                    </div>
                       <div class="b-layout__txt b-layout__txt_padbot_30 b-layout__txt_fontsize_11">
                       Пожалуйста, выберите и укажите одну специализацию.<br />
                       Владельцы аккаунта PRO могут выбирать до 5 специализаций. 
                       </div>
                       <div class="b-layout__txt b-layout__txt_relative b-layout__txt_italic b-layout__txt_fontsize_15 b-layout__txt_color_6db335 b-layout__txt_padleft_150">
                       Укажите специализацию &mdash;<br /> попадите в каталог <span class="b-promo__arrow" style="top:-30px; right:20px;"></span>
                       </div>
            </td>
            <td class="b-layout__one b-layout__one_width_170"><img class="b-layout__pic" src="/images/cat.png" alt="" width="155" height="289" /></td>
         </tr>
      </table>
			


        
        
						<div class="b-buttons b-buttons_padtb_30 b-buttons_bg_6fb400 b-buttons_center b-buttons_marglr_-20 b-buttons_margbot_-20">
                          <a class="b-button b-button_round_green" href="/users/<?= $_SESSION['login'] ?>/setup/specsetup/">
                              <span class="b-button__b1">
                                  <span class="b-button__b2"><span class="b-button__txt">Выбрать специализацию</span></span>
                              </span>
                          </a>
                        </div>        					
                        
					</div>
				</div>
			</div>
		</div>
	</div>
	<span class="b-shadow__icon b-shadow__icon_close" onclick="$('no-spec-overlay').dispose()"></span>
</div>
</div>
<div class="b-shadow__overlay b-shadow__overlay_bg_black" id="no-spec-overlay"></div>