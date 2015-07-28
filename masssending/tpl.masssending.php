<p>Вы можете обратиться сразу ко всем фрилансерам нашего сайта, и они вас услышат.<br />Фрилансеры получат ваше сообщение в виде личного письма. Более подробно о сервисе вы можете узнать в <noindex><a rel="nofollow" href="https://feedback.fl.ru/">разделе помощи</a></noindex>.</p>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_11"><span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold">Обратите внимание!</span> Стоимость рассылки зависит от общего количества фрилансеров, которым будет отправлено сообщение. Это количество может отличаться от числа, полученного путем простого сложения представителей выбранных вами разделов, так как один и тот же фрилансер может иметь несколько специализаций на сайте.</div>
<div class="masss-mess-b">
    <h4>Сообщение</h4>
    <div class="form masss-mess">
        <b class="b1"></b>
        <b class="b2"></b>
        <div class="form-in">
            <div class="form-block first">
                <div class="form-el">
                    <textarea id="msg" name="msg" rows="6" cols="100" onfocus="if (this.className != '') { this.className = ''; this.value = '' }" onchange="spam.set(this.name, this.value)"><?=($params['msg']? htmlspecialchars($params['msg']): '')?></textarea>
                </div>
                                    <div>
                            <strong>Важно!</strong> Рассылка предназначена только для реальных целевых проектов и поиска исполнителей. Реклама к рассылке не допускается.
                        </div>
            </div>
            <div class="form-block last">
                <div class="form-el">
                    <div class="masss-files<?=(empty($params['files'])? ' flt-hide': ' flt-show')?>" id="masss-files" page="0">
                        <div class="masss-files-clip">
                            <a href="javascript: void(0);" class="flt-tgl-lnk lnk-dot-blue">Прикрепленные файлы (развернуть)</a>
                        </div>
                        <div id="flt-masss-files" class="flt-cnt-masssend с" >
                            <div class="cl-form-files c"> 
                                <ul id="mf-files-list" class="form-files-added"></ul> 
                                <ul class="form-files-list">
                                    <li id="c">
                                        <input id="mf-file" name="attach" type="file" />
                                        &nbsp;&nbsp;<img id="mf-load" src="/images/loader-gray.gif" style="display: none"  alt=""/>
                                    </li>
                                </ul>
                                <div class="masss-files-inf">
                                    <p><strong>Вы можете прикрепить до <?=masssending::MAX_FILES?> файлов общим объемом не более <?=ConvertBtoMB(masssending::MAX_FILE_SIZE)?>.</strong><br />
                                    Файлы следующих форматов запрещены к загрузке: <?=implode(', ', $GLOBALS['disallowed_array'])?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <b class="b2"></b>
        <b class="b1"></b>
    </div>
</div>


<!-- Фильтр -->
<div <?=(empty($params)? ' class="flt-hide"': ' class="flt-show"')?>>
<div class="flt-out flt-masss" id="flt-masss" page="3">
    <b class="b1"></b>
    <b class="b2"></b>
    <div class="flt-bar">
            <a href="javascript: void(0);" class="flt-tgl-lnk"><?=(empty($params)? 'Развернуть': 'Свернуть')?></a>
            <h4>Дополнительные параметры</h4>
    </div>
    <div id="flt-cnt" class="flt-cnt-masssend"><input type="hidden" value="<?php print $_SESSION["rand"]?>" name="u_token_key"/>
        <div class="flt-block flt-b-fc">
            <label class="flt-lbl">&nbsp;</label>
            <div class="flt-b-in">
            <ul class="flt-more c">
                <li><label><input class="i-chk" type="checkbox" name="opi_is_positive" id="opi_is_positive" value="1" onclick="spam.set(this.name, this.checked)" onkeyup="spam.set(this.name, this.checked)" <?=(empty($params['opi_is_positive'])? '': 'checked="checked" ')?>/> С положительными отзывами</label></li>
                <li><label><input class="i-chk" type="checkbox" name="opi_not_negative" id="opi_not_negative" value="1" onclick="spam.set(this.name, this.checked)" onkeyup="spam.set(this.name, this.checked)" <?=(empty($params['opi_not_negative'])? '': 'checked="checked" ')?>/> Без отрицательных отзывов</label></li>
                <li><label><input class="i-chk" type="checkbox" name="opi_is_verify" id="opi_is_verify" value="1" onclick="spam.set(this.name, this.checked)" onkeyup="spam.set(this.name, this.checked)" <?=(empty($params['opi_is_verify'])? '': 'checked="checked" ')?>/> С <span class="b-icon b-icon__ver" alt="верифицированым" title="верифицированым"></span> аккаунтом</label></li>

                <?/*<li><label><input class="i-chk" type="checkbox" name="positive" id="positive" value="1" onclick="spam.set(this.name, this.checked)" onkeyup="spam.set(this.name, this.checked)" <?=(empty($params['positive'])? '': 'checked="checked" ')?>/> С положительными отзывами</label></li>*/?>
                <li><label><input class="i-chk" type="checkbox" name="free" id="free" value="1" onclick="spam.set(this.name, this.checked)" onkeyup="spam.set(this.name, this.checked)" <?=(empty($params['free'])? '': 'checked="checked" ')?>/> Только свободные</label></li>
                <?/* <li><label><input class="i-chk" type="checkbox" name="negative" id="negative" value="1" onclick="spam.set(this.name, this.checked)" onkeyup="spam.set(this.name, this.checked)" <?=(empty($params['negative'])? '': 'checked="checked" ')?>/> Без отрицательных отзывов</label></li> */?>
                <li class="flt-more-b"><label><input class="i-chk" type="checkbox" id="portfolio" name="portfolio" value="1" onclick="spam.set(this.name, this.checked)" onkeyup="spam.set(this.name, this.checked)" <?=(empty($params['portfolio'])? '': 'checked="checked" ')?>/> Только с примерами работ</label></li>
                <li><label><input class="i-chk" type="checkbox" name="favorites" id="favorites" value="1" onclick="spam.set(this.name, this.checked)" onkeyup="spam.set(this.name, this.checked)" <?=(empty($params['favorites'])? '': 'checked="checked" ')?>/> У меня в избранных</label></li>
            </ul>
            </div>
        </div>

        <div class="flt-block">
            <label class="flt-lbl">Месторасположение:</label>
            <div class="flt-b-in">
                <div class="form-p">
                    <div class="flt-b-row">
                        <span class="flt-add"><span class="flt-spec"><span class="flt-s-in"><a id="btnAddLocation" href="." onclick="locations.add(); return false;"><img src="/images/flt-add.png" alt="" width="15" height="15" />Добавить еще</a></span></span></span>
                        <span class="flt-prm"><select name="country" id="countries" onchange="GetCities(this.value); spam.send();">
                            <option value="0">Все страны</option>
                            <? foreach ($countries as $country) { ?>
                                <option value="<?=$country['id']?>"<?=((!empty($params['defs']) && $params['defs']['countries'] == $country['id'])? ' selected="selected"': '')?>><?=htmlspecialchars($country['country_name'])?></option>
                            <? } ?>
                            </select></span><span class="flt-prm">
                            <select name="city" id="cities" onchange="spam.send()">
                            <option value="0">Все города</option>
                            </select></span>
                    </div>
                    <div id="locations-block"></div>
                </div>

                <div class="flt-b-row flt-b-row-mb">
                    <label><input class="i-chk" type="checkbox" id="inoffice" name="inoffice" value="1" onclick="spam.set(this.name, this.checked)" onkeyup="spam.set(this.name, this.checked)" <?=(empty($params['inoffice'])? '': 'checked="checked" ')?> /> Ищет работу в офисе</label>
                </div>

                <script type="text/javascript">
                    GetCities( document.getElementById('countries').value, <?=(empty($params['defs'])? '0': intval($params['defs']['cities']))?> );
                    <? if (!empty($params)) { ?>locations.restore();<? } ?>
                </script>

            </div>
        </div>

        <div class="flt-block" id="flt-block">
            <label class="flt-lbl">Стоимость:</label>
            <div class="flt-b-in">
                <div id="costs-block" style="display: block; height: auto"></div>
            </div>
        </div>

        <div class="flt-block flt-b-lc">
            <label class="flt-lbl">Опыт работы:</label>
            <div class="flt-b-in">
                <span class="flt-prm">
                    <input id="expire_from" maxlength="2" name="expire_from" type="text" size="10" class="flt-prm3" onchange="spam.set(this.name, this.value)" onkeypress="return isNumKeyPressed(event)" value="<?=(empty($params['expire_from'])? '': $params['expire_from'])?>" /> &mdash; 
                    <input id="expire_to" maxlength="2" name="expire_to" type="text" size="10" class="flt-prm3" onchange="spam.set(this.name, this.value)" onkeypress="return isNumKeyPressed(event)" value="<?=(empty($params['expire_to'])? '': $params['expire_to'])?>" />&nbsp; лет
                </span>
            </div>
        </div>

    </div>

    <b class="b2"></b>
    <b class="b1"></b>

</div>
</div>
<!-- Конец фильтра -->

<div class="form masss-cat">

    <b class="b1"></b>
    <b class="b2"></b>

    <div class="form-in">
        <div class="form-block first last">
            <div class="form-el">
                <label class="form-label">Раздел каталога:</label>
                <div class="form-p">
                    <div class="flt-b-row">
                        <span class="flt-add"><span class="flt-spec"><span class="flt-s-in"><a href="." onclick="professions.add(); return false;"><img src="/images/flt-add.png" alt="" width="15" height="15" />Добавить еще</a></span></span></span>
                        <span class="flt-prm"><select id="prof_groups" name="prof_group" onchange="GetProfessions(this.value); spam.send();">
                            <option value="-1">&lt;Выберите раздел&gt;</option>
                            <option value="0"<?=((isset($params['defs']['prof_groups']) && ($params['defs']['prof_groups']==0))? ' selected="selected"': '')?>>Все разделы</option>
                            <? for ($i=0; $i<count($prof_groups); $i++) { ?>
                                <option value="<?=$prof_groups[$i]['id']?>"<?=(empty($params['defs'])? (($dcg==$prof_groups[$i]['id'])? ' selected="selected"': ''): (($params['defs']['prof_groups']==$prof_groups[$i]['id'])? ' selected="selected"': ''))?>><?=htmlspecialchars($prof_groups[$i]['name'])?></option>
                            <? } ?>
                            </select></span><span class="flt-prm">
                            <select id="profs" name="profession" onchange="spam.send()">
                                <option>Все подразделы</option>
                            </select></span>
                    </div>
                    <div id="professions-block"></div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            GetProfessions( document.getElementById('prof_groups').value, <?=(empty($params['defs'])? intval($dcp): intval($params['defs']['professions']))?> );
            <? if (!empty($params['professions'])) { ?>professions.restore();<? } ?>
        </script>
    </div>

    <b class="b2"></b>
    <b class="b1"></b>

</div>


<div class="form masss-proonly">
    <b class="b1"></b>
    <b class="b2"></b>

    <div class="form-in">
        <div class="form-block first last">

            <div class="b-check b-check_margleft_-20 b-check_padbot_10">
                <input id="chk-pro" class="b-check__input" name="is_pro" type="checkbox" onclick="spam.set(this.name, this.checked)" onkeyup="spam.set(this.name, this.checked)" <?=(empty($params['is_pro'])? '': 'checked="checked" ')?>/>
                <label class="b-check__label b-check__label_bold" for="chk-pro">Только с <a href="/payed/" class="b-layout__link b-layout__link_inline-block b-layout__link_lineheight_1"><span title="Платный аккаунт" class="b-icon b-icon__pro b-icon__pro_f b-icon_top_-1"></span></a> аккаунтом</label>
            </div>

            <p>Владельцы профессионального аккаунта &ndash; это наиболее активная и ответственная часть аудитории Free-lance.ru.<br />Ограничивая свое послание кругом профессионалов, вы значительно экономите деньги, одновременно выигрывая в качестве.</p>
            <p>Получателей с <a href="/payed/" class="b-layout__link b-layout__link_inline-block b-layout__link_lineheight_1"><span title="Платный аккаунт" class="b-icon b-icon__pro b-icon__pro_f b-icon_top_-1"></span></a> : <span id="pro-users"><?=format($calc['pro']['count'])?></span> (<span id="pro-costFM"><?=format($calc['pro']['cost'])?></span> руб.)</p>
        </div>
    </div>

    <b class="b2"></b>
    <b class="b1"></b>
</div>

<div class="masss-res с">
    <div class="masss-res-b">
        <b class="b1"></b>
        <b class="b2"></b>

        <div class="masss-res-b-in" id="calc_done">
            <h4>Результат</h4>
            <p>Получателей:&nbsp;&nbsp;&nbsp;<strong id="users"><?=format($calc['count'])?></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="mass-rez-iphone">Стоимость:&nbsp;&nbsp;&nbsp;<strong id="costFM"><?=format($calc['cost'])?></strong> руб.</span></p>
            <em class="masss-price b-page__desktop b-page__ipad">1 получатель &ndash; <?=format($tariff['no_pro'], 1)?> руб, 1 получатель с PRO &ndash; <?=format($tariff['pro'], 1)?> руб</em>
        </div>

        <div class="masss-res-b-in" id="calc_waiting" style="display: none;">
            <h4>Результат</h4>
            <p>Получателей:&nbsp;&nbsp;&nbsp;<span id="calc_waiting_users">расчитывается</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="mass-rez-iphone">Стоимость:&nbsp;&nbsp;&nbsp;<span id="calc_waiting_cost">расчитывается</span></span></p>
            <em class="masss-price b-page__desktop b-page__ipad">1 получатель &ndash; <?=format($tariff['no_pro'], 1)?> руб, 1 получатель с PRO &ndash; <?=format($tariff['pro'], 1)?> руб</em>
        </div>
        <b class="b2"></b>
        <b class="b1"></b>
        <div class="b-page__iphone b-layout__txt b-layout__txt_padtop_10">1 получатель &ndash; <?=format($tariff['no_pro'], 1)?> руб, 1 получатель с PRO &ndash; <?=format($tariff['pro'], 1)?> руб</div>
    </div>
<?php /*
    <span class="mass-no-money" id="warning"><?=$masssending->error?></span>
*/ ?>
    <div class="masss-btn">
        <div class="b-buttons">
            <a href="." id="mass_btn_submit" onclick="this.blur(); return SendIt();" class="b-button b-button_flat b-button_flat_green"><span id="button_load_span">Отправить на модерацию</span>
            <img id="button_load_img" class="b-button__load" style="display: none;" height="6" width="26" alt="" src="/css/block/b-button/b-button__load.gif" />
            </a>	
            <span id="calc_msg" class="b-buttons__txt b-buttons__txt_color_80 b-buttons__txt_padleft_10"></span>
        </div>							
    </div>
<p style="padding-top:10px;">Премодерация рассылки осуществляется в рабочие часы с 10:00 до 18:00.</p>
</div>