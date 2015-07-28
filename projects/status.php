<?php

/*
 * Обрабатываем смену статуса проекта по ссылке
 */

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_helper.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_status.php");

session_start();

$uid = get_uid();

//Нужна авторизация
if($uid <= 0)
{
    header("Location: /registration/");
    exit;
}


$project_id = __paramInit('string','project_id','project_id',NULL);
$status = __paramInit('string','status','status',NULL);
$hash = __paramInit('string','hash','hash',NULL);
$current_hash = projects_helper::getStatusHash(array(
    'project_id' => $project_id,
    'status' => $status
));




//Проверка корректности входных параметров по хешу
if($hash !== $current_hash)
{
    header("Location: /404.php");
    exit;
}

$obj_project = new projects();
$project = $obj_project->GetPrjCust($project_id);

//Если нет такого проекта или юзер непричастен к нему то 404
if(!$project || (($project['user_id'] != $uid) && ($project['exec_id'] != $uid)))
{
    header("Location: /404.php");
    exit;
}


$is_emp = is_emp();
$attr = array(
    'is_emp' => $is_emp,
    'project' => $project
);
$offer = array();    
    
if($project['exec_id'])
{
    $obj_offer = new projects_offers();
    $offer = $obj_offer->GetPrjOffer($project['id'], $project['exec_id']);
    
    if(!$offer) 
    {
        header("Location: /404.php");
        exit;
    }
    
    $attr['offer'] = $offer;    
}


$projectsStatus = new projects_status();
$projectsStatus->attributes($attr);
$projectsStatus->changeStatus($status);


header("Location: ". getFriendlyURL("project", $project));
exit;