<?php

require_once(ABS_PATH . '/classes/yii/CItem.php');
require_once(ABS_PATH . '/classes/tservices/tservices_helper.php');

class TServiceItem extends CItem
{
    public function getThumbnail()
    {
        return null;
    }

    public function getThumbnailUrl()
    {
        return isset($this->_data['file'])?
            tservices_helper::image_src(
                    $this->_data['file'], 
                    $this->user['login']):null;
    }
    
    public function getTitle()
    {
        return LenghtFormatEx(reformat($this->title, 20, 0, 1), 80);
    }
    
    
    public function getUrl()
    {
        return sprintf('/tu/%d/%s.html', 
                $this->id, 
                tservices_helper::translit($this->title));
    }
    
    
    public function hasVideo()
    {
        return !empty($this->videos) && count($this->videos);
    }
    
    public function getSoldCount()
    {
        $sold_count = !empty($this->count_sold)?$this->count_sold:$this->total_feedbacks;
        return number_format($sold_count);
    }
    
    public function getPrice()
    {
        return view_cost_format($this->price, true);
    }
}