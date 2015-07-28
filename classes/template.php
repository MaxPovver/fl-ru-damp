<?php
/**
* Класс для работы с функциями для работы с php шаблонами
*/

class Template
{
    /**
    * Формирует контент на освнове шаблона и возващает его
    *
    * @param string $path Путь к шаблону
    * @param array $vars Переменные для передачи в шаблон
    * @return string Контент свофрмированные на основе шаблона
    */
    public static function render($path, $vars = array())
    {
        extract($vars);

        ob_start();
        include($path);        
        return ob_get_clean();
    }
    
}
