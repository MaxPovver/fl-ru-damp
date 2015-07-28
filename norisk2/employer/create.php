<?
$pdrd_disabled = ($sbr->scheme_type != sbr::SCHEME_PDRD && time() < strtotime('2011-01-01'));
$categories = professions::GetAllGroupsLite(true, true);
$sub_categories = professions::GetProfList();
$frl_ftype = sbr::FT_PHYS;
if($sbr->frl_id) {
    $frl = new freelancer();
    $frl->GetUserByUID($sbr->frl_id);
    if(!$sbr->frl_login) $sbr->data['frl_login'] = $frl->login;
    if($frl_reqvs = sbr_meta::getUserReqvs($frl->uid)) {
        $frl_ftype = (int)$frl_reqvs['form_type'];
        $frl_rtype = $frl_reqvs['rez_type'];
    }
}
?>
<script type="text/javascript">
Sbr.prototype.DEBUG=0;
var SBR = new Sbr('createFrm');
window.addEvent('domready', function() { SBR = new Sbr('createFrm'); } );
Sbr.prototype.CATEGORIES={<? // категории/подкатегории: {ид_кат:{имя_кат:{ид_подкат:имя_подкат,ид_подкат:...}},ид_кат:...}
foreach($sub_categories as $sc) {
    $cc = $sc['prof_group'];
    $ccname = str_replace("'", "\\'", $categories[$cc]['name']);
    $scname = str_replace("'", "\\'", $sc['name']);
    if($lcc!=$cc) {
        echo ($lcc ? '}},' : '') . "$cc:{'$ccname':{";
        $lcc = $cc;
        $j=0;
    }
    echo ($j++ ? ',' : '') . "{$sc['id']}:'{$scname}'";
}
if($lcc) echo '}}';
?>};
Sbr.prototype.ERRORS={<?
$i=0;
foreach($sbr->error as $f=>$m) {
    if($f!='stages')
        echo ($i++ ? ',' : '') . "'$f':'$m'";
    else {
        foreach($m as $num=>$errs) {
            foreach($errs as $sf=>$sm)
                echo ($i++ ? ',' : '') . "'stages[$num][$sf]':'$sm'";
        }
    }
}
?>};
Sbr.prototype.FRL_LOGIN='<?=$sbr->data['frl_login']?>';
Sbr.prototype.DYN_SEND=<?=(int)($sbr->status==sbr::STATUS_CANCELED || $sbr->status==sbr::STATUS_REFUSED)?>;
Sbr.prototype.ATTACH_SOURCE_PRJ=<?=sbr_stages::ATTACH_SOURCE_PRJ?>;
Sbr.prototype.MAX_FILES=<?=sbr::MAX_FILES?>;
Sbr.prototype.STAGE_FILES_COUNT={<?
$j=0;
foreach($sbr->stages as $i=>$s) {
    if($site=='editstage' && $s->id != $stage_id) continue;
    echo ($j++ ? ',' : '') . $i . ':' . ($s->attach ? count($s->attach) : 0);
}
?>};
Sbr.prototype.STAGE_DEADLINES={<?
$j=0;
foreach($sbr->stages as $i=>$s) {
    if($site=='editstage' && $s->id != $stage_id) continue;
    echo ($j++ ? ',' : '') . $i . ':' . ($s->dead_time ? 'new Date(' . date('Y,n-1,j',strtotime($s->dead_time)) . ')' : 'null');
}
?>};
Sbr.prototype.COST=<?
if($cst = (float)$sbr->cost) {
    foreach($sbr->stages as $s) {
        if($site=='editstage' && $s->id != $stage_id) continue;
        $cst -= $s->cost;
    }
}
echo $cst;
?>;
Sbr.prototype.RUR_SYS=<?=exrates::BANK?>;
Sbr.prototype.SCHEME_TYPE=<?=(isset($sbr->data['scheme_type']) ? $sbr->data['scheme_type'] : 'null')?>;
Sbr.prototype.SCHEMES=<?=sbr_meta::jsSchemeTaxes($sbr_schemes, $frl_reqvs, $sbr->getUserReqvs())?>;
Sbr.prototype.STAGES_COSTS=[<?
$i=0;
foreach($sbr->stages as $s) {
    echo ($i++?',':'').$s->cost;
}
?>];
SbrStage.prototype.HTML_FILE_ITEM=function(){return '<li><input name="stages['+this.num+'][attach][]" type="file" size="23" class="i-file" /></li>'};
SbrStage.prototype.MAX_WORK_TIME=<?=sbr_stages::MAX_WORK_TIME?>;
Sbr.prototype.FT_FRL=<?=$frl_ftype?>;
Sbr.prototype.RT_FRL=<?=(int)$frl_rtype?>;
Sbr.prototype.PDRD_DISABLED=<?=($sbr->reserved_id || $pdrd_disabled ? 'true' : 'false')?>;
</script>
<div class="tabs-in">
	<div class="lnk-nr-back">
        <a href=".">Вернуться в проекты по «Безопасным Сделкам»</a>
	</div>
    <h3><?=($sbr->id ? '' : 'Новая &laquo;Безопасная Сделка&raquo;')?></h3>
    <form action="?site=<?=$site?>" method="post" enctype="multipart/form-data" id="createFrm">
        <div class="form nr-form-name">
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in">
                <div class="form-block first last">
                    <div class="form-el">
                        <label class="form-label" for="sbr_name">Название проекта</label>
                        <span><input type="text" class="nr-i-name" id="sbr_name" name="name" value="<?=html_attr($sbr->data['name'])?>" maxlength="<?=sbr::NAME_LENGTH?>" onfocus="SBR.adErrCls(this)" onkeydown="return SBR.cancelEnter(event)" /></span>
                        <div class="tip tip-t2 tip7"></div>
                    </div>
                </div>
            </div>
            <b class="b2"></b>
            <b class="b1"></b>
        </div>
        <div class="form nr-form-frl">
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in">
                <div class="form-block first last">
                    <div class="form-el c">
                        <div class="nr-frl-info" id="frlbx">
                          <?=sbr_meta::view_frl($frl)?>
                        </div>
                        <? if($site=='create' || $sbr->isDraft() || $sbr->data['status']==sbr::STATUS_CANCELED || $sbr->data['status']==sbr::STATUS_REFUSED) { ?>
                        <label class="form-label" for="frl_login">Исполнитель</label>
                        <span><input type="text" id="frl_login" name="frl_login" value="<?=($sbr->data['frl_login_added'] ? $sbr->data['frl_login_added'] : ($sbr->data['frl_login'] ? html_attr($sbr->data['frl_login']) : 'логин'))?>" onfocus="SBR.onfrlfocus(this);this.select()" onkeydown="if(event.keyCode==13){SBR.addFrl();return false;}" onblur="SBR.onfrlblur(this)" class="nr-i-login" />
                        <input type="button" class="i-btn" value="<?=($frl->uid ? 'Сменить' : 'Добавить')?>" onclick="SBR.addFrl()"/></span>
                        <div class="tip tip-t2" style="left:160px;top:14px;z-index:1"></div>
                        <? } ?>
                    </div>
                </div>
                <div class="form-block last"<?=($frl && !$frl_rtype ? '' : ' style="display:none"')?> id="unknown_frl_rez">
                    <div class="form-el">
                        <span class="dred">Обратите внимание, исполнитель не указал свое резиденство. Для резидентов Республики Беларусь, Республики Казахстан или Украины действует особое ограничение &mdash;
                        максимальный бюджет задачи не может превышать <?=sbr_meta::view_cost($sbr->maxNorezCost(), exrates::BANK)?> (эквивалент <?=sbr::MAX_COST_USD?> USD). Для остальных нерезидентов Российской Федерации «Безопасная Сделка» недоступна.</span>
                    </div>
                </div>
            </div>
            <b class="b2"></b>
            <b class="b1"></b>
        </div>
        <? 
          foreach($sbr->stages as $num=>$stage)
          { 
              if($site=='editstage' && $stage->id != $stage_id) continue;
        ?>
        <fieldset class="nr-task">
            
            <legend>Задача №<a class="nr-task-anchor" name="stage<?=($num+1)?>" innum="<?=$num?>"><?=($num+1)?></a> <? // !!! переименовать якорь ?>
              <? if($site=='create' || $sbr->isDraft()) { // !!! !$sbr->reserved_id || ?>
                <span id="delstage_box<?=$num?>"<?=($num || $sbr->stages_cnt > 1 ? '' : ' style="display:none"')?>>(<a href="javascript:;">удалить</a>)</span>
              <? } ?>
            </legend>
            <div class="form">
                <b class="b1"></b>
                <b class="b2"></b>
                <div class="form-in">
                    <div class="form-block first">
                        <div class="form-el">
                            <label for="name_task" class="form-label">Название задачи:</label>
                            <span class="fprm-p"><input type="text" name="stages[<?=$num?>][name]" id="name_task" class="nr-i-taskname" value="<?=html_attr($stage->data['name'])?>" maxlength="<?=sbr_stages::NAME_LENGTH?>" onfocus="SBR.adErrCls(this)" onkeydown="return SBR.cancelEnter(event)"/></span>
                            <div class="tip tip-t2 tip5"></div>
                        </div>
                        <div class="form-el">
                            <label for="razdel-choose" class="form-label">Раздел:</label>
                            <span class="nr-task-cat">
                                <span>
                                  <select id="razdel-choose" name="stages[<?=$num?>][category]" onchange="SBR.getStageByItem(this).changeCat(this.value)">
                                  <option value="0">&lt;Выберите раздел&gt;</option>
                                  <? foreach($categories as $cc) { ?>
                                     <option value="<?=$cc['id']?>"<?=($cc['id']==$stage->data['category'] ? ' selected="selected"' : '')?>><?=$cc['name']?></option>
                                  <? } ?>
                                  </select>
                                </span>
                                <span>
                                  <select name="stages[<?=$num?>][sub_category]" id="<?=$stage->data['sub_category']?>">111<?/* JS */?></select>
                                </span>
                            </span>
                        </div>
                        <div class="form-el">
                            <label for="descr-task" class="form-label">Описание задачи:</label>
                            <div class="nr-task-info">
                                <span><textarea id="descr-task" rows="5" cols="5" name="stages[<?=$num?>][descr]" onfocus="try{SBR.adErrCls(this)}catch(e){}"><?=$stage->data['descr']?></textarea></span>
                                <div class="tip tip-t2 tip4"></div>
                                <!-- Прикрепленные файлы -->
                                <div class="form form-files">
                                    <b class="b1"></b>
                                    <b class="b2"></b>
                                    <div class="form-in">
                                        <div class="form-block first last">
                                            <div class="form-el">
                                                <div class="flt-<?=($stage->data['attach']||$sbr->error['stages'][$num]['err_attach'] ? 'show' : 'hide')?>" id="nr-files1">
                                                    <div class="form-files-tglbar">
                                                        <a href="javascript: void(0);" class="flt-tgl-lnk lnk-dot-blue">Прикрепленные файлы (<?=($stage->data['attach'] ? 'с' : 'раз')?>вернуть)</a>
                                                    </div>
                                                    <div class="flt-cnt с">
																												
																														<ul class="form-files-added">
																																<? if($stage->data['attach']) foreach($stage->data['attach'] as $id=>$a) { ?>
																																		<? if($a['source_type']==sbr_stages::ATTACH_SOURCE_PRJ) { ?>
																																				<input type="hidden" name="stages[<?=$num?>][project_attach][<?=$id?>]" value="<?=$a['file_id']?>"/>
																																		<? } if($a['source_type']==sbr_stages::ATTACH_SOURCE_OLD) { ?>
																																				<input type="hidden" name="stages[<?=$num?>][del_attach][<?=$id?>]" value="<?=$a['is_deleted']?>"/>
																																		<? } if($a['is_deleted']!='t') { ?>
																																				<li>
																																						<a href="javascript:;" onclick="SBR.getStageByItem(this).delAttach(this, <?=(int)$id?>)">
																																							<img src="/images/btn-remove2.png" alt="Удалить">
																																						</a>
																																						<a href="<?=WDCPREFIX.'/'.$a['path'].$a['name']?>" target="_blank"><?=($a['orig_name'] ? $a['orig_name'] : $a['name'])?></a>
																																				</li>
																																		<? } ?>
																																<? } ?>
																														</ul>
                                                        
                                                        
                                                        <ul class="form-files-list"><?/* JS */?></ul>
                                                        <div class="form-files-inf" style="position: relative">
                                                            <span><input type="hidden" name="stages[<?=$num?>][err_attach]" /></span><div class="tip tip-t2" style="top:2px;left:0px;z-index:1"></div>
                                                            <p>
                                                            Вы можете прикрепить к сообщению:<br />
                                                            <strong>Файл:</strong> <?=sbr::MAX_FILE_SIZE/1024/1024?> Мб.<br />
                                                            <strong>Картинку</strong>: 600x1000 пикселей, 300 Кб.<br />
                                                            Файлы следующих форматов запрещены к загрузке: <?=implode(', ', $GLOBALS['disallowed_array'])?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <b class="b2"></b>
                                    <b class="b1"></b>
                                </div>
                                <!-- конец Прикрепленные файлы -->
                            </div>
                        </div>
                    </div>
                    <div class="form-block last" style="height:auto">
                        <div class="nr-imp">
                            <h4>Минимальный бюджет — <?=sbr_stages::MIN_COST_RUR?> руб.</h4>
                        </div>
                        <div class="form-el form-s-el">
                            <label for="budjet-prj" class="form-label">Стоимость работы, в т.ч. НДС:</label>
                            <span>
                                <input id="budjet-prj" name="stages[<?=$num?>][cost]" type="text" style="width:120px"<?=($sbr->reserved_id ? ' disabled="disabled"' : ' onchange="SBR.getStageByItem(this).changeCost(this.value)"')?> value="<?=html_attr($stage->data['cost'])?>" maxlength="12" onfocus="SBR.adErrCls(this); SBR.adErrCls($('cost_sys_err_tbl'))" onkeydown="return SBR.cancelEnter(event)"/>
                                <? if($num == 0) { ?>
                                  <input type="hidden" name="cost_sys_err" />
                                <? } ?>
                                <select name="cost_sys[<?=$num?>]"<?=($num>0 || $sbr->reserved_id ? ' disabled="disabled"' : ' onchange="SBR.changeSys()" onfocus="SBR.adErrCls(this);"')?>>
                                <? foreach($EXRATE_CODES as $id=>$ex) { 
                                       if($id==exrates::FM || $id==exrates::WMZ) continue;
                                       if(($id==exrates::YM || $id==exrates::WMR) && $sbr->user_reqvs['form_type']==sbr::FT_JURI) continue;
                                ?>
                                   <option value="<?=$id?>"<?=($sbr->cost_sys==$id ? ' selected="selected"' : '')?>><?=$ex[0]?></option>
                                <? } ?>
                                </select><span></span>
                            </span>
                            <div class="tip" style="left:393px"></div>
                            <div class="nr-imp norez_maxcost_block"<?=($frl_rtype!=sbr::RT_UABYKZ ? ' style="display:none"' : '')?>>
                                <h4>Максимальный бюджет &mdash; <?=sbr_meta::view_cost($sbr->maxNorezCost(), exrates::BANK)?>, поскольку выбранный исполнитель не является резидентом Российской Федерации</h4>
                            </div>
                        </div>
                        <div class="form-el">
                            <label for="itogo-pay" class="form-label3">Итого к оплате:</label>
                            <span>
                                <input id="itogo-pay" type="text" style="width:120px" name="stages[<?=$num?>][cost_total]"<?=($sbr->reserved_id ? ' disabled="disabled"' : ' onchange="SBR.getStageByItem(this).changeCostTotal(this.value)"')?> value="<?=html_attr($stage->data['cost_total'])?>" maxlength="12" onfocus="SBR.adErrCls(this); SBR.adErrCls($('cost_sys_err_tbl'))" onkeydown="return SBR.cancelEnter(event)"/>
                                
                                <span>рублей</span>
                            </span>
                        </div>
                        <div class="nr-imp" style="margin-top:-20px">
                            <h4>Отсчет времени начинается с момента резервирования денежных средств</h4>
                        </div>
                        <div class="form-el">
                            <? if(!$sbr->data['reserved_id'] || !$stage->data['dead_time']) { ?>
                                <label for="time-lavel" class="form-label">Время на этап:</label>
                                <span>
                                    <input id="time-lavel" name="stages[<?=$num?>][work_time]" type="text" size="7" value="<?=($stage->data['work_days'] ? html_attr($stage->data['work_days']) : '')?>" maxlength="3" onfocus="SBR.adErrCls(this)" onkeydown="return SBR.cancelEnter(event)"/>
                                    (дней)
                                </span>
                                <div class="tip tip-t2" style="top:12px;left:160px"></div>
                            <? } else { ?>
                                <label for="srok-task" class="form-label">Срок задачи:</label>
                                <span class="nr-diedline">
                                    <input id="srok-task" type="text" size="2" name="stages[<?=$num?>][dead_day]" value="<?=date('j',strtotime($stage->data['dead_time']))?>" maxlength="2" onchange="SBR.getStageByItem(this).setWTime()" onkeydown="return SBR.cancelEnter(event)"/>
                                    <select name="stages[<?=$num?>][dead_month]" onchange="SBR.getStageByItem(this).setWTime()">
                                        <? foreach($MONTHA as $idx=>$m) { ?>
                                          <option value="<?=$idx-1?>"<?=($idx==date('n',strtotime($stage->data['dead_time'])) ? ' selected="selected"' : '')?>><?=$m?></option>
                                        <? } ?>
                                    </select>
                                    <input name="stages[<?=$num?>][dead_year]" type="text" size="4" value="<?=date('Y',strtotime($stage->data['dead_time']))?>" maxlength="4" onchange="SBR.getStageByItem(this).setWTime()" onkeydown="return SBR.cancelEnter(event)" />
                                    <br /><br />
                                    <select style="width: 121px" name="stages[<?=$num?>][add_wt_switch]" onchange="SBR.getStageByItem(this).setWTime(null,1)">
                                      <option value="+"<?=($stage->data['add_wt_switch']=='+' ? ' selected="selected"' : '')?>>Добавить</option>
                                      <option value="-"<?=($stage->data['add_wt_switch']=='-' ? ' selected="selected"' : '')?>>Отнять</option>
                                    </select>
                                    <input name="stages[<?=$num?>][add_work_time]" type="text" size="4" value="<?=html_attr($stage->data['add_work_time'])?>" maxlength="3" onfocus="SBR.adErrCls(this)" onchange="SBR.getStageByItem(this).setWTime(this.value)" onkeyup="SBR.getStageByItem(this).setWTime(this.value, null, true)" onkeydown="return SBR.cancelEnter(event)"/> (дней)
                                </span>
                            <? } ?>
                        </div>
                    </div>
                </div>
                <b class="b2"></b>
                <b class="b1"></b>
            <input type="hidden" name="stages[<?=$num?>][id]" value="<?=$stage->data['id']?>" />
            </div>
                        <? if($site!='editstage' && ($site=='create' || $sbr->isDraft())) { // !!! ?>
                            <div class="form-el-btn">
                                <input type="button" class="i-btn" value="+ Добавить еще одну задачу" onclick="SBR.addStage()"/>
                            </div>
                        <? } ?>
        </fieldset>
        <? } ?>
        <div class="form form-nr-scheme">
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in">
                <div class="form-block first">
                    <div class="form-el form-el-resident">
                        <ul class="form-list">
                            <li>
                                <label><input name="rez_type" type="radio" value="<?=sbr::RT_RU?>" class="i-radio"<?=($rt_disabled && $rez_type && $rez_type != sbr::RT_RU ? ' disabled="disabled"' : '' )?><?=($rt_checked && $rez_type == sbr::RT_RU ? ' checked="checked"' : '' )?>
                                onclick="SBR.changeEmpRezType(<?=sbr::RT_RU?>)"/>
                                  Я подтверждаю, что являюсь резидентом Российской Федерации
                                </label>
                            </li>
                            <li>
                                <label><input name="rez_type" type="radio" value="<?=sbr::RT_UABYKZ?>" class="i-radio"<?=($rt_disabled && $rez_type && $rez_type != sbr::RT_UABYKZ ? ' disabled="disabled"' : '' )?><?=($rt_checked && $rez_type == sbr::RT_UABYKZ ? ' checked="checked"' : '' )?>
                                onclick="SBR.changeEmpRezType(<?=sbr::RT_UABYKZ?>)"/>
                                  Я подтверждаю, что являюсь резидентом любого другого государства, кроме Российской Федерации
                                </label>
                                <div class="form fs-o form-resident-inf"<?=($rt_checked && $rez_type == sbr::RT_UABYKZ ? '' : ' style="display:none"' )?> id="norez_info">
                                    <b class="b1"></b>
                                    <b class="b2"></b>
                                    <div class="form-in">
                                        Максимальная сумма сделки составляет <?=sbr::MAX_COST_USD?> USD (<?=sbr_meta::view_cost($sbr->maxNorezCost(), exrates::BANK)?>)<br />
                                    </div>
                                    <b class="b2"></b>
                                    <b class="b1"></b>
                                </div>
                                
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="form-block last">
                    <div class="form-el c">
                        <ul class="form-nr-scheme-ul">
                            <? if($sbr->scheme_type==sbr::SCHEME_OLD) { ?>
                              <li><input type="radio" name="scheme_type" value="<?=sbr::SCHEME_OLD?>" onclick="SBR.changeSchemeType(this.value)"<?=($sbr->reserved_id ? ' disabled="disabled"' : '')?> checked="checked" /><?=sbr::$scheme_types[sbr::SCHEME_OLD][0]?></li>
                            <? } ?>
                            <li><input type="radio" id="scheme_type<?=sbr::SCHEME_AGNT?>" name="scheme_type" value="<?=sbr::SCHEME_AGNT?>" onclick="SBR.changeSchemeType(this.value)"<?=($sbr->reserved_id ? ' disabled="disabled"' : '')?><?=($sbr->scheme_type==sbr::SCHEME_AGNT ? ' checked="checked"' : '')?> /><a href="<?=sbr::$scheme_types[sbr::SCHEME_AGNT][1]?>" target="_blank"><?=sbr::$scheme_types[sbr::SCHEME_AGNT][0]?></a></li>
                            <li><input type="radio" id="scheme_type<?=sbr::SCHEME_PDRD?>" name="scheme_type"<?=($site == 'create' || $sbr->scheme_type==sbr::SCHEME_OLD)?> value="<?=sbr::SCHEME_PDRD?>" onclick="SBR.changeSchemeType(this.value)"<?=($sbr->reserved_id || $pdrd_disabled ? ' disabled="disabled"' : '')?><?=($sbr->scheme_type==sbr::SCHEME_PDRD ? ' checked="checked"' : '')?> /><a href="<?=sbr::$scheme_types[sbr::SCHEME_PDRD][1]?>" target="_blank"><?=sbr::$scheme_types[sbr::SCHEME_PDRD][0]?></a>
                            <span><input type="hidden" name="scheme_type_err" /></span>
                            <div class="tip tip-t2" style="left:17px;top:20px;z-index:1"></div>
                            <? if($pdrd_disabled) { ?>
                              <span style="color:gray">(в связи со сменой ставки налогов договором подряда можно будет воспользоваться с 1 января)</span>
                            <? } ?>
                            </li>
                        </ul>
                        <div class="form-nr-scheme-tbl">
                            
                            <? foreach($sbr_schemes as $sch) { ?>
                            <table style="display:none" id="sch_<?=$sch['type']?>">
                                <col width="406" />
                                <col width="125" />
                                <col width="100" />
                                <tr>
                                    <th>Стоимость работы, в т.ч. НДС</th>
                                    <td style="width: 125px;">&mdash;</td>
                                    <td class="col-sum" id="sch_<?=$sch['type']?>_f"><?=(float)$sbr->data['cost']?></td>
                                </tr>
                                <? foreach($sch['taxes'][1] as $id=>$tax) { $s=$e=''; if($id==sbr::TAX_NDS) {$s='<strong>';$e='</strong>';}  ?>
                                    <tr id="taxrow_<?=$sch['type'].'_'.$id?>">
                                        <th<?=$id=='t' ? '  class="nr-sheme-sum"' : ''?>><?=$s?><?=$tax['name']?><?=$e?></th>
                                        <td><?=$s?><?=($tax['percent'] ? '<span id="taxper_'.$sch['type'].'_'.$id.'"></span>%' : '&mdash;')?><?=$e?></td>
                                        <td class="col-sum <?=$id=='t' ? ' nr-sheme-sum' : ''?>"  id="taxsum_<?=$sch['type']?>_<?=$id?>"<?=$s ? ' style="font-weight:800"' : ''?>>&nbsp;</td>
                                    </tr>
                                <? } ?>
                            </table>
                            <? } ?>
                            <span><input type="hidden" name="cost_sys_err_tbl" id="cost_sys_err_tbl"/></span>
                            <div class="tip tip-t2 tip-t2-r" style="right:92px;top:20px;z-index:10;top:"></div>
                        </div>
                    </div>
                </div>
            </div>
            <b class="b2"></b>
            <b class="b1"></b>
        </div>
        <?=$sbr->view_sign_alert()?>
        <div class="form nr-form-budjet">
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in">
                <div class="form-block first last">
                    <div class="form-el">
                        <h4>Обратите внимание!</h4>
                        <p>Резервирование денежных средств происходит после согласования условий с исполнителем. Резервирование денежных средств для «Безопасной Сделки» не может быть осуществлено с помощью личного счета в FM.</p>
                    </div>
                </div>
            </div>
            <b class="b2"></b>
            <b class="b1"></b>
        </div>
        <div class="form nr-form-btns">
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in">
                <div class="form-block first last">
                    <div class="form-el">
                        <p id="schalert<?=sbr::SCHEME_AGNT?>" style="display:none">Отправляя данное Техническое задание на утверждение Исполнителю путем нажатия на кнопку &laquo;Отправить на утверждение исполнителю&raquo;, вы предлагаете Исполнителю заключить Соглашение о выполнении работы и/или оказании услуги с использованием онлайн сервиса &laquo;Безопасная Сделка&raquo;. Текст Соглашения расположен на Сайте Free-lance.ru в сети Интернет по адресу: <a href="/agreement_escrow.pdf" target="_blank"><nobr><?=HTTP_PREFIX?>www.free-lance.ru/agreement_escrow.pdf</nobr></a>.<br/><br/>
                          Настоящим Сайт Free-lance.ru (ООО "Ваан") предлагает Оферту на заключение Договора об использовании онлайн сервиса &laquo;Безопасная Сделка&raquo;. Текст Оферты на заключение Договора об использовании онлайн сервиса &laquo;«Безопасная Сделка»&raquo; расположен на Сайте Free-lance.ru в сети Интернет по адресу: <a href="<?=sbr::$scheme_types[sbr::SCHEME_AGNT][1]?>" target="_blank"><nobr><?=sbr::$scheme_types[sbr::SCHEME_AGNT][1]?></nobr></a>. Нажимая на кнопку &laquo;Отправить на утверждение исполнителю&raquo;, вы принимаете условия Оферты на заключение Договора об использовании онлайн сервиса &laquo;Безопасная Сделка&raquo;.</p>
                        <p id="schalert<?=sbr::SCHEME_PDRD?>" style="display:none">Отправляя данное Техническое задание на утверждение Исполнителю путем нажатия на кнопку &laquo;Отправить на утверждение исполнителю&raquo;, вы заключаете Соглашение о выполнении работы и/или оказании услуги с использованием онлайн сервиса &laquo;Безопасная Сделка&raquo;. Текст Соглашения расположен на Сайте Free-lance.ru в сети Интернет по адресу: <a href="/offer_work_employer.pdf" target="_blank"><nobr><?=HTTP_PREFIX?>www.free-lance.ru/offer_work_employer.pdf</nobr></a>. </p>
                        <input type="submit" name="send" class="i-btn nr-btn-send" value="Отправить на утверждение исполнителю" <?=(!$rt_checked ? ' disabled="disabled"' : '')?> />
                        <? if($sbr->status==sbr::STATUS_CANCELED || $sbr->status==sbr::STATUS_REFUSED) { ?>
                          <input type="submit" name="save" class="i-btn nr-btn-send" value="&nbsp;Сохранить&nbsp;" <?=(!$rt_checked ? ' disabled="disabled"' : '')?> />
                        <? } if($site!='editstage' && ($site == 'create' || $sbr->isDraft())) { ?>
                          <input type="submit" name="draft" class="i-btn" value="Сохранить как черновик"<?=(!$rt_checked ? ' disabled="disabled"' : '')?> />
                        <? } else { ?>
                          <input type="submit" name="cancel" class="i-btn" value="Отменить" />
                        <? } ?>
                    </div>
                </div>
            </div>
            <b class="b2"></b>
            <b class="b1"></b>
        <? if($sbr->data['delstages']) { foreach ($sbr->data['delstages'] as $id=>$d)?>
          <input type="hidden" name="delstages[<?=$id?>]" value="<?=$id?>" />
        <? } ?>
        <? if($site == 'create') { ?>
          <input type="hidden" name="project_id" value="<?=$sbr->project_id?>" />
        <? } ?>
        <? if($site != 'create') { ?>
          <input type="hidden" name="id" value="<?=$sbr->id?>" />
        <? } ?>
        <? if($site == 'editstage') { ?>
          <input type="hidden" name="stage_id" value="<?=$stage_id?>" />
        <? } ?>
        <? if($version) { ?>
          <input type="hidden" name="v" value="<?=$version?>" />
        <? } ?>
        <input type="hidden" name="site" value="<?=$site?>" />
        <input type="hidden" name="action" value="<?=$site?>" />
        </div>
    </form>
</div>
