<?
/**
 * Файл для буферизации данных со страницы проектов (/projects)
 * 
 */

/**
 * Подключаем файл для работы с буфером мемкеша
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
// Инициализируем класс 
$memBuff = new memBuff();
$memBuff->flushGroup("prjsFPPages".$kind); // Записываем в буфер

?>