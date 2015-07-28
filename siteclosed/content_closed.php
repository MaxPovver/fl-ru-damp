<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");
$stc = new static_compress;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta content="text/html; charset=windows-1251" http-equiv="Content-Type"/>
		<title>Удаленная работа (фри-ланс) на Free-lance.ru</title>
		
		<style type="text/css">
			*{
				margin: 0;
				padding: 0;
				}
			html, body{
				font: 12px Tahoma, Arial, sans-serif;
				color: #333;
				background: #fff url(/images/maintenance.jpg) no-repeat center center;
				height: 100%;
				}
			.container{
				width: 520px;
				height: 450px;
				position:absolute;
				top: 50%;
				left: 50%;
				margin: -206px 0 0 -260px;
				text-align:center;
				color: #5f5f5f;
				font: 18px 'Trebuchet MS', Tahoma;
				}
			.container h1{
				font: 22px 'Trebuchet MS', Tahoma;
				padding: 51px 0 21px;
				}
			.b-footer__counter{ position:relative; left:20px; border:0;}
			.b-social{ margin-top:90px !important; position:relative; left:25px;}
		</style>
	</head>
	<body>
		<div class="container">
			<img src="/images/logo.png" alt="Free-lance.ru" class="logo" />
			<h1>На сайте проводятся технические работы.</h1>
			<p>Free-lance.ru будет доступен для посещения с <?= IS_CLOSED_UNTIL?> по московскому времени. </p>
			<p>Приносим свои извинения за временные неудобства.</p>
			
						<div class="b-social b-social_width_240 b-social_center">
							<h4 class="b-social__title">Мы в социальных сетях:</h4>
							<ul class="b-social__list b-social__list_padbot_10">
									<li class="b-social__item"><a class="b-social__link b-social__link_v" target="_blank" href="http://vk.com/free_lanceru"></a></li>
									<li class="b-social__item"><a class="b-social__link b-social__link_f" target="_blank" href="http://www.facebook.com/freelanceru"></a></li>
									<li class="b-social__item"><a class="b-social__link b-social__link_t" target="_blank" href="https://twitter.com/free_lanceru "></a></li>
									<li class="b-social__item"><a class="b-social__link b-social__link_o" target="_blank" href="http://www.odnoklassniki.ru/freelanceru"></a></li>
									<li class="b-social__item"><a class="b-social__link b-social__link_y" target="_blank" href="http://www.youtube.com/user/rufreelance"></a></li>
							</ul>
						</div>
   <? include_once ('form.php'); ?>
            <br/><br/><br/><br/><br/>
		</div>
	</body>
</html>
