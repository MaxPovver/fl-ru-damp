        <? if($gr==55 && $allow_love){ ?>
        <div id="valentin">
    <div class="form fs-o f14">
		<b class="b1"></b>
		<b class="b2"></b>
		<div class="form-in">
			<h2>Спешите знакомиться, влюбляться и приглашать на ужин при свечах.</h2>
			<p>Этот раздел блогов будет скрыт через&nbsp; <span class="ic-o"><span><span id="big_timer">00 часов 00 минут 00 секунд</span></span></span>&nbsp;&nbsp;<a href="<?=($_SESSION['login']? '#bottom': '/fbd.php')?>" class="lnk-dot-grey">Не упустите свой шанс!</a></p>
		</div>
		<b class="b2"></b>
		<b class="b1"></b>
	</div>
            <script type="text/javascript">
var launchdate2=new cdLocalTime("big_timer", '<?=date("F d, Y H:i:s")?>', 0, '<?=VALENTIN_DATE_END?>');
launchdate2.displaycountdown("days", formatresults)
</script>
        </div>
        <? } ?>
