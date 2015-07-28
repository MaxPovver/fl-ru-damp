<?php

/**
 * Class Form_Element
 * 
 * Базовый класс специфического элемента формы
 */

abstract class Form_Element extends Zend_Form_Element
{
    //Позволяет классу Form_View переопределять 
    //пути и вьюшки для кастомных элементов
    public $override_view_script = true;

}