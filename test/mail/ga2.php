<?php

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/statistic/StatisticFactory.php");






///s.php?t=0&y=2014&l=b5abed10c9930d169f498b1e483baa79&h=0b10162959f17150cec25696a634ad62
print_r(StatisticHelper::track_url(0, 2014, 'kazakov1'));


exit;

/*
$ga = StatisticFactory::getInstance('GA', array(
    'v' => 1,
    'tid' => 'UA-49048745-1',
    'cid' => md5('UA-49048745-1'),
));
*/

$ga = StatisticFactory::getInstance('GA');

//var_dump($ga->getOptions());
//exit;

$data = array(
    '2006' => 3459,
    '2009' => 6876,
    '2008' => 7099,
    '2011' => 7012,
    '2010' => 6981,
    '2012' => 6951,
    '2007' => 6789,
    '2013' => 3499,
    'total' => 48666
);

var_dump(
        $ga->newsletterNewProjectsFrl($data)
        );

var_dump(
        $ga->newsletterNewProjectsEmp($data)
        );



/*
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/vendors/UniversalAnalytics/vendor/autoload.php");


$ua = new \UniversalAnalytics\UA(array(
    'v' => 1,
    'tid' => 'UX-XXXX-XXX',
    'cid' => 555,
));

print_r($ua);
exit;
 */


/*
class Statistic_GA
{
    
}

class Statistic_YM
{
    
}

class Statistic_KeenIO
{
    
}



class StatisticFactory
{
    public static function getInstanse($name, $options) {
        //tgodo        $class = ucfirst($make);
        $filename = sprintf('%s/Cars/%s.php', __DIR__, $class);

        if (file_exists($filename)) {
            require_once $filename;
            return new $class;
        } else {
            throw new Exception('Car not found!');
        }
    }
    
    
}


$obj = StatisticFactory::getInstanse('GA', $options);

$obj->send('newsletterFrlNewProjects',$data);*/