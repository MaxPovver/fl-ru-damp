<?php
/* 
 * 
 * Данный файл является частью проекта Веб Мессенджер.
 * 
 * Все права защищены. (c) 2005-2009 ООО "ТОП".
 * Данное программное обеспечение и все сопутствующие материалы
 * предоставляются на условиях лицензии, доступной по адресу
 * http://webim.ru/license.html
 * 
 */
?>
<?php

interface DBDriver {

  public function __construct($link = null);

  public function getEscapedString($str);

  public function Query($query_string, $parms = null);

  public function execArrayOfQuerys($querys);

  public function nextRecord();

  public function getRow();

  public function getNumRows();

  public function getInsertId();

  public function getArrayOfRows();

}

?>
