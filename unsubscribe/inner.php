<form name="unsubscribedform" id="unsubscribedform" method="POST">
<div class="b-layout">
		<div class="b-layout__right b-layout__right_width_73ps b-layout__right_float_right">
				<h1 class="b-page__title b-page__title_padbot_30">
                                    <?php if($type == 'new_projects'){ ?>
                                    Отписка от рассылки проектов
                                    <?php } elseif($type == 'mailer'){ ?>
                                    Отписка от рассылки новостей
                                    <?php } else { ?>
                                    Отписка от рассылок
                                    <?php } ?>
                                </h1>
		</div>		
		<div class="b-layout__right b-layout__right_width_73ps b-layout__right_float_right">
			<div class="b-fon b-fon_inline-block b-fon_padbot_100">
					<div class="b-fon__body b-fon__body_pad_15  b-fon__body_padleft_30 b-fon__body_padright_40 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf b-fon__body_lineheight_18">
                    <?php
                    $it = 'eё';
                    switch ($type) {
                        case 'new_projects':
                            $letter_name = 'рассылки проектов с FL.ru';
                            break;
                    
                        case 'mailer':
                            $letter_name = 'рассылки новостей с FL.ru';
                            break;

                        default:
                            $letter_name = 'всех рассылок FL.ru';
                            $it = 'их';
                            break;
                    }
                    ?>
                    Чтобы отписать <span class="b-layout__bold"><?=$email ?></span> от <?=$letter_name?><br>
                    и больше не получать <?=$it?>, введите символы с картинки:
					<div class="b-captcha b-captcha_padtop_15 b-captcha_padleft_90 b-form">
						<img alt="Введите символы на изображении" id="rndnumimage" src="/image.php?num=<?=$captchanum?>&r=<?=rand(1000, 9999) ?>" class="b-captcha__img b-captcha__img_bord_ebe8e8">
						<div class="b-captcha__txt b-captcha__txt_inline-block b-captcha__txt_padtop_10" style="padding-top:22px">-&nbsp;</div>						
						<div class="b-combo b-combo_inline-block" style="padding-top:10px">
							<div class="b-combo__input <?php if($alert) {?>b-combo__input_error<? } ?> b-combo__input_width_90 b-combo__input_height_41 ">
								<input type="text" size="80" name="rndnum" id="rndnum" class="b-combo__input-text b-combo__input-text_center">
								<input type="hidden"  name="captchanum" id="captchanum" value="<?=$captchanum ?>">
								<input type="hidden"  name="action" value="unsubscribe">
							</div>
						</div>
                        <?php if ($alert) {?><script type="text/javascript">$("rndnum").focus();</script><?php } ?>
						<div class="b-captcha__txt b-captcha__txt_padbot_20 b-captcha__txt_padtop_5"><a href="#" class="b-captcha__link" onclick="$('rndnumimage').set('src','/image.php?num='+$('captchanum').get('value')+'&r='+Math.random()); return false;">Обновить картинку</a></div>
						<?php if ($alert) {?>
						    <div class="b-captcha__txt b-captcha__txt_color_c4271f"><span class="b-form__error"></span><?=$alert ?></div>
						<?}?>
						<div class="b-buttons b-buttons_padtop_20 b-buttons_padleft_6 b-buttons_padbot_10">
						
							<a class="b-button b-button_flat b-button_flat_green" href="#" onclick="$('unsubscribedform').submit(); return false;">
                                                                                                            <?php if($type){ ?>
                                                                                                            Отписаться от рассылки
                                                                                                            <?php } else { ?>
                                                                                                            Отписаться от рассылок
                                                                                                            <?php } ?>
							</a>						
						</div>
					</div>
				</div>
			</div>
		</div>
</div>
</form>