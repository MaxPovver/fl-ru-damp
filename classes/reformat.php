<?
/**
 * Файл переформировывания папок под файлы
 */

//$dir = "/home/admin/free-lance.ru/html/users/";
$dir = "/proj/free-lance2/users/";
if ($dh = opendir($dir)) {
	while (($file = readdir($dh)) !== false) {
		if (($file=="..") or ($file==".")) continue;
		@mkdir($dir . substr($file, 0, 2)."/");
		if (file_exists($dir . substr($file, 0, 2)."/upload/")){
			mkdir($dir . substr($file, 0, 2) . "/" . substr($file, 0, 2) . "/");
			rename($dir . $file . "/upload/", $dir . substr($file, 0, 2) . "/". $file . "/upload/");
			rename($dir . $file . "/foto/", $dir . substr($file, 0, 2) . "/". $file . "/foto/");
			rename($dir . $file . "/resume/", $dir . substr($file, 0, 2) . "/". $file . "/resume/");
			rename($dir . $file . "/contacts/", $dir . substr($file, 0, 2) . "/". $file . "/contacts/");
			continue;
		}
		rename($dir . $file . "/", $dir . substr($file, 0, 2) . "/". $file . "/");
	}
	closedir($dh);
}

?>