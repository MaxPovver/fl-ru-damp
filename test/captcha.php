<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/captcha.php");
$captcha = new captcha();
$captcha->setNumber();
?>

<img src="/test/captcha-image.php?type=1" border="1"> <img src="/test/captcha-image.php?type=1&n=1" border="1"> <img src="/test/captcha-image.php?type=1&s=1" border="1">
<br>
<img src="/test/captcha-image.php?type=2" border="1"> <img src="/test/captcha-image.php?type=2&n=1" border="1"> <img src="/test/captcha-image.php?type=2&s=1" border="1">
<br>
<img src="/test/captcha-image.php?type=3" border="1"> <img src="/test/captcha-image.php?type=3&n=1" border="1"> <img src="/test/captcha-image.php?type=3&s=1" border="1">
<br>
<img src="/test/captcha-image.php?type=4" border="1"> <img src="/test/captcha-image.php?type=4&n=1" border="1"> <img src="/test/captcha-image.php?type=4&s=1" border="1">
<br>
<img src="/test/captcha-image.php?type=1&f=1" border="1"> <img src="/test/captcha-image.php?type=1&f=1&n=1" border="1"> <img src="/test/captcha-image.php?type=1&s=1&f=1" border="1">
<br>
<img src="/test/captcha-image.php?type=2&f=1" border="1"> <img src="/test/captcha-image.php?type=2&f=1&n=1" border="1"> <img src="/test/captcha-image.php?type=2&s=1&f=1" border="1">
<br>
<img src="/test/captcha-image.php?type=3&f=1" border="1"> <img src="/test/captcha-image.php?type=3&f=1&n=1" border="1"> <img src="/test/captcha-image.php?type=3&s=1&f=1" border="1">
<br>
<img src="/test/captcha-image.php?type=4&f=1" border="1"> <img src="/test/captcha-image.php?type=4&f=1&n=1" border="1"> <img src="/test/captcha-image.php?type=4&s=1&f=1" border="1">


<br><br>

<img src="/test/captcha-image.php?type=2&s=1&f=1" border="1">
<br>
<img src="/test/captcha-image.php?type=2&s=2&f=1" border="1">

<br>
!!!<img src="/test/captcha-image.php?type=2&s=2&f=1&l=1" border="1">!!!
<br>
<img src="/test/captcha-image.php?type=2&s=2&f=1&l=2" border="1">
<br>
<img src="/test/captcha-image.php?type=2&s=2&f=1&l=3" border="1">

<br>
<img src="/test/captcha-image.php?type=2&s=2&f=1&l=1&cc=1" border="1">
<br>
<img src="/test/captcha-image.php?type=2&s=2&f=1&l=2&cc=1" border="1">
<br>
<img src="/test/captcha-image.php?type=2&s=2&f=1&l=3&cc=1" border="1">

<br>
<img src="/test/captcha-image.php?type=2&s=2&f=1&l=1&acc=1" border="1">
<br>
<img src="/test/captcha-image.php?type=2&s=2&f=1&l=2&acc=1" border="1">
<br>
<img src="/test/captcha-image.php?type=2&s=2&f=1&l=3&acc=1" border="1">

<br>
<img src="/test/captcha-image.php?type=2&s=2&f=1&l=1&cc=1&acc=1" border="1">
<br>
<img src="/test/captcha-image.php?type=2&s=2&f=1&l=2&cc=1&acc=1" border="1">
<br>
<img src="/test/captcha-image.php?type=2&s=2&f=1&l=3&cc=1&acc=1" border="1">
