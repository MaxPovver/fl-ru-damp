<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");

/**
 * Class Form_Validate_Professions
 * 
 * ¬алидатор наличи€ раздела и/или специализации
 */
class Form_Validate_Professions extends Zend_Validate_Abstract  
{
    const GROUP_INVALID = 'groupInvalid';
    const SPEC_INVALID  = 'specInvalid';
    
    protected $_messageTemplates = array(
        self::GROUP_INVALID => '”казанный раздел не найден',
        self::SPEC_INVALID => '”казанна€ специализаци€ не найдена'
    );
    
    protected $group_idx;
    protected $spec_idx;




    /**
     * Sets validator options
     *
     * @param  integer|array|Zend_Config $options
     * @return void
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            $options = func_get_args();
            $temp['group'] = array_shift($options);
            $temp['spec'] = array_shift($options);
            $options = $temp;
        }

        if (array_key_exists('group', $options)) {
            $this->group_idx = $options['group'];
        }       
        
        if (array_key_exists('spec', $options)) {
            $this->spec_idx = $options['spec'];
        }
    }
    
    
    public function isValid($value)
    {
        $group_id = intval(@$value[$this->group_idx]);
        $spec_id = intval(@$value[$this->spec_idx]);
        
        $data = professions::getGroupAndProf($group_id, $spec_id);
        
        if (!isset($data) || $group_id <= 0 || !$data['group_id']) {
            $this->_error(self::GROUP_INVALID);
            return false;
        }
        
        if ($spec_id > 0 && !$data['prof_id']) {
            $this->_error(self::SPEC_INVALID);
            return false;
        }
        
        return true;
    }    
}