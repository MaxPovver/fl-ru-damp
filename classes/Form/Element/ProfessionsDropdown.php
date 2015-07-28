<?php

require_once('FormElement.php');

class Form_Element_ProfessionsDropdown extends Form_Element
{
    const GROUP_DBID = 'group_db_id';
    const SPEC_DBID  = 'spec_db_id';
    
    const GROUP = 'group';
    const SPEC  = 'spec';
    
    
    public function init()
    {
        global $js_file;
        $js_file['ElementsFactory'] = 'form/ElementsFactory.js';
        $js_file['ElementProfessionsDropdown'] = 'form/ProfessionsDropdown.js';

        $this->addValidator('Professions', true, array(
                    'group' => self::GROUP_DBID,
                    'spec' => self::SPEC_DBID
                ));
    }
    
    
    public function setValue($value) 
    {
        $value = (isset($value[self::GROUP_DBID]) && 
                  $value[self::GROUP_DBID] > 0)?$value:null;
        return parent::setValue($value);
    }
    
    
    public function getGroupDbIdValue()
    {
        $value = $this->getValue();
        return intval(@$value[self::GROUP_DBID]);
    }
    
    
    public function getSpecDbIdValue()
    {
        $value = $this->getValue();
        return intval(@$value[self::SPEC_DBID]);
    }
    
    
    public function getGroupValue()
    {
        $value = $this->getValue();
        return @$value[self::GROUP];
    }
    
    
    public function getSpecValue()
    {
        $value = $this->getValue();
        return @$value[self::SPEC];
    }
    
}