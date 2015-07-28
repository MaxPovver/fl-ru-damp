<script type="text/javascript">
var SBR = new Sbr('reserveFrm');
window.addEvent('domready', function() { SBR = new Sbr('reserveFrm'); } );
</script>
<div class="tabs-in nr-tabs-in2">
    <? include('tpl.stage-header.php') ?>
    <div class="form form-reserv">
		<div class="form-h">
			<b class="b1"></b>
			<b class="b2"></b>
			<div class="form-h-in">
                <h3>Исполнитель принял условия проекта и ожидает резервирования денежных средств для начала работы</h3>
			</div>
		</div>
		<div class="form-in">
			<div class="form-block first">
				<div class="form-el">
					<p>Обратите внимание, исполнитель может приступить к работе только в том случае, если полная сумма проекта зарезервирована.</p>
				</div>
			</div>
			<div class="form-block form-reserv-tbl">
				<table>
                    <col />
					<col width="95" />
                    <? foreach($sbr->scheme['taxes'][1] as $tax_id=>$tax) { if($tax['not_used']) continue; ?>
                      <col width="125" />
                    <? } ?>
                    <col width="125" />
					<thead>
						<tr>
                        <th style="text-align:left">Задача</th>
                        <th>Стоимость работы, в т.ч. НДС</th>
                        <? foreach($sbr->scheme['taxes'][1] as $tax_id=>$tax) { if($tax['not_used']) continue; ?>
                           <th><?=$tax['name']?><?=$tax['percent'] ? ' (' . ($tax['percent']*100) . '%)' : ''?></th>
                        <? } ?>
                        <th>Итого</th>
							</tr>
					</thead>
                    <tfoot>
                        <tr>
                            <th colspan="<?=count($sbr->scheme['taxes'][1])+3?>">Итого: <span><?=sbr_meta::view_cost($sbr->reserve_sum, $sbr->cost_sys, false)?></span></th>
                        </tr>
                        <tr> 
                            <td colspan="<?=count($sbr->scheme['taxes'][1])+3?>" class="nds"><?=str_replace(' - ', ' &mdash; ', $ndss)?></td> 
                        </tr> 
                    </tfoot>
					<tbody>
                        <? foreach($sbr->stages as $stg) { $t=0; if(!$stg->data['num']) $stg_0 = $stg; ?>
						<tr>
                            <td style="text-align:left"><a href="?site=Stage&id=<?=$stg->id?>"><?=reformat($stg->name,25,0,1)?></a></td>
                            <td><?=sbr_meta::view_cost($stg->cost, $sbr->cost_sys, false)?></td>
                            <? foreach($sbr->scheme['taxes'][1] as $tax_id=>$tax) { if($tax['not_used']) continue; $t+=($ts = round($stg->calcTax($tax),2));  ?>
                               <td><?=sbr_meta::view_cost($ts, NULL, false)?></td>
                            <? } ?>
                            <td><?=sbr_meta::view_cost($stg->cost + $t, NULL, false)?></td>
						</tr>
                        <? } ?>
					</tbody>
				</table>
			</div>
			<div class="form-block last">
       <? if ($no_reserve) { ?>
       <div class="form fs-p">
           <b class="b1"></b>
           <b class="b2"></b>
           <div class="form-in">
               <p>Сумма «Безопасной Сделки» не может быть менее <?= sbr_stages::MIN_COST_RUR ?> рублей. 
                   Пожалуйста, нажмите кнопку "Отказаться, поместить проект в черновики" и 
                   внесите исправления в поле "Бюджет". После этого вы можете 
                   начать «Безопасную Сделку» заново.</p>                    
           </div>
           <b class="b2"></b>
           <b class="b1"></b>
       </div>
       <? } ?>

				<div class="form-el">
                    <div class="nr-prj-btns c">
                        <span class="btn-o-green">
                            <a href="javascript:;" onclick="<?= !$no_reserve ? 'SBR.sendForm()' : '' ?>" class="btnr btnr-<?= !$no_reserve ? 'green2' : 'disabled' ?>"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Зарезервировать деньги под проект</span></span></span></a>
                            <?
                            switch ($sbr->cost_sys)
                            {
                                case exrates::BANK :
                            ?> 
                            <form action="." method="get" id="reserveFrm">
															<div>
                                <input type="hidden" name="site" value="<?=$site?>" />
                                <input type="hidden" name="id" value="<?=$stage->data['id']?>" />
                                <input type="hidden" name="bank" value="1" />
															</div>
                            <?
                                break;
                                case exrates::WMR :
                                case exrates::WMZ :
                                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wmpay.php");
                                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pmpay.php");
                                    $wm = new wmpay();
                                    $pm = new pmpay();
                            ?>
                            <form method="POST" action="https://paymaster.ru/Payment/Init" id="reserveFrm">
															<div>
                                <input type="hidden" name="LMI_MERCHANT_ID" value="<?=$pm->merchants[pmpay::MERCHANT_SBR]?>" />
                                <input type="hidden" name="LMI_PAYMENT_AMOUNT" value="<?=round($sbr->reserve_sum, 2)?>" />
                                <input type="hidden" name="LMI_CURRENCY" value="RUB" />
                                <input type="hidden" name="LMI_PAYMENT_DESC_BASE64" value="<?=base64_encode(iconv('CP1251', 'UTF-8', 'Оплата по договору-оферте ' . $sbr->getContractNum() . '. ' . $ndss . '. Счет #' .$account->id. ', логин ' . $sbr->getLogin() )) ?>" />
                                <input type="hidden" name="LMI_PAYMENT_NO" value="<?=$pm->genPaymentNo()?>" />
                                <input type="hidden" name="LMI_SIM_MODE" value="0" />
                                <input type="hidden" name="PAYMENT_BILL_NO" value="<?=$account->id?>" />
                                <input type="hidden" name="OPERATION_TYPE" value="<?=sbr::OP_RESERVE?>" />
                                <input type="hidden" name="OPERATION_ID" value="<?=$sbr->id?>" />
															</div>
                            <?  
                                break;
                                case exrates::YM :
                            ?>
                            <form name="ydpay" method="POST" action="http://money.yandex.ru/eshop.xml" id="reserveFrm">
															<div>
                                <input name="scid" value="3428" type="hidden" />
                                <input type="hidden" name="ShopID" value="<?=ydpay::SHOP_SBR_RESERVE?>" />
                                <input type="hidden" name="Sum" value="<?=$sbr->reserve_sum?>" />
                                <input type="hidden" name="CustomerNumber" value="<?=$account->id?>" />
                                <input type="hidden" name="OPERATION_TYPE" value="<?=sbr::OP_RESERVE?>" />
                                <input type="hidden" name="OPERATION_ID" value="<?=$sbr->id?>" />
															</div>
                            <? } ?>
                            </form>
                        </span>

                    </div>
                    <p class="nr-finish"><strong>Способ оплаты:</strong> <?=$EXRATE_CODES[$sbr->cost_sys][3]?> (<a href="?site=editstage&id=<?=$stg_0->data['id']?>">сменить</a>)</p>
				</div>
			</div>
            <? if(DEBUG) { ?>
                <div class="form-block last">
                    <div class="form-el">
                        <div class="nr-prj-btns c">
                                <span class="btn-o-green">
                                    <? if (!$no_reserve) { ?>
                                    <a href="javascript:;" onclick="SBR.submitLock(document.getElementById('commonFrm'), {action:'test_reserve'})" class="btnr btnr-green2"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Я тестю</span></span></span></a>
                                    <? } else { ?>
                                    <a href="javascript:;" class="btnr btnr-disabled"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Я тестю</span></span></span></a>
                                    <? } ?>
                                </span>
                        </div>
                    </div>
                </div>
            <? } ?>
		</div>
		<b class="b2"></b>
		<b class="b1"></b>
	</div>
    <form action="." method="post" id="commonFrm">
			<div>
        <input type="hidden" name="site" value="<?=$site?>" />
        <input type="hidden" name="id" value="<?=$stage->data['id']?>" />
        <input type="hidden" name="action" value="" />
			</div>
    </form>
    <? include('tpl.stage-msgs.php'); ?>
</div>
