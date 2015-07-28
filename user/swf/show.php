<?
// sawa: Ќе пон€л, где это используетс€. ≈сли используетс€, то надо выкинуть обращени€ к file_exists()
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
  $user = __paramInit('string', 'user', NULL);
  $file = __paramInit('string', 'f', NULL);
  $src_dir = $_SERVER['DOCUMENT_ROOT'].'/users/'.substr($user,0,2).'/'.$user.'/upload';
  $dst_dir = $_SERVER['DOCUMENT_ROOT'].'/users/'.substr($user,0,2).'/'.$user.'/swf/';
  $src     = $src_dir.'/'.$file;
  $dst     = $dst_dir.'/'.$file;
  if(!$user || !$file || !file_exists($src))                 { header('Location: /404.php'); exit; }
  if(!file_exists($dst_dir) && !mkdir($dst_dir, 0777, true)) { header('Location: /404.php'); exit; }
  if(!file_exists($dst) && !copy($src, $dst))                { header('Location: /404.php'); exit; }
  ob_start();
?>
<html>
  <head>
    <style>body{margin:0 0 0 0}</style>
  </head>
  <body>
    <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab#version=9,0,16,0" width="100%" height="100%">
      <param name="movie" value="/users/<?=$user?>/swf/<?=$file?>" />
      <param name="quality" value="high" />
      <param name="bgcolor" value="white" />
      <param name="AllowScriptAccess" value="never" />
      <embed src="/users/<?=$user?>/swf/<?=$file?>" width="100%" height="100%" quality="high" bgcolor="white" align="center" type="application/x-shockwave-flash" AllowScriptAccess="never" pluginspage="http://www.macromedia.com/go/getflashplayer" />
    </object>
  </body>
</html>
<?
  $str = ob_get_clean();
  $str = preg_replace('/>\s+</','><',$str);
  $str = preg_replace('/\r?\s/',' ',$str);
  print(trim($str));
?>
