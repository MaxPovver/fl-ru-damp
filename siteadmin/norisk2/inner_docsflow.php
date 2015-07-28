<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
if(!$sbr) exit;
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/sbr.common.php");
$xajax->printJavascript('/xajax/');
?>
<script type="text/javascript">
var SBR; window.addEvent('domready', function() { SBR = new Sbr('siteadminFrm'); <?php if($is_edit_access) {?>SBR.checkChecked();<?php }//if?> } );
Sbr.prototype.SCHEME=<?=(int)$scheme?>;
<?php if($is_edit_access) {?>
Sbr.prototype.checkChecked
=function() {
    this.form.recv_docs.disabled=(this.checkedElms<=0);
    this.form.unrecv_docs.disabled=(this.checkedElms<=0);
};
<?php } //if?>

function ShowFinInfo(type, id) {
    switch(type) {
        case 'c':
            $('finblock_h_c'+id).setStyle('font-weight', 'bold');
            $('finblock_h_b'+id).setStyle('font-weight', 'normal');
            $('finblock_h_e'+id).setStyle('font-weight', 'normal');
            $('fininfo_c'+id).setStyle('display', 'table');
            $('fininfo_b'+id).setStyle('display', 'none');
            $('fininfo_e'+id).setStyle('display', 'none');
            break;
        case 'b':
            $('finblock_h_c'+id).setStyle('font-weight', 'normal');
            $('finblock_h_b'+id).setStyle('font-weight', 'bold');
            $('finblock_h_e'+id).setStyle('font-weight', 'normal');
            $('fininfo_c'+id).setStyle('display', 'none');
            $('fininfo_b'+id).setStyle('display', 'table');
            $('fininfo_e'+id).setStyle('display', 'none');
            break;
        case 'e':
            $('finblock_h_c'+id).setStyle('font-weight', 'normal');
            $('finblock_h_b'+id).setStyle('font-weight', 'normal');
            $('finblock_h_e'+id).setStyle('font-weight', 'bold');
            $('fininfo_c'+id).setStyle('display', 'none');
            $('fininfo_b'+id).setStyle('display', 'none');
            $('fininfo_e'+id).setStyle('display', 'table');
            break;
    }
}
</script>
<h3>Документооборот / <?=$scheme==sbr::SCHEME_AGNT ? 'Агент' : ($scheme==sbr::SCHEME_PDRD || $scheme==sbr::SCHEME_PDRD2 ? 'Подряд' : ($scheme == -1 ? 'Архив' : 'Все'))?></h3>
<? if(!$scheme) { ?>
  <div style="padding: 13px 13px 13px 15px;background: #FFE5E5 !important;">
    Здесь выводятся все завершенные сделки (люди) за всю историю СБР, даже если они уже ушли в выплаты или выплачены.
  </div>
  <br/>
<? } ?>
<form action="." method="get" id="siteadminFrm">
    <div class="form form-nr-docs-sort<?=htmlspecialchars($_COOKIE['ccNrAdmFlt']) ? ' form-hide' : ''?>">
        <b class="b1"></b>
        <b class="b2"></b>
        <div class="form-in">
                <div class="form-h">
                    <a href="javascript:void(0);" class="lnk-dot-666" onclick="
                        var bx=$(this).getParent('.form'),d=new Date();
                        bx.toggleClass('form-hide');
                        d.setMonth(d.getMonth()+1);
                        document.cookie='ccNrAdmFlt='+(bx.hasClass('form-hide')-0)+'; expires='+d.toGMTString();
                      ">Фильтр</a>
                </div>
                <div class="form-b">
                    <div class="form-block first">
                    	<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
                        	<tr class="b-layout__tr">
                            	<td class="b-layout__left b-layout__left_width_90">
                            		<label class="b-layout__txt b-layout__txt_block b-layout__txt_fontsize_11 b-layout__txt_padtop_3" for="filter[user]">ФИО или логин:</label>
                                </td>
                                <td class="b-layout__right b-layout__right_padbot_10">
                                  <div class="b-input">
                                      <input id="filter[user]" class="b-input__text" type="text" name="filter[user]" value="<?=htmlspecialchars($filter['user'])?>" />
                                  </div>
                                </td>
                            </tr>
                        	<tr class="b-layout__tr">
                            	<td class="b-layout__left b-layout__left_width_90">
                            		<label class="b-layout__txt b-layout__txt_block b-layout__txt_fontsize_11 b-layout__txt_padtop_3" for="filter[contract_num]">Номер СБР</label>
                                </td>
                                <td class="b-layout__right b-layout__right_padbot_10">
                                  <div class="b-input b-input_width_200">
                                      <input id="filter[contract_num]" class="b-input__text" name="filter[contract_num]" type="text" size="20" maxlength="15" value="<?=htmlspecialchars($filter['contract_num'])?>"  />
                                  </div>
                                </td>
                            </tr>
                        	<tr class="b-layout__tr">
                            	<td class="b-layout__left b-layout__left_width_90">
                            		<label class="b-layout__txt b-layout__txt_block b-layout__txt_fontsize_11 b-layout__txt_padtop_3" for="filter[name]">Название СБР:</label>
                                </td>
                                <td class="b-layout__right b-layout__right_padbot_10">
                                  <div class="b-input">
                                      <input id="filter[name]" class="b-input__text" name="filter[name]" type="text" value="<?=htmlspecialchars($filter['name'])?>"  />
                                  </div>
                                </td>
                            </tr>
                        	<tr class="b-layout__tr">
                            	<td class="b-layout__left b-layout__left_width_90">
                            		<label class="b-layout__txt b-layout__txt_block b-layout__txt_fontsize_11 b-layout__txt_padtop_3" for="filter[act_sum]">Сумма акта:</label>
                                </td>
                                <td class="b-layout__right b-layout__right_padbot_10">
                                  <div class="b-input b-input_width_200 b-input_inline-block">
                                      <input id="filter[act_sum]" class="b-input__text" name="filter[act_sum]" size="20" type="text" value="<?=htmlspecialchars($filter['act_sum'])?>"  />
                                  </div>
                                  <div class="b-select b-select_inline-block">
                                      <select class="b-select__select b-select__select_width_60" name="filter[act_sys]">
                                          <option value="0"></option>
                                          <? foreach($EXRATE_CODES as $exc=>$ex) { if($exc==exrates::WMZ) continue; ?>
                                            <option value="<?=$exc?>"<?=$exc==$filter['act_sys'] ? ' selected="selected"' : ''?>><?=$ex[1]?></option>
                                          <? } ?>
                                      </select>
                                  </div>
                                </td>
                            </tr>
                        	<tr class="b-layout__tr">
                            	<td class="b-layout__left b-layout__left_width_90">
                            		<label class="b-layout__txt b-layout__txt_fontsize_11">Документы:</label>
                                </td>
                                <td class="b-layout__right b-layout__right_padbot_10">
                                    <div class="b-radio b-radio_layout_horizontal b-radio_padtop_2">
                                        <? for($a='0tf',$w='получены',$f='has_docs',$j=0,$i=0; $j<3; $j++,$i=$a[$j]) { ?>
                                        	<div class="b-radio__item">
                                           		<input id="filter[<?=$f?>]_<?=$j?>" class="b-radio__input" type="radio" name="filter[<?=$f?>]" value="<?=$i?>"<?=$i==$filter[$f] ? ' checked="checked"' : ''?> /><label class="b-radio__label" for="filter[<?=$f?>]_<?=$j?>"><?=($i=='0' ? 'не важно' : ($i=='f' ? 'не ' : '').$w)?></label>
                                           </div>
                                        <? } ?>
                                    </div>
                                </td>
                            </tr>
                        	<tr class="b-layout__tr">
                            	<td class="b-layout__left b-layout__left_width_90">
                            		<label class="b-layout__txt b-layout__txt_fontsize_11">Акт услуг:</label>
                                </td>
                                <td class="b-layout__right b-layout__right_padbot_10">
                                    <div class="b-radio b-radio_layout_horizontal b-radio_padtop_2">
                                        <? for($a='0tf',$w='загружен&nbsp;',$f='has_act',$j=0,$i=0; $j<3; $j++,$i=$a[$j]) { ?>
                                        	<div class="b-radio__item">
                                           		<input id="filter[<?=$f?>]__<?=$j?>" class="b-radio__input" type="radio" name="filter[<?=$f?>]" value="<?=$i?>"<?=$i==$filter[$f] ? ' checked="checked"' : ''?> /><label class="b-radio__label" for="filter[<?=$f?>]__<?=$j?>"><?=($i=='0' ? 'не важно' : ($i=='f' ? 'не ' : '').$w)?></label>
                                           </div>
                                        <? } ?>
                                    </div>
                                </td>
                            </tr>
                        	<tr class="b-layout__tr">
                            	<td class="b-layout__left b-layout__left_width_90">
                            		<label class="b-layout__txt b-layout__txt_fontsize_11">Счет-фактура:</label>
                                </td>
                                <td class="b-layout__right b-layout__right_padbot_10">
                                    <div class="b-radio b-radio_layout_horizontal b-radio_padtop_2">
                                        <? for($a='0tf',$w='загружена',$f='has_fct',$j=0,$i=0; $j<3; $j++,$i=$a[$j]) { ?>
                                        	<div class="b-radio__item">
                                           		<input id="filter[<?=$f?>]_-<?=$j?>" class="b-radio__input" type="radio" name="filter[<?=$f?>]" value="<?=$i?>"<?=$i==$filter[$f] ? ' checked="checked"' : ''?> /><label class="b-radio__label" for="filter[<?=$f?>]_-<?=$j?>"><?=($i=='0' ? 'не важно' : ($i=='f' ? 'не ' : '').$w)?></label>
                                           </div>
                                        <? } ?>
                                    </div>
                                </td>
                            </tr>
                        	<tr class="b-layout__tr">
                            	<td class="b-layout__left b-layout__left_width_90">
                            		<label class="b-layout__txt b-layout__txt_fontsize_11">Реквизиты:</label>
                                </td>
                                <td class="b-layout__right b-layout__right_padbot_10">
                                    <div class="b-radio b-radio_layout_horizontal b-radio_padtop_2">
                                        <? for($a='0tf',$w='заполены&nbsp;',$f='has_reqv',$j=0,$i=0; $j<3; $j++,$i=$a[$j]) { ?>
                                        	<div class="b-radio__item">
                                           		<input id="filter[<?=$f?>]-_<?=$j?>" class="b-radio__input" type="radio" name="filter[<?=$f?>]" value="<?=$i?>"<?=$i==$filter[$f] ? ' checked="checked"' : ''?> /><label class="b-radio__label" for="filter[<?=$f?>]-_<?=$j?>"><?=($i=='0' ? 'не важно' : ($i=='f' ? 'не ' : '').$w)?></label>
                                           </div>
                                        <? } ?>
                                    </div>
                                </td>
                            </tr>
                        <? if(!(int)$scheme) { ?>
                        	<tr class="b-layout__tr">
                            	<td class="b-layout__left b-layout__left_width_90">
                            		<label class="b-layout__txt b-layout__txt_fontsize_11">Статус:</label>
                                </td>
                                <td class="b-layout__right b-layout__right_padbot_10">
                                    <div class="b-radio b-radio_layout_horizontal b-radio_padtop_2">
                                        <? for($a='0tf',$w='удалены&nbsp;',$f='is_removed',$j=0,$i=0; $j<3; $j++,$i=$a[$j]) { ?>
                                        	<div class="b-radio__item">
                                           		<input id="filter[<?=$f?>]-_-<?=$j?>" class="b-radio__input" type="radio" name="filter[<?=$f?>]" value="<?=$i?>"<?=$i==$filter[$f] ? ' checked="checked"' : ''?> /><label class="b-radio__label" for="filter[<?=$f?>]-_-<?=$j?>"><?=($i=='0' ? 'не важно' : ($i=='f' ? 'не ' : '').$w)?></label>
                                           </div>
                                        <? } ?>
                                    </div>
                                </td>
                            </tr>
                        <? } ?>
                        <? if(!(int)$scheme) { ?>
                        	<tr class="b-layout__tr">
                            	<td class="b-layout__left b-layout__left_width_90">
                            		<label class="b-layout__txt b-layout__txt_fontsize_11">Архив:</label>
                                </td>
                                <td class="b-layout__right b-layout__right_padbot_10">
                                    <div class="b-radio b-radio_layout_horizontal b-radio_padtop_2">
                                        <? for($a='0tf',$w='в архиве&nbsp;',$f='archive',$j=0,$i=0; $j<3; $j++,$i=$a[$j]) { ?>
                                        	<div class="b-radio__item">
                                           		<input id="filter[<?=$f?>]-__<?=$j?>" class="b-radio__input" type="radio" name="filter[<?=$f?>]" value="<?=$i?>"<?=$i==$filter[$f] ? ' checked="checked"' : ''?> /><label class="b-radio__label" for="filter[<?=$f?>]-__<?=$j?>"><?=($i=='0' ? 'не важно' : ($i=='f' ? 'не ' : '').$w)?></label>
                                           </div>
                                        <? } ?>
                                    </div>
                                </td>
                            </tr>
                        <? } ?>
                        	<tr class="b-layout__tr">
                            	<td class="b-layout__left b-layout__left_width_90">
                            		<label class="b-layout__txt b-layout__txt_block b-layout__txt_fontsize_11 b-layout__txt_padtop_3">Дата:</label>
                                </td>
                                <td class="b-layout__right b-layout__right_padbot_10">
									<? include($_SERVER['DOCUMENT_ROOT'].'/norisk2/tpl.filter-period.php') ?>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="form-block last">
                    	<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
                        	<tr class="b-layout__tr">
                            	<td class="b-layout__left b-layout__left_width_90">&#160;</td>
                                <td class="b-layout__right b-layout__right_padbot_10">
                                    <input type="submit" value="Отфильтровать" />
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
        </div>
        <b class="b2"></b>
        <b class="b1"></b>
    </div>
    <div class="nr-stat2-bar c">
        <?php if ($is_edit_access) { ?>
        <span class="i-chk">
            <input type="checkbox" onclick="SBR.setAllChecked(this, 'suids[]')" />
        </span>
        <input type="submit" name="recv_docs" value="Пришли документы" />
        <input type="submit" name="unrecv_docs" value="Документы ожидаются" />
        <?php } //if?>
        <span class="sel">
            Сортировать по
            <select onchange="SBR.changeFormDir(this.options[this.selectedIndex].value, 'DESC')">
              <? foreach($sbr->form_cols[$site] as $idx=>$val) { ?>
                <option value="<?=$idx?>"<?=$dir_col==$idx ? ' selected="true"' : ''?>><?=$val[0]?></option>
              <? } ?>
            </select>
        </span>
    </div>

    <? if($docs) foreach($docs as $m=>$stg) {
        $doc_access = is_emp($stg['role']) ? sbr::DOCS_ACCESS_EMP : sbr::DOCS_ACCESS_FRL;
        $item_id = "{$stg['id']}_{$stg['user_id']}";
    ?>
        <div class="nr-stat2-one" id="subx_<?=$item_id?>">
            <a name="<?=$item_id?>"></a>
            <ul class="nr-stat2-one-i c">
                <li class="nr-stat-date"><?=date('d.m.Y H:i', strtotime($stg['act_upload_time']))?></li>
                <li class="nr-stat-cat"><a href="javascript:;" onclick="if(window.lstfw)window.lstfw.style.display='none';(window.lstfw=document.getElementById('finwin<?=$item_id?>')).style.display='block'">Страница финансов</a></li>
                <li class="nr-stat-chk">
                  <? if(!is_emp($stg['role']) && $is_edit_access) { ?>
                    <input type="checkbox" name="suids[]" value="<?=$stg['id'].'-'.$stg['user_id']?>" onclick="SBR.incChecked(this.checked, !this.checked)" />
                  <? } else { ?>&nbsp;<? } ?>
                </li>
                <li>
                <a href="/users/<?=$stg['login']?>/" class="<?=is_emp($stg['role']) ? 'employer' : 'freelancer'?>-name" target="_blank"><?=$stg['uname']?> <?=$stg['usurname']?> [<?=$stg['login']?>]</a>
                <?php if($form_type[$m] == sbr::FT_JURI && ( $stg['scheme_type'] == sbr::SCHEME_PDRD || $stg['scheme_type'] == sbr::SCHEME_PDRD2 ) && !is_emp($stg['role'])) { ?>
                <span class="red">(Юридическое лицо)</span>
                <?php }?>
                </li>
            </ul>
            <h4>
              <?php if($is_edit_access){ ?><img style="cursor: pointer;" src="/images/flt-<?= $stg['is_removed'] == 'f' ? 'close' : 'on'?>.png" align="right" onclick="SBR.setRemoved('<?=$item_id?>', null, this)" title="<?= $stg['is_removed'] == 'f' ? 'Удалить' : 'Восстановить'?>" /><?php }//?>
              <strong><?=$sbr->getContractNum($stg['sbr_id'], $stg['scheme_type'], false)?></strong><br/>
              Этап <?=$stg['sbr_id'].'-'.($stg['num']+1)?>: <a href="/norisk2/?site=Stage&id=<?=$stg['id']?>&access=A"><?=reformat($stg['name'], 40, 0, 1)?></a><br/><br/>
            </h4>
            <? if(!is_emp($stg['role']) && $stg['form_type']==sbr::FT_JURI && $stg['rez_type']==sbr::RT_UABYKZ && $stg['act_notnp']=='t') { // deprecated block ?>
                <div class="form fs-p rez-check">
                    <b class="b1"></b>
                    <b class="b2"></b>
                    <div class="form-in">
                        <label><input type="checkbox" class="i-chk" checked="true" onclick="if(!window.confirm('После отмены данного параметра необходимо будет заново загрузить Акт и пересчитать сумму выплаты. Продолжить?')) return false; SBR.setNotNp(this, <?=$stg['user_id']?>, <?=$stg['id']?>, false)"/>
                          Налог на прибыль исключен из Акта, взамен на отправку 
                        </label>
                        <a href="/users/<?=$stg['login']?>/setup/finance/" target="_blank">справки о резиденстве</a>
                    </div>
                    <b class="b2"></b>
                    <b class="b1"></b>
                </div>
            <? } ?>
            <table>
                <thead>
                    <tr>
                        <th class="first">Сумма СБР</th>
                        <th>Сумма акта</th>
                        <th><?=((float)$stg['act_lnp'] ? 'Налог на прибыль' : 'Сумма НДФЛ')?></th>
                        <th>5%</th>
                        <th>Процент за обмен</th>
                        <th>Итого ООО "ВААН"</th>
                        <th class="last">Сумма к выплате</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="first"><?=sbr_meta::view_cost($stg['cost'], $stg['cost_sys'])?></td>
                        <td><?=((float)$stg['act_sum'] ? sbr_meta::view_cost($stg['act_sum'], $stg['act_sys']) : '-')?></td>
                        <? if((float)$stg['act_lnp']) { ?>
                          <td><?=sbr_meta::view_cost($stg['act_lnp'], $stg['act_sys'])?></td>
                        <? } else { ?>
                          <td><?=((float)$stg['act_lndfl'] ? sbr_meta::view_cost($stg['act_lndfl'], $stg['act_sys']) : '-')?></td>
                        <? } ?>
                        <td><?=((float)$stg['act_lcomm'] ? sbr_meta::view_cost($stg['act_lcomm'], $stg['act_sys']) : '-')?></td>
                        <td><?=((float)$stg['act_lintr'] ? sbr_meta::view_cost($stg['act_lintr'], $stg['act_sys']) : '-')?></td>
                        <td><?=((float)$stg['act_sum'] ? sbr_meta::view_cost($stg['act_sum'], $stg['act_sys']) : '-')?></td>
                        <td class="last"><a href="/users/<?=$stg['login']?>/setup/finance/?sid=<?=$stg['id']?>" class="inherit"><?=((float)$stg['credit_sum'] ? sbr_meta::view_cost($stg['credit_sum'], $stg['credit_sys']) : '-')?></a></td>
                    </tr>
                </tbody>
            </table>
            <table class="nr-stat-docs" style="width:750px" cellspacing="10" >
                <col style="width:240px" />
                <col style="width:240px" />
                <col style="width:240px" />
                <?php
                $nCol = 0;
                ?>
                <tr style="height:35px">
                
                    <?php if ( !is_emp($stg['role']) || (is_emp($stg['role']) && ( $stg['scheme_type'] == sbr::SCHEME_PDRD || $stg['scheme_type'] == sbr::SCHEME_PDRD2 ) ) ) {
                        $nCol++;
                    ?>
                      <td class="nr-stat-docs1">
                        <?php if ( $stg['docs_received']=='t' ) { ?>
                          Документы получены&nbsp; <?php if ($is_edit_access) { ?><a href="javascript:;" class="lnk-dot-666" onclick="SBR.setRecvDocs(this, '<?=$item_id?>', 0)">Отменить</a><?php } //if?>
                        <?php } else { ?>
                          Документы ожидаются <?php if ($is_edit_access) { ?><input type="button" value="Пришли" onclick="SBR.setRecvDocs(this, '<?=$item_id?>', 1)" /><?php } //if?>
                        <? } ?>
                      </td>
                    <?php } ?>
                    
                    
                    <?php if(!is_emp($stg['role']) && $stg['credit_sys'] == exrates::FM) {
                        $nCol++;
                    ?>
                    <td class="nr-stat-docs2">
                        <?=sbr_adm::view_doc_field($stg['uploaded_docs_a'][sbr::DOCS_TYPE_FM_APPL], $item_id, $stg['id'], sbr::DOCS_TYPE_FM_APPL, $doc_access, $is_edit_access)?>
                    </td>
                    <?php } ?>
                    <?php if($stg['credit_sys'] == exrates::WMR) {
                        $nCol++;
                    ?>
                    <td class="nr-stat-docs2">
                        <?=sbr_adm::view_doc_field($stg['uploaded_docs_a'][sbr::DOCS_TYPE_WM_APPL], $item_id, $stg['id'], sbr::DOCS_TYPE_WM_APPL, $doc_access, $is_edit_access)?>
                    </td>
                    <?php } ?>
                    
                    <?php $nCol++; ?>
                    <td class="nr-stat-docs2">
                        <?=sbr_adm::view_doc_field($stg['uploaded_docs_a'][sbr::DOCS_TYPE_ACT], $item_id, $stg['id'], sbr::DOCS_TYPE_ACT, $doc_access, $is_edit_access)?>
                    </td>
                    <?php if ( $nCol == 3 ) { $nCol = 0; echo '</tr><tr style="height:35px">'; } ?>
                    
                    <?php if(is_emp($stg['role'])) {
                        $nCol++;
                    ?>
                       <td class="nr-stat-docs2">
                        <?=sbr_adm::view_doc_field($stg['uploaded_docs_a'][sbr::DOCS_TYPE_AGENT_REP], $item_id, $stg['id'], sbr::DOCS_TYPE_AGENT_REP, $doc_access, $is_edit_access)?>
                       </td>
                    <?php } ?>
                    <?php if ( $nCol == 3 ) { $nCol = 0; echo '</tr><tr style="height:35px">'; } ?>
                    
                    <?php $nCol++; ?>
                    <td class="nr-stat-docs3">
                      <?=sbr_adm::view_doc_field($stg['uploaded_docs_a'][sbr::DOCS_TYPE_FACTURA], $item_id, $stg['id'], sbr::DOCS_TYPE_FACTURA, $doc_access, $is_edit_access)?>
                    </td>
                    <?php if ( $nCol == 3 ) { $nCol = 0; echo '</tr><tr style="height:35px">'; } ?>
                    
                
                    <?php if($stg['status'] == sbr_stages::STATUS_INARBITRAGE || $stg['status'] == sbr_stages::STATUS_ARBITRAGED) { 
                        $nCol++;
                    ?>
                      <td class="nr-stat-docs4">
                        <?=sbr_adm::view_doc_field($stg['uploaded_docs_a'][sbr::DOCS_TYPE_ARB_REP], $item_id, $stg['id'], sbr::DOCS_TYPE_ARB_REP, sbr::DOCS_ACCESS_ALL, $is_edit_access)?>
                      </td>
                    <?php } ?>
                    <?php if ( $nCol == 3 ) { $nCol = 0; echo '</tr><tr style="height:35px">'; } ?>
                    
                    
                    <?php if(!is_emp($stg['role']) && $stg['credit_sys'] != exrates::FM) {
                        $nCol++;
                    ?>
                       <td class="nr-stat-docs5">
                        <?=sbr_adm::view_doc_field($stg['uploaded_docs_a'][sbr::DOCS_TYPE_COPY_ACT], $item_id, $stg['id'], sbr::DOCS_TYPE_COPY_ACT, $doc_access, $is_edit_access)?>
                       </td>
                    <?php } ?>
                    <?php if ( $nCol == 3 ) { $nCol = 0; echo '</tr><tr style="height:35px">'; } ?>
                    
                    
                    <?php if(!is_emp($stg['role']) && $stg['form_type'] == sbr::FT_JURI) {
                        $nCol++;
                    ?>
                      <td class="nr-stat-docs3">
                        <?=sbr_adm::view_doc_field($stg['uploaded_docs_a'][sbr::DOCS_TYPE_COPY_FACTURA], $item_id, $stg['id'], sbr::DOCS_TYPE_COPY_FACTURA, $doc_access, $is_edit_access)?>
                      </td>
                    <?php } ?>
                    <?php if ( $nCol == 3 ) { $nCol = 0; echo '</tr><tr style="height:35px">'; } ?>
                      
                    <?php // заявление о выплате ЯД физики нерезиденты
                    if(!is_emp($stg['role']) && $stg['form_type'] == sbr::FT_PHYS && $stg['uploaded_docs_a'][sbr::DOCS_TYPE_YM_APPL]) {
                        $nCol++;
                    ?>
                      <td class="nr-stat-docs3">
                        <?=sbr_adm::view_doc_field($stg['uploaded_docs_a'][sbr::DOCS_TYPE_YM_APPL], $item_id, $stg['id'], sbr::DOCS_TYPE_YM_APPL, $doc_access, $is_edit_access)?>
                      </td>
                    <?php } ?>
                    <?php if ( $nCol == 3 ) { $nCol = 0; echo '</tr><tr style="height:35px">'; } ?>
                    
                    <?php if ( $nCol > 0 ) {
                        for ( $i = $nCol; $i < 3; $i++ ) { echo '<td>&nbsp;</td>'; }
                    } ?>
                    
                </tr>
            </table>
            <? if($error[$item_id]) { ?><h4 style="color:red"><?=$error[$item_id]?></h4><? } ?>
        </div>
        <div class="overlay ov-out ov-fin" id="finwin<?=$item_id?>" style="display:none">
            <b class="c1"></b>
            <b class="c2"></b>
            <b class="ov-t"></b>
            <div class="ov-r">
                <div class="ov-l">
                    <div class="ov-in">
                        <div class="ov-h">
                            <a href="javascript:;" title="Закрыть" onclick="document.getElementById('finwin<?=$item_id?>').style.display='none'" class="ov-close"><img src="/images/flt-close.png" alt="Закрыть" /></a>
                            Страница финансов <a href="/users/<?=$stg['login']?>/setup/finance/" class="<?=is_emp($stg['role']) ? 'employer' : 'freelancer'?>-name" target="_blank"><?=$stg['uname']?> <?=$stg['usurname']?> [<?=$stg['login']?>]</a>
                            <div style="float:right;padding-right:25px">
                              <a href="javascript:;" onclick="SBR.printFinWin('finblock<?=$item_id?>')" class="lnk-dot-666">Печать</a>
                            </div>
                        </div>
                        <div class="ov-fin-data" id="finblock<?=$item_id?>">
                            <a href="" id="finblock_h_c<?=$item_id?>" style="font-weight: bold;" onClick="ShowFinInfo('c', '<?=$item_id?>'); return false;">Текущая</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="" id="finblock_h_b<?=$item_id?>" onClick="ShowFinInfo('b', '<?=$item_id?>'); return false;">Начало СБР</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="" id="finblock_h_e<?=$item_id?>"  onClick="ShowFinInfo('e', '<?=$item_id?>'); return false;">Конец СБР</a>
                            <table id="fininfo_c<?=$item_id?>">
                                <tr>
                                    <th>Лицо:</th>
                                    <td><?=($stg['form_type']==sbr::FT_JURI ? 'Юридическое' : ($stg['form_type']==sbr::FT_PHYS ? 'Физическое' : 'Не задано'))?></td>
                                </tr>
                                <tr>
                                    <th>Резидент:</th>
                                    <td><?=($stg['rez_type']==sbr::RT_RU ? 'РФ' : ($stg['rez_type']==sbr::RT_UABYKZ ? 'СНГ' : 'не задано'))?></td>
                                </tr>
                                <?
                                  if($stg['form_type']==sbr::FT_JURI) {
                                      foreach(sbr_meta::$reqv_fields[sbr::FT_JURI] as $key=>$field) {
                                        if($field['rez_type'] && !($field['rez_type'] & $stg['rez_type'])) continue;
                                        ?><tr><th><?=$field['name']?>:</th><td><?=$stg['_'.sbr::FT_JURI.'_'.$key]?></td></tr><?
                                      }
                                  } else {
                                      foreach(sbr_meta::$reqv_fields[sbr::FT_PHYS] as $key=>$field) {
                                        if($field['rez_type'] && !($field['rez_type'] & $stg['rez_type'])) continue;
                                        ?><tr><th><?=$field['name']?>:</th><td><?=$stg['_'.sbr::FT_PHYS.'_'.$key]?></td></tr><?
                                      }
                                  }
                                  if ( !empty($stg['attaches']) ) {
                                      ?><tr><th>Загруженные документы:</th><td><?
                                      $sh = '';
                                      foreach ( $stg['attaches'] as $v ) {
                                          $sh .= "<a href='".WDCPREFIX."/users/".$stg['login']."/upload/".$v['name']."' target='_blank'>" . $v['orig_name'] . "</a> ";
                                      }
                                      echo $sh;
                                      ?></td></tr><?
                                  }
                                ?>
                            </table>
                            <table id="fininfo_b<?=$item_id?>" style="display:none;">
                                <tr>
                                    <th>Лицо:</th>
                                    <td><?=($stg['reqv_history']['b']['form_type']==sbr::FT_JURI ? 'Юридическое' : ($stg['reqv_history']['b']['form_type']==sbr::FT_PHYS ? 'Физическое' : 'Не задано'))?></td>
                                </tr>
                                <tr>
                                    <th>Резидент:</th>
                                    <td><?=($stg['reqv_history']['b']['rez_type']==sbr::RT_RU ? 'РФ' : ($stg['reqv_history']['b']['rez_type']==sbr::RT_UABYKZ ? 'СНГ' : 'не задано'))?></td>
                                </tr>
                                <?
                                  if($stg['reqv_history']['b']['form_type']==sbr::FT_JURI) {
                                      foreach(sbr_meta::$reqv_fields[sbr::FT_JURI] as $key=>$field) {
                                        if($field['rez_type'] && !($field['rez_type'] & $stg['reqv_history']['b']['rez_type'])) continue;
                                        ?><tr><th><?=$field['name']?>:</th><td><?=$stg['reqv_history']['b']['_'.sbr::FT_JURI.'_'.$key]?></td></tr><?
                                      }
                                  } else {
                                      foreach(sbr_meta::$reqv_fields[sbr::FT_PHYS] as $key=>$field) {
                                        if($field['rez_type'] && !($field['rez_type'] & $stg['reqv_history']['b']['rez_type'])) continue;
                                        ?><tr><th><?=$field['name']?>:</th><td><?=$stg['reqv_history']['b']['_'.sbr::FT_PHYS.'_'.$key]?></td></tr><?
                                      }
                                  }
                                  if ( !empty($stg['reqv_history']['b']['attaches'])) {
                                      ?><tr><th>Загруженные документы:</th><td><?
                                      $sh = '';
                                      foreach ( $stg['reqv_history']['b']['attaches'] as $v ) {
                                          $sh .= "<a href='".WDCPREFIX."/users/".$stg['login']."/upload/".$v['name']."' target='_blank'>" . $v['orig_name'] . "</a> ";
                                      }
                                      echo $sh;
                                      ?></td></tr><?
                                  }
                                ?>
                            </table>
                            <table id="fininfo_e<?=$item_id?>" style="display:none;">
                                <tr>
                                    <th>Лицо:</th>
                                    <td><?=($stg['reqv_history']['e']['form_type']==sbr::FT_JURI ? 'Юридическое' : ($stg['reqv_history']['e']['form_type']==sbr::FT_PHYS ? 'Физическое' : 'Не задано'))?></td>
                                </tr>
                                <tr>
                                    <th>Резидент:</th>
                                    <td><?=($stg['reqv_history']['e']['rez_type']==sbr::RT_RU ? 'РФ' : ($stg['reqv_history']['e']['rez_type']==sbr::RT_UABYKZ ? 'СНГ' : 'не задано'))?></td>
                                </tr>
                                <?
                                  if($stg['form_type']==sbr::FT_JURI) {
                                      foreach(sbr_meta::$reqv_fields[sbr::FT_JURI] as $key=>$field) {
                                        if($field['rez_type'] && !($field['rez_type'] & $stg['reqv_history']['e']['rez_type'])) continue;
                                        ?><tr><th><?=$field['name']?>:</th><td><?=$stg['reqv_history']['e']['_'.sbr::FT_JURI.'_'.$key]?></td></tr><?
                                      }
                                  } else {
                                      foreach(sbr_meta::$reqv_fields[sbr::FT_PHYS] as $key=>$field) {
                                        if($field['rez_type'] && !($field['rez_type'] & $stg['reqv_history']['e']['rez_type'])) continue;
                                        ?><tr><th><?=$field['name']?>:</th><td><?=$stg['reqv_history']['e']['_'.sbr::FT_PHYS.'_'.$key]?></td></tr><?
                                      }
                                  }
                                  if ( !empty($stg['reqv_history']['e']['attaches'])) {
                                      ?><tr><th>Загруженные документы:</th><td><?
                                      $sh = '';
                                      foreach ( $stg['reqv_history']['e']['attaches'] as $v ) {
                                          $sh .= "<a href='".WDCPREFIX."/users/".$stg['login']."/upload/".$v['name']."' target='_blank'>" . $v['orig_name'] . "</a> ";
                                      }
                                      echo $sh;
                                      ?></td></tr><?
                                  }
                                ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <b class="ov-b"></b>
            <b class="c3"></b>
            <b class="c4"></b>
        </div>
    <? } ?>
    <div class="pager">
        <?=new_paginator($page, ceil($page_count/sbr_adm::PAGE_SA_SIZE), 10, "%s?site={$site}&scheme={$scheme}".str_replace('%','%%',$filter_prms)."&dir_col={$dir_col}&dir={$dir}&page=%d%s")?>
    </div>
    <input type="hidden" name="site" value="<?=$site?>" />
    <input type="hidden" name="scheme" value="<?=$scheme?>" />
    <input type="hidden" name="page" value="<?=$page?>" />
    <input type="hidden" name="dir" value="<?=$dir?>" />
    <input type="hidden" name="dir_col" value="<?=$dir_col?>" />
</form>
