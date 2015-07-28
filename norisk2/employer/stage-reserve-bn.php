<script type="text/javascript">
var SBR = new Sbr('reserveFrm');
window.addEvent('domready', function() { SBR = new Sbr('reserveFrm'); } );
Sbr.prototype.ERRORS=<?=sbr_meta::jsInputErrors($sbr->error['reqv'], "ft{$form_type}[", "]")?>;
</script>
<div class="tabs-in nr-tabs-in2">
    <? include('tpl.stage-header.php') ?>
    <div class="form form-reserv" id="reserveBox">
        <?=$sbr->view_invoice_form($stage->id, $form_type, 1, $save_finance)?>
	</div>
    <? include('tpl.stage-msgs.php'); ?>
</div>
