<?php

require_once('GuestForm.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/ProfessionsDropdown.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/BudgetExt.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Form/Element/GuestProjectUploader.php");

/**
 * Class GuestNewProjectForm
 * Форма нового проекта
 */
class GuestNewProjectForm  extends GuestForm
{
    /**
     * Инициализация формы
     */
    public function init()
    {
        $this->addElement(
           new Zend_Form_Element_Text('name', array(
               'label' => 'Название проекта',
               'required' => true,
               'padbot' => 30, // отступ снизу
               'maxlength' => 60,
               'filters' => $this->filtersAll,
               'validators' => array(
                   array('StringLength',true,array('max' => 60,'min' => 4))
                ),
               'placeholder' => 'Кого вы ищете и какую работу нужно выполнить.'
        )));        
        
        if ($this->isAdm()) {
            $this->addElement(
                new Zend_Form_Element_Text('link', array(
                    'label' => 'Ссылка на проект',
                    'required' => true,
                    'padbot' => 30, // отступ снизу
                    'filters' => $this->filters,
                    'validators' => array(
                        array('StringLength', true, array('min' => 4)),
                        array('UrlInvited', true, array('type' => GuestConst::TYPE_PROJECT))
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
              'padbot' => 30, // отступ снизу
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
          new Form_Element_BudgetExt('cost', array(
              'padbot' => $this->isAdm() ? 5 : 30, // отступ снизу
              'label' => 'Бюджет',
              'filters' => $this->filters,
              'value' => array(
                  'priceby_db_id' => 4
              )
          ))
        );
        
        if ($this->isAdm()) {
            $this->addElement(new Zend_Form_Element_Hidden('prefer_sbr', array('value' => 1)));
        } else {
            $this->addElement(
                new Zend_Form_Element_Radio('prefer_sbr',array(
                    'padbot' => 30, // отступ снизу
                    'label' => 'Способ оплаты',
                    'value' => 1,
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
            
            /*
            $this->addElement(
                new Zend_Form_Element_MultiCheckbox('filter', array(
                    'padbot' => 5, // отступ снизу
                    'label' => 'Ответить на проект могут только ...',
                    'value' => 'pro_only',
                    'multiOptions' => array(
                        'pro_only' => 'Фрилансеры с аккаунтом '.  view_profi() . ' или ' . view_pro(),
                        //'verify_only' => 'Фрилансеры c верификацией ' . view_verify()
                    )
                ))
            );*/
        }
        
        $this->addElement(
          new Form_Element_Hidden('auth', array(
              'validators' => array(
                  array('Digits')
               )
        )));
        
        $this->addElement(new Zend_Form_Element_Hidden('kind', array('value' => 1)));
        
        
    }    
    
    
    
    public function getCustomErrorMessage($err)
    {
        return GuestConst::getErrorMessage($err, GuestConst::TYPE_PROJECT);
    }
    
    
    public function getCustomMessage($mes)
    {
        return GuestConst::getMessage($mes, GuestConst::TYPE_PROJECT);
    }
    
}
