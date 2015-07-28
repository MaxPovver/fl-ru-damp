<?   
  $rpath='../';
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stat_collector.php");
  session_start();
  if(!hasGroupPermissions('administrator') && $_SESSION['login']!='sll') { header('Location: /404.php'); exit; }
  $header = "../header.php";
  $content = "content.php";
  $footer = "../footer.html";
  include ("../template.php");
?>
