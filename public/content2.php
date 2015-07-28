<?
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/public.common.php");
  $xajax->printJavascript('/xajax/');
  
  $addedPrc = is_pro() ? 0 : new_projects::PRICE_ADDED ;
  
  $category = professions::GetGroup($project['category'], $error);
  $logo = $tmpPrj->getLogo();
  $colorPrc = (!false) * new_projects::PRICE_COLOR * (1-(int)($project['payed_items'][new_projects::PAYED_IDX_COLOR] || $project['is_pro']=='t'));
  $boldPrc = (!false) * (new_projects::PRICE_BOLD + $addedPrc) * (1-(int)$project['payed_items'][new_projects::PAYED_IDX_BOLD]);
  $logoPrc = (!false) * (new_projects::PRICE_LOGO + $addedPrc) * (1-(int)$project['payed_items'][new_projects::PAYED_IDX_LOGO]);
  $topDays = $tmpPrj->getTopDays();
  $remTPeriod = $tmpPrj->getRemainingTopPeriod($remTD, $remTH, $remTM, $remtverb);
  $addedTD = $tmpPrj->getAddedTopDays();
  $pex = project_exrates::GetAll(false);
  $cex = array(project_exrates::USD, project_exrates::EUR, project_exrates::RUR, project_exrates::FM);
  $PROprice = 0;
  $price = $tmpPrj->getPrice($items, $PROprice) + $logoPrc * (!!$error['logo']);
  $PROprice += ($logoPrc - $addedPrc )  * (!!$error['logo']);
  $contestPriceTop = (is_pro()?new_projects::PRICE_CONTEST_TOP1DAY_PRO:new_projects::PRICE_CONTEST_TOP1DAY);
  $nTopPrice = ( $tmpPrj->isKonkurs() ) ? ( $contestPriceTop + $addedPrc ) : ( (is_pro()?new_projects::PRICE_TOP1DAYPRO:new_projects::PRICE_TOP1DAY) + $addedPrc );
  $nTopProPrice = ( $tmpPrj->isKonkurs() ) ? ( new_projects::PRICE_CONTEST_TOP1DAY_PRO ) : ( new_projects::PRICE_TOP1DAYPRO );
  
  if (isset($project['descr'])) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
    $stop_words = new stop_words( false );
    
    $project['descr'] = $project['kind'] != 4 && !is_pro() ? $stop_words->replace($project['descr']) : $project['descr'];
    $project['descr'] = preg_replace("/^ /", "\x07", $project['descr']);
    $project['descr'] = preg_replace("/(\n) /", "$1\x07", $project['descr']);
    $project['descr'] = reformat($project['descr'], 100, 0, 0, 1);
    $project['descr'] = preg_replace("/\x07/", "&nbsp;", $project['descr']);
  }

  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
  $project_exRates = project_exrates::GetAll();
  $exch = array(1=>'FM', 'USD','Euro','Руб');
  $translate_exRates = array
  (
  0 => 2,
  1 => 3,
  2 => 4,
  3 => 1
  );
?>
<script>
var ge=function(id){return document.getElementById(id);}
var S=<?=round($price);?>;
var PS=<?=round($PROprice);?>;
var TS=null;
var PTS=null;
var AS=<?=round($account->sum,2)?>;
var BS=<?=round($account->bonus_sum,2)?>;

function setbold(_th) {
  if(bldPrvw=$('colorPrvw')) {
    $('colorPrvw').removeClass('prj-weight');
    if (_th.checked) {
        $('colorPrvw').addClass('prj-weight');
    }
  }
  recalc((_th.checked?1:-1)*<?=round($boldPrc)?>);
  recalcPRO((_th.checked?1:-1)*<?= round($boldPrc - $addedPrc)?>);
}
function setcolor(_th) {
  if(clrPrvw=$('colorPrvw')) {
    if (_th.checked) {
        $('colorPrvw').addClass('prj-colored');
        $('colorPrvw').removeClass('prj-usual');
    } else {
        $('colorPrvw').removeClass('prj-colored');
        $('colorPrvw').addClass('prj-usual');
    }
  }
  recalc((_th.checked?1:-1)*<?= round($colorPrc)?>);
  recalcPRO((_th.checked?1:-1)*<?= round($colorPrc - $colorPrc)?>);
}
function oktop(_th) {
  if(tainp=ge('topAInp')) {
    tainp.disabled=!_th.checked;
    if ($('istop')) {
        (tainp.disabled) ? $('istop').hide() : $('istop').show();
    }
    var s=(_th.checked?1:-1)*uiv(tainp.value)*<?=round($nTopPrice);?>;
    recalc(s);
    recalcPRO((_th.checked?1:-1)*uiv(tainp.value)*<?= round($nTopProPrice)?>);
  }
}
function addtop(_th,tsdef) {
  _th.value=uiv(_th.value);
  var s=_th.value*<?=round($nTopPrice);?>;
  if(TS===null)TS=tsdef*<?= round($nTopPrice)?>;
  var ps=_th.value*<?=round($nTopProPrice)?>;
  if(PTS===null)PTS=tsdef*<?=round($nTopProPrice)?>;
  recalc(s-TS);
  recalcPRO(ps-PTS);
  TS=s;
  PTS=ps;
  if(tsm=ge('topSum'))
    tsm.innerHTML=s;
}
function adlogo() {
  if(el=ge('logoBox'))el.style.display='none';
  if(el=ge('logoPrvw'))el.innerHTML = '<img src="/images/yourlogo.png" alt="Ваш логотип и ссылка на сайт" />';
  if(el=ge('logoInp'))el.style.display='block';
  if(el=ge('linkInp'))el.style.display='block';
  if(el=ge('logoCbx'))el.disabled=false;
}
function setlogo(_th) {
  if((lginp=ge('logoInp'))) {
    lgPrvw=ge('logoPrvw');
    linp=ge('linkInp');
    if(_th.checked) {
      lginp.style.display='block';
      linp.style.display='block';
      if(lgPrvw)lgPrvw.style.display='block';
      recalc(<?=round($logoPrc)?>);
      recalcPRO(<?= round($logoPrc-$addedPrc)?>);
    }
    else {
      lginp.style.display='none';
      linp.style.display='none';
      if(lgPrvw)lgPrvw.style.display='none';
      recalc(-<?=round($logoPrc)?>);
      recalcPRO(-(<?= round($logoPrc-$addedPrc)?>));
    }
    if(lgerr=ge('logoErr'))
      lgerr.parentNode.removeChild(lgerr);
  }
}
function recalc(s) {
  S+=s;
  if(el=ge('sum_fm'))el.innerHTML=S;
  if(el=ge('sum_rur'))el.innerHTML=mny(S*<?=$pex[project_exrates::FM.project_exrates::RUR]?>);
  if(el=ge('sum_usd'))el.innerHTML=mny(S*<?=$pex[project_exrates::RUR.project_exrates::USD]?>);

    <? if(!$project['payed'] && !$tmpPrj->isKonkurs() && $project['kind']!=4) { ?>
      if((pt=ge('ptype')))
        pt.innerHTML=(S>0?'Платное':'Бесплатное')+' объявление';
    <? } ?>
    ge('payedBox').style.display=S>0?'block':'none';
    ge('noPayedBox').style.display=S>0?'none':'block';
    ge('nomnyBox').style.display=(S>AS&&S>BS)?'block':'none';
    ge('nomnySum').innerHTML=mny(S-AS);
    ge('payBtn').disabled=(S>AS&&S>BS);

}

function recalcPRO(s) {
    var nothing = 'не будут вам стоить ничего';
    PS = String(PS).replace(nothing, '');
    PS = Number(PS) + s;
    if(el=ge('sum_fm_pro')) {
        var prefix = 'будут стоить всего ';
        var postfix = ' рублей';
        if ( parseInt(PS) == 0 ) {
        	prefix = '';
        	postfix = '';
        	PS = nothing;
        }
        el.innerHTML=PS;
        $('sum_fm_pro_prefix').set("text", prefix);
        $('sum_fm_pro_postfix').set("text", postfix);
    }
}


function mny(s){return((Math.round(s*100)/100));}
function uiv(v) {
  var nv=v.replace(/^[^-1-9]+/,'').replace(/[^0-9]+$/,'');
  nv=isNaN(nv-0)?0:nv-0;
  return nv>0?nv:0;
}
function cibywheel(obj,low,up,dir) {
  event.returnValue=false;
  if(!dir)dir=1;
  var cv=obj.value-0,dlt=event.wheelDelta;
  if(isNaN(cv))obj.value=0;
  else if(cv==low&&dlt<0||cv==up&&dlt>0);
  else obj.value=cv+(dlt>0?dir:-dir);
}
</script>
<h1 class="b-page__title">Предпросмотр</h1>
<div class="add-project-preview">
<form action="/public/" method="POST" enctype="multipart/form-data" id="publicForm" onsubmit="ge('freeBtn').disabled=true;ge('payBtn').disabled=true">
  <h3 id="ptype"><?=(($project['payed']||$tmpPrj->isKonkurs()||$price||$project['kind']==4) ? 'Платное' : 'Бесплатное')?> объявление<?=($tmpPrj->isKonkurs() ? ' (конкурс)' : '')?></h3>
  <div class="app-left">
    <p>У платных объявлений на порядок <br />больше просмотров и ответов.<br /> Обычно фрилансеры воспринимают <br />платное объявление более серьезно.</p>
    <ul class="apf-list app-list">
        <li <?=(($project['kind'] == 7 || ($project['kind'] == 4 && is_pro() == false)) ? '' : 'style="display: none;"')?>><input type="checkbox" name="public" value="1" id="public" disabled="disabled" checked="checked" /> <label for="public">Публикация проекта &mdash; <?= round($project['kind'] == 4 ? new_projects::getProjectInOfficePrice() : new_projects::getKonkursPrice()); ?> руб.</label></li>
        <li>
          <input type="checkbox" id="f4" name="top_ok" value="1" onchange="oktop(this)"<?=($remTPeriod||$addedTD ? ' checked' : '').($remTPeriod ? ' disabled' : '')?>/>
          <label for="f4" class="clip">Закрепить наверху ленты</label>
          <p class="app-dayes">
            <? if(!$remTPeriod) { ?>
              на <input type="text" size="2" onmousewheel="cibywheel(this,0,365);addtop(this,<?=($addedTD ? $addedTD : 1)?>);" onchange="addtop(this,<?=($addedTD ? $addedTD : 1)?>)" id="topAInp" name="top_days" value="<?=($addedTD ? $addedTD : 1)?>"<?=(!$addedTD ? ' disabled' : '')?> />
              дней = <span id="topSum"><?=round(($addedTD ? $addedTD : 1) * $nTopPrice)?></span> руб.
            <? } else { ?>
              Вы закрепили объявление на <?=$topDays.' '.getSymbolicName($topDays,'day')?>.
              <?=$remtverb.' '.$remTPeriod?>
            <? } ?>
          </p>
        </li>
        <? if($remTPeriod) { ?>
          <li>
            <input type="checkbox" id="f5" name="top_ok" value="1" onchange="oktop(this)"<?=($addedTD ? ' checked' : '')?>/>
            <label for="f5">Продлить на</label>
            <input type="text" size="2" onmousewheel="cibywheel(this,0,365);addtop(this,<?=($addedTD ? $addedTD : 0)?>);" onchange="addtop(this,<?=($addedTD ? $addedTD : 0)?>)" id="topAInp" name="top_days" value="<?=$addedTD?>" <?=(!$addedTD ? ' disabled' : '')?>/>
            дней = <span id="topSum"><?= round( ($addedTD ? $addedTD : 0) * $nTopPrice)?></span> руб.
          </li>
        <? } ?>
        <li><input type="checkbox" name="is_color" value="1" id="f1" onchange="setcolor(this)"<?=($project['is_color']=='t' ? ' checked' : '')?>/> <label for="f1">Выделить цветом<?=($colorPrc ? " &ndash; " . round($colorPrc) . " руб." : '')?></label></li>
        <li><input type="checkbox" name="is_bold" value="1" id="f3" onchange="setbold(this)"<?=($project['is_bold']=='t' ? ' checked' : '')?>/> <label for="f3">Выделить жирным<?=($boldPrc ? " &ndash; " . round($boldPrc) . " руб." : '')?></label></li>

      <li><input type="checkbox" name="logo_ok" id="logoCbx" onchange="setlogo(this)"<?=($logo||$error['logo'] ? ' checked' : '')?><?=($logo ? ' disabled' : '')?>/> <label for="logoCbx">Загрузить логотип со ссылкой<?=($logoPrc ? " &ndash; " . round($logoPrc) . " руб." : '')?></label>
        <p class="app-logo" style="margin:10px 0 0 5px<?=($error['logo'] ? '' : ';display:none')?>" id="logoInp">
            <strong>Логотип:</strong><br />
            <input type="file" name="logo" size="17" /><br />
            Не более <?=(new_projects::LOGO_SIZE/1024)?> Кб.<br/><?=new_projects::LOGO_WIDTH?> пикселей в ширину, до <?=new_projects::LOGO_HEIGHT?> в высоту (gif, jpeg, png).<br/>
            <?=($error['logo'] ? '<span id="logoErr"><br/><img src="/images/ico_error.gif" alt="" width="22" height="18" border="0"/>&nbsp;'.$error['logo'].'<br/></span>' : '')?>
            <br/><input onclick="document.getElementById('publicForm').action.value='reload'"type="submit" value=" Загрузить " />
        </p>
        <? if($logo) { ?>
          <p id="logoBox" class="app-logo" style="margin-left:5px">
            <strong>Загруженный логотип:</strong><br/>
            <a href="<?=WDCPREFIX?>/<?=$logo['path'].$logo['name']?>" target="_blank">Посмотреть</a> (<?=$logo['ftype']?>; <?=ConvertBtoMB($logo['size'])?>)
            <span class="lnk-del"><a href="javascript:;" onclick="xajax_DelLogo('<?=$key?>')">удалить</a>
          </p>
        <? } ?>
        <p class="app-logo" style="margin:10px 0 0 5px<?=($logo||$error['logo'] ? '' : ';display:none')?>" id="linkInp">


          <strong>Ссылка:</strong><br />
          <input type="text" name="link" size="27" value="<?=$project['link']?>"/>
        </p>
      </li>
    </ul>
    <div class="apf-payed-info" id="payedBox"<?=(!$price ? ' style="display:none"' : '')?>>
      <div class="app-sum">
        <strong>Итого: <SPAN id="sum_fm"><?= round($price)?></SPAN> рублей</strong>
			
				<?/*<div class="i-shadow i-shadow_inline-block">
					<span class="b-shadow__icon b-shadow__icon_quest"></span>
					<div class="b-shadow b-shadow_width_210 b-shadow_left_-87 b-shadow_hide">
						<div class="b-shadow__right">
							<div class="b-shadow__left">
								<div class="b-shadow__top">
									<div class="b-shadow__bottom">
										<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
											<div class="b-shadow__txt">Free-Money (FM) - условная валюта сайта Free-lance.ru <a class="b-shadow__link" href="/bill/">Ваш личный счет на сайте</a></div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="b-shadow__tl"></div>
						<div class="b-shadow__tr"></div>
						<div class="b-shadow__bl"></div>
						<div class="b-shadow__br"></div>
						<span class="b-shadow__icon b-shadow__icon_close"></span>
						<span class="b-shadow__icon b-shadow__icon_nosik"></span>
					</div>
				</div>*/?>	
						
        <br />Это $<SPAN id="sum_usd"><?=round(($price*$pex[project_exrates::FM.project_exrates::USD]), 2)?></SPAN>
      </div>
        
			<div class="b-promo b-promo_marg_20_0">
				<?php if ( !is_pro() ) {?>
                <div class="b-promo__note">
						<div class="b-promo__note-inner">
								<h3 class="b-promo__h3 b-promo__h3_padbot_5">С <span class="b-promo__pro b-promo__pro_emp"></span>&#160;дешевле</h3>
								<p class="b-promo__p b-promo__p_fontsize_13">
									<a href="/payed-emp/" class="b-promo__link">Купите профессиональный аккаунт</a> и выбранные услуги <?php
                                     if ( $PROprice > 0 ) {
                                     ?><span id="sum_fm_pro_prefix">будут стоить всего</span> 
                                       <span class="b-promo__txt b-promo__txt_bold b-promo__txt_color_fd6c30">
                                           <span id="sum_fm_pro"><?= round($PROprice) ?></span> 
                                           <span id="sum_fm_pro_postfix">рублей</span>
                                       </span><?php } else 
                                       {
                                       ?><span id="sum_fm_pro_prefix"></span>
                                       <span class="b-promo__txt b-promo__txt_bold b-promo__txt_color_fd6c30" >
                                           <span id="sum_fm_pro"> не будут вам стоить ничего</span>
                                       </span><span id="sum_fm_pro_postfix"></span><?
                                       } ?>.</p>
                        </div>
                </div>
                <?php }//else?>
			</div>		
        

			
      <p class="add-btn"><input id="payBtn" type="image"<?=($account->sum < $price && $account->bonus_sum < $price ? ' disabled' : '')?> src="<?=($tmpPrj->isEdit() ? '/images/save-payed.png' : '/images/add-payed.png')?>" width="157" height="52" alt="Оплатить и сохранить" /></p>
      <?=($error['buy'] ? view_error($error['buy']) : '')?>

      <div<?=($account->sum >= $price||$account->bonus_sum >= $price ? ' style="display:none"' : '')?> id="nomnyBox">
        <p class="error-color"><strong>У вас на счету не хватает <span id="nomnySum"><?= round($price - $account->sum)?></span> руб.</strong></p>
        <p class="add-btn"><input onclick="document.getElementById('publicForm').action.value='bill'"type="image" src="/images/add-payed2.png" width="150" height="24" alt="Пополнить счет" /></p>
      </div>
    </div>
    <div>
      <br/>
      <p class="add-btn"><input onclick="document.getElementById('publicForm').action.value='prev'"type="image"  src="/images/edit-project.png" width="150" height="24" alt="Редактировать" /></p>
      <p class="add-btn" id="noPayedBox"<?=($price ? ' style="display:none"' : '')?>><input id="freeBtn" type="image" src="/images/save-project.png" width="150" height="24" alt="Сохранить" /></p>
    </div>
  </div>
  <div class="app-right app-free">
      <div class="prj-one<?= $project['payed'] ? ' prj-payed' : '' ?><?= $project['is_color'] == 't' ? ' prj-colored' : ' prj-usual' ?><?= $project['is_bold'] == 't' ? ' prj-weight' : '' ?>"  id="colorPrvw">
          <div class="form">
              <b class="b1"></b>
              <b class="b2"></b>
              <div class="form-in">
                  <?php $priceby_str = getPricebyProject($project['priceby']);?>
                  <?php if ($project['cost']) { ?>
                  <var class="bujet"><?=CurToChar($project['cost'], $project['currency'])?><?=$priceby_str?></var>
                  <? } else { ?>
                  <var class="bujet-dogovor">По договоренности</var>
                  <? } ?>
                  <? // ЛОГО ?>
                  <?php if ($logo) { ?>
                      <?php if ( trim(formatLink($project['link'])) ) {?>
                        <a id="logoPrvw" href="http://<?= formatLink($project['link']) ?>" target="_blank">
                            <img src="<?= WDCPREFIX . '/' . $logo['path'] . $logo['name'] ?>" alt="" class="prj-clogo" />
                        </a>
                      <? } else {?>
                         <div id="logoPrvw">
                            <img src="<?= WDCPREFIX . '/' . $logo['path'] . $logo['name'] ?>" alt="" class="prj-clogo" />
                         </div>
                      <? } ?>
                      
                  <? } else { ?>
                  <img id="logoPrvw"<?=($error['logo'] ? '' : ' style="display:none"')?> src="/images/yourlogo.png" alt="Ваш логотип и ссылка на сайт" class="prj-clogo" />
                  <? } ?>
                      
                  <? // ЗАГОЛОВОК ?>
                  <h3>
                      <img  id="istop"<?=(($remTPeriod || $addedTD) ? '' : ' style="display:none"')?> src="/images/tp<?= $project['is_color'] == 't' ? '2' : '' ?>.gif" alt="" title="<?=$topDays?>"/>
                      <?php $sName = $project['kind'] != 4 && !is_pro() ? $stop_words->replace($project['name']) : $project['name'] ?>
                      <?php if($project['id'] > 0) { ?>
                      <a name="prj<?= $project['id'] ?>" href="/projects/?pid=<?= $project['id'] ?>">
                          <?= reformat2($sName, 30, 0, 1) ?>
                      </a>
                      <?php } else {//if?>
                          <?= reformat2($sName, 30, 0, 1) ?>
                      <?php } //else?>
                  </h3>
                  
            <?// ТЕКСТ ПРОЕКТА ?>
            <div class="prj-full-display">
                <div class="utxt">
                    <p id="boldPrvw">
                        <?= reformat2($project['descr'], 40, 0, 1) ?>
                    </p>
                    <? if (count($project['attaches'])) { ?>
                    <div style="padding:10px 0 0 0">
                        <? foreach ($project['attaches'] as $a) { ?>
                        <div class="flw_offer_attach">

                            <? if ( $a['virus'] & 1 == 1 ) { ?>
                            <a href="" onclick="alert('Обнаружен вирус. Файл удален.');return false;" target="_blank">Загрузить</a>
                            <? } else { ?>
                            <a href="<?= WDCPREFIX ?>/<?= $a['path'].$a['name'] ?>" target="_blank">Загрузить</a> (<?= CFile::getext($a['name']) ?>; <?= ConvertBtoMB($a['size']) ?> )
                            <? } ?>

                            <? if ( $a['virus'] & 1 == 1 ) { ?>(<?= CFile::getext($a['name']) ?>; <?= ConvertBtoMB($a['size']) ?> )
                            <span class="avs-err"><span>Обнаружен вирус.</span> Файл удален с сервера.</span>
                            <? } else if ( $a['virus'] === 0 ) { ?>
                            <span class="avs-ok">Проверено антивирусом.</span>
                            <? } else if ( $a['virus'] == 2 ) { ?>
                            <span class="avs-errcheck">Невозможно проверить.</span>
                            <? } else if ( $a['virus'] == 8 ) { ?>
                            <? } else { ?>
                            <span class="avs-nocheck">Файл не проверен.</span>
                            <? } ?>

                        </div>
                        <? } ?>

                    </div>
                    <? } ?>
                </div>
            </div>
            <br/>
            <div class="prj-full-display">
                <? if ($project['pro_only'] == 't' || $project['verify_only'] == 't') { ?>
                <ul class="project-info">
                    <li>Только для 
                        <? if ($project['pro_only'] == 't') { ?>
                            <a href="/payed/"><img src="/images/icons/f-pro.png" alt="PRO" /></a>
                        <? } ?>
                        <? if ($project['verify_only'] == 't') { ?>
                            <?= view_verify('Паспортные данные подтверждены')?>
                        <? } ?>
                    </li>
                </ul>
                <? } ?>

                <ul class="prj-info c">
                    <li class="pi-answer">
                        <a href="/projects/?pid=<?= $project['id'] ?>">
                        <? if (!is_emp()) { ?>
                            <? if (!$project['offer_id']) { ?>
                            Ответить на проект
                            <? } else { ?>
                            Вы уже ответили на этот проект
                            <? } ?>
                        <? } else { ?>
                            Предложения
                        <? } ?>
                        </a> (<?= intval($project['offers_count']) ?>) 
                        <? if (hasPermissions('projects') && $project['unread']) { ?>
                        <strong style='color:#6BB24B'>(<?= $project['unread'] . ' ' . ending($project['unread'], 'новое', 'новых', 'новых') ?>)</strong>
                        <? } ?>
                    </li>
                    <? if ($project['payed']  && $project['kind'] != 2 && $project['kind'] != 7) { ?>
                    <li class="pi-payed"><strong>Платный проект</strong></li>
                    <? } ?>

                    <? if ($project['kind'] == 2 || $project['kind'] == 7) { ?>
                    <li class="pi-red">Конкурс</li>
                    <? } else if ($project['kind'] == 4) { ?>
                    <li class="pi-office">
                        В офис
                    </li>
                    <? } ?>
                    <li class="pi-time"><?= ago_pub_x($project['post_date']?strtotime($project['post_date']) : time()) ?></li>

                </ul>
            </div>

              </div>
          </div>
      </div>
  </div>
</div>
  <input type="hidden" name="draft_id" value="<?=$draft_id?>"/>
  <input type="hidden" name="draft_prj_id" value="<?=$draft_prj_id?>"/>
  <input type="hidden" name="pk" value="<?=$key?>"/>
  <input type="hidden" name="action" value="save"/>
  <input type="hidden" name="step" value="2"/>
</form>
