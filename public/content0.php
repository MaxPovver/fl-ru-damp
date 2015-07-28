<h1 class="b-page__title">Публикация проекта</h1>
											<table class="b-layout__table" cellpadding="0" cellspacing="0" border="0">
												<tr class="b-layout__tr">
													<td class="b-layout__left b-layout__left_padright_20">
														<a class="btn btn-blue2 " href="/public/?step=1&kind=1&red=" style="display:block"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Опубликовать проект</span></span></span></a>    
													</td>
													<td class="b-layout__right b-layout__right_width_72ps">
														<div class="b-layout__txt b-layout__txt_padbot_40">Самый быстрый способ найти исполнителя &mdash; опубликовать проект. Ваше объявление моментально появляется на главной странице сайта, где его видят тысячи фрилансеров. Существует два типа проектов &mdash; платные и бесплатные.</div>
													</td>
												</tr>
												<tr class="b-layout__tr">
													<td class="b-layout__left b-layout__left_padright_20">
														<a class="btn btn-blue2 " href="/public/?step=1&kind=7&red=" style="display:block"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Опубликовать конкурс <?= (new_projects::getKonkursPrice())?> рублей</span></span></span></a>
													</td>
													<td class="b-layout__right b-layout__right_width_72ps">
            									<div class="b-layout__txt b-layout__txt_padbot_20">Работодатели! Если вы хотите выбирать из десятков готовых вариантов решения вашей задачи &mdash; опубликуйте конкурс.</div>
													</td>
												</tr>
												<tr class="b-layout__tr">
													<td class="b-layout__left b-layout__left_padright_20">
														<div class="b-promo">
                                                            <?php if(!is_pro()) { ?>
															<div class="b-promo__note">
																	<div class="b-promo__note-inner">
																			<h3 class="b-promo__h3">С <span class="b-promo__pro b-promo__pro_emp"></span>&#160;дешевле</h3>
																			<p class="b-promo__p b-promo__p_fontsize_13"><a href="/payed-emp/" class="b-promo__link">Купите профессиональный аккаунт</a></p>
																			<p class="b-promo__p b-promo__p_fontsize_13">и публикация конкурса будет стоить всего <span class="b-promo__txt b-promo__txt_bold b-promo__txt_color_fd6c30">3000 рублей</span>.</p>
																	</div>
															</div>
                                                            <?php }//if?>
														</div>				
													</td>
													<td class="b-layout__right b-layout__right_width_72ps">
                        			<div class="b-layout__txt b-layout__txt_padbot_20">Публикация конкурса стоит <?= (new_projects::getKonkursPrice())?> рублей. После публикации ваш проект появится на вкладке &laquo;Конкурсы&raquo;, а фрилансеры предложат варианты решения вашего задания. Вам останется лишь выбрать победителя, оплатить его работу и закрыть конкурс.</div>
                    					<div class="b-layout__txt b-layout__txt_padbot_40">Рекламодатели! Если вы хотите рассказать о новой услуге или продукте и получить от фрилансеров нестандартные идеи для развития вашего брэнда &mdash; проведите конкурс. Стоимость и условия проведения Вы можете узнать <a class="b-promo__link" target="_blank" href="/press/adv/">здесь</a>.</div>
													</td>
												</tr>
												<tr class="b-layout__tr">
													<td class="b-layout__left b-layout__left_padright_20">
														<a class="btn btn-green2" href="/masssending/" style="display:block"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Рассылка по каталогу</span></span></span></a>
													
													</td>
													<td class="b-layout__right b-layout__right_width_72ps">
                    					<div class="b-layout__txt">Вы можете обратиться напрямую ко всем фрилансерам из каталога, и они вас услышат.</div>
                    					<div class="b-layout__txt">Фрилансеры получат ваше сообщение в виде личного письма.</div>
													</td>
												</tr>
											</table>



