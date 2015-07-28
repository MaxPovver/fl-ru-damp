{{include "header.tpl"}}

<?=$$xajax->printJavascript('/xajax/');?>
<script type="text/javascript">
var lockShHiCal=null;
function shHiCal(hi) {
    if(!lockShHiCal)
        $$('.bill-calendar').setStyle('display', hi ? 'none' : 'block');
    lockShHiCal=1;
}
document.body.onclick=function() {shHiCal(1);lockShHiCal=null;};
$(window).addEvent('onload', function() {
	$$('.cl_link').addEvent('click', function (event) {
	    document.location = this.href;
	});
});

function cal2interval(type) {
	if(type==1) {
		$$('#interval_content').setStyle('display', 'none');
		$$('#int_view').setStyle('display', 'none');
		$$('#cal_link').setStyle('display', 'none');
		$$('#calendar_content').setStyle('display', 'inline');
		$$('#int_link').setStyle('display', 'inline');
		$$('#cal_view').setStyle('display', 'inline');
	} else {
		$$('#interval_content').setStyle('display', 'inline');
		$$('#int_view').setStyle('display', 'inline');
		$$('#cal_link').setStyle('display', 'inline');
		$$('#calendar_content').setStyle('display', 'none');
		$$('#int_link').setStyle('display', 'none');
		$$('#cal_view').setStyle('display', 'none');
	}
}

var link = "/<?=$$name_page?>/history/<?=$$page_h?>/<?=($$v1==0?$$v1+1:$$v1)?>/";
//var my_uid  = "<?=$$my_uid?>";

function interval2Link() {
	var from = $('from_day').value+$('from_month').value+$('from_year').value;
	var to   = $('to_day').value+$('to_month').value+$('to_year').value;
	var link = "/<?=$$name_page?>/history/1/<?=($$v1==0?$$v1+1:$$v1)?>/";
	window.location.href = link+from+'-'+to;
}

function calendar2Link() {
	window.location.href = link+$('month_cal').value+$('year_cal').value;
}


function filter2link(loc) {
	loc = loc.replace(/\s/gi, '');
	loc = loc.replace(/\,/gi, '.');
	var lnk = "/<?=$$name_page?>/history/<?=$$page_h?>/<?=($$v1==0?$$v1+1:$$v1)?>/<?=($$v2==""?0:($$v2))?>/";

	window.location.href = lnk+''+loc+'/';
}

 function setBillHistoryPP(val){
   var expiry = new Date();
   expiry.setTime(expiry.getTime() + 24*60*60*1000);
   document.cookie='bill_history_pp='+val+'; path=/; expires=' + expiry.toGMTString();
    //var lnk = "/<?=$$name_page?>/history/1/<?=($$v1==0?$$v1+1:$$v1)?>/<?=($$v2==""?0:($$v2))?>/";
   var clink = document.location.href;
   var proc_link = clink.replace(/<?=$$name_page?>\/history\/\d+\//g,"<?=$$name_page?>/history/1/");

   document.location.href = proc_link;
 }
</script>

<div class="body c">
				<div class="main c">
					<h1 class="b-page__title">Мой счет</h1>
					<div class="rcol-big">
						{{include "bill/bill_menu.tpl"}}
						<div class="tabs-in bill-t-in">
							<h3>История счета</h3>
							<div class="bill-history-block">
								<div class="bill-calendar" style="display:none" onclick="lockShHiCal=1">
									<b class="b1"></b>
									<div class="bill-calendar-in" >
									<?
												if($$is_calendar) {
													$monthDay = $$monthDay;
													$month    = $$month;
													$year     = $$year;
													if($month == date('m') && $year == date('Y')) $day      = date('d');
												} else {
													$day      = date('d');
	 												$monthDay = date('t');
													$month    = date('m');
													$year     = date('Y');
												}

												if($$selected_day) {
													$day = $selected_day;
												}
									?>
										<div class="bc-toggle"><a href="javascript:void(0)" onClick="cal2interval(1)" id="cal_link" <? if($$caltype==1): ?>style="display:none"<? endif; ?>>Дата</a><strong id="cal_view"<? if($$caltype==2): ?>style="display:none"<? endif; ?>>Дата</strong>&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onClick="cal2interval(2)" id="int_link" <? if($$caltype==2): ?>style="display:none"<? endif; ?>>Интервал дат</a><strong id="int_view" <? if($$caltype==1): ?>style="display:none"<? endif; ?>>Интервал дат</strong></div>
										<span id="interval_content" <? if($$caltype==1): ?>style="display:none"<? endif; ?>>

										  <div class="bc-sels">От
												<select class="bc-sel1" id="from_day">
													<? foreach (range(1, 31) as $n): ?>
														    <option value="<?=$n<10?"0".$n:$n?>"  <?=($n==(int)$$from_day?'selected':'')?>><?=$n?></option>
													<? endforeach; ?>
												</select>
												<select class="bc-sel2" id="from_month">
													<? for($i=1;$i<=12;$i++): ?>
														<option value="<?=$i<10?"0".$i:$i?>" <?if($$from_month == '') print($month==$i?'selected':''); else print($i==(int)$$from_month?'selected':'');?>><?=$$month_name[$i]?></option>
													<? endfor; ?>
												</select>
												<select class="bc-sel3" id="from_year">
													<? for($i= 1998;$i<=date('Y');$i++): ?>
														<option value="<?=substr($i, 2, 2)?>" <?if($$from_year === NULL) print($year==$i?'selected':''); else print((int)substr($i,2,2)==(int)$$from_year?'selected':'');?>><?=$i?></option>
													<? endfor; ?>
												</select></div>
											<div class="bc-sels">До
												<select class="bc-sel1" id="to_day">
													<? foreach (range(1, 31) as $n): ?>
														    <option value="<?=$n<10?"0".$n:$n?>" <?if($$to_day == '') print($n==31?'selected':''); else print($n==(int)$$to_day?'selected':'')?>><?=$n?></option>
													<? endforeach; ?>
												</select>
												<select class="bc-sel2" id="to_month">
													<? for($i=1;$i<=12;$i++): ?>
														<option value="<?=$i<10?"0".$i:$i?>" <?if($$to_month == '') print($month==$i?'selected':''); else print($i==(int)$$to_month?'selected':'')?>><?=$$month_name[$i]?></option>
													<? endfor; ?>
												</select>
												<select class="bc-sel3" id="to_year">
													<? for($i=1998;$i<=date('Y');$i++): ?>
														<option value="<?=substr($i, 2, 2)?>" <?if($$to_year === NULL) print($year==$i?'selected':''); else print((int)substr($i,2,2)==(int)$$to_year?'selected':'')?>><?=$i?></option>
													<? endfor; ?>
												</select>
											</div>

											<div class="bc-btn">
												 <a href="javascript:void(0)"><img src="/images/btn-close.png" alt="Закрыть" class="close_date_content" onclick="shHiCal(1)" /></a> <a href="javascript:void(0)" id="send_interval" onClick="interval2Link()"><img src="/images/btn-set.png" alt="Применить"></a>
											</div>
										</span>

										<span id="calendar_content" <? if($$caltype==2): ?>style="display:none"<? endif; ?>>
										  {{include "bill/bill_history_calendar.tpl"}}
										</span>
									</div>
									<b class="b1"></b>
								</div>
								<table class="bill-tbl" id="bill-history" cellspacing="0">
									<col width="165" />
									<col width="300" />
									<col width="90" />
									<col width="1%"/>
									<col />
									<thead>
										<tr>
											<th>Дата <?=getSortStatus('/'.$$name_page.'/history/'.$$page_h.'/1/'.($$v2!=''?($$v2)."/":'').($$v3!=''?($$v3)."/":''),'/'.$$name_page.'/history/'.$$page_h.'/2/'.($$v2!=''?($$v2)."/":'').($$v3!=''?($$v3)."/":''), ($$sort==1 ? 1 : ($$sort==2? 2 : 0)));?></th>
											<th>Событие <?=getSortStatus('/'.$$name_page.'/history/'.$$page_h.'/3/'.($$v2!=''?($$v2)."/":'').($$v3!=''?($$v3)."/":''),'/'.$$name_page.'/history/'.$$page_h.'/4/'.($$v2!=''?($$v2)."/":'').($$v3!=''?($$v3)."/":''), ($$sort==3 ? 1 : ($$sort==4? 2 : 0)))?></th>
											<th colspan="2">Баланс <?=getSortStatus('/'.$$name_page.'/history/'.$$page_h.'/5/'.($$v2!=''?($$v2)."/":'').($$v3!=''?($$v3)."/":''),'/'.$$name_page.'/history/'.$$page_h.'/6/'.($$v2!=''?($$v2)."/":'').($$v3!=''?($$v3)."/":''), ($$sort==5 ? 1 : ($$sort==6? 2 : 0)))?></th>
											<th class="bt-ct">Примечание</th>
										</tr>
										<tr>
											<td><a href="javascript:void(0)" class="bt-date-o" onclick="shHiCal(0)"><span class="bt-date" id="in_data_period" ><?=$$date_input?></span></a><a href="javascript:void(0)" class="bt-calendar"><img src="/images/calendar.png" alt="" id="data_period" onclick="shHiCal(0)"/></a></td>
											<td>
												<select id="s_filter" class="bt-event" onchange="filter2link('e'+this.value);">
													<option id="filter_events" value="">Все события</option>
													<?php if ( $$event ) { 
													   foreach($$event as $id=>$name) { 
                                                            $sName = str_replace( '%username%', $_SESSION['login'], $name );
													       ?>
													<option value="<?=$id?>" <?=($$opselect==$id?'selected':'')?>><?=$sName?><?if($id==16):?>(EMP)<?endif;?><?if($id==52):?>(FL)<?endif;?></option>
													<?php } } ?>
												</select>
											</td>
											<td><input type="text" value="<?=$$f?>" size="5" class="bt-balance" id="filter_ammount" onKeyUp="if(event.keyCode == 13 && this.value != 0) { filter2link('f'+this.value); } else if(event.keyCode == 13 && this.value==0) { filter2link('a'); }"/></td>
											<td>
												<select class="bt-be" onchange="filter2link(this.value);" id="balans_select">
													<option value="a" <?=($$v3=='a'?'selected':'')?>>Все</option>
													<option value="p" <?=($$v3=='p'?'selected':'')?>>Ввод</option>
													<option value="m" <?=($$v3=='m'?'selected':'')?>>Вывод</option>
												</select>
											</td>
											<td></td>
										</tr>
									</thead>
									<? if($$history) { ?>
										<tfoot>
											<tr>
												<td colspan="5">
													<span class="output-of">
														Выводить по
														<select id="bh_pp" onchange="setBillHistoryPP(this.value)">
															<option value="10" <?= $$per_page == 10 ? 'selected="selected"' : '';?>>10</option>
															<option value="20" <?= $$per_page == 20 ? 'selected="selected"' : '';?>>20</option>
															<option value="50" <?= $$per_page == 50 ? 'selected="selected"' : '';?>>50</option>
															<option value="100" <?= $$per_page == 100 ? 'selected="selected"' : '';?>>100</option>
															<option value="0" <?= $$per_page == 0 ? 'selected="selected"' : '';?>>Все</option>
														</select>
													</span>
													Количество денег, потраченных на сервис: <?= round($$total_fm,2);?> руб.
												</td>
											</tr>
										</tfoot>
										<tbody>
											<?
											  foreach($$history as $k=>$val) {
												  $i++;
												  $comments = $val['comments'];												  
													  $sHistoryText = account::GetHistoryText($val);
													  $sHistoryText = str_replace( '%username%', $_SESSION['login'], $sHistoryText );
											?>
												<tr <?=($i%2!==0)?'class="bill-tbl-even"':''?>>
													<td><?=date("d.m.Y H:i", strtotime($val['op_date']));?></td>
                                                    <? if($val['project_id']) { ?>
													<td><strong id="bil<?=$val['id']?>"><a href="/projects/<?= $val['project_id'] ?>"><?=$sHistoryText?></a></strong></td>
                                                    <? } else { ?>
													<td><strong id="bil<?=$val['id']?>"><?=$sHistoryText?></strong></td>
                                                    <? } ?>
													<td class="bt-bb"><?= number_format( $val['balance'], 2, ',', ' ');?>&nbsp;руб.</td>
													<td <?=($val['ammount']>=0?"class='bt-bp'><span>+&nbsp;". round($val['ammount'],2).'</span>':"class='bt-bm'><span>&ndash;&nbsp;". round( $val['ammount']*-1, 2) ).'</span>';?></td>
													<td class="bt-c"><?=reformat(htmlspecialchars_decode($comments), 27, 0, 1)?></td>
												</tr>

											<? } ?>
										</tbody>
									<? }else{ ?>
										<tbody>
                                                                                        <tr>
												<td colspan="5">В данный период событий не было.</td>
											</tr>
											<tr>
												<td colspan="5">Количество денег, потраченных на сервис: <?= round($$total_fm,2);?> руб.</td>
											</tr>
										</tbody>
									<? } ?>
								</table>
								<?= new_paginator($$page_h, $$pages_h, 4, "%s/".$$name_page."/history/%d/".($$v1==0?intval($$v1+1)."/":$$v1."/").($$v2==""?0:($$v2."/")).($$v3!=''?($$v3)."/":"")."%s")?>
								<? //paginator($$page_h, $$pages_h, PAGINATOR_PAGES_COUNT, "%s/".$$name_page."/history/%d/".($$v1==0?intval($$v1+1)."/":$$v1."/").($$v2==""?0:($$v2."/")).($$v3!=''?($$v3)."/":"")."%s");?>

							</div>
						</div>
					</div>
				</div>
			</div>
{{include "footer.tpl"}}