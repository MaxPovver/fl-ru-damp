<?
if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}

	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
	$countries = country::GetCountries();
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/teams.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/notes.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/user_langs.php");
	$recoms = new teams;
	if ($user->birthday && !$error_flag){
	 $mnth = dateFormat("m",$user->birthday);
	 $day = dateFormat("d",$user->birthday);
	 $year = dateFormat("Y",$user->birthday);
	} elseif ($error_flag){
		$mnth = trim($_POST['datem']);
		$day = trim($_POST['dated']);
		$year = trim($_POST['datey']);
		$frl->login = $user->login;
		$user = $frl;
	}
	
	if ($user->birthday == "1910-01-01") {
	 $mnth = 01;
	 $day = "";
	 $year = "";
	}

        $cities = city::GetCities($user->country);
	$info_for_reg = @unserialize($user->info_for_reg);

    $languages = users::getUserLangs($user->uid);
    $lang_list = user_langs::getLanguages();
    if ( count($languages) > 0 ) {
        foreach ($lang_list as $key=>$lang) {
            $lang_list[$key] = "<option value=\"{$lang['id']}\">{$lang['name']}</option>";
        }
    }
    $lang_list = "<option value=\"0\">Выбрать язык</option>" . join('', $lang_list);
	if($_SESSION['uid']) {
    	$note = notes::GetNotes($_SESSION['uid'], null, $error);
    	
    	if(count($note) > 0)
        	foreach($note as $key=>$value) {
        	    $notes[$value['to_id']] = $value;
        	}
	}
//  require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/commune.common.php");
//  $xajax->printJavascript('/xajax/');
  
	require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/commune.common.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/countrys.common.php");
	$xajax->printJavascript('/xajax/');
?>
<?
	if ($error_flag) {
?>
<script type="text/javascript">
<!--
	//window.navigate('#frm');
//-->
</script>
<? } ?>
<script type="text/javascript">
<!--
	function CityUpd(v){
		ct = document.getElementById("frm").pf_city;
		ct.disabled = true;
		ct.options[0].innerHTML = "Подождите...";
		ct.value = 0;
		xajax_GetCitysByCid(v);
	}

    var f_visible = new Array();
    f_visible['email'] = new Array();
    f_visible['email']['data'] = new Array(<?=$user->email_1?"1":"0"?>,<?=$user->email_2?"1":"0"?>,<?=$user->email_3?"1":"0"?>);
    f_visible['email']['count'] = <?=(($user->email_1?1:0)+($user->email_2?1:0)+($user->email_3?1:0))?>;
    f_visible['icq'] = new Array();
    f_visible['icq']['data'] = new Array(<?=$user->icq_1?"1":"0"?>,<?=$user->icq_2?"1":"0"?>,<?=$user->icq_3?"1":"0"?>);
    f_visible['icq']['count'] = <?=(($user->icq_1?1:0)+($user->icq_2?1:0)+($user->icq_3?1:0))?>;

    f_visible['site'] = new Array();
    f_visible['site']['data'] = new Array(<?=$user->site_1?"1":"0"?>,<?=$user->site_2?"1":"0"?>,<?=$user->site_3?"1":"0"?>);
    f_visible['site']['count'] = <?=(($user->site_1?1:0)+($user->site_2?1:0)+($user->site_3?1:0))?>;
    f_visible['jabber'] = new Array();
    f_visible['jabber']['data'] = new Array(<?=$user->jabber_1?"1":"0"?>,<?=$user->jabber_2?"1":"0"?>,<?=$user->jabber_3?"1":"0"?>);
    f_visible['jabber']['count'] = <?=(($user->jabber_1?1:0)+($user->jabber_2?1:0)+($user->jabber_3?1:0))?>;
    f_visible['phone'] = new Array();
    f_visible['phone']['data'] = new Array(<?=$user->phone_1?"1":"0"?>,<?=$user->phone_2?"1":"0"?>,<?=$user->phone_3?"1":"0"?>);
    f_visible['phone']['count'] = <?=(($user->phone_1?1:0)+($user->phone_2?1:0)+($user->phone_3?1:0))?>;
    f_visible['lj'] = new Array();
    f_visible['lj']['data'] = new Array(<?=$user->lj_1?"1":"0"?>,<?=$user->lj_2?"1":"0"?>,<?=$user->lj_3?"1":"0"?>);
    f_visible['lj']['count'] = <?=(($user->lj_1?1:0)+($user->lj_2?1:0)+($user->lj_3?1:0))?>;
    f_visible['skype'] = new Array();
    f_visible['skype']['data'] = new Array(<?=$user->skype_1?"1":"0"?>,<?=$user->skype_2?"1":"0"?>,<?=$user->skype_3?"1":"0"?>);
    f_visible['skype']['count'] = <?=(($user->skype_1?1:0)+($user->skype_2?1:0)+($user->skype_3?1:0))?>;

    function m_field_add(type) {
        if(f_visible[type]['count']!=3) {
            var is_done = 0;
            if(f_visible[type]['data'][0]==0 && !is_done) {
                f_visible[type]['data'][0] = 1;
                f_visible[type]['count']++;
                document.getElementById('m_'+type+'_1').style.display='';
                is_done = 1;
            }
            if(f_visible[type]['data'][1]==0 && !is_done) {
                f_visible[type]['data'][1] = 1;
                f_visible[type]['count']++;
                document.getElementById('m_'+type+'_2').style.display='';
                is_done = 1;
            }
            if(f_visible[type]['data'][2]==0 && !is_done) {
                f_visible[type]['data'][2] = 1;
                f_visible[type]['count']++;
                document.getElementById('m_'+type+'_3').style.display='';
                is_done = 1;
            }
            if(f_visible[type]['count']==3) {
                document.getElementById('bm_'+type).style.display = 'none';
            }
        }
    }

    function m_field_del(type,num) {
        if(f_visible[type]['count']==3) {
            document.getElementById('bm_'+type).style.display='';
        }
        f_visible[type]['count']--;
        f_visible[type]['data'][num-1] = 0;
        document.getElementById(type+'_'+num).value='';
        document.getElementById('m_'+type+'_'+num).style.display='none';
        try {
            document.getElementById('em_'+type+'_'+num).style.display='none';
        } catch(err) { }
    }
//-->
</script>
<form action="." method="post" enctype="multipart/form-data" name="frm" id="frm" onsubmit="return allowedExt(this['resume'].value);">
            <table cellspacing="0" cellpadding="0" class="inform-setup"  style="width:100%; border:0">
                <tr><td colspan="2" style="height:10px"></td></tr>
                <?php if ($error) { ?>
                <tr><td colspan="2"><?= view_error($error); ?></td></tr>
                <?php } ?>
                <?php if ($info) { ?>
                <tr><td colspan="2">
                <?= view_info($info); ?>
                <script type="text/javascript">_gaq.push(['_trackPageview', '/virtual/freelance/profile']); ga('send', 'pageview', '/virtual/freelance/profile');</script>
                </td></tr>
                <?php } ?>
                <tr>
                    <td style=" width:110px;"><div class="b-layout__txt b-layout__txt_bold b-layout__txt_padtop_5">Дата рождения:</div></td>
                    <td class="">
                       <div class="b-combo b-combo_inline-block">
                          <div class="b-combo__input b-combo__input_width_50">
                             <input class="b-combo__input-text " type="text" name="dated" size="4" maxlength="2" value="<?= htmlspecialchars($day, ENT_QUOTES) ?>" />
                          </div>
                       </div>
                       <div class="b-select b-select_inline-block b-select_width_140">
                        <select class="b-select__select " name="datem">
                            <option value="1" <?= ($mnth == 1) ? "selected='selected'" : "" ?> >января</option>
                            <option value="2" <?= ($mnth == 2) ? "selected='selected'" : "" ?>>февраля</option>
                            <option value="3" <?= ($mnth == 3) ? "selected='selected'" : "" ?>>марта</option>
                            <option value="4" <?= ($mnth == 4) ? "selected='selected'" : "" ?>>апреля</option>
                            <option value="5" <?= ($mnth == 5) ? "selected='selected'" : "" ?>>мая</option>
                            <option value="6" <?= ($mnth == 6) ? "selected='selected'" : "" ?>>июня</option>
                            <option value="7" <?= ($mnth == 7) ? "selected='selected'" : "" ?>>июля</option>
                            <option value="8" <?= ($mnth == 8) ? "selected='selected'" : "" ?>>августа</option>
                            <option value="9" <?= ($mnth == 9) ? "selected='selected'" : "" ?>>сентября</option>
                            <option value="10" <?= ($mnth == 10) ? "selected='selected'" : "" ?>>октября</option>
                            <option value="11" <?= ($mnth == 11) ? "selected='selected'" : "" ?>>ноября</option>
                            <option value="12" <?= ($mnth == 12) ? "selected='selected'" : "" ?>>декабря</option>
                        </select>
                        </div>
                       <div class="b-combo b-combo_inline-block">
                          <div class="b-combo__input b-combo__input_width_60">
                            <input class="b-combo__input-text " type="text" name="datey" size="8" maxlength="4" value="<?= htmlspecialchars($year, ENT_QUOTES) ?>" />
                          </div>
                       </div>
                    </td>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <?php if ($alert[1]) { ?>
                <tr><td>&nbsp;</td><td><?= view_error($alert[1]) ?></td></tr>
                <?php } //if ?>
                <tr>
                    <td><div class="b-layout__txt b-layout__txt_bold">Пол:</div></td>
                    <td>
                        <div class="b-radio b-radio_layout_horizontal">
                           <div class="b-radio__item">
                              <input type="radio" id="sex_w" class="b-radio__input" name="sex" value="0" <?= $user->sex == 'f' ? 'checked="checked"' : ''; ?>/><label class="b-radio__label b-radio__label_fontsize_13 " for="sex_w">Женский</label>
                           </div>&#160;&#160;
                           <div class="b-radio__item">
                              <input type="radio" id="sex_m" class="b-radio__input" name="sex" value="1" <?= $user->sex == 't' ? 'checked="checked"' : ''; ?>/><label class="b-radio__label b-radio__label_fontsize_13" for="sex_m">Мужской</label>
                           </div>
                       </div>
                    </td>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td><div class="b-layout__txt b-layout__txt_bold b-layout__txt_padtop_5">Страна:</div></td>
                    <td>
                        <div class="b-select">
                            <select name="country" class="b-select__select b-select__select_width_220 " onChange="CityUpd(this.value)">
                                <option value="0">Не выбрано</option>
                                <?php foreach ($countries as $countid => $country) { ?>
                                <option value="<?= $countid ?>"<? if ($countid == $user->country) print(" selected='selected'") ?> ><?= $country ?></option>
                                <?php } //foreach ?>
                            </select>
                        </div>
                    </td>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <? if ($alert['country']) { ?>
                <tr><td>&nbsp;</td><td><?= view_error($alert['country']) ?></td></tr>
                <? } ?>
                <tr>
                    <td><div class="b-layout__txt b-layout__txt_bold b-layout__txt_padtop_5">Город:</div></td>
                    <td id="frm_city">
                        <div class="b-select">
                            <select name="pf_city" class="b-select__select b-select__select_width_220 " <? if (!$cities) print("disabled='disabled'") ?> >
                                <option value="0">Не выбрано</option>
                                <?php if ($cities) foreach ($cities as $cityid => $city) { ?>
                                <option value="<?= $cityid ?>"<? if ($cityid == $user->city) print(" selected='selected'") ?> ><?= $city ?></option>
                                <?php } //foreach ?>
                            </select>
                        </div>
                    </td>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr id="lang_item_0" class="langitem">
                    <td><div class="b-layout__txt b-layout__txt_bold b-layout__txt_padtop_5">Язык:</div></td>
                    <td>
                        <div class="b-select b-select_inline-block b-select_padright_10 b-layout_padbot_10_ipad">
                            <select class="b-select__select b-select__select_width_220 " name="langs[0]" id="langs-0">
                                <?=$lang_list; $style = '';
                                    if ( count($languages)  < 2) {
                                        $style = 'style="margin-left:4px;"';
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="b-radio b-radio_layout_horizontal b-radio_inline-block b-radio_top_3 b-radio_layout_vertical_iphone">
                           <div class="b-radio__item b-radio__item_padright_20">
                            <input id="b-radio__input1" class="b-radio__input" type="radio" value="1" name="lang-q[0]"><label class="b-radio__label b-radio__label_fontsize_13 sign_first_row" for="b-radio__input1" <?=$style ?>>Начальный</label>
                           </div>
                           <div class="b-radio__item b-radio__item_padright_20">
                            <input id="b-radio__input2" class="b-radio__input" type="radio" value="2" name="lang-q[0]" checked="checked"><label class="b-radio__label b-radio__label_fontsize_13 sign_first_row" for="b-radio__input2" <?=$style ?>>Средний</label>
                           </div>
                           <div class="b-radio__item b-radio__item_padright_20">
                            <input id="b-radio__input3" class="b-radio__input" type="radio" value="3" name="lang-q[0]"><label class="b-radio__label b-radio__label_fontsize_13 sign_first_row" for="b-radio__input3" <?=$style ?>>Продвинутый</label>
                           </div>
                           <div class="b-radio__item">
                            <input id="b-radio__input4" class="b-radio__input" type="radio" value="4" name="lang-q[0]"><label class="b-radio__label b-radio__label_fontsize_13 sign_first_row" for="b-radio__input4" <?=$style ?>>Родной</label>
                           </div>
                        </div>
                        <? if ( count($languages) ) {?>
                        <script type="text/javascript">lang_set_selected_item(0, "<?=$languages[0]['lang_id'] ?>", "<?=$languages[0]['quality'] ?>");</script>
                        <?} ?>
                        <input type="hidden" id="lang-id-0" value="<?=$languages[0]['id'] ?>" />
                    </td>
                    <td class="rem_add_btn">
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_bordbot_dot_6db335" href="#" onclick="return lang_add();">+ Добавить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? $padtop = "padding-top:1px;";
                   browserCompat($browser);
                   if ( $browser == "chrome" || $browser == "opera") {
                       $padtop = "";
                   }
                   for ( $i = 1; $i < count($languages); $i++ ) {?>
                  <tr id="lang_item_<?=$i ?>" class="langitem">
                    <td>&nbsp;</td>
                    <td>
                        <div class="b-select b-select_inline-block b-select_padright_10 b-layout_padbot_10_ipad">
                            <select class="b-select__select b-select__select_width_220  " name="langs[<?=$i ?>]" id="langs-<?=$i ?>">
                                <?=$lang_list ?>
                            </select>
                        </div>
                        <div class="b-radio b-radio_layout_horizontal b-radio_inline-block b-radio_top_3 b-radio_layout_vertical_iphone">
                           <div class="b-radio__item b-radio__item_padright_20">
                            <input id="b-radio__input<?=$i ?>1" class="b-radio__input" type="radio" value="1" name="lang-q[<?=$i ?>]"><label class="b-radio__label b-radio__label_fontsize_13" for="b-radio__input<?=$i ?>1">Начальный</label>
                           </div>
                           <div class="b-radio__item b-radio__item_padright_20">
                            <input id="b-radio__input<?=$i ?>2" class="b-radio__input" type="radio" value="2" name="lang-q[<?=$i ?>]"><label class="b-radio__label b-radio__label_fontsize_13" for="b-radio__input<?=$i ?>2">Средний</label>
                           </div>
                           <div class="b-radio__item b-radio__item_padright_20">
                            <input id="b-radio__input<?=$i ?>3" class="b-radio__input" type="radio" value="3" name="lang-q[<?=$i ?>]"><label class="b-radio__label b-radio__label_fontsize_13" for="b-radio__input<?=$i ?>3">Продвинутый</label>
                           </div>
                           <div class="b-radio__item">
                            <input id="b-radio__input<?=$i ?>4" class="b-radio__input" type="radio" value="4" name="lang-q[<?=$i ?>]"><label class="b-radio__label b-radio__label_fontsize_13" for="b-radio__input<?=$i ?>4">Родной</label>
                           </div>
                        </div>
                        <script type="text/javascript">lang_set_selected_item("<?=$i ?>", "<?=$languages[$i]['lang_id']?>", "<?=$languages[$i]['quality'] ?>");</script>
                        <input type="hidden" id="lang-id-<?=$i ?>" value="<?=$languages[$i]['id'] ?>" />
                    </td>
                    <td style="padding-left:10px;" class="rem_add_btn">
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_dot_c10600" onclick="return lang_del(<?=$i ?>);" href="#">- Удалить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? } ?>
                <?php if ($alert['city']) { ?>
                <tr><td>&nbsp;</td><td><?= view_error($alert['city']) ?></td></tr>
                <?php } //if?>
                <tr>
                    <td><div class="b-layout__txt b-layout__txt_bold b-layout__txt_padtop_5">Сайт:</div></td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo">
                          <div class="b-combo__input">
                                <input type="text" autocomplete="off" name="site" class="b-combo__input-text " value="<?= $user->site ? $user->site : 'http://' ?>" />
                          </div>
                       </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_bordbot_dot_6db335" href="#" id="bm_site" style="<?= (($user->site_1 && $user->site_2 && $user->site_3) ? 'display:none;' : '') ?>" onClick="m_field_add('site'); return false;">+ Добавить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <?php if ($alert[11]) { ?>
                <tr><td>&nbsp;</td><td><?= view_error($alert[11]) ?></td></tr>
                <?php }//if ?>
                    
                <!-- more site -->
                <tr id="m_site_1" style=" <?= $user->site_1 ? "" : "display:none;" ?>">
                    <td>&nbsp;</td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo">
                          <div class="b-combo__input">
                           <input type="text" autocomplete="off" id="site_1" name="site_1" class="b-combo__input-text " value="<?= $user->site_1 ? $user->site_1 : 'http://' ?>" />
                          </div>
                       </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_dot_c10600" href="#" onClick="m_field_del('site',1); return false;">- Удалить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert[41]) { ?>
                <tr id="em_site_1"><td>&nbsp;</td><td><?= view_error($alert[41]) ?></td></tr>
                <? } ?>
                <tr id="m_site_2" style=" <?= $user->site_2 ? '' : 'display:none;' ?>">
                    <td>&nbsp;</td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo">
                          <div class="b-combo__input">
                            <input type="text" autocomplete="off" id="site_2" name="site_2" class="b-combo__input-text " value="<?= $user->site_2 ? $user->site_2 : 'http://' ?>" />
                          </div>
                       </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_dot_c10600" href="#" onClick="m_field_del('site',2); return false;">- Удалить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert[42]) { ?>
                <tr id="em_site_2"><td>&nbsp;</td><td><?= view_error($alert[42]) ?></td></tr>
                <? } ?>
                <tr id="m_site_3" style=" <?= $user->site_3 ? "" : "display:none;" ?>">
                    <td>&nbsp;</td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo">
                          <div class="b-combo__input">
                                <input type="text" autocomplete="off" id="site_3" name="site_3" class="b-combo__input-text " value="<?= $user->site_3 ? $user->site_3 : 'http://' ?>" />
                          </div>
                       </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_dot_c10600" href="#" onClick="m_field_del('site',3); return false;">- Удалить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert[43]) { ?>
                <tr id="em_site_3"><td>&nbsp;</td><td><?= view_error($alert[43]) ?></td></tr>
                <? } ?>
                <!-- more site -->
    
                <tr>
                    <td><div class="b-layout__txt b-layout__txt_bold b-layout__txt_padtop_5">ICQ:</div></td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo">
                          <div class="b-combo__input">
                              <input type="text" autocomplete="off" name="icq" class="b-combo__input-text " value="<?= $user->icq ?>" maxlength="96" />
                          </div>
                       </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_bordbot_dot_6db335" href="#" id="bm_icq" style="<?= (($user->icq_1 && $user->icq_2 && $user->icq_3) ? 'display:none;' : '') ?>" onClick="m_field_add('icq'); return false;">+ Добавить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert[2]) { ?>
                <tr><td>&nbsp;</td><td><?= view_error($alert[2]) ?></td></tr>
                <? } ?>
                    
                <!-- more icq -->
                <tr id="m_icq_1" style=" <?= $user->icq_1 ? '' : 'display:none;' ?>">
                    <td>&nbsp;</td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo">
                          <div class="b-combo__input">
                            <input type="text" autocomplete="off" id="icq_1" name="icq_1" class="b-combo__input-text " value="<?= $user->icq_1 ?>" maxlength="96" />
                          </div>
                       </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_dot_c10600" href="#"  onClick="m_field_del('icq',1); return false;">- Удалить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert[31]) { ?>
                <tr id="em_icq_1"><td>&nbsp;</td><td><?= view_error($alert[31]) ?></td></tr>
                <? } ?>
                <tr id="m_icq_2" style="<?= $user->icq_2 ? "" : "display:none;" ?>">
                    <td>&nbsp;</td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo">
                          <div class="b-combo__input">
                              <input type="text" autocomplete="off" id="icq_2" name="icq_2" class="b-combo__input-text " value="<?= $user->icq_2 ?>" maxlength="96" />
                          </div>
                       </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_dot_c10600" href="#" onClick="m_field_del('icq',2); return false;">- Удалить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert[32]) { ?>
                <tr id="em_icq_2"><td>&nbsp;</td><td><?= view_error($alert[32]) ?></td></tr>
                <? } ?>
                <tr id="m_icq_3" style=" <?= $user->icq_3 ? '' : 'display:none;' ?>">
                    <td>&nbsp;</td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo">
                          <div class="b-combo__input">
                            <input type="text" autocomplete="off" id="icq_3" name="icq_3" class="b-combo__input-text " value="<?= $user->icq_3 ?>" maxlength="96"/>
                          </div>
                       </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_dot_c10600" href="#" onClick="m_field_del('icq',3); return false;">- Удалить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert[33]) { ?>
                <tr id="em_icq_3"><td>&nbsp;</td><td><?= view_error($alert[33]) ?></td></tr>
                <? } ?>
                <!-- more icq -->
    
                <tr>
                    <td><div class="b-layout__txt b-layout__txt_bold b-layout__txt_padtop_5">Jabber:</div></td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo">
                          <div class="b-combo__input">
                              <input type="text" autocomplete="off" name="jabber"  class="b-combo__input-text " value="<?= $user->jabber ?>" />
                          </div>
                       </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_bordbot_dot_6db335" href="#" id="bm_jabber" style=" <?= (($user->jabber_1 && $user->jabber_2 && $user->jabber_3) ? 'display:none;' : '') ?>" onClick="m_field_add('jabber'); return false;">+ Добавить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert['jabber']) { ?>
                <tr><td>&nbsp;</td><td><?= view_error($alert['jabber']) ?></td></tr>
                <? } ?>
                <!-- more jabber -->
                <tr id="m_jabber_1" style="<?= $user->jabber_1 ? "" : "display:none;" ?>">
                    <td></td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo">
                          <div class="b-combo__input">
                              <input type="text" autocomplete="off" id="jabber_1" name="jabber_1" class="b-combo__input-text " value="<?= $user->jabber_1 ?>" />
                          </div>
                       </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_dot_c10600" href="#" onClick="m_field_del('jabber',1); return false;">- Удалить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert[51]) { ?>
                <tr id="em_jabber_1"><td>&nbsp;</td><td><?= view_error($alert[51]) ?></td></tr>
                <? } ?>
                <tr id="m_jabber_2" style="<?= $user->jabber_2 ? "" : "display:none;" ?>">
                    <td></td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo">
                          <div class="b-combo__input">
                              <input type="text" autocomplete="off" id="jabber_2" name="jabber_2" class="b-combo__input-text " value="<?= $user->jabber_2 ?>" />
                          </div>
                       </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_dot_c10600" href="#" onClick="m_field_del('jabber',2); return false;">- Удалить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert[52]) { ?>
                <tr id="em_jabber_2"><td>&nbsp;</td><td><?= view_error($alert[52]) ?></td></tr>
                <? } ?>
                <tr id="m_jabber_3" style="<?= $user->jabber_3 ? "" : "display:none;" ?>">
                    <td></td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo">
                          <div class="b-combo__input">
                              <input type="text" autocomplete="off" id="jabber_3" name="jabber_3" class="b-combo__input-text " value="<?= $user->jabber_3 ?>" />
                          </div>
                       </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_dot_c10600" href="#" onClick="m_field_del('jabber',3); return false;">- Удалить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert[53]) { ?>
                <tr id="em_jabber_3"><td>&nbsp;</td><td><?= view_error($alert[53]) ?></td></tr>
                <? } ?>
                <!-- more jabber -->
    
                <tr>
                    <td><div class="b-layout__txt b-layout__txt_bold b-layout__txt_padtop_5">Телефон:</div></td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo">
                          <div class="b-combo__input">
                              <input type="text" autocomplete="off" name="phone" class="b-combo__input-text " value="<?= $user->phone ?>" />
                          </div>
                       </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_bordbot_dot_6db335" href="#" id="bm_phone" style=" <?= (($user->phone_1 && $user->phone_2 && $user->phone_3) ? 'display:none;' : '') ?>" onClick="m_field_add('phone'); return false;">+ Добавить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert[3]) { ?>
                <tr><td>&nbsp;</td><td><?= view_error($alert[3]) ?></td></tr>
                <? } ?>
                <!-- more phone -->
                <tr id="m_phone_1" style="<?= $user->phone_1 ? "" : "display:none;" ?>">
                    <td></td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo">
                          <div class="b-combo__input">
                              <input type="text" autocomplete="off" id="phone_1" name="phone_1" class="b-combo__input-text " value="<?= $user->phone_1 ?>" />
                          </div>
                       </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_dot_c10600" href="#" onClick="m_field_del('phone',1); return false;">- Удалить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert[61]) { ?>
                <tr id="em_phone_1"><td>&nbsp;</td><td><?= view_error($alert[61]) ?></td></tr>
                <? } ?>
                <tr id="m_phone_2" style=" <?= $user->phone_2 ? '' : 'display:none;' ?>">
                    <td></td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo">
                          <div class="b-combo__input">
                              <input type="text" autocomplete="off" id="phone_2" name="phone_2" class="b-combo__input-text " value="<?= $user->phone_2 ?>" />
                          </div>
                       </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_dot_c10600" href="#" onClick="m_field_del('phone',2); return false;">- Удалить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert[62]) { ?>
                <tr id="em_phone_2"><td>&nbsp;</td><td><?= view_error($alert[62]) ?></td></tr>
                <? } ?>
                <tr id="m_phone_3" style=" <?= $user->phone_3 ? "" : "display:none;" ?>">
                    <td></td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo">
                          <div class="b-combo__input">
                              <input type="text" autocomplete="off" id="phone_3" name="phone_3" class="b-combo__input-text " value="<?= $user->phone_3 ?>" />
                          </div>
                       </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_dot_c10600" href="#" onClick="m_field_del('phone',3); return false;">- Удалить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert[63]) { ?>
                <tr id="em_phone_3"><td>&nbsp;</td><td><?= view_error($alert[63]) ?></td></tr>
                <? } ?>
                <!-- more phone -->
    
                <tr>
                    <td><div class="b-layout__txt b-layout__txt_bold b-layout__txt_padtop_5">LiveJournal user:</div></td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo">
                          <div class="b-combo__input">
                              <input type="text" autocomplete="off" name="ljuser" class="b-combo__input-text " value="<?= $user->ljuser ?>" />
                          </div>
                       </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_bordbot_dot_6db335" href="#" id="bm_lj" style=" <?= (($user->lj_1 && $user->lj_2 && $user->lj_3) ? 'display:none;' : '') ?>" onClick="m_field_add('lj'); return false;">+ Добавить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert[12]) { ?>
                <tr><td>&nbsp;</td><td><?= view_error($alert[12]) ?></td></tr>
                <? } ?>
                <!-- more lj -->
                <tr id="m_lj_1" style=" <?= $user->lj_1 ? "" : "display:none;" ?>">
                    <td></td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo">
                          <div class="b-combo__input">
                              <input type="text" autocomplete="off" id="lj_1" name="lj_1" class="b-combo__input-text " value="<?= $user->lj_1 ?>" />
                          </div>
                       </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_dot_c10600" href="#" onClick="m_field_del('lj',1); return false;">- Удалить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert[71]) { ?>
                <tr id="em_lj_1"><td>&nbsp;</td><td><?= view_error($alert[71]) ?></td></tr>
                <? } ?>
                <tr id="m_lj_2" style="<?= $user->lj_2 ? "" : "display:none;" ?>">
                    <td></td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo">
                          <div class="b-combo__input">
                              <input type="text" autocomplete="off" id="lj_2" name="lj_2" class="b-combo__input-text " value="<?= $user->lj_2 ?>" />
                          </div>
                       </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_dot_c10600" href="#" onClick="m_field_del('lj',2); return false;">- Удалить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert[72]) { ?>
                <tr id="em_lj_2"><td>&nbsp;</td><td><?= view_error($alert[72]) ?></td></tr>
                <? } ?>
                <tr id="m_lj_3" style="<?= $user->lj_3 ? "" : "display:none;" ?>">
                    <td></td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo">
                          <div class="b-combo__input">
                              <input type="text" autocomplete="off" id="lj_3" name="lj_3" class="b-combo__input-text " value="<?= $user->lj_3 ?>" />
                          </div>
                       </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_dot_c10600" href="#" onClick="m_field_del('lj',3); return false;">- Удалить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert[73]) { ?>
                <tr id="em_lj_3"><td>&nbsp;</td><td><?= view_error($alert[73]) ?></td></tr>
                <? } ?>
                <!-- more lj -->
    
                <tr>
                    <td><div class="b-layout__txt b-layout__txt_bold b-layout__txt_padtop_5">Skype:</div></td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo b-combo_inline-block b-combo_valign_mid">
                          <div class="b-combo__input b-combo__input_width_280">
                            <input type="text" autocomplete="off" name="skype" value="<?= $user->skype ?>" maxlength="64" class="b-combo__input-text " />
                          </div>
                       </div>
                        <div class="b-check b-check_inline-block b-check_valign_middle b-check_padleft_10 b-check_padtop_8" style="display: none">
                           <input id="by-skype" class="b-check__input" name="skype_as_link" type="checkbox" value="1"<?=$user->skype_as_link == 't' ? ' checked="checked"' : ''?> />
                           <label for="by-skype" class="b-check__label b-check__label_fontsize_13">Отображать "Связаться по skype" вместо логина</label>
                        </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_bordbot_dot_6db335" href="#" id="bm_skype" style=" <?= (($user->skype_1 && $user->skype_2 && $user->skype_3) ? 'display:none;' : '') ?>" onClick="m_field_add('skype'); return false;">+ Добавить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                
                <!-- more skype -->
                <?php for($i = 1; $i <= 3; $i++): ?>
                <tr id="m_skype_<?=$i?>" style=" <?= $user->{'skype_'.$i} ? "" : "display:none;" ?>">
                    <td></td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo b-combo_inline-block b-combo_valign_mid">
                          <div class="b-combo__input b-combo__input_width_280">
                              <input type="text" autocomplete="off" id="skype_<?=$i?>" name="skype_<?=$i?>" class="b-combo__input-text " value="<?= $user->{'skype_'.$i} ?>" />
                          </div>
                       </div>
                        <div class="b-check b-check_inline-block b-check_valign_middle b-check_padleft_10 b-check_padtop_8" style="display: none">
                           <input id="by-skype<?=$i?>" class="b-check__input" name="skype_<?=$i?>_as_link" type="checkbox" value="1"<?=$user->{'skype_'.$i.'_as_link'} == 't' ? ' checked="checked"' : ''?> />
                           <label for="by-skype<?=$i?>" class="b-check__label b-check__label_fontsize_13">Отображать "Связаться по skype" вместо логина</label>
                        </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_dot_c10600" href="#" onClick="m_field_del('skype',<?=$i?>); return false;">- Удалить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <?php endfor; ?>
                <!-- more skype -->
    
                <tr>
                    <td><div class="b-layout__txt b-layout__txt_bold b-layout__txt_padtop_5">E-mail:</div></td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo b-combo_inline-block b-combo_valign_mid">
                          <div class="b-combo__input b-combo__input_width_280">
                            <input type="text" autocomplete="off" maxlength="100" name="second_email" class="b-combo__input-text " value="<?= $user->second_email ?>" />
                          </div>
                       </div>
                        <div class="b-check b-check_inline-block b-check_valign_middle b-check_padleft_10 b-check_padtop_8" style="display: none">
                           <input id="by-mail" class="b-check__input" name="email_as_link" type="checkbox" value="1"<?=$user->email_as_link == 't' ? ' checked="checked"' : ''?> />
                           <label for="by-mail" class="b-check__label b-check__label_fontsize_13">Отображать "Написать письмо" вместо адреса</label>
                        </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_bordbot_dot_6db335" href="#" id="bm_email" style=" <?= (($user->email_1 && $user->email_2 && $user->email_3) ? 'display:none;' : '') ?>" onClick="m_field_add('email'); return false;">+ Добавить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert[10]) { ?>
                <tr><td>&nbsp;</td><td><?= view_error($alert[10]) ?></td></tr>
                <? } ?>
                    
                <!-- more emails -->
                <?php for($i = 1; $i <= 3; $i++): ?>
                <tr id="m_email_<?=$i?>" style="<?= $user->{'email_'.$i} ? "" : "display:none;" ?>">
                    <td></td>
                    <td class="b-layout__td b-layout__td_width_640">
                       <div class="b-combo b-combo_inline-block b-combo_valign_mid">
                          <div class="b-combo__input b-combo__input_width_280">
                              <input type="text" autocomplete="off" id="email_<?=$i?>" name="email_<?=$i?>" class="b-combo__input-text " value="<?= $user->{'email_'.$i} ?>" />
                          </div>
                       </div>
                        <div class="b-check b-check_inline-block b-check_valign_middle b-check_padleft_10 b-check_padtop_8" style="display: none">
                           <input id="by-mail<?=$i?>" class="b-check__input" name="email_<?=$i?>_as_link" type="checkbox" value="1"<?=$user->{'email_'.$i.'_as_link'} == 't' ? ' checked="checked"' : ''?> />
                           <label for="by-mail<?=$i?>" class="b-check__label b-check__label_fontsize_13">Отображать "Написать письмо" вместо адреса</label>
                        </div>
                    </td>
                    <td>
                        <div class="b-layout__txt b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_dot_c10600" href="#" onClick="m_field_del('email',<?=$i?>); return false;">- Удалить</a></div>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <? if ($alert['2'.$i]): ?>
                <tr id="em_email_<?$i?>"><td>&nbsp;</td><td><?= view_error($alert['2'.$i]) ?></td></tr>
                <?php endif; ?>
                <?php endfor; ?>
                <!-- more emails -->
		<tr><td  colspan="3" style="height:10px"></td></tr>
		<tr><td></td><td>
            <div class="b-layout__txt b-layout__txt_fontsize_11" style="display: none;"><span class="b-icon b-icon_sbr_oattent"></span>Для безопасности вашего аккаунта рекомендуем скрывать свои skype-логины и адреса почт от публичного доступа.</div>
      </td><td></td></tr>
		<tr><td  colspan="3" style="height:10px"></td></tr>
            </table>
<table  cellspacing="0" cellpadding="0" style="width:100%; border:0">
<tr>
	<td class="brdtop" style="padding:3px 20px;">Резюме</td>
</tr>
</table>
<table cellspacing="0" cellpadding="4"  style="width:100%; border:0; margin-top:10px;"  class="dop-inf-tabl">
<tr>
    <td style="height:20px">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr><td>Загрузить резюме:</td>
            <? if ($user->resume_file) { ?>
            <td style="text-align:right"><input type="hidden" name="del_resume" value="0" /> <a href="<?=WDCPREFIX?>/users/<?=$user->login?>/resume/<?=$user->resume_file?>" class="blue">Резюме загружено</a>&nbsp;&nbsp;(<span onclick="if (warning(4)) {frm.del_resume.value='1'; frm.submit();}" class="ah">Удалить</span>)</td>
            <? } ?>
        </tr></table>
    </td>
</tr>
<tr>
    <td>
        <input type="file" name="resume" class="wdh100" />
        <? if ($alert[4]) print(view_error($alert[4])) ?>
        Максимальный размер файла: 5 Мб.<br/>
        Файлы следующих форматов запрещены к загрузке: <?=implode(', ', $GLOBALS['disallowed_array'])?>
    </td>
</tr>
<tr>
    <td>
        Текст:
    </td>
</tr>
<tr>
    <td>
                    <div class="b-textarea"><textarea cols="89" rows="17" name="resumetxt" class="b-textarea__textarea b-textarea_noresize tawl"  rel="4000"><?=(($_POST['resumetxt'] != "")?  antispam(htmlspecialchars($_POST['resumetxt'])):input_ref($user->resume))?></textarea></div>
    </td>
</tr>
<? if ($alert[8]) { ?>
<tr><td><?=view_error($alert[8])?></td></tr>
<? } ?>
<tr>
    <td>
        Максимум 4000 знаков.<br />
        Вы можете использовать простые теги для форматирования текста.
    </td>
</tr>
<tr>
    <td>&nbsp;</td>
</tr>
</table>

<table  cellspacing="0" cellpadding="0" 	 style="width:100%; background:#ffe5d5; border:0">
<tr>
	<td class="brdtop" style="padding:3px 20px;">Участие в конкурсах и награды<div class="b-check b-check_float_right b-check_float_none_iphone"><input class="b-check__input" type="checkbox" name="showkonk" value="1" <? if ($user->blocks[1]) print "checked='checked'" ?> id="ch-b1"  /> <label class="b-check__label b-check__label_bold b-check__label_color_666" for="ch-b1">Показывать блок</label></div></td>
</tr>
</table>
<table cellspacing="0" cellpadding="0" style="width:100%">
<tr>
	<td>
		<table cellspacing="0" cellpadding="4" style="width:100%; border:0"  class="dop-inf-tabl">
		<tr>
			<td>
				Текст:
			</td>
		</tr>
		<tr>
		<td>
                    <div class="b-textarea"><textarea cols="89" rows="9" name="konk" class="b-textarea__textarea"><?=(($_POST['konk'] != "")?  antispam(htmlspecialchars($_POST['konk'])):input_ref($user->konk))?></textarea></div>
		</td>
		</tr>
		<? if ($alert[9]) { ?>
		<tr><td><?=view_error($alert[9])?></td></tr>
		<? } ?>
		<tr>
			<td>
				Максимум 4000 знаков.<br />
				Вы можете использовать простые теги для форматирования текста
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
		</table>
	</td>
	</tr>
</table>

<? $limit = 5; ?>

<table style="width:100%; border:0" cellspacing="0" cellpadding="0">
<tr>
	<td class="brdtop" style="padding:3px 20px;">В избранном у работодателей <div class="b-check b-check_float_right b-check_float_none_iphone"><input class="b-check__input" type="checkbox" name="showempl" value="1" <? if ($user->blocks[3]) print "checked='checked'" ?> id="ch-b2"  /> <label class="b-check__label b-check__label_bold b-check__label_color_666" for="ch-b2">Показывать блок</label></div></td>
</tr>
</table>
<table cellspacing="0" cellpadding="19" style="width:100%">
  <tr>
    <td>
      &nbsp;
      <div class=" izbr izbr-setup"><?
      $recs = $recoms->teamsInEmpFavorites($user->login, $error);
      $i=0;
      $allCnt = count($recs);
      if ($recs) {
        $pt=0;$type = 1;
        foreach ($recs as $rec) {
            if(count($notes[$rec['uid']]) > 0) {
                $note = $notes[$rec['uid']];
            } else {
                $note = false;
            }
            $is_emp = is_emp($rec['role']);
            $cls    = $is_emp?"emp":"frl";
          if(++$i > $limit)
            break;
        ?>
          <div id="n<?=$rec['uid']?>" class="izbr-item">
          <span id="elm-offset-<?=$rec['uid']?>-<?=$type?>"></span>
          <div class="izbr-check">&nbsp;</div>
			<?= __commPrntUsrAvtr($rec)?>
			<div class="izbr-text">
                <span class="user-inf">
                <span class="<?=$cls?>name11"><a href="/users/<?=$rec['login']?>" class="<?=$cls?>name11" title="<?=($rec['uname']." ".$rec['usurname'])?>"><?=($rec['uname']." ".$rec['usurname'])?></a> [<a href="/users/<?=$rec['login']?>/" class="<?=$cls?>name11" title="<?=$rec['login']?>"><?=$rec['login']?></a>]</span> <?= view_mark_user($rec);?> <?= $session->view_online_status($rec['login'], false, "")?>
                </span>
                <?php if(!is_emp($rec['role'])) {?>
                    Специализация: <?= professions::GetProfNameWP($rec['spec'], ' / ', "не указано", "lnk-666", true)?>
                <?php }//if?>
				<div class="userFav_<?=$rec['uid']?>">
				    <?php if($note === false) { ?>
                    <div class="sent-mark"><a href="javascript:void(0)" onclick="xajax_getNotesForm(<?= $rec['uid']?>, false, <?=$type?>);">Оставить заметку</a>&nbsp;<span></span></div>
                    <?php } else { //if ?>
                        <?include ("../tpl.notes-textitem.php"); ?>
                    <?php }//else ?>
				</div>
			</div>
		</div>
     <?   $pt = 15;
      } }
     ?>
     </div>
    </td>
  </tr>

  <? if($i > $limit) { ?>
    <tr>
      <td style="padding:0 19px 19px;">
        <a class="b-layout__link" href='/users/<?=$user->login?>/all/?mode=1'><b>Все (<?=$allCnt?>)</b></a>
      </td>
    </tr>
  <? } ?>
</table>

<table style="width:100%; border:0" cellspacing="0" cellpadding="0">
<tr>
	<td class="brdtop" style="padding:3px 20px;">В избранном у фрилансеров <div class="b-check b-check_float_right b-check_float_none_iphone"><input class="b-check__input" type="checkbox" name="showfrl" value="1" <? if ($user->blocks[4]) print " checked='checked'" ?> id="ch-b3" /> <label class="b-check__label b-check__label_bold b-check__label_color_666" for="ch-b3">Показывать блок</label></div></td>
</tr>
</table>
<table style="width:100%; border:0" cellspacing="0" cellpadding="19">
  <tr>
    <td>
      &nbsp;
      <div class=" izbr izbr-setup">
      <?
      $recs = $recoms->teamsInFrlFavorites($user->login, $error);
      $i=0;
      $allCnt = count($recs);
      if ($recs) {
          
        //Получаем is_profi
        $ids = array();
        
        foreach($recs as $rec) {
            $ids[] = $rec['uid'];
        }

        if($ids) {
            $recsProfi = $user->getUsersProfi($ids);
        }    
          
          
        $pt=0;$type = 2;
        foreach ($recs as $rec) {
            
            if(isset($recsProfi[$rec['uid']])) {
                $rec['is_profi'] = $recsProfi[$rec['uid']];
            } 
            
            if(count($notes[$rec['uid']]) > 0) {
                $note = $notes[$rec['uid']];
            } else {
                $note = false;
            }
            $is_emp = is_emp($rec['role']);
            $cls    = $is_emp?"emp":"frl";
          if(++$i > $limit)
            break;
        ?>
          <div id="n<?=$rec['uid']?>" class="izbr-item">
          <span id="elm-offset-<?=$rec['uid']?>-<?=$type?>"></span> 
          <div class="izbr-check">&nbsp;</div>
			<?= __commPrntUsrAvtr($rec)?>
			<div class="izbr-text">
                <span class="user-inf"><span class="<?=$cls?>name11"><a href="/users/<?=$rec['login']?>" class="<?=$cls?>name11" title="<?=($rec['uname']." ".$rec['usurname'])?>"><?=($rec['uname']." ".$rec['usurname'])?></a> [<a href="/users/<?=$rec['login']?>/" class="<?=$cls?>name11" title="<?=$rec['login']?>"><?=$rec['login']?></a>]</span> <?= view_mark_user($rec);?> <?= $session->view_online_status($rec['login'], false, "")?></span>
                <?php if(!is_emp($rec['role'])) {?>
                    Специализация: <?= professions::GetProfNameWP($rec['spec'], ' / ', "не указано", "lnk-666", true)?>
                <?php }//if?>
				<div class="userFav_<?=$rec['uid']?>">
				    <?php if($note === false) { ?>
                    <div class="sent-mark"><a href="javascript:void(0)" onclick="xajax_getNotesForm(<?= $rec['uid']?>, false, <?=$type?>);">Оставить заметку</a>&nbsp;<span></span></div>
                    <?php } else { //if ?>
                        <?include ("../tpl.notes-textitem.php"); ?>
                    <?php }//else ?>
				</div>
			</div>
		</div>
     <?   $pt = 15;
        } 
      }
     ?>
     </div>
    </td>
  </tr>
  <? if($i > $limit) { ?>
    <tr>
      <td style="padding:0 0 19px 19px;">
        <a class="blue" href='/users/<?=$user->login?>/all/?mode=2'><b>Все (<?=$allCnt?>)</b></a>
      </td>
    </tr>
  <? } ?>
</table>

<a name="team" id="team"></a>
<table style="width:100%; border:0" cellspacing="0" cellpadding="0">
<tr>
	<td class="brdtop" style="padding:3px 20px;">Избранные <div class="b-check b-check_float_right b-check_float_none_iphone"><input class="b-check__input" type="checkbox" name="showmyrec" value="1" <? if ($user->blocks[5]) print " checked='checked'" ?> id="ch-b4" /> <label class="b-check__label b-check__label_bold b-check__label_color_666" for="ch-b4">Показывать блок</label></div></td>
</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="19">
	<tr>
		<td style="padding:0px 19px;">
      &nbsp;
      <div class=" izbr izbr-setup">
      <?
			$recs = $recoms->teamsFavorites( $user->login, $error, true );
      $allCnt = count($recs);
      if ($recs) {
          
        //Получаем is_profi
        $ids = array();
        foreach($recs as $rec) {
            if(is_emp($rec['role'])) {
                continue;
            }
            
            $ids[] = $rec['uid'];
        }

        if($ids) {
            $recsProfi = $user->getUsersProfi($ids);
        }    
          
        $pt=0; $type = 3;
        foreach ($recs as $rec) { 
                   
            if (isset($recsProfi[$rec['uid']])) {
                $rec['is_profi'] = $recsProfi[$rec['uid']];
            } 
            
            if(count($notes[$rec['uid']]) > 0) {
                $note = $notes[$rec['uid']];
            } else {
                $note = false;
            }
            $is_emp = is_emp($rec['role']);
            $cls    = $is_emp?"emp":"frl";
            ?>
        <? if($rec['is_banned'] == 0): ?>
        <div id="n<?=$rec['uid']?>" class="izbr-item">
            <span id="elm-offset-<?=$rec['uid']?>-<?=$type?>"></span> 
            <div class="izbr-check"><input type="checkbox"  checked='checked' name="id[]" value="<?=$rec['uid']?>" /></div>
			<?= __commPrntUsrAvtr($rec)?>
			<div class="izbr-text">
                <span class="user-inf"><span class="<?=$cls?>name11"><a href="/users/<?=$rec['login']?>" class="<?=$cls?>name11" title="<?=($rec['uname']." ".$rec['usurname'])?>"><?=($rec['uname']." ".$rec['usurname'])?></a> [<a href="/users/<?=$rec['login']?>/" class="<?=$cls?>name11" title="<?=$rec['login']?>"><?=$rec['login']?></a>]</span> <?= view_mark_user($rec);?> <?= $session->view_online_status($rec['login'], false, "")?></span>
                <?php if(!is_emp($rec['role'])) {?>
                    Специализация: <?= professions::GetProfNameWP($rec['spec'], ' / ', "не указано", "lnk-666", true)?>
                <?php }//if?>
				<div class="userFav_<?=$rec['uid']?>">
				    <?php if($note === false) { ?>
                    <div class="sent-mark"><a href="javascript:void(0)" onclick="xajax_getNotesForm(<?= $rec['uid']?>, false, <?=$type?>);">Оставить заметку</a>&nbsp;<span></span></div>
                    <?php } else { //if ?>
                        <?include ("../tpl.notes-textitem.php"); ?>
                    <?php }//else ?>
				</div>
			</div>
		</div>
          <? else: ?>
          <input type="hidden"  checked='checked' name="id[]" value="<?=$rec['uid']?>" />
          <? endif; ?>
     <?   $pt = 15;
        }
      }
      ?>
      </div>
		</td>
	</tr>
</table>
<?
  $uid = get_uid();
  if(!($communes = commune::GetCommunes(NULL, $uid, NULL, commune::OM_CM_MY)))
    $communes = array();

  $commCnt = count($communes);
?>
<br>
<a name="commune" id="commune"></a>
<table style="width:100%; border:0;clear:both;" cellspacing="0" cellpadding="0">
<tr>
  <td class="brdtop" style="padding:3px 20px;">Создал сообщества (<?=$commCnt?>) <div class="b-check b-check_float_right b-check_float_none_iphone"><input class="b-check__input" type="checkbox" name="showcommune" value="1" <? if ($user->blocks[6]) print " checked='checked'" ?> id="ch-b5" /> <label class="b-check__label b-check__label_bold b-check__label_color_666" for="ch-b5">Показывать блок</label></div></td>
</tr>
</table>
      <table style="width:100%; border:0" cellspacing="0" cellpadding="0">
        <col/>
        <col/>
        <col style="width:10px"/>
        <? foreach($communes as $comm) {
              
             $i++;
             // Название.
             $name = "<a href='".getFriendlyURL("commune_commune", $comm['id'])."' class='blue' style='font-size:20px'>".reformat($comm['name'], 25, 1)."</a>";
             $descr = reformat($comm['descr'], 25, 1);
             // Сколько участников.
             $mAcceptedCnt = $comm['a_count'] - $comm['w_count'] + 1;
             $mCnt = $mAcceptedCnt.' участник'.getSymbolicName($mAcceptedCnt, 'man');
        ?>
        <tr style="vertical-align:top">
            <td style="width:200px">

              <?=__commPrntImage($comm, 'author_')?>
            </td>
            <td style="padding:0 0 0 20px">
              <div>
              <?=$name?>
              </div>
              <div><?=$descr?></div>
              <div style="margin-top:10px">
               <?=commune::GetJoinAccessStr($comm['restrict_type'], TRUE)?> 
              </div>
              <div style="margin-top:25px">
                <?=$mCnt?>
              </div>

              <div style="margin-top:4px">
                <?=__commPrntAge($comm)?>
              </div>
             </td>
            <td align="right" class="commune-lo">
				<div id="idCommRating_<?=$comm['id']?>" class="b-voting b-voting_float_right">
                            <?=__commPrntRating($comm, $uid)?>
                </div>
					<div><?=__commPrntJoinButton($comm, $uid, null, 2)?></div>
					<div id="commSubscrButton_<?=$comm['id']?>"><?=__commPrntSubmitButton($comm, $uid, null, false)?>
				</div> 
            </td>
          </tr>
          <tr><td colspan="3"><br /></td></tr>
        <? } ?>
      </table>
<?
  if(!($communes = commune::GetCommunes(NULL, NULL, $uid, commune::OM_CM_JOINED, $uid)))
    $communes = array();

  $commCnt = count($communes);
?>
<br>
<a name="commune_join" id="commune_join"></a>
<table style="width:100%; border:0;" cellspacing="0" cellpadding="0">
<tr>
  <td class="brdtop" style="padding:3px 20px;">Состоит в сообществах (<?=$commCnt?>) <div class="b-check b-check_float_right b-check_float_none_iphone"><input class="b-check__input" type="checkbox" name="showjoincommune" value="1" <? if ($user->blocks[7]) print " checked='checked'" ?> id="ch-b6" /> <label class="b-check__label b-check__label_bold b-check__label_color_666" for="ch-b6">Показывать блок</label></div></td>
</tr>
</table>
<table style="width:100%; border:0" cellspacing="0" cellpadding="19">
  <tr>
    <td style="padding:10px 0px;">
      <table style="width:100%; border:0"   cellspacing="0" cellpadding="0">
        <col/>
        <col/>
        <col style="width:10px"/>
        <? foreach($communes as $comm) {
             $i++;
             // Название.
             $name = "<a href='".getFriendlyURL("commune_commune", $comm['id'])."' class='blue' style='font-size:20px'>".reformat($comm['name'], 25, 1)."</a>";
             $descr = reformat($comm['descr'], 25, 1);
             // Сколько участников.
             $mAcceptedCnt = $comm['a_count'] - $comm['w_count'] + 1;
             $mCnt = $mAcceptedCnt.' участник'.getSymbolicName($mAcceptedCnt, 'man');
        ?>
        <!-- NEW -->
        <tr style="vertical-align:top">
            <td style="width:200px">

              <?=__commPrntImage($comm, 'author_')?>
            </td>
            <td style="padding:0 0 0 20px">
              <div>
              <?=$name?>
              </div>
              <div><?=$descr?></div>
              <div style="margin-top:10px">
               <?=commune::GetJoinAccessStr($comm['restrict_type'], TRUE)?> 
              </div>
              <div style="margin-top:25px">
                <?=$mCnt?>
              </div>

              <div style="margin-top:4px">
                <?=__commPrntAge($comm)?>
              </div>
             </td>
            <td style="text-align:right" class="commune-lo">
				<div id="idCommRating_<?=$comm['id']?>" class="b-voting b-voting_float_right">
                   <?=__commPrntRating($comm, $uid)?>
                </div>
				<div><?=__commPrntJoinButton($comm, $uid, "users/".$_SESSION['login']."/setup/info/", 2)?></div>
				<div id="commSubscrButton_<?=$comm['id']?>"><?=__commPrntSubmitButton($comm, $uid, null, false)?></div> 
			</td>
          </tr>
        <tr><td colspan="3"><br/></td></tr>
        <!-- NEW -->
        
        
        
        <?php if(false){?>
          <tr style="vertical-align:top">
            <td style="width:200px">
              <?=__commPrntImage($comm, 'author_')?>
            </td>
            <td style="padding:0 0 0 20px">
              <div>
                <?=$name?>
              </div>
              <div>
                <?=$descr?>
              </div>
              <div style="margin-top:10px">
                <?=commune::GetJoinAccessStr($comm['restrict_type'], TRUE)?>
              </div>
              <div style="margin-top:25px">
                <?=$mCnt?>
              </div>
              <div style="margin-top:4px">
                <?=__commPrntAge($comm)?>
              </div>
            </td>
            <td style="text-align:right">
              <div>
                <div id="idCommRating_<?=$comm['id']?>">
                  <?=__commPrntRating($comm)?>
                </div>
              </div>
            </td>
          </tr>
          <?php }?>
          <tr><td colspan="3"><br /></td></tr>
        <? } ?>
      </table>
    </td>
  </tr>
  <tr>
    <td style="text-align:right; vertical-align:bottom; height:50px; padding-right:19px;">
      <input type="hidden" name="action" value="inform_change" /><button type="submit" name="btn" class="b-button b-button_flat b-button_flat_green b-button_float_right">Изменить</button>
    </td>
  </tr>
</table>
</form>
<span id="noteFormContent"></span>
<style type="text/css">
.mac label[for^=ch-b]{ position:relative; top:2px;}
</style>