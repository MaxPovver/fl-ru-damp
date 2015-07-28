<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/View.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/BudgetExt.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/GuestProjectUploader.php");

/**
 * Class CustomerNewProjectForm
 * Форма нового проекта
 */
class CustomerNewProjectForm  extends Form_View
{   
    protected $viewScriptPrefixPath = 'classes/Form/Templates/Horizontal';
    protected $viewScriptFormPrefixPath = 'welcome/views/forms';
    
    protected $step;
    
    public $filters = array(
        'StringTrim',
        'StripSlashes'
    ); 

    public $filtersAll = array(
        'StripTags',
        'StringTrim',
        'StripSlashes'        
    );


    public function __construct($options = null) 
    {
        if (isset($options['step'])) {
            $this->step = $options['step'];
            unset($options['step']);
        }
        
        parent::__construct($options);
    }
    
    
    /**
     * Общая вьюшка для форм
     */
    public function loadDefaultDecorators()
    {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 
                $this->viewScriptFormPrefixPath . 
                '/customer-default-form.phtml'))
        ));
    }    
    
    
    /**
     * Инициализация формы
     */
    public function init()
    {
        if ($this->step == 3) {

            $this->addElement(
               new Zend_Form_Element_Text('name', array(
                   'td_class' => 'b-layout__td_width_full',
                   'label_class' => 'b-layout__txt_fontsize_20 b-layout__txt_padbot_5',
                   'class' => 'b-combo_large',
                   'label' => 'Дайте проекту название',
                   'padbot' => 35, // отступ снизу
                   'required' => true,
                   'maxlength' => 60,
                   'filters' => $this->filtersAll,
                   'validators' => array(
                       array('StringLength',true,array('max' => 60,'min' => 4))
                    ),
                   'placeholder' => 'Дизайн баннера'
            )));        

            $this->addElement(
              new Zend_Form_Element_Textarea('descr', array(
                  'label_class' => 'b-layout__txt_fontsize_20 b-layout__txt_padbot_5',
                  'label' => 'Описание',
                  'padbot' => 35, // отступ снизу
                  'required' => true,
                  'placeholder' => 'Сделать дизайн',
                  'filters' => $this->filtersAll,
                  'validators' => array(
                      array('StringLength', true, array('max' => 5000, 'min' => 4))
                   )
            )));        

            //@todo: элемент требует проработки
            $this->addElement(
              new Form_Element_GuestProjectUploader('IDResource' , array(
                  'label_class' => 'b-layout__txt_fontsize_20',
                  'padbot' => 35, // отступ снизу
                  'label' => 'Загрузите файлы, которые хотите передать потенциальному исполнителю'
              ))
            ); 

        } else {        
       
            $this->addElement(
              new Form_Element_BudgetExt('cost', array(
                  'label_class' => 'b-layout__txt_fontsize_20 b-layout__txt_padbot_5',
                  'label' => 'Установите бюджет',
                  'class' => 'b-combo_large',
                  'td1_class' => 'b-layout__td_nowrap',
                  'td2_class' => 'b-layout__td_nowrap b-layout__td_padtop_20',
                  'hide_or' => true,
                  'padbot' => 35, // отступ снизу
                  'filters' => $this->filters,
                  'budget_width' => 110,
                  'currency_width' => 100,
                  'priceby_width' => 190,
                  'value' => array(
                      'priceby_db_id' => 4
                  )
              ))
            );
        
            $this->addElement(
                new Zend_Form_Element_Radio('prefer_sbr',array(
                    'label_class' => 'b-layout__txt_fontsize_20 b-layout__txt_padbot_5',
                    'label' => 'Выберите способ оплаты',
                    'radio_label_class' => 'b-radio__label_bold b-radio__label_width_90ps',
                    'value' => 1,
                    'padbot' => 35, // отступ снизу
                    'required' => true,
                    'attr' => array(
                        1 => 'data-show-class="#order_status_indicator_1" data-hide-class="#order_status_indicator_0"',
                        0 => 'data-show-class="#order_status_indicator_0" data-hide-class="#order_status_indicator_1"'
                    ),
                    'multiOptions' => array(
                        1 => 'Безопасная сделка (с резервированием бюджета) &#160;<a class="b-layout__link" href="/promo/bezopasnaya-sdelka/" target="_blank"><span class="b-shadow__icon b-shadow__icon_quest2 b-icon_top_2"></span></a>',
                        0 => 'Прямая оплата Исполнителю на его кошелек/счет'
                    ),
                    'subTitles' => array(
                        1 => 'Безопасное сотрудничество с гарантией возврата средств. Вы резервируете бюджет заказа на сайте FL.ru - а мы гарантируем вам возврат суммы, если работа будет выполнена Исполнителем некачественно или не в срок.',
                        0 => 'Сотрудничество без участия сайта в процессе оплаты. Вы сами договариваетесь с Исполнителем о способе и порядке оплаты. 
                              И самостоятельно регулируете все претензии, связанные с качеством и сроками выполнения работы.'
                    )
                ))
            );
            
        }

        
        $this->addElement(
           new Zend_Form_Element_Submit('singin', array(
               'td_class' => 'b-layout__td_width_full',
               'parent_class' => 'b-buttons_center',
               'class' => 'b-button_flat_med',
               'padbot' => 10,
               'label' => ($this->step == 3?'Далее ':'Опубликовать проект на fl.ru').'<span class="b-icon b-icon__rarr b-icon_margleft_20 b-icon_top_2"></span>'
        )));
        
    }
}
