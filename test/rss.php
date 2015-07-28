<?php

require_once '../classes/stdf.php';
require_once '../classes/projects.php';
require_once '../classes/freelancer.php';

switch ( $argv[1] ) { 
    
    case 'yandex-office': {
        new_projects::yandexGenerateRss( empty($argv[2])? 'upload/yandex-office.xml': $argv[2], array(4) );
        break;
    }
    
    case 'yandex-project': {
        new_projects::yandexGenerateRss( empty($argv[2])? 'upload/yandex-project.xml': $argv[2], array(1, 2, 7));
        break;
    }
    
    case 'joobradio': {
        new_projects::jobradioGenerateRss('upload/jobradio.xml');
        break;
    }

    case 'careerjet': {
        new_projects::careerjetGenerateRss('upload/careerjet.xml');
        break;
    }

    case 'webprof': {
        freelancer::webprofGenerateRss('upload/webprof.xml');
        break;
    }

    case 'jooble': {
        new_projects::joobleGenerateRss('upload/jooble.xml');
        break;
    }

    case 'indeed': {
        new_projects::indeedGenerateRss('upload/indeed.xml');
        break;
    }

    
}