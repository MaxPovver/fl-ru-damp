<?php

require_once('FormElement.php');

class Form_Element_MultiDropdown extends Form_Element
{
    protected $_db_id = 0;
    
    protected $_columns = array(0,0);
    
    public function setValue($value) 
    {
        $db_id_idx = $this->getName() . '_db_id';
        $this->_db_id = __paramInit('int', $db_id_idx, $db_id_idx, 0);

        $columns_id_idx = 'el-' . $this->getName() . '_columns';
        $this->_columns = __paramInit('array_int', $columns_id_idx, $columns_id_idx, $this->_columns);
        
        return parent::setValue($value);
    }

    public function getColumnId($idx)
    {
        return isset($this->_columns[$idx])?$this->_columns[$idx]:0;
    }

    public function getColumns()
    {
        return $this->_columns;
    }

    public function getDbId()
    {
        return $this->_db_id;
    }

    
    public function getValue()
    {
        return $this->getDbId();
        //return $this->getColumns();
    }
}
