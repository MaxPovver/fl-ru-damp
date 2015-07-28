<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
if(!$sbr) exit;
// курсы обмена
$exrates = exrates::GetAll();
?>
<script type="text/javascript">
var SBR; window.addEvent('domready', function() { SBR = new Sbr('siteadminFrm'); } );
</script>
<h3>Статистика СБР</h3>
<div class="m-cl-bar">
    <form action="." method="get" id="siteadminFrm">
        Период:
        <? include($_SERVER['DOCUMENT_ROOT'].'/norisk2/tpl.filter-period.php') ?>
        <input type="hidden" name="site" value="stat" />
        <input type="submit" value="Показать" />
        &nbsp;&nbsp;
        <a href="/siteadmin/norisk2/?site=stat" class="lnk-dot-666">Сбросить фильтр</a>
    </form>
</div>
<? foreach($stats as $type=>$st) { ?>
<div class="nr-stat-one">
    <div class="nr-stat-one-h">
<!--
        <strong><?=sbr_meta::view_cost($st['total']['fm_sum'], exrates::FM)?></strong>
        <span>Количество: <?=$st['total']['cnt']?></span>
-->
        <h4><?=sbr_adm::$stat_graphs[$type]?></h4>
    </div>

<?
$vsegoSum = 0;
$vsegoSum += $st['graphs'][1]['total']['sum'] * $exrates["1" . exrates::BANK];
$vsegoSum += $st['graphs'][2]['total']['sum'] * $exrates["2" . exrates::BANK];
$vsegoSum += $st['graphs'][3]['total']['sum'] * $exrates["3" . exrates::BANK];
$vsegoSum += $st['graphs'][4]['total']['sum'] * $exrates["4" . exrates::BANK];
$vsegoSum += $st['graphs'][5]['total']['sum'] * $exrates["5" . exrates::BANK];

$vsegoCnt = 0;
$vsegoCnt += intval($st['graphs'][1]['total']['cnt']);
$vsegoCnt += intval($st['graphs'][2]['total']['cnt']);
$vsegoCnt += intval($st['graphs'][3]['total']['cnt']);
$vsegoCnt += intval($st['graphs'][4]['total']['cnt']);
$vsegoCnt += intval($st['graphs'][5]['total']['cnt']);
?>
    
<table>
    <tr>
        <td width="70">&nbsp;</td>
        <td width="120"><div style="background-color: #ccc; padding:5px; font-weight: bold;">WMZ</div></td>
        <td width="120"><div style="background-color: #ccc; padding:5px; font-weight: bold;">WMR</div></td>
        <td width="120"><div style="background-color: #ccc; padding:5px; font-weight: bold;">ЯД</div></td>
        <td width="120"><div style="background-color: #ccc; padding:5px; font-weight: bold;">Б/Н</div></td>
        <td width="120"><div style="background-color: #ccc; padding:5px; font-weight: bold;">Руб.</div></td>
        <td width="120"><div style="background-color: #ccc; padding:5px; font-weight: bold;">Всего, рублей</div></td>
    </tr>
    <tr>
        <td><div style="padding:5px;"><strong>Сумма:</strong></div></td>
        <td><div style="padding:5px;"><?=round($st['graphs'][2]['total']['sum'],2)?> $</div></td>
        <td><div style="padding:5px;"><?=round($st['graphs'][3]['total']['sum'],2)?> руб</div></td>
        <td><div style="padding:5px;"><?=round($st['graphs'][4]['total']['sum'],2)?> руб</div></td>
        <td><div style="padding:5px;"><?=round($st['graphs'][5]['total']['sum'],2)?></div></td>
        <td><div style="padding:5px;"><?=round($st['graphs'][1]['total']['sum'],2)?></div></td>
        <td><div style="padding:5px;"><?=round($vsegoSum,2)?></div></td>
    </tr>
    <tr>
        <td><div style="padding:5px;"><strong>Кол-во:</strong></div></td>
        <td><div style="padding:5px;"><?=intval($st['graphs'][2]['total']['cnt'])?></div></td>
        <td><div style="padding:5px;"><?=intval($st['graphs'][3]['total']['cnt'])?></div></td>
        <td><div style="padding:5px;"><?=intval($st['graphs'][4]['total']['cnt'])?></div></td>
        <td><div style="padding:5px;"><?=intval($st['graphs'][5]['total']['cnt'])?></div></td>
        <td><div style="padding:5px;"><?=intval($st['graphs'][1]['total']['cnt'])?></div></td>
        <td><div style="padding:5px;"><?=$vsegoCnt?></div></td>
    </tr>
</table>
<br/>

    <?
    // общие суммы для всей таблицы
    $total_sums = array();

    $n = 0; foreach($st['graphs'] as $sys=>$graph) { $n++; ?>
<!--
        <h5><?=$EXRATE_CODES[$sys][3]?>&nbsp;(<?=sbr_meta::view_cost($graph['total']['sum'])?>&nbsp;&nbsp;/&nbsp;&nbsp;<?=$graph['total']['cnt']?>)</h5>
-->
        <div>
            <div class="nr-as-tbl-out">
                <table class="nr-as-tbl">
                    <tbody>
                        <tr style="border-top: 1px solid #F0EFED;">
                            <td style="width:80px;"><?=$EXRATE_CODES[$sys][3]?></td>
                            <?
                              foreach($graph['months'] as $year=>$months) {
                                  if (!is_array($total_sums[$year])) {
                                      $total_sums[$year] = array();
                                  }
                                  foreach($months as $mon => $month) {
                                      if (!is_array($total_sums[$year][$mon])) {
                                          $total_sums[$year][$mon] = array('sum' => 0, 'cnt' => 0);
                                      }
                                      $total_sums[$year][$mon]['sum'] += $month['sum'] * $exrates[$sys . exrates::BANK];
                                      $total_sums[$year][$mon]['cnt'] += $month['cnt'];
                                      
                                      $sum = round($month['sum']);
                                      $ssum = $sum ? $sum : '&nbsp;';
                                      $cnt = $sum ? intval($month['cnt']) : '&nbsp';
                            ?>
                                <td class="<?=($year % 2)?'o':'e'?>">
                                    <span><small style="color:#333"><?= ($sys == exrates::FM ? ( (int) $ssum > 0 ? _bill($ssum) : '' ) : $ssum );?><br/><?=$cnt?></small></span>
                                    <? if($month['fm_sum'] && $st['total']['fm_max']) { ?>
                                    <div style="height:<?=(1+round(100 * ($month['fm_sum'] / $st['total']['fm_max'])))?>px"></div>
                                    <? } else { ?>
                                    <div class="empty"></div>
                                    <? } ?>
                                </td>
                            <? } } ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    <? } ?>
    
    
    <? // ГРАФИК С ОБЩИМИ СУММАМИ //////////////////// 
    
    // ищем максимальную сумму
    $max_sum = 0;
    foreach ($total_sums as $year => $months) {
        foreach ($months as $month) {
            $max_sum = $max_sum < $month['sum'] ? $month['sum'] : $max_sum;
        }
    }
    ?>
    
    <div>
        <div class="nr-as-tbl-out">
            <table class="nr-as-tbl">

                <tfoot>
                    <tr>
                        <th style="width:80px;">&nbsp;</td>
                        <?
                            $ycols = array();
                            foreach($graph['months'] as $year=>$months) {
                                $ycols[$year]=0;
                                foreach($months as $i=>$month) {
                                    $ycols[$year]++;
                        ?>
                            <th class="<?=($year % 2)?'o':'e'?>"><span><?=$i?></span></th>
                        <? } } ?>
                    </tr>
                    <tr>
                        <th style="width:80px;">&nbsp;</td>
                        <? foreach($ycols as $year=>$cs) { ?>
                            <th colspan="<?=$cs?>" class="<?=($year % 2)?'o':'e'?>"><strong><?=$year?></strong></th>
                        <? } ?>
                    </tr>
                </tfoot>

                <tbody>
                    <tr style="border-top: 1px solid #F0EFED;">
                        <td style="width:80px;">Всего, в рублях</td>
                        <?
                            foreach($total_sums as $year=>$months) {
                                foreach($months as $mon => $month) {
                                    $sum = round($month['sum']);
                                    $ssum = $sum ? $sum : '&nbsp;';
                                    $cnt = $sum ? intval($month['cnt']) : '&nbsp';
                        ?>
                            <td class="<?=($year % 2)?'o':'e'?>">
                                <span><small style="color:#333"><?=$ssum?><br/><?=$cnt?></small></span>
                                <div style="height:<?=(1+round(100 * ($month['sum'] / $max_sum)))?>px"></div>
                            </td>
                        <? } } ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>    
    
    <? //********************************************* ?>
    
    
    

</div>
<? } ?>
