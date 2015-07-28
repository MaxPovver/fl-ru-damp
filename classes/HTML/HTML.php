<?php 

/**
 * Класс для отображения шаблонов. Общие шаблоны.
 *
 */
class HTML{
    
    
    /**
     * Вывод стандартного сообщения об ошибке (красный треугольник + текст)
     *
     * @param string $error		текст для сообщения об ошибке
     * @return string			html-код сообщения об ошибке
     */
    public function error($error){
        if ($error) {
            $error_str = "<div class=\"errorBox\"><img src=\"/images/ico_error.gif\" alt=\"\" width=\"22\" height=\"18\" border=\"0\"> &nbsp;$error</div>";
        }
        return $error_str;
    }
    
    /**
     * Вывод иконки ПРО для работодателя или фрилансера
     *
     * @param string $role			битовая строка с ролью юзера
     * @param boolean $is_pro_test	флаг статуса аккаунта ПРО (true - тестовый, false - полный)
     * @return string				html-код для иконки ПРО
     */
    public function pro($role, $is_pro_test = false){
          if (is_emp($role)) {
              $img = 'icons/e-pro.png'; 
              $class =  'ac-epro';
              $href  = '/payed-emp/';
          } else {
              $img = ($is_pro_test) ? 'ico_pro_test.gif' : 'icons/f-pro.png';
              $class = 'ac-pro';
              $href  = '/payed/';
          }
          return "<a href=\"$href\" class=\"".$class."\"><img src=\"/images/".$img."\" alt=\"PRO\" /></a>";
    }
    
    /**
     * Вывод строки информации о пользователе в виде "имя [логин]"
     *
     * @param string $role          информация о правах доступа
     * @param string $login         логин пользователя
     * @param string $username      имя
     * @param string @usersurname   фамилия
     * @return string			    html-код 
     */
    public function userName($role, $login, $username, $usersurname){
        $class = (is_emp($role))? "empname11" : "frlname11";
        $out = "<font class=\"$class\">&nbsp;<a class=\"$class\" href=\"/users/$login\" title=\"$username $usersurname\">$username"
             . "$usersurname</a> [<a class=\"$class\" href=\"/users/$login\" title=\"$login\">$login</a>]</font>";
    }
    
}

