<?php
chdir(dirname(__FILE__));
require_once ('../classes/config.php');
require_once ('../classes/stdf.php');
require_once ('../classes/sitemap.php');

sitemap::create('articles', false);
sitemap::create('interview', false);
sitemap::create('portfolio', false);
sitemap::create('users', false);

?>