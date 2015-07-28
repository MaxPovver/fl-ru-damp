<script type="text/javascript">
window.addEvent('domready', 
    function() {
        var SEARCH = new search('main-search-form');
        SEARCH.init();  
        
        if(document.getElementById('search-request')) {
            var KeyWord = __key(1);
            KeyWord.bind(document.getElementById('search-request'), kword, {bodybox:"body_1", maxlen:120});    
        }  
        
        $$('.b-pager__link').each( function(elm) {
            var link = elm.href;
            
            link = link.replace(/\+/gi, "%2B");
            link = link.replace(/#/gi, "%23");
            
            elm.href = link;
        });
    }
);
</script>

<div id="content-content">
    <a name="search"></a>
    <div id="content-inner" class="search-page">
        <?php
        $after_block = false;
        $is_matches  = true;
        $view_advanced = true;
        
        $uid_fields = null;
        
        switch($type) {
            default:
                $type ="users"; 
            case "users_test":
            case "users":
                $name_advanced_search = 'Расширенный поиск';
                $search_advanced_tpl = "tpl.form-users.php";
                $search_block_tpl = "tpl.search-users.php";
                break;
            case "projects_test":
            case "projects":
                $name_advanced_search = 'Расширенный поиск';
                $search_lenta_css = "search-prj";
                $search_advanced_tpl = "tpl.form-projects.php";
                $search_block_tpl = "tpl.search-projects.php";
                break;
            case "works":
                if(!$search_lenta_css) $search_lenta_css = "c";  
                
            case "messages":
                
                $uid_fields = array(
                    'from_id' => 'f_',
                    'to_id' => 't_'
                );
                
            case "commune":
                if(!$search_lenta_css) $search_lenta_css = "search-commune";    
            case "blogs":
            case "notes":
                if(!$search_lenta_css) $search_lenta_css = "search-own";
                
                if (!$uid_fields) {
                    $uid_fields = array('user_id' => '');
                }
                
            case "articles":
                $after_block = true;
                $is_matches  = true;
                if($element->total > 0) $is_matches  = false;
                $sections    = true;
                $view_advanced = false;
                if($_SESSION['search_string']) $search_advanced_tpl = "tpl.form-sections.php";
                $search_block_tpl = "tpl.search-{$type}.php";

                //Получаем значения is_profi и расширяем выдачу результата поиска
                if (in_array($type, array('messages','notes')) && 
                    count($element->results) && 
                    $uid_fields) {
                    
                    $ids = array();
                    foreach ($element->results as $result) {
                        foreach ($uid_fields as $uid_field => $uid_pfx) {
                            if(is_emp($result[$uid_pfx . 'role'])) {
                                continue;
                            }
                            
                            $ids[] = $result[$uid_field];
                        }
                    }
                    
                    if (count($ids)) {
                        $ids = array_unique($ids);
                        $freelancer = new freelancer();
                        $list = $freelancer->getUsersProfi($ids);
                        if($list) {
                            foreach($element->results as $key => $result) {
                                foreach ($uid_fields as $uid_field => $uid_pfx) {
                                    $_uid = $result[$uid_field];
                                    if(isset($list[$_uid])) {
                                        $element->results[$key][$uid_pfx . 'is_profi'] = $list[$_uid];
                                    }
                                }
                            }
                        }
                    }
                }
                
                break;    
        }
        include ($_SERVER['DOCUMENT_ROOT']."/search/tpl.form-search.php");
        
        if($element->results) {
            $cntResultPage = count($element->results);
        } else {
            $cntResultPage = 0;
        }
        if($element->total <= $set_usr_limit && $cntResultPage != $element->total) {
            $totalSearch = $cntResultPage;
        } else {
            $totalSearch = $element->total;
        }
        
        if($_GET['action'] == 'search' && $_SESSION['search_string'] == '') {
            $is_not_search_string = true;
        }
        
        if ($top_projects_cnt) {
            $is_not_search_string = false;
            $element->total = count($top_projects);
            foreach ($top_projects as $k => $v) {
                $top_projects[$k]['logo_path'] = $v['logo']; 
            }
            $element->results = $top_projects;
        }
        ?>
        
        <?php if(($is_matches && $is_search) || $is_not_search_string) { ?>
        <br/>
                    <? if($type=='users') { ?>
                    <a class="b-button b-button_flat b-button_flat_green b-page__ipad b-page__iphone" href="<?= "/masssending/?from_search=2&search_count=" . $totalSearch . "&" . $_SERVER['QUERY_STRING'] ?>">Рассылка по фрилансерам</a>
                    <? } ?>
        <div class="search-rama">
            <div class="search-finded">
																<p>
                    <? if($type=='users') { ?>
                    <a id="masssend-to-users" class="b-button b-button_flat b-button_flat_green b-button_float_right b-page__desktop" href="<?= "/masssending/?from_search=2&search_count=" . $totalSearch . "&" . $_SERVER['QUERY_STRING'] ?>">Рассылка по фрилансерам</a>
                    <? } ?>
                    <?php if($totalSearch > 0 ) {?>
                    Найдено <?=$totalSearch?> <?= ending($totalSearch, "совпадение", "совпадения", "совпадений")?> 
                    <?php } else {//if?>
                    <?= $is_not_search_string?"Введите поисковый запрос":"Совпадений не найдено"?>
                    <?php } //else?>
                </p>
            </div>
        </div>
        <?php } // if?>
        
        <div class="search-lenta <?= $search_lenta_css?>">
        <?php  
        if($element->total > 0 && $element->results) {
        $first_element = true;
        $i = $offset = $element->getProperty('offset');
            foreach($element->results as $num=>$result) {$i++;
                include($_SERVER['DOCUMENT_ROOT']."/search/tpl/{$search_block_tpl}");
                $first_element = false;
                // в зависимости от раздела поиска
            }
            
        $query_string_menu = str_replace('%','%%', $query_string_menu);
        $pages = ceil($element->total/$element->getProperty('limit'));
        print(new_paginator($page, $pages, 3, "%s/search/?type={$type}&{$query_string_menu}&page=%d%s"));
        } //if ?>  
        </div><!--/search-lenta-->
        <?php if($element->total > 0 && $element->results) {
            $bottomLimitBlock = $type === 'users';
            include($_SERVER['DOCUMENT_ROOT']."/search/tpl.user-limit-block.php");
        }//if?>
    </div><!--/#content-inner-->
</div><!--/#content-content-->
