<?php

require_once(ABS_PATH . '/classes/yii/CItem.php');
require_once(ABS_PATH . '/classes/stat_collector.php');

class PortfolioItem extends CItem
{
    public function getThumbnail()
    {
        return view_preview2(
                $this->user['login'], 
                $this->prev_pict, 
                'upload', 'center', true, true, '', 200);
    }
    
    public function getTitle()
    {
        return LenghtFormatEx(reformat($this->title, 20, 0, 1), 80);
    }
    
    public function getTitleFull()
    {
        return reformat($this->title, 17, 0, 1);
    }
    
    public function getDescr()
    {
        return viewdescr($this->user['login'], reformat2($this->descr, 42, 0, 1));
    }

    public function isText()
    {
        return $this->prev_type == 1;
    }
    
    public function isPortfolio()
    {
        return true;
    }
    
    public function getUrl()
    {
        return "/users/{$this->user['login']}/viewproj.php?prjid={$this->id}&f=" . stat_collector::REFID_CATALOG;
    }
    
    public function getAttrTitle()
    {
        return htmlspecialchars(htmlspecialchars_decode($this->title));
    }
}