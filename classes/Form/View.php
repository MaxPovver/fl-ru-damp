<?php

//Путь для автоподгрузки классов
set_include_path(get_include_path()
        . PATH_SEPARATOR . ABS_PATH . '/classes/'
);

// Перевод сообщений об ошибках заполнения форм Zend_Form
$translateValidators = array(
    Zend_Validate_Alnum::NOT_ALNUM => 'Введенное значение неправильное. Разрешены только латинские символы и цифры', 
    Zend_Validate_Alnum::STRING_EMPTY => 'Поле не может быть пустым. Заполните его, пожалуйста', 
    Zend_Validate_Alpha::NOT_ALPHA => 'Введите в это поле только латинские символы', 
    Zend_Validate_Alpha::STRING_EMPTY => 'Поле не может быть пустым. Заполните его, пожалуйста', 
    Zend_Validate_Between::NOT_BETWEEN => 'Значение должно быть в диапазоне между "%min%" и "%max%"', 
    Zend_Validate_Between::NOT_BETWEEN_STRICT => 'Значение не находится строго между "%min%" и "%max%"', 
    Zend_Validate_Ccnum::LENGTH => 'Значение должно быть численным значением от 13 до 19 цифр длинной', 
    Zend_Validate_Ccnum::CHECKSUM => 'Подсчёт контрольной суммы неудался. Значение неверно', 
    Zend_Validate_Date::INVALID => 'Неверная дата', 
    Zend_Validate_Date::FALSEFORMAT => 'Значение не подходит по формату', 
    Zend_Validate_Digits::NOT_DIGITS => 'Значение неправильное. Введите только цифры', 
    Zend_Validate_Digits::STRING_EMPTY => 'Поле не может быть пустым. Заполните его, пожалуйста', 
    Zend_Validate_EmailAddress::INVALID => 'Неправильный адрес електронной почты. Введите его в формате имя@домен', 
    Zend_Validate_EmailAddress::INVALID_FORMAT => "Адрес электронной почты должен содержать @, точку и, минимум, два символа после точки.",
    Zend_Validate_EmailAddress::INVALID_HOSTNAME => '"%hostname%" неверный домен для адреса "%value%"', 
    Zend_Validate_EmailAddress::INVALID_MX_RECORD => 'Домен "%hostname%" не имеет MX-записи об адресе "%value%"', 
    Zend_Validate_EmailAddress::DOT_ATOM => '"%localPart%" не соответствует формату dot-atom', 
    Zend_Validate_EmailAddress::QUOTED_STRING => '"%localPart%" не соответствует формату указанной строки', 
    Zend_Validate_EmailAddress::INVALID_LOCAL_PART => '"%localPart%" не правильное имя для адреса, вводите адрес вида имя@домен', 
    Zend_Validate_Float::NOT_FLOAT => 'Значение не является дробным числом', 
    Zend_Validate_GreaterThan::NOT_GREATER => 'Значение не превышает "%min%"', 
    Zend_Validate_Hex::NOT_HEX => 'Значение содержит в себе не только шестнадцатеричные символы', 
    Zend_Validate_Hostname::IP_ADDRESS_NOT_ALLOWED => '"%value%" - это IP-адрес, но IP-адреса не разрешены ', 
    Zend_Validate_Hostname::UNKNOWN_TLD => '"%value%" - это DNS имя хоста, но оно не дожно быть из TLD-списка', 
    Zend_Validate_Hostname::INVALID_DASH => '"%value%" - это DNS имя хоста, но знак "-" находится в неправильном месте', 
    Zend_Validate_Hostname::INVALID_HOSTNAME_SCHEMA => '"%value%" - это DNS имя хоста, но оно не соответствует TLD для TLD "%tld%"', 
    Zend_Validate_Hostname::UNDECIPHERABLE_TLD => '"%value%" - это DNS имя хоста. Не удаётся извлечь TLD часть', 
    Zend_Validate_Hostname::INVALID_HOSTNAME => '"%value%" - не соответствует ожидаемой структуре для DNS имени хоста', 
    Zend_Validate_Hostname::INVALID_LOCAL_NAME => '"%value%" - адрес является недопустимым локальным сетевым адресом', 
    Zend_Validate_Hostname::LOCAL_NAME_NOT_ALLOWED => '"%value%" - адрес является сетевым расположением, но локальные сетевые адреса не разрешены', 
    Zend_Validate_Identical::NOT_SAME => 'Значения не совпадают', 
    Zend_Validate_Identical::MISSING_TOKEN => 'Не было введено значения для проверки на идентичность', 
    Zend_Validate_InArray::NOT_IN_ARRAY => 'Значение не найдено в перечисленных допустимых значениях', 
    Zend_Validate_Int::NOT_INT => 'Значение не является целочисленным значением', 
    Zend_Validate_Ip::NOT_IP_ADDRESS => 'Значение не является правильным IP-адресом', 
    Zend_Validate_LessThan::NOT_LESS => 'Значение не меньше, чем "%max%"', 
    Zend_Validate_NotEmpty::IS_EMPTY => 'Введённое значение пустое, заполните поле, пожалуйста', 
    Zend_Validate_StringLength::TOO_SHORT => 'Длина введённого значения, меньше чем %min% симв.', 
    Zend_Validate_StringLength::TOO_LONG => 'Длина введённого значения не должна быть больше чем %max% символов', 
);
$translator = new Zend_Translate('Zend_Translate_Adapter_Array', $translateValidators);
Zend_Validate_Abstract::setDefaultTranslator($translator);


class Form_View extends Zend_Form
{
    protected $viewScriptPrefixPath = 'classes/Form/Templates';
    protected $_idSuffix = null;
    protected $_idPreffix = null;


    public function __construct($options = null)
    {
        parent::__construct($options);
        $view = new Zend_View();
        $view->setScriptPath($_SERVER['DOCUMENT_ROOT']);
        $this->setView($view);
        
        //Где ищем кастомные фильтры
        $this->addElementPrefixPath(
                'Form_Filter',
                'Form/Filter',
                'filter');
        
        //Где ищем кастомные валидаторы
        $this->addElementPrefixPath(
                'Form_Validate',
                'Form/Validate',
                'validate');
        
        //По умолчанию для элементов ставим преффикс ID - имя класса объекта
        $this->setDefaultIdPreffix();
    }

    public function loadDefaultDecorators()
    {
        $this->setDecorators(array(
            'FormElements',
            'Form',
        ));

        $this->setSubFormDecorators(array(
            'FormElements'
        ));
    }

    public function addElement($element, $name = null, $options = null)
    {
        $names = explode('_', get_class($element));
        if ($names[0] === 'Zend' || 
            (
                isset($element->override_view_script) && 
                $element->override_view_script == true
            )) {
            
            $element_name = array_pop($names);
            $element->clearDecorators();
            $element->addDecorator('ViewScript', array('viewScript' => $this->viewScriptPrefixPath.'/'.$element_name.'.phtml'));
        }

        if ($this->getAttrib('readonly')) {
            // Делаем все элементы формы только для чтения
            $element->setAttrib('readonly', true);
            $options['readonly'] = true;
        }

        $view = new Zend_View();
        $view->setScriptPath($_SERVER['DOCUMENT_ROOT']);
        $element->setView($view);   

        return parent::addElement($element, $name, $options);
    }

    
    
    public function addElementByName($element, $name, $options)
    {
        return parent::addElement($element, $name, $options);
    }

    



    /**
    * Объединяет все значения сабформ в один массив
    *
    * @return array $values
    */
    public function getSubFormsValues()
    {
        $values = array();

        foreach ($this->getSubForms() as $form) {
            $name = $form->getName();
            $value = $form->getValues(); 
        
            $values = array_merge($value[$name], $values);    
        }
        
        return $values;
    }
    
    
    /**
     * Установить суффикс ID
     *
     * @param string $suffix
     * @return My_Form
     */
    public function setIdSuffix($suffix)
    {
        $this->_idSuffix = $suffix;
        return $this;
    }

    /**
     * Установить префикс ID
     * 
     * @param type $preffix
     * @return \Form_View
     */
    public function setIdPreffix($preffix)
    {
        $this->_idPreffix = $preffix;
        return $this;
    }
    
    /**
     * Установить поумолчанию вкачестве префикса ID имя класса объекта
     * 
     * @return object
     */
    public function setDefaultIdPreffix()
    {
        $preffix = strtolower(str_replace('Form', '', get_called_class()));
        return $this->setIdPreffix($preffix);
    }

    

    /**
     * Вывод формы
     * @todo: переопределили для установки префикса и суффикса для ID формы и/или элементов
     *
     * @param Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        if (!is_null($this->_idSuffix) || 
            !is_null($this->_idPreffix)) {
            
            // form
            $formId = $this->getId();
            if (0 < strlen($formId) && !is_null($this->_idSuffix)) {
                $this->setAttrib('id', $formId . '-' . $this->_idSuffix);
            }

            // elements
            $elements = $this->getElements();
            foreach ($elements as $element) {
                
                $element_id = $element->getId();
                
                if ($this->_idPreffix) {
                    $element_id = $this->_idPreffix . '-' . $element_id;
                }
                
                if ($this->_idSuffix) {
                    $element_id .= '-' . $this->_idSuffix;
                }
                
                $element->setAttrib('id', $element_id);
            }
        }

        return parent::render($view);
    }
    
    /**
     * Собираем ошибки элементов в одну строку 
     * для каждого с указанным разделителем
     * 
     * @param type $glue
     * @return type
     */
    public function getAllMessages($glue = '. ')
    {
        $result = null;
        $messages = $this->getMessages();
        
        if (count($messages)) {
            foreach($messages as $key => $value) {
                $result[$key] = implode($glue, $value);
            }
        }
        
        return $result;
    }
    
    
}

