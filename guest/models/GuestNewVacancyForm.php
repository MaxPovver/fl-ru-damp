<?php

require_once('GuestForm.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/MultiDropdown.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/BudgetExt.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/GuestProjectUploader.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/Hidden.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Validate/CostOrAgreementRequired.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Validate/UrlInvited.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");

/**
 * Class GuestNewVacancyForm
 * Форма новой вакансии
 */
class GuestNewVacancyForm  extends GuestForm
{
    /**
     * Инициализация формы
     */
    public function init()
    {
        $this->addElement(
           new Zend_Form_Element_Text('name', array(
               'label' => 'Название вакансии',
               'required' => true,
               'placeholder' => 'Кого вы ищете и какую работу нужно выполнить.',
               'padbot' => 30, // отступ снизу
               'maxlength' => 60,
               'filters' => $this->filtersAll,
               'validators' => array(
                   array('StringLength',true,array('max' => 60,'min' => 4))
                )
        )));  
        

        if ($this->isAdm()) {
            $this->addElement(
                new Zend_Form_Element_Text('link', array(
                    'label' => 'Ссылка на вакансию',
                    'required' => true,
                    'padbot' => 30, // отступ снизу
                    'filters' => $this->filters,
                    'validators' => array(
                        array('StringLength',true,array('min' => 4)),
                        array(new Form_Validate_UrlInvited(array('type' => GuestConst::TYPE_VACANCY)), true)
                     )
                ))
            );
        }
        
        $this->addElement(
          new Zend_Form_Element_Textarea('descr', array(
              'label' => 'Подробно опишите задание',
              'required' => true,
              'placeholder' => 'Укажите требования к исполнителю и результату, сроки выполнения и другие условия работы.',
              'padbot' => 5, // отступ снизу
              'filters' => $this->filtersAll,
              'validators' => array(
                  array('StringLength', true, array('max' => 5000, 'min' => 4))
               )
        )));        
        

        //@todo: элемент требует проработки
        $this->addElement(
          new Form_Element_GuestProjectUploader('IDResource' , array(
              'hide_label' => true,
              'label' => 'Файлы',
              'padbot' => 30 // отступ снизу
          ))
        ); 
        
        
        $this->addElement(
          new Form_Element_ProfessionsDropdown('profession', array(
              'padbot' => 30, // отступ снизу
              'label' => 'Специализация проекта',
              'required' => true,
              'class'       => 'b-combo__input_width_320',
              'spec_class'  => 'b-combo__input_width_300',
              'sort_type'   => 'sort_cnt',
              //если нужно по умолчанию
              /*
              'value' => array(
                  'group_db_id' => 3,
                  'group' => 'Дизайн',
                  'spec_db_id' => 46,
                  'spec' => 'Логотипы'),
               */
              'placeholder' => 'Выберите раздел',
              'spec_placeholder' => 'Выберите специализацию (не обязательно)'
          ))
        );
        
        
        $this->addElement(
          new Form_Element_MultiDropdown('location', array(
              'padbot' => 30, // отступ снизу
              'label' => 'Нужен исполнитель из…',
              'class' => 'b-combo__input_width_250 
                          b-combo__input_visible_height_200 
                          b-combo__input_arrow_yes 
                          b-combo__input_init_citiesList
                          b-combo__input_on_click_request_id_getcities',
              'suffix' => 'Если предполагается работа в офисе - укажите, в каком городе он находится.',
              'value' => 'Все страны',
              'validators' => array(
                  array('Digits', true)
              )
          ))
        );
        
        $this->addElement(
            new Form_Element_BudgetExt('cost', array(
                'padbot' => 30, // отступ снизу
                'label' => 'Бюджет',
                'required' => true,
                'filters' => $this->filters,
                'validators' => array(
                    array(new Form_Validate_CostOrAgreementRequired(), true)
                ),
                'value' => array(
                    'priceby_db_id' => 3
                )
            ))
        );

        if (!$this->isAdm()) {
            $this->addElement(
              new Zend_Form_Element_MultiCheckbox('filter', array(
                  'padbot' => 5, // отступ снизу
                  'label' => 'Ответить на вакансию могут только ...',
                  'value' => 'pro_only',
                  'multiOptions' => array(
                      'pro_only' => 'Фрилансеры с аккаунтом '.  view_profi() . ' или ' . view_pro(),
                      //'verify_only' => 'Фрилансеры c верификацией ' . view_verify()
                  )
              ))
            );
        }
        
        $this->addElement(
          new Form_Element_Hidden('auth', array(
              'validators' => array(
                  array('Digits')
               )
        ))); 
        
        $this->addElement(new Zend_Form_Element_Hidden('kind', array('value' => 4)));
        
    }    
    
    
    
    public function getCustomErrorMessage($err)
    {
        return GuestConst::getErrorMessage($err, GuestConst::TYPE_VACANCY);
    }
    
    
    public function getCustomMessage($mes)
    {
        $message = GuestConst::getMessage($mes, GuestConst::TYPE_VACANCY);
        
        if ($mes == GuestConst::MSG_SUBMIT) {
            $vacancyPrice = new_projects::getProjectInOfficePrice();
            $message = sprintf($message, $vacancyPrice);
        }
        
        return $message;
    }
    
}