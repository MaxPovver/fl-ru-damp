<?
/**
 * Класс шаблонных функций
 * В шаблоне алиас как "%%имя_функции(...)"
 */
class system_tpl_helper {
	/**
	 * Вывод возраста
	 * @param object $timestamp Количество секунд UNIX
	 * @return Строка возраста с падежом
	 */
    function user_age_str($timestamp) {
        $ia = (int)time()-(int)$timestamp;
        
        $y = floor($ia/(31557600/*60*60*24*365.25*/));
        
        $int = ($y < 10 || $y > 20);
        $yi  = ($y%10);
        if($yi == 1 && $int) {
            $in = "год";    
        } elseif($yi >= 1 && $yi <=4 && $int) {
            $in = "года";    
        } else {    
            $in = "лет";
        }
        
        return $y." ".$in;
    }
}
?>