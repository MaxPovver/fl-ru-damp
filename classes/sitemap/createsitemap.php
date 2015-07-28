<?php
chdir(dirname(__FILE__));
require_once "../stdf.php";
require_once "../sitemap.php";


$help = "
--blogs\t\tCreate blogs sitemap\n
--commune\tCreate commune sitemap\n  
--projects\tCreate project sitemap\n  
--users\tCreate users sitemap\n
--portfolio\tCreate portfolio sitemap\n
--articles\tCreate articles sitemap\n
--interview\tCreate interview sitemap\n
--regions\tCreate region sitemap\n
--catalog\tCreate freelancers catalog sitemap\n
--userpages\tCreate user pages sitemap\n
--tservices\tCreate tservices sitemap\n
";

$type = preg_replace('/^--/', '', $argv[1]);
$send = SERVER==='release';


if($type == 'all') {
   foreach(sitemap::$types as $t=>$p)
    	sitemap::create($t, $send);
   	sitemap::generateMainSitemap();
   	if($send) {
   		sitemap::send();
   	}
}
else if(sitemap::$types[$type]) {
    sitemap::create($type, $send);
    sitemap::generateMainSitemap();
   	if($send) {
   		sitemap::send();
   	}
}
else {
    echo $help;
}

?>