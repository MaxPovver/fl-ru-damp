<?php

require_once(ABS_PATH . '/classes/reserves/ReservesTServiceOrderModel.php');
require_once('ReservesArchiveModel.php');

class ReservesArchiveItemModel
{
    protected $_data;
    
    public function __construct($_data) 
    {
        $this->_data = $_data;
    }

    public function __get($name) 
    {
        return isset($this->_data[$name])? $this->_data[$name] : null;
    }
    
    public function getFields()
    {
        return mb_unserialize($this->_data['fields']);
    }

    public function getParams()
    {
        $bs_ids = $this->getFields();
        
        if (!is_array($bs_ids) || 
            empty($bs_ids)) {
            
            return '';
        }
        
        array_walk($bs_ids, function(&$value){
            $href = tservices_helper::getOrderCardUrl($value);
            $value = sprintf(ReservesTServiceOrderModel::NUM_FORMAT, $value);
            $value = "<a href=\"{$href}\" target=\"_blank\">{$value}</a>";
        });

        return implode(", ", $bs_ids);
    }
    
    public function getDate()
    {
        return date('d.m.Y H:i', strtotime($this->_data['date']));
    }
    
    public function getName()
    {
        return $this->_data['original_name'];
    }
    
    /*
    public function getTranslitName()
    {
        return translit(strtolower(htmlspecialchars_decode($this->_data['original_name'], ENT_QUOTES)));
    }
    */
    
    public function getStatus()
    {
        return $this->_data['status'];
    }
    
    public function isStatusSuccess()
    {
        return $this->_data['status'] == ReservesArchiveModel::STATUS_SUCCESS;
    }
    
    public function isStatusError()
    {
        return $this->_data['status'] == ReservesArchiveModel::STATUS_ERROR;
    }    
    
    public function isStatusProgress()
    {
        return $this->_data['status'] == ReservesArchiveModel::STATUS_INPROGRESS;
    }    
    
    
    public function getStatusColor()
    {
        switch($this->_data['status']) {
            case ReservesArchiveModel::STATUS_SUCCESS: return 'success';
            case ReservesArchiveModel::STATUS_ERROR: return 'danger';
            case ReservesArchiveModel::STATUS_NEW:     
            case ReservesArchiveModel::STATUS_INPROGRESS: return 'warning';
        }
        
        return 'default';
    }
    
    public function getStatusText()
    {
        switch($this->_data['status']) {
            case ReservesArchiveModel::STATUS_SUCCESS: return 'Готов';
            case ReservesArchiveModel::STATUS_ERROR: return "Ошибка";
            case ReservesArchiveModel::STATUS_NEW:    
            case ReservesArchiveModel::STATUS_INPROGRESS: return 'В процессе';
        }
        
        return '';
    }    
    
    public function getTryCount()
    {
        return $this->_data['try_count'];
    }

    public function getTechMessage()
    {
        return $this->_data['techmessage'];
    }    
    
    public function getArchiveLink()
    {
        return WDCPREFIX . $this->_data['filename'];
    }
    
    
    public function getId()
    {
        return $this->_data['id'];
    }
    
}