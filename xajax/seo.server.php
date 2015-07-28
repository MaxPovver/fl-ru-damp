<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/xajax/seo.common.php';
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/seo.php");

/**
 * Загрузка списка доступных позиций
 * @param  integer  $parent_section   ID родителя раздела
 * @param  integer  $direct_id
 * @return object xajaxResponse
 */
function getPositions($parent_section = false, $direct_id = null) {
  $objResponse = new xajaxResponse();
  $seo = new seo($_SESSION['subdomain']);
  $positions = $seo->getPositions($parent_section, $direct_id);

  $section_num = 0;
  $sections = $seo->getSections(false, $direct_id);
  if($sections) {
    foreach($sections as $section) {
      $section_num++;
      if($section['id']==$parent_section) { break; }
    }
  }

  for($n=1; $n<=$positions; $n++) {
    $html .= "<option value={$n}>{$section_num}.{$n}</option>";
  }
  if($positions) {
    $html .= "<option value=-1 selected>Последний</option>";
  } else {
    $html .= "<option value=1>{$section_num}.1</option>";
  }
  $objResponse->script('$("new_position").set("html","'.$html.'");');
  $objResponse->script('$("new_position").set("disabled", false);');
  return $objResponse;
}

/**
 * Загрузка формы для создания/редактирования раздела
 *
 * @param integer|boolean $parent_section если не false - загрузка формы создания подраздела
 * @return object xajaxResponse
 */
function loadForm($parent_section = false, $direct_id = null) {
    session_start();
    if(!hasPermissions('seo')) return false;
    $objResponse = new xajaxResponse();
    
    $seo = new seo($_SESSION['subdomain']);
    $directions = $seo->getDirections();
    $subdomains = $seo->getSubdomains(false);
    if($parent_section) {
        $sections = $seo->getSections(false, $direct_id);
    }
    
    $subdomain_id = $seo->subdomain['id'];
    
    
    //$disabled_position = true;
    $positions = $seo->getPositions($parent_section, $direct_id);

    $is_subcategory = ($parent_section ? true : false);
    $form_section['direct_id'] = $direct_id;

    ob_start();
    include($_SERVER['DOCUMENT_ROOT'] . '/catalog/admin/tpl.form-section.php');
    $html = ob_get_clean();
    
    $objResponse->assign("form_content", "innerHTML", $html);
    $objResponse->script("window.addEvent('domready', function() { var KeyWord = __key(1); KeyWord.bind(document.getElementById('kword_se'), kword, {bodybox:'body_1', maxlen:120}); CKEDITOR.replaceAll('ckeditor');});");
    
    return $objResponse;
}

/**
 * Загрузка формы для редактирования раздела (подраздела)
 *  
 * @param integer $section_id      ИД Раздела
 * @param integer $parent_section  ИД родителя если есть (для подразделов)
 * @return object xajaxResponse
 */
function loadFormEdit($section_id, $parent_section=false, $direct_id = null) {
    session_start();
    if(!hasPermissions('seo')) return false;
    $objResponse = new xajaxResponse();
    
    $is_edit = true;
    
    $seo = new seo($_SESSION['subdomain']);
    $subdomains = $seo->getSubdomains(false);
    
    if($parent_section) {
        $sections = $seo->getSections(false, $direct_id);
    }
    $positions = $seo->getPositions($parent_section, $direct_id);
    $form_section = $seo->getSectionById($section_id);
    if ($direct_id) {
        $form_section['direct_id'] = $direct_id;
    }
    $subdomain_id = $form_section['subdomain_id'];
    $directions = $seo->getDirections();

    $is_subcategory = ($parent_section ? true : false);
    
    ob_start();
    include($_SERVER['DOCUMENT_ROOT'] . '/catalog/admin/tpl.form-section.php');
    $html = ob_get_clean();
    
    $objResponse->assign("form_content", "innerHTML", $html);
    $objResponse->script("window.addEvent('domready', function() { var KeyWord = __key(1); KeyWord.bind(document.getElementById('kword_se'), kword, {bodybox:'body_1', maxlen:120}); CKEDITOR.replaceAll('ckeditor');});");
    
    return $objResponse;    
}

/**
 * Создание раздела/подраздела
 *
 * @param array $info Данные создания
 * @return object xajaxResponse
 */
function createSection($info, $action = 'create') {
   global $GLOBALS;
   session_start();
   if(!hasPermissions('seo')) return false;
    
   $objResponse = new xajaxResponse();
   $info['id']           = intval($info['id']);
   $info['bind']         = intval($info['bind']);
   $info['subdomain']    = intval($info['subdomain']);
   $info['parent']       = intval($info['parent']);
   $info['old_position'] = intval($info['old_position']);
   $info['new_position'] = intval($info['new_position']);
   $info['direct_id']    = $direct_id = intval($info['direction']);
   $info['old_direction']    = intval($info['old_direction']);

   $saved_disable_link_processing = $GLOBALS['disable_link_processing'];
   $GLOBALS['disable_link_processing'] = TRUE;
   $section = array("bind"             => 0, 
                    "parent"           => (int)    $info['parent'], 
                    "name_section"     => (string) change_q_x($info['name_section'], true),  
                    "name_section_link"=> (string) change_q_x($info['name_section_link'], true),
                    "meta_description" => (string) change_q_x($info['meta_description'], true), 
                    "meta_keywords"    => (string) change_q_x($info['meta_keywords'], true), 
                    "content_before"   => (string) __paramValue('ckeditor', $info['content_before']),
                    "content_after"    => (string) __paramValue('ckeditor', $info['content_after']),
                    "date_create"      => "NOW();");
   $GLOBALS['disable_link_processing'] = $saved_disable_link_processing;

   $seo = new seo($_SESSION['subdomain']);  

   if ($info['direct_id']) {
       $section['direct_id'] = $info['direct_id'];
   } else {
       $objResponse->script("alert('Не указано направление');"); 
       $objResponse->script("$('s_direction').focus();"); 
       return $objResponse;
   }

   if($info['is_subcategory'] && $info['subdomain'] == 0) {
       $objResponse->script("alert('Не указан регион');"); 
       return $objResponse;
   }
   
   if(trim($info['name_section']) == "") {
       $objResponse->script("alert('Введите название раздела');"); 
       $objResponse->script("$('name_section').focus();"); 
       return $objResponse;
   }
   
   if(preg_match('/[^A-Za-z0-9_\-]/', $info['name_section_link'])) {
       $objResponse->script("alert('Название ссылки раздела должно содержать только латинские буквы, цифры, нижнее подчеркивание или тире');"); 
       $objResponse->script("$('name_section_link').focus();"); 
       return $objResponse;
   }

   if($seo->checkLink('section', $section['name_section_link'], $info['direct_id'], $info['parent'], $info['id'], $info['subdomain'])) {
        $objResponse->script("alert('В выбранном разделе уже есть элемент с такой ссылкой');");
        $objResponse->script("$('name_section_link').focus();");
        return $objResponse;
   }
                      
   switch($action) {
       default:
       case 'create':
           $section['name_section']     = stripslashes($section['name_section']);
           $section['meta_description'] = stripslashes($section['meta_description']);
           $section['meta_keywords']    = stripslashes($section['meta_keywords']);
           $section['content_before']   = stripslashes($section['content_before']);
           $section['content_after']    = stripslashes($section['content_after']);
           
           $new_section = $seo->createSection($section, $info['subdomain'], (int)$info['is_draft']);

           $section['id'] = $new_section['id'];
           $pos_old = $new_section['pos'];

           $section['pos_num'] = (int) $info['new_position'];
           if($section['pos_num']!=-1) {
              $seo->updatePosition($pos_old, $info['new_position'], $info['parent'], $direct_id);
              $seo->updateSection($section['id'], $section, $info['bind'], $info['subdomain'], (int)$info['is_draft']);
           }

           if($info['is_subcategory']) {
              $success_text  = "Подраздел успешно добавлен";
              $url['msgok'] = 5;
           } else {
              $success_text  = "Раздел успешно добавлен";
              $url['msgok'] = 1;
           }
           break;
       case 'update':
           if($info['old_parent'] == $info['parent']) {
               $section['pos_num'] = (int) $info['new_position'];
               if ($info['old_direction'] != $direct_id) {
                   $seo->updatePosition($info['old_position'], null, $info['parent'], $info['old_direction']);
                   $section['pos_num'] = intval($seo->getPositions($section['parent'], $direct_id)) + 1;
               } else {
                   if($info['new_position']==-1) {
                      $section['pos_num'] = intval($seo->getPositions($section['parent'], $direct_id)) ;
                      $info['new_position'] = $section['pos_num'];
                   }
                   $seo->updatePosition($info['old_position'], $info['new_position'], $info['parent'], $direct_id);
               }
               $seo->updateSection($info['id'], $section, $info['bind'], $info['subdomain'], (int)$info['is_draft']);
           } else {

               $new_position = $seo->getPositions($info['parent'], $direct_id) + 1;
               $section['pos_num'] = (int) $new_position;
               $seo->updatePositionsByParent($info['old_parent'], $info['old_position'], $direct_id);
               $seo->updateSection($info['id'], $section, $info['bind'], $info['subdomain'], (int)$info['is_draft']);

               if($info['new_position']!=-1) {
                  $pos_old = $section['pos_num'];
                  $pos_new = $info['new_position'];
                  $seo->updatePosition($pos_old, $pos_new, $info['parent'], $direct_id);
                  $section['pos_num'] = $pos_new;
                  $seo->updateSection($info['id'], $section, $info['bind'], $info['subdomain'], (int)$info['is_draft']);
               }
           }
           
           $section['id'] = $info['id'];

           if($info['is_subcategory']) {
              $success_text  = "Подраздел успешно изменен";
              $url['msgok'] = 6;
           } else {
              $success_text  = "Раздел успешно изменен";
              $url['msgok'] = 2;
           }
           break;
   }
   if($info['old_position'] != $info['new_position'] && $info['parent'] == 0) {
//       $objResponse->script("location.href = '/catalog/admin/'");
       $dir = $seo->getDirectionById($info['direct_id']);
        $url['direction'] = $dir['name_section_link'];
        if ($_SESSION['subdomain']) {
            $url['subdomain'] = $_SESSION['subdomain'];
        }
        $url = "/catalog/admin/?" . http_build_query($url);
        $objResponse->redirect($url);
       return $objResponse;
   }
   if($info['old_direction'] != $info['direct_id']) {
       $dir = $seo->getDirectionById($info['direct_id']);
        $url['direction'] = $dir['name_section_link'];
        if ($_SESSION['subdomain']) {
            $url['subdomain'] = $_SESSION['subdomain'];
        }
        $url = "/catalog/admin/?" . http_build_query($url);
        $objResponse->redirect($url);
        return $objResponse;
   }
   
   $section = $seo->getFullSectionById($info['parent'] != 0?$info['parent']:$section['id']);  
   
   ob_start();
   include($_SERVER['DOCUMENT_ROOT'] . '/catalog/admin/tpl.section.php');
   $html = ob_get_clean();

   if($info['parent'] != 0) {
       $objResponse->assign("section_{$info['parent']}", "innerHTML", $html);
       $objResponse->script("init_collapse_button('section_{$section['id']}');"); // активируем кнопку разворачивания подменю
       $objResponse->script("$('section_{$info['parent']}').addClass('active');"); 
   } else if ($action == 'update') {
       $objResponse->assign("section_{$section['id']}", "innerHTML", $html);
       $objResponse->script("init_collapse_button('section_{$section['id']}');"); // активируем кнопку разворачивания подменю
       $objResponse->script("$('section_{$section['id']}').addClass('active');"); 
   } else {
       $objResponse->script("var section = new Element('li#section_{$section['id']}');
                            $('section_content').adopt(section);");
       $objResponse->assign("section_{$section['id']}", "innerHTML", $html);
   }
   
   
   
   if($info['parent'] != $info['old_parent'] && $action == 'update') {
       $section = $seo->getFullSectionById($info['old_parent']);  
       
       ob_start();
       include($_SERVER['DOCUMENT_ROOT'] . '/catalog/admin/tpl.section.php');
       $html = ob_get_clean();
        
       $objResponse->assign("section_{$info['old_parent']}", "innerHTML", $html);
       $objResponse->script("init_collapse_button('section_{$info['old_parent']}');"); // активируем кнопку разворачивания подменю
       $objResponse->script("$('section_{$info['old_parent']}').removeClass('active');"); 
   }
   
   
   $objResponse->assign("form_content", "innerHTML", "<div class=\"b-fon b-fon_width_full b-fon_padbot_17\"><div class=\"b-fon__body b-fon__body_pad_10 b-fon__body_padleft_35 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf b-fon__body_bordbot_dfedcf\"><span class=\"b-icon b-icon_sbr_gok b-icon_margleft_-25\"></span><span>{$success_text}</span></div></div>");
   
   return $objResponse;
}

/**
 * Удаление разделов (подразделов)
 *
 * @param integer $section_id ИД раздела
 * @param inetger $sub        ИД родителя подраздела (для удаления подразделов)
 * @return object xajaxResponse
 */
function deleteSection($section_id, $sub=false) {
    session_start();
    if(!hasPermissions('seo')) return false;
    
    $objResponse = new xajaxResponse();
   
    
    $seo = new seo($_SESSION['subdomain']);
    $section = $seo->getSectionById($section_id);
    $direction = $seo->getDirections($section['direct_id']);
    $seo->deleteSection($section_id);
    
    if($sub) {
        $section = $seo->getFullSectionById($sub);  
       
        ob_start();
        include($_SERVER['DOCUMENT_ROOT'] . '/catalog/admin/tpl.section.php');
        $html = ob_get_clean();
        
        $objResponse->assign("section_{$sub}", "innerHTML", $html);
        $objResponse->script("init_collapse_button('section_{$sub}');"); // активируем кнопку разворачивания подменю
    } else {
        $objResponse->script("location.href = '/catalog/admin/?direction={$direction[0]['name_section_link']}'");
    }
    
    return $objResponse;        
}

/**
 * Загрузка формы редактирования содержания поддомена (региона)
 *
 * @param integer $subdomain   ИД поддомена (региона)
 * @return object xajaxResponse
 */
function loadMainForm($subdomain, $is_save=false, $msgtext='') {
    session_start();
    
    $objResponse = new xajaxResponse();
    $seo = new seo(intval($subdomain));  
    $seo->getLoadMainFormTemplate($objResponse, $is_save, $msgtext);
   
    return $objResponse;
}

/**
 * Редактированеи содержания поддомена (региона)
 *
 * @param array $info    Данные поддомена (региона)
 * @return object xajaxResponse
 */
function updateContentSubdomain($info) {
    session_start();
    if(!hasPermissions('seo')) return false;
    $objResponse = new xajaxResponse();
    
    $update = array("meta_description" => $info['meta_description'],
                    "meta_keywords"    => $info['meta_keywords'],
                    "content"          => $info['content']);
    $seo = new seo((int)$info['subdomain']);
    $seo->updateContentSubdomain($update, $info['subdomain']);
    
    /* @todo Зарефакторить надо */
    $seo->subdomain['meta_description'] = $info['meta_description'];
    $seo->subdomain['meta_keywords']    = $info['meta_keywords'];
    $seo->subdomain['content']          = $info['content'];
    
    $seo->getLoadMainFormTemplate($objResponse, true);
    
    return $objResponse; 
}

function setTranslit($text) {
    $objResponse = new xajaxResponse();
    $objResponse->assign("name_section_link", "value", translit($text));  
    return $objResponse;  
}


/**
 * Загрузка формы для создания/редактирования направления
 *
 * @param integer $id если не null - загрузка формы создания редактирования
 * @return object xajaxResponse
 */
function loadDirectForm($id = null, $is_save = null) {
    session_start();
    if(!hasPermissions('seo')) return false;
    $objResponse = new xajaxResponse();
    
    $id = intval($id);
    
    if ($id) {
        $seo = new seo($_SESSION['subdomain']);
        $form_data = $seo->getDirectionById($id);
    }

    ob_start();
    include($_SERVER['DOCUMENT_ROOT'] . '/catalog/admin/tpl.form-direction.php');
    $html = ob_get_clean();
    
    $objResponse->assign("form_content", "innerHTML", $html);
    $objResponse->script("window.addEvent('domready', function() { var KeyWord = __key(1); KeyWord.bind(document.getElementById('kword_se'), kword, {bodybox:'body_1', maxlen:120}); CKEDITOR.replaceAll('ckeditor'); CSRF(_TOKEN_KEY); });");
    
    return $objResponse;
}

/**
 * Сохраняет/редактирует направление
 *
 * @param array $info - данные
 * @return object xajaxResponse
 */
function saveDirectForm($info) {
    global $GLOBALS;
    session_start();
    if (!hasPermissions('seo'))
        return false;
    $objResponse = new xajaxResponse();

    $seo = new seo($_SESSION['subdomain']);    

    if (trim($info['name_section']) == "") {
        $objResponse->script("alert('Введите название направления');");
        $objResponse->script("$('name_section').focus();");
        return $objResponse;
    }

    if (preg_match('/[^A-Za-z0-9_\-]/', $info['name_section_link']) || trim($info['name_section_link']) == "") {
        $objResponse->script("alert('Название ссылки должно содержать только латинские буквы, цифры, нижнее подчеркивание или тире');");
        $objResponse->script("$('name_section_link').focus();");
        return $objResponse;
    }

    if($seo->checkLink('direct', $info['name_section_link'], $info['id'])) {
        $objResponse->script("alert('Направление с такой ссылкой уже есть');");
        $objResponse->script("$('name_section_link').focus();");
        return $objResponse;
    }

    $saved_disable_link_processing = $GLOBALS['disable_link_processing'];
    $GLOBALS['disable_link_processing'] = TRUE;
    $data = array(
        "dir_name"          => (string) change_q_x($info['name_section'], true),
        "name_section_link" => (string) change_q_x($info['name_section_link'], true),
        "meta_description"  => (string) change_q_x($info['meta_description'], true),
        "meta_keywords"     => (string) change_q_x($info['meta_keywords'], true),
        "page_content"      => (string) __paramValue('ckeditor', $info['content']),
    );
    $GLOBALS['disable_link_processing'] = $saved_disable_link_processing;

    if (!$info['id']) {
        $data['date_create'] = 'NOW()';
    } else {
        $data['date_modified'] = 'NOW()';
    }
    
    $newid = $seo->saveDirection($data, $info['id']);
    
    if (!$info['id'] && $newid) {
        $url = array();
        $url['direction'] = $newid;
        if ($info['name_section_link']) {
            $url['direction'] = $info['name_section_link'];
        }
        if ($_SESSION['subdomain']) {
            $url['subdomain'] = $_SESSION['subdomain'];
        }
        $url['msgok'] = 3;
        $url = "/catalog/admin/?" . http_build_query($url);
        $objResponse->redirect($url);
    } elseif ($info['id'] && $newid) {
        $url['direction'] = $info['name_section_link'];
        if ($_SESSION['subdomain']) {
            $url['subdomain'] = $_SESSION['subdomain'];
        }
        $url['msgok'] = 4;
        $url = "/catalog/admin/?" . http_build_query($url);
        $objResponse->redirect($url);
    } else {
        $objResponse->alert('Ошибка');
    }
    
    return $objResponse;
}

function deleteDirection($id) {
    session_start();
    if (!hasPermissions('seo'))
        return false;
    $objResponse = new xajaxResponse();
    
    $id = intval($id);
    
    if (!$id) {
       $objResponse->alert('Идентификатор направления не указан');
       return $objResponse;
    }
    
    $seo = new seo();
    if (!$seo->deleteDirection($id)) {
       $objResponse->alert('Ошибка удаления');
       return $objResponse;
    }
    
    $objResponse->redirect('/catalog/admin/?direction=-1');
    
    return $objResponse;
}

$xajax->processRequest();
?>