<?php
$timer = $_SESSION['login_wait_time']-time();
if($timer<0) { $timer = 0; }
?>
<div class="b-layout__right b-layout__right_float_right b-layout__right_width_240">
    <!-- Banner 240x400 -->
    <?= printBanner240(false); ?>
    <!-- end of Banner 240x400 -->
</div>
<div class="b-layout__left b-layout__left_margright_270">
<h1 class="b-page__title">Неправильный пароль</h1>
<div class="b-layout__txt">Вы более <?=$max_login_tries?> раз набрали неправильный пароль. Подождите <span id="login_wait_timer"><?=floor($timer/60)?>:<?=( ($timer-floor($timer/60)*60)<10 ? '0'.($timer-floor($timer/60)*60) : ($timer-floor($timer/60)*60))?></span> минут, прежде чем продолжить.</div>
</div>
<script type="text/javascript">
document.addEvent('domready', function() {
	var timers = { timer: <?=$timer?> };

	var TimeTic = function () {
		this.timer--;
		if(this.timer>=0) {
			var m = Math.floor(this.timer/60);
			var s = this.timer - Math.floor(this.timer/60)*60;
			if(s<10) {
				s = '0'+s;
			}
			$('login_wait_timer').set('html', m+':'+s);
		} else {
			$clear(timer);
		}
  	}
	var timer = TimeTic.periodical(1000, timers);

});
</script>
