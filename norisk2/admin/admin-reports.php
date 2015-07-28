<style>
@media print {
    *<.nr-a-tbl {display:none}
}
</style>
<script type="text/javascript">
var SBR; window.addEvent('domready', function() { SBR = new Sbr('adminFrm'); } );
</script>
<div class="norisk-admin c">
	<div class="norisk-in">
		<div class="form form-vigruzka">
			<b class="b1"></b>
			<b class="b2"></b>
			<div class="form-in">
                <form action="." method="post" id="adminFrm">
                <div>
                    <div class="form-block first">
                        <div class="form-el">
                            <label class="form-label">Период</label>
                            <span class="form-input">
                                <? include($_SERVER['DOCUMENT_ROOT'].'/norisk2/tpl.filter-period.php') ?>
                            </span>
                        </div>
                        <div class="form-el">
                            <label class="form-label">Валюта</label>
                            <ul class="form-input c">
                                <? foreach($EXRATE_CODES as $ex_code=>$ex) { if( !($ex_code==exrates::BANK||$ex_code==exrates::YM||$ex_code==exrates::WMR) ) continue; ?>
                                <li>
                                    <label><span class="i-chk"><input type="checkbox" name="filter[cost_sys][]" value="<?=$ex_code?>"<?=($filter['cost_sys'] && in_array($ex_code, $filter['cost_sys']) ? ' checked="checked"' : '')?> /></span><?=$ex[2]?>
                                    </label>
                                </li>
                                <? } ?>
                            </ul>
                        </div>
                    </div>
                    <div class="form-block last">
                        <div class="form-el form-btn">
                            <input type="submit" class="i-btn" value="Построить выгрузки" />
                            <input type="submit" name="ndfl" class="i-btn" value="Выгрузить НДФЛ" />
                            <input type="submit" name="act_rev" class="i-btn" value="Акт сверки" />
                            <input type="submit" name="yd_report" class="i-btn" value="Выплаты ЯД" />
                        </div>
                    </div>
                    <input type="hidden" name="site" value="<?=$site?>" />
                    <input type="hidden" name="mode" value="<?=$mode?>" />
                    <input type="hidden" name="action" value="" />
                </div>
                </form>
			</div>
			<b class="b2"></b>
			<b class="b1"></b>
		</div>
	</div>
</div>
