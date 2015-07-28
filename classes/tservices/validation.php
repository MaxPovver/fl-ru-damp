<?php

/**
 * Валидация данных
 */
class validation 
{
    
    const VALIDATION_MSG_SYMBOLS_INTERVAL               = 'Поле должно содержать от %d до %d символов';
    const VALIDATION_MSG_REQUIRED                       = 'Заполните это поле';//'Обязательно для заполнения.'
    const VALIDATION_MSG_REQUIRED_PRICE                 = 'Укажите стоимость';
    const VALIDATION_MSG_PRICE_GREATER_THAN_EQUAL_TO    = 'Минимальная стоимость %s';//Стоимость должна быть более или равная %s
    const VALIDATION_MSG_PRICE_LESS_THAN_EQUAL_TO       = 'Максимальная стоимость %s';//Стоимость должна быть менее или равна %s
    const VALIDATION_MSG_REQUIRED_TIME                  = 'Укажите срок';//(более специфично?) Укажите срок выполнения работы.    
    const VALIDATION_MSG_INTERVAL                       = 'Укажите от %s до %s';
    const VALIDATION_MSG_CATEGORY_FROM_LIST             = 'Выберите категорию из списка';
    const VALIDATION_MSG_MAX_TAGS                       = 'Укажите до %d разных ключевых слов';
    const VALIDATION_MSG_BAD_LINK                       = 'Некорректная ссылка';
    const VALIDATION_MSG_FROM_RADIO                     = 'Выберите подходящий вариант';
    const VALIDATION_MSG_CITY_FROM_LIST                 = 'Выберите город из списка';
    const VALIDATION_MSG_ONE_REQUIRED                   = 'Необходимо согласиться с условиями размещения';
    const VALIDATION_MSG_FROM_LIST                      = 'Выберите из списка';
    const VALIDATION_MSG_PRICE_MIN_TOTAL                = 'Минимальная стоимость %s <br/>c учетом всех скидок';
            
    
    
    /**
     * MB support
     * @var bool
     */
    private $mb_enabled = FALSE;//Отключаю так как на сервере FL mb_strlen считает не корректно
    


    
    
    
    // --------------------------------------------------------------------
    
    
    /**
     * 
     * Проверяем и парсим превьюшки и видео по ссылке
     * Спасибо за код https://gist.github.com/bezumkin/4243590
     * 
     * @param string $url       - ссылка на видео
     * @param type $thumb_id    - индекс превьюшки
     * @return array('video' - url для встраивания, 'image' - массив или одна ссылка на картинку)
     */
    public function video_validate_with_thumbs($url, $thumb_id = null){
        
        $REGEXP_IS_PROTOCOL     = '/^(http|https)\:\/\//i';
        
        $REGEXP_ID_YOUTUBE_1    = '/^(?:http|https):\/\/(?:www\.|)youtube\.com\/watch\?(?:.*)?v=([a-zA-Z0-9_\-]+)/i';
        $REGEXP_ID_YOUTUBE_2    = '/^(?:http|https):\/\/(?:www\.|)youtube\.com\/embed\/([a-zA-Z0-9_\-]+)/i';
        $REGEXP_ID_YOUTUBE_3    = '/^(?:http|https):\/\/(?:www\.|)youtu\.be\/([a-zA-Z0-9_\-]+)/i';
        
        $REGEXP_ID_VIMEO_1      = '/^(?:http|https):\/\/(?:www\.|)vimeo\.com\/([a-zA-Z0-9_\-]+)(&.+)?/i';
        $REGEXP_ID_VIMEO_2      = '/^(?:http|https):\/\/player\.vimeo\.com\/video\/([a-zA-Z0-9_\-]+)(&.+)?/i';
        
        $REGEXP_ID_RUTUBE_1     = '/^(?:http|https):\/\/(?:www\.|)rutube\.ru\/video\/embed\/([a-zA-Z0-9_\-]+)/i';
        $REGEXP_ID_RUTUBE_2     = '/^(?:http|https):\/\/(?:www\.|)rutube\.ru\/tracks\/([a-zA-Z0-9_\-]+)(&.+)?/i';
        $REGEXP_ID_RUTUBE_3     = '/^(?:http|https):\/\/(?:www\.|)rutube\.ru\/video\/([a-zA-Z0-9_\-]+)\//i';
        
        
        if (!preg_match($REGEXP_IS_PROTOCOL, $url)) 
        {
            $url = 'http://' . $url;
        }
        
        
        
        
        
        $result = FALSE;
        $matches = array();
        
	// YouTube
	if (preg_match($REGEXP_ID_YOUTUBE_1, $url, $matches) || 
            preg_match($REGEXP_ID_YOUTUBE_2, $url, $matches) || 
            preg_match($REGEXP_ID_YOUTUBE_3, $url, $matches)) 
        {
            $url_id = $matches[1];
            
            $headers = get_headers("https://img.youtube.com/vi/{$url_id}/0.jpg");
            if (strpos($headers[0], '200')) {
                $images = array(
                    'https://img.youtube.com/vi/'.$url_id.'/0.jpg',
                    'https://img.youtube.com/vi/'.$url_id.'/1.jpg',
                    'https://img.youtube.com/vi/'.$url_id.'/2.jpg',
                    'https://img.youtube.com/vi/'.$url_id.'/3.jpg'
                );

                if($thumb_id >= 0) $images = $images[$thumb_id];

                $result = array(
                    'video' => 'https://www.youtube.com/embed/'.$url_id,
                    'image' => $images
                );
            }
        }
	// Vimeo
	else if (preg_match($REGEXP_ID_VIMEO_1, $url, $matches) || 
                 preg_match($REGEXP_ID_VIMEO_2, $url, $matches)) 
        {
            $url_id = $matches[1];
            $images = FALSE;
            
            
            $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$url_id.php"));
            
            if(is_array($hash))
            {
                $images = array(
                    $hash[0]['thumbnail_large'],
                    $hash[0]['thumbnail_medium'],
                    $hash[0]['thumbnail_small']
                );

                if ($thumb_id >= 0) $images = $images[$thumb_id];
            }
            
            /* 
            //Чет не пашет
            if ($xml = simplexml_load_file('http://vimeo.com/api/v2/video/'.$url_id.'.xml')) 
            {
                 //print_r($xml);
                 //exit;
                
                 $images = array(
                       (string) $xml->video->thumbnail_small,
                       (string) $xml->video->thumbnail_medium,
                       (string) $xml->video->thumbnail_large
                 );
                 
                 if($thumb_id) $images = $images[$thumb_id];
            }
            */
            
            if($images)
            {
                $result = array(
                    'video' => 'https://player.vimeo.com/video/'.$url_id,
                    'image' => $images
                );
            }
	}
	// ruTube
	else if (preg_match($REGEXP_ID_RUTUBE_1, $url, $matches) || 
                 preg_match($REGEXP_ID_RUTUBE_2, $url, $matches) || 
                 preg_match($REGEXP_ID_RUTUBE_3, $url, $matches)) 
        {
            $url_id = $matches[1];
            $images = FALSE;            
            $embed_url = 'https://rutube.ru/video/embed/'.$url_id;

            if ($xml = simplexml_load_string(file_get_contents("http://rutube.ru/api/video/$url_id/?format=xml"))) 
            {
                $images = array(
                    (string) $xml->thumbnail_url . '?size=l',
                    (string) $xml->thumbnail_url . '?size=s'
                );
                
                if($thumb_id >= 0) $images = $images[$thumb_id];
                
                $embed_url = (string) $xml->embed_url;
                $embed_url = preg_replace("/^http:\/\//", "https://", $embed_url);
            }

            if($images)
            {
                $result = array(
                    'video' => $embed_url,
                    'image' => $images
                );
            }
	}

        return $result;
    }



    
    
    /**
     * 
     * Валидация ссылки с Youtube/Rutube/Vimeo
     * 
     * @param   string          $url   ссылка, которую нужно проверить
     * @return  string|boolean         если валидация успешна, возвращает правильно отредактированную ссылку, или FALSE
     * 
     * 
     * форматы ссылок на youtube
     * http://www.youtube.com/watch?feature=player_detailpage&v=hZI-LMHYU48 - не встраиваемая, надо переделывать
     * http://www.youtube.com/watch?feature=player_detailpage&v=hZI-LMHYU48#t=7s - тоже не встраиваемая
     * http://www.youtube.com/watch?v=hZI-LMHYU48&feature=g-logo&context=G295d7c5FOAAAAAAAAAA
     * эти три вида преобразуем в формат - http://www.youtube.com/watch?v=hZI-LMHYU48
     * http://youtu.be/hZI-LMHYU48
     */
    public function video_validate($url){
        
        $REGEXP_IS_PROTOCOL         = "/^(?:http|https)?:\/\//i";
        $REGEXP_IS_YOUTU_BE         = "/^((?:http|https):\/\/youtu\.be\/([-_A-Za-z0-9]+))/i";
        $REGEXP_IS_YOUTUBE_COM      = "/^((?:http|https):\/\/(?:ru\.|www\.)?youtube\.com\/watch\?).*(v=[-_A-Za-z0-9]+)/i";
        

        $REGEXP_IS_RUTUBE           = "/^((?:http|https)?:\/\/(?:www\.)?rutube\.ru\/video\/(embed\/)?[-_A-Za-z0-9]+\/{0,1})/i";
        $REGEXP_IS_RUTUBE2          = "/^((?:http|https)?:\/\/(?:www\.|video\.)?rutube\.ru\/(?:tracks\/)?[-_A-Za-z0-9]+(?:\.html)?)/i";
        
        //$REGEXP_IS_RUTUBE     = '/[http|https]+:\/\/(?:www\.|)rutube\.ru\/video\/embed\/([a-zA-Z0-9_\-]+)/i';
        //$REGEXP_IS_RUTUBE2     = '/[http|https]+:\/\/(?:www\.|)rutube\.ru\/tracks\/([a-zA-Z0-9_\-]+)(&.+)?/i';
        ///[http|https]+:\/\/(?:www\.|)rutube\.ru\/video\/([a-zA-Z0-9_\-]+)\//i
        
        //} else if (preg_match("/^((?:http|https)?:\/\/(?:www\.)?rutube\.ru\/video\/?[-_A-Za-z0-9]+\/{0,1})/i", $url, $o)) {
        
        
        $REGEXP_IS_VIMEO            = "/^((?:http|https):\/\/(?:www\.)?vimeo\.com\/[0-9]+)/i";
        
        //@todo: взято со старого сайта - непонятно зачем нужны эти замены? для красоты урла? 
        $REGEXP_REPLACE_PROTOCOL    = function($url){return $url; /*preg_replace(array("/^http:\/\/www\./", "/^https:\/\/www\./", "/^http:\/\//"), "https://", $url);*/};
        $REGEXP_REPLACE_PROTOCOL2   = function($url){return $url; /*preg_replace(array("/^http:\/\/www\./", "/^https:\/\/www\./", "/^https:\/\//"), "http://", $url);*/};
        
        

        
        if (!preg_match($REGEXP_IS_PROTOCOL, $url)) $url = 'http://'.$url;

        
        if (preg_match($REGEXP_IS_YOUTU_BE, $url, $o)) 
        {
            return $o[1];
        } 
        else if (preg_match($REGEXP_IS_YOUTUBE_COM, $url, $o)) 
        {
            return $o[1] . $o[2];
        } 
        else if (preg_match($REGEXP_IS_RUTUBE, $url, $o)) 
        {
            return $REGEXP_REPLACE_PROTOCOL($o[1]);
        } 
        else if (preg_match($REGEXP_IS_RUTUBE2, $url, $o)) 
        {
            return $REGEXP_REPLACE_PROTOCOL($o[1]);
        } 
        else if (preg_match($REGEXP_IS_VIMEO, $url, $o)) 
        {
            return $REGEXP_REPLACE_PROTOCOL2($o[1]);
        }
        
        return FALSE;
    }
    
    
    
    // --------------------------------------------------------------------
    
    
     /**
    * Валидация ссылки с Youtube/Rutube/Vimeo
    * 
    * @param   string          $url   ссылка, которую нужно проверить
    * @return  string|boolean  если валидация успешна, возвращает правильно отредактированную ссылку, или FALSE
    */
    /*
    function video_validate($url) {
        if (!preg_match("/^(?:http|https)?:\/\//i", $url)) $url = 'http://'.$url;
        if (preg_match("/^((?:http|https):\/\/youtu\.be\/([-_A-Za-z0-9]+))/i", $url, $o)) {
            return $o[1];
        } else if (preg_match("/^((?:http|https):\/\/(?:ru\.|www\.)?youtube\.com\/watch\?).*(v=[-_A-Za-z0-9]+)/i", $url, $o)) {
            return $o[1] . $o[2];
        } else if (preg_match("/^((?:http|https)?:\/\/(?:www\.)?rutube\.ru\/video\/?[-_A-Za-z0-9]+\/{0,1})/i", $url, $o)) {
            return preg_replace(array( "/^http:\/\/www\./", "/^https:\/\/www\./", "/^http:\/\//"), "https://", $o[1]);
        } else if (preg_match("/^((?:http|https)?:\/\/(?:www\.|video\.)?rutube\.ru\/(?:tracks\/)?[-_A-Za-z0-9]+(?:\.html)?)/i", $url, $o)) {
            return preg_replace(array( "/^http:\/\/www\./", "/^https:\/\/www\./", "/^http:\/\//"), "https://", $o[1]);
        } else if (preg_match("/^((?:http|https):\/\/(?:www\.)?vimeo\.com\/[0-9]+)/i", $url, $o)) {
            return preg_replace(array("/^http:\/\/www\./", "/^https:\/\/www\./", "/^https:\/\//"),  "http://", $o[1]);
        }
        return FALSE;
        
        // форматы ссылок на youtube
        // http://www.youtube.com/watch?feature=player_detailpage&v=hZI-LMHYU48 - не встраиваемая, надо переделывать
        // http://www.youtube.com/watch?feature=player_detailpage&v=hZI-LMHYU48#t=7s - тоже не встраиваемая
        // http://www.youtube.com/watch?v=hZI-LMHYU48&feature=g-logo&context=G295d7c5FOAAAAAAAAAA
        // эти три вида преобразуем в формат - http://www.youtube.com/watch?v=hZI-LMHYU48
        // http://youtu.be/hZI-LMHYU48
    }   
    */
    
    // --------------------------------------------------------------------
    
    
    /**
     * Equal to or Less than
     *
     * @param	string
     * @return	bool
     */
    public function less_than_equal_to($str, $max) {
        if (!is_numeric($str)) {
            return FALSE;
        }
        return $str <= $max;
    }
    
    
    // --------------------------------------------------------------------
    
    
    /**
     * Less than
     *
     * @param	string
     * @return	bool
     */
    public function less_than($str, $max) {
        if (!is_numeric($str)) {
            return FALSE;
        }
        return $str < $max;
    }
    
    
    
    // --------------------------------------------------------------------

    
    
    /**
     * Equal to or Greater than
     *
     * @param	string
     * @return	bool
     */
    public function greater_than_equal_to($str, $min) {
        if (!is_numeric($str)) {
            return FALSE;
        }
        return $str >= $min;
    }

    
    // --------------------------------------------------------------------
    
    
    /**
     * Number in interval
     * 
     * @param string $str
     * @param int $min
     * @param int $max
     * @return bool
     */
    public function numeric_interval($str, $min, $max){
        return $this->greater_than_equal_to($str, $min) && $this->less_than_equal_to($str, $max);
    }




    // --------------------------------------------------------------------
    
    
    
    /**
     * Is a Natural number  (0,1,2,3, etc.)
     *
     * @param	string
     * @return	bool
     */
    public function is_natural($str) {
        return (bool) preg_match('/^[0-9]+$/', $str);
    }

    
    // --------------------------------------------------------------------
    
    
    /**
     * Is a Natural number, but not a zero  (1,2,3, etc.)
     *
     * @param	string
     * @return	bool
     */
    public function is_natural_no_zero($str) {
        return ($str != 0 && preg_match('/^[0-9]+$/', $str));
    }
    
    
    
    // --------------------------------------------------------------------
    
    
    /**
     * Is a integer number, but not a zero  (-3,-2,-1,1,2,3, etc.)
     * 
     * @param string $str
     * @return bool
     */
    public function is_integer_no_zero($str) {
        return ($str != 0 && $this->integer($str));
    }

    
    // --------------------------------------------------------------------

    
    /**
     * Integer
     *
     * @param	string
     * @return	bool
     */
    public function integer($str) {
        return (bool) preg_match('/^[\-+]?[0-9]+$/', $str);
    }


    // --------------------------------------------------------------------
    
    
    /**
     * Required
     *
     * @param	string
     * @return	bool
     */
    public function required($str) {
        return (!is_array($str)) ? (trim($str) !== '') : (!empty($str));
    }
    
    
    // --------------------------------------------------------------------
    
    
    /**
     * Minimum Length
     *
     * @param	string
     * @param	value
     * @return	bool
     */
    public function min_length($str, $val) {
        if (preg_match('/[^0-9]/', $val)) {
            return FALSE;
        }

        if ($this->mb_enabled === TRUE) {
            return !(mb_strlen($str) < $val);
        }

        return !(strlen($str) < $val);
    }
    
    
    // --------------------------------------------------------------------
    
    
    /**
     * Max Length
     *
     * @param	string
     * @param	value
     * @return	bool
     */
    public function max_length($str, $val) {
        if (preg_match('/[^0-9]/', $val)) {
            return FALSE;
        }

        if ($this->mb_enabled === TRUE) {
            return !(mb_strlen($str) > $val);
        }

        return !(strlen($str) > $val);
    }
    
    
    // --------------------------------------------------------------------
    
    
    public function symbols_interval($str, $min, $max){
        return $this->min_length($str, $min) && $this->max_length($str, $max);
    }
    
}