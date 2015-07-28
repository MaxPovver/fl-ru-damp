<?php

define('BASE_PATH','/var/www/_beta/html');
define('BASE_HOST','http://beta.fl.ru');

$dirs = array(
    //'/html/main2013/',
    'CSS Blocks' => '/css/block/'//,
    //'HTML'  => '/html/main2013/'
);


?>
<style>
    table td {vertical-align: top;}
</style>
<table width="100%">
    <tr>
        <td>
<?php
$idx = 0;
foreach($dirs as $key => $path)
{
    echo '<h1>' . $key . '</h1>';
    
    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(BASE_PATH . $path), RecursiveIteratorIterator::SELF_FIRST);
    $objects = new RegexIterator($objects, '/^.+\.(css|php|html)$/i', RecursiveRegexIterator::GET_MATCH);
    
    if(count($objects))
    {
        foreach($objects as $name => $object)
        {
            $link = BASE_HOST . str_replace(BASE_PATH, '', $object[0]);
?>
    <a target="_blank" href="<?=$link?>"><?=$link?></a><br/>
<?php            
        }
    }
    $idx++;
    
    if(count($dirs) > $idx) echo '</td><td>';
}
?>
        </td>
    </tr>
    </table>
<?php
    