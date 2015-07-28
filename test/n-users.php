<?php

require_once '../classes/stdf.php';

if (!is_admin()) {
	die;
}

if (isset($_GET['users'])) {

	$DB = new DB('master');

	if ($_GET['users'] == 'freelancers') {
		$res = $DB->query("SELECT * FROM freelancer WHERE is_banned = B'0' AND ops_minus >= 3 ORDER BY ops_minus");
	}
	if ($_GET['users'] == 'employers') {
		$res = $DB->query("SELECT * FROM employer WHERE is_banned = B'0' AND ops_minus >= 3 ORDER BY ops_minus");
	}

	if (!empty($res)) {
		$fp = tmpfile();
		$filesize = 0;
		while ($row = pg_fetch_assoc($res)) {
			$str = "{$row['ops_minus']};http://{$_SERVER['HTTP_HOST']}/users/{$row['login']}\n";
			fwrite($fp, $str);
			$filesize += strlen($str);
		}

		header("HTTP/1.1 200 OK");
		header("Last-Modified: " . gmdate('D, d M Y H:i:s \G\M\T'));
		header("Content-Type: application/octet-stream");
		header("Content-Length: " . $filesize);
		header("Content-Disposition: attachment; filename=" . $_GET['users'] . '.csv');
		header("Proxy-Connection: close");

		fseek($fp, 0);
		$blocksize = 512;
		$first_sended_byte = 0;
		$last_sended_byte = -1;
		$sended = 0;

		while (($sended < $filesize) && !feof($fp)) {

			if ($sended + $blocksize > $filesize) {
				$bsize = $filesize - $sended;
			} else {
				$bsize = $blocksize;
			}

			echo fread($fp, $bsize);
			flush();

			$sended += $bsize;
			$last_sended_byte = $first_sended_byte + $sended - 1;

			if (connection_aborted()) break;
		}

	}

}

?><HTML>

<HEAD>
	<TITLE>Пользователи с 3-мя и более отрицательными отзывами</TITLE>
</HEAD>

<BODY>

	<form method="get" action="<?=$_SERVER['PHP_SELF']?>">
		<input type="hidden" name="users" value="freelancers">
		<input type="submit" value="Фрилансеры">
	</form>

	<form method="get" action="<?=$_SERVER['PHP_SELF']?>">
		<input type="hidden" name="users" value="employers">
		<input type="submit" value="Работодатели">
	</form>

</BODY>

</HTML>
