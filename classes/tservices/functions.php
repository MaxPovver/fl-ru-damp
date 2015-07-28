<?php



/*
 * @todo: перенес в stdf.php
function mb_unserialize($string) 
{
    $string = preg_replace('/s:(\d+):"([^"]*)";/se', "'s:'. strlen('\\2') .':\"\\2\";'", $string);
    //$string = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $string);
    return unserialize($string);
}
*/


/**
* Проверка данных из формы
*/

function tu_validation(&$tservice, $is_exist_feedbacks = 0)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_categories.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/validation.php");
    
    $errors = array();
    $validator = new validation();
    $tservices_categories = new tservices_categories();

    //---

    //$tservice->title = trim(htmlspecialchars(InPost('title'),ENT_QUOTES,'cp1251'));
    //$tservice->title = antispam(__paramInit('string', NULL, 'name', NULL, 60, TRUE));
    
    $tservice->title = sentence_case(__paramInit('html', NULL, 'title', NULL, 100, TRUE));
    $title = trim(stripslashes(InPost('title')));
    
    if (!$validator->required($title)) {
        $errors['title'] = validation::VALIDATION_MSG_REQUIRED;
    } elseif(!$validator->symbols_interval($title,4,100)){
        $errors['title'] = sprintf(validation::VALIDATION_MSG_SYMBOLS_INTERVAL,4,100);
    }
    
    //---
        
    $tservice->price = intval(trim(InPost('price')));

    if (!$validator->is_natural_no_zero($tservice->price)) {
        $errors['price'] = validation::VALIDATION_MSG_REQUIRED_PRICE;
    }elseif (!$validator->greater_than_equal_to($tservice->price,300)){    
        $errors['price'] = sprintf(validation::VALIDATION_MSG_PRICE_GREATER_THAN_EQUAL_TO,'300 р.');
    } elseif (!$validator->less_than_equal_to($tservice->price, 999999)) {
        $errors['price'] = sprintf(validation::VALIDATION_MSG_PRICE_LESS_THAN_EQUAL_TO,'999 999 р.');
    }
 
    //---
        
    $days_db_id = intval(trim(InPost('days_db_id')));
        
    if (!$validator->is_natural_no_zero($days_db_id) || 
        !in_array($days_db_id, array(1,2,3,4,5,6,7,8,9,10,14,21,30,45,60,90)))
    {
        $errors['days'] = validation::VALIDATION_MSG_FROM_LIST;
        $days_db_id = 1;
    }
        
    $tservice->days = $days_db_id;        
        
    //---

    //Если есть отзывы то не даем изменить категорию
    if(!(InPost('action') == 'save' && $is_exist_feedbacks > 0))        
    {      
        $category_id = intval(trim(InPost('category_db_id')));
        $parent_category_id = $tservices_categories->getCategoryParentId($category_id);

        if ($parent_category_id === FALSE) {
            $errors['category'] = validation::VALIDATION_MSG_CATEGORY_FROM_LIST;
        } else {
            $tservice->category_id = $category_id;
            //$this->property()->parent_category_id = $parent_category_id;
        }
    }
        
    //---

    $str_tags = trim(preg_replace('/\s+/s', ' ', strip_tags(InPost('tags'))));
    $tags = (strlen($str_tags) > 0) ? array_unique(array_map('trim', explode(',', $str_tags))) : array();
    $tags = array_filter($tags, function($el) {
        $len = strlen(stripslashes($el));
        return $len < 80 && $len > 2;
    });
    $tags_cnt = count(array_unique(array_map('strtolower', $tags)));
    $tags = array_map(function($value){return htmlspecialchars($value,ENT_QUOTES, "cp1251");}, $tags);
    $tservice->tags = $tags;

    if (!$validator->required($str_tags)) {
        $errors['tags'] = validation::VALIDATION_MSG_REQUIRED;
    } elseif ($tags_cnt > 10) {
        $errors['tags'] = sprintf(validation::VALIDATION_MSG_MAX_TAGS,10);
    }

    //---

    $videos = __paramInit('array',NULL,'videos',array());  
    $videos = (is_array($videos))?array_values($videos):array();

    if (count($videos)) {
        $tservice->videos = NULL;
        foreach ($videos as $key => $video) {
            if ($validator->required($video)) {
                $_video_data = array(
                    'url' => $video,
                    'video' => FALSE,
                    'image' => FALSE);

                //$_video = $validator->video_validate($video);
                $_video = $validator->video_validate($video);
                $is_error = TRUE;
                    
                if ($_video) {
                    $_video_data['url'] = $_video;
                    
                    if ($_video_meta = $validator->video_validate_with_thumbs($_video, 0)) {
                        $_video_data = array_merge($_video_data, $_video_meta);
                        $is_error = FALSE;
                    }
                } 
                        
                if($is_error){
                    $errors['videos'][$key] = validation::VALIDATION_MSG_BAD_LINK;
                }

                $tservice->videos[$key] = $_video_data;
           }
       }
   }


   //---

   
   //$tservice->description = trim(htmlspecialchars(InPost('description'),ENT_QUOTES, "cp1251"));
   //$description = trim(InPost('description'));
   
   $tservice->description = trim(__paramInit('html', NULL, 'description', NULL, 5000, TRUE));
   $description = trim(stripslashes(InPost('description')));
   
   
   if (!$validator->required($description)){
       $errors['description'] = validation::VALIDATION_MSG_REQUIRED;
   } elseif (!$validator->symbols_interval($description,4,5000)){
       $errors['description'] = sprintf(validation::VALIDATION_MSG_SYMBOLS_INTERVAL,4,5000);
   }

   
   //---

   
   //$tservice->requirement = trim(htmlspecialchars(InPost('requirement'),ENT_QUOTES, "cp1251"));
   //$requirement = trim(InPost('requirement'));
   
   $tservice->requirement = trim(__paramInit('html', NULL, 'requirement', NULL, 5000, TRUE));
   $requirement = trim(stripslashes(InPost('requirement')));
   
   if (!$validator->required($requirement)) {
       $errors['requirement'] = validation::VALIDATION_MSG_REQUIRED;
   } elseif (!$validator->symbols_interval($requirement,4,5000)) {
       $errors['requirement'] = sprintf(validation::VALIDATION_MSG_SYMBOLS_INTERVAL,4,5000);
   }


   //---

   
   $extra = __paramInit('array',NULL,'extra',array());  
   $extra = (is_array($extra))?array_values($extra):array();
   $total_extra_price = 0;

   if (count($extra)) {
       $key = 0;
       $tservice->extra = NULL;
       foreach ($extra as $el) {
           if (isset($el['title'], $el['price'], $el['days_db_id'])) {
               
               $el['title'] = stripslashes($el['title']);
               $title = trim(htmlspecialchars($el['title'],ENT_QUOTES, "cp1251"));
               $title_native = trim($el['title']);
               
               $price = trim($el['price']);

               if (!$validator->required($title_native) && !$validator->required($price)) continue;

               $is_title = $validator->min_length($title_native, 4) && $validator->max_length($title_native, 255);
               $is_price = $validator->is_integer_no_zero($price) && $validator->numeric_interval($price, -999999, 999999);

               if (!$is_price) $errors['extra'][$key]['price'] = validation::VALIDATION_MSG_REQUIRED_PRICE;
               if (!$is_title) $errors['extra'][$key]['title'] = sprintf(validation::VALIDATION_MSG_SYMBOLS_INTERVAL,4,255);

               $days = trim($el['days_db_id']);
               $is_days = $validator->is_natural($days) && $validator->less_than_equal_to($days, 5);
               if (!$is_days) {
                   $errors['extra'][$key]['days'] = sprintf(validation::VALIDATION_MSG_INTERVAL,'0','5 дней');
                   $days = 1;
               }
                    
               $price = intval($price);
               $days = intval($days);
                    
               $tservice->extra[$key] = array('title' => $title, 'price' => $price, 'days' => $days);
                    
               $key++;
               if($price < 0) $total_extra_price += $price;
          }
      }
  }
  
  
  //---
  
  $tservice->is_express = 'f';
  $tservice->express_price = 0;
  $tservice->express_days = 1;
        
  if (InPost('express_activate') == 1 && $tservice->days > 1) {
      $express = InPost('express');
      $price = trim($express['price']);
      if (!$validator->is_natural_no_zero($price) ||
          !$validator->less_than_equal_to($price, 999999)) 
      {
          $errors['express']['price'] = validation::VALIDATION_MSG_REQUIRED_PRICE;
      }
     
      $days_db_id = intval(trim($express['days_db_id']));

      if (!$validator->is_natural_no_zero($days_db_id) || 
          !in_array($days_db_id, array(1,2,3,4,5,6,7,8,9,10,14,21,30,45,60,90)))
      {
          $errors['express']['days'] = validation::VALIDATION_MSG_FROM_LIST;
          $days_db_id = 1;
      }

            
      $tservice->is_express = 't';
      $tservice->express_price = intval($price);
      $tservice->express_days = $days_db_id;
  }

        
  //---
        
        
  //Проверка общей суммы с учетом скидок, опций (срочность не учитываю так как она выбирается по желанию)
  if(!isset($errors['price']) && !$validator->greater_than_equal_to($tservice->price + $total_extra_price,300))
  {
      $errors['price'] = sprintf(validation::VALIDATION_MSG_PRICE_MIN_TOTAL,'300 р.');
  }
        
        
  //---

            
  //TODO: Есть проблема с контроллом выпадающего списка
  // он не отрабатывает новое значение укзанное по умолчанию
  if (!in_array(intval(InPost('distance')), array(1, 2))) {
      $errors['distance'] = validation::VALIDATION_MSG_FROM_RADIO;
  } else if (intval(InPost('distance')) == 2) {
      $city_db_id = intval(InPost('city_db_id'));
      $city = new city();
      if($city_db_id <= 0 || !$city->getCityName($city_db_id)){
          $errors['distance'] = validation::VALIDATION_MSG_CITY_FROM_LIST;
      }else{
          $tservice->city = intval(InPost('city_db_id'));
          $tservice->is_meet = 't';
      }
  } else {
      $tservice->is_meet = 'f';
  }


  //---


  $tservice->agree = InPost('agree') == 1 ? 't' : 'f';
  if ($tservice->agree === 'f'){
      $errors['agree'] = validation::VALIDATION_MSG_ONE_REQUIRED;
  }
        
        
  //---
        
  
  if(in_array(InPost('active'),array(0,1)))
  {
      $tservice->active = intval(InPost('active')) == 1 ? 't' : 'f';
      if($tservice->is_angry) $tservice->active = 't';
  }

  
  //---
  
  //Вырезаем слеши если ошибка
  if(count($errors) > 0)
  {
      $attrs = array('title','description','requirement','tags');
      foreach($attrs as $attr)
      {
          if(is_array($tservice->{$attr}))
          {
             foreach($tservice->{$attr} as &$value) 
                $value = stripslashes($value); 
          }
          else
          {
            $tservice->{$attr} = stripslashes($tservice->{$attr});
          }
      }
  }
  
  
  return $errors;
}