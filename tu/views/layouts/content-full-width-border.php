<?php
if(!defined('IN_STDF')) 
{
	header("HTTP/1.0 404 Not Found");
	exit;
}

global $css_file;
$css_file[] = "projects.css";


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

?>

        <?php echo $content ?>
