<?php

require_once('FreelancersPreviewModel.php');
require_once(ABS_PATH . '/tu/models/TServiceItem.php');
require_once(ABS_PATH . '/portfolio/models/PortfolioItem.php');


class FreelancersPreviewItemIterator extends ArrayIterator
{    
    public function current() 
    {
        $value = parent::current();
        
        $class_name = FreelancersPreviewModel::getTypeClass($value['type']);
        
        if ($class_name) {
            return new $class_name($value);
        }
        
        return null;
    }
}