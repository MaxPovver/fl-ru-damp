<?if($$calendar) {$calendar = $$calendar; }?>
<?if($$name_page) {$name_page = $$name_page; }?>
<?if($$month_name) {$month_name = $$month_name; }?>

<div class="bc-sel">
<span class="cl-lr">
<?
$pre_month = $month-1;
$nex_month = $month+1;
$pre_year  = $year;
$nex_year = $year;
if($pre_month<1) { $pre_month = 12; $pre_year = $year-1; }
if($nex_month>12) { $nex_month = 1; $nex_year = $year+1; }
?>
												<a href="javascript:void(0)" class="bc-sel-l" onClick="xajax_changeCalendarMonth('<?=$pre_month?>', '<?=$pre_year?>')"></a>
												<? if($month == date('m') && $year == date('Y')): ?>
												    <span class="bc-sel-r"></span>
												<? else:?>
												    <a href="javascript:void(0)" class="bc-sel-r" onClick="xajax_changeCalendarMonth('<?=$nex_month?>', '<?=$nex_year?>')"></a>
												<? endif; ?>

											</span>
<select name="month" id="month_cal" onChange="xajax_changeCalendarMonth(this.value, $('year_cal').value)" style="z-index:100">
<? for($i=1;$i<=12;$i++): ?>
    <option value="<?=$i<10?"0".$i:$i?>" <?=($month==$i?'selected':'')?>><?=$month_name[$i]?></option>
<? endfor; ?>
</select> 
<select name="year" id="year_cal" onChange="xajax_changeCalendarMonth($('month_cal').value, this.value)" style="z-index:100">
<? for($i=$year-10;$i<=date('Y');$i++): ?>
    <option value="<?=$i?>" <?=($year==$i?'selected':'')?>><?=$i?></option>
<? endfor; ?>
</select>
										</div>
										<table class="block_blue_right_table" id="calendar_table_content">
											<tr>
													<th>Пн</th>
													<th>Вт</th>
													<th>Ср</th>
													<th>Чт</th>
													<th>Пт</th>
													<th>Сб</th>
													<th>Вс</th>
												</tr>
												<?
												
												$fday = mktime(0,0,0, $month, 1, $year);
												$eday  = mktime(0,0,0,$month, $monthDay, $year);
												$prevMonth = $fday-1;
												
												$firstMonthWeekDay = date('w', $fday);
												$endMonthWeekDay   = date('w', $eday);
												$prevMonthDays = date('d', $prevMonth);
												
												if($firstMonthWeekDay==0) $firstMonthWeekDay = 7;
												if($endMonthWeekDay==0) $endMonthWeekDay = 7;
												
												
												echo "<tr>";
												for($i=$firstMonthWeekDay-1;$i>0;$i--) {
													echo "<td><em>".($prevMonthDays-($i-1))."</em></td>";		
												}
												for($i=1;$i<=8-$firstMonthWeekDay;$i++) {
													echo "<td ".($day==$i?'class="selected"':'').($calendar[$i]==$i?'class="white_bg"':'').">".($calendar[$i]==$i?'<a '.($day==$i?'style="color:white"':'class="cl_link"').' onClick="document.location = \'/'.$name_page."/history/1/1/".($i<10?"0".$i:$i).$month.$year.'/\'" href="/'.$name_page."/history/1/1/".($i<10?"0".$i:$i).$month.$year.'/">':'').$i.($calendar[$i]==$i?'</a>':'')."</td>";		
												}
												echo "</tr>";
												for($k=$i, $j=0;$k<=$monthDay;$k++, $j++) {
													if($j==0) { echo "<tr>"; }
													echo "<td ".($day==$k?'class="selected"':'').($calendar[$k]==$k?'class="white_bg"':'').">".($calendar[$k]==$k?'<a '.($day==$k?'style="color:white"':'class="cl_link"').' onClick="document.location = \'/'.$name_page."/history/1/1/".($k<10?"0".$k:$k).$month.$year.'/\'" href="/'.$name_page."/history/1/1/".($k<10?"0".$k:$k).$month.$year.'/">':'').$k.($calendar[$k]==$k?'</a>':'')."</td>";
													if($j==6) { echo "</tr>"; $j=-1; }
												}
												
												
												if($endMonthWeekDay != 7) {
													for($i=$endMonthWeekDay,$k=1;$i<7;$i++,$k++) {
														echo "<td><em>$k</em></td>";
													}
													echo "</tr>";
												}
												?>	
										</table>
										<div class="bc-btn">
											 <a href="javascript:void(0)"><img src="/images/btn-close.png" alt="Закрыть" onClick="$$('.bill-calendar').setStyle('display', 'none')" class="close_date_content"/></a> <a href="javascript:void(0)" onClick="calendar2Link();"><img src="/images/btn-set.png" alt="Применить" /></a> 
										</div>

