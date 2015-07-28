<?php

$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/wizard.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/wizard.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");


function nextStep($pos, $id) {
    
}

function searchProject($string, $categories, $page=1, $type_loading = 1) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/search/search.php");
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php";
    $objResponse = new xajaxResponse();
    if(!$string) $string = "";
    
    $limit_project = 10;
    
    if($type_loading == 0) {
        $filter['active']   = 't';
        $filter['my_specs'] = 'f';
        if($categories[0] != '-1') {
            $filter['categories'][0] = array($categories[0] => "0");
            if($categories[1] != '-1') {
                unset($filter['categories'][0]);
                $filter['categories'][1] = array($categories[1] => "1");
            }
        }
        
        $project = new new_projects();
        $project->page_size = $limit_project;
        $projects = $project->getProjects($total, -1, $page, false, $filter, true, false, NULL, true);
    } else {
        $_SESSION['search_tab_active'] = "projects";
        $search = new search(false);
        $search->setUserLimit($limit_project);
        $search->addElement("projects", true, $limit_project);
        $filter['active']   = 't';
        $filter['my_specs'] = 'f';
        if($categories[0] != '-1') {
            $filter['categories'][0] = array($categories[0] => "0");
            if($categories[1] != '-1') {
                unset($filter['categories'][0]);
                $filter['categories'][1] = array($categories[1] => "1");
            }
        } else {
            $filter['categories'] = null;
        }
        $filter['is_closed_contest'] = true;
        $search->search($string, $page, $filter);

        $element  = $search->getElements();
        $total    = $element['projects']->total;
        $projects = $element['projects']->results;
    }
    
    $max_page = ( ceil($total/$limit_project) );
    $next_page = $page+1;
    if($projects) {
        if($page > 1) {
            $dont_show_hint = true;
        }
        ob_start();
        include($_SERVER['DOCUMENT_ROOT'].'/wizard/registration/steps/tpl.step.search.project.php');
        $html = ob_get_clean();
        $objResponse->script("$('project_search_hint').show()");
    } else {
        $html = "Совпадений не найдено";
        $objResponse->script("$('project_search_hint').hide()");
    }
    
    if($next_page > $max_page) {
        $objResponse->script("$('load_project').getElement('.b-button').addClass('b-button_disabled')");
    } else {
        $objResponse->script("$('load_project').getElement('.b-button').removeClass('b-button_disabled')");
        $objResponse->assign("page-search", "value", $next_page);
    }
    if($page > 1) {
        $objResponse->assign("project_loader_content", "innerHTML", $html);
        $objResponse->script("var html = new Element('span', {html:$('project_loader_content').get('html')});
                              $('project_content').adopt(html);");
        $objResponse->assign("project_loader_content", "innerHTML", "");
    } else {
        $objResponse->assign("project_content", "innerHTML", $html);
    }
    
    return $objResponse;
}

$xajax->processRequest();